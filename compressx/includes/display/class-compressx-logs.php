<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Logs
{
    public function __construct()
    {

        add_action('wp_ajax_compressx_open_log',array( $this,'open_log'));
        add_action('wp_ajax_compressx_download_log', array($this, 'download_log'));
        add_action('wp_ajax_compressx_delete_log', array($this, 'delete_log'));
        add_action('wp_ajax_compressx_delete_all_log', array($this, 'delete_all_log'));
        //
        add_action('wp_ajax_compressx_get_logs_list', array($this, 'get_logs_list'));
    }

    public function display()
    {
        ?>
        <div id="compressx-root">
            <div id="compressx-wrap">
                <?php
                $this->output_nav();
                $this->output_header();
                $this->output_logs();
                $this->output_footer();
                ?>
            </div>
        </div>
        <?php
    }

    public function output_nav()
    {
        do_action('compressx_output_nav');
    }

    public function output_header()
    {
        do_action('compressx_output_header');
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    public function output_logs()
    {
        ?>
        <section>
            <div class="compressx-container compressx-header">
                <div class="compressx-logs">
                    <article>
                        <div class="cx-title"><span><?php esc_html_e('Logs: ','compressx')?></span>
                            <span class="cx-bulk-opt-results">
                            <span><a style="cursor: pointer" id="cx_empty_log"><?php esc_html_e('Empty Logs','compressx')?></a></span>
                        </div>
                        <div id="cx_log_list" class="cx-table-overflow">
                            <?php
                            $list=$this->get_log_list();
                            $log_list=new CompressX_Log_List();
                            $log_list->set_log_list($list);
                            $log_list->prepare_items();
                            $log_list->display();
                            ?>
                        </div>
                    </article>
                    <div id="cx_log_scroll_test"></div>
                </div>
            </div>
        </section>
        <section id="cx_log_detail_section" style="display: none">
            <div class="compressx-container compressx-section">
                <div class="compressx-bulk-log">
                    <article>
                        <div class="cx-title">
                            <span>Log: </span><span id="cx_log_name"></span>
                        </div>
                        <div class="cx-logs-table">
                            <textarea id="cx_read_optimize_log_content" style="width:100%; height:300px; overflow-x:auto;">
                            </textarea>
                        </div>
                        <div class="compressx-general-settings-footer">
                            <input class="button-primary cx-button" id="cx_close_log" type="submit" value="Close">
                        </div>
                    </article>
                </div>
            </div>
        </section>
        <?php
    }

    public function get_log_list()
    {
        $log_list=array();
        $log=new CompressX_Log();
        $dir=$log->GetSaveLogFolder();
        $files=array();
        $handler=opendir($dir);
        $regex='#^compressx.*_log.txt#';
        if($handler!==false)
        {
            while(($filename=readdir($handler))!==false)
            {
                if($filename != "." && $filename != "..")
                {
                    if(is_dir($dir.$filename))
                    {
                        continue;
                    }else{
                        if(preg_match($regex,$filename))
                        {
                            $files[$filename] = $dir.$filename;
                        }
                    }
                }
            }
            if($handler)
                @closedir($handler);
        }

        foreach ($files as $file)
        {
            $handle = @fopen($file, "r");
            if ($handle)
            {
                $log_file['file_name']=basename($file);
                if(preg_match('/compressx-(.*?)_/', basename($file), $matches))
                {
                    $id= $matches[0];
                    $id=substr($id,0,strlen($id)-1);
                    $log_file['id']=$id;
                }
                $log_file['path']=$file;
                $log_file['size']=filesize($file);
                $log_file['name']=basename($file);
                $log_file['time']=filemtime($file);

                $offset=get_option('gmt_offset');
                $localtime = $log_file['time'] + $offset * 60 * 60;
                $log_file['date']=gmdate('M-d-y H:i',$localtime);

                $line = fgets($handle);
                if($line!==false)
                {
                    $pos=strpos($line,'Log created: ');
                    if($pos!==false)
                    {
                        $log_file['time']=substr ($line,$pos+strlen('Log created: '));
                    }
                }

                fclose($handle);
                $log_list[basename($file)]=$log_file;
            }
        }

        $log_list =$this->sort_list($log_list);

        return $log_list;
    }

    public function get_log_list_ex($start_date,$start_time,$end_date,$end_time)
    {
        if(empty($start_date))
        {
            $start=0;
        }
        else if(empty($start_time))
        {
            $start=strtotime($start_date);
        }
        else
        {
            $start=strtotime($start_date.' '.$start_time);
        }

        if($start>0)
        {
            $offset=get_option('gmt_offset');
            $start = $start - $offset * 60 * 60;
        }

        if(empty($end_date))
        {
            $end=0;
        }
        else if(empty($end_time))
        {
            $end=strtotime($end_date);
        }
        else
        {
            $end=strtotime($end_date.' '.$end_time);
        }

        if($end>0)
        {
            $offset=get_option('gmt_offset');
            $end = $end - $offset * 60 * 60;
        }


        $log_list=array();
        $log=new CompressX_Log();
        $dir=$log->GetSaveLogFolder();
        $files=array();
        $handler=opendir($dir);
        $regex='#^compressx.*_log.txt#';
        if($handler!==false)
        {
            while(($filename=readdir($handler))!==false)
            {
                if($filename != "." && $filename != "..")
                {
                    if(is_dir($dir.$filename))
                    {
                        continue;
                    }else{
                        if(preg_match($regex,$filename))
                        {
                            $files[$filename] = $dir.$filename;
                        }
                    }
                }
            }
            if($handler)
                @closedir($handler);
        }

        foreach ($files as $file)
        {
            $handle = @fopen($file, "r");
            if ($handle)
            {
                $log_file['file_name']=basename($file);
                if(preg_match('/compressx-(.*?)_/', basename($file), $matches))
                {
                    $id= $matches[0];
                    $id=substr($id,0,strlen($id)-1);
                    $log_file['id']=$id;
                }
                $log_file['path']=$file;
                $log_file['size']=filesize($file);
                $log_file['name']=basename($file);
                $log_file['time']=preg_replace('/[^0-9]/', '', basename($file));

                $offset=get_option('gmt_offset');
                $localtime = strtotime($log_file['time']) + $offset * 60 * 60;
                $log_file['date']=gmdate('M-d-y H:i',$localtime);

                $line = fgets($handle);
                if($line!==false)
                {
                    $pos=strpos($line,'Log created: ');
                    if($pos!==false)
                    {
                        $log_file['time']=substr ($line,$pos+strlen('Log created: '));
                    }
                }

                fclose($handle);

                if($start>0&&$end>0)
                {
                    if($localtime>=$start&&$localtime<=$end)
                    {
                        $log_list[basename($file)]=$log_file;
                    }
                }
                else if($start>0)
                {
                    if($localtime>=$start)
                    {
                        $log_list[basename($file)]=$log_file;
                    }
                }
                else if($end>0)
                {
                    if($localtime<=$end)
                    {
                        $log_list[basename($file)]=$log_file;
                    }
                }
                else
                {
                    $log_list[basename($file)]=$log_file;
                }
            }
        }

        $log_list =$this->sort_list($log_list);

        return $log_list;
    }

    public function sort_list($list)
    {
        uasort ($list,function($a, $b)
        {
            if($a['time']>$b['time'])
            {
                return -1;
            }
            else if($a['time']===$b['time'])
            {
                return 0;
            }
            else
            {
                return 1;
            }
        });

        return $list;
    }

    public function get_logs_list()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        if(isset($_POST['start_date']))
        {
            $start_date=sanitize_key($_POST['start_date']);
        }
        else
        {
            $start_date='';
        }

        if(isset($_POST['start_time']))
        {
            $start_time=sanitize_key($_POST['start_time']);
        }
        else
        {
            $start_time='';
        }

        if(isset($_POST['end_date']))
        {
            $end_date=sanitize_key($_POST['end_date']);
        }
        else
        {
            $end_date='';
        }

        if(isset($_POST['end_time']))
        {
            $end_time=sanitize_key($_POST['end_time']);
        }
        else
        {
            $end_time='';
        }

        if(isset($_POST['page']))
        {
            $page=sanitize_key($_POST['page']);
        }
        else
        {
            $page=0;
        }

        $list=$this->get_log_list_ex($start_date,$start_time,$end_date,$end_time);
        $log_list=new CompressX_Log_List();
        $log_list->set_log_list($list,$page);
        $log_list->prepare_items();
        ob_start();
        $log_list->display();
        $json['result'] = 'success';
        $json['html'] =  ob_get_clean();

        echo wp_json_encode($json);
        die();
    }

    public function open_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try
        {
            if(!isset($_POST['filename']))
            {
                die();
            }

            $file_name=sanitize_text_field($_POST['filename']);
            $loglist=$this->get_log_list();
            if(!empty($loglist))
            {
                if(isset($loglist[$file_name]))
                {
                    $log=$loglist[$file_name];
                }
                else
                {
                    $json['result'] = 'failed';
                    $json['error'] =__('The log not found ','compressx').$file_name;
                    echo wp_json_encode($json);
                    die();
                }

                $path=$log['path'];
                if (!file_exists($path))
                {
                    $json['result'] = 'failed';
                    $json['error'] = __('The log not found ','compressx').$file_name;
                    echo wp_json_encode($json);
                    die();
                }

                $file = fopen($path, 'r');

                if (!$file) {
                    $json['result'] = 'failed';
                    $json['error'] = __('Unable to open the log file.','compressx');
                    echo wp_json_encode($json);
                    die();
                }

                $offset=get_option('gmt_offset');
                $localtime = strtotime($log['time']) + $offset * 60 * 60;
                $buffer = 'Open log file created:'.gmdate('Y-m-d',$localtime).' '.PHP_EOL;
                while (!feof($file)) {
                    $buffer .= fread($file, 1024);
                }
                fclose($file);

                $json['result'] = 'success';
                $json['html'] = $buffer;
                echo wp_json_encode($json);
                die();
            }
            else
            {
                $json['result'] = 'failed';
                $json['error'] = 'The log not found.';
                echo wp_json_encode($json);
                die();
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function download_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        $admin_url = admin_url();

        try
        {
            if (isset($_REQUEST['log']))
            {
                $log = sanitize_text_field($_REQUEST['log']);
                $log = basename($log);
                $loglist=$this->get_log_list();

                if(isset($loglist[$log]))
                {
                    $log=$loglist[$log];
                }
                else
                {
                    $message= 'Log does not exist. It might have been deleted or lost during a website migration.';
                    echo esc_html($message);
                    die();
                }

                $path=$log['path'];

                if (!file_exists($path))
                {
                    $message= 'Log does not exist. It might have been deleted or lost during a website migration.';
                    echo esc_html($message);
                    die();
                }

                if (file_exists($path))
                {
                    if (session_id())
                        session_write_close();

                    $size = filesize($path);
                    if (!headers_sent())
                    {
                        header('Content-Description: File Transfer');
                        header('Content-Type: text');
                        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                        header('Cache-Control: must-revalidate');
                        header('Content-Length: ' . $size);
                        header('Content-Transfer-Encoding: binary');
                    }

                    ob_end_clean();
                    readfile($path);
                    exit;
                }
                else
                {
                    echo esc_html(' file not found.');
                    die();
                }

            } else {
                $message = 'Reading the log failed. Please try again.';
                echo esc_html($message);
                die();
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo esc_html($message);
            die();
        }
    }

    public function delete_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try
        {
            if(!isset($_POST['filename']))
            {
                die();
            }

            $file_name=sanitize_text_field($_POST['filename']);
            $loglist=$this->get_log_list();
            if(!empty($loglist))
            {
                if(isset($loglist[$file_name]))
                {
                    $log=$loglist[$file_name];
                }
                else
                {
                    $json['result'] = 'failed';
                    $json['error'] = __('The log not found ','compressx').$file_name;
                    echo wp_json_encode($json);
                    die();
                }

                $path=$log['path'];
                @wp_delete_file($path);

                $list=$this->get_log_list();
                $log_list=new CompressX_Log_List();
                $log_list->set_log_list($list);
                $log_list->prepare_items();
                ob_start();
                $log_list->display();
                $json['result'] = 'success';
                $json['html'] =  ob_get_clean();

                echo wp_json_encode($json);
                die();
            }
            else
            {
                $json['result'] = 'failed';
                $json['error'] = __('The log not found ','compressx');
                echo wp_json_encode($json);
                die();
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_all_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try
        {
            $loglist=$this->get_log_list();
            if(!empty($loglist))
            {
                foreach ($loglist as $log)
                {
                    $path=$log['path'];
                    @wp_delete_file($path);
                }

                $list=$this->get_log_list();
                $log_list=new CompressX_Log_List();
                $log_list->set_log_list($list);
                $log_list->prepare_items();
                ob_start();
                $log_list->display();
                $json['result'] = 'success';
                $json['html'] =  ob_get_clean();

                echo wp_json_encode($json);
                die();
            }
            else
            {
                $json['result'] = 'failed';
                $json['error'] = __('The log not found ','compressx');
                echo wp_json_encode($json);
                die();
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }


}