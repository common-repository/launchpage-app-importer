<?php
	
	global $wpdb;

	$content = $wpdb->get_var("select content from launch_page_importer_content where local_post_id = " . get_the_ID());
	
	
	echo ($content);
