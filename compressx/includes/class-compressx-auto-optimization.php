<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Auto_Optimization
{
    public $auto_opt_ids;
    public $log=false;

    public function __construct()
    {
        $this->auto_opt_ids=array();

        add_action( 'add_attachment',                  array( $this, 'add_auto_opt_id' ), 1000 );
        add_filter( 'wp_generate_attachment_metadata', array( $this, 'update_auto_opt_id_status' ), 1000, 2 );
        add_filter( 'wp_update_attachment_metadata',   array( $this, 'auto_optimize' ), 1000, 2 );

        add_filter( 'compressx_allowed_image_auto_optimization',   array( $this, 'allowed_image_auto_optimization' ), 10 );
    }

    public function allowed_image_auto_optimization()
    {
        $is_auto=get_option('compressx_auto_optimize',false);

        if($is_auto)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function add_auto_opt_id($attachment_id)
    {
        $is_auto=apply_filters('compressx_allowed_image_auto_optimization',false);

        if($is_auto)
        {
            $this->auto_opt_ids[$attachment_id]=0;
        }
    }

    public function update_auto_opt_id_status($metadata, $attachment_id)
    {
        if(isset( $this->auto_opt_ids[$attachment_id]))
        {
            if ( ! wp_attachment_is_image( $attachment_id ) )
            {
                unset($this->auto_opt_ids[$attachment_id]);
            }
            else
            {
                $this->WriteLog('Add attachment images id:'.$attachment_id,'notice');
                $this->auto_opt_ids[$attachment_id]=1;
            }
        }

        return $metadata;
    }

    public function auto_optimize($metadata, $attachment_id)
    {
        $is_auto=apply_filters('compressx_allowed_image_auto_optimization',false);

        if($is_auto)
        {
            if(isset($this->auto_opt_ids[$attachment_id])&&$this->auto_opt_ids[$attachment_id])
            {
                $supported_mime_types = array(
                    "image/jpg",
                    "image/jpeg",
                    "image/png",
                    "image/webp",
                    "image/avif");

                $mime_type=get_post_mime_type($attachment_id);
                if(in_array($mime_type,$supported_mime_types))
                {
                    if($this->is_excludes($attachment_id))
                    {
                        $this->WriteLog('Exclude attachment images id:'.$attachment_id,'notice');
                        return $metadata;
                    }

                    set_time_limit(300);
                    delete_transient('compressx_set_global_stats');
                    $this->do_optimize_image($attachment_id);
                }
            }
        }

        return $metadata;
    }

    public function is_excludes($attachment_id)
    {
        $excludes=get_option('compressx_media_excludes',array());
        $exclude_regex_folder=array();
        if(!empty($excludes))
        {
            foreach ($excludes as $item)
            {
                $exclude_regex_folder[]='#'.preg_quote(CompressX_Image_Opt_Method::transfer_path($item), '/').'#';
            }
        }

        if(CompressX_Image_Opt_Method::exclude_path($attachment_id,$exclude_regex_folder))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function WriteLog($log,$type)
    {
        if (is_a($this->log, 'CompressX_Log'))
        {
            $this->log->WriteLog($log,$type);
        }
        else
        {
            $this->log=new CompressX_Log();
            $this->log->OpenLogFile();
            $this->log->WriteLog($log,$type);
        }
    }

    public function do_optimize_image($attachment_id)
    {
        $options=get_option('compressx_general_settings',array());

        $this->log=new CompressX_Log();
        $this->log->CreateLogFile();

        $this->WriteLog('Start optimizing new media images id:'.$attachment_id,'notice');

        $options['convert_to_webp']=CompressX_Image_Opt_Method::get_convert_to_webp();
        $options['convert_to_avif']=CompressX_Image_Opt_Method::get_convert_to_avif();

        $options['compressed_webp']=CompressX_Image_Opt_Method::get_compress_to_webp();
        $options['compressed_avif']=CompressX_Image_Opt_Method::get_compress_to_avif();

        $options['converter_method']=CompressX_Image_Opt_Method::get_converter_method();

        $quality_options=get_option('compressx_quality',array());

        $options['quality']=isset($quality_options['quality'])?$quality_options['quality']:'lossy';
        if($options['quality']=="custom")
        {
            $options['quality_webp']=isset($quality_options['quality_webp'])?$quality_options['quality_webp']: 80;
            $options['quality_avif']=isset($quality_options['quality_avif'])?$quality_options['quality_avif']: 60;
        }

        if(isset($options['resize']))
        {
            $options['resize_enable']= isset($options['resize']['enable'])?$options['resize']['enable']:true;
            $options['resize_width']=isset( $options['resize']['width'])? $options['resize']['width']:2560;
            $options['resize_height']=isset( $options['resize']['height'])? $options['resize']['height']:2560;
        }
        else
        {
            $options['resize_enable']= true;
            $options['resize_width']=2560;
            $options['resize_height']=2560;
        }

        $options['remove_exif']=isset($options['remove_exif'])?$options['remove_exif']:false;
        $options['auto_remove_larger_format']=isset($options['auto_remove_larger_format'])?$options['auto_remove_larger_format']:true;

        $image_optimize_meta=$this->get_images_meta($attachment_id,$options);

        CompressX_Image_Meta::update_image_progressing($attachment_id);

        $file_path = get_attached_file( $attachment_id );
        if(empty($file_path))
        {
            CompressX_Image_Opt_Method::WriteLog($this->log,'Image:'.$attachment_id.' failed. Error: failed to get get_attached_file','notice');

            $image_optimize_meta['size']['og']['status']='failed';
            $image_optimize_meta['size']['og']['error']='Image:'.$attachment_id.' failed. Error: failed to get get_attached_file';
            CompressX_Image_Meta::update_image_meta_status($attachment_id,'failed');
            CompressX_Image_Meta::update_images_meta($attachment_id,$image_optimize_meta);
            $ret['result']='success';
            return $ret;
        }

        if(!$this->check_file_mime_content_type($file_path))
        {
            CompressX_Image_Opt_Method::WriteLog($this->log,'Image:'.$attachment_id.' failed. Error: mime content type not support','notice');

            $image_optimize_meta['size']['og']['status']='failed';
            $image_optimize_meta['size']['og']['error']='Image:'.$attachment_id.' failed. Error: mime content type not support';

            CompressX_Image_Meta::update_image_meta_status($attachment_id,'failed');
            CompressX_Image_Meta::update_images_meta($attachment_id,$image_optimize_meta);
            $ret['result']='success';
            return $ret;
        }

        if($image_optimize_meta['resize_status']==0)
        {
            if(CompressX_Image_Opt_Method::resize($attachment_id,$options, $this->log))
            {
                $image_optimize_meta['resize_status']=1;
                CompressX_Image_Meta::update_images_meta($attachment_id,$image_optimize_meta);
            }
            else
            {
                //
            }
        }

        $has_error=false;

        if(CompressX_Image_Opt_Method::compress_image($attachment_id,$options, $this->log)===false)
        {
            $has_error=true;
        }

        if(!$this->is_exclude_webp($attachment_id,$options))
        {
            if(CompressX_Image_Opt_Method::convert_to_webp($attachment_id,$options, $this->log)===false)
            {
                $has_error=true;
            }
        }


        if(!$this->is_exclude_avif($attachment_id,$options))
        {
            if(CompressX_Image_Opt_Method::convert_to_avif($attachment_id,$options, $this->log)===false)
            {
                $has_error=true;
            }
        }


        CompressX_Image_Meta::delete_image_progressing($attachment_id);
        if($has_error)
        {
            CompressX_Image_Meta::update_image_meta_status($attachment_id,'failed');
        }
        else
        {
            CompressX_Image_Meta::update_image_meta_status($attachment_id,'optimized');
            do_action('compressx_uploading_add_watermark',$attachment_id);
            do_action('compressx_after_optimize_image',$attachment_id);
        }

        $ret['result']='success';
        return $ret;
    }

    public function check_file_mime_content_type($file_path)
    {
        if(function_exists( 'mime_content_type' ))
        {
            $type=mime_content_type($file_path);
            if($type=="text/html")
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }

    public function get_images_meta($image_id,$options)
    {
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        if(empty($image_optimize_meta))
        {
            $image_optimize_meta =CompressX_Image_Meta::generate_images_meta($image_id,$options);
        }

        return $image_optimize_meta;
    }

    public function is_exclude_avif($image_id,$options)
    {
        $options['exclude_png']=isset($options['exclude_png'])?$options['exclude_png']:false;
        $options['exclude_jpg_avif']=isset($options['exclude_jpg_avif'])?$options['exclude_jpg_avif']:false;

        $file_path = get_attached_file( $image_id );
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if( $options['exclude_png']&&$type== 'png')
        {
            return true;
        }
        else if($options['exclude_jpg_avif']&&$type== 'jpg')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function is_exclude_webp($image_id,$options)
    {
        $options['exclude_png_webp']=isset($options['exclude_png_webp'])?$options['exclude_png_webp']:false;
        $options['exclude_jpg_webp']=isset($options['exclude_jpg_webp'])?$options['exclude_jpg_webp']:false;

        $file_path = get_attached_file( $image_id );
        $type=pathinfo($file_path, PATHINFO_EXTENSION);

        if($options['exclude_png_webp']&&$type== 'png')
        {
            return true;
        }
        else if($options['exclude_jpg_webp']&&$type== 'jpg')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}