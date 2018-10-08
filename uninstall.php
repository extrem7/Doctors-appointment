<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function doctors_delete_plugin() {
	$posts = get_posts(
		array(
			'numberposts' => - 1,
			'post_type'   => 'doctors',
			'post_status' => 'any',
		)
	);

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}

doctors_delete_plugin();
