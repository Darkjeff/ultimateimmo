-- ============================================================================
-- Copyright (C) 2013      	Olivier Geffroy		<jeff@jeffinfo.com>
-- Copyright (C) 2016		Alexandre Spangaro	<aspangaro@zendsi.com>
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
--
-- ============================================================================

CREATE TABLE IF NOT EXISTS llx_immo_payment (
   rowid  			integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   fk_contract  	integer NOT NULL,
   fk_property  	integer NOT NULL,
   fk_renter  		integer NOT NULL,
   amount  			double(24,8) NOT NULL DEFAULT 0,
   fk_bank			integer NOT NULL,
   fk_typepayment	integer NOT NULL,
   num_payment		varchar(50),
   comment  		text,
   date_payment		datetime DEFAULT NULL,
   fk_owner			integer NOT NULL DEFAULT 1,
   fk_receipt		integer NOT NULL
)ENGINE=InnoDB;

