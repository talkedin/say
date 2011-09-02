<?php defined('THISPATH') or die('Can\'t access directly!');

class Library_posts {

    public function __construct(){
	
	$this->formatting = new Library_formatting;
    }
    
    /**
     * Mendapatkan gambar2 yg ada didalam post/thread
     *
     * @param string $post
     * @return arry
     */
    public function get_image_attached($post){
	
	preg_match_all('/src=(["\'])(.*?)\1/', $post, $matches);
	$img = $matches[2];
	
	return $img;
	
	return false;
    }

    /**
     * method ini menggantikan apply_filters( 'the_title', $title );
     */
    public function filters_the_title($title){
	
	$title = $this->formatting->wptexturize($title);
	$title = $this->formatting->convert_chars($title);
	
	return trim($title);
    }
    
    /**
     * Menampilkan title yg sudah di-echo
     */
    public function the_title($post, $total_char = 0){
	$title = $this->cut_title($post->post_title, $total_char);
	echo $this->filters_the_title($title);
    }

    public function the_content($content){
	
	$this->shortcodes = new Library_shortcodes;
	
	$content    = $this->shortcodes->do_shortcode($content);
	$content    = $this->formatting->wpautop($content);
	$content    = $this->formatting->wptexturize($content);
	$content    = $this->pre($content);
	//die($content);
	
	return $content;
    }
    
    /**
     * Originally from David Walsh
     * @link http://davidwalsh.name/php-html-entities
     */
    private function pre_entities($matches) {
	return str_replace($matches[1], htmlentities($matches[1]), $matches[0]);
    }
    
    /**
     * Originally from David Walsh
     * @link http://davidwalsh.name/php-html-entities
     */
    private function pre($content){
	
	//replaces pre content with html entities
	//to html entities;  assume content is in the "content" variable
	return preg_replace_callback('/<pre.*?>(.*?)<\/pre>/imsu', array('Library_posts', 'pre_entities'), $content);
    }

    public function cut_title($title, $total_char = 0){

	if($total_char > 0){
            if( mb_strlen($title) > $total_char){
                $title = strrev (substr($title, 0, $total_char));
                $title = strrev( strstr($title, ' ') ) . '...';
            }
	}

	return $this->filters_the_title($title);
    }

    public function cut_by_string($title, $total_char = 0){
	
	if($total_char > 0){
            if( mb_strlen($title) > $total_char){
                $title = substr($title, 0, $total_char) .'...';
            }
	}
	
	return $title;
    }

    public function title2url($title){
	return str_replace(' ', '-', $title);
    }

}
