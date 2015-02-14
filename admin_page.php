<?php

namespace adi;

defined( 'ABSPATH' ) or die( '' );


add_action( 'load-post.php', 'adi\page_meta_boxes_setup' );
add_action( 'load-post-new.php', 'adi\page_meta_boxes_setup' );

function page_meta_boxes_setup( ) {
	add_action( 'add_meta_boxes', 'adi\add_page_meta_boxes' );
	add_action( 'save_post', 'adi\save_titlepage_meta', 10, 2 );
}

function add_page_meta_boxes( $type ) {
	if ( 'page' !== $type ) return;
	if ( isset( $_GET['post'] ) ) {
		$id = intval( $_GET['post'] );
		if ( START_PAGE_ID === $id ||
			EVENTS_PAGE_ID === $id ) {
			return;
		}
	}

	add_meta_box(
		'adi_for_pages',
		'Adiomatique',
		'adi\page_meta_box',
		'page',
		'normal',
		'high'
	);
}

function page_meta_box( $post ) {
	if ( 'Startseite' === $post->post_title ) return;
	if ( ACTIVITY_ARCHIVE_PAGE_ID == $post->post_parent ) {
		echo 'Dieses Projekt ist archiviert.';
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
				'child_of'                 => EVENTS_CAT_ID,
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

	<p><strong></strong></p>
	<form method="post">

	<?php if ( $adi_is_titlepage ) : ?>

		<input type="checkbox" id="adi_do_archivate" name="adi_do_archivate">
		<label for="adi_do_archivate"><strong>Archivieren.</strong> <em>Titelseite sowie die jeweiligen Veranstaltungen</em></label>

	<?php else : ?>

		<input type="checkbox" id="adi_is_titlepage" name="adi_is_titlepage">
		<label for="adi_is_titlepage">Dies ist <strong>Titelseite</strong> eines Projektes mit mehreren Veranstaltungen.</label>

	<?php endif ?>

		<?php wp_nonce_field( basename( __FILE__ ), 'adi_page_nonce' ); ?>
	</form>

<?php
}

function save_titlepage_meta( $page_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['adi_page_nonce'] ) || ! wp_verify_nonce( $_POST['adi_page_nonce'], basename( __FILE__ ) ) ) return;
	if ( ! current_user_can( 'edit_page', $post->ID ) ) return;
	if ( ACTIVITY_ARCHIVE_PAGE_ID == $post->post_parent ) return;

	$was_titlepage = get_post_meta( $page_id, 'adi_is_titlepage', true );
	$is_normal_page = empty( $was_titlepage );

	$new_title = sanitize_text_field( $post->post_title );

	if ( $is_normal_page ) {
		$is_titlepage = isset( $_POST['adi_is_titlepage'] ) ? $_POST['adi_is_titlepage'] : NULL;
		$keep_as_normal_page = empty( $is_titlepage );
		if ( $keep_as_normal_page ) return;
		// first run, create category, save metas
		$new_cat_id = wp_create_category( $new_title, EVENTS_CAT_ID );

		update_post_meta( $page_id, 'adi_titlepage_cat_id', $new_cat_id );
		update_post_meta( $page_id, 'adi_is_titlepage', $is_titlepage );
		update_post_meta( $page_id, 'adi_titlepage_title', $new_title );

		remove_action( 'save_post', 'adi_save_titlepage_meta' );
		wp_update_post( array( 'ID' => $page_id, 'post_parent' => ACTIVITY_PARENT_PAGE_ID ) );
		add_action( 'save_post', 'adi_save_titlepage_meta', 10, 2 );
	} else {
		$cat_id = get_post_meta( $page_id, 'adi_titlepage_cat_id', true );
		$do_archivate = isset( $_POST['adi_do_archivate'] ) ? $_POST['adi_do_archivate'] : NULL;
		if ( $do_archivate ) {
			wp_update_category( array( 'cat_ID' => $cat_id, 'category_parent' => EVENTS_ARCHIVE_CAT_ID ) );

			remove_action( 'save_post', 'adi_save_titlepage_meta' );
			wp_update_post( array( 'ID' => $post->ID, 'post_parent' => ACTIVITY_ARCHIVE_PAGE_ID ) );
			add_action( 'save_post', 'adi_save_titlepage_meta', 10, 2 );
		} else {
			// keep the title always up-to-date
			wp_update_category( array( 'cat_ID' => $cat_id, 'cat_name' => $new_title ) );
		}
	}

}
