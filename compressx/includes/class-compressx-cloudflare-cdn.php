<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CompressX_CloudFlare_CDN
{
    private $options;

    public function __construct($options)
    {
        $this->options=$options;
    }

    public function purge_cache()
    {
        if(empty($this->options['zone_id']))
        {
            $ret['result']='failed';
            $ret['error']=__('Zone ID is required, please enter a valid Zone ID','compressx');
            return $ret;
        }

        if(empty($this->options['email']))
        {
            $ret['result']='failed';
            $ret['error']=__('Cloudflare email is required, please enter your Cloudflare email.','compressx');
            return $ret;
        }

        if(empty($this->options['api_key']))
        {
            $ret['result']='failed';
            $ret['error']=__('Global API key is required, please enter your Cloudflare global API key.','compressx');
            return $ret;
        }

        $zone_id=$this->options['zone_id'];
        $email = $this->options['email'];
        $api_key   = $this->options['api_key'];

        $headers = array(
            'Content-Type' => 'application/json'
        );

        $headers["X-Auth-Email"] = $email;
        $headers["X-Auth-Key"]   = $api_key;

        $args['method'] = 'POST';
        $args['body']   = wp_json_encode( array( 'purge_everything' => true ) );
        $args['headers'] = $headers;
        $response = wp_remote_post(
            esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$zone_id}/purge_cache" ),
            $args
        );

        if ( is_wp_error( $response ) )
        {
            $ret['result']='failed';
            $ret['error']=__('Connecting to Cloudflare failed:','compressx').$response->get_error_message();
            return $ret;
        }

        $response_body = wp_remote_retrieve_body($response);
        $json = json_decode( $response_body, true);

        if( $json['success'] == false )
        {

            $error = array();

            foreach($json['errors'] as $single_error)
            {
                $error[] = "{$single_error['message']} (err code: {$single_error['code']})";
            }

            $error = implode(' - ', $error);

            $ret['result']='failed';
            $ret['error']=__('Connecting to Cloudflare failed:','compressx').$error;
            return $ret;
        }

        $ret['result']='success';
        return $ret;
    }

}