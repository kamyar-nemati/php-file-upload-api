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
        <title>contUpload</title>
    </head>
    <body>
        <h1><i>Upload Continuation</i></h1>
        <h1>REST API: /api/upload/contUpload</h1>
        <ul>
            <li><b>URL:</b> /api/upload/contUpload</li>
            <li><b>Method:</b> PUT</li>
            <li><b>Header:</b>
                <ol>
                    <li><b>sess_key</b> <i>(string) the session key.</i></li>
                    <li><b>file_offset</b> <i>(int) the total chunk size in bytes.</i></li>
                    <li><b>chunk_md5</b> <i>(string) [optional] the MD5SUM of the chunk.</i></li>
                </ol>
            </li>
            <li><b>Body:</b>
                <ol>
                    <li><b>binary_data</b></li>
                </ol>
            </li>
        </ul>

        <!--<form method="put" action="/api/upload/contUpload">
        
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
                <td><input type="submit" value="Submit PUT Method" ></td>
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
