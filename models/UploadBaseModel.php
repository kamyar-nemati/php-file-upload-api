<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * You may define your own upload path.
 */
defined('MEDIA_UPLOAD_PATH') OR define('MEDIA_UPLOAD_PATH', 'media_uploads');

/**
 * @description The Upload base model class. This is an abstract class.
 * @subpackage UploadBaseModel
 * @package CI_Model
 * @author Kamyar
 */
abstract class UploadBaseModel extends CI_Model {
    
    //returnable object's JSON key
    const JKEY_STAT     = 'stat';
    const JKEY_MSG      = 'msg';
    
    //session file keys
    const SESS_CONTENT_FILE_ABS_PATH    = "file_abs_path";
    const SESS_CONTENT_UPLOAD_PATH      = "upload_path";
    const SESS_CONTENT_FILE_NAME        = "file_name";
    const SESS_CONTENT_FILE_META        = "file_meta";
    const SESS_CONTENT_SESS_KEY         = "sess_key";
    
    //session file name
    private $SESS_FILE = "session.json";
    
    //the chunk size in bytes
    private $slice_size;
    //base url
    private $base_url;

    /**
     * @description Constructor
     * @author Kamyar
     */
    public function __construct() {
        parent::__construct();
        
        //get 'n set the chunk size from config file
        $this->slice_size = $this->config->item('upload_chunk_size');
        //get 'n set the base url from config file
        $this->base_url = $this->config->item('base_url');
    }
    
    //make unique id
    protected function mkuid() {
        return uniqid(time(), TRUE);
    }
    
    //return pretty json
    protected function Json(&$obj) {
        $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(
                        $obj, JSON_PRETTY_PRINT | 
                        JSON_UNESCAPED_UNICODE | 
                        JSON_UNESCAPED_SLASHES)
                );
    }
    
    //construct the absolute path to the file being uploaded
    protected function get_abs_path(&$upload_path, &$file_name) {
        return APPPATH . "../{$upload_path}/{$file_name}";
    }
    
    //get the session file as json object
    protected function get_sess_json(&$sess_key) {
        //validate session
        if (!$this->is_sess_valid($sess_key)) {
            return NULL;
        }
        
        //read the session file
        $file_full_path = $this->get_sess_file_abs_path($sess_key);
        $data = file_get_contents($file_full_path);
        
        //parse and return as JSON
        $json = json_decode($data, TRUE);
        return $json;
    }
    
    //set the session file as plain text
    protected function set_sess_json(&$sess_key, &$json) {
        //parse back the JSON
        $data = json_encode($json);
        
        //save the session
        $file_full_path = $this->get_sess_file_abs_path($sess_key);
        return file_put_contents($file_full_path, $data);
    }
    
    //get chunk size
    public function get_slice_size() {
        return $this->slice_size;
    }
    
    //get base url
    public function get_base_url() {
        return $this->base_url;
    }
    
    //get full path to the session file
    private function get_sess_file_abs_path(&$sess_key) {
        return APPPATH . "../" . MEDIA_UPLOAD_PATH . "/{$sess_key}/{$this->SESS_FILE}";
    }
    
    //get full url to downloadable file
    public function get_downloadable_url(&$upload_path, &$file_name) {
        return "{$this->base_url}/{$upload_path}/{$file_name}";
    }
    
    //check if session is valid
    protected function is_sess_valid(&$sess_key) {
        $sess_file_sull_path = $this->get_sess_file_abs_path($sess_key);
        return file_exists($sess_file_sull_path);
    }
}
