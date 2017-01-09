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

CREATE TABLE IF NOT EXISTS  llx_immo_local  (
   rowid  integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   immeuble_id  integer NOT NULL DEFAULT 0,
   nom  varchar(50)   NOT NULL DEFAULT '',
   adresse  varchar(300)   NOT NULL DEFAULT '',
   commentaire  text   NOT NULL,
   statut  varchar(10)   NOT NULL DEFAULT 'Actif',
   superficie   double(28,4) NOT NULL DEFAULT 0,
   proprietaire_id integer NOT NULL DEFAULT 1
)ENGINE=InnoDB;
