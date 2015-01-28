<?php


class CustomValuesStorage {

	var $id;

	function __construct ( $id ) {
		$this->id = $id;
	}

	public function update( $key, $value ) {
		update_post_meta( $this->id, $key, $value );
	}

	public function delete( $key ) {
		delete_post_meta( $this->id, $key );
	}

	public function getStr( $key ) {
		return sanitize_text_field( get_post_meta( $this->id, $key, true ) );
	}

	public function getInt( $key ) {
		return intval( get_post_meta( $this->id, $key, true ) );
	}

}

?>
