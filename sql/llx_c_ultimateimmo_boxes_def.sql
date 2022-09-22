-- ========================================================================
-- Copyright (C) 2022  Florian HENRY <florian.henry@scopen.fr>
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
-- ========================================================================
CREATE TABLE llx_c_ultimateimmo_boxes_def(
     rowid INTEGER AUTO_INCREMENT PRIMARY KEY NOT NULL,
     file VARCHAR(200) NOT NULL,
     entity INTEGER NOT NULL DEFAULT 1,
     tms timestamp,
     note VARCHAR(130)
)ENGINE=InnoDB;
