-- <one line to give the program's name and a brief idea of what it does.>
-- Copyright (C) <year>  <name of author>
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
CREATE TABLE IF NOT EXISTS llx_mandat (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  ref_interne varchar(80) NOT NULL,
  fk_soc integer NOT NULL, 
  fk_biens integer NOT NULL,
  date_contrat date NOT NULL,
  date_cloture date NOT NULL,
  status integer NOT NULL,
  mise_en_service date NOT null,
  fin_validite date NOT null,
  fk_bails integer not null,
  fk_commercial integer not null,
  notes_private text,
  notes_public text,
  fk_user_author integer NOT NULL,
  datec date NOT NULL,
  fk_user_mod integer NOT NULL,
  tms timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  entity integer NOT NULL DEFAULT 1
) ENGINE=InnoDB;




