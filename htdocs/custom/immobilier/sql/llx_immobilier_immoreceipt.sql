-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u2
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Lun 25 Juin 2018 à 14:59
-- Version du serveur :  5.5.59-0+deb8u1
-- Version de PHP :  5.6.33-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `dbv7immo`
--

-- --------------------------------------------------------

--
-- Structure de la table `llx_immobilier_immoreceipt`
--

CREATE TABLE IF NOT EXISTS `llx_immobilier_immoreceipt` (
`rowid` int(11) NOT NULL,
  `ref` varchar(128) NOT NULL,
  `entity` int(11) NOT NULL DEFAULT '1',
  `label` varchar(255) DEFAULT NULL,
  `fk_rent` int(11) DEFAULT NULL,
  `fk_property` int(11) DEFAULT NULL,
  `fk_renter` int(11) DEFAULT NULL,
  `fk_owner` int(11) DEFAULT NULL,
  `fk_soc` int(11) DEFAULT NULL,
  `note_public` text,
  `note_private` text,
  `rentamount` double(24,8) DEFAULT NULL,
  `chargesamount` double(24,8) DEFAULT NULL,
  `total_amount` double(24,8) DEFAULT NULL,
  `balance` double(24,8) DEFAULT NULL,
  `paiepartiel` double(24,8) DEFAULT NULL,
  `echeance` datetime DEFAULT NULL,
  `vat_amount` double(24,8) DEFAULT NULL,
  `vat_tx` int(11) DEFAULT NULL,
  `paye` int(11) DEFAULT NULL,
  `date_rent` datetime DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `tms` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fk_user_creat` int(11) NOT NULL,
  `fk_user_modif` int(11) DEFAULT NULL,
  `import_key` varchar(14) DEFAULT NULL,
  `model_pdf` varchar(128) DEFAULT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `llx_immobilier_immoreceipt`
--
ALTER TABLE `llx_immobilier_immoreceipt`
 ADD PRIMARY KEY (`rowid`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `llx_immobilier_immoreceipt`
--
ALTER TABLE `llx_immobilier_immoreceipt`
MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
