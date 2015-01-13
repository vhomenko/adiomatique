<?php

defined( 'ABSPATH' ) or die( '' );



add_action( 'load-post.php', 'adi_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'adi_post_meta_boxes_setup' );

function adi_post_meta_boxes_setup() {
	
	add_action( 'add_meta_boxes', 'adi_add_post_meta_boxes' );
	
	add_action( 'save_post', 'adi_save_meta', 10, 2 );

	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-css', plugins_url() . '/adiomatique/css/jquery-ui.css' );
	wp_enqueue_style( 'jquery-ui-timepicker-css', plugins_url() . '/adiomatique/css/jquery.ui.timepicker.css' );
	wp_enqueue_script( 'jquery-ui-timepicker', plugins_url() . '/adiomatique/js/jquery.ui.timepicker.js' );

}



function adi_add_post_meta_boxes() {

	add_meta_box(
		'adi_for_posts',
		'Adiomatique: Veranstaltungstermin',
		'adi_post_meta_box',
		'post',
		'normal', 
		'high'
	);

}



function adi_post_meta_box( $post ) { 
	
	if ( in_category( ADI_EVENTS_ARCHIVE_CAT_ID, $post->ID ) ) {
		echo 'Diese Terminankündigung ist archiviert.';
		return;
	}

	$ts = get_post_meta( $post->ID, 'adi_event_timestamp', true );
	
	if ( empty( $ts ) ) {
		$adi_event_date = date( 'j.m.y' );
		$adi_event_time = '';
	} else {
		$datetime = new DateTime( '@' . $ts );
		$adi_event_date = $datetime->format( 'j.m.y' );
		$adi_event_time = $datetime->format( 'G:i' );
	}
	
	$adi_event_period = get_post_meta( $post->ID, 'adi_event_periodicity', true );
	$adi_event_type = get_post_meta( $post->ID, 'adi_event_type', true );

	$adi_event_titlepage_id = intval( get_post_meta( $post->ID, 'adi_event_titlepage_id', true ) );
	
?>

<form method="post">
	<p>
		<input type="text" name="adi_event_date" id="adi_event_date" value="<?php echo $adi_event_date ?>" size="13" />
		<input type="text" name="adi_event_time" id="adi_event_time" value="<?php echo $adi_event_time ?>" size="6" />
		<br>
		Die Terminseingaben werden gespeichert, wenn es eine Uhrzeit eingetragen wird!
	</p>
	<hr>
	<p>
		<select id="adi_event_periodicity" name="adi_event_periodicity">
			<option value="0" <?php selected( $adi_event_period, 0 ); ?>>einmalig</option>
			<option value="1" <?php selected( $adi_event_period, 1 ); ?>>wöchentlich</option>
			<option value="2" <?php selected( $adi_event_period, 2 ); ?>>zweiwöchentlich</option>
			<option value="4" <?php selected( $adi_event_period, 4 ); ?>>monatlich</option>
		</select>

		<select id="adi_event_titlepage_id" name="adi_event_titlepage_id">
			<option value="eigenstaendig" <?php selected( $adi_event_titlepage_id, 0 ); ?>>eigenständig</option>
		<?php

			$args = array(
				'hierarchical' => 0,
				'parent' => ADI_ACTIVITY_PARENT_PAGE_ID,
				'post_type' => 'page',
				'post_status' => 'publish'
			); 

			$pages = get_pages( $args );

			foreach ( $pages as $page ) {
				$option  = '<option value="' . $page->ID . '" ';
				$option .= selected( $adi_event_titlepage_id,  $page->ID ) . '>';
				$option .= $page->post_title;
				$option .= '</option>';
				echo $option;
			}

		?>

		</select>
		<br>
		Die Kategorie wird beim Speichern automatisch aktualisiert.
	</p>
	<hr>
	<p>
		<select id="adi_event_type" name="adi_event_type">
			<option value="50" <?php if ( empty( $adi_event_type ) ) $adi_event_type = 50; selected( $adi_event_type, 50 ); ?> >normal</option>
			<option value="100" <?php selected( $adi_event_type, 100 ); ?> >wichtig</option>
			<option value="75" <?php selected( $adi_event_type, 75 ); ?> >geändert</option>
			<option value="25" <?php selected( $adi_event_type, 25 ); ?> >extern</option>
			<option value="1" <?php selected( $adi_event_type, 1 ); ?> >inaktiv</option>
		</select>

		<a class="delete" href="javascript:;" id="adi_event_eraser" onclick="window.ADI.resetEvent()">zurücksetzen</a>

		<?php wp_nonce_field( basename( __FILE__ ), 'adi_post_nonce' ); ?>

	</p>
</form>

<script>
	jQuery(document).ready(function(){
		window.ADI = window.ADI || {};
		
		var dateFormat = "d.mm.y";
		var monthNames = ['Januar','Februar','März','April','Mai','Juni',
		'Juli','August','September','Oktober','November','Dezember'];
		var monthNamesShort = ['Jan','Feb','Mär','Apr','Mai','Jun',
		'Jul','Aug','Sep','Okt','Nov','Dez'];
		var dayNames = ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'];
		var dayNamesShort = ['So','Mo','Di','Mi','Do','Fr','Sa'];
		jQuery( "#adi_event_date" ).datepicker({
			closeText: 'Schließen',
			monthNames: monthNames,
			monthNamesShort: monthNamesShort,
			dayNames: dayNames,
			dayNamesShort: dayNamesShort,
			dayNamesMin: dayNamesShort,
			weekHeader: 'KW',
			firstDay: 1,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: '',
			dateFormat : dateFormat});
		if (jQuery( "#adi_event_date" ).val() === "") {
			var cur = jQuery.datepicker.formatDate(dateFormat, new Date(), {
				monthNames: monthNames,
				monthNamesShort: monthNamesShort,
				dayNames: dayNames,
				dayNamesShort: dayNamesShort
			});
			jQuery( "#adi_event_date" ).val( cur );
		}
		window.ADI.resetEvent = function () {
			jQuery( "#adi_event_time" ).val("");

			var cur = jQuery.datepicker.formatDate(dateFormat, new Date(), {
				monthNames: monthNames,
				monthNamesShort: monthNamesShort,
				dayNames: dayNames,
				dayNamesShort: dayNamesShort
			});
			jQuery( "#adi_event_date" ).val( cur );	

			document.getElementById( 'adi_event_periodicity' ).value = '0';
			document.getElementById( 'adi_event_titlepage_id' ).value = '<?php echo "eigenstaendig"; ?>';
			document.getElementById( 'adi_event_type' ).value = '50';
		}
		jQuery('#adi_event_time').timepicker({
			defaultTime: '19:00',
			showLeadingZero: false, 
			showPeriodLabels: false,
			hourText: 'Stunden',
			minuteText: 'Min',
			showNowButton: false,
			closeButtonText: 'Schließen',
			showCloseButton: true,
			minutes: { interval: 15 }
		});
	});
</script>

<?php

}



function adi_save_meta( $post_id, $post ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( ! isset( $_POST['adi_post_nonce'] ) || ! wp_verify_nonce( $_POST['adi_post_nonce'], basename( __FILE__ ) ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$time = $_POST['adi_event_time'];
	
	if ( empty( $time ) ) {
		delete_post_meta( $post_id, 'adi_event_periodicity' );
		delete_post_meta( $post_id, 'adi_event_type' );
		delete_post_meta( $post_id, 'adi_event_timestamp' );
		delete_post_meta( $post_id, 'adi_event_titlepage_id' );

		return;
	}

	$date = $_POST['adi_event_date'];

	$a = explode( '.', $date );
	$day = $a[0];
	$month = $a[1];
	$year = $a[2];

	$b = explode( ':', $time );
	$hour = $b[0];
	$min = $b[1];

	$stamp = mktime( $hour, $min, 0, $month, $day, $year );

	update_post_meta( $post_id, 'adi_event_timestamp', $stamp );
	
	$periodicity = $_POST['adi_event_periodicity'];
	$type = $_POST['adi_event_type'];

	update_post_meta( $post_id, 'adi_event_periodicity', $periodicity );
	update_post_meta( $post_id, 'adi_event_type', $type );

	$t_id = $_POST['adi_event_titlepage_id'];
	
	if ( $t_id !== 'eigenstaendig' ) {
		$titlepage_id = intval( $t_id );

		update_post_meta( $post_id, 'adi_event_titlepage_id', $titlepage_id );
	
		$cat_id = intval( get_post_meta( $titlepage_id, 'adi_titlepage_cat_id', true ) );

		// update the event's category		
		wp_set_post_terms( $post_id, array( $cat_id ), 'category' );
	} else {
		wp_set_post_terms( $post_id, array( ADI_INDEPENDENT_EVENTS_CAT_ID ), 'category' );	
		delete_post_meta( $post_id, 'adi_event_titlepage_id' );
	}

}

