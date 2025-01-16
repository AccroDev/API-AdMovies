<?php

namespace Controllers;

class Errors { 
    public static function error($err) { 
        //save error in file.json or file.txt
        $error = [
            'message' => $err->getMessage(),
            'file' => $err->getFile(),
            'line' => $err->getLine(),
            'code' => $err->getCode(),
            'trace' => $err->getTraceAsString()
        ];
        $error = json_encode($error);
        file_put_contents('error.json', $error);  
    }

}