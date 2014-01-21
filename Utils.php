<?php
/*
* Static resources/methods to be used inside your theme
* Example usage: 
*   Utils::post_thumbnail('full', 'class'); 
*/
class Utils{

	/**
	* Functions to encode/decode HTML in array and objects. Thanks to http://stackoverflow.com/questions/14861354/apply-html-entity-decode-to-objects-and-arrays-using-recursion
	*/
	static function encode($data) {
		if (is_array($data)) {
			return array_map(array(self,'encode'), $data);
		}
		if (is_object($data)) {
			$tmp = clone $data; // avoid modifing original object
			foreach ( $data as $k => $var )
				$tmp->{$k} = self::encode($var);
			return $tmp;
		}
		return htmlentities($data);
	}

	static function decode($data) {
		if (is_array($data)) {
			return array_map(array(self,'decode'), $data);
		}
		if (is_object($data)) {
			$tmp = clone $data; // avoid modifing original object
			foreach ( $data as $k => $var )
				$tmp->{$k} = self::decode($var);
			return $tmp;
		}
		return html_entity_decode($data);
	}

	/* 
	* Check the current post for the existence of a short code 
	*/
	static function has_shortcode($shortcode = '') {
		
		$post_to_check = get_post(get_the_ID());
		
		// false because we have to search through the post content first
		$found = false;
		
		// if no short code was provided, return false
		if (!$shortcode) {
			return $found;
		}
		// check the post content for the short code
		if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) {
			// we have found the short code
			$found = true;
		}
		
		// return our final results
		return $found;
	}

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
	static function post_thumbnail($size = 'thumbnail', $class = ''){
		if ( has_post_thumbnail() ) { 
			echo "<aside class='entry-thumbnail'><a href='".get_permalink()."' class='$class' title='".get_the_title()."' rel='canonical'>";
			the_post_thumbnail($size);
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
					$href = wp_get_attachment_image_src( $attachment->ID, $size);
				}
				echo "<aside class='entry-thumbnail'><a class='default $class' href='".get_permalink()."' title='".get_the_title()."' rel='canonical'>";
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