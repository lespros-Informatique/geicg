-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: c2833857c_eicg
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.5
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!50503 SET NAMES utf8mb4 */
;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */
;
/*!40103 SET TIME_ZONE='+00:00' */
;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */
;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */
;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */
;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */
;
--
-- Table structure for table `absences`
--

DROP TABLE IF EXISTS `absences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `absences` (
  `id_absence` int NOT NULL AUTO_INCREMENT,
  `code_absence` varchar(50) NOT NULL,
  `inscription_code` varchar(50) NOT NULL,
  `emploi_code` varchar(50) NOT NULL,
  `matiere_code` varchar(50) NOT NULL,
  `date_absence` date NOT NULL,
  `duree_heures` decimal(4, 2) NOT NULL DEFAULT '2.00',
  `justifiee` enum('oui', 'non') NOT NULL DEFAULT 'non',
  `motif` text,
  `annee_code` varchar(50) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `created_at_absence` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_absence`),
  UNIQUE KEY `code_absence` (`code_absence`),
  KEY `idx_absences_inscription_code` (`inscription_code`),
  KEY `idx_absences_emploi_code` (`emploi_code`),
  KEY `idx_absences_matiere_code` (`matiere_code`),
  KEY `idx_absences_date_absence` (`date_absence`),
  KEY `idx_absences_annee_code` (`annee_code`),
  KEY `idx_absences_etablissement_code` (`etablissement_code`),
  KEY `idx_abs_insc_date` (`inscription_code`, `date_absence`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `absences`
--

LOCK TABLES `absences` WRITE;
/*!40000 ALTER TABLE `absences` DISABLE KEYS */
;
/*!40000 ALTER TABLE `absences` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `accessoire_inscription`
--

DROP TABLE IF EXISTS `accessoire_inscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `accessoire_inscription` (
  `id_accessoire_inscription` int NOT NULL AUTO_INCREMENT,
  `code_accessoire_inscription` varchar(50) NOT NULL,
  `inscription_code` varchar(50) NOT NULL,
  `accessoire_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `statut_accessoire_inscription` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `etat_retrait` enum('en_attente', 'retire') NOT NULL DEFAULT 'en_attente',
  `date_retrait` datetime DEFAULT NULL,
  `created_at_accessoire_inscription` timestamp NOT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_accessoire_inscription`),
  UNIQUE KEY `code_accessoire_inscription` (`code_accessoire_inscription`),
  KEY `idx_accessoire_inscripti_inscription_code` (`inscription_code`),
  KEY `idx_accessoire_inscripti_accessoire_code` (`accessoire_code`),
  KEY `idx_accessoire_inscripti_annee_code` (`annee_code`),
  KEY `idx_accessoire_inscripti_statut_accessoire_inscription` (`statut_accessoire_inscription`),
  KEY `idx_accessoire_inscripti_etat_retrait` (`etat_retrait`),
  KEY `idx_accessoire_inscripti_etablissement_code` (`etablissement_code`),
  KEY `idx_accessoire_inscripti_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `accessoire_inscription`
--

LOCK TABLES `accessoire_inscription` WRITE;
/*!40000 ALTER TABLE `accessoire_inscription` DISABLE KEYS */
;
/*!40000 ALTER TABLE `accessoire_inscription` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `accessoire_niveau_filiere`
--

DROP TABLE IF EXISTS `accessoire_niveau_filiere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `accessoire_niveau_filiere` (
  `id_accessoire_niveau_filiere` int NOT NULL AUTO_INCREMENT,
  `code_accessoire_niveau_filiere` varchar(50) NOT NULL,
  `accessoire_code` varchar(50) NOT NULL,
  `filiere_code` varchar(50) DEFAULT NULL,
  `niveau_code` varchar(50) DEFAULT NULL,
  `annee_code` varchar(50) DEFAULT NULL,
  `statut_accessoire_niveau_filiere` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `etablissement_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `created_at_accessoire_niveau_filiere` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_accessoire_niveau_filiere` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_accessoire_niveau_filiere`),
  UNIQUE KEY `uk_code_anf` (`code_accessoire_niveau_filiere`),
  KEY `idx_anf_accessoire` (`accessoire_code`),
  KEY `idx_anf_filiere` (`filiere_code`),
  KEY `idx_anf_niveau` (`niveau_code`),
  KEY `idx_anf_annee` (`annee_code`),
  KEY `idx_anf_statut` (`statut_accessoire_niveau_filiere`),
  KEY `idx_anf_etab` (`etablissement_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `accessoire_niveau_filiere`
--

LOCK TABLES `accessoire_niveau_filiere` WRITE;
/*!40000 ALTER TABLE `accessoire_niveau_filiere` DISABLE KEYS */
;
/*!40000 ALTER TABLE `accessoire_niveau_filiere` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `accessoires`
--

DROP TABLE IF EXISTS `accessoires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `accessoires` (
  `id_accessoire` int NOT NULL AUTO_INCREMENT,
  `code_accessoire` varchar(50) NOT NULL,
  `libelle_accessoire` varchar(255) NOT NULL,
  `type_filiere_cible` enum('TOUT', 'INDUSTRIELLE', 'TERTIAIRE') NOT NULL DEFAULT 'TOUT',
  `niveau_code` varchar(50) DEFAULT NULL,
  `statut_accessoire` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_accessoire` timestamp NOT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_accessoire`),
  UNIQUE KEY `code_accessoire` (`code_accessoire`),
  KEY `idx_acc_niveau` (`niveau_code`),
  KEY `idx_accessoires_statut_accessoire` (`statut_accessoire`),
  KEY `idx_accessoires_etablissement_code` (`etablissement_code`),
  KEY `idx_accessoires_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `accessoires`
--

LOCK TABLES `accessoires` WRITE;
/*!40000 ALTER TABLE `accessoires` DISABLE KEYS */
;
/*!40000 ALTER TABLE `accessoires` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `annees`
--

DROP TABLE IF EXISTS `annees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `annees` (
  `id_annee` int NOT NULL AUTO_INCREMENT,
  `libelle_annee` varchar(50) NOT NULL,
  `date_debut_annee` date NOT NULL,
  `date_fin_annee` date NOT NULL,
  `code_annee` varchar(50) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `created_at_annee` datetime NOT NULL,
  `updated_at_annee` datetime DEFAULT NULL,
  `statut_annee` enum('planifie', 'actif', 'cloture') NOT NULL DEFAULT 'planifie',
  `user_code` varchar(50) DEFAULT NULL,
  `coef_semestre1` int NOT NULL DEFAULT '2',
  `coef_semestre2` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`id_annee`),
  UNIQUE KEY `uq_annee_libelle` (`libelle_annee`, `etablissement_code`),
  KEY `idx_annees_code_annee` (`code_annee`),
  KEY `idx_annees_etablissement_code` (`etablissement_code`),
  KEY `idx_annees_statut_annee` (`statut_annee`),
  KEY `idx_annees_user_code` (`user_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `annees`
--

LOCK TABLES `annees` WRITE;
/*!40000 ALTER TABLE `annees` DISABLE KEYS */
;
INSERT INTO `annees`
VALUES (
    1,
    '2025-2026',
    '2025-09-15',
    '2026-07-15',
    'ANN-S9RCBUVE',
    '5454544456',
    '2026-09-14 15:28:19',
    NULL,
    'cloture',
    '5wBEh2OfI00frxk8ITPf',
    2,
    2
  ),
  (
    2,
    '2026-2027',
    '2026-09-16',
    '2027-06-16',
    'ANN-LS4V0ORV',
    '5454544456',
    '2026-09-15 03:08:30',
    '2026-09-17 00:22:32',
    'actif',
    '5wBEh2OfI00frxk8ITPf',
    2,
    2
  );
/*!40000 ALTER TABLE `annees` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `classes` (
  `id_classe` int NOT NULL AUTO_INCREMENT,
  `code_classe` varchar(50) NOT NULL,
  `libelle_classe` varchar(150) NOT NULL,
  `capacite_max_classe` int DEFAULT NULL,
  `filiere_code` varchar(50) NOT NULL,
  `cycle_code` varchar(50) DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `niveau_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `statut_classe` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_classe` datetime NOT NULL,
  `updated_at_classe` datetime DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_classe`),
  UNIQUE KEY `code_classe` (`code_classe`),
  KEY `idx_classes_filiere_code` (`filiere_code`),
  KEY `idx_classes_cycle_code` (`cycle_code`),
  KEY `idx_classes_etablissement_code` (`etablissement_code`),
  KEY `idx_classes_niveau_code` (`niveau_code`),
  KEY `idx_classes_annee_code` (`annee_code`),
  KEY `idx_classes_statut_classe` (`statut_classe`),
  KEY `idx_classes_user_code` (`user_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */
;
INSERT INTO `classes`
VALUES (
    3,
    'CLA-BNB9746Y',
    'IDA 1A',
    35,
    'FIL-XMA5DLZF',
    'CYC-RHUFSGDY',
    '5454544456',
    'NIV-EPQZ6JJ1',
    'ANN-LS4V0ORV',
    'actif',
    '2026-09-18 14:43:02',
    NULL,
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    4,
    'CLA-ZRMW58QQ',
    'IDA 2A',
    35,
    'FIL-XMA5DLZF',
    NULL,
    '5454544456',
    'NIV-L6TC826S',
    'ANN-LS4V0ORV',
    'actif',
    '2026-09-18 14:45:23',
    NULL,
    '5wBEh2OfI00frxk8ITPf'
  );
/*!40000 ALTER TABLE `classes` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `composition_matieres`
--

DROP TABLE IF EXISTS `composition_matieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `composition_matieres` (
  `id_composition_matiere` int NOT NULL AUTO_INCREMENT,
  `composition_code` varchar(50) NOT NULL,
  `composition_niveau_filiere_code` varchar(50) NOT NULL,
  `matiere_code` varchar(50) NOT NULL,
  `created_at_composition_matiere` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_composition_matiere`),
  UNIQUE KEY `uk_comp_cible_mat` (
    `composition_code`,
    `composition_niveau_filiere_code`,
    `matiere_code`
  ),
  KEY `idx_comp_code` (`composition_code`),
  KEY `idx_cible_code` (`composition_niveau_filiere_code`),
  KEY `idx_mat_code` (`matiere_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `composition_matieres`
--

LOCK TABLES `composition_matieres` WRITE;
/*!40000 ALTER TABLE `composition_matieres` DISABLE KEYS */
;
/*!40000 ALTER TABLE `composition_matieres` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `composition_niveau_filiere`
--

DROP TABLE IF EXISTS `composition_niveau_filiere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `composition_niveau_filiere` (
  `id_composition_niveau_filiere` int NOT NULL AUTO_INCREMENT,
  `code_composition_niveau_filiere` varchar(50) NOT NULL,
  `composition_code` varchar(50) NOT NULL,
  `niveau_code` varchar(50) NOT NULL,
  `filiere_code` varchar(50) NOT NULL,
  `created_at_composition_niveau_filiere` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_composition_niveau_filiere`),
  UNIQUE KEY `code_composition_cible` (`code_composition_niveau_filiere`),
  UNIQUE KEY `uk_comp_niv_fil` (
    `composition_code`,
    `niveau_code`,
    `filiere_code`
  ),
  KEY `idx_composition_code` (`composition_code`),
  KEY `idx_niveau_code` (`niveau_code`),
  KEY `idx_filiere_code` (`filiere_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `composition_niveau_filiere`
--

LOCK TABLES `composition_niveau_filiere` WRITE;
/*!40000 ALTER TABLE `composition_niveau_filiere` DISABLE KEYS */
;
/*!40000 ALTER TABLE `composition_niveau_filiere` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `compositions`
--

DROP TABLE IF EXISTS `compositions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `compositions` (
  `id_composition` int NOT NULL AUTO_INCREMENT,
  `code_composition` varchar(50) NOT NULL,
  `libelle_composition` varchar(150) NOT NULL,
  `type_composition` enum('EXAMEN', 'DEVOIR', 'INTERROGATION', 'AUTRE') NOT NULL DEFAULT 'EXAMEN',
  `semestre_code` varchar(50) NOT NULL,
  `coefficient` decimal(4, 2) NOT NULL DEFAULT '1.00',
  `date_composition` date DEFAULT NULL,
  `date_fin_composition` date DEFAULT NULL,
  `statut_composition` enum('programme', 'termine', 'annule') NOT NULL DEFAULT 'programme',
  `annee_code` varchar(50) DEFAULT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `created_at_composition` datetime DEFAULT NULL,
  `updated_at_composition` datetime DEFAULT NULL,
  PRIMARY KEY (`id_composition`),
  UNIQUE KEY `code_composition` (`code_composition`),
  KEY `idx_compositions_semestre_code` (`semestre_code`),
  KEY `idx_compositions_date_composition` (`date_composition`),
  KEY `idx_compositions_statut_composition` (`statut_composition`),
  KEY `idx_compositions_annee_code` (`annee_code`),
  KEY `idx_compositions_etablissement_code` (`etablissement_code`),
  KEY `idx_compositions_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `compositions`
--

LOCK TABLES `compositions` WRITE;
/*!40000 ALTER TABLE `compositions` DISABLE KEYS */
;
/*!40000 ALTER TABLE `compositions` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `configurations`
--

DROP TABLE IF EXISTS `configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `configurations` (
  `id_configuration` int NOT NULL AUTO_INCREMENT,
  `nom_etablissement` varchar(255) DEFAULT NULL,
  `sigle_etablissement` varchar(100) DEFAULT NULL,
  `slogan_etablissement` varchar(255) DEFAULT NULL,
  `numero_autorisation` varchar(150) DEFAULT NULL,
  `ministere_tutelle` varchar(255) DEFAULT NULL,
  `logo_etablissement` varchar(500) DEFAULT NULL,
  `adresse_etablissement` text,
  `boite_postale` varchar(100) DEFAULT NULL,
  `ville_etablissement` varchar(100) DEFAULT NULL,
  `pays_etablissement` varchar(100) DEFAULT NULL,
  `telephone_fixe` varchar(50) DEFAULT NULL,
  `telephone_mobile` varchar(50) DEFAULT NULL,
  `telephone_mobile2` varchar(50) DEFAULT NULL,
  `email_etablissement` varchar(150) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `titre_direction_pedagogique` varchar(150) DEFAULT NULL,
  `nom_directeur_etudes` varchar(150) DEFAULT NULL,
  `titre_direction_generale` varchar(150) DEFAULT NULL,
  `nom_directeur_general` varchar(150) DEFAULT NULL,
  `titre_service_finance` varchar(150) DEFAULT NULL,
  `nom_responsable_finance` varchar(150) DEFAULT NULL,
  `titre_service_scolarite` varchar(150) DEFAULT NULL,
  `nom_responsable_scolarite` varchar(150) DEFAULT NULL,
  `nom_secretaire_general` varchar(150) DEFAULT NULL,
  `mention_cachet_signature` varchar(255) DEFAULT NULL,
  `ville_signature` varchar(100) DEFAULT NULL,
  `texte_pied_page` varchar(255) DEFAULT NULL,
  `prefixe_reference` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_configuration`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `configurations`
--

LOCK TABLES `configurations` WRITE;
/*!40000 ALTER TABLE `configurations` DISABLE KEYS */
;
INSERT INTO `configurations`
VALUES (
    1,
    'etablissement A',
    NULL,
    'test',
    '',
    NULL,
    'public/uploads/logos/logo_1787358264.jpg',
    'adresse',
    NULL,
    NULL,
    NULL,
    NULL,
    '0102030405',
    '0302010405',
    'test@gmail.com',
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    '2026-11-19 00:42:10',
    '2026-11-19 00:42:10'
  );
/*!40000 ALTER TABLE `configurations` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `cycles`
--

DROP TABLE IF EXISTS `cycles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `cycles` (
  `id_cycle` int NOT NULL AUTO_INCREMENT,
  `code_cycle` varchar(50) NOT NULL,
  `libelle_cycle` varchar(100) NOT NULL,
  `slug_cycle` varchar(100) DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `created_at_cycle` datetime NOT NULL,
  `updated_at_cycle` datetime DEFAULT NULL,
  `statut_cycle` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_cycle`),
  UNIQUE KEY `code_cycle` (`code_cycle`),
  KEY `idx_cycles_etablissement_code` (`etablissement_code`),
  KEY `idx_cycles_statut_cycle` (`statut_cycle`),
  KEY `idx_cycles_user_code` (`user_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `cycles`
--

LOCK TABLES `cycles` WRITE;
/*!40000 ALTER TABLE `cycles` DISABLE KEYS */
;
INSERT INTO `cycles`
VALUES (
    1,
    'CYC-RHUFSGDY',
    'BREVET DE TECHNICIEN SUPERIEUR',
    'BTS',
    '5454544456',
    '2026-09-18 14:05:27',
    NULL,
    'actif',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    2,
    'CYC-UEFZFDJF',
    'licences',
    'licenses',
    '5454544456',
    '2026-09-18 14:21:48',
    NULL,
    'actif',
    '5wBEh2OfI00frxk8ITPf'
  );
/*!40000 ALTER TABLE `cycles` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `depenses`
--

DROP TABLE IF EXISTS `depenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `depenses` (
  `id_depense` int NOT NULL AUTO_INCREMENT,
  `type_depense_code` varchar(50) NOT NULL,
  `code_depense` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) NOT NULL,
  `montant_depense` float NOT NULL,
  `description_depense` text NOT NULL,
  `piece_justificative` varchar(255) DEFAULT NULL,
  `periode_depense` datetime NOT NULL,
  `created_at_depense` datetime NOT NULL,
  `updated_at_depense` datetime DEFAULT NULL,
  `annee_code` varchar(50) NOT NULL,
  `statut_depense` enum('en_attente', 'approuve', 'annule') NOT NULL DEFAULT 'en_attente',
  `user_confirm` varchar(50) DEFAULT NULL,
  `created_at_confirm` datetime DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  PRIMARY KEY (`id_depense`),
  KEY `type_id` (`type_depense_code`),
  KEY `employe_id` (`user_code`),
  KEY `idx_depenses_code_depense` (`code_depense`),
  KEY `idx_depenses_periode_depense` (`periode_depense`),
  KEY `idx_depenses_annee_code` (`annee_code`),
  KEY `idx_depenses_statut_depense` (`statut_depense`),
  KEY `idx_depenses_etablissement_code` (`etablissement_code`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `depenses`
--

LOCK TABLES `depenses` WRITE;
/*!40000 ALTER TABLE `depenses` DISABLE KEYS */
;
/*!40000 ALTER TABLE `depenses` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `documents` (
  `id_document` int NOT NULL AUTO_INCREMENT,
  `code_document` varchar(50) DEFAULT NULL,
  `libelle_document` varchar(100) NOT NULL,
  `type_document` varchar(50) DEFAULT NULL,
  `lien_document` varchar(255) NOT NULL,
  `filiere_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `niveaux_code` varchar(50) NOT NULL,
  `etablisement_code` varchar(100) NOT NULL,
  `statut_document` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_document` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_document`),
  KEY `idx_documents_code_document` (`code_document`),
  KEY `idx_documents_filiere_code` (`filiere_code`),
  KEY `idx_documents_annee_code` (`annee_code`),
  KEY `idx_documents_niveaux_code` (`niveaux_code`),
  KEY `idx_documents_etablisement_code` (`etablisement_code`),
  KEY `idx_documents_statut_document` (`statut_document`),
  KEY `idx_documents_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */
;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `dossier_etudiant`
--

DROP TABLE IF EXISTS `dossier_etudiant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `dossier_etudiant` (
  `id_dossier_etudiant` int NOT NULL AUTO_INCREMENT,
  `code_dossier_etudiant` varchar(50) NOT NULL,
  `inscription_code` varchar(50) NOT NULL,
  `etudiant_code` varchar(50) NOT NULL,
  `piece_code` varchar(50) NOT NULL,
  `statut_depot` enum('depose', 'en_attente', 'rejete', 'non_requis') NOT NULL DEFAULT 'depose',
  `date_depot` datetime DEFAULT CURRENT_TIMESTAMP,
  `fichier_joint` varchar(255) DEFAULT NULL,
  `observations` text,
  `user_code` varchar(50) DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL DEFAULT '5454544456',
  `created_at_dossier_etudiant` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_dossier_etudiant` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dossier_etudiant`),
  UNIQUE KEY `code_dossier_etudiant` (`code_dossier_etudiant`),
  UNIQUE KEY `unique_inscr_piece` (`inscription_code`, `piece_code`),
  KEY `idx_inscr` (`inscription_code`),
  KEY `idx_etudiant` (`etudiant_code`),
  KEY `idx_piece` (`piece_code`),
  KEY `idx_dossier_etudiant_statut_depot` (`statut_depot`),
  KEY `idx_dossier_etudiant_user_code` (`user_code`),
  KEY `idx_dossier_etudiant_etablissement_code` (`etablissement_code`),
  KEY `idx_dos_insc_piece_statut` (`inscription_code`, `piece_code`, `statut_depot`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `dossier_etudiant`
--

LOCK TABLES `dossier_etudiant` WRITE;
/*!40000 ALTER TABLE `dossier_etudiant` DISABLE KEYS */
;
/*!40000 ALTER TABLE `dossier_etudiant` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `emplois_temps`
--

DROP TABLE IF EXISTS `emplois_temps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `emplois_temps` (
  `id_emploi` int NOT NULL AUTO_INCREMENT,
  `code_emploi` varchar(50) NOT NULL,
  `classe_code` varchar(50) NOT NULL,
  `matiere_code` varchar(50) NOT NULL,
  `enseignant_code` varchar(50) NOT NULL,
  `salle_code` varchar(50) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `user_code` varchar(50) NOT NULL,
  `jour` enum(
    'lundi',
    'mardi',
    'mercredi',
    'jeudi',
    'vendredi',
    'samedi',
    'dimanche'
  ) NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `created_at_emploi` datetime DEFAULT NULL,
  `updated_at_emploi` datetime DEFAULT NULL,
  `statut_emploi` enum('actif', 'inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_emploi`),
  KEY `idx_emplois_temps_code_emploi` (`code_emploi`),
  KEY `idx_emplois_temps_classe_code` (`classe_code`),
  KEY `idx_emplois_temps_matiere_code` (`matiere_code`),
  KEY `idx_emplois_temps_enseignant_code` (`enseignant_code`),
  KEY `idx_emplois_temps_salle_code` (`salle_code`),
  KEY `idx_emplois_temps_etablissement_code` (`etablissement_code`),
  KEY `idx_emplois_temps_annee_code` (`annee_code`),
  KEY `idx_emplois_temps_user_code` (`user_code`),
  KEY `idx_emplois_temps_statut_emploi` (`statut_emploi`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `emplois_temps`
--

LOCK TABLES `emplois_temps` WRITE;
/*!40000 ALTER TABLE `emplois_temps` DISABLE KEYS */
;
/*!40000 ALTER TABLE `emplois_temps` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `enseignant_matiere`
--

DROP TABLE IF EXISTS `enseignant_matiere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `enseignant_matiere` (
  `id_enseignant_matiere` int NOT NULL AUTO_INCREMENT,
  `enseignant_code` varchar(50) NOT NULL,
  `matiere_code` varchar(50) NOT NULL,
  `classe_code` varchar(50) DEFAULT NULL,
  `coefficient` decimal(4, 2) NOT NULL DEFAULT '1.00',
  `etablissement_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `created_at_enseignant_matiere` datetime NOT NULL,
  `updated_at_enseignant_matiere` datetime DEFAULT NULL,
  `statut_enseignant_matiere` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`id_enseignant_matiere`),
  UNIQUE KEY `uq_matiere_classe_annee` (`matiere_code`, `classe_code`, `annee_code`),
  KEY `idx_enseignant_matiere_enseignant_code` (`enseignant_code`),
  KEY `idx_enseignant_matiere_classe_code` (`classe_code`),
  KEY `idx_enseignant_matiere_etablissement_code` (`etablissement_code`),
  KEY `idx_enseignant_matiere_annee_code` (`annee_code`),
  KEY `idx_enseignant_matiere_user_code` (`user_code`),
  KEY `idx_enseignant_matiere_statut_enseignant_matiere` (`statut_enseignant_matiere`),
  KEY `idx_ensmat_classe_mat_annee` (`classe_code`, `matiere_code`, `annee_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `enseignant_matiere`
--

LOCK TABLES `enseignant_matiere` WRITE;
/*!40000 ALTER TABLE `enseignant_matiere` DISABLE KEYS */
;
/*!40000 ALTER TABLE `enseignant_matiere` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `enseignants`
--

DROP TABLE IF EXISTS `enseignants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `enseignants` (
  `id_enseignant` int NOT NULL AUTO_INCREMENT,
  `code_enseignant` varchar(50) NOT NULL,
  `statut_enseignant` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_enseignant` datetime NOT NULL,
  `updated_at_enseignant` datetime DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `grade_enseignant` varchar(100) DEFAULT NULL,
  `type_contrat` enum('permanent', 'vacataire', 'prestataire') DEFAULT 'permanent',
  `numero_autorisation` varchar(100) DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  PRIMARY KEY (`id_enseignant`),
  UNIQUE KEY `code_enseignant` (`code_enseignant`),
  KEY `idx_enseignants_statut_enseignant` (`statut_enseignant`),
  KEY `idx_enseignants_user_code` (`user_code`),
  KEY `idx_enseignants_etablissement_code` (`etablissement_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `enseignants`
--

LOCK TABLES `enseignants` WRITE;
/*!40000 ALTER TABLE `enseignants` DISABLE KEYS */
;
/*!40000 ALTER TABLE `enseignants` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `etablissements`
--

DROP TABLE IF EXISTS `etablissements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `etablissements` (
  `id_etablissement` int NOT NULL AUTO_INCREMENT,
  `code_etablissement` varchar(50) NOT NULL,
  `libelle_etablissement` varchar(200) NOT NULL,
  `numero_autorisation_etablissement` varchar(100) DEFAULT NULL,
  `adresse_etablissement` text,
  `telephone_etablissement` varchar(30) DEFAULT NULL,
  `telephone_etablissement2` varchar(30) DEFAULT NULL,
  `email_etablissement` varchar(150) DEFAULT NULL,
  `logo_etablissement` varchar(500) DEFAULT NULL,
  `slogan_etablissement` varchar(200) DEFAULT NULL,
  `created_at_etablissement` datetime NOT NULL,
  `updated_at_etablissement` datetime DEFAULT NULL,
  `statut_etablissement` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `use_slug_filiere` tinyint(1) NOT NULL DEFAULT '0',
  `use_slug_cycle` tinyint(1) NOT NULL DEFAULT '0',
  `use_slug_niveau` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_etablissement`),
  UNIQUE KEY `code_etablissement` (`code_etablissement`),
  KEY `idx_etablissements_statut_etablissement` (`statut_etablissement`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `etablissements`
--

LOCK TABLES `etablissements` WRITE;
/*!40000 ALTER TABLE `etablissements` DISABLE KEYS */
;
INSERT INTO `etablissements`
VALUES (
    1,
    '5454544456',
    'etablissement A',
    '',
    'adresse',
    '0102030405',
    '0302010405',
    'test@gmail.com',
    'public/uploads/logos/logo_1787358264.jpg',
    'test',
    '2026-07-05 15:36:21',
    NULL,
    'actif',
    1,
    1,
    1
  );
/*!40000 ALTER TABLE `etablissements` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `etudiants` (
  `id_etudiant` int NOT NULL AUTO_INCREMENT,
  `code_etudiant` varchar(50) NOT NULL,
  `matricule_etudiant` varchar(50) NOT NULL,
  `matricule_menet` varchar(50) DEFAULT NULL,
  `matricule_mesrs` varchar(50) DEFAULT NULL,
  `nom_etudiant` varchar(100) NOT NULL,
  `prenom_etudiant` varchar(255) NOT NULL,
  `date_naissance_etudiant` date DEFAULT NULL,
  `lieu_naissance_etudiant` varchar(255) DEFAULT NULL,
  `sexe_etudiant` varchar(25) DEFAULT NULL,
  `nationalite_etudiant` varchar(100) DEFAULT NULL,
  `lieu_residence_etudiant` text,
  `telephone_etudiant` varchar(50) DEFAULT NULL,
  `email_etudiant` varchar(150) DEFAULT NULL,
  `photo_etudiant` varchar(255) DEFAULT NULL,
  `password_etudiant` varchar(255) DEFAULT NULL,
  `numero_cni` varchar(50) DEFAULT NULL,
  `statut_etudiant` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `etablissement_origine` varchar(255) DEFAULT NULL,
  `created_at_etudiant` datetime NOT NULL,
  `updated_at_etudiant` datetime DEFAULT NULL,
  `user_code` varchar(50) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  PRIMARY KEY (`id_etudiant`),
  UNIQUE KEY `code_etudiant` (`code_etudiant`),
  UNIQUE KEY `matricule_etudiant` (`matricule_etudiant`),
  KEY `idx_etudiants_statut_etudiant` (`statut_etudiant`),
  KEY `idx_etudiants_user_code` (`user_code`),
  KEY `idx_etudiants_etablissement_code` (`etablissement_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `etudiants`
--

LOCK TABLES `etudiants` WRITE;
/*!40000 ALTER TABLE `etudiants` DISABLE KEYS */
;
/*!40000 ALTER TABLE `etudiants` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `evenements`
--

DROP TABLE IF EXISTS `evenements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `evenements` (
  `id_evenement` int NOT NULL AUTO_INCREMENT,
  `titre_evenement` varchar(255) NOT NULL,
  `image_evenement` varchar(255) DEFAULT NULL,
  `description_evenement` text,
  `is_principal_evenement` tinyint(1) DEFAULT '0',
  `date_creation_evenement` datetime DEFAULT NULL,
  `date_modification_evenement` datetime DEFAULT NULL,
  `statut_evenement` enum('actif', 'innactif') DEFAULT 'actif',
  PRIMARY KEY (`id_evenement`),
  KEY `idx_evenements_statut_evenement` (`statut_evenement`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `evenements`
--

LOCK TABLES `evenements` WRITE;
/*!40000 ALTER TABLE `evenements` DISABLE KEYS */
;
/*!40000 ALTER TABLE `evenements` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `filiere_cycles`
--

DROP TABLE IF EXISTS `filiere_cycles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `filiere_cycles` (
  `id_filiere_cycle` int NOT NULL AUTO_INCREMENT,
  `code_filiere_cycle` varchar(50) NOT NULL,
  `filiere_code` varchar(50) NOT NULL,
  `cycle_code` varchar(50) NOT NULL,
  `niveau_code` varchar(50) DEFAULT NULL,
  `type_filiere` enum('INDUSTRIELLE', 'TERTIAIRE') DEFAULT NULL,
  `etablissement_code` varchar(50) DEFAULT '5454544456',
  `statut_filiere_cycle` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_filiere_cycle` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_filiere_cycle` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_filiere_cycle`),
  UNIQUE KEY `uk_code_filiere_cycle` (`code_filiere_cycle`),
  UNIQUE KEY `uk_filiere_cycle_pair` (`filiere_code`, `cycle_code`, `niveau_code`),
  KEY `idx_fc_niveau` (`niveau_code`),
  KEY `idx_filiere_cycles_cycle_code` (`cycle_code`),
  KEY `idx_filiere_cycles_type_filiere` (`type_filiere`),
  KEY `idx_filiere_cycles_etablissement_code` (`etablissement_code`),
  KEY `idx_filiere_cycles_statut_filiere_cycle` (`statut_filiere_cycle`),
  KEY `idx_filiere_cycles_user_code` (`user_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `filiere_cycles`
--

LOCK TABLES `filiere_cycles` WRITE;
/*!40000 ALTER TABLE `filiere_cycles` DISABLE KEYS */
;
INSERT INTO `filiere_cycles`
VALUES (
    1,
    'FCYC-ZW8JQ9L0',
    'FIL-XMA5DLZF',
    'CYC-RHUFSGDY',
    'NIV-EPQZ6JJ1',
    NULL,
    '5454544456',
    'actif',
    '2026-09-18 14:25:05',
    '2026-09-19 17:51:47',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    2,
    'FCYC-LU6PT7OF',
    'FIL-5RZLAV7W',
    'CYC-RHUFSGDY',
    'NIV-EPQZ6JJ1',
    NULL,
    '5454544456',
    'actif',
    '2026-09-18 14:25:17',
    '2026-09-19 17:52:12',
    '5wBEh2OfI00frxk8ITPf'
  );
/*!40000 ALTER TABLE `filiere_cycles` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `filieres`
--

DROP TABLE IF EXISTS `filieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `filieres` (
  `id_filiere` int NOT NULL AUTO_INCREMENT,
  `code_filiere` varchar(50) NOT NULL,
  `libelle_filiere` varchar(150) NOT NULL,
  `slug_filiere` varchar(100) DEFAULT NULL,
  `description_filiere` text,
  `type_filiere` enum('INDUSTRIELLE', 'TERTIAIRE') DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `created_at_filiere` datetime NOT NULL,
  `updated_at_filiere` datetime DEFAULT NULL,
  `statut_filiere` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_filiere`),
  UNIQUE KEY `code_filiere` (`code_filiere`),
  KEY `idx_filieres_type_filiere` (`type_filiere`),
  KEY `idx_filieres_etablissement_code` (`etablissement_code`),
  KEY `idx_filieres_statut_filiere` (`statut_filiere`),
  KEY `idx_filieres_user_code` (`user_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `filieres`
--

LOCK TABLES `filieres` WRITE;
/*!40000 ALTER TABLE `filieres` DISABLE KEYS */
;
INSERT INTO `filieres`
VALUES (
    1,
    'FIL-XMA5DLZF',
    'INFORMATIQUE ET DÉVELOPPEUR D\'APPLICATIONS',
    'IDA',
    '',
    'INDUSTRIELLE',
    '5454544456',
    '2026-09-18 14:05:47',
    NULL,
    'actif',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    2,
    'FIL-5RZLAV7W',
    'RÉSEAUX INFORMATIQUES ET TÉLÉCOMMUNICATIONS',
    'RIT',
    '',
    'INDUSTRIELLE',
    '5454544456',
    '2026-09-18 14:22:11',
    NULL,
    'actif',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    3,
    'FIL-RPITM7VE',
    'RESSOURCES HUMAINES ET COMMUNICATION',
    'RHCOM',
    '',
    'TERTIAIRE',
    '5454544456',
    '2026-09-18 14:24:11',
    NULL,
    'actif',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    4,
    'FIL-CF90EZLC',
    'GESTION COMMERCIALE',
    'GEC',
    '',
    'TERTIAIRE',
    '5454544456',
    '2026-09-18 14:24:47',
    NULL,
    'actif',
    '5wBEh2OfI00frxk8ITPf'
  );
/*!40000 ALTER TABLE `filieres` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `fonctions`
--

DROP TABLE IF EXISTS `fonctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `fonctions` (
  `id_fonction` int NOT NULL AUTO_INCREMENT,
  `code_fonction` varchar(50) NOT NULL,
  `libelle_fonction` varchar(150) NOT NULL,
  `description_fonction` text,
  `etablissement_code` varchar(50) DEFAULT '5454544456',
  `statut_fonction` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_fonction` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_fonction` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_fonction`),
  UNIQUE KEY `uk_code_fonction` (`code_fonction`),
  UNIQUE KEY `uk_libelle_fonction` (`libelle_fonction`),
  KEY `idx_fonctions_etablissement_code` (`etablissement_code`),
  KEY `idx_fonctions_statut_fonction` (`statut_fonction`),
  KEY `idx_fonctions_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `fonctions`
--

LOCK TABLES `fonctions` WRITE;
/*!40000 ALTER TABLE `fonctions` DISABLE KEYS */
;
/*!40000 ALTER TABLE `fonctions` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `frais_annexes`
--

DROP TABLE IF EXISTS `frais_annexes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `frais_annexes` (
  `id_frais_annexe` int NOT NULL AUTO_INCREMENT,
  `code_frais_annexe` varchar(50) NOT NULL,
  `libelle_frais_annexe` varchar(150) NOT NULL,
  `categorie_frais_annexe` enum('inscription', 'autre') NOT NULL DEFAULT 'autre',
  `type_filiere` enum('INDUSTRIELLE', 'TERTIAIRE', 'TOUT') NOT NULL DEFAULT 'TOUT',
  `niveau_code` varchar(50) DEFAULT NULL,
  `montant_frais_annexe` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `annee_code` varchar(50) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `statut_frais_annexe` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `user_code` varchar(50) DEFAULT NULL,
  `created_at_frais_annexe` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_frais_annexe` datetime DEFAULT NULL,
  PRIMARY KEY (`id_frais_annexe`),
  UNIQUE KEY `code_frais_annexe` (`code_frais_annexe`),
  KEY `idx_frais_annexe_lookup` (
    `type_filiere`,
    `annee_code`,
    `etablissement_code`
  ),
  KEY `idx_frais_annexe_niveau` (`niveau_code`),
  KEY `idx_frais_annexes_annee_code` (`annee_code`),
  KEY `idx_frais_annexes_etablissement_code` (`etablissement_code`),
  KEY `idx_frais_annexes_statut_frais_annexe` (`statut_frais_annexe`),
  KEY `idx_frais_annexes_user_code` (`user_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `frais_annexes`
--

LOCK TABLES `frais_annexes` WRITE;
/*!40000 ALTER TABLE `frais_annexes` DISABLE KEYS */
;
INSERT INTO `frais_annexes`
VALUES (
    1,
    'FRAIS-ANN-IND2026',
    'Frais Annexes Filières Industrielles (Uniforme, Blouse, Badge, Assurance)',
    'inscription',
    'INDUSTRIELLE',
    NULL,
    60000.00,
    'ANN-LS4V0ORV',
    '5454544456',
    'actif',
    '5wBEh2OfI00frxk8ITPf',
    '2026-09-19 20:30:21',
    NULL
  ),
  (
    2,
    'FRAIS-ANN-TER2026',
    'Frais Annexes Filières Tertiaires (Uniforme Ville, Badge, Assurance)',
    'inscription',
    'TERTIAIRE',
    NULL,
    45000.00,
    'ANN-LS4V0ORV',
    '5454544456',
    'actif',
    '5wBEh2OfI00frxk8ITPf',
    '2026-09-19 20:30:21',
    NULL
  );
/*!40000 ALTER TABLE `frais_annexes` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `galerie_medias`
--

DROP TABLE IF EXISTS `galerie_medias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `galerie_medias` (
  `id_media` int NOT NULL AUTO_INCREMENT,
  `code_media` varchar(50) NOT NULL,
  `galerie_code` varchar(50) NOT NULL,
  `titre_media` varchar(255) DEFAULT NULL,
  `description_media` text,
  `chemin_media` varchar(255) NOT NULL,
  `statut_media` enum('actif', 'brouillon', 'archive') NOT NULL DEFAULT 'actif',
  `created_at_media` datetime NOT NULL,
  `updated_at_media` datetime DEFAULT NULL,
  `ordre_media` int DEFAULT '1',
  `miniature_media` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_media`),
  UNIQUE KEY `code_media` (`code_media`),
  KEY `galerie_code` (`galerie_code`),
  KEY `idx_galerie_medias_statut_media` (`statut_media`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `galerie_medias`
--

LOCK TABLES `galerie_medias` WRITE;
/*!40000 ALTER TABLE `galerie_medias` DISABLE KEYS */
;
/*!40000 ALTER TABLE `galerie_medias` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `galeries`
--

DROP TABLE IF EXISTS `galeries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `galeries` (
  `id_galerie` int NOT NULL AUTO_INCREMENT,
  `code_galerie` varchar(50) NOT NULL,
  `type_galerie` enum('photo', 'video') NOT NULL,
  `titre_galerie` varchar(255) NOT NULL,
  `url_fichier` varchar(255) DEFAULT NULL,
  `description_galerie` text,
  `statut_galerie` enum('actif', 'brouillon', 'archive') NOT NULL DEFAULT 'actif',
  `etablissement_code` varchar(50) NOT NULL DEFAULT 'EICG001',
  `user_code` varchar(50) DEFAULT NULL,
  `created_at_galerie` datetime NOT NULL,
  `updated_at_galerie` datetime DEFAULT NULL,
  PRIMARY KEY (`id_galerie`),
  UNIQUE KEY `code_galerie` (`code_galerie`),
  KEY `idx_galeries_statut_galerie` (`statut_galerie`),
  KEY `idx_galeries_etablissement_code` (`etablissement_code`),
  KEY `idx_galeries_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `galeries`
--

LOCK TABLES `galeries` WRITE;
/*!40000 ALTER TABLE `galeries` DISABLE KEYS */
;
/*!40000 ALTER TABLE `galeries` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `inscriptions` (
  `id_inscription` int NOT NULL AUTO_INCREMENT,
  `code_inscription` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `etudiant_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `classe_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `etablissement_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `annee_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at_inscription` datetime DEFAULT NULL,
  `updated_at_inscription` datetime DEFAULT NULL,
  `montant_scolarite_inscription` decimal(10, 2) DEFAULT '0.00',
  `canal_inscription` enum('guichet', 'en_ligne') NOT NULL DEFAULT 'guichet',
  `affectation_etat` enum('oui', 'non') NOT NULL DEFAULT 'non',
  `statut_inscription` enum('en_attente', 'valide', 'solde', 'annule') NOT NULL DEFAULT 'valide',
  `photo_inscription` varchar(255) DEFAULT NULL,
  `date_photo` datetime DEFAULT NULL,
  PRIMARY KEY (`id_inscription`),
  UNIQUE KEY `uq_inscription` (`etudiant_code`, `annee_code`),
  KEY `idx_inscriptions_canal` (`canal_inscription`),
  KEY `idx_inscriptions_code_inscription` (`code_inscription`),
  KEY `idx_inscriptions_classe_code` (`classe_code`),
  KEY `idx_inscriptions_etablissement_code` (`etablissement_code`),
  KEY `idx_inscriptions_annee_code` (`annee_code`),
  KEY `idx_inscriptions_user_code` (`user_code`),
  KEY `idx_inscriptions_affectation_etat` (`affectation_etat`),
  KEY `idx_inscriptions_statut_inscription` (`statut_inscription`),
  KEY `idx_inscriptions_date_photo` (`date_photo`),
  KEY `idx_insc_annee_classe_statut` (
    `annee_code`,
    `classe_code`,
    `statut_inscription`
  )
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `inscriptions`
--

LOCK TABLES `inscriptions` WRITE;
/*!40000 ALTER TABLE `inscriptions` DISABLE KEYS */
;
/*!40000 ALTER TABLE `inscriptions` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `logs_activites`
--

DROP TABLE IF EXISTS `logs_activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `logs_activites` (
  `id_log` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `user_nom` varchar(200) DEFAULT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `entite_id` varchar(100) DEFAULT NULL,
  `description` text,
  `details_json` longtext,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_logs_user_id` (`user_id`),
  KEY `idx_logs_user_code` (`user_code`),
  KEY `idx_logs_etab` (`etablissement_code`),
  KEY `idx_logs_action` (`action`),
  KEY `idx_logs_module` (`module`),
  KEY `idx_logs_created_at` (`created_at`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `logs_activites`
--

LOCK TABLES `logs_activites` WRITE;
/*!40000 ALTER TABLE `logs_activites` DISABLE KEYS */
;
INSERT INTO `logs_activites`
VALUES (
    1,
    NULL,
    NULL,
    'Système',
    '5454544456',
    'DECONNEXION',
    'authentification',
    '',
    'Déconnexion du système de ',
    NULL,
    '::1',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',
    '2026-11-19 01:17:32'
  ),
  (
    2,
    1,
    '5wBEh2OfI00frxk8ITPf',
    'Admin Admin',
    '5454544456',
    'CONNEXION',
    'authentification',
    '1',
    'Connexion réussie au système (admin@gmail.com)',
    '{\"email\":\"admin@gmail.com\"}',
    '::1',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36',
    '2026-11-19 01:17:35'
  );
/*!40000 ALTER TABLE `logs_activites` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `maquettes_pedagogiques`
--

DROP TABLE IF EXISTS `maquettes_pedagogiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `maquettes_pedagogiques` (
  `id_maquette` int NOT NULL AUTO_INCREMENT,
  `code_maquette` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `filiere_code` varchar(50) NOT NULL,
  `niveau_code` varchar(50) NOT NULL,
  `matiere_code` varchar(50) NOT NULL,
  `categorie_matiere` enum('GENERALE', 'PROFESSIONNELLE') NOT NULL DEFAULT 'GENERALE',
  `coefficient` decimal(4, 2) NOT NULL DEFAULT '1.00',
  `etablissement_code` varchar(50) DEFAULT NULL,
  `statut_maquette` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `user_code` varchar(50) DEFAULT NULL,
  `created_at_maquette` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_maquette` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_maquette`),
  UNIQUE KEY `uk_code_maquette` (`code_maquette`),
  UNIQUE KEY `uk_maquette_unique` (
    `annee_code`,
    `filiere_code`,
    `niveau_code`,
    `matiere_code`
  ),
  KEY `idx_maquette_filiere_niveau` (`filiere_code`, `niveau_code`),
  KEY `idx_maquette_matiere` (`matiere_code`),
  KEY `idx_maquette_annee` (`annee_code`),
  KEY `idx_maquette_etab` (`etablissement_code`),
  KEY `idx_maquette_statut` (`statut_maquette`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `maquettes_pedagogiques`
--

LOCK TABLES `maquettes_pedagogiques` WRITE;
/*!40000 ALTER TABLE `maquettes_pedagogiques` DISABLE KEYS */
;
/*!40000 ALTER TABLE `maquettes_pedagogiques` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `matieres`
--

DROP TABLE IF EXISTS `matieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `matieres` (
  `id_matiere` int NOT NULL AUTO_INCREMENT,
  `code_matiere` varchar(30) NOT NULL,
  `libelle_matiere` varchar(150) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `created_at_matiere` datetime NOT NULL,
  `updated_at_matiere` datetime DEFAULT NULL,
  `statut_matiere` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`id_matiere`),
  UNIQUE KEY `code_matiere` (`code_matiere`),
  KEY `idx_matieres_etablissement_code` (`etablissement_code`),
  KEY `idx_matieres_user_code` (`user_code`),
  KEY `idx_matieres_statut_matiere` (`statut_matiere`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `matieres`
--

LOCK TABLES `matieres` WRITE;
/*!40000 ALTER TABLE `matieres` DISABLE KEYS */
;
/*!40000 ALTER TABLE `matieres` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `messages` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `objet_message` varchar(255) NOT NULL,
  `description_message` text NOT NULL,
  `statut_message` enum('en_attente', 'envoye', 'vue', 'archive') DEFAULT 'en_attente',
  `created_at_message` datetime DEFAULT NULL,
  `update_at_message` datetime DEFAULT NULL,
  PRIMARY KEY (`id_message`),
  KEY `idx_messages_statut_message` (`statut_message`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */
;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `niveaux`
--

DROP TABLE IF EXISTS `niveaux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `niveaux` (
  `id_niveau` int NOT NULL AUTO_INCREMENT,
  `code_niveau` varchar(50) NOT NULL,
  `libelle_niveau` varchar(100) NOT NULL,
  `slug_niveau` varchar(100) DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `created_at_niveau` datetime NOT NULL,
  `updated_at_niveau` datetime DEFAULT NULL,
  `statut_niveau` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`id_niveau`),
  UNIQUE KEY `uq_niveau_filiere` (`code_niveau`),
  KEY `idx_niveaux_etablissement_code` (`etablissement_code`),
  KEY `idx_niveaux_statut_niveau` (`statut_niveau`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `niveaux`
--

LOCK TABLES `niveaux` WRITE;
/*!40000 ALTER TABLE `niveaux` DISABLE KEYS */
;
INSERT INTO `niveaux`
VALUES (
    1,
    'NIV-EPQZ6JJ1',
    'PREMIERE ANNEE',
    '1 A',
    '5454544456',
    '2026-09-18 14:26:14',
    NULL,
    'actif'
  ),
  (
    2,
    'NIV-L6TC826S',
    'DEUXIEME ANNEE',
    '2A',
    '5454544456',
    '2026-09-18 14:26:50',
    NULL,
    'actif'
  ),
  (
    3,
    'NIV-NGA3819E',
    'LICENSES ',
    'L3',
    '5454544456',
    '2026-09-18 14:27:48',
    NULL,
    'actif'
  );
/*!40000 ALTER TABLE `niveaux` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `notes` (
  `id_note` int NOT NULL AUTO_INCREMENT,
  `code_note` varchar(50) NOT NULL,
  `valeur_note` decimal(5, 2) NOT NULL,
  `composition_code` varchar(50) NOT NULL,
  `composition_matiere_id` int DEFAULT NULL,
  `etudiant_code` varchar(50) NOT NULL,
  `observations` text,
  `inscription_code` varchar(50) NOT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `created_at_note` datetime NOT NULL,
  `updated_at_note` datetime DEFAULT NULL,
  `statut_note` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`id_note`),
  UNIQUE KEY `code_note` (`code_note`),
  KEY `idx_notes_comp` (`composition_code`),
  KEY `idx_notes_etudiant` (`etudiant_code`),
  KEY `idx_notes_inscription` (`inscription_code`),
  KEY `idx_notes_composition_matiere_id` (`composition_matiere_id`),
  KEY `idx_notes_composition_code` (`composition_code`),
  KEY `idx_notes_inscription_code` (`inscription_code`),
  KEY `idx_notes_user_code` (`user_code`),
  KEY `idx_notes_etablissement_code` (`etablissement_code`),
  KEY `idx_notes_statut_note` (`statut_note`),
  KEY `idx_notes_insc_comp_statut` (
    `inscription_code`,
    `composition_code`,
    `statut_note`
  )
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */
;
/*!40000 ALTER TABLE `notes` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `paiements` (
  `id_paiement` int NOT NULL AUTO_INCREMENT,
  `code_paiement` varchar(50) NOT NULL,
  `montant_paiement` decimal(12, 2) NOT NULL,
  `date_paiement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_paiement` enum(
    'en_attente',
    'confirme',
    'annule',
    'rembourse',
    'echoue'
  ) NOT NULL DEFAULT 'confirme',
  `canal_paiement` enum('guichet', 'en_ligne') NOT NULL DEFAULT 'guichet',
  `reference_paiement` varchar(100) DEFAULT NULL,
  `observations` text,
  `type_paiement` varchar(100) NOT NULL,
  `categorie_paiement` enum('SCOLARITE', 'FRAIS_ANNEXES', 'ARRIERE') NOT NULL DEFAULT 'SCOLARITE',
  `mode_paiement` varchar(50) NOT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `annee_code` varchar(50) DEFAULT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `inscription_code` varchar(50) NOT NULL,
  `tranche_code` varchar(50) DEFAULT NULL,
  `session_caisse_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_paiement`),
  UNIQUE KEY `code_paiement` (`code_paiement`),
  KEY `idx_paiements_canal` (`canal_paiement`),
  KEY `idx_paiements_date_paiement` (`date_paiement`),
  KEY `idx_paiements_statut_paiement` (`statut_paiement`),
  KEY `idx_paiements_categorie_paiement` (`categorie_paiement`),
  KEY `idx_paiements_user_code` (`user_code`),
  KEY `idx_paiements_annee_code` (`annee_code`),
  KEY `idx_paiements_etablissement_code` (`etablissement_code`),
  KEY `idx_paiements_inscription_code` (`inscription_code`),
  KEY `idx_paiements_tranche_code` (`tranche_code`),
  KEY `idx_paiements_session_caisse_code` (`session_caisse_code`),
  KEY `idx_paie_annee_statut_cat` (
    `annee_code`,
    `statut_paiement`,
    `categorie_paiement`
  )
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `paiements`
--

LOCK TABLES `paiements` WRITE;
/*!40000 ALTER TABLE `paiements` DISABLE KEYS */
;
/*!40000 ALTER TABLE `paiements` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `parents`
--

DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `parents` (
  `id_parent` int NOT NULL AUTO_INCREMENT,
  `code_parent` varchar(100) NOT NULL,
  `nom_pere` varchar(255) DEFAULT NULL,
  `telephone_pere` varchar(30) DEFAULT NULL,
  `profession_pere` varchar(100) DEFAULT NULL,
  `nom_mere` varchar(255) DEFAULT NULL,
  `telephone_mere` varchar(30) DEFAULT NULL,
  `profession_mere` varchar(100) DEFAULT NULL,
  `nom_tuteur` varchar(255) DEFAULT NULL,
  `telephone_tuteur` varchar(30) DEFAULT NULL,
  `profession_tuteur` varchar(100) DEFAULT NULL,
  `parent_habitation` varchar(255) DEFAULT NULL,
  `created_at_parent` datetime NOT NULL,
  `updated_at_parent` datetime DEFAULT NULL,
  `etudiant_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `etablissement_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `statut_parent` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`id_parent`),
  KEY `idx_parents_statut` (`statut_parent`),
  KEY `idx_parents_code_parent` (`code_parent`),
  KEY `idx_parents_etudiant_code` (`etudiant_code`),
  KEY `idx_parents_user_code` (`user_code`),
  KEY `idx_parents_etablissement_code` (`etablissement_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `parents`
--

LOCK TABLES `parents` WRITE;
/*!40000 ALTER TABLE `parents` DISABLE KEYS */
;
/*!40000 ALTER TABLE `parents` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `permissions` (
  `id_permission` int NOT NULL AUTO_INCREMENT,
  `code_permission` varchar(50) NOT NULL,
  `libelle_permission` varchar(150) NOT NULL,
  `module_permission` varchar(50) NOT NULL,
  `statut_permission` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_permission` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_permission`),
  UNIQUE KEY `code_permission` (`code_permission`),
  KEY `idx_permissions_statut_permission` (`statut_permission`)
) ENGINE = InnoDB AUTO_INCREMENT = 190 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */
;
INSERT INTO `permissions`
VALUES (
    2,
    'MANAGE_CYCLES',
    'Créer & Modifier les Cycles',
    'ACADEMIQUE',
    'actif',
    '2026-08-21 22:59:34'
  ),
  (
    3,
    'MANAGE_FILIERES',
    'Créer & Modifier les Filières',
    'ACADEMIQUE',
    'actif',
    '2026-08-21 22:59:34'
  ),
  (
    4,
    'MANAGE_CLASSES',
    'Créer & Modifier les Classes',
    'ACADEMIQUE',
    'actif',
    '2026-08-21 22:59:34'
  ),
  (
    5,
    'MANAGE_MATIERES',
    'Créer & Modifier les Matières',
    'ACADEMIQUE',
    'actif',
    '2026-08-21 22:59:34'
  ),
  (
    16,
    'VIEW_REPORTS',
    'Impression Bulletins et Procès-Verbaux',
    'PEDAGOGIE',
    'actif',
    '2026-08-21 22:59:34'
  ),
  (
    17,
    'MANAGE_USERS',
    'Créer & Gérer les Comptes d\'Accès',
    'ADMINISTRATION',
    'actif',
    '2026-08-21 22:59:34'
  ),
  (
    18,
    'MANAGE_ROLES',
    'Créer, Modifier & Attribuer les Rôles',
    'ADMINISTRATION',
    'actif',
    '2026-08-21 22:59:34'
  ),
  (
    20,
    'VIEW_ANNEES',
    'Consulter les Années Académiques',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    21,
    'MANAGE_ANNEES',
    'Créer & Configurer les Années Académiques',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    22,
    'VIEW_CYCLES',
    'Consulter les Cycles de Formation',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    23,
    'VIEW_FILIERES',
    'Consulter les Filières & Spécialités',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    24,
    'VIEW_NIVEAUX',
    'Consulter les Niveaux d\'Études',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    25,
    'MANAGE_NIVEAUX',
    'Créer & Modifier les Niveaux',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    26,
    'VIEW_CLASSES',
    'Consulter les Classes & Groupes',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    27,
    'VIEW_SEMESTRES',
    'Consulter les Semestres & Sessions',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    28,
    'MANAGE_SEMESTRES',
    'Créer & Configurer les Semestres',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    29,
    'VIEW_MATIERES',
    'Consulter les Matières & Unités (UE)',
    'ACADEMIQUE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    30,
    'VIEW_ENSEIGNANTS',
    'Consulter le Corps Enseignant',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    31,
    'MANAGE_ENSEIGNANTS',
    'Créer & Gérer les Profils Enseignants',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    32,
    'VIEW_AFFECTATIONS',
    'Consulter les Affectations de Cours',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    33,
    'MANAGE_AFFECTATIONS',
    'Affecter les Matières & Coefficients',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    34,
    'VIEW_EMPLOI_TEMPS',
    'Consulter les Emplois du Temps',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    35,
    'MANAGE_EMPLOI_TEMPS',
    'Planifier les Emplois du Temps',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    36,
    'VIEW_NOTES',
    'Consulter les Notes & Évaluations',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    37,
    'ENTER_NOTES',
    'Saisir & Modifier les Notes',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    38,
    'LOCK_NOTES',
    'Valider & Clôturer les Notes',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    39,
    'VIEW_BULLETINS',
    'Consulter les Bulletins & Moyennes',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    40,
    'GENERATE_BULLETINS',
    'Générer & Imprimer les Bulletins',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    41,
    'VIEW_EXAMENS',
    'Consulter les Examens & Délibérations',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    42,
    'MANAGE_EXAMENS',
    'Organiser les Sessions d\'Examens',
    'PEDAGOGIE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    43,
    'VIEW_ETUDIANTS',
    'Consulter le Registre des Étudiants',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    44,
    'MANAGE_ETUDIANTS',
    'Créer & Gérer les Dossiers Étudiants',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    45,
    'VIEW_INSCRIPTIONS',
    'Consulter les Inscriptions & Réinscriptions',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    46,
    'MANAGE_INSCRIPTIONS',
    'Valider les Inscriptions d\'Élèves',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    47,
    'VIEW_ABSENCES',
    'Consulter le Registre des Absences',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    48,
    'MANAGE_ABSENCES',
    'Saisir & Justifier les Absences',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    49,
    'VIEW_DISCIPLINE',
    'Consulter les Sanctions Disciplinaires',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    50,
    'MANAGE_DISCIPLINE',
    'Émettre des Sanctions & Avertissements',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    51,
    'PRINT_ATTESTATIONS',
    'Imprimer Certificats & Cartes Étudiants',
    'SCOLAIRITE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    52,
    'VIEW_FRAIS_SCOLARITE',
    'Consulter la Grille des Frais & Échéances',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    53,
    'MANAGE_FRAIS_SCOLARITE',
    'Configurer les Frais & Échéanciers',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    54,
    'VIEW_PAIEMENTS',
    'Consulter l\'Historique des Règlements',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    55,
    'RECORD_PAIEMENTS',
    'Encaisser les Frais & Émettre les Reçus',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    56,
    'CANCEL_PAIEMENTS',
    'Annuler & Rembourser un Paiement',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    57,
    'MANAGE_MORATOIRES',
    'Accorder & Suivre les Moratoires',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    58,
    'VIEW_DEPENSES',
    'Consulter le Journal des Dépenses',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    59,
    'RECORD_DEPENSES',
    'Saisir & Justifier les Dépenses',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    60,
    'VALIDATE_DEPENSES',
    'Valider & Ordonnancer les Dépenses',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    61,
    'VIEW_RAPPORTS_FINANCIERS',
    'Consulter les Bilans & Clôtures de Caisse',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    62,
    'EXPORT_FINANCE',
    'Exporter les États Comptables',
    'FINANCE',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    63,
    'VIEW_EVENEMENTS',
    'Consulter le Calendrier & Événements',
    'COMMUNICATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    64,
    'MANAGE_EVENEMENTS',
    'Créer & Publier les Événements',
    'COMMUNICATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    65,
    'VIEW_DOCUMENTS',
    'Consulter la Bibliothèque & Documents',
    'COMMUNICATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    66,
    'MANAGE_DOCUMENTS',
    'Publier & Archiver les Documents',
    'COMMUNICATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    67,
    'SEND_MESSAGES',
    'Diffuser des Alertes & Notifications',
    'COMMUNICATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    68,
    'VIEW_HISTORIQUE',
    'Consulter les Journaux d\'Audit & Logs',
    'COMMUNICATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    69,
    'VIEW_USERS',
    'Consulter les Utilisateurs du Système',
    'ADMINISTRATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    70,
    'VIEW_ROLES',
    'Consulter les Rôles RBAC',
    'ADMINISTRATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    71,
    'VIEW_PERMISSIONS',
    'Consulter les Permissions Granulaires',
    'ADMINISTRATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    72,
    'MANAGE_PERMISSIONS',
    'Gérer le Registre des Permissions',
    'ADMINISTRATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    73,
    'MANAGE_ETABLISSEMENT',
    'Configurer les Paramètres Établissement',
    'ADMINISTRATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    74,
    'MANAGE_FONCTIONS',
    'Gérer les Postes & Fonctions Internes',
    'ADMINISTRATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    75,
    'BACKUP_DATABASE',
    'Sauvegarder & Exporter la Base de Données',
    'ADMINISTRATION',
    'actif',
    '2026-08-26 11:02:29'
  ),
  (
    77,
    'VIEW_DASHBOARD_PEDAGOGIE',
    'Afficher le Tableau de Bord Pédagogie & Études',
    'DASHBOARD',
    'actif',
    '2026-09-12 13:26:21'
  ),
  (
    78,
    'VIEW_DASHBOARD_SCOLARITE',
    'Afficher le Tableau de Bord Scolarité & Admissions',
    'DASHBOARD',
    'actif',
    '2026-09-12 13:26:21'
  ),
  (
    79,
    'VIEW_DASHBOARD_FINANCE',
    'Afficher le Tableau de Bord Finance & Caisse',
    'DASHBOARD',
    'actif',
    '2026-09-12 13:26:21'
  ),
  (
    80,
    'VIEW_DASHBOARD_ENSEIGNANT',
    'Afficher le Tableau de Bord Espace Enseignant',
    'DASHBOARD',
    'actif',
    '2026-09-12 13:26:21'
  ),
  (
    81,
    'VIEW_DASHBOARD_COMMUNICATION',
    'Afficher le Tableau de Bord Communication & Événements',
    'DASHBOARD',
    'actif',
    '2026-09-12 13:26:21'
  ),
  (
    82,
    'VIEW_DASHBOARD_ACTIONS',
    'Afficher les Raccourcis Rapides & Boutons du Dashboard',
    'DASHBOARD',
    'actif',
    '2026-09-12 13:26:21'
  ),
  (
    83,
    'MANAGE_DEPOT_DOSSIERS',
    'Gérer le Dépôt des Dossiers Étudiants',
    'SCOLAIRITE',
    'actif',
    '2026-09-12 14:16:30'
  ),
  (
    84,
    'MANAGE_REMISE_KITS',
    'Gérer le Registre & la Remise des Kits',
    'SCOLAIRITE',
    'actif',
    '2026-09-12 14:16:37'
  ),
  (
    85,
    'CONFIG_ACADEMIQUE',
    'Configuration Académique Globale',
    'ACADEMIQUE',
    'actif',
    '2026-09-17 01:09:21'
  ),
  (
    87,
    'MANAGE_ACCESSOIRES',
    'Créer & Modifier les Accessoires / Kits',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:09:28'
  ),
  (
    88,
    'VIEW_ACCESSOIRES',
    'Consulter les Accessoires & Kits',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:09:28'
  ),
  (
    89,
    'VIEW_REMISE_KITS',
    'Consulter le Registre Remise de Kits',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:09:28'
  ),
  (
    90,
    'MANAGE_PIECES',
    'Créer & Modifier les Pièces à Fournir',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:09:28'
  ),
  (
    91,
    'VIEW_PIECES',
    'Consulter le Répertoire des Pièces à Fournir',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:09:28'
  ),
  (
    92,
    'VIEW_DEPOT_DOSSIERS',
    'Consulter le Dépôt des Dossiers Étudiants',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:09:28'
  ),
  (
    93,
    'VIEW_DASHBOARD_EXECUTIVE',
    'Afficher le Tableau de Bord Exécutif & Synthèse',
    'DASHBOARD',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    94,
    'MANAGE_IMPAYES',
    'Gérer & Traiter les Relances des Impayés',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    95,
    'VIEW_IMPAYES',
    'Consulter l\'Échéancier & la Liste des Impayés',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    96,
    'SEND_RELANCES',
    'Envoyer des Relances d\'Impayés aux Parents',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    97,
    'OUVERTURE_CAISSE',
    'Ouvrir une Session de Caisse',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    98,
    'CLOTURE_CAISSE',
    'Clôturer & Valider la Caisse',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    99,
    'MANAGE_CAISSE',
    'Gérer l\'Administration des Caisses',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    100,
    'MANAGE_PAIEMENTS',
    'Gérer & Valider les Règlements',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    101,
    'MANAGE_TYPES_DEPENSE',
    'Créer & Modifier les Catégories de Dépense',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    102,
    'VIEW_TYPES_DEPENSE',
    'Consulter les Catégories de Dépense',
    'FINANCE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    103,
    'MANAGE_SALLES',
    'Créer & Modifier les Salles de Classe',
    'ACADEMIQUE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    104,
    'VIEW_SALLES',
    'Consulter le Répertoire des Salles',
    'ACADEMIQUE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    105,
    'MANAGE_UE',
    'Créer & Modifier les Unités d\'Enseignement (UE)',
    'ACADEMIQUE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    106,
    'VIEW_UE',
    'Consulter les Unités d\'Enseignement (UE)',
    'ACADEMIQUE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    107,
    'MANAGE_PARENTS',
    'Créer & Modifier les Profils Parents / Tuteurs',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    108,
    'VIEW_PARENTS',
    'Consulter le Répertoire des Parents / Tuteurs',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    110,
    'MANAGE_GALLERY',
    'Gérer les Médias & Albums de la Galerie',
    'COMMUNICATION',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    111,
    'VIEW_GALLERY',
    'Consulter la Galerie Médias & Albums',
    'COMMUNICATION',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    112,
    'MANAGE_COMMUNICATION',
    'Administration du Module Communication & Alertes',
    'COMMUNICATION',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    114,
    'VIEW_FONCTIONS',
    'Consulter le Répertoire des Postes & Fonctions',
    'ADMINISTRATION',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    115,
    'CONFIG_SYSTEM',
    'Configuration Avancée & Paramètres Système',
    'ADMINISTRATION',
    'actif',
    '2026-09-17 01:12:56'
  ),
  (
    116,
    'VIEW_INSCRIPTIONS_SANS_PHOTO',
    'Consulter les Inscriptions Sans Photo',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 17:39:03'
  ),
  (
    117,
    'MANAGE_INSCRIPTIONS_SANS_PHOTO',
    'Gérer & Téléverser la Photo d\'Inscription',
    'SCOLAIRITE',
    'actif',
    '2026-09-17 17:39:03'
  ),
  (
    118,
    'VIEW_ANNEES_ANTERIEURES',
    'Consulter les années antérieures',
    'ANNEES',
    'actif',
    '2026-09-17 23:22:46'
  ),
  (
    119,
    'EDIT_ANNEES_ANTERIEURES',
    'Modifier les années antérieures',
    'ANNEES',
    'actif',
    '2026-09-17 23:22:46'
  ),
  (
    120,
    'MANAGE_DEROGATION_INSCRIPTION',
    'Accorder des Dérogations Administratives d\'Inscription',
    'SCOLAIRITE',
    'actif',
    '2026-09-18 02:06:58'
  ),
  (
    121,
    'MANAGE_FRAIS_ANNEXES',
    'Configurer les Frais Annexes',
    'FINANCE',
    'actif',
    '2026-09-19 20:27:27'
  ),
  (
    122,
    'VIEW_FRAIS_ANNEXES',
    'Consulter la Grille des Frais Annexes',
    'FINANCE',
    'actif',
    '2026-09-19 20:27:27'
  ),
  (
    123,
    'VIEW_COMPTABILITE_ETUDIANTS',
    'Consulter la Comptabilité et Suivi Financier des Étudiants',
    'FINANCE',
    'actif',
    '2026-09-20 03:34:04'
  ),
  (
    124,
    'PRINT_OFFRE_ACADEMIQUE',
    'Imprimer l\'Offre Académique & Parcours de Formation',
    'ACADEMIQUE',
    'actif',
    '2026-09-23 21:57:46'
  ),
  (
    125,
    'PRINT_FILIERES',
    'Imprimer le Catalogue des Filières de Formation',
    'ACADEMIQUE',
    'actif',
    '2026-09-23 22:35:53'
  ),
  (
    126,
    'PRINT_SALLES',
    'Imprimer le Répertoire des Salles de Cours',
    'ACADEMIQUE',
    'actif',
    '2026-09-23 23:13:53'
  ),
  (
    127,
    'PRINT_CLASSES',
    'Imprimer le Répertoire des Classes',
    'ACADEMIQUE',
    'actif',
    '2026-09-24 00:53:42'
  ),
  (
    128,
    'PRINT_FRAIS_SCOLARITE',
    'Imprimer la Grille des Frais de Scolarité & Échéanciers',
    'FINANCE',
    'actif',
    '2026-09-24 01:25:34'
  ),
  (
    129,
    'PRINT_FRAIS_ANNEXES',
    'Imprimer la Grille des Frais Annexes',
    'FINANCE',
    'actif',
    '2026-09-24 02:36:12'
  ),
  (
    130,
    'VIEW_CAISSE_ANNEXE',
    'Consulter la Caisse Annexe & Bilan des Frais Annexes',
    'FINANCE',
    'actif',
    '2026-10-24 02:55:51'
  ),
  (
    131,
    'MANAGE_CAISSE_ANNEXE',
    'Gérer les Encaissements de la Caisse Annexe',
    'FINANCE',
    'actif',
    '2026-10-24 02:55:52'
  ),
  (
    132,
    'CHANGER_CLASSE_ETUDIANT',
    'Changer la classe / Réaffecter un étudiant',
    'SCOLAIRITE',
    'actif',
    '2026-10-24 16:47:24'
  ),
  (
    133,
    'EDIT_ETUDIANT',
    'Modifier la fiche et les coordonnées d\'un étudiant',
    'SCOLAIRITE',
    'actif',
    '2026-10-24 16:47:24'
  ),
  (
    134,
    'ANNULER_INSCRIPTION',
    'Annuler l\'inscription d\'un étudiant',
    'SCOLAIRITE',
    'actif',
    '2026-10-24 17:49:28'
  ),
  (
    176,
    'MENU_STRUCTURE',
    'Menu : Structure & Établissement',
    'SIDEBAR',
    'actif',
    '2026-10-27 04:15:33'
  ),
  (
    177,
    'MENU_PLANIFICATION',
    'Menu : Planification Académique',
    'SIDEBAR',
    'actif',
    '2026-10-27 04:15:33'
  ),
  (
    178,
    'MENU_SCOLARITE',
    'Menu : Admissions & Scolarité',
    'SIDEBAR',
    'actif',
    '2026-10-27 04:15:33'
  ),
  (
    179,
    'MENU_FINANCE',
    'Menu : Finance & Caisse',
    'SIDEBAR',
    'actif',
    '2026-10-27 04:15:33'
  ),
  (
    180,
    'MENU_PEDAGOGIE',
    'Menu : Pédagogie & Évaluations',
    'SIDEBAR',
    'actif',
    '2026-10-27 04:15:33'
  ),
  (
    181,
    'MENU_COMMUNICATION',
    'Menu : Portail & Communication',
    'SIDEBAR',
    'actif',
    '2026-10-27 04:15:33'
  ),
  (
    182,
    'MENU_ADMINISTRATION',
    'Menu : Administration & Sécurité',
    'SIDEBAR',
    'actif',
    '2026-10-27 04:15:33'
  ),
  (
    183,
    'MANAGE_MAQUETTES',
    'Créer & Modifier les Maquettes Pédagogiques',
    'ACADEMIQUE',
    'actif',
    '2026-11-19 00:42:16'
  ),
  (
    184,
    'VIEW_MAQUETTES',
    'Consulter les Maquettes Pédagogiques',
    'ACADEMIQUE',
    'actif',
    '2026-11-19 00:42:16'
  ),
  (
    185,
    'PRINT_MAQUETTES',
    'Imprimer les Maquettes Pédagogiques',
    'ACADEMIQUE',
    'actif',
    '2026-11-19 00:42:16'
  ),
  (
    186,
    'VIEW_LOGS',
    'Consulter le Journal d\'Audit & Logs Système',
    'ADMINISTRATION',
    'actif',
    '2026-11-19 00:42:16'
  ),
  (
    187,
    'CANCEL_CAISSE_ANNEXE',
    'Annuler un Encaissement de la Caisse Annexe',
    'FINANCE',
    'actif',
    '2026-11-19 00:42:16'
  ),
  (
    188,
    'MANAGE_ARRIERES',
    'Gérer & Encaisser les Arriérés Années Antérieures',
    'FINANCE',
    'actif',
    '2026-11-19 00:42:16'
  ),
  (
    189,
    'VIEW_ARRIERES',
    'Consulter les Arriérés Années Antérieures',
    'FINANCE',
    'actif',
    '2026-11-19 00:42:16'
  ),
  (
    190,
    'REPRENDRE_PHOTO_INSCRIPTION',
    'Reprendre / Modifier la Photo d\'Identité d\'un Étudiant',
    'SCOLAIRITE',
    'actif',
    '2026-11-22 02:00:00'
  );
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `piece_fournir_cycle`
--

DROP TABLE IF EXISTS `piece_fournir_cycle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `piece_fournir_cycle` (
  `id_piece_cycle` int NOT NULL AUTO_INCREMENT,
  `code_piece_cycle` varchar(50) NOT NULL,
  `cycle_code` varchar(50) NOT NULL,
  `niveau_code` varchar(50) DEFAULT NULL,
  `piece_code` varchar(50) NOT NULL,
  `nombre_exemplaires` int NOT NULL DEFAULT '1',
  `nature_document` enum(
    'photocopie_simple',
    'photocopie_legalisee',
    'original',
    'numerique',
    'aucun'
  ) NOT NULL DEFAULT 'photocopie_simple',
  `etablissement_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `statut_piece_cycle` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_piece_cycle` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_piece_cycle` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_piece_cycle`),
  UNIQUE KEY `code_piece_cycle` (`code_piece_cycle`),
  UNIQUE KEY `unique_cycle_piece_niveau` (`cycle_code`, `piece_code`, `niveau_code`),
  KEY `idx_pfc_cycle` (`cycle_code`),
  KEY `idx_pfc_niveau` (`niveau_code`),
  KEY `idx_pfc_piece` (`piece_code`),
  KEY `idx_pfc_statut` (`statut_piece_cycle`),
  KEY `idx_piece_fournir_cycle_etablissement_code` (`etablissement_code`),
  KEY `idx_piece_fournir_cycle_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `piece_fournir_cycle`
--

LOCK TABLES `piece_fournir_cycle` WRITE;
/*!40000 ALTER TABLE `piece_fournir_cycle` DISABLE KEYS */
;
/*!40000 ALTER TABLE `piece_fournir_cycle` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `pieces_fournir`
--

DROP TABLE IF EXISTS `pieces_fournir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `pieces_fournir` (
  `id_piece_fournir` int NOT NULL AUTO_INCREMENT,
  `code_piece_fournir` varchar(50) NOT NULL,
  `libelle_piece` varchar(255) NOT NULL,
  `description_piece` text,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `statut_piece` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_piece` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_piece` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_piece_fournir`),
  UNIQUE KEY `code_piece_fournir` (`code_piece_fournir`),
  KEY `idx_pieces_fournir_code` (`code_piece_fournir`),
  KEY `idx_pieces_fournir_statut` (`statut_piece`),
  KEY `idx_pieces_fournir_etablissement_code` (`etablissement_code`),
  KEY `idx_pieces_fournir_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `pieces_fournir`
--

LOCK TABLES `pieces_fournir` WRITE;
/*!40000 ALTER TABLE `pieces_fournir` DISABLE KEYS */
;
/*!40000 ALTER TABLE `pieces_fournir` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `relances_impayes`
--

DROP TABLE IF EXISTS `relances_impayes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `relances_impayes` (
  `id_relance` int NOT NULL AUTO_INCREMENT,
  `code_relance` varchar(50) NOT NULL,
  `etudiant_code` varchar(50) NOT NULL,
  `inscription_code` varchar(50) DEFAULT NULL,
  `niveau_relance` enum(
    'rappel_amiable',
    'relance_ferme',
    'mise_en_demeure'
  ) NOT NULL DEFAULT 'rappel_amiable',
  `canal_relance` enum('sms', 'email', 'whatsapp', 'appel') NOT NULL DEFAULT 'sms',
  `montant_impaye` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `telephone_destinataire` varchar(50) DEFAULT NULL,
  `message_relance` text,
  `statut_relance` enum('envoye', 'en_attente', 'regle') NOT NULL DEFAULT 'envoye',
  `user_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `created_at_relance` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_relance`),
  UNIQUE KEY `code_relance` (`code_relance`),
  KEY `idx_relances_impayes_etudiant_code` (`etudiant_code`),
  KEY `idx_relances_impayes_inscription_code` (`inscription_code`),
  KEY `idx_relances_impayes_statut_relance` (`statut_relance`),
  KEY `idx_relances_impayes_user_code` (`user_code`),
  KEY `idx_relances_impayes_annee_code` (`annee_code`),
  KEY `idx_relances_impayes_etablissement_code` (`etablissement_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `relances_impayes`
--

LOCK TABLES `relances_impayes` WRITE;
/*!40000 ALTER TABLE `relances_impayes` DISABLE KEYS */
;
/*!40000 ALTER TABLE `relances_impayes` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_code` varchar(50) NOT NULL,
  `permission_code` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_perm` (`role_code`, `permission_code`),
  KEY `idx_role_permissions_permission_code` (`permission_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 1616 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */
;
INSERT INTO `role_permissions`
VALUES (1347, 'all_control', 'ANNULER_INSCRIPTION'),
  (1604, 'all_control', 'CANCEL_CAISSE_ANNEXE'),
  (1358, 'all_control', 'CANCEL_PAIEMENTS'),
  (1327, 'all_control', 'CHANGER_CLASSE_ETUDIANT'),
  (1332, 'all_control', 'EDIT_ETUDIANT'),
  (848, 'all_control', 'MANAGE_ABSENCES'),
  (854, 'all_control', 'MANAGE_ACCESSOIRES'),
  (1608, 'all_control', 'MANAGE_ARRIERES'),
  (1322, 'all_control', 'MANAGE_CAISSE_ANNEXE'),
  (1365, 'all_control', 'MANAGE_CLASSES'),
  (852, 'all_control', 'MANAGE_DEPOT_DOSSIERS'),
  (850, 'all_control', 'MANAGE_DISCIPLINE'),
  (844, 'all_control', 'MANAGE_ETUDIANTS'),
  (1179, 'all_control', 'MANAGE_FRAIS_ANNEXES'),
  (846, 'all_control', 'MANAGE_INSCRIPTIONS'),
  (
    863,
    'all_control',
    'MANAGE_INSCRIPTIONS_SANS_PHOTO'
  ),
  (1586, 'all_control', 'MANAGE_MAQUETTES'),
  (860, 'all_control', 'MANAGE_PARENTS'),
  (857, 'all_control', 'MANAGE_PIECES'),
  (853, 'all_control', 'MANAGE_REMISE_KITS'),
  (1583, 'all_control', 'MENU_FINANCE'),
  (1584, 'all_control', 'MENU_PEDAGOGIE'),
  (1581, 'all_control', 'MENU_PLANIFICATION'),
  (1582, 'all_control', 'MENU_SCOLARITE'),
  (1580, 'all_control', 'MENU_STRUCTURE'),
  (851, 'all_control', 'PRINT_ATTESTATIONS'),
  (1203, 'all_control', 'PRINT_CLASSES'),
  (1193, 'all_control', 'PRINT_FILIERES'),
  (1215, 'all_control', 'PRINT_FRAIS_ANNEXES'),
  (1208, 'all_control', 'PRINT_FRAIS_SCOLARITE'),
  (1596, 'all_control', 'PRINT_MAQUETTES'),
  (1188, 'all_control', 'PRINT_OFFRE_ACADEMIQUE'),
  (1198, 'all_control', 'PRINT_SALLES'),
  (847, 'all_control', 'VIEW_ABSENCES'),
  (855, 'all_control', 'VIEW_ACCESSOIRES'),
  (1612, 'all_control', 'VIEW_ARRIERES'),
  (1317, 'all_control', 'VIEW_CAISSE_ANNEXE'),
  (
    1184,
    'all_control',
    'VIEW_COMPTABILITE_ETUDIANTS'
  ),
  (840, 'all_control', 'VIEW_DASHBOARD_ACTIONS'),
  (859, 'all_control', 'VIEW_DEPOT_DOSSIERS'),
  (849, 'all_control', 'VIEW_DISCIPLINE'),
  (843, 'all_control', 'VIEW_ETUDIANTS'),
  (1180, 'all_control', 'VIEW_FRAIS_ANNEXES'),
  (845, 'all_control', 'VIEW_INSCRIPTIONS'),
  (
    862,
    'all_control',
    'VIEW_INSCRIPTIONS_SANS_PHOTO'
  ),
  (1601, 'all_control', 'VIEW_LOGS'),
  (1591, 'all_control', 'VIEW_MAQUETTES'),
  (861, 'all_control', 'VIEW_PARENTS'),
  (858, 'all_control', 'VIEW_PIECES'),
  (856, 'all_control', 'VIEW_REMISE_KITS'),
  (338, 'ROLE_CAISSIER', 'CANCEL_PAIEMENTS'),
  (865, 'ROLE_CAISSIER', 'CLOTURE_CAISSE'),
  (344, 'ROLE_CAISSIER', 'EXPORT_FINANCE'),
  (866, 'ROLE_CAISSIER', 'MANAGE_CAISSE'),
  (1325, 'ROLE_CAISSIER', 'MANAGE_CAISSE_ANNEXE'),
  (335, 'ROLE_CAISSIER', 'MANAGE_FRAIS_SCOLARITE'),
  (339, 'ROLE_CAISSIER', 'MANAGE_MORATOIRES'),
  (1385, 'ROLE_CAISSIER', 'MANAGE_PAIEMENTS'),
  (1571, 'ROLE_CAISSIER', 'MENU_FINANCE'),
  (1570, 'ROLE_CAISSIER', 'MENU_PLANIFICATION'),
  (864, 'ROLE_CAISSIER', 'OUVERTURE_CAISSE'),
  (1218, 'ROLE_CAISSIER', 'PRINT_FRAIS_ANNEXES'),
  (1211, 'ROLE_CAISSIER', 'PRINT_FRAIS_SCOLARITE'),
  (341, 'ROLE_CAISSIER', 'RECORD_DEPENSES'),
  (337, 'ROLE_CAISSIER', 'RECORD_PAIEMENTS'),
  (342, 'ROLE_CAISSIER', 'VALIDATE_DEPENSES'),
  (1615, 'ROLE_CAISSIER', 'VIEW_ARRIERES'),
  (1320, 'ROLE_CAISSIER', 'VIEW_CAISSE_ANNEXE'),
  (330, 'ROLE_CAISSIER', 'VIEW_CLASSES'),
  (
    1183,
    'ROLE_CAISSIER',
    'VIEW_COMPTABILITE_ETUDIANTS'
  ),
  (536, 'ROLE_CAISSIER', 'VIEW_DASHBOARD_ACTIONS'),
  (535, 'ROLE_CAISSIER', 'VIEW_DASHBOARD_FINANCE'),
  (340, 'ROLE_CAISSIER', 'VIEW_DEPENSES'),
  (334, 'ROLE_CAISSIER', 'VIEW_FRAIS_SCOLARITE'),
  (336, 'ROLE_CAISSIER', 'VIEW_PAIEMENTS'),
  (343, 'ROLE_CAISSIER', 'VIEW_RAPPORTS_FINANCIERS'),
  (1073, 'ROLE_CHEF_DEP', 'ENTER_NOTES'),
  (1075, 'ROLE_CHEF_DEP', 'GENERATE_BULLETINS'),
  (1079, 'ROLE_CHEF_DEP', 'MANAGE_ABSENCES'),
  (1069, 'ROLE_CHEF_DEP', 'MANAGE_AFFECTATIONS'),
  (1071, 'ROLE_CHEF_DEP', 'MANAGE_EMPLOI_TEMPS'),
  (1589, 'ROLE_CHEF_DEP', 'MANAGE_MAQUETTES'),
  (1560, 'ROLE_CHEF_DEP', 'MENU_COMMUNICATION'),
  (1559, 'ROLE_CHEF_DEP', 'MENU_PEDAGOGIE'),
  (1557, 'ROLE_CHEF_DEP', 'MENU_PLANIFICATION'),
  (1558, 'ROLE_CHEF_DEP', 'MENU_SCOLARITE'),
  (1556, 'ROLE_CHEF_DEP', 'MENU_STRUCTURE'),
  (1599, 'ROLE_CHEF_DEP', 'PRINT_MAQUETTES'),
  (1078, 'ROLE_CHEF_DEP', 'VIEW_ABSENCES'),
  (1068, 'ROLE_CHEF_DEP', 'VIEW_AFFECTATIONS'),
  (1074, 'ROLE_CHEF_DEP', 'VIEW_BULLETINS'),
  (1060, 'ROLE_CHEF_DEP', 'VIEW_CLASSES'),
  (1057, 'ROLE_CHEF_DEP', 'VIEW_CYCLES'),
  (1066, 'ROLE_CHEF_DEP', 'VIEW_DASHBOARD_ACTIONS'),
  (
    1065,
    'ROLE_CHEF_DEP',
    'VIEW_DASHBOARD_PEDAGOGIE'
  ),
  (1080, 'ROLE_CHEF_DEP', 'VIEW_DISCIPLINE'),
  (1064, 'ROLE_CHEF_DEP', 'VIEW_DOCUMENTS'),
  (1070, 'ROLE_CHEF_DEP', 'VIEW_EMPLOI_TEMPS'),
  (1067, 'ROLE_CHEF_DEP', 'VIEW_ENSEIGNANTS'),
  (1077, 'ROLE_CHEF_DEP', 'VIEW_ETUDIANTS'),
  (1063, 'ROLE_CHEF_DEP', 'VIEW_EVENEMENTS'),
  (1076, 'ROLE_CHEF_DEP', 'VIEW_EXAMENS'),
  (1058, 'ROLE_CHEF_DEP', 'VIEW_FILIERES'),
  (1594, 'ROLE_CHEF_DEP', 'VIEW_MAQUETTES'),
  (1062, 'ROLE_CHEF_DEP', 'VIEW_MATIERES'),
  (1059, 'ROLE_CHEF_DEP', 'VIEW_NIVEAUX'),
  (1072, 'ROLE_CHEF_DEP', 'VIEW_NOTES'),
  (1061, 'ROLE_CHEF_DEP', 'VIEW_SEMESTRES'),
  (250, 'ROLE_COMMUNICATION', 'MANAGE_DOCUMENTS'),
  (248, 'ROLE_COMMUNICATION', 'MANAGE_EVENEMENTS'),
  (1578, 'ROLE_COMMUNICATION', 'MENU_COMMUNICATION'),
  (1577, 'ROLE_COMMUNICATION', 'MENU_PEDAGOGIE'),
  (1576, 'ROLE_COMMUNICATION', 'MENU_SCOLARITE'),
  (251, 'ROLE_COMMUNICATION', 'SEND_MESSAGES'),
  (
    540,
    'ROLE_COMMUNICATION',
    'VIEW_DASHBOARD_ACTIONS'
  ),
  (
    539,
    'ROLE_COMMUNICATION',
    'VIEW_DASHBOARD_COMMUNICATION'
  ),
  (249, 'ROLE_COMMUNICATION', 'VIEW_DOCUMENTS'),
  (253, 'ROLE_COMMUNICATION', 'VIEW_ENSEIGNANTS'),
  (252, 'ROLE_COMMUNICATION', 'VIEW_ETUDIANTS'),
  (247, 'ROLE_COMMUNICATION', 'VIEW_EVENEMENTS'),
  (1606, 'ROLE_COMPTABLE', 'CANCEL_CAISSE_ANNEXE'),
  (1296, 'ROLE_COMPTABLE', 'CANCEL_PAIEMENTS'),
  (1306, 'ROLE_COMPTABLE', 'CLOTURE_CAISSE'),
  (1302, 'ROLE_COMPTABLE', 'EXPORT_FINANCE'),
  (1610, 'ROLE_COMPTABLE', 'MANAGE_ARRIERES'),
  (1307, 'ROLE_COMPTABLE', 'MANAGE_CAISSE'),
  (1324, 'ROLE_COMPTABLE', 'MANAGE_CAISSE_ANNEXE'),
  (1311, 'ROLE_COMPTABLE', 'MANAGE_FRAIS_ANNEXES'),
  (1293, 'ROLE_COMPTABLE', 'MANAGE_FRAIS_SCOLARITE'),
  (1303, 'ROLE_COMPTABLE', 'MANAGE_IMPAYES'),
  (1297, 'ROLE_COMPTABLE', 'MANAGE_MORATOIRES'),
  (1308, 'ROLE_COMPTABLE', 'MANAGE_PAIEMENTS'),
  (1309, 'ROLE_COMPTABLE', 'MANAGE_TYPES_DEPENSE'),
  (1568, 'ROLE_COMPTABLE', 'MENU_FINANCE'),
  (1305, 'ROLE_COMPTABLE', 'OUVERTURE_CAISSE'),
  (1315, 'ROLE_COMPTABLE', 'PRINT_FRAIS_ANNEXES'),
  (1314, 'ROLE_COMPTABLE', 'PRINT_FRAIS_SCOLARITE'),
  (1299, 'ROLE_COMPTABLE', 'RECORD_DEPENSES'),
  (1295, 'ROLE_COMPTABLE', 'RECORD_PAIEMENTS'),
  (1300, 'ROLE_COMPTABLE', 'VALIDATE_DEPENSES'),
  (1614, 'ROLE_COMPTABLE', 'VIEW_ARRIERES'),
  (1319, 'ROLE_COMPTABLE', 'VIEW_CAISSE_ANNEXE'),
  (
    1313,
    'ROLE_COMPTABLE',
    'VIEW_COMPTABILITE_ETUDIANTS'
  ),
  (1287, 'ROLE_COMPTABLE', 'VIEW_DASHBOARD_ACTIONS'),
  (
    1386,
    'ROLE_COMPTABLE',
    'VIEW_DASHBOARD_EXECUTIVE'
  ),
  (1286, 'ROLE_COMPTABLE', 'VIEW_DASHBOARD_FINANCE'),
  (1298, 'ROLE_COMPTABLE', 'VIEW_DEPENSES'),
  (1312, 'ROLE_COMPTABLE', 'VIEW_FRAIS_ANNEXES'),
  (1292, 'ROLE_COMPTABLE', 'VIEW_FRAIS_SCOLARITE'),
  (1304, 'ROLE_COMPTABLE', 'VIEW_IMPAYES'),
  (1294, 'ROLE_COMPTABLE', 'VIEW_PAIEMENTS'),
  (
    1301,
    'ROLE_COMPTABLE',
    'VIEW_RAPPORTS_FINANCIERS'
  ),
  (1310, 'ROLE_COMPTABLE', 'VIEW_TYPES_DEPENSE'),
  (1349, 'ROLE_DIR_ETUDES', 'ANNULER_INSCRIPTION'),
  (1110, 'ROLE_DIR_ETUDES', 'BACKUP_DATABASE'),
  (
    1329,
    'ROLE_DIR_ETUDES',
    'CHANGER_CLASSE_ETUDIANT'
  ),
  (1096, 'ROLE_DIR_ETUDES', 'CONFIG_ACADEMIQUE'),
  (1113, 'ROLE_DIR_ETUDES', 'CONFIG_SYSTEM'),
  (1334, 'ROLE_DIR_ETUDES', 'EDIT_ETUDIANT'),
  (1145, 'ROLE_DIR_ETUDES', 'ENTER_NOTES'),
  (1148, 'ROLE_DIR_ETUDES', 'GENERATE_BULLETINS'),
  (1146, 'ROLE_DIR_ETUDES', 'LOCK_NOTES'),
  (1158, 'ROLE_DIR_ETUDES', 'MANAGE_ABSENCES'),
  (1164, 'ROLE_DIR_ETUDES', 'MANAGE_ACCESSOIRES'),
  (1141, 'ROLE_DIR_ETUDES', 'MANAGE_AFFECTATIONS'),
  (1087, 'ROLE_DIR_ETUDES', 'MANAGE_ANNEES'),
  (1084, 'ROLE_DIR_ETUDES', 'MANAGE_CLASSES'),
  (1124, 'ROLE_DIR_ETUDES', 'MANAGE_COMMUNICATION'),
  (1082, 'ROLE_DIR_ETUDES', 'MANAGE_CYCLES'),
  (1162, 'ROLE_DIR_ETUDES', 'MANAGE_DEPOT_DOSSIERS'),
  (
    1174,
    'ROLE_DIR_ETUDES',
    'MANAGE_DEROGATION_INSCRIPTION'
  ),
  (1160, 'ROLE_DIR_ETUDES', 'MANAGE_DISCIPLINE'),
  (1118, 'ROLE_DIR_ETUDES', 'MANAGE_DOCUMENTS'),
  (1143, 'ROLE_DIR_ETUDES', 'MANAGE_EMPLOI_TEMPS'),
  (1139, 'ROLE_DIR_ETUDES', 'MANAGE_ENSEIGNANTS'),
  (1108, 'ROLE_DIR_ETUDES', 'MANAGE_ETABLISSEMENT'),
  (1154, 'ROLE_DIR_ETUDES', 'MANAGE_ETUDIANTS'),
  (1116, 'ROLE_DIR_ETUDES', 'MANAGE_EVENEMENTS'),
  (1150, 'ROLE_DIR_ETUDES', 'MANAGE_EXAMENS'),
  (1083, 'ROLE_DIR_ETUDES', 'MANAGE_FILIERES'),
  (1109, 'ROLE_DIR_ETUDES', 'MANAGE_FONCTIONS'),
  (1122, 'ROLE_DIR_ETUDES', 'MANAGE_GALLERY'),
  (1156, 'ROLE_DIR_ETUDES', 'MANAGE_INSCRIPTIONS'),
  (
    1173,
    'ROLE_DIR_ETUDES',
    'MANAGE_INSCRIPTIONS_SANS_PHOTO'
  ),
  (1588, 'ROLE_DIR_ETUDES', 'MANAGE_MAQUETTES'),
  (1085, 'ROLE_DIR_ETUDES', 'MANAGE_MATIERES'),
  (1091, 'ROLE_DIR_ETUDES', 'MANAGE_NIVEAUX'),
  (1170, 'ROLE_DIR_ETUDES', 'MANAGE_PARENTS'),
  (1107, 'ROLE_DIR_ETUDES', 'MANAGE_PERMISSIONS'),
  (1167, 'ROLE_DIR_ETUDES', 'MANAGE_PIECES'),
  (1163, 'ROLE_DIR_ETUDES', 'MANAGE_REMISE_KITS'),
  (1102, 'ROLE_DIR_ETUDES', 'MANAGE_ROLES'),
  (1097, 'ROLE_DIR_ETUDES', 'MANAGE_SALLES'),
  (1094, 'ROLE_DIR_ETUDES', 'MANAGE_SEMESTRES'),
  (1099, 'ROLE_DIR_ETUDES', 'MANAGE_UE'),
  (1101, 'ROLE_DIR_ETUDES', 'MANAGE_USERS'),
  (1554, 'ROLE_DIR_ETUDES', 'MENU_ADMINISTRATION'),
  (1553, 'ROLE_DIR_ETUDES', 'MENU_COMMUNICATION'),
  (1551, 'ROLE_DIR_ETUDES', 'MENU_FINANCE'),
  (1552, 'ROLE_DIR_ETUDES', 'MENU_PEDAGOGIE'),
  (1549, 'ROLE_DIR_ETUDES', 'MENU_PLANIFICATION'),
  (1550, 'ROLE_DIR_ETUDES', 'MENU_SCOLARITE'),
  (1548, 'ROLE_DIR_ETUDES', 'MENU_STRUCTURE'),
  (1161, 'ROLE_DIR_ETUDES', 'PRINT_ATTESTATIONS'),
  (1205, 'ROLE_DIR_ETUDES', 'PRINT_CLASSES'),
  (1195, 'ROLE_DIR_ETUDES', 'PRINT_FILIERES'),
  (1220, 'ROLE_DIR_ETUDES', 'PRINT_FRAIS_ANNEXES'),
  (1213, 'ROLE_DIR_ETUDES', 'PRINT_FRAIS_SCOLARITE'),
  (1598, 'ROLE_DIR_ETUDES', 'PRINT_MAQUETTES'),
  (
    1190,
    'ROLE_DIR_ETUDES',
    'PRINT_OFFRE_ACADEMIQUE'
  ),
  (1200, 'ROLE_DIR_ETUDES', 'PRINT_SALLES'),
  (1119, 'ROLE_DIR_ETUDES', 'SEND_MESSAGES'),
  (1157, 'ROLE_DIR_ETUDES', 'VIEW_ABSENCES'),
  (1165, 'ROLE_DIR_ETUDES', 'VIEW_ACCESSOIRES'),
  (1140, 'ROLE_DIR_ETUDES', 'VIEW_AFFECTATIONS'),
  (1086, 'ROLE_DIR_ETUDES', 'VIEW_ANNEES'),
  (
    1114,
    'ROLE_DIR_ETUDES',
    'VIEW_ANNEES_ANTERIEURES'
  ),
  (1147, 'ROLE_DIR_ETUDES', 'VIEW_BULLETINS'),
  (1092, 'ROLE_DIR_ETUDES', 'VIEW_CLASSES'),
  (
    1186,
    'ROLE_DIR_ETUDES',
    'VIEW_COMPTABILITE_ETUDIANTS'
  ),
  (1088, 'ROLE_DIR_ETUDES', 'VIEW_CYCLES'),
  (
    1131,
    'ROLE_DIR_ETUDES',
    'VIEW_DASHBOARD_ACTIONS'
  ),
  (
    1130,
    'ROLE_DIR_ETUDES',
    'VIEW_DASHBOARD_COMMUNICATION'
  ),
  (
    1129,
    'ROLE_DIR_ETUDES',
    'VIEW_DASHBOARD_ENSEIGNANT'
  ),
  (
    1132,
    'ROLE_DIR_ETUDES',
    'VIEW_DASHBOARD_EXECUTIVE'
  ),
  (
    1128,
    'ROLE_DIR_ETUDES',
    'VIEW_DASHBOARD_FINANCE'
  ),
  (
    1126,
    'ROLE_DIR_ETUDES',
    'VIEW_DASHBOARD_PEDAGOGIE'
  ),
  (
    1127,
    'ROLE_DIR_ETUDES',
    'VIEW_DASHBOARD_SCOLARITE'
  ),
  (1169, 'ROLE_DIR_ETUDES', 'VIEW_DEPOT_DOSSIERS'),
  (1159, 'ROLE_DIR_ETUDES', 'VIEW_DISCIPLINE'),
  (1117, 'ROLE_DIR_ETUDES', 'VIEW_DOCUMENTS'),
  (1142, 'ROLE_DIR_ETUDES', 'VIEW_EMPLOI_TEMPS'),
  (1138, 'ROLE_DIR_ETUDES', 'VIEW_ENSEIGNANTS'),
  (1153, 'ROLE_DIR_ETUDES', 'VIEW_ETUDIANTS'),
  (1115, 'ROLE_DIR_ETUDES', 'VIEW_EVENEMENTS'),
  (1149, 'ROLE_DIR_ETUDES', 'VIEW_EXAMENS'),
  (1089, 'ROLE_DIR_ETUDES', 'VIEW_FILIERES'),
  (1112, 'ROLE_DIR_ETUDES', 'VIEW_FONCTIONS'),
  (1123, 'ROLE_DIR_ETUDES', 'VIEW_GALLERY'),
  (1120, 'ROLE_DIR_ETUDES', 'VIEW_HISTORIQUE'),
  (1155, 'ROLE_DIR_ETUDES', 'VIEW_INSCRIPTIONS'),
  (
    1172,
    'ROLE_DIR_ETUDES',
    'VIEW_INSCRIPTIONS_SANS_PHOTO'
  ),
  (1593, 'ROLE_DIR_ETUDES', 'VIEW_MAQUETTES'),
  (1095, 'ROLE_DIR_ETUDES', 'VIEW_MATIERES'),
  (1090, 'ROLE_DIR_ETUDES', 'VIEW_NIVEAUX'),
  (1144, 'ROLE_DIR_ETUDES', 'VIEW_NOTES'),
  (1171, 'ROLE_DIR_ETUDES', 'VIEW_PARENTS'),
  (1106, 'ROLE_DIR_ETUDES', 'VIEW_PERMISSIONS'),
  (1168, 'ROLE_DIR_ETUDES', 'VIEW_PIECES'),
  (1166, 'ROLE_DIR_ETUDES', 'VIEW_REMISE_KITS'),
  (1137, 'ROLE_DIR_ETUDES', 'VIEW_REPORTS'),
  (1105, 'ROLE_DIR_ETUDES', 'VIEW_ROLES'),
  (1098, 'ROLE_DIR_ETUDES', 'VIEW_SALLES'),
  (1093, 'ROLE_DIR_ETUDES', 'VIEW_SEMESTRES'),
  (1100, 'ROLE_DIR_ETUDES', 'VIEW_UE'),
  (1104, 'ROLE_DIR_ETUDES', 'VIEW_USERS'),
  (1348, 'ROLE_DIR_GENERAL', 'ANNULER_INSCRIPTION'),
  (1605, 'ROLE_DIR_GENERAL', 'CANCEL_CAISSE_ANNEXE'),
  (1356, 'ROLE_DIR_GENERAL', 'CANCEL_PAIEMENTS'),
  (
    1328,
    'ROLE_DIR_GENERAL',
    'CHANGER_CLASSE_ETUDIANT'
  ),
  (1333, 'ROLE_DIR_GENERAL', 'EDIT_ETUDIANT'),
  (1609, 'ROLE_DIR_GENERAL', 'MANAGE_ARRIERES'),
  (1323, 'ROLE_DIR_GENERAL', 'MANAGE_CAISSE_ANNEXE'),
  (1366, 'ROLE_DIR_GENERAL', 'MANAGE_CLASSES'),
  (1587, 'ROLE_DIR_GENERAL', 'MANAGE_MAQUETTES'),
  (1056, 'ROLE_DIR_GENERAL', 'MANAGE_ROLES'),
  (1546, 'ROLE_DIR_GENERAL', 'MENU_ADMINISTRATION'),
  (1545, 'ROLE_DIR_GENERAL', 'MENU_FINANCE'),
  (1544, 'ROLE_DIR_GENERAL', 'MENU_PLANIFICATION'),
  (1204, 'ROLE_DIR_GENERAL', 'PRINT_CLASSES'),
  (1194, 'ROLE_DIR_GENERAL', 'PRINT_FILIERES'),
  (1216, 'ROLE_DIR_GENERAL', 'PRINT_FRAIS_ANNEXES'),
  (
    1209,
    'ROLE_DIR_GENERAL',
    'PRINT_FRAIS_SCOLARITE'
  ),
  (1597, 'ROLE_DIR_GENERAL', 'PRINT_MAQUETTES'),
  (
    1189,
    'ROLE_DIR_GENERAL',
    'PRINT_OFFRE_ACADEMIQUE'
  ),
  (1199, 'ROLE_DIR_GENERAL', 'PRINT_SALLES'),
  (1613, 'ROLE_DIR_GENERAL', 'VIEW_ARRIERES'),
  (1318, 'ROLE_DIR_GENERAL', 'VIEW_CAISSE_ANNEXE'),
  (
    1185,
    'ROLE_DIR_GENERAL',
    'VIEW_COMPTABILITE_ETUDIANTS'
  ),
  (1602, 'ROLE_DIR_GENERAL', 'VIEW_LOGS'),
  (1592, 'ROLE_DIR_GENERAL', 'VIEW_MAQUETTES'),
  (1055, 'ROLE_DIR_GENERAL', 'VIEW_ROLES'),
  (1384, 'ROLE_ENSEIGNANT', 'ENTER_NOTES'),
  (1574, 'ROLE_ENSEIGNANT', 'MENU_COMMUNICATION'),
  (1573, 'ROLE_ENSEIGNANT', 'MENU_PEDAGOGIE'),
  (1381, 'ROLE_ENSEIGNANT', 'VIEW_DOCUMENTS'),
  (1382, 'ROLE_ENSEIGNANT', 'VIEW_EMPLOI_TEMPS'),
  (1380, 'ROLE_ENSEIGNANT', 'VIEW_EVENEMENTS'),
  (1383, 'ROLE_ENSEIGNANT', 'VIEW_NOTES'),
  (1350, 'ROLE_SCOLARITE', 'ANNULER_INSCRIPTION'),
  (
    1330,
    'ROLE_SCOLARITE',
    'CHANGER_CLASSE_ETUDIANT'
  ),
  (1335, 'ROLE_SCOLARITE', 'EDIT_ETUDIANT'),
  (201, 'ROLE_SCOLARITE', 'MANAGE_ABSENCES'),
  (374, 'ROLE_SCOLARITE', 'MANAGE_DEPOT_DOSSIERS'),
  (
    834,
    'ROLE_SCOLARITE',
    'MANAGE_DEROGATION_INSCRIPTION'
  ),
  (203, 'ROLE_SCOLARITE', 'MANAGE_DISCIPLINE'),
  (197, 'ROLE_SCOLARITE', 'MANAGE_ETUDIANTS'),
  (199, 'ROLE_SCOLARITE', 'MANAGE_INSCRIPTIONS'),
  (378, 'ROLE_SCOLARITE', 'MANAGE_REMISE_KITS'),
  (1566, 'ROLE_SCOLARITE', 'MENU_COMMUNICATION'),
  (1565, 'ROLE_SCOLARITE', 'MENU_PEDAGOGIE'),
  (1563, 'ROLE_SCOLARITE', 'MENU_PLANIFICATION'),
  (1564, 'ROLE_SCOLARITE', 'MENU_SCOLARITE'),
  (1562, 'ROLE_SCOLARITE', 'MENU_STRUCTURE'),
  (204, 'ROLE_SCOLARITE', 'PRINT_ATTESTATIONS'),
  (1206, 'ROLE_SCOLARITE', 'PRINT_CLASSES'),
  (1196, 'ROLE_SCOLARITE', 'PRINT_FILIERES'),
  (1219, 'ROLE_SCOLARITE', 'PRINT_FRAIS_ANNEXES'),
  (1212, 'ROLE_SCOLARITE', 'PRINT_FRAIS_SCOLARITE'),
  (1191, 'ROLE_SCOLARITE', 'PRINT_OFFRE_ACADEMIQUE'),
  (1201, 'ROLE_SCOLARITE', 'PRINT_SALLES'),
  (209, 'ROLE_SCOLARITE', 'SEND_MESSAGES'),
  (200, 'ROLE_SCOLARITE', 'VIEW_ABSENCES'),
  (190, 'ROLE_SCOLARITE', 'VIEW_ANNEES'),
  (206, 'ROLE_SCOLARITE', 'VIEW_BULLETINS'),
  (194, 'ROLE_SCOLARITE', 'VIEW_CLASSES'),
  (191, 'ROLE_SCOLARITE', 'VIEW_CYCLES'),
  (532, 'ROLE_SCOLARITE', 'VIEW_DASHBOARD_ACTIONS'),
  (
    531,
    'ROLE_SCOLARITE',
    'VIEW_DASHBOARD_SCOLARITE'
  ),
  (202, 'ROLE_SCOLARITE', 'VIEW_DISCIPLINE'),
  (208, 'ROLE_SCOLARITE', 'VIEW_DOCUMENTS'),
  (205, 'ROLE_SCOLARITE', 'VIEW_EMPLOI_TEMPS'),
  (196, 'ROLE_SCOLARITE', 'VIEW_ETUDIANTS'),
  (207, 'ROLE_SCOLARITE', 'VIEW_EVENEMENTS'),
  (192, 'ROLE_SCOLARITE', 'VIEW_FILIERES'),
  (198, 'ROLE_SCOLARITE', 'VIEW_INSCRIPTIONS'),
  (193, 'ROLE_SCOLARITE', 'VIEW_NIVEAUX'),
  (195, 'ROLE_SCOLARITE', 'VIEW_SEMESTRES'),
  (1346, 'ROLE_SUPERADMIN', 'ANNULER_INSCRIPTION'),
  (403, 'ROLE_SUPERADMIN', 'BACKUP_DATABASE'),
  (1603, 'ROLE_SUPERADMIN', 'CANCEL_CAISSE_ANNEXE'),
  (425, 'ROLE_SUPERADMIN', 'CANCEL_PAIEMENTS'),
  (
    1326,
    'ROLE_SUPERADMIN',
    'CHANGER_CLASSE_ETUDIANT'
  ),
  (672, 'ROLE_SUPERADMIN', 'CLOTURE_CAISSE'),
  (581, 'ROLE_SUPERADMIN', 'CONFIG_ACADEMIQUE'),
  (674, 'ROLE_SUPERADMIN', 'CONFIG_SYSTEM'),
  (
    829,
    'ROLE_SUPERADMIN',
    'EDIT_ANNEES_ANTERIEURES'
  ),
  (1331, 'ROLE_SUPERADMIN', 'EDIT_ETUDIANT'),
  (444, 'ROLE_SUPERADMIN', 'ENTER_NOTES'),
  (431, 'ROLE_SUPERADMIN', 'EXPORT_FINANCE'),
  (447, 'ROLE_SUPERADMIN', 'GENERATE_BULLETINS'),
  (445, 'ROLE_SUPERADMIN', 'LOCK_NOTES'),
  (457, 'ROLE_SUPERADMIN', 'MANAGE_ABSENCES'),
  (588, 'ROLE_SUPERADMIN', 'MANAGE_ACCESSOIRES'),
  (440, 'ROLE_SUPERADMIN', 'MANAGE_AFFECTATIONS'),
  (385, 'ROLE_SUPERADMIN', 'MANAGE_ANNEES'),
  (1607, 'ROLE_SUPERADMIN', 'MANAGE_ARRIERES'),
  (685, 'ROLE_SUPERADMIN', 'MANAGE_CAISSE'),
  (1321, 'ROLE_SUPERADMIN', 'MANAGE_CAISSE_ANNEXE'),
  (382, 'ROLE_SUPERADMIN', 'MANAGE_CLASSES'),
  (687, 'ROLE_SUPERADMIN', 'MANAGE_COMMUNICATION'),
  (380, 'ROLE_SUPERADMIN', 'MANAGE_CYCLES'),
  (461, 'ROLE_SUPERADMIN', 'MANAGE_DEPOT_DOSSIERS'),
  (
    830,
    'ROLE_SUPERADMIN',
    'MANAGE_DEROGATION_INSCRIPTION'
  ),
  (459, 'ROLE_SUPERADMIN', 'MANAGE_DISCIPLINE'),
  (407, 'ROLE_SUPERADMIN', 'MANAGE_DOCUMENTS'),
  (442, 'ROLE_SUPERADMIN', 'MANAGE_EMPLOI_TEMPS'),
  (438, 'ROLE_SUPERADMIN', 'MANAGE_ENSEIGNANTS'),
  (401, 'ROLE_SUPERADMIN', 'MANAGE_ETABLISSEMENT'),
  (453, 'ROLE_SUPERADMIN', 'MANAGE_ETUDIANTS'),
  (405, 'ROLE_SUPERADMIN', 'MANAGE_EVENEMENTS'),
  (449, 'ROLE_SUPERADMIN', 'MANAGE_EXAMENS'),
  (381, 'ROLE_SUPERADMIN', 'MANAGE_FILIERES'),
  (402, 'ROLE_SUPERADMIN', 'MANAGE_FONCTIONS'),
  (1175, 'ROLE_SUPERADMIN', 'MANAGE_FRAIS_ANNEXES'),
  (422, 'ROLE_SUPERADMIN', 'MANAGE_FRAIS_SCOLARITE'),
  (704, 'ROLE_SUPERADMIN', 'MANAGE_GALLERY'),
  (706, 'ROLE_SUPERADMIN', 'MANAGE_IMPAYES'),
  (455, 'ROLE_SUPERADMIN', 'MANAGE_INSCRIPTIONS'),
  (
    822,
    'ROLE_SUPERADMIN',
    'MANAGE_INSCRIPTIONS_SANS_PHOTO'
  ),
  (1585, 'ROLE_SUPERADMIN', 'MANAGE_MAQUETTES'),
  (383, 'ROLE_SUPERADMIN', 'MANAGE_MATIERES'),
  (426, 'ROLE_SUPERADMIN', 'MANAGE_MORATOIRES'),
  (389, 'ROLE_SUPERADMIN', 'MANAGE_NIVEAUX'),
  (711, 'ROLE_SUPERADMIN', 'MANAGE_PAIEMENTS'),
  (712, 'ROLE_SUPERADMIN', 'MANAGE_PARENTS'),
  (400, 'ROLE_SUPERADMIN', 'MANAGE_PERMISSIONS'),
  (614, 'ROLE_SUPERADMIN', 'MANAGE_PIECES'),
  (462, 'ROLE_SUPERADMIN', 'MANAGE_REMISE_KITS'),
  (395, 'ROLE_SUPERADMIN', 'MANAGE_ROLES'),
  (718, 'ROLE_SUPERADMIN', 'MANAGE_SALLES'),
  (392, 'ROLE_SUPERADMIN', 'MANAGE_SEMESTRES'),
  (724, 'ROLE_SUPERADMIN', 'MANAGE_TYPES_DEPENSE'),
  (725, 'ROLE_SUPERADMIN', 'MANAGE_UE'),
  (394, 'ROLE_SUPERADMIN', 'MANAGE_USERS'),
  (1542, 'ROLE_SUPERADMIN', 'MENU_ADMINISTRATION'),
  (1541, 'ROLE_SUPERADMIN', 'MENU_COMMUNICATION'),
  (1539, 'ROLE_SUPERADMIN', 'MENU_FINANCE'),
  (1540, 'ROLE_SUPERADMIN', 'MENU_PEDAGOGIE'),
  (1537, 'ROLE_SUPERADMIN', 'MENU_PLANIFICATION'),
  (1538, 'ROLE_SUPERADMIN', 'MENU_SCOLARITE'),
  (1536, 'ROLE_SUPERADMIN', 'MENU_STRUCTURE'),
  (728, 'ROLE_SUPERADMIN', 'OUVERTURE_CAISSE'),
  (460, 'ROLE_SUPERADMIN', 'PRINT_ATTESTATIONS'),
  (1202, 'ROLE_SUPERADMIN', 'PRINT_CLASSES'),
  (1192, 'ROLE_SUPERADMIN', 'PRINT_FILIERES'),
  (1214, 'ROLE_SUPERADMIN', 'PRINT_FRAIS_ANNEXES'),
  (1207, 'ROLE_SUPERADMIN', 'PRINT_FRAIS_SCOLARITE'),
  (1595, 'ROLE_SUPERADMIN', 'PRINT_MAQUETTES'),
  (
    1187,
    'ROLE_SUPERADMIN',
    'PRINT_OFFRE_ACADEMIQUE'
  ),
  (1197, 'ROLE_SUPERADMIN', 'PRINT_SALLES'),
  (428, 'ROLE_SUPERADMIN', 'RECORD_DEPENSES'),
  (424, 'ROLE_SUPERADMIN', 'RECORD_PAIEMENTS'),
  (408, 'ROLE_SUPERADMIN', 'SEND_MESSAGES'),
  (733, 'ROLE_SUPERADMIN', 'SEND_RELANCES'),
  (429, 'ROLE_SUPERADMIN', 'VALIDATE_DEPENSES'),
  (456, 'ROLE_SUPERADMIN', 'VIEW_ABSENCES'),
  (631, 'ROLE_SUPERADMIN', 'VIEW_ACCESSOIRES'),
  (439, 'ROLE_SUPERADMIN', 'VIEW_AFFECTATIONS'),
  (384, 'ROLE_SUPERADMIN', 'VIEW_ANNEES'),
  (
    828,
    'ROLE_SUPERADMIN',
    'VIEW_ANNEES_ANTERIEURES'
  ),
  (1611, 'ROLE_SUPERADMIN', 'VIEW_ARRIERES'),
  (446, 'ROLE_SUPERADMIN', 'VIEW_BULLETINS'),
  (1316, 'ROLE_SUPERADMIN', 'VIEW_CAISSE_ANNEXE'),
  (390, 'ROLE_SUPERADMIN', 'VIEW_CLASSES'),
  (
    1181,
    'ROLE_SUPERADMIN',
    'VIEW_COMPTABILITE_ETUDIANTS'
  ),
  (386, 'ROLE_SUPERADMIN', 'VIEW_CYCLES'),
  (521, 'ROLE_SUPERADMIN', 'VIEW_DASHBOARD_ACTIONS'),
  (
    520,
    'ROLE_SUPERADMIN',
    'VIEW_DASHBOARD_COMMUNICATION'
  ),
  (
    519,
    'ROLE_SUPERADMIN',
    'VIEW_DASHBOARD_ENSEIGNANT'
  ),
  (
    515,
    'ROLE_SUPERADMIN',
    'VIEW_DASHBOARD_EXECUTIVE'
  ),
  (518, 'ROLE_SUPERADMIN', 'VIEW_DASHBOARD_FINANCE'),
  (
    516,
    'ROLE_SUPERADMIN',
    'VIEW_DASHBOARD_PEDAGOGIE'
  ),
  (
    517,
    'ROLE_SUPERADMIN',
    'VIEW_DASHBOARD_SCOLARITE'
  ),
  (427, 'ROLE_SUPERADMIN', 'VIEW_DEPENSES'),
  (645, 'ROLE_SUPERADMIN', 'VIEW_DEPOT_DOSSIERS'),
  (458, 'ROLE_SUPERADMIN', 'VIEW_DISCIPLINE'),
  (406, 'ROLE_SUPERADMIN', 'VIEW_DOCUMENTS'),
  (441, 'ROLE_SUPERADMIN', 'VIEW_EMPLOI_TEMPS'),
  (437, 'ROLE_SUPERADMIN', 'VIEW_ENSEIGNANTS'),
  (452, 'ROLE_SUPERADMIN', 'VIEW_ETUDIANTS'),
  (404, 'ROLE_SUPERADMIN', 'VIEW_EVENEMENTS'),
  (448, 'ROLE_SUPERADMIN', 'VIEW_EXAMENS'),
  (387, 'ROLE_SUPERADMIN', 'VIEW_FILIERES'),
  (761, 'ROLE_SUPERADMIN', 'VIEW_FONCTIONS'),
  (1176, 'ROLE_SUPERADMIN', 'VIEW_FRAIS_ANNEXES'),
  (421, 'ROLE_SUPERADMIN', 'VIEW_FRAIS_SCOLARITE'),
  (763, 'ROLE_SUPERADMIN', 'VIEW_GALLERY'),
  (409, 'ROLE_SUPERADMIN', 'VIEW_HISTORIQUE'),
  (765, 'ROLE_SUPERADMIN', 'VIEW_IMPAYES'),
  (454, 'ROLE_SUPERADMIN', 'VIEW_INSCRIPTIONS'),
  (
    821,
    'ROLE_SUPERADMIN',
    'VIEW_INSCRIPTIONS_SANS_PHOTO'
  ),
  (1600, 'ROLE_SUPERADMIN', 'VIEW_LOGS'),
  (1590, 'ROLE_SUPERADMIN', 'VIEW_MAQUETTES'),
  (393, 'ROLE_SUPERADMIN', 'VIEW_MATIERES'),
  (388, 'ROLE_SUPERADMIN', 'VIEW_NIVEAUX'),
  (443, 'ROLE_SUPERADMIN', 'VIEW_NOTES'),
  (423, 'ROLE_SUPERADMIN', 'VIEW_PAIEMENTS'),
  (771, 'ROLE_SUPERADMIN', 'VIEW_PARENTS'),
  (399, 'ROLE_SUPERADMIN', 'VIEW_PERMISSIONS'),
  (662, 'ROLE_SUPERADMIN', 'VIEW_PIECES'),
  (
    430,
    'ROLE_SUPERADMIN',
    'VIEW_RAPPORTS_FINANCIERS'
  ),
  (664, 'ROLE_SUPERADMIN', 'VIEW_REMISE_KITS'),
  (436, 'ROLE_SUPERADMIN', 'VIEW_REPORTS'),
  (398, 'ROLE_SUPERADMIN', 'VIEW_ROLES'),
  (778, 'ROLE_SUPERADMIN', 'VIEW_SALLES'),
  (391, 'ROLE_SUPERADMIN', 'VIEW_SEMESTRES'),
  (780, 'ROLE_SUPERADMIN', 'VIEW_TYPES_DEPENSE'),
  (781, 'ROLE_SUPERADMIN', 'VIEW_UE'),
  (397, 'ROLE_SUPERADMIN', 'VIEW_USERS'),
  (1616, 'ROLE_SUPERADMIN', 'REPRENDRE_PHOTO_INSCRIPTION'),
  (1617, 'all_control', 'REPRENDRE_PHOTO_INSCRIPTION'),
  (1618, 'ROLE_DIR_ETUDES', 'REPRENDRE_PHOTO_INSCRIPTION');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle_role` varchar(50) NOT NULL,
  `code_role` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `groupe` varchar(50) NOT NULL,
  `statut_role` enum('actif', 'inactif', 'null', '') NOT NULL DEFAULT 'actif',
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_role` (`code_role`),
  KEY `groupe` (`groupe`),
  KEY `idx_roles_statut_role` (`statut_role`)
) ENGINE = InnoDB AUTO_INCREMENT = 34 DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */
;
INSERT INTO `roles`
VALUES (
    24,
    'Super Administrateur',
    'ROLE_SUPERADMIN',
    'ADMINISTRATION',
    'Direction & IT',
    'actif',
    'Accès administrateur complet sur l\'ensemble des modules'
  ),
  (
    25,
    'Directeur Général',
    'ROLE_DIR_GENERAL',
    'DIRECTION',
    'Direction Générale',
    'actif',
    'Supervision globale, rapports financiers et statistiques'
  ),
  (
    26,
    'Directeur des Études',
    'ROLE_DIR_ETUDES',
    'PEDAGOGIE',
    'Direction Pédagogique',
    'actif',
    'Gestion académique, plannings, délibérations et examens'
  ),
  (
    27,
    'Chef de Département',
    'ROLE_CHEF_DEP',
    'PEDAGOGIE',
    'Coordination Filière',
    'actif',
    'Gestion des maquettes, affectation des cours et notes de la filière'
  ),
  (
    28,
    'Responsable Scolarité',
    'ROLE_SCOLARITE',
    'SCOLARITE',
    'Service Admissions',
    'actif',
    'Gestion des inscriptions, dossiers étudiants et attestations'
  ),
  (
    29,
    'Chef Comptable',
    'ROLE_COMPTABLE',
    'FINANCE',
    'Service Comptabilité',
    'actif',
    'Gestion financière, scolarités, tranches, relances et dépenses'
  ),
  (
    30,
    'Agent de Caisse',
    'ROLE_CAISSIER',
    'FINANCE',
    'Guichet & Caisse',
    'actif',
    'Encaissements quotidiens et ouverture/clôture de caisse'
  ),
  (
    31,
    'Enseignant / Formateur',
    'ROLE_ENSEIGNANT',
    'PEDAGOGIE',
    'Corps Professoral',
    'actif',
    'Consultation des cours, plannings et saisie des notes'
  ),
  (
    32,
    'Chargé de Communication',
    'ROLE_COMMUNICATION',
    'COMMUNICATION',
    'Portail & Médias',
    'actif',
    'Gestion des actualités, galeries et documents'
  ),
  (
    33,
    'all',
    'all_control',
    'ADMINISTRATION',
    'Direction',
    'actif',
    'Accès système absolu sans restriction.'
  );
/*!40000 ALTER TABLE `roles` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `salles`
--

DROP TABLE IF EXISTS `salles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `salles` (
  `id_salle` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code_salle` varchar(20) NOT NULL,
  `libelle_salle` varchar(100) NOT NULL,
  `capacite_salle` int DEFAULT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `user_code` varchar(50) NOT NULL,
  `statut_salle` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_salle`),
  UNIQUE KEY `id_salle` (`id_salle`),
  UNIQUE KEY `code_salle` (`code_salle`),
  KEY `idx_salles_etablissement_code` (`etablissement_code`),
  KEY `idx_salles_user_code` (`user_code`),
  KEY `idx_salles_statut_salle` (`statut_salle`)
) ENGINE = InnoDB AUTO_INCREMENT = 11 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `salles`
--

LOCK TABLES `salles` WRITE;
/*!40000 ALTER TABLE `salles` DISABLE KEYS */
;
INSERT INTO `salles`
VALUES (
    1,
    'SAL-FM0J7M0A',
    'Salle 1',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    2,
    'SAL-R7CZX5E5',
    'Salle 2',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    3,
    'SAL-NUHEM45Q',
    'Bibliothèque',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    4,
    'SAL-G49LMTSG',
    'Salle 3',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    5,
    'SAL-XP88YIB3',
    'Salle 4',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    6,
    'SAL-WSBLBIXP',
    'Salle 5',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    7,
    'SAL-B72UDEX6',
    'Salle 6',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    8,
    'SAL-IQBHCPU7',
    'Salle Multimédia',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    9,
    'SAL-6Y96P781',
    'Salle Informatique',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  ),
  (
    10,
    'SAL-GSAHJE2M',
    'Salle 7',
    NULL,
    '5454544456',
    '5wBEh2OfI00frxk8ITPf',
    'actif',
    '2026-09-19 21:59:12',
    NULL
  );
/*!40000 ALTER TABLE `salles` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `scolarites`
--

DROP TABLE IF EXISTS `scolarites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `scolarites` (
  `id_scolarite` int NOT NULL AUTO_INCREMENT,
  `code_scolarite` varchar(50) NOT NULL,
  `montant_scolarite` decimal(12, 2) NOT NULL,
  `affectation_etat` enum('affecte', 'non_affecte') NOT NULL DEFAULT 'non_affecte',
  `niveau_code` varchar(50) DEFAULT NULL,
  `filiere_code` varchar(50) DEFAULT NULL,
  `annee_code` varchar(50) DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `statut_scolarite` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_scolarite` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_scolarite` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_scolarite`),
  UNIQUE KEY `code_scolarite` (`code_scolarite`),
  KEY `idx_scolarites_affectation_etat` (`affectation_etat`),
  KEY `idx_scolarites_niveau_code` (`niveau_code`),
  KEY `idx_scolarites_filiere_code` (`filiere_code`),
  KEY `idx_scolarites_annee_code` (`annee_code`),
  KEY `idx_scolarites_user_code` (`user_code`),
  KEY `idx_scolarites_etablissement_code` (`etablissement_code`),
  KEY `idx_scolarites_statut_scolarite` (`statut_scolarite`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `scolarites`
--

LOCK TABLES `scolarites` WRITE;
/*!40000 ALTER TABLE `scolarites` DISABLE KEYS */
;
/*!40000 ALTER TABLE `scolarites` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `semestres`
--

DROP TABLE IF EXISTS `semestres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `semestres` (
  `id_semestre` int NOT NULL AUTO_INCREMENT,
  `code_semestre` varchar(50) NOT NULL,
  `libelle_semestre` varchar(50) NOT NULL,
  `statut_semestre` enum('planifie', 'actif', 'cloture') NOT NULL DEFAULT 'planifie',
  `etablissement_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `date_debut_semestre` date DEFAULT NULL,
  `date_fin_semestre` date DEFAULT NULL,
  `created_at_semestre` datetime NOT NULL,
  `updated_at_semestre` datetime DEFAULT NULL,
  `user_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_semestre`),
  UNIQUE KEY `uq_semestre` (`code_semestre`, `annee_code`),
  KEY `idx_semestres_statut_semestre` (`statut_semestre`),
  KEY `idx_semestres_etablissement_code` (`etablissement_code`),
  KEY `idx_semestres_annee_code` (`annee_code`),
  KEY `idx_semestres_user_code` (`user_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `semestres`
--

LOCK TABLES `semestres` WRITE;
/*!40000 ALTER TABLE `semestres` DISABLE KEYS */
;
/*!40000 ALTER TABLE `semestres` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `sessions_caisse`
--

DROP TABLE IF EXISTS `sessions_caisse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `sessions_caisse` (
  `id_session` int NOT NULL AUTO_INCREMENT,
  `code_session` varchar(50) NOT NULL,
  `date_session` date NOT NULL,
  `heure_ouverture` time NOT NULL,
  `fond_initial` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `observations_ouverture` text,
  `heure_cloture` time DEFAULT NULL,
  `total_especes` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `total_mobile_money` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `total_cheque_virement` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `total_general` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `solde_attendu` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `ecart_caisse` decimal(12, 2) NOT NULL DEFAULT '0.00',
  `observations_cloture` text,
  `statut_session` enum('ouverte', 'cloturee', 'valide', 'rejete') NOT NULL DEFAULT 'ouverte',
  `user_code` varchar(50) DEFAULT NULL,
  `user_validation` varchar(50) DEFAULT NULL,
  `annee_code` varchar(50) DEFAULT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `created_at_session` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_session`),
  UNIQUE KEY `code_session` (`code_session`),
  KEY `idx_sessions_caisse_statut_session` (`statut_session`),
  KEY `idx_sessions_caisse_user_code` (`user_code`),
  KEY `idx_sessions_caisse_annee_code` (`annee_code`),
  KEY `idx_sessions_caisse_etablissement_code` (`etablissement_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `sessions_caisse`
--

LOCK TABLES `sessions_caisse` WRITE;
/*!40000 ALTER TABLE `sessions_caisse` DISABLE KEYS */
;
/*!40000 ALTER TABLE `sessions_caisse` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `tranches_scolarite`
--

DROP TABLE IF EXISTS `tranches_scolarite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `tranches_scolarite` (
  `id_tranche` int NOT NULL AUTO_INCREMENT,
  `code_tranche` varchar(50) NOT NULL,
  `libelle_tranche` varchar(100) NOT NULL,
  `montant_tranche` decimal(12, 2) NOT NULL,
  `date_limite` date NOT NULL,
  `scolarite_code` varchar(50) DEFAULT NULL,
  `filiere_code` varchar(50) NOT NULL,
  `niveau_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `etablissement_code` varchar(50) NOT NULL,
  `statut_tranche` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `created_at_tranche` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tranche`),
  UNIQUE KEY `code_tranche` (`code_tranche`),
  KEY `idx_tranches_scolarite_date_limite` (`date_limite`),
  KEY `idx_tranches_scolarite_scolarite_code` (`scolarite_code`),
  KEY `idx_tranches_scolarite_filiere_code` (`filiere_code`),
  KEY `idx_tranches_scolarite_niveau_code` (`niveau_code`),
  KEY `idx_tranches_scolarite_annee_code` (`annee_code`),
  KEY `idx_tranches_scolarite_etablissement_code` (`etablissement_code`),
  KEY `idx_tranches_scolarite_statut_tranche` (`statut_tranche`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `tranches_scolarite`
--

LOCK TABLES `tranches_scolarite` WRITE;
/*!40000 ALTER TABLE `tranches_scolarite` DISABLE KEYS */
;
/*!40000 ALTER TABLE `tranches_scolarite` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `type_depenses`
--

DROP TABLE IF EXISTS `type_depenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `type_depenses` (
  `id_type_depense` int NOT NULL AUTO_INCREMENT,
  `code_type_depense` varchar(50) NOT NULL,
  `libelle_type_depense` varchar(255) NOT NULL,
  `statut_type_depense` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `etablissement_code` varchar(50) NOT NULL,
  `user_code` varchar(50) NOT NULL,
  PRIMARY KEY (`id_type_depense`),
  KEY `idx_type_depenses_code_type_depense` (`code_type_depense`),
  KEY `idx_type_depenses_statut_type_depense` (`statut_type_depense`),
  KEY `idx_type_depenses_etablissement_code` (`etablissement_code`),
  KEY `idx_type_depenses_user_code` (`user_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 12 DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `type_depenses`
--

LOCK TABLES `type_depenses` WRITE;
/*!40000 ALTER TABLE `type_depenses` DISABLE KEYS */
;
INSERT INTO `type_depenses`
VALUES (
    1,
    'TYP-DESK001',
    'Fournitures & Consommables de Bureau',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    2,
    'TYP-RENT002',
    'Loyer & Charges Locatives',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    3,
    'TYP-ELEC003',
    'Électricité, Eau & Énergie',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    4,
    'TYP-SALR004',
    'Salaires, Honoraires & Rémunérations',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    5,
    'TYP-MAINT05',
    'Maintenance & Entretien des Locaux',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    6,
    'TYP-ITCOMP6',
    'Matériel Informatique & Équipements',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    7,
    'TYP-COMM007',
    'Téléphone, Internet & Communication',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    8,
    'TYP-TRAN008',
    'Transport & Frais de Déplacement',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    9,
    'TYP-BANK009',
    'Frais Bancaires & Services Financiers',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    10,
    'TYP-MISC010',
    'Dépenses Diverses & Imprévus',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  ),
  (
    11,
    'TYP-JRP5CVR2',
    'test',
    'actif',
    '5454544456',
    '5wBEh2OfI00frxk8ITPf'
  );
/*!40000 ALTER TABLE `type_depenses` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `user_annee_acces`
--

DROP TABLE IF EXISTS `user_annee_acces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `user_annee_acces` (
  `id_user_annee_acces` int NOT NULL AUTO_INCREMENT,
  `user_code` varchar(50) NOT NULL,
  `annee_code` varchar(50) NOT NULL,
  `niveau_acces` enum('lecture', 'ecriture') NOT NULL DEFAULT 'lecture',
  `created_at_acces` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_acces` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user_annee_acces`),
  UNIQUE KEY `uq_user_annee` (`user_code`, `annee_code`),
  KEY `idx_uaa_user` (`user_code`),
  KEY `idx_uaa_annee` (`annee_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `user_annee_acces`
--

LOCK TABLES `user_annee_acces` WRITE;
/*!40000 ALTER TABLE `user_annee_acces` DISABLE KEYS */
;
/*!40000 ALTER TABLE `user_annee_acces` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `user_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_code` varchar(50) NOT NULL,
  `role_code` varchar(50) NOT NULL,
  `create_permission` int NOT NULL DEFAULT '0',
  `edit_permission` int NOT NULL DEFAULT '0',
  `show_permission` int NOT NULL DEFAULT '0',
  `delete_permission` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_role` (`user_code`, `role_code`),
  KEY `idx_user_roles_role_code` (`role_code`)
) ENGINE = InnoDB AUTO_INCREMENT = 160 DEFAULT CHARSET = latin1;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */
;
INSERT INTO `user_roles`
VALUES (
    114,
    '5wBEh2OfI00frxk8ITPf',
    'ROLE_SUPERADMIN',
    1,
    1,
    1,
    1
  ),
  (
    157,
    'USR-2G4SUZC0',
    'ROLE_DIR_GENERAL',
    0,
    0,
    0,
    0
  ),
  (
    159,
    'USR-3UGH4028',
    'ROLE_DIR_ETUDES',
    1,
    1,
    1,
    0
  );
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `code_user` varchar(50) NOT NULL,
  `matricule_user` varchar(50) DEFAULT NULL,
  `nom_user` varchar(100) NOT NULL,
  `prenom_user` varchar(100) NOT NULL,
  `email_user` varchar(150) NOT NULL,
  `sexe_user` varchar(20) DEFAULT NULL,
  `password_user` varchar(255) NOT NULL,
  `telephone_user` varchar(30) DEFAULT NULL,
  `photo_user` varchar(255) DEFAULT NULL,
  `last_connexion` datetime DEFAULT NULL,
  `token_user` varchar(255) DEFAULT NULL,
  `service_code` varchar(50) DEFAULT NULL,
  `fonction_code` varchar(100) DEFAULT NULL,
  `etablissement_code` varchar(50) DEFAULT NULL,
  `auth_uid` varchar(255) DEFAULT NULL,
  `created_at_user` datetime NOT NULL,
  `updated_at_user` datetime DEFAULT NULL,
  `statut_user` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email_user` (`email_user`),
  UNIQUE KEY `code_user` (`code_user`),
  KEY `idx_users_service_code` (`service_code`),
  KEY `idx_users_fonction_code` (`fonction_code`),
  KEY `idx_users_etablissement_code` (`etablissement_code`),
  KEY `idx_users_statut_user` (`statut_user`)
) ENGINE = InnoDB AUTO_INCREMENT = 33 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */
;
INSERT INTO `users`
VALUES (
    1,
    '5wBEh2OfI00frxk8ITPf',
    '54564777165',
    'Admin',
    'Admin',
    'admin@gmail.com',
    'M',
    '$2y$10$Sr17rNg92YcFfbWbXh0M/eWgStriw9Q.xGtNf5CkJwMbEQ7O6khDu',
    '0544564564',
    NULL,
    '2026-08-21 21:29:46',
    NULL,
    '123',
    'FCT-DIR',
    '5454544456',
    NULL,
    '2026-07-15 11:48:46',
    '2026-08-26 11:23:16',
    'actif'
  ),
  (
    31,
    'USR-2G4SUZC0',
    NULL,
    'Sanogo',
    'Abou Treyka',
    'treyka@gmail.com',
    'M',
    '$2y$10$Sr17rNg92YcFfbWbXh0M/eWgStriw9Q.xGtNf5CkJwMbEQ7O6khDu',
    '0575444757',
    NULL,
    NULL,
    NULL,
    NULL,
    '',
    '5454544456',
    NULL,
    '2026-09-10 17:52:07',
    '2026-09-18 14:54:22',
    'actif'
  ),
  (
    32,
    'USR-3UGH4028',
    NULL,
    'patrice',
    'Kone',
    'test@gmail.com',
    'M',
    '$2y$10$Sr17rNg92YcFfbWbXh0M/eWgStriw9Q.xGtNf5CkJwMbEQ7O6khDu',
    '0102030405',
    NULL,
    NULL,
    NULL,
    NULL,
    '',
    '5454544456',
    NULL,
    '2026-09-10 19:46:05',
    '2026-09-18 15:03:10',
    'actif'
  );
/*!40000 ALTER TABLE `users` ENABLE KEYS */
;
UNLOCK TABLES;
--
-- Temporary view structure for view `v_absences_completes`
--

DROP TABLE IF EXISTS `v_absences_completes`;
/*!50001 DROP VIEW IF EXISTS `v_absences_completes`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_absences_completes` AS SELECT 
 1 AS `id_absence`,
 1 AS `code_absence`,
 1 AS `inscription_code`,
 1 AS `emploi_code`,
 1 AS `matiere_code`,
 1 AS `date_absence`,
 1 AS `duree_heures`,
 1 AS `justifiee`,
 1 AS `motif`,
 1 AS `motif_absence`,
 1 AS `annee_code`,
 1 AS `etablissement_code`,
 1 AS `created_at_absence`,
 1 AS `inscription_annee_code`,
 1 AS `classe_code`,
 1 AS `etu_nom`,
 1 AS `etu_prenom`,
 1 AS `nom_etudiant`,
 1 AS `matricule_etudiant`,
 1 AS `telephone_etudiant`,
 1 AS `email_etudiant`,
 1 AS `code_etudiant`,
 1 AS `code_classe`,
 1 AS `libelle_classe`,
 1 AS `niveau_code`,
 1 AS `libelle_filiere`,
 1 AS `libelle_niveau`,
 1 AS `libelle_matiere`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_bilan_paiements_inscriptions`
--

DROP TABLE IF EXISTS `v_bilan_paiements_inscriptions`;
/*!50001 DROP VIEW IF EXISTS `v_bilan_paiements_inscriptions`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_bilan_paiements_inscriptions` AS SELECT 
 1 AS `inscription_code`,
 1 AS `total_paye_scolarite`,
 1 AS `total_paye_confirme_scolarite`,
 1 AS `total_paye_annexes`,
 1 AS `nb_versements`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_bulletin_inscriptions_synthese`
--

DROP TABLE IF EXISTS `v_bulletin_inscriptions_synthese`;
/*!50001 DROP VIEW IF EXISTS `v_bulletin_inscriptions_synthese`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_bulletin_inscriptions_synthese` AS SELECT 
 1 AS `id_inscription`,
 1 AS `code_inscription`,
 1 AS `etudiant_code`,
 1 AS `classe_code`,
 1 AS `annee_code`,
 1 AS `etablissement_code`,
 1 AS `statut_inscription`,
 1 AS `affectation_etat`,
 1 AS `photo_inscription`,
 1 AS `id_etudiant`,
 1 AS `code_etudiant`,
 1 AS `matricule_etudiant`,
 1 AS `matricule_menet`,
 1 AS `matricule_mesrs`,
 1 AS `nom_etudiant`,
 1 AS `prenom_etudiant`,
 1 AS `etudiant_nom`,
 1 AS `sexe_etudiant`,
 1 AS `photo_etudiant`,
 1 AS `telephone_etudiant`,
 1 AS `email_etudiant`,
 1 AS `code_classe`,
 1 AS `libelle_classe`,
 1 AS `classe_nom`,
 1 AS `filiere_code`,
 1 AS `niveau_code`,
 1 AS `cycle_code`,
 1 AS `code_filiere`,
 1 AS `libelle_filiere`,
 1 AS `filiere_nom`,
 1 AS `slug_filiere`,
 1 AS `type_filiere`,
 1 AS `code_niveau`,
 1 AS `libelle_niveau`,
 1 AS `niveau_nom`,
 1 AS `slug_niveau`,
 1 AS `code_annee`,
 1 AS `libelle_annee`,
 1 AS `annee_nom`,
 1 AS `coef_semestre1`,
 1 AS `coef_semestre2`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_bulletin_notes_details`
--

DROP TABLE IF EXISTS `v_bulletin_notes_details`;
/*!50001 DROP VIEW IF EXISTS `v_bulletin_notes_details`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_bulletin_notes_details` AS SELECT 
 1 AS `id_note`,
 1 AS `code_note`,
 1 AS `valeur_note`,
 1 AS `statut_note`,
 1 AS `observations`,
 1 AS `created_at_note`,
 1 AS `inscription_code`,
 1 AS `etudiant_code`,
 1 AS `classe_code`,
 1 AS `annee_code`,
 1 AS `filiere_code`,
 1 AS `niveau_code`,
 1 AS `libelle_classe`,
 1 AS `libelle_filiere`,
 1 AS `libelle_niveau`,
 1 AS `slug_niveau`,
 1 AS `id_composition`,
 1 AS `code_composition`,
 1 AS `libelle_composition`,
 1 AS `type_evaluation_code`,
 1 AS `semestre_code`,
 1 AS `coefficient_composition`,
 1 AS `statut_composition`,
 1 AS `enseignant_user_code`,
 1 AS `nom_enseignant`,
 1 AS `enseignant`,
 1 AS `matiere_code`,
 1 AS `libelle_matiere`,
 1 AS `coefficient_maquette`,
 1 AS `categorie_matiere`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_dash_depenses_details`
--

DROP TABLE IF EXISTS `v_dash_depenses_details`;
/*!50001 DROP VIEW IF EXISTS `v_dash_depenses_details`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_dash_depenses_details` AS SELECT 
 1 AS `id_depense`,
 1 AS `code_depense`,
 1 AS `montant_depense`,
 1 AS `description_depense`,
 1 AS `periode_depense`,
 1 AS `annee_code`,
 1 AS `statut_depense`,
 1 AS `created_at_depense`,
 1 AS `code_type_depense`,
 1 AS `libelle_type_depense`,
 1 AS `nom_user`,
 1 AS `prenom_user`,
 1 AS `libelle_annee`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_dash_inscriptions_details`
--

DROP TABLE IF EXISTS `v_dash_inscriptions_details`;
/*!50001 DROP VIEW IF EXISTS `v_dash_inscriptions_details`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_dash_inscriptions_details` AS SELECT 
 1 AS `id_inscription`,
 1 AS `code_inscription`,
 1 AS `etudiant_code`,
 1 AS `classe_code`,
 1 AS `annee_code`,
 1 AS `statut_inscription`,
 1 AS `montant_scolarite_inscription`,
 1 AS `affectation_etat`,
 1 AS `created_at_inscription`,
 1 AS `matricule_etudiant`,
 1 AS `matricule_menet`,
 1 AS `matricule_mesrs`,
 1 AS `nom_etudiant`,
 1 AS `prenom_etudiant`,
 1 AS `nom_complet_etudiant`,
 1 AS `sexe_etudiant`,
 1 AS `telephone_etudiant`,
 1 AS `email_etudiant`,
 1 AS `photo_etudiant`,
 1 AS `statut_etudiant`,
 1 AS `libelle_classe`,
 1 AS `code_filiere`,
 1 AS `libelle_filiere`,
 1 AS `code_niveau`,
 1 AS `libelle_niveau`,
 1 AS `libelle_annee`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_dash_paiements_details`
--

DROP TABLE IF EXISTS `v_dash_paiements_details`;
/*!50001 DROP VIEW IF EXISTS `v_dash_paiements_details`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_dash_paiements_details` AS SELECT 
 1 AS `id_paiement`,
 1 AS `code_paiement`,
 1 AS `montant_paiement`,
 1 AS `date_paiement`,
 1 AS `statut_paiement`,
 1 AS `reference_paiement`,
 1 AS `type_paiement`,
 1 AS `mode_paiement`,
 1 AS `annee_code`,
 1 AS `inscription_code`,
 1 AS `etudiant_code`,
 1 AS `classe_code`,
 1 AS `matricule_etudiant`,
 1 AS `nom_etudiant`,
 1 AS `prenom_etudiant`,
 1 AS `nom_complet_etudiant`,
 1 AS `libelle_classe`,
 1 AS `libelle_annee`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_dash_pedagogie_affectations`
--

DROP TABLE IF EXISTS `v_dash_pedagogie_affectations`;
/*!50001 DROP VIEW IF EXISTS `v_dash_pedagogie_affectations`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_dash_pedagogie_affectations` AS SELECT 
 1 AS `id_enseignant_matiere`,
 1 AS `enseignant_code`,
 1 AS `matiere_code`,
 1 AS `classe_code`,
 1 AS `coefficient`,
 1 AS `etablissement_code`,
 1 AS `annee_code`,
 1 AS `user_code`,
 1 AS `created_at_enseignant_matiere`,
 1 AS `updated_at_enseignant_matiere`,
 1 AS `statut_enseignant_matiere`,
 1 AS `libelle_matiere`,
 1 AS `libelle_classe`,
 1 AS `niveau_code`,
 1 AS `filiere_code`,
 1 AS `libelle_filiere`,
 1 AS `libelle_niveau`,
 1 AS `code_enseignant`,
 1 AS `grade_enseignant`,
 1 AS `nom_prof`,
 1 AS `prenom_prof`,
 1 AS `nom_enseignant`,
 1 AS `prenom_enseignant`,
 1 AS `email_enseignant`,
 1 AS `telephone_enseignant`,
 1 AS `enseignant_nom`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_dash_synthese_annuelle`
--

DROP TABLE IF EXISTS `v_dash_synthese_annuelle`;
/*!50001 DROP VIEW IF EXISTS `v_dash_synthese_annuelle`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_dash_synthese_annuelle` AS SELECT 
 1 AS `code_annee`,
 1 AS `libelle_annee`,
 1 AS `statut_annee`,
 1 AS `total_inscrits`,
 1 AS `total_classes`,
 1 AS `total_scolarite_attendue`,
 1 AS `total_encaisse`,
 1 AS `total_depenses`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_emplois_temps_complets`
--

DROP TABLE IF EXISTS `v_emplois_temps_complets`;
/*!50001 DROP VIEW IF EXISTS `v_emplois_temps_complets`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_emplois_temps_complets` AS SELECT 
 1 AS `id_emploi`,
 1 AS `code_emploi`,
 1 AS `classe_code`,
 1 AS `matiere_code`,
 1 AS `enseignant_code`,
 1 AS `salle_code`,
 1 AS `etablissement_code`,
 1 AS `annee_code`,
 1 AS `user_code`,
 1 AS `jour`,
 1 AS `heure_debut`,
 1 AS `heure_fin`,
 1 AS `created_at_emploi`,
 1 AS `updated_at_emploi`,
 1 AS `statut_emploi`,
 1 AS `libelle_classe`,
 1 AS `niveau_code`,
 1 AS `libelle_niveau`,
 1 AS `libelle_matiere`,
 1 AS `libelle_salle`,
 1 AS `nom_prof`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_inscriptions_completes`
--

DROP TABLE IF EXISTS `v_inscriptions_completes`;
/*!50001 DROP VIEW IF EXISTS `v_inscriptions_completes`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_inscriptions_completes` AS SELECT 
 1 AS `id_inscription`,
 1 AS `code_inscription`,
 1 AS `etudiant_code`,
 1 AS `classe_code`,
 1 AS `etablissement_code`,
 1 AS `annee_code`,
 1 AS `user_code`,
 1 AS `created_at_inscription`,
 1 AS `updated_at_inscription`,
 1 AS `montant_scolarite_inscription`,
 1 AS `canal_inscription`,
 1 AS `affectation_etat`,
 1 AS `statut_inscription`,
 1 AS `photo_inscription`,
 1 AS `id_etudiant`,
 1 AS `code_etudiant`,
 1 AS `matricule_etudiant`,
 1 AS `matricule_menet`,
 1 AS `matricule_mesrs`,
 1 AS `nom_etudiant`,
 1 AS `prenom_etudiant`,
 1 AS `etudiant_nom`,
 1 AS `nom_complet_etudiant`,
 1 AS `date_naissance_etudiant`,
 1 AS `lieu_naissance_etudiant`,
 1 AS `sexe_etudiant`,
 1 AS `nationalite_etudiant`,
 1 AS `lieu_residence_etudiant`,
 1 AS `telephone_etudiant`,
 1 AS `email_etudiant`,
 1 AS `photo_etudiant`,
 1 AS `statut_etudiant`,
 1 AS `etablissement_origine`,
 1 AS `code_classe`,
 1 AS `libelle_classe`,
 1 AS `classe_nom`,
 1 AS `filiere_code`,
 1 AS `niveau_code`,
 1 AS `cycle_code`,
 1 AS `code_filiere`,
 1 AS `libelle_filiere`,
 1 AS `slug_filiere`,
 1 AS `type_filiere`,
 1 AS `code_niveau`,
 1 AS `libelle_niveau`,
 1 AS `slug_niveau`,
 1 AS `code_annee`,
 1 AS `libelle_annee`,
 1 AS `annee_nom`,
 1 AS `coef_semestre1`,
 1 AS `coef_semestre2`,
 1 AS `nom_pere`,
 1 AS `telephone_pere`,
 1 AS `profession_pere`,
 1 AS `nom_mere`,
 1 AS `telephone_mere`,
 1 AS `profession_mere`,
 1 AS `nom_tuteur`,
 1 AS `telephone_tuteur`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_maquettes_completes`
--

DROP TABLE IF EXISTS `v_maquettes_completes`;
/*!50001 DROP VIEW IF EXISTS `v_maquettes_completes`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_maquettes_completes` AS SELECT 
 1 AS `id_maquette`,
 1 AS `code_maquette`,
 1 AS `annee_code`,
 1 AS `filiere_code`,
 1 AS `niveau_code`,
 1 AS `matiere_code`,
 1 AS `categorie_matiere`,
 1 AS `coefficient`,
 1 AS `etablissement_code`,
 1 AS `statut_maquette`,
 1 AS `user_code`,
 1 AS `created_at_maquette`,
 1 AS `updated_at_maquette`,
 1 AS `libelle_matiere`,
 1 AS `libelle_filiere`,
 1 AS `type_filiere`,
 1 AS `libelle_niveau`,
 1 AS `libelle_annee`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_notes_completes`
--

DROP TABLE IF EXISTS `v_notes_completes`;
/*!50001 DROP VIEW IF EXISTS `v_notes_completes`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_notes_completes` AS SELECT 
 1 AS `id_note`,
 1 AS `code_note`,
 1 AS `valeur_note`,
 1 AS `composition_code`,
 1 AS `composition_matiere_id`,
 1 AS `etudiant_code`,
 1 AS `observations`,
 1 AS `inscription_code`,
 1 AS `user_code`,
 1 AS `etablissement_code`,
 1 AS `created_at_note`,
 1 AS `updated_at_note`,
 1 AS `statut_note`,
 1 AS `etu_nom`,
 1 AS `etu_prenom`,
 1 AS `nom_etudiant`,
 1 AS `matricule_etudiant`,
 1 AS `telephone_etudiant`,
 1 AS `email_etudiant`,
 1 AS `libelle_matiere`,
 1 AS `matiere_code`,
 1 AS `code_classe`,
 1 AS `libelle_classe`,
 1 AS `niveau_code`,
 1 AS `filiere_code`,
 1 AS `libelle_filiere`,
 1 AS `libelle_niveau`,
 1 AS `libelle_semestre`,
 1 AS `semestre_code`,
 1 AS `libelle_annee`,
 1 AS `annee_code`,
 1 AS `inscription_classe_code`,
 1 AS `libelle_composition`,
 1 AS `type_composition`,
 1 AS `type_evaluation_code`,
 1 AS `coefficient`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_paiements_complets`
--

DROP TABLE IF EXISTS `v_paiements_complets`;
/*!50001 DROP VIEW IF EXISTS `v_paiements_complets`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_paiements_complets` AS SELECT 
 1 AS `id_paiement`,
 1 AS `code_paiement`,
 1 AS `montant_paiement`,
 1 AS `date_paiement`,
 1 AS `statut_paiement`,
 1 AS `canal_paiement`,
 1 AS `reference_paiement`,
 1 AS `observations`,
 1 AS `type_paiement`,
 1 AS `categorie_paiement`,
 1 AS `mode_paiement`,
 1 AS `user_code`,
 1 AS `annee_code`,
 1 AS `etablissement_code`,
 1 AS `inscription_code`,
 1 AS `tranche_code`,
 1 AS `session_caisse_code`,
 1 AS `code_inscription`,
 1 AS `etudiant_code`,
 1 AS `classe_code`,
 1 AS `inscription_annee_code`,
 1 AS `statut_inscription`,
 1 AS `montant_scolarite_inscription`,
 1 AS `affectation_etat`,
 1 AS `photo_inscription`,
 1 AS `code_etudiant`,
 1 AS `matricule_etudiant`,
 1 AS `nom_etudiant`,
 1 AS `prenom_etudiant`,
 1 AS `etudiant_nom`,
 1 AS `telephone_etudiant`,
 1 AS `email_etudiant`,
 1 AS `photo_etudiant`,
 1 AS `code_classe`,
 1 AS `libelle_classe`,
 1 AS `niveau_code`,
 1 AS `filiere_code`,
 1 AS `code_filiere`,
 1 AS `libelle_filiere`,
 1 AS `type_filiere`,
 1 AS `code_niveau`,
 1 AS `libelle_niveau`,
 1 AS `libelle_annee`,
 1 AS `nom_user`,
 1 AS `prenom_user`,
 1 AS `nom_caissier`,
 1 AS `prenom_caissier`,
 1 AS `caissier_code`,
 1 AS `libelle_tranche`,
 1 AS `montant_tranche`,
 1 AS `date_limite_tranche`*/
;
SET character_set_client = @saved_cs_client;
--
-- Temporary view structure for view `v_sessions_evaluations`
--

DROP TABLE IF EXISTS `v_sessions_evaluations`;
/*!50001 DROP VIEW IF EXISTS `v_sessions_evaluations`*/
;
SET @saved_cs_client = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */
;
/*!50001 CREATE VIEW `v_sessions_evaluations` AS SELECT 
 1 AS `id_composition`,
 1 AS `code_composition`,
 1 AS `libelle_composition`,
 1 AS `type_composition`,
 1 AS `semestre_code`,
 1 AS `coefficient`,
 1 AS `date_composition`,
 1 AS `date_fin_composition`,
 1 AS `statut_composition`,
 1 AS `created_at_composition`,
 1 AS `composition_user_code`,
 1 AS `annee_code`,
 1 AS `etablissement_code`,
 1 AS `id_composition_matiere`,
 1 AS `matiere_code`,
 1 AS `libelle_matiere`,
 1 AS `code_classe`,
 1 AS `libelle_classe`,
 1 AS `niveau_code`,
 1 AS `filiere_code`,
 1 AS `libelle_niveau`,
 1 AS `libelle_filiere`,
 1 AS `libelle_semestre`,
 1 AS `statut_semestre`,
 1 AS `libelle_annee`,
 1 AS `date_enregistrement`,
 1 AS `date_enregistrement_note`,
 1 AS `total_etudiants`,
 1 AS `nb_notes`,
 1 AS `moyenne_classe`*/
;
SET character_set_client = @saved_cs_client;
--
-- Dumping routines for database 'c2833857c_eicg'
--

--
-- Final view structure for view `v_absences_completes`
--

/*!50001 DROP VIEW IF EXISTS `v_absences_completes`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_absences_completes` AS select `abs`.`id_absence` AS `id_absence`,`abs`.`code_absence` AS `code_absence`,`abs`.`inscription_code` AS `inscription_code`,`abs`.`emploi_code` AS `emploi_code`,`abs`.`matiere_code` AS `matiere_code`,`abs`.`date_absence` AS `date_absence`,`abs`.`duree_heures` AS `duree_heures`,`abs`.`justifiee` AS `justifiee`,`abs`.`motif` AS `motif`,`abs`.`motif` AS `motif_absence`,`abs`.`annee_code` AS `annee_code`,`abs`.`etablissement_code` AS `etablissement_code`,`abs`.`created_at_absence` AS `created_at_absence`,`ins`.`annee_code` AS `inscription_annee_code`,`ins`.`classe_code` AS `classe_code`,`e`.`nom_etudiant` AS `etu_nom`,`e`.`prenom_etudiant` AS `etu_prenom`,concat(coalesce(`e`.`nom_etudiant`,''),' ',coalesce(`e`.`prenom_etudiant`,'')) AS `nom_etudiant`,`e`.`matricule_etudiant` AS `matricule_etudiant`,`e`.`telephone_etudiant` AS `telephone_etudiant`,`e`.`email_etudiant` AS `email_etudiant`,`e`.`code_etudiant` AS `code_etudiant`,`cl`.`code_classe` AS `code_classe`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`niveau_code` AS `niveau_code`,`f`.`libelle_filiere` AS `libelle_filiere`,`n`.`libelle_niveau` AS `libelle_niveau`,`m`.`libelle_matiere` AS `libelle_matiere` from ((((((`absences` `abs` left join `inscriptions` `ins` on((`ins`.`code_inscription` = `abs`.`inscription_code`))) left join `etudiants` `e` on((`e`.`code_etudiant` = `ins`.`etudiant_code`))) left join `classes` `cl` on((`cl`.`code_classe` = `ins`.`classe_code`))) left join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) left join `niveaux` `n` on((`n`.`code_niveau` = `cl`.`niveau_code`))) left join `matieres` `m` on((`m`.`code_matiere` = `abs`.`matiere_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_bilan_paiements_inscriptions`
--

/*!50001 DROP VIEW IF EXISTS `v_bilan_paiements_inscriptions`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_bilan_paiements_inscriptions` AS select `p`.`inscription_code` AS `inscription_code`,coalesce(sum((case when ((`p`.`statut_paiement` <> 'annule') and ((`p`.`categorie_paiement` is null) or (`p`.`categorie_paiement` <> 'FRAIS_ANNEXES')) and ((`p`.`tranche_code` is null) or (`p`.`tranche_code` <> 'FRAIS_ANNEXES')) and ((`p`.`type_paiement` is null) or (not((`p`.`type_paiement` like '%Frais Annexe%'))))) then `p`.`montant_paiement` else 0 end)),0) AS `total_paye_scolarite`,coalesce(sum((case when ((`p`.`statut_paiement` = 'confirme') and ((`p`.`categorie_paiement` is null) or (`p`.`categorie_paiement` <> 'FRAIS_ANNEXES')) and ((`p`.`tranche_code` is null) or (`p`.`tranche_code` <> 'FRAIS_ANNEXES')) and ((`p`.`type_paiement` is null) or (not((`p`.`type_paiement` like '%Frais Annexe%'))))) then `p`.`montant_paiement` else 0 end)),0) AS `total_paye_confirme_scolarite`,coalesce(sum((case when ((`p`.`statut_paiement` <> 'annule') and ((`p`.`categorie_paiement` = 'FRAIS_ANNEXES') or (`p`.`tranche_code` = 'FRAIS_ANNEXES') or (`p`.`type_paiement` like '%Frais Annexe%'))) then `p`.`montant_paiement` else 0 end)),0) AS `total_paye_annexes`,count((case when (`p`.`statut_paiement` <> 'annule') then 1 end)) AS `nb_versements` from `paiements` `p` group by `p`.`inscription_code` */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_bulletin_inscriptions_synthese`
--

/*!50001 DROP VIEW IF EXISTS `v_bulletin_inscriptions_synthese`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_bulletin_inscriptions_synthese` AS select `i`.`id_inscription` AS `id_inscription`,`i`.`code_inscription` AS `code_inscription`,`i`.`etudiant_code` AS `etudiant_code`,`i`.`classe_code` AS `classe_code`,`i`.`annee_code` AS `annee_code`,`i`.`etablissement_code` AS `etablissement_code`,`i`.`statut_inscription` AS `statut_inscription`,`i`.`affectation_etat` AS `affectation_etat`,`i`.`photo_inscription` AS `photo_inscription`,`e`.`id_etudiant` AS `id_etudiant`,`e`.`code_etudiant` AS `code_etudiant`,`e`.`matricule_etudiant` AS `matricule_etudiant`,`e`.`matricule_menet` AS `matricule_menet`,`e`.`matricule_mesrs` AS `matricule_mesrs`,`e`.`nom_etudiant` AS `nom_etudiant`,`e`.`prenom_etudiant` AS `prenom_etudiant`,trim(concat(coalesce(`e`.`nom_etudiant`,''),' ',coalesce(`e`.`prenom_etudiant`,''))) AS `etudiant_nom`,`e`.`sexe_etudiant` AS `sexe_etudiant`,`e`.`photo_etudiant` AS `photo_etudiant`,`e`.`telephone_etudiant` AS `telephone_etudiant`,`e`.`email_etudiant` AS `email_etudiant`,`cl`.`code_classe` AS `code_classe`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`libelle_classe` AS `classe_nom`,`cl`.`filiere_code` AS `filiere_code`,`cl`.`niveau_code` AS `niveau_code`,`cl`.`cycle_code` AS `cycle_code`,`f`.`code_filiere` AS `code_filiere`,`f`.`libelle_filiere` AS `libelle_filiere`,`f`.`libelle_filiere` AS `filiere_nom`,`f`.`slug_filiere` AS `slug_filiere`,`f`.`type_filiere` AS `type_filiere`,`n`.`code_niveau` AS `code_niveau`,`n`.`libelle_niveau` AS `libelle_niveau`,`n`.`libelle_niveau` AS `niveau_nom`,`n`.`slug_niveau` AS `slug_niveau`,`a`.`code_annee` AS `code_annee`,`a`.`libelle_annee` AS `libelle_annee`,`a`.`libelle_annee` AS `annee_nom`,`a`.`coef_semestre1` AS `coef_semestre1`,`a`.`coef_semestre2` AS `coef_semestre2` from (((((`inscriptions` `i` join `etudiants` `e` on((`e`.`code_etudiant` = `i`.`etudiant_code`))) left join `classes` `cl` on((`cl`.`code_classe` = `i`.`classe_code`))) left join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) left join `niveaux` `n` on((`n`.`code_niveau` = `cl`.`niveau_code`))) left join `annees` `a` on((`a`.`code_annee` = `i`.`annee_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_bulletin_notes_details`
--

/*!50001 DROP VIEW IF EXISTS `v_bulletin_notes_details`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_bulletin_notes_details` AS select `n`.`id_note` AS `id_note`,`n`.`code_note` AS `code_note`,`n`.`valeur_note` AS `valeur_note`,`n`.`statut_note` AS `statut_note`,`n`.`observations` AS `observations`,`n`.`created_at_note` AS `created_at_note`,`n`.`inscription_code` AS `inscription_code`,`i`.`etudiant_code` AS `etudiant_code`,`i`.`classe_code` AS `classe_code`,`i`.`annee_code` AS `annee_code`,`cl`.`filiere_code` AS `filiere_code`,`cl`.`niveau_code` AS `niveau_code`,`cl`.`libelle_classe` AS `libelle_classe`,`f`.`libelle_filiere` AS `libelle_filiere`,`niv`.`libelle_niveau` AS `libelle_niveau`,`niv`.`slug_niveau` AS `slug_niveau`,`comp`.`id_composition` AS `id_composition`,`comp`.`code_composition` AS `code_composition`,`comp`.`libelle_composition` AS `libelle_composition`,`comp`.`type_composition` AS `type_evaluation_code`,`comp`.`semestre_code` AS `semestre_code`,coalesce(`comp`.`coefficient`,1.00) AS `coefficient_composition`,`comp`.`statut_composition` AS `statut_composition`,`comp`.`user_code` AS `enseignant_user_code`,coalesce(nullif(trim(`edt_ens`.`enseignant`),''),concat(coalesce(`u`.`nom_user`,''),' ',coalesce(`u`.`prenom_user`,''))) AS `nom_enseignant`,`edt_ens`.`enseignant` AS `enseignant`,`cm`.`matiere_code` AS `matiere_code`,`m`.`libelle_matiere` AS `libelle_matiere`,coalesce(`mq`.`coefficient`,1.00) AS `coefficient_maquette`,coalesce(`mq`.`categorie_matiere`,'GENERALE') AS `categorie_matiere` from (((((((((((`notes` `n` join `composition_matieres` `cm` on((`cm`.`id_composition_matiere` = `n`.`composition_matiere_id`))) join `composition_niveau_filiere` `cnf` on((`cnf`.`code_composition_niveau_filiere` = `cm`.`composition_niveau_filiere_code`))) join `compositions` `comp` on((`comp`.`code_composition` = `cm`.`composition_code`))) join `inscriptions` `i` on((`i`.`code_inscription` = `n`.`inscription_code`))) join `classes` `cl` on((`cl`.`code_classe` = `i`.`classe_code`))) join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) join `niveaux` `niv` on((`niv`.`code_niveau` = `cl`.`niveau_code`))) join `matieres` `m` on((`m`.`code_matiere` = `cm`.`matiere_code`))) left join `users` `u` on((`u`.`code_user` = `comp`.`user_code`))) left join (select `edt`.`classe_code` AS `classe_code`,`edt`.`matiere_code` AS `matiere_code`,coalesce(`edt`.`annee_code`,'') AS `annee_code`,group_concat(distinct trim(concat(coalesce(`u_edt`.`nom_user`,''),' ',coalesce(`u_edt`.`prenom_user`,''))) separator ', ') AS `enseignant` from (`emplois_temps` `edt` left join `users` `u_edt` on((`u_edt`.`code_user` = `edt`.`enseignant_code`))) where ((`edt`.`statut_emploi` = 'actif') or (`edt`.`statut_emploi` is null)) group by `edt`.`classe_code`,`edt`.`matiere_code`,coalesce(`edt`.`annee_code`,'')) `edt_ens` on(((`edt_ens`.`classe_code` = `i`.`classe_code`) and (`edt_ens`.`matiere_code` = `cm`.`matiere_code`) and ((`edt_ens`.`annee_code` = `i`.`annee_code`) or (`edt_ens`.`annee_code` = '') or (`edt_ens`.`annee_code` is null))))) left join `maquettes_pedagogiques` `mq` on(((`mq`.`filiere_code` = `cl`.`filiere_code`) and (`mq`.`niveau_code` = `cl`.`niveau_code`) and (`mq`.`matiere_code` = `cm`.`matiere_code`) and ((`mq`.`annee_code` = `i`.`annee_code`) or (`mq`.`annee_code` = '') or (`mq`.`annee_code` is null)) and ((`mq`.`statut_maquette` = 'actif') or (`mq`.`statut_maquette` is null))))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_dash_depenses_details`
--

/*!50001 DROP VIEW IF EXISTS `v_dash_depenses_details`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_dash_depenses_details` AS select `d`.`id_depense` AS `id_depense`,`d`.`code_depense` AS `code_depense`,`d`.`montant_depense` AS `montant_depense`,`d`.`description_depense` AS `description_depense`,`d`.`periode_depense` AS `periode_depense`,`d`.`annee_code` AS `annee_code`,`d`.`statut_depense` AS `statut_depense`,`d`.`created_at_depense` AS `created_at_depense`,`td`.`code_type_depense` AS `code_type_depense`,`td`.`libelle_type_depense` AS `libelle_type_depense`,`u`.`nom_user` AS `nom_user`,`u`.`prenom_user` AS `prenom_user`,`a`.`libelle_annee` AS `libelle_annee` from (((`depenses` `d` left join `type_depenses` `td` on((`td`.`code_type_depense` = `d`.`type_depense_code`))) left join `annees` `a` on((`a`.`code_annee` = `d`.`annee_code`))) left join `users` `u` on((`u`.`code_user` = `d`.`user_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_dash_inscriptions_details`
--

/*!50001 DROP VIEW IF EXISTS `v_dash_inscriptions_details`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_dash_inscriptions_details` AS select `i`.`id_inscription` AS `id_inscription`,`i`.`code_inscription` AS `code_inscription`,`i`.`etudiant_code` AS `etudiant_code`,`i`.`classe_code` AS `classe_code`,`i`.`annee_code` AS `annee_code`,`i`.`statut_inscription` AS `statut_inscription`,`i`.`montant_scolarite_inscription` AS `montant_scolarite_inscription`,`i`.`affectation_etat` AS `affectation_etat`,`i`.`created_at_inscription` AS `created_at_inscription`,`e`.`matricule_etudiant` AS `matricule_etudiant`,`e`.`matricule_menet` AS `matricule_menet`,`e`.`matricule_mesrs` AS `matricule_mesrs`,`e`.`nom_etudiant` AS `nom_etudiant`,`e`.`prenom_etudiant` AS `prenom_etudiant`,concat(`e`.`nom_etudiant`,' ',`e`.`prenom_etudiant`) AS `nom_complet_etudiant`,`e`.`sexe_etudiant` AS `sexe_etudiant`,`e`.`telephone_etudiant` AS `telephone_etudiant`,`e`.`email_etudiant` AS `email_etudiant`,`e`.`photo_etudiant` AS `photo_etudiant`,`e`.`statut_etudiant` AS `statut_etudiant`,`cl`.`libelle_classe` AS `libelle_classe`,`f`.`code_filiere` AS `code_filiere`,`f`.`libelle_filiere` AS `libelle_filiere`,`n`.`code_niveau` AS `code_niveau`,`n`.`libelle_niveau` AS `libelle_niveau`,`a`.`libelle_annee` AS `libelle_annee` from (((((`inscriptions` `i` join `etudiants` `e` on((`e`.`code_etudiant` = `i`.`etudiant_code`))) join `classes` `cl` on((`cl`.`code_classe` = `i`.`classe_code`))) join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) join `niveaux` `n` on((`n`.`code_niveau` = `cl`.`niveau_code`))) join `annees` `a` on((`a`.`code_annee` = `i`.`annee_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_dash_paiements_details`
--

/*!50001 DROP VIEW IF EXISTS `v_dash_paiements_details`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_dash_paiements_details` AS select `p`.`id_paiement` AS `id_paiement`,`p`.`code_paiement` AS `code_paiement`,`p`.`montant_paiement` AS `montant_paiement`,`p`.`date_paiement` AS `date_paiement`,`p`.`statut_paiement` AS `statut_paiement`,`p`.`reference_paiement` AS `reference_paiement`,`p`.`type_paiement` AS `type_paiement`,`p`.`categorie_paiement` AS `categorie_paiement`,`p`.`tranche_code` AS `tranche_code`,`p`.`mode_paiement` AS `mode_paiement`,`p`.`annee_code` AS `annee_code`,`p`.`inscription_code` AS `inscription_code`,`ins`.`etudiant_code` AS `etudiant_code`,`ins`.`classe_code` AS `classe_code`,`e`.`matricule_etudiant` AS `matricule_etudiant`,`e`.`nom_etudiant` AS `nom_etudiant`,`e`.`prenom_etudiant` AS `prenom_etudiant`,concat(`e`.`nom_etudiant`,' ',coalesce(`e`.`prenom_etudiant`,'')) AS `nom_complet_etudiant`,`cl`.`libelle_classe` AS `libelle_classe`,`a`.`libelle_annee` AS `libelle_annee` from ((((`paiements` `p` join `inscriptions` `ins` on((`ins`.`code_inscription` = `p`.`inscription_code`))) join `etudiants` `e` on((`e`.`code_etudiant` = `ins`.`etudiant_code`))) left join `classes` `cl` on((`cl`.`code_classe` = `ins`.`classe_code`))) left join `annees` `a` on((`a`.`code_annee` = `p`.`annee_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_dash_pedagogie_affectations`
--

/*!50001 DROP VIEW IF EXISTS `v_dash_pedagogie_affectations`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_dash_pedagogie_affectations` AS select `em`.`id_enseignant_matiere` AS `id_enseignant_matiere`,`em`.`enseignant_code` AS `enseignant_code`,`em`.`matiere_code` AS `matiere_code`,`em`.`classe_code` AS `classe_code`,`em`.`coefficient` AS `coefficient`,`em`.`etablissement_code` AS `etablissement_code`,`em`.`annee_code` AS `annee_code`,`em`.`user_code` AS `user_code`,`em`.`created_at_enseignant_matiere` AS `created_at_enseignant_matiere`,`em`.`updated_at_enseignant_matiere` AS `updated_at_enseignant_matiere`,`em`.`statut_enseignant_matiere` AS `statut_enseignant_matiere`,`m`.`libelle_matiere` AS `libelle_matiere`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`niveau_code` AS `niveau_code`,`cl`.`filiere_code` AS `filiere_code`,`f`.`libelle_filiere` AS `libelle_filiere`,`n`.`libelle_niveau` AS `libelle_niveau`,`e`.`code_enseignant` AS `code_enseignant`,`e`.`grade_enseignant` AS `grade_enseignant`,`u`.`nom_user` AS `nom_prof`,`u`.`prenom_user` AS `prenom_prof`,`u`.`nom_user` AS `nom_enseignant`,`u`.`prenom_user` AS `prenom_enseignant`,`u`.`email_user` AS `email_enseignant`,`u`.`telephone_user` AS `telephone_enseignant`,concat(coalesce(`u`.`nom_user`,''),' ',coalesce(`u`.`prenom_user`,'')) AS `enseignant_nom` from ((((((`enseignant_matiere` `em` left join `matieres` `m` on((`m`.`code_matiere` = `em`.`matiere_code`))) left join `classes` `cl` on((`cl`.`code_classe` = `em`.`classe_code`))) left join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) left join `niveaux` `n` on((`n`.`code_niveau` = `cl`.`niveau_code`))) left join `enseignants` `e` on((`e`.`code_enseignant` = `em`.`enseignant_code`))) left join `users` `u` on((`u`.`code_user` = `em`.`enseignant_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_dash_synthese_annuelle`
--

/*!50001 DROP VIEW IF EXISTS `v_dash_synthese_annuelle`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_dash_synthese_annuelle` AS select `a`.`code_annee` AS `code_annee`,`a`.`libelle_annee` AS `libelle_annee`,`a`.`statut_annee` AS `statut_annee`,(select count(0) from `inscriptions` `ins` where ((`ins`.`annee_code` = `a`.`code_annee`) and ((`ins`.`statut_inscription` <> 'annule') or (`ins`.`statut_inscription` is null)))) AS `total_inscrits`,(select count(0) from `classes` `cl` where ((`cl`.`annee_code` = `a`.`code_annee`) and ((`cl`.`statut_classe` = 'actif') or (`cl`.`statut_classe` is null)))) AS `total_classes`,(select coalesce(sum(`ins2`.`montant_scolarite_inscription`),0) from `inscriptions` `ins2` where ((`ins2`.`annee_code` = `a`.`code_annee`) and ((`ins2`.`statut_inscription` <> 'annule') or (`ins2`.`statut_inscription` is null)))) AS `total_scolarite_attendue`,(select coalesce(sum(`p`.`montant_paiement`),0) from `paiements` `p` where ((`p`.`annee_code` = `a`.`code_annee`) and (`p`.`statut_paiement` = 'confirme') and ((`p`.`categorie_paiement` is null) or (`p`.`categorie_paiement` <> 'FRAIS_ANNEXES')) and ((`p`.`tranche_code` is null) or (`p`.`tranche_code` <> 'FRAIS_ANNEXES')) and ((`p`.`type_paiement` is null) or (not((`p`.`type_paiement` like '%Frais Annexe%')))))) AS `total_encaisse`,(select coalesce(sum(`d`.`montant_depense`),0) from `depenses` `d` where ((`d`.`annee_code` = `a`.`code_annee`) and ((`d`.`statut_depense` <> 'annule') or (`d`.`statut_depense` is null)))) AS `total_depenses` from `annees` `a` */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_emplois_temps_complets`
--

/*!50001 DROP VIEW IF EXISTS `v_emplois_temps_complets`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_emplois_temps_complets` AS select `edt`.`id_emploi` AS `id_emploi`,`edt`.`code_emploi` AS `code_emploi`,`edt`.`classe_code` AS `classe_code`,`edt`.`matiere_code` AS `matiere_code`,`edt`.`enseignant_code` AS `enseignant_code`,`edt`.`salle_code` AS `salle_code`,`edt`.`etablissement_code` AS `etablissement_code`,`edt`.`annee_code` AS `annee_code`,`edt`.`user_code` AS `user_code`,`edt`.`jour` AS `jour`,`edt`.`heure_debut` AS `heure_debut`,`edt`.`heure_fin` AS `heure_fin`,`edt`.`created_at_emploi` AS `created_at_emploi`,`edt`.`updated_at_emploi` AS `updated_at_emploi`,`edt`.`statut_emploi` AS `statut_emploi`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`niveau_code` AS `niveau_code`,`n`.`libelle_niveau` AS `libelle_niveau`,`m`.`libelle_matiere` AS `libelle_matiere`,`s`.`libelle_salle` AS `libelle_salle`,concat(coalesce(`u`.`nom_user`,''),' ',coalesce(`u`.`prenom_user`,'')) AS `nom_prof` from ((((((`emplois_temps` `edt` left join `classes` `cl` on((`cl`.`code_classe` = `edt`.`classe_code`))) left join `niveaux` `n` on((`n`.`code_niveau` = `cl`.`niveau_code`))) left join `matieres` `m` on((`m`.`code_matiere` = `edt`.`matiere_code`))) left join `salles` `s` on((`s`.`code_salle` = `edt`.`salle_code`))) left join `enseignants` `e` on((`e`.`code_enseignant` = `edt`.`enseignant_code`))) left join `users` `u` on((`u`.`code_user` = `edt`.`enseignant_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_inscriptions_completes`
--

/*!50001 DROP VIEW IF EXISTS `v_inscriptions_completes`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_inscriptions_completes` AS select `i`.`id_inscription` AS `id_inscription`,`i`.`code_inscription` AS `code_inscription`,`i`.`etudiant_code` AS `etudiant_code`,`i`.`classe_code` AS `classe_code`,`i`.`etablissement_code` AS `etablissement_code`,`i`.`annee_code` AS `annee_code`,`i`.`user_code` AS `user_code`,`i`.`created_at_inscription` AS `created_at_inscription`,`i`.`updated_at_inscription` AS `updated_at_inscription`,`i`.`montant_scolarite_inscription` AS `montant_scolarite_inscription`,`i`.`canal_inscription` AS `canal_inscription`,`i`.`affectation_etat` AS `affectation_etat`,`i`.`statut_inscription` AS `statut_inscription`,`i`.`photo_inscription` AS `photo_inscription`,`e`.`id_etudiant` AS `id_etudiant`,`e`.`code_etudiant` AS `code_etudiant`,`e`.`matricule_etudiant` AS `matricule_etudiant`,`e`.`matricule_menet` AS `matricule_menet`,`e`.`matricule_mesrs` AS `matricule_mesrs`,`e`.`nom_etudiant` AS `nom_etudiant`,`e`.`prenom_etudiant` AS `prenom_etudiant`,concat(coalesce(`e`.`nom_etudiant`,''),' ',coalesce(`e`.`prenom_etudiant`,'')) AS `etudiant_nom`,concat(coalesce(`e`.`nom_etudiant`,''),' ',coalesce(`e`.`prenom_etudiant`,'')) AS `nom_complet_etudiant`,`e`.`date_naissance_etudiant` AS `date_naissance_etudiant`,`e`.`lieu_naissance_etudiant` AS `lieu_naissance_etudiant`,`e`.`sexe_etudiant` AS `sexe_etudiant`,`e`.`nationalite_etudiant` AS `nationalite_etudiant`,`e`.`lieu_residence_etudiant` AS `lieu_residence_etudiant`,`e`.`telephone_etudiant` AS `telephone_etudiant`,`e`.`email_etudiant` AS `email_etudiant`,`e`.`photo_etudiant` AS `photo_etudiant`,`e`.`statut_etudiant` AS `statut_etudiant`,`e`.`etablissement_origine` AS `etablissement_origine`,`cl`.`code_classe` AS `code_classe`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`libelle_classe` AS `classe_nom`,`cl`.`filiere_code` AS `filiere_code`,`cl`.`niveau_code` AS `niveau_code`,`cl`.`cycle_code` AS `cycle_code`,`f`.`code_filiere` AS `code_filiere`,`f`.`libelle_filiere` AS `libelle_filiere`,`f`.`slug_filiere` AS `slug_filiere`,`f`.`type_filiere` AS `type_filiere`,`n`.`code_niveau` AS `code_niveau`,`n`.`libelle_niveau` AS `libelle_niveau`,`n`.`slug_niveau` AS `slug_niveau`,`a`.`code_annee` AS `code_annee`,`a`.`libelle_annee` AS `libelle_annee`,`a`.`libelle_annee` AS `annee_nom`,`a`.`coef_semestre1` AS `coef_semestre1`,`a`.`coef_semestre2` AS `coef_semestre2`,`p`.`nom_pere` AS `nom_pere`,`p`.`telephone_pere` AS `telephone_pere`,`p`.`profession_pere` AS `profession_pere`,`p`.`nom_mere` AS `nom_mere`,`p`.`telephone_mere` AS `telephone_mere`,`p`.`profession_mere` AS `profession_mere`,`p`.`nom_tuteur` AS `nom_tuteur`,`p`.`telephone_tuteur` AS `telephone_tuteur` from ((((((`inscriptions` `i` left join `etudiants` `e` on((`e`.`code_etudiant` = `i`.`etudiant_code`))) left join `classes` `cl` on((`cl`.`code_classe` = `i`.`classe_code`))) left join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) left join `niveaux` `n` on((`n`.`code_niveau` = `cl`.`niveau_code`))) left join `annees` `a` on((`a`.`code_annee` = `i`.`annee_code`))) left join `parents` `p` on((`p`.`etudiant_code` = `e`.`code_etudiant`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_maquettes_completes`
--

/*!50001 DROP VIEW IF EXISTS `v_maquettes_completes`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_maquettes_completes` AS select `mq`.`id_maquette` AS `id_maquette`,`mq`.`code_maquette` AS `code_maquette`,`mq`.`annee_code` AS `annee_code`,`mq`.`filiere_code` AS `filiere_code`,`mq`.`niveau_code` AS `niveau_code`,`mq`.`matiere_code` AS `matiere_code`,`mq`.`categorie_matiere` AS `categorie_matiere`,`mq`.`coefficient` AS `coefficient`,`mq`.`etablissement_code` AS `etablissement_code`,`mq`.`statut_maquette` AS `statut_maquette`,`mq`.`user_code` AS `user_code`,`mq`.`created_at_maquette` AS `created_at_maquette`,`mq`.`updated_at_maquette` AS `updated_at_maquette`,`m`.`libelle_matiere` AS `libelle_matiere`,`f`.`libelle_filiere` AS `libelle_filiere`,`f`.`type_filiere` AS `type_filiere`,`n`.`libelle_niveau` AS `libelle_niveau`,`a`.`libelle_annee` AS `libelle_annee` from ((((`maquettes_pedagogiques` `mq` left join `matieres` `m` on((`m`.`code_matiere` = `mq`.`matiere_code`))) left join `filieres` `f` on((`f`.`code_filiere` = `mq`.`filiere_code`))) left join `niveaux` `n` on((`n`.`code_niveau` = `mq`.`niveau_code`))) left join `annees` `a` on((`a`.`code_annee` = `mq`.`annee_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_notes_completes`
--

/*!50001 DROP VIEW IF EXISTS `v_notes_completes`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_notes_completes` AS select `n`.`id_note` AS `id_note`,`n`.`code_note` AS `code_note`,`n`.`valeur_note` AS `valeur_note`,`n`.`composition_code` AS `composition_code`,`n`.`composition_matiere_id` AS `composition_matiere_id`,`n`.`etudiant_code` AS `etudiant_code`,`n`.`observations` AS `observations`,`n`.`inscription_code` AS `inscription_code`,`n`.`user_code` AS `user_code`,`n`.`etablissement_code` AS `etablissement_code`,`n`.`created_at_note` AS `created_at_note`,`n`.`updated_at_note` AS `updated_at_note`,`n`.`statut_note` AS `statut_note`,`e`.`nom_etudiant` AS `etu_nom`,`e`.`prenom_etudiant` AS `etu_prenom`,concat(coalesce(`e`.`nom_etudiant`,''),' ',coalesce(`e`.`prenom_etudiant`,'')) AS `nom_etudiant`,`e`.`matricule_etudiant` AS `matricule_etudiant`,`e`.`telephone_etudiant` AS `telephone_etudiant`,`e`.`email_etudiant` AS `email_etudiant`,`m`.`libelle_matiere` AS `libelle_matiere`,`cm`.`matiere_code` AS `matiere_code`,`cl`.`code_classe` AS `code_classe`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`niveau_code` AS `niveau_code`,`cl`.`filiere_code` AS `filiere_code`,`f`.`libelle_filiere` AS `libelle_filiere`,`niv`.`libelle_niveau` AS `libelle_niveau`,`s`.`libelle_semestre` AS `libelle_semestre`,`comp`.`semestre_code` AS `semestre_code`,`a`.`libelle_annee` AS `libelle_annee`,`ins`.`annee_code` AS `annee_code`,`ins`.`classe_code` AS `inscription_classe_code`,`comp`.`libelle_composition` AS `libelle_composition`,`comp`.`type_composition` AS `type_composition`,`comp`.`type_composition` AS `type_evaluation_code`,`comp`.`coefficient` AS `coefficient` from ((((((((((`notes` `n` join `compositions` `comp` on((`comp`.`code_composition` = `n`.`composition_code`))) left join `composition_matieres` `cm` on((`cm`.`id_composition_matiere` = `n`.`composition_matiere_id`))) left join `etudiants` `e` on((`e`.`code_etudiant` = `n`.`etudiant_code`))) left join `inscriptions` `ins` on((`ins`.`code_inscription` = `n`.`inscription_code`))) left join `classes` `cl` on((`cl`.`code_classe` = `ins`.`classe_code`))) left join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) left join `niveaux` `niv` on((`niv`.`code_niveau` = `cl`.`niveau_code`))) left join `matieres` `m` on((`m`.`code_matiere` = `cm`.`matiere_code`))) left join `semestres` `s` on(((`s`.`code_semestre` = `comp`.`semestre_code`) and (`s`.`annee_code` = coalesce(nullif(`comp`.`annee_code`,''),`ins`.`annee_code`))))) left join `annees` `a` on((`a`.`code_annee` = coalesce(nullif(`comp`.`annee_code`,''),`ins`.`annee_code`)))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_paiements_complets`
--

/*!50001 DROP VIEW IF EXISTS `v_paiements_complets`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_paiements_complets` AS select `p`.`id_paiement` AS `id_paiement`,`p`.`code_paiement` AS `code_paiement`,`p`.`montant_paiement` AS `montant_paiement`,`p`.`date_paiement` AS `date_paiement`,`p`.`statut_paiement` AS `statut_paiement`,`p`.`canal_paiement` AS `canal_paiement`,`p`.`reference_paiement` AS `reference_paiement`,`p`.`observations` AS `observations`,`p`.`type_paiement` AS `type_paiement`,`p`.`categorie_paiement` AS `categorie_paiement`,`p`.`mode_paiement` AS `mode_paiement`,`p`.`user_code` AS `user_code`,`p`.`annee_code` AS `annee_code`,`p`.`etablissement_code` AS `etablissement_code`,`p`.`inscription_code` AS `inscription_code`,`p`.`tranche_code` AS `tranche_code`,`p`.`session_caisse_code` AS `session_caisse_code`,`ins`.`code_inscription` AS `code_inscription`,`ins`.`etudiant_code` AS `etudiant_code`,`ins`.`classe_code` AS `classe_code`,`ins`.`annee_code` AS `inscription_annee_code`,`ins`.`statut_inscription` AS `statut_inscription`,`ins`.`montant_scolarite_inscription` AS `montant_scolarite_inscription`,`ins`.`affectation_etat` AS `affectation_etat`,`ins`.`photo_inscription` AS `photo_inscription`,`e`.`code_etudiant` AS `code_etudiant`,`e`.`matricule_etudiant` AS `matricule_etudiant`,`e`.`nom_etudiant` AS `nom_etudiant`,`e`.`prenom_etudiant` AS `prenom_etudiant`,trim(concat(coalesce(`e`.`nom_etudiant`,''),' ',coalesce(`e`.`prenom_etudiant`,''))) AS `etudiant_nom`,`e`.`telephone_etudiant` AS `telephone_etudiant`,`e`.`email_etudiant` AS `email_etudiant`,`e`.`photo_etudiant` AS `photo_etudiant`,`cl`.`code_classe` AS `code_classe`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`niveau_code` AS `niveau_code`,`cl`.`filiere_code` AS `filiere_code`,`f`.`code_filiere` AS `code_filiere`,`f`.`libelle_filiere` AS `libelle_filiere`,`f`.`type_filiere` AS `type_filiere`,`n`.`code_niveau` AS `code_niveau`,`n`.`libelle_niveau` AS `libelle_niveau`,`a`.`libelle_annee` AS `libelle_annee`,`u`.`nom_user` AS `nom_user`,`u`.`prenom_user` AS `prenom_user`,`u`.`nom_user` AS `nom_caissier`,`u`.`prenom_user` AS `prenom_caissier`,`u`.`code_user` AS `caissier_code`,`t`.`libelle_tranche` AS `libelle_tranche`,`t`.`montant_tranche` AS `montant_tranche`,`t`.`date_limite` AS `date_limite_tranche` from ((((((((`paiements` `p` left join `inscriptions` `ins` on((`ins`.`code_inscription` = `p`.`inscription_code`))) left join `etudiants` `e` on((`e`.`code_etudiant` = `ins`.`etudiant_code`))) left join `classes` `cl` on((`cl`.`code_classe` = `ins`.`classe_code`))) left join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) left join `niveaux` `n` on((`n`.`code_niveau` = `cl`.`niveau_code`))) left join `annees` `a` on((`a`.`code_annee` = coalesce(nullif(`p`.`annee_code`,''),`ins`.`annee_code`)))) left join `users` `u` on((`u`.`code_user` = `p`.`user_code`))) left join `tranches_scolarite` `t` on((`t`.`code_tranche` = `p`.`tranche_code`))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
--
-- Final view structure for view `v_sessions_evaluations`
--

/*!50001 DROP VIEW IF EXISTS `v_sessions_evaluations`*/
;
/*!50001 SET @saved_cs_client          = @@character_set_client */
;
/*!50001 SET @saved_cs_results         = @@character_set_results */
;
/*!50001 SET @saved_col_connection     = @@collation_connection */
;
/*!50001 SET character_set_client      = utf8mb4 */
;
/*!50001 SET character_set_results     = utf8mb4 */
;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */
;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`admin`@`localhost` SQL SECURITY INVOKER */
/*!50001 VIEW `v_sessions_evaluations` AS select `comp`.`id_composition` AS `id_composition`,`comp`.`code_composition` AS `code_composition`,`comp`.`libelle_composition` AS `libelle_composition`,`comp`.`type_composition` AS `type_composition`,`comp`.`semestre_code` AS `semestre_code`,coalesce(`comp`.`coefficient`,1.00) AS `coefficient`,`comp`.`date_composition` AS `date_composition`,`comp`.`date_fin_composition` AS `date_fin_composition`,`comp`.`statut_composition` AS `statut_composition`,`comp`.`created_at_composition` AS `created_at_composition`,`comp`.`user_code` AS `composition_user_code`,coalesce(nullif(`comp`.`annee_code`,''),`cl`.`annee_code`) AS `annee_code`,`comp`.`etablissement_code` AS `etablissement_code`,`cm`.`id_composition_matiere` AS `id_composition_matiere`,`cm`.`matiere_code` AS `matiere_code`,`m`.`libelle_matiere` AS `libelle_matiere`,`cl`.`code_classe` AS `code_classe`,`cl`.`libelle_classe` AS `libelle_classe`,`cl`.`niveau_code` AS `niveau_code`,`cl`.`filiere_code` AS `filiere_code`,`niv`.`libelle_niveau` AS `libelle_niveau`,`f`.`libelle_filiere` AS `libelle_filiere`,`s`.`libelle_semestre` AS `libelle_semestre`,`s`.`statut_semestre` AS `statut_semestre`,`a`.`libelle_annee` AS `libelle_annee`,coalesce(`ns`.`date_enregistrement_note`,`comp`.`date_composition`,`comp`.`created_at_composition`) AS `date_enregistrement`,`ns`.`date_enregistrement_note` AS `date_enregistrement_note`,coalesce(`eff`.`total_etudiants`,0) AS `total_etudiants`,coalesce(`ns`.`nb_notes`,0) AS `nb_notes`,`ns`.`moyenne_classe` AS `moyenne_classe` from ((((((((((`composition_matieres` `cm` join `composition_niveau_filiere` `cnf` on((`cnf`.`code_composition_niveau_filiere` = `cm`.`composition_niveau_filiere_code`))) join `compositions` `comp` on((`comp`.`code_composition` = `cm`.`composition_code`))) join `classes` `cl` on(((`cl`.`niveau_code` = `cnf`.`niveau_code`) and (`cl`.`filiere_code` = `cnf`.`filiere_code`) and ((`comp`.`annee_code` is null) or (`comp`.`annee_code` = '') or (`cl`.`annee_code` = `comp`.`annee_code`))))) left join `filieres` `f` on((`f`.`code_filiere` = `cl`.`filiere_code`))) left join `niveaux` `niv` on((`niv`.`code_niveau` = `cl`.`niveau_code`))) left join `matieres` `m` on((`m`.`code_matiere` = `cm`.`matiere_code`))) left join `semestres` `s` on(((`s`.`code_semestre` = `comp`.`semestre_code`) and (`s`.`annee_code` = coalesce(nullif(`comp`.`annee_code`,''),`cl`.`annee_code`))))) left join `annees` `a` on((`a`.`code_annee` = coalesce(nullif(`comp`.`annee_code`,''),`cl`.`annee_code`)))) left join (select `i`.`classe_code` AS `classe_code`,`i`.`annee_code` AS `annee_code`,count(distinct `i`.`code_inscription`) AS `total_etudiants` from `inscriptions` `i` where ((`i`.`statut_inscription` <> 'annule') or (`i`.`statut_inscription` is null)) group by `i`.`classe_code`,`i`.`annee_code`) `eff` on(((`eff`.`classe_code` = `cl`.`code_classe`) and (`eff`.`annee_code` = coalesce(nullif(`comp`.`annee_code`,''),`cl`.`annee_code`))))) left join (select `n`.`composition_code` AS `composition_code`,`n`.`composition_matiere_id` AS `composition_matiere_id`,`i`.`classe_code` AS `classe_code`,count(distinct `n`.`id_note`) AS `nb_notes`,round(avg(`n`.`valeur_note`),2) AS `moyenne_classe`,max(`n`.`created_at_note`) AS `date_enregistrement_note` from (`notes` `n` join `inscriptions` `i` on((`i`.`code_inscription` = `n`.`inscription_code`))) where ((`n`.`statut_note` = 'actif') and ((`i`.`statut_inscription` <> 'annule') or (`i`.`statut_inscription` is null))) group by `n`.`composition_code`,`n`.`composition_matiere_id`,`i`.`classe_code`) `ns` on(((`ns`.`composition_code` = `comp`.`code_composition`) and (`ns`.`composition_matiere_id` = `cm`.`id_composition_matiere`) and (`ns`.`classe_code` = `cl`.`code_classe`)))) */
;
/*!50001 SET character_set_client      = @saved_cs_client */
;
/*!50001 SET character_set_results     = @saved_cs_results */
;
/*!50001 SET collation_connection      = @saved_col_connection */
;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */
;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */
;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */
;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */
;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */
;
-- Dump completed on 2026-11-19  1:33:06
--
-- Table structure and data for table `baremes_bulletin`
--
DROP TABLE IF EXISTS `baremes_bulletin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */
;
/*!50503 SET character_set_client = utf8mb4 */
;
CREATE TABLE `baremes_bulletin` (
  `id_bareme` int NOT NULL AUTO_INCREMENT,
  `code_bareme` varchar(50) NOT NULL,
  `note_min` decimal(4, 2) NOT NULL,
  `note_max` decimal(4, 2) NOT NULL,
  `appreciation_matiere` varchar(100) NOT NULL,
  `mention_bulletin` varchar(100) NOT NULL,
  `appreciation_conseil` varchar(150) NOT NULL,
  `appreciation_conseil_s2` varchar(150) NOT NULL DEFAULT '',
  `code_distinction_sanction` enum(
    'AUCUN',
    'FELICITATIONS',
    'ENCOURAGEMENTS',
    'HONNEUR',
    'AVERTISSEMENT',
    'BLAME'
  ) NOT NULL DEFAULT 'AUCUN',
  `ordre_affichage` int NOT NULL DEFAULT '1',
  `etablissement_code` varchar(50) DEFAULT NULL,
  `statut_bareme` enum('actif', 'inactif') NOT NULL DEFAULT 'actif',
  `updated_at_bareme` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_bareme`),
  UNIQUE KEY `uk_code_bareme` (`code_bareme`),
  KEY `idx_bareme_statut` (`statut_bareme`),
  KEY `idx_bareme_notes` (`note_min`, `note_max`)
) ENGINE = InnoDB AUTO_INCREMENT = 7 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */
;
INSERT INTO `baremes_bulletin`
VALUES (
    1,
    'BAR-TB',
    16.00,
    20.00,
    'Très Bien',
    'Très Bien',
    'Félicitations du Conseil',
    'Admis(e) avec Félicitations du Conseil',
    'FELICITATIONS',
    1,
    '5454544456',
    'actif',
    '2026-11-21 15:08:01'
  ),
  (
    2,
    'BAR-B',
    14.00,
    15.99,
    'Bien',
    'Bien',
    'Tableau d\'Honneur',
    'Admis(e) avec Tableau d\'Honneur',
    'ENCOURAGEMENTS',
    2,
    '5454544456',
    'actif',
    '2026-11-21 15:08:01'
  ),
  (
    3,
    'BAR-AB',
    12.00,
    13.99,
    'Assez Bien',
    'Assez Bien',
    'Encouragements',
    'Admis(e) avec Encouragements',
    'HONNEUR',
    3,
    '5454544456',
    'actif',
    '2026-11-21 15:08:01'
  ),
  (
    4,
    'BAR-P',
    10.00,
    11.99,
    'Passable',
    'Passable',
    'Travail passable, peut mieux faire',
    'Admis(e) en classe supérieure',
    'AUCUN',
    4,
    '5454544456',
    'actif',
    '2026-11-21 15:08:01'
  ),
  (
    5,
    'BAR-I',
    8.00,
    9.99,
    'Insuffisant',
    'Insuffisant',
    'Travail insuffisant, redoubler d\'efforts',
    'Autorisé(e) à redoubler',
    'AVERTISSEMENT',
    5,
    '5454544456',
    'actif',
    '2026-11-21 15:08:01'
  ),
  (
    6,
    'BAR-TI',
    0.00,
    7.99,
    'Très Insuffisant',
    'Très Insuffisant',
    'Résultats très faibles, blâme travail',
    'Ajourné(e) / Résultats très faibles',
    'BLAME',
    6,
    '5454544456',
    'actif',
    '2026-11-21 15:08:01'
  );


--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id_reset` int NOT NULL AUTO_INCREMENT,
  `user_code` varchar(50) NOT NULL,
  `email_reset` varchar(150) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reset`),
  UNIQUE KEY `uq_password_resets_token_hash` (`token_hash`),
  KEY `idx_password_resets_user_code` (`user_code`),
  KEY `idx_password_resets_email_reset` (`email_reset`),
  KEY `idx_password_resets_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limits` (
  `id_rate_limit` int NOT NULL AUTO_INCREMENT,
  `rate_key` varchar(191) NOT NULL,
  `action_name` varchar(64) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int NOT NULL DEFAULT '1',
  `window_start` datetime NOT NULL,
  `locked_until` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rate_limit`),
  UNIQUE KEY `uq_rate_limits_key` (`rate_key`),
  KEY `idx_rate_limits_action` (`action_name`),
  KEY `idx_rate_limits_ip` (`ip_address`),
  KEY `idx_rate_limits_locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
