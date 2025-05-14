<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_Stats_Manager
{
    const META_KEY = 'compressx_image_meta';
    const OPTION_KEY = 'compressx_global_stats';
    const PROGRESS_KEY = 'compressx_stats_progress';
    const TRANSIENT_KEY = 'compressx_set_global_stats';
    const BATCH_SIZE = 1000;
    const MAX_DURATION = 25; // seconds

    public static function init()
    {
        add_action('wp_ajax_compressx_start_stats', [__CLASS__, 'ajax_start']);
        add_action('wp_ajax_compressx_get_stats', [__CLASS__, 'ajax_get']);
        add_action('wp_ajax_compressx_continue_stats', [__CLASS__, 'ajax_continue']);
    }

    public static function ajax_start()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');
        $cached = get_transient(self::TRANSIENT_KEY);

        if ($cached)
        {
            $data=get_option(self::OPTION_KEY);
            wp_send_json_success([
                'message' => 'Cached data available',
                'status' => 'cached',
                'cached' => $data,
            ]);
        }

        delete_option(self::OPTION_KEY);
        delete_option(self::PROGRESS_KEY);
        delete_transient(self::TRANSIENT_KEY);

        update_option(self::PROGRESS_KEY, [
            'offset' => 0,
            'accumulated' => [],
            'state' => 'wait',
        ]);

        wp_send_json_success(['status' => 'started']);
    }

    public static function ajax_continue()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        $progress = get_option(self::PROGRESS_KEY);
        if (!$progress || $progress['state'] !== 'wait') {
            wp_send_json_error('Task not ready or already running.');
        }

        $progress['state'] = 'running';
        update_option(self::PROGRESS_KEY, $progress);

        self::process_batch(self::BATCH_SIZE, $progress['offset'], $progress['accumulated']);

        wp_send_json_success(['status' => 'processing']);
    }


    public static function ajax_get()
    {
        global $compressx;
        $compressx->ajax_check_security('compressx-can-convert');

        $progress = get_option(self::PROGRESS_KEY);
        if ($progress) {
            if ($progress['state'] === 'running') {
                wp_send_json_success(['status' => 'executing']);
            }
            if ($progress['state'] === 'wait') {
                wp_send_json_success(['status' => 'in_progress']);
            }
        }

        $final = get_option(self::OPTION_KEY);
        if ($final) wp_send_json_success(array_merge(['status' => 'done'], $final));

        wp_send_json_success(['status' => 'not_started']);
    }

    public static function process_batch($batch_size, $offset, $accumulated)
    {
        global $wpdb;

        $start_time = time();

        $data = wp_parse_args($accumulated, [
            'converted_webp' => 0,
            'converted_avif' => 0,
            'total_count' => 0,
            'original_total_webp' => 0,
            'original_total_avif' => 0,
            'webp_total' => 0,
            'avif_total' => 0,
        ]);

        while (true) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s LIMIT %d OFFSET %d",
                self::META_KEY, $batch_size, $offset
            ), ARRAY_A);

            $count_this_batch = count($results);
            if ($count_this_batch === 0) break;

            foreach ($results as $row) {
                $meta = maybe_unserialize($row['meta_value']);
                if (!is_array($meta)) continue;
                $original = isset($meta['og_file_size']) ? (int)$meta['og_file_size'] : 0;

                if(isset($meta['webp_converted']))
                {
                    if (!empty($meta['webp_converted']) && $meta['webp_converted'] == 1)
                    {
                        $webp = isset($meta['webp_converted_size']) ? (int)$meta['webp_converted_size'] : 0;
                        $data['converted_webp']++;
                        if ($original > 0 && $webp > 0 && $webp <= $original)
                        {
                            $data['original_total_webp'] += $original;
                            $data['webp_total'] += $webp;
                        }
                    }
                }
                else
                {
                    $post_id=$row['post_id'];
                    if(CompressX_Image_Meta::get_image_meta_webp_converted($post_id))
                    {
                        $webp = CompressX_Image_Meta::get_webp_converted_size($post_id);
                        $data['converted_webp']++;
                        if ($original > 0 && $webp > 0 && $webp <= $original)
                        {
                            $data['original_total_webp'] += $original;
                            $data['webp_total'] += $webp;
                        }
                    }

                    wp_cache_delete($post_id, 'post_meta' );
                }

                if(isset($meta['avif_converted']))
                {
                    if (!empty($meta['avif_converted']) && $meta['avif_converted'] == 1)
                    {
                        $avif = isset($meta['avif_converted_size']) ? (int)$meta['avif_converted_size'] : 0;
                        $data['converted_avif']++;
                        if ($original > 0 && $avif > 0 && $avif <= $original) {

                            $data['original_total_avif'] += $original;
                            $data['avif_total'] += $avif;
                        }
                    }
                }
                else
                {
                    $post_id=$row['post_id'];
                    if(CompressX_Image_Meta::get_image_meta_avif_converted($post_id))
                    {
                        $avif = CompressX_Image_Meta::get_avif_converted_size($post_id);
                        $data['converted_avif']++;
                        if ($original > 0 && $avif > 0 && $avif <= $original) {

                            $data['original_total_avif'] += $original;
                            $data['avif_total'] += $avif;
                        }
                    }

                    wp_cache_delete($post_id, 'post_meta' );
                }


                $data['total_count']++;
            }

            $offset += $batch_size;
            if ((time() - $start_time) >= self::MAX_DURATION) {
                update_option(self::PROGRESS_KEY, [
                    'offset' => $offset,
                    'accumulated' => $data,
                    'state' => 'wait',
                ]);
                return;
            }
        }

        delete_option(self::PROGRESS_KEY);

        $webp_saved = $data['original_total_webp'] - $data['webp_total'];
        $avif_saved = $data['original_total_avif'] - $data['avif_total'];

        $percent_webp = $data['original_total_webp'] > 0 ? ($webp_saved / $data['original_total_webp']) * 100 : 0;
        $percent_avif = $data['original_total_avif'] > 0 ? ($avif_saved / $data['original_total_avif']) * 100 : 0;

        $max_webp_count=self::get_max_webp_image_count();
        $max_avif_count=self::get_max_avif_image_count();
        $conversion_webp = $max_webp_count > 0 ? ($data['converted_webp'] / $max_webp_count) * 100 : 0;
        $conversion_avif = $max_avif_count > 0 ? ($data['converted_avif'] / $max_avif_count) * 100 : 0;

        $final = [
            'conversion_webp_percent' => round($conversion_webp, 2),
            'conversion_avif_percent' => round($conversion_avif, 2),
            'space_saved_webp_percent' => round($percent_webp, 2),
            'space_saved_avif_percent' => round($percent_avif, 2),
            'converted_webp' => $data['converted_webp'],
            'converted_avif' => $data['converted_avif'],
            'total_count' => $data['total_count'],
            'saved_webp_size' => $webp_saved,
            'saved_avif_size' => $avif_saved,
            'webp_total' => $data['webp_total'],
            'avif_total' => $data['avif_total'],
            'original_total_webp' => $data['original_total_webp'],
            'original_total_avif' => $data['original_total_avif'],
            'calculated_at' => time(),
        ];

        update_option(self::OPTION_KEY, $final);
        set_transient(self::TRANSIENT_KEY, $final, MINUTE_IN_SECONDS * 10);
    }

    public static function get_max_webp_image_count()
    {
        global $wpdb;

        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",);

        //$supported_mime_types=apply_filters('compressx_supported_mime_types',$supported_mime_types);

        $result=$wpdb->get_results( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s) ",$supported_mime_types),ARRAY_N);
        if($result && sizeof($result)>0)
        {
            return $result[0][0];
        }
        else
        {
            return 0;
        }
    }

    public static function get_max_avif_image_count()
    {
        global $wpdb;

        $supported_mime_types = array(
            "image/jpg",
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/avif");

        //$supported_mime_types=apply_filters('compressx_supported_mime_types',$supported_mime_types);

        $result=$wpdb->get_results($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type IN (%s,%s,%s,%s,%s) ",$supported_mime_types),ARRAY_N);
        if($result && sizeof($result)>0)
        {
            return $result[0][0];
        }
        else
        {
            return 0;
        }
    }
}