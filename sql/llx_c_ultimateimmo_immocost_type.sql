-- Copyright (C) 2013		Olivier Geffroy  <jeff@jeffinfo.com>
-- Copyright (C) 2018-2019 	Philippe GRAND 	 <philippe.grand@atoo-net.com>
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

CREATE TABLE IF NOT EXISTS  llx_c_ultimateimmo_immocost_type  (
   rowid integer NOT NULL  AUTO_INCREMENT PRIMARY KEY,
   ref varchar(50) NOT NULL,
   label varchar(200) NOT NULL DEFAULT '',
   famille	varchar(50) NOT NULL,
   entity integer DEFAULT 1 NOT NULL,
   date_creation datetime NOT NULL,
   tms timestamp NOT NULL,
   fk_user_creat integer NOT NULL,
   fk_user_modif integer,
   import_key varchar(14),
   status integer NOT NULL
)ENGINE=InnoDB;

