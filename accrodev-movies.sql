-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 13 jan. 2025 à 13:30
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `accrodev-movies`
--

-- --------------------------------------------------------

--
-- Structure de la table `movies`
--

DROP TABLE IF EXISTS `movies`;
CREATE TABLE IF NOT EXISTS `movies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `date` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `updateDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `auteur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `categori` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `visibilite` int NOT NULL DEFAULT '1',
  `miniature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `adress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=682 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `movies`
--

INSERT INTO `movies` (`id`, `titre`, `date`, `updateDate`, `contenu`, `auteur`, `categori`, `visibilite`, `miniature`, `source`, `adress`, `description`) VALUES
(680, 'Prison Break', '2005-08-29', '2025-01-13 09:44:06', '{\"adult\":false,\"backdrop_path\":\"\\/7w165QdHmJuTHSQwEyJDBDpuDT7.jpg\",\"created_by\":[{\"id\":54869,\"credit_id\":\"52572f17760ee3776a320b04\",\"name\":\"Paul T. Scheuring\",\"original_name\":\"Paul T. Scheuring\",\"gender\":2,\"profile_path\":\"\\/2hNYumD0sMCDezKvTtDwginHP4L.jpg\"}],\"episode_run_time\":[45],\"first_air_date\":\"2005-08-29\",\"genres\":[{\"id\":10759,\"name\":\"Action & Adventure\"},{\"id\":80,\"name\":\"Crime\"},{\"id\":18,\"name\":\"Drame\"}],\"homepage\":\"http:\\/\\/www.fox.com\\/prisonbreak\",\"id\":2288,\"in_production\":false,\"languages\":[\"en\"],\"last_air_date\":\"2017-05-30\",\"last_episode_to_air\":{\"id\":1303881,\"name\":\"Le Dernier Plan\",\"overview\":\"Pos\\u00e9idon retient toujours son fils mais Michael met en ex\\u00e9cution le plan qu\'il pr\\u00e9pare depuis 7 ans pour faire tomber celui qui l\'a pi\\u00e9g\\u00e9\\u2026\",\"vote_average\":8.1,\"vote_count\":32,\"air_date\":\"2017-05-30\",\"episode_number\":9,\"episode_type\":\"finale\",\"production_code\":\"1AZM09\",\"runtime\":45,\"season_number\":5,\"show_id\":2288,\"still_path\":\"\\/d21YkZrWDPGXzqvM4PVqWwU0vJN.jpg\"},\"name\":\"Prison Break\",\"next_episode_to_air\":null,\"networks\":[{\"id\":19,\"logo_path\":\"\\/1DSpHrWyOORkL9N2QHX7Adt31mQ.png\",\"name\":\"FOX\",\"origin_country\":\"US\"}],\"number_of_episodes\":88,\"number_of_seasons\":5,\"origin_country\":[\"US\"],\"original_language\":\"en\",\"original_name\":\"Prison Break\",\"overview\":\"Michael Scofield s\'engage dans une v\\u00e9ritable lutte contre la montre : son fr\\u00e8re Lincoln est dans le couloir de la mort, en attente de son ex\\u00e9cution. Persuad\\u00e9 de son innocence mais \\u00e0 court de solutions, Michael d\\u00e9cide de se faire incarc\\u00e9rer \\u00e0 son tour dans le p\\u00e9nitencier d\'\\u00e9tat de Fox River pour organiser leur \\u00e9vasion...\",\"popularity\":872.638,\"poster_path\":\"\\/6chvwfFcJPE97lhFwuQzhldgsee.jpg\",\"production_companies\":[{\"id\":22548,\"logo_path\":null,\"name\":\"Adelstein-Parouse Productions\",\"origin_country\":\"US\"},{\"id\":1556,\"logo_path\":\"\\/31h94rG9hzjprXoYNy3L1ErUya2.png\",\"name\":\"20th Century Fox Television\",\"origin_country\":\"US\"},{\"id\":12007,\"logo_path\":null,\"name\":\"RAT Entertainment\",\"origin_country\":\"US\"},{\"id\":210499,\"logo_path\":null,\"name\":\"Dawn Olmstead Productions\",\"origin_country\":\"US\"},{\"id\":210500,\"logo_path\":null,\"name\":\"Adelstein Productions\",\"origin_country\":\"US\"},{\"id\":210501,\"logo_path\":null,\"name\":\"One Light Road Productions\",\"origin_country\":\"US\"},{\"id\":333,\"logo_path\":\"\\/5xUJfzPZ8jWJUDzYtIeuPO4qPIa.png\",\"name\":\"Original Film\",\"origin_country\":\"US\"}],\"production_countries\":[{\"iso_3166_1\":\"US\",\"name\":\"United States of America\"}],\"seasons\":[{\"air_date\":\"2005-10-11\",\"episode_count\":10,\"id\":7135,\"name\":\"\\u00c9pisodes sp\\u00e9ciaux\",\"overview\":\"\",\"poster_path\":\"\\/r4I83zsnxdkoyrVcYpnmgT9EcUD.jpg\",\"season_number\":0,\"vote_average\":0},{\"air_date\":\"2005-08-29\",\"episode_count\":22,\"id\":7132,\"name\":\"Saison 1\",\"overview\":\"Lincoln Burrows est accus\\u00e9 du meurtre de Terrence Steadman, qui est le fr\\u00e8re de la vice-pr\\u00e9sidente des \\u00c9tats-Unis. Les preuves \\u00e9tant accablantes, il est d\\u00e9clar\\u00e9 coupable de meurtre au premier degr\\u00e9 avec pr\\u00e9m\\u00e9ditation et condamn\\u00e9 \\u00e0 mort. Il est incarc\\u00e9r\\u00e9 dans la prison de Fox River pour attendre son ex\\u00e9cution. Le fr\\u00e8re de Lincoln, Michael Scofield, est convaincu de son innocence et con\\u00e7oit un ing\\u00e9nieux plan d\'\\u00e9vasion. Apr\\u00e8s avoir lui-m\\u00eame provoqu\\u00e9 son incarc\\u00e9ration \\u00e0 Fox River, c\'est une course contre la montre qui s\'engage pour Michael, qui doit affronter de nombreux obstacles et \\u00e9tablir de bons rapports avec les d\\u00e9tenus et le personnel de la prison pour r\\u00e9ussir \\u00e0 s\'\\u00e9vader avec son fr\\u00e8re. Les deux h\\u00e9ros sont aid\\u00e9s par Veronica Donovan, l\'avocate et ex-petite amie de Lincoln, qui commence \\u00e0 enqu\\u00eater sur la conspiration qui a envoy\\u00e9 Lincoln en prison.\",\"poster_path\":\"\\/bp2ZuaxdNwVlCuTGVOEwEnnkRcf.jpg\",\"season_number\":1,\"vote_average\":8.7},{\"air_date\":\"2006-08-21\",\"episode_count\":22,\"id\":7133,\"name\":\"Saison 2\",\"overview\":\"L\'histoire commence quelques heures apr\\u00e8s l\'\\u00e9vasion de la prison de Fox River et se concentre sur les principaux fugitifs. L\'agent f\\u00e9d\\u00e9ral Alexander Mahone, est charg\\u00e9 de traquer et de capturer les huit \\u00e9vad\\u00e9s. Les fugitifs poursuivent leur voyage \\u00e0 travers les villes des \\u00c9tats-Unis en ayant constamment les autorit\\u00e9s \\u00e0 leurs trousses tandis que chacun poursuit son propre objectif. Pendant ce temps, l\'histoire autour de la conspiration se d\\u00e9veloppe, les membres du Cartel continuent leur plan pour trouver et \\u00e9liminer Lincoln Burrows ainsi que tous ceux qui se mettent en travers de leur chemin.\",\"poster_path\":\"\\/ml6hUmejWFMuPOnm7axHYRT1Lrc.jpg\",\"season_number\":2,\"vote_average\":8.6},{\"air_date\":\"2007-09-17\",\"episode_count\":13,\"id\":7134,\"name\":\"Saison 3\",\"overview\":\"Michael, Bellick, T-Bag et Mahone se retrouvent derri\\u00e8re les barreaux \\u00e0 Sona, une prison de haute s\\u00e9curit\\u00e9 au Panama. Sara Tancredi et LJ Burrows se sont fait capturer par le Cartel. Pour d\\u00e9livrer son neveu et sa petite amie, Michael doit faire sortir de prison James Whistler, qui a l\'air d\'avoir des liens avec l\'organisation. Michael doit donc organiser un nouveau plan d\'\\u00e9vasion, mais cette fois, il n\'a rien pr\\u00e9vu, et la prison est beaucoup plus prot\\u00e9g\\u00e9e ext\\u00e9rieurement que celle de Fox River. Le chef de cette prison est un prisonnier, Lechero. Il surveille ce qui se passe dans la prison et a \\u00e9tabli des r\\u00e8gles que les d\\u00e9tenus se retrouvent contraints \\u00e0 suivre. Car dans cette prison, il n\'y a aucune police, les prisonniers sont seuls, entre eux-m\\u00eames : les gardes se contentent en effet de surveiller le p\\u00e9rim\\u00e8tre ext\\u00e9rieur, depuis une \\u00e9meute qui se serait produite un an avant l\'incarc\\u00e9ration de Michael\\u2026\",\"poster_path\":\"\\/vQyP4tvvPdaMkJvk5nXuzpwei4.jpg\",\"season_number\":3,\"vote_average\":8.4},{\"air_date\":\"2008-09-01\",\"episode_count\":22,\"id\":7136,\"name\":\"Saison 4\",\"overview\":\"Michael Scofield et Lincoln Burrows veulent mettre un terme d\\u00e9finitif \\u00e0 cette course-poursuite entre eux, le Cartel et la police. Michael veut \\u00e9galement se venger de la \\u00ab mort \\u00bb de Sara Tancredi mais il apprend tr\\u00e8s vite qu\'elle est en vie. Pour cela, ils vont tenter l\'impossible : faire tomber le Cartel, avec l\'aide de plusieurs personnes, dont Fernando Sucre, Brad Bellick et Alexander Mahone, ainsi que Don Self, un agent du Gouvernement. Dans cette saison, on en apprend beaucoup plus sur le Cartel, ses convictions, ses dirigeants, comme le G\\u00e9n\\u00e9ral Jonathan Krantz. Des tueurs \\u00e0 gage seront envoy\\u00e9s tout au long de la saison pour attraper les fugitifs...\",\"poster_path\":\"\\/2tEpC6jogdcs9DFGF4GTHS8FRnt.jpg\",\"season_number\":4,\"vote_average\":8.5},{\"air_date\":\"2017-04-04\",\"episode_count\":9,\"id\":75021,\"name\":\"Saison 5\",\"overview\":\"Alors que Michael Scofield est pr\\u00e9sum\\u00e9 \\u00eatre mort il y a 7 ans, T-Bag rencontre Lincoln Burrows et lui dit que son fr\\u00e8re serait en vie et en prison au Moyen-Orient. Lincoln Burrows et C-Note vont donc essayer de faire sortir Michael de prison.\",\"poster_path\":\"\\/ntrOGZ1eIZbaPZni9MTBH0zGng0.jpg\",\"season_number\":5,\"vote_average\":8}],\"spoken_languages\":[{\"english_name\":\"English\",\"iso_639_1\":\"en\",\"name\":\"English\"}],\"status\":\"Ended\",\"tagline\":\"Entre par effraction. Sauve la vie de ton fr\\u00e8re.\",\"type\":\"Scripted\",\"vote_average\":8.071,\"vote_count\":5230}', 'admin', 'serie', 1, 'https://image.tmdb.org/t/p/w342/7w165QdHmJuTHSQwEyJDBDpuDT7.jpg', '2288', '', 'Michael Scofield s\'engage dans une véritable lutte contre la montre : son frère Lincoln est dans le couloir de la mort, en attente de son exécution. Persuadé de son innocence mais à court de solutions, Michael décide de se faire incarcérer à son tour dans le pénitencier d\'état de Fox River pour organiser leur évasion...'),
(681, 'Fast and Furious', '2001-06-22', '2025-01-13 09:45:32', '{\"adult\":false,\"backdrop_path\":\"\\/jY9ef5nqY4xIIMu3yzW3qamUCoi.jpg\",\"belongs_to_collection\":{\"id\":9485,\"name\":\"Fast and Furious - Saga\",\"poster_path\":\"\\/pbaRxkvGXtC0GFTYxMgEyFGw9RP.jpg\",\"backdrop_path\":\"\\/z5A5W3WYJc3UVEWljSGwdjDgQ0j.jpg\"},\"budget\":38000000,\"genres\":[{\"id\":28,\"name\":\"Action\"},{\"id\":80,\"name\":\"Crime\"},{\"id\":53,\"name\":\"Thriller\"}],\"homepage\":\"\",\"id\":9799,\"imdb_id\":\"tt0232500\",\"origin_country\":[\"US\"],\"original_language\":\"en\",\"original_title\":\"The Fast and the Furious\",\"overview\":\"La nuit tomb\\u00e9e, Dominic Toretto r\\u00e8gne sur les rues de Los Angeles \\u00e0 la t\\u00eate d\'une \\u00e9quipe de fid\\u00e8les qui partagent son go\\u00fbt du risque, sa passion de la vitesse et son culte des voitures de sport lanc\\u00e9es \\u00e0 plus de 250 km\\/h dans des rod\\u00e9os urbains d\'une rare violence. Ses journ\\u00e9es sont consacr\\u00e9es \\u00e0 bricoler et \\u00e0 relooker des mod\\u00e8les haut de gamme, \\u00e0 les rendre toujours plus performants et plus voyants, \\u00e0 organiser des joutes illicites o\\u00f9 de nombreux candidats s\'affrontent sans merci sous le regard \\u00e9namour\\u00e9 de leurs groupies. \\u00c0 la suite de plusieurs attaques de camions, la police de L.A. d\\u00e9cide d\'enqu\\u00eater sur le milieu des street racers. Brian, un jeune policier, est charg\\u00e9 d\'infiltrer la bande de Toretto, qui figure, avec celle de son rival Johnny Tran, au premier rang des suspects.\",\"popularity\":9.267,\"poster_path\":\"\\/gsW9S3K6oBLKVMMLuSvKwEPOKHc.jpg\",\"production_companies\":[{\"id\":26281,\"logo_path\":null,\"name\":\"Ardustry Entertainment\",\"origin_country\":\"US\"},{\"id\":33,\"logo_path\":\"\\/3wwjVpkZtnog6lSKzWDjvw2Yi00.png\",\"name\":\"Universal Pictures\",\"origin_country\":\"US\"},{\"id\":333,\"logo_path\":\"\\/5xUJfzPZ8jWJUDzYtIeuPO4qPIa.png\",\"name\":\"Original Film\",\"origin_country\":\"US\"},{\"id\":26282,\"logo_path\":null,\"name\":\"Mediastream Film GmbH & Co. Productions KG\",\"origin_country\":\"\"}],\"production_countries\":[{\"iso_3166_1\":\"DE\",\"name\":\"Germany\"},{\"iso_3166_1\":\"US\",\"name\":\"United States of America\"}],\"release_date\":\"2001-06-22\",\"revenue\":207283925,\"runtime\":107,\"spoken_languages\":[{\"english_name\":\"English\",\"iso_639_1\":\"en\",\"name\":\"English\"}],\"status\":\"Released\",\"tagline\":\"Ils ne vivent que pour les 400 m\\u00e8tres d\\u2019une course\\u2026 car pendant ces 10 secondes, ils sont libres !\",\"title\":\"Fast and Furious\",\"video\":false,\"vote_average\":6.986,\"vote_count\":10044}', 'admin', 'film', 1, 'https://image.tmdb.org/t/p/w342/jY9ef5nqY4xIIMu3yzW3qamUCoi.jpg', '9799', '', 'La nuit tombée, Dominic Toretto règne sur les rues de Los Angeles à la tête d\'une équipe de fidèles qui partagent son goût du risque, sa passion de la vitesse et son culte des voitures de sport lancées à plus de 250 km/h dans des rodéos urbains d\'une rare violence. Ses journées sont consacrées à bricoler et à relooker des modèles haut de gamme, à les rendre toujours plus performants et plus voyants, à organiser des joutes illicites où de nombreux candidats s\'affrontent sans merci sous le regard énamouré de leurs groupies. À la suite de plusieurs attaques de camions, la police de L.A. décide d\'enquêter sur le milieu des street racers. Brian, un jeune policier, est chargé d\'infiltrer la bande de Toretto, qui figure, avec celle de son rival Johnny Tran, au premier rang des suspects.');

-- --------------------------------------------------------

--
-- Structure de la table `shops`
--

DROP TABLE IF EXISTS `shops`;
CREATE TABLE IF NOT EXISTS `shops` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `miniature` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `ville` varchar(100) COLLATE utf8mb4_bin NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_bin NOT NULL,
  `address` text COLLATE utf8mb4_bin NOT NULL,
  `date` int DEFAULT NULL,
  `auth` int NOT NULL,
  `visibility` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Déchargement des données de la table `shops`
--

INSERT INTO `shops` (`id`, `name`, `miniature`, `ville`, `phone_number`, `address`, `date`, `auth`, `visibility`) VALUES
(1, 'joseph', '1R68vl3d5s86JsS2NPjl8UoMqIS.jpg', 'ssss', 'sss', 'sss', 0, 0, 0),
(2, 'joseph', '1R68vl3d5s86JsS2NPjl8UoMqIS.jpg', 'ssss', 'sss', 'sss', 1736769636, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `shop_movies`
--

DROP TABLE IF EXISTS `shop_movies`;
CREATE TABLE IF NOT EXISTS `shop_movies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shop_id` int NOT NULL,
  `movie_id` int NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_id` (`shop_id`,`movie_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Déchargement des données de la table `shop_movies`
--

INSERT INTO `shop_movies` (`id`, `shop_id`, `movie_id`, `address`, `added_at`) VALUES
(2, 2, 1, NULL, '2025-01-13 12:23:48'),
(3, 2, 680, NULL, '2025-01-13 12:35:08');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `accreditation` int NOT NULL DEFAULT '0',
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `codeConfirm` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Structure de la table `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE IF NOT EXISTS `votes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `movie_id` int NOT NULL,
  `date` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Déchargement des données de la table `votes`
--

INSERT INTO `votes` (`id`, `user_id`, `movie_id`, `date`) VALUES
(2, 1, 2, 1736767864),
(3, 1, 680, 1736768183);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `movies`
--
ALTER TABLE `movies` ADD FULLTEXT KEY `titre` (`titre`,`description`,`contenu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
