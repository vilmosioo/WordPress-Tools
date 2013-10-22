<?php
/**
* Common function.php functions to minimize the main functions.php
*
* Example usage to include this code (in your funtions.php):
* 
* class MyTheme extends Hyperion{
*		function __construct(){	
*			parent::__construct();
*	
*			/...your code.../
* 	}
*   
*   /...other functions.../
* }
* add_action( 'after_setup_theme', create_function( '', 'global $theme; $theme = new MyTheme();' ) );
*
*/

// Define theme-wide constants
define( 'THEME_PATH', get_bloginfo( 'stylesheet_directory' ) );
define( 'HOME_URL', home_url() );
if ( ! isset( $content_width ) ) $content_width = 1200;

/*
* Main theme class
* 
* Loads default settings for all themes 
*/
class Hyperion{
	
	function __construct() {
		// customise theme
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 150, 150, true );
		add_theme_support( 'menus' );
		add_editor_style('css/editor-style.css');
			
			// add actions
		add_action( 'wp_enqueue_scripts', array( &$this, 'add_scripts') );  
		add_action( 'manage_media_custom_column', array( &$this, 'media_custom_columns'), 0, 2);
		
		//add filters
		add_filter( 'manage_upload_columns', array( &$this, 'upload_columns'));
		add_filter( 'post_thumbnail_html', array( &$this, 'remove_thumbnail_dimensions' ), 10 );
		add_filter( 'image_send_to_editor', array( &$this, 'remove_thumbnail_dimensions' ), 10 );
		add_filter( 'the_content', array( &$this, 'remove_thumbnail_dimensions' ), 10 );	
		add_filter( 'the_content', array( &$this, 'filter_ptags_on_images' ));
		add_filter( 'admin_footer_text', array( &$this, 'remove_footer_admin' ));
	}
	
	function add_scripts(){
		wp_enqueue_script( 'jquery' );
		if(is_singular()){
			wp_enqueue_script( 'comment-reply' ); 
		}
	}

	// Customise the footer in admin area
	function remove_footer_admin () {
		echo get_avatar('cool.villi@gmail.com' , '40' );
		echo 'Theme designed and developed by <a href="http://vilmosioo.co.uk" target="_blank">Vilmos Ioo</a>';
	}
	
	// stop images getting wrapped up in p tags when they get dumped out with the_content() for easier theme styling
	function filter_ptags_on_images( $content ){
		return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
	}

	
	// remove attached image sizes 
	function remove_thumbnail_dimensions( $html ) {
		$html = preg_replace( '/(width|height)=\"\d*\"\s/', "", $html );
		return $html;
	}

	function upload_columns($columns) {
		unset($columns['parent']);
		$columns['better_parent'] = "Parent";
		return $columns;
	}

	// better media parent management
	function media_custom_columns($column_name, $id) {
		$post = get_post($id);
		if($column_name != 'better_parent')
			return;
		if ( $post->post_parent > 0 ) {
			if ( get_post($post->post_parent) ) {
				$title =_draft_or_post_title($post->post_parent);
			}
			?>
			<strong><a href="<?php echo get_edit_post_link( $post->post_parent ); ?>"><?php echo $title ?></a></strong>, <?php echo get_the_time(__('Y/m/d')); ?>
			<br />
			<a class="hide-if-no-js" onclick="findPosts.open('media[]','<?php echo $post->ID ?>');return false;" href="#the-list"><?php _e('Re-Attach'); ?></a></td>
			<?php
		} else {
			?>
			<?php _e('(Unattached)'); ?><br />
			<a class="hide-if-no-js" onclick="findPosts.open('media[]','<?php echo $post->ID ?>');return false;" href="#the-list"><?php _e('Attach'); ?></a>
			<?php
		}
	}
}
?>