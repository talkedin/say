<?php defined('THISPATH') or die('Can\'t access directly!');

class Library_formatting {
    
    public function __construct(){
	
	$this->shortcodes = new Library_shortcodes;
    }
    
    public function wptexturize($text) {
	//global $wp_cockneyreplace;
	$next = true;
	$has_pre_parent = false;
	$output = '';
	$curl = '';
	$textarr = preg_split('/(<.*>|\[.*\])/Us', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$stop = count($textarr);

	// if a plugin has provided an autocorrect array, use it
	if ( isset($wp_cockneyreplace) ) {
            $cockney = array_keys($wp_cockneyreplace);
            $cockneyreplace = array_values($wp_cockneyreplace);
	} else {
            $cockney = array("'tain't","'twere","'twas","'tis","'twill","'til","'bout","'nuff","'round","'cause");
            $cockneyreplace = array("&#8217;tain&#8217;t","&#8217;twere","&#8217;twas","&#8217;tis","&#8217;twill","&#8217;til","&#8217;bout","&#8217;nuff","&#8217;round","&#8217;cause");
	}
        
	$static_characters = array_merge(array('---', ' -- ', '--', 'xn&#8211;', '...', '``', '\'s', '\'\'', ' (tm)'), $cockney);
	$static_replacements = array_merge(array('&#8212;', ' &#8212; ', '&#8211;', 'xn--', '&#8230;', '&#8220;', '&#8217;s', '&#8221;', ' &#8482;'), $cockneyreplace);
        
	$dynamic_characters = array('/\'(\d\d(?:&#8217;|\')?s)/', '/(\s|\A|")\'/', '/(\d+)"/', '/(\d+)\'/', '/(\S)\'([^\'\s])/', '/(\s|\A)"(?!\s)/', '/"(\s|\S|\Z)/', '/\'([\s.]|\Z)/', '/(\d+)x(\d+)/');
	$dynamic_replacements = array('&#8217;$1','$1&#8216;', '$1&#8243;', '$1&#8242;', '$1&#8217;$2', '$1&#8220;$2', '&#8221;$1', '&#8217;$1', '$1&#215;$2');
        
	for ( $i = 0; $i < $stop; $i++ ) {
            $curl = $textarr[$i];
            
            if (isset($curl{0}) && '<' != $curl{0} && '[' != $curl{0} && $next && !$has_pre_parent) { // If it's not a tag
                // static strings
                $curl = str_replace($static_characters, $static_replacements, $curl);
                // regular expressions
                $curl = preg_replace($dynamic_characters, $dynamic_replacements, $curl);
            } elseif (strpos($curl, '<code') !== false || strpos($curl, '<kbd') !== false || strpos($curl, '<style') !== false || strpos($curl, '<script') !== false) {
                $next = false;
            } elseif (strpos($curl, '<pre') !== false) {
                $has_pre_parent = true;
            } elseif (strpos($curl, '</pre>') !== false) {
                $has_pre_parent = false;
            } else {
                $next = true;
            }
            
            $curl = preg_replace('/&([^#])(?![a-zA-Z1-4]{1,8};)/', '&#038;$1', $curl);
            $output .= $curl;
	}
        
  	return $output;
    }
    
    public function wpautop($pee, $br = 1) {
        
	$pee = $pee . "\n"; // just to make things a little easier, pad the end
	$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
        
	// Space things out a little
	$allblocks = '(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|map|area|blockquote|address|math|style|input|p|h[1-6]|hr)';
	$pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
	$pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines
	
        if ( strpos($pee, '<object') !== false ) {
            $pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee); // no pee inside object/embed
            $pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
	}
	
        $pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
	
	/**
	 * Proses di bawah mengakibatkan ditulisan yg panjang tidak muncul.
	 */
	//$pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "<p>$1</p>\n", $pee); // make paragraphs, including one at the end
	$pee = '<p>'.preg_replace('#(<br\s*?/?>\s*?){2,}#', '</p>'."\n".'<p>', nl2br($pee)).'</p>';
	
	$pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
	$pee = preg_replace('!<p>([^<]+)\s*?(</(?:div|address|form)[^>]*>)!', "<p>$1</p>$2", $pee);
	$pee = preg_replace( '|<p>|', "$1<p>", $pee );
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
	$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
	$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
	$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
	
        if ($br) {
            $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', create_function('$matches', 'return str_replace("\n", "<WPPreserveNewline />", $matches[0]);'), $pee);
            $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
            $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
	}
	
        $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
	$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
	
        if (strpos($pee, '<pre') !== false)
	    $pee = preg_replace_callback('!(<pre.*?>)(.*?)</pre>!is', array($this, 'clean_pre'), $pee );
	
        $pee = preg_replace( "|\n</p>$|", '</p>', $pee );
	$pee = preg_replace('/<p>\s*?(' . $this->shortcodes->get_shortcode_regex() . ')\s*<\/p>/s', '$1', $pee); // don't auto-p wrap shortcodes that stand alone
        
	return $pee;
    }
    
    public function clean_pre($matches) {
	if ( is_array($matches) )
	    $text = $matches[1] . $matches[2] . "</pre>";
	else
	    $text = $matches;

	$text = str_replace('<br />', '', $text);
	$text = str_replace('<p>', "\n", $text);
	$text = str_replace('</p>', '', $text);

	return $text;
    }
    
    public function convert_chars($content, $deprecated = '') {
	// Translation of invalid Unicode references range to valid range
	$wp_htmltranswinuni = array(
	'&#128;' => '&#8364;', // the Euro sign
	'&#129;' => '',
	'&#130;' => '&#8218;', // these are Windows CP1252 specific characters
	'&#131;' => '&#402;',  // they would look weird on non-Windows browsers
	'&#132;' => '&#8222;',
	'&#133;' => '&#8230;',
	'&#134;' => '&#8224;',
	'&#135;' => '&#8225;',
	'&#136;' => '&#710;',
	'&#137;' => '&#8240;',
	'&#138;' => '&#352;',
	'&#139;' => '&#8249;',
	'&#140;' => '&#338;',
	'&#141;' => '',
	'&#142;' => '&#382;',
	'&#143;' => '',
	'&#144;' => '',
	'&#145;' => '&#8216;',
	'&#146;' => '&#8217;',
	'&#147;' => '&#8220;',
	'&#148;' => '&#8221;',
	'&#149;' => '&#8226;',
	'&#150;' => '&#8211;',
	'&#151;' => '&#8212;',
	'&#152;' => '&#732;',
	'&#153;' => '&#8482;',
	'&#154;' => '&#353;',
	'&#155;' => '&#8250;',
	'&#156;' => '&#339;',
	'&#157;' => '',
	'&#158;' => '',
	'&#159;' => '&#376;'
	);
        
	// Remove metadata tags
	$content = preg_replace('/<title>(.+?)<\/title>/','',$content);
	$content = preg_replace('/<category>(.+?)<\/category>/','',$content);
        
	// Converts lone & characters into &#38; (a.k.a. &amp;)
	$content = preg_replace('/&([^#])(?![a-z1-4]{1,8};)/i', '&#038;$1', $content);
        
	// Fix Word pasting
	$content = strtr($content, $wp_htmltranswinuni);
        
	// Just a little XHTML help
	$content = str_replace('<br>', '<br />', $content);
	$content = str_replace('<hr>', '<hr />', $content);
        
	return $content;
    }
    
    public function teaser($word_limit, $content){
        
	$content	= $this->post_tag_filter($content);
	$content 	= $this->wpautop($content);
	$content	= $this->wptexturize($content);
	$content	= $this->post_tag_filter($content);
	$teaser		= $this->cut_article_by_words($content, $word_limit);
        
	return $teaser;
    }
    
    function post_tag_filter($source){
	
	$replace_all_html	= strip_tags($source);
	$bbc_tag		= array('/\[caption(.*?)]\[\/caption\]/is');
	$result			= preg_replace($bbc_tag, '', $replace_all_html);
        
	return $result;
    }
    
    public function cut_article_by_words($original_text, $how_many){
	
	$word_cut = strtok($original_text," ");
	
	$return = '';

	for ($i=1;$i<=$how_many;$i++){
	    
	    $return	.= $word_cut;
	    $return	.= (" ");
	    $word_cut = strtok(" ");
	}
	
	$return .= '';
	return $return;
    }
    
    public function display_name($display_name){
        return ucwords(strtolower($display_name));
    }
    
    /**
     * Convert format tanggal mysql (2011-08-24 13:27:00) ke format date yg dinginkan.
     * 
     * @link http://php.net/manual/en/function.date.php
     * @param string $mysqlstring
     * @param string $dateformatstring
     * @return mix
     */
    public function mysql2date($mysqlstring, $dateformatstring = 'Y-m-d'){
        
	$m = $mysqlstring;
	if ( empty( $m ) )
	    return false;
	
	$i = mktime(
            (int) substr( $m, 11, 2 ), (int) substr( $m, 14, 2 ), (int) substr( $m, 17, 2 ),
            (int) substr( $m, 5, 2 ), (int) substr( $m, 8, 2 ), (int) substr( $m, 0, 4 )
	);
        
	return date($dateformatstring, $i);
    }
    
    /**
     * Convert string date format ke dalam format date mysql.
     *
     * @param int $month
     * @param int $day
     * @param int $year
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param string $dateformatstring
     * @return mix
    */
    public function date2mysql($month, $day, $year, $hour = 0, $minute = 0, $second = 0, $dateformatstring = 'Y-m-d'){
	$date = mktime( (int) $hour, (int) $minute, (int) $second, (int) $month, (int) $day, (int) $year );
	
	return date($dateformatstring, $date);
    }
    
    /**
     * Parsing url dalam string dan jadikan clickable
     *
     * @param string
     * @return string
     */
    public function make_clickable($ret) {
	$ret = ' ' . $ret;
	// in testing, using arrays here was found to be faster
	$ret = preg_replace_callback('#([\s>])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is', array($this, '_make_url_clickable_cb'), $ret);
	$ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is', array($this, '_make_web_ftp_clickable_cb'), $ret);
	$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', array($this, '_make_email_clickable_cb'), $ret);
	// this one is not in an array because we need it to run last, for cleanup of accidental links within links
	$ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
	$ret = trim($ret);
	return $ret;
    }
    
    public function _make_url_clickable_cb($matches) {
	$ret = '';
	$url = $matches[2];
	//$url = clean_url($url);
	if ( empty($url) )
		return $matches[0];
	// removed trailing [.,;:] from URL
	if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($url, -1);
		$url = substr($url, 0, strlen($url)-1);
	}
	return $matches[1] . "<a href=\"$url\" rel=\"nofollow\">$url</a>" . $ret;
    }

    function _make_web_ftp_clickable_cb($matches) {
	$ret = '';
	$dest = $matches[2];
	$dest = 'http://' . $dest;
	//$dest = clean_url($dest);
	if ( empty($dest) )
		return $matches[0];
	// removed trailing [,;:] from URL
	if ( in_array(substr($dest, -1), array('.', ',', ';', ':')) === true ) {
		$ret = substr($dest, -1);
		$dest = substr($dest, 0, strlen($dest)-1);
	}
	return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
    }

    public function _make_email_clickable_cb($matches) {
	$email = $matches[2] . '@' . $matches[3];
	return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
    }
    
    public function convert_smilies($text) {
	
	$this->smilies_init();
	
	$output = '';
	if ( !empty($this->wp_smiliessearch) && !empty($this->wp_smiliesreplace) ) {
	    // HTML loop taken from texturize function, could possible be consolidated
	    //$textarr = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
	    //$stop = count($textarr);// loop stuff
	    //for ($i = 0; $i < $stop; $i++) {
		//$content = $textarr[$i];
		$content = $text;
		if ((strlen($content) > 0) /*&& ('<' != $content{0})*/) { // If it's not a tag
		    //$content = preg_replace($this->wp_smiliessearch, $this->wp_smiliesreplace, $content);
		    $content = str_replace($this->wp_smiliessearch, $this->wp_smiliesreplace, $content);
		}
		$output .= $content;
	    //}
	} else {
	    // return default text.
	    $output = $text;
	}
	return $output;
    }
    
    public function smilies_init() {
	
	$this->config = Library_config::instance();
	
	if ( !isset( $this->wpsmiliestrans ) ) {
		$this->wpsmiliestrans = array(
		':mrgreen:' => 'icon_mrgreen.gif',
		':neutral:' => 'icon_neutral.gif',
		':twisted:' => 'icon_twisted.gif',
		  ':arrow:' => 'icon_arrow.gif',
		  ':shock:' => 'icon_eek.gif',
		  ':smile:' => 'icon_smile.gif',
		    ':???:' => 'icon_confused.gif',
		   ':cool:' => 'icon_cool.gif',
		   ':evil:' => 'icon_evil.gif',
		   ':grin:' => 'icon_biggrin.gif',
		   ':idea:' => 'icon_idea.gif',
		   ':oops:' => 'icon_redface.gif',
		   ':razz:' => 'icon_razz.gif',
		   ':roll:' => 'icon_rolleyes.gif',
		   ':wink:' => 'icon_wink.gif',
		    ':cry:' => 'icon_cry.gif',
		    ':eek:' => 'icon_surprised.gif',
		    ':lol:' => 'icon_lol.gif',
		    ':mad:' => 'icon_mad.gif',
		    ':sad:' => 'icon_sad.gif',
		      '8-)' => 'icon_cool.gif',
		      '8-O' => 'icon_eek.gif',
		      ':-(' => 'icon_sad.gif',
		      ':-)' => 'icon_smile.gif',
		      ':-?' => 'icon_confused.gif',
		      ':-D' => 'icon_biggrin.gif',
		      ':-P' => 'icon_razz.gif',
		      ':-o' => 'icon_surprised.gif',
		      ':-x' => 'icon_mad.gif',
		      ':-|' => 'icon_neutral.gif',
		      ';-)' => 'icon_wink.gif',
		       '8)' => 'icon_cool.gif',
		       '8O' => 'icon_eek.gif',
		       ':(' => 'icon_sad.gif',
		       ':)' => 'icon_smile.gif',
		       ':?' => 'icon_confused.gif',
		       ':D' => 'icon_biggrin.gif',
		       ':P' => 'icon_razz.gif',
		       ':o' => 'icon_surprised.gif',
		       ':x' => 'icon_mad.gif',
		       ':|' => 'icon_neutral.gif',
		       ';)' => 'icon_wink.gif',
		      ':!:' => 'icon_exclaim.gif',
		      ':?:' => 'icon_question.gif',
		);
	}
	
	foreach ( (array) $this->wpsmiliestrans as $smiley => $img ) {
	    $this->wp_smiliessearch[] = $smiley;
	    $smiley_masked = trim( $smiley );
	    $this->wp_smiliesreplace[] = ' <img src="'.$this->config->static_url.'statics/images/smilies/'.$img.'" alt="" class="wp-smiley" /> ';
	}
    }
    
    public function attribute_escape($text){
	return $this->wp_specialchars($text, true);
    }
    
    public function wp_specialchars( $text, $quotes = 0 ) {
	// Like htmlspecialchars except don't double-encode HTML entities
	$text = str_replace('&&', '&#038;&', $text);
	$text = str_replace('&&', '&#038;&', $text);
	$text = preg_replace('/&(?:$|([^#])(?![a-z1-4]{1,8};))/', '&#038;$1', $text);
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('>', '&gt;', $text);
	if ( 'double' === $quotes ) {
		$text = str_replace('"', '&quot;', $text);
	} elseif ( 'single' === $quotes ) {
		$text = str_replace("'", '&#039;', $text);
	} elseif ( $quotes ) {
		$text = str_replace('"', '&quot;', $text);
		$text = str_replace("'", '&#039;', $text);
	}
	return $text;
    }
    
    /*
    force_balance_tags
   
    Balances Tags of string using a modified stack.
   
    @param text      Text to be balanced
    @param force     Forces balancing, ignoring the value of the option
    @return          Returns balanced text
    @author          Leonard Lin (leonard@acm.org)
    @version         v1.1
    @date            November 4, 2001
    @license         GPL v2.0
    @notes
    @changelog
    ---  Modified by Scott Reilly (coffee2code) 02 Aug 2004
	   1.2  ***TODO*** Make better - change loop condition to $text
	   1.1  Fixed handling of append/stack pop order of end text
		Added Cleaning Hooks
	   1.0  First Version
   */
    public function force_balance_tags( $text ) {
	$tagstack = array(); $stacksize = 0; $tagqueue = ''; $newtext = '';
	$single_tags = array('br', 'hr', 'img', 'input'); //Known single-entity/self-closing tags
	$nestable_tags = array('blockquote', 'div', 'span'); //Tags that can be immediately nested within themselves

	# WP bug fix for comments - in case you REALLY meant to type '< !--'
	$text = str_replace('< !--', '<    !--', $text);
	# WP bug fix for LOVE <3 (and other situations with '<' before a number)
	$text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

	while (preg_match("/<(\/?\w*)\s*([^>]*)>/",$text,$regex)) {
		$newtext .= $tagqueue;

		$i = strpos($text,$regex[0]);
		$l = strlen($regex[0]);

		// clear the shifter
		$tagqueue = '';
		// Pop or Push
		if ( ( isset($regex[1][0])) && ($regex[1][0] == "/") ) { // End Tag
			$tag = strtolower(substr($regex[1],1));
			// if too many closing tags
			if($stacksize <= 0) {
				$tag = '';
				//or close to be safe $tag = '/' . $tag;
			}
			// if stacktop value = tag close value then pop
			else if ($tagstack[$stacksize - 1] == $tag) { // found closing tag
				$tag = '</' . $tag . '>'; // Close Tag
				// Pop
				array_pop ($tagstack);
				$stacksize--;
			} else { // closing tag not at top, search for it
				for ($j=$stacksize-1;$j>=0;$j--) {
					if ($tagstack[$j] == $tag) {
					// add tag to tagqueue
						for ($k=$stacksize-1;$k>=$j;$k--){
							$tagqueue .= '</' . array_pop ($tagstack) . '>';
							$stacksize--;
						}
						break;
					}
				}
				$tag = '';
			}
		} else { // Begin Tag
			$tag = strtolower($regex[1]);

			// Tag Cleaning

			// If self-closing or '', don't do anything.
			if((substr($regex[2],-1) == '/') || ($tag == '')) {
			}
			// ElseIf it's a known single-entity tag but it doesn't close itself, do so
			elseif ( in_array($tag, $single_tags) ) {
				$regex[2] .= '/';
			} else {	// Push the tag onto the stack
				// If the top of the stack is the same as the tag we want to push, close previous tag
				if (($stacksize > 0) && !in_array($tag, $nestable_tags) && ($tagstack[$stacksize - 1] == $tag)) {
					$tagqueue = '</' . array_pop ($tagstack) . '>';
					$stacksize--;
				}
				$stacksize = array_push ($tagstack, $tag);
			}

			// Attributes
			$attributes = $regex[2];
			if($attributes) {
				$attributes = ' '.$attributes;
			}
			$tag = '<'.$tag.$attributes.'>';
			//If already queuing a close tag, then put this tag on, too
			if ($tagqueue) {
				$tagqueue .= $tag;
				$tag = '';
			}
		}
		$newtext .= substr($text,0,$i) . $tag;
		$text = substr($text,$i+$l);
	}

	// Clear Tag Queue
	$newtext .= $tagqueue;

	// Add Remaining text
	$newtext .= $text;

	// Empty Stack
	while($x = array_pop($tagstack)) {
		$newtext .= '</' . $x . '>'; // Add remaining tags to close
	}

	// WP fix for the bug with HTML comments
	$newtext = str_replace("< !--","<!--",$newtext);
	$newtext = str_replace("<    !--","< !--",$newtext);

	return $newtext;
    }
    
    public function rel_nofollow( $text ) {
	
	$this->db = new Library_db();
	// This is a pre save filter, so text is already escaped.
	$text = stripslashes($text);
	$text = preg_replace_callback('|<a (.+?)>|i', array(&$this, 'rel_nofollow_callback'), $text);
	$text = $this->db->escape($text);
	return $text;
    }

    public function rel_nofollow_callback( $matches ) {
	$text = $matches[1];
	$text = str_replace(array(' rel="nofollow"', " rel='nofollow'"), '', $text);
	return "<a $text rel=\"nofollow\">";
    }
    
    public function human_time_language($lang = 'id'){
	
	$language['en'] = array('ago' => 'ago', 'minute' => 'min', 'minutes' => 'mins', 'hour' => 'hour', 'hours' => 'hours', 'day' => 'day', 'days' => 'days');
	$language['id'] = array('ago' => 'yang lalu', 'minute' => 'menit', 'minutes' => 'menit', 'hour' => 'jam', 'hours' => 'jam', 'day' => 'hari', 'days' => 'hari');
	
	return $language[$lang];
    }
    
    /**
     * Pindahkan ke Library formatting
     */
    public function human_time($mysql_date, $language = 'id'){
	
	$lang = $this->human_time_language($language);
	
	$time = strtotime($mysql_date);
	if ( ( abs(time() - $time) ) < 86400 ) {
	    return sprintf('%s '.$lang['ago'], $this->human_time_diff( $time ) );
	} else {
	    return $this->mysql2date($mysql_date, 'j F Y H:i');
	}
    }
    
    /**
     * Pindahkan ke Library formatting
     */
    private function human_time_diff( $from, $to = '', $language = 'id' ) {
	
	$lang = $this->human_time_language($language);
	
	if ( empty($to) )
	    $to = time();
	$diff = (int) abs($to - $from);
	if ($diff <= 3600) {
	    $mins = round($diff / 60);
	    if ($mins <= 1) {
		$mins = 1;
	    }
	    $since = sprintf( $this->_ngettext('%s '.$lang['minute'], '%s '.$lang['minutes'], $mins), $mins );
	} else if (($diff <= 86400) && ($diff > 3600)) {
	    $hours = round($diff / 3600);
	    if ($hours <= 1) {
		$hours = 1;
	    }
	    $since = sprintf( $this->_ngettext('%s '.$lang['hour'], '%s '.$lang['hours'], $hours), $hours );
	} elseif ($diff >= 86400) {
	    $days = round($diff / 86400);
	    if ($days <= 1) {
		$days = 1;
	    }
	    $since = sprintf( $this->_ngettext('%s '.$lang['day'], '%s '.$lang['days'], $days), $days );
	}
	return $since;
    }
    
    /**
     * Pindahkan ke Library formatting
     */
    private function _ngettext( $single, $plural, $number ){
	
	if ($number != 1)
	    return $plural;
	else
	    return $single;
    }
    
    /**
    * Unserialize value only if it was serialized.
    *
    * @since 2.0.0
    *
    * @param string $original Maybe unserialized original, if is needed.
    * @return mixed Unserialized data can be any type.
    */
   public function maybe_unserialize( $original ) {
	if ( $this->is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		if ( false !== $gm = @unserialize( $original ) )
			return $gm;
	return $original;
    }
    
    /**
    * Check value to find if it was serialized.
    *
    * If $data is not an string, then returned value will always be false.
    * Serialized data is always a string.
    *
    * @since 2.0.5
    *
    * @param mixed $data Value to check to see if was serialized.
    * @return bool False if not serialized and true if it was.
    */
    public function is_serialized( $data ) {
	// if it isn't a string, it isn't serialized
	if ( ! is_string( $data ) )
		return false;
	$data = trim( $data );
	if ( 'N;' == $data )
		return true;
	if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
		return false;
	switch ( $badions[1] ) {
		case 'a' :
		case 'O' :
		case 's' :
			if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
				return true;
			break;
		case 'b' :
		case 'i' :
		case 'd' :
			if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
				return true;
			break;
	}
	return false;
    }
    
    /**
    * Check whether serialized data is of string type.
    *
    * @since 2.0.5
    *
    * @param mixed $data Serialized data
    * @return bool False if not a serialized string, true if it is.
    */
    public function is_serialized_string( $data ) {
	// if it isn't a string, it isn't a serialized string
	if ( !is_string( $data ) )
		return false;
	$data = trim( $data );
	if ( preg_match( '/^s:[0-9]+:.*;$/s', $data ) ) // this should fetch all serialized strings
		return true;
	return false;
    }
    
    /**
    * Serialize data, if needed.
    *
    * @param mixed $data Data that might be serialized.
    * @return mixed A scalar data
    */
    public function maybe_serialize( $data ) {
	if ( is_string( $data ) )
		return $data;
	elseif ( is_array( $data ) || is_object( $data ) )
		return serialize( $data );
	if ( $this->is_serialized( $data ) )
		return serialize( $data );
	return $data;
    }
    
    public function simple_content($content){
	$content   = $this->post_tag_filter($content);
        $content   = $this->wpautop($content);
        $content   = $this->shortcodes->do_shortcode($content);
        return $this->wptexturize($content);
    }
}