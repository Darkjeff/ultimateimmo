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

CREATE TABLE IF NOT EXISTS llx_immo_receipt (
   rowid   integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   fk_contract   integer NOT NULL,
   fk_property   integer NOT NULL,
   name  varchar(50)   NOT NULL DEFAULT '',
   fk_renter   integer NOT NULL,
   amount_total   double(28,4) NOT NULL DEFAULT 0,
   rent   double(28,4) NOT NULL DEFAULT 0,
   balance   double(28,4) NOT NULL DEFAULT 0,
   paiepartiel   double(28,4) NOT NULL DEFAULT 0,
   charges   double(28,4) NOT NULL DEFAULT 0,
   vat   double(28,4) NOT NULL DEFAULT 0,
   echeance  datetime NOT NULL,
   commentaire  text  ,
   statut  varchar(20)   NOT NULL DEFAULT '',
   date_rent  datetime DEFAULT NULL,
   date_start  datetime NOT NULL,
   date_end  datetime NOT NULL,
   fk_owner integer NOT NULL DEFAULT 1,
   paye integer NOT NULL DEFAULT 0
)ENGINE=InnoDB;

