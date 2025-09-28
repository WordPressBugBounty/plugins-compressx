<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_ImgOptim_Task
{
    public $task;
    public $log=false;

    public function __construct()
    {
        $this->get_task();
    }

    public function update_task()
    {
        CompressX_Options::update_option('compressx_image_opt_task',$this->task);
        $this->get_task();
    }

    public function get_task()
    {
        $this->task=CompressX_Options::get_option('compressx_image_opt_task',array());
    }

    public function init_task($force=false)
    {
        $this->task=array();

        $this->task['log']=uniqid('cx-');
        $this->log=new CompressX_Log();
        $this->log->OpenLogFile();

        $this->init_options();

        $this->task['options']['force']=$force;
        $this->task['offset']=0;

        if(!$this->get_need_optimize_images())
        {
            $ret['result']='failed';
            $ret['error']=__('No unoptimized images found.','compressx');
            $this->update_task();
            return $ret;
        }

        $this->task['status']='init';
        $this->task['last_update_time']=time();
        $this->task['retry']=0;
        $this->task['total_retry']=0;

        //$this->task['total_images']=CompressX_Options::get_option("compressx_need_optimized_images",0);
        $this->task['total_images']=CompressX_Image_Scanner::get_need_optimize_images_count($force);
        $this->task['optimized_images']=0;
        $this->task['skipped_images']=0;
        $this->task['opt_images']=0;
        $this->task['failed_images']=0;

        $this->task['current_image']=0;
        $this->task['current_file']='';

        $this->task['error']='';
        $this->task['update_error_list']=false;
        $this->update_task();

        $ret['result']='success';
        $ret["test"]=$this->task;
        return $ret;
    }

    public function init_options()
    {
        $general_options=CompressX_Options::get_general_settings();
        $quality_options=CompressX_Options::get_quality_option();
        $this->task['options']=array_merge($general_options,$quality_options);

        $this->task['options']['exclude']=CompressX_Options::get_excludes();
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
        $ret=$this->init_optimize_images();
        if($ret['finished']&&empty($this->task['images']))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function get_task_status()
    {
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

    public function init_optimize_images()
    {
        $this->task['images']=array();

        $max_count=200;
        $force=$this->task['options']['force'];
        $start_row=$this->task['offset'];
        $image_ids=CompressX_Image_Scanner::get_need_optimize_images_by_cursor($start_row,$max_count,$force);
        if(!empty($image_ids))
        {
            $last_id=$this->task['offset'];
            foreach ($image_ids as $image_id)
            {
                $image['id']=$image_id;
                $image['finished']=0;
                $this->task['images'][$image['id']]=$image;
                $last_id = $image_id;
            }
            $this->task['offset'] = $last_id;
            $this->update_task();
        }

        if (empty($image_ids)||count($image_ids) < $max_count)
        {
            $finished=true;
        }
        else
        {
            $finished=false;
        }
        $ret['result']='success';
        $ret['finished']=$finished;
        return $ret;

        /*
        $max_image_count=CompressX_Image_Method::get_max_image_count();

        $page=100;
        $max_count=5000;

        $force=$this->task['options']['force'];

        $start_row=$this->task['offset'];

        $need_optimized_images=array();

        $convert_to_webp=$this->task['options']['convert_to_webp'];
        $convert_to_avif=$this->task['options']['convert_to_avif'];

        $exclude_regex_folder=CompressX_Options::get_excludes();

        $time_start=time();
        $max_timeout_limit=21;
        $count=0;
        $finished=true;

        for ($current_row=$start_row; $current_row <= $max_image_count; $current_row += $page)
        {
            $images=CompressX_Image_Method::scan_unoptimized_image($page,$current_row,$convert_to_webp,$convert_to_avif,$exclude_regex_folder,$force);
            $need_optimized_images=array_merge($images,$need_optimized_images);

            $count=$count+$page;
            $time_spend=time()-$time_start;

            if(sizeof($need_optimized_images)>=100)
            {
                $current_row+= $page;
                $finished=false;
                break;
            }

            if($time_spend>$max_timeout_limit)
            {
                $current_row+=$page;
                $finished=false;
                break;
            }
            else if($count>$max_count)
            {
                $current_row+=$page;
                $finished=false;
                break;
            }
        }

        if(!empty($need_optimized_images))
        {
            foreach ($need_optimized_images as $image)
            {
                $this->task['images'][$image['id']]=$image;
            }
        }

        $this->task['offset']=$current_row;

        $this->update_task();

        $ret['result']='success';
        $ret['finished']=$finished;
        return $ret;*/
    }

    public function get_need_optimize_image()
    {
        if(!empty($this->task['images']))
        {
            foreach ($this->task['images'] as $image)
            {
                if($image['finished']==0)
                {
                    $ret['result']='success';
                    $ret['finished']=false;
                    $ret['image_id']=$image['id'];
                    return $ret;
                }
            }
        }

        $this->task['images']=array();
        $ret=$this->init_optimize_images();

        if($ret['finished']&&empty($this->task['images']))
        {
            $ret['result']='success';
            $ret['finished']=true;
            $ret['image_id']=false;
            return $ret;
        }
        else
        {
            if(empty($this->task['images']))
            {
                $ret['result']='success';
                $ret['finished']=false;
                $ret['image_id']=false;
                return $ret;
            }
            else
            {
                foreach ($this->task['images'] as $image)
                {
                    if($image['finished']==0)
                    {
                        $ret['result']='success';
                        $ret['finished']=false;
                        $ret['image_id']=$image['id'];
                        return $ret;
                    }
                }

                $ret['result']='success';
                $ret['finished']=false;
                $ret['image_id']=false;
                return $ret;
            }
        }
    }

    public function do_optimize_image()
    {
        if($this->check_timeout())
        {
            $this->WriteLog('Optimizing image failed. Error:task timeout','error');

            $this->task['status']='error';
            $this->task['error']='task timeout';
            $this->task['last_update_time']=time();
            $this->update_task();

            $ret['result']='failed';
            $ret['error']='task timeout';
            return $ret;
        }

        $this->task['status']='running';
        $this->task['last_update_time']=time();
        $this->update_task();

        $converter_images_pre_request=$this->task['options']['converter_images_pre_request'];

        $time_start=time();
        $max_timeout_limit=90;
        for ($i=0;$i<$converter_images_pre_request;$i++)
        {
            $ret=$this->get_need_optimize_image();
            if($ret['finished']&&$ret['image_id']===false)
            {
                $ret['result']='success';

                $this->task['status']='finished';
                $this->task['last_update_time']=time();
                delete_transient('compressx_set_global_stats');
                $this->update_task();

                do_action('compressx_purge_cache');

                return $ret;
            }
            else if($ret['image_id']===false)
            {
                break;
            }
            else
            {
                $image_id=$ret['image_id'];
            }

            $this->task['status']='running';
            $this->task['last_update_time']=time();
            $this->update_task();

            if(CompressX_Image_Method::exclude_path($image_id,$this->task['options']['exclude']))
            {
                $this->WriteLog('Exclude images: id:'.$image_id,'notice');
                $this->task['images'][$image_id]['finished']=1;
                $this->task['last_update_time']=time();
                $this->task['retry']=0;
                $this->task['total_retry']=0;
                $this->task['skipped_images']++;
                $this->update_task();
            }
            else
            {
                if($this->task['options']['force'])
                {
                    CompressX_Image_Meta::generate_images_meta($image_id,$this->task['options']);
                }

                $this->WriteLog('Start optimizing images: id:'.$image_id,'notice');

                $ret=$this->optimize_image($image_id);

                if($ret['result']=='success')
                {
                    $this->WriteLog('Optimizing image id:'.$image_id.' succeeded.','notice');

                    $this->task['images'][$image_id]['finished']=1;
                    $this->task['last_update_time']=time();
                    $this->task['retry']=0;
                    $this->task['total_retry']=0;
                    $this->task['optimized_images']++;
                    $this->update_task();

                    do_action('compressx_after_optimize_image',$image_id);
                }
                else
                {
                    $this->WriteLog('Optimizing image failed. Error:'.$ret['error'],'error');

                    $this->task['status']='error';
                    $this->task['error']=$ret['error'];
                    $this->task['last_update_time']=time();
                    $this->update_task();
                    return $ret;
                }
            }


            $time_spend=time()-$time_start;
            if($time_spend>$max_timeout_limit)
            {
                break;
            }
        }

        delete_transient('compressx_set_global_stats');

        $time_spend=time()-$time_start;
        $this->WriteLog('End request cost time:'.$time_spend.'.','notice');
        $this->task['status']='completed';
        $this->task['last_update_time']=time();
        $this->update_task();
        $ret['result']='success';
        $ret['test']=$this->task;
        return $ret;
    }

    public function skip_current_image()
    {
        $image_id=$this->task['current_image'];
        if(isset($this->task['images'][$image_id]))
        {
            $this->task['images'][$image_id]['finished']=1;
            $this->task['last_update_time']=time();
            $this->task['failed_images']++;

            CompressX_Image_Meta::update_image_meta_status($image_id,'failed');

            $this->update_task();

            $this->WriteLog('skip current timout image:'.$image_id,'error');
            return true;
        }
        else
        {
            return false;
        }
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

        $this->task['current_image']=$image_id;
        $file_path = get_attached_file( $image_id );
        $abs_root=CompressX_Image_Method::transfer_path(ABSPATH);
        $attachment_dir=CompressX_Image_Method::transfer_path($file_path);
        $this->task['current_file']=str_replace($abs_root,'',$attachment_dir);
        $this->update_task();

        $image=new Compressx_Image($image_id,$this->task['options']);

        if(empty($file_path))
        {
            $this->WriteLog('Image:'.$image_id.' failed. Error: failed to get get_attached_file','notice');

            $this->task['failed_images']++;
            $this->update_task();

            $error='Image:'.$image_id.' failed. Error: failed to get get_attached_file';
            CompressX_Image_Meta::update_image_failed($image_id,$error);

            $ret['result']='success';
            return $ret;
        }

        if(!file_exists($file_path))
        {
            $this->WriteLog('Image:'.$image_id.' failed. Error: file not exists '.$file_path,'notice');

            $this->task['failed_images']++;
            $this->update_task();

            $error='Image:'.$image_id.' failed. Error: file not exists '.$file_path;
            CompressX_Image_Meta::update_image_failed($image_id,$error);

            $ret['result']='success';
            return $ret;
        }

        if(!$this->check_file_mime_content_type($file_path))
        {
            $this->WriteLog('Image:'.$image_id.' failed. Error: mime content type not support','notice');

            $error='Image:'.$image_id.' failed. Error: mime content type not support';

            $this->task['failed_images']++;
            $this->update_task();
            CompressX_Image_Meta::update_image_failed($image_id,$error);

            $ret['result']='success';
            return $ret;
        }

        CompressX_Image_Meta::update_image_progressing($image_id);

        $image->resize();

        if($image->convert())
        {
            $this->task['opt_images']++;
            $this->update_task();
            CompressX_Image_Meta::update_image_meta_status($image_id,'optimized');
        }
        else
        {
            $this->task['failed_images']++;
            $this->update_task();
            CompressX_Image_Meta::update_image_meta_status($image_id,'failed');
        }
        CompressX_Image_Meta::delete_image_progressing($image_id);

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

    public function get_task_progress()
    {
        $this->get_task();

        if(empty($this->task))
        {
            $ret['result']='failed';
            $ret['error']=__('Finish optimizing images.','compressx');
            $ret['percent']=0;
            $ret['timeout']=0;
            $ret['log']=__('All image(s) optimized successfully.','compressx');
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

        if($ret['total_images']>0)
        {
            $percent= intval(($ret['optimized_images']/$ret['total_images'])*100);
        }
        else
        {
            $percent=0;
        }

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

                $dismiss=CompressX_Options::get_option('compressx_rating_dismiss',false);
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
                    $size=CompressX_Image_Method::get_opt_folder_size();
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
                    if(time()-$this->task['last_update_time']>120)
                    {
                        $this->task['last_update_time']=time();
                        $this->task['retry']++;
                        $this->task['total_retry']++;
                        $this->task['status']='timeout';
                        $this->update_task();

                        $ret['timeout']=1;

                        $ret['result']='failed';
                        $ret['error']='Task timeout';
                        $ret['percent']=0;
                        $ret['continue']=0;
                        $ret['finished']=0;
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

        $ret['test']=$this->task;
        return $ret;
    }

    public function check_timeout()
    {
        if(isset($this->task['status']))
        {
            if($this->task['status']=='timeout')
            {
                if($this->task['retry']>3&&$this->task['total_retry']<10)
                {
                    if($this->skip_current_image())
                    {
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
                else if($this->task['total_retry']>10)
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
        else
        {
            return false;
        }
    }

}