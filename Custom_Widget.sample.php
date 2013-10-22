<?php
/**
* Widget
* 
* Creates a Widget to be added in the sidebar. 
* This is a template class. To create your own widget, duplicate this class and add your own $title, $description, $fields.
* 
* Hint: You can use any input types to save your widget fields, display static information, or even WP perform queries! Happy coding!
*
* Example usage:
* function register_widget() {
*		register_widget( 'Custom_Widget' );
* }
* add_action( 'widgets_init', 'register_widget' );
*
* Required: Utils.php
*/

require_once 'Utils.php';

class Custom_Widget extends WP_Widget{
	private $slug, $title, $description, $class;
	private $fields;

	function __construct($args = array()) {
		$args = array_merge ( array(
			"title" => 'Custom Widget',
			"description" => 'A widget that displays the authors name ',
			"class" => 'hyperion_widget'
		), $args );
		
		$this->slug = Utils::generate_slug($args['title']);
		$this->title = $args['title'];
		$this->description = $args['description'];
		$this->class = $args['class'];
		$this->fields = array('blablabla');

		parent::__construct( false, $this->title );
	}

	function Custom_Widget() {
		$widget_ops = array( 
			'classname' => $this->class, 
			'description' => $this->description
		);  
		$control_ops = array( 
			'width' => 300, 
			'height' => 350, 
			'id_base' => $this->class 
		);  
		$this->WP_Widget( $this->slug, $this->title, $widget_ops, $control_ops ); 
	}  
	
	/**
	* Front-end display of widget (print the widget)
	*
	* @see WP_Widget::widget()
	*
	* @param array $args     Widget arguments.
	* @param array $instance Saved values from database.
	*/
	function widget( $args, $instance ) {
		extract( $args );  

		$title = apply_filters( 'widget_title', $instance['title'] );  

		echo $before_widget;  

		if ( $title )  
			echo $before_title . $title . $after_title;  

		foreach( $this->fields as $field ){
			echo $instance[$field] ? $instance[$field] : "";
		}

		echo $after_widget;
	}

	/**
	* Sanitize widget form values as they are saved.
	*
	* @see WP_Widget::update()
	*
	* @param array $new_instance Values just sent to be saved.
	* @param array $old_instance Previously saved values from database.
	*
	* @return array Updated safe values to be saved.
	*/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;  
	
		//Strip tags from title and name to remove HTML  
		$instance['title'] = strip_tags( $new_instance['title'] );  
		foreach($this->fields as $field){
			$instance[$field] = strip_tags( $new_instance[$field] );  
		}

		return $instance; 
	}

	/**
	* Back-end widget form.
	*
	* @see WP_Widget::form()
	*
	* @param array $instance Previously saved values from database.
	*/
	function form( $instance ) {
		//Set up some default widget settings.  
		$instance = (array) $instance; 
		?> 
			<p>  
				<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>  
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />  
			</p>  
			
		<?php foreach($this->fields as $field){ ?> 
			<p>  
				<label for="<?php echo $this->get_field_id( $field ); ?>"><?php echo $field; ?>:</label>  
				<input id="<?php echo $this->get_field_id( $field ); ?>" name="<?php echo $this->get_field_name( $field ); ?>" value="<?php echo $instance[$field]; ?>" style="width:100%;" />  
			</p>  
		<?php
		}
	}
}