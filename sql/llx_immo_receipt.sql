-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Jeu 15 Décembre 2016 à 08:30
-- Version du serveur: 5.5.43
-- Version de PHP: 5.4.45-0+deb7u5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `dbjeffimmo`
--

-- --------------------------------------------------------

--
-- Structure de la table `llx_immo_receipt`
--

CREATE TABLE IF NOT EXISTS `llx_immo_receipt` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `fk_contract` int(11) NOT NULL,
  `fk_property` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `fk_renter` int(11) NOT NULL,
  `amount_total` double(28,4) NOT NULL DEFAULT '0.0000',
  `rent` double(28,4) NOT NULL DEFAULT '0.0000',
  `balance` double(28,4) NOT NULL DEFAULT '0.0000',
  `paiepartiel` double(28,4) NOT NULL DEFAULT '0.0000',
  `charges` double(28,4) NOT NULL DEFAULT '0.0000',
  `vat` double(28,4) NOT NULL DEFAULT '0.0000',
  `echeance` datetime NOT NULL,
  `commentaire` text,
  `statut` varchar(20) NOT NULL DEFAULT '',
  `date_rent` datetime DEFAULT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `fk_owner` int(11) NOT NULL DEFAULT '1',
  `paye` int(11) NOT NULL DEFAULT '0',
  `model_pdf` int(11) NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1364 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
