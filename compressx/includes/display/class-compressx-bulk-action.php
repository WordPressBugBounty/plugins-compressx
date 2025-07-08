<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Bulk_Action
{
    public $end_shutdown_function;
    public function __construct()
    {
        add_action('wp_ajax_compressx_start_scan_unoptimized_image', array($this, 'start_scan_unoptimized_image'));
        add_action('wp_ajax_compressx_init_bulk_optimization_task', array($this, 'init_bulk_optimization_task'));
        add_action('wp_ajax_compressx_run_optimize', array($this, 'run_optimize'));
        add_action('wp_ajax_compressx_get_opt_progress', array($this, 'get_opt_progress'));
    }

    public function start_scan_unoptimized_image()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        $force=isset($_POST['force'])?sanitize_key($_POST['force']):'0';
        if($force=='1')
        {
            $force=true;
        }
        else
        {
            $force=false;
        }

        $start_row=isset($_POST['offset'])?sanitize_key($_POST['offset']):0;

        $ret=CompressX_Image_Scanner::scan_unoptimized_images($force,$start_row);
        /*
        $max_image_count=CompressX_Image_Method::get_max_image_count();

        $convert_to_webp=CompressX_Image_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Method::get_convert_to_avif();

        $exclude_regex_folder=CompressX_Options::get_excludes();

        $time_start=time();
        $max_timeout_limit=21;
        $finished=true;
        $page=300;

        $max_count=500;
        if($start_row==0)
        {
            $need_optimize_images=0;
            CompressX_Options::delete_option('compressx_need_optimized_images');
        }
        else
        {
            $need_optimize_images=CompressX_Options::get_option("compressx_need_optimized_images",0);
        }

        $count=0;
        for ($offset=$start_row; $offset <= $max_image_count; $offset += $page)
        {
            $images=CompressX_Image_Method::scan_unoptimized_image($page,$offset,$convert_to_webp,$convert_to_avif,$exclude_regex_folder,$force);

            $count=$count+$page;
            $need_optimize_images=$need_optimize_images+sizeof($images);
            $time_spend=time()-$time_start;
            if($time_spend>$max_timeout_limit)
            {
                $offset+=$page;
                $finished=false;
                break;
            }
            else if($count>$max_count)
            {
                $offset+=$page;
                $finished=false;
                break;
            }
        }

        CompressX_Options::update_option("compressx_need_optimized_images",$need_optimize_images);

        if($finished)
        {
            $log=new CompressX_Log();
            $log->CreateLogFile();
            $log->WriteLog("Scanning images: ".$need_optimize_images." found ","notice");

            if($need_optimize_images==0)
            {
                $ret['result']='failed';
                $ret['error']=__('No unoptimized images found.','compressx');
                echo wp_json_encode($ret);
                die();
            }
        }

        $ret['result']='success';
        $ret['progress']=sprintf(
            __('Scanning images: %1$d found' ,'compressx'),
            $need_optimize_images);
        $ret['finished']=$finished;
        $ret['offset']=$offset;
        $ret['test']=$max_image_count;*/

        echo wp_json_encode($ret);

        die();
    }

    public function init_bulk_optimization_task()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

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
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        set_time_limit(120);
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
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        $task=new CompressX_ImgOptim_Task();

        $result=$task->get_task_progress();

        echo wp_json_encode($result);

        die();
    }
}
