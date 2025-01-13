<?php

use Controllers\Router;
use Controllers\UserController;
use Dotenv\Dotenv;

require "vendor/autoload.php";

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Vérifier les cookies et connecter l'utilisateur automatiquement
$userController = new UserController();
$userController->autoLogin();

$routeur = new Router("Controllers");

$routeur 
    // path, class@methode, name
    ->get("/search","Search@search","search")
    ->get("/add-movie","MovieAutoAdd@addMovie","addMovie")
    ->get("/create-index","Search@createIndex","createIndex")
    ->get("/vote/[i:id]","SeriesVote@voteOrDevote","voteOrDevote")
    ->get("/top-recommended","SeriesVote@getTopRecommended","topRecommended")
    ->get("/addShopMovie","ShopController@addShopMovie","addShopMovie")
    ->post("/create-shop","ShopController@createShop","createShop")
    //get movie from shop
    ->get("/get/[i:id]","ShopController@getShopMovies","getShopMovies")
    //get all shops of one user
    ->get("/get-shops","ShopController@getShops","getShops")
    //get all shops of one user
    ->get("/get-user-shops","ShopController@getUserShops","getUserShops")
    ->post("/register","UserController@register","register")
    ->post("/confirm","UserController@confirm","confirm")
    ->post("/login","UserController@login","login")
    ->get("/logout","UserController@logout","logout")
    ->run();
?>