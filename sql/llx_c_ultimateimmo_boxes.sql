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
CREATE TABLE llx_c_ultimateimmo_boxes(
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY NOT NULL,
    entity INTEGER NOT NULL DEFAULT 1,
    box_id INTEGER NOT NULL,
    position SMALLINT NOT NULL,
    box_order VARCHAR(3) NOT NULL,
    fk_user INTEGER NOT NULL,
    maxline INTEGER,
    params INTEGER
)ENGINE=InnoDB;
