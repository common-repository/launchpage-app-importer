<?php
/*
Plugin Name: LaunchPage.app Importer
Plugin URI:  https://binarycarpenter.com/launch-page/
Description: This plugin helps you import landing pages you created on LaunchPage.app
Version: 1.1
Author: launch-page-importer
Author URI: https://launchpage.app

License: GPL2
*/


include_once 'functions.php';

register_activation_hook(__FILE__, 'launch_page_importer_init');

function launch_page_importer_init()
{

    global $wpdb;

    $table_name = LAUNCH_PAGE_IMPORTER_TBL_CONTENT;
    $charset_collate = $wpdb->get_charset_collate();

    //remote id (post ID on launchpage.app
    $sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
  id int(11) AUTO_INCREMENT PRIMARY KEY,
  remote_post_id int(11) NOT NULL,
  local_post_id int(11) NOT NULL,
  content longtext NOT NULL,
  url varchar(200) NOT NULL
) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}