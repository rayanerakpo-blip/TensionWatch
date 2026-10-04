-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
--

-- --------------------------------------------------------
--
-- Structure de la table `contacts_urgence`
--

CREATE TABLE `contacts_urgence` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `lien_avec_patient` varchar(100) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Structure de la table `effets_indesirables`
--

CREATE TABLE `effets_indesirables` (
  `id` int(11) NOT NULL,
  `type_effet` varchar(100) DEFAULT NULL,
  `intensite` int(11) DEFAULT NULL,
  `frequence` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_effet` date DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Structure de la table `medicaments`
--

CREATE TABLE `medicaments` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `frequence` varchar(100) DEFAULT NULL,
  `heure_prise` time DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Structure de la table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `Date_de_naissance` date DEFAULT NULL,
  `groupe_sanguin` varchar(5) DEFAULT NULL,
  `poids` decimal(5,2) DEFAULT NULL,
  `medecin` varchar(150) DEFAULT NULL,
  `antecedants` text DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Structure de la table `rappels`
--

CREATE TABLE `rappels` (
  `id` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `date_rappel` date NOT NULL,
  `heure_rappel` time DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tensions`
--

CREATE TABLE `tensions` (
  `id` int(11) NOT NULL,
  `systolique` int(11) DEFAULT NULL,
  `diastolique` int(11) DEFAULT NULL,
  `date_mesure` datetime DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_de_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


--
-- Index pour les tables déchargées
--

--
-- Index pour la table `contacts_urgence`
--
ALTER TABLE `contacts_urgence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Index pour la table `effets_indesirables`
--
ALTER TABLE `effets_indesirables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Index pour la table `medicaments`
--
ALTER TABLE `medicaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Index pour la table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `rappels`
--
ALTER TABLE `rappels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Index pour la table `tensions`
--
ALTER TABLE `tensions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `contacts_urgence`
--
ALTER TABLE `contacts_urgence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `effets_indesirables`
--
ALTER TABLE `effets_indesirables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `medicaments`
--
ALTER TABLE `medicaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `rappels`
--
ALTER TABLE `rappels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tensions`
--
ALTER TABLE `tensions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `contacts_urgence`
--
ALTER TABLE `contacts_urgence`
  ADD CONSTRAINT `contacts_urgence_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Contraintes pour la table `effets_indesirables`
--
ALTER TABLE `effets_indesirables`
  ADD CONSTRAINT `effets_indesirables_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Contraintes pour la table `medicaments`
--
ALTER TABLE `medicaments`
  ADD CONSTRAINT `medicaments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Contraintes pour la table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `rappels`
--
ALTER TABLE `rappels`
  ADD CONSTRAINT `rappels_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Contraintes pour la table `tensions`
--
ALTER TABLE `tensions`
  ADD CONSTRAINT `tensions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
