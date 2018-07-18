<?php
/**
 * Gutenberg Blocks modules Orbit Fox Module.
 *
 * @link       https://themeisle.com
 * @since      2.2.5
 */

/**
 * Class Gutenberg_Blocks_OBFX_Module
 */
class Gutenberg_Blocks_OBFX_Module extends Orbit_Fox_Module_Abstract {

	protected $blocks_classes = array();

	/**
	 * Gutenberg_Blocks_OBFX_Module constructor.
	 *
	 * @since   2.2.5
	 * @access  public
	 */
	public function __construct() {
		parent::__construct();
		$this->name        = __( 'Gutenberg Blocks', 'themeisle-companion' );
		$this->description = __( 'A set of awesome Gutenberg Blocks!', 'themeisle-companion' );
		$this->active_default = false;
	}

	/**
	 * Determine if module should be loaded.
	 *
	 * @since   2.2.5
	 * @access  public
	 * @return bool
	 */
	public function enable_module() {
		require_once( ABSPATH . 'wp-admin' . '/includes/plugin.php' );
		return is_plugin_active( 'gutenberg/gutenberg.php' ) && function_exists( 'register_block_type');
	}

	/**
	 * The loading logic for the module.
	 *
	 * @since   2.2.5
	 * @access  public
	 */
	public function load() {}

	/**
	 * Method to define hooks needed.
	 *
	 * @since   2.2.5
	 * @access  public
	 */
	public function hooks() {
		$this->loader->add_action( 'init', $this, 'load_js_blocks' );
		$this->loader->add_action( 'init', $this, 'autoload_block_classes', 11 );
		$this->loader->add_action( 'wp', $this, 'load_server_side_blocks', 11 );

		add_action( 'init', array( $this, 'register_post_types' ) );

		//add_action( 'enqueue_block_editor_assets', 'gutenberg_examples_02_enqueue_block_editor_assets' );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
	}

	/**
	 * Method that returns an array of scripts and styles to be loaded
	 * for the front end part.
	 *
	 * @since   2.2.5
	 * @access  public
	 * @return array
	 */
	public function public_enqueue() {
		return array();
	}

	/**
	 * Method that returns an array of scripts and styles to be loaded
	 * for the admin part.
	 *
	 * @since   2.2.5
	 * @access  public
	 * @return array
	 */
	public function admin_enqueue() {
		return array();
	}

	/**
	 * Method to define the options fields for the module
	 *
	 * @since   2.2.5
	 * @access  public
	 * @return array
	 */
	public function options() {
		return array();
	}

	/**
	 * Load Gutenberg blocks
	 *
	 * @since   2.2.5
	 * @access  public
	 */
	public function load_js_blocks(){
		if ( ! is_admin() ) {
			return;
		}
		wp_enqueue_script('lodash');
		// @TODO for the moment load one js file with all the blocks. Maybe in future we'll group and enable them selectively
		wp_enqueue_script(
			'obfx-gutenberg-blocks',
			plugins_url( '/build/block.js', __FILE__ ),
			array( 'wp-api' ),
			filemtime( plugin_dir_path( __FILE__ ) . '/build/block.js' ),
			true
		);

		wp_enqueue_style(
			'obfx-gutenberg-blocks-editor',
			plugins_url( 'build/edit-blocks.css', __FILE__ ),
			array( 'wp-edit-blocks' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'build/edit-blocks.css' )
		);
	}

	public function load_server_side_blocks() {

		foreach ( $this->blocks_classes as $classname ) {

			if ( ! class_exists ( $classname ) ) {
				continue;
			}

			$block = new $classname();

			if ( method_exists( $block, 'register_block' ) ) {
				$block->register_block();
			}
		}
	}

	function autoload_block_classes(){
		// load the base class
		require_once plugin_dir_path( __FILE__ ) . 'class-gutenberg-block.php';
		$ss_blocks = glob( __DIR__ . '/blocks/*/*.php');

		foreach ( $ss_blocks as $block ) {
			require_once $block;

			// remove the class prefix and the extension
			$classname = str_replace( array( 'class-' , '.php' ), '', basename( $block ) );
			// get an array of words from class names and we'll make them capitalized.
			$classname = explode( '-', $classname );
			$classname = array_map( 'ucfirst', $classname );
			// rebuild the classname string as capitalized and separated by underscores.
			$classname = 'OrbitFox\Gutenberg_Blocks\\' . implode( '_', $classname );

			if ( ! class_exists ( $classname ) ) {
				continue;
			}

			if ( strpos( $block, '-block.php' ) ) {
				// we need to init these blocks on a hook later than "init". See `load_server_side_blocks`
				$this->blocks_classes[] = $classname;
				continue;
			}

			$block = new $classname();

			if ( method_exists( $block, 'instance' ) ) {
				$block->instance();
			}
		}
	}

	function enqueue_block_assets() {
		wp_enqueue_style(
			'obfx-block_styles',
			plugins_url( 'build/style.css', __FILE__ ),
			array( 'wp-blocks' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'build/style.css' )
		);

		// this should only be quequed when a chart block is present.
		wp_enqueue_script(
			'obfx-charts',
			plugins_url( 'blocks/chart/chart.js', __FILE__ ),
			array( 'jquery' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'blocks/chart/chart.js' )
		);

		if ( is_admin() ) {
			return;
		}

		// next scripts ar for front-end only

		// @TODO content forms are not ready yet.
//		wp_enqueue_style(
//			'obfx-contact_form_styles',
//			plugins_url( 'build/contact-form.css', __FILE__ ),
//			array(),
//			filemtime( plugin_dir_path( __FILE__ ) . 'build/contact-form.css' )
//		);
//
//		wp_enqueue_script(
//			'obfx-contact_form_script',
//			plugins_url( 'build/contact-form.js', __FILE__ ),
//			array( 'jquery' ),
//			filemtime( plugin_dir_path( __FILE__ ) . 'build/contact-form.js' )
//		);
	}


	function register_post_types() {
		register_post_type(
			'obfx_contact_form',
			array(
				'description' => 'test',
				'public' => true,
				'publicly_queryable' => true,
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true,
				'exclude_from_search' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'can_export' => true,
				'delete_with_user' => false,
				'hierarchical' => false,
				'has_archive' => false,
				'query_var' => 'obfx_contact_form',
				'show_in_rest' => true,
				'rest_base' => 'obfx_contact_form',
				'rest_controller_class' => 'WP_REST_Posts_Controller'
			)
		);
	}
}