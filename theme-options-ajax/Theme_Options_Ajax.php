<?php
/*
* Theme options, the easy way.
* Options are handled using only Ajax.
* Hint: you can display all sorts of content for your theme options page. Previews, images, WP query results etc. 
*
*	Example usage: 
*		$theme_options = new Theme_Options_Ajax();
*		$theme_options::init(); 
*
* Required: Utils.php
*/
define('PLUGIN_URL', plugin_dir_url(__FILE__));
define('TOA_WP_VERSION', get_bloginfo( 'version' ));

// TODO Add Utils reference
// require_once PLUGIN_URL.'Utils.php';

class Theme_Options_Ajax{

	const AJAX_UPDATE = 'ajax_action';
	const PAGE_TITLE = 'Page title';
	const DATA = 'key';
	const ADMIN_SCRIPT_HANDLE = 'script-handle';

	// Generates an instance of the Settings class
	static public function init(){
		return new Theme_Options_Ajax();
	}

	private $fields = array();

	// Initializes the plugin by setting localization, filters, and administration functions.
	private function __construct() {
		if(!is_admin()) return;

		add_action('admin_menu', array(&$this, 'register_settings_page'));
		add_action( 'wp_ajax_'.Theme_Options_Ajax::AJAX_UPDATE, array(&$this, 'update') );

		array_push($this->fields, 'Option 1');
		array_push($this->fields, 'Option 2');
	} 

	// Function to update options
	public function update(){
		$fields = $_POST['fields'];
		$option = get_option(Theme_Options_Ajax::DATA);
		if(!is_array($option)){
			$option = array();
		}
		if(is_array($fields)) foreach ($fields as $key => $value) {
			if(array_key_exists('key', $value) && array_key_exists('value', $value))
			$option[$value['key']] = $value['value'];
		}
		update_option(Theme_Options_Ajax::DATA, $option);

		die(json_encode(array(
			"code" => 200
		)));
	}

	public function register_settings_page(){
		$page = add_options_page(
			Theme_Options_Ajax::PAGE_TITLE, 
			Theme_Options_Ajax::PAGE_TITLE, 
			'manage_options',
			Theme_Options_Ajax::DATA, 
			array(&$this, 'print_page')
		);
		add_action( "admin_print_scripts-$page", array(&$this, 'settings_styles_and_scripts'));
	}

	public function settings_styles_and_scripts(){
		wp_enqueue_script(Theme_Options_Ajax::ADMIN_SCRIPT_HANDLE, PLUGIN_URL. 'admin.js');
	}

	// Print the settngs page
	// You can customize this however you want!
	public function print_page(){
		echo "<div class='wrap'>";
		echo "<h2>".Theme_Options_Ajax::PAGE_TITLE."</h2>";
		echo "<p>Page description</p>";
		echo "<div id='toa-fields'>";

		$option = get_option(Theme_Options_Ajax::DATA);
		foreach ($this->fields as $key => $value) {
			echo "<h3>$value</h3>";
			$slug = WPSocialTumblelog_Utils::generate_slug($value);
			echo "<p><input type='text' id='$slug' name='$slug' value='".$option[$slug]."'></p>";
		}
		echo "<p><button class='button button-primary' id='toa-button'>Save</button><img style='display:none;' src='".(TOA_WP_VERSION >= 3.8 ? admin_url('images/spinner.gif') : admin_url('images/loading.gif'))."' id='toa-spinner'></p>";
		echo "</div>";
		echo "</div>";
	}

}