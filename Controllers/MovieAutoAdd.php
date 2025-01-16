<?php
namespace Controllers;

use Models\GetPDO;
use Dotenv\Dotenv;
use PDO;
use TeamTNT\TNTSearch\TNTSearch;

class MovieAutoAdd {
    private $tnt;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();

        // Configuration de TNTSearch
        $config = [
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'database'  => $_ENV['DB_NAME'],
            'username'  => $_ENV['DB_USER'],
            'password'  => $_ENV['DB_PASS'],
            'storage'   => dirname(__DIR__).DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR.'indexs'.DIRECTORY_SEPARATOR,
            'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class // optionnel
        ]; 
        $this->tnt = new TNTSearch();
        $this->tnt->loadConfig($config);
    }

    public function addMovie() {
        $movieId = $_GET['id'] ?? null;
        $category = $_GET['category'] ?? null;

        if (!$movieId || !$category) {
            echo json_encode(['statut' => false, 'message' => 'Missing movie ID or category']);
            return;
        }

        $category = $category === 'movie' ? 'film' : 'serie';
       
        $url = $category === 'film' 
            ? 'https://api.themoviedb.org/3/movie/' . $movieId . '?api_key=' . $_ENV['TMDB_API_KEY'] . '&language=fr'
            : 'https://api.themoviedb.org/3/tv/' . $movieId . '?api_key=' . $_ENV['TMDB_API_KEY'] . '&language=fr';

        $bdd = GetPDO::getpdo();
        $checkQuery = $bdd->prepare('SELECT COUNT(*) FROM movies WHERE source = ?');
        $checkQuery->execute([$movieId]);
        $exists = (int) $checkQuery->fetchColumn();

        if ($exists > 0) {
            echo json_encode(['statut' => false, 'message' => 'Movie already exists']);
            return;
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        if ($response === false) {
            echo json_encode(['statut' => false, 'message' => 'Erreur lors de la requête cURL: ' . curl_error($curl)]);
            return;
        }
        curl_close($curl);

        $movieData = json_decode($response, true);

        if (isset($movieData['success']) && $movieData['success'] === false) {
            echo json_encode(['statut' => false, 'message' => 'Movie not found']);
            return;
        }

        $insertQuery = $bdd->prepare('INSERT INTO movies (titre, date, updateDate, contenu, auteur, categori, visibilite, miniature, source, adress, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $result = $insertQuery->execute([
            $movieData['title'] ?? $movieData['name'],
            $movieData['release_date'] ?? $movieData['first_air_date'],
            date('Y-m-d H:i:s'),
            json_encode($movieData),
            isset($_SESSION["id"]) ?? 'admin', // Assuming 'admin' as the author
            $category,
            1, 
            'https://image.tmdb.org/t/p/w342' . $movieData['backdrop_path'],
            $movieId,
            '',
            $movieData['overview']
        ]);

        if ($result) {  
            echo json_encode(['statut' => true]);
        } else {
            echo json_encode(['statut' => false , 'message' => 'Failed to add movie']);
        }
    }

    public function insertTNTindex () 
    {
        $tntID = $_GET['tntID'] ?? null; 

        if (!$tntID) {
            echo json_encode(['statut' => false, 'message' => 'Missing tnt ID']);
            return;
        } 

        $bdd = GetPDO::getpdo();
        $checkQuery = $bdd->prepare('SELECT id, titre, description, contenu FROM movies WHERE source = ?');
        $checkQuery->execute([$tntID]); 
        $movieData = $checkQuery->fetch(PDO::FETCH_ASSOC);

        if (!$movieData) {
            echo json_encode(['statut' => false, 'message' => 'Movie not found']);
            return;
        }

        $this->tnt->selectIndex('movie.index');
        $indexer = $this->tnt->getIndex();
        $indexer->insert([
            'id' => $movieData['id'],
            'titre' => $movieData['titre'],
            'description' => $movieData['description'],
            'contenu' => $movieData['contenu']
        ]); 
        echo json_encode(['statut' => true]);

    }
}
?>