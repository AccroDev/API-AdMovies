<?php

use Controllers\Router;
use Controllers\UserController;
use Dotenv\Dotenv;
require "vendor/autoload.php";

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load(); 
 
session_start();   

header("Access-Control-Allow: *");
$routeur = new Router("Controllers");
 

error_reporting(E_ALL);
set_error_handler('Controllers\Errors::error');
set_exception_handler('Controllers\Errors::error'); 
  

$routeur   

    //search
    ->get("/api/search","Search@search","search")
    ->get("/api/tntAdd","MovieAutoAdd@insertTNTindex","insertTNTindex")
    ->get("/api/create-index","Search@createIndex","createIndex")

    // recommendation
    ->get("/api/top-recommended","SeriesVote@getTopRecommended","topRecommended")
    ->get("/api/vote/[i:id]","SeriesVote@voteOrDevote","voteOrDevote")

    //movie 
    ->get("/api/add-movie","MovieAutoAdd@addMovie","addMovie")
    ->get("/api/update-shop-path","ShopController@updateShopPath","updateShopPath")
    ->get("/api/movies","MovieController@getMovies","getMovies")
    ->get("/api/movie/[i:id]","MovieController@getMovieById","getMovieById")
    ->get("/api/get-movies-in-shop/[i:id]","ShopController@getMoviesInShop","getMoviesInShop")

    //shop
    ->get("/api/addShopMovie","ShopController@addShopMovie","addShopMovie")
    ->post("/api/create-shop","ShopController@createShop","createShop") 
    ->get("/api/get/[i:id]","ShopController@getShopMovies","getShopMovies") 
    ->get("/api/get-shops","ShopController@getShops","getShops")
    ->get("/api/get-user-shops","ShopController@getUserShops","getUserShops")//st
    ->get("/api/getMovieIdShop","ShopController@getMovieIdShop","getMovieIdShop")
    ->get("/api/movie-shops-by-ville","ShopController@getMovieShopsByVille","getMovieShopsByVille")
    ->get("/api/get-shops-by-ville","ShopController@getShopsByVille","getShopsByVille")
    ->post("/api/delete-shop","ShopController@deleteShop","deleteShop")


    //user
    ->get("/api/get-history","MovieController@getHistory","getHistory")
    ->post("/api/register","UserController@register","register")
    ->post("/api/confirm","UserController@confirm","confirm")
    ->post("/api/login","UserController@login","login")
    ->post("/api/forgot-password","UserController@forgotPassword","forgotPassword")
    ->post("/api/reset-password","UserController@resetPassword","resetPassword")
    ->post("/api/update-avatar","UserController@updateAvatar","updateAvatar")
    ->post("/api/logout","UserController@logout","logout")
    ->get("/api/premiumSubscribe","UserController@premiumSubscribe","premiumSubscribe")

    //download movies
    ->get("/api/get-download-movie","Download@getDownloadMovie","getDownloadMovie")
    ->get("/download/[*:slug]-[i:id]","Download@download","download")
    ->get("/watch/[i:id]/[*:slug]","Download@watch","watch")

    //others
    ->get("/api/allVilles","MovieController@getAllVilles","getAllVilles")
    ->post("/api/getSubscribe","ApiAll@getSubscribe","getSubscribe")
    ->post("/api/post-comment","Commentaires@postComment","postComment")
    ->get("/comments/[i:id]","Commentaires@getComments","getComments")
    ->run();
?>