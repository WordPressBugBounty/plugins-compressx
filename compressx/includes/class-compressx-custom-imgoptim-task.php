<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Custom_ImgOptim_Task
{
    public $task;
    public $log=false;

    public function __construct()
    {
        $this->task=get_option('compressx_custom_image_opt_task',array());
        CompressX_Custom_Image_Meta::check_custom_table();
    }

    public function init_task($force=false)
    {
        $this->task=array();

        $this->task['options']['force']=$force;

        $this->task['log']=uniqid('cx-');
        $this->log=new CompressX_Log();
        $this->log->CreateLogFile();

        $this->init_options();

        $this->task['images']=$this->get_need_optimize_images();

        if(empty($this->task['images']))
        {
            $ret['result']='failed';
            $ret['error']=__('No unoptimized images found.','compressx');
            update_option('compressx_custom_image_opt_task',$this->task,false);
            return $ret;
        }

        $this->task['status']='init';
        $this->task['last_update_time']=time();
        $this->task['retry']=0;

        $this->task['opt_images']=0;
        $this->task['failed_images']=0;

        $this->task['error']='';
        $this->task['error_list']=array();
        $this->task['update_error_list']=false;
        update_option('compressx_custom_image_opt_task',$this->task,false);

        $ret['result']='success';
        $ret["test"]=$this->task;
        return $ret;
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

    public function init_options()
    {
        $options=get_option('compressx_general_settings',array());

        $this->task['options']['remove_exif']=isset($options['remove_exif'])?$options['remove_exif']:false;
        $this->task['options']['auto_remove_larger_format']=isset($options['auto_remove_larger_format'])?$options['auto_remove_larger_format']:true;

        $quality_options=get_option('compressx_quality',array());

        $this->task['options']['quality']=isset($quality_options['quality'])?$quality_options['quality']:'lossy';
        if($this->task['options']['quality']=="custom")
        {
            $this->task['options']['quality_webp']=isset($quality_options['quality_webp'])?$quality_options['quality_webp']: 80;
            $this->task['options']['quality_avif']=isset($quality_options['quality_avif'])?$quality_options['quality_avif']: 60;
        }

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

        $output_format_webp=get_option('compressx_output_format_webp',1);
        $output_format_avif=get_option('compressx_output_format_avif',1);

        $convert_to_webp=$output_format_webp;
        $convert_to_avif=$output_format_avif;

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

        $this->task['options']['convert_to_webp']=$convert_to_webp;
        $this->task['options']['convert_to_avif']=$convert_to_avif;

    }

    public function get_need_optimize_images()
    {
        $images=get_option("compressx_need_optimized_custom_images",array());
        $need_optimize_images=array();
        $index=0;
        foreach ($images as $path)
        {
            $image['id']=$index;
            $image['finished']=1;
            $image['path']=$path;
            $image['force']=0;
            $status=CompressX_Custom_Image_Meta::get_image_status($path);

            if($this->task['options']['convert_to_webp'])
            {
                $image['convert_webp_status']=$status['convert_webp_status'];
                $image['finished']=0;
            }

            if($this->task['options']['convert_to_avif'])
            {
                $image['convert_avif_status']=$status['convert_avif_status'];
                $image['finished']=0;
            }

            if($image['finished']==0)
            {
                $need_optimize_images[$index]=$image;
                $index++;
            }
        }
        return $need_optimize_images;
    }

    public function get_task_status()
    {
        $this->task=get_option('compressx_custom_image_opt_task',array());

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

    public function do_optimize_image()
    {
        $this->task=get_option('compressx_custom_image_opt_task',array());
        $this->task['status']='running';
        $this->task['last_update_time']=time();
        update_option('compressx_custom_image_opt_task',$this->task,false);

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

        if($image_id===false)
        {
            $ret['result']='success';

            $this->task['status']='finished';
            $this->task['last_update_time']=time();
            update_option('compressx_custom_image_opt_task',$this->task,false);

            return $ret;
        }

        $this->task['status']='running';
        $this->task['last_update_time']=time();
        update_option('compressx_custom_image_opt_task',$this->task,false);

        if($need_reset)
        {
            $ret=$this->reset_optimize_image($image_id);
            if($ret['result']=='success')
            {
                $this->task['images'][$image_id]['force']=1;
                update_option('compressx_image_opt_task',$this->task,false);
            }
        }

        $this->WriteLog('Start optimizing custom images id:'.$image_id,'notice');
        $ret=$this->optimize_image($image_id);

        if($ret['result']=='success')
        {
            $this->WriteLog('Optimizing custom image id:'.$image_id.' succeeded.','notice');
            $this->task['images'][$image_id]['finished']=1;
            $this->task['status']='completed';
            $this->task['last_update_time']=time();
            $this->task['retry']=0;
            update_option('compressx_custom_image_opt_task',$this->task,false);

            $ret['result']='success';
            $ret['test']=$this->task;
            return $ret;
        }
        else
        {
            $this->WriteLog('Optimizing images failed. Error:'.$ret['error'],'error');

            $this->task['status']='error';
            $this->task['error']=$ret['error'];
            $this->task['last_update_time']=time();
            update_option('compressx_custom_image_opt_task',$this->task,false);
            return $ret;
        }
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
        $filename=$this->get_image_path($image_id);
        CompressX_Custom_Image_Meta::generate_custom_image_meta($filename,$this->task['options']);

        $ret['result']='success';
        return $ret;
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

        $has_error=false;
        $filename=$this->get_image_path($image_id);
        $this->get_custom_image_meta($filename);

        if(CompressX_Image_Opt_Method::custom_convert_to_webp($filename,$this->task['options'],$this->log)===false)
        {
            $has_error=true;
        }

        if(CompressX_Image_Opt_Method::custom_convert_to_avif($filename,$this->task['options'],$this->log)===false)
        {
            $has_error=true;
        }

        if($has_error)
        {
            $this->task=get_option('compressx_image_opt_task',array());
            $this->task['failed_images']++;
            update_option('compressx_custom_image_opt_task',$this->task,false);
        }
        else
        {
            $this->task=get_option('compressx_image_opt_task',array());
            $this->task['opt_images']++;
            update_option('compressx_custom_image_opt_task',$this->task,false);
        }


        $ret['result']='success';
        return $ret;
    }

    public function get_output_path($og_path)
    {
        $compressx_path=WP_CONTENT_DIR."/compressx-nextgen";


        $upload_root=$this->transfer_path(WP_CONTENT_DIR.'/');
        $attachment_dir=dirname($og_path);
        $attachment_dir=$this->transfer_path($attachment_dir);
        $sub_dir=str_replace($upload_root,'',$attachment_dir);
        $sub_dir=untrailingslashit($sub_dir);
        $path=$compressx_path.'/'.$sub_dir;

        if(!file_exists($path))
        {
            @mkdir($path,0777,true);
        }

        return $path.DIRECTORY_SEPARATOR.basename($og_path);
    }

    public function get_image_path($image_id)
    {
        $image=$this->task['images'][$image_id];
        return $this->transfer_path($image['path']);
    }

    public function get_custom_image_meta($filename)
    {
        $filename=$this->transfer_path($filename);
        $meta=CompressX_Custom_Image_Meta::get_custom_image_meta($filename);
        if(empty($meta))
        {
            $meta=CompressX_Custom_Image_Meta::generate_custom_image_meta($filename,$this->task['options']);
        }

        return $meta;
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public function get_task_progress()
    {
        $this->task=get_option('compressx_custom_image_opt_task',array());

        if(empty($this->task))
        {
            $ret['result']='failed';
            $ret['error']=__('All image(s) optimized successfully.','compressx');
            $ret['percent']=0;
            $ret['timeout']=0;
            $ret['log']=__('All image(s) optimized successfully.','compressx');
            $ret['error_list']=$this->task['error_list'];
            $this->task['update_error_list']=false;
            update_option('compressx_custom_image_opt_task',$this->task,false);
            return $ret;
        }

        if(isset($this->task['images']))
        {
            $ret['total_images']=sizeof($this->task['images']);
        }
        else
        {
            $ret['total_images']=0;
        }

        $ret['optimized_images']=0;
        if(isset($this->task['images']))
        {
            foreach ($this->task['images'] as $image)
            {
                if($image['finished'])
                {
                    $ret['optimized_images']++;
                }
            }
        }

        $percent= intval(($ret['optimized_images']/$ret['total_images'])*100);

        $ret['log']=sprintf(
        /* translators: %1$d: total images, %2$d: processed images, %3$d: total images, %4$d: Processed percent */
            __('%1$d images found | Processed:%2$d/%3$d (%4$d%%)' ,'compressx'),
        $ret['total_images'],$ret['optimized_images'],$ret['total_images'],$percent);

        if(isset($this->task['status']))
        {
            if($this->task['status']=='error')
            {
                $ret['result']='failed';
                $ret['error']=$this->task['error'];
                $ret['timeout']=0;
                $ret['percent']= intval(($ret['optimized_images']/$ret['total_images'])*100);
                $ret['log']=$this->task['error'];
            }
            else if($this->task['status']=='finished')
            {
                $ret['result']='success';
                $ret['continue']=0;
                $ret['finished']=1;
                $ret['timeout']=0;
                $ret['percent']= 100;
                $ret['log']=__('Finish Optimizing images.','compressx');
            }
            else if($this->task['status']=='completed')
            {
                $ret['result']='success';
                $ret['continue']=0;
                $ret['finished']=0;
                $ret['timeout']=0;
                $ret['percent']= intval(($ret['optimized_images']/$ret['total_images'])*100);
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
                        update_option('compressx_custom_image_opt_task',$this->task,false);
                        if($this->task['retry']<3)
                        {
                            $ret['timeout']=1;
                        }
                        else
                        {
                            $ret['timeout']=0;
                            update_option('compressx_custom_image_opt_task',array(),false);
                        }

                        $ret['result']='failed';
                        $ret['error']=__('Task timed out','compressx');
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
                        $ret['percent']= intval(($ret['optimized_images']/$ret['total_images'])*100);
                    }
                }
                else
                {
                    $ret['result']='failed';
                    $ret['error']='Not start task';
                    $ret['timeout']=0;
                    $ret['percent']=0;
                    $ret['log']='Not start task';
                }
            }
        }
        else
        {
            $ret['result']='failed';
            $ret['error']='Not start task';
            $ret['timeout']=0;
            $ret['percent']=0;
            $ret['log']='Not start task';
        }

        if(isset($this->task['error_list']))
            $ret['error_list']=$this->task['error_list'];
        else
            $ret['error_list']=array();

        if(!empty($ret['error_list']))
        {
            $this->task['update_error_list']=false;
            update_option('compressx_custom_image_opt_task',$this->task,false);
        }
        return $ret;
    }
}