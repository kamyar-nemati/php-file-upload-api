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
        <title>atomicUpload</title>
    </head>
    <body>
        <h1><i>Atomic Upload</i></h1>
        <h1>REST API: /api/upload/atomicUpload</h1>
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

        <!--<form method="post" action="/api/upload/atomicUpload">
        
        <table>
            <tr>
                <td>id (bigint)</td>
                <td><input style="width:500px;" type="edit" name="id" value=""></td>
            </tr>
            <tr>
                <td>No parameters required.</td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Submit POST Method" ></td>
            </tr>
        </table>
        
        </form>-->

        <!--<h4><b>Note: (...)</b></h4>
        <ul>
            <li>0: ...</li>
            <li>1: ...</li>
            <li>Note: ...</li>
        </ul>
        <br>-->

    </body>

</html>
