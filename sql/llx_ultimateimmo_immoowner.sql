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


CREATE TABLE llx_ultimateimmo_immoowner(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	entity integer DEFAULT 1 NOT NULL, 
	fk_soc integer,
	societe varchar(128),	
	fk_owner_type integer,
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp NOT NULL default CURRENT_TIMESTAMP, 	
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	status integer NOT NULL, 
	civility_id integer(3), 
	firstname varchar(255) NOT NULL, 
	lastname varchar(255) NOT NULL,
	address varchar(255),
	zip varchar(32), 
	town varchar(64), 
	country_id integer,	
	email varchar(255) NOT NULL, 
	birth datetime,
	phone varchar(30), 
	phone_mobile varchar(30)
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
