-- Immobilier
-- Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
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
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

CREATE TABLE IF NOT EXISTS llx_immo_charge  (
   rowid   					integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   fk_property 				integer NOT NULL,
   type  					integer NOT NULL DEFAULT 0,
   libelle  				varchar(100) NOT NULL DEFAULT '',
   fournisseur  			varchar(200) NOT NULL DEFAULT '',
   montant_ht				double(24,8) NOT NULL DEFAULT 0,
   montant_tva				double(24,8) NOT NULL DEFAULT 0,
   montant_ttc				double(24,8) NOT NULL DEFAULT 0,
   date_acq					datetime DEFAULT NULL,
   periode_du				datetime DEFAULT NULL,
   periode_au				datetime DEFAULT NULL,
   commentaire  			text,
   proprietaire_id 			integer NOT NULL DEFAULT 1
)ENGINE=InnoDB;
