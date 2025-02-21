<?php

namespace Controllers;

class Errors { 
    public static function error($err) { 
        //save error in file.json or file.txt 
        file_put_contents('error.txt', $err);  
    }

}