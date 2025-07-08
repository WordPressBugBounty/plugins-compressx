<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX
{
    public function __construct()
    {
        $this->load_method();
        $this->load_meta();
        $this->load_dependencies();
        $this->load_admin();

        $this->load_hooks();
    }

    public function load_method()
    {
        include_once COMPRESSX_DIR . '/includes/method/class-compressx-image-method.php';
        include_once COMPRESSX_DIR . '/includes/method/class-compressx-image-opt-method.php';
        include_once COMPRESSX_DIR . '/deprecated/class-compressx-image-opt-method-deprecated.php';
    }

    public function load_meta()
    {
        include_once COMPRESSX_DIR . '/includes/meta/class-compressx-image-meta.php';
        include_once COMPRESSX_DIR . '/includes/meta/class-compressx-custom-image-meta.php';
    }

    public function load_dependencies()
    {
        include_once COMPRESSX_DIR . '/includes/class-compressx-image.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-options.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-imgoptim-task.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-image-scanner.php';

        include_once COMPRESSX_DIR . '/includes/class-compressx-custom-imgoptim-task.php';
        include_once COMPRESSX_DIR. '/includes/class-compressx-log.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-auto-optimization.php';
        new CompressX_Auto_Optimization();
        include_once COMPRESSX_DIR . '/includes/class-compressx-picture-load.php';
        new CompressX_Picture_Load();
        include_once COMPRESSX_DIR . '/includes/class-compressx-stats-manager.php';
        CompressX_Stats_Manager::init();
    }

    public function load_hooks()
    {
        add_action( 'delete_attachment', array( $this, 'delete_images' ), 20 );

        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . 'compressx.php' );
        add_filter('plugin_action_links_' . $plugin_basename, array( $this,'add_action_links'));

        add_action( 'wp_ajax_compressx_dissmiss_conflict_notice', array( $this, 'dissmiss_conflict_notice' ), 20 );
        add_action('compressx_purge_cache_event',array( $this,'purge_cache_event'));
        add_action('compressx_purge_cache',array( $this,'purge_cache'));
    }

    public function load_admin()
    {
        if(is_admin())
        {
            include_once COMPRESSX_DIR . '/includes/display/class-compressx-display.php';
            new CompressX_Display();

            include_once COMPRESSX_DIR . '/includes/display/class-compressx-custom-media-lib.php';
            new CompressX_Custom_Media_Lib();

            include_once COMPRESSX_DIR . '/includes/class-compressx-manual-optimization.php';
            new CompressX_Manual_Optimization();

            add_action( 'admin_notices', array( $this, 'optimizer_conflicts' ) );
            add_action('in_admin_header',array( $this,'hide_notices'), 99);

            $this->set_locale();
        }
    }

    public function ajax_check_security($role='manage_options')
    {
        check_ajax_referer( 'compressx_ajax', 'nonce' );
        $check=is_admin()&&current_user_can('manage_options');
        $check=apply_filters('compressx_ajax_check_security',$check,$role);
        if(!$check)
        {
            die();
        }
    }

    public function hide_notices()
    {
        if(is_multisite())
        {
            $screen_ids[]='toplevel_page_CompressX-network';
        }
        else
        {
            $screen_ids[]='toplevel_page_'.COMPRESSX_SLUG;
            $screen_ids[]='compressx_page_info-compressx';
            $screen_ids[]='compressx_page_logs-compressx';
            $screen_ids[]='compressx_page_cdn-compressx';
            $screen_ids[]='compressx_page_addons-compressx';
        }

        $screen_ids=apply_filters('compressx_get_screen_ids',$screen_ids);

        if(in_array(get_current_screen()->id,$screen_ids))
        {
            remove_all_actions('admin_notices');
            add_action( 'admin_notices', array( $this, 'optimizer_conflicts' ) );
        }
    }

    private function set_locale()
    {
        require_once COMPRESSX_DIR . '/includes/class-compressx-i18n.php';
        $plugin_i18n = new CompressX_i18n();
        add_action('init',array( $plugin_i18n,'load_plugin_textdomain'));
    }

    public function dissmiss_conflict_notice()
    {
        update_option('compressx_dissmiss_conflict_notice', true,false);
        wp_send_json_success();
    }

    public function optimizer_conflicts()
    {
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) )
        {
            return;
        }

        if ( is_network_admin() )
        {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) )
        {
            return;
        }

        $dissmiss=CompressX_Options::get_option('compressx_dissmiss_conflict_notice');
        if($dissmiss)
        {
            return;
        }

        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugins=array();
        $plugins["wp-smush"]="wp-smushit/wp-smush.php";
        $plugins["wp-smush-pro"]="wp-smush-pro/wp-smush.php";
        $plugins["kraken"]="kraken-image-optimizer/kraken.php";
        $plugins["tinypng"]="tiny-compress-images/tiny-compress-images.php";
        $plugins["shortpixel"]="shortpixel-image-optimiser/wp-shortpixel.php";
        $plugins["ewww"]="ewww-image-optimizer/ewww-image-optimizer.php";
        $plugins["ewww-cloud"]="ewww-image-optimizer-cloud/ewww-image-optimizer-cloud.php";
        $plugins["imagerecycle"]="imagerecycle-pdf-image-compression/wp-image-recycle.php";
        $plugins["imagify"]="imagify/imagify.php";
        $plugins["webp-converter-for-media"]="webp-converter-for-media/webp-converter-for-media.php";
        $plugins["optimole-wp"]="optimole-wp/optimole-wp.php";
        $plugins["wp-optimize"]="wp-optimize/wp-optimize.php";
        $plugins["wpvivid-imgoptim"]="wpvivid-imgoptim/wpvivid-imgoptim.php";

        $plugins = array_filter( $plugins, 'is_plugin_active' );

        if(empty($plugins))
        {
            return;
        }

        $plugins_names=array();
        foreach ( $plugins as $plugin )
        {
            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin,false,false );
            $plugins_names[]     = $plugin_data['Name'];
        }

        $plugins_names = implode( ', ', $plugins_names );

        ?>
        <div class="notice notice-warning is-dismissible" id="compressx_conflict_notice">
            <p class="notice-title">
                <strong>Multiple image optimization plugins detected</strong>
            </p>
            <p class="description">
                <strong>
                <?php
                echo wp_kses_post(sprintf(
                /* translators: %1$s: plugins name */
                    esc_html__('It seems that multiple image optimization plugins are installed on the website. This may cause conflicts while optimizing your images. In order to optimize your images properly and effectively, you may consider disabling other image optimization plugins: %1$s.','compressx'),
                    $plugins_names
                ));
                ?>
                </strong>
            </p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"
                   class="button button-primary button-hero">Go to Plugins page
                </a>
                <a href="#" style="margin-left: 15px" id="compressx_dissmiss_conflict_notice">Dismiss</a>
            </p>
        </div>
        <script>
            jQuery(document).on('click', '#compressx_dissmiss_conflict_notice', function()
            {
                jQuery('#compressx_conflict_notice').hide();
                var ajax_data = {
                    'action': 'compressx_dissmiss_conflict_notice'
                };
                var time_out = 30000;
                jQuery.ajax({
                    type: "post",
                    url: '<?php echo esc_url(admin_url('admin-ajax.php'));?>',
                    data: ajax_data,
                    success: function (data) {
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                    },
                    timeout: time_out
                });
            });
        </script>
        <?php
    }

    public function add_action_links( $links )
    {
        if(!is_multisite())
        {
            $settings_link = array(
                '<a href="' . admin_url( 'admin.php?page=' .COMPRESSX_SLUG ) . '">Settings</a>',
            );
        }
        else
        {
            $settings_link = array(
                '<a href="' . network_admin_url( 'admin.php?page=' . COMPRESSX_SLUG ) . '">Settings</a>',
            );
        }

        return array_merge(  $settings_link, $links );
    }

    public function delete_images( $image_id )
    {
        if ( empty( $image_id ) )
        {
            return;
        }

        CompressX_Image_Method::delete_image($image_id);
    }

    public function purge_cache_event()
    {
        include_once COMPRESSX_DIR . '/includes/class-compressx-cloudflare-cdn.php';

        $options=CompressX_Options::get_option('compressx_general_settings',array());

        $setting=$options['cf_cdn'];

        $cdn=new CompressX_CloudFlare_CDN($setting);

        $cdn->purge_cache();
        die();
    }

    public function purge_cache()
    {
        $options=CompressX_Options::get_option('compressx_general_settings',array());
        if(isset($options['cf_cdn']['auto_purge_cache'])&&$options['cf_cdn']['auto_purge_cache'])
        {
            include_once COMPRESSX_DIR . '/includes/class-compressx-cloudflare-cdn.php';
            $setting=$options['cf_cdn'];
            $cdn=new CompressX_CloudFlare_CDN($setting);
            $cdn->purge_cache();
        }
    }
}