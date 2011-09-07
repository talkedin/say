<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_forum extends Panada_module {
    
    private $site_info;
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->request          = new Library_request;
        $this->library_site     = new Site_library_site;
        $this->model_forums     = new Site_model_forums;
        $this->model_threads    = new Site_model_threads;
        $this->model_site_info  = new Site_model_site_info;
        $this->users            = new Model_users;
        
        if( ! $this->site_info = $this->model_site_info->data() )
            Library_error::_404();
        
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
        Library_error::_404();
    }
    
    public function alias(){
        
        $args = func_get_args();
        $total = count($args);
        
        if( $total < 1 )
            Library_error::_404();
        
        if( ! $this->site_info = $this->model_site_info->data() )
            Library_error::_404();
        
        $this->sub_forum($args);
        return;
    }
    
    private function forum( $args = array() ){
        
        $name                   = end($args);
        $views['site']          = $this->site_info;
        $views['forum']         = $this->model_forums->find_one( array('site_id' => $this->site_info->site_id, 'name' => $name) );
        $views['bredcump']      = $this->model_forums->bredcump($this->site_info->site_id, $views['forum']->forum_id);
        $views['sub_forums']    = $this->model_forums->find_all( array('site_id' => $this->site_info->site_id, 'parent_id' => $views['forum']->forum_id) );
        
        $this->output('template/forum', $views);
    }
    
    /**
     * Melakukan pengecekan sebelum dikirim ke method thread
     */
    private function sub_forum( $args = array() ){
        
        $name = end($args);
        
        // Jika sudah tidak ada di table forum, beri 404
        if( ! $forum = $this->model_forums->find_one( array('site_id' => $this->site_info->site_id, 'name' => $name) ) )
            Library_error::_404();
            
        
        // Waktunya untuk menampilkan list thread jika is_parent-nya sudah 0
        if($forum->is_parent == 0){
            $this->threads($forum, $args);
            return;
        }
        
        // Sebaliknya, bawa kembali ke method forum jika is_parentnya 1
        else{
            $this->forum($args);
            return;
        }
    }
    
    /**
     * Dapatkan semua listing thread dari sebuah forum/sub-forum
     */
    private function threads( $forum, $args ){
        
        $name               = end($args);
        $views['prefix_url']= implode('/', $args);
        $views['forum']     = $forum;
        $views['site']      = $this->site_info;
        $views['threads']   = $this->model_threads->find_all( array('site_id' => $this->site_info->site_id, 'forum_id' => $forum->forum_id) );
        $views['bredcump']  = $this->model_forums->bredcump($this->site_info->site_id, $forum->forum_id);
        
        $this->output('template/threads', $views);
    }
}