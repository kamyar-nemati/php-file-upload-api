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
        <title>termUpload</title>
    </head>
    <body>
        <h1><i>Upload Termination</i></h1>
        <h1>REST API: /api/upload/termUpload</h1>
        <ul>
            <li><b>URL:</b> /api/upload/termUpload</li>
            <li><b>Method:</b> POST</li>
            <li><b>Body:</b>
                <ol>
                    <li><b>sess_key</b> <i>(string) the session key.</i></li>
                    <li><b>file_meta</b> <i>(bool: 0|1) whether or not to return the file metadata.</i></li>
                    <li><b>file_md5</b> <i>(string) [optional] returns the file MD5SUM.</i></li>
                </ol>
            </li>
        </ul>

        <form method="post" action="/api/upload/termUpload">

            <table>
                <tr>
                    <td>sess_key (string)</td>
                    <td><input style="width:500px;" type="edit" name="sess_key" value=""></td>
                </tr>
                <tr>
                    <td>file_meta (bool: 0|1)</td>
                    <td><input style="width:500px;" type="edit" name="file_meta" value=""></td>
                </tr>
                <tr>
                    <td>file_md5 (string)</td>
                    <td><input style="width:500px;" type="edit" name="file_md5" value=""></td>
                    <td>Optional</td>
                </tr>
            <!--    <tr>
                    <td>No parameters required.</td>
                </tr>-->
                <tr>
                    <td></td>
                    <td><input type="submit" value="Submit POST Method" ></td>
                </tr>
            </table>

        </form>

        <!--<h4><b>Note: (...)</b></h4>
        <ul>
            <li>0: ...</li>
            <li>1: ...</li>
            <li>Note: ...</li>
        </ul>
        <br>-->

    </body>

</html>
