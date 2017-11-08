<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Kamyar
 */

require_once APPPATH . '/libraries/REST_Controller.php';

class InitUpload extends Restserver\Libraries\REST_Controller {
    
    public function __construct($config = 'rest') {
        parent::__construct($config);
    }

    public function index_post() {
        //the object to be returned
        $obj = [
            "stat" => -1
        ];
        
        //validate args
        if (!isset($this->_post_args["file_name"]) || 
            !isset($this->_post_args["byte_size"])) {
            
            $obj["msg"] = "Missing arg(s).";
            return $this->Json($obj);
        }
        
        $file_name = $this->_post_args["file_name"];
        $byte_size = $this->_post_args["byte_size"];
        
        $this->load->model('UploadInitializationModel');
        
        return $this->Json(
                $this->UploadInitializationModel->initialize($file_name, $byte_size));
    }
    
    private function Json(&$obj) {
        $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(
                        $obj, JSON_PRETTY_PRINT | 
                        JSON_UNESCAPED_UNICODE | 
                        JSON_UNESCAPED_SLASHES)
                );
    }
}