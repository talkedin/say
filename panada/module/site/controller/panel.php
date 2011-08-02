<?php defined('THISPATH') or die('Can\'t access directly!');

class Site_controller_panel extends Panada_module {
    
    public function __construct(){
        
        parent::__construct();
        
    }
    
    public function index(){
        
        echo __METHOD__;
    }
}