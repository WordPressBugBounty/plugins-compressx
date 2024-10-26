<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_ImgOptim_Task
{
    public $task;
    public $log=false;

    public function __construct()
    {
        $this->task=get_option('compressx_image_opt_task',array());
    }

    public function init_task($force=false)
    {
        $this->task=array();

        $this->task['options']['force']=$force;

        $this->task['log']=uniqid('cx-');
        $this->log=new CompressX_Log();
        $this->log->OpenLogFile();

        $this->init_options();

        /*
        $this->task['images']=$this->get_need_optimize_images();

        if(empty($this->task['images']))
        {
            $ret['result']='failed';
            $ret['error']='No unoptimized images found.';
            update_option('compressx_image_opt_task',$this->task);
            return $ret;
        }
        */
        $this->task['offset']=0;

        $this->get_need_optimize_images();
        if(empty($this->task['images']))
        {
            $ret['result']='failed';
            $ret['error']=__('No unoptimized images found.','compressx');
            update_option('compressx_image_opt_task',$this->task,false);
            return $ret;
        }

        $this->task['status']='init';
        $this->task['last_update_time']=time();
        $this->task['retry']=0;

        $this->task['total_images']=get_option("compressx_need_optimized_images",0);
        $this->task['optimized_images']=0;
        $this->task['opt_images']=0;
        $this->task['failed_images']=0;

        $this->task['current_image']=0;
        $this->task['current_file']='';

        $this->task['error']='';
        $this->task['error_list']=array();
        $this->task['update_error_list']=false;
        update_option('compressx_image_opt_task',$this->task,false);
        delete_transient('compressx_set_global_stats');

        $ret['result']='success';
        $ret["test"]=$this->task;
        return $ret;
    }

    public function init_options()
    {
        $options=get_option('compressx_general_settings',array());

        $this->task['options']['remove_exif']=isset($options['remove_exif'])?$options['remove_exif']:false;
        $this->task['options']['auto_remove_larger_format']=isset($options['auto_remove_larger_format'])?$options['auto_remove_larger_format']:true;
        //
        $this->task['options']['converter_images_pre_request']=isset($options['converter_images_pre_request'])?$options['converter_images_pre_request']:5;

        $output_format_webp=get_option('compressx_output_format_webp',1);
        $output_format_avif=get_option('compressx_output_format_avif',1);

        $convert_to_webp=$output_format_webp;
        $convert_to_avif=$output_format_avif;
        $compressed_webp=$output_format_webp;
        $compressed_avif=$output_format_avif;

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

        $this->task['options']['converter_method']=$converter_method;

        if($converter_method=='gd')
        {
            if($convert_to_webp&&CompressX_Image_Opt_Method::is_support_gd_webp())
            {
                $convert_to_webp=true;
            }
            else
            {
                $convert_to_webp=false;
                $compressed_webp=false;
            }

            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_gd_avif())
            {
                $convert_to_avif=true;
            }
            else
            {
                $convert_to_avif=false;
                $compressed_avif=false;
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
                $compressed_webp=false;
            }

            if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_imagick_avif())
            {
                $convert_to_avif=true;
            }
            else
            {
                $convert_to_avif=false;
                $compressed_avif=false;
            }
        }

        $this->task['options']['convert_to_webp']=$convert_to_webp;
        $this->task['options']['convert_to_avif']=$convert_to_avif;

        $this->task['options']['compressed_webp']=$compressed_webp;
        $this->task['options']['compressed_avif']=$compressed_avif;

        $quality_options=get_option('compressx_quality',array());

        $this->task['options']['quality']=isset($quality_options['quality'])?$quality_options['quality']:'lossy';
        if($this->task['options']['quality']=="custom")
        {
            $this->task['options']['quality_webp']=isset($quality_options['quality_webp'])?$quality_options['quality_webp']: 80;
            $this->task['options']['quality_avif']=isset($quality_options['quality_avif'])?$quality_options['quality_avif']: 60;
        }

        if(isset($options['resize']))
        {
            $this->task['options']['resize_enable']= isset($options['resize']['enable'])?$options['resize']['enable']:true;
            $this->task['options']['resize_width']=isset( $options['resize']['width'])? $options['resize']['width']:2560;
            $this->task['options']['resize_height']=isset( $options['resize']['height'])? $options['resize']['height']:2560;
        }
        else
        {
            $this->task['options']['resize_enable']= true;
            $this->task['options']['resize_width']=2560;
            $this->task['options']['resize_height']=2560;
        }

        $this->task['options']['skip_size']=isset($options['skip_size'])?$options['skip_size']:array();

        $this->task['options']['exclude_png']=isset($options['exclude_png'])?$options['exclude_png']:false;
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

    public function get_need_optimize_images()
    {
        $this->init_optimize_images();
    }

    public function get_task_status()
    {
        $this->task=get_option('compressx_image_opt_task',array());

        if(empty($this->task))
        {
            $ret['result']='failed';
            $ret['error']=__('All image(s) optimized successfully.','compressx');
            return $ret;
        }

        if($this->task['status']=='error')
        {
            $ret['result']='failed';
            $ret['error']=$this->task['error'];
        }
        else if($this->task['status']=='completed')
        {
            $ret['result']='success';
            $ret['status']='completed';
        }
        else if($this->task['status']=='finished')
        {
            $ret['result']='success';
            $ret['status']='finished';
        }
        else if($this->task['status']=='timeout')
        {
            $ret['result']='success';
            $ret['status']='completed';
        }
        else if($this->task['status']=='init')
        {
            $ret['result']='success';
            $ret['status']='completed';
        }
        else
        {
            $ret['result']='success';
            $ret['status']='running';
        }
        return $ret;
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

    public function init_optimize_images()
    {
        $this->task['images']=array();
        $max_image_count=$this->get_max_image_count();

        $page=100;
        $force=$this->task['options']['force'];

        $start_row=$this->task['offset'];

        $need_optimized_images=array();

        $convert_to_webp=$this->task['options']['convert_to_webp'];
        $convert_to_avif=$this->task['options']['convert_to_avif'];

        $excludes=get_option('compressx_media_excludes',array());
        $exclude_regex_folder=array();
        if(!empty($excludes))
        {
            foreach ($excludes as $item)
            {
                $exclude_regex_folder[]='#'.preg_quote(CompressX_Image_Opt_Method::transfer_path($item), '/').'#';
            }
        }

        $this->task['memory_peak_1']=size_format(memory_get_peak_usage(),2);
        for ($current_row=$start_row; $current_row <= $max_image_count; $current_row += $page)
        {
            $images=CompressX_Image_Opt_Method::scan_unoptimized_image($page,$current_row,$convert_to_webp,$convert_to_avif,$exclude_regex_folder,$force);
            $need_optimized_images=array_merge($images,$need_optimized_images);

            if(sizeof($need_optimized_images)>=100)
            {
                $current_row+= $page;
                break;
            }
        }

        foreach ($need_optimized_images as $image)
        {
            $this->task['images'][$image['id']]=$image;
        }

        $this->task['offset']=$current_row;
        $this->task['memory_peak_2']=size_format(memory_get_peak_usage(),2);

        update_option('compressx_image_opt_task',$this->task,false);
    }

    public function get_need_optimize_image()
    {
        foreach ($this->task['images'] as $image)
        {
            if($image['finished']==0)
            {
                return $image['id'];
            }
        }

        $this->task['images']=array();
        $this->init_optimize_images();

        if(empty($this->task['images']))
        {
            return false;
        }
        else
        {
            foreach ($this->task['images'] as $image)
            {
                if($image['finished']==0)
                {
                    return $image['id'];
                }
            }

            return false;
        }
    }

    public function do_optimize_image()
    {
        $this->task=get_option('compressx_image_opt_task',array());
        $this->task['status']='running';
        $this->task['last_update_time']=time();
        update_option('compressx_image_opt_task',$this->task,false);

        /*
        if(empty($this->task)||!isset($this->task['images']))
        {
            $ret['result']='success';
            return $ret;
        }

        $image_id=false;
        $need_reset=false;
        foreach ($this->task['images'] as $image)
        {
            if($image['finished']==0)
            {
                $image_id=$image['id'];

                if($this->task['options']['force']&&$image['force']==0)
                {
                    $need_reset=true;
                }
                break;
            }
        }
        */

        $converter_images_pre_request=$this->task['options']['converter_images_pre_request'];

        $time_start=time();
        $max_timeout_limit=90;
        for ($i=0;$i<$converter_images_pre_request;$i++)
        {
            $image_id=$this->get_need_optimize_image();

            if($image_id===false)
            {
                $ret['result']='success';

                $this->task['status']='finished';
                $this->task['last_update_time']=time();
                delete_transient('compressx_set_global_stats');
                update_option('compressx_image_opt_task',$this->task,false);


                $options=get_option('compressx_general_settings',array());
                if(isset($options['cf_cdn']['auto_purge_cache'])&&$options['cf_cdn']['auto_purge_cache'])
                {
                    include_once COMPRESSX_DIR . '/includes/class-compressx-cloudflare-cdn.php';
                    $setting=$options['cf_cdn'];
                    $cdn=new CompressX_CloudFlare_CDN($setting);
                    $ret=$cdn->purge_cache();
                    if($ret['result']=='failed')
                    {
                        $this->WriteLog('purge_cache:'.$ret['error'],'notice');
                    }
                }


                return $ret;
            }

            $this->task['status']='running';
            $this->task['last_update_time']=time();
            update_option('compressx_image_opt_task',$this->task,false);

            if($this->task['options']['force'])
            {
                $this->reset_optimize_image($image_id);
            }

            $this->WriteLog('Start optimizing images: id:'.$image_id,'notice');

            $ret=$this->optimize_image($image_id);

            if($ret['result']=='success')
            {
                $this->WriteLog('Optimizing image id:'.$image_id.' succeeded.','notice');

                $this->task=get_option('compressx_image_opt_task',array());
                $this->task['images'][$image_id]['finished']=1;
                //$this->task['status']='completed';
                $this->task['last_update_time']=time();
                $this->task['retry']=0;
                $this->task['optimized_images']++;
                update_option('compressx_image_opt_task',$this->task,false);
            }
            else
            {
                $this->WriteLog('Optimizing image failed. Error:'.$ret['error'],'error');

                $this->task=get_option('compressx_image_opt_task',array());
                $this->task['status']='error';
                $this->task['error']=$ret['error'];
                $this->task['last_update_time']=time();
                update_option('compressx_image_opt_task',$this->task,false);
                return $ret;
            }

            $time_spend=time()-$time_start;
            if($time_spend>$max_timeout_limit)
            {
                break;
            }
        }

        delete_transient('compressx_set_global_stats');

        $this->task=get_option('compressx_image_opt_task',array());
        $time_spend=time()-$time_start;
        $this->WriteLog('End request cost time:'.$time_spend.'.','notice');
        $this->task['status']='completed';
        $this->task['last_update_time']=time();
        update_option('compressx_image_opt_task',$this->task,false);
        $ret['result']='success';
        $ret['test']=$this->task;
        return $ret;
    }

    public function get_file_path($path)
    {
        $root_path = WP_CONTENT_DIR;
        $root_path = str_replace('\\','/',$root_path);
        $root_path = $root_path.'/';

        $root_path=$this->transfer_path($root_path);
        $path=str_replace($root_path,'',$this->transfer_path($path));

        return $path;
    }

    public function reset_optimize_image($image_id)
    {
        $this->reset_images_meta($image_id);
        //$this->restore_image($image_id);
        $ret['result']='success';
        return $ret;
    }

    public function reset_images_meta($image_id)
    {
        CompressX_Image_Meta::generate_images_meta($image_id,$this->task['options']);
    }

    public function optimize_image($image_id)
    {
        if (is_a($this->log, 'CompressX_Log'))
        {
            //
        }
        else
        {
            $this->log=new CompressX_Log();
            $this->log->OpenLogFile();
        }

        $image_optimize_meta=$this->get_image_meta($image_id);
        CompressX_Image_Meta::update_image_progressing($image_id);

        $file_path = get_attached_file( $image_id );

        $this->task['current_image']=$image_id;

        $abs_root=CompressX_Image_Opt_Method::transfer_path(ABSPATH);
        $attachment_dir=CompressX_Image_Opt_Method::transfer_path($file_path);
        $this->task['current_file']=str_replace($abs_root,'',$attachment_dir);

        update_option('compressx_image_opt_task',$this->task,false);

        if(empty($file_path))
        {
            CompressX_Image_Opt_Method::WriteLog($this->log,'Image:'.$image_id.' failed. Error: failed to get get_attached_file','notice');

            $image_optimize_meta['size']['og']['status']='failed';
            $image_optimize_meta['size']['og']['error']='Image:'.$image_id.' failed. Error: failed to get get_attached_file';

            $this->task=get_option('compressx_image_opt_task',array());
            $this->task['failed_images']++;
            update_option('compressx_image_opt_task',$this->task,false);
            CompressX_Image_Meta::update_image_meta_status($image_id,'failed');

            $ret['result']='success';
            return $ret;
        }

        if($image_optimize_meta['resize_status']==0)
        {
            if(CompressX_Image_Opt_Method::resize($image_id,$this->task['options'],$this->log))
            {
                $image_optimize_meta['resize_status']=1;
                CompressX_Image_Meta::update_images_meta($image_id,$image_optimize_meta);
            }
            else
            {
                $file_path = get_attached_file( $image_id );

                $error['filename']=$this->get_file_path($file_path);
                $error['time']=time();
                $error['error_info']='resize failed';
                $error['level']='warning';
                $this->task['error_list'][]=$error;
                $this->task['update_error_list']=true;
                update_option('compressx_image_opt_task',$this->task,false);
            }
        }

        $has_error=false;

        if(CompressX_Image_Opt_Method::compress_image($image_id,$this->task['options'],$this->log)===false)
        {
            $has_error=true;
        }

        if(CompressX_Image_Opt_Method::convert_to_webp($image_id,$this->task['options'],$this->log)===false)
        {
            $has_error=true;
        }

        if(!$this->is_exclude_png($image_id))
        {
            if(CompressX_Image_Opt_Method::convert_to_avif($image_id,$this->task['options'],$this->log)===false)
            {
                $has_error=true;
            }
        }


        CompressX_Image_Meta::delete_image_progressing($image_id);
        if($has_error)
        {
            $this->task=get_option('compressx_image_opt_task',array());
            $this->task['failed_images']++;
            update_option('compressx_image_opt_task',$this->task,false);
            CompressX_Image_Meta::update_image_meta_status($image_id,'failed');
        }
        else
        {
            $this->task=get_option('compressx_image_opt_task',array());
            $this->task['opt_images']++;
            update_option('compressx_image_opt_task',$this->task,false);
            CompressX_Image_Meta::update_image_meta_status($image_id,'optimized');
        }


        $ret['result']='success';
        return $ret;
    }

    public function is_exclude_png($image_id)
    {
        if($this->task['options']['exclude_png'])
        {
            $file_path = get_attached_file( $image_id );

            $type=pathinfo($file_path, PATHINFO_EXTENSION);
            if ($type== 'png')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function get_image_meta($image_id)
    {
        $image_optimize_meta =CompressX_Image_Meta::get_image_meta($image_id);
        if(empty($image_optimize_meta))
        {
            $image_optimize_meta =CompressX_Image_Meta::generate_images_meta($image_id,$this->task['options']);
        }

        return $image_optimize_meta;
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public function get_task_progress()
    {
        $this->task=get_option('compressx_image_opt_task',array());

        if(empty($this->task))
        {
            $ret['result']='failed';
            $ret['error']=__('Finish optimizing images.','compressx');
            $ret['percent']=0;
            $ret['timeout']=0;
            $ret['log']=__('All image(s) optimized successfully.','compressx');
            $ret['error_list']=$this->task['error_list'];
            $this->task['update_error_list']=false;
            update_option('compressx_image_opt_task',$this->task,false);
            return $ret;
        }

        if(isset($this->task['total_images']))
        {
            $ret['total_images']=$this->task['total_images'];
        }
        else
        {
            $ret['total_images']=0;
        }

        $ret['optimized_images']=0;
        if(isset($this->task['optimized_images']))
        {
            $ret['optimized_images']=$this->task['optimized_images'];
        }

        $percent= intval(($ret['optimized_images']/$ret['total_images'])*100);

        $ret['log']=sprintf(
        /* translators: %1$d: total images, %2$d: processed images, %3$d: total images, %4$d: Processed percent */
            __('%1$d images found | Processed:%2$d/%3$d (%4$d%%)' ,'compressx'),
            $ret['total_images'],$this->task['optimized_images'],$ret['total_images'],$percent);

        $sub_total=sizeof($this->task['images']);
        $sub_optimized_images=0;
        foreach ($this->task['images'] as $image)
        {
            if($image['finished']==1)
            {
                $sub_optimized_images++;
            }
        }

        if($sub_total>0)
        {
            $sub_percent= intval(($sub_optimized_images/$sub_total)*100);
        }
        else
        {
            $sub_percent=0;
        }

        if(!empty($this->task['current_file']))
        {
            $ret['sub_log']=sprintf(
            /* translators: %1$d: $sub task processed images, %2$d: $sub task total images*/
                __('Current Subtask: %1$d/%2$d | Processing:%3$s' ,'compressx'),
                $sub_optimized_images,$sub_total,$this->task['current_file']);
        }
        else
        {
            $ret['sub_log']=sprintf(
            /* translators: %1$d: $sub task processed images, %2$d: $sub task total images*/
                __('Current Subtask: %1$d/%2$d ' ,'compressx'),
                $sub_optimized_images,$sub_total);
        }

        $ret['percent']= $sub_percent;
        //$ret['sub_log']='Current Subtask: '.$sub_optimized_images.'/'.$sub_total;


        if(isset($this->task['status']))
        {
            if($this->task['status']=='error')
            {
                $ret['result']='failed';
                $ret['error']=$this->task['error'];
                $ret['timeout']=0;
                $ret['log']=$this->task['error'];
            }
            else if($this->task['status']=='finished')
            {
                $ret['result']='success';
                $ret['continue']=0;
                $ret['finished']=1;
                $ret['timeout']=0;
                $ret['percent']= 100;

                $ret['message']=sprintf(
                /* translators: %1$d: total images, %2$d: Succeeded images, %3$d: failed images*/
                    __('Total optimized images:%1$d Succeeded:%2$d Failed:%3$d' ,'compressx'),
                    $ret['total_images'],$this->task['opt_images'],$this->task['failed_images']);

                //$ret['message']='Total optimized images:'.$ret['total_images'].' Succeeded:'.$this->task['opt_images'].' Failed:'.$this->task['failed_images'];

                $dismiss=get_option('compressx_rating_dismiss',false);
                if($dismiss===false)
                {
                    $ret['show_review']=1;
                }
                else if($dismiss==0)
                {
                    $ret['show_review']=0;
                }
                else if($dismiss<time())
                {
                    $ret['show_review']=1;
                }
                else
                {
                    $ret['show_review']=0;
                }

                if($ret['show_review']==1)
                {
                    delete_transient('compressx_set_global_stats');
                    $size=$this->get_opt_folder_size();
                    $ret['opt_size']=size_format($size,2);
                }
            }
            else if($this->task['status']=='completed')
            {
                $ret['result']='success';
                $ret['continue']=0;
                $ret['finished']=0;
                $ret['timeout']=0;
                $ret['message']=sprintf(
                /* translators: %1$d: total images, %2$d: Succeeded images, %3$d: failed images*/
                    __('Total optimized images:%1$d Succeeded:%2$d Failed:%3$d' ,'compressx'),
                    $ret['total_images'],$this->task['opt_images'],$this->task['failed_images']);
            }
            else
            {
                if(isset($this->task['last_update_time']))
                {
                    if(time()-$this->task['last_update_time']>180)
                    {
                        $this->task['last_update_time']=time();
                        $this->task['retry']++;
                        $this->task['status']='timeout';
                        update_option('compressx_image_opt_task',$this->task,false);
                        if($this->task['retry']<3)
                        {
                            $ret['timeout']=1;
                        }
                        else
                        {
                            $ret['timeout']=0;
                            update_option('compressx_image_opt_task',array(),false);
                        }

                        $ret['result']='failed';
                        $ret['error']='Task timed out';
                        $ret['percent']=0;
                        $ret['retry']=$this->task['retry'];
                        $ret['log']='task time out';
                    }
                    else
                    {
                        $ret['continue']=1;
                        $ret['finished']=0;
                        $ret['timeout']=0;
                        $ret['running_time']=time()-$this->task['last_update_time'];
                        $ret['result']='success';
                    }
                }
                else
                {
                    $ret['result']='failed';
                    $ret['error']='not start task';
                    $ret['timeout']=0;
                    $ret['percent']=0;
                    $ret['log']='not start task';
                }
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='not start task';
            $ret['timeout']=0;
            $ret['percent']=0;
            $ret['log']='not start task';
        }

        if(isset($this->task['error_list']))
            $ret['error_list']=$this->task['error_list'];
        else
            $ret['error_list']=array();

        if(!empty($ret['error_list']))
        {
            $this->task['update_error_list']=false;
            update_option('compressx_image_opt_task',$this->task,false);
        }

        $ret['test']=$this->task;
        return $ret;
    }

    public function get_opt_folder_size()
    {
        try {
            $compressx_path=WP_CONTENT_DIR."/compressx-nextgen/uploads";
            $bytestotal = 0;
            $path = realpath($compressx_path);
            if($path!==false && $path!='' && file_exists($path)){
                foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
                    $bytestotal += $object->getSize();
                }
            }
            return $bytestotal;
        }
        catch (Exception $e)
        {
            return 0;
        }
    }
}