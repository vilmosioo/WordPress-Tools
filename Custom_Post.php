<?php
/*
* Custom Post
* 
* Creates a custom post for a WordPress theme. You can overwrite any default arguments that you wish.
*
* Example usage: Custom_Post::create(array('name' => 'Portfolio'))
* 
* Required: Utils.php
*/

require_once 'Utils.php';

class Custom_Post{
	
	static public function create($args){
		return new Custom_Post($args);
	}

	protected $name, $supports, $slug;

	protected function __construct($args = array('name' => 'Portfolio')) {
		$args['labels'] = array_merge(
			array(
				'name' => $args['name'],
				'singular_name' => $args['name'],
				'add_new' => 'Add New',
				'add_new_item' => 'Add New '. $args['name'],
				'edit_item' => 'Edit '. $args['name'],
				'new_item' => 'New '. $args['name'],
				'all_items' => 'All '. Utils::pluralize($args['name']),
				'view_item' => 'View '. $args['name'],
				'search_items' => 'Search '. $args['name'],
				'not_found' =>  'No '. Utils::pluralize($args['name']).' found',
				'not_found_in_trash' => 'No '. Utils::pluralize($args['name']).' found in Trash', 
				'parent_item_colon' => '',
				'menu_name' => Utils::pluralize($args['name'])
			),
			isset($args['labels']) ? $args['labels'] : array()
		);
		$args['supports'] = array_merge(
			array('title', 'editor', 'author', 'thumbnail', 'custom-fields'), 
			isset($args['supports']) ? $args['supports'] : array()
		);

		$args = array_merge( array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => array( 'slug' => Utils::generate_slug($args['name']) ),
			'capability_type' => 'post',
			'has_archive' => Utils::generate_slug($args['name']), 
			'hierarchical' => false,
			'menu_position' => null
		), $args ); 

		$this->name = $args['name'];
		$this->supports = $args['supports'];
		$this->slug = Utils::generate_slug($args['name']);
		
		register_post_type( $this->slug, $args );
		
		add_action( 'right_now_content_table_end' , array(&$this, 'add_to_dashboard') );

	}

	public function add_to_dashboard(){
		$post_type = get_post_type_object($this->slug);
		$num_posts = wp_count_posts( $post_type->name );
		$num = number_format_i18n( $num_posts->publish );
		$text = _n( Utils::pluralize($post_type->labels->singular_name), Utils::pluralize($post_type->labels->name) , intval( $num_posts->publish ) );
		if ( current_user_can( 'edit_posts' ) ) {
			$num = "<a href='edit.php?post_type=$post_type->name'>$num</a>";
			$text = "<a href='edit.php?post_type=$post_type->name'>$text</a>";
		}
		echo '<tr><td class="first b b-' . $post_type->name . '">' . $num . '</td>';
		echo '<td class="t ' . $post_type->name . '">' . $text . '</td></tr>';
	}
}
?>