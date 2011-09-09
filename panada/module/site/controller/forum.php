<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_forum extends Panada_module {
    
    private $site_info, $url_args = array();
    
    public function __construct(){
        
        parent::__construct();
        
        $this->session          = new Library_session;
        $this->request          = new Library_request;
        $this->library_site     = new Site_library_site;
        $this->model_forums     = new Site_model_forums;
        $this->model_threads    = new Site_model_threads;
        $this->model_site_info  = new Site_model_site_info;
        $this->users            = new Model_users;
        $this->cache            = new Library_cache('memcached');
        
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
        
        $this->url_args = func_get_args();
        $total = count($this->url_args);
        
        if( end($this->url_args) == 'create' ){
            
            $this->thread_create();
            return;
        }
        
        if( $total < 1 )
            Library_error::_404();
        
        if( ! $this->site_info = $this->model_site_info->data() )
            Library_error::_404();
        
        $this->sub_forum();
        return;
    }
    
    private function forum(){
        
        $name                   = end($this->url_args);
        $views['site']          = $this->site_info;
        $views['forum']         = $this->model_forums->find_one( array('site_id' => $this->site_info->site_id, 'name' => $name) );
        $views['bredcump']      = $this->model_forums->bredcump($this->site_info->site_id, $views['forum']->forum_id);
        $views['sub_forums']    = $this->model_forums->find_all( array('site_id' => $this->site_info->site_id, 'parent_id' => $views['forum']->forum_id) );
        
        $this->output('template/forum', $views);
    }
    
    /**
     * Melakukan pengecekan sebelum dikirim ke method thread
     */
    private function sub_forum(){
        
        $name = end($this->url_args);
        
        // Jika sudah tidak ada di table forum, beri 404
        if( ! $forum = $this->model_forums->find_one( array('site_id' => $this->site_info->site_id, 'name' => $name) ) )
            Library_error::_404();
            
        
        // Waktunya untuk menampilkan list thread jika is_parent-nya sudah 0
        if($forum->is_parent == 0){
            $this->threads($forum);
            return;
        }
        
        // Sebaliknya, bawa kembali ke method forum jika is_parentnya 1
        else{
            $this->forum();
            return;
        }
    }
    
    /**
     * Dapatkan semua listing thread dari sebuah forum/sub-forum
     */
    private function threads( $forum ){
        
        $name               = end($this->url_args);
        $views['prefix_url']= implode('/', $this->url_args);
        $views['forum']     = $forum;
        $views['site']      = $this->site_info;
        $views['threads']   = $this->model_threads->find_all( array('site_id' => $this->site_info->site_id, 'forum_id' => $forum->forum_id) );
        $views['bredcump']  = $this->model_forums->bredcump($this->site_info->site_id, $forum->forum_id);
        
        $this->output('template/threads', $views);
    }
    
    /**
     * Membuat thread baru
     */
    private function thread_create(){
        
        $this->posts_lib    = new Library_posts;
        $this->write_lib    = new Library_write;
        
        $name               = prev($this->url_args);
        $views['site']      = $this->site_info;
        $views['errors']    = false;
        $views['is_editor'] = false;
        $views['title']     = false;
        
        $views['forum']     = $this->model_forums->find_one( array('site_id' => $this->site_info->site_id, 'name' => $name) );
        
        if( ! $views['forum'] )
            Library_error::_404();
        
        $cache_key_post     = 'thread_create_post_'.session_id();
        $cache_key_title    = 'thread_create_title_'.session_id();
        
        $views['post']      = $this->cache->get_value($cache_key_post);
        $views['title']     = $this->cache->get_value($cache_key_title);
        
        
        // Processing preview
        if( $this->request->post('preview') ){
            
            $views['post'] = $this->request->post('post');
            
            if( $views['post'] ){
                
                $views['is_editor']= true;
                
                $this->cache->set_value($cache_key_post, $views['post'], 300);
                
                // Bersihkan dari tag2 yg tidak diizinkan.
                $views['post'] = $this->write_lib->sanitize_post_content( $views['post'] );
                
                // Rapihkan tag2 (jika ada) dan format menjadi siap ditampilkan ke HTML
                $views['post'] = $this->posts_lib->the_content( $views['post'] );
                
                if( $views['title'] = $this->request->post('title') ){
                    $views['title'] = $this->write_lib->sanitize_post_title($views['title']);
                    $this->cache->set_value($cache_key_title, $views['title'], 300);
                }
                else{
                    $views['title'] = 'Untitled';
                }
            }
            else{
                $views['errors'][] = 'Please enter your thread post.';
                $views['title'] = $this->request->post('title');
            }
            
            if( $views['post'] && strlen($views['post']) < 50 )
                $views['errors'][] = 'Write at least 50 character in your post.';
        }
        
        
        // Procession submit
        if( $this->request->post('submit') ){
            
            
            // Apakah user sudah signin? Jika belum simpan data yg ia submit ke cache.
            // Setelah itu redirect ke halaman signin.
            if( ! $this->session->get('user_id') ){
                
                if( $views['title'] = $this->request->post('title') ){
                    $views['title'] = $this->write_lib->sanitize_post_title($views['title']);
                    $this->cache->set_value($cache_key_title, $views['title'], 300);
                }
                
                if( $views['post'] = $this->request->post('post') )
                    $this->cache->set_value($cache_key_post, $views['post'], 300);
                
                $this->redirect( 'signin?next=' . $this->library_site->curent_location() );
            }
            
            
            // Apakah data yg disubmit tidak kosong? atau jika kosong, apakah data
            // dalam cache tersedia?
            if( ! $views['post'] = $this->request->post('post') )
                if( ! $views['post'] = $this->cache->get_value($cache_key_post) )
                    $views['errors'][] = 'Please enter your thread post.';
            
            
            // Pengecekan juga pada title.
            if( ! $views['title'] = $this->request->post('title') )
                if( ! $views['title'] = $this->cache->get_value($cache_key_title) )
                    $views['errors'][] = 'Please enter your thread title.';
            
            
            // Jika post ada, apakah sudah lebih dari 50 karakter?
            if( $views['post'] && strlen($views['post']) < 50 )
                $views['errors'][] = 'Write at least 50 character in your post.';
            
            
            // Sudah tidak ada error, waktunya untuk submit ke db.
            if( ! $views['errors'] ){
                
                // Pastikan bahwa thread name adalah unik.
                $name = $this->write_lib->sanitize_post_name($views['title']);
                $name = $this->model_threads->create_unque_name($name, $this->site_info->site_id);
                
                $data = array(
                    'forum_id'      => $views['forum']->forum_id,
                    'site_id'       => $this->site_info->site_id,
                    'author_id'     => $this->session->get('user_id'),
                    'name'          => $name,
                    'title'         => $this->write_lib->sanitize_post_title($views['title']),
                    'content'       => $this->write_lib->sanitize_post_content($views['post']),
                    'create_date'   => date('Y-m-d H:i:s')
                );
                
                if( $insert_id = $this->model_threads->add_new($data) ){
                    
                    $data = $this->model_threads->find_one( array('thread_id' => $insert_id, 'site_id' => $this->site_info->site_id) );
                    
                    $this->cache->delete_value($cache_key_title);
                    $this->cache->delete_value($cache_key_post);
                    
                    $this->redirect( $this->library_site->thread_url($data) );
                }
            }
        }
        
        
        // Clear cache
        if( $this->request->post('clear') ){
            
            $this->cache->delete_value($cache_key_title);
            $this->cache->delete_value($cache_key_post);
            $this->redirect( $this->library_site->curent_location(false) );
        }
        
        // edit
        if( $this->request->post('edit') )
            $this->redirect( $this->library_site->curent_location(false) );
        
        // Jika pd judul terdapat tanda kutip (",') maka karekter ini harus dimodifikasi dahulu
        // agar muncul di form input text.
        $views['title'] = $this->formatting->attribute_escape($views['title']);
        
        $views['bredcump']  = $this->model_forums->bredcump($this->site_info->site_id, $views['forum']->forum_id);
        
        $this->output('template/thread_create', $views);
    }
}