<?php
namespace Controllers;

use Models\GetPDO;
use Dotenv\Dotenv;

class ShopController {
    public function __construct() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }

    /**
     * Crée une boutique de transfert des films
     */
    public function createShop() {
        
        $userId = $_SESSION['id'] ?? null;

        if (!$userId) {
            echo json_encode(['statut' => false, 'message' => 'Please log in']);
            return;
        }

        $name = $_POST['name'] ?? null;
        $miniature = $_FILES['miniature'] ?? null;
        $ville = $_POST['ville'] ?? null;
        $phoneNumber = $_POST['phone_number'] ?? null;
        $address = $_POST['address'] ?? null;
        
        if (!$name || !$miniature || !$ville || !$phoneNumber || !$address) {
            echo json_encode(['statut' => false, 'message' => 'Missing required fields']);
            return;
        }

        // Enregistrer l'image
        $targetDir = dirname(__DIR__) . '/Views/img/shops/';
        $targetFile = $targetDir . basename($miniature['name']);
        if (!move_uploaded_file($miniature['tmp_name'], $targetFile)) {
            echo json_encode(['statut' => false, 'message' => 'Failed to upload image']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $insertQuery = $bdd->prepare('INSERT INTO shops (name, miniature, ville, phone_number, address, date, auth, visibility) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $result = $insertQuery->execute([
            $name,
            $miniature['name'],
            $ville,
            $phoneNumber,
            $address,
            time(),
            $userId,
            1 // Par défaut, la visibilité est à 1
        ]);

        if ($result) {
            echo json_encode(['statut' => true, 'message' => 'Shop created successfully']);
        } else {
            echo json_encode(['statut' => false, 'message' => 'Failed to create shop']);
        }
    }

    /**
     * Ajoute ou supprime un film dans une boutique
     */
    public function addShopMovie() {
        $movieId = $_GET['movie'] ?? null;
        $shopId = $_GET['shop'] ?? null;
        $address = $_GET['address'] ?? null;

        if (!$movieId || !$shopId) {
            echo json_encode(['statut' => false, 'message' => 'Missing movie ID or shop ID']);
            return;
        }

        $bdd = GetPDO::getpdo();

        // Vérifier si le film est déjà dans la boutique
        $checkQuery = $bdd->prepare('SELECT id FROM shop_movies WHERE shop_id = ? AND movie_id = ?');
        $checkQuery->execute([$shopId, $movieId]);
        $shopMovie = $checkQuery->fetch();

        if ($shopMovie) {
            // Supprimer le film de la boutique
            $deleteQuery = $bdd->prepare('DELETE FROM shop_movies WHERE id = ?');
            $result = $deleteQuery->execute([$shopMovie['id']]);

            if ($result) {
                echo json_encode(['statut' => true, 'message' => 'Movie removed from shop successfully']);
            } else {
                echo json_encode(['statut' => false, 'message' => 'Failed to remove movie from shop']);
            }
        } else {
            // Ajouter le film à la boutique
            $insertQuery = $bdd->prepare('INSERT INTO shop_movies (shop_id, movie_id, added_at, address) VALUES (?, ?, ?, ?)');
            $result = $insertQuery->execute([$shopId, $movieId, date('Y-m-d H:i:s'), $address]);

            if ($result) {
                echo json_encode(['statut' => true, 'message' => 'Movie added to shop successfully']);
            } else {
                echo json_encode(['statut' => false, 'message' => 'Failed to add movie to shop']);
            }
        }
    }

    /**
     * Récupère les séries ou films d'une boutique en particulier
     * @param array $params - Paramètres de la route
     */
    public function getShopMovies($params) {
        $shopId = $params['id'] ?? null;
        $type = $_GET['type'] ?? null;

        if (!$shopId) {
            echo json_encode(['statut' => false, 'message' => 'Missing shop ID']);
            return;
        }

        $bdd = GetPDO::getpdo();
        if ($type) {
            $query = $bdd->prepare('SELECT movies.id, movies.titre, movies.miniature, movies.description, movies.contenu FROM shop_movies JOIN movies ON shop_movies.movie_id = movies.id WHERE shop_movies.shop_id = ? AND movies.categori = ?');
            $query->execute([$shopId, $type]);
        } else {
            $query = $bdd->prepare('SELECT movies.id, movies.titre, movies.miniature, movies.description, movies.contenu FROM shop_movies JOIN movies ON shop_movies.movie_id = movies.id WHERE shop_movies.shop_id = ?');
            $query->execute([$shopId]);
        }
        $movies = $query->fetchAll();

        $formattedResults = [];
        foreach ($movies as $movie) {
            $contenu = json_decode($movie['contenu'], true);
            $formattedResults[] = [
                'id' => $movie['id'],
                'titre' => $movie['titre'],
                'miniature' => $movie['miniature'],
                'description' => $movie['description'],
                'vote' => $contenu['vote_average']
            ];
        }

        echo json_encode($formattedResults);
    }

    /**
     * Récupère toutes les boutiques d'un utilisateur
     */
    public function getShops() {
        
        $userId = $_SESSION['id'] ?? null;

        if (!$userId) {
            //echo json_encode(['statut' => false, 'message' => 'Please log in']);
            echo json_encode([]);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT * FROM shops WHERE auth = ?');
        $query->execute([$userId]);
        $shops = $query->fetchAll();

        $allShops = [];
        foreach ($shops as $shop) {
            $allShops[] = [
                'id' => $shop['id'],
                'name' => $shop['name'],
                'miniature' => '/Views/img/shops/' . $shop['miniature'],
                'ville' => $shop['ville'],
                'phone_number' => $shop['phone_number'],
                'address' => $shop['address'],
                'date' => date('d-m-Y', strtotime($shop['date']))
            ];
        }

        echo json_encode($allShops);
    }

    /**
     * Récupère toutes les boutiques dans une ville donnée qui ont une série ou un film spécifique
     * @param string $ville - La ville à rechercher
     * @param string $type - Le type de contenu (film ou serie)
     * @param int $movieId - L'ID du film ou de la série
     * @return array - Les boutiques filtrées
     */
    public function getShopsByVilleAndMovie($ville, $type, $movieId) {
        $bdd = GetPDO::getpdo();
        $query = 'SELECT shops.id, shops.name, shops.miniature, shops.ville, shops.phone_number, shops.address, shops.date FROM shops LEFT JOIN shop_movies ON shops.id = shop_movies.shop_id LEFT JOIN movies ON shop_movies.movie_id = movies.id WHERE shops.ville = :ville AND movies.categori = :type AND movies.id = :movieId';
        $params = [
            'ville' => $ville,
            'type' => $type,
            'movieId' => $movieId
        ];

        $stmt = $bdd->prepare($query);
        $stmt->execute($params);
        $shops = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($shops);
    }

    /**
     * Supprime une boutique
     */
    public function deleteShop() {
        $shopId = $_POST['shop_id'] ?? null;

        if (!$shopId) {
            echo json_encode(['statut' => false, 'message' => 'Missing shop ID']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $deleteQuery = $bdd->prepare('DELETE FROM shops WHERE id = ?');
        $result = $deleteQuery->execute([$shopId]);

        if ($result) {
            echo json_encode(['statut' => true, 'message' => 'Shop deleted successfully']);
        } else {
            echo json_encode(['statut' => false, 'message' => 'Failed to delete shop']);
        }
    }
}
?>
