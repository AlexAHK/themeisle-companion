<?php
namespace OrbitFox\Gutenberg_Blocks;

class Contact_Form_Block extends Base_Block {

	public function __construct() {
		parent::__construct();
	}

	function set_block_slug() {
		$this->block_slug = 'contact-form';
	}

	function set_attributes(){
		$this->attributes = array();
	}

	/**
	 * 
	 * @param $attributes
	 *
	 * @return mixed|string
	 */
	function render( $attributes ) {
		return 'tester';
	}
}