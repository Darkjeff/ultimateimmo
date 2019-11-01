-- ========================================================================
-- Copyright (C) 2018-2019  Philippe GRAND 	<philippe.grand@atoo-net.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.
-- ========================================================================
--

CREATE TABLE IF NOT EXISTS `llx_ultimateimmo_immoreceipt` (
   rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `ref` varchar(128) NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `entity` int(11) NOT NULL DEFAULT '1',
  `label` varchar(255) DEFAULT NULL,
  `fk_rent` int(11) DEFAULT NULL,
  `fk_property` int(11) DEFAULT NULL,
  `fk_renter` int(11) DEFAULT NULL,
  `fk_owner` int(11) DEFAULT NULL,
  `fk_soc` int(11) DEFAULT NULL,
  `note_public` text,
  `note_private` text,
  `date_echeance` datetime DEFAULT NULL,
  `rentamount` double(24,8) DEFAULT NULL,
  `chargesamount` double(24,8) DEFAULT NULL,
  `total_amount` double(24,8) DEFAULT NULL,
  `balance` double(24,8) DEFAULT NULL,
  `partial_payment` double(24,8) DEFAULT NULL,
  --`fk_mode_reglement` int(11) DEFAULT NULL,
  --`fk_payment` int(11) DEFAULT NULL,
  `paye` int(11) NULL DEFAULT '0',
  `vat_amount` double(24,8) DEFAULT NULL,
  `vat_tx` int(11) DEFAULT NULL,
  `date_rent` datetime DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `date_valid` datetime DEFAULT NULL,
  `tms` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fk_statut` int(6) NULL DEFAULT '0',
  `fk_user_creat` int(11) NOT NULL,
  `fk_user_modif` int(11) DEFAULT NULL,
  `fk_user_valid` int(11) DEFAULT NULL,
  `import_key` varchar(14) DEFAULT NULL,
  `model_pdf` varchar(128) DEFAULT NULL,
  `last_main_doc` varchar(255) DEFAULT NULL,
  `status` int(11) NULL DEFAULT '0'
) ENGINE=InnoDB;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `llx_ultimateimmo_immoreceipt`
--
ALTER TABLE `llx_ultimateimmo_immoreceipt`
 ADD PRIMARY KEY (`rowid`);


