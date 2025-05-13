<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_CDN
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_save_cdn', array($this, 'save_cdn'));
        add_action('wp_ajax_compressx_purge_cache', array($this, 'purge_cache'));
    }

    public function display()
    {
        ?>
        <div id="compressx-root">
            <div id="compressx-wrap">
                <?php
                $this->output_nav();
                $this->output_header();
                $this->output_cdn();
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

    public function output_cdn()
    {
        $options=CompressX_Options::get_option('compressx_general_settings',array());

        $zone_id=isset($options['cf_cdn']['zone_id'])?$options['cf_cdn']['zone_id']:'';
        $email=isset($options['cf_cdn']['email'])?$options['cf_cdn']['email']:'';
        $api_key=isset($options['cf_cdn']['api_key'])?$options['cf_cdn']['api_key']:'';

        $auto_purge_cache=isset($options['cf_cdn']['auto_purge_cache'])?$options['cf_cdn']['auto_purge_cache']:true;

        if($auto_purge_cache)
        {
            $auto_purge_cache='checked';
        }
        else
        {
            $auto_purge_cache='';
        }

        $auto_purge_cache_after_manual=isset($options['cf_cdn']['auto_purge_cache_after_manual'])?$options['cf_cdn']['auto_purge_cache_after_manual']:true;

        if($auto_purge_cache_after_manual)
        {
            $auto_purge_cache_after_manual='checked';
        }
        else
        {
            $auto_purge_cache_after_manual='';
        }

        ?>
        <section>
            <div class="compressx-container compressx-section ">
                <div class="compressx-general-settings">
                    <div class="compressx-general-settings-header">
                        <h5><?php esc_html_e('Cloudflare Integration','compressx')?></h5>
                    </div>
                    <div class="compressx-general-settings-body">
                        <div class="compressx-general-settings-body-grid">
                            <div class="compressing-converting" style="margin-top: 0;">
                                <span class="cx-title">
                                    <strong><?php esc_html_e('Cloudflare E-mail: ','compressx')?></strong>
                                </span>
                                <p>
                                    <input style="width: 100%;" type="text" option="cf_cdn" name="email" value="<?php echo esc_attr($email); ?>" >
                                </p>
                                <span class="cx-title">
                                    <strong><?php esc_html_e('Global API Key: ','compressx')?></strong>
                                </span>
                                <p>
                                    <input style="width: 100%;" type="password" autocomplete="new-password" option="cf_cdn" name="api_key" value="<?php echo esc_attr($api_key); ?>" >
                                </p>
                                <span class="cx-title">
                                    <strong><?php esc_html_e('Zone ID: ','compressx')?></strong>
                                </span>
                                <p>
                                    <input style="width: 100%;" type="password" autocomplete="new-password" option="cf_cdn" name="zone_id" value="<?php echo esc_attr($zone_id); ?>" >
                                </p>
                                <span class="cx-title">
                                    <strong><?php esc_html_e('Purge Cache Automatically: ','compressx')?></strong>
                                </span>
                                <p>
                                    <input id="compressx_auto_purge_cache" type="checkbox" option="cf_cdn" name="auto_purge_cache" <?php echo esc_attr($auto_purge_cache); ?>>
                                    <?php esc_html_e('Purge all cache automatically after successfully converting images in bulk.','compressx')?>
                                </p>
                                <p>
                                    <input id="compressx_auto_purge_cache_after_manual" type="checkbox" option="cf_cdn" name="auto_purge_cache_after_manual" <?php echo esc_attr($auto_purge_cache_after_manual); ?>>
                                    <?php esc_html_e('Purge all cache automatically after 5 minutes of manually converting images on media library.','compressx')?>
                                </p>
                            </div>
                            <div class="compressing-converting">
                                <div class="compressing-converting-information">
                                    <span><strong><?php esc_html_e('Cloudflare Email:','compressx')?></strong> <?php esc_html_e('Email address associated to your Cloudflare account','compressx')?></span>
                                    <p><span><strong><?php esc_html_e('Global API Key:','compressx')?></strong> <?php esc_html_e('A key for granting access to Cloudflare API to perform actions. You can find it in your Cloudflare dashboard > My Profile > API Tokens > Global API Key.','compressx')?></span></p>
                                    <span><strong><?php esc_html_e('Zone ID:','compressx')?></strong> <?php esc_html_e('A zone ID is generated automatically when a domain is added to Cloudflare and is required for API operations. You can find it in your Cloudflare Dashboard > The website overview > API section.','compressx')?></span>
                                    <br>
                                </div>
                                <div>
                                    <p>
                                        <input id="compressx_purge_cache" class="button" type="submit" value="<?php esc_attr_e('Purge All Cloudflare CDN Cache Manually','compressx')?>">
                                        <span style="padding:0 0.2rem;"></span>
                                        <span id="compressx_purge_cache_progress" style="display: none">
                                        <img src="../wp-admin/images/loading.gif" alt="">
                                    </span>
                                        <span id="compressx_purge_cache_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Done!','compressx')?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="compressx-general-settings-footer">
                        <input class="button-primary cx-button" id="compressx_save_cdn" type="submit" value="<?php esc_attr_e('Save Changes','compressx')?>">
                        <span style="padding:0 0.2rem;"></span>
                        <span id="compressx_save_cdn_progress" style="display: none">
                            <img src="../wp-admin/images/loading.gif" alt="">
                        </span>
                        <span id="compressx_save_cdn_text" class="success hidden" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!','compressx')?></span>
                    </div>
                </div>
            </div>
        </section>
        <script>
            jQuery('#compressx_save_cdn').click(function()
            {
                compressx_save_cdn_setting();
            });

            jQuery('#compressx_purge_cache').click(function()
            {
                compressx_purge_cache();
            });
            //

            function compressx_purge_cache()
            {
                var json = {};

                var setting_data = compressx_ajax_data_transfer('cf_cdn');
                var json1 = JSON.parse(setting_data);

                jQuery.extend(json1, json);
                setting_data=JSON.stringify(json1);

                var ajax_data = {
                    'action': 'compressx_purge_cache',
                    'setting': setting_data,
                };
                jQuery('#compressx_purge_cache').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#compressx_purge_cache_progress').show();

                compressx_post_request(ajax_data, function (data)
                {
                    jQuery('#compressx_purge_cache_progress').hide();
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);

                        jQuery('#compressx_purge_cache').css({'pointer-events': 'auto', 'opacity': '1'});
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#compressx_purge_cache_text').removeClass("hidden");
                            setTimeout(function ()
                            {
                                jQuery('#compressx_purge_cache_text').addClass( 'hidden' );
                            }, 3000);                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                        jQuery('#compressx_purge_cache').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#compressx_purge_cache_progress').hide();
                    jQuery('#compressx_purge_cache').css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }
            function compressx_save_cdn_setting()
            {
                var json = {};

                var setting_data = compressx_ajax_data_transfer('cf_cdn');
                var json1 = JSON.parse(setting_data);

                jQuery.extend(json1, json);
                setting_data=JSON.stringify(json1);

                var ajax_data = {
                    'action': 'compressx_save_cdn',
                    'setting': setting_data,
                };
                jQuery('#compressx_save_cdn').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#compressx_save_cdn_progress').show();
                //
                compressx_post_request(ajax_data, function (data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);

                        jQuery('#compressx_save_cdn').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#compressx_save_cdn_progress').hide();
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#compressx_save_cdn_text').removeClass("hidden");
                            setTimeout(function ()
                            {
                                jQuery('#compressx_save_cdn_text').addClass( 'hidden' );
                            }, 3000);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                        jQuery('#compressx_save_cdn').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#compressx_save_cdn_progress').hide();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#compressx_save_cdn').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#compressx_save_cdn_progress').hide();
                    var error_message = compressx_output_ajaxerror('changing settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public function save_cdn()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-cdn');

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

            if($this->need_test($setting))
            {
                include_once COMPRESSX_DIR . '/includes/class-compressx-cloudflare-cdn.php';
                $cdn=new CompressX_CloudFlare_CDN($setting);

                $ret=$cdn->purge_cache();

                if($ret['result']!='success')
                {
                    echo wp_json_encode($ret);
                    die();
                }
            }

            $options=CompressX_Options::get_option('compressx_general_settings',array());

            $options['cf_cdn']['zone_id']=$setting['zone_id'];
            $options['cf_cdn']['email']=$setting['email'];
            $options['cf_cdn']['api_key']=$setting['api_key'];
            if(isset($setting['auto_purge_cache']))
            {
                $options['cf_cdn']['auto_purge_cache']=$setting['auto_purge_cache'];
            }
            else
            {
                $options['cf_cdn']['auto_purge_cache']=false;
            }

            if(isset($setting['auto_purge_cache_after_manual']))
            {
                $options['cf_cdn']['auto_purge_cache_after_manual']=$setting['auto_purge_cache_after_manual'];
            }
            else
            {
                $options['cf_cdn']['auto_purge_cache_after_manual']=false;
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

    public function need_test($setting)
    {
        if(isset($setting['auto_purge_cache'])&&$setting['auto_purge_cache'])
        {
           return true;
        }

        if(isset($setting['auto_purge_cache_after_manual'])&&$setting['auto_purge_cache_after_manual'])
        {
            return true;
        }

        return false;
    }

    public function purge_cache()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-cdn');

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

            include_once COMPRESSX_DIR . '/includes/class-compressx-cloudflare-cdn.php';

            $cdn=new CompressX_CloudFlare_CDN($setting);

            $ret=$cdn->purge_cache();

            echo wp_json_encode($ret);
            die();
        }
        else
        {
            include_once COMPRESSX_DIR . '/includes/class-compressx-cloudflare-cdn.php';

            $options=CompressX_Options::get_option('compressx_general_settings',array());

            $setting=$options['cf_cdn'];

            $cdn=new CompressX_CloudFlare_CDN($setting);

            $ret=$cdn->purge_cache();

            echo wp_json_encode($ret);
            die();
        }
    }
}