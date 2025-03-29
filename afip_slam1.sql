-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 29 mars 2025 à 10:51
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `afip_slam1`
--

-- --------------------------------------------------------

--
-- Structure de la table `fiches`
--

CREATE TABLE `fiches` (
  `id_fiches` int(11) NOT NULL,
  `id_users` int(11) NOT NULL,
  `op_date` date NOT NULL,
  `cl_date` date DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `id_comptable` int(11) DEFAULT NULL,
  `total_frais` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_rembourse` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fiches`
--

INSERT INTO `fiches` (`id_fiches`, `id_users`, `op_date`, `cl_date`, `status_id`, `id_comptable`, `total_frais`, `total_rembourse`) VALUES
(7, 1, '2024-12-19', '2025-03-17', 4, 6, 86.00, 86.00),
(8, 1, '2024-09-02', NULL, 1, NULL, 0.00, 0.00),
(9, 1, '2024-12-19', '2025-03-17', 4, 6, 43.20, 40.20),
(10, 1, '2024-12-19', '2025-03-17', 3, 14, 0.00, 0.00),
(11, 1, '2024-11-19', '2025-01-14', 3, 6, 0.00, 0.00),
(12, 1, '2024-12-19', '2025-03-17', 3, 6, 0.00, 0.00),
(13, 1, '2024-10-18', '2025-02-18', 1, NULL, 0.00, 0.00),
(14, 1, '2024-12-02', '2025-03-17', 3, 6, 0.00, 0.00),
(15, 1, '2024-01-16', '2025-03-17', 1, NULL, 0.00, 0.00),
(16, 1, '2024-12-02', NULL, 2, NULL, 0.00, 0.00),
(17, 1, '2024-12-03', NULL, 2, NULL, 0.00, 0.00),
(25, 1, '2025-02-19', '0000-00-00', 2, NULL, 0.00, 0.00),
(28, 5, '2025-03-11', NULL, 2, NULL, 0.00, 0.00),
(31, 5, '2025-03-24', NULL, 2, NULL, 0.00, 0.00),
(32, 5, '2025-03-24', NULL, 2, NULL, 0.00, 0.00),
(33, 5, '2025-03-24', NULL, 2, NULL, 0.00, 0.00),
(38, 5, '2025-03-26', NULL, 2, NULL, 0.00, 0.00),
(39, 5, '2025-03-26', NULL, 2, NULL, 0.00, 0.00),
(40, 5, '2025-03-26', '2025-03-26', 4, 6, 26.00, 12.00),
(41, 13, '2025-03-28', NULL, 2, NULL, 0.00, 0.00),
(42, 13, '2025-03-28', '2025-03-28', 3, 14, 0.00, 0.00),
(43, 13, '2025-03-28', NULL, 2, NULL, 0.00, 0.00),
(44, 13, '2025-03-28', '2025-03-28', 1, NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `lignes_frais`
--

CREATE TABLE `lignes_frais` (
  `id_lf` int(11) NOT NULL,
  `id_fiche` int(11) NOT NULL,
  `id_tf` int(11) NOT NULL,
  `quantité` float NOT NULL,
  `total` float NOT NULL,
  `sp_date` date NOT NULL,
  `justif` varchar(255) NOT NULL,
  `refund_status` tinyint(1) DEFAULT NULL,
  `motif_refus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `lignes_frais`
--

INSERT INTO `lignes_frais` (`id_lf`, `id_fiche`, `id_tf`, `quantité`, `total`, `sp_date`, `justif`, `refund_status`, `motif_refus`) VALUES
(7, 7, 3, 2, 30, '2024-12-17', 'justificatif/6763f8db44d28_test.PDF', NULL, NULL),
(8, 7, 1, 1, 50, '2024-12-09', 'justificatif/6763f8db54856_test.PDF', NULL, NULL),
(9, 7, 2, 2, 6, '2024-12-05', 'justificatif/6763f8db54cc9_test.PDF', NULL, NULL),
(10, 8, 3, 1, 3, '2024-09-10', 'justificatif/6763f97064974_test.PDF', NULL, NULL),
(11, 8, 2, 1, 10, '2024-09-20', 'justificatif/6763f97064c8e_test.PDF', NULL, NULL),
(12, 9, 1, 2, 40.2, '2024-12-02', 'justificatif/6763faf935d27_test.PDF', 1, NULL),
(13, 10, 1, 2, 46.8, '2024-11-18', 'justificatif/6763fb362d6ab_test.PDF', NULL, NULL),
(14, 11, 4, 1, 69.99, '2024-11-15', 'justificatif/6763fc7e902f0_test.PDF', NULL, NULL),
(15, 12, 2, 1, 10.01, '2024-12-11', 'justificatif/676418be3549a_test.PDF', NULL, NULL),
(16, 13, 4, 1, 0.6, '2024-12-16', 'justificatif/67641958504c1_test.PDF', NULL, NULL),
(17, 13, 1, 1, 0.8, '2024-12-17', 'justificatif/67641958507db_test.PDF', NULL, NULL),
(18, 14, 4, 3, 56.89, '2024-12-09', 'justificatif/67641bfa53cee_test.PDF', NULL, NULL),
(19, 15, 3, 1, 89.5, '2024-01-16', 'justificatif/67641c45691ac_test.PDF', NULL, NULL),
(20, 16, 1, 5, 21, '2024-12-16', 'justificatif/67641c6258344_test.PDF', NULL, NULL),
(21, 17, 1, 2, 2.58, '2024-12-10', 'justificatif/67641c799745e_test.PDF', NULL, NULL),
(22, 8, 4, 1, 2, '2024-12-09', 'justificatif/justificatif6764271f4ba99_test.PDF', NULL, NULL),
(23, 9, 2, 1, 3, '2024-12-02', 'justificatif/justificatif67642764e213f_test.PDF', 0, 'pas envie'),
(24, 8, 4, 2, 3.5, '2024-12-03', 'justificatif/67653e8b53690_test.PDF', NULL, NULL),
(25, 11, 1, 1, 15, '2025-01-08', 'justificatif/67862d39ac307_tst.pdf', NULL, NULL),
(31, 25, 1, 1, 23, '2025-02-20', 'justificatif/67b5e8d4e21a2_t.pdf', NULL, NULL),
(35, 28, 4, 5, 100, '2025-03-12', 'justificatif/67d057334feba_t.pdf', NULL, NULL),
(39, 28, 1, 3, 28, '2025-03-06', 'justificatif/67d1b55e11f84_t.pdf', NULL, NULL),
(40, 31, 3, 5, 77.77, '2025-03-04', 'justificatif/67e17b9eafef8_t.pdf', NULL, NULL),
(41, 31, 4, 3, 15, '2025-03-04', 'justificatif/67e17b9eb01e2_t.pdf', NULL, NULL),
(42, 31, 2, 4, 40, '2025-03-04', 'justificatif/67e17b9eb61d3_t.pdf', NULL, NULL),
(43, 32, 1, 5, 6, '2025-03-05', 'justificatif/67e17be204ab3_t.pdf', NULL, NULL),
(44, 32, 2, 7, 8, '2025-03-05', 'justificatif/67e17be2054c0_t.pdf', NULL, NULL),
(45, 32, 3, 9, 10, '2025-03-05', 'justificatif/67e17be21593b_t.pdf', NULL, NULL),
(46, 33, 2, 2, 4, '2025-03-18', 'justificatif/67e18e03baafe_t.pdf', NULL, NULL),
(47, 33, 4, 3, 5, '2025-03-17', 'justificatif/67e18e03c71ee_t.pdf', NULL, NULL),
(48, 38, 3, 5, 77.77, '2025-03-04', 'justificatif/67e17b9eafef8_t.pdf', NULL, NULL),
(49, 38, 4, 3, 15, '2025-03-04', 'justificatif/67e17b9eb01e2_t.pdf', NULL, NULL),
(50, 38, 2, 4, 40, '2025-03-04', 'justificatif/67e17b9eb61d3_t.pdf', NULL, NULL),
(51, 38, 1, 5, 33.33, '2025-03-12', 'justificatif/67e44fe1ae189_IMG_1868.png', NULL, NULL),
(52, 39, 3, 5, 77.77, '2025-03-04', 'justificatif/67e17b9eafef8_t.pdf', NULL, NULL),
(53, 39, 2, 4, 40, '2025-03-04', 'justificatif/67e17b9eb61d3_t.pdf', NULL, NULL),
(54, 39, 1, 5, 33.33, '2025-03-12', 'justificatif/67e44fe1ae189_IMG_1868.png', NULL, NULL),
(55, 40, 1, 1, 5, '2025-03-12', 'justificatif/67e45479d0a2b_IMG_1868.png', 1, NULL),
(56, 40, 1, 2, 6, '2025-03-13', 'justificatif/67e45479d0d50_IMG_1868.png', 0, 'non'),
(57, 40, 1, 3, 7, '2025-03-14', 'justificatif/67e45479d0ff1_IMG_1868.png', 1, NULL),
(58, 40, 1, 4, 8, '2025-03-17', 'justificatif/67e45479d1235_IMG_1868.png', 0, 'non'),
(59, 41, 2, 2, 11.32, '2025-03-27', 'justificatif/67e6091b18c35_t.pdf', NULL, NULL),
(60, 41, 3, 2, 25, '2025-03-27', 'justificatif/67e6091b383d9_t.pdf', NULL, NULL),
(61, 42, 1, 5, 132.5, '2025-03-18', 'justificatif/67e60984817d3_t.pdf', NULL, NULL),
(62, 42, 2, 3, 28.78, '2025-03-24', 'justificatif/67e6098481a84_t.pdf', NULL, NULL),
(63, 42, 4, 1, 35.19, '2025-03-07', 'justificatif/67e6098481c60_t.pdf', NULL, NULL),
(64, 43, 1, 1, 14.99, '2025-03-05', 'justificatif/67e609ae465a2_t.pdf', NULL, NULL),
(65, 44, 2, 2, 11.32, '2025-03-27', 'justificatif/67e6091b18c35_t.pdf', NULL, NULL),
(66, 44, 3, 2, 25, '2025-03-27', 'justificatif/67e6091b383d9_t.pdf', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id_role` int(11) NOT NULL,
  `role` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id_role`, `role`) VALUES
(1, 'Administrateur'),
(2, 'Visiteur'),
(3, 'Comptable');

-- --------------------------------------------------------

--
-- Structure de la table `status_fiche`
--

CREATE TABLE `status_fiche` (
  `status_id` int(11) NOT NULL,
  `name_status` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `status_fiche`
--

INSERT INTO `status_fiche` (`status_id`, `name_status`) VALUES
(1, 'Cloturée'),
(2, 'Ouverte'),
(3, 'En cours de traitement'),
(4, 'Traitée');

-- --------------------------------------------------------

--
-- Structure de la table `type_frais`
--

CREATE TABLE `type_frais` (
  `id_tf` int(11) NOT NULL,
  `type` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `type_frais`
--

INSERT INTO `type_frais` (`id_tf`, `type`) VALUES
(1, 'Nuité'),
(2, 'Déplacement'),
(3, 'Repas'),
(4, 'Hors forfait');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `user_firstname` varchar(30) NOT NULL,
  `user_lastname` varchar(30) NOT NULL,
  `user_email` varchar(40) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `id_role` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `user_firstname`, `user_lastname`, `user_email`, `user_password`, `id_role`) VALUES
(1, 'Miguel', 'Janos', 'miguel.janos@gsb.com', '$2y$10$etWQH4IqWlSs79.3HJN/JeJ5LM.Go2ay3w5LY4o5e/47/VHR9hPc.', 1),
(4, 'Jean-Michel', 'Crappo', 'jm.crappo@gsb.com', '$2y$10$ErV1VIMIViOTbOJ.enkg9u8qy5Gdrqt1LWizBtLzBcdH3vn7Wn8ZK', 1),
(5, 'Nicolas', 'Barbet', 'nicolas.barbet@gsb.com', '$2y$10$3rMufdtzCtXBiiCbgZDZ9u9ahZ.BV3FqOs1dEeGISVg2VKUYxqfbK', 2),
(6, 'Sophie', 'Delrah', 'sophie.delrah@gsb.com', '$2y$10$2ZW76rvtqPBCKDunOjMd5ezRilKBhahgglsORL9BkkljWf06BaI/e', 3),
(13, 'Bob', 'Epoleur', 'bob.epoleur@gsb.com', '$2y$10$JtvNJQMsR72g/qTtY4atJO8aBJ7TdHiP7Aw/zHj109MjWbXs3GIeK', 2),
(14, 'Alice', 'Merveille', 'alice.merveille@gsb.com', '$2y$10$TwtnJflDceHr8WtjdEml/uTEzeaOfFifIcMmCOtF6DFgI/QOKILqW', 3);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `fiches`
--
ALTER TABLE `fiches`
  ADD PRIMARY KEY (`id_fiches`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `id_comptable` (`id_comptable`);

--
-- Index pour la table `lignes_frais`
--
ALTER TABLE `lignes_frais`
  ADD PRIMARY KEY (`id_lf`),
  ADD KEY `id_fiche` (`id_fiche`),
  ADD KEY `id_tf` (`id_tf`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`);

--
-- Index pour la table `status_fiche`
--
ALTER TABLE `status_fiche`
  ADD PRIMARY KEY (`status_id`);

--
-- Index pour la table `type_frais`
--
ALTER TABLE `type_frais`
  ADD PRIMARY KEY (`id_tf`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_role` (`id_role`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `fiches`
--
ALTER TABLE `fiches`
  MODIFY `id_fiches` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT pour la table `lignes_frais`
--
ALTER TABLE `lignes_frais`
  MODIFY `id_lf` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `status_fiche`
--
ALTER TABLE `status_fiche`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `type_frais`
--
ALTER TABLE `type_frais`
  MODIFY `id_tf` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `fiches`
--
ALTER TABLE `fiches`
  ADD CONSTRAINT `fiches_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fiches_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status_fiche` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fiches_comptable` FOREIGN KEY (`id_comptable`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `lignes_frais`
--
ALTER TABLE `lignes_frais`
  ADD CONSTRAINT `lignes_frais_ibfk_1` FOREIGN KEY (`id_fiche`) REFERENCES `fiches` (`id_fiches`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lignes_frais_ibfk_2` FOREIGN KEY (`id_tf`) REFERENCES `type_frais` (`id_tf`);

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
