<?php
/**
 * Created by PhpStorm.
 * User: myn
 * Date: 9/27/18
 * Time: 11:11 AM
 */

include_once 'inc/actions.php';
include_once 'inc/config.php';
include_once 'inc/image_importer.php';
include_once 'vendor/autoload.php';

add_action('admin_menu', LAUNCH_PAGE_IMPORTER_PREFIX . '_main_add_menu');

function launch_page_importer__main_add_menu() { add_menu_page(LAUNCH_PAGE_IMPORTER_NAME, LAUNCH_PAGE_IMPORTER_NAME, 'edit_posts', LAUNCH_PAGE_IMPORTER_SLUG, 'launch_page_importer_main_ui'); }


function launch_page_importer_main_ui()
{
    include_once 'ui/main-ui.php';
}

add_action( 'admin_post_launch_page_importer_form_response', 'launch_page_importer_form_handler');




add_action('admin_enqueue_scripts', 'launch_page_load_backend_scripts', 1000);

function launch_page_load_backend_scripts()
{
    wp_register_style('launch-page-backend-bundle-style', plugins_url( 'bundle/css/backend.css', __FILE__ ));
    wp_enqueue_style('launch-page-backend-bundle-style');
}


//filter the content get from the remote url, import images...
function launch_page_replace_with_local_images($content)
{
    //start importing images to local
    $pattern = '~https?://[^/\s]+/\S+\.(jpg|png|gif|webp)~';

    preg_match_all($pattern,$content,$matches);

    $filtered_images = array_unique ($matches[0]);

    foreach ($filtered_images as $image_url)
    {

        $file_name = explode("/", $image_url)[1];

        if (!launch_page_importer_exist_locally($file_name))
        {
            $result = launch_page_importer_download_image($image_url);

            //replace the image url with
            if (is_array($result))
                $content = str_ireplace($image_url, $result['url'], $content);
        }

    }

    return $content;
}



function launch_page_importer_form_handler()
{
    set_time_limit(800000);
    if (!wp_verify_nonce($_POST['launch_page_importer_form_nonce'], 'launch_page_importer_form_nonce'))
    {
        return;
    }

    $remote_url = $_POST['url'];

    $remote_post_id = str_ireplace(".txt", "", explode("_______", $remote_url)[1]);


    $client = new \GuzzleHttp\Client();

    $res = $client->request('GET', $remote_url);

    $status_code =  $res->getStatusCode();
    if ($status_code !== 200)
    {
        echo 'error fetching url';
        die();
    } else
    {
        echo 'status ok';
    }

    $content =  $res->getBody()->getContents();




    $content = launch_page_replace_with_local_images($content);


    $type = $_POST['post-type'];
    $title = $_POST['title'];

    global $wpdb;

    $post_id = wp_insert_post(
        array(
            'post_content' => 'This content will not be displayed',
            'post_title' => $title,
            'post_type' => $type,
            'post_status' => 'publish',
            'meta_input' => array(
                'is_launch_page_app' => true
            )
        )
    );


    $wpdb->insert(
    LAUNCH_PAGE_IMPORTER_TBL_CONTENT,
        array(
            'remote_post_id' => $remote_post_id,
            'local_post_id' => $post_id,
            'content' => $content,
            'url' => $remote_url
        )
    );


    header("Location: " . get_permalink($post_id));

    die();

}


add_filter('template_include', 'launch_page_importer_custom_template', PHP_INT_MAX);

function launch_page_importer_custom_template($page_template)
{
    //if the page is created by wp lead plus x, it will have this meta key
    $isLPA = get_post_meta(get_the_ID(), 'is_launch_page_app');

    if ($isLPA != false)
    {
        return plugin_dir_path(__FILE__) .'inc/blank-template.php';
    }
//	die();
    return $page_template;

}


//add the option to update the content

add_filter('post_row_actions', 'launch_page_importer_update_content', 10, 2);
add_filter('page_row_actions', 'launch_page_importer_update_content', 10, 2);

function launch_page_importer_update_content($actions, $post)
{

    if (get_post_meta($post->ID, 'is_launch_page_app', true))
    {
        $url = add_query_arg(
            array(
                'post_id' => $post->ID,
                'action' => 'launchpage_importer_update_page_content',
            )
        );
        $actions['launchpage_importer_update_page_content'] = sprintf(
            '<a target="_blank" href="%1$s">%2$s</a>',
            esc_url($url),
            __( 'Update LaunchPage.app content', 'launchpage-importer' )
        );
    }


    return $actions;

}

add_action('admin_init', 'launchpage_importer_update_page_content');

function launchpage_importer_update_page_content()
{
    if (isset($_REQUEST['action']) && 'launchpage_importer_update_page_content' == $_REQUEST['action'])
    {
        $id = $_REQUEST['post_id'];
        global $wpdb;


        //get the url
        $url = $wpdb->get_var("select url from launch_page_importer_content where local_post_id = " . $id);

        $client = new \GuzzleHttp\Client();

        $res = $client->request('GET', $url);

        $status_code =  $res->getStatusCode();
        if ($status_code !== 200)
        {
            echo 'error fetching url';
            die();
        } else
        {
            echo 'status ok';
        }

        $content =  $res->getBody()->getContents();


        $wpdb->update(
            LAUNCH_PAGE_IMPORTER_TBL_CONTENT,
            array(
                'content' => $content
            ),
            array(
                'local_post_id' => $id
            )
        );

        header("Location: " . get_permalink($id));
        exit();
    }


    //redirect to

}