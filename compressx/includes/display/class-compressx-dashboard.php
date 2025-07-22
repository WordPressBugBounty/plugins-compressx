<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Dashboard
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_set_general_settings', array($this, 'set_general_setting'));
        add_action('wp_ajax_compressx_save_others_setting', array($this, 'save_others_setting'));
        add_action('wp_ajax_compressx_save_size_setting', array($this, 'save_size_setting'));

        add_action('wp_ajax_compressx_get_custom_tree_dir', array($this, 'get_custom_tree_dir'));
        add_action('wp_ajax_compressx_add_exclude_folders', array($this, 'add_exclude_folders'));
        add_action('wp_ajax_compressx_add_exclude_folder', array($this, 'add_exclude_folder'));
        //
        add_action('wp_ajax_compressx_remove_exclude_folders', array($this, 'remove_exclude_folders'));

        add_action('wp_ajax_compressx_delete_files', array($this, 'delete_files'));
        //
        add_action('wp_ajax_compressx_update_overview', array($this, 'update_overview'));
        //
        add_action('wp_ajax_compressx_hide_notice', array($this, 'compressx_hide_notice'));
        add_action('wp_ajax_compressx_rating_dismiss', array($this, 'compressx_rating_dismiss'));

        add_action('compressx_output_review', array($this, 'output_review'));
        add_action('compressx_output_notice', array($this, 'output_notice'));
    }

    public function display()
    {
        ?>
        <div id="compressx-root">
            <div id="compressx-wrap">
                <?php
                $this->output_nav();
                $this->output_header();
                $this->output_notice();
                $this->output_review();

                if(apply_filters('compressx_current_user_can',true,'compressx-can-convert'))
                {
                    $this->output_bulk_and_settings();
                }

                if(apply_filters('compressx_current_user_can',true,'compressx-can-use-general-settings'))
                {
                    $this->output_others_settings();
                }

                if(apply_filters('compressx_current_user_can',true,'compressx-can-use-thumbnail-settings'))
                {
                    $this->output_thumbnail_settings();
                }

                if(apply_filters('compressx_current_user_can',true,'compressx-can-use-exclude'))
                {
                    $this->output_exclude();
                }

                if(apply_filters('compressx_current_user_can',true,'compressx-can-bulk-custom-convert'))
                {
                    do_action("cx_output_custom_bulk");
                }

                if(apply_filters('compressx_current_user_can',true,'compressx-can-delete'))
                {
                    $this->output_delete_images();
                }

                $this->output_footer();
                ?>
            </div>
        </div>
        <?php
    }

    public function output_review()
    {
        $show_review=CompressX_Options::get_option('compressx_show_review',false);
        if($show_review===false||$show_review==1)
        {
            $show_review=false;
        }

        if($show_review!==false&&$show_review<time())
        {
            CompressX_Options::update_option('compressx_show_review',true);
            $size=CompressX_Image_Method::get_opt_folder_size();
            $opt_size=size_format($size,2);
            ?>
            <section id="cx_rating_box" >
                <div class="compressx-container compressx-section">
                    <div class="compressx-notification" style="position: relative">
                        <div class="compressx-notification-5-star">
                            <span class="dashicons dashicons-thumbs-up" style="font-size: 3rem; text-shadow: 2px 2px #7CDA24;"></span>
                        </div>
                        <div style="padding:0 1rem 0 6rem;">
                            <h5 style="font-size: 0.9rem;">Compressx.io has successfully processed <span  style="color:#071c4d;"><strong><span id="cx_size_of_opt_images"><?php echo esc_html($opt_size)?></span></strong></span> of images, and it's <span style="color:#071c4d;"><strong>completely free</strong></span>.</h5>
                            <p><?php esc_html_e('If the plugin has helped you, perhaps you could leave us a nice review and give us 5 stars? It would mean a lot to us and would be very motivating!','compressx')?></p>
                            <div class="cx-rating">
                                <div><span id="cx_rating_btn" class="cx-rating-btn"><?php esc_html_e('Yes, let me give a nice review','compressx')?></span></div>
                                <div><span id="cx_rating_ask_me_later"><a href=""><?php esc_html_e('Ask me later','compressx')?></a></span></div>
                                <div><span id="cx_rating_already"><a href=""><?php esc_html_e('I already did:)','compressx')?></a></span></div>
                                <div><span id="cx_rating_dismiss"><a href=""><?php esc_html_e('Dismiss','compressx')?></a></span></div>
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <button id="cx_rating_close" type="button" class="notice-dismiss">
                            <span class="screen-reader-text">Dismiss this notice.</span>
                        </button>
                    </div>
                </div>
            </section>
            <?php
        }
        else
        {
            ?>
            <section id="cx_rating_box" style="display: none">
                <div class="compressx-container compressx-section">
                    <div class="compressx-notification" style="position: relative">
                        <div class="compressx-notification-5-star">
                            <span class="dashicons dashicons-thumbs-up" style="font-size: 3rem; text-shadow: 2px 2px #7CDA24;"></span>
                        </div>
                        <div style="padding:0 1rem 0 6rem;">
                            <h5 style="font-size: 0.9rem;">Compressx.io has successfully processed <span  style="color:#071c4d;"><strong><span id="cx_size_of_opt_images"></span></strong></span> of images, and it's <span style="color:#071c4d;"><strong>completely free</strong></span>.</h5>
                            <p><?php esc_html_e('If the plugin has helped you, perhaps you could leave us a nice review and give us 5 stars? It would mean a lot to us and would be very motivating!','compressx')?></p>
                            <div class="cx-rating">
                                <div><span id="cx_rating_btn" class="cx-rating-btn"><?php esc_html_e('Yes, let me give a nice review','compressx')?></span></div>
                                <div><span id="cx_rating_ask_me_later"><a href=""><?php esc_html_e('Ask me later','compressx')?></a></span></div>
                                <div><span id="cx_rating_already"><a href=""><?php esc_html_e('I already did:)','compressx')?></a></span></div>
                                <div><span id="cx_rating_dismiss"><a href=""><?php esc_html_e('Dismiss','compressx')?></a></span></div>
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                        <button id="cx_rating_close" type="button" class="notice-dismiss">
                            <span class="screen-reader-text">Dismiss this notice.</span>
                        </button>
                    </div>
                </div>
            </section>
            <?php
        }
        ?>

        <script>
            jQuery('#cx_rating_btn').click(function()
            {
                window.open('https://wordpress.org/support/plugin/compressx/reviews/?filter=5#new-post', '_blank');

                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value':'already'
                };
                compressx_post_request(ajax_data, function (data)
                {
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            });

            jQuery('#cx_rating_ask_me_later').click(function()
            {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value':'ask_me_later'
                };
                compressx_post_request(ajax_data, function (data)
                {
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            });

            jQuery('#cx_rating_already').click(function()
            {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value':'already'
                };
                compressx_post_request(ajax_data, function (data)
                {
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            });

            jQuery('#cx_rating_dismiss').click(function()
            {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value':'dismiss'
                };
                compressx_post_request(ajax_data, function (data)
                {
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            });

            jQuery('#cx_rating_close').click(function()
            {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value':'close'
                };
                compressx_post_request(ajax_data, function (data)
                {
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            });
        </script>
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

    public function output_notice()
    {
        if(!CompressX_Image_Method::is_support_gd()&&!CompressX_Image_Method::is_support_imagick())
        {
            $has_notice=true;
        }
        else
        {
            $has_notice=false;
        }

        $options=CompressX_Options::get_option('compressx_general_settings',array());
        $image_load=isset($options['image_load'])?$options['image_load']:'htaccess';
        if ($image_load == "htaccess")
        {
            include_once COMPRESSX_DIR . '/includes/class-compressx-rewrite-checker.php';
            $test=new CompressX_Rewrite_Checker();
            $result=$test->test();
        }
        else
        {
            $result=true;
        }

        if(!$result||$has_notice)
        {
            ?>
            <section id="cx_notice" style="display:block;">
                <div class="compressx-container compressx-section">
                    <div class="compressx-notification">
                        <?php
                        if(!CompressX_Image_Method::is_support_gd()&&!CompressX_Image_Method::is_support_imagick())
                        {
                            ?>
                            <p>
                                <span class="dashicons dashicons-warning" style="color:#FF3951;"></span>
                                <span><?php esc_html_e('Your server does not have GD or Imagick extension installed, images cannot be converted to WebP or AVIF on the website.Please install GD or Imagick PHP extension and restart the server service to convert images to WebP and AVIF.','compressx')?></span>
                            </p>
                            <?php

                        }

                        if(!$result)
                        {
                            if($test->is_active_cache())
                            {
                                ?>
                                <p><span class="dashicons dashicons-warning" style="color:#FF3951;"></span>
                                    <span><?php esc_html_e('We\'ve detected a cache plugin on the site which may be causing rewrite rules of CompressX to fail. Please clear website cache to ensure the rewrite rules take effect.','compressx')?></span>
                                </p>
                                <?php
                            }
                            else if($test->is_apache())
                            {
                                ?>
                                <p><span class="dashicons dashicons-warning" style="color:#FF3951;"></span>
                                    <span><?php echo wp_kses_post(__('.htaccess rewrite rules - we\'ve detected that .htaccess write rules are not executed on your Apache server, this can be because the server is not configured correctly for using .htaccess file from custom locations. For more details, please read this doc - <a href="https://compressx.io/docs/config-apache-htaccess-rules/">How-to: Config Apache htaccess Rules','compressx'))?></a></span>
                                </p>
                                <?php
                            }
                            else if($test->is_nginx())
                            {
                                ?>
                                <p><span class="dashicons dashicons-warning" style="color:#FF3951;"></span>
                                    <span><?php echo wp_kses_post(__('We’ve detected that you use Nginx server. Nginx server does not support .htaccess rewrite rules and needs additional configurations to work. For more details, please read this doc - <a href="https://compressx.io/docs/config-nginx-htaccess-rules/">How-to: Config Nginx htaccess Rules','compressx'))?></a></span>
                                </p>
                                <?php
                            }
                            else if($test->is_litespeed())
                            {
                                ?>
                                <p><span class="dashicons dashicons-warning" style="color:#FF3951;"></span>
                                    <span><?php esc_html_e('We\'ve detected that the server is LiteSpeed, which requires a service restart for rewrite rules to take effect. Please restart the Litespeed service to make CompressX rewrite rules effective.','compressx')?></a></span>
                                </p>
                                <?php
                            }
                            else
                            {
                                ?>
                                <p><span class="dashicons dashicons-warning" style="color:#FF3951;"></span>
                                    <span><?php esc_html_e('We’ve not detected an Apache or Nginx server. You may be using a different web server that we have not tested our plugin on.','compressx')?></span>
                                </p>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>
            <?php
        }

        do_action('compressx_notices');
    }

    public function output_overview()
    {
        $cached = get_transient("compressx_set_global_stats");

        if ($cached)
        {
            $cx_webp_percent=$cached['conversion_webp_percent'].'%';
            $cx_avif_percent=$cached['conversion_avif_percent'].'%';
        }
        else
        {
            $cx_webp_percent='0%';
            $cx_avif_percent='0%';
        }
        ?>
        <div class="cx-overview_body-free">
            <div class="cx-overview_body-webp-free">
                <div class="cx-process-webp">
                    <div class="cx-process-position">
                        <span id="cx_conversion_webp_percent" class="cx-processed" ><?php echo esc_html($cx_webp_percent)?><span class="cx-percent-sign"> images</span></span>
                        <span class="cx-processing"><?php esc_html_e('Outputted to WEBP','compressx')?></span>
                    </div>
                </div>
                <div class="cx-process-webp">
                    <div class="cx-process-position">
                        <span id="cx_conversion_avif_percent" class="cx-processed"><?php echo esc_html($cx_avif_percent)?><span class="cx-percent-sign"> images</span></span>
                        <span class="cx-processing"><?php esc_html_e('Outputted to AVIF','compressx')?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function update_overview()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        $stats=CompressX_Image_Meta::get_global_stats();

        $ret['result']='success';
        $ret['html'] =" <div class=\"cx-overview_body-free\">
            <div class=\"cx-overview_body-webp-free\">
                <div class=\"cx-process-webp\">
                    <div class=\"cx-process-position\">
                        <span class=\"cx-processed\">".$stats['webp_converted_percent']."%<span class=\"cx-percent-sign\"> images</span></span>
                        <span class=\"cx-processing\">Outputted to WEBP</span>
                    </div>
                </div>
                <div class=\"cx-process-webp\">
                    <div class=\"cx-process-position\">
                        <span class=\"cx-processed\">".$stats['avif_converted_percent']."%<span class=\"cx-percent-sign\"> images</span></span>
                        <span class=\"cx-processing\">Outputted to AVIF</span>
                    </div>
                </div>
            </div>
        </div>";
        $ret['webp_saved']=$stats['avif_saved_percent'].'%';
        $ret['avif_saved']=$stats['webp_saved_percent'].'%';
        //webp_saved
        //avif_saved
        echo wp_json_encode($ret);

        die();
    }

    public function compressx_hide_notice()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        CompressX_Options::update_option('compressx_hide_notice',true);

        die();
    }

    public function compressx_rating_dismiss()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        if(isset($_POST['value']))
        {
            $value=sanitize_text_field($_POST['value']);
            if($value=='ask_me_later')
            {
                $time=time()+259200;
                CompressX_Options::update_option('compressx_rating_dismiss',$time);
            }
            if($value=='close')
            {
                $time=time()+604800;
                CompressX_Options::update_option('compressx_rating_dismiss',$time);
            }
            else if($value=='already')
            {
                CompressX_Options::update_option('compressx_rating_dismiss',0);
            }
            else if($value=='dismiss')
            {
                CompressX_Options::update_option('compressx_rating_dismiss',0);
            }
        }

        die();
    }

    public function delete_files()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-delete');

        global $wpdb;

        $where['meta_key']="compressx_image_meta_status";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta_webp_converted";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta_avif_converted";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta_compressed";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta_og_file_size";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta_webp_converted_size";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta_avif_converted_size";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta_compressed_size";
        $wpdb->delete($wpdb->postmeta,$where);

        $where['meta_key']="compressx_image_meta";
        $wpdb->delete($wpdb->postmeta,$where);

        delete_transient('compressx_set_global_stats');

        $table_name = $wpdb->prefix . "compressx_files_opt_meta";
        $wpdb->get_results("TRUNCATE TABLE $table_name",ARRAY_A);

        $this->_delete_files();

        $ret['result']="success";
        echo wp_json_encode($ret);
        die();
    }

    public function _delete_files()
    {
        $path = WP_CONTENT_DIR.'/compressx-nextgen';
        $this->deleteDir($path);

        include_once COMPRESSX_DIR . '/includes/class-compressx-default-folder.php';
        $dir=new CompressX_default_folder();
        $dir->create_uploads_dir();
    }

    public function deleteDir($dirPath)
    {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException(esc_html("$dirPath must be a directory"));
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                wp_delete_file($file);
            }
        }
        @rmdir($dirPath);
    }

    public function output_bulk_and_settings()
    {
        ?>
        <header style="display: block;">
            <div class="compressx-container compressx-header">
                <div id="compressx_bulk_progress_2" class="compressx-bulk-process" style="display: none">
                    <article style="display: block;">
                        <p style="text-align: center;">
                            <strong>Do Not</strong> close or refresh the page when processing.
                        </p>
                        <div>
                            <div class="cx-stepper-wrapper">
                                <div id="compressx_bulk_progress_step1" class="cx-stepper-item">
                                    <div class="cx-step-counter">1</div>
                                    <div class="cx-step-name"><?php esc_html_e('Images Scanning','compressx')?></div>
                                </div>
                                <div id="compressx_bulk_progress_step2" class="cx-stepper-item">
                                    <div class="cx-step-counter">2</div>
                                    <div class="cx-step-name"><?php esc_html_e('Bulk Processing','compressx')?></div>
                                </div>
                                <div id="compressx_bulk_progress_step3" class="cx-stepper-item ">
                                    <div class="cx-step-counter">3</div>
                                    <div class="cx-step-name"><?php esc_html_e('Finished','compressx')?></div>
                                </div>
                            </div>
                        </div>
                        <div id="compressx_bulk_progress_part1" style="">
                            <span><?php esc_html_e('CompressX is processing the images. Keep the page open to finish the bulk action.','compressx')?> </span><br>
                            <div class="cx-process-bar">
                                <span id="compressx_bulk_progress_sub_text"></span>
                                <span class="cx-process-bar-100">
                                    <span class="cx-process-bar-percentage" id="compressx_bulk_progress_2_bar" style="width: 0%"></span>
                                </span>
                                <br>
                                <span id="compressx_bulk_progress_2_text">Processing...</span><br>
                            </div>
                        </div>
                        <div id="compressx_bulk_progress_part2" style="display: none;position: relative;border-top: 1px solid var(--color-grey);padding-top: 1rem;">
                            <span id="compressx_bulk_progress_2_notice"><?php esc_html_e('CompressX is processing the images. Keep the page open to finish the bulk action.','compressx')?> </span>
                            <input class="button-primary cx-button" id="compressx_close_progress" type="submit" value="<?php esc_attr_e('Close','compressx');?>" style="position: absolute;right:0; cursor:pointer">
                        </div>
                    </article>
                </div>
                <div class="compressx-header-block">
                    <?php $this->output_convert_setting();?>
                    <?php $this->output_bulk();?>
                </div>
            </div>
        </header>
        <script>
            jQuery('#cx_converter_method_gd').click(function()
            {
                var json = {};
                json['converter_method']='gd';
                var setting_data=JSON.stringify(json);

                var ajax_data = {
                    'action': 'compressx_set_general_settings',
                    'setting': setting_data,
                };
                compressx_post_request(ajax_data, function (data)
                {
                    var jsonarray = jQuery.parseJSON(data);

                    if (jsonarray.result === 'success')
                    {
                        if(jsonarray.disable_avif)
                        {
                            jQuery("#cx_convert_to_avif").prop("disabled", true);
                            jQuery("#cx_convert_to_avif").prop("checked", false);
                        }
                        else
                        {
                            jQuery("#cx_convert_to_avif").prop("disabled", false);
                            jQuery("#cx_convert_to_avif").prop("checked", jsonarray.check_avif);
                        }

                        if(jsonarray.disable_webp)
                        {
                            jQuery("#cx_convert_to_webp").prop("disabled", true);
                            jQuery("#cx_convert_to_webp").prop("checked", false);
                        }
                        else
                        {
                            jQuery("#cx_convert_to_webp").prop("disabled", false);
                            jQuery("#cx_convert_to_webp").prop("checked", jsonarray.check_webp);
                        }
                    }
                    jQuery('#cx_converter_method_text').removeClass("hidden");
                    setTimeout(function ()
                    {
                        jQuery('#cx_converter_method_text').addClass( 'hidden' );
                    }, 3000);
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            });

            jQuery('#cx_converter_method_imagick').click(function()
            {
                var json = {};
                json['converter_method']='imagick';
                var setting_data=JSON.stringify(json);

                var ajax_data = {
                    'action': 'compressx_set_general_settings',
                    'setting': setting_data,
                };
                compressx_post_request(ajax_data, function (data)
                {
                    var jsonarray = jQuery.parseJSON(data);

                    if (jsonarray.result === 'success')
                    {
                        if(jsonarray.disable_avif)
                        {
                            jQuery("#cx_convert_to_avif").prop("disabled", true);
                            jQuery("#cx_convert_to_avif").prop("checked", false);
                        }
                        else
                        {
                            jQuery("#cx_convert_to_avif").prop("disabled", false);
                            jQuery("#cx_convert_to_avif").prop("checked", jsonarray.check_avif);
                        }

                        if(jsonarray.disable_webp)
                        {
                            jQuery("#cx_convert_to_webp").prop("disabled", true);
                            jQuery("#cx_convert_to_webp").prop("checked", false);
                        }
                        else
                        {
                            jQuery("#cx_convert_to_webp").prop("disabled", false);
                            jQuery("#cx_convert_to_webp").prop("checked", jsonarray.check_webp);
                        }
                    }

                    jQuery('#cx_converter_method_text').removeClass("hidden");
                    setTimeout(function ()
                    {
                        jQuery('#cx_converter_method_text').addClass( 'hidden' );
                    }, 3000);
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                });
            });
        </script>
        <?php
        $this->output_custom_compression_setting();
    }

    public function output_convert_setting()
    {
        $is_auto=CompressX_Options::get_option('compressx_auto_optimize',false);

        if($is_auto)
        {
            $is_auto='checked';
        }
        else
        {
            $is_auto='';
        }

        $options=CompressX_Options::get_option('compressx_quality',array());
        $quality=isset($options['quality'])?$options['quality']:'lossy';

        if($quality=="lossless")
        {
            $quality_lossless="checked";
            $quality_lossy_minus="";
            $quality_lossy="";
            $quality_lossy_plus="";
            $quality_lossy_super="";
            $quality_custom="";
            $quality_custom_notice="display:none";
        }
        else if($quality=="lossy_minus")
        {
            $quality_lossless="";
            $quality_lossy_minus="checked";
            $quality_lossy="";
            $quality_lossy_plus="";
            $quality_lossy_super="";
            $quality_custom="";
            $quality_custom_notice="display:none";
        }
        else if($quality=="lossy")
        {
            $quality_lossless="";
            $quality_lossy_minus="";
            $quality_lossy="checked";
            $quality_lossy_plus="";
            $quality_lossy_super="";
            $quality_custom="";
            $quality_custom_notice="display:none";
        }
        else if($quality=="lossy_plus")
        {
            $quality_lossless="";
            $quality_lossy_minus="";
            $quality_lossy="";
            $quality_lossy_plus="checked";
            $quality_lossy_super="";
            $quality_custom="";
            $quality_custom_notice="display:none";
        }
        else if($quality=="lossy_super")
        {
            $quality_lossless="";
            $quality_lossy_minus="";
            $quality_lossy="";
            $quality_lossy_plus="";
            $quality_lossy_super="checked";
            $quality_custom="";
            $quality_custom_notice="display:none";
        }
        else
        {
            $quality_lossless="";
            $quality_lossy_minus="";
            $quality_lossy="";
            $quality_lossy_plus="";
            $quality_lossy_super="";
            $quality_custom="checked";
            $quality_custom_notice="";
        }

        if(CompressX_Image_Opt_Method::is_support_gd())
        {
            $is_support_gd="";
        }
        else
        {
            $is_support_gd="disabled";
        }

        if(CompressX_Image_Opt_Method::is_support_imagick())
        {
            $is_support_imagick="";
        }
        else
        {
            $is_support_imagick="disabled";
        }

        $converter_method=CompressX_Options::get_option('compressx_converter_method',false);

        if(empty($converter_method))
        {
            $converter_method=CompressX_Image_Opt_Method::set_default_compress_server();
        }

        if($converter_method=="gd")
        {
            $gd_checked="checked";
            $imagick_checked="";
        }
        else if($converter_method=="imagick")
        {
            $gd_checked="";
            $imagick_checked="checked";
        }
        else
        {
            $gd_checked="";
            $imagick_checked="";
        }

        $convert_to_webp=CompressX_Options::get_option('compressx_output_format_webp','not init');
        if($convert_to_webp==='not init')
        {
            $convert_to_webp=CompressX_Image_Opt_Method::set_default_output_format_webp();
        }

        $convert_to_avif=CompressX_Options::get_option('compressx_output_format_avif','not init');
        if($convert_to_avif==='not init')
        {
            $convert_to_avif=CompressX_Image_Opt_Method::set_default_output_format_avif();
        }
        
        if($convert_to_webp)
        {
            $convert_to_webp='checked';
        }
        else
        {
            $convert_to_webp='';
        }

        if($convert_to_avif)
        {
            $convert_to_avif='checked';
        }
        else
        {
            $convert_to_avif='';
        }


        if(CompressX_Image_Opt_Method::is_current_support_webp())
        {
            $webp_support='';
        }
        else
        {
            $convert_to_webp='';
            $webp_support='disabled';
        }
        
        if(CompressX_Image_Opt_Method::is_current_support_avif())
        {
            $avif_support='';
        }
        else
        {
            $convert_to_avif='';
            $avif_support='disabled';
        }

        $compression_level_url=admin_url(). 'admin.php?page=compression-level-compressx';

        $cached = get_transient("compressx_set_global_stats");

        if ($cached)
        {
            $cx_webp_saved=$cached['space_saved_webp_percent'].'%';
            $cx_avif_saved=$cached['space_saved_avif_percent'].'%';
        }
        else
        {
            $cx_webp_saved='0%';
            $cx_avif_saved='0%';
        }
        ?>
        <article>
            <div class="compressx-general-settings-body-grid" style="border-bottom:1px solid #ddd;padding-bottom:0.8rem;">
                <div class="compress-new-images" >
                    <label class="cx-switch">
                        <input type="checkbox" <?php echo esc_attr($is_auto); ?> id="cx_enable_auto_optimize">
                        <span class="cx-slider cx-round"></span>
                    </label><span style="padding-left:1rem;"><?php esc_html_e('Enable it to convert the new uploaded images.','compressx')?></span>
                </div>
                <div style="padding:0.4rem;">
                    <span><span>Total Savings: </span><span>AVIF: </span><span id="cx_avif_saved"><?php echo esc_html($cx_avif_saved); ?></span><span style="padding: 0 0.2rem;">|</span>
                    <span>Webp: </span><span id="cx_webp_saved"><?php echo esc_html($cx_webp_saved); ?></span></span>
                </div>
            </div>
            <div class="compressx-general-settings-body-grid">
                <div>
                    <div class="compressx-general-settings-body">
                        <div class="cx-title">
                            <span><strong><?php esc_html_e('Library to Process Images','compressx')?></strong></span>
                            <span class="compressx-dashicons-help compressx-tooltip">
                                            <a href="#"><span class="dashicons dashicons-editor-help"></span></a>
                                            <div class="compressx-bottom">
                                                <!-- The content you need -->
                                                <p>
                                                    <span><?php esc_html_e('Choose the PHP extension to process images.','compressx')?></span><br>
                                                    <span><?php esc_html_e('GD is a PHP extension for handling image optimization.It may be slightly faster at processing large images but supports fewer image formats','compressx')?></span><br>
                                                    <span><?php esc_html_e('Imagick is another image optimization library that supports more image formats and produces higher quality images.','compressx')?></span>
                                                </p>
                                                <i></i> <!-- do not delete this line -->
                                            </div>
                                        </span>
                            <a href="<?php echo esc_url( admin_url("admin.php")."?page=info-compressx&check_environment" ); ?>"><?php esc_html_e('Check Environment','compressx')?></a>
                        </div>
                        <div class="cx-library-select">
                                        <span>
                                            <input id="cx_converter_method_gd" name="converter_method" type="radio" value="gd" <?php echo esc_attr($gd_checked); ?> <?php echo esc_attr($is_support_gd); ?>>
                                        </span>
                            <span style="padding-right: 0.5rem;"><strong>GD</strong></span>
                            <span>
                                            <span>
                                                <input id="cx_converter_method_imagick" name="converter_method" type="radio" value="imagick" <?php echo esc_attr($imagick_checked); ?> <?php echo esc_attr($is_support_imagick); ?>>
                                            </span>
                                            <span style="padding-right: 0.5rem;"><strong>Imagick</strong></span>
                                        </span>
                            <span id="cx_converter_method_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!','compressx')?></span>
                        </div>
                        <div class="cx-title">
                            <span><strong><?php esc_html_e('Output Formats: ','compressx')?></strong></span><span style="padding:0 0.5rem"></span>
                            <span>
                                            <span>
                                                <input id="cx_convert_to_webp" type="checkbox" <?php echo esc_attr($convert_to_webp); ?> <?php echo esc_attr($webp_support); ?> >
                                            </span>
                                            <span><strong>Webp</strong></span>
                                        </span>
                            <span style="padding:0 0.2rem;"></span>
                            <span>
                                            <span>
                                                <input id="cx_convert_to_avif" type="checkbox" <?php echo esc_attr($convert_to_avif); ?> <?php echo esc_attr($avif_support); ?> >
                                            </span>
                                            <span><strong>AVIF</strong></span>
                                        </span>
                            <span class="compressx-dashicons-help compressx-tooltip">
                                            <a href="#"><span class="dashicons dashicons-editor-help"></span></a>
                                            <div class="compressx-bottom">
                                                <!-- The content you need -->
                                                <p>
                                                    <span><?php esc_html_e('Convert .jpg and .png images to WebP or/and AVIF format.','compressx')?></span><br>
                                                    <span><?php esc_html_e('If the original image is a WebP image, it will be converted to AVIF (if checked) and compressed.','compressx')?></span><br>
                                                    <span><?php esc_html_e('If the original image is an AVIF image, it will be compressed.','compressx')?></span>
                                                </p>
                                                <i></i> <!-- do not delete this line -->
                                            </div>
                                        </span>
                            <span id="cx_save_convert_format" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!','compressx')?></span>
                        </div>
                    </div>
                    <div class="compressx-general-settings-body">
                        <div class="cx-title">
                            <span>
                                <strong>Watermark</strong>
                            </span>
                            <span style="padding:0 0.1rem"></span>
                            <span>
                                <a href="https://compressx.io">(Pro only)</a>
                            </span>
                            <span class="compressx-dashicons-help compressx-tooltip">
                                <a href="#"><span class="dashicons dashicons-editor-help"></span></a>
                                <div class="compressx-bottom">
                                    <!-- The content you need -->
                                    <p>
                                        <span>Enable this option to automatically tag newly uploaded images, making them ready for batch watermarking. This feature allows you to easily identify and process recent uploads for watermark application, streamlining your image protection process.</span>
                                    </p>
                                    <i></i> <!-- do not delete this line -->
                                </div>
                            </span>
                            <p>
                                <span>
                                    <label class="compressx-switch" title="">
                                        <input type="checkbox" disabled>
                                        <span class="compressx-slider compressx-round"></span>
                                    </label>
                                    <span style="padding:0 0.2rem;"></span>
                                    <span>Mark New Uploads for Watermarking</span>
                                     <span style="padding:0 0.1rem"></span>
                                    <span><a href="https://compressx.io">(Pro only)</a></span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="compressx-general-settings-body" style="margin-bottom: 0;">
                        <div class="cx-title">
                            <span><strong><?php esc_html_e('Compression Level','compressx')?></strong></span>
                            <span class="compressx-dashicons-help compressx-tooltip">
                                <a href="#"><span class="dashicons dashicons-editor-help"></span></a>
                                <div class="compressx-bottom">
                                    <!-- The content you need -->
                                    <p>
                                        <span>Choose the most appropriate compression level. The 5 compression levels are increasing from lossless to lossy. The <strong>default level</strong> is the Thumb, which is suitable for most situations. You can customize the compression level for WebP and AVIF formats.</span><br>
                                        <span><strong>Lossless: </strong>A compression level of 99</span><br>
                                        <span><span class="dashicons dashicons-arrow-left-alt"></span>: A compression level of 90</span><br>
                                        <span><span class="dashicons dashicons-thumbs-up"></span>: The default level. A compression level of 80</span><br>
                                        <span><span class="dashicons dashicons-arrow-right-alt"></span>: A compression level of 70</span><br>
                                        <span><strong>Lossy: </strong>A compression level of 60</span><br>
                                        <span><strong>Custom: </strong>Customize the compression level</span>
                                    </p>
                                    <i></i> <!-- do not delete this line -->
                                </div>
                            </span>
                        </div>
                        <div class="compressx-general-settings-body" style="margin-bottom: 0;">
                            <div class="cx-radio-toolbar" style="padding-left: 0.5rem;">
                                <input type="radio" id="cx_radioLossless" option="setting" name="quality" value="lossless" <?php echo esc_attr($quality_lossless); ?>>
                                <label for="cx_radioLossless">Lossless</label>

                                <input type="radio" id="cx_radioLossMinus" option="setting" name="quality" value="lossy_minus" <?php echo esc_attr($quality_lossy_minus); ?>>
                                <label for="cx_radioLossMinus"><span class="dashicons dashicons-arrow-left-alt"></span></label>

                                <input type="radio" id="cx_radioLossy" option="setting" name="quality" value="lossy" <?php echo esc_attr($quality_lossy); ?>>
                                <label for="cx_radioLossy"><span class="dashicons dashicons-thumbs-up"></span></label>

                                <input type="radio" id="cx_radioLossyPlus" option="setting" name="quality" value="lossy_plus" <?php echo esc_attr($quality_lossy_plus); ?>>
                                <label for="cx_radioLossyPlus"><span class="dashicons dashicons-arrow-right-alt"></span></label>

                                <input type="radio" id="cx_radioLossySuper" option="setting" name="quality" value="lossy_super" <?php echo esc_attr($quality_lossy_super); ?>>
                                <label for="cx_radioLossySuper">Lossy</label>

                                <input type="radio" id="cx_radioCustom" option="setting" name="quality" value="custom" <?php echo esc_attr($quality_custom); ?> >
                                <label for="cx_radioCustom">Custom</label>

                                <span id="cx_save_compression_level" class="success hidden" aria-hidden="true" style="color:#007017">Saved!</span>
                            </div>
                            <!--p id="cx_custom_notice" style="padding-left:0.5rem;<?php //echo esc_attr($quality_custom_notice); ?>">
                                <a href="<?php //echo esc_url( $compression_level_url);?>">Configure <strong>Advanced Custom Compression Level</strong> settings</a>
                            </p-->
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <?php
    }

    public function output_custom_compression_setting()
    {
        $options=CompressX_Options::get_option('compressx_quality',array());

        $quality=isset($options['quality'])?$options['quality']:'lossy';
        if($quality=="custom")
        {
            $quality_custom_style="";
        }
        else
        {
            $quality_custom_style="display:none";
        }

        $quality_webp=isset($options['quality_webp'])?$options['quality_webp']: 80;
        $quality_avif=isset($options['quality_avif'])?$options['quality_avif']: 60;
        ?>
        <section id="cx_compressing_strategy_custom" style="<?php echo esc_attr($quality_custom_style); ?>" >
            <div class="compressx-container compressx-section ">
                <div class="compressx-general-settings">
                    <div class="compressx-general-settings-header" style="position: relative;">
                        <span>
                            <span id="cx_close_custom_compression" class="dashicons dashicons-no-alt" style="position: absolute; top:0.5rem;;right:0; cursor:pointer"></span>
                        </span>
                        <h5><span><?php esc_html_e('Custom Compression Level','compressx')?></span></h5>
                    </div>
                    <div class="compressx-general-settings-body">
                        <div class="compressx-general-settings-body-grid">
                            <div>
                                <span>
                                    <input type="radio" checked><span><strong>Global compression level</strong></span>
                                </span>
                                <p style="padding-left: 2rem;">
                                    <span>Webp<input type="text" style="width: 2.5rem;" id="cx_quality_webp" value="<?php echo esc_attr($quality_webp); ?>"></span>
                                    <span style="padding: 0 0.5rem;"></span>
                                    <span>AVIF<input type="text" style="width: 2.5rem;" id="cx_quality_avif" value="<?php echo esc_attr($quality_avif); ?>"></span>
                                </p>
                                <p style="padding-left: 2rem;"><a href="<?php echo esc_url( admin_url("admin.php")."?page=addons-compressx" ); ?>">Advanced compression level</a></p>
                            </div>
                            <div class="compressing-converting">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('Customize the compression level, a value of 100 means lossless compression, less than 100 means lossy compression. The recommended/default value is 80 for WebP and 60 for AVIF.','compressx')?></span><br>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="compressx-general-settings-footer">
                        <input class="button-primary cx-button" id="cx_save_custom_quality" type="submit" value="<?php esc_attr_e('Save and Close','compressx'); ?>">
                        <span style="padding:0 0.2rem;"></span>
                        <span id="cx_save_custom_quality_progress" style="display: none">
                            <img src="../wp-admin/images/loading.gif" alt="">
                        </span>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    public function output_bulk()
    {
        ?>
        <article class="cx-overview">
            <div class="cx-title">
                <span>
                    <input class="button-primary cx-button" id="compressx_start_bulk_optimization" type="submit" value="<?php esc_attr_e('Start Bulk Processing','compressx'); ?>">
                    <input class="button-primary cx-button" style="display: none" id="compressx_cancel_bulk_optimization" type="submit" value="<?php esc_attr_e('Cancel Processing','compressx'); ?>">
                    <span class="compressx-dashicons-help compressx-tooltip" >
                        <a href="#"><span class="dashicons dashicons-editor-help" style="padding-top: 0.2rem;"></span></a>
                        <div class="compressx-bottom">
                            <!-- The content you need -->
                            <p>
                                <?php esc_html_e('Process images only in the WordPress media library (Uploads) in bulk.','compressx')?>
                            </p>
                            <i></i> <!-- do not delete this line -->
                        </div>
                    </span>
                </span>
            </div>
            <div id="cx_overview" >
                <?php $this->output_overview();?>
            </div>
            <div class="cx-overview_footer">
                <span><input type="checkbox" disabled>Process images in the background.</span>
                <span style="padding:0 0.1rem"></span>
                <span><a href="https://compressx.io">(Pro Only)</a></span><br>
                <span><input id="cx_force_optimization" type="checkbox"><?php esc_html_e('Force all images to be re-processed.','compressx')?></span>
            </div>
        </article>
        <?php
    }

    public function output_thumbnail_settings()
    {
        $options=CompressX_Options::get_option('compressx_general_settings',array());

        global $_wp_additional_image_sizes;
        $intermediate_image_sizes = get_intermediate_image_sizes();
        $image_sizes=array();
        $image_sizes[ 'og' ]['skip']=isset($options['skip_size']['og'])?$options['skip_size']['og']:false;

        foreach ( $intermediate_image_sizes as $size_key )
        {
            if ( in_array( $size_key, array( 'thumbnail', 'medium', 'large' ), true ) )
            {
                $image_sizes[ $size_key ]['width']  = get_option( $size_key . '_size_w' );
                $image_sizes[ $size_key ]['height'] = get_option( $size_key . '_size_h' );
                $image_sizes[ $size_key ]['crop']   = (bool) get_option( $size_key . '_crop' );
                if(isset($options['skip_size'][$size_key])&&$options['skip_size'][$size_key])
                {
                    $image_sizes[ $size_key ]['skip']=true;
                }
                else
                {
                    $image_sizes[ $size_key ]['skip']=false;
                }
            }
            else if ( isset( $_wp_additional_image_sizes[ $size_key ] ) )
            {
                $image_sizes[ $size_key ] = array(
                    'width'  => $_wp_additional_image_sizes[ $size_key ]['width'],
                    'height' => $_wp_additional_image_sizes[ $size_key ]['height'],
                    'crop'   => $_wp_additional_image_sizes[ $size_key ]['crop'],
                );
                if(isset($options['skip_size'][$size_key])&&$options['skip_size'][$size_key])
                {
                    $image_sizes[ $size_key ]['skip']=true;
                }
                else
                {
                    $image_sizes[ $size_key ]['skip']=false;
                }
            }
        }

        if ( ! isset( $sizes['medium_large'] ) || empty( $sizes['medium_large'] ) )
        {
            $width  = intval( get_option( 'medium_large_size_w' ) );
            $height = intval( get_option( 'medium_large_size_h' ) );

            $image_sizes['medium_large'] = array(
                'width'  => $width,
                'height' => $height,
            );

            if(isset($options['skip_size']['medium_large'])&&$options['skip_size']['medium_large'])
            {
                $image_sizes[ 'medium_large' ]['skip']=true;
            }
            else
            {
                $image_sizes[ 'medium_large' ]['skip']=false;
            }
        }

        ?>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-general-settings">
                    <div class="compressx-general-settings-header">
                        <h5><?php esc_html_e('Choose the thumbnail sizes you want to process.','compressx')?></h5>
                    </div>
                    <div class="compressx-general-settings-body">
                        <div class="compressx-general-settings-body-grid">
                            <div class="others">
                                <?php
                                $max_show=3;
                                $showed=0;
                                $show_more=false;
                                foreach ($image_sizes as $size_key=>$size)
                                {
                                    if($showed>$max_show)
                                    {
                                        $style="display:none";
                                        if(!$show_more)
                                        {
                                            $show_more=true;
                                            ?>
                                            <span id="cx_show_more_size"><a style="cursor: pointer">More...</a></span>
                                            <?php
                                        }
                                    }
                                    else
                                    {
                                        $style="";
                                    }
                                    ?>
                                <span class="cx-thumbnail-size" style="<?php echo esc_attr($style);?>">
                                    <?php
                                    if($size['skip'])
                                    {
                                        $checked='';
                                    }
                                    else
                                    {
                                        $checked='checked';
                                    }

                                    if($size_key=='og')
                                    {
                                        $text='Original image';
                                    }
                                    else
                                    {
                                        $text=$size_key.' ('.$size['width'].'x'.$size['height'].')';
                                    }

                                    ?>
                                    <input type="checkbox" option="size_setting" name="<?php echo esc_attr($size_key);?>" <?php echo esc_attr($checked);?> ><?php echo esc_html($text)?>
                                </span>
                                    <?php
                                    $showed++;
                                }
                                ?>
                            </div>
                            <div class="compressx-lazyload">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('Choose thumbnail sizes you want to process. Some themes may generate new thumbnail sizes sometimes, then you\'ll need to reprocess the thumbnails sizes using the Bulk Processing function.','compressx')?></span><br>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="compressx-general-settings-footer">
                        <input class="button-primary cx-button" id="compressx_save_size" type="submit" value="<?php esc_attr_e('Save Changes','compressx'); ?>">
                        <span style="padding:0 0.2rem;"></span>
                        <span id="compressx_save_size_progress" style="display: none">
                            <img src="../wp-admin/images/loading.gif" alt="">
                        </span>
                        <span id="compressx_save_size_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!','compressx')?></span>

                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    public function output_delete_images()
    {
        ?>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-general-settings" style="padding-bottom: 0;">
                    <div class="compressx-general-settings-header">
                        <h5><?php esc_html_e('Delete Images Generated by CompressX','compressx')?></h5>
                    </div>
                    <div class="compressx-general-settings-body">
                        <div class="compressx-general-settings-body-grid" style="margin-bottom: 1rem;">
                            <div>
                                <p>
                                    <span>
                                        <input type="text" id="cx_confirm_delete_file">
                                        <input class="button-primary cx-button" id="cx_delete_file" type="submit" value="Go">
                                    </span>
                                    <span id="cx_delete_file_success" class="success hidden" aria-hidden="true" style="color:#007017">Deleted!</span>
                                </p>
                                <span>Enter the text <strong>Delete</strong> and click Go to confirm the deletion.</span>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('All WebP and AVIF images and data in the database generated by CompressX will be deleted. Please proceed with caution. Original images will not be affected.','compressx')?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    public function output_others_settings()
    {
        $options=CompressX_Options::get_option('compressx_general_settings',array());

        if(isset($options['resize']))
        {
            $resize=$options['resize']['enable'];
            $resize_width=$options['resize']['width'];
            $resize_height=$options['resize']['height'];
        }
        else
        {
            $resize=true;
            $resize_width=2560;
            $resize_height=2560;
        }

        if($resize)
        {
            $resize='checked';
        }
        else
        {
            $resize='';
        }

        $options=CompressX_Options::get_option('compressx_general_settings',array());

        $remove_exif=isset($options['remove_exif'])?$options['remove_exif']:false;

        if($remove_exif)
        {
            $remove_exif='checked';
        }
        else
        {
            $remove_exif='';
        }

        $exclude_png=isset($options['exclude_png'])?$options['exclude_png']:false;

        if($exclude_png)
        {
            $exclude_png='checked';
        }
        else
        {
            $exclude_png='';
        }

        $exclude_png_webp=isset($options['exclude_png_webp'])?$options['exclude_png_webp']:false;

        if($exclude_png_webp)
        {
            $exclude_png_webp='checked';
        }
        else
        {
            $exclude_png_webp='';
        }

        $auto_remove_larger_format=isset($options['auto_remove_larger_format'])?$options['auto_remove_larger_format']:true;

        if($auto_remove_larger_format)
        {
            $auto_remove_larger_format='checked';
        }
        else
        {
            $auto_remove_larger_format='';
        }

        if(isset($options['image_load']))
        {
            if($options['image_load']=="htaccess")
            {
                $htaccess="checked";
                $picture="";
                $compat_htaccess="";
            }
            else if($options['image_load']=="compat_htaccess")
            {
                $htaccess="";
                $picture="";
                $compat_htaccess="checked";
            }
            else
            {
                $htaccess="";
                $picture="checked";
                $compat_htaccess="";
            }
        }
        else
        {
            $htaccess="checked";
            $picture="";
            $compat_htaccess="";
        }

        $converter_images_pre_request=isset($options['converter_images_pre_request'])?$options['converter_images_pre_request']:5;

        $disable_cache_control=isset($options['disable_cache_control'])?$options['disable_cache_control']:false;

        if($disable_cache_control)
        {
            $disable_cache_control='checked';
        }
        else
        {
            $disable_cache_control='';
        }
        ?>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-general-settings">
                    <div class="compressx-general-settings-header">
                        <h5><?php esc_html_e('General Settings','compressx')?></h5>
                    </div>
                    <div class="compressx-general-settings-body">
                        <div class="compressx-general-settings-body-grid compressx-general-settings-body-grid-768" style="margin-bottom: 1rem;">
                            <div>
                                <div class="cx-title">
                                    <span><strong><?php esc_html_e('Browser compatibility','compressx')?></strong></span>
                                </div>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="radio" option="others_setting" name="image_load" value="htaccess" <?php echo esc_attr($htaccess); ?> ><?php esc_html_e('Use rewrite rule','compressx')?>
                                    </span>
                                </p>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="radio" option="others_setting" name="image_load" value="compat_htaccess" <?php echo esc_attr($compat_htaccess); ?> ><?php esc_html_e('Compatible Rewrite Rule (Beta)','compressx')?>
                                    </span>
                                </p>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="radio" option="others_setting" name="image_load" value="picture" <?php echo esc_attr($picture); ?> ><?php esc_html_e('Use picture tag','compressx')?>
                                    </span>
                                </p>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('Rewrite rule:Load WebP and AVIF images by adding rewrite rules to the .htaccess file. So if the browser supports AVIF, AVIF images will be loaded. If AVIF is not supported, WebP images will be loaded. If both formats are not supported, the original .jpg and .png images will be loaded if any.The \'.htaccess\' refers to \'/wp-content/.htaccess\'.','compressx')?></span>
                                    <p>
                                        <span><?php esc_html_e('Compatible Rewrite Rule (Beta): An alternative set of rewrite rules for broader server compatibility. Try it when the standard "Rewrite Rule" fails.','compressx')?></span>
                                    </p>
                                    <p>
                                        <span><?php esc_html_e('Picture tag: Load WebP and AVIF images by replacing <img> tags with <picture> tags. You can use it when .htaccess can not take effect on your server. For example, if you are not able to restart an OpenLiteSpeed server which is required for .htaccess to take effect. This method works for most browsers but does not support images in CSS.','compressx')?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="compressx-general-settings-body-grid compressx-general-settings-body-grid-768" style="margin-bottom: 1rem;">
                            <div>
                                <div class="cx-title">
                                    <span><strong><?php esc_html_e('Do not convert PNG images','compressx')?></strong></span>
                                </div>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="checkbox" option="others_setting" name="exclude_png_webp" <?php echo esc_attr($exclude_png_webp); ?> ><?php esc_html_e('Do not convert PNG to WebP format','compressx')?>
                                    </span>
                                </p>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="checkbox" option="others_setting" name="exclude_png" <?php echo esc_attr($exclude_png); ?> ><?php esc_html_e('Do not convert PNG to AVIF format','compressx')?>
                                    </span>
                                </p>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span>You may use the options when:</span><br>
                                    <span>1. Your image library is ImageMagick 6.x. PNG images may lose transparent background when being converted to AVIF with ImageMagick 6.x. Then you can choose not to convert PNG to AVIF. A higher ImageMagick version like 7 does not have the issue.</span><br>
                                    <span>2. You don't want to convert PNG to WebP or AVIF because you want to keep the transparent background.</span>
                                </div>
                            </div>
                        </div>
                        <div class="compressx-general-settings-body-grid compressx-general-settings-body-grid-768" style="margin-bottom: 1rem;">
                            <div>
                                <div class="cx-title">
                                    <span><strong><?php esc_html_e('Remove EXIF data','compressx')?></strong></span>
                                </div>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="checkbox" option="others_setting" name="remove_exif" <?php echo esc_attr($remove_exif); ?> ><?php esc_html_e('Remove EXIF data(unavailable for the GD conversion method)','compressx')?>
                                    </span>
                                </p>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('Remove metadata recorded in images (Only supported by Imagick), including geolocation,timestamps, authorship, image summary, etc. This helps to protect your privacy.','compressx')?></span>
                                </div>
                            </div>
                        </div>
                        <div class="compressx-general-settings-body-grid compressx-general-settings-body-grid-768" style="margin-bottom: 1rem;">
                            <div>
                                <div class="cx-title">
                                    <span><strong><?php esc_html_e('Parameters of processing images','compressx')?></strong></span>
                                </div>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <select id="cx_converter_images_pre_request" option="others_setting" name="converter_images_pre_request">
                                            <option value="1">1</option>
                                            <option value="3">3</option>
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                        </select>
                                    </span>
                                    <span> <?php esc_html_e('images processed per ajax request.','compressx')?></span>
                                </p>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span>
                                        <span><?php esc_html_e('This value indicates how many WordPress image attachments (including original images and thumbnails) can be processed in one AJAX cycle. For example, if the value is set to 1, the plugin will process 1 attachment, which may include 1 original image and 20 thumbnails. Typically, web hosting services allow an AJAX execution time of 120 seconds, during which 3 image attachments can be processed, equating to 3 original images and 60 thumbnails. The default value is set to 5.','compressx')?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="compressx-general-settings-body-grid compressx-general-settings-body-grid-768" style="margin-bottom: 1rem;">
                            <div>
                                <div class="cx-title">
                                    <span><strong><?php esc_html_e('Automatic removal of files in output formats larger than the original ones','compressx')?></strong></span>
                                </div>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="checkbox" option="others_setting" name="auto_remove_larger_format" <?php echo esc_attr($auto_remove_larger_format); ?> ><?php esc_html_e('Automatic removal of files in output formats larger than the original ones','compressx')?>
                                    </span>
                                </p>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('Auto-delete larger AVIF/WebP images Automatically delete AVIF/WebP images when they are larger than the original images.','compressx')?></span>
                                </div>
                            </div>
                        </div>
                        <div class="compressx-general-settings-body-grid compressx-general-settings-body-grid-768" style="margin-bottom: 1rem;">
                            <div>
                                <div class="cx-title">
                                    <span><strong><?php esc_html_e('Auto-resizing large images','compressx')?></strong></span>
                                </div>
                                <div class="width">
                                    <p style="padding-left: 1rem;">
                                        <input type="checkbox" option="others_setting" name="resize" <?php echo esc_attr($resize); ?>>
                                        <strong><?php esc_html_e('Enable auto-resizing large images','compressx')?></strong>
                                    </p>
                                    <span><?php esc_html_e('Maximum Width:','compressx')?> <input type="text" option="others_setting" name="resize_width" value="<?php echo esc_attr($resize_width); ?>" style="width: 100px;" onkeyup="value=value.replace(/\D/g,'')">px</span>
                                    <span><?php esc_html_e('Maximum Height:','compressx')?><input type="text" option="others_setting" name="resize_height" value="<?php echo esc_attr($resize_height); ?>" style="width: 100px;" onkeyup="value=value.replace(/\D/g,'')">px</span>
                                </div>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('This option allows you to enter a width and height, so large images will be proportionately resized upon upload. For example, if you set 1280 px for the width, all large images will be resized in proportion to 1280 px in width upon upload.','compressx')?></span>
                                </div>
                            </div>
                        </div>
                        <div class="compressx-general-settings-body-grid compressx-general-settings-body-grid-768" style="margin-bottom: 1rem;">
                            <div>
                                <div class="cx-title">
                                    <span><strong><?php esc_html_e('Remove \'Header always set Cache-Control "private"\' from .htaccess file.','compressx')?></strong></span>
                                </div>
                                <p style="padding-left: 1rem;">
                                    <span>
                                        <input type="checkbox" option="others_setting" name="disable_cache_control" <?php echo esc_attr($disable_cache_control); ?> ><?php esc_html_e('Remove \'Header always set Cache-Control "private"\' from .htaccess file,the \'.htaccess\' refers to \'/wp-content/.htaccess\'','compressx')?>
                                    </span>
                                </p>
                            </div>
                            <div class="compressing-converting" style="margin-top: 0rem;">
                                <div class="compressing-converting-information">
                                    <span><?php esc_html_e('You can try to check this option if the site cannot be cached by Cloudflare.','compressx')?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="compressx-general-settings-footer">
                        <input class="button-primary cx-button" id="compressx_save_others" type="submit" value="<?php esc_attr_e('Save Changes','compressx'); ?>">
                        <span style="padding:0 0.2rem;"></span>
                        <span id="compressx_save_others_progress" style="display: none">
                            <img src="../wp-admin/images/loading.gif" alt="">
                        </span>
                        <span id="compressx_save_others_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!','compressx')?></span>
                    </div>
                </div>
            </div>
        </section>
        <script>
            jQuery(document).ready(function (){
                jQuery('#cx_converter_images_pre_request').val("<?php echo esc_attr($converter_images_pre_request); ?>");
            });
        </script>
        <?php
    }

    public function output_exclude()
    {
        $excludes=CompressX_Options::get_option('compressx_media_excludes',array());

        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));
        ?>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-general-settings" style="padding-bottom: 0;">
                    <div class="compressx-general-settings-header">
                        <h5><?php esc_html_e('Exclude Folders','compressx')?></h5>
                    </div>

                    <div class="compressing-converting-information" style="margin-top: 1rem;">
                        <?php esc_html_e('Select folders in the Uploads folder (media library) and exclude them from the processing.','compressx')?>
                    </div>
                    <!-- advanced block -->
                    <div class="compressx-general-settings-body">
                        <div class="cx-mediafolder-rules">
                            <div class="cx-mediafolders">
                                <span>
                                    <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span><span><?php esc_html_e('Media library(Uploads):','compressx')?></span>
                                </span>
                                <div class="cx-upload-treeviewer" id="compressx_exclude_js_tree">
                                </div>
                            </div>
                            <div class="cx-mediafolder-included">
                                <span>
                                    <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                                    <span><strong><?php esc_html_e('Excluded Folders:','compressx')?> </strong>Media files inside these folders will <strong>NOT</strong> be processed</span>
                                </span>
                                <div class="cx-mediafolder-list" id="compressx_exclude_dir_node">
                                    <ul>
                                        <?php

                                        foreach ($excludes as $exclude)
                                        {
                                            $path=str_replace($abs_path,'',$exclude);
                                            ?>
                                            <li>
                                                <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                                                <span><?php echo esc_html($path.',('.$this->get_children_count($exclude).') images')?></span>
                                                <span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-exclude-tree" data-id="<?php echo esc_attr($exclude)?>"></span>
                                            </li>
                                            <?php
                                        }

                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div>
                            <input type="submit" id="compressx_add_exclude_folders" class="button-primary cx-button" value="<?php esc_attr_e('Save Changes','compressx'); ?>">
                            <span style="padding:0 0.2rem;"></span>
                            <span id="compressx_save_exclude_progress" style="display: none">
                                <img src="../wp-admin/images/loading.gif" alt="">
                            </span>
                            <span id="compressx_save_exclude_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!','compressx')?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }

    public function set_general_setting()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-general-settings');

        if(isset($_POST['setting'])&&!empty($_POST['setting']))
        {
            $json_setting = sanitize_text_field($_POST['setting']);
            $json_setting = stripslashes($json_setting);
            $setting = json_decode($json_setting, true);
            if (is_null($setting))
            {
                $ret['result']='failed';
                $ret['error']='json decode failed';
                echo wp_json_encode($ret);
                die();
            }

            if(isset($setting['auto_optimize']))
            {
                if($setting['auto_optimize']=='1')
                {
                    $options=true;
                }
                else
                {
                    $options=false;
                }

                if(CompressX_Options::get_option('compressx_show_review',false)===false)
                {
                    CompressX_Options::update_option('compressx_show_review',time()+259200);
                }

                CompressX_Options::update_option('compressx_auto_optimize',$options);
            }

            if(isset($setting['convert_to_webp']))
            {
                if($setting['convert_to_webp']=='1')
                {
                    $options=1;
                }
                else
                {
                    $options=0;
                }

                CompressX_Options::update_option('compressx_output_format_webp',$options);
            }

            if(isset($setting['convert_to_avif']))
            {
                if($setting['convert_to_avif']=='1')
                {
                    $options=1;
                }
                else
                {
                    $options=0;
                }

                CompressX_Options::update_option('compressx_output_format_avif',$options);
            }

            if(isset($setting['quality']))
            {
                $options['quality']=$setting['quality'];
                if($options['quality']=="custom")
                {
                    $options['quality_webp']=isset($setting['quality_webp'])?$setting['quality_webp']: 80;
                    $options['quality_avif']=isset($setting['quality_avif'])?$setting['quality_avif']: 60;
                }

                CompressX_Options::update_option('compressx_quality',$options);
            }

            if(isset($setting['converter_method']))
            {
                $converter_method=$setting['converter_method'];
                CompressX_Options::update_option('compressx_converter_method',$converter_method);

                $convert_to_webp=CompressX_Options::get_option('compressx_output_format_webp',1);

                if($convert_to_webp)
                {
                    $ret['check_webp']=true;
                }
                else
                {
                    $ret['check_webp']=false;
                }

                $convert_to_avif=CompressX_Options::get_option('compressx_output_format_avif',1);

                if($convert_to_avif)
                {
                    $ret['check_avif']=true;
                }
                else
                {
                    $ret['check_avif']=false;
                }

                if($converter_method=='gd')
                {
                    if(CompressX_Image_Method::is_support_gd_webp())
                    {
                        $ret['disable_webp']=false;
                    }
                    else
                    {
                        $ret['disable_webp']=true;
                        $ret['check_webp']=false;
                    }

                    if(CompressX_Image_Method::is_support_gd_avif())
                    {
                        $ret['disable_avif']=false;
                    }
                    else
                    {
                        $ret['disable_avif']=true;
                        $ret['check_avif']=false;
                    }
                }
                else if($converter_method=='imagick')
                {
                    if(CompressX_Image_Method::is_support_imagick_webp())
                    {
                        $ret['disable_webp']=false;
                    }
                    else
                    {
                        $ret['disable_webp']=true;
                        $ret['check_webp']=false;
                    }

                    if(CompressX_Image_Method::is_support_imagick_avif())
                    {
                        $ret['disable_avif']=false;
                    }
                    else
                    {
                        $ret['disable_avif']=true;
                        $ret['check_avif']=false;
                    }
                }
                else
                {
                    $ret['disable_webp']=true;
                    $ret['check_webp']=false;
                    $ret['disable_avif']=true;
                    $ret['check_avif']=false;
                }
            }
            /*
            include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

            $rewrite=new CompressX_Webp_Rewrite();

            $upload_dir = wp_upload_dir();
            $rewrite->save_webp_rewrite_rule($upload_dir['basedir']);
            */

            $ret['result']='success';
            echo wp_json_encode($ret);
            die();
        }
        else
        {
            die();
        }
    }

    public function save_others_setting()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-general-settings');

        if(isset($_POST['setting'])&&!empty($_POST['setting']))
        {
            $json_setting = sanitize_text_field($_POST['setting']);
            $json_setting = stripslashes($json_setting);
            $setting = json_decode($json_setting, true);
            if (is_null($setting))
            {
                $ret['result']='failed';
                $ret['error']='json decode failed';
                echo wp_json_encode($ret);
                die();
            }

            $options=CompressX_Options::get_option('compressx_general_settings',array());

            if(isset($setting['remove_exif']))
                $options['remove_exif']=$setting['remove_exif'];
            if(isset($setting['exclude_png']))
                $options['exclude_png']=$setting['exclude_png'];
            if(isset($setting['exclude_png_webp']))
                $options['exclude_png_webp']=$setting['exclude_png_webp'];
            //
            if(isset($setting['auto_remove_larger_format']))
                $options['auto_remove_larger_format']=$setting['auto_remove_larger_format'];

            $reset_rewrite=false;
            if(isset($setting['disable_cache_control']))
            {
                $reset_rewrite=true;
                $options['disable_cache_control']=$setting['disable_cache_control'];
            }

            if(isset($setting['image_load']))
            {
                if(!isset($options['image_load']))
                {
                    $options['image_load']='htaccess';
                }

                if($options['image_load']!=$setting['image_load'])
                    $reset_rewrite=true;

                $options['image_load']=$setting['image_load'];
            }


            if(isset($setting['resize']))
                $options['resize']['enable']=$setting['resize'];
            if(isset($setting['resize_width']))
                $options['resize']['width']=$setting['resize_width'];
            if(isset($setting['resize_height']))
                $options['resize']['height']=$setting['resize_height'];

            if(isset($setting['converter_images_pre_request']))
                $options['converter_images_pre_request']=intval($setting['converter_images_pre_request']);
            //
            CompressX_Options::update_option('compressx_general_settings',$options);

            if(isset($setting['converter_method']))
            {
                $converter_method=$setting['converter_method'];
                CompressX_Options::update_option('compressx_converter_method',$converter_method);
            }

            if($options['image_load']=='htaccess')
            {
                if( $reset_rewrite)
                {
                    include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

                    $rewrite=new CompressX_Webp_Rewrite();
                    $rewrite->create_rewrite_rules();
                    $ret['test']='1';
                }
            }
            else if($options['image_load']=='compat_htaccess')
            {
                if( $reset_rewrite)
                {
                    include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

                    $rewrite=new CompressX_Webp_Rewrite();
                    $rewrite->create_rewrite_rules_ex();
                    $ret['test']='1';
                }
            }
            else
            {
                include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';
                $rewrite=new CompressX_Webp_Rewrite();
                $rewrite->remove_rewrite_rule();
            }


            $ret['result']='success';
            echo wp_json_encode($ret);
            die();
        }
        else
        {
            die();
        }
    }

    public function save_size_setting()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-thumbnail-settings');

        if(isset($_POST['setting'])&&!empty($_POST['setting']))
        {
            $json_setting = sanitize_text_field($_POST['setting']);
            $json_setting = stripslashes($json_setting);
            $setting = json_decode($json_setting, true);
            if (is_null($setting))
            {
                $ret['result']='failed';
                $ret['error']='json decode failed';
                echo wp_json_encode($ret);
                die();
            }

            $options=CompressX_Options::get_option('compressx_general_settings',array());

            $intermediate_image_sizes = get_intermediate_image_sizes();

            if(isset($setting['og']))
            {
                $options['skip_size']['og']=!$setting['og'];
            }
            else
            {
                $options['skip_size']['og']=false;
            }

            foreach ($intermediate_image_sizes as $size_key)
            {
                if(isset($setting[$size_key]))
                {
                    $options['skip_size'][$size_key]=!$setting[$size_key];
                }
                else
                {
                    $options['skip_size'][$size_key]=false;
                }
            }

            CompressX_Options::update_option('compressx_general_settings',$options);

            $ret['result']='success';
            echo wp_json_encode($ret);
            die();
        }
        else
        {
            die();
        }
    }

    public function get_custom_tree_dir()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-exclude');

        try{
            $node_array = array();

            if ($_POST['tree_node']['node']['id'] == '#') {
                $path = ABSPATH;

                if (!empty($_POST['tree_node']['path'])) {
                    $path = sanitize_text_field($_POST['tree_node']['path']);
                }

                /*
                $node_array[] = array(
                    'text' => basename($path),
                    'children' => true,
                    'id' => $path,
                    'icon' => 'dashicons dashicons-category cx-icon-color-yellow',
                    'state' => array(
                        'opened' => true
                    )
                );*/
            } else {
                $path =  sanitize_text_field($_POST['tree_node']['node']['id']);
            }

            $upload_dir = wp_upload_dir();
            $uploads_path = $upload_dir['basedir'];
            $uploads_path = str_replace('\\','/',$uploads_path);
            $uploads_path = $uploads_path.'/';

            if($path==$uploads_path)
            {
                $init=true;
                $key = gmdate( 'Y', time() );
            }
            else
            {
                $init=false;
                $key = gmdate( 'Y', time() );
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
                            $node['children'] = $this->has_children($path . '/' . $value);
                            $node['id'] = $path . '/' . $value;
                            if($init)
                            {
                                if($value==$key)
                                {
                                    $node['state']['opened']=true;
                                }
                            }
                            //
                            $node['icon'] = 'dashicons dashicons-open-folder cx-icon-color-techblue compressx-text';
                            $node['children_count']=$this->get_children_count($path . '/' . $value);
                            $node['text'] = $value.',('. $node['children_count'].') images';
                            $node['text'] .='<span class="dashicons dashicons-insert cx-remove-rule cx-add-custom-exclude-tree" data-id="'.$node['id'].'"></span>';
                            $node_array[] = $node;

                            /*
                            if($this->is_media_folder($path . '/' . $value))
                            {
                                $node['children'] = $this->has_children($path . '/' . $value);
                                $node['id'] = $path . '/' . $value;
                                if($init)
                                {
                                    if($value==$key)
                                    {
                                        $node['state']['opened']=true;
                                    }
                                }
                                //
                                $node['icon'] = 'dashicons dashicons-open-folder cx-icon-color-techblue compressx-text';
                                $node['children_count']=$this->get_children_count($path . '/' . $value);
                                $node['text'] = $value.',('. $node['children_count'].') images';
                                $node['text'] .='<span class="dashicons dashicons-insert cx-remove-rule cx-add-custom-exclude-tree" data-id="'.$node['id'].'"></span>';
                                $node_array[] = $node;
                            }
                            else
                            {
                                continue;
                            }*/
                        }
                        else{
                            //$node['text'] = $value;
                            //$node['children'] = true;
                            //$node['id'] = $path . '/' . $value;
                            //$node['icon'] = 'dashicons dashicons-media-default cx-icon-color-yellow';
                            //$node_array[] = $node;
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

        return false;
    }

    public function get_children_count($path)
    {
        try {
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            $files = 0;

            /** @var SplFileInfo $file */
            foreach ($rii as $file) {
                if ($file->isDir()){
                    continue;
                }

                $files++;
            }

            return $files;
        }
        catch (Exception $e)
        {
            return 0;
        }
    }

    public function add_exclude_folders()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-exclude');

        $json_excludes = sanitize_text_field($_POST['excludes']);
        $json_excludes = stripslashes($json_excludes);
        $new_excludes = json_decode($json_excludes, true);

        CompressX_Options::update_option('compressx_media_excludes',$new_excludes);

        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));
        $html='<ul>';
        foreach ($new_excludes as $exclude)
        {
            $path=str_replace($abs_path,'',$exclude);
            $html.= '<li><span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>'.
                '<span>'.$path.',('.$this->get_children_count($exclude).') images'.'</span>'.
                '<span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-exclude-tree" data-id="'.$exclude.'"></span></li>';
        }
        $html.='<ul>';
        $ret['result']='success';
        $ret['html']=$html;
        echo wp_json_encode($ret);
        die();
    }

    public function add_exclude_folder()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-exclude');

        $exclude = sanitize_text_field($_POST['id']);
        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));
        $path=str_replace($abs_path,'',$exclude);

        $html= '<li><span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>'.
            '<span>'.$path.',('.$this->get_children_count($exclude).') images'.'</span>'.
            '<span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-exclude-tree" data-id="'.$exclude.'"></span></li>';
        $ret['result']='success';
        $ret['html']=$html;
        echo wp_json_encode($ret);
        die();
    }

    public function remove_exclude_folders()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-exclude');

        $excludes=CompressX_Options::get_option('compressx_media_excludes',array());

        $id = sanitize_text_field($_POST['id']);
        unset($excludes[$id]);

        CompressX_Options::update_option('compressx_media_excludes',$excludes);

        $ret['result']='success';

        echo wp_json_encode($ret);
        die();
    }
}