<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author Kamyar
 */
?>

<!DOCTYPE HTML>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <title>atomicUpload</title>
    </head>
    <body>
        <h1><i>Atomic Upload</i></h1>
        <h1>MediaServer REST API: /api/upload/atomicUpload</h1>
        <ul>
            <li><b>URL:</b> /api/upload/atomicUpload</li>
            <li><b>Method:</b> POST</li>
            
            <li><b>Header:</b>
                <ul>
                    <li><u>Upload: Initialization + Continuation</u>
                        <ol>
                            <li><b>file_name</b> <i>(string) the name of the file including extension.</i></li>
                            <li><b>byte_size</b> <i>(int) the total size of the file in bytes.</i></li>
                            <li><b>file_offset</b> <i>(int) the total chunk size in bytes.</i></li>
                            <li><b>chunk_md5</b> <i>(string) [optional] the MD5SUM of the chunk.</i></li>
                        </ol>
                    </li>
                </ul>
                
                <ul>
                    <li><u>Upload: Continuation</u>
                        <ol>
                            <li><b>sess_key</b> <i>(string) the session key.</i></li>
                            <li><b>file_offset</b> <i>(int) the total chunk size in bytes.</i></li>
                            <li><b>chunk_md5</b> <i>(string) [optional] the MD5SUM of the chunk.</i></li>
                        </ol>
                    </li>
                </ul>

                <ul>
                    <li><u>Upload: Continuation + Termination</u>
                        <ol>
                            <li><b>sess_key</b> <i>(string) the session key.</i></li>
                            <li><b>file_offset</b> <i>(int) the total chunk size in bytes.</i></li>
                            <li><b>msg_type</b> <i>(int: 2:image | 3:video | 4:audio | 7:file) determines the file type.</i></li>
                            <li><b>file_meta</b> <i>(bool: 0|1) whether or not to return the file metadata.</i></li>
                            <li><b>file_md5</b> <i>(string) [optional] returns the file MD5SUM.</i></li>
                        </ol>
                    </li>
                </ul>

                <ul>
                    <li><u>Upload: Initialization + Continuation + Termination</u>
                        <ol>
                            <li><b>file_name</b> <i>(string) the name of the file including extension.</i></li>
                            <li><b>byte_size</b> <i>(int) the total size of the file in bytes.</i></li>
                            <li><b>file_offset</b> <i>(int) the total chunk size in bytes.</i></li>
                            <li><b>msg_type</b> <i>(int: 2:image | 3:video | 4:audio | 7:file) determines the file type.</i></li>
                            <li><b>file_meta</b> <i>(bool: 0|1) whether or not to return the file metadata.</i></li>
                            <li><b>file_md5</b> <i>(string) [optional] returns the file MD5SUM.</i></li>
                        </ol>
                    </li>
                </ul>
            </li>
            
            <li><b>Body:</b>
                <ol>
                    <li><b>binary_data</b></li>
                </ol>
            </li>
        </ul>

        <form method="post" action="/api/upload/atomicUpload">
        
        <table>
            <tr>
                <td>file_name (string)</td>
                <td><input style="width:500px;" type="edit" name="file_name" id="file_name" value=""></td>
            </tr>
            <tr>
                <td>byte_size (int)</td>
                <td><input style="width:500px;" type="edit" name="byte_size" id="byte_size" value=""></td>
            </tr>
            <tr>
                <td>file_offset (int)</td>
                <td><input style="width:500px;" type="edit" name="file_offset" id="file_offset" value=""></td>
            </tr>
            <tr>
                <td>chunk_md5 (string)</td>
                <td><input style="width:500px;" type="edit" name="chunk_md5" id="chunk_md5" value=""></td>
            </tr>
            <tr>
                <td>sess_key (string)</td>
                <td><input style="width:500px;" type="edit" name="sess_key" id="sess_key" value=""></td>
            </tr>
            <tr>
                <td>msg_type (int)</td>
                <td><input style="width:500px;" type="edit" name="msg_type" id="msg_type" value=""></td>
            </tr>
            <tr>
                <td>file_meta (bool)</td>
                <td><input style="width:500px;" type="edit" name="file_meta" id="file_meta" value=""></td>
            </tr>
            <tr>
                <td>file_md5 (string)</td>
                <td><input style="width:500px;" type="edit" name="file_md5" id="file_md5" value=""></td>
            </tr>
            <tr>
                <td>binary_data (file)</td>
                <td><input style="width:500px;" type="file" name="binary_data" id="binary_data" value=""></td>
            </tr>
            <tr>
                <td>No parameters required.</td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" id="btn_submit" value="Submit POST Method" ></td>
            </tr>
        </table>
        
        </form>
        
        <textarea id="result" rows="20" cols="100"></textarea>

        <!--<h4><b>Note: (...)</b></h4>
        <ul>
            <li>0: ...</li>
            <li>1: ...</li>
            <li>Note: ...</li>
        </ul>
        <br>-->

        <script type="text/javascript">
        $(document).ready(function() {
            
            var _file = null;
            
            $(document).on('change', '#binary_data', function(e) {
                _file = e.target.files[0];
            });
            
            $(document).on('click', '#btn_submit', function(e) {
                
                e.preventDefault();
                
                $.ajax({
                    url: '/api/upload/atomicUpload',
                    type: 'POST',
                    async: false,
                    data: _file,
                    contentType: 'application/x-www-form-urlencoded',
                    processData: false,
                    beforeSend: function (xhr) {
                        
                        var _file_name = $('#file_name').val();
                        var _byte_size = $('#byte_size').val();
                        var _file_offset = $('#file_offset').val();
                        var _chunk_md5 = $('#chunk_md5').val();
                        var _sess_key = $('#sess_key').val();
                        var _msg_type = $('#msg_type').val();
                        var _file_meta = $('#file_meta').val();
                        var _file_md5 = $('#file_md5').val();
                        
                        if (_file_name !== '')
                            xhr.setRequestHeader('file_name', _file_name);
                        
                        if (_byte_size !== '')
                            xhr.setRequestHeader('byte_size', _byte_size);
                    
                        if (_file_offset !== '')
                            xhr.setRequestHeader('file_offset', _file_offset);
                    
                        if (_chunk_md5 !== '')
                            xhr.setRequestHeader('chunk_md5', _chunk_md5);
                    
                        if (_sess_key !== '')
                            xhr.setRequestHeader('sess_key', _sess_key);
                    
                        if (_msg_type !== '')
                            xhr.setRequestHeader('msg_type', _msg_type);
                    
                        if (_file_meta !== '')
                            xhr.setRequestHeader('file_meta', _file_meta);
                    
                        if (_file_md5 !== '')
                            xhr.setRequestHeader('file_md5', _file_md5);
                        
                    },
                    success: function (data, textStatus, jqXHR) {
                        
//                        if(data.stat == 0) {
//                            alert('Success');
//                        } else {
//                            alert('Failure');
//                        }

                        var json = JSON.stringify(data);
                        document.getElementById('result').value = json;
                        
                    },
                    complete: function (jqXHR, textStatus) {
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                    }
                });
                
            });
            
        });
    </script>
        
    </body>
    
</html>
