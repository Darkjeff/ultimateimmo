-- Immobilier
-- Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
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

CREATE TABLE IF NOT EXISTS llx_immo_cost_det (
  rowid int(11) NOT NULL AUTO_INCREMENT,
  fk_property int(11) NOT NULL,
  fk_cost int(11) NOT NULL,
  amount int(11) NOT NULL,
  type text,
  PRIMARY KEY (`Rowid`)
) ENGINE=InnoDB;
