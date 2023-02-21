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


CREATE TABLE llx_ultimateimmo_immorent(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	entity integer DEFAULT 1 NOT NULL,
	fk_property integer, 
	fk_renter integer, 
	fk_account integer,
	fk_soc integer,
	country_id integer,	
	vat varchar(4), 
	fk_owner integer,
	location_type_id integer,	
	note_public text, 
	note_private text,
	rentamount integer, 
	chargesamount integer, 
	totalamount integer, 
	deposit integer,
	encours	integer,		
	periode varchar(128),
	preavis	integer,
	date_start date,
	date_end date,
	date_next_rent date,
	date_last_regul date,
	date_last_regul_charge date,
	date_creation datetime NULL,
	tms timestamp NOT NULL default CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14),
	model_pdf varchar(128) DEFAULT NULL,	
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
