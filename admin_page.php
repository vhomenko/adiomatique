<?php

defined( 'ABSPATH' ) or die( '' );



add_action( 'load-post.php', 'adi_page_meta_boxes_setup' );
add_action( 'load-post-new.php', 'adi_page_meta_boxes_setup' );

function adi_page_meta_boxes_setup() {

	add_action( 'add_meta_boxes', 'adi_add_page_meta_boxes' );
	add_action( 'save_post', 'adi_save_titlepage_meta', 10, 2 );
	
}



function adi_add_page_meta_boxes() {

	add_meta_box(
		'adi_for_pages',
		'Adiomatique',
		'adi_page_meta_box',
		'page',
		'normal',
		'high'
	);
	
}



function adi_page_meta_box( $post ) {
 
	if ( adi_page_is_in_archive( $post->ID ) ) {
		echo 'Diese Aktivität wurde archiviert.';
		return;
	}

	$adi_is_titlepage = get_post_meta( $post->ID, 'adi_is_titlepage', true );

	if ( ! empty( $adi_is_titlepage ) ) {
		// some integrity checks

		// partially undo sanitize_text_field for the sake of string comparison
		$post_title = html_entity_decode( $post->post_title );

		$cat_id = intval( get_post_meta( $post->ID, 'adi_titlepage_cat_id', true ) );
	
		$cat = get_category( $cat_id );

		if ( empty( $cat ) ) {
	
			echo '<p>Warnung! Die Kategorie (ID:' . $cat_id . ') dieser Aktivität existiert nicht mehr!</p>';
		
			$args = array(
				'type'                     => 'post',
				'child_of'                 => ADI_EVENTS_CAT_ID,
				'orderby'                  => 'none',
				'hide_empty'               => 0,
				'hierarchical'             => 0,
				'taxonomy'                 => 'category' );
		
			$categories = get_categories( $args );
		
			$found_the_cat = false;
		
			foreach ( $categories as $category ) {
				if ( $category->cat_name === $post_title ) {
					update_post_meta( $post->ID, 'adi_titlepage_cat_id', $category->cat_ID );
					echo '<p>Allerdings wurde eine Kategorie mit demselben Namen gefunden und gespeichert: <a href="' . esc_url( get_category_link( $category->cat_ID ) ) . '">' . $category->cat_name . '</a>!</p>';
					$found_the_cat = true;
					break;
				}
			}
		
			if ( ! $found_the_cat ) {
				echo '<p>Leider ist weder der Name noch ID-Nummer ist zu finden.</p>';
			}

			echo '<hr>';
		
		} else if ( $post_title !== $cat->cat_name ) {
			echo '<p>Warnung! Der Name dieser Titelseite stimmt nicht mehr mit dem Namen der jeweiligen Kategorie überein:<br>';
			var_dump( $post_title );
			var_dump( $cat->cat_name );
			echo '<hr>';
		}
	
	}
	
?>
	
	<p><strong>Ist dies die Titelseite einer Aktivität mit mehreren Veranstaltungen?</strong></p>
	<p>Lass dir eine Kategorie für die Veranstaltungsbeiträge erstellen. So werden die immer automatisch aktualisiert und dargestellet. Zusätzlich wird diese Seite automatisch der "AGs und Projekte" Seite untergeordnet.</p>
	<form method="post">
		<input type="checkbox" id="adi_is_titlepage" name="adi_is_titlepage" <?php checked( $adi_is_titlepage, 'on' ); ?>>
		<label for="adi_is_titlepage">Das klingt chillig, 'ne Kategorie dazu will ich!</label>
		
		<p>Wenn du das Kästchen abwählst, wird diese Seite der Aktivitäten-Archiv Seite untergeordnet und die jeweiligen Veranstaltungsbeiträge ins Veranstaltungsarchiv verschoben.</p>
		<p>Die Pflege des Navigationsmenüs (bzw. Inhalt der Aktivitäten-Archiv Seite) bleibt deine Aufgabe.</p>

<?php 
	wp_nonce_field( basename( __FILE__ ), 'adi_page_nonce' );
	echo '</form>';
	
}



function adi_save_titlepage_meta( $page_id, $post ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['adi_page_nonce'] ) || ! wp_verify_nonce( $_POST['adi_page_nonce'], basename( __FILE__ ) ) ) return;
	if ( ! current_user_can( 'edit_page', $post->ID ) ) return;
	if (  adi_page_is_in_archive( $post->ID ) ) return;

	$was_titlepage = get_post_meta( $page_id, 'adi_is_titlepage', true );
	$old_title = get_post_meta( $page_id, 'adi_titlepage_title', true );
	$old_cat_id = get_post_meta( $page_id, 'adi_titlepage_cat_id', true );

	$is_titlepage = $_POST['adi_is_titlepage'];
	$cur_title = sanitize_text_field( $post->post_title );

	if ( empty( $is_titlepage ) ) {
		if ( 'on' == $was_titlepage ) {
			// do archivation
			
			remove_action( 'save_post', 'adi_save_titlepage_meta' );
			wp_update_post( array( 'ID' => $post->ID, 'post_parent' => ADI_ACTIVITY_ARCHIVE_PAGE_ID	) );
			add_action( 'save_post', 'adi_save_titlepage_meta', 10, 2 );

			// it's events-category gets under parent category "Veranstaltungsarchiv"
			wp_update_category( array( 'cat_ID' => $old_cat_id, 'category_parent' => ADI_EVENTS_ARCHIVE_CAT_ID ) );
		}
		
		return;
		
	} else {
		update_post_meta( $page_id, 'adi_is_titlepage', $is_titlepage );
	}

	if ( empty( $old_title ) ) {
		// first run, create category, save metas
		$new_cat_id = wp_create_category( $cur_title, ADI_EVENTS_CAT_ID );
		
		update_post_meta( $page_id, 'adi_titlepage_cat_id', $new_cat_id );

		remove_action( 'save_post', 'adi_save_titlepage_meta' );
		wp_update_post( array( 'ID' => $page_id, 'post_parent' => ADI_ACTIVITY_PARENT_PAGE_ID ) );
		add_action( 'save_post', 'adi_save_titlepage_meta', 10, 2 );
				
	} else if ( $old_title != $cur_title ) {
		// user changed the title, we update cat's name
		wp_update_category( array( 'cat_ID' => $old_cat_id, 'cat_name' => $cur_title ) );
	}
	update_post_meta( $page_id, 'adi_titlepage_title', $cur_title );

}
