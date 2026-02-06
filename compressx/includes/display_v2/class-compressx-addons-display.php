<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Addons_Display
{
    public function __construct()
    {
    }

    public function display()
    {
        ?>
        <div class="compressx-root">
            <div class="compressx-v2-py-6 compressx-v2-w-full compressx-v2-max-w-[1200px] compressx-v2-mx-auto">
                <?php
                $this->output_header();
                $this->output_addons();
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
                    <?php echo esc_html(__('Addons', 'compressx')); ?>
                </h1>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-2">
                    <?php echo esc_html(__('Extra utilities to simplify your workflow and manage images beyond compression.', 'compressx')); ?>
                </p>
            </div>
            <div class="compressx-v2-mt-4">
                <button onclick="window.open('https://compressx.io/pricing', '_blank')" class="compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-rounded compressx-v2-px-4 compressx-v2-py-2 compressx-v2-text-sm">
                    <?php esc_html_e('Upgrade Now', 'compressx') ?>
                </button>
            </div>
        </div>
        <?php
    }

    public function output_addons()
    {
        ?>
        <section class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-6">
            <div class="compressx-v2-grid sm:compressx-v2-grid-cols-2 lg:compressx-v2-grid-cols-3 compressx-v2-gap-6">

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('Advanced Compression Level Settings', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Maximize image optimization with smart algorithms that reduce file size while preserving visual quality. Ideal for users who want the best balance of speed and image clarity for their site', 'compressx') ?>
                        </p>
                    </div>
                </div>

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('Remove JPG/PNG Thumbnails', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Automatically delete original JPG/PNG thumbnails after converting them to WebP or AVIF to free up disk space.', 'compressx') ?>
                        </p>
                    </div>
                </div>

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('Cron Support', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Automates your image optimization workflows with a robust cron system, flexibly scheduling AVIF/WebP conversions for new, existing, and popular images.', 'compressx') ?>
                        </p>
                    </div>
                </div>

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('Thumbnails Generation/reGeneration', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Regenerate thumbnails (WebP, AVIF, JPG, or PNG) for your website images based on your settings. Add custom thumbnail sizes. Remove unused and orphan thumbnails and their metadata.', 'compressx') ?>
                        </p>
                    </div>
                </div>

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('WP-CLI', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Use WP-CLI to efficiently convert images in your WordPress media library. You can choose to convert all images, or a specific image, or convert the latest n images.', 'compressx') ?>
                        </p>
                    </div>
                </div>



                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('Watermark', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Add watermarks to your images during the process of uploading, bulk WebP/AVIF conversion, and thumbnail regeneration (WebP, AVIF, JPG, or PNG).', 'compressx') ?>
                        </p>
                    </div>
                </div>

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('CDN Integration', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Integrate with Cloudflare Pro and and BunnyCDN to convert and serve your website images as WebP/AVIF formats. Enable \'Vary for images\' to dynamically serve correct variants.', 'compressx') ?>
                        </p>
                    </div>
                </div>

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('Roles and Capabilities', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Control CompressX Pro settings and features by setting a super admin. The super admin can grant access for other user roles include admin.', 'compressx') ?>
                        </p>
                    </div>
                </div>

                <div class="compressx-v2-bg-white compressx-v2-rounded compressx-v2-shadow-sm compressx-v2-border compressx-v2-p-5 compressx-v2-flex compressx-v2-flex-col compressx-v2-justify-between">
                    <div>
                        <h2 class="compressx-v2-text-lg compressx-v2-font-medium compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                            <?php esc_html_e('Multisite Support', 'compressx') ?>
                        </h2>
                        <p class="compressx-v2-text-sm compressx-v2-text-gray-500 compressx-v2-mt-2">
                            <?php esc_html_e('Manage and optimize images across all sites in your WordPress Multisite network from a single dashboard.', 'compressx') ?>
                        </p>
                    </div>
                </div>

            </div>
        </section>
        <?php
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

}