<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_topic extends Panada_module {
    
    private $site_info;
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->library_site     = new Site_library_site;
        $this->model_site_info  = new Site_model_site_info;
        $this->model_forums     = new Site_model_forums;
        $this->model_threads    = new Site_model_threads;
        
        if( ! $this->site_info = $this->model_site_info->data() )
            Library_error::_404();
    }
    
    public function index(){
        
    }
    
    public function alias(){
        
        $args = func_get_args();
        
        if( empty($args) )
            Library_error::_404();
        
        $views['site']      = $this->site_info;
        $views['thread']    = $this->model_threads->find_one( array('site_id' => $this->site_info->site_id, 'name' => $args[0]) );
        $views['bredcump']  = $this->model_forums->bredcump($this->site_info->site_id, $views['thread']->forum_id);
        
        $this->output('template/thread', $views);
    }
}