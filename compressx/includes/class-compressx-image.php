<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Compressx_Image
{
    public $image_id;
    public $options;
    public $log;
    public function __construct($image_id,$options=array())
    {
        $this->image_id=$image_id;
        $this->options=$options;

        $this->log=new CompressX_Log();
        $this->log->OpenLogFile();

        if(!CompressX_Image_Meta::has_image_meta($this->image_id))
        {
            CompressX_Image_Meta::generate_images_meta($this->image_id, $this->options);
        }
    }

    public function resize()
    {
        if(CompressX_Image_Meta::get_image_meta_value($this->image_id,'resize_status')==0)
        {
            if(CompressX_Image_Opt_Method::resize($this->image_id,$this->options,$this->log))
            {
                CompressX_Image_Meta::update_images_meta_value($this->image_id,'resize_status',1);
            }
        }
    }

    public function resize_ex($metadata)
    {
        if(CompressX_Image_Meta::get_image_meta_value($this->image_id,'resize_status')==0)
        {
            $ret=CompressX_Image_Opt_Method::resize_ex($this->image_id,$this->options,$metadata,$this->log);
            if($ret['result']=='success')
            {
                CompressX_Image_Meta::update_images_meta_value($this->image_id,'resize_status',1);
                return $ret;
            }
        }

        $ret['result']='success';
        $ret['meta']=$metadata;
        return $ret;
    }

    public function convert()
    {
        $has_error=false;

        if($this->compress_image()===false)
        {
            $has_error=true;
        }

        if($this->convert_to_webp()===false)
        {
            $has_error=true;
        }

        if($this->convert_to_avif()===false)
        {
            $has_error=true;
        }

        return !$has_error;
    }

    public function compress_image()
    {
        $success=true;
        $has_compress=true;

        if($this->need_compress())
        {
            if(CompressX_Image_Meta::get_image_meta_compressed($this->image_id)==0)
            {
                $uploads=wp_get_upload_dir();
                $size =CompressX_Image_Meta::get_image_meta_value($this->image_id,'size');

                $file_path = get_attached_file($this->image_id);

                foreach ($size as $size_key => $size_meta)
                {
                    if($this->skip_size($size_key))
                    {
                        continue;
                    }

                    if ($size_meta['compress_status'] == 0)
                    {
                        if ($size_key == "og")
                        {
                            $filename = $file_path;
                        }
                        else if(CompressX_Image_Opt_Method::is_elementor_thumbs($size_key))
                        {
                            $filename = $uploads['basedir'].'/'.$size_meta['file'];
                        }
                        else
                        {
                            $filename = path_join(dirname($file_path), $size_meta['file']);
                        }

                        $output_path=CompressX_Image_Method::get_output_path($filename);

                        CompressX_Image_Opt_Method::WriteLog($this->log,'Start Compression '.basename($filename),'notice');

                        if(!file_exists($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($this->log,'File '.basename($filename).' not exist,so skip compress.','notice');
                            $size_meta['compress_status']=1;
                            $size_meta['status']='optimized';
                            CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);
                            continue;
                        }

                        if($this->options['converter_method']=='gd')
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_gd_ex($filename,$output_path,$this->options);
                        }
                        else if($this->options['converter_method']=='imagick')
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_imagick_ex($filename,$output_path,$this->options);
                        }
                        else
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_gd_ex($filename,$output_path,$this->options);
                        }

                        if($ret['result']=='success')
                        {
                            CompressX_Image_Opt_Method::WriteLog($this->log,'Compression '.basename($filename).' succeeded.','notice');

                            if($this->options['auto_remove_larger_format'])
                            {
                                if(filesize($output_path)>filesize($filename))
                                {
                                    CompressX_Image_Opt_Method::WriteLog($this->log,'Compressed size larger than original size, deleting file '.basename($output_path).'.','notice');
                                    @wp_delete_file($output_path);
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_compressed_size($this->image_id,filesize($filename));
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_compressed_size($this->image_id,filesize($output_path));
                                    }
                                }
                            }
                            else
                            {
                                if($size_key=="og")
                                {
                                    CompressX_Image_Meta::update_compressed_size($this->image_id,filesize($output_path));
                                }
                            }

                            $size_meta['compress_status']=1;
                            $size_meta['status']='optimized';
                            CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);
                        }
                        else
                        {
                            CompressX_Image_Opt_Method::WriteLog($this->options,'Compression '.basename($filename).' failed. Error:'.$ret['error'],'notice');
                            $size_meta['compress_status']=0;
                            $size_meta['status']='failed';
                            $size_meta['error']=$ret['error'];
                            CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);

                            $success=false;
                            $has_compress=false;
                        }

                    }
                }

                if($has_compress)
                    CompressX_Image_Meta::update_image_meta_compressed($this->image_id,1);
            }
        }

        return $success;
    }

    public function convert_to_webp()
    {
        if(!CompressX_Image_Method::is_exclude_webp($this->image_id,$this->options))
        {
            $success=true;
            $has_convert=true;
            if($this->need_convert_to_webp_ex())
            {
                if(CompressX_Image_Meta::get_image_meta_webp_converted($this->image_id)==0)
                {
                    $size =CompressX_Image_Meta::get_image_meta_value($this->image_id,'size');
                    $uploads=wp_get_upload_dir();
                    $file_path = get_attached_file( $this->image_id );

                    foreach ($size as $size_key=>$size_meta)
                    {
                        if($this->skip_size($size_key))
                        {
                            continue;
                        }

                        if($size_meta['convert_webp_status']==0)
                        {
                            if($size_key=="og")
                            {
                                $filename= $file_path;
                            }
                            else if(CompressX_Image_Opt_Method::is_elementor_thumbs($size_key))
                            {
                                $filename = $uploads['basedir'].'/'.$size_meta['file'];
                            }
                            else
                            {
                                $filename= path_join( dirname( $file_path ), $size_meta['file'] );
                            }

                            $output_path=CompressX_Image_Method::get_output_path($filename);
                            $output_path=$output_path.'.webp';

                            CompressX_Image_Opt_Method::WriteLog($this->log,'Start WebP conversion '.basename($filename),'notice');

                            if(!file_exists($filename))
                            {
                                CompressX_Image_Opt_Method::WriteLog($this->log,'File '.basename($filename).' not exist,so skip convert.','notice');
                                $size_meta['convert_webp_status']=1;
                                $size_meta['status']='optimized';
                                CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);
                                continue;
                            }

                            if($this->options['converter_method']=='gd')
                            {
                                $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$this->options);
                            }
                            else if($this->options['converter_method']=='imagick')
                            {
                                $ret=CompressX_Image_Opt_Method::convert_webp_imagick_ex($filename,$output_path,$this->options);
                            }
                            else
                            {
                                $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$this->options);
                            }

                            if($ret['result']=='success')
                            {
                                CompressX_Image_Opt_Method::WriteLog($this->log,'Converting '.basename($filename).' to WebP succeeded.','notice');

                                if($this->options['auto_remove_larger_format'])
                                {
                                    if(filesize($output_path)>filesize($filename))
                                    {
                                        CompressX_Image_Opt_Method::WriteLog($this->log,'WebP size larger than original size, deleting file '.basename($output_path).'.','notice');
                                        @wp_delete_file($output_path);
                                        if($size_key=="og")
                                        {
                                            CompressX_Image_Meta::update_webp_converted_size($this->image_id,filesize($filename));
                                        }
                                    }
                                    else
                                    {
                                        if($size_key=="og")
                                        {
                                            CompressX_Image_Meta::update_webp_converted_size($this->image_id,filesize($output_path));
                                        }
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_webp_converted_size($this->image_id,filesize($output_path));
                                    }
                                }

                                $size_meta['convert_webp_status']=1;
                                $size_meta['status']='optimized';
                                CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);
                            }
                            else
                            {
                                CompressX_Image_Opt_Method::WriteLog($this->log,'Converting '.basename($filename).' to WebP failed. Error:'.$ret['error'],'notice');
                                $size_meta['convert_webp_status']=0;
                                $size_meta['status']='failed';
                                $size_meta['error']=$ret['error'];
                                CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);

                                $success=false;
                                $has_convert=false;
                            }
                        }
                    }

                    if($has_convert)
                        CompressX_Image_Meta::update_image_meta_webp_converted($this->image_id,1);
                }
            }

            return $success;
        }

        return true;
    }

    public function convert_to_avif()
    {
        if(!CompressX_Image_Method::is_exclude_avif($this->image_id,$this->options))
        {
            $success=true;
            $has_convert=true;

            if($this->need_convert_to_avif_ex())
            {
                if(CompressX_Image_Meta::get_image_meta_avif_converted($this->image_id)==0)
                {
                    $size =CompressX_Image_Meta::get_image_meta_value($this->image_id,'size');
                    $uploads=wp_get_upload_dir();
                    $file_path = get_attached_file( $this->image_id );

                    foreach ($size as $size_key=>$size_meta)
                    {
                        if($this->skip_size($size_key))
                        {
                            continue;
                        }

                        if($size_meta['convert_avif_status']==0)
                        {
                            if($size_key=="og")
                            {
                                $filename= $file_path;
                            }
                            else if(CompressX_Image_Opt_Method::is_elementor_thumbs($size_key))
                            {
                                $filename = $uploads['basedir'].'/'.$size_meta['file'];
                            }
                            else
                            {
                                $filename= path_join( dirname( $file_path ), $size_meta['file'] );
                            }

                            $output_path=CompressX_Image_Method::get_output_path($filename);
                            $output_path=$output_path.'.avif';

                            CompressX_Image_Opt_Method::WriteLog($this->log,'Start AVIF conversion:'.basename($filename),'notice');

                            if(!file_exists($filename))
                            {
                                CompressX_Image_Opt_Method::WriteLog($this->log,'File '.basename($filename).' not exist,so skip convert.','notice');
                                $size_meta['convert_avif_status']=1;
                                $size_meta['status']='optimized';
                                CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);

                                continue;
                            }

                            if($this->options['converter_method']=='gd')
                            {
                                $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$this->options);
                            }
                            else if($this->options['converter_method']=='imagick')
                            {
                                CompressX_Image_Opt_Method::WriteLog($this->log,'test imagick '.basename($filename),'notice');
                                $ret=CompressX_Image_Opt_Method::convert_avif_imagick_ex($filename,$output_path,$this->options);
                            }
                            else
                            {
                                $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$this->options);
                            }

                            if($ret['result']=='success')
                            {
                                CompressX_Image_Opt_Method::WriteLog($this->log,'Converting '.basename($filename).' to AVIF succeeded.','notice');

                                if($this->options['auto_remove_larger_format'])
                                {
                                    if(filesize($output_path)>filesize($filename))
                                    {
                                        CompressX_Image_Opt_Method::WriteLog($this->log,'AVIF size larger than original size, deleting file '.basename($output_path).'.','notice');
                                        @wp_delete_file($output_path);
                                        if($size_key=="og")
                                        {
                                            CompressX_Image_Meta::update_avif_converted_size($this->image_id,filesize($filename));
                                        }
                                    }
                                    else
                                    {
                                        if($size_key=="og")
                                        {
                                            CompressX_Image_Meta::update_avif_converted_size($this->image_id,filesize($output_path));
                                        }
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_avif_converted_size($this->image_id,filesize($output_path));
                                    }
                                }

                                $size_meta['convert_avif_status']=1;
                                $size_meta['status']='optimized';
                                CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);
                            }
                            else
                            {
                                CompressX_Image_Opt_Method::WriteLog($this->log,'Converting '.basename($filename).' to AVIF failed. Error:'.$ret['error'],'notice');
                                $size_meta['convert_avif_status']=0;
                                $size_meta['status']='failed';
                                $size_meta['error']=$ret['error'];
                                CompressX_Image_Meta::update_image_meta_size($this->image_id,$size_key,$size_meta);

                                $success=false;
                                $has_convert=false;
                            }
                        }
                    }

                    if($has_convert)
                        CompressX_Image_Meta::update_image_meta_avif_converted($this->image_id,1);
                }
            }

            return $success;
        }

        return true;
    }

    public function need_compress()
    {
        $file_path = get_attached_file($this->image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='webp')
        {
            return $this->options['compressed_webp'];
        }
        else if($type=='avif')
        {
            return $this->options['compressed_avif'];
        }

        return false;
    }

    public function skip_size($size_key)
    {
        if(isset($this->options['skip_size'])&&isset($this->options['skip_size'][$size_key]))
        {
            return $this->options['skip_size'][$size_key];
        }

        return false;
    }

    public function need_convert_to_webp_ex()
    {
        $file_path = get_attached_file($this->image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='webp'||$type=='avif')
        {
            return false;
        }

        return $this->options['convert_to_webp'];
    }

    public function need_convert_to_avif_ex()
    {
        $file_path = get_attached_file($this->image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='avif')
        {
            return false;
        }

        return $this->options['convert_to_avif'];
    }
}