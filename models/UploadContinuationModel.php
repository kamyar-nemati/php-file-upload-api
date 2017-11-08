<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/UploadBaseModel.php';

/**
 * @description The UploadContinuationModel class allows continue uploading chunks of a file.
 * @subpackage UploadContinuationModel
 * @package UploadBaseModel
 * @author Kamyar
 */
class UploadContinuationModel extends UploadBaseModel {
    
    //returnable object's JSON key
    const JKEY_CHUNK_MD5SUM     = 'chunk_md5sum';
    const JKEY_BYTES_WRITTEN    = 'bytes_written';
    
    private $BINARY_FILE = "binary.bin";
    private $PHP_FSTREAM = "php://input";
    
    /**
     * @description Constructor
     * @author Kamyar
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @description Continue uploading chunks of a file.
     * @param string $sess_key The session key.
     * @param int $file_offset The size of the chunk in bytes.
     * @param string $chunk_md5 The MD5SUM hash of the chunk. <i>[Optional]</i>
     * @return JSON
     */
    public function upload(&$sess_key, &$file_offset, $chunk_md5 = NULL) {
        //the object to be returned
        $obj = [
            self::JKEY_STAT => -1
        ];
        
        //parse args
        $sess_key = strval($sess_key);
        $file_offset = intval($file_offset);
        
        //parse optional md5
        if ($chunk_md5 !== NULL) {
            $chunk_md5 = strval($chunk_md5);
        }
        
        //validate the session
        if (!$this->is_sess_valid($sess_key)) {
            
            $obj[self::JKEY_MSG] = "Invalid session.";
            return $obj;
        }
        
        //read the session file
        $sess_obj = $this->get_sess_json($sess_key);
        $main_file_full_path = $sess_obj[self::SESS_CONTENT_FILE_ABS_PATH];
        
        //get the upload path
        $upload_path = MEDIA_UPLOAD_PATH . "/{$sess_key}";
        
        //get the upload file
        $binary_file_full_path = $this->get_abs_path($upload_path, $this->BINARY_FILE);
        
        //read and save the binary data from http request
        $bin = file_get_contents($this->PHP_FSTREAM);
        file_put_contents($binary_file_full_path, $bin);
        
        //do md5
        if ($chunk_md5 !== NULL) {
            $md5sum = md5_file($binary_file_full_path);
            $obj[self::JKEY_CHUNK_MD5SUM] = $md5sum;
        }
        
        //glue the binary data on the allocated file
        $bytesWritten = 0;
        $appended = $this->appendBinary(
                $main_file_full_path, 
                $binary_file_full_path, 
                $file_offset,
                $bytesWritten);
        
        //delete the binary data
        @unlink($binary_file_full_path);
        
        //check if it's glued
        if (!$appended) {
            
            $obj[self::JKEY_MSG] = "Server failed to append the slice.";
            return $obj;
        }
        
        //return
        $obj[self::JKEY_STAT] = 0;
        $obj[self::JKEY_BYTES_WRITTEN] = $bytesWritten;
        return $obj;
    }
    
    //write the received binary
    private function appendBinary(
            &$main_file_full_path, 
            &$binary_file_full_path, 
            &$file_offset,
            &$bytesWritten) {
        
        //check for the allocated file
        if (!file_exists($main_file_full_path)) {
            return FALSE;
        }
        
        //check for the binary data
        if (!file_exists($binary_file_full_path)) {
            return FALSE;
        }
        
        //open the allocated file
        $fs = fopen($main_file_full_path, 'r+');
        
        if ($fs === FALSE) {
            return FALSE;
        }
        
        //seek to the offset on the allocated file
        if (fseek($fs, $file_offset) === -1) {
            return FALSE;
        }
        
        //write the binary data on the allocated file
        $bytesWritten = fwrite($fs, file_get_contents($binary_file_full_path));
        
        if ($bytesWritten === FALSE) {
            return FALSE;
        }
        
        //close the allocated file
        if (!fclose($fs)) {
            return FALSE;
        }
        
        //return
        return TRUE;
    }
}
