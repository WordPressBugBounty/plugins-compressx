<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Media_Replace_Display
{
    public $attachment_id;
    public function __construct()
    {

        add_action('wp_ajax_compressx_save_media_replace_setting', array($this, 'save_settings'));
        add_action('wp_ajax_compressx_replace_media', array($this, 'replace_media'));

        add_filter('media_row_actions', array($this,'add_row_action'), 10, 2);
        add_action('add_meta_boxes_attachment',array($this,'add_meta_boxes'), 10);
        add_filter('attachment_fields_to_edit',array($this,'attachment_editor'), 10,2);
        //
    }

    public function add_row_action($actions, $post)
    {
        if(!$this->is_supported_image($post))
        {
            return $actions;
        }

        $post_id = $post->ID;
        $url = admin_url("upload.php");
        $url = add_query_arg(array(
            'page' => 'media-replace-compressx',
            'attachment_id' => $post_id,
        ), $url);
        $actions['compressx_media_replace']="<a href='$url' aria-label='Replace Media' rel='permalink'>Replace Media</a>";

        return $actions;
    }

    public function add_meta_boxes($post)
    {
        if (! $post || ! is_object($post)) {
            return;
        }

        if (! current_user_can('upload_files')) {
            return;
        }

        $mime = (string) get_post_mime_type($post);
        if (strpos($mime, 'image/') !== 0) {
            return;
        }

        add_meta_box(
            'compressx-media-replace-box',
            __('Replace Media', 'compressx'),
            array($this,'render_media_replace_metabox'),
            'attachment',
            'side',
            'low'
        );
    }

    public function render_media_replace_metabox($post)
    {
        $attachment_id = (int) $post->ID;

        $url = add_query_arg(
            array(
                'page'          => 'media-replace-compressx',
                'attachment_id' => $attachment_id,
            ),
            admin_url('upload.php')
        );

        $url = wp_nonce_url($url, 'compressx_media_replace');

        echo '<p><a class="button button-secondary" href="' . esc_url($url) . '">'
            . esc_html__('Upload & Replace Media File', 'compressx')
            . '</a></p>';

        echo '<p class="description">'
            . esc_html__('Replace this attachment and regenerate thumbnails according to your settings.', 'compressx')
            . '</p>';
    }

    public function attachment_editor($form_fields, $post)
    {
        if (! current_user_can('upload_files')) {
            return $form_fields;
        }

        $mime = (string) get_post_mime_type($post);
        if (strpos($mime, 'image/') !== 0)
        {
            return $form_fields;
        }

        if (function_exists('get_current_screen'))
        {
            $screen = get_current_screen();
            if ($screen && $screen->id === 'attachment') {
                return $form_fields;
            }
        }

        if (wp_doing_ajax())
        {
            $ref = wp_get_referer();
            if (!$ref && !empty($_SERVER['HTTP_REFERER'])) {
                $ref = $_SERVER['HTTP_REFERER'];
            }

            if (!$ref || strpos($ref, 'upload.php') === false) {
                return $form_fields;
            }
        }

        $url = add_query_arg(
            array(
                'page'          => 'media-replace-compressx',
                'attachment_id' => (int) $post->ID,
            ),
            admin_url('upload.php')
        );

        $url = wp_nonce_url($url, 'compressx_media_replace');

        $form_fields['compressx-media-replace'] = array(
            'label' => __('Replace Media', 'compressx'),
            'input' => 'html',
            'html'  => '<a class="button button-secondary" href="' . esc_url($url) . '">' .
                esc_html__('Upload a new file', 'compressx') .
                '</a>',
            'helps' => __('Click to replace this file via CompressX.', 'compressx'),
        );

        return $form_fields;
    }

    public function is_supported_image($post)
    {
        if (! is_object($post))
        {
            return false;
        }
        $post_id = $post->ID;
        $post_type = $post->post_type;

        if ($post_type !== 'attachment')
        {
            return false;
        }

        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif");

        $mime_type=get_post_mime_type($post_id);
        if(in_array($mime_type,$supported_mime_types))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function display()
    {
        if (array_key_exists("attachment_id", $_GET) && intval($_GET["attachment_id"]) > 0)
        {
            $this->attachment_id= intval($_GET["attachment_id"]);
            ?>
            <div class="compressx-root">
                <div class="compressx-v2-py-6 compressx-v2-w-full compressx-v2-max-w-[1200px] compressx-v2-mx-auto">
                    <?php
                    $this->output_header();
                    $this->output_media_replace_v2();
                    $this->output_footer();
                    ?>
                </div>
            </div>
            <?php
        }
    }

    public function output_header()
    {
        ?>
        <div class=" compressx-v2-pr-4 compressx-v2-flex compressx-v2-items-center compressx-v2-justify-between compressx-v2-mb-4">
            <div>
                <h1 class="compressx-v2-text-2xl compressx-v2-font-semibold compressx-v2-text-gray-900">
                    <?php echo esc_html(__('Replace Media', 'compressx')); ?>
                </h1>
                <p class="compressx-v2-text-sm compressx-v2-text-gray-600 compressx-v2-mt-2">
                    <?php echo esc_html(__(' Safely replace this media file without changing its URL.', 'compressx')); ?>
                </p>
            </div>
        </div>
        <?php
    }

    public function output_media_replace_v2()
    {
        ?>
        <div class="compressx-v2-bg-white">
            <div class="compressx-v2-grid compressx-v2-grid-cols-1 md:compressx-v2-grid-cols-10 compressx-v2-p-4 compressx-v2-gap-6">
                <!-- Left: Settings (30%) -->
                <?php $this->output_media_replace_setting_v2()?>
                <!-- Right: Manipulation (70%) -->
                <?php $this->output_media_replace_manipulation()?>
            </div>
            <section class="compressx-v2-sticky compressx-v2-bottom-0 compressx-v2-bg-white compressx-v2-border-t compressx-v2-border-gray-200 compressx-v2-p-4">
                <div class="compressx-v2-max-w-[1200px] compressx-v2-mx-auto compressx-v2-flex compressx-v2-justify-end compressx-v2-gap-3">
                    <!-- Save button -->
                    <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-2">
                        <button id="cx_replace_media" class="compressx-v2-inline-flex compressx-v2-items-center compressx-v2-gap-1 compressx-v2-bg-blue-600 hover:compressx-v2-bg-blue-700 compressx-v2-text-white compressx-v2-text-sm compressx-v2-font-medium compressx-v2-px-4 compressx-v2-py-2 compressx-v2-rounded">
                            Replace Media
                        </button>
                        <span id="cx_replace_media_progress" class="compressx-v2-flex compressx-v2-items-center compressx-v2-hidden">
                        <img src="<?php echo esc_url(is_network_admin() ? network_admin_url('images/loading.gif') : admin_url('images/loading.gif')); ?>" alt="Loading..." style="width: 16px; height: 16px;">
                    </span>
                        <span id="cx_replace_media_text" class="success compressx-v2-hidden compressx-v2-text-sm compressx-v2-font-medium" style="color:#007017"><?php esc_html_e('Success!', 'compressx') ?></span>
                    </div>
                    <input type="hidden" id="compressx_attachment_id" value="<?php echo esc_attr( $this->attachment_id ); ?>" style="display:none;"/>
            </section>
        </div>
        <?php
    }

    public function output_media_replace_setting_v2()
    {
        $options=CompressX_Options::get_option('compressx_media_replace',array());
        $auto_re_optimize=isset($options['auto_re_optimize'])?$options['auto_re_optimize']:true;
        $thumbnail_generation=isset($options['thumbnail_generation'])?$options['thumbnail_generation']:'default_fill';
        if($thumbnail_generation=="default_fill")
        {
            $default_fill=true;
            $match_original=false;
        }
        else
        {
            $default_fill=false;
            $match_original=true;
        }

        $backup_original=false;

        ?>
        <div class="md:compressx-v2-col-span-3">
            <div class="compressx-v2-bg-[#F2FBFA] compressx-v2-border compressx-v2-p-4 compressx-v2-mb-4 compressx-v2-rounded">
                <h4 class="compressx-v2-text-xs compressx-v2-font-medium compressx-v2-text-gray-700 compressx-v2-mb-3">
                    Current Compression Settings
                </h4>
                <ul class="compressx-v2-text-xs compressx-v2-text-gray-600 compressx-v2-space-y-1.5">
                    <li>
                        <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-4 compressx-v2-text-gray-700">
                            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-1.5">
                                <span class="dashicons dashicons-yes compressx-v2-text-green-600"></span>
                                <span class="compressx-v2-font-medium">General</span>
                            </div>
                            <span class="compressx-v2-text-gray-300">|</span>

                            <div class="compressx-v2-flex compressx-v2-items-center compressx-v2-gap-1.5 compressx-v2-text-gray-400">
                                <span class="dashicons dashicons-star-filled"></span>
                                <span class="compressx-v2-font-medium">Smart</span>
                                <span class="compressx-v2-text-[11px]"><a href="https://compressx.io/pricing/plans/">(Pro)</a></span>
                            </div>
                        </div>
                    </li>
                    <li>• Formats: <span class="compressx-v2-font-medium">WebP, AVIF</span></li>
                    <li>• Thumbnails: <span class="compressx-v2-font-medium">All Selected Thumbnail sizes</span></li>
                </ul>
            </div>
            <div class="compressx-v2-border compressx-v2-p-4">
                <div class="compressx-v2-pb-4">
                    <div class="compressx-v2-grid compressx-v2-grid-cols-[auto,1fr] compressx-v2-gap-4 compressx-v2-border-b compressx-v2-mb-4 compressx-v2-items-start">
                        <!-- Checkbox -->
                        <div class="compressx-v2-pt-1">
                            <input id="cx_auto_re_optimize" type="checkbox" checked class="compressx-v2-h-4 compressx-v2-w-4" <?php echo esc_attr(checked($auto_re_optimize));?> >
                        </div>

                        <!-- Title + Description -->
                        <div>
                            <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-800">
                                Auto re-optimize after replacement<br>
                                <span id="auto_re_optimize-saved-indicator"  class="compressx-v2-ml-2 compressx-v2-text-xs compressx-v2-text-green-600 hidden">✓ Saved</span>
                            </h3>
                            <p class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-mt-1">
                                Replace all WordPress-generated thumbnails with new ones created from the replaced file, then re-optimize them automatically.
                            </p>
                            <div class="compressx-v2-mt-4 compressx-v2-mb-4">
                                <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-900 compressx-v2-mb-3">
                                    Thumbnail Generation Strategy
                                </h3>
                                <div class="compressx-v2-space-y-3">
                                    <!-- Option 1 -->
                                    <label class="compressx-v2-grid compressx-v2-grid-cols-[auto,1fr] compressx-v2-items-center compressx-v2-gap-3 compressx-v2-cursor-pointer">
                                        <input type="radio" name="thumbnail_strategy" value="default_fill" <?php echo esc_attr(checked($default_fill));?>/>
                                        <div>
                                            <div class="compressx-v2-text-sm compressx-v2-text-gray-900">
                                                Use current default sizes and fill missing ones (Recommended)<br>
                                                <span id="default_fill-saved-indicator" class="compressx-v2-ml-2 compressx-v2-text-xs compressx-v2-text-green-600 hidden">✓ Saved</span>
                                            </div>
                                            <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                                Generate thumbnails using the current default sizes and automatically create any missing sizes.
                                            </div>
                                        </div>
                                    </label>
                                    <!-- Option 2 -->
                                    <label class="compressx-v2-grid compressx-v2-grid-cols-[auto,1fr] compressx-v2-items-center compressx-v2-gap-3 compressx-v2-cursor-pointer">
                                        <input type="radio" name="thumbnail_strategy" value="match_original" class="compressx-v2-mt-1" <?php echo esc_attr(checked($match_original));?>/>
                                        <div>
                                            <div class="compressx-v2-text-sm compressx-v2-text-gray-900">
                                                Match original thumbnail sizes<br>
                                                <span id="match_original-saved-indicator" class="compressx-v2-ml-2 compressx-v2-text-xs compressx-v2-text-green-600 hidden">✓ Saved</span>
                                            </div>
                                            <div class="compressx-v2-text-xs compressx-v2-text-gray-500">
                                                Generate new thumbnails strictly based on the original image’s existing thumbnail sizes.
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="compressx-v2-grid compressx-v2-grid-cols-[auto,1fr] compressx-v2-border-b compressx-v2-pb-4 compressx-v2-mb-4 compressx-v2-gap-4 compressx-v2-items-start">
                        <!-- Checkbox -->
                        <div class="compressx-v2-pt-1">
                            <input id="cx_backup_original" type="checkbox"  class="compressx-v2-h-4 compressx-v2-w-4" disabled>
                        </div>
                        <!-- Title + Description -->
                        <div>
                            <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-text-gray-800">
                                Keep temporary versions after replacement <span class="compressx-v2-text-gray-400"><a href="https://compressx.io/pricing/plans/">(Pro)</a></span>
                            </h3>

                            <!-- Temporary Versions Retention -->
                            <div class="compressx-v2-space-y-3 compressx-v2-mt-3">
                                <!-- Versions count -->
                                <div class="compressx-v2-space-y-1">
                                    <label class="compressx-v2-text-xs compressx-v2-text-gray-600">
                                        Keep up to
                                    </label>
                                    <select class="compressx-v2-w-full
                                                        compressx-v2-border
                                                        compressx-v2-border-gray-300
                                                        compressx-v2-rounded
                                                        compressx-v2-h-8
                                                        compressx-v2-px-2
                                                        compressx-v2-text-sm">
                                        <option>1</option>
                                        <option>2</option>
                                        <option>3</option>
                                    </select>
                                    <div class="compressx-v2-text-[11px] compressx-v2-text-gray-500">
                                        versions
                                    </div>
                                </div>
                                <!-- Retention time -->
                                <div class="compressx-v2-space-y-1">
                                    <label class="compressx-v2-text-xs compressx-v2-text-gray-600">
                                        Retention period
                                    </label>
                                    <select class="compressx-v2-w-full compressx-v2-border compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-h-8 compressx-v2-px-2 compressx-v2-text-sm">
                                        <option value="1">1 day</option>
                                        <option value="3">3 days</option>
                                        <option value="7">7 days</option>
                                    </select>
                                </div>
                                <!-- Helper text -->
                                <div class="compressx-v2-text-[11px] compressx-v2-text-gray-500">
                                    Versions are removed automatically after expiration.
                                </div>
                            </div>
                            <p class="compressx-v2-text-[11px] compressx-v2-text-gray-400 compressx-v2-mt-1">
                                This is a short-term safety buffer, not a backup system.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function output_media_replace_manipulation()
    {
        ?>
        <div class="md:compressx-v2-col-span-7">
            <?php $this->output_current_media_v2()?>
            <?php $this->output_upload_new_media_v2()?>
            <!-- Replace Impact -->
            <div class="compressx-v2-mb-4">
                <h3 class="compressx-v2-text-sm compressx-v2-font-medium compressx-v2-mb-2">
                    After Replacement
                </h3>

                <ul class="compressx-v2-text-sm compressx-v2-text-gray-700 compressx-v2-space-y-1">
                    <li>✓ URL unchanged</li>
                    <li>✓ Attachment ID unchanged</li>
                    <li>✓ Thumbnails regenerated (PNG/JPG/WEBP/AVIF)</li>
                    <li>✓ Formats re-optimized</li>
                    <li>✓ The previous version will be kept for <span>1</span> day.</li>
                </ul>
            </div>
        </div>
        <?php
    }

    public function output_current_media_v2()
    {
        $url='';
        $medium = wp_get_attachment_image_src($this->attachment_id, 'medium');
        if (is_array($medium) && !empty($medium[0]))
        {
            $url   = $medium[0];
        }
        else
        {
            $full = wp_get_attachment_image_src($this->attachment_id, 'full');
            $full_url = $full[0];
            $full_w   = (int) $full[1];

            if ($full_w > 0 && $full_w < 300)
            {
                $url   = $full_url;
            }
            else
            {
                $meta = wp_get_attachment_metadata($this->attachment_id);
                if (!empty($meta['sizes']) && is_array($meta['sizes']))
                {
                    $candidates = [];

                    foreach ($meta['sizes'] as $size_name => $info)
                    {
                        if (empty($info['width']) || empty($info['file'])) continue;

                        $w = (int) $info['width'];
                        if ($w >= 300)
                        {
                            $candidates[] = ['w' => $w,'s'=>$size_name];
                        }
                    }

                    if (!empty($candidates))
                    {
                        usort($candidates, function($a, $b) { return $a['w'] <=> $b['w']; });

                        $src=wp_get_attachment_image_src($this->attachment_id, $candidates[0]['s']);
                        $url=$src[0];
                    }
                }
            }

            if(empty($url))
            {
                $url=$full_url;
            }
        }

        $url.='?v='.time();

        $width=0;
        $height=0;
        $size=0;
        $meta = wp_get_attachment_metadata($this->attachment_id);
        $file = get_attached_file($this->attachment_id);
        if (is_array($meta))
        {
            if(!empty($meta['filesize']))
            {
                $size=(int)$meta['filesize'];
            }

            if (!empty($meta['width']))
                $width  = (int) $meta['width'];
            if (!empty($meta['height']))
                $height = (int) $meta['height'];
        }
        else
        {
            if ($file && file_exists($file))
            {
                $size = (int) @filesize($file);

                $img = @getimagesize($file);
                if (is_array($img) && !empty($img[0]) && !empty($img[1]))
                {
                    $width = (int) $img[0];
                    $height = (int) $img[1];
                }
            }
        }

        $dimensions="Dimensions: $width × $height";
        $size="File size: ".size_format($size,2);
        if(CompressX_Image_Meta_V2::is_image_optimized($this->attachment_id))
        {
            $status="Status: Optimized";
        }
        else
        {
            $status="Status: Unoptimized";
        }

        if(!empty($meta['sizes']))
            $thumbnail_counts="Thumbnails: ".count($meta['sizes']);
        else
            $thumbnail_counts="Thumbnails: 0";

        $mime_type=get_post_mime_type($this->attachment_id);
        $ft = wp_check_filetype($file);
        $ext = !empty($ft['ext']) ? $ft['ext'] : '';
        $type = $ext ? strtoupper($ext) : '-';
        ?>
        <div class="compressx-v2-mb-4">
            <div class="compressx-v2-border-b compressx-v2-pb-4 compressx-v2-mb-4">
                <h3 class="compressx-v2-text-sm compressx-v2-font-medium">
                    <span>Current Media</span>
                </h3>
            </div>
            <div class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-4 compressx-v2-items-start">
                <div class="compressx-v2-bg-gray-100 compressx-v2-rounded compressx-v2-flex compressx-v2-items-center compressx-v2-justify-center">
                    <img src="<?php echo esc_url( $url ); ?>" alt="Replace preview"/>
                </div>
                <div class="compressx-v2-text-sm compressx-v2-text-gray-700">
                    <div id="og_image_dimensions" data-mime="<?php echo esc_attr( $mime_type ); ?>" data-width="<?php echo esc_attr( $width ); ?>" data-height="<?php echo esc_attr( $height ); ?>" ><?php echo esc_html( $dimensions ); ?></div>
                    <div>Format: <span><?php echo esc_html( $type ); ?></span></div>
                    <div><?php echo esc_html( $size ); ?></div>
                    <div><?php echo esc_html( $status ); ?></div>
                    <div><?php echo esc_html( $thumbnail_counts ); ?></div>
                    <div class="compressx-v2-mt-2
                                                compressx-v2-flex compressx-v2-items-start compressx-v2-gap-1.5
                                                compressx-v2-text-[11px] compressx-v2-text-gray-400">
                        <p class="compressx-v2-leading-snug">
                            <span class="compressx-v2-font-medium compressx-v2-text-gray-500">Note:</span>
                            The image preview may still show a cached version due to browser or CDN caching. <a href="https://compressx.io/docs/media-replace/">Learn more...</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function output_upload_new_media_v2()
    {
        ?>
        <div class="compressx-v2-mb-4">
            <div class="compressx-v2-bg-gray-50 compressx-v2-border compressx-v2-border-dashed compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-4">
                <h3 class="compressx-v2-text-sm compressx-v2-font-semibold compressx-v2-text-gray-900 compressx-v2-mb-1">
                    New Media
                </h3>
                <div class="compressx-v2-text-xs compressx-v2-text-gray-500 compressx-v2-leading-relaxed">
                    For best visual consistency, upload an image with a similar width (or slightly wider) than the original.
                    <a href="https://compressx.io/docs/media-replace/" class="compressx-v2-text-blue-600 hover:compressx-v2-underline">Learn more...</a>
                </div>
            </div>
            <div id="cx-upload-notice" style="display: none" class="compressx-v2-bg-yellow-50 compressx-v2-border-l-4 compressx-v2-border-yellow-400 compressx-v2-rounded compressx-v2-p-4 compressx-v2-mb-4 compressx-v2-relative">
                <!-- Close Button -->
                <button id="cx-hide-upload-notice"
                        class="compressx-v2-absolute compressx-v2-top-2 compressx-v2-right-2 compressx-v2-text-yellow-700 hover:compressx-v2-text-yellow-900
                               compressx-v2-text-xs compressx-v2-font-medium compressx-v2-bg-transparent compressx-v2-border compressx-v2-border-yellow-300
                               hover:compressx-v2-bg-yellow-100 compressx-v2-rounded compressx-v2-px-2 compressx-v2-py-0.5 compressx-v2-transition">Dismiss</button>
                <div class="compressx-v2-flex compressx-v2-items-start compressx-v2-gap-3">
                    <!-- Warning Icon -->
                    <span class="dashicons dashicons-warning compressx-v2-text-yellow-500 compressx-v2-text-xl compressx-v2-mt-0.5"></span>
                    <div class="compressx-v2-flex-1">
                        <p class="compressx-v2-font-medium compressx-v2-text-yellow-800">
                            Warning: <span id="cx_notice_title" class="compressx-v2-font-semibold"></span>
                        </p>
                        <p id="cx_notice_content" class="compressx-v2-text-sm compressx-v2-text-yellow-700 compressx-v2-mt-0.5">
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upload Area -->
            <div id="compressx-new-media-dropzone" role="button" tabindex="0" aria-label="Upload new media"
                 class="compressx-v2-border compressx-v2-border-dashed compressx-v2-border-gray-300 compressx-v2-rounded compressx-v2-bg-white compressx-v2-p-12 compressx-v2-text-center compressx-v2-transition hover:compressx-v2-border-gray-400">
                <p class="compressx-v2-text-sm compressx-v2-text-gray-700 compressx-v2-mb-1">
                    Click to upload or drag and drop a file here
                </p>
                <p class="compressx-v2-text-xs compressx-v2-text-gray-400">
                    The file will replace the current media without changing the URL.
                </p>
            </div>
            <div id="compressx-new-media-preview-layout" style="display:none;" class="compressx-v2-grid compressx-v2-grid-cols-2 compressx-v2-gap-4 compressx-v2-items-start">
                <div class="compressx-v2-bg-gray-100 compressx-v2-rounded compressx-v2-flex compressx-v2-items-center compressx-v2-justify-center">
                    <img id="compressx-new-media-preview" alt="Replace preview"/>
                </div>
                <div class="compressx-v2-text-sm compressx-v2-text-gray-700">
                    <div>Dimensions: <span id="compressx-new-media-dim">-</span></div>
                    <div>Format: <span id="compressx-new-media-format">-</span></div>
                    <div>File size: <span id="compressx-new-media-size">-</span></div>
                    <div>
                        <button id="compressx-remove-new-media-preview" type="button" class="compressx-v2-mt-2 compressx-v2-text-xs compressx-v2-font-medium compressx-v2-text-gray-600
                        hover:compressx-v2-text-gray-800 compressx-v2-border compressx-v2-border-gray-300 hover:compressx-v2-border-gray-400
                        compressx-v2-bg-white hover:compressx-v2-bg-gray-50 compressx-v2-rounded compressx-v2-px-2 compressx-v2-py-1 compressx-v2-transition">
                            Remove the uploaded file
                        </button>
                    </div>
                </div>
            </div>
            <input type="file" id="compressx-new-media-file" name="compressx_new_media_file" accept="image/*" style="display:none;"/>
        </div>
        <?php
    }

    public function output_footer()
    {
        do_action('compressx_output_footer');
    }

    public function save_settings()
    {
        global $compressx;
        $compressx->ajax_check_security();

        if (isset($_POST['settings']) && !empty($_POST['settings']))
        {
            $settings=$_POST['settings'];
            $options=CompressX_Options::get_option('compressx_media_replace',array());

            if (isset($settings['auto_re_optimize']))
            {
                $auto_re_optimize=sanitize_text_field($settings['auto_re_optimize']);
                if($auto_re_optimize=='1')
                {
                    $auto_re_optimize=true;
                }
                else
                {
                    $auto_re_optimize=false;
                }
                $options['auto_re_optimize']=$auto_re_optimize;
            }

            if (isset($settings['thumbnail_generation']))
            {
                $thumbnail_generation=sanitize_text_field($settings['thumbnail_generation']);
                if($thumbnail_generation=='default_fill')
                {
                    $thumbnail_generation='default_fill';
                }
                else
                {
                    $thumbnail_generation='match_original';
                }
                $options['thumbnail_generation']=$thumbnail_generation;
            }

            CompressX_Options::update_option('compressx_media_replace',$options);
            $ret['result']='success';
            echo wp_json_encode($ret);
            die();
        }
        else
        {
            die();
        }
    }

    public function replace_media()
    {
        global $compressx;
        $compressx->ajax_check_security();

        if (empty($_FILES['image']) || !is_array($_FILES['image']))
        {
            wp_send_json(['result' => 'failed', 'error' => 'No file uploaded.']);
        }

        if (empty($_POST['attachment_id']))
        {
            wp_send_json(['result' => 'failed', 'error' => 'No attachment_id.']);
        }

        $file = $_FILES['image'];
        $attachment_id = sanitize_text_field($_POST['attachment_id']);
        $allowed_mimes = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'webp'         => 'image/webp',
            'avif'         => 'image/avif',
        ];

        if (!function_exists('wp_handle_upload'))
        {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        add_filter('upload_dir',array($this, 'get_compressx_tmp_folder'));

        $overrides = [
            'test_form' => false,
            'mimes'     => $allowed_mimes,
        ];

        $uploaded = wp_handle_upload($file, $overrides);

        remove_filter('upload_dir', array($this, 'get_compressx_tmp_folder'));

        if (isset($uploaded['error']))
        {
            wp_send_json(['result' => 'failed', 'error' => $uploaded['error']]);
        }

        include_once COMPRESSX_DIR . '/includes/class-compressx-media-replace.php';
        $media_replace=new CompressX_Media_Replace();

        $options=CompressX_Options::get_option('compressx_media_replace',array());

        if(CompressX_Image_Meta_V2::is_image_optimized($attachment_id))
        {
            $this->delete_converted_image($attachment_id);
        }

        $ret=$media_replace->replace($attachment_id,$uploaded['file'],$options);
        if($ret['result']=='success')
        {
            $auto_re_optimize=isset($options['auto_re_optimize'])?$options['auto_re_optimize']:true;
            if($auto_re_optimize)
            {
                $this->reconvert_image($attachment_id);
            }
        }

        echo wp_json_encode($ret);

        die();
    }

    public function reconvert_image($image_id)
    {

        $options=$this->init_option();

        $this->delete_converted_image($image_id);

        $image=new Compressx_Image($image_id,$options);
        $file_path = get_attached_file( $image_id );
        if(empty($file_path))
        {
            $error='Image:'.$image_id.' failed. Error: failed to get get_attached_file';
            CompressX_Image_Meta_V2::update_image_failed($image_id,$error);
            return;
        }

        CompressX_Image_Meta_V2::update_image_progressing($image_id);

        //$image->resize();

        if($image->convert())
        {
            CompressX_Image_Meta_V2::update_image_meta_status($image_id, 'optimized');
        }
        else
        {
            CompressX_Image_Meta_V2::update_image_meta_status($image_id,'failed');
        }

        CompressX_Image_Meta_V2::delete_image_progressing($image_id);
    }

    public function delete_converted_image($image_id)
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
            $file=CompressX_Image_Method::get_output_path($file);
            $webp_file =$file.'.webp';
            $avif_file =$file.'.avif';
            if(file_exists($webp_file))
                @wp_delete_file($webp_file);

            if(file_exists($avif_file))
                @wp_delete_file($avif_file);

            if(file_exists($file))
                @wp_delete_file($file);

        }

        delete_post_meta($image_id,'compressx_image_meta_status');
        delete_post_meta($image_id,'compressx_image_meta_webp_converted');
        delete_post_meta($image_id,'compressx_image_meta_avif_converted');
        delete_post_meta($image_id,'compressx_image_meta_compressed');

        delete_post_meta($image_id,'compressx_image_meta_og_file_size');
        delete_post_meta($image_id,'compressx_image_meta_webp_converted_size');
        delete_post_meta($image_id,'compressx_image_meta_avif_converted_size');
        delete_post_meta($image_id,'compressx_image_meta_compressed_size');
        delete_post_meta($image_id,'compressx_image_meta');
        delete_post_meta($image_id,'compressx_image_progressing');
        CompressX_Image_Meta_V2::delete_image_meta($image_id);
    }

    public function init_option()
    {
        $general_options=CompressX_Options::get_general_settings();
        $quality_options=CompressX_Options::get_quality_option();
        return array_merge($general_options,$quality_options);

    }

    public function get_compressx_tmp_folder( $uploads ) {

        $path = WP_CONTENT_DIR . '/compressx';
        $url  = content_url( '/compressx' );

        // Ensure directory exists.
        if ( ! is_dir( $path ) ) {
            wp_mkdir_p( $path );
        }

        // Init WP_Filesystem.
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // If WP_Filesystem is not available, fallback to original uploads.
        if ( empty( $wp_filesystem ) ) {
            return $uploads;
        }

        // Create empty index.html (prevent directory listing).
        $index_file = $path . '/index.html';
        if ( ! $wp_filesystem->exists( $index_file ) ) {
            $wp_filesystem->put_contents( $index_file, '', FS_CHMOD_FILE );
        }

        // .htaccess content (no heredoc).
        $root_htaccess = "# CompressX - block public access by default\n\n"
            . "<IfModule mod_authz_core.c>\n"
            . "  Require all denied\n"
            . "</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n"
            . "  Deny from all\n"
            . "</IfModule>\n";

        $root_ht_file = $path . '/.htaccess';

        $current = '';
        if ( $wp_filesystem->exists( $root_ht_file ) ) {
            $current = $wp_filesystem->get_contents( $root_ht_file );
            if ( false === $current ) {
                $current = '';
            }
        }

        // Write .htaccess only if changed.
        if ( $current !== $root_htaccess ) {
            $wp_filesystem->put_contents( $root_ht_file, $root_htaccess, FS_CHMOD_FILE );
        }

        // Override uploads paths.
        $uploads['path']    = $path;
        $uploads['url']     = $url;
        $uploads['subdir']  = '';
        $uploads['basedir'] = $path;
        $uploads['baseurl'] = $url;

        return $uploads;
    }

}