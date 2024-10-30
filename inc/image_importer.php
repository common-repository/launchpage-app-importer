<?php
/**
 * Created by PhpStorm.
 * User: MYN
 * Date: 12/2/2018
 * Time: 1:44 PM
 */

function launch_page_importer_download_image($url)
{
    $temp_file = download_url($url, 15000);
    if ( !is_wp_error( $temp_file ) ) {

        // Array based on $_FILE as seen in PHP file uploads
        $file = array(
            'name'     => basename($url), // ex: wp-header-logo.png
            'type'     => wp_check_filetype(basename($temp_file), null),
            'tmp_name' => $temp_file,
            'error'    => 0,
            'size'     => filesize($temp_file),
        );

        $overrides = array(
            // Tells WordPress to not look for the POST form
            // fields that would normally be present as
            // we downloaded the file from a remote server, so there
            // will be no form fields
            // Default is true
            'test_form' => false,
            'unique_filename_callback' => 'launch_page_importer_custom_file_name',

            // Setting this to false lets WordPress allow empty files, not recommended
            // Default is true
            'test_size' => true,
        );

        // Move the temporary file into the uploads directory
        $results = wp_handle_sideload( $file, $overrides );

        if ( !empty( $results['error'] ) ) {
            // Insert any error handling here
            return false;
        } else {

            /*
            $filename  = $results['file']; // Full path to the file
            $local_url = $results['url'];  // URL to the file in the uploads dir
            $type      = $results['type']; // MIME type of the file

            */

            return $results;

            // Perform any actions here based in the above results
        }

    }
}

function launch_page_importer_custom_file_name()
{

}

function launch_page_importer_exist_locally($file_name)
{
    return (file_exists(wp_upload_dir()['path']. $file_name));
}