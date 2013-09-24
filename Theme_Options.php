<?php
/*
* Theme options, the easy way
* Each tab is saved in a separate WordPress option, as an array of key-value pairs
* Only support text inputs at the moment but can easily be extended.
*
* Hint: you can display all sorts of content for your theme options page. Previews, images, WP query results etc. 
*
*	Example usage: 
*		$theme_options = new Theme_Options();
*		$theme_options->addTab(array(
*			'name' => 'General',
*			'options' => array(
*				array('name' => 'Option name', 'type' => Theme_Options::TEXT)
*			)
*		));
*		$theme_options->addTab(...)
*		...
*		$theme_options->render(); 
*
* Required: Utils.php
*/

require_once 'Utils.php';

class Theme_Options{

	protected $tabs;
	protected $current;

	const TEXT = 0;
	const PORTFOLIO_SELECT = 1;

	public function __construct(){
		if(!is_admin()) return;
		$this->current = ( isset( $_GET['tab'] ) ? $_GET['tab'] : '' ); 
	}

	// Add a field to a tab
	// Parameters : slug, name, description, tab
	protected function addField($args = array()){
		if(!is_array($args['option']) && is_string($args['option'])){
			$args['option'] = array('name' => $args['option']);
		}

		$args['option'] = array_merge ( array(
			"name" => 'Option name',
			"desc" => "",
			"type" => self::TEXT
		), $args['option'] );

		$this->tabs[$args['tab']]['options'][Utils::generate_slug($args['option']['name'])] = array(
			'name' => $args['option']['name'],
			'desc' => $args['option']['desc'],
			'type' => $args['option']['type']
		);
	}

	// Add a new tab. 
	// Parameters : tab name, description, option array
	public function addTab($args = array()){
		$args = array_merge ( array(
			"name" => 'General',
			"desc" => "",
			"options" => array()
		), $args );

		$slug = Utils::generate_slug($args['name']);
		$this->current = empty($this->current) ? $slug : $this->current;

		$this->tabs[$slug] = array(
			'name' => $args['name'],
			'desc' => $args['desc']
		);

		foreach ($args['options'] as $option) {
			$this->addField(array('tab' => $slug, 'option' => $option));        	
		} 
	}

	// display the tabs
	public function render(){
		// initialise options
		foreach($this->tabs as $slug => $tab){
			if(!get_option('vilmosioo_options_'.$slug)){
				$defaults = array();
				
				foreach( $tab['options'] as $option){
					$name = Utils::generate_slug($option['name']);
					$title = $option['name'];
					$desc = $option['desc'];
				
					$defaults[$name] = $title;
				}
			
				update_option( 'vilmosioo_options_'.$slug, $defaults );
			}	
		}

		add_action('admin_menu', array(&$this, 'init'));
		add_action('admin_init', array(&$this, 'register_mysettings') );
	}

	/*
	* Init function
	* 
	* Initializes the theme's options. Called on admin menu action.
	*/
	public function init(){
		add_theme_page('Theme Options', 'Theme Options', 'administrator', 'vilmosioo', array(&$this, 'settings_page_setup'));
	}

	/*
	* Settings page set up
	*
	* Handles the display of the Theme Options page (under Appearance)
	*/
	public function settings_page_setup() {
		echo '<div class="wrap">';
		$this->page_tabs() ;
		if ( isset( $_GET['settings-updated'] ) ) {
			echo "<div class='updated'><p>Theme settings updated successfully.</p></div>";
		} 
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'vilmosioo_options_'.$this->current ); ?>
			<?php do_settings_sections( 'vilmosioo' ); ?>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		</div>
		<?php 
	} 

	/*
	* Page tabs
	*
	* Prints out the naviagtion for page tabs
	*/
	protected function page_tabs(){		
		
		$links = array();

		foreach( $this->tabs as $slug => $tab ){
			$active_class = $slug == $this->current ? "nav-tab-active" : "";
			$links[] = "<a class='nav-tab $active_class' href='?page=vilmosioo&tab=$slug'>$tab[name]</a>";
		}

		echo '<div id="icon-themes" class="icon32"><br /></div>'.
			'<h2 class="nav-tab-wrapper">';
		
		foreach ( $links as $link ){
			echo $link;
		}

		echo '</h2>';
	}

	/*
	* Register settings
	* 
	* Register all settings and setting sections
	*/
	public function register_mysettings() {		
		foreach($this->tabs as $slug=>$tab){
			register_setting( 'vilmosioo_options_'.$slug, 'vilmosioo_options_'.$slug );
			if($slug != $this->current) continue;
			add_settings_section( 'options_section_'.$slug, '', array(&$this, 'section_handler'), 'vilmosioo' ); 
			foreach($tab['options'] as $key => $option){
				add_settings_field( $key, $option['name'], array(&$this, 'input_handler'), 'vilmosioo', 'options_section_'.$slug, array("tab" => $slug, 'option' => array_merge(array('slug' => $key), $option)));
			}
		}
	}

	public function section_handler($args){
		$id = substr($args['id'], 16); // 16 is the length of the section prefix: vilmosioo_options_
		echo "<h2 class='section'>".$this->tabs[$id]['title']."</h2>"; 
		echo "<p>".$this->tabs[$id]['desc']."</p>"; 
	}

	public function input_handler($args){
		$option = $args['option'];
		$id = $option['slug'];
		$name = 'vilmosioo_options_'.$args['tab']."[$id]";
		$values = get_option("vilmosioo_options_".$args['tab']);
		$value = $values[$id];
		
		switch ($option['type']) {
			case self::PORTFOLIO_SELECT:
				echo "<select id='$id' name='$name".'[]'."' multiple=\"multiple\">";
				$id = array();
				$args = array( 'post_type' => 'portfolio-item', 'posts_per_page' => -1 );
				$the_query = new WP_Query( $args );
				
				while ( $the_query->have_posts() ) : $the_query->the_post(); 
			?>
					<option value="<?php the_ID(); ?>" <?php if( is_array($value) && in_array(get_the_ID(), $value) ){ echo "selected='selected'"; array_push($id, get_the_ID()); } ?> ><?php the_title(); ?></option>
			<?php 
				endwhile; 
			
				echo "</select>";
				wp_reset_postdata(); 
			?>
				<h2>Preview slider images</h2>
			<?php
				$args = array( 'post_type' => 'portfolio-item', 'posts_per_page' => -1 , 'post__in' => $id);
				$the_query = new WP_Query( $args );
				while ( $the_query->have_posts() ) : $the_query->the_post();
					echo "<div class='img'>";
					the_title('<h3>','</h3>');
					the_post_thumbnail( 'nivo' ); 
					echo "</div>";
				endwhile; 
				wp_reset_postdata(); 
			break;
			default:
				echo "<input type='text' id='$id' name='$name' value='$value'>"; 
			break;
		}

		if ( $option['desc'] != '' )
			echo '<br /><span class="description">' . $option['desc'] . '</span>';
	}
}
?>