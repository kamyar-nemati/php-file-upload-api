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

class AtomicUpload extends Restserver\Libraries\REST_Controller {
    
    public function __construct($config = 'rest') {
        parent::__construct($config);
    }

    public function index_post() {
        //object to be returned
        $obj = [
            'stat'  =>  -1
        ];
        
        //read headers
        $HTTP_FILE_NAME     = filter_input(INPUT_SERVER, "HTTP_FILE_NAME");
        $HTTP_BYTE_SIZE     = filter_input(INPUT_SERVER, "HTTP_BYTE_SIZE");
        $HTTP_FILE_OFFSET   = filter_input(INPUT_SERVER, "HTTP_FILE_OFFSET");
        $HTTP_CHUNK_MD5     = filter_input(INPUT_SERVER, "HTTP_CHUNK_MD5");
        $HTTP_SESS_KEY      = filter_input(INPUT_SERVER, "HTTP_SESS_KEY");
        $HTTP_FILE_META     = filter_input(INPUT_SERVER, "HTTP_FILE_META");
        $HTTP_FILE_MD5      = filter_input(INPUT_SERVER, "HTTP_FILE_MD5");
        
        /*
         * Possible calls:
         * 1. Init + Cont
         * 2. Cont + Term
         * 3. Init + Cont + Term
         */
        
        //-----------------------------Init + Cont------------------------------
        
        if ($HTTP_FILE_NAME     !== NULL && $HTTP_FILE_NAME      !== FALSE  && 
            $HTTP_BYTE_SIZE     !== NULL && $HTTP_BYTE_SIZE      !== FALSE  && 
            $HTTP_FILE_OFFSET   !== NULL && $HTTP_FILE_OFFSET    !== FALSE  && 
            /*($HTTP_CHUNK_MD5    === NULL || $HTTP_CHUNK_MD5      === FALSE) &&*/
            ($HTTP_SESS_KEY     === NULL || $HTTP_SESS_KEY       === FALSE) && 
            ($HTTP_FILE_META    === NULL || $HTTP_FILE_META      === FALSE) && 
            ($HTTP_FILE_MD5     === NULL || $HTTP_FILE_MD5       === FALSE)) {
            
            $file_name      = strval($HTTP_FILE_NAME);
            $byte_size      = intval($HTTP_BYTE_SIZE);
            $file_offset    = intval($HTTP_FILE_OFFSET);
            
            $chunk_md5 = NULL;
            if ($HTTP_CHUNK_MD5 !== NULL && $HTTP_CHUNK_MD5 !== FALSE)
            {
                $chunk_md5 = strval($HTTP_CHUNK_MD5);
            }
            
            $this->initCont(
                    $file_name, $byte_size, $file_offset, $chunk_md5, $obj);
        }
        
        //--------------------------------Cont----------------------------------
        
        if ($HTTP_SESS_KEY      !== NULL && $HTTP_SESS_KEY      !== FALSE  && 
            $HTTP_FILE_OFFSET   !== NULL && $HTTP_FILE_OFFSET   !== FALSE  && 
            /*($HTTP_CHUNK_MD5    === NULL || $HTTP_CHUNK_MD5     === FALSE) &&*/
            ($HTTP_FILE_NAME    === NULL || $HTTP_FILE_NAME     === FALSE) && 
            ($HTTP_BYTE_SIZE    === NULL || $HTTP_BYTE_SIZE     === FALSE) && 
            ($HTTP_FILE_META    === NULL || $HTTP_FILE_META     === FALSE) && 
            ($HTTP_FILE_MD5     === NULL || $HTTP_FILE_MD5      === FALSE)) {
            
            $sess_key       = strval($HTTP_SESS_KEY);
            $file_offset    = intval($HTTP_FILE_OFFSET);
            
            $chunk_md5 = NULL;
            if ($HTTP_CHUNK_MD5 !== NULL && $HTTP_CHUNK_MD5 !== FALSE)
            {
                $chunk_md5 = strval($HTTP_CHUNK_MD5);
            }
            
            $this->load->model('UploadContinuationModel');
        
            $obj_cont = $this->UploadContinuationModel
                    ->upload($sess_key, $file_offset, $chunk_md5);
            
            return $this->Json($obj_cont);
        }
        
        //-----------------------------Cont + Term------------------------------
        
        if ($HTTP_SESS_KEY      !== NULL && $HTTP_SESS_KEY      !== FALSE   && 
            $HTTP_FILE_OFFSET   !== NULL && $HTTP_FILE_OFFSET   !== FALSE   && 
            $HTTP_FILE_META     !== NULL && $HTTP_FILE_META     !== FALSE   && 
            ($HTTP_FILE_NAME    === NULL || $HTTP_FILE_NAME     === FALSE)  && 
            ($HTTP_BYTE_SIZE    === NULL || $HTTP_BYTE_SIZE     === FALSE)  && 
            /*($HTTP_FILE_MD5     === NULL || $HTTP_FILE_MD5      === FALSE)  &&*/
            ($HTTP_CHUNK_MD5    === NULL || $HTTP_CHUNK_MD5     === FALSE)) {
            
            $sess_key       = strval($HTTP_SESS_KEY);
            $file_offset    = intval($HTTP_FILE_OFFSET);
            $file_meta      = intval($HTTP_FILE_META) === 0 ? FALSE : TRUE;
            
            $file_md5 = NULL;
            if ($HTTP_FILE_MD5 !== NULL && $HTTP_FILE_MD5 !== FALSE)
            {
                $file_md5 = strval($HTTP_FILE_MD5);
            }
            
            $this->contTerm(
                    $sess_key, $file_offset, $file_meta, $file_md5, $obj);
        }
        
        //-------------------------Init + Cont + Term---------------------------
        
        if ($HTTP_FILE_NAME     !== NULL && $HTTP_FILE_NAME     !== FALSE   && 
            $HTTP_BYTE_SIZE     !== NULL && $HTTP_BYTE_SIZE     !== FALSE   && 
            $HTTP_FILE_OFFSET   !== NULL && $HTTP_FILE_OFFSET   !== FALSE   && 
            $HTTP_FILE_META     !== NULL && $HTTP_FILE_META     !== FALSE   && 
            ($HTTP_SESS_KEY     === NULL || $HTTP_SESS_KEY      === FALSE)  && 
            /*($HTTP_FILE_MD5     === NULL || $HTTP_FILE_MD5      === FALSE)  &&*/
            ($HTTP_CHUNK_MD5    === NULL || $HTTP_CHUNK_MD5     === FALSE)) {
            
            $file_name      = strval($HTTP_FILE_NAME);
            $byte_size      = intval($HTTP_BYTE_SIZE);
            $file_offset    = intval($HTTP_FILE_OFFSET);
            $file_meta      = intval($HTTP_FILE_META) === 0 ? FALSE : TRUE;
            
            $file_md5 = NULL;
            if ($HTTP_FILE_MD5 !== NULL && $HTTP_FILE_MD5 !== FALSE)
            {
                $file_md5 = strval($HTTP_FILE_MD5);
            }
            
            $this->initContTerm(
                    $file_name, $byte_size, $file_offset, $file_meta, $file_md5, $obj);
        }
        
        //return
        return $this->Json($obj);
    }
    
    private function initCont(&$file_name, &$byte_size, &$file_offset, &$chunk_md5, &$obj)
    {
        //---------------------------------Init---------------------------------
        
        $this->load->model('UploadInitializationModel');

        $obj_init = $this->UploadInitializationModel
                ->initialize($file_name, $byte_size);

        if ($obj_init[UploadInitializationModel::JKEY_STAT] !== 0)
        {
            return $this->Json($obj_init);
        }

        $sess_key = $obj_init[UploadInitializationModel::JKEY_SESS_KEY];

        //---------------------------------Cont---------------------------------
        
        $this->load->model('UploadContinuationModel');

        $obj_cont = $this->UploadContinuationModel
                ->upload($sess_key, $file_offset, $chunk_md5);

        if ($obj_cont[UploadContinuationModel::JKEY_STAT] !== 0)
        {
            return $this->Json($obj_cont);
        }

        $bytes_written = $obj_cont[UploadContinuationModel::JKEY_BYTES_WRITTEN];

        //----------------------------------------------------------------------

        //prepare return object
        $obj[UploadInitializationModel::JKEY_SESS_KEY] = $sess_key;
        $obj[UploadContinuationModel::JKEY_BYTES_WRITTEN] = $bytes_written;
        if ($chunk_md5 !== NULL)
        {
            $obj[UploadContinuationModel::JKEY_CHUNK_MD5SUM] = 
                    $obj_cont[UploadContinuationModel::JKEY_CHUNK_MD5SUM];
        }
        $obj[UploadInitializationModel::JKEY_SUGGESTED_CHUNK_SIZE] = 
                $obj_init[UploadInitializationModel::JKEY_SUGGESTED_CHUNK_SIZE];
        $obj[UploadBaseModel::JKEY_STAT] = 0;
    }
    
    private function contTerm(&$sess_key, &$file_offset, &$file_meta, &$file_md5, &$obj)
    {
        //---------------------------------Cont---------------------------------
        
        $this->load->model('UploadContinuationModel');
            
        $obj_cont = $this->UploadContinuationModel
                ->upload($sess_key, $file_offset);

        if ($obj_cont[UploadContinuationModel::JKEY_STAT] !== 0)
        {
            return $this->Json($obj_cont);
        }

        $bytes_written = $obj_cont[UploadContinuationModel::JKEY_BYTES_WRITTEN];

        //---------------------------------Term---------------------------------
        
        $this->load->model('UploadTerminationModel');

        $obj_term = 
                $this->UploadTerminationModel
                ->terminate($sess_key, $file_meta, $file_md5);

        if ($obj_term[UploadTerminationModel::JKEY_STAT] !== 0)
        {
            return $this->Json($obj_term);
        }

        $data = $obj_term[UploadTerminationModel::JKEY_DATA];

        //----------------------------------------------------------------------
        
        //prepare return object
        $obj[UploadContinuationModel::JKEY_BYTES_WRITTEN] = $bytes_written;
        $obj[UploadTerminationModel::JKEY_DATA] = $data;
        if ($file_md5 !== NULL)
        {
            $obj[UploadTerminationModel::JKEY_FILE_MD5SUM] = 
                    $obj_term[UploadTerminationModel::JKEY_FILE_MD5SUM];
        }
        $obj[UploadBaseModel::JKEY_STAT] = 0;
    }
    
    private function initContTerm(&$file_name, &$byte_size, &$file_offset, &$file_meta, &$file_md5, &$obj)
    {
        //---------------------------------Init---------------------------------
        
        $this->load->model('UploadInitializationModel');

        $obj_init = $this->UploadInitializationModel
                ->initialize($file_name, $byte_size);

        if ($obj_init[UploadInitializationModel::JKEY_STAT] !== 0)
        {
            return $this->Json($obj_init);
        }

        $sess_key = $obj_init[UploadInitializationModel::JKEY_SESS_KEY];
        
        //---------------------------------Cont---------------------------------
        
        $this->load->model('UploadContinuationModel');
            
        $obj_cont = $this->UploadContinuationModel
                ->upload($sess_key, $file_offset);

        if ($obj_cont[UploadContinuationModel::JKEY_STAT] !== 0)
        {
            return $this->Json($obj_cont);
        }

        $bytes_written = $obj_cont[UploadContinuationModel::JKEY_BYTES_WRITTEN];

        //---------------------------------Term---------------------------------
        
        $this->load->model('UploadTerminationModel');

        $obj_term = $this->UploadTerminationModel
                ->terminate($sess_key, $file_meta, $file_md5);

        if ($obj_term[UploadTerminationModel::JKEY_STAT] !== 0)
        {
            return $this->Json($obj_term);
        }

        $data = $obj_term[UploadTerminationModel::JKEY_DATA];
        
        //----------------------------------------------------------------------
        
        //prepare return object
        $obj[UploadInitializationModel::JKEY_SESS_KEY] = $sess_key;
        $obj[UploadContinuationModel::JKEY_BYTES_WRITTEN] = $bytes_written;
        $obj[UploadTerminationModel::JKEY_DATA] = $data;
        if ($file_md5 !== NULL)
        {
            $obj[UploadTerminationModel::JKEY_FILE_MD5SUM] = 
                    $obj_term[UploadTerminationModel::JKEY_FILE_MD5SUM];
        }
        $obj[UploadBaseModel::JKEY_STAT] = 0;
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