<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX
{
    public function __construct()
    {
        include_once COMPRESSX_DIR . '/includes/class-compressx-image-meta.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-custom-image-meta.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-imgoptim-task.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-image-opt-method.php';
        include_once COMPRESSX_DIR . '/includes/class-compressx-custom-imgoptim-task.php';

        include_once COMPRESSX_DIR. '/includes/class-compressx-log.php';
        if(is_admin())
        {
            include_once COMPRESSX_DIR . '/includes/display/class-compressx-display.php';
            new CompressX_Display();

            include_once COMPRESSX_DIR . '/includes/display/class-compressx-custom-media-lib.php';
            new CompressX_Custom_Media_Lib();
            include_once COMPRESSX_DIR . '/includes/class-compressx-manual-optimization.php';
            new CompressX_Manual_Optimization();

            add_action( 'admin_notices', array( $this, 'optimizer_conflicts' ) );

            $this->set_locale();
        }

        include_once COMPRESSX_DIR . '/includes/class-compressx-auto-optimization.php';
        new CompressX_Auto_Optimization();

        add_action( 'delete_attachment', array( $this, 'delete_images' ), 20 );

        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . 'compressx.php' );
        add_filter('plugin_action_links_' . $plugin_basename, array( $this,'add_action_links'));

        add_action( 'wp_ajax_compressx_dissmiss_conflict_notice', array( $this, 'dissmiss_conflict_notice' ), 20 );
        //
        include_once COMPRESSX_DIR . '/includes/class-compressx-picture-load.php';
        new CompressX_Picture_Load();
    }

    private function set_locale()
    {
        require_once COMPRESSX_DIR . '/includes/class-compressx-i18n.php';
        $plugin_i18n = new CompressX_i18n();
        add_action('plugins_loaded',array( $plugin_i18n,'load_plugin_textdomain'));
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

        $dissmiss=get_option('compressx_dissmiss_conflict_notice',false);
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
            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin );
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

        $this->delete_webp($image_id);

        delete_transient('compressx_set_global_stats');
    }

    public function delete_webp($image_id)
    {
        $files=array();
        $file_path = get_attached_file( $image_id );
        $meta = wp_get_attachment_metadata( $image_id, true );

        if ( ! empty( $meta['sizes'] ) )
        {
            foreach ( $meta['sizes'] as $size_key => $size_data )
            {
                $filename= path_join( dirname( $file_path ), $size_data['file'] );
                $files[$size_key] =$filename;
            }

            if(!in_array($file_path,$files))
            {
                $files['og']=$file_path;
            }
        }
        else
        {
            $files['og']=$file_path;
        }

        foreach ($files as $size_key=>$file)
        {
            $file=$this->get_output_path($file);
            $webp_file =$file.'.webp';
            $avif_file =$file.'.avif';
            if(file_exists($webp_file))
                @wp_delete_file($webp_file);

            if(file_exists($avif_file))
                @wp_delete_file($avif_file);

            if(file_exists($file))
                @wp_delete_file($file);

        }
    }

    public function get_output_path($og_path)
    {
        $compressx_path=WP_CONTENT_DIR."/compressx-nextgen/uploads";

        $upload_dir = wp_get_upload_dir();
        $upload_root=$this->transfer_path($upload_dir['basedir']);
        $attachment_dir=dirname($og_path);
        $attachment_dir=$this->transfer_path($attachment_dir);
        $sub_dir=str_replace($upload_root,'',$attachment_dir);
        $sub_dir=untrailingslashit($sub_dir);
        $real_path=$compressx_path.'/'.$sub_dir;

        if(!file_exists($real_path))
        {
            @mkdir($real_path,0777,true);
        }

        return $real_path.'/'.basename($og_path);
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }
}