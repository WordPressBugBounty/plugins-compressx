<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Custom_Bulk_Action
{
    public  $support_extension;

    public function __construct()
    {
        $this->support_extension=array();
        $this->support_extension[]='jpg';
        $this->support_extension[]='jpeg';
        $this->support_extension[]='png';
        $this->support_extension[]='webp';

        add_action('wp_ajax_compressx_get_custom_tree_dir_ex', array($this, 'get_custom_tree_dir_ex'));
        add_action('wp_ajax_compressx_add_include_folders', array($this, 'add_include_folders'));
        add_action('wp_ajax_compressx_remove_include_folders', array($this, 'remove_include_folders'));
        //
        add_action('wp_ajax_compressx_start_scan_custom_images', array($this, 'start_scan_custom_images'));
        add_action('wp_ajax_compressx_init_custom_bulk_optimization_task', array($this, 'init_custom_bulk_optimization_task'));
        add_action('wp_ajax_compressx_run_custom_optimize', array($this, 'run_custom_optimize'));
        add_action('wp_ajax_compressx_get_custom_opt_progress', array($this, 'get_custom_opt_progress'));
        add_action('wp_ajax_compressx_get_dir_info', array($this, 'get_dir_info'));
        //
        add_action('cx_output_custom_bulk',array($this, 'output_custom_bulk'));
    }

    public function output_custom_bulk()
    {
        CompressX_Custom_Image_Meta::check_custom_table();

        $includes=CompressX_Options::get_option('compressx_custom_includes',array());
        $stats=$this->get_custom_stats($includes);
        $found=$stats['files'];
        $saved=$stats['saved'];
        $processed=$stats['processed_files'];
        ?>
        <header>
            <div class="compressx-container compressx-header">
                <div class="compressx-general-settings" style="padding-bottom: 0;">
                    <div class="compressx-general-settings-header">
                        <h5><?php esc_html_e('Custom Folders','compressx')?></h5>
                    </div>
                    <div class="compressing-converting-information" style="margin-top: 1rem;">
                        <?php esc_html_e('Select custom folders in the /wp-content folder and process images inside them.','compressx')?>
                    </div>
                    <div class="compressx-general-settings-body">
                        <?php
                        $this->output_custom_folders();
                        ?>
                        <div class="cx-custom-overview">
                            <div>
                                <span id="cx_custom_overview">
                                    <span><?php esc_html_e('Media Files Found: ','compressx')?></span><span id="cx_custom_founds"><?php echo esc_html($found);?></span><span> | </span>
                                    <span><?php esc_html_e('Total Savings: ','compressx')?></span><span id="cx_custom_total_saving"><?php echo esc_html($saved);?></span><span>%</span><span> | </span>
                                    <span><?php esc_html_e('Processed Media Files: ','compressx')?></span><span id="cx_custom_processed"><?php echo esc_html($processed);?></span>
                                </span>
                            </div>
                            <div style="padding-top:1rem;">
                                <input type="submit" id="compressx_start_custom_bulk_optimization" class="button-primary cx-button" value="<?php esc_attr_e('Bulk Process Now','compressx')?>">
                                <input class="button-primary cx-button" style="display: none" id="compressx_cancel_custom_bulk_optimization" type="submit" value="<?php esc_attr_e('Cancel Processing','compressx')?>">
                                <span id="cx_custom_bulk_progress_text" style="display: none"></span>
                            </div>
                            <div style="padding-top:1rem;">
                                <span><input id="cx_custom_force_optimization" type="checkbox"><?php esc_html_e('Force all images to be re-processed','compressx')?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <?php
    }

    public function get_custom_stats($includes)
    {
        $stats=array();

        $stats['files']=0;
        $stats['saved']=0;
        $stats['processed_files']=0;
        $stats['total']=0;
        $stats['processed']=0;

        $convert_to_webp=CompressX_Image_Opt_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Opt_Method::get_convert_to_avif();

        foreach ($includes as $include)
        {
            $this->get_folder_stats($include,$stats,$convert_to_webp,$convert_to_avif);
        }

        if($stats['total']>0)
        {
            $stats['saved'] = ( $stats['processed'] / $stats['total'] ) * 100;
            $stats['saved'] = round( $stats['saved'], 1 );
        }

        return $stats;
    }

    public function get_folder_stats($path,&$stats,$convert_to_webp,$convert_to_avif)
    {
        try {
            $count=0;
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($rii as $file)
            {
                if ($file->isDir())
                {
                    continue;
                }

                if($this->is_support_extension($file->getExtension()))
                {
                    $filename=$path.'/'.$file->getFilename();
                    $filename=$this->transfer_path($filename);

                    $type=pathinfo($filename, PATHINFO_EXTENSION);

                    if($convert_to_webp&&$type!="webp")
                    {
                        if(CompressX_Custom_Image_Meta::is_convert_webp($filename))
                        {
                            $stats['processed_files']++;
                            $stats['processed']+=CompressX_Custom_Image_Meta::get_convert_webp_size($filename);
                        }

                        $count++;
                        $stats['files']++;
                        $stats['total']+=$file->getSize();
                    }
                    else if($convert_to_avif&&$type!="avif")
                    {
                        if(CompressX_Custom_Image_Meta::is_convert_avif($filename))
                        {
                            $stats['processed_files']++;
                            $stats['processed']+=CompressX_Custom_Image_Meta::get_convert_avif_size($filename);
                        }

                        $count++;
                        $stats['files']++;
                        $stats['total']+=$file->getSize();
                    }
                }
            }
            return $count;
        }
        catch (Exception $exception)
        {
            return 0;
        }

    }

    public function is_support_extension($extension)
    {
       if(in_array($extension,$this->support_extension))
       {
           return true;
       }
       else
       {
           return false;
       }
    }

    public function output_custom_folders()
    {
        $includes=CompressX_Options::get_option('compressx_custom_includes',array());
        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));

        $convert_to_avif=CompressX_Options::get_option('compressx_output_format_avif',1);

        if($convert_to_avif&&CompressX_Image_Opt_Method::is_support_avif())
        {
            $convert_to_avif=true;
        }
        else
        {
            $convert_to_avif=false;
        }

        ?>
        <div class="cx-mediafolder-rules">
            <div class="cx-mediafolders">
                <span>
                    <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span><span>wp-content:</span>
                </span>
                <div class="cx-upload-treeviewer" id="compressx_custom_include_js_tree">
                </div>
            </div>
            <div class="cx-mediafolder-included">
                <span>
                    <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                    <span><strong><?php esc_html_e('Included Folders: ','compressx')?></strong><?php esc_html_e('Media files inside these folders will be processed','compressx')?></span>
                </span>
                <div class="cx-mediafolder-list" id="compressx_include_dir_node">
                    <ul>
                        <?php

                        foreach ($includes as $include)
                        {
                            $path=str_replace($abs_path,'',$include);
                            ?>
                            <li>
                                <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                                <span><?php echo esc_html($path.'('.$this->get_children_count($include,$convert_to_avif).')')?></span>
                                <span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-include-tree" data-id="<?php echo esc_attr($include)?>"></span>
                            </li>
                            <?php
                        }

                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_custom_tree_dir_ex()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        try{
            $node_array = array();

            if ($_POST['tree_node']['node']['id'] == '#')
            {
                $path = ABSPATH;

                if (!empty($_POST['tree_node']['path']))
                {
                    $path = sanitize_text_field($_POST['tree_node']['path']);
                }
            } else {
                $path = sanitize_text_field($_POST['tree_node']['node']['id']);
            }

            $path = trailingslashit(str_replace('\\', '/', realpath($path)));

            if ($dh = opendir($path))
            {
                while (substr($path, -1) == '/')
                {
                    $path = rtrim($path, '/');
                }
                $skip_paths = array(".", "..");

                while (($value = readdir($dh)) !== false)
                {
                    trailingslashit(str_replace('\\', '/', $value));
                    if (!in_array($value, $skip_paths))
                    {
                        if (is_dir($path . '/' . $value))
                        {
                            if(!$this->is_media_folder($path . '/' . $value))
                            {
                                $node['children'] = $this->has_children($path . '/' . $value);
                                $node['id'] = $path . '/' . $value;
                                $node['icon'] = 'dashicons dashicons-category cx-icon-color-techblue';
                                $node['text'] ='<span>'.$value.'</span>';
                                $node['text'] .='<span class="dashicons dashicons-insert cx-remove-rule cx-add-custom-include-tree" data-id="'.$node['id'].'"></span>';
                                $node_array[] = $node;
                            }
                        }
                        else{
                            continue;
                        }
                    }
                }
            }

            $ret['nodes'] = $node_array;
            echo wp_json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo wp_json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function has_children($path)
    {
        if ($dh = opendir($path))
        {
            while (substr($path, -1) == '/')
            {
                $path = rtrim($path, '/');
            }
            $skip_paths = array(".", "..");

            while (($value = readdir($dh)) !== false)
            {
                trailingslashit(str_replace('\\', '/', $value));
                if (!in_array($value, $skip_paths))
                {
                    if (is_dir($path . '/' . $value))
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function is_media_folder($path)
    {
        $upload_dir = wp_upload_dir();
        $base_dir   = $upload_dir['basedir'];
        $base_dir = trailingslashit(str_replace('\\', '/', realpath($base_dir)));
        $path=str_replace( $base_dir, '', $path );
        $path_arr = explode( '/', $path );

        if ( count( $path_arr ) >= 1)
        {
            if(is_numeric( $path_arr[0] ) && $path_arr[0] > 1950 && $path_arr[0] < 2050)
            {
                return true;
            }
        }

        $path=$this->transfer_path($path);
        $upload_root=$this->transfer_path($upload_dir['basedir']);
        $compressx1=$this->transfer_path(WP_CONTENT_DIR.'/compressx');
        $compressx2=$this->transfer_path(WP_CONTENT_DIR.'/compressx-nextgen');

        if($path==$compressx1)
        {
            return true;
        }
        else if($path==$compressx2)
        {
            return true;
        }
        else if($path==$upload_root)
        {
            return true;
        }

        return false;
    }

    public function get_children_count($path,$convert_to_avif)
    {
        try {
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            $files = 0;

            /** @var SplFileInfo $file */
            foreach ($rii as $file)
            {
                if ($file->isDir())
                {
                    continue;
                }

                if($this->is_support_extension($file->getExtension()))
                {
                    $filename=$path.'/'.$file->getFilename();
                    $type=pathinfo($filename, PATHINFO_EXTENSION);

                    if($type=='webp'&&$convert_to_avif)
                    {
                        $files++;
                    }
                    else
                    {
                        $files++;
                    }

                }

            }

            return $files;
        }
        catch (Exception $e)
        {
            return 0;
        }
    }

    public function add_include_folders()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        $includes=CompressX_Options::get_option('compressx_custom_includes',array());

        $new_include = sanitize_text_field($_POST['id']);

        $new_includes[$new_include]=$new_include;

        $includes=array_merge($includes,$new_includes);

        CompressX_Options::update_option('compressx_custom_includes',$includes);

        $stats=array();

        $stats['files']=0;
        $stats['saved']=0;
        $stats['processed_files']=0;
        $stats['total']=0;
        $stats['processed']=0;

        $convert_to_webp=CompressX_Image_Opt_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Opt_Method::get_convert_to_avif();

        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));

        $html='<ul>';
        foreach ($includes as $include)
        {
            $path=str_replace($abs_path,'',$include);
            $html.= '<li><span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>'.
                '<span>'.$path.'('.$this->get_folder_stats($include,$stats,$convert_to_webp,$convert_to_avif).')'.'</span>'.
                '<span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-include-tree" data-id="'.$include.'"></span></li>';
        }
        $html.='<ul>';

        if($stats['total']>0)
        {
            $stats['saved'] = ( $stats['processed'] / $stats['total'] ) * 100;
            $stats['saved'] = round( $stats['saved'], 1 );
        }

        $ret['result']='success';
        $ret['html']=$html;
        $ret['found']=$stats['files'];
        $ret['saved']=$stats['saved'];
        $ret['processed']=$stats['processed_files'];

        echo wp_json_encode($ret);
        die();
    }

    public function get_dir_info()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        $includes=CompressX_Options::get_option('compressx_custom_includes',array());

        $stats=array();

        $stats['files']=0;
        $stats['saved']=0;
        $stats['processed_files']=0;
        $stats['total']=0;
        $stats['processed']=0;

        $convert_to_webp=CompressX_Image_Opt_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Opt_Method::get_convert_to_avif();

        foreach ($includes as $include)
        {
            $this->get_folder_stats($include,$stats,$convert_to_webp,$convert_to_avif);
        }

        if($stats['total']>0)
        {
            $stats['saved'] = ( $stats['processed'] / $stats['total'] ) * 100;
            $stats['saved'] = round( $stats['saved'], 1 );
        }

        $ret['result']='success';
        $ret['found']=$stats['files'];
        $ret['saved']=$stats['saved'];
        $ret['processed']=$stats['processed_files'];

        echo wp_json_encode($ret);
        die();
    }

    public function remove_include_folders()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        $includes=CompressX_Options::get_option('compressx_custom_includes',array());

        $id = sanitize_text_field($_POST['id']);
        unset($includes[$id]);

        CompressX_Options::update_option('compressx_custom_includes',$includes);

        $stats=array();

        $stats['files']=0;
        $stats['saved']=0;
        $stats['processed_files']=0;
        $stats['total']=0;
        $stats['processed']=0;

        $convert_to_webp=CompressX_Image_Opt_Method::get_convert_to_webp();
        $convert_to_avif=CompressX_Image_Opt_Method::get_convert_to_avif();

        foreach ($includes as $include)
        {
            $this->get_folder_stats($include,$stats,$convert_to_webp,$convert_to_avif);
        }

        if($stats['total']>0)
        {
            $stats['saved'] = ( $stats['processed'] / $stats['total'] ) * 100;
            $stats['saved'] = round( $stats['saved'], 1 );
        }

        $ret['result']='success';
        $ret['found']=$stats['files'];
        $ret['saved']=$stats['saved'];
        $ret['processed']=$stats['processed_files'];

        echo wp_json_encode($ret);
        die();
    }

    public function start_scan_custom_images()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        $includes=CompressX_Options::get_option('compressx_custom_includes',array());
        if(empty($includes))
        {
            $ret['result']='failed';
            $ret['error']='Please select at least one folder to process.';
            echo wp_json_encode($ret);
            die();
        }

        CompressX_Options::update_option("compressx_need_optimized_custom_images",array());

        $images=array();

        foreach ($includes as $include)
        {
            $this->get_folder_images($images,$include);
        }

        CompressX_Options::update_option("compressx_need_optimized_custom_images",$images);

        $ret['result']='success';
        $ret['progress']=sprintf(
        /* translators: %1$d: Scanning images*/
            __('Scanning: %1$d Found | View Log' ,'compressx'),
            sizeof($images));
        $ret['finished']=true;
        $ret['test']=$includes;

        echo wp_json_encode($ret);

        die();
    }

    public function get_folder_images(&$images,$path)
    {
        if(is_dir($path))
        {
            $handler = opendir($path);
            if($handler!==false)
            {
                while (($filename = readdir($handler)) !== false)
                {
                    if ($filename != "." && $filename != "..")
                    {
                        if (is_dir($path . DIRECTORY_SEPARATOR . $filename))
                        {
                            $this->get_folder_images($images, $path . DIRECTORY_SEPARATOR . $filename);
                        }
                        else
                        {
                            if($this->check_image($path . DIRECTORY_SEPARATOR . $filename))
                            {
                                $images[] = $path . DIRECTORY_SEPARATOR . $filename;
                            }
                        }
                    }
                }
                if($handler)
                    @closedir($handler);
            }
        }
    }

    public function check_image($file)
    {
        if ( ! file_exists( $file ))
        {
            return false;
        }
        $extension = array('jpg', 'jpeg', 'png','webp','avif');

        $ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );

        if ( !in_array( $ext, $extension, true ) )
        {
            return false;
        }

        return true;
    }

    public function init_custom_bulk_optimization_task()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        $force=isset($_POST['force'])?sanitize_key($_POST['force']):'0';
        if($force=='1')
        {
            $force=true;
        }
        else
        {
            $force=false;
        }

        $task=new CompressX_Custom_ImgOptim_Task();
        $ret=$task->init_task($force);
        echo wp_json_encode($ret);
        die();
    }

    public function run_custom_optimize()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        set_time_limit(180);

        $task=new CompressX_Custom_ImgOptim_Task();

        $ret=$task->get_task_status();

        if($ret['result']=='success'&&$ret['status']=='completed')
        {
            $ret=$task->do_optimize_image();
            echo wp_json_encode($ret);
        }
        else
        {
            echo wp_json_encode($ret);
        }
        die();
    }

    public function get_custom_opt_progress()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-bulk-custom-convert');

        $task=new CompressX_Custom_ImgOptim_Task();

        $result=$task->get_task_progress();

        echo wp_json_encode($result);

        die();
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }
}
