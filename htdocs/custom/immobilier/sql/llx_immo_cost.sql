-- ===================================================================
-- Copyright (C) 2016-2017		Olivier Geffroy      <jeff@jeffinfo.com>
-- Copyright (C) 2016-2017	Alexandre Spangaro   <aspangaro@zendsi.com>
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
--

CREATE TABLE IF NOT EXISTS llx_immo_cost
(
	rowid			integer AUTO_INCREMENT PRIMARY KEY,
	tms             timestamp,
	datec			datetime,
	fk_property		integer NOT NULL,
	cost_type		integer DEFAULT -1,
	label			varchar(255),
	supplier		varchar(64) NOT NULL,
	new_supplier	varchar(255) NOT NULL,
	amount_ht		double(24,8) NOT NULL default 0,
	amount_vat		double(24,8) NOT NULL default 0,
	amount			double(24,8) NOT NULL default 0,
	date_start		datetime,
	date_end		datetime,
	note_public		text,
	fk_owner		integer NOT NULL DEFAULT 0,
	dispatch		smallint(8) NOT NULL DEFAULT 0,
	fk_user_author	integer,
	fk_user_modif	integer,
	fk_soc			integer
) ENGINE=InnoDB;

