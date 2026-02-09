<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Settings_Display
{
    public function __construct()
    {
        add_action('wp_ajax_compressx_get_custom_tree_dir', array($this, 'get_custom_tree_dir'));
        add_action('wp_ajax_compressx_add_exclude_folders', array($this, 'add_exclude_folders'));
        add_action('wp_ajax_compressx_add_exclude_folder', array($this, 'add_exclude_folder'));
        //
        add_action('wp_ajax_compressx_remove_exclude_folders', array($this, 'remove_exclude_folders'));

        add_action('wp_ajax_compressx_v2_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_compressx_delete_files', array($this, 'delete_files'));

    }

    public function display()
    {
        ?>
        <div class="compressx-root">
            <div class="compressx-v2-py-6 compressx-v2-w-full compressx-v2-max-w-[1200px] compressx-v2-mx-auto">
                <?php
                $this->output_header();
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
        ?>
        <div class=" compressx-v2-pr-4 compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-4">
            <div>
                <h1 class="compressx-v2-text-2xl compressx-v2-font-semibold compressx-v2-text-gray-900">
                    <?php echo esc_html(__('Global Settings', 'compressx')); ?>
                </h1>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-2">
                    <?php echo esc_html(__('Manage all core settings in one place.', 'compressx')); ?>
                </p>
            </div>
        </div>
        <?php
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    private function output_settings()
    {
        ?>
        <!-- Global Settings Section -->
        <section class="compressx-v2-bg-white compressx-v2-border compressx-v2-border-gray-200 compressx-v2-rounded compressx-v2-p-5 compressx-v2-mb-6">

            <!-- Nav tabs for settings -->
            <div class="compressx-v2-border-b compressx-v2-mb-4">
                <nav class="compressx-v2-flex compressx-v2-gap-4">
                    <button id="cx-v2-tab-image-optimization" class="compressx-v2-px-4 compressx-v2-py-2 compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-blue-600 compressx-v2-border-b-2 compressx-v2-border-blue-600">
                        <?php esc_html_e('Image Optimization', 'compressx'); ?>
                    </button>
                </nav>
            </div>

            <!-- Image Optimization Tab Content -->
            <div id="cx-v2-content-image-optimization">
                <?php $this->output_thumbnail_sizes(); ?>
                <?php $this->output_exclude_folders(); ?>
                <?php $this->output_custom_folders(); ?>
                <?php $this->output_cache_control_settings(); ?>
                <?php $this->output_delete_images(); ?>

            </div>

        </section>
        <?php
    }

    private function output_thumbnail_sizes()
    {
        $options = CompressX_Options::get_option('compressx_general_settings', array());

        global $_wp_additional_image_sizes;
        $intermediate_image_sizes = get_intermediate_image_sizes();
        $image_sizes = array();
        $image_sizes['og']['skip'] = isset($options['skip_size']['og']) ? $options['skip_size']['og'] : false;

        foreach ($intermediate_image_sizes as $size_key) {
            if (in_array($size_key, array('thumbnail', 'medium', 'large'), true)) {
                $image_sizes[$size_key]['width']  = get_option($size_key . '_size_w');
                $image_sizes[$size_key]['height'] = get_option($size_key . '_size_h');
                $image_sizes[$size_key]['crop']   = (bool) get_option($size_key . '_crop');
                if (isset($options['skip_size'][$size_key]) && $options['skip_size'][$size_key]) {
                    $image_sizes[$size_key]['skip'] = true;
                } else {
                    $image_sizes[$size_key]['skip'] = false;
                }
            } else if (isset($_wp_additional_image_sizes[$size_key])) {
                $image_sizes[$size_key] = array(
                    'width'  => $_wp_additional_image_sizes[$size_key]['width'],
                    'height' => $_wp_additional_image_sizes[$size_key]['height'],
                    'crop'   => $_wp_additional_image_sizes[$size_key]['crop'],
                );
                if (isset($options['skip_size'][$size_key]) && $options['skip_size'][$size_key]) {
                    $image_sizes[$size_key]['skip'] = true;
                } else {
                    $image_sizes[$size_key]['skip'] = false;
                }
            }
        }

        if (! isset($image_sizes['medium_large']) || empty($image_sizes['medium_large'])) {
            $width  = intval(get_option('medium_large_size_w'));
            $height = intval(get_option('medium_large_size_h'));

            $image_sizes['medium_large'] = array(
                'width'  => $width,
                'height' => $height,
            );

            if (isset($options['skip_size']['medium_large']) && $options['skip_size']['medium_large']) {
                $image_sizes['medium_large']['skip'] = true;
            } else {
                $image_sizes['medium_large']['skip'] = false;
            }
        }
        ?>
        <div class="compressx-v2-border compressx-v2-rounded compressx-v2-bg-white compressx-v2-mb-4 compressx-v2-p-6 compressx-v2-space-y-4">

            <!-- Title & Description -->
            <div>
                <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-800">
                    <?php esc_html_e('Thumbnail Sizes to Process', 'compressx'); ?>
                    <span>
                        <?php $this->output_tooltip('', esc_html__('Choose thumbnail sizes you want to process. Some themes may generate new thumbnail sizes sometimes, then you\'ll need to reprocess the thumbnails sizes using the Bulk Processing function.', 'compressx'), 'large'); ?>
                    </span>
                </h3>
                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                    <?php esc_html_e('Select which image sizes CompressX should optimize. These apply to both new uploads and bulk processing.', 'compressx'); ?>
                </p>
            </div>

            <!-- Checkboxes in 2 columns -->
            <div class="compressx-v2-grid compressx-v2-grid-cols-1 md:compressx-v2-grid-cols-2 compressx-v2-gap-2">
                <?php
                $max_show = 3;
                $showed = 0;
                $show_more = false;
                foreach ($image_sizes as $size_key => $size) {
                    if ($showed > $max_show) {
                        $style = "display:none";
                        if (!$show_more) {
                            $show_more = true;
                            ?>
                            <span id="cx-v2-show-more-size" class="compressx-v2-col-span-2"><a style="cursor: pointer" class="compressx-v2-text-xs compressx-v2-text-blue-600 hover:underline"><?php esc_html_e('Show more sizes…', 'compressx'); ?></a></span>
                            <?php
                        }
                    } else {
                        $style = "";
                    }

                    $checked = $size['skip'] ? '' : 'checked';

                    if ($size_key == 'og') {
                        $text = __('Original image', 'compressx');
                    } else {
                        $text = $size_key . ' (' . $size['width'] . '×' . $size['height'] . ')';
                    }
                    ?>
                    <label class="cx-v2-thumbnail-size compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2" style="<?php echo esc_attr($style); ?>">
                        <input type="checkbox" option="size_setting" name="<?php echo esc_attr($size_key); ?>" <?php echo esc_attr($checked); ?>>
                        <span class="compressx-v2-text-sm"><?php echo esc_html($text); ?></span>
                    </label>
                    <?php
                    $showed++;
                }
                ?>
            </div>

            <!-- Helper note -->
            <div class="compressx-v2-bg-blue-50 compressx-v2-border compressx-v2-border-blue-200 compressx-v2-rounded compressx-v2-p-3">
                <p class="compressx-v2-text-xs compressx-v2-text-blue-700">
                    <?php esc_html_e('Some themes or plugins may generate additional image sizes. If new sizes appear, you may need to reprocess them using the Bulk Processing tool.', 'compressx'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    private function output_exclude_folders()
    {
        $excludes = CompressX_Options::get_option('compressx_media_excludes', array());
        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));
        ?>
        <div class="compressx-v2-border compressx-v2-rounded compressx-v2-bg-white compressx-v2-p-6 compressx-v2-mb-4 compressx-v2-space-y-4">

            <div>
                <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-800">
                    <?php esc_html_e('Exclude Folders', 'compressx'); ?>
                    <span>
                        <?php $this->output_tooltip('', esc_html__('Select folders in the Uploads folder (media library) and exclude them from the processing.', 'compressx'), 'large'); ?>
                    </span>
                </h3>
                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                    <?php esc_html_e('Select folders in the Uploads folder (media library) and exclude them from the processing.', 'compressx'); ?>
                </p>
            </div>

            <div class="compressx-general-settings-body">
                <div class="cx-mediafolder-rules">
                    <div class="cx-mediafolders">
                    <span>
                        <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span><span><?php esc_html_e('Media library(Uploads):', 'compressx') ?></span>
                    </span>
                        <div class="cx-upload-treeviewer" id="compressx_exclude_js_tree">
                        </div>
                    </div>
                    <div class="cx-mediafolder-included">
                    <span>
                        <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                        <span><strong><?php esc_html_e('Excluded Folders:', 'compressx') ?> </strong><?php esc_html_e('Media files inside these folders will', 'compressx') ?> <strong><?php esc_html_e('NOT', 'compressx') ?></strong> <?php esc_html_e('be processed', 'compressx') ?></span>
                    </span>
                        <div class="cx-mediafolder-list" id="compressx_exclude_dir_node">
                            <ul>
                                <?php
                                foreach ($excludes as $exclude) {
                                    $path = str_replace($abs_path, '', $exclude);
                                    ?>
                                    <li>
                                        <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                                        <span><?php echo esc_html($path . ',(' . $this->get_children_count($exclude) . ') images') ?></span>
                                        <span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-exclude-tree" data-id="<?php echo esc_attr($exclude) ?>"></span>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function output_custom_folders()
    {
        $custom_folders = CompressX_Options::get_option('compressx_custom_includes', array());
        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));

        $stats = $this->get_custom_stats($custom_folders);
        $found = $stats['files'];
        $saved = $stats['saved'];
        $processed = $stats['processed_files'];
        ?>
        <div class="compressx-v2-border compressx-v2-rounded compressx-v2-bg-white compressx-v2-p-6 compressx-v2-mb-4 compressx-v2-space-y-4">

            <div>
                <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-800">
                    <?php esc_html_e('Custom Folders', 'compressx'); ?>
                    <span>
                        <?php $this->output_tooltip('', esc_html__('Select custom folders in the /wp-content folder and process images inside them.', 'compressx'), 'large'); ?>
                    </span>
                </h3>
                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                    <?php esc_html_e('Select custom folders in the /wp-content folder and process images inside them.', 'compressx'); ?>
                </p>
            </div>

            <div class="compressx-general-settings-body">
                <div class="cx-mediafolder-rules">
                    <div class="cx-mediafolders">
                    <span>
                        <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span><span><?php esc_html_e('wp-content:', 'compressx') ?></span>
                    </span>
                        <div class="cx-upload-treeviewer" id="compressx_custom_include_js_tree">
                        </div>
                    </div>
                    <div class="cx-mediafolder-included">
                    <span>
                        <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                        <span><strong><?php esc_html_e('Included Folders:', 'compressx') ?> </strong><?php esc_html_e('Media files inside these folders will be processed', 'compressx') ?></span>
                    </span>
                        <div class="cx-mediafolder-list" id="compressx_include_dir_node">
                            <ul>
                                <?php
                                foreach ($custom_folders as $folder) {
                                    $path = str_replace($abs_path, '', $folder);
                                    ?>
                                    <li>
                                        <span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>
                                        <span><?php echo esc_html($path . '(' . $this->get_custom_children_count($folder) . ')') ?></span>
                                        <span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-include-tree" data-id="<?php echo esc_attr($folder) ?>"></span>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="cx-custom-overview">
                    <div>
                    <span id="cx_custom_overview">
                        <span><?php esc_html_e('Media Files Found:', 'compressx') ?> </span><span id="cx_custom_founds"><?php echo esc_html($found); ?></span><span> | </span>
                        <span><?php esc_html_e('Total Savings:', 'compressx') ?> </span><span id="cx_custom_total_saving"><?php echo esc_html($saved); ?></span><span>%</span><span> | </span>
                        <span><?php esc_html_e('Processed Media Files:', 'compressx') ?> </span><span id="cx_custom_processed"><?php echo esc_html($processed); ?></span>
                    </span>
                    </div>
                    <div style="padding-top:1rem;">
                        <input type="submit" id="compressx_start_custom_bulk_optimization" class="button-primary cx-button" value="<?php esc_attr_e('Bulk Process Now', 'compressx') ?>">
                        <input class="button-primary cx-button" style="display: none" id="compressx_cancel_custom_bulk_optimization" type="submit" value="<?php esc_attr_e('Cancel Processing', 'compressx') ?>">
                        <span id="cx_custom_bulk_progress_text" style="display: none"></span>
                    </div>
                    <div style="padding-top:1rem;">
                        <span><input id="cx_custom_force_optimization" type="checkbox"><?php esc_html_e('Force all images to be re-processed', 'compressx') ?></span>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }

    private function output_delete_images()
    {
        ?>
        <!-- WebP AVIF deletion -->
        <div class="compressx-v2-border compressx-v2-rounded compressx-v2-bg-white compressx-v2-p-6 compressx-v2-space-y-4">

            <!-- Title -->
            <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-800">
                <?php esc_html_e('Delete Images Generated by CompressX', 'compressx'); ?>
            </h3>

            <!-- Warning -->
            <div class="compressx-v2-bg-red-50 compressx-v2-border compressx-v2-border-red-300 compressx-v2-text-red-700 compressx-v2-rounded compressx-v2-p-3 compressx-v2-text-sm compressx-v2-space-y-1">
                <p>⚠️ <?php esc_html_e('This will permanently delete all', 'compressx'); ?> <span class="compressx-v2-font-medium">WebP</span> <?php esc_html_e('and', 'compressx'); ?> <span class="compressx-v2-font-medium">AVIF</span> <?php esc_html_e('images and database records generated by CompressX.', 'compressx'); ?></p>
                <p class="compressx-v2-font-medium"><?php esc_html_e('Your original images will not be affected, and you can always generate new WebP/AVIF versions again by re-optimizing.', 'compressx'); ?></p>
            </div>

            <div class="compressx-v2-space-y-2">
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                    <input type="text" id="cx-v2-confirm-delete-file" placeholder="<?php esc_attr_e('Type DELETE to confirm', 'compressx'); ?>"
                           class="compressx-v2-flex-1 compressx-v2-border compressx-v2-rounded compressx-v2-px-3 compressx-v2-py-2 compressx-v2-text-sm">
                    <button id="cx-v2-delete-file" class="compressx-v2-px-4 compressx-v2-py-2 compressx-v2-bg-red-600 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-rounded hover:compressx-v2-bg-red-700">
                        <?php esc_html_e('Delete', 'compressx'); ?>
                    </button>

                    <span id="cx-v2-delete-file-progress" style="display: none;" class="compressx-v2-flex compressx-v2-items-center">
                        <img src="<?php echo esc_url(is_network_admin() ? network_admin_url('images/loading.gif') : admin_url('images/loading.gif')); ?>" alt="Loading..." style="width: 16px; height: 16px;">
                    </span>

                    <span id="cx-v2-delete-file-success" class="success hidden compressx-v2-text-sm compressx-v2-font-medium" aria-hidden="true" style="color:#007017"><?php esc_html_e('Deleted!', 'compressx') ?></span>
                </div>
                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                    <?php esc_html_e('Enter', 'compressx'); ?> <span class="compressx-v2-font-medium">DELETE</span> <?php esc_html_e('and click Delete to confirm this action.', 'compressx'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    private function output_cache_control_settings()
    {
        $options = CompressX_Options::get_option('compressx_general_settings', array());
        $disable_cache_control = isset($options['disable_cache_control']) ? $options['disable_cache_control'] : false;
        ?>
        <div class="compressx-v2-border compressx-v2-rounded compressx-v2-bg-white compressx-v2-p-6 compressx-v2-mb-4 compressx-v2-space-y-4">

            <div>
                <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-800">
                    <?php esc_html_e('Remove \'Header always set Cache-Control "private"\' from .htaccess file', 'compressx'); ?>
                    <span>
                        <?php $this->output_tooltip('', esc_html__('You can try to check this option if the site cannot be cached by Cloudflare.', 'compressx'), 'large'); ?>
                    </span>
                </h3>
                <p class="compressx-v2-text-xs compressx-v2-text-gray-500">
                    <?php esc_html_e('The \'.htaccess\' refers to \'/wp-content/.htaccess\'', 'compressx'); ?>
                </p>
            </div>

            <div>
                <label class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                    <input type="checkbox" option="cache_control_setting" name="disable_cache_control" <?php echo $disable_cache_control ? 'checked' : ''; ?>>
                    <span class="compressx-v2-text-sm"><?php esc_html_e('Remove \'Header always set Cache-Control "private"\' from .htaccess file', 'compressx'); ?></span>
                </label>
            </div>
        </div>
        <?php
    }

    private function output_save_section()
    {
        ?>
        <section class="compressx-v2-sticky compressx-v2-bottom-0 compressx-v2-bg-white compressx-v2-border-t compressx-v2-border-gray-200 compressx-v2-p-4">
            <div class="compressx-v2-max-w-[1200px] compressx-v2-mx-auto compressx-v2-flex compressx-v2-justify-end compressx-v2-gap-3">
                <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                    <button id="cx-v2-save-settings" class="compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded">
                        <?php esc_html_e('Save Changes', 'compressx'); ?>
                    </button>

                    <span id="cx-v2-save-settings-progress" style="display: none;" class="compressx-v2-flex compressx-v2-items-center">
                        <img src="<?php echo esc_url(is_network_admin() ? network_admin_url('images/loading.gif') : admin_url('images/loading.gif')); ?>" alt="Loading..." style="width: 16px; height: 16px;">
                    </span>

                    <span id="cx-v2-save-settings-text" class="success hidden compressx-v2-text-sm compressx-v2-font-medium" aria-hidden="true" style="color:#007017"><?php esc_html_e('Saved!', 'compressx') ?></span>
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

    private function get_custom_stats($includes)
    {
        $stats = array();
        $stats['files'] = 0;
        $stats['saved'] = 0;
        $stats['processed_files'] = 0;
        $stats['total'] = 0;
        $stats['processed'] = 0;

        if (!class_exists('CompressX_Image_Opt_Method')) {
            return $stats;
        }

        $convert_to_webp = false;
        $convert_to_avif = false;
        if (method_exists('CompressX_Image_Opt_Method', 'get_convert_to_webp')) {
            $convert_to_webp = CompressX_Image_Opt_Method::get_convert_to_webp();
        }
        if (method_exists('CompressX_Image_Opt_Method', 'get_convert_to_avif')) {
            $convert_to_avif = CompressX_Image_Opt_Method::get_convert_to_avif();
        }

        foreach ($includes as $include) {
            $this->get_folder_stats($include, $stats, $convert_to_webp, $convert_to_avif);
        }

        if ($stats['total'] > 0) {
            $stats['saved'] = ($stats['processed'] / $stats['total']) * 100;
            $stats['saved'] = round($stats['saved'], 1);
        }

        return $stats;
    }

    private function get_folder_stats($path, &$stats, $convert_to_webp, $convert_to_avif)
    {
        try {
            if (!class_exists('CompressX_Custom_Image_Meta')) {
                return 0;
            }

            $support_extension = array('jpg', 'jpeg', 'png', 'webp');
            $count = 0;
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($rii as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $extension = strtolower($file->getExtension());
                if (in_array($extension, $support_extension)) {
                    $filename = $path . '/' . $file->getFilename();
                    $filename = $this->transfer_path($filename);
                    $type = pathinfo($filename, PATHINFO_EXTENSION);

                    if ($convert_to_webp && $type != "webp") {
                        if (CompressX_Custom_Image_Meta::is_convert_webp($filename)) {
                            $stats['processed_files']++;
                            $stats['processed'] += CompressX_Custom_Image_Meta::get_convert_webp_size($filename);
                        }

                        $count++;
                        $stats['files']++;
                        $stats['total'] += $file->getSize();
                    } else if ($convert_to_avif && $type != "avif") {
                        if (CompressX_Custom_Image_Meta::is_convert_avif($filename)) {
                            $stats['processed_files']++;
                            $stats['processed'] += CompressX_Custom_Image_Meta::get_convert_avif_size($filename);
                        }

                        $count++;
                        $stats['files']++;
                        $stats['total'] += $file->getSize();
                    }
                }
            }
            return $count;
        } catch (Exception $exception) {
            return 0;
        }
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\', '/', $path);
        $values = explode('/', $path);
        return implode(DIRECTORY_SEPARATOR, $values);
    }

    public function get_custom_tree_dir()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-exclude');

        try {
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
            $uploads_path = str_replace('\\', '/', $uploads_path);
            $uploads_path = $uploads_path . '/';

            if ($path == $uploads_path) {
                $init = true;
                $key = gmdate('Y', time());
            } else {
                $init = false;
                $key = gmdate('Y', time());
            }

            $path = trailingslashit(str_replace('\\', '/', realpath($path)));

            if ($dh = opendir($path)) {
                while (substr($path, -1) == '/') {
                    $path = rtrim($path, '/');
                }
                $skip_paths = array(".", "..");

                while (($value = readdir($dh)) !== false) {
                    trailingslashit(str_replace('\\', '/', $value));
                    if (!in_array($value, $skip_paths)) {
                        if (is_dir($path . '/' . $value)) {
                            $node['children'] = $this->has_children($path . '/' . $value);
                            $node['id'] = $path . '/' . $value;
                            if ($init) {
                                if ($value == $key) {
                                    $node['state']['opened'] = true;
                                }
                            }
                            //
                            $node['icon'] = 'dashicons dashicons-open-folder cx-icon-color-techblue compressx-text';
                            $node['children_count'] = $this->get_children_count($path . '/' . $value);
                            $node['text'] = $value . ',(' . $node['children_count'] . ') images';
                            $node['text'] .= '<span class="dashicons dashicons-insert cx-remove-rule cx-add-custom-exclude-tree" data-id="' . $node['id'] . '"></span>';
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
                        } else {
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
        } catch (Exception $error) {
            $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
            error_log($message);
            echo wp_json_encode(array('result' => 'failed', 'error' => $message));
        }
        die();
    }

    public function has_children($path)
    {
        if ($dh = opendir($path)) {
            while (substr($path, -1) == '/') {
                $path = rtrim($path, '/');
            }
            $skip_paths = array(".", "..");

            while (($value = readdir($dh)) !== false) {
                trailingslashit(str_replace('\\', '/', $value));
                if (!in_array($value, $skip_paths)) {
                    if (is_dir($path . '/' . $value)) {
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
        $path = str_replace($base_dir, '', $path);
        $path_arr = explode('/', $path);

        if (count($path_arr) >= 1) {
            if (is_numeric($path_arr[0]) && $path_arr[0] > 1950 && $path_arr[0] < 2050) {
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
                if ($file->isDir()) {
                    continue;
                }

                $files++;
            }

            return $files;
        } catch (Exception $e) {
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

        CompressX_Options::update_option('compressx_media_excludes', $new_excludes);

        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));
        $html = '<ul>';
        foreach ($new_excludes as $exclude) {
            $path = str_replace($abs_path, '', $exclude);
            $html .= '<li><span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>' .
                '<span>' . $path . ',(' . $this->get_children_count($exclude) . ') images' . '</span>' .
                '<span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-exclude-tree" data-id="' . $exclude . '"></span></li>';
        }
        $html .= '<ul>';
        $ret['result'] = 'success';
        $ret['html'] = $html;
        echo wp_json_encode($ret);
        die();
    }

    public function add_exclude_folder()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-exclude');

        $exclude = sanitize_text_field($_POST['id']);
        $abs_path = trailingslashit(str_replace('\\', '/', realpath(ABSPATH)));
        $path = str_replace($abs_path, '', $exclude);

        $html = '<li><span class="dashicons dashicons-open-folder cx-icon-color-techblue"></span>' .
            '<span>' . $path . ',(' . $this->get_children_count($exclude) . ') images' . '</span>' .
            '<span class="dashicons dashicons-remove cx-remove-rule cx-remove-custom-exclude-tree" data-id="' . $exclude . '"></span></li>';
        $ret['result'] = 'success';
        $ret['html'] = $html;
        echo wp_json_encode($ret);
        die();
    }

    public function remove_exclude_folders()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-use-exclude');

        $excludes = CompressX_Options::get_option('compressx_media_excludes', array());

        $id = sanitize_text_field($_POST['id']);
        unset($excludes[$id]);

        CompressX_Options::update_option('compressx_media_excludes', $excludes);

        $ret['result'] = 'success';

        echo wp_json_encode($ret);
        die();
    }

    public function save_settings()
    {
        global $compressx;
        $compressx->ajax_check_security();

        if (isset($_POST['settings']) && !empty($_POST['settings']))
        {
            $json_settings = sanitize_text_field($_POST['settings']);
            $json_settings = stripslashes($json_settings);
            $settings = json_decode($json_settings, true);
            if (is_null($settings))
            {
                $ret['result'] = 'failed';
                $ret['error'] = 'json decode failed';
                echo wp_json_encode($ret);
                die();
            }
        }
        else
        {
            die();
        }

        $options = CompressX_Options::get_option('compressx_general_settings', array());

        if (isset($settings['size_settings']))
        {
            $intermediate_image_sizes = get_intermediate_image_sizes();

            if (isset($settings['size_settings']['og'])) {
                $options['skip_size']['og'] = !$settings['size_settings']['og'];
            } else {
                $options['skip_size']['og'] = true;
            }

            foreach ($intermediate_image_sizes as $size_key) {
                if (isset($settings['size_settings'][$size_key])) {
                    $options['skip_size'][$size_key] = !$settings['size_settings'][$size_key];
                } else {
                    $options['skip_size'][$size_key] = true;
                }
            }
        }

        // Handle list_view_setting (Media Library Display Options)
        if (isset($settings['list_view_setting'])) {
            $media_library_list_view = CompressX_Options::get_option('compressx_media_library_list_view', array());

            $media_library_list_view['delete_thumbnails'] = isset($settings['list_view_setting']['delete_thumbnails']) && $settings['list_view_setting']['delete_thumbnails'] == '1';
            $media_library_list_view['delete_thumbnails_saved'] = isset($settings['list_view_setting']['delete_thumbnails_saved']) && $settings['list_view_setting']['delete_thumbnails_saved'] == '1';
            $media_library_list_view['dropdown_delete_thumbnails'] = isset($settings['list_view_setting']['dropdown_delete_thumbnails']) && $settings['list_view_setting']['dropdown_delete_thumbnails'] == '1';
            $media_library_list_view['watermark_status'] = isset($settings['list_view_setting']['watermark_status']) && $settings['list_view_setting']['watermark_status'] == '1';
            $media_library_list_view['dropdown_watermark'] = isset($settings['list_view_setting']['dropdown_watermark']) && $settings['list_view_setting']['dropdown_watermark'] == '1';

            CompressX_Options::update_option('compressx_media_library_list_view', $media_library_list_view);
        }

        // Handle cache_control_setting
        $reset_rewrite = false;
        if (isset($settings['cache_control_setting']))
        {
            if (isset($settings['cache_control_setting']['disable_cache_control']))
            {
                $old_value = isset($options['disable_cache_control']) ? $options['disable_cache_control'] : false;
                $new_value = $settings['cache_control_setting']['disable_cache_control'] == '1';

                if ($old_value != $new_value)
                {
                    $reset_rewrite = true;
                    $options['disable_cache_control'] = $new_value;
                }
            }
        }

        CompressX_Options::update_option('compressx_general_settings', $options);

        if ($reset_rewrite)
        {
            $image_load = isset($options['image_load']) ? $options['image_load'] : 'htaccess';
            if ($image_load == 'htaccess')
            {
                include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';
                $rewrite = new CompressX_Webp_Rewrite();
                $rewrite->create_rewrite_rules();
            }
            else if ($image_load == 'compat_htaccess')
            {
                include_once COMPRESSX_DIR . '/includes/class-compressx-webp-rewrite.php';
                $rewrite = new CompressX_Webp_Rewrite();
                $rewrite->create_rewrite_rules_ex();
            }
        }

        if (isset($_POST['excludes']) && !empty($_POST['excludes']))
        {
            $json_settings = sanitize_text_field($_POST['excludes']);
            $json_settings = stripslashes($json_settings);
            $excludes = json_decode($json_settings, true);
            if (is_null($excludes))
            {
                $ret['result'] = 'failed';
                $ret['error'] = 'json decode failed';
                echo wp_json_encode($ret);
                die();
            }

            CompressX_Options::update_option('compressx_media_excludes', $excludes);

        }

        $ret['result'] = 'success';
        echo wp_json_encode($ret);
        die();
    }

    public function delete_files()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-delete');

        global $wpdb;

        $where['meta_key'] = "compressx_image_meta_status";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta_webp_converted";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta_avif_converted";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta_compressed";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta_og_file_size";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta_webp_converted_size";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta_avif_converted_size";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta_compressed_size";
        $wpdb->delete($wpdb->postmeta, $where);

        $where['meta_key'] = "compressx_image_meta";
        $wpdb->delete($wpdb->postmeta, $where);

        delete_transient('compressx_set_global_stats');

        $table_name = $wpdb->prefix . "compressx_files_opt_meta";
        $exists = $wpdb->get_var(
            $wpdb->prepare("SHOW TABLES LIKE %s", $table_name)
        );
        if ($exists == $table_name)
        {
            $wpdb->get_results("TRUNCATE TABLE $table_name", ARRAY_A);
        }

        CompressX_Image_Meta_V2::delete_all_image_meta();
        $this->_delete_files();

        $ret['result'] = "success";
        echo wp_json_encode($ret);
        die();
    }

    public function _delete_files()
    {
        $path = WP_CONTENT_DIR . '/compressx-nextgen';
        $this->deleteDir($path);

        include_once COMPRESSX_DIR . '/includes/class-compressx-default-folder.php';
        $dir = new CompressX_default_folder();
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

    private function get_custom_children_count($path)
    {
        try
        {
            $convert_to_avif = CompressX_Options::get_option('compressx_output_format_avif', 1);
            if ($convert_to_avif&&CompressX_Image_Opt_Method::is_support_avif())
            {
                $convert_to_avif = true;
            }
            else
            {
                $convert_to_avif = false;
            }

            $support_extension = array('jpg', 'jpeg', 'png', 'webp');
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            $files = 0;

            foreach ($rii as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $extension = strtolower($file->getExtension());
                if (in_array($extension, $support_extension)) {
                    $filename = $path . '/' . $file->getFilename();
                    $type = pathinfo($filename, PATHINFO_EXTENSION);

                    if ($type == 'webp' && $convert_to_avif) {
                        $files++;
                    } else if ($type != 'webp') {
                        $files++;
                    }
                }
            }

            return $files;
        } catch (Exception $e) {
            return 0;
        }
    }
}