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


CREATE TABLE llx_ultimateimmo_immorenter(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	ref varchar(128) NOT NULL, 
	entity integer DEFAULT 1 NOT NULL,
	fk_rent integer,
	fk_owner integer,
	fk_soc integer,
	societe varchar(128),	
	note_public text, 
	note_private text, 
	civility_id integer NOT NULL,	
	firstname varchar(255),
	lastname varchar(255),
	email varchar(255),
	photo varchar(255), 
	birth datetime,
	country_id integer,
    town varchar(255),
    phone varchar(30),
	phone_mobile varchar(30),  	
	date_creation datetime NOT NULL, 
	tms timestamp NOT NULL default CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	model_pdf varchar(128),
	status integer NOT NULL  		
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
