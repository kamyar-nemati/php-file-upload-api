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

class ContUpload extends Restserver\Libraries\REST_Controller {
    
    public function __construct($config = 'rest') {
        parent::__construct($config);
    }
    
    public function index_put() {
        //support PUT method
        $this->upload();
    }
    
    public function index_post() {
        //support POST method
        $this->upload();
    }
    
    private function upload() {
        //the object to be returned
        $obj = [
            "stat" => -1
        ];
        
        //read http headers
        $HTTP_SESS_KEY = filter_input(INPUT_SERVER, "HTTP_SESS_KEY");
        $HTTP_FILE_OFFSET = filter_input(INPUT_SERVER, "HTTP_FILE_OFFSET");
        $HTTP_CHUNK_MD5 = filter_input(INPUT_SERVER, "HTTP_CHUNK_MD5"); //optional
        
        //verify headers
        if ($HTTP_SESS_KEY === NULL || $HTTP_SESS_KEY === FALSE ||
            $HTTP_FILE_OFFSET === NULL || $HTTP_FILE_OFFSET === FALSE) {
            
            $obj['msg'] = "Missing header(s).";
            return $this->Json($obj);
        }
        
        $sess_key = $HTTP_SESS_KEY;
        $file_offset = $HTTP_FILE_OFFSET;
        $chunk_md5 = $HTTP_CHUNK_MD5;
        
        if ($HTTP_CHUNK_MD5 === NULL || $HTTP_CHUNK_MD5 === FALSE) {
            $chunk_md5 = NULL;
        }
        
        $this->load->model('UploadContinuationModel');
        
	$obj_cont = $this->UploadContinuationModel
		->upload($sess_key, $file_offset, $chunk_md5);

        return $this->Json($obj_cont);
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
