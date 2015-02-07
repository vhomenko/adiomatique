<?php

namespace adi;

defined( 'ABSPATH' ) or die( '' );



add_action( 'load-post.php', 'adi\post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'adi\post_meta_boxes_setup' );

function post_meta_boxes_setup() {
	add_action( 'add_meta_boxes', 'adi\add_post_meta_boxes' );
	add_action( 'save_post', 'adi\save_meta' );
}

function add_post_meta_boxes() {
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-css', plugins_url() . '/adiomatique/css/jquery-ui.css' );
	wp_enqueue_style( 'jquery-ui-timepicker-css', plugins_url() . '/adiomatique/css/jquery.ui.timepicker.css' );
	wp_enqueue_script( 'jquery-ui-timepicker', plugins_url() . '/adiomatique/js/jquery.ui.timepicker.js' );
	wp_enqueue_script( 'adi-admin-post', plugins_url() . '/adiomatique/js/admin_post.js' );
	add_meta_box(
		'adi_for_posts',
		'Adiomatique: Veranstaltungstermin',
		'adi\post_meta_box',
		'post',
		'normal', 
		'high' );
}

function post_meta_box( $post ) { 
	if ( in_category( EVENTS_ARCHIVE_CAT_ID, $post->ID ) ) {
		echo 'Diese Terminankündigung ist archiviert.';
		return;
	}

	$e = new Event( $post->ID );

	if ( $e->isEmpty() ) {
		$date = date( 'j.m.y' );
		$time = '';
	} else {
		$date = $e->getFullDate();
		$time = $e->getTime();
	}

	$periodicity = $e->getPeriodicity();
	$week_to_skip = $e->getWeekToSkip();
	$location = $e->getLocation();
	$titlepage_id = $e->getTitlepageID();

#TODO: warn if no event data, but in event cat
# + don't show controls for posts in non default + non-event cat

?>

<form method="post">
	<p>
		<input style="text-align:center;" type="text" name="adi_event_date" id="adi_event_date" value="<?php echo $date ?>" size="8" />
		<input style="text-align:center;" type="text" name="adi_event_time" id="adi_event_time" value="<?php echo $time ?>" size="5" />
		<select style="vertical-align:top;" id="adi_event_periodicity" name="adi_event_periodicity" onchange="window.ADI.toggleWeekToSkipBox()">
			<option value="0" <?php selected( $periodicity, 0 ); ?>>einmalig</option>
			<option value="1" <?php selected( $periodicity, 1 ); ?>>wöchentlich</option>
			<option value="2" <?php selected( $periodicity, 2 ); ?>>zweiwöchentlich</option>
			<option value="4" <?php selected( $periodicity, 4 ); ?>>monatlich</option>
		</select>
		<br>
		<span id="adi_week_to_skip_box">
		<select id="adi_event_week_to_skip" name="adi_event_week_to_skip">
			<option value="0" <?php selected( $week_to_skip, 0 ); ?>>Keine</option>
			<option value="1" <?php selected( $week_to_skip, 1 ); ?>>Erste</option>
			<option value="2" <?php selected( $week_to_skip, 2 ); ?>>Zweite</option>
			<option value="3" <?php selected( $week_to_skip, 3 ); ?>>Dritte</option>
			<option value="4" <?php selected( $week_to_skip, 4 ); ?>>Vierte</option>
		</select>
		Woche des Monats überspringen.<br></span>
		<em>Die Termineingaben werden gespeichert, wenn eine <strong>Uhrzeit</strong> eingetragen wird!</em>
	</p>
	<hr>
	<p>
		<select id="adi_event_titlepage_id" name="adi_event_titlepage_id">
			<option value="0" <?php selected( $titlepage_id, 0 ); ?>>eigenständig</option>
		<?php

			$args = array(
				'hierarchical' => 0,
				'parent' => ACTIVITY_PARENT_PAGE_ID,
				'post_type' => 'page',
				'post_status' => 'publish' ); 

			$pages = get_pages( $args );

			foreach ( $pages as $page ) {
				$option  = '<option value="' . $page->ID . '" ';
				$option .= selected( $titlepage_id,  $page->ID ) . '>';
				$option .= $page->post_title;
				$option .= '</option>';
				echo $option;
			}

		?>
		</select>
	</p>
	<hr>
	<p>
		Externer Ort: <input style="min-width: 80%;" type="text" name="adi_event_location" id="adi_event_location" value="<?php echo $location; ?>" />
	</p>
	<hr>
	<p>
		<a class="delete" href="javascript:;" id="adi_event_eraser" onclick="window.ADI.resetEventData()">Alle Termineingaben zurücksetzen</a>
		<?php wp_nonce_field( basename( __FILE__ ), 'adi_post_nonce' ); ?>
	</p>
</form>

<?php

}

function save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( ! isset( $_POST['adi_post_nonce'] ) || ! wp_verify_nonce( $_POST['adi_post_nonce'], basename( __FILE__ ) ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$e = new Event( $post_id, true );
	$e->setFromPost(
		sanitize_text_field( $_POST['adi_event_time'] ),
		sanitize_text_field( $_POST['adi_event_date'] ),
					 intval( $_POST['adi_event_periodicity'] ),
					 intval( $_POST['adi_event_week_to_skip'] ),
		sanitize_text_field( $_POST['adi_event_location'] ),
					 intval( $_POST['adi_event_titlepage_id'] )
	);
}
