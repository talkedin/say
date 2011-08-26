<?php defined('THISPATH') or die('Can\'t access directly!');

/**
 * Diambil dan dimodifikasi dari fungsi-fungsi shortcodes wordpress (2.6) wp-includes/shortcodes.php
 * Class ini berfungsi untuk 'merapihkan' tampilan pada detail post.
 *
 * WordPress API for creating bbcode like tags or what WordPress calls
 * "shortcodes." The tag and attribute parsing or regular expression code is
 * based on the Textpattern tag parser.
 *
 * A few examples are below:
 *
 * [shortcode /]
 * [shortcode foo="bar" baz="bing" /]
 * [shortcode foo="bar"]content[/shortcode]
 *
 * @author Wordpress Dev Team
 * @package WordPress
 * @subpackage Shortcodes
 * @since 2.5
 * @link http://codex.wordpress.org/Shortcode_API
 */
class Library_shortcodes {
    
    public function __construct(){
	
	$this->shortcode_tags = array(
            'wp_caption' => 'img_caption_shortcode',
            'caption' => 'img_caption_shortcode',
            //'gallery' => 'gallery_shortcode'
        );
	
	$this->media = new Library_media();
    }
    
    public function get_shortcode_regex() {
        
	$tagnames = array_keys($this->shortcode_tags);
	$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
        
	return '\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\1\])?';
    }
    
    function do_shortcode($content) {
	
	if (empty($this->shortcode_tags) || !is_array($this->shortcode_tags))
	    return $content;
	
	$pattern = $this->get_shortcode_regex();
	return preg_replace_callback('/'.$pattern.'/s', array(&$this, 'do_shortcode_tag'), $content);
    }
    
    public function do_shortcode_tag($m) {
	
	$tag = $m[1];
	$attr = $this->shortcode_parse_atts($m[2]);
	
	if ( isset($m[4]) ) {
	    // enclosing tag - extra parameter
	    return call_user_func(array($this->media, $this->shortcode_tags[$tag]), $attr, $m[4]);
	} else {
	    // self-closing tag
	    return call_user_func(array($this->media, $this->shortcode_tags[$tag]), $attr);
	}
    }
    
    public function shortcode_parse_atts($text) {
	$atts = array();
	$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
	$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
	if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
	    foreach ($match as $m) {
		if ( ! empty($m[1]) )
		    $atts[strtolower($m[1])] = stripcslashes($m[2]);
		elseif ( ! empty($m[3]) )
		    $atts[strtolower($m[3])] = stripcslashes($m[4]);
		elseif ( ! empty($m[5]) )
		    $atts[strtolower($m[5])] = stripcslashes($m[6]);
		elseif (isset($m[7]) and strlen($m[7]))
		    $atts[] = stripcslashes($m[7]);
		elseif (isset($m[8]))
		    $atts[] = stripcslashes($m[8]);
	    }
	}
	else {
	    $atts = ltrim($text);
	}
	return $atts;
    }
    
    /**
    * Combine user attributes with known attributes and fill in defaults when needed.
    *
    * @param array $pairs Entire list of supported attributes and their defaults.
    * @param array $atts User defined attributes in shortcode tag.
    * @return array Combined and filtered attribute list.
    */
    public static function shortcode_atts($pairs, $atts) {
	$atts = (array)$atts;
	$out = array();
	foreach($pairs as $name => $default) {
	    if ( array_key_exists($name, $atts) )
		$out[$name] = $atts[$name];
	    else
		$out[$name] = $default;
	}
	return $out;
    }
}