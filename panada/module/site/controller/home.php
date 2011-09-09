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
        $this->users            = new Model_users;
        $this->formatting       = new Library_formatting;
        
        // Inisial properties untuk user yang sudah sign in.
        $this->signed_in->username = 'Anonymous';
        $this->signed_in->avatar = $this->users->find_gravatar();
        
        
        // Reinisial object di atas jika user sudah sign in.
        if( $this->session->get('user_id') ){
            $this->signed_in->username = $this->session->get('username');
            $this->signed_in->avatar = $this->session->get('avatar');
        }
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