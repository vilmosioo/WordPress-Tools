<?php
/*
* Static resources/methods to be used inside your theme
* Example usage: 
*   Utils::post_thumbnail('full', 'class'); 
*/
class Utils{
	/*
	* Return details about the last JSON error
	*/
	static function json_error(){
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				echo ' - No errors';
			break;
			case JSON_ERROR_DEPTH:
				echo ' - Maximum stack depth exceeded';
			break;
			case JSON_ERROR_STATE_MISMATCH:
				echo ' - Underflow or the modes mismatch';
			break;
			case JSON_ERROR_CTRL_CHAR:
				echo ' - Unexpected control character found';
			break;
			case JSON_ERROR_SYNTAX:
				echo ' - Syntax error, malformed JSON';
			break;
			case JSON_ERROR_UTF8:
				echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
			default:
				echo ' - Unknown error';
			break;
		}
	}

	/*
	* Generate a slug from string (lowercase and '-' as separator)
	*/

	static function generate_slug($s = ""){
	return strtolower(str_replace(" ", "-", $s));
	}

	/* 
	* Nice arrays
	*
	* This function will print out a nicely formatted array
	*/
	static function print_pre($s = "") {
	echo '<pre>';
	print_r( $s );
	echo '</pre>';
	}

	/* 
	* A smart way to get the post thumbnail
	* 
	* If found, the function will retur the post thumbnail, 
	* else it will return the first image attached to the post
	*/
	static function post_thumbnail($full = 'thumbnail', $class = ''){
	if ( has_post_thumbnail() ) { 
		echo "<aside><a href='".get_permalink()."' class='$class' title='".get_the_title()."' rel='canonical'>";
		the_post_thumbnail($full);
		echo "</a></aside>";
	} else {
		$attachments = get_posts( array(
		'post_type' => 'attachment',
		'numberposts'     => 1,
		'post_parent' => get_the_ID(),
		'exclude'     => get_post_thumbnail_id()
		) );

		if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$href = wp_get_attachment_image_src( $attachment->ID, $full);
		}
		echo "<aside><a class='default $class' href='".get_permalink()."' title='".get_the_title()."' rel='canonical'>";
		echo "<img src='".$href[0]."' alt='".get_the_title()."'/>"; 
		echo "</a></aside>";
		}
	}
	}

	/* 
	* Get a custom length excerpt
	*/
	static function custom_excerpt($s, $length){
	$temp = substr(strip_tags($s), 0, $length);
	if(strlen(strip_tags( $s ) ) > $length) $temp .= "&#0133;";
	return $temp; 
	}

	/*
	* Get a list of related posts based on common tags and categories
	* 
	* @param id : the id of the post
	*/
	static function related_posts($id){
	$categories = get_the_category($id);
	$tags = get_the_tags($id);
	if ($categories || $tags) {
		$category_ids = array();
		if($categories) foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
	 
		$tag_ids = array();
		if($tags) foreach($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;
	 
		$args=array(
		'tax_query' => array(
			'relation' => 'OR',
			array(
			'taxonomy' => 'category',
			'field' => 'id',
			'terms' => $category_ids
			),
			array(
			'taxonomy' => 'post_tag',
			'field' => 'id',
			'terms' => $tag_ids
			)
		),
		'post__not_in' => array($post->ID),
		'posts_per_page'=> 4, // Number of related posts that will be shown.
		);
	 
		$my_query = new WP_Query( $args );
		if( $my_query->have_posts() ) {
		echo "<h3>Related posts</h3><ul class='list related'>";
		while( $my_query->have_posts() ) {
			$my_query->the_post(); ?>
			<li>
			<?php Utils::post_thumbnail('thumbnail', 'cutout'); ?>
			<a href='<?php the_permalink(); ?>' rel='canonical'><?php the_title();?></a>
			</li>
			<?php
		}
		echo "</ul><div class='clear'></div>";
		}
	}
	wp_reset_postdata();
	} 

	/*
	* Pluralize English strings
	*
	* Thanks
	* https://github.com/iamntz/base-project
	* 
	*/
	static $plural = array(
		'/(quiz)$/i'               => "$1zes",
		'/^(ox)$/i'                => "$1en",
		'/([m|l])ouse$/i'          => "$1ice",
		'/(matr|vert|ind)ix|ex$/i' => "$1ices",
		'/(x|ch|ss|sh)$/i'         => "$1es",
		'/([^aeiouy]|qu)y$/i'      => "$1ies",
		'/(hive)$/i'               => "$1s",
		'/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
		'/(shea|lea|loa|thie)f$/i' => "$1ves",
		'/sis$/i'                  => "ses",
		'/([ti])um$/i'             => "$1a",
		'/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
		'/(bu)s$/i'                => "$1ses",
		'/(alias)$/i'              => "$1es",
		'/(octop)us$/i'            => "$1i",
		'/(ax|test)is$/i'          => "$1es",
		'/(us)$/i'                 => "$1es",
		'/s$/i'                    => "s",
		'/$/'                      => "s"
	);

	static $singular = array(
		'/(quiz)zes$/i'             => "$1",
		'/(matr)ices$/i'            => "$1ix",
		'/(vert|ind)ices$/i'        => "$1ex",
		'/^(ox)en$/i'               => "$1",
		'/(alias)es$/i'             => "$1",
		'/(octop|vir)i$/i'          => "$1us",
		'/(cris|ax|test)es$/i'      => "$1is",
		'/(shoe)s$/i'               => "$1",
		'/(o)es$/i'                 => "$1",
		'/(bus)es$/i'               => "$1",
		'/([m|l])ice$/i'            => "$1ouse",
		'/(x|ch|ss|sh)es$/i'        => "$1",
		'/(m)ovies$/i'              => "$1ovie",
		'/(s)eries$/i'              => "$1eries",
		'/([^aeiouy]|qu)ies$/i'     => "$1y",
		'/([lr])ves$/i'             => "$1f",
		'/(tive)s$/i'               => "$1",
		'/(hive)s$/i'               => "$1",
		'/(li|wi|kni)ves$/i'        => "$1fe",
		'/(shea|loa|lea|thie)ves$/i'=> "$1f",
		'/(^analy)ses$/i'           => "$1sis",
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
		'/([ti])a$/i'               => "$1um",
		'/(n)ews$/i'                => "$1ews",
		'/(h|bl)ouses$/i'           => "$1ouse",
		'/(corpse)s$/i'             => "$1",
		'/(us)es$/i'                => "$1",
		'/s$/i'                     => ""
	);

	static $irregular = array(
		'move'   => 'moves',
		'foot'   => 'feet',
		'goose'  => 'geese',
		'sex'    => 'sexes',
		'child'  => 'children',
		'man'    => 'men',
		'tooth'  => 'teeth',
		'person' => 'people'
	);

	static $uncountable = array(
		'sheep',
		'fish',
		'deer',
		'series',
		'species',
		'money',
		'rice',
		'information',
		'equipment'
	);

	public static function pluralize( $string )
	{
		// save some time in the case that singular and plural are the same
		if ( in_array( strtolower( $string ), self::$uncountable ) )
			return $string;

		// check for irregular singular forms
		foreach ( self::$irregular as $pattern => $result )
		{
			$pattern = '/' . $pattern . '$/i';

			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string);
		}

		// check for matches using regular expressions
		foreach ( self::$plural as $pattern => $result )
		{
			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string );
		}

		return $string;
	}

	public static function singularize( $string )
	{
		// save some time in the case that singular and plural are the same
		if ( in_array( strtolower( $string ), self::$uncountable ) )
			return $string;

		// check for irregular plural forms
		foreach ( self::$irregular as $result => $pattern )
		{
			$pattern = '/' . $pattern . '$/i';

			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string);
		}

		// check for matches using regular expressions
		foreach ( self::$singular as $pattern => $result )
		{
			if ( preg_match( $pattern, $string ) )
				return preg_replace( $pattern, $result, $string );
		}

		return $string;
	}

	public static function pluralize_if($count, $string)
	{
		if ($count == 1)
			return "1 $string";
		else
			return $count . " " . self::pluralize($string);
	}
}
?>