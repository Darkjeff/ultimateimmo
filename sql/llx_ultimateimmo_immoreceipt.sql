-- ========================================================================
-- Copyright (C) 2018-2020  Philippe GRAND 	<philippe.grand@atoo-net.com>
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

CREATE TABLE IF NOT EXISTS llx_ultimateimmo_immoreceipt ( 
   rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
   ref varchar(128) NOT NULL,
   type smallint(6) NOT NULL DEFAULT 0,
   entity INT(11) NOT NULL DEFAULT 1,
   label varchar(255) DEFAULT NULL,
   fk_rent INT(11) DEFAULT NULL,
   fk_property INT(11) DEFAULT NULL,
   fk_renter INT(11) DEFAULT NULL,
   fk_owner INT(11) DEFAULT NULL,
   fk_soc INT(11) DEFAULT NULL,
   note_public text,
   note_private text,
   date_echeance datetime DEFAULT NULL,
   rentamount double(24,8) DEFAULT NULL,
   chargesamount double(24,8) DEFAULT NULL,
   total_amount double(24,8) DEFAULT NULL,
   balance double(24,8) DEFAULT NULL,
   partial_payment double(24,8) DEFAULT NULL,
   --fk_mode_reglement int(11) DEFAULT NULL,
   fk_payment INT(11) DEFAULT NULL,
   paye INT(2) NULL DEFAULT 0,
   vat_amount double(24,8) DEFAULT NULL,
   vat_tx INT(11) DEFAULT NULL,
   date_rent datetime DEFAULT NULL,
   date_start datetime DEFAULT NULL,
   date_end datetime DEFAULT NULL,
   date_creation datetime DEFAULT NULL,
   date_validation datetime DEFAULT NULL,
   tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   fk_statut INT(6) NULL DEFAULT 0,
   fk_user_creat INT(11) NOT NULL,
   fk_user_modif INT(11) DEFAULT NULL,
   fk_user_valid INT(11) DEFAULT NULL,
   import_key varchar(14) DEFAULT NULL,
   model_pdf varchar(128) DEFAULT NULL,
   last_main_doc varchar(255) DEFAULT NULL,
   status INT(11) NULL DEFAULT 0
) ENGINE=InnoDB;


