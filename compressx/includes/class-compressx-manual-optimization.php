<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Manual_Optimization
{
    public $log;

    public function __construct()
    {
        add_action('wp_ajax_compressx_opt_single_image', array($this, 'opt_single_image'));
        //add_action('wp_ajax_compressx_opt_image', array($this, 'opt_image'));
        add_action('wp_ajax_compressx_get_opt_single_image_progress', array($this, 'get_opt_single_image_progress'));
        add_action('wp_ajax_compressx_delete_single_image', array($this, 'delete_single_image'));
    }

    public function delete_single_image()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        if(!isset($_POST['id']))
        {
            die();
        }

        $id=sanitize_key($_POST['id']);

        if(isset($_POST['page'])&&is_string($_POST['page']))
        {
            $page=sanitize_text_field($_POST['page']);
        }
        else
        {
            $page='media';
        }

        try
        {
            CompressX_Image_Opt_Method::delete_image($id);

            if($page=='edit')
            {
                $html='<h4>'.__('CompressX', 'compressx').'</h4>';
            }
            else
            {
                $html='';
            }

            if(!CompressX_Image_Meta::is_image_optimized($id))
            {
                if($this->is_image_progressing($id))
                {
                    $html.= "<a  class='cx-media-progressing button' data-id='{$id}'>".__('Converting...', 'compressx')."</a>";
                }
                else
                {
                    $html.= "<a  class='cx-media button' data-id='{$id}'>".__('Convert','compressx')."</a>";

                    if($this->is_image_processing_failed($id))
                    {
                        $meta=CompressX_Image_Meta::get_image_meta($id);
                        foreach ($meta['size'] as $size_key => $size_data)
                        {
                            if(!empty($size_data['error']))
                            {
                                $html.='<p style="border-bottom:1px solid #D2D3D6;margin-top: 12px;"></p>';
                                $html.="<span>".esc_html($size_data['error'])."</span>";
                                break;
                            }
                        }
                    }
                }
            }
            else
            {
                $convert_size=CompressX_Image_Meta::get_webp_converted_size($id);
                $og_size=CompressX_Image_Meta::get_og_size($id);
                if($og_size>0)
                {
                    if($convert_size>0)
                    {
                        $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                    }
                    else if(CompressX_Image_Meta::is_webp_image($id))
                    {
                        $convert_size=CompressX_Image_Meta::get_compressed_size($id);
                        if($convert_size>0)
                        {
                            $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                        }
                        else
                        {
                            $webp_percent=0;
                        }
                    }
                    else if(CompressX_Image_Meta::is_avif_image($id))
                    {
                        $webp_percent=0;
                    }
                    else
                    {
                        $webp_percent=0;
                    }
                }
                else
                {
                    $webp_percent=0;
                }

                $avif_size=CompressX_Image_Meta::get_avif_converted_size($id);
                if($og_size>0)
                {
                    if($avif_size>0)
                    {
                        $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                    }
                    else if(CompressX_Image_Meta::is_avif_image($id))
                    {
                        $avif_size=CompressX_Image_Meta::get_compressed_size($id);
                        if($avif_size>0)
                        {
                            $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                        }
                        else
                        {
                            $avif_percent=0;
                        }
                    }
                    else
                    {
                        $avif_percent=0;
                    }
                }
                else
                {
                    $avif_percent=0;
                }

                $meta=CompressX_Image_Meta::get_image_meta($id);
                $thumbnail_counts=count($meta['size']);

                $html.='<ul>';
                $html.= '<li><span>'.__('Original','compressx').' : </span><strong>'.size_format($og_size,2).'</strong></li>';
                $html.= '<li><span>'.__('Webp','compressx').' : </span><strong>'.size_format($convert_size,2).'</strong><span> '.__('Saved','compressx').' : </span><strong>'.$webp_percent.'%</strong></li>';
                $html.= '<li><span>'.__('AVIF','compressx').' : </span><strong>'.size_format($avif_size,2).'</strong><span> '.__('Saved','compressx').' : </span><strong>'.$avif_percent.'%</strong></li>';
                $html.= '<li><span>'.__('Thumbnails generated','compressx').' : </span><strong>'.$thumbnail_counts.'</strong></li>';
                $html.="<li><a class='cx-media-delete button' data-id='".esc_attr($id)."'>Delete</a>
<span class='compressx-dashicons-help compressx-tooltip'>
                                    <a href='#'><span class='dashicons dashicons-editor-help' style='padding-top: 3px;'></span></a>
                                    <div class='compressx-bottom'>
                                        <!-- The content you need -->
                                        <p>
                                            <span>".__('Delete the WebP and AVIF images generated by CompressX.','compressx')."</span><br>
                                        </p>
                                        <i></i> <!-- do not delete this line -->
                                    </div>
                                </span>
</li>";
                $html.='</ul>';
            }

            $ret[$id]['html']=$html;
            $ret['result']='success';

            echo json_encode($ret);
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function opt_single_image()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        if(!isset($_POST['id']))
        {
            die();
        }

        $id=sanitize_key($_POST['id']);
        delete_transient('compressx_set_global_stats');

        set_time_limit(180);

        $this->log=new CompressX_Log();
        $this->log->CreateLogFile();

        $this->do_optimize_image($id);

        die();
    }

    public function do_optimize_image($attachment_id)
    {
        $this->WriteLog('Start optimizing image id:'.$attachment_id,'notice');

        $general_options=CompressX_Options::get_general_settings();
        $quality_options=CompressX_Options::get_quality_option();
        $options=array_merge($general_options,$quality_options);

        CompressX_Image_Meta::update_image_progressing($attachment_id);

        $image=new Compressx_Image($attachment_id,$options);
        $file_path = get_attached_file( $attachment_id );
        if(empty($file_path))
        {
            $this->WriteLog('Image:'.$attachment_id.' failed. Error: failed to get get_attached_file','notice');

            $error='Image:'.$attachment_id.' failed. Error: failed to get get_attached_file';
            CompressX_Image_Meta::update_image_failed($attachment_id,$error);
            CompressX_Image_Meta::delete_image_progressing($attachment_id);
            CompressX_Image_Meta::update_image_meta_status($attachment_id,'failed');
            $ret['result']='success';
            return $ret;
        }

        if(!file_exists($file_path))
        {
            $this->WriteLog('Image:'.$attachment_id.' failed. Error: file not exists '.$file_path,'notice');

            $error='Image:'.$attachment_id.' failed. Error: file not exists '.$file_path;
            CompressX_Image_Meta::update_image_failed($attachment_id,$error);
            CompressX_Image_Meta::delete_image_progressing($attachment_id);
            CompressX_Image_Meta::update_image_meta_status($attachment_id,'failed');
            $ret['result']='success';
            return $ret;
        }

        $image->resize();

        if($image->convert())
        {
            CompressX_Image_Meta::delete_image_progressing($attachment_id);
            CompressX_Image_Meta::update_image_meta_status($attachment_id,'optimized');
            do_action('compressx_purge_cache');
            do_action('compressx_after_optimize_image',$attachment_id);
        }
        else
        {
            CompressX_Image_Meta::delete_image_progressing($attachment_id);
            CompressX_Image_Meta::update_image_meta_status($attachment_id,'failed');
        }

        $ret['result']='success';
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

    public function get_opt_single_image_progress()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        if(!isset($_POST['ids'])||!is_string($_POST['ids']))
        {
            die();
        }

        $ids=sanitize_text_field($_POST['ids']);
        $ids=json_decode($ids,true);

        $running=false;

        if(isset($_POST['page']))
        {
            $page=sanitize_text_field($_POST['page']);
        }
        else
        {
            $page='media';
        }

        foreach ($ids as $id)
        {
            if(!CompressX_Image_Meta::is_image_optimized($id))
            {
                if($this->is_image_progressing($id))
                {
                    $running=true;
                    break;
                }
            }
        }

        $ret['result']='success';
        if($running)
        {
            $ret['continue']=1;
            $ret['finished']=0;
        }
        else
        {
            $ret['continue']=0;
            $ret['finished']=1;
        }

        foreach ($ids as $id)
        {
            if($page=='edit')
            {
                $html='<h4>'.__('CompressX', 'compressx').'</h4>';
            }
            else
            {
                $html='';
            }

            if(!CompressX_Image_Meta::is_image_optimized($id))
            {
                if($this->is_image_progressing($id))
                {
                    $html.= "<a  class='cx-media-progressing button' data-id='{$id}'>".__('Converting...', 'compressx')."</a>";
                }
                else
                {
                    if($running)
                    {
                        $html.= "<a  class='cx-media button button-disabled' data-id='{$id}'>".__('Convert','compressx')."</a>";
                    }
                    else
                    {
                        $html.= "<a  class='cx-media button' data-id='{$id}'>".__('Convert','compressx')."</a>";
                    }

                    if($this->is_image_processing_failed($id))
                    {
                        $meta=CompressX_Image_Meta::get_image_meta($id);
                        foreach ($meta['size'] as $size_key => $size_data)
                        {
                            if(!empty($size_data['error']))
                            {
                                $html.='<p style="border-bottom:1px solid #D2D3D6;margin-top: 12px;"></p>';
                                $html.="<span>".esc_html($size_data['error'])."</span>";
                                break;
                            }
                        }
                    }
                }
            }
            else
            {
                $convert_size=CompressX_Image_Meta::get_webp_converted_size($id);
                $og_size=CompressX_Image_Meta::get_og_size($id);
                if($og_size>0)
                {
                    if($convert_size>0)
                    {
                        $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                    }
                    else if(CompressX_Image_Meta::is_webp_image($id))
                    {
                        $convert_size=CompressX_Image_Meta::get_compressed_size($id);
                        if($convert_size>0)
                        {
                            $webp_percent = round(100 - ($convert_size / $og_size) * 100, 2);
                        }
                        else
                        {
                            $webp_percent=0;
                        }
                    }
                    else if(CompressX_Image_Meta::is_avif_image($id))
                    {
                        $webp_percent=0;
                    }
                    else
                    {
                        $webp_percent=0;
                    }
                }
                else
                {
                    $webp_percent=0;
                }

                $avif_size=CompressX_Image_Meta::get_avif_converted_size($id);
                if($og_size>0)
                {
                    if($avif_size>0)
                    {
                        $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                    }
                    else if(CompressX_Image_Meta::is_avif_image($id))
                    {
                        $avif_size=CompressX_Image_Meta::get_compressed_size($id);
                        if($avif_size>0)
                        {
                            $avif_percent = round(100 - ($avif_size / $og_size) * 100, 2);
                        }
                        else
                        {
                            $avif_percent=0;
                        }
                    }
                    else
                    {
                        $avif_percent=0;
                    }
                }
                else
                {
                    $avif_percent=0;
                }

                $meta=CompressX_Image_Meta::get_image_meta($id);
                $thumbnail_counts=count($meta['size']);

                $html.='<ul>';
                $html.= '<li><span>'.__('Original','compressx').' : </span><strong>'.size_format($og_size,2).'</strong></li>';
                $html.= '<li><span>'.__('Webp','compressx').' : </span><strong>'.size_format($convert_size,2).'</strong><span> '.__('Saved','compressx').' : </span><strong>'.$webp_percent.'%</strong></li>';
                $html.= '<li><span>'.__('AVIF','compressx').' : </span><strong>'.size_format($avif_size,2).'</strong><span> '.__('Saved','compressx').' : </span><strong>'.$avif_percent.'%</strong></li>';
                $html.= '<li><span>'.__('Thumbnails generated','compressx').' : </span><strong>'.$thumbnail_counts.'</strong></li>';
                $html.="<li><a class='cx-media-delete button' data-id='".esc_attr($id)."'>".__('Delete','compressx')."</a>
<span class='compressx-dashicons-help compressx-tooltip'>
                                    <a href='#'><span class='dashicons dashicons-editor-help' style='padding-top: 3px;'></span></a>
                                    <div class='compressx-bottom'>
                                        <!-- The content you need -->
                                        <p>
                                            <span>".__('Delete the WebP and AVIF images generated by CompressX.','compressx')."</span><br>
                                        </p>
                                        <i></i> <!-- do not delete this line -->
                                    </div>
                                </span>
</li>";
                $html.='</ul>';
            }
            $ret[$id]['html']=$html;
        }

        echo wp_json_encode($ret);

        die();
    }

    public function is_image_processing_failed($post_id)
    {
        $status=CompressX_Image_Meta::get_image_meta_status($post_id);

        if(empty($status))
        {
            return false;
        }
        else
        {
            if($status=='failed')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function is_image_progressing($post_id)
    {
        $progressing=CompressX_Image_Meta::get_image_progressing($post_id);

        if(empty($progressing))
        {
            return false;
        }
        else
        {
            $current_time=time();
            if(($current_time-$progressing)>180)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }
}