<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_CDN_Display
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_save_cdn', array($this, 'save_cdn'));
        add_action('wp_ajax_compressx_purge_cache', array($this, 'purge_cache'));
    }

    public function display()
    {
        ?>
        <div class="compressx-root">
            <div class="compressx-v2-py-6 compressx-v2-w-full compressx-v2-max-w-[1200px] compressx-v2-mx-auto">
                <?php
                $this->output_header();
                $this->output_cdn();
                $this->output_save_section();
                $this->output_footer();
                ?>
            </div>
        </div>
        <?php
    }

    public function output_cdn()
    {
        $options = CompressX_Options::get_option('compressx_general_settings', array());

        $zone_id = isset($options['cf_cdn']['zone_id']) ? $options['cf_cdn']['zone_id'] : '';
        $email = isset($options['cf_cdn']['email']) ? $options['cf_cdn']['email'] : '';
        $api_key = isset($options['cf_cdn']['api_key']) ? $options['cf_cdn']['api_key'] : '';

        $auto_purge_cache = isset($options['cf_cdn']['auto_purge_cache']) ? $options['cf_cdn']['auto_purge_cache'] : true;
        $auto_purge_cache_after_manual = isset($options['cf_cdn']['auto_purge_cache_after_manual']) ? $options['cf_cdn']['auto_purge_cache_after_manual'] : true;

        $auto_purge_cache_checked = $auto_purge_cache ? 'checked' : '';
        $auto_purge_cache_after_manual_checked = $auto_purge_cache_after_manual ? 'checked' : '';
        ?>

        <section class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-mb-4 compressx-v2-p-6">
            <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-border compressx-v2-mb-4 compressx-v2-p-6">
                <h2 class="compressx-v2-text-lg compressx-v2-font-medium"><?php esc_html_e('CDN Provider', 'compressx'); ?></h2>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-500"><?php esc_html_e('Select your CDN provider to configure integration details.', 'compressx'); ?></p>

                <select id="compressx_cdn_provider" class="compressx-v2-mt-3 compressx-v2-w-64 compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-text-sm">
                    <option value="cloudflare"><?php esc_html_e('Cloudflare', 'compressx'); ?></option>
                    <option value="bunnycdn"><?php esc_html_e('BunnyCDN', 'compressx'); ?></option>
                </select>
            </div>

            <div id="cloudflare_container">
                <?php
                $this->render_cloudflare_settings($zone_id, $email, $api_key);
                $this->render_cloudflare_features($auto_purge_cache_checked, $auto_purge_cache_after_manual_checked);
                ?>
            </div>

            <?php $this->render_bunnycdn_placeholder(); ?>
        </section>
        <?php
    }

    public function render_cloudflare_settings($zone_id = '', $email = '', $api_key = '')
    {
        ?>
        <div id="cloudflare_settings" class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-border compressx-v2-mb-4 compressx-v2-p-6">
            <h2 class="compressx-v2-text-lg compressx-v2-font-medium"><?php esc_html_e('Cloudflare Integration', 'compressx'); ?></h2>
            <p class="compressx-v2-text-sm compressx-v2-text-gray-500"><?php esc_html_e('Provide credentials to allow CompressX to connect with Cloudflare.', 'compressx'); ?></p>

            <div class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-4 compressx-v2-mt-4">
                <div>
                    <label class="compressx-v2-text-sm">
                        <?php esc_html_e('Global API Key', 'compressx'); ?>
                        <?php
                        $this->output_tooltip(
                            'cf-api-key-tip',
                            __('A key for granting access to Cloudflare API to perform actions. You can find it in your Cloudflare dashboard > My Profile > API Tokens > Global API Key.', 'compressx'),
                            'small'
                        );
                        ?>
                    </label>
                    <input type="password" autocomplete="new-password" option="cf_cdn" name="api_key" value="<?php echo esc_attr($api_key); ?>" class="compressx-v2-w-full compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2">
                </div>
                <div>
                    <label class="compressx-v2-text-sm">
                        <?php esc_html_e('Cloudflare Email', 'compressx'); ?>
                        <?php
                        $this->output_tooltip(
                            'cf-email-tip',
                            __('Email address associated to your Cloudflare account.', 'compressx'),
                            'small'
                        );
                        ?>
                    </label>
                    <input type="text" option="cf_cdn" name="email" value="<?php echo esc_attr($email); ?>" class="compressx-v2-w-full compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2">
                </div>
                <div>
                    <label class="compressx-v2-text-sm">
                        <?php esc_html_e('Zone ID', 'compressx'); ?>
                        <?php
                        $this->output_tooltip(
                            'cf-zone-id-tip',
                            __('A zone ID is generated automatically when a domain is added to Cloudflare and is required for API operations. You can find it in your Cloudflare Dashboard > The website overview > API section.', 'compressx'),
                            'small'
                        );
                        ?>
                    </label>
                    <input type="password" autocomplete="new-password" option="cf_cdn" name="zone_id" value="<?php echo esc_attr($zone_id); ?>" class="compressx-v2-w-full compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2">
                </div>
            </div>
        </div>
        <?php
    }

    public function render_cloudflare_features($auto_purge_cache_checked = '', $auto_purge_cache_after_manual_checked = '')
    {
        ?>
        <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-border compressx-v2-p-6">
            <h2 class="compressx-v2-text-lg compressx-v2-font-medium"><?php esc_html_e('Features', 'compressx'); ?></h2>

            <div id="cloudflare_features" class="compressx-v2-mt-3 compressx-v2-space-y-3">
                <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                    <input type="checkbox" option="cf_cdn" name="auto_purge_cache" <?php echo esc_attr($auto_purge_cache_checked); ?> class="compressx-v2-mt-1">
                    <span class="compressx-v2-text-sm"><?php esc_html_e('Purge cache automatically after bulk image optimization.', 'compressx'); ?></span>
                </label>

                <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                    <input type="checkbox" option="cf_cdn" name="auto_purge_cache_after_manual" <?php echo esc_attr($auto_purge_cache_after_manual_checked); ?> class="compressx-v2-mt-1">
                    <span class="compressx-v2-text-sm"><?php esc_html_e('Purge cache automatically after manual image conversions (5 min delay).', 'compressx'); ?></span>
                </label>

                <div class="compressx-v2-mt-4">
                    <button id="compressx_purge_cache" class="compressx-v2-border compressx-v2-text-sm compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 hover:compressx-v2-bg-gray-100">
                        <?php esc_html_e('Purge All Cloudflare CDN Cache Manually', 'compressx'); ?>
                    </button>
                    <span id="compressx_purge_cache_progress" style="display: none" class="compressx-v2-text-sm compressx-v2-text-blue-600 compressx-v2-ml-2">
                        <?php esc_html_e('Processing...', 'compressx'); ?>
                    </span>
                    <span id="compressx_purge_cache_text" class="success hidden compressx-v2-text-sm compressx-v2-text-green-600 compressx-v2-ml-2" aria-hidden="true">
                        <?php esc_html_e('Done!', 'compressx'); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_bunnycdn_placeholder()
    {
        ?>
        <div id="bunnycdn_container" style="display:none">
            <div id="bunnycdn_settings" class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-border compressx-v2-mb-4 compressx-v2-p-6 compressx-v2-relative compressx-v2-opacity-60">
                <div class="compressx-v2-absolute compressx-v2-inset-0 compressx-v2-bg-white compressx-v2-bg-opacity-50 compressx-v2-rounded compressx-v2-cursor-not-allowed" style="z-index: 1;"></div>

                <h2 class="compressx-v2-text-lg compressx-v2-font-medium"><?php esc_html_e('BunnyCDN Integration', 'compressx'); ?>

                </h2>

                <p class="compressx-v2-text-sm compressx-v2-text-gray-500"><?php esc_html_e('Provide credentials to allow CompressX to connect with BunnyCDN.', 'compressx'); ?>
                    <a href="https://compressx.io/pricing" target="_blank" class="compressx-v2-text-blue-600"><?php esc_html_e('Pro only', 'compressx') ?></a></p>

                <div class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-4 compressx-v2-mt-4">
                    <div>
                        <label class="compressx-v2-text-sm"><?php esc_html_e('Access Key', 'compressx'); ?>
                        </label>
                        <input type="password" autocomplete="new-password" disabled placeholder="<?php esc_attr_e('Pro Feature', 'compressx'); ?>" class="compressx-v2-w-full compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-bg-gray-50">
                    </div>

                    <div>
                        <label class="compressx-v2-text-sm"><?php esc_html_e('Pull Zone ID', 'compressx'); ?>
                        </label>
                        <input type="text" disabled placeholder="<?php esc_attr_e('Pro Feature', 'compressx'); ?>" class="compressx-v2-w-full compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-bg-gray-50">
                    </div>
                </div>
            </div>

            <!-- BunnyCDN Features (Disabled) -->
            <div id="bunnycdn_features" class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-border compressx-v2-p-6 compressx-v2-relative compressx-v2-opacity-60">
                <div class="compressx-v2-absolute compressx-v2-inset-0 compressx-v2-bg-white compressx-v2-bg-opacity-50 compressx-v2-rounded compressx-v2-cursor-not-allowed" style="z-index: 1;"></div>

                <h2 class="compressx-v2-text-lg compressx-v2-font-medium"><?php esc_html_e('Features', 'compressx'); ?></h2>

                <div class="compressx-v2-mt-3 compressx-v2-space-y-3">
                    <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <input type="checkbox" disabled class="compressx-v2-mt-1">
                        <span class="compressx-v2-text-sm">
                            <?php esc_html_e('Enable', 'compressx'); ?> <strong><?php esc_html_e('Vary for Images', 'compressx'); ?></strong>
                            <?php esc_html_e('(ensures proper delivery of image variants across browsers).', 'compressx'); ?>
                        </span>
                    </label>

                    <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <input type="checkbox" disabled class="compressx-v2-mt-1">
                        <span class="compressx-v2-text-sm"><?php esc_html_e('Purge cache automatically after bulk image optimization.', 'compressx'); ?></span>
                    </label>

                    <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <input type="checkbox" disabled class="compressx-v2-mt-1">
                        <span class="compressx-v2-text-sm"><?php esc_html_e('Purge cache automatically after manual image conversions (5 min delay).', 'compressx'); ?></span>
                    </label>

                    <div class="compressx-v2-mt-4">
                        <button disabled class="compressx-v2-border compressx-v2-text-sm compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-bg-gray-50 compressx-v2-cursor-not-allowed">
                            <?php esc_html_e('Purge All BunnyCDN Cache Manually', 'compressx'); ?>
                        </button>
                    </div>
                </div>
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
                    <?php echo esc_html(__('CDN Support', 'compressx')); ?>
                </h1>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-2">
                    <?php echo esc_html(__('Connect CompressX with your CDN to deliver optimized images worldwide, with automatic cache purging and faster performance.', 'compressx')); ?>
                </p>
            </div>
        </div>
        <?php
    }

    public function output_save_section()
    {
        ?>
        <section class="compressx-v2-sticky compressx-v2-bottom-0 compressx-v2-bg-white compressx-v2-border-t compressx-v2-border-gray-200 compressx-v2-p-4">
            <div class="compressx-v2-max-w-[1200px] compressx-v2-mx-auto compressx-v2-flex compressx-v2-justify-end compressx-v2-gap-3">
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-3">
                    <button id="compressx_save_cdn" class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-1 compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded">
                        <?php esc_html_e('Save Changes', 'compressx'); ?>
                    </button>
                    <span id="compressx_save_cdn_progress" style="display: none" class="compressx-v2-text-sm compressx-v2-text-blue-600">
                        <?php esc_html_e('Saving...', 'compressx'); ?>
                    </span>
                    <span id="compressx_save_cdn_text" class="success hidden compressx-v2-text-sm compressx-v2-text-green-600" aria-hidden="true">
                        <?php esc_html_e('Saved!', 'compressx'); ?>
                    </span>
                </div>
            </div>
        </section>
        <?php
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

    public function output_footer()
    {
        do_action('compressx_output_footer');
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

            if(isset($setting['zone_id'])) {
                $options['cf_cdn']['zone_id']=$setting['zone_id'];
            }
            if(isset($setting['email'])) {
                $options['cf_cdn']['email']=$setting['email'];
            }
            if(isset($setting['api_key'])) {
                $options['cf_cdn']['api_key']=$setting['api_key'];
            }
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