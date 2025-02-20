<?php
namespace Controllers;

use Models\GetPDO;
use TeamTNT\TNTSearch\TNTSearch;
use Dotenv\Dotenv;
use Error;
use PDO;

class Search {
    private $tnt;
    private $config;

    public function __construct() { 
        
        // Charger les variables d'environnement
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // Configuration de TNTSearch
        $this->config = [
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'database'  => $_ENV['DB_NAME'],
            'username'  => $_ENV['DB_USER'],
            'password'  => $_ENV['DB_PASS'],
            'storage'   => dirname(__DIR__).DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR.'indexs'.DIRECTORY_SEPARATOR,
            'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class // optionnel
        ]; 
        $this->tnt = new TNTSearch();
        $this->tnt->loadConfig($this->config);
    }

    /**
     * Méthode principale de recherche
     * @param array $params - Paramètres de la route
     * @param string $routeName - Nom de la route
     * 
     * Les clés `query` et `type` doivent être présentes dans $_GET.
     * - `query` : La requête de recherche.
     * - `type` : Le type de recherche à effectuer. Les valeurs possibles sont :
     *   - 'p' : Recherche avec TNTSearch.
     *   - 'a' : Recherche en texte intégral.
     *   - 't' : Recherche sur l'API de The MovieDB.
     */
    public function search($params, $routeName) {
        $query = $_GET['query'] ?? '';
        $type = $_GET['type'] ?? '';

        switch ($type) {
            case 'p':
                return $this->tntSearch($query);
            case 'a':
                return $this->fullTextSearch($query);
            case 't':
                return $this->tmdbSearch($query);
            default:
                return $this->invalidType(); 
        }
    }

    /**
     * Crée l'index de recherche pour TNTSearch
     */
    public function createIndex() {
        $indexer = $this->tnt->createIndex('movie.index');
        $indexer->query('SELECT id, titre, description, contenu FROM movies');
        $indexer->run();

        echo json_encode(['success' => true, 'message' => 'Index created successfully']);
    }

    /**
     * Recherche avec TNTSearch
     * @param string $query - La requête de recherche
     */
    private function tntSearch($query) { 
        $userId = $_SESSION['id'] ?? null;

        $this->tnt->selectIndex('movie.index');
        $searchResp = $this->tnt->search($query);

        $bdd = GetPDO::getpdo();
        $ids = implode(',', $searchResp['ids']);
        
        if (!empty($ids)) {
            $request = $bdd->query("SELECT id, titre, miniature, contenu, source, date FROM movies WHERE id IN ($ids) ORDER BY FIELD(id, $ids)");
            $results = $request->fetchAll();
        } else {
            $results = [];
        }

        $firstQuery = $bdd->prepare('SELECT id, titre, miniature, contenu, source, date FROM movies WHERE MATCH(titre, contenu, description) AGAINST (:query IN NATURAL LANGUAGE MODE)'); 
        $firstQuery->execute(['query' => $query]);
        $fullTextResults = $firstQuery->fetchAll();

        $combinedResults = array_merge($results, $fullTextResults);

        $threeMonthsAgo = time() - (3 * 30 * 24 * 60 * 60); // Timestamp pour trois mois

        //check if shop is for this user
        $isAuth = false;
        if (isset($_GET['shop']) && $_GET['shop'] !== '' && isset($_SESSION['id'])) 
        {
            $isAuthRequest = $bdd->prepare('SELECT auth FROM shops WHERE auth = ? AND id = ?');
            $isAuthRequest->execute([$_SESSION['id'],$_GET['shop']]); 
            $isAuth = $isAuthRequest->fetch();
        }

        // get all movie.id on this ville if ville is selected
        $allMoviesOnThisTown = [];
        if (
            isset($_GET['ville']) && 
            $_GET['ville'] !== '' && 
            (!isset($_GET['shop']) || $_GET['shop'] === '')
            ) {

            // selectionner les movies.id dans movies qui movies.id is dans shop_movies.movie_id et shop_movies.shop_id
            $allShopOnThisTown = $bdd->prepare('SELECT id FROM shops WHERE ville = ?');
            $allShopOnThisTown->execute([$_GET['ville']]); 
            $allShop = $allShopOnThisTown->fetchAll(PDO::FETCH_COLUMN);
            if ($allShop && !empty($allShop)) {  

                $shopsFound = implode(',',array_map('intval', $allShop));  

                $allMoviesId = $bdd->query('SELECT movie_id FROM shop_movies WHERE shop_id IN (' . $shopsFound . ')'); 
                $allMoviesOnThisTown = $allMoviesId->fetchAll(PDO::FETCH_COLUMN);  
            }
        }

        $formattedResults = [];
        foreach ($combinedResults as $result) {
            $contenu = json_decode($result['contenu'], true);

            // Récupérer le nombre de votes durant les 3 derniers mois
            $likeCountQuery = $bdd->prepare('SELECT COUNT(*) as vote_count FROM votes WHERE movie_id = ? AND date >= ?');
            $likeCountQuery->execute([$result['id'], $threeMonthsAgo]);
            $likeCount = $likeCountQuery->fetchColumn();

            // Récupérer le nombre total de votes
            $totalLikeQuery = $bdd->prepare('SELECT COUNT(*) as total_vote_count FROM votes WHERE movie_id = ?');
            $totalLikeQuery->execute([$result['id']]);
            $totalLike = $totalLikeQuery->fetchColumn();

            // Vérifier si l'utilisateur actuel a voté pour cette série durant les 3 derniers mois
            $userLikeQuery = $bdd->prepare('SELECT COUNT(*) as user_vote_count FROM votes WHERE movie_id = ? AND user_id = ? AND date >= ?');
            $userLikeQuery->execute([$result['id'], $userId, $threeMonthsAgo]);
            $userLike = $userLikeQuery->fetchColumn() > 0;

            // vérifier si ce film se trouve dans cette boutique ou ville
            $address = false;
            if (isset($_GET['shop']) && $_GET['shop'] !== '') {  
                $checkInShop = $bdd->prepare('SELECT address FROM shop_movies WHERE movie_id = ? AND shop_id = ? LIMIT 1');
                $checkInShop->execute([$result['id'],$_GET['shop']]);
                $address = $checkInShop->fetch();   
            }
 
            $returnArray = [
                'id' => $result['id'],
                'titre' => $result['titre'],
                'miniature' => $result['miniature'],
                'vote' => $contenu['vote_average'],
                'date' => $result['date'],
                "idTmdb" => $result['source'],
                'like' => $userLike,
                'likeCount' => (int) $likeCount,
                'totLike' => (int) $totalLike
            ];

            if (isset($_GET["shop"])) {
                $returnArray["match"] = $address ? true : false;
                if ($address && $isAuth) { 
                    $returnArray["address"] = $address['address'];
                }
            }

            if (
                isset($_GET["ville"]) && 
                (!isset($_GET["shop"])) && 
                $allMoviesOnThisTown) {
                $returnArray["match"] = array_search($result['id'], $allMoviesOnThisTown) ? true : false;
            }

            $formattedResults[] = $returnArray;
        }

        echo json_encode($formattedResults);
    }

    /**
     * Recherche en texte intégral
     * @param string $query - La requête de recherche
     * 
     * La clé `found` peut être présente dans $_GET.
     * - `found` : Un tableau JSON contenant des IDs de films déjà trouvés. Ces IDs seront exclus des résultats de recherche.
     */
    private function fullTextSearch($query) { 
        $userId = $_SESSION['id'] ?? null;

        $bdd = GetPDO::getpdo();
        
        // Utiliser LIKE de SQL
        $firstQuery = $bdd->prepare('SELECT id, titre, miniature, contenu, source, date FROM movies WHERE (titre LIKE :query OR description LIKE :query OR contenu LIKE :query)');
        $firstQuery->execute(['query' => '%' . $query . '%']);
        $likeResults = $firstQuery->fetchAll(); 

        // Utiliser MATCH AGAINST avec un mode de recherche étendu
        $secondQuery = $bdd->prepare('SELECT id, titre, miniature, contenu, source, date FROM movies WHERE MATCH(titre, contenu, description) AGAINST (:query IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION)');
        $secondQuery->execute(['query' => $query]);
        $matchResults = $secondQuery->fetchAll();

        $combinedResults = array_merge($likeResults, $matchResults);

        $foundIds = isset($_GET['found']) && json_decode($_GET['found'], true) ? json_decode($_GET['found'], true) : []; 
        $filteredResults = array_filter($combinedResults, function($result) use ($foundIds) {
            return !in_array($result['id'], $foundIds);
        });

        $threeMonthsAgo = time() - (3 * 30 * 24 * 60 * 60); // Timestamp pour trois mois

         //check if shop is for this user
         $isAuth = false;
         if (isset($_GET['shop']) && $_GET['shop'] !== '' && isset($_SESSION['id'])) 
         {
             $isAuthRequest = $bdd->prepare('SELECT auth FROM shops WHERE auth = ? AND id = ?');
             $isAuthRequest->execute([$_SESSION['id'],$_GET['shop']]); 
             $isAuth = $isAuthRequest->fetch();
         }

         // get all movie.id on this ville if ville is selected
         $allMoviesOnThisTown = [];
         if (
             isset($_GET['ville']) && 
             $_GET['ville'] !== '' && 
             (!isset($_GET['shop']) || $_GET['shop'] === '')
             ) {
 
             // selectionner les movies.id dans movies qui movies.id is dans shop_movies.movie_id et shop_movies.shop_id
             $allShopOnThisTown = $bdd->prepare('SELECT id FROM shops WHERE ville = ?');
             $allShopOnThisTown->execute([$_GET['ville']]); 
             $allShop = $allShopOnThisTown->fetchAll(PDO::FETCH_COLUMN);
             if ($allShop && !empty($allShop)) {  
 
                 $shopsFound = implode(',',array_map('intval', $allShop));  
 
                 $allMoviesId = $bdd->query('SELECT movie_id FROM shop_movies WHERE shop_id IN (' . $shopsFound . ')'); 
                 $allMoviesOnThisTown = $allMoviesId->fetchAll(PDO::FETCH_COLUMN);  
             }
         }

        $formattedResults = [];
        foreach ($filteredResults as $result) {
            $contenu = json_decode($result['contenu'], true);

            // Récupérer le nombre de votes durant les 3 derniers mois
            $likeCountQuery = $bdd->prepare('SELECT COUNT(*) as vote_count FROM votes WHERE movie_id = ? AND date >= ?');
            $likeCountQuery->execute([$result['id'], $threeMonthsAgo]);
            $likeCount = $likeCountQuery->fetchColumn();

            // Récupérer le nombre total de votes
            $totalLikeQuery = $bdd->prepare('SELECT COUNT(*) as total_vote_count FROM votes WHERE movie_id = ?');
            $totalLikeQuery->execute([$result['id']]);
            $totalLike = $totalLikeQuery->fetchColumn();

            // Vérifier si l'utilisateur actuel a voté pour cette série durant les 3 derniers mois
            $userLikeQuery = $bdd->prepare('SELECT COUNT(*) as user_vote_count FROM votes WHERE movie_id = ? AND user_id = ? AND date >= ?');
            $userLikeQuery->execute([$result['id'], $userId, $threeMonthsAgo]);
            $userLike = $userLikeQuery->fetchColumn() > 0;

            // vérifier si ce film se trouve dans cette boutique ou ville
            $address = false;
            if (isset($_GET['shop']) && $_GET['shop'] !== '') {  
                $checkInShop = $bdd->prepare('SELECT address FROM shop_movies WHERE movie_id = ? AND shop_id = ? LIMIT 1');
                $checkInShop->execute([$result['id'],$_GET['shop']]);
                $address = $checkInShop->fetch();   
            }

            $returnArray = [
                'id' => $result['id'],
                'titre' => $result['titre'],
                'miniature' => $result['miniature'],
                'vote' => $contenu['vote_average'],
                'date' => $result['date'],
                "idTmdb" => $result['source'],
                'like' => $userLike,
                'likeCount' => (int) $likeCount,
                'totLike' => (int) $totalLike
            ];

            if (isset($_GET["shop"])) {
                $returnArray["match"] = $address ? true : false;
                if ($address && $isAuth) { 
                    $returnArray["address"] = $address['address'];
                }
            }

            if (
                isset($_GET["ville"]) && 
                (!isset($_GET["shop"])) && 
                $allMoviesOnThisTown) {
                $returnArray["match"] = array_search($result['id'], $allMoviesOnThisTown) ? true : false;
            }

           

            $formattedResults[] =  $returnArray;
        }

        echo json_encode($formattedResults);
    }

    /**
     * Recherche sur l'API de The MovieDB
     * @param string $query - La requête de recherche
     * 
     * La clé `found` peut être présente dans $_GET.
     * - `found` : Un tableau JSON contenant des IDs de films déjà trouvés. Ces IDs seront exclus des résultats de recherche.
     */
    private function tmdbSearch($query) {
        $url = 'https://api.themoviedb.org/3/search/multi?query=' . urlencode($query) . '&include_adult=false&language=fr&api_key=' . $_ENV['TMDB_API_KEY'];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        if ($response === false) {  
            echo json_encode([]);
            return; 
        }
        curl_close($curl);

        $results = json_decode($response, true)['results'];
        $formattedResults = [];

        $foundIds = $_GET['found'] && json_decode($_GET['found'], true) ? json_decode($_GET['found'], true) : []; 
        foreach ($results as $result) {
            if (!in_array($result['id'], $foundIds) && isset($result['backdrop_path'])) {
                $formattedResults[] = [
                    'id' => $result['id'],
                    'titre' => $result['title'] ?? $result['name'],
                    'miniature' => 'https://image.tmdb.org/t/p/w342' . $result['backdrop_path'],
                    'description' => $result['overview'],
                    'vote' => $result['vote_average'],
                    'date' => $result['release_date'] ?? $result['first_air_date'],
                    'type' => $result['media_type'] ?? "movie",
                    
                ];
            }
        } 
        
        echo json_encode($formattedResults);
    }

    /**
     * Affiche un message d'erreur pour un type de recherche invalide
     */
    private function invalidType() {
        echo 'Invalid search type';
    }
}
?>
