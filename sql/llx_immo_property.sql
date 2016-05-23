-- ===================================================================
-- Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
-- Copyright (C) 2015-2016 Alexandre Spangaro   <aspangaro.dolibarr@gmail.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================

CREATE TABLE IF NOT EXISTS llx_immo_property
(
  rowid				integer AUTO_INCREMENT PRIMARY KEY,
  entity			integer DEFAULT 1 NOT NULL,	-- multi company id
  fk_type_property	integer DEFAULT 1 NOT NULL,
  fk_property       integer,  -- Hierarchic parent
  fk_owner	        integer,  -- Hierarchic parent
  name				varchar(128) NOT NULL,
  address			text,
  building			varchar(32),
  staircase 		varchar(8),
  floor				varchar(8),
  numberofdoor		varchar(8),
  area				varchar(8),
  numberofpieces	varchar(8),
  zip				varchar(32),
  town				varchar(64),
  fk_pays 			integer,
  statut			smallint NOT NULL DEFAULT 0,
  note_private		text DEFAULT NULL,
  note_public		text DEFAULT NULL,
  datec				datetime,  -- date de creation
  tms				timestamp, -- date de modification
  fk_user_author	integer,
  fk_user_modif		integer DEFAULT NULL
)ENGINE=InnoDB;
