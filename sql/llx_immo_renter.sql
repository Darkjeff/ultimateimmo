-- ============================================================================
-- Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
-- Copyright (C) 2015-2016	Alexandre Spangaro  <aspangaro.dolibarr@gmail.com>
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
--
-- Structure de la table llx_immo_renter
--

CREATE TABLE IF NOT EXISTS llx_immo_renter (
	rowid			integer NOT NULL auto_increment PRIMARY KEY,
	entity			integer NOT NULL DEFAULT 1,
	nom				varchar(50) NOT NULL,
	prenom			varchar(50) NOT NULL,
	civilite		varchar(6) NOT NULL,
	fk_user_author	integer default NULL,
	fk_user_mod		integer NOT NULL,
	datec			datetime NOT NULL,
	tms				timestamp NOT NULL,
	fk_soc			integer NOT NULL,
	fk_socpeople	integer default NULL,
	fk_owner	    integer,
	fonction		varchar(60) default NULL,
	tel1			varchar(30) default NULL,
	tel2			varchar(30) default NULL,
	mail			varchar(100) default NULL,
	date_birth		datetime default NULL,
	place_birth		varchar(100) default NULL,
	statut			smallint NOT NULL DEFAULT 0,
	note			text,
	import_key		varchar(14)
) ENGINE=InnoDB;

