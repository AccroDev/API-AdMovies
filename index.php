<?php

use Controllers\Router;
use Controllers\UserController;
use Dotenv\Dotenv;
require "vendor/autoload.php";

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
header("Access-Control-Allow-Origin: *");

// Vérifier les cookies et connecter l'utilisateur automatiquement
$userController = new UserController();
$userController->autoLogin();

$routeur = new Router("Controllers");
session_start();  

/* error_reporting(E_ALL);
set_error_handler('Controllers\Errors::error');
set_exception_handler('Controllers\Errors::error');  */

$_SESSION["id"] = 2;//a supprimmer


$routeur 
    // path, class@methode, name
    ->get("/api/search","Search@search","search")
    ->get("/api/add-movie","MovieAutoAdd@addMovie","addMovie")
    ->get("/api/tntAdd","MovieAutoAdd@insertTNTindex","insertTNTindex")
    ->get("/api/create-index","Search@createIndex","createIndex")
    ->get("/api/vote/[i:id]","SeriesVote@voteOrDevote","voteOrDevote")
    ->get("/api/top-recommended","SeriesVote@getTopRecommended","topRecommended")
    ->get("/api/addShopMovie","ShopController@addShopMovie","addShopMovie")
    ->post("/api/create-shop","ShopController@createShop","createShop")
    //get movie from shop
    ->get("/api/get/[i:id]","ShopController@getShopMovies","getShopMovies")
    //get all shops of one user
    ->get("/api/get-shops","ShopController@getShops","getShops")
    //get all shops of one user
    ->get("/api/get-user-shops","ShopController@getShops","getUserShops")
    ->get("/api/movies","MovieController@getMovies","getMovies")
    ->get("/api/movie/[i:id]","MovieController@getMovieById","getMovieById")
    ->get("/api/get-movies-in-shop/[i:id]","ShopController@getMoviesInShop","getMoviesInShop")
    ->get("/api/update-shop-path","ShopController@updateShopPath","updateShopPath")
    ->get("/api/get-history","MovieController@getHistory","getHistory")
    ->get("/api/ville","VilleController@getville","ville")
    ->post("/api/register","UserController@register","register")
    ->post("/api/confirm","UserController@confirm","confirm")
    ->post("/api/login","UserController@login","login")
    ->post("/api/forgot-password","UserController@forgotPassword","forgotPassword")
    ->post("/api/reset-password","UserController@resetPassword","resetPassword")
    ->post("/api/delete-shop","ShopController@deleteShop","deleteShop")
    ->post("/api/logout","UserController@logout","logout")
    ->run();
?>