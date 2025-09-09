<?php
/*
Plugin Name: HT Design Assets Server
Description: Serves HT Design assets to fix 403 errors on staging sites
Version: 1.0
Author: HoliThemes
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class HT_Design_Assets_Server {
    
    public function __construct() {
        add_action( 'init', array( $this, 'add_rewrite' ) );
        add_action( 'template_redirect', array( $this, 'serve_assets' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }
    
    public function add_rewrite() {
        add_rewrite_rule( '^ht-design-assets/([^/]+)$', 'index.php?htd_asset=$matches[1]', 'top' );
        add_rewrite_tag( '%htd_asset%', '([^&]+)' );
    }

    public function activate() {
        update_option( 'htd_use_server_mode', true );
        $this->add_rewrite();
        flush_rewrite_rules();
    }

    public function deactivate() {
        delete_option( 'htd_use_server_mode' );
        flush_rewrite_rules();
    }

    public function serve_assets() {
        $file = get_query_var( 'htd_asset' );
        if ( ! $file ) {
            return;
        }

        $ext = pathinfo( $file, PATHINFO_EXTENSION );
        if ( ! in_array( $ext, ['js', 'css'] ) ) {
            status_header(403);
            exit;
        }

        $upload_dir = wp_upload_dir();
        $paths = [
            WP_CONTENT_DIR . '/ht-design/' . $file,
            $upload_dir['basedir'] . '/ht-design/' . $file,
            $upload_dir['basedir'] . '/ht-design-assets/' . $file,
        ];

        $found_path = null;
        foreach ( $paths as $path ) {
            if ( file_exists( $path ) ) {
                $found_path = $path;
                break;
            }
        }

        if ( ! $found_path ) {
            status_header(404);
            exit;
        }

        status_header(200);
        header( 'Content-Type: ' . ( $ext === 'js' ? 'application/javascript; charset=UTF-8' : 'text/css; charset=UTF-8' ) );
        header( 'Cache-Control: public, max-age=2592000' );
        readfile( $found_path );
        exit;
    }
}

new HT_Design_Assets_Server();