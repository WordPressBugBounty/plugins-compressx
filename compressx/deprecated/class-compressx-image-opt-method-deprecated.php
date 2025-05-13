<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Image_Opt_Method_Deprecated
{
    public static function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public static function compress_image($image_id,$options,$log=false)
    {
        $success=true;
        $has_compress=true;
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        $uploads=wp_get_upload_dir();
        if(CompressX_Image_Opt_Method_Deprecated::need_compress($image_id,$options))
        {
            if(CompressX_Image_Meta::get_image_meta_compressed($image_id)==0)
            {
                $file_path = get_attached_file($image_id);

                foreach ($image_optimize_meta['size'] as $size_key => $size_meta)
                {
                    if(CompressX_Image_Opt_Method_Deprecated::skip_size($size_key,$options))
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

                        CompressX_Image_Opt_Method::WriteLog($log,'Start Compression '.basename($filename),'notice');

                        if(!file_exists($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'File '.basename($filename).' not exist,so skip compress.','notice');
                            $image_optimize_meta['size'][$size_key]['compress_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';
                            continue;
                        }

                        if($options['converter_method']=='gd')
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_gd_ex($filename,$output_path,$options);
                        }
                        else if($options['converter_method']=='imagick')
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_imagick_ex($filename,$output_path,$options);
                        }
                        else
                        {
                            $ret=CompressX_Image_Opt_Method::compress_image_gd_ex($filename,$output_path,$options);
                        }

                        if($ret['result']=='success')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Compression '.basename($filename).' succeeded.','notice');
                            $image_optimize_meta['size'][$size_key]['compress_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';

                            if($options['auto_remove_larger_format'])
                            {
                                if(filesize($output_path)>filesize($filename))
                                {
                                    CompressX_Image_Opt_Method::WriteLog($log,'Compressed size larger than original size, deleting file '.basename($output_path).'.','notice');
                                    @wp_delete_file($output_path);
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_compressed_size($image_id,filesize($filename));
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_compressed_size($image_id,filesize($output_path));
                                    }
                                }
                            }
                            else
                            {
                                if($size_key=="og")
                                {
                                    CompressX_Image_Meta::update_compressed_size($image_id,filesize($output_path));
                                }
                            }


                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);
                        }
                        else
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Compression '.basename($filename).' failed. Error:'.$ret['error'],'notice');
                            $image_optimize_meta['size'][$size_key]['compress_status']=0;
                            $image_optimize_meta['size'][$size_key]['status']='failed';
                            $image_optimize_meta['size'][$size_key]['error']=$ret['error'];
                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);

                            $success=false;
                            $has_compress=false;
                        }

                    }
                }

                if($has_compress)
                    CompressX_Image_Meta::update_image_meta_compressed($image_id,1);
            }
        }

        return $success;
    }

    public static function convert_to_webp($image_id,$options,$log=false)
    {
        $success=true;
        $has_convert=true;
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        $uploads=wp_get_upload_dir();
        if(CompressX_Image_Opt_Method_Deprecated::need_convert_to_webp_ex($image_id,$options))
        {
            if(CompressX_Image_Meta::get_image_meta_webp_converted($image_id)==0)
            {
                $file_path = get_attached_file( $image_id );

                foreach ($image_optimize_meta['size'] as $size_key=>$size_meta)
                {
                    if(CompressX_Image_Opt_Method_Deprecated::skip_size($size_key,$options))
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

                        CompressX_Image_Opt_Method::WriteLog($log,'Start WebP conversion '.basename($filename),'notice');

                        if(!file_exists($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'File '.basename($filename).' not exist,so skip convert.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_webp_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';
                            continue;
                        }

                        if($options['converter_method']=='gd')
                        {
                            $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$options);
                        }
                        else if($options['converter_method']=='imagick')
                        {
                            $ret=CompressX_Image_Opt_Method::convert_webp_imagick_ex($filename,$output_path,$options);
                        }
                        else
                        {
                            $ret=CompressX_Image_Opt_Method::convert_webp_gd_ex($filename,$output_path,$options);
                        }

                        if($ret['result']=='success')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to WebP succeeded.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_webp_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';

                            if($options['auto_remove_larger_format'])
                            {
                                if(filesize($output_path)>filesize($filename))
                                {
                                    CompressX_Image_Opt_Method::WriteLog($log,'WebP size larger than original size, deleting file '.basename($output_path).'.','notice');
                                    @wp_delete_file($output_path);
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_webp_converted_size($image_id,filesize($filename));
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_webp_converted_size($image_id,filesize($output_path));
                                    }
                                }
                            }
                            else
                            {
                                if($size_key=="og")
                                {
                                    CompressX_Image_Meta::update_webp_converted_size($image_id,filesize($output_path));
                                }
                            }


                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);
                        }
                        else
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to WebP failed. Error:'.$ret['error'],'notice');
                            $image_optimize_meta['size'][$size_key]['convert_webp_status']=0;
                            $image_optimize_meta['size'][$size_key]['status']='failed';
                            $image_optimize_meta['size'][$size_key]['error']=$ret['error'];
                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);

                            $success=false;
                            $has_convert=false;
                        }
                    }
                }

                if($has_convert)
                    CompressX_Image_Meta::update_image_meta_webp_converted($image_id,1);
            }
        }

        return $success;
    }

    public static function convert_to_avif($image_id,$options,$log=false)
    {
        $success=true;
        $has_convert=true;
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        $uploads=wp_get_upload_dir();
        if(CompressX_Image_Opt_Method_Deprecated::need_convert_to_avif_ex($image_id,$options))
        {
            if(CompressX_Image_Meta::get_image_meta_avif_converted($image_id)==0)
            {
                $file_path = get_attached_file( $image_id );

                foreach ($image_optimize_meta['size'] as $size_key=>$size_meta)
                {
                    if(CompressX_Image_Opt_Method_Deprecated::skip_size($size_key,$options))
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

                        CompressX_Image_Opt_Method::WriteLog($log,'Start AVIF conversion:'.basename($filename),'notice');

                        if(!file_exists($filename))
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'File '.basename($filename).' not exist,so skip convert.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_avif_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';
                            continue;
                        }

                        if($options['converter_method']=='gd')
                        {
                            $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$options);
                        }
                        else if($options['converter_method']=='imagick')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'test imagick '.basename($filename),'notice');
                            $ret=CompressX_Image_Opt_Method::convert_avif_imagick_ex($filename,$output_path,$options);
                        }
                        else
                        {
                            $ret=CompressX_Image_Opt_Method::convert_avif_gd_ex($filename,$output_path,$options);
                        }

                        if($ret['result']=='success')
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to AVIF succeeded.','notice');
                            $image_optimize_meta['size'][$size_key]['convert_avif_status']=1;
                            $image_optimize_meta['size'][$size_key]['status']='optimized';

                            if($options['auto_remove_larger_format'])
                            {
                                if(filesize($output_path)>filesize($filename))
                                {
                                    CompressX_Image_Opt_Method::WriteLog($log,'AVIF size larger than original size, deleting file '.basename($output_path).'.','notice');
                                    @wp_delete_file($output_path);
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_avif_converted_size($image_id,filesize($filename));
                                    }
                                }
                                else
                                {
                                    if($size_key=="og")
                                    {
                                        CompressX_Image_Meta::update_avif_converted_size($image_id,filesize($output_path));
                                    }
                                }
                            }
                            else
                            {
                                if($size_key=="og")
                                {
                                    CompressX_Image_Meta::update_avif_converted_size($image_id,filesize($output_path));
                                }
                            }

                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);
                        }
                        else
                        {
                            CompressX_Image_Opt_Method::WriteLog($log,'Converting '.basename($filename).' to AVIF failed. Error:'.$ret['error'],'notice');
                            $image_optimize_meta['size'][$size_key]['convert_avif_status']=0;
                            $image_optimize_meta['size'][$size_key]['status']='failed';
                            $image_optimize_meta['size'][$size_key]['error']=$ret['error'];
                            CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);

                            $success=false;
                            $has_convert=false;
                        }
                    }
                }

                if($has_convert)
                    CompressX_Image_Meta::update_image_meta_avif_converted($image_id,1);
            }
        }

        return $success;
    }

    public static function need_convert_to_webp_ex($image_id,$options)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='webp'||$type=='avif')
        {
            return false;
        }

        return $options['convert_to_webp'];
    }

    public static function need_convert_to_avif_ex($image_id,$options)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='avif')
        {
            return false;
        }

        return $options['convert_to_avif'];
    }

    public static function need_compress($image_id,$options)
    {
        $file_path = get_attached_file($image_id);
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($type=='webp')
        {
            return $options['compressed_webp'];
        }
        else if($type=='avif')
        {
            return $options['compressed_avif'];
        }

        return false;
    }

    public static function skip_size($size_key,$options)
    {
        if(isset($options['skip_size'])&&isset($options['skip_size'][$size_key]))
        {
            return $options['skip_size'][$size_key];
        }

        return false;
    }
}