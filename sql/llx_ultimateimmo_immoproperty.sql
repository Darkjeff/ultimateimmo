-- ========================================================================
-- Copyright (C) 2018-2019  Philippe GRAND 	<philippe.grand@atoo-net.com>
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


CREATE TABLE llx_ultimateimmo_immoproperty(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	label varchar(255) NOT NULL,
	juridique_id integer,	
	entity integer DEFAULT 1 NOT NULL, 
	fk_owner integer NOT NULL,
	fk_soc integer,	
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp NOT NULL default CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	status integer NOT NULL, 
	address varchar(255), 
	building varchar(32), 
	staircase varchar(8), 
	property_type_id integer NOT NULL,
	fk_property integer,	
	numfloor varchar(8), 
	numflat varchar(8), 
	numdoor varchar(8), 
	area varchar(8),
	numroom integer,	
	zip varchar(32), 
	town varchar(64), 
	country_id integer, 
	datep date,
	datebuilt varchar(32), 	
	target integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
