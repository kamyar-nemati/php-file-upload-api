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

class TermUpload extends Restserver\Libraries\REST_Controller {
    
    public function __construct($config = 'rest') {
        parent::__construct($config);
    }

    public function index_post() {
        //the object to be returned
        $obj = [
            "stat" => -1
        ];
        
        //validate arg
        if (!isset($this->_post_args["sess_key"]) || 
            !isset($this->_post_args["file_meta"])) {
            
            $obj["msg"] = "Missing arg(s).";
            return $this->Json($obj);
        }
        
        $sess_key = $this->_post_args["sess_key"];
        
        $file_meta = 
                intval($this->_post_args["file_meta"]) === 0 
                ? FALSE : TRUE;
        
        $file_md5 = NULL;
        if (isset($this->_post_args["file_md5"])) {
            $file_md5 = $this->_post_args["file_md5"];
        }
        
        $this->load->model('UploadTerminationModel');
        
        return $this->Json(
                $this->UploadTerminationModel->terminate($sess_key, $file_meta, $file_md5));
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