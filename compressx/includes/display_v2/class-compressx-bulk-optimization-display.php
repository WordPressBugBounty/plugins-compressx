<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Bulk_Optimization_Display
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_get_latest_image_data', array($this, 'get_latest_image_data'));

        add_action('wp_ajax_compressx_start_scan_unoptimized_image', array($this, 'start_scan_unoptimized_image'));
        add_action('wp_ajax_compressx_init_bulk_optimization_task', array($this, 'init_bulk_optimization_task'));

        add_action('wp_ajax_compressx_run_optimize', array($this, 'run_optimize'));
        add_action('wp_ajax_compressx_get_opt_progress', array($this, 'get_opt_progress'));
        add_action('wp_ajax_compressx_get_task_log', array($this, 'get_task_log'));
        add_action('wp_ajax_compressx_download_task_log', array($this, 'download_task_log'));
        //
    }

    public function start_scan_unoptimized_image()
    {
        global $compressx;
        $compressx->ajax_check_security();

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

        $ret=CompressX_Image_Scanner::scan_unoptimized_images_v2($force,$start_row);

        echo wp_json_encode($ret);

        die();
    }

    public function init_bulk_optimization_task()
    {
        global $compressx;
        $compressx->ajax_check_security();

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
        $compressx->ajax_check_security();

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
        $compressx->ajax_check_security();

        $task=new CompressX_ImgOptim_Task();

        $result=$task->get_task_progress_ex();

        //  $file_name=$task->get_log_file();
        //        if(empty($file_name))
        //        {
        //            $log=CompressX_Log_Ex::get_instance();
        //            $log->OpenLogFile();
        //        }
        //        else
        //        {
        //            $log=CompressX_Log_Ex::get_instance();
        //            $log->OpenLogFile($file_name);
        //        }

        echo wp_json_encode($result);

        die();
    }

    public function get_task_log()
    {
        global $compressx;
        $compressx->ajax_check_security();

        $task=new CompressX_ImgOptim_Task();

        $offset=isset($_POST['offset'])?sanitize_key($_POST['offset']):0;
        $offset=intval($offset);

        $file_name=$task->get_log_file();
        if(empty($file_name))
        {
            $log=CompressX_Log_Ex::get_instance();
            $log->OpenLogFile();
        }
        else
        {
            $log=CompressX_Log_Ex::get_instance();
            $log->OpenLogFile($file_name);
        }

        $ret=$log->get_log_content($offset);

        echo wp_json_encode($ret);
        die();
    }

    public function download_task_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified in ajax_check_security above
            $task=new CompressX_ImgOptim_Task();
            $file_name=$task->get_log_file();
            if(!empty($file_name))
            {
                $log=CompressX_Log_Ex::get_instance();
                $log->OpenLogFile($file_name);
                $path=$log->log_file;
            }
            else
            {
                die();
            }

            if (!file_exists($path))
            {
                die();
            }

            if (session_id()) {
                session_write_close();
            }

            $size = filesize($path);
            if (!headers_sent()) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $file_name . '"');
                header('Content-Transfer-Encoding: binary');
                header('Connection: Keep-Alive');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . $size);
            }

            ob_end_clean();
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Sending file to browser for download
            readfile($path);
        } catch (Exception $error) {
            $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
            //error_log($message);
        }
        exit;
    }

    public function display()
    {
        ?>
        <div class="compressx-root">
            <div class="compressx-v2-py-6 compressx-v2-w-full compressx-v2-max-w-[1200px] compressx-v2-mx-auto">
                <?php
                $this->output_header();
                $this->output_review();
                $this->output_warning();
                $this->output_bulk();
                $this->output_progress();
                $this->output_comparison();
                $this->output_logs();
                $this->output_footer();
                ?>
            </div>
        </div>
        <?php
    }

    public function output_header()
    {
        ?>
        <div class=" compressx-v2-pr-4 compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-4">
            <div>
                <h1 class="compressx-v2-text-2xl compressx-v2-font-semibold compressx-v2-text-gray-900">
                    <?php echo esc_html(__('Bulk Optimization', 'compressx')); ?>
                </h1>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-2">
                    <?php echo esc_html(__('CompressX will scan and optimize all your images in bulk.', 'compressx')); ?>
                </p>
            </div>
        </div>
        <?php
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    public function output_warning()
    {
        if (!CompressX_Image_Method::is_support_gd() && !CompressX_Image_Method::is_support_imagick())
        {
            ?>
            <div class="compressx-v2-bg-yellow-50 compressx-v2-border-l-4 compressx-v2-border-yellow-400 compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-4">
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <span class="dashicons dashicons-warning compressx-v2-text-yellow-500 compressx-v2-text-xl"></span>
                    <div>
                        <p class="compressx-v2-text-sm compressx-v2-text-yellow-700">
                            <?php esc_html_e('Your server does not have GD or Imagick extension installed, images cannot be converted to WebP or AVIF on the website.Please install GD or Imagick PHP extension and restart the server service to convert images to WebP and AVIF.', 'compressx') ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function output_review()
    {
        do_action("compressx_output_review_v2");
    }

    public function output_bulk()
    {
        ?>
        <section id="cx-v2-bulk-ready" class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-mb-4 compressx-v2-rounded compressx-v2-p-6 compressx-v2-shadow-sm">
            <div class="compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-mb-3">
                <h2 class="compressx-v2-text-lg compressx-v2-font-semibold"><?php esc_html_e('Bulk Optimization', 'compressx') ?></h2>
                <span class="compressx-v2-bg-blue-100 compressx-v2-text-blue-700 compressx-v2-text-xs compressx-v2-font-medium compressx-v2-px-2 compressx-v2-py-1 compressx-v2-rounded"><?php esc_html_e('Free Mode', 'compressx') ?></span>
            </div>

            <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mb-3">
                <?php esc_html_e('CompressX will scan and optimize your images. You can either start right now or schedule it later', 'compressx') ?> (<a href="https://compressx.io/" target="_self" class="compressx-v2-text-blue-600 hover:compressx-v2-underline"><?php esc_html_e('Pro', 'compressx') ?></a>).
            </p>

            <div class="compressx-v2-bg-blue-50 compressx-v2-text-blue-700 compressx-v2-text-xs compressx-v2-px-3 compressx-v2-py-2 compressx-v2-rounded compressx-v2-mb-5">
                <strong><?php esc_html_e('Note:', 'compressx') ?></strong> <a href="https://compressx.io/" target="_self" class="compressx-v2-text-blue-800 hover:compressx-v2-underline compressx-v2-font-medium"><?php esc_html_e('Pro users', 'compressx') ?></a> <?php esc_html_e('can close this page and processing will continue in the background. Free users must keep this page open until completion.', 'compressx') ?>
            </div>

            <div class="compressx-v2-mb-4">
                <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2 compressx-v2-text-sm compressx-v2-text-gray-700 compressx-v2-cursor-pointer">
                    <input type="checkbox" id="cx-v2-force-reprocess" class="compressx-v2-rounded compressx-v2-border-gray-300 compressx-v2-text-blue-600 focus:compressx-v2-ring-blue-500">
                    <span><?php esc_html_e('Force all images to be re-processed', 'compressx') ?></span>
                </label>
            </div>

            <div class="compressx-v2-grid compressx-v2-grid-cols-1 sm:compressx-v2-grid-cols-2 compressx-v2-gap-4">
                <div class="compressx-v2-text-center">
                    <button id="cx-v2-start-now" class="compressx-v2-w-full compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-font-medium compressx-v2-px-5 compressx-v2-py-3 compressx-v2-rounded compressx-v2-flex compressx-v2-items-center compressx-v2-justify-center compressx-v2-gap-2">
                        🚀 <?php esc_html_e('Start Now', 'compressx') ?>
                    </button>
                    <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mt-2">
                        <?php esc_html_e('Run immediately in the foreground (Free) or background', 'compressx') ?> (<a href="https://compressx.io/" target="_self" class="compressx-v2-text-blue-600 hover:compressx-v2-underline"><?php esc_html_e('Pro', 'compressx') ?></a>).
                    </p>
                </div>

                <div class="compressx-v2-text-center">
                    <button id="cx-v2-schedule-later" class="compressx-v2-w-full compressx-v2-bg-gray-100 hover:compressx-v2-bg-gray-200 compressx-v2-text-gray-700 compressx-v2-font-medium compressx-v2-px-5 compressx-v2-py-3 compressx-v2-rounded compressx-v2-flex compressx-v2-items-center compressx-v2-justify-center compressx-v2-gap-2" disabled>
                        ⏰ <?php echo esc_html__('Schedule', 'compressx'); ?>
                    </button>
                    <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mt-2">
                        <?php esc_html_e('Use your configured schedule to process automatically.', 'compressx') ?>
                        (<a href="https://compressx.io/" target="_self" class="compressx-v2-text-blue-600 hover:compressx-v2-underline"><?php esc_html_e('Pro', 'compressx') ?></a>).
                    </p>
                </div>
            </div>
        </section>

        <?php
    }

    public function output_progress()
    {
        ?>
        <section id="cx-v2-bulk-progress" class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-mb-4 compressx-v2-rounded compressx-v2-p-6 compressx-v2-shadow-sm compressx-v2-space-y-6" style="display: none">

            <div class="compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center">
                <h2 class="compressx-v2-text-lg compressx-v2-font-semibold"><?php esc_html_e('Bulk Optimization in Progress', 'compressx') ?></h2>
                <span id="cx-v2-progress-status" class="compressx-v2-text-xs compressx-v2-bg-blue-100 compressx-v2-text-blue-700 compressx-v2-px-2 compressx-v2-py-1 compressx-v2-rounded">
                    <?php echo esc_html__('Ready', 'compressx') ?>
                </span>
            </div>

            <div id="cx_bulk_warning" class="compressx-v2-bg-yellow-50 compressx-v2-border-l-4 compressx-v2-border-yellow-400 compressx-v2-rounded compressx-v2-p-4">
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <span class="dashicons dashicons-warning compressx-v2-text-yellow-500 compressx-v2-text-xl"></span>
                    <div>
                        <p class="compressx-v2-font-medium compressx-v2-text-yellow-800"><?php esc_html_e('Warning:', 'compressx') ?></p>
                        <p class="compressx-v2-text-sm compressx-v2-text-yellow-700">
                            <?php esc_html_e('You must keep this page open until completion.', 'compressx') ?>
                        </p>
                    </div>
                </div>
            </div>

            <div id="cx_bulk_success" style="display: none" class="compressx-v2-bg-green-50 compressx-v2-border-l-4 compressx-v2-border-green-400 compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-4 compressx-v2-relative">

                <!-- Close Button -->
                <button id="cx_bulk_success_hide"
                        class="compressx-v2-absolute compressx-v2-top-2 compressx-v2-right-2 compressx-v2-text-green-600 hover:compressx-v2-text-green-800 compressx-v2-text-sm compressx-v2-font-medium compressx-v2-bg-transparent compressx-v2-border compressx-v2-border-green-300 hover:compressx-v2-bg-green-100 compressx-v2-rounded-md compressx-v2-px-2 compressx-v2-py-0.5 compressx-v2-transition-all">
                    Got it
                </button>

                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <span class="dashicons dashicons-thumbs-up compressx-v2-text-green-500 compressx-v2-text-xl"></span>
                    <div>
                        <p id="cx_bulk_success_title" class="compressx-v2-font-medium compressx-v2-text-green-800"></p>
                        <p id="cx_bulk_success_message" class="compressx-v2-text-sm compressx-v2-text-green-700"></p>
                    </div>
                </div>
            </div>

            <div id="cx_bulk_error" style="display: none" class="compressx-v2-bg-red-50 compressx-v2-border-l-4 compressx-v2-border-red-400 compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-4 compressx-v2-relative">
                <button id="cx_bulk_error_hide"
                        class="compressx-v2-absolute compressx-v2-top-2 compressx-v2-right-2 compressx-v2-text-green-600 hover:compressx-v2-text-green-800 compressx-v2-text-sm compressx-v2-font-medium compressx-v2-bg-transparent compressx-v2-border compressx-v2-border-green-300 hover:compressx-v2-bg-green-100 compressx-v2-rounded-md compressx-v2-px-2 compressx-v2-py-0.5 compressx-v2-transition-all">
                    Got it
                </button>

                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <span class="dashicons dashicons-dismiss compressx-v2-text-red-500 compressx-v2-text-xl"></span>
                    <div>
                        <p id="cx_bulk_error_title" class="compressx-v2-font-medium compressx-v2-text-red-800"></p>
                        <p id="cx_bulk_error_message" class="compressx-v2-text-sm compressx-v2-text-red-700"></p>
                    </div>
                </div>
            </div>


            <div class="compressx-v2-mt-8 compressx-v2-pt-4 compressx-v2-border-t compressx-v2-border-gray-100">
                <div class="compressx-v2-grid compressx-v2-grid-cols-1 lg:compressx-v2-grid-cols-2 compressx-v2-gap-6">
                    <!-- LEFT: Progress -->
                    <div>
                        <!-- SCAN PROGRESS -->
                        <p  id="cx-v2-scan-status" class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-1">
                            0 images scanned
                        </p>
                        <div class="compressx-v2-h-2 compressx-v2-bg-gray-200 compressx-v2-rounded-full compressx-v2-overflow-hidden compressx-v2-mb-3">
                            <div id="cx-v2-scan-progress-bar"
                                 class="compressx-v2-h-2 compressx-v2-bg-blue-500 compressx-v2-transition-all compressx-v2-duration-700"
                                 style="width:0%"></div>
                        </div>

                        <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-1" id="cx-v2-overall-progress-text">
                            <?php echo esc_html("N/A") ?> <?php esc_html_e('images processed', 'compressx') ?>
                        </p>
                        <div class="compressx-v2-h-2 compressx-v2-bg-gray-200 compressx-v2-rounded-full compressx-v2-overflow-hidden compressx-v2-mb-3">
                            <div id="cx-v2-overall-progress-bar" class="compressx-v2-h-2 compressx-v2-bg-blue-500 compressx-v2-transition-all compressx-v2-duration-700" style="width:0%"></div>
                        </div>

                        <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-1">
                            <?php esc_html_e('Current Job', 'compressx') ?> (<span id="cx-v2-job-current"><?php echo esc_html("N/A")?></span>)
                        </p>
                        <div class="compressx-v2-h-2 compressx-v2-bg-gray-200 compressx-v2-rounded-full compressx-v2-overflow-hidden">
                            <div id="cx-v2-job-progress-bar" class="compressx-v2-h-2 compressx-v2-bg-green-500 compressx-v2-transition-all compressx-v2-duration-700" style="width:0%"></div>
                        </div>

                        <div class="compressx-v2-grid compressx-v2-grid-cols-3 compressx-v2-gap-3 compressx-v2-mt-5">
                            <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-p-3 compressx-v2-text-center">
                                <p id="cx-v2-stat-optimized" class="compressx-v2-text-blue-600 compressx-v2-font-bold compressx-v2-text-lg">0</p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Processed', 'compressx') ?></p>
                            </div>
                            <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-p-3 compressx-v2-text-center">
                                <p id="cx-v2-stat-errors" class="compressx-v2-text-red-600 compressx-v2-font-bold compressx-v2-text-lg">0</p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Failed', 'compressx') ?></p>
                            </div>
                            <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-p-3 compressx-v2-text-center">
                                <p id="cx-v2-stat-remaining" class="compressx-v2-text-gray-700 compressx-v2-font-bold compressx-v2-text-lg">0</p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Remaining', 'compressx') ?></p>
                            </div>
                        </div>

                        <div class="compressx-v2-flex compressx-v2-gap-3 compressx-v2-mt-5">
                            <!--button id="cx-v2-pause-bulk" class="compressx-v2-bg-yellow-400 hover:compressx-v2-bg-yellow-500 compressx-v2-text-white compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded"><?php //esc_html_e('Pause', 'compressx') ?></button !-->
                            <button id="cx-v2-cancel-bulk" class="compressx-v2-bg-red-500 hover:compressx-v2-bg-red-600 compressx-v2-text-white compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded"><?php esc_html_e('Cancel', 'compressx') ?></button>
                        </div>
                    </div>

                    <!-- RIGHT: Live Log -->
                    <div>
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-2">
                            <h3 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-gray-700"><?php esc_html_e('Live Log', 'compressx') ?></h3>
                            <div class="compressx-v2-flex compressx-v2-gap-2">
                                <label class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-flex compressx-v2-items-center compressx-v2-gap-1">
                                    <input type="checkbox" id="cx-v2-log-autoscroll" checked class="compressx-v2-rounded-sm"> <?php esc_html_e('Auto-scroll', 'compressx') ?>
                                </label>
                                <button id="cx-v2-log-copy" class="compressx-v2-text-xs compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-px-2 hover:compressx-v2-bg-gray-50"><?php esc_html_e('Copy', 'compressx') ?></button>
                                <button id="cx-v2-log-download" class="compressx-v2-text-xs compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-px-2 hover:compressx-v2-bg-gray-50"><?php esc_html_e('Download', 'compressx') ?></button>
                            </div>
                        </div>

                        <div id="cx-v2-live-log" class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-border compressx-v2-border-gray-200 compressx-v2-font-mono compressx-v2-text-xs compressx-v2-text-gray-700 compressx-v2-p-3 compressx-v2-h-56 compressx-v2-overflow-y-auto compressx-v2-whitespace-pre-line">
                            <?php esc_html_e('[Waiting for optimization to start...]', 'compressx') ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    public function output_comparison()
    {
        ?>
        <section id="cx-v2-image-comparison" class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-mb-4 compressx-v2-rounded compressx-v2-p-6 compressx-v2-shadow-sm">
            <h2 class="compressx-v2-text-lg compressx-v2-font-semibold compressx-v2-mb-4"><?php esc_html_e('Image Comparison', 'compressx') ?></h2>
            <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-4 text-center">
                <?php esc_html_e('This comparison updates automatically for the images being optimized. After completion, the last processed image will remain visible.', 'compressx') ?>
            </p>

            <div class="compressx-v2-bg-gray-50 compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-6">
                <div class="compressx-v2-relative">
                    <div id="cx-v2-bulk-comparison-container" class="compressx-v2-h-[30rem] compressx-v2-bg-white compressx-v2-rounded compressx-v2-flex compressx-v2-items-center compressx-v2-justify-center compressx-v2-border compressx-v2-border-gray-100 compressx-v2-overflow-hidden">
                        <div class="cx-comparison-placeholder">
                            <span class="compressx-v2-text-gray-500 compressx-v2-text-sm"><?php esc_html_e('Comparison will appear here during optimization', 'compressx') ?></span>
                        </div>
                    </div>

                    <div id="cx-v2-bulk-format-toggle" class="compressx-v2-absolute compressx-v2-top-3 compressx-v2-right-3 compressx-v2-flex compressx-v2-gap-2" style="display:none;">
                        <button id="cx-v2-bulk-show-webp" class="compressx-v2-px-3 compressx-v2-py-1 compressx-v2-bg-gray-50 compressx-v2-border compressx-v2-border-gray-300 compressx-v2-text-gray-700 compressx-v2-text-[11px] compressx-v2-font-medium compressx-v2-rounded hover:compressx-v2-bg-gray-100 compressx-v2-shadow-sm">
                            <?php esc_html_e('Original vs WebP', 'compressx') ?>
                        </button>
                        <button id="cx-v2-bulk-show-avif" class="compressx-v2-px-3 compressx-v2-py-1 compressx-v2-bg-gray-50 compressx-v2-border compressx-v2-border-gray-300 compressx-v2-text-gray-700 compressx-v2-text-[11px] compressx-v2-font-medium compressx-v2-rounded hover:compressx-v2-bg-gray-100 compressx-v2-shadow-sm">
                            <?php esc_html_e('Original vs AVIF', 'compressx') ?>
                        </button>
                        <button id="cx-v2-bulk-show-formats" class="compressx-v2-px-3 compressx-v2-py-1 compressx-v2-bg-gray-50 compressx-v2-border compressx-v2-border-gray-300 compressx-v2-text-gray-700 compressx-v2-text-[11px] compressx-v2-font-medium compressx-v2-rounded hover:compressx-v2-bg-gray-100 compressx-v2-shadow-sm">
                            <?php esc_html_e('WebP vs AVIF', 'compressx') ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="compressx-v2-grid compressx-v2-grid-cols-1 md:compressx-v2-grid-cols-3 compressx-v2-gap-6">
                <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-p-4 compressx-v2-text-center">
                    <p class="compressx-v2-font-medium compressx-v2-mb-2"><?php esc_html_e('Original', 'compressx') ?></p>
                    <p id="cx-v2-original-size" class="compressx-v2-text-sm compressx-v2-text-gray-600">
                        <?php echo esc_html('--') ?>
                    </p>
                </div>

                <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-p-4 compressx-v2-text-center">
                    <p class="compressx-v2-font-medium compressx-v2-mb-2"><?php esc_html_e('WebP', 'compressx') ?></p>
                    <p id="cx-v2-webp-size" class="compressx-v2-text-sm compressx-v2-text-gray-600">
                        <?php echo esc_html('--') ?>
                    </p>
                    <p id="cx-v2-webp-savings" class="compressx-v2-text-xs compressx-v2-text-green-600">
                    </p>
                </div>

                <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-p-4 compressx-v2-text-center">
                    <p class="compressx-v2-font-medium compressx-v2-mb-2"><?php esc_html_e('AVIF', 'compressx') ?></p>
                    <p id="cx-v2-avif-size" class="compressx-v2-text-sm compressx-v2-text-gray-600">
                        <?php echo esc_html('--') ?>
                    </p>
                    <p id="cx-v2-avif-savings" class="compressx-v2-text-xs compressx-v2-text-green-600">
                    </p>
                </div>
            </div>
        </section>
        <?php
    }

    public function output_logs()
    {
        $logs = $this->get_recent_logs();
        $url=admin_url('admin.php?page=info-compressx');
        ?>
        <section class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded-lg compressx-v2-p-5 compressx-v2-shadow-sm">
            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-4">
                <h2 class="compressx-v2-text-lg compressx-v2-font-semibold"><?php esc_html_e('Recent 10 Logs', 'compressx') ?></h2>
                <a href="<?php echo esc_url($url) ?>" class="compressx-v2-text-sm compressx-v2-text-blue-600 hover:compressx-v2-underline"><?php esc_html_e('View All', 'compressx') ?></a>
            </div>

            <div class="compressx-v2-overflow-x-auto">
                <table class="compressx-v2-w-full compressx-v2-text-sm compressx-v2-text-gray-700">
                    <thead>
                    <tr class="compressx-v2-bg-gray-50 compressx-v2-text-left">
                        <th class="compressx-v2-px-4 compressx-v2-py-2"><?php esc_html_e('Date & Time', 'compressx') ?></th>
                        <th class="compressx-v2-px-4 compressx-v2-py-2"><?php esc_html_e('Log File', 'compressx') ?></th>
                        <th class="compressx-v2-px-4 compressx-v2-py-2"><?php esc_html_e('Size', 'compressx') ?></th>
                        <th class="compressx-v2-px-4 compressx-v2-py-2"><?php esc_html_e('Actions', 'compressx') ?></th>
                    </tr>
                    </thead>
                    <tbody id="cx-v2-logs-table" class="compressx-v2-divide-y compressx-v2-divide-gray-200">
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="compressx-v2-px-4 compressx-v2-py-2"><?php echo esc_html($log['date']) ?></td>
                            <td class="compressx-v2-px-4 compressx-v2-py-2"><?php echo esc_html($log['filename']) ?></td>
                            <td class="compressx-v2-px-4 compressx-v2-py-2"><?php echo esc_html($log['size']) ?></td>
                            <td class="compressx-v2-px-4 compressx-v2-py-2 compressx-v2-space-x-2">
                                <a href="#" data-log="<?php echo esc_attr($log['filename']) ?>" class="cx-log-details compressx-v2-text-blue-600 hover:compressx-v2-underline"><?php esc_html_e('Details', 'compressx') ?></a>
                                <a href="#" data-log="<?php echo esc_attr($log['filename']) ?>" class="cx-log-download compressx-v2-text-green-600 hover:compressx-v2-underline"><?php esc_html_e('Download', 'compressx') ?></a>
                                <a href="#" data-log="<?php echo esc_attr($log['filename']) ?>" class="cx-log-delete compressx-v2-text-red-600 hover:compressx-v2-underline"><?php esc_html_e('Delete', 'compressx') ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Log Detail Section (Hidden by default) -->
        <section id="cx-v2-log-modal" class="compressx-v2-mt-6 compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-6 compressx-v2-shadow-sm" style="display: none;">
            <!-- Header -->
            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-4">
                <h2 class="compressx-v2-text-gray-900 compressx-v2-font-semibold compressx-v2-text-xl">
                    <?php esc_html_e('Activity Log', 'compressx'); ?>
                </h2>
                <div class="compressx-v2-flex compressx-v2-gap-2">
                    <button id="cx-v2-download-current-log" class="compressx-v2-bg-blue-100 hover:compressx-v2-bg-blue-200 compressx-v2-text-blue-700 compressx-v2-font-medium compressx-v2-px-3 compressx-v2-py-1.5 compressx-v2-rounded compressx-v2-transition">
                        <?php esc_html_e('Download Log', 'compressx'); ?>
                    </button>
                    <button id="cx-v2-close-log" class="compressx-v2-text-gray-400 hover:compressx-v2-text-gray-600 compressx-v2-text-lg compressx-v2-ml-2" title="<?php esc_attr_e('Close', 'compressx'); ?>">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Info -->
            <div class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-3">
                <?php esc_html_e('Open log file created:', 'compressx'); ?> <span id="cx-v2-log-created-date" class="compressx-v2-font-semibold">--</span>
            </div>

            <!-- Log Container (Y Overflow) -->
            <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-border compressx-v2-p-4 compressx-v2-h-[400px]">
                <textarea id="cx-v2-log-content" style="border: none;background-color: transparent !important;" class="compressx-v2-w-full compressx-v2-h-full compressx-v2-bg-transparent compressx-v2-border-0 compressx-v2-p-0 compressx-v2-text-sm compressx-v2-font-mono compressx-v2-text-gray-700 compressx-v2-resize-none focus:compressx-v2-outline-none" readonly></textarea>
            </div>
        </section>
        <?php
    }

    public function get_recent_logs()
    {
        $log_list = $this->get_log_list();

        $formatted_logs = array();
        $count = 0;
        foreach ($log_list as $log) {
            if ($count >= 10) break;

            $size_formatted = 'N/A';
            if (isset($log['size']) && is_numeric($log['size'])) {
                if ($log['size'] >= 1024) {
                    $size_formatted = round($log['size'] / 1024, 2) . ' KB';
                } else {
                    $size_formatted = $log['size'] . ' B';
                }
            }

            $formatted_logs[] = array(
                'date' => isset($log['date']) ? $log['date'] : gmdate('M-d-y H:i'),
                'filename' => isset($log['name']) ? $log['name'] : (isset($log['file_name']) ? $log['file_name'] : 'unknown.txt'),
                'size' => $size_formatted,
                'detail_url' => '#',
                'download_url' => '#',
                'delete_url' => '#'
            );
            $count++;
        }

        return $formatted_logs;
    }

    /* old
    private function get_log_list()
    {
        $log_list = array();
        $log = new CompressX_Log();
        $dir = $log->GetSaveLogFolder();
        $files = array();
        $handler = opendir($dir);
        $regex = '#^compressx.*_log.txt#';

        if ($handler !== false) {
            while (($filename = readdir($handler)) !== false) {
                if ($filename != "." && $filename != "..") {
                    if (is_dir($dir . $filename)) {
                        continue;
                    } else {
                        if (preg_match($regex, $filename)) {
                            $files[$filename] = $dir . $filename;
                        }
                    }
                }
            }
            if ($handler) {
                @closedir($handler);
            }
        }

        foreach ($files as $file) {
            $handle = @fopen($file, "r");
            if ($handle) {
                $log_file = array();
                $log_file['file_name'] = basename($file);
                if (preg_match('/compressx-(.*?)_/', basename($file), $matches)) {
                    $id = $matches[0];
                    $id = substr($id, 0, strlen($id) - 1);
                    $log_file['id'] = $id;
                }
                $log_file['path'] = $file;
                $log_file['size'] = filesize($file);
                $log_file['name'] = basename($file);
                $log_file['time'] = filemtime($file);

                $offset = get_option('gmt_offset');
                $localtime = $log_file['time'] + $offset * 60 * 60;
                $log_file['date'] = gmdate('M-d-y H:i', $localtime);

                $line = fgets($handle);
                if ($line !== false) {
                    $pos = strpos($line, 'Log created: ');
                    if ($pos !== false) {
                        $log_file['time'] = substr($line, $pos + strlen('Log created: '));
                    }
                }

                fclose($handle);
                $log_list[basename($file)] = $log_file;
            }
        }

        uasort($log_list, function ($a, $b) {
            if ($a['time'] > $b['time']) {
                return -1;
            } else if ($a['time'] === $b['time']) {
                return 0;
            } else {
                return 1;
            }
        });

        return $log_list;
    }
    */

    private function get_log_list()
    {

        $log_list = array();

        $log = new CompressX_Log();
        $dir = trailingslashit( $log->GetSaveLogFolder() );

        // Init WP_Filesystem.
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if ( empty( $wp_filesystem ) ) {
            return $log_list;
        }

        if ( ! $wp_filesystem->is_dir( $dir ) ) {
            return $log_list;
        }

        $regex = '#^compressx.*_log\.txt$#';

        // List directory files.
        $dir_list = $wp_filesystem->dirlist( $dir, false, false );

        if ( empty( $dir_list ) || ! is_array( $dir_list ) ) {
            return $log_list;
        }

        foreach ( $dir_list as $filename => $info ) {

            if ( empty( $filename ) ) {
                continue;
            }

            // Skip folders.
            if ( isset( $info['type'] ) && 'd' === $info['type'] ) {
                continue;
            }

            // Match log files.
            if ( ! preg_match( $regex, $filename ) ) {
                continue;
            }

            $file = $dir . $filename;

            $log_file = array();
            $log_file['file_name'] = $filename;
            $log_file['path']      = $file;
            $log_file['name']      = $filename;

            // Size.
            $size = $wp_filesystem->size( $file );
            $log_file['size'] = ( false === $size ) ? 0 : (int) $size;

            // Time.
            $mtime = $wp_filesystem->mtime( $file );
            $mtime = ( false === $mtime ) ? 0 : (int) $mtime;
            $log_file['time'] = $mtime;

            // ID extraction (keep your original logic).
            if ( preg_match( '/compressx-(.*?)_/', $filename, $matches ) ) {
                $id = $matches[0];
                $id = substr( $id, 0, strlen( $id ) - 1 );
                $log_file['id'] = $id;
            }

            // Local time format.
            $offset    = (float) get_option( 'gmt_offset' );
            $localtime = $mtime + (int) ( $offset * 3600 );
            $log_file['date'] = gmdate( 'M-d-y H:i', $localtime );

            // Read first line from log file (simulate fgets).
            $content = $wp_filesystem->get_contents( $file );

            if ( ! empty( $content ) ) {
                $lines = preg_split( "/\r\n|\n|\r/", $content );
                $line  = isset( $lines[0] ) ? $lines[0] : '';

                if ( ! empty( $line ) ) {
                    $pos = strpos( $line, 'Log created: ' );
                    if ( false !== $pos ) {
                        $log_file['time'] = substr( $line, $pos + strlen( 'Log created: ' ) );
                    }
                }
            }

            $log_list[ $filename ] = $log_file;
        }

        uasort(
            $log_list,
            function ( $a, $b ) {
                if ( $a['time'] > $b['time'] ) {
                    return -1;
                } elseif ( $a['time'] === $b['time'] ) {
                    return 0;
                } else {
                    return 1;
                }
            }
        );

        return $log_list;
    }

    public function get_latest_image_data()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-image-optimization');

        $current_image_id=CompressX_Options::get_option('compressx_latest_bulk_image',null);//


        $meta=CompressX_Image_Meta_V2::get_image_meta($current_image_id);
        if(empty($current_image_id))
        {
            $default_image_data=true;
        }
        else if(empty($meta))
        {
            $default_image_data=true;
        }
        else if(wp_get_attachment_url( $current_image_id )===false)
        {
            $default_image_data=true;
        }
        else
        {
            if($this->webp_converted_exist($current_image_id)||$this->avif_converted_exist($current_image_id))
            {
                $default_image_data=false;
            }
            else
            {
                $default_image_data=true;
            }
        }

        $ret['compressx_latest_bulk_image']=$current_image_id;
        $ret['default_image_data']=$default_image_data;

        if($default_image_data)
        {
            $og_test_file='/includes/assets/compressx_test.png';
            $og_test_webp_file='/includes/assets/compressx_test.png.webp';
            $test_file='compressx_test_comparison.png';
            $test_webp_file='compressx_test_comparison.webp';

            $upload_dir = wp_upload_dir();
            $path = $upload_dir['basedir'] . '/' .$test_file;
            if (!file_exists($path))
            {
                $og_path = COMPRESSX_DIR . $og_test_file;
                copy($og_path, $path);
            }

            $path2 = $upload_dir['basedir'] . '/' . $test_webp_file;

            if (!file_exists($path2))
            {
                $og_path = COMPRESSX_DIR . $og_test_webp_file;
                copy($og_path, $path2);
            }

            $url=$upload_dir['baseurl'].'/'. $test_file;
            $url2=$upload_dir['baseurl'].'/'. $test_webp_file;

            $original_size=filesize($path);
            $webp_size=filesize($path2);
            $savings = round((($original_size - $webp_size) / $original_size) * 100);
            $current_image['original_path']=$path;
            $current_image['original_url']=$url;
            $current_image['webp_path']=$path2;
            $current_image['webp_url']=$url2;
            $current_image['avif_path']='';
            $current_image['avif_url']='';
            $current_image['original_size']=size_format($original_size,2);
            $current_image['webp_size']=size_format($webp_size,2);
            $current_image['webp_savings'] ="~{$savings}% smaller";
            $current_image['avif_size']="";
            $current_image['avif_savings']="";

            $current_image['webp_disabled'] = false;
            $current_image['avif_disabled'] = true;
        }
        else
        {
            $og_url=wp_get_attachment_url($current_image_id);
            $current_image['original_url']=$og_url.'?original';
            $current_image['original_path'] = get_attached_file($current_image_id);
            $meta=CompressX_Image_Meta_V2::get_image_meta($current_image_id);

            $current_image['original_size']=size_format($meta['og_file_size']);

            if($meta['webp_converted'])
            {
                $current_image['webp_disabled'] = false;
                $current_image['webp_size'] = size_format($meta['webp_converted_size'],2);
                $savings = round((( $meta['og_file_size'] -  $meta['webp_converted_size']) / $meta['og_file_size']) * 100);

                $output_path=CompressX_Image_Method::get_output_path($current_image['original_path']);
                if(isset($meta['mime_type'])&&$meta['mime_type']=="image/webp")
                {
                    $current_image['webp_path']=$output_path;
                    $current_image['webp_url']=$this->get_nextgen_url($og_url).'?original';
                }
                else
                {
                    $current_image['webp_path']=$output_path.'.webp';
                    $current_image['webp_url']=$this->get_nextgen_url($og_url).'.webp?original';
                }

                $current_image['webp_savings'] ="~{$savings}% smaller";
            }
            else
            {
                $current_image['webp_disabled'] = true;
                $current_image['webp_path']='';
                $current_image['webp_url']='';
                $current_image['webp_size'] =0;
                $current_image['webp_savings'] =0;
            }

            if($meta['avif_converted'])
            {
                $current_image['avif_disabled'] = false;
                $output_path=CompressX_Image_Method::get_output_path($current_image['original_path']);
                if(isset($meta['mime_type'])&&$meta['mime_type']=="image/avif")
                {
                    $current_image['avif_path']=$output_path;
                    $current_image['avif_url']=$this->get_nextgen_url($og_url).'?original';
                }
                else
                {
                    $current_image['avif_path']=$output_path.'.avif';
                    $current_image['avif_url']=$this->get_nextgen_url($og_url).'.avif?original';
                }

                $current_image['avif_size'] = size_format($meta['avif_converted_size'],2);
                $savings = round((( $meta['og_file_size'] -  $meta['avif_converted_size']) /  $meta['og_file_size']) * 100);
                $current_image['avif_savings'] ="~{$savings}% smaller";
            }
            else
            {
                $current_image['avif_disabled'] = true;
                $current_image['avif_path']='';
                $current_image['avif_url']='';
                $current_image['avif_size'] = 0;
                $current_image['avif_savings']=0;
            }
        }

        $ret['result']='success';
        $ret['current_image']=$current_image;
        echo wp_json_encode($ret);
        die();
    }

    public function webp_converted_exist($image_id)
    {
        $original_path = get_attached_file($image_id);
        $meta=CompressX_Image_Meta_V2::get_image_meta($image_id);
        if($meta['webp_converted'])
        {
            $output_path=CompressX_Image_Method::get_output_path($original_path);
            if(isset($meta['mime_type'])&&$meta['mime_type']=="image/webp")
            {
                $webp_path=$output_path;
            }
            else
            {
                $webp_path=$output_path.'.webp';
            }

            if(file_exists($webp_path))
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

    public function avif_converted_exist($image_id)
    {
        $original_path = get_attached_file($image_id);
        $meta=CompressX_Image_Meta_V2::get_image_meta($image_id);

        if($meta['avif_converted'])
        {
            $output_path=CompressX_Image_Method::get_output_path($original_path);
            if(isset($meta['mime_type'])&&$meta['mime_type']=="image/avif")
            {
                $avif_path=$output_path;
            }
            else
            {
                $avif_path=$output_path.'.avif';
            }

            if(file_exists($avif_path))
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

    public function get_nextgen_url( $orig_url )
    {
        $upload_dir = wp_get_upload_dir();

        return str_replace(
            $upload_dir['baseurl'],
            content_url( 'compressx-nextgen/uploads' ),
            $orig_url
        );
    }
}