<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Addons
{
    public function __construct()
    {

    }

    public function display()
    {
        ?>
        <div id="compressx-root">
            <div id="compressx-wrap">
                <?php
                $this->output_nav();
                $this->output_header();
                $this->output_addons();
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

    public function output_addons()
    {
        ?>
        <header>
            <div class="compressx-container compressx-header">
                <div class="compressx-header-dashboard">
                    <h5>Addons <a href="https://compressx.io">(Pro Only)</a></h5>
                </div>
                <div class="compressx-addons-grid">
                    <div class="cx-addons" style="position: relative;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-calendar-alt compressx-icon-style"></span>
                        </div>
                        <div>
                            <h5>Cron Support</h5>
                            <span>Automates your image optimization workflows with a robust cron system, flexibly scheduling AVIF/WebP conversions for new, existing, and popular images.</span>
                        </div>

                    </div>
                    <div class="cx-addons" style="position: relative;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-format-gallery compressx-icon-style" style="color:#f25767"></span>
                        </div>
                        <div>
                            <h5>Thumbnails Generation/reGeneration</h5>
                            <span>Regenerate thumbnails (WebP, AVIF, JPG, or PNG) for your website images based on your settings. Add custom thumbnail sizes. Remove unused and orphan thumbnails and their metadata.</span>
                        </div>
                    </div>
                    <div class="cx-addons" style="position: relative;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-shortcode compressx-icon-style" style="color:#071c4d"></span>
                        </div>
                        <div>
                            <h5>WP-CLI</h5>
                            <span>Use WP-CLI to efficiently convert images in your WordPress media library. You can choose to convert all images, or a specific image, or convert the latest n images(coming soon).</span>
                        </div>

                    </div>

                    <div class="cx-addons" style="position: relative;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-cover-image compressx-icon-style"></span>
                        </div>
                        <div>
                            <h5>Watermark</h5>
                            <span>Add watermarks to your images during the process of uploading, bulk WebP/AVIF conversion, and thumbnail regeneration (WebP, AVIF, JPG, or PNG).</span>
                        </div>

                    </div>
                    <div class="cx-addons" style="position: relative;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-cloud compressx-icon-style"></span>
                        </div>
                        <div>
                            <h5>CDN Integration</h5>
                            <span>Integrate with Cloudflare pro to convert and serve your website images as WebP/AVIF formats. Enable ‘Vary for images’ to dynamically serve correct variants.</span>
                        </div>

                    </div>
                    <div class="cx-addons" style="position: relative;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-groups compressx-icon-style" style="color:#f25767"></span>
                        </div>
                        <div>
                            <h5>Roles and Capabilities</h5>
                            <span>Control CompressX Pro settings and features by setting a super admin. The super admin can grant access for other user roles include admin.</span>
                        </div>

                    </div>
                    <div class="cx-addons" style="position: relative;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-wordpress compressx-icon-style" style="color:#ffb116"></span>
                        </div>
                        <div>
                            <h5>Multisite Support</h5>
                            <span>Coming soon..</span>
                        </div>

                    </div>
                    <div class="cx-addons" style="position: relative; display:none;">
                        <div class="cx-addons-icon-block">
                            <span class="dashicons dashicons-wordpress compressx-icon-style" style="color:#ffb116"></span>
                        </div>
                        <div>
                            <h5>Multisite Support</h5>
                            <span>Include rules are processed first, however, if an exclude rule matches data that matched the include rule filter, the data is excluded</span>
                        </div>
                        <label class="compressx-switch" title="Enable/Disable the addon" style="position:absolute; top:1rem;right:1rem">
                            <input type="checkbox">
                            <span class="compressx-slider compressx-round"></span>
                        </label>
                    </div>

                </div>
            </div>
        </header>
        <?php
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }
}