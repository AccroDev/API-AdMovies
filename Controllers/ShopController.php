<?php
namespace Controllers;

use Models\GetPDO;
use Dotenv\Dotenv;
use PDO;

class ShopController {
    public function __construct() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }

    /**
     * Crée ou met à jour une boutique de transfert des films
     */
    public function createShop() {
        $userId = $_SESSION['id'] ?? null;

        if (!$userId) {
            echo json_encode(['statut' => false, 'message' => 'Please log in']);
            return;
        }

        $shopId = $_POST['shop_id'] ?? null;
        $name = $_POST['name'] ?? null;
        $miniature = $_FILES['miniature'] ?? null;
        $ville = $_POST['ville'] ?? null;
        $phoneNumber = $_POST['phone_number'] ?? null;
        $address = $_POST['address'] ?? null;
        $prixSaison = $_POST['prixSaison'] ?? null;
        $prixFilm = $_POST['prixFilm'] ?? null;

        if (!$name || !$ville || !$phoneNumber || !$address) {
            echo json_encode(['statut' => false, 'message' => 'Missing required fields']);
            return;
        }

        $bdd = GetPDO::getpdo();

        if ($shopId) {
            // Mettre à jour la boutique existante
            if ($miniature && $miniature['size'] > 0) {
                // Enregistrer la nouvelle image
                $targetDir = dirname(__DIR__) . '/Views/img/shops/';
                $targetFile = $targetDir . basename($miniature['name']);
                if (!move_uploaded_file($miniature['tmp_name'], $targetFile)) {
                    echo json_encode(['statut' => false, 'message' => 'Failed to upload image']);
                    return;
                }
                $miniaturePath = $miniature['name'];
            } else {
                // Garder l'ancienne image
                $query = $bdd->prepare('SELECT miniature FROM shops WHERE id = ?');
                $query->execute([$shopId]);
                $miniaturePath = $query->fetchColumn();
            }

            $updateQuery = $bdd->prepare('UPDATE shops SET name = ?, miniature = ?, ville = ?, phone_number = ?, address = ?, prixFilm = ?, prixSaison = ? WHERE id = ? AND auth = ?');
            $result = $updateQuery->execute([$name, $miniaturePath, $ville, $phoneNumber, $address, $prixFilm, $prixSaison, $shopId, $userId]);

            if ($result) {
                echo json_encode(['statut' => true, 'message' => 'Shop updated successfully']);
            } else {
                echo json_encode(['statut' => false, 'message' => 'Failed to update shop']);
            }
            return;
        } else {
            // Créer une nouvelle boutique
            if ($miniature) {
                // Enregistrer l'image
                $targetDir = dirname(__DIR__) . '/Views/img/shops/';
                $targetFile = $targetDir . basename($miniature['name']);
                if (!move_uploaded_file($miniature['tmp_name'], $targetFile)) {
                    echo json_encode(['statut' => false, 'message' => 'Failed to upload image']);
                    return;
                }
                $miniaturePath = $miniature['name'];
            } else {
                $miniaturePath = null;
            }

            $insertQuery = $bdd->prepare('INSERT INTO shops (name, miniature, ville, phone_number, address, date, auth, visibility, prixFilm, prixSaison) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $result = $insertQuery->execute([
                $name,
                $miniaturePath,
                $ville,
                $phoneNumber,
                $address,
                time(),
                $userId,
                1, // Par défaut, la visibilité est à 1
                $prixFilm,
                $prixSaison
            ]);

            if ($result) {
                echo json_encode(['statut' => true, 'message' => 'Shop created successfully']);
            } else {
                echo json_encode(['statut' => false, 'message' => 'Failed to create shop']);
            }
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
    public function getUserShops() {
        
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
                'date' => date('d-m-Y', strtotime($shop['date'])),
                'prixFilm' => $shop['prixFilm'],
                'prixSaison' => $shop['prixSaison']
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

    /**
     * Vérifie si un film se trouve dans les boutiques de l'utilisateur connecté
     * @param array $params - Paramètres de la route
     */
    public function getMoviesInShop($params) {
        $movieId = $params['id'] ?? null;
        $userId = $_SESSION['id'] ?? null;

        if (!$movieId || !$userId) {
            echo json_encode(['statut' => false, 'message' => 'Missing movie ID or user ID']);
            return;
        }

        $bdd = GetPDO::getpdo();

        // Récupérer les ID des boutiques de l'utilisateur
        $query = $bdd->prepare('SELECT id FROM shops WHERE auth = ?');
        $query->execute([$userId]);
        $shops = $query->fetchAll();

        $formattedResults = []; 
        foreach ($shops as $shop) {
            
            $sq = $bdd->prepare('SELECT address FROM shop_movies WHERE shop_id = ? AND movie_id = ?');
            $sq->execute([$shop['id'], $movieId]); 
            $movieAdress = $sq->fetch();

            if ($movieAdress) {
                $formattedResults[$shop['id']] = [ 
                    'exist' => true,
                    'path' => $movieAdress['address'] ?? ""
                ];
            }else {
                $formattedResults[$shop['id']] = [ 
                    'exist' => false,
                    'path' => ""
                ];
            }
        }  
        echo json_encode($formattedResults);
    }

    /**
     * Recuper les films d'une boutique
     */

    public function getMovieIdShop() {

        $shopId = $_GET['shop'] ?? null;

        if (!$shopId) {
            echo json_encode(['statut' => false, 'message' => 'Missing shop ID']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT movie_id, address FROM shop_movies WHERE shop_id = ?');
        $query->execute([$shopId]);
        $movies = $query->fetchAll();

        //check if user is auth of this shop
        $isAuth = false;
        if (isset($_SESSION['id'])) { 
            $isAuthRequest = $bdd->prepare('SELECT auth FROM shops WHERE auth = ? AND id = ?');
            $isAuthRequest->execute([$_SESSION['id'], $shopId]); 
            $isAuth = $isAuthRequest->fetch(); 
        }

        $formattedResults = [];
        foreach ($movies as $movie) {
            $result = [
                "id" => $movie['movie_id']
            ];
            if ($isAuth && $isAuth !== false) {
                $result["path"] = $movie['address'];
            }

            $formattedResults[] = $result;
        }

        echo json_encode($formattedResults);
    }

    public function updateShopPath()
    {
        $movieId = $_GET['movie'] ?? null; 
        $address = $_GET['address'] ?? null; 
        $shopId = $_GET['shop'] ?? null; 

        if (!$movieId || !$shopId) {
            echo json_encode(['statut' => false, 'message' => 'Missing movie ID or shop ID']);
            return;
        }

        $bdd = GetPDO::getpdo();
        if (!$address || $address === '') {
            //delete movie from this shop
            $query = $bdd->prepare('DELETE FROM shop_movies WHERE shop_id = ? AND movie_id = ?');
            $query->execute([$shopId, $movieId]);
            echo json_encode(['statut' => true, 'message' => 'Movie removed from shop successfully']);
            return;
        }

        // count if movie exist in this shop
        $query = $bdd->prepare('SELECT id FROM shop_movies WHERE shop_id = ? AND movie_id = ?');
        $query->execute([$shopId, $movieId]);
        $shopMovie = $query->fetch();
        if ($shopMovie) {
            # update path
            $query = $bdd->prepare('UPDATE shop_movies SET address = ? WHERE shop_id = ? AND movie_id = ?');
            $query->execute([$address, $shopId, $movieId]);
            echo json_encode(['statut' => true, 'message' => 'Movie path updated successfully']);
        } else {
            # add movie to shop
            $query = $bdd->prepare('INSERT INTO shop_movies (shop_id, movie_id, added_at, address) VALUES (?, ?, ?, ?)');
            $query->execute([$shopId, $movieId, date('Y-m-d'), $address]);
            echo json_encode(['statut' => true, 'message' => 'Movie added to shop successfully']);
            return;
        } 
    }

    /**
     * Récupère les boutiques d'une ville spécifique qui ont un film spécifique
     */
    public function getMovieShopsByVille() {
        $ville = $_GET['ville'] ?? null;
        $movieId = $_GET['movie'] ?? null;

        if (!$ville || !$movieId) {
            echo json_encode(['statut' => false, 'message' => 'Missing ville or movie ID']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT shops.id, shops.name, shops.miniature, shops.ville, shops.phone_number, shops.address, shops.date FROM shops LEFT JOIN shop_movies ON shops.id = shop_movies.shop_id WHERE shops.ville = ? AND shop_movies.movie_id = ?');
        $query->execute([$ville, $movieId]);
        $shops = $query->fetchAll(PDO::FETCH_ASSOC);

        $formattedResults = [];
        foreach ($shops as $shop) {
            $formattedResults[] = [
                'id' => $shop['id'],
                'name' => $shop['name'],
                'miniature' => '/Views/img/shops/' . $shop['miniature'],
                'ville' => $shop['ville'],
                'phone_number' => $shop['phone_number'],
                'address' => $shop['address'],
                'date' => date('d-m-Y', strtotime($shop['date']))
            ];
        }

        echo json_encode($formattedResults);
    }

    /**
     * Récupère toutes les boutiques dans une ville spécifique
     */
    public function getShopsByVille() {
        $ville = $_GET['ville'] ?? null;

        if (!$ville) {
            echo json_encode(['statut' => false, 'message' => 'Missing ville']);
            return;
        }

        $bdd = GetPDO::getpdo();
        $query = $bdd->prepare('SELECT id, name, miniature, ville, phone_number, address, date, auth FROM shops WHERE ville = ?');
        $query->execute([$ville]);
        $shops = $query->fetchAll(PDO::FETCH_ASSOC);

        $formattedResults = [];
        foreach ($shops as $shop) {
            $formattedResults[] = [
                'id' => $shop['id'],
                'name' => $shop['name'],
                'miniature' => '/Views/img/shops/' . $shop['miniature'],
                'ville' => $shop['ville'],
                'phone_number' => $shop['phone_number'],
                'address' => $shop['address'],
                'auth' => $shop['auth'],
                'date' => date('d-m-Y', strtotime($shop['date']))
            ];
        }

        echo json_encode($formattedResults);
    }
}
?>
