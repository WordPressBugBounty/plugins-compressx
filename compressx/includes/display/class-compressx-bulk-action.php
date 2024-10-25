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

        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif");

        $args  = $supported_mime_types;
        $result=$wpdb->get_results($wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s,%s)", $args ),ARRAY_N);

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

        ob_start();
        $this->output_overview();
        $result['overview_html'] = ob_get_clean();

        echo wp_json_encode($result);

        die();
    }

    public function output_overview()
    {
        $webp_data=$this->get_optimized_data();
        $failed_images_count=CompressX_Image_Meta::get_failed_images_count();
        $url=admin_url().'upload.php?compressx-filter=failed_optimized';
        ?>
        <div class="cx-overview_body-free">
            <div class="cx-overview_body-webp-free">
                <div class="cx-process-webp">
                    <div class="cx-process-position">
                        <span class="cx-processed"><?php echo esc_html($webp_data['webp_converted_percent']);?>%<span class="cx-percent-sign"> images</span></span>
                        <span class="cx-processing"><?php esc_html_e('Outputted to WEBP','compressx')?></span>
                    </div>
                </div>
                <div class="cx-process-webp">
                    <div class="cx-process-position">
                        <span class="cx-processed"><?php echo esc_html($webp_data['avif_converted_percent']);?>%<span class="cx-percent-sign"> images</span></span>
                        <span class="cx-processing"><?php esc_html_e('Outputted to AVIF','compressx')?></span>
                    </div>
                </div>
            </div>
            <div class="compressing-converting-information" style="position:relative;">

                <div style="padding-bottom: 0.5rem;"><span><strong><?php esc_html_e('Processed Images','compressx')?></strong></a></span>
                    <span> (</span><a href="<?php echo esc_url($url);?>"><span><?php esc_html_e('Failed: ','compressx')?></span><span id="cx_failed_images_count"><?php echo esc_html($failed_images_count);?></span></a><span>)</span></div>
                <div class="cx-overview_body-webp-free">

                    <div class="cx-process-media-files">

                        <span class="cx-process-media-type"><?php esc_html_e('Webp Size:','compressx')?></span><span class="cx-porcess-media-files-label"><?php echo esc_html(size_format($webp_data['webp_saved'],2));?></span>
                    </div>
                    <div class="cx-process-media-files">
                        <span class="cx-process-media-type"><?php esc_html_e('Total Savings:','compressx')?></span><span class="cx-porcess-media-files-label"><?php echo esc_html($webp_data['webp_saved_percent']);?>%</span>
                    </div>
                    <div class="cx-process-media-files">
                        <span class="cx-process-media-type"><?php esc_html_e('AVIF Size:','compressx')?></span><span class="cx-porcess-media-files-label"><?php echo esc_html(size_format($webp_data['avif_saved'],2));?></span>
                    </div>
                    <div class="cx-process-media-files">
                        <span class="cx-process-media-type"><?php esc_html_e('Total Savings:','compressx')?></span><span class="cx-porcess-media-files-label"><?php echo esc_html($webp_data['avif_saved_percent']);?>%</span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_optimized_data()
    {
        $stats=CompressX_Image_Meta::get_global_stats();

        $webp_data=array();

        $webp_data['webp_outputted']=$stats['webp_converted']+$stats['webp_compressed'];
        $webp_data['webp_total']=$stats['webp_total'];
        $webp_data['webp_saved']=$stats['webp_saved'];
        $webp_images_count=$this->get_max_webp_image_count();
        $avif_images_count=$this->get_max_avif_image_count();

        if($webp_images_count!=0)
        {
            $webp_data['webp_converted_percent'] = ( $webp_data['webp_outputted'] / $webp_images_count ) * 100;
            $webp_data['webp_converted_percent'] = round( $webp_data['webp_converted_percent'], 2 );
        }
        else
        {
            $webp_data['webp_converted_percent']=0;
        }

        if($stats['webp_total']!=0)
        {
            if($stats['webp_total']>$stats['webp_saved'])
            {
                $saved=$stats['webp_total']-$stats['webp_saved'];
                $webp_data['webp_saved_percent'] = ( $saved / $stats['webp_total'] ) * 100;
                $webp_data['webp_saved_percent'] = round( $webp_data['webp_saved_percent'], 2 );
            }
            else
            {
                $webp_data['webp_saved_percent']=0;
            }
        }
        else
        {
            $webp_data['webp_saved_percent']=0;
        }

        $webp_data['avif_converted']=$stats['avif_converted']+$stats['avif_compressed'];
        $webp_data['avif_total']=$stats['avif_total'];
        $webp_data['avif_saved']=$stats['avif_saved'];
        if($stats['avif_total']!=0)
        {
            if($stats['avif_total']>$stats['avif_saved'])
            {
                $saved=$stats['avif_total']-$stats['avif_saved'];

                $webp_data['avif_saved_percent'] = ($saved / $stats['avif_total'] ) * 100;
                $webp_data['avif_saved_percent'] = round( $webp_data['avif_saved_percent'], 2 );
            }
            else
            {
                $webp_data['avif_saved_percent'] = 0;
            }

            $webp_data['avif_converted_percent'] = ( $webp_data['avif_converted'] / $avif_images_count ) * 100;
            $webp_data['avif_converted_percent'] = round( $webp_data['avif_converted_percent'], 2 );
        }
        else
        {
            $webp_data['avif_converted_percent']=0;
            $webp_data['avif_saved_percent']=0;
        }

        return $webp_data;
    }

    private function get_max_webp_image_count()
    {
        global $wpdb;

        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",);

        //$supported_mime_types=apply_filters('compressx_supported_mime_types',$supported_mime_types);

        $result=$wpdb->get_results( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s) ",$supported_mime_types),ARRAY_N);
        if($result && sizeof($result)>0)
        {
            return $result[0][0];
        }
        else
        {
            return 0;
        }
    }

    public function get_max_avif_image_count()
    {
        global $wpdb;

        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif");

        //$supported_mime_types=apply_filters('compressx_supported_mime_types',$supported_mime_types);

        $result=$wpdb->get_results($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s,%s) ",$supported_mime_types),ARRAY_N);
        if($result && sizeof($result)>0)
        {
            return $result[0][0];
        }
        else
        {
            return 0;
        }
    }
}
