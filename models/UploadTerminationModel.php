<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/UploadBaseModel.php';

/**
 * @description The UploadTerminationModel class finalizes the upload session.
 * @subpackage UploadTerminationModel
 * @package UploadBaseModel
 * @author Kamyar
 */
class UploadTerminationModel extends UploadBaseModel {
    
    //returnable object's JSON key
    const JKEY_FILE_MD5SUM          = 'file_md5sum';
    const JKEY_DATA                 = 'data';
    const JKEY_MEDIA_URL            = 'media_url';
    const JKEY_MEDIA_DATA           = 'media_data';
    const JKEY_MEDIA_THUMB_URL      = 'media_thumb_url';
    const JKEY_MEDIA_THUMB_DATA     = 'media_thumb_data';
    const JKEY_MEDIA_BASE64_DATA    = 'media_base64_data';
    
    
    //different mime types
    private $MIME_TYPE_IMAGE        = "image";
    private $MIME_TYPE_VIDEO        = "video";
    private $MIME_TYPE_AUDIO        = "audio";
    private $MIME_TYPE_TEXT         = "text";
    private $MIME_TYPE_APPLICATION  = "application";
    
    private $THUMBNAIL_MAX_WIDTH    = 400;
    private $THUMBNAIL_MAX_HEIGHT   = 400;
    
    private $BASE64_MAX_WIDTH       = 20;
    private $BASE64_MAX_HEIGHT      = 20;
    
    //the uploaded file info
    private $file_meta;
    private $file_mime;
    
    /**
     * @description Constructor
     * @author Kamyar
     */
    public function __construct() {
        parent::__construct();
        
        //initialize attributes
        $this->file_meta = [];
        $this->file_mime = NULL;
        
        //load the image library
        $this->load->library('image_lib', array());
    }

    /**
     * @description Finalizes the upload session and returns file metadata.
     * @param string $sess_key The session key.
     * @param Bool $file_meta Whether or not to return the metadata of the file. <i>[Optional]</i>
     * @param string $file_md5 The MD5SUM hash of the file. <i>[Optional]</i>
     * @return JSON
     */
    public function terminate(&$sess_key, $file_meta = FALSE, $file_md5 = NULL) {
        //the object to be returned
        $obj = [
            self::JKEY_STAT => -1
        ];
        
        //parse arg
        $sess_key = strval($sess_key);
        
        //verify arg
        if (empty($sess_key)) {
            
            $obj[self::JKEY_MSG] = "Invalid arg(s).";
            return $obj;
        }
        
        //read the session
        $sess_obj = $this->get_sess_json($sess_key);
        
        //validate session
        if ($sess_obj === NULL) {
            
            $obj[self::JKEY_MSG] = "Invalid session key.";
            return $obj;
        }
        
        //get session info
        $file_full_path = $sess_obj[self::SESS_CONTENT_FILE_ABS_PATH];
        $upload_path = $sess_obj[self::SESS_CONTENT_UPLOAD_PATH];
        $file_name = $sess_obj[self::SESS_CONTENT_FILE_NAME];
        $file_meta_sess = $sess_obj[self::SESS_CONTENT_FILE_META];
        
        //compute MD5 hash of the file
        if ($file_md5 !== NULL) {
            $md5sum = md5_file($file_full_path);
            $obj[self::JKEY_FILE_MD5SUM] = $md5sum;
        }
        
        //get file metadata
        if ($file_meta === TRUE) {
            if (empty($file_meta_sess)) {
                if (!$this->processFile($file_full_path, $upload_path, $file_name)) {

                    $obj[self::JKEY_MSG] = "Server failed to process the file.";
                    return $obj;
                }
                $obj[self::JKEY_DATA] = $this->file_meta;
                
                //update session file
                $json = [
                    self::SESS_CONTENT_FILE_ABS_PATH    => $file_full_path,
                    self::SESS_CONTENT_UPLOAD_PATH      => $upload_path,
                    self::SESS_CONTENT_FILE_NAME        => $file_name,
                    self::SESS_CONTENT_FILE_META        => $this->file_meta,
                    self::SESS_CONTENT_SESS_KEY         => $sess_key,
                ];
                $this->set_sess_json($sess_key, $json);
            } else {
                $obj[self::JKEY_DATA] = $file_meta_sess;
            }
        } else {
            //make the final url to the uploaded file
            $downloadable_file_path = $this->get_downloadable_url($upload_path, $file_name);
            $obj[self::JKEY_MEDIA_URL] = $downloadable_file_path;
        }
        
        //return
        $obj[self::JKEY_STAT] = 0;
        return $obj;
    }
    
    //get file's metadata according to its mime type
    private function processFile(&$file_full_path, &$upload_path, &$file_name) {
        //get mime type
        if (!$this->getMimeType($file_full_path)) {
            return FALSE;
        }
        //handle according to the mime type
        if ($this->file_mime === $this->MIME_TYPE_IMAGE) {
            return $this->handleImage($file_full_path, $upload_path, $file_name);
        } else if ($this->file_mime === $this->MIME_TYPE_VIDEO) {
            return $this->handleVideo($file_full_path, $upload_path, $file_name);
        } else {
            return $this->handleOther($file_full_path, $upload_path, $file_name);
        }
    }
    
    //determine file's mime
    private function getMimeType(&$file_full_path) {
        $mime_info = mime_content_type($file_full_path);
        $mime_type = substr($mime_info, 0, stripos($mime_info, '/'));
        if ($mime_type !== $this->MIME_TYPE_APPLICATION &&
                $mime_type !== $this->MIME_TYPE_AUDIO &&
                $mime_type !== $this->MIME_TYPE_IMAGE &&
                $mime_type !== $this->MIME_TYPE_TEXT &&
                $mime_type !== $this->MIME_TYPE_VIDEO) {
            return FALSE;
        }
        $this->file_mime = $mime_type;
        return TRUE;
    }
    
    private function handleImage(&$image_full_path, &$upload_path, &$image_full_name) {
        //get image name-only
        $image_name = explode('.', $image_full_name)[0];
        
        //get image type
        $image_ext = explode('.', $image_full_name)[1];
        
        //get image size
        $image_size = filesize($image_full_path);
        
        //get image dimension
        $image_dimension = getimagesize($image_full_path);
        $image_width = $image_dimension[0];
        $image_height = $image_dimension[1];
        
        //image mete
        $image_meta = [
            'width'     => $image_width,
            'height'    => $image_height,
            'bytes'     => $image_size,
            'type'      => $image_ext
        ];
        $this->file_meta[self::JKEY_MEDIA_DATA] = $image_meta;
        
        //append the media url
        $this->file_meta[self::JKEY_MEDIA_URL] = $this->get_downloadable_url($upload_path, $image_full_name);
        
        //make thumbnail
        $foo = $this->createThumbnail($upload_path, $image_full_path, $image_name, $image_ext);
        
        if (!$foo) {
            return FALSE;
        }
        
        //extract image base64 data
        $bar = $this->extractBase64($upload_path, $image_full_path, $image_name, $image_ext);
        
        if (!$bar) {
            return FALSE;
        }
        
        return TRUE;
    }
    
    private function handleVideo(&$video_full_path, &$upload_path, &$video_full_name) {
        //append the media url
        $this->file_meta[self::JKEY_MEDIA_URL] = $this->get_downloadable_url($upload_path, $video_full_name);
        
        //get video name-only
        $video_name = explode('.', $video_full_name)[0];
        
        //get video type
        $video_ext = explode('.', $video_full_name)[1];
        
        //get image size
        $video_size = filesize($video_full_path);
        
        //video mete
        $video_meta = [
            'bytes'     => $video_size,
            'type'      => $video_ext
        ];
        $this->file_meta[self::JKEY_MEDIA_DATA] = $video_meta;
        
        //get yet-to-be-created-thumbnail name, ext, full name, and full path
        $video_thumb_name = "{$video_name}_thumb";
        $video_thumb_ext = "png";
        $video_thumb_full_name = "{$video_thumb_name}.{$video_thumb_ext}";
        $video_thumb_full_path = $this->get_abs_path($upload_path, $video_thumb_full_name);
        
        //the shell command to extract thumbnail
        $cmd = "/usr/bin/avconv -y -i {$video_full_path} -r 30 -ss 0.0 -frames:v 1 {$video_thumb_full_path}";
        
        //execute the command
        shell_exec($cmd);
        
        //check if thumbnail extracted
        if (!file_exists($video_thumb_full_path)) {
            return FALSE;
        }
        
        //process the thumbnail
        $foo = $this->createThumbnail($upload_path, $video_thumb_full_path, $video_thumb_name, $video_thumb_ext, FALSE);
        
        if (!$foo) {
            return FALSE;
        }
        
        //extract image base64 data
        $bar = $this->extractBase64($upload_path, $video_thumb_full_path, $video_thumb_name, $video_thumb_ext);
        
        if (!$bar) {
            return FALSE;
        }
        
        return TRUE;
    }
    
    private function handleOther(&$file_full_path, &$upload_path, &$file_name) {
        //append the media url
        $this->file_meta[self::JKEY_MEDIA_URL] = $this->get_downloadable_url($upload_path, $file_name);
        
        return TRUE;
    }
    
    private function createThumbnail(&$upload_path, &$image_full_path, &$image_name, &$image_ext, $create_thumb = TRUE) {
        //library config
        $config_image_lib = [
            'image_library'     => 'gd2',  
            'source_image'      => $image_full_path,
            'create_thumb'      => $create_thumb,
            'maintain_ratio'    => TRUE,
            'width'             => $this->THUMBNAIL_MAX_WIDTH,
            'height'            => $this->THUMBNAIL_MAX_HEIGHT,
        ];
        
        //re-init the image library
        $this->image_lib->clear();
        $this->image_lib->initialize($config_image_lib);
        
        //make thumbnail
        if (!$this->image_lib->resize()) {
            return FALSE;
        }
        
        //get thumbnail full name and full path
        $image_thumb_full_name = NULL;
        if (!$create_thumb) {
            $image_thumb_full_name = "{$image_name}.{$image_ext}";
        } else {
            $image_thumb_full_name = "{$image_name}_thumb.{$image_ext}";
        }
        $image_thumb_full_path = $this->get_abs_path($upload_path, $image_thumb_full_name);
        
        //get thumbnail type
        $image_thumb_ext = explode('.', $image_thumb_full_name)[1];
        
        //get thumbnail size
        $image_thumb_size = filesize($image_thumb_full_path);
        
        //get thumbnail dimension
        $image_thumb_dimension = getimagesize($image_thumb_full_path);
        $image_thumb_width = $image_thumb_dimension[0];
        $image_thumb_height = $image_thumb_dimension[1];
        
        //thumbnail mete
        $image_thumb_meta = [
            'width'     => $image_thumb_width,
            'height'    => $image_thumb_height,
            'bytes'     => $image_thumb_size,
            'type'      => $image_thumb_ext
        ];
        $this->file_meta[self::JKEY_MEDIA_THUMB_DATA] = $image_thumb_meta;
        
        //append the media thumb url
        $this->file_meta[self::JKEY_MEDIA_THUMB_URL] = $this->get_downloadable_url($upload_path, $image_thumb_full_name);
        
        return TRUE;
    }
    
    private function extractBase64(&$upload_path, &$image_full_path, &$image_name, &$image_ext) {
        //get base64 full name and full path
        $image_base64_full_name = "{$image_name}_base64.{$image_ext}";
        $image_base64_full_path = $this->get_abs_path($upload_path, $image_base64_full_name);
        
        //copy the image
        if (!copy($image_full_path, $image_base64_full_path)) {
            return FALSE;
        }
        
        //library config
        $config_image_lib = [
            'image_library'     => 'gd2',  
            'source_image'      => $image_base64_full_path,
            'create_thumb'      => FALSE,
            'maintain_ratio'    => TRUE,
            'width'             => $this->BASE64_MAX_WIDTH,
            'height'            => $this->BASE64_MAX_HEIGHT,
        ];;
        
        //re-init the image library
        $this->image_lib->clear();
        $this->image_lib->initialize($config_image_lib);
        
        //make base64
        if (!$this->image_lib->resize()) {
            return FALSE;
        }
        
        //read the base64 image as binary
        $image_base64_bin = file_get_contents($image_base64_full_path);
        $image_base64_encoded = base64_encode($image_base64_bin);
        $this->file_meta[self::JKEY_MEDIA_BASE64_DATA] = $image_base64_encoded;
        
        //delete the base64 file
        @unlink($image_base64_full_path);
        
        return TRUE;
    }
}
