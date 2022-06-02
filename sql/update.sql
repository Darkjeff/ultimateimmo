-- ============================================================================
-- Copyright (C) 2014-2022   Philippe Grand		<philippe.grand@atoo-net.com>
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


ALTER TABLE llx_ultimateimmo_immoowner MODIFY COLUMN fk_pays country_id integer;
ALTER TABLE llx_c_ultimateimmo_juridique MODIFY COLUMN code varchar(50) NOT NULL;
ALTER TABLE llx_c_ultimateimmo_immorent_type MODIFY COLUMN code varchar(50) NOT NULL;
ALTER TABLE llx_ultimateimmo_immorenter ADD COLUMN town varchar(255) AFTER country_id;
ALTER TABLE llx_c_ultimateimmo_immoproperty_type MODIFY COLUMN code varchar(50) NOT NULL;

ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_start date;
ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_end date;
ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_next_rent date;
ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_last_regul date;
ALTER TABLE llx_ultimateimmo_immocost_type CHANGE COLUMN fk_user_creat fk_user_create integer NOT NULL;
ALTER TABLE llx_ultimateimmo_immocost_type ADD CONSTRAINT llx_ultimateimmo_immocost_type_fk_user_creat FOREIGN KEY (fk_user_create) REFERENCES llx_user(rowid);








