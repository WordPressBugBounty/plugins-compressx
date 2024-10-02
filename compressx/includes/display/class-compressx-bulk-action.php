<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Bulk_Action
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_start_scan_unoptimized_image', array($this, 'start_scan_unoptimized_image'));
        add_action('wp_ajax_compressx_init_bulk_optimization_task', array($this, 'init_bulk_optimization_task'));
        add_action('wp_ajax_compressx_run_optimize', array($this, 'run_optimize'));
        add_action('wp_ajax_compressx_get_opt_progress', array($this, 'get_opt_progress'));
    }

    public function start_scan_unoptimized_image()
    {
        check_ajax_referer( 'compressx_ajax', 'nonce' );
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        update_option("compressx_need_optimized_images",0,false);
        delete_transient('compressx_set_global_stats');
        $max_image_count=$this->get_max_image_count();

        $force=isset($_POST['force'])?sanitize_key($_POST['force']):'0';
        if($force=='1')
        {
            $force=true;
        }
        else
        {
            $force=false;
        }
        $page=300;
        $offset=0;
        $need_optimize_images=array();

        $convert_to_webp=get_option('compressx_output_format_webp',true);
        $convert_to_avif=get_option('compressx_output_format_avif',true);
        $converter_method=get_option('compressx_converter_method',false);
        if(empty($converter_method))
        {
            if( function_exists( 'gd_info' ) && function_exists( 'imagewebp' )  )
            {
                $converter_method= 'gd';
            }
            else if ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) )
            {
                $converter_method= 'imagick';
            }
            else
            {
                $converter_method= 'gd';
            }
        }

        if($converter_method=='gd')
        {
            if($convert_to_webp&&CompressX_Image_Opt_Method::is_support_gd_webp())
            {
                $convert_to_webp=true;
            }
            else
            {
                $convert_to_webp=false;
            }

            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_gd_avif())
            {
                $convert_to_avif=true;
            }
            else
            {
                $convert_to_avif=false;
            }
        }
        else
        {
            if($convert_to_webp&&CompressX_Image_Opt_Method::is_support_imagick_webp())
            {
                $convert_to_webp=true;
            }
            else
            {
                $convert_to_webp=false;
            }

            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_imagick_avif())
            {
                $convert_to_avif=true;
            }
            else
            {
                $convert_to_avif=false;
            }
        }

        $excludes=get_option('compressx_media_excludes',array());
        $exclude_regex_folder=array();
        if(!empty($excludes))
        {
            foreach ($excludes as $item)
            {
                $exclude_regex_folder[]='#'.preg_quote(CompressX_Image_Opt_Method::transfer_path($item), '/').'#';
            }
        }

        for ($offset=0; $offset <= $max_image_count; $offset += $page)
        {
            $images=CompressX_Image_Opt_Method::scan_unoptimized_image($page,$offset,$convert_to_webp,$convert_to_avif,$exclude_regex_folder,$force);
            $need_optimize_images=array_merge($images,$need_optimize_images);
        }
        update_option("compressx_need_optimized_images",sizeof($need_optimize_images),false);

        $log=new CompressX_Log();
        $log->CreateLogFile();
        $log->WriteLog("Scanning images: ".sizeof($need_optimize_images)." found ","notice");

        //$ret['result']='failed';
        //$ret['error']="Scanning images: ".sizeof($need_optimize_images)." found ";
        $ret['result']='success';
        $ret['progress']=sprintf(
        /* translators: %1$d: Scanning images*/
            __('Scanning images: %1$d found' ,'compressx'),
            sizeof($need_optimize_images));
        $ret['finished']=true;
        $ret['test']=$max_image_count;

        echo wp_json_encode($ret);

        die();
    }

    public function get_max_image_count()
    {
        global $wpdb;

        $options=get_option('compressx_general_settings',array());
        $exclude_png=isset($options['exclude_png'])?$options['exclude_png']:false;
        if($exclude_png)
        {
            $supported_mime_types = array(
                "image/jpg",
                "image/jpeg",
                "image/webp",
                "image/avif");

            $args  = $supported_mime_types;
            $result=$wpdb->get_results($wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s)", $args ),ARRAY_N);
        }
        else
        {
            $supported_mime_types = array(
                "image/jpg",
                "image/jpeg",
                "image/png",
                "image/webp",
                "image/avif");

            $args  = $supported_mime_types;
            $result=$wpdb->get_results($wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s,%s)", $args ),ARRAY_N);
        }


        if($result && sizeof($result)>0)
        {
            return $result[0][0];
        }
        else
        {
            return 0;
        }
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public function is_image_optimized($post_id,$exclude_regex_folder,$force=false)
    {
        $meta=CompressX_Image_Meta::get_image_meta($post_id);
        if(!empty($meta)&&isset($meta['size'])&&!empty($meta['size']))
        {
            foreach ($meta['size'] as $size_key => $size_data)
            {
                if(!isset($size_data['convert_webp_status'])||$size_data['convert_webp_status']==0||$force)
                {
                    if($this->exclude_path($post_id,$exclude_regex_folder))
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
            }

            return true;
        }
        else
        {
            if($this->exclude_path($post_id,$exclude_regex_folder))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

    }

    public function exclude_path($post_id,$exclude_regex_folder)
    {
        if(empty($exclude_regex_folder))
        {
            return false;
        }

        $file_path = get_attached_file( $post_id );
        $file_path = $this->transfer_path($file_path);
        if ($this->regex_match($exclude_regex_folder, $file_path))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function regex_match($regex_array,$string)
    {
        if(empty($regex_array))
        {
            return false;
        }

        foreach ($regex_array as $regex)
        {
            if(preg_match($regex,$string))
            {
                return true;
            }
        }

        return false;
    }

    public function init_bulk_optimization_task()
    {
        check_ajax_referer( 'compressx_ajax', 'nonce');
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        $force=isset($_POST['force'])?sanitize_key($_POST['force']):'0';
        if($force=='1')
        {
            $force=true;
        }
        else
        {
            $force=false;
        }

        $task=new CompressX_ImgOptim_Task();
        $ret=$task->init_task($force);
        echo wp_json_encode($ret);
        die();
    }

    public function run_optimize()
    {
        check_ajax_referer( 'compressx_ajax', 'nonce');
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }

        set_time_limit(180);
        $task=new CompressX_ImgOptim_Task();

        $ret=$task->get_task_status();

        if($ret['result']=='success'&&$ret['status']=='completed')
        {
            $this->flush($ret);
            $task->do_optimize_image();
            //echo wp_json_encode($ret);
        }
        else
        {
            echo wp_json_encode($ret);
        }
        die();
    }

    private function flush($ret)
    {
        $json=wp_json_encode($ret);
        if(!headers_sent())
        {
            header('Content-Length: '.strlen($json));
            header('Connection: close');
            header('Content-Encoding: none');
        }


        if (session_id())
            session_write_close();
        echo wp_json_encode($ret);

        if(function_exists('fastcgi_finish_request'))
        {
            fastcgi_finish_request();
        }
        else
        {
            ob_flush();
            flush();
        }
    }

    public function get_opt_progress()
    {
        check_ajax_referer( 'compressx_ajax', 'nonce');
        $check=current_user_can('manage_options');
        if(!$check)
        {
            die();
        }
        $task=new CompressX_ImgOptim_Task();

        $result=$task->get_task_progress();

        if(empty($result['error_list']))
        {
            $result['html']='';
            $result['update_error_log']=false;
        }
        else
        {
            $result['update_error_log']=true;
            $result['html']='';
        }

        echo wp_json_encode($result);

        die();
    }
}
