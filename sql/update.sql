-- ============================================================================
-- Copyright (C) 2021   	 Alexandre Spangaro		<info@open-dsi.fr>
-- 
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

ALTER TABLE llx_ultimateimmo_immoowner CHANGE COLUMN fk_pays country_id integer;
ALTER TABLE llx_c_ultimateimmo_juridique MODIFY COLUMN code varchar(20) NOT NULL;
ALTER TABLE llx_c_ultimateimmo_immorent_type MODIFY COLUMN code varchar(50) NOT NULL;
ALTER TABLE llx_ultimateimmo_immorenter ADD COLUMN town varchar(255) AFTER country_id;
ALTER TABLE llx_c_ultimateimmo_immoproperty_type MODIFY COLUMN code varchar(50) NOT NULL;

ALTER TABLE llx_ultimateimmo_immoproperty MODIFY COLUMN label varchar(255) NOT NULL;

ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_start date;
ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_end date;
ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_next_rent date;
ALTER TABLE llx_ultimateimmo_immorent MODIFY COLUMN date_last_regul date;
ALTER TABLE llx_ultimateimmo_immorent ADD COLUMN date_last_regul_charge date after date_last_regul;
ALTER TABLE llx_ultimateimmo_immocost_type CHANGE COLUMN fk_user_creat fk_user_create integer NOT NULL;
ALTER TABLE llx_ultimateimmo_immocost_type ADD CONSTRAINT llx_ultimateimmo_immocost_type_fk_user_creat FOREIGN KEY (fk_user_create) REFERENCES llx_user(rowid);
ALTER TABLE llx_ultimateimmo_immocompteur ADD COLUMN compteur_type_id integer AFTER fk_immoproperty;


ALTER TABLE llx_ultimateimmo_building add date_creation datetime NOT NULL after fk_property;
ALTER TABLE llx_ultimateimmo_building add tms timestamp NOT NULL after date_creation;
ALTER TABLE llx_ultimateimmo_building add fk_user_creat integer NOT NULL after tms;
ALTER TABLE llx_ultimateimmo_building add fk_user_modif integer after fk_user_creat;

ALTER TABLE llx_ultimateimmo_immocost_type add active integer after status;
ALTER TABLE llx_ultimateimmo_immocost_type add fk_user_creat integer NOT NULL integer after status;

ALTER TABLE llx_ultimateimmo_immocost CHANGE COLUMN fk_user_create fk_user_creat integer;

ALTER TABLE llx_ultimateimmo_immoproperty ADD COLUMN section_cadastrale varchar(32) after country_id;
ALTER TABLE llx_ultimateimmo_immoproperty ADD COLUMN parcelle_cadastrale varchar(32) after section_cadastrale;
ALTER TABLE llx_ultimateimmo_immoproperty ADD COLUMN num_prm_edf varchar(32) after parcelle_cadastrale;
ALTER TABLE llx_ultimateimmo_immoproperty ADD COLUMN num_internet_line varchar(32) after num_prm_edf;
