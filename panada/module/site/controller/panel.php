<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_panel extends Panada_module {
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->db               = new Library_db;
        $this->request          = new Library_request;
        $this->library_site     = new Site_library_site;
        $this->model_forums     = new Site_model_forums;
        $this->model_site_info  = new Site_model_site_info;
        
    }
    
    public function index(){
        
        $this->output('panel/index');
    }
    
    public function forums(){
        
        $args = func_get_args();
        
        if( ! empty( $args ) ){
            
            if( is_string($args[0]) ){
                $method = 'forums_'.$args[0];
                $this->$method();
                exit;
            }
        }
        
        $this->output('panel/forums/index');
    }
    
    private function forums_create(){
        
        if( $this->request->post('submit') ){
            
            $data['site_id']    = $this->model_site_info->site_id();
            $data['author_id']  = $this->session->get('user_id');
            $data['title']      = $this->request->post('title');
            $data['parent_id']  = 0;
            $data['create_date']= date('Y-m-d H:i:s');
            
            if( $this->model_forums->add_new($data) )
                $this->redirect( $this->library_site->location('panel/forums') );
        }
        
        $this->output('panel/forums/create');
    }
}