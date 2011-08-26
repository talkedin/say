<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Proses manipulasi dan modifikasi terkait post/artikel.
 *
 * @package Kompasiana
 * @category Core System
 * @since Kompasiana Vesrsion 3.0 (Oktober 2010)
 */

class Library_media {
    
    public function __construct(){
	
	$this->config = new Library_config();
    }
    
    /**
    * Diambil dan dimodifikasi dari fungsi-fungsi shortcodes wordpress (2.6) wp-includes/media.php
    *
    * @author WordPress Dev Team
    * @return string
    * @param $attr array Html attribute
    * @param $content string
    * @since Kompasiana 2.0
    */
    public function img_caption_shortcode($attr, $content = null) {
        
	extract(Library_shortcodes::shortcode_atts(array(
		'id'	=> '',
		'align'	=> 'alignnone',
		'width'	=> '',
		'caption' => ''
	), $attr));
	
	if ( 1 > (int) $width || empty($caption) )
		return $content;
	
	if ( $id ) $id = 'id="' . $id . '" ';
	
	return '<div ' . $id . 'class="wp-caption ' . $align . '" style="width: ' . (10 + (int) $width) . 'px">'
	. $content . '<p class="wp-caption-text">' . $caption . '</p></div>';
    }
    
    /**
     * Membuat post tumbnail. Thumbnail dibuat dari image yang di-embed oleh
     * penulis di dalam artikelnya. Dengan catatan image-nya diupload di Kompasiana.
     *
     * @author kandar
     * @return mix string if true else false
     * @param $img_url string Url dari image yang akan dibuat thumbnail-nya
     */
    public function make_post_thumbnail($img_url_ori){
	
	$img_url = explode('/', $img_url_ori);
	
	if( ! isset($img_url[2]) )
	    return false;
	
	if( $img_url[2] != 'stat.ks.kidsklik.com' )
	    if( $img_url[2] != 'stat.kompasiana.com' )
		return false;
	
	$img_path = THISPATH.'statics/files/'.$img_url[5].'/'.$img_url[6].'/';
	$img_name = $img_url[7];
	
	if ( ! file_exists($img_path.$img_name) )
	    return false;
	
	$new_img_url = $this->config->static_url.'statics/files/'.$img_url[5].'/'.$img_url[6].'/';
	
	if( file_exists($img_path.'thumb_'.$img_name) )
	    return $new_img_url.'thumb_'.$img_name;
	
	$file_ext = end(explode('.', $img_name));
	
	$this->image = new Library_image();
	$this->image->edit_type = 'resize_crop';
	$this->image->folder = rtrim($img_path, '/');
	$this->image->new_file_name = 'thumb_'.str_replace('.'.$file_ext, '', $img_name);
	$this->image->resize_width = 85;
	$this->image->crop_width = 80;
	$this->image->crop_height = 60;
	$this->image->edit($img_name);
	
	return $new_img_url.'thumb_'.$img_name;
	
    }
    
    /**
     * Mendapatkan post thumbnail.
     *
     * @author kandar
     * @return string jika true atau boolean false jika tidak ada.
     * @param $post_content Content dari post
     */
    public function get_post_thumbnail($post_content){
	
	$this->posts_lib = new Library_posts();
	$this->memcached = new Library_memcached();
	
	if( ! $post_img = $this->posts_lib->get_image_attached($post_content) )
	    return false;
	
	if( $cached = $this->memcached->get($post_img[0]) )
	    return $cached;
	
	if( ! $thumbnail = $this->make_post_thumbnail($post_img[0]) )
	    return false;
	
	$this->memcached->set($post_img[0], $thumbnail);
	return $thumbnail;
    }
    
    public function media_metadata($metadata, $key = false){
	
	$metadata = @unserialize($metadata);
	if($key)
	    return $metadata[$key];
	
	return $metadata;
    }
    
    public function thumb_url($metadata, $file){
	
	$data = $this->media_metadata($metadata);
	$thumb_file = $data['sizes']['thumbnail']['file'];
	$file_name = basename($file);
	
	return str_replace($file_name, $thumb_file, $file);
    }
    
    public function static_files_spreader( $post_params = array() ){
	
	if( empty($post_params) )
	    return false;
	
	$this->rest = new Library_rest;
	
        //$post_params['file'] = '@'.'/home/kandar/Pictures/modern-apartement.jpg';
        //$post_params['submit'] = urlencode('submit');
	
	$contents = $this->rest->send_request('http://10.52.9.24/index.php/files_receiver', 'post', $post_params);
	
	if (! $contents )
	    return false;
	
	parse_str($contents);
	
	if($status == 'true')
	    return true;
    }
}