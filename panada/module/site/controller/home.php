<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_home extends Panada_module {
    
    public $curent_location;
    
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
        
        if( ! $views['site'] = $this->model_site_info->data() )
            Library_error::_404();
        
        $views['forums'] = $this->model_forums->find_all(
                                                        array(
                                                            'site_id' => (int) $views['site']->site_id,
                                                            //'parent_id' => 0
                                                        )
                                                    );
        
        $this->output('template/index', $views);
    }
}