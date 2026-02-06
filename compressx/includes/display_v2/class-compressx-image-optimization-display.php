<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Image_Optimization_Display
{
    public $bulk_optimization;

    public function __construct()
    {
        $this->bulk_optimization=new CompressX_Bulk_Optimization_Display();
        add_action('compressx_output_review_v2', array($this, 'output_review'));
        add_action('wp_ajax_compressx_save_image_optimization_settings', array($this, 'set_setting'));
        add_action('wp_ajax_compressx_rating_dismiss', array($this, 'compressx_rating_dismiss'));

        add_action('wp_ajax_compressx_hide_big_update_v2', array($this, 'hide_big_update'));
    }

    public function display()
    {
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : '';
        switch ($view)
        {
            case 'bulk':
                $this->bulk_optimization->display();
                break;
            default:
                $this->display_image_optimization();
                break;
        }
    }

    public function display_image_optimization()
    {
        ?>
        <div class="compressx-root">
            <div class="compressx-v2-py-6 compressx-v2-w-full compressx-v2-max-w-[1200px] compressx-v2-mx-auto">
                <?php
                $this->output_header();
                $this->output_review();
                $this->output_notice();
                $this->output_format();
                $this->output_quality();
                $this->output_settings();
                $this->output_save_section();
                $this->output_footer();
                ?>
            </div>
        </div>
        <?php
    }

    public function output_header()
    {
        $bulk_progress = $this->get_bulk_progress();
        ?>
        <!-- header section -->
        <div class=' compressx-v2-pr-4 compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-4'>
            <!-- Left: Title & description -->
            <div>
                <h1 class="compressx-v2-text-2xl compressx-v2-font-semibold compressx-v2-text-gray-900">
                    Image Optimization Settings
                </h1>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-2">
                    Apply best-practice optimization to new uploads and historical images. The defaults balance quality and size for most sites.
                </p>
            </div>

            <div>
                <button id="cx_start_bulk_optimization" class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-1 compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded">
                    Bulk Optimization
                    <?php if ($bulk_progress > 0 && $bulk_progress < 100): ?>
                        <span>(<?php echo esc_html($bulk_progress) ?>%)</span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
        <?php
    }

    public function output_review()
    {
        $dismiss=CompressX_Options::get_option('compressx_rating_dismiss',false);
        if(intval($dismiss)!==0&&$dismiss<time())
        {
            //show
            $show_review = CompressX_Options::get_option('compressx_show_review', false);
            if ($show_review === false)
            {
                $show = false;
            }
            else if($show_review ==1)
            {
                $show = false; //
            }
            else if($show_review < time())
            {
                $show=true;
            }
            else
            {
                $show=false;
            }
        }
        else
        {
            $show=false;
        }

        if($show)
        {
            $size = CompressX_Image_Method::get_opt_folder_size();
            $opt_size = size_format($size, 2);
            $show_style="display:block";
        }
        else
        {
            $opt_size=size_format(0, 2);
            $show_style="display:none";
        }

        ?>
        <section id="cx_rating_box" style="<?php echo esc_attr($show_style)?>" class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-6 compressx-v2-flex compressx-v2-items-center compressx-v2-gap-5 compressx-v2-shadow-sm compressx-v2-mb-6">
            <!-- Content -->
            <div class="compressx-v2-flex-1">
                <h3 class="compressx-v2-font-semibold compressx-v2-text-gray-900 compressx-v2-text-base">
                    🎉 CompressX.io has optimized <span id="cx_size_of_opt_images" class="compressx-v2-text-blue-600"><?php echo esc_html($opt_size)?></span> of images for you!
                </h3>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-1">
                    If CompressX has helped you, could you leave us a <span class="compressx-v2-font-medium">5-star review</span>?
                    Your feedback motivates us to keep improving 🚀
                </p>

                <!-- Actions -->
                <div class="compressx-v2-flex compressx-v2-flex-col sm:compressx-v2-flex-row compressx-v2-flex-wrap compressx-v2-gap-4 compressx-v2-mt-5">
                    <button id="cx_rating_btn" class="compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-px-5 compressx-v2-py-2.5 compressx-v2-rounded compressx-v2-shadow">
                        ⭐ Yes, I’ll leave a review
                    </button>
                    <button id="cx_rating_ask_me_later" class="compressx-v2-text-sm compressx-v2-text-gray-500 hover:compressx-v2-text-blue-600">
                        Ask me later
                    </button>
                    <button id="cx_rating_already" class="compressx-v2-text-sm compressx-v2-text-gray-500 hover:compressx-v2-text-green-600">
                        I already did 🙂
                    </button>
                    <button id="cx_rating_dismiss" class="compressx-v2-text-sm compressx-v2-text-gray-500 hover:compressx-v2-text-red-500">
                        Dismiss
                    </button>
                </div>
            </div>
        </section>
        <script>
            jQuery('#cx_rating_btn').click(function() {
                window.open('https://wordpress.org/support/plugin/compressx/reviews/?filter=5#new-post', '_blank');

                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value': 'already'
                };
                compressx_post_request(ajax_data, function(data) {}, function(XMLHttpRequest, textStatus, errorThrown) {});
            });

            jQuery('#cx_rating_ask_me_later').click(function() {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value': 'ask_me_later'
                };
                compressx_post_request(ajax_data, function(data) {}, function(XMLHttpRequest, textStatus, errorThrown) {});
            });

            jQuery('#cx_rating_already').click(function() {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value': 'already'
                };
                compressx_post_request(ajax_data, function(data) {}, function(XMLHttpRequest, textStatus, errorThrown) {});
            });

            jQuery('#cx_rating_dismiss').click(function() {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value': 'dismiss'
                };
                compressx_post_request(ajax_data, function(data) {}, function(XMLHttpRequest, textStatus, errorThrown) {});
            });

            jQuery('#cx_rating_close').click(function() {
                jQuery('#cx_rating_box').hide();
                var ajax_data = {
                    'action': 'compressx_rating_dismiss',
                    'value': 'close'
                };
                compressx_post_request(ajax_data, function(data) {}, function(XMLHttpRequest, textStatus, errorThrown) {});
            });
        </script>
        <?php
    }

    public function output_notice()
    {
        $options = CompressX_Options::get_option('compressx_general_settings', array());
        $hide    =  CompressX_Options::get_option('compressx_hide_big_update', false);

        if(!empty($options)&&!$hide)
        {
            ?>
            <section id="cx_big_update" class="compressx-v2-border compressx-v2-rounded compressx-v2-bg-white compressx-v2-p-6 compressx-v2-mb-4 compressx-v2-space-y-4">

                <!-- Title -->
                <h2 class="compressx-v2-text-lg compressx-v2-font-semibold compressx-v2-text-gray-800">
                    🚀 Big Update – Same Features, Better Experience
                </h2>

                <!-- Description -->
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600">
                    We’ve redesigned the interface to make it cleaner and easier to use.
                    Every feature from the old version is still here — just better organized.
                </p>

                <!-- Highlights -->
                <ul class="compressx-v2-list-disc compressx-v2-ml-5 compressx-v2-space-y-1 compressx-v2-text-sm compressx-v2-text-gray-700">
                    <li>🎨 Refreshed UI – simpler navigation and setup</li>

                    <li>🔄 Switch Back Option – if you feel something is missing, you can return to the old UI anytime</li>
                </ul>

                <!-- Reassurance -->
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600">
                    If you notice a feature missing, <strong><a href="https://wordpress.org/support/plugin/compressx/">let us know</a></strong> — we’ll fix it quickly.
                    Don’t worry: the old UI will stay available and maintained until the new interface is fully stable.
                </p>

                <!-- Footer Button -->
                <div class="compressx-v2-pt-2">
                    <button id="cx_hide_big_update" class="compressx-v2-px-4 compressx-v2-py-2 compressx-v2-bg-blue-600 compressx-v2-text-white compressx-v2-text-sm compressx-v2-rounded hover:compressx-v2-bg-blue-700">
                        Got it
                    </button>
                </div>
            </section>
            <?php
        }
        else
        {
            CompressX_Options::update_option('compressx_hide_big_update', true);
        }

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


        $options = CompressX_Options::get_option('compressx_general_settings', array());
        $image_load = isset($options['image_load']) ? $options['image_load'] : 'htaccess';
        if ($image_load == "htaccess")
        {
            include_once COMPRESSX_DIR . '/includes/class-compressx-rewrite-checker.php';
            $test = new CompressX_Rewrite_Checker();
            $result = $test->test();
            if (!$result )
            {
                ?>
                <div class="compressx-v2-bg-yellow-50 compressx-v2-border-l-4 compressx-v2-border-yellow-400 compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-4">
                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                        <span class="dashicons dashicons-warning compressx-v2-text-yellow-500 compressx-v2-text-xl"></span>
                        <div>
                            <?php
                            if ($test->is_active_cache())
                            {
                                ?>
                                <p class="compressx-v2-text-sm compressx-v2-text-yellow-700">
                                    <span><?php esc_html_e('We\'ve detected a cache plugin on the site which may be causing rewrite rules of CompressX to fail. Please clear website cache to ensure the rewrite rules take effect.', 'compressx') ?></span>
                                </p>
                                <?php
                            } else if ($test->is_apache())
                            {
                                ?>
                                <p class="compressx-v2-text-sm compressx-v2-text-yellow-700">
                                    <span><?php echo wp_kses_post(__('.htaccess rewrite rules - we\'ve detected that .htaccess write rules are not executed on your Apache server, this can be because the server is not configured correctly for using .htaccess file from custom locations. For more details, please read this doc - <a href="https://compressx.io/docs/config-apache-htaccess-rules/">How-to: Config Apache htaccess Rules', 'compressx')) ?></a></span>
                                </p>
                                <?php
                            } else if ($test->is_nginx())
                            {
                                ?>
                                <p class="compressx-v2-text-sm compressx-v2-text-yellow-700">
                                    <span><?php echo wp_kses_post(__('We’ve detected that you use Nginx server. Nginx server does not support .htaccess rewrite rules and needs additional configurations to work. For more details, please read this doc - <a href="https://compressx.io/docs/config-nginx-htaccess-rules/">How-to: Config Nginx htaccess Rules', 'compressx')) ?></a></span>
                                </p>
                                <?php
                            }
                            else if ($test->is_litespeed())
                            {
                                ?>
                                <p class="compressx-v2-text-sm compressx-v2-text-yellow-700">
                                    <span><?php esc_html_e('We\'ve detected that the server is LiteSpeed, which requires a service restart for rewrite rules to take effect. Please restart the Litespeed service to make CompressX rewrite rules effective.', 'compressx') ?></a></span>
                                </p>
                                <?php
                            }
                            else
                            {
                                ?>
                                <p class="compressx-v2-text-sm compressx-v2-text-yellow-700">
                                    <span><?php esc_html_e('We’ve not detected an Apache or Nginx server. You may be using a different web server that we have not tested our plugin on.', 'compressx') ?></span>
                                </p>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        do_action('compressx_notices_v2');
    }

    public function output_format()
    {
        $is_auto = CompressX_Options::get_option('compressx_auto_optimize', false);

        if ($is_auto)
        {
            $is_auto_checked="true";
            $data_is_auto = '1';
        } else {
            $is_auto_checked="false";
            $data_is_auto = '0';
        }
        if (CompressX_Image_Opt_Method::is_support_gd())
        {
            $is_support_gd = true;
        } else {
            $is_support_gd = false;
        }

        if (CompressX_Image_Opt_Method::is_support_imagick())
        {
            $is_support_imagick = true;
        } else {
            $is_support_imagick = false;
        }

        $converter_method = CompressX_Options::get_option('compressx_converter_method', false);

        if (empty($converter_method))
        {
            $converter_method = CompressX_Image_Opt_Method::set_default_compress_server();
        }

        $convert_to_webp = CompressX_Options::get_option('compressx_output_format_webp', 'not init');
        if ($convert_to_webp === 'not init')
        {
            $convert_to_webp = CompressX_Image_Opt_Method::set_default_output_format_webp();
        }

        $convert_to_avif = CompressX_Options::get_option('compressx_output_format_avif', 'not init');
        if ($convert_to_avif === 'not init')
        {
            $convert_to_avif = CompressX_Image_Opt_Method::set_default_output_format_avif();
        }

        if ($convert_to_webp) {
            $convert_to_webp = 'checked';
        } else {
            $convert_to_webp = '';
        }

        if ($convert_to_avif) {
            $convert_to_avif = 'checked';
        } else {
            $convert_to_avif = '';
        }


        if (CompressX_Image_Opt_Method::is_current_support_webp())
        {
            $webp_support = true;
        } else {
            $convert_to_webp = '';
            $webp_support = false;
        }

        if (CompressX_Image_Opt_Method::is_current_support_avif()) {
            $avif_support = true;
        } else {
            $convert_to_avif = '';
            $avif_support = false;
        }

        //$webp_supported = CompressX_Image_Opt_Method::is_current_support_webp();
        //$avif_supported = CompressX_Image_Opt_Method::is_current_support_avif();
        ?>
        <section class="compressx-v2-bg-[#F9FDF6] compressx-v2-p-4 compressx-v2-rounded compressx-v2-border compressx-v2-mb-6">
            <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-mb-4"><?php esc_html_e('Global & Output Formats', 'compressx') ?></h2>
            <div class="compressx-v2-grid compressx-v2-grid-cols-1 md:compressx-v2-grid-cols-3 compressx-v2-mb-4 compressx-v2-gap-6">

                <div class="compressx-v2-flex compressx-v2-flex-wrap compressx-v2-gap-4 compressx-v2-items-center compressx-v2-mb-3">
                    <h3 class="compressx-v2-text-sm">
                        <label class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-2 cursor-pointer">
                            <button type="button"
                                    class="compressx-v2-relative compressx-v2-flex compressx-v2-items-center compressx-v2-justify-start
                                compressx-v2-w-11 compressx-v2-h-6
                                compressx-v2-bg-gray-300 compressx-v2-rounded compressx-v2-transition-colors
                                compressx-v2-border compressx-v2-border-gray-300"
                                    role="switch" aria-checked="<?php echo $is_auto_checked ?>" id="cx_enable_auto_optimize" data-checked="<?php echo $data_is_auto?>">
                            <span class="compressx-v2-w-4 compressx-v2-h-4 compressx-v2-bg-white
                                        compressx-v2-rounded compressx-v2-transition-all
                                        compressx-v2-mx-0.5"></span>
                            </button>

                            <span class="compressx-v2-text-sm"><?php esc_html_e('Auto-optimize new uploads', 'compressx') ?></span>
                        </label>

                        <?php
                        $this->output_tooltip(
                            'cx-v2-tip-auto-optimize',
                            esc_html__('Enable it to convert the new uploaded images.', 'compressx'),
                            'large'
                        );
                        ?>
                    </h3>
                </div>

                <div>
                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-2">
                        <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-700">
                            <?php esc_html_e('Library to Process Images', 'compressx') ?>

                            <?php
                            $this->output_tooltip(
                                'cx-v2-tip-library',
                                esc_html__('Choose the PHP extension for image processing. GD is faster for large images but supports fewer formats. Imagick supports more formats and produces higher quality results.', 'compressx')
                            );
                            ?>
                        </h3>
                        <a href="<?php echo esc_url(is_network_admin() ? network_admin_url('admin.php?page=info-compressx') : admin_url('admin.php?page=info-compressx')) ?>" class="compressx-v2-text-xs compressx-v2-text-blue-600 hover:underline">
                            <?php esc_html_e('Check Environment', 'compressx') ?>
                        </a>
                    </div>

                    <div class="compressx-v2-flex compressx-v2-gap-3">
                        <label class="compressx-v2-flex-1 compressx-v2-border compressx-v2-rounded compressx-v2-p-3 compressx-v2-cursor-pointer compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2 hover:compressx-v2-border-blue-500">
                            <input id="cx_converter_method_gd" type="radio" name="cx-v2-library" value="gd" <?php checked($converter_method, 'gd') ?> <?php echo !$is_support_gd ? 'disabled' : '' ?>>
                            <div>
                                <p class="compressx-v2-font-medium compressx-v2-text-sm"><?php esc_html_e('GD', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs <?php echo $is_support_gd ? 'compressx-v2-text-gray-500' : 'compressx-v2-text-red-600' ?>">
                                    <?php echo $is_support_gd ? esc_html__('Default PHP library', 'compressx') : esc_html__('Not Installed', 'compressx') ?>
                                </p>
                            </div>
                        </label>

                        <label class="compressx-v2-flex-1 compressx-v2-border compressx-v2-rounded compressx-v2-p-3 compressx-v2-cursor-pointer compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2 hover:compressx-v2-border-blue-500">
                            <input id="cx_converter_method_imagick" type="radio" name="cx-v2-library" value="imagick" <?php checked($converter_method, 'imagick') ?> <?php echo !$is_support_imagick ? 'disabled' : '' ?>>
                            <div>
                                <p class="compressx-v2-font-medium compressx-v2-text-sm"><?php esc_html_e('Imagick', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs <?php echo $is_support_imagick ? 'compressx-v2-text-gray-500' : 'compressx-v2-text-red-600' ?>">
                                <?php echo $is_support_imagick ? esc_html__('Better performance', 'compressx') : esc_html__('Not Installed', 'compressx') ?>
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-700 compressx-v2-mb-2">
                        <?php esc_html_e('Output Formats', 'compressx') ?>

                        <?php
                        $this->output_tooltip(
                            'cx-v2-tip-formats',
                            esc_html__('Convert .jpg and .png images to WebP or/and AVIF format.', 'compressx')
                        );
                        ?>
                    </h3>
                    <div class="compressx-v2-flex compressx-v2-gap-3">
                        <label class="compressx-v2-flex-1 compressx-v2-border compressx-v2-rounded compressx-v2-p-3 compressx-v2-cursor-pointer hover:compressx-v2-border-blue-500">
                            <input id="cx_convert_to_webp" type="checkbox" <?php echo $convert_to_webp ? 'checked' : '' ?> <?php echo !$webp_support ? 'disabled' : '' ?> class="compressx-v2-mb-1">
                            <span class="compressx-v2-font-medium compressx-v2-text-sm"><?php esc_html_e('WebP', 'compressx') ?></span>
                            <p id="cx_webp_status" class="compressx-v2-text-xs <?php echo $webp_support ? 'compressx-v2-text-green-600' : 'compressx-v2-text-red-600' ?>">
                                <?php echo $webp_support ? esc_html__('Supported', 'compressx') : esc_html__('Unsupported', 'compressx') ?>
                            </p>
                        </label>
                        <label class="compressx-v2-flex-1 compressx-v2-border compressx-v2-rounded compressx-v2-p-3 compressx-v2-cursor-pointer hover:compressx-v2-border-blue-500">
                            <input id="cx_convert_to_avif" type="checkbox" <?php echo $convert_to_avif ? 'checked' : '' ?> <?php echo !$avif_support ? 'disabled' : '' ?> class="compressx-v2-mb-1">
                            <span class="compressx-v2-font-medium compressx-v2-text-sm"><?php esc_html_e('AVIF', 'compressx') ?></span>
                            <p id="cx_avif_status" class="compressx-v2-text-xs <?php echo $avif_support ? 'compressx-v2-text-green-600' : 'compressx-v2-text-red-600' ?>">
                                <?php echo $avif_support ? esc_html__('Supported', 'compressx') : esc_html__('Unsupported', 'compressx') ?>
                            </p>
                        </label>
                    </div>

                </div>
            </div>
            <div>
                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                    <?php esc_html_e('Note: If the server lacks codecs, it will show as', 'compressx') ?>
                    <span class="compressx-v2-text-red-600 compressx-v2-font-medium"><?php esc_html_e('Unsupported', 'compressx') ?></span>
                    <?php esc_html_e('and be disabled automatically.', 'compressx') ?>
                </p>
            </div>
        </section>
        <?php
    }

    public function output_quality()
    {
        $bulk_progress = $this->get_bulk_progress();
        ?>
        <section class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-5 compressx-v2-mb-6">
            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-border-b compressx-v2-border-gray-200 compressx-v2-pb-4 compressx-v2-justify-between compressx-v2-mb-4">
                <h2 class="compressx-v2-text-lg compressx-v2-font-medium">
                    <?php esc_html_e('Image Quality Presets', 'compressx') ?>

                    <?php
                    $this->output_tooltip(
                        'cx-v2-tip-quality',
                        esc_html__('Choose the most appropriate compression level. Higher quality means larger file sizes but better image appearance.', 'compressx'),
                        'large'
                    );
                    ?>
                </h2>

                <button id="cx_start_bulk_optimization_2" type="button" class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-1 compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded compressx-v2-cursor-pointer">
                    <?php esc_html_e('Bulk Optimization', 'compressx') ?>
                    <?php if ($bulk_progress > 0 && $bulk_progress < 100) : ?>
                        <span>(<?php echo esc_html($bulk_progress) ?>%)</span>
                    <?php endif; ?>
                </button>
            </div>

            <?php $this->output_free_quality_section(); ?>

        </section>
        <?php
    }

    private function output_free_quality_section()
    {
        $enable_smart_mode=false;
        $class_general="";
        $general_selected="compressx-v2-bg-white";

        $class_smart="compressx-v2-hidden";
        $smart_selected="";

        ?>
        <div id="cx-v2-tab-buttons" class="compressx-v2-flex compressx-v2-gap-1 compressx-v2-border-b compressx-v2-border-gray-200 compressx-v2-mb-2">
            <button data-tab="global" class="cx-v2-tab-btn compressx-v2-px-4 compressx-v2-py-2 compressx-v2-border-b-2 compressx-v2-border-blue-600 compressx-v2-font-medium"><?php esc_html_e('Global Preset', 'compressx') ?></button>
            <button data-tab="woo" class="cx-v2-tab-btn compressx-v2-px-4 compressx-v2-py-2 compressx-v2-text-slate-600 hover:compressx-v2-text-blue-600"><?php esc_html_e('WooCommerce Images', 'compressx') ?>
            </button>
            <button data-tab="wp" class="cx-v2-tab-btn compressx-v2-px-4 compressx-v2-py-2 compressx-v2-text-slate-600 hover:compressx-v2-text-blue-600"><?php esc_html_e('WordPress Standard Images', 'compressx') ?>
            </button>
        </div>

        <div id="cx-v2-tab-content" class="compressx-v2-bg-[#F2FBFA] compressx-v2-rounded compressx-v2-p-4">
            <div data-tab-panel="global">
                <div>
                    <div class="compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-flex-wrap compressx-v2-gap-2 compressx-v2-mb-4">
                        <!-- Left Column -->
                        <div>
                            <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-1">
                                <span class="dashicons dashicons-admin-site-alt3 compressx-v2-text-blue-600"></span>
                                Global Compression Mode <span class="compressx-v2-text-blue-600">
                            </h3>
                            <p class="compressx-v2-text-sm compressx-v2-text-gray-500">
                                Choose how compression quality is applied across your images.
                            </p>
                        </div>
                        <!-- Right Column (beautified text) -->
                        <div class="compressx-v2-space-x-2">
                            <span class="compressx-v2-bg-slate-50 compressx-v2-border compressx-v2-border-slate-200 compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-1 compressx-v2-text-xs compressx-v2-text-slate-600 compressx-v2-font-medium compressx-v2-whitespace-nowrap">
                                <span class="compressx-v2-text-gray-500">Lossless:</span> WebP <span class="compressx-v2-font-semibold compressx-v2-text-gray-700">99</span>,AVIF <span class="compressx-v2-font-semibold compressx-v2-text-gray-700">99</span>.
                                <span class="compressx-v2-text-gray-500">Default:</span> WebP <span class="compressx-v2-font-semibold compressx-v2-text-gray-700">80</span>,
                                AVIF <span class="compressx-v2-font-semibold compressx-v2-text-gray-700">60</span>.
                            </span>
                            <span class="compressx-v2-relative compressx-v2-inline-flex compressx-v2-items-center compressx-v2-group">
                                    <!-- Trigger Button -->
                                    <button type="button" class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-justify-center compressx-v2-h-6 compressx-v2-w-6 compressx-v2-rounded compressx-v2-border compressx-v2-border-slate-300 compressx-v2-bg-white hover:compressx-v2-bg-slate-50 compressx-v2-text-slate-600 hover:compressx-v2-text-slate-800 compressx-v2-shadow-sm focus:compressx-v2-outline-none focus:compressx-v2-ring-2 focus:compressx-v2-ring-sky-400" aria-describedby="cxp-tip-1">
                                        <span class="compressx-v2-font-semibold compressx-v2-text-xs">i</span>
                                    </button>
                                <!-- Tooltip -->
                                    <div id="cxp-tip-1" role="tooltip" class="compressx-v2-absolute compressx-v2-z-50 compressx-v2-bottom-full compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-mb-2
                                        compressx-v2-hidden group-hover:compressx-v2-block group-focus-within:compressx-v2-block
                                        compressx-v2-min-w-64 compressx-v2-max-w-96 compressx-v2-rounded compressx-v2-bg-slate-900/95 compressx-v2-text-white compressx-v2-text-xs compressx-v2-leading-5 compressx-v2-px-3 compressx-v2-py-3
                                        compressx-v2-shadow-xl compressx-v2-ring-1 compressx-v2-ring-black/10">
                                        <div class="compressx-v2-flex compressx-v2-gap-2 compressx-v2-items-start">
                                        <span class="compressx-v2-mt-0.5 compressx-v2-inline-block compressx-v2-h-1.5 compressx-v2-w-1.5 compressx-v2-rounded compressx-v2-bg-emerald-400"></span>
                                        <div>
                                            <div class="compressx-v2-font-medium compressx-v2-text-[11px] compressx-v2-tracking-wide compressx-v2-text-emerald-300 compressx-v2-mb-0.5">
                                            Tip
                                            </div>
                                            <div>
                                            Re-generate modern formats (WebP/AVIF) anytime. This won’t touch your original image.
                                            <a href="#" class="compressx-v2-text-sky-300 hover:compressx-v2-underline">Learn more...</a>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="compressx-v2-absolute compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-top-full compressx-v2-h-2 compressx-v2-w-2 compressx-v2-rotate-45 compressx-v2-bg-slate-900/95"></div>
                                    </div>
                            </span>
                        </div>
                    </div>
                    <div class="compressx-v2-rounded compressx-v2-mb-2">
                        <!-- Radio Group -->
                        <div class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-4">
                            <!-- General Mode -->
                            <label class="compressx-v2-border compressx-v2-rounded compressx-v2-p-4 compressx-v2-cursor-pointer <?php echo esc_attr($general_selected) ;?>">
                                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 ">
                                    <input id="cx_compression_mode_general" type="radio" name="compression_mode" value="general" class="compressx-v2-mt-1" <?php echo checked(!$enable_smart_mode)?>/>
                                    <div>
                                        <div class="compressx-v2-text-sm compressx-v2-font-medium">
                                            General
                                        </div>
                                        <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                            Use a single fixed compression quality for all images.
                                        </div>
                                        <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                            The simplest settings for common websites.
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <!-- Smart Mode -->
                            <label class="compressx-v2-border compressx-v2-rounded compressx-v2-p-4 compressx-v2-cursor-pointer <?php echo esc_attr($smart_selected) ;?>">
                                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                                    <input id="cx_compression_mode_smart" type="radio" name="compression_mode" value="smart" class="compressx-v2-mt-1" <?php echo checked($enable_smart_mode)?> />
                                    <div>
                                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                                            <span class="compressx-v2-text-sm compressx-v2-font-medium">Smart</span>
                                            <span class="compressx-v2-text-xs compressx-v2-text-green-600">Pro</span>
                                            <span class="compressx-v2-text-xs compressx-v2-text-green-600">
                                                <a href="https://compressx.io/docs/smart-image-optimization/">Learn more</a>
                                            </span>
                                        </div>
                                        <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                            Automatically adjust quality based on image size.
                                        </div>
                                        <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                            Higher quality for small images, stronger compression for large images.
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <!-- General radio content -->
                    <div id="compressx_general_quality_setting" class="compressx-v2-bg-white compressx-v2-border compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-6 compressx-v2-p-2 <?php echo esc_attr($class_general)?>">
                        <?php $this->output_general();?>
                    </div>

                    <!-- Smart radio content -->
                    <div id="compressx_smart_quality_setting" class="compressx-v2-bg-white compressx-v2-border compressx-v2-rounded compressx-v2-p-4 <?php echo esc_attr($class_smart)?>">
                        <?php $this->output_smart();?>
                    </div>
                </div>
            </div>

            <div data-tab-panel="woo" class="compressx-v2-hidden">
                <div class="compressx-v2-mb-4 compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-flex-wrap compressx-v2-gap-2 compressx-v2-opacity-50 compressx-v2-pointer-events-none">
                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <div>
                            <button type="button" class="compressx-v2-relative compressx-v2-flex compressx-v2-items-center compressx-v2-justify-start
                                        compressx-v2-w-11 compressx-v2-h-6
                                        compressx-v2-bg-gray-300 compressx-v2-rounded compressx-v2-transition-colors
                                        compressx-v2-border compressx-v2-border-gray-300" role="switch" aria-checked="false" disabled>
                                <span class="compressx-v2-w-4 compressx-v2-h-4 compressx-v2-bg-white
                                                compressx-v2-rounded compressx-v2-transition-all
                                                compressx-v2-mx-0.5"></span>
                            </button>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-flex-col">
                            <span class="compressx-v2-text-sm compressx-v2-text-slate-500">
                                <?php esc_html_e('Enable to control compression levels for WooCommerce-specific images.', 'compressx') ?>
                                <span class="compressx-v2-text-blue-600"><a href="https://compressx.io/pricing" target="_blank"><?php esc_html_e('Pro only', 'compressx') ?></a></span>
                            </span>
                        </div>
                    </div>
                    <div>
                        <button class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-1.5 hover:compressx-v2-bg-gray-50 compressx-v2-text-sm compressx-v2-text-gray-700" disabled>
                            <?php esc_html_e('Reset to Default', 'compressx') ?>
                        </button>
                    </div>
                </div>

                <div class="compressx-v2-grid compressx-v2-grid-cols-1 md:compressx-v2-grid-cols-2 compressx-v2-gap-4 compressx-v2-opacity-50 compressx-v2-pointer-events-none">
                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Product Featured Image', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (85-90), AVIF (85-90)---brand/visual integrity important', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Product Gallery Image', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (75-85), AVIF (75-85)---visual but secondary to main image', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="80" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="80" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Variation-Specific Image', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (75-85), AVIF (75-85)---often similar to gallery use', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="80" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="80" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Category Image (Taxonomy)', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (70-80), AVIF (70-80)---smaller, not full focus', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="75" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="75" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div data-tab-panel="wp" class="compressx-v2-hidden">
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-flex-wrap compressx-v2-gap-2 compressx-v2-mb-4 compressx-v2-opacity-50 compressx-v2-pointer-events-none">
                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <div>
                            <button type="button" class="compressx-v2-relative compressx-v2-flex compressx-v2-items-center compressx-v2-justify-start
                                        compressx-v2-w-11 compressx-v2-h-6
                                        compressx-v2-bg-gray-300 compressx-v2-rounded compressx-v2-transition-colors
                                        compressx-v2-border compressx-v2-border-gray-300" role="switch" aria-checked="false" disabled>
                                <span class="compressx-v2-w-4 compressx-v2-h-4 compressx-v2-bg-white
                                                compressx-v2-rounded compressx-v2-transition-all
                                                compressx-v2-mx-0.5"></span>
                            </button>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-flex-col">
                            <span class="compressx-v2-text-sm compressx-v2-text-slate-500">
                                <?php esc_html_e('Enable to fine-tune compression levels for common WordPress image types.', 'compressx') ?>
                                <span class="compressx-v2-text-blue-600"><a href="https://compressx.io/pricing" target="_blank"><?php esc_html_e('Pro only', 'compressx') ?></a></span>
                            </span>
                        </div>
                    </div>
                    <div>
                        <button class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-1.5 hover:compressx-v2-bg-gray-50 compressx-v2-text-sm compressx-v2-text-gray-700" disabled>
                            <?php esc_html_e('Reset to Default', 'compressx') ?>
                        </button>
                    </div>
                </div>

                <div class="compressx-v2-grid compressx-v2-grid-cols-1 md:compressx-v2-grid-cols-2 compressx-v2-gap-4 compressx-v2-opacity-50 compressx-v2-pointer-events-none">
                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Site Logo', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (88+), AVIF (85+)---branding clarity is essential', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Header Background', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (75-85), AVIF (75-85)---may span full width, retain clarity', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Featured Image (Post)', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (85-90), AVIF (85-90)---visually impactful, often shared', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="90" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Featured Image (Page)', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (85-90), AVIF (85-90)---visually impactful, often shared', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="88" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="86" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Image in Sidebar', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (60-75), AVIF (60-75)---small visual element', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="68" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="65" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>

                    <div class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4 compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-gap-4 hover:compressx-v2-shadow-sm compressx-v2-transition">
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input type="checkbox" class="compressx-v2-mt-1" disabled>
                            <div>
                                <p class="compressx-v2-font-medium"><?php esc_html_e('Image Field Used via ACF', 'compressx') ?></p>
                                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                    <?php esc_html_e('Webp (70-80), AVIF (70-80)---depends on context, often supportive', 'compressx') ?>
                                </p>
                            </div>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-gap-4 compressx-v2-items-center">
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="75" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">WebP</label>
                            </div>
                            <div class="compressx-v2-flex compressx-v2-flex-col compressx-v2-items-center">
                                <input type="number" value="72" class="compressx-v2-w-16 compressx-v2-border compressx-v2-rounded compressx-v2-text-center compressx-v2-px-1 compressx-v2-py-1" disabled>
                                <label class="compressx-v2-text-[10px] compressx-v2-text-gray-500">AVIF</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function output_general()
    {
        $quality_options = CompressX_Options::get_option('compressx_quality', array());
        $webp_quality = CompressX_Options::get_webp_quality($quality_options);
        $avif_quality = CompressX_Options::get_avif_quality($quality_options);

        ?>
        <!-- WebP Settings -->
        <div class="compressx-v2-border compressx-v2-border-blue-100 compressx-v2-rounded compressx-v2-p-4 compressx-v2-bg-white">
            <h4 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-blue-700 compressx-v2-mb-3">
                WebP
            </h4>
            <div>
                <label class="compressx-v2-text-sm compressx-v2-font-medium">
                    Compression Quality (1–99)
                    <span class="compressx-v2-relative compressx-v2-inline-flex compressx-v2-items-center compressx-v2-group">
                        <button type="button"
                                class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-justify-center compressx-v2-h-6 compressx-v2-w-6 compressx-v2-rounded compressx-v2-border compressx-v2-border-slate-300 compressx-v2-bg-white hover:compressx-v2-bg-slate-50 compressx-v2-text-slate-600 hover:compressx-v2-text-slate-800 compressx-v2-shadow-sm focus:compressx-v2-outline-none focus:compressx-v2-ring-2 focus:compressx-v2-ring-sky-400"
                                aria-describedby="cxp-tip-1">
                            <span class="compressx-v2-font-semibold compressx-v2-text-xs">i</span>
                        </button>

                        <!-- Tooltip -->
                        <div id="cxp-tip-1" role="tooltip"
                             class="compressx-v2-absolute compressx-v2-z-50 compressx-v2-bottom-full compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-mb-2
                                    compressx-v2-hidden group-hover:compressx-v2-block group-focus-within:compressx-v2-block
                                    compressx-v2-min-w-64 compressx-v2-max-w-96 compressx-v2-rounded compressx-v2-bg-slate-900/95 compressx-v2-text-white compressx-v2-text-xs compressx-v2-leading-5 compressx-v2-px-3 compressx-v2-py-3
                                    compressx-v2-shadow-xl compressx-v2-ring-1 compressx-v2-ring-black/10">
                            <div class="compressx-v2-flex compressx-v2-gap-2 compressx-v2-items-start">
                                <span class="compressx-v2-mt-0.5 compressx-v2-inline-block compressx-v2-h-1.5 compressx-v2-w-1.5 compressx-v2-rounded compressx-v2-bg-emerald-400"></span>
                                <div>
                                    <div class="compressx-v2-font-medium compressx-v2-text-[11px] compressx-v2-tracking-wide compressx-v2-text-emerald-300 compressx-v2-mb-0.5">
                                        Tip
                                    </div>
                                    <div>
                                        Higher values apply stronger compression across all image sizes and reduce quality more noticeably.
                                    </div>
                                </div>
                            </div>
                            <div class="compressx-v2-absolute compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-top-full compressx-v2-h-2 compressx-v2-w-2 compressx-v2-rotate-45 compressx-v2-bg-slate-900/95"></div>
                        </div>
                    </span>
                </label>

                <!-- Slider with value on the right -->
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 compressx-v2-mt-1">
                    <input id="cx-v2-webp-quality-input" type="range" min="1" max="99" value="<?php echo esc_attr($webp_quality); ?>"
                           class="compressx-v2-flex-1 compressx-v2-w-full">
                    <!-- 👇 value display -->
                    <span id="webpStrengthValue" class="compressx-v2-w-8 compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-slate-600 compressx-v2-text-right">
                       <?php echo esc_html($webp_quality);?>
                    </span>
                </div>
            </div>

            <!-- Script -->
            <script>
                const range = document.getElementById("cx-v2-webp-quality-input");
                const valueDisplay = document.getElementById("webpStrengthValue");
                range.addEventListener("input", () => {
                    valueDisplay.textContent = range.value;
                });
            </script>
        </div>

        <!-- AVIF Settings -->
        <div class="compressx-v2-border compressx-v2-border-green-100 compressx-v2-rounded compressx-v2-p-4 compressx-v2-bg-white">
            <h4 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-green-700 compressx-v2-mb-3">
                AVIF
            </h4>
            <div>
                <label class="compressx-v2-text-sm compressx-v2-font-medium">
                    Compression Quality (1–99)
                    <span class="compressx-v2-relative compressx-v2-inline-flex compressx-v2-items-center compressx-v2-group">
                        <button type="button"
                                class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-justify-center compressx-v2-h-6 compressx-v2-w-6 compressx-v2-rounded compressx-v2-border compressx-v2-border-slate-300 compressx-v2-bg-white hover:compressx-v2-bg-slate-50 compressx-v2-text-slate-600 hover:compressx-v2-text-slate-800 compressx-v2-shadow-sm focus:compressx-v2-outline-none focus:compressx-v2-ring-2 focus:compressx-v2-ring-sky-400"
                                aria-describedby="cxp-tip-1">
                            <span class="compressx-v2-font-semibold compressx-v2-text-xs">i</span>
                        </button>

                        <!-- Tooltip -->
                        <div id="cxp-tip-1" role="tooltip"
                             class="compressx-v2-absolute compressx-v2-z-50 compressx-v2-bottom-full compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-mb-2
                             compressx-v2-hidden group-hover:compressx-v2-block group-focus-within:compressx-v2-block
                             compressx-v2-min-w-64 compressx-v2-max-w-96 compressx-v2-rounded compressx-v2-bg-slate-900/95 compressx-v2-text-white compressx-v2-text-xs compressx-v2-leading-5 compressx-v2-px-3 compressx-v2-py-3
                             compressx-v2-shadow-xl compressx-v2-ring-1 compressx-v2-ring-black/10">
                            <div class="compressx-v2-flex compressx-v2-gap-2 compressx-v2-items-start">
                                <span class="compressx-v2-mt-0.5 compressx-v2-inline-block compressx-v2-h-1.5 compressx-v2-w-1.5 compressx-v2-rounded compressx-v2-bg-emerald-400"></span>
                                <div>
                                    <div class="compressx-v2-font-medium compressx-v2-text-[11px] compressx-v2-tracking-wide compressx-v2-text-emerald-300 compressx-v2-mb-0.5">
                                        Tip
                                    </div>
                                    <div>
                                        Higher values apply stronger compression across all image sizes and reduce quality more noticeably.
                                    </div>
                                </div>
                            </div>
                            <div class="compressx-v2-absolute compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-top-full compressx-v2-h-2 compressx-v2-w-2 compressx-v2-rotate-45 compressx-v2-bg-slate-900/95"></div>
                        </div>
                    </span>
                </label>

                <!-- Slider + Value display -->
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 compressx-v2-mt-1">
                    <input id="cx-v2-avif-quality-input" type="range" min="1" max="100" value="<?php echo esc_attr($avif_quality); ?>" class="compressx-v2-flex-1 compressx-v2-w-full">
                    <span id="avifStrengthValue" class="compressx-v2-w-8 compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-slate-600 compressx-v2-text-right">
                        <?php echo esc_html($avif_quality); ?>
                    </span>
                </div>
            </div>

            <!-- Script -->
            <script>
                const avifRange = document.getElementById("cx-v2-avif-quality-input");
                const avifDisplay = document.getElementById("avifStrengthValue");
                avifRange.addEventListener("input", () => {
                    avifDisplay.textContent = avifRange.value;
                });
            </script>
        </div>

        <?php
    }

    public function output_smart()
    {
        $size_threshold=80;

        $quality_options = CompressX_Options::get_option('compressx_quality', array());
        $webp_quality = CompressX_Options::get_webp_quality($quality_options);
        $avif_quality = CompressX_Options::get_avif_quality($quality_options);

        $fixed_webp=80;
        $fixed_avif=60;

        $webp_max=80;
        $webp_min=35;

        $avif_max=60;
        $avif_min=30;

        ?>
        <div class="compressx-v2-bg-white compressx-v2-rounded">

            <!-- Global Rule Explanation -->
            <!-- Global Rule Explanation (2 Columns) -->
            <div class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-6 compressx-v2-mb-4">
                <!-- Left: Title & Description -->
                <div>
                    <h4 class="compressx-v2-text-sm compressx-v2-font-medium">
                        Smart Quality Rules
                    </h4>
                    <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                        Define how compression quality changes based on image size.
                    </p>
                </div>
                <!-- Right: Tip -->
                <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-p-3">
                    <p class="compressx-v2-text-xs compressx-v2-text-gray-600">
                        <span class="compressx-v2-font-medium">Tip:</span>
                        In most cases, you do not need to change any settings below.
                        Smart mode works well with the default values for most sites.
                    </p>
                </div>
            </div>
            <!-- Size Threshold -->
            <div class="compressx-v2-border compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-4">
                <div class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-6">
                    <!-- Left: Size Threshold Rule -->
                    <div>
                        <h5 class="compressx-v2-text-sm compressx-v2-font-medium">
                            Size Threshold
                        </h5>
                        <div class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-3">
                            Define the split point between fixed and adaptive compression.
                            <a id="cx_show_size_threshold_tip" style="cursor: pointer" class="compressx-v2-text-xs compressx-v2-text-blue-600 hover:compressx-v2-text-blue-700">Why?</a>
                        </div>
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <span class="compressx-v2-text-sm">
                                Images ≤
                            </span>
                            <input id="compressx_size_threshold" type="number" value="<?php echo esc_attr($size_threshold)?>" min="10" max="500" class="compressx-v2-w-20 compressx-v2-border compressx-v2-rounded compressx-v2-text-center" readonly/>
                            <span class="compressx-v2-text-sm">
                                KB use fixed quality
                            </span>
                        </div>
                        <div class="compressx-v2-text-xs compressx-v2-text-gray-400 compressx-v2-mt-2">
                            Recommended value: 30KB – 100KB.
                        </div>
                    </div>
                    <!-- Right: Design Rationale Tip -->
                    <div id="cx_size_threshold_tip" style="display: none"  class="compressx-v2-text-xs compressx-v2-text-gray-500">
                        <div class="compressx-v2-font-medium compressx-v2-text-gray-600 compressx-v2-mb-1">
                            Why a size threshold?
                        </div>
                        <div>
                            Very small images are more sensitive to quality changes.
                            Using a fixed quality helps preserve visual clarity.
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main Rule Layout -->
            <div class="compressx-v2-grid compressx-v2-grid-cols-3 compressx-v2-gap-4 compressx-v2-mb-4">
                <!-- Small Images -->
                <div class="compressx-v2-col-span-1 compressx-v2-border compressx-v2-rounded compressx-v2-p-4">
                    <h5 class="compressx-v2-text-sm compressx-v2-font-medium">
                        Small Images (≤ Threshold)
                    </h5>
                    <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-4">
                        Fixed compression quality is applied.
                    </p>
                    <div class="compressx-v2-mb-4">
                        <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                            WebP Fixed Quality
                        </div>
                        <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 compressx-v2-mt-1">
                                <input disabled id="compressx_fixed_webp" type="range" min="1" max="99" value="<?php echo esc_attr($fixed_webp) ?>" class="compressx-v2-flex-1 compressx-v2-w-full cx-quality-range">
                                <!-- 👇 value display -->
                                <span class="compressx-v2-w-8 compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-slate-600 compressx-v2-text-right">
                                   <?php echo esc_html($fixed_webp) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                            AVIF Fixed Quality
                        </div>
                        <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 compressx-v2-mt-1">
                                <input disabled id="compressx_fixed_avif" type="range" min="1" max="99" value="<?php echo esc_attr($fixed_avif) ?>" class="cx-quality-range compressx-v2-flex-1 compressx-v2-w-full">
                                <!-- 👇 value display -->
                                <span class="compressx-v2-w-8 compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-slate-600 compressx-v2-text-right">
                                    <?php echo esc_html($fixed_avif) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Large Images -->
                <div class="compressx-v2-col-span-2 compressx-v2-border compressx-v2-rounded compressx-v2-p-4">
                    <h5 class="compressx-v2-text-sm compressx-v2-font-medium">
                        Large Images (> Threshold)
                    </h5>
                    <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-4">
                        Compression quality decreases automatically as image size increases.
                    </p>
                    <div class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-4">
                        <!-- WebP -->
                        <div class="compressx-v2-border compressx-v2-rounded compressx-v2-p-3">
                            <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                WebP Quality Range
                            </div>
                            <div class="compressx-v2-mt-2">
                                <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                    Quality at Threshold
                                </div>
                                <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                                    <span id="cx_webp_max"><?php echo esc_html($webp_max)?></span> <span class="compressx-v2-text-xs compressx-v2-text-gray-400">(Inherited)</span>
                                </div>
                                <div class="compressx-v2-mt-2">
                                    <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                        Threshold → 4MB
                                    </div>
                                    <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                                        <span><span id="cx_webp_max_2"><?php echo esc_html($webp_max)?></span><span> to </span><span id="cx_webp_min_2"><?php echo esc_html($webp_min)?></span></span><span class="compressx-v2-text-xs compressx-v2-text-gray-400"> Lower quality, smaller file size. <a href="https://compressx.io/docs/smart-image-optimization/#compressx-size-threshold" >Why?</a></span>
                                    </div>
                                </div>
                            </div>
                            <div class="compressx-v2-mt-2">
                                <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                    Quality ≥ 4MB
                                </div>
                                <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 compressx-v2-mt-1">
                                        <span class="compressx-v2-w-8 compressx-v2-text-lg compressx-v2-font-semibold compressx-v2-text-slate-600 ">
                                            <?php echo esc_html($webp_min)?>
                                        </span>
                                        <input disabled id="compressx_webp_min" type="range" min="1" max="99" value="<?php echo esc_attr($webp_min)?>" class="cx-quality-range compressx-v2-flex-1 compressx-v2-w-full">
                                        <!-- 👇 value display -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- AVIF -->
                        <div class="compressx-v2-border compressx-v2-rounded compressx-v2-p-3">
                            <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                AVIF Quality Range
                            </div>
                            <div class="compressx-v2-mt-2">
                                <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                    Quality at Threshold
                                </div>
                                <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                                    <span id="cx_avif_max"><?php echo esc_html($avif_max)?> </span> <span class="compressx-v2-text-xs compressx-v2-text-gray-400">(Inherited)</span>
                                </div>
                                <div class="compressx-v2-mt-2">
                                    <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                        Threshold → 4MB
                                    </div>
                                    <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                                        <span><span id="cx_avif_max_2"><?php echo esc_html($avif_max)?></span><span> to </span><span id="cx_avif_min_2"><?php echo esc_html($avif_min)?></span></span><span class="compressx-v2-text-xs compressx-v2-text-gray-400"> Lower quality, smaller file size. <a href="https://compressx.io/docs/smart-image-optimization/#compressx-size-threshold">Why?</a></span>
                                    </div>
                                </div>
                            </div>
                            <div class="compressx-v2-mt-2">
                                <div class="compressx-v2-text-xs compressx-v2-text-gray-400">
                                    Quality ≥ 4MB
                                </div>
                                <div class="compressx-v2-text-lg compressx-v2-font-semibold">
                                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 compressx-v2-mt-1">
                                        <span class="compressx-v2-w-8 compressx-v2-text-lg compressx-v2-font-semibold compressx-v2-text-slate-600">
                                            <?php echo esc_html($avif_min)?>
                                        </span>
                                        <input disabled id="compressx_avif_min" type="range" min="1" max="99" value="<?php echo esc_attr($avif_min)?>" class="cx-quality-range compressx-v2-flex-1 compressx-v2-w-full">
                                        <!-- 👇 value display -->
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Chart -->
            <div>
                <!-- Full Width Chart -->
                <div class="compressx-v2-w-full compressx-v2-border compressx-v2-h-96 compressx-v2-bg-gray-50 compressx-v2-rounded">
                    <canvas id="compressxCurveDual"></canvas>
                </div>
            </div>
        </div>
        <script>
            jQuery('.cx-quality-range').change(function(e)
            {
                jQuery(this).prev('span').text(jQuery(this).val());
                jQuery(this).next('span').text(jQuery(this).val());
            });

            jQuery('#compressx_fixed_webp').change(function(e)
            {
                let webp_max=jQuery(this).val();
                jQuery("#cx_webp_max").html(webp_max);
            });

            jQuery('#compressx_fixed_avif').change(function(e)
            {
                let avif_max=jQuery(this).val();
                jQuery("#cx_avif_max").html(avif_max);
            });
        </script>
        <script>
            const ctx = document.getElementById('compressxCurveDual');
            let chart;

            function clamp(v, min, max) {
                return Math.max(min, Math.min(max, v));
            }

            /**
             * JS port of calculate_quality_v2:
             * 1) size <= threshold => fixedQ
             * 2) threshold < size <= 4MB => log curve maxQ -> minQ
             * 3) size > 4MB => minQ
             */
            function calculateQualityV2(sizeKB, thresholdKB, fixedQ, minQ, maxQ, K = 1.6) {
                const Smax = 4000.0; // 4MB

                sizeKB = Math.max(1.0, Number(sizeKB));
                thresholdKB = Math.max(1.0, Number(thresholdKB));

                fixedQ = parseInt(fixedQ, 10);
                minQ = parseInt(minQ, 10);
                maxQ = parseInt(maxQ, 10);

                if (maxQ < minQ) [maxQ, minQ] = [minQ, maxQ];

                fixedQ = clamp(fixedQ, 0, 100);
                minQ = clamp(minQ, 0, 100);
                maxQ = clamp(maxQ, 0, 100);

                // Rule 1: small images fixed
                if (sizeKB <= thresholdKB) return fixedQ;

                // Rule 3: > 4MB uses minQ
                if (sizeKB > Smax) return minQ;

                // Rule 2: in-between curve
                if (thresholdKB >= Smax) return minQ; // degenerate config safeguard

                let ratio = (Math.log(sizeKB) - Math.log(thresholdKB)) / (Math.log(Smax) - Math.log(thresholdKB));
                ratio = clamp(ratio, 0.0, 1.0);

                const rK = Math.pow(ratio, K);

                const Q = maxQ - (maxQ - minQ) * rK;
                return clamp(Q, 0, 100);
            }

            function buildKeySizes(thresholdKB, opts = {})
            {
                const {
                    includeZero = true,
                    sMax = 4000,               // 4MB
                    tail = [6144, 8192, 10240],// 6/8/10MB
                    keys = [100, 200, 300, 400, 500,600,700,800,900, 1024, 2048, 3072, 4000], //
                    maxMidPoints = 13           //
                } = opts;

                thresholdKB = Math.max(1, parseInt(thresholdKB || 200, 10));

                const sizes = [];

                // A) 0 and threshold
                if (includeZero) sizes.push(0);
                sizes.push(thresholdKB);

                // B) threshold..4MB key points
                let midCandidates = keys
                    .filter(k => k > thresholdKB && k < sMax);

                if (midCandidates.length > maxMidPoints) {
                    const step = (midCandidates.length - 1) / (maxMidPoints - 1);
                    const picked = [];
                    for (let i = 0; i < maxMidPoints; i++) {
                        picked.push(midCandidates[Math.round(i * step)]);
                    }
                    midCandidates = Array.from(new Set(picked));
                }

                sizes.push(...midCandidates);

                if (!sizes.includes(sMax)) sizes.push(sMax);

                tail.forEach(t => sizes.push(t));

                return Array.from(new Set(sizes)).sort((a, b) => a - b);
            }

            function generateCurve(format = 'webp') {
                const thresholdKB = parseInt(document.getElementById("compressx_size_threshold")?.value || 200, 10);

                let fixedQ, minQ;
                if (format === 'avif') {
                    fixedQ = parseInt(document.getElementById("compressx_fixed_avif")?.value || 90, 10);
                    minQ = parseInt(document.getElementById("compressx_avif_min")?.value || 60, 10);
                } else {
                    fixedQ = parseInt(document.getElementById("compressx_fixed_webp")?.value || 90, 10);
                    minQ = parseInt(document.getElementById("compressx_webp_min")?.value || 60, 10);
                }

                const maxQ = fixedQ;
                const K = 1.6;

                const sizes = buildKeySizes(thresholdKB, {
                    includeZero: true,
                    maxMidPoints: 10,
                    keys: [100, 200, 300, 400, 500,600,700,800,900, 1024, 2048, 3072, 4000]
                });

                const data = sizes.map(sizeKB => {
                    const safeSize = Math.max(1, sizeKB);
                    return calculateQualityV2(safeSize, thresholdKB, fixedQ, minQ, maxQ, K);
                });

                return { sizes, data };
            }

            function formatSize(kb) {
                kb = Number(kb) || 0;

                if (kb === 4000) return "4MB";

                if (kb === 6144) return "6MB";
                if (kb === 8192) return "8MB";
                if (kb === 10240) return "10MB";

                if (kb < 1024) return `${Math.round(kb)}KB`;

                const mb = kb / 1024;
                return `${mb.toFixed(mb >= 10 ? 0 : 1)}MB`;
            }


            function drawCurves() {
                const webp = generateCurve('webp');
                const avif = generateCurve('avif');

                const labels = webp.sizes.map(formatSize);

                const thresholdKB = parseInt(document.getElementById("compressx_size_threshold")?.value || 200, 10);
                const thresholdLabel = formatSize(thresholdKB);
                const thresholdMaxLabel = formatSize(4000);

                const thresholdLine = {
                    label: 'Threshold',
                    type: 'line',
                    data: [
                        { x: thresholdLabel, y: 0 },
                        { x: thresholdLabel, y: 100 }
                    ],
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                };

                const thresholdLineMax = {
                    label: '4MB',
                    type: 'line',
                    data: [
                        { x: thresholdMaxLabel, y: 0 },
                        { x: thresholdMaxLabel, y: 100 }
                    ],
                    borderColor: '#cbd5e1',
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                };

                if (chart) chart.destroy();

                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'WebP', data: webp.data, borderColor: '#3b82f6', tension: 0.4, fill: false },
                            { label: 'AVIF', data: avif.data, borderColor: '#10b981', tension: 0.4, fill: false },
                            thresholdLine,
                            thresholdLineMax
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: { display: true, text: 'File Size' }
                            },
                            y: {
                                min: 0,
                                max: 100,
                                title: { display: true, text: 'Quality %' }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    title: function(items) {
                                        return `Size: ${items[0].label}`;
                                    },
                                    label: function(item) {
                                        if (item.dataset.label === 'Threshold') return null;
                                        return `${item.dataset.label}: ${Math.round(item.raw)}%`;
                                    }
                                }
                            }
                        }
                    }
                });
            }



            jQuery(function ($) {

                const watchIds = [
                    '#compressx_size_threshold',
                    '#compressx_fixed_webp',
                    '#compressx_fixed_avif',
                    '#compressx_webp_min',
                    '#compressx_avif_min'
                ];

                function updateRangeDisplay($el) {
                    const $span = $el.closest('.compressx-v2-flex').find('span').last();
                    if ($span.length) $span.text($el.val());
                }

                function onAnyControlChange(e) {
                    const $el = $(e.target);

                    if ($el.attr('type') === 'range') {
                        updateRangeDisplay($el);
                    }

                    drawCurves();
                }

                watchIds.forEach(id => {
                    const $el = $(id);
                    if ($el.length && $el.attr('type') === 'range') {
                        updateRangeDisplay($el);
                    }
                });

                $(watchIds.join(',')).on('input change', onAnyControlChange);

                drawCurves();
            });

        </script>
        <?php
    }

    public function output_settings()
    {
        $options = CompressX_Options::get_option('compressx_general_settings', array());

        $interface_version =CompressX_Options::get_interface_version();

        $resize=isset($options['resize']['enable']) ? $options['resize']['enable'] : true;
        $resize_width = isset($options['resize']['width']) ? $options['resize']['width'] : 2560;
        $resize_height = isset($options['resize']['height']) ? $options['resize']['height'] : 2560;
        $remove_exif = isset($options['remove_exif']) ? $options['remove_exif'] : false;
        $auto_remove_larger_format = isset($options['auto_remove_larger_format']) ? $options['auto_remove_larger_format'] : true;
        $converter_images_pre_request = isset($options['converter_images_pre_request']) ? $options['converter_images_pre_request'] : 5;
        $image_load = isset($options['image_load']) ? $options['image_load'] : 'htaccess';
        $exclude_png = isset($options['exclude_png']) ? $options['exclude_png'] : false;
        $exclude_png_webp = isset($options['exclude_png_webp']) ? $options['exclude_png_webp'] : false;

        ?>
        <!--Settings-->
        <section class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-5 compressx-v2-mb-6">
            <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-mb-4"><?php esc_html_e('General Settings', 'compressx') ?></h2>

            <div class="compressx-v2-space-y-5">

                <!-- Change Style -->
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium">
                        <?php esc_html_e('Change Style', 'compressx') ?>

                        <?php
                        $this->output_tooltip(
                            'cx-v2-tip-interface-version',
                            esc_html__('Switch between the new CompressX interface and the previous layout. Use the old style if you experience any display issues.', 'compressx')
                        );
                        ?>
                    </label>
                    <div class="compressx-v2-flex compressx-v2-gap-6 compressx-v2-text-sm">
                        <label class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-interface-v2" type="radio" name="cx-v2-interface-version" value="v2" <?php checked($interface_version, 'v2') ?>>
                            <span><?php esc_html_e('New Style', 'compressx') ?></span>
                        </label>
                        <label class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-interface-v1" type="radio" name="cx-v2-interface-version" value="v1" <?php checked($interface_version, 'v1') ?>>
                            <span><?php esc_html_e('Old Style', 'compressx') ?></span>
                        </label>
                    </div>
                </div>

                <!-- Browser compatibility -->
                <div class="compressx-v2-flex compressx-v2-items-start compressx-v2-gap-3">
                    <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium"><?php esc_html_e('Browser compatibility', 'compressx') ?>
                        <?php $this->output_tooltip('', esc_html__("Choose how to deliver WebP/AVIF images: via .htaccess rewrite rules (standard or compatible for broader support) or picture tags (when .htaccess doesn't work, but excludes CSS images).", 'compressx')); ?>
                    </label>

                    <div class="compressx-v2-space-y-2 compressx-v2-text-sm">
                        <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-browser-htaccess" type="radio" name="cx-v2-browser" value="htaccess" <?php checked($image_load, 'htaccess') ?>>
                            <span><?php esc_html_e('Use rewrite rule (default)', 'compressx') ?></span>
                        </label>
                        <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-browser-compat" type="radio" name="cx-v2-browser" value="compat_htaccess" <?php checked($image_load, 'compat_htaccess') ?>>
                            <span><?php esc_html_e('Compatible rewrite rule (Beta)', 'compressx') ?></span>
                        </label>
                        <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-browser-picture" type="radio" name="cx-v2-browser" value="picture" <?php checked($image_load, 'picture') ?>>
                            <span><?php esc_html_e('Use picture tag', 'compressx') ?></span>
                        </label>
                    </div>
                </div>

                <!-- Max dimensions -->
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium"><?php esc_html_e('Max dimensions', 'compressx') ?>
                        <?php $this->output_tooltip('', esc_html__('This option allows you to enter a width and height, so large images will be proportionately resized upon upload. For example, if you set 1280 px for the width, all large images will be resized in proportion to 1280 px in width upon upload.', 'compressx')); ?>
                    </label>

                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                         <span>
                                <button id="cx-v2-resize-enable" type="button"
                                        class="compressx-v2-relative compressx-v2-flex compressx-v2-items-center compressx-v2-justify-start
                                            compressx-v2-w-11 compressx-v2-h-6
                                            compressx-v2-bg-gray-300 compressx-v2-rounded compressx-v2-transition-colors
                                            compressx-v2-border compressx-v2-border-gray-300"
                                        role="switch" aria-checked="<?php echo $resize ? 'true' : 'false' ?>" data-checked="<?php echo $resize ? '1' : '0' ?>">
                                        <span class="compressx-v2-w-4 compressx-v2-h-4 compressx-v2-bg-white
                                                    compressx-v2-rounded compressx-v2-transition-all
                                                    compressx-v2-mx-0.5"></span>
                                </button>
                         </span>
                        <label for="">
                            <input id="cx-v2-resize-width" type="number" value="<?php echo esc_attr($resize_width) ?>" class="compressx-v2-border compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-px-2 compressx-v2-py-1 compressx-v2-w-24">
                            <span class="compressx-v2-text-gray-500">×</span>
                            <input id="cx-v2-resize-height" type="number" value="<?php echo esc_attr($resize_height) ?>" class="compressx-v2-border compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-px-2 compressx-v2-py-1 compressx-v2-w-24">
                            <span class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('px (images larger than this will be downscaled proportionally)', 'compressx') ?></span>
                        </label>
                    </div>
                </div>

                <!-- EXIF data -->
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium"><?php esc_html_e('EXIF data', 'compressx') ?>
                        <?php $this->output_tooltip('', esc_html__('Remove metadata recorded in images (Only supported by Imagick), including geolocation,timestamps, authorship, image summary, etc. This helps to protect your privacy.', 'compressx')); ?>
                    </label>
                    <div class="compressx-v2-flex compressx-v2-gap-6 compressx-v2-text-sm">
                        <label class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-exif-keep" type="radio" name="cx-v2-exif" value="keep" <?php echo !$remove_exif ? 'checked' : '' ?>>
                            <span><?php esc_html_e('Keep (default)', 'compressx') ?></span>
                        </label>
                        <label class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-exif-strip" type="radio" name="cx-v2-exif" value="strip" <?php echo $remove_exif ? 'checked' : '' ?>>
                            <span><?php esc_html_e('Strip', 'compressx') ?></span>
                        </label>
                    </div>
                </div>

                <!-- Avoid larger files -->
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium"><?php esc_html_e('Avoid larger files', 'compressx') ?>
                        <?php $this->output_tooltip('', esc_html__('Auto-delete larger AVIF/WebP images Automatically delete AVIF/WebP images when they are larger than the original images.', 'compressx')); ?>
                    </label>
                    <label class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-2 cursor-pointer">
                        <button type="button"
                                class="compressx-v2-relative compressx-v2-flex compressx-v2-items-center compressx-v2-justify-start
                                compressx-v2-w-11 compressx-v2-h-6
                                compressx-v2-bg-gray-300 compressx-v2-rounded compressx-v2-transition-colors
                                compressx-v2-border compressx-v2-border-gray-300"
                                role="switch" aria-checked="<?php echo $auto_remove_larger_format ? 'true' : 'false' ?>" id="cx-v2-avoid-larger" data-checked="<?php echo $auto_remove_larger_format ? '1' : '0' ?>">
                            <span class="compressx-v2-w-4 compressx-v2-h-4 compressx-v2-bg-white
                                        compressx-v2-rounded compressx-v2-transition-all
                                        compressx-v2-mx-0.5"></span>
                        </button>
                        <span class="compressx-v2-text-sm"><?php esc_html_e('If optimized file is larger, keep original', 'compressx') ?></span>
                    </label>
                </div>

                <!-- Format exclusions -->
                <div class="compressx-v2-flex compressx-v2-items-start compressx-v2-gap-3">
                    <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium"><?php esc_html_e('Format exclusions', 'compressx') ?>
                        <?php $this->output_tooltip('', esc_html__('Select the formats you want to exclude from conversion. This can help reduce server load and improve performance.', 'compressx')); ?>
                    </label>
                    <div class="compressx-v2-space-y-2 compressx-v2-text-sm">
                        <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-exclude-png-webp" type="checkbox" <?php checked($exclude_png_webp) ?>>
                            <span><?php esc_html_e('Do not convert PNG to WebP', 'compressx') ?></span>
                        </label>
                        <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <input id="cx-v2-exclude-png" type="checkbox" <?php checked($exclude_png) ?>>
                            <span><?php esc_html_e('Do not convert PNG to AVIF', 'compressx') ?></span>
                        </label>
                    </div>
                </div>

                <!-- Queue throughput -->
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium"><?php esc_html_e('Queue throughput', 'compressx') ?>
                        <?php $this->output_tooltip('', esc_html__('Number of WordPress image attachments (including original and thumbnails) processed per AJAX cycle. Default is 5.', 'compressx')); ?>
                    </label>
                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <select id="cx-v2-throughput" class="compressx-v2-border compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-px-2 compressx-v2-py-1">
                            <option value="5" <?php selected($converter_images_pre_request, 5) ?>><?php esc_html_e('Process 5 images per batch (recommended)', 'compressx') ?></option>
                            <option value="10" <?php selected($converter_images_pre_request, 10) ?>><?php esc_html_e('Process 10 images per batch', 'compressx') ?></option>
                            <option value="20" <?php selected($converter_images_pre_request, 20) ?>><?php esc_html_e('Process 20 images per batch', 'compressx') ?></option>
                            <option value="1" <?php selected($converter_images_pre_request, 1) ?>><?php esc_html_e('Process 1 image per batch (safest)', 'compressx') ?></option>
                        </select>
                        <span class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Est. ~300 images/hour (depends on server performance)', 'compressx') ?></span>
                    </div>
                </div>

                <?php
                $this->output_cron_watermark_placeholders();
                ?>

            </div>

            <div class="compressx-v2-mt-6 compressx-v2-flex compressx-v2-justify-start">
                <a href="<?php echo esc_url(admin_url('admin.php?page=settings-compressx')) ?>"
                   class="compressx-v2-text-sm compressx-v2-text-blue-600 hover:compressx-v2-underline">
                    ⚙️ <?php esc_html_e('View More Settings', 'compressx') ?>
                </a>
            </div>
        </section>
        <?php
    }

    private function output_cron_watermark_placeholders()
    {
        ?>
        <div class="compressx-v2-flex compressx-v2-items-start compressx-v2-gap-3 compressx-v2-opacity-50">
            <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium">
                <?php esc_html_e('New Upload (Cron)', 'compressx') ?>
                <?php $this->output_tooltip('', esc_html__('Choose when to process new uploads: immediately (real-time but may delay availability), after a delay (recommended to avoid workflow interruption), or during scheduled low-traffic windows (minimizes server impact).', 'compressx')); ?>
                <a href="https://compressx.io/pricing" target="_blank" class="compressx-v2-text-blue-600"><?php esc_html_e('Pro only', 'compressx') ?></a>
            </label>

            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2 compressx-v2-pointer-events-none">
                <div>
                    <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-mb-4 compressx-v2-gap-2">
                        <input type="radio" name="cx-v2-cron-uploads" value="immediate" disabled>
                        <div>
                            <span class="compressx-v2-text-sm compressx-v2-font-medium text-gray-800"><?php esc_html_e('Process immediately on upload', 'compressx') ?></span>
                            <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                <?php esc_html_e('Images are optimized right after uploading. May cause short delays on image availability during conversion.', 'compressx') ?>
                            </p>
                        </div>
                    </label>

                    <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-mb-4 compressx-v2-gap-2">
                        <input type="radio" name="cx-v2-cron-uploads" value="delay" checked disabled>
                        <div>
                            <span class="compressx-v2-text-sm compressx-v2-font-medium text-gray-800"><?php esc_html_e('(Recommended) Delay processing', 'compressx') ?></span>
                            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2 compressx-v2-mt-1">
                                <span class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Process images after', 'compressx') ?></span>
                                <select class="compressx-v2-border compressx-v2-rounded compressx-v2-text-sm compressx-v2-px-2 compressx-v2-py-1" disabled>
                                    <option>1</option>
                                    <option>3</option>
                                    <option>5</option>
                                    <option>10</option>
                                </select>
                                <span class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('minutes of uploading', 'compressx') ?></span>
                            </div>
                            <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                <?php esc_html_e('Helps avoid slowdowns during peak uploads by deferring conversion slightly.', 'compressx') ?>
                            </p>
                        </div>
                    </label>

                    <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <input type="radio" name="cx-v2-cron-uploads" value="schedule" disabled>
                        <div>
                            <span class="compressx-v2-text-sm compressx-v2-font-medium text-gray-800"><?php esc_html_e('Scheduled processing window', 'compressx') ?></span>
                            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2 compressx-v2-mt-1">
                                <span class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Daily:', 'compressx') ?></span>
                                <input type="time" value="00:00" class="compressx-v2-border compressx-v2-rounded compressx-v2-text-sm compressx-v2-px-2" disabled>
                                <span class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('to', 'compressx') ?></span>
                                <input type="time" value="06:00" class="compressx-v2-border compressx-v2-rounded compressx-v2-text-sm compressx-v2-px-2" disabled>
                            </div>
                            <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                <?php esc_html_e('Process new uploads only within a set time window (e.g. overnight) to reduce server load.', 'compressx') ?>
                            </p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3 compressx-v2-opacity-50">
            <label class="compressx-v2-w-56 compressx-v2-text-sm compressx-v2-font-medium">
                <?php esc_html_e('Watermark (optional)', 'compressx') ?>
                <?php $this->output_tooltip('', esc_html__('Enable this option to automatically tag newly uploaded images, making them ready for batch watermarking. This feature allows you to easily identify and process recent uploads for watermark application, streamlining your image protection process.', 'compressx')); ?>
                <a href="https://compressx.io/pricing" target="_blank" class="compressx-v2-text-blue-600"><?php esc_html_e('Pro only', 'compressx') ?></a>
            </label>
            <label class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-2 cursor-pointer compressx-v2-pointer-events-none">
                <button type="button"
                        class="compressx-v2-relative compressx-v2-flex compressx-v2-items-center compressx-v2-justify-start
                        compressx-v2-w-11 compressx-v2-h-6
                        compressx-v2-bg-gray-300 compressx-v2-rounded compressx-v2-transition-colors
                        compressx-v2-border compressx-v2-border-gray-300"
                        role="switch" aria-checked="false" disabled>
                    <span class="compressx-v2-w-4 compressx-v2-h-4 compressx-v2-bg-white
                                compressx-v2-rounded compressx-v2-transition-all
                                compressx-v2-mx-0.5"></span>
                </button>
                <span class="compressx-v2-text-sm"><?php esc_html_e('Mark new uploads for watermarking', 'compressx') ?></span>
            </label>
        </div>
        <?php
    }

    public function get_bulk_progress()
    {
        $stats = CompressX_Image_Meta_V2::get_global_stats_ex();
        if(isset($stats['converted_percent']))
            return $stats['converted_percent'];
        else
            return 0;
    }

    public function output_save_section()
    {
        ?>
        <section class="compressx-v2-sticky compressx-v2-bottom-0 compressx-v2-bg-white compressx-v2-border-t compressx-v2-border-gray-200 compressx-v2-p-4">
            <div class="compressx-v2-max-w-[1200px] compressx-v2-mx-auto compressx-v2-flex compressx-v2-justify-end compressx-v2-items-center compressx-v2-gap-3">
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                    <button id="cx-v2-save-settings" class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-1 compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded">
                        <?php esc_html_e('Save Changes', 'compressx') ?>
                    </button>

                    <span id="cx-v2-save-settings-progress" class="compressx-v2-flex compressx-v2-items-center compressx-v2-hidden">
                        <img src="<?php echo esc_url(is_network_admin() ? network_admin_url('images/loading.gif') : admin_url('images/loading.gif')); ?>" alt="Loading..." style="width: 16px; height: 16px;">
                    </span>

                    <span id="cx-v2-save-settings-text" class="success compressx-v2-hidden compressx-v2-text-sm compressx-v2-font-medium" style="color:#007017"><?php esc_html_e('Saved!', 'compressx') ?></span>
                </div>
            </div>
        </section>
        <?php
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    public function output_tooltip($id, $content, $button_size = 'medium')
    {
        if (empty($content)) {
            return;
        }

        // Button size classes
        $size_classes = array(
            'small' => 'compressx-v2-h-4 compressx-v2-w-4',
            'medium' => 'compressx-v2-h-5 compressx-v2-w-5',
            'large' => 'compressx-v2-h-6 compressx-v2-w-6'
        );

        $button_class = isset($size_classes[$button_size]) ? $size_classes[$button_size] : $size_classes['medium'];
        ?>
        <div class="compressx-v2-relative compressx-v2-inline-flex compressx-v2-items-center compressx-v2-group">
            <button
                    type="button"
                    class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-justify-center <?php echo esc_attr($button_class) ?> compressx-v2-rounded compressx-v2-border compressx-v2-border-slate-300 compressx-v2-bg-white hover:compressx-v2-bg-slate-50 compressx-v2-text-slate-600 hover:compressx-v2-text-slate-800 compressx-v2-shadow-sm focus:compressx-v2-outline-none focus:compressx-v2-ring-2 focus:compressx-v2-ring-sky-400"
                    aria-describedby="<?php echo esc_attr($id) ?>"
                    data-tooltip-toggle>
                <span class="compressx-v2-font-semibold compressx-v2-text-xs">i</span>
            </button>

            <div
                    id="<?php echo esc_attr($id) ?>"
                    role="tooltip"
                    class="compressx-v2-absolute compressx-v2-z-50 compressx-v2-bottom-full compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-mb-2
                    compressx-v2-hidden group-hover:compressx-v2-block group-focus-within:compressx-v2-block
                    compressx-v2-min-w-64 compressx-v2-max-w-96 compressx-v2-rounded compressx-v2-bg-slate-900/95 compressx-v2-text-white compressx-v2-text-xs compressx-v2-leading-5 compressx-v2-px-3 compressx-v2-py-3
                    compressx-v2-shadow-xl compressx-v2-ring-1 compressx-v2-ring-black/10">
                <div class="compressx-v2-flex compressx-v2-gap-2 compressx-v2-items-start">
                    <span class="compressx-v2-mt-0.5 compressx-v2-inline-block compressx-v2-h-1.5 compressx-v2-w-1.5 compressx-v2-rounded compressx-v2-bg-emerald-400"></span>
                    <div>
                        <div class="compressx-v2-font-medium compressx-v2-text-[11px] compressx-v2-tracking-wide compressx-v2-text-emerald-300 compressx-v2-mb-0.5">
                            <?php esc_html_e('Tip', 'compressx') ?>
                        </div>
                        <div>
                            <?php echo wp_kses_post($content) ?>
                        </div>
                    </div>
                </div>
                <!-- Caret -->
                <div class="compressx-v2-absolute compressx-v2-left-1/2 -compressx-v2-translate-x-1/2 compressx-v2-top-full compressx-v2-h-2 compressx-v2-w-2 compressx-v2-rotate-45 compressx-v2-bg-slate-900/95"></div>
            </div>
        </div>
        <?php
    }

    public function set_setting()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-general-settings');

        if (isset($_POST['settings']) && !empty($_POST['settings']))
        {
            $json_setting = sanitize_text_field($_POST['settings']);
            $json_setting = stripslashes($json_setting);
            $setting = json_decode($json_setting, true);
            if (is_null($setting)) {
                $ret['result'] = 'failed';
                $ret['error'] = 'json decode failed';
                echo wp_json_encode($ret);
                die();
            }

            if (isset($setting['auto_optimize']))
            {
                if ($setting['auto_optimize'] == '1')
                {
                    $options = true;
                } else {
                    $options = false;
                }

                if (CompressX_Options::get_option('compressx_show_review', false) === false)
                {
                    CompressX_Options::update_option('compressx_show_review', time() + 259200);
                }
                CompressX_Options::update_option('compressx_auto_optimize', $options);
            }

            if (isset($setting['convert_to_webp']))
            {
                if ($setting['convert_to_webp'] == '1')
                {
                    $options = 1;
                } else {
                    $options = 0;
                }

                CompressX_Options::update_option('compressx_output_format_webp', $options);
            }

            if (isset($setting['convert_to_avif']))
            {
                if ($setting['convert_to_avif'] == '1')
                {
                    $options = 1;
                } else {
                    $options = 0;
                }

                CompressX_Options::update_option('compressx_output_format_avif', $options);
            }

            if (isset($setting['converter_method'])&&!empty($setting['converter_method']))
            {
                $converter_method = $setting['converter_method'];
                CompressX_Options::update_option('compressx_converter_method', $converter_method);
            }

            if (isset($setting['quality_webp'])&&isset($setting['quality_avif']))
            {
                $quality_options['quality'] = "custom";
                $quality_options['quality_webp'] = isset($setting['quality_webp']) ? $setting['quality_webp'] : 80;
                $quality_options['quality_avif'] = isset($setting['quality_avif']) ? $setting['quality_avif'] : 60;

                CompressX_Options::update_option('compressx_quality', $quality_options);
            }

            $options = CompressX_Options::get_option('compressx_general_settings', array());

            if (isset($setting['remove_exif']))
                $options['remove_exif'] = $setting['remove_exif'];
            if (isset($setting['exclude_png']))
                $options['exclude_png'] = $setting['exclude_png'];
            if (isset($setting['exclude_png_webp']))
                $options['exclude_png_webp'] = $setting['exclude_png_webp'];
            //
            if (isset($setting['auto_remove_larger_format']))
                $options['auto_remove_larger_format'] = $setting['auto_remove_larger_format'];

            $interface_version_changed=false;

            if (isset($setting['interface_version'])) {
                $old_interface_version = isset($options['interface_version']) ? $options['interface_version'] : 'v1';
                if ($old_interface_version !== $setting['interface_version']) {
                    $interface_version_changed = true;
                }
                $options['interface_version'] = $setting['interface_version'];
            }

            $reset_rewrite=false;
            if (isset($setting['image_load'])) {
                if (!isset($options['image_load'])) {
                    $options['image_load'] = 'htaccess';
                }

                if ($options['image_load'] != $setting['image_load'])
                    $reset_rewrite = true;

                $options['image_load'] = $setting['image_load'];
            }


            if (isset($setting['resize']))
            {
                if($setting['resize']=='1')
                {
                    $options['resize']['enable']=true;
                }
                else
                {
                    $options['resize']['enable']=false;
                }
            }

            if (isset($setting['resize_width']))
                $options['resize']['width'] = $setting['resize_width'];
            if (isset($setting['resize_height']))
                $options['resize']['height'] = $setting['resize_height'];

            if (isset($setting['converter_images_pre_request']))
                $options['converter_images_pre_request'] = intval($setting['converter_images_pre_request']);
            //
            if ($options['image_load'] == 'htaccess')
            {
                if ($reset_rewrite)
                {
                    include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

                    $rewrite = new CompressX_Webp_Rewrite();
                    $rewrite->create_rewrite_rules();
                    $ret['test'] = '1';
                }
            } else if ($options['image_load'] == 'compat_htaccess') {
                if ($reset_rewrite) {
                    include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';

                    $rewrite = new CompressX_Webp_Rewrite();
                    $rewrite->create_rewrite_rules_ex();
                    $ret['test'] = '1';
                }
            } else {
                include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';
                $rewrite = new CompressX_Webp_Rewrite();
                $rewrite->remove_rewrite_rule();
            }

            CompressX_Options::update_option('compressx_general_settings', $options);

            if ($interface_version_changed)
            {
                $ret['interface_version_changed']=true;
            }
            else
            {
                $ret['interface_version_changed']=false;
            }
            $ret['result'] = 'success';
            echo wp_json_encode($ret);
            die();
        }
        else
        {
            die();
        }
    }

    public function compressx_rating_dismiss()
    {
        global $compressx;
        $compressx->ajax_check_security();

        if (isset($_POST['value'])) {
            $value = sanitize_text_field($_POST['value']);
            if ($value == 'ask_me_later') {
                $time = time() + 259200;
                CompressX_Options::update_option('compressx_rating_dismiss', $time);
            }
            if ($value == 'close') {
                $time = time() + 604800;
                CompressX_Options::update_option('compressx_rating_dismiss', $time);
            } else if ($value == 'already') {
                CompressX_Options::update_option('compressx_rating_dismiss', 0);
            } else if ($value == 'dismiss') {
                CompressX_Options::update_option('compressx_rating_dismiss', 0);
            }
        }

        die();
    }

    public function hide_big_update()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-image-optimization');

        CompressX_Options::update_option('compressx_hide_big_update', true);

        die();
    }
}