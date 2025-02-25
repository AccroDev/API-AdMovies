<?php

    namespace Controllers;

use Dotenv\Dotenv;
use Models\GetPDO;

    class ApiAll {

        public function __construct() {
            $dotenv = Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->load(); 
        }

        public function getSubscribe()
        {   
            $apiKey = $_POST["API_KEY"] ?? null; 

            if (!$apiKey || !password_verify($apiKey, $_ENV["MOVIES_API_KEY"])) {
                echo json_encode([]);
                return;
            }

            $bdd = GetPDO::getpdo();
            $rq = $bdd->query("SELECT * FROM subscribe");
            $data = $rq->fetchAll(); 
            echo json_encode($data); 
        }
    }

?>