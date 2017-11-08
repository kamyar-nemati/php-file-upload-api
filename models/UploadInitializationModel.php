<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/UploadBaseModel.php';

/**
 * @description The UploadInitializationModel class initializes an upload session for a file.
 * @subpackage UploadInitializationModel
 * @package UploadBaseModel
 * @author Kamyar
 */
class UploadInitializationModel extends UploadBaseModel {
    
    /**
     * @description Constructor
     * @author Kamyar
     */
    public function __construct() {
        parent::__construct();
    }
    
    //returnable object's JSON key
    const JKEY_SESS_KEY                 = 'sess_key';
    const JKEY_SUGGESTED_CHUNK_SIZE     = 'suggested_chunk_size';

    /**
     * @description Initialize an upload session for a file.
     * @param string $file_name The name of the file.
     * @param int $byte_size The total size of the file in bytes.
     * @return JSON
     */
    public function initialize(&$file_name, &$byte_size) {
        //the object to be returned
        $obj = [
            self::JKEY_STAT => -1
        ];
        
        //parse args
        $file_name = strval($file_name);
        $byte_size = intval($byte_size);
        
        //verify args
        if (empty($file_name) || $byte_size <= 0) {
            
            $obj[self::JKEY_MSG] = "Invalid arg(s).";
            return $obj;
        }
        
        //make a solid file name
        $this->encryptFileName($file_name);
        
        //make unique id
        $sess_key = $this->mkuid();
        
        //set a unique upload path
        $upload_path = MEDIA_UPLOAD_PATH . "/{$sess_key}";
        
        //check if path exists, this is almost impossible
        while (file_exists($upload_path)) {
            $sess_key = $this->mkuid();
            $upload_path = MEDIA_UPLOAD_PATH . "/{$sess_key}";
        }
        
        //make the path available
        @mkdir($upload_path);
        
        //get the yet-to-be-uploaded file full path
        $file_full_path = $this->get_abs_path($upload_path, $file_name);
        
        //allocate the file on disk
        $seek = $byte_size - 1;
        $cmd = "dd if=/dev/zero of={$file_full_path} bs=1 count=1 seek={$seek}";
        shell_exec($cmd);
        
        //check against allocation failure
        if (!file_exists($file_full_path)) {
            
            $obj[self::JKEY_MSG] = "Server file system allocation failed.";
            return $obj;
        }
        
        //create session file on disk
        $json = [
            self::SESS_CONTENT_FILE_ABS_PATH    => $file_full_path,
            self::SESS_CONTENT_UPLOAD_PATH      => $upload_path,
            self::SESS_CONTENT_FILE_NAME        => $file_name,
            self::SESS_CONTENT_FILE_META        => "",
            self::SESS_CONTENT_SESS_KEY         => $sess_key,
        ];
        $this->set_sess_json($sess_key, $json);
        
        //return
        $obj[self::JKEY_SESS_KEY] = $sess_key;
        $obj[self::JKEY_SUGGESTED_CHUNK_SIZE] = $this->get_slice_size();
        $obj[self::JKEY_STAT] = 0;
        return $obj;
    }
    
    //encrypt file name to get rid of illegal characters
    private function encryptFileName(&$file_name) {
        $pin = strripos($file_name, '.');
        $ext = substr($file_name, $pin + 1);
        $md5 = md5($file_name);
        $file_name = "{$md5}.{$ext}";
    }
}
