<?php
namespace Controllers;

use Models\GetPDO;
use Dotenv\Dotenv;

class VilleController {
    public function __construct() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }

    public function getville(){
        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT `id`, `name` FROM `villes`');
        $query->execute();
        $villes = $query->fetchAll();
        if($villes && $villes !== null){
          $villes = json_encode($villes);
          echo $villes;  
        }else{
            echo json_encode([]);
        }
        
    }

}
?>
