-- ===================================================================
-- Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================

CREATE TABLE IF NOT EXISTS  llx_immobilier_immocontrat  (
   rowid   					integer AUTO_INCREMENT PRIMARY KEY,
   fk_property 				integer NOT NULL,
   fk_renter				integer NOT NULL,
   date_start				datetime NOT NULL,
   date_end					datetime NOT NULL,
   preavis					integer NOT NULL DEFAULT 0,
   date_prochain_loyer  	timestamp NOT NULL,
   date_derniere_regul  	timestamp NULL DEFAULT NULL,
   montant_tot  			double(24,8) NOT NULL DEFAULT 0,
   loyer					double(24,8) NOT NULL DEFAULT 0,
   charges					double(24,8) NOT NULL DEFAULT 0,
   tva						double(24,8) NOT NULL DEFAULT 0,
   depot					double(24,8) NOT NULL DEFAULT 0,
   encours					double(24,8) NOT NULL DEFAULT 0,
   periode					varchar(50) NOT NULL DEFAULT '1 month',
   date_der_rev				datetime NOT NULL DEFAULT '2009-01-01 00:00:00',
   statut					smallint NOT NULL DEFAULT 0,
   fk_user_author			integer,
   fk_user_modif			integer,
   datec					datetime NOT NULL,
   tms						timestamp NOT NULL,
   commentaire				text NOT NULL
)ENGINE=InnoDB;
