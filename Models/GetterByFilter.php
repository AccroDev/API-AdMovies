<?php
namespace Models;
use PDO;

class GetterByFilter {
    /**
     * Récupère les films par différents filtres
     * @param array $filters - Les filtres à appliquer
     * @return array - Les films filtrés
     */
    public static function getMoviesByFilters(array $filters) {
        $bdd = GetPDO::getpdo();
        $query = 'SELECT movies.id, movies.titre, movies.miniature, movies.description, movies.contenu, shops.ville, shops.name as shop_name FROM movies LEFT JOIN shop_movies ON movies.id = shop_movies.movie_id LEFT JOIN shops ON shop_movies.shop_id = shops.id WHERE 1=1';
        $params = [];

        if (isset($filters['type'])) {
            if ($filters['type'] === 'film' || $filters['type'] === 'serie') {
                $query .= ' AND movies.categori = :type';
                $params['type'] = $filters['type'];
            }
        }

        if (isset($filters['ville'])) {
            $query .= ' AND shops.ville = :ville';
            $params['ville'] = $filters['ville'];
        }

        if (isset($filters['shop'])) {
            $query .= ' AND shops.name = :shop';
            $params['shop'] = $filters['shop'];
        }

        $stmt = $bdd->prepare($query);
        $stmt->execute($params);
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($filters['accept_filters'])) {
            foreach ($movies as &$movie) {
                $movie['accept_filters'] = true;
            }
        }

        return $movies;
    }
}
?>
