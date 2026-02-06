<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_System_Info_Display
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_open_log', array($this, 'open_log'));
        add_action('wp_ajax_compressx_download_log', array($this, 'download_log'));
        add_action('wp_ajax_compressx_delete_log', array($this, 'delete_log'));
        add_action('wp_ajax_compressx_delete_all_log', array($this, 'delete_all_log'));
        add_action('wp_ajax_compressx_get_logs_list', array($this, 'get_logs_list'));
        add_action('wp_ajax_compressx_create_debug_package', array($this, 'create_debug_package'));
        add_action('wp_ajax_compressx_send_debug_info', array($this, 'send_debug_info'));
    }

    public function display()
    {
        ?>
        <div class="compressx-root">
            <div class="compressx-v2-py-6 compressx-v2-w-full compressx-v2-max-w-[1200px] compressx-v2-mx-auto">
                <?php
                $this->output_header();
                $this->output_system_information();
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
                    <?php echo esc_html( __('Logs & System Information', 'compressx')); ?>
                </h1>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-2">
                    <?php echo esc_html(__('Access error logs and system details to quickly diagnose issues and ensure CompressX is running smoothly.', 'compressx')); ?>
                </p>
            </div>
        </div>
        <?php
    }

    private function output_system_information()
    {
        $debug_info = $this->get_debug_info();
        ?>
        <section class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-6 compressx-v2-mb-6">
            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-6">
                <h2 class="compressx-v2-text-xl compressx-v2-font-semibold compressx-v2-text-gray-800">
                    <?php esc_html_e('System Information', 'compressx'); ?>
                </h2>
                <button id="compressx_show_debug_form" class="compressx-v2-bg-blue-600 compressx-v2-text-white compressx-v2-px-5 compressx-v2-py-2 compressx-v2-rounded hover:compressx-v2-bg-blue-700">
                    <?php esc_html_e('Send Debug Info', 'compressx'); ?>
                </button>
            </div>

            <div class="compressx-v2-grid compressx-v2-grid-cols-1 lg:compressx-v2-grid-cols-4 compressx-v2-mb-4 compressx-v2-gap-6">
                <!-- Environment -->
                <div class="compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4">
                    <h3 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-mb-2">🌐 <?php esc_html_e('Environment', 'compressx'); ?></h3>
                    <ul class="compressx-v2-space-y-1 compressx-v2-text-sm">
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Server:', 'compressx'); ?></span> <?php echo esc_html($debug_info['Web Server'] ?? 'Unknown'); ?></li>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('WordPress:', 'compressx'); ?></span> <?php echo esc_html($debug_info['Wordpress version'] ?? 'Unknown'); ?></li>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('PHP:', 'compressx'); ?></span> <?php echo esc_html($debug_info['PHP version'] ?? 'Unknown'); ?>
                            <?php if (version_compare($debug_info['PHP version'] ?? '0', '7.4', '>=')): ?>
                                <span class="compressx-v2-ml-2 compressx-v2-text-xs compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('OK', 'compressx'); ?></span>
                            <?php endif; ?>
                        </li>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Home Url:', 'compressx'); ?></span> <span><?php echo esc_html($debug_info['home'] ?? ''); ?></span></li>
                    </ul>
                </div>

                <!-- Image Libraries -->
                <div class="compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4">
                    <h3 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-mb-2">🖼️ <?php esc_html_e('Image Libraries', 'compressx'); ?></h3>
                    <ul class="compressx-v2-space-y-1 compressx-v2-text-sm">
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('GD Extension:', 'compressx'); ?></span>
                            <?php
                            if (function_exists('gd_info'))
                            {
                                esc_html_e('Installed', 'compressx'); ?><span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Active', 'compressx'); ?></span><?php
                            }
                            else
                            {
                                esc_html_e('Not Installed', 'compressx'); ?> <span class="compressx-v2-ml-1 compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Missing', 'compressx'); ?></span><?php
                            }
                            ?>
                        </li>
                        <?php
                        if (function_exists('gd_info'))
                        {
                            ?>
                            <li><span class="compressx-v2-font-medium"><?php esc_html_e('WebP:', 'compressx'); ?></span>
                                <?php echo esc_html($debug_info['GD WebP Support'] ? 'true' : 'false'); ?>
                                <?php if ($debug_info['GD WebP Support']): ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Supported', 'compressx'); ?></span>
                                <?php else: ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Unsupported', 'compressx'); ?></span>
                                <?php endif; ?>
                            </li>
                            <li><span class="compressx-v2-font-medium"><?php esc_html_e('AVIF:', 'compressx'); ?></span>
                                <?php echo esc_html($debug_info['GD AVIF Support'] ? 'true' : 'false'); ?>
                                <?php if ($debug_info['GD AVIF Support']): ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Supported', 'compressx'); ?></span>
                                <?php else: ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Unsupported', 'compressx'); ?></span>
                                <?php endif; ?>
                            </li>
                            <?php
                        }
                        ?>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Imagick:', 'compressx'); ?></span>
                            <?php if (extension_loaded('imagick') && class_exists('\Imagick')): ?>
                                <?php esc_html_e('Installed', 'compressx'); ?> <span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Active', 'compressx'); ?></span>
                            <?php else: ?>
                                <?php esc_html_e('Not Installed', 'compressx'); ?> <span class="compressx-v2-ml-1 compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Missing', 'compressx'); ?></span>
                            <?php endif; ?>
                        </li>
                        <?php
                        if (extension_loaded('imagick') && class_exists('\Imagick'))
                        {
                            ?>
                            <li><span class="compressx-v2-font-medium"><?php esc_html_e('WebP:', 'compressx'); ?></span>
                                <?php echo esc_html($debug_info['Imagick WebP Support'] ? 'true' : 'false'); ?>
                                <?php if ($debug_info['Imagick WebP Support']): ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Supported', 'compressx'); ?></span>
                                <?php else: ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Unsupported', 'compressx'); ?></span>
                                <?php endif; ?>
                            </li>
                            <li><span class="compressx-v2-font-medium"><?php esc_html_e('AVIF:', 'compressx'); ?></span>
                                <?php echo esc_html($debug_info['Imagick AVIF Support'] ? 'true' : 'false'); ?>
                                <?php if ($debug_info['Imagick AVIF Support']): ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Supported', 'compressx'); ?></span>
                                <?php else: ?>
                                    <span class="compressx-v2-ml-1 compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Unsupported', 'compressx'); ?></span>
                                <?php endif; ?>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4">
                    <h3 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-mb-2">⚡ <?php esc_html_e('Resources', 'compressx'); ?></h3>
                    <ul class="compressx-v2-space-y-1 compressx-v2-text-sm">
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Memory Current:', 'compressx'); ?></span> <?php echo esc_html($debug_info['memory_current'] ?? 'Unknown'); ?></li>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Memory Peak:', 'compressx'); ?></span> <?php echo esc_html($debug_info['memory_peak'] ?? 'Unknown'); ?></li>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Memory Limit:', 'compressx'); ?></span> <?php echo esc_html($debug_info['memory_limit'] ?? 'Unknown'); ?>
                            <?php if (intval($debug_info['memory_limit'] ?? 0) >= 256): ?>
                                <span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('OK', 'compressx'); ?></span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>

                <!-- WordPress Setup -->
                <div class="compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-4">
                    <h3 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-mb-2">⚙️ <?php esc_html_e('WordPress Setup', 'compressx'); ?></h3>
                    <ul class="compressx-v2-space-y-1 compressx-v2-text-sm">
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Theme:', 'compressx'); ?></span> <?php echo esc_html($debug_info['active_theme'] ?? 'Unknown'); ?></li>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Plugins:', 'compressx'); ?></span>
                            <?php
                            $plugins = get_plugins();

                            $plugin_count = count($plugins);
                            if ($plugin_count > 50)
                            {
                                $plugin_count= '50+';
                            }
                            echo esc_html($plugin_count.' installed');
                            ?>
                            <button class="compressx-v2-text-blue-600 compressx-v2-text-xs hover:compressx-v2-underline" onclick="document.getElementById('plugin-details').style.display='block'"><?php esc_html_e('View full list', 'compressx'); ?></button>
                        </li>
                        <li><span class="compressx-v2-font-medium"><?php esc_html_e('Rewrite Rules:', 'compressx'); ?></span>
                            <?php if (($debug_info['Rewrite rules test'] ?? '') === 'success'): ?>
                                <?php esc_html_e('Success', 'compressx'); ?> <span class="compressx-v2-ml-1 compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('OK', 'compressx'); ?></span>
                            <?php else: ?>
                                <?php esc_html_e('Failed', 'compressx'); ?> <span class="compressx-v2-ml-1 compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-rounded compressx-v2-px-2"><?php esc_html_e('Error', 'compressx'); ?></span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Active Plugins Table -->
            <div id="plugin-details" class="compressx-v2-grid compressx-v2-grid-cols-1" style="display: none;">
                <div class="compressx-v2-flex compressx-v2-justify-between compressx-v2-items-center compressx-v2-mb-4">
                    <span class="compressx-v2-text compressx-v2-font-medium"><?php esc_html_e('Active Plugins', 'compressx'); ?></span>
                    <button onclick="document.getElementById('plugin-details').style.display='none'" class="compressx-v2-text-sm compressx-v2-text-blue-600 hover:compressx-v2-underline">
                        <?php esc_html_e('Close', 'compressx'); ?>
                    </button>
                </div>

                <div class="compressx-v2-overflow-x-auto">
                    <table class="compressx-v2-min-w-full compressx-v2-text-sm compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded">
                        <thead class="compressx-v2-bg-gray-50 compressx-v2-text-left">
                        <tr>
                            <th class="compressx-v2-px-4 compressx-v2-py-2 compressx-v2-font-medium compressx-v2-text-gray-700"><?php esc_html_e('Plugin', 'compressx'); ?></th>
                            <th class="compressx-v2-px-4 compressx-v2-py-2 compressx-v2-font-medium compressx-v2-text-gray-700"><?php esc_html_e('Path', 'compressx'); ?></th>
                            <th class="compressx-v2-px-4 compressx-v2-py-2 compressx-v2-font-medium compressx-v2-text-gray-700"><?php esc_html_e('Status', 'compressx'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="compressx-v2-divide-y compressx-v2-divide-gray-200">
                        <?php
                        $plugins = get_plugins();
                        foreach ( $plugins as $plugin_file => $plugin_data )
                        {
                        $is_active = is_plugin_active( $plugin_file ) ? 'Active' : 'Inactive';
                        $plugin_name = $plugin_data['Name'];
                        ?>
                        <tr>
                            <td class="compressx-v2-px-4 compressx-v2-py-2"><?php echo esc_html($plugin_name); ?></td>
                            <td class="compressx-v2-px-4 compressx-v2-py-2"><?php echo esc_html($plugin_file); ?></td>
                            <td class="compressx-v2-px-4 compressx-v2-py-2">
                                <?php
                                if($is_active=='Active')
                                {
                                    ?>
                                    <span class="compressx-v2-bg-green-100 compressx-v2-text-green-700 compressx-v2-text-xs compressx-v2-px-2 compressx-v2-rounded"><?php esc_html_e($is_active, 'compressx'); ?></span>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <span class="compressx-v2-bg-red-100 compressx-v2-text-red-700 compressx-v2-text-xs compressx-v2-px-2 compressx-v2-rounded"><?php esc_html_e($is_active, 'compressx'); ?></span>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="compressx_debug_form_section" style="display: none" class="compressx-v2-bg-white compressx-v2-p-6 compressx-v2-rounded compressx-v2-mb-6 compressx-v2-shadow-sm compressx-v2-border compressx-v2-relative">
            <div class="compressx-v2-grid compressx-v2-grid-cols-1 md:compressx-v2-grid-cols-2 compressx-v2-gap-6">
                <button class="compressx-v2-absolute compressx-v2-top-3 compressx-v2-right-3 compressx-v2-text-gray-400 hover:compressx-v2-text-gray-600" onclick="document.getElementById('compressx_debug_form_section').style.display='none'">
                    ✕
                </button>
                <div class="compressx-v2-space-y-4">
                    <h2 class="compressx-v2-text-lg compressx-v2-font-semibold compressx-v2-text-gray-800">
                        <?php esc_html_e('Method 1 (Recommended)', 'compressx'); ?>
                    </h2>
                    <p class="compressx-v2-text-sm compressx-v2-text-gray-600">
                        <?php esc_html_e('Send debug info directly via email if SMTP is configured. We\'ll notify you once it\'s resolved.', 'compressx'); ?>
                    </p>

                    <div class="compressx-v2-space-y-3">
                        <div>
                            <label class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('CompressX Support Email', 'compressx'); ?></label>
                            <input type="text" id="compressx_support_mail" value="support@compressx.io" disabled
                                   class="compressx-v2-w-full compressx-v2-bg-gray-100 compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-text-sm">
                        </div>
                        <div>
                            <label class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Your Email', 'compressx'); ?></label>
                            <input type="email" id="compressx_user_mail" placeholder="you@example.com"
                                   class="compressx-v2-w-full compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-text-sm focus:compressx-v2-border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="compressx-v2-text-xs compressx-v2-text-gray-500"><?php esc_html_e('Problem Description', 'compressx'); ?></label>
                        <textarea id="compressx_debug_comment" rows="4" placeholder="<?php esc_attr_e('Please describe your problem here...', 'compressx'); ?>"
                                  class="compressx-v2-w-full compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-text-sm focus:compressx-v2-border-blue-500"></textarea>
                    </div>

                    <button id="compressx_debug_submit" class="compressx-v2-bg-blue-600 compressx-v2-text-white compressx-v2-px-5 compressx-v2-py-2 compressx-v2-rounded hover:compressx-v2-bg-blue-700">
                        <?php esc_html_e('Send Debug Information', 'compressx'); ?>
                    </button>
                </div>

                <div class="compressx-v2-space-y-4">
                    <h2 class="compressx-v2-text-lg compressx-v2-font-semibold compressx-v2-text-gray-800">
                        <?php esc_html_e('Method 2 (Alternative)', 'compressx'); ?>
                    </h2>
                    <p class="compressx-v2-text-sm compressx-v2-text-gray-600">
                        <?php esc_html_e('If SMTP is not configured, download the debug information (website info & error logs) and send it to us manually.', 'compressx'); ?>
                    </p>
                    <button id="compressx_download_debug_info" class="compressx-v2-bg-gray-700 compressx-v2-text-white compressx-v2-px-5 compressx-v2-py-2 compressx-v2-rounded hover:compressx-v2-bg-gray-800">
                        <?php esc_html_e('Download Debug File', 'compressx'); ?>
                    </button>
                </div>

            </div>
        </section>
        <?php
    }

    private function output_logs()
    {
        ?>
        <!-- Logs Section -->
        <section class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-6 compressx-v2-mb-6">
            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-3">
                <h2 class="compressx-v2-text-lg compressx-v2-font-medium"><?php esc_html_e('Logs', 'compressx'); ?></h2>
                <button id="cx_empty_log" class="compressx-v2-text-xs compressx-v2-font-medium compressx-v2-px-3 compressx-v2-py-1 compressx-v2-rounded compressx-v2-text-red-600 hover:compressx-v2-bg-red-50">
                    🗑 <?php esc_html_e('Empty Logs', 'compressx'); ?>
                </button>
            </div>
            <div id="cx_log_list" class="cx-table-overflow">
                <?php
                $list=$this->get_log_list();
                $log_list=new CompressX_Log_List_V2();
                $log_list->set_log_list($list);
                $log_list->prepare_items();
                $log_list->display();
                ?>
            </div>
        </section>

        <!-- Log Detail Section (Hidden by default) -->
        <section id="cx_log_detail_section" style="display: none" class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-6 compressx-v2-mb-6 compressx-v2-shadow-sm">
            <!-- Header -->
            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-4">
                <h2 class="compressx-v2-text-gray-900 compressx-v2-font-semibold compressx-v2-text-xl">
                    <?php esc_html_e('Activity Log', 'compressx'); ?>
                </h2>
                <div class="compressx-v2-flex compressx-v2-gap-2">
                    <button id="cx_download_current_log" class="compressx-v2-bg-blue-100 hover:compressx-v2-bg-blue-200 compressx-v2-text-blue-700 compressx-v2-font-medium compressx-v2-px-3 compressx-v2-py-1.5 compressx-v2-rounded compressx-v2-transition">
                        <?php esc_html_e('Download Log', 'compressx'); ?>
                    </button>
                    <button id="cx_close_log" class="compressx-v2-text-gray-400 hover:compressx-v2-text-gray-600 compressx-v2-text-lg compressx-v2-ml-2" title="<?php esc_attr_e('Close', 'compressx'); ?>">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Info -->
            <div class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mb-3">
                <?php esc_html_e('Open log file created:', 'compressx'); ?> <span id="cx_log_created_date" class="compressx-v2-font-semibold">--</span>
            </div>

            <!-- Log Container (Y Overflow) -->
            <div class="compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-border compressx-v2-p-4 compressx-v2-h-[400px]">
                <textarea id="cx_read_optimize_log_content" style="border: none;background-color: transparent !important;" class="compressx-v2-w-full compressx-v2-h-full compressx-v2-bg-transparent compressx-v2-border-0 compressx-v2-p-0 compressx-v2-text-sm compressx-v2-font-mono compressx-v2-text-gray-700 compressx-v2-resize-none focus:compressx-v2-outline-none" readonly></textarea>
            </div>
        </section>
        <?php
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    /**
     * Get list of log files
     *
     * @since 2.0.0
     * @return array
     */
    public function get_log_list()
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
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Simple read operation for log file metadata
            $handle = @fopen($file, "r");
            if ($handle) {
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

                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing file opened above for log reading
                fclose($handle);
                $log_list[basename($file)] = $log_file;
            }
        }

        $log_list = $this->sort_list($log_list);
        return $log_list;
    }

    public function sort_list($list)
    {
        uasort($list, function ($a, $b) {
            if ($a['time'] > $b['time']) {
                return -1;
            } else if ($a['time'] === $b['time']) {
                return 0;
            } else {
                return 1;
            }
        });

        return $list;
    }

    /**
     * Get filtered log list by date/time range
     *
     * @since 2.0.0
     * @param string $start_date Start date
     * @param string $start_time Start time
     * @param string $end_date End date
     * @param string $end_time End time
     * @return array
     */
    public function get_log_list_ex($start_date, $start_time, $end_date, $end_time)
    {
        if (empty($start_date)) {
            $start = 0;
        } else if (empty($start_time)) {
            $start = strtotime($start_date);
        } else {
            $start = strtotime($start_date . ' ' . $start_time);
        }

        if ($start > 0) {
            $offset = get_option('gmt_offset');
            $start = $start - $offset * 60 * 60;
        }

        if (empty($end_date)) {
            $end = 0;
        } else if (empty($end_time)) {
            $end = strtotime($end_date);
        } else {
            $end = strtotime($end_date . ' ' . $end_time);
        }

        if ($end > 0) {
            $offset = get_option('gmt_offset');
            $end = $end - $offset * 60 * 60;
        }

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
            if ($handler)
                @closedir($handler);
        }

        foreach ($files as $file) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Simple read operation for log file metadata
            $handle = @fopen($file, "r");
            if ($handle) {
                $log_file['file_name'] = basename($file);
                if (preg_match('/compressx-(.*?)_/', basename($file), $matches)) {
                    $id = $matches[0];
                    $id = substr($id, 0, strlen($id) - 1);
                    $log_file['id'] = $id;
                }
                $log_file['path'] = $file;
                $log_file['size'] = filesize($file);
                $log_file['name'] = basename($file);
                /*
                $log_file['time'] = preg_replace('/[^0-9]/', '', basename($file));

                $offset = get_option('gmt_offset');
                $localtime = strtotime($log_file['time']) + $offset * 60 * 60;
                $log_file['date'] = gmdate('M-d-y H:i', $localtime);*/

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

                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing file opened above for log reading
                fclose($handle);

                if ($start > 0 && $end > 0) {
                    if ($localtime >= $start && $localtime <= $end) {
                        $log_list[basename($file)] = $log_file;
                    }
                } else if ($start > 0) {
                    if ($localtime >= $start) {
                        $log_list[basename($file)] = $log_file;
                    }
                } else if ($end > 0) {
                    if ($localtime <= $end) {
                        $log_list[basename($file)] = $log_file;
                    }
                } else {
                    $log_list[basename($file)] = $log_file;
                }
            }
        }

        $log_list = $this->sort_list($log_list);

        return $log_list;
    }

    private function is_log_file($file_name)
    {
        if (preg_match('/compressx_.*_log\.txt$/', $file_name)) {
            return true;
        }
        return false;
    }

    public function open_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in ajax_check_security above
            if (!isset($_POST['filename'])) {
                die();
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce is verified in ajax_check_security above
            $file_name = sanitize_text_field(wp_unslash($_POST['filename']));
            $loglist = $this->get_log_list();
            if (!empty($loglist)) {
                if (isset($loglist[$file_name])) {
                    $log = $loglist[$file_name];
                } else {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('The log not found ', 'compressx') . $file_name;
                    echo wp_json_encode($ret);
                    die();
                }

                $path = $log['path'];
                if (!file_exists($path)) {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('The log not found ', 'compressx') . $file_name;
                    echo wp_json_encode($ret);
                    die();
                }

                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Simple read operation for log display
                $file = fopen($path, 'r');
                if (!$file) {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('Unable to open the log file.', 'compressx');
                    echo wp_json_encode($ret);
                    die();
                }

                $offset = get_option('gmt_offset');
                $buffer = '';
                $create =  gmdate('Y-m-d', $log['time']) ;
                while (!feof($file)) {
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread -- Reading log file for display
                    $buffer .= fread($file, 1024);
                }
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing file opened above
                fclose($file);
                $ret['create'] = $create;
                $ret['result'] = 'success';
                $ret['data'] = $buffer;
                echo wp_json_encode($ret);
                die();
            } else {
                $ret['result'] = 'failed';
                $ret['error'] = 'The log not found.';
                echo wp_json_encode($ret);
                die();
            }
        } catch (Exception $error) {
            $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
            error_log($message);
            echo wp_json_encode(array('result' => 'failed', 'error' => $message));
        }
        die();
    }

    public function download_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified in ajax_check_security above
            $file_name = sanitize_file_name($_GET['log']);
            $log = new CompressX_Log();
            $path = $log->GetSaveLogFolder() . $file_name;

            if (!file_exists($path)) {
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
            error_log($message);
        }
        exit;
    }

    public function delete_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in ajax_check_security above
            if (!isset($_POST['filename'])) {
                die();
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Nonce is verified in ajax_check_security above
            $file_name = sanitize_text_field(wp_unslash($_POST['filename']));
            $loglist = $this->get_log_list();
            if (!empty($loglist)) {
                if (isset($loglist[$file_name])) {
                    $log = $loglist[$file_name];
                } else {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('The log not found ', 'compressx') . $file_name;
                    echo wp_json_encode($ret);
                    die();
                }

                $path = $log['path'];
                @wp_delete_file($path);

                $ret['result'] = 'success';
                echo wp_json_encode($ret);
                die();
            } else {
                $ret['result'] = 'failed';
                $ret['error'] = __('The log not found ', 'compressx');
                echo wp_json_encode($ret);
                die();
            }
        } catch (Exception $error) {
            $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
            error_log($message);
            echo wp_json_encode(array('result' => 'failed', 'error' => $message));
        }
        die();
    }

    public function delete_all_log()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-logs');

        try {
            $log = new CompressX_Log();
            $dir = $log->GetSaveLogFolder();
            $files = array();
            $handler = opendir($dir);
            if ($handler !== false) {
                while (($filename = readdir($handler)) !== false) {
                    if ($filename != "." && $filename != "..") {
                        if (is_dir($dir . $filename)) {
                            continue;
                        } else {
                            if ($this->is_log_file($filename)) {
                                @wp_delete_file($dir . $filename);
                            }
                        }
                    }
                }
                if ($handler) {
                    closedir($handler);
                }
            }
            $ret['result'] = 'success';
        } catch (Exception $error) {
            $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
            error_log($message);
            $ret['result'] = 'failed';
            $ret['error'] = $message;
        }
        echo wp_json_encode($ret);
        die();
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
        $log_list=new CompressX_Log_List_V2();
        $log_list->set_log_list($list,$page);
        $log_list->prepare_items();
        ob_start();
        $log_list->display();
        $json['result'] = 'success';
        $json['html'] =  ob_get_clean();

        echo wp_json_encode($json);
        die();
    }

    public function get_debug_info()
    {
        global $wp_version;
        $debug_info = array();

        $debug_info['home'] = $this->get_domain();
        $debug_info['Server name'] = $_SERVER['SERVER_NAME'];
        $debug_info['Web Server'] = sanitize_text_field($_SERVER["SERVER_SOFTWARE"]);
        $debug_info['CompressX version'] = COMPRESSX_VERSION;
        $debug_info['Wordpress version'] = $wp_version;
        $debug_info['PHP version'] = phpversion();

        if (!function_exists('gd_info')) {
            $debug_info['GD extension'] = __("GD extension is not installed or enabled.", 'compressx');
        } else {
            $debug_info['GD extension'] = __("The GD extension has been properly installed.", 'compressx');
            $info = gd_info();
            $debug_info['GD Version'] = isset($info['GD Version']) ? $info['GD Version'] : '';
            $debug_info['GD WebP Support'] = isset($info['WebP Support']) ? $info['WebP Support'] : false;
            $debug_info['GD AVIF Support'] = isset($info['AVIF Support']) ? $info['AVIF Support'] : false;
        }

        if (extension_loaded('imagick') && class_exists('\Imagick')) {
            $debug_info['Imagick extension'] = __("The imagick extension has been properly installed.", 'compressx');
            $debug_info['Imagick Version'] = \Imagick::getVersion();
            $debug_info['Imagick WebP Support'] = \Imagick::queryformats('WEBP') ? true : false;
            $debug_info['Imagick AVIF Support'] = \Imagick::queryformats('AVIF') ? true : false;
        } else {
            $debug_info['Imagick extension'] = __("Imagick extension is not installed or enabled.", 'compressx');
        }

        $debug_info['active_plugins'] = get_option('active_plugins');
        $debug_info['active_theme'] = wp_get_theme()->get_template();
        $debug_info['memory_current'] = size_format(memory_get_usage(), 2);
        $debug_info['memory_peak'] = size_format(memory_get_peak_usage(), 2);
        $debug_info['memory_limit'] = ini_get('memory_limit');

        include_once COMPRESSX_DIR . '/includes/class-compressx-rewrite-checker.php';
        $test = new CompressX_Rewrite_Checker();
        $result = $test->test_ex();
        if ($result['result'] == 'success') {
            $debug_info['Rewrite rules test'] = 'success';
        } else {
            $debug_info['Rewrite rules test'] = $result['error'];
        }

        $options = get_option('compressx_general_settings', array());
        $debug_info['setting'] = $options;
        $quality_options = get_option('compressx_quality', array());
        $converter_method = get_option('compressx_converter_method', false);
        $output_format_webp = get_option('compressx_output_format_webp', 1);
        $output_format_avif = get_option('compressx_output_format_avif', 1);
        $debug_info['setting']['quality'] = $quality_options;
        $debug_info['setting']['output_format_webp'] = $output_format_webp;
        $debug_info['setting']['output_format_avif'] = $output_format_avif;
        $debug_info['setting']['converter_method'] = $converter_method;
        $debug_info['setting'] = json_encode($debug_info['setting']);

        return $debug_info;
    }

    public function get_domain()
    {
        global $wpdb;
        $home_url = home_url();
        $db_home_url = home_url();
        $home_url_sql = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name = %s", 'home'));
        foreach ($home_url_sql as $home) {
            $db_home_url = untrailingslashit($home->option_value);
        }

        if ($home_url === $db_home_url) {
            $domain = $home_url;
        } else {
            $domain = $db_home_url;
        }

        return strtolower($domain);
    }

    public function create_debug_package()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-system-info');

        try {
            if (!class_exists('PclZip'))
                include_once(ABSPATH . '/wp-admin/includes/class-pclzip.php');

            $backup_path = "compressx";
            $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_debug.zip';
            if (!is_dir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path)) {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Creating protected directory for debug package
                @mkdir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path, 0777, true);
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Creating protection files
                @fopen(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'index.html', 'x');
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Creating htaccess protection file
                $tempfile = @fopen(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . '.htaccess', 'x');
                if ($tempfile) {
                    $text = "deny from all";
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Writing htaccess protection
                    fwrite($tempfile, $text);
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing htaccess file
                    fclose($tempfile);
                }
            }

            if (file_exists($path)) {
                @wp_delete_file($path);
            }

            $archive = new PclZip($path);
            $server_info = wp_json_encode($this->get_debug_info());
            $server_file_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_server_info.json';
            if (file_exists($server_file_path)) {
                @wp_delete_file($server_file_path);
            }
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Creating temporary debug info file
            $server_file = fopen($server_file_path, 'x');
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing temporary file
            fclose($server_file);
            file_put_contents($server_file_path, $server_info);
            if (!$archive->add($server_file_path, PCLZIP_OPT_REMOVE_ALL_PATH)) {
                exit;
            }
            @wp_delete_file($server_file_path);

            $log = new CompressX_Log();
            $files = $log->get_logs();
            if (!empty($files)) {
                if (!$archive->add($files, PCLZIP_OPT_REMOVE_ALL_PATH)) {
                    exit;
                }
            }

            if (session_id())
                session_write_close();

            $size = filesize($path);
            if (!headers_sent()) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                header('Cache-Control: must-revalidate');
                header('Content-Length: ' . $size);
                header('Content-Transfer-Encoding: binary');
            }

            ob_end_clean();
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Sending debug package to browser for download
            readfile($path);
            @wp_delete_file($path);
        } catch (Exception $error) {
            $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
            error_log($message);
            echo wp_json_encode(array('result' => 'failed', 'error' => $message));
            die();
        }
        exit;
    }

    public function send_debug_info()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-system-info');

        try {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in ajax_check_security above
            if (!isset($_POST['user_mail']) || empty($_POST['user_mail'])) {
                $ret['result'] = 'failed';
                $ret['error'] = __('User\'s email address is required.', 'compressx');
            } else {
                $pattern = '/^[a-z0-9]+([._-][a-z0-9]+)*@([0-9a-z]+\.[a-z]{2,14}(\.[a-z]{2})?)$/i';
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in ajax_check_security above
                if (!preg_match($pattern, $_POST['user_mail'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = __('Please enter a valid email address.', 'compressx');
                } else {
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in ajax_check_security above
                    $user_mail = sanitize_email($_POST['user_mail']);
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in ajax_check_security above
                    $comment = sanitize_text_field($_POST['comment']);
                    $ret = $this->_send_debug_info($user_mail, $comment);
                }
            }
            echo wp_json_encode($ret);
            die();
        } catch (Exception $error) {
            $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
            error_log($message);
            echo wp_json_encode(array('result' => 'failed', 'error' => $message));
            die();
        }
    }

    public function _send_debug_info($user_email, $comment)
    {
        $send_to = 'support@compressx.io';
        $subject = 'Debug Information';
        $body = '<div>User\'s email: ' . $user_email . '.</div>';
        $body .= '<div>Comment: ' . $comment . '.</div>';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (!class_exists('PclZip'))
            include_once(ABSPATH . '/wp-admin/includes/class-pclzip.php');

        $backup_path = "compressx";
        $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_debug.zip';
        if (!is_dir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path)) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Creating protected directory for debug package
            @mkdir(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path, 0777, true);
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Creating protection files
            @fopen(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'index.html', 'x');
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Creating htaccess protection file
            $tempfile = @fopen(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . '.htaccess', 'x');
            if ($tempfile) {
                $text = "deny from all";
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Writing htaccess protection
                fwrite($tempfile, $text);
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing htaccess file
                fclose($tempfile);
            }
        }

        if (file_exists($path)) {
            @wp_delete_file($path);
        }

        $archive = new PclZip($path);
        $server_info = wp_json_encode($this->get_debug_info());
        $server_file_path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $backup_path . DIRECTORY_SEPARATOR . 'compressx_server_info.json';
        if (file_exists($server_file_path)) {
            @wp_delete_file($server_file_path);
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Creating temporary debug info file
        $server_file = fopen($server_file_path, 'x');
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing temporary file
        fclose($server_file);
        file_put_contents($server_file_path, $server_info);
        $archive->add($server_file_path, PCLZIP_OPT_REMOVE_ALL_PATH);
        @wp_delete_file($server_file_path);

        $log = new CompressX_Log();
        $files = $log->get_logs();
        if (!empty($files)) {
            $archive->add($files, PCLZIP_OPT_REMOVE_ALL_PATH);
        }

        $attachments[] = $path;

        if (wp_mail($send_to, $subject, $body, $headers, $attachments) === false) {
            $ret['result'] = 'failed';
            $ret['error'] = __('Unable to send email. Please check the configuration of email server.', 'compressx');
        } else {
            $ret['result'] = 'success';
        }

        @wp_delete_file($path);
        return $ret;
    }
}