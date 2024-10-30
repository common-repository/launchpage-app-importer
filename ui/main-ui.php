<h1>Start importing your content</h1>
<p>When you click on Start importing button, a new page/post will be created with the content of the landing page you created on <a href="https://launchpage.app">Launchpage.app</a></p>
<p>To update your post/page with new content, please go to Post->All posts or Pages->All pages and click on the link "Upload launchpage.app content.</p>

<h2>Enter the details below to get started</h2>
<form id="launch-page-importer-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">

    <label for="title">Title of your post/page</label>
    <input type="text" id="title" name="title" placeholder="your post/page title">


    <label for="post-type">Post or Page</label>
    <select name="post-type" id="post-type">
        <option value="page">Page</option>
        <option value="post">Post</option>
    </select>

    <input type="hidden" name="action" value="launch_page_importer_form_response">
    <input type="hidden" name="launch_page_importer_form_nonce" value="<?php echo wp_create_nonce('launch_page_importer_form_nonce') ?>" />

    <label for="content-url">Content URL</label>
    <input type="text" name="url" id="content-url">

    <input type="submit" value="Start importing" name="submit" class="button-primary">


</form>



