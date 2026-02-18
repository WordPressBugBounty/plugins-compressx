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
