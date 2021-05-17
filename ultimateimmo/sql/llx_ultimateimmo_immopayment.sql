-- ========================================================================
-- Copyright (C) 2018-2021  Philippe GRAND 	<philippe.grand@atoo-net.com>
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


CREATE TABLE llx_ultimateimmo_immopayment(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL,			-- payment reference number
	entity integer DEFAULT 1 NOT NULL,	-- Multi company id
	amount double(24,8) DEFAULT NULL, 	-- amount paid in Dolibarr currency
	fk_rent integer, 
	fk_property integer, 
	fk_renter integer, 
	fk_account integer, 
	fk_mode_reglement integer, 
	fk_owner integer, 
	fk_soc integer,
	fk_receipt integer,
	fk_payment integer NOT NULL,		-- type of payment in llx_c_paiement
	num_payment varchar(50) DEFAULT NULL, 
	check_transmitter varchar(50) DEFAULT NULL, 
	chequebank varchar(50) DEFAULT NULL, 
	note_public text,  
	date_payment datetime NOT NULL, 	-- payment date
	date_creation datetime NOT NULL, 	-- date de creation
	tms timestamp NOT NULL, 
	fk_user_creat integer NOT NULL, 	-- utilisateur qui a cree l'info
	fk_user_modif integer, 				-- utilisateur qui a modifie l'info
	import_key varchar(14), 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;