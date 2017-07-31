-- ============================================================================
-- Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
-- Copyright (C) 2015 		Alexandre Spangaro  <aspangaro@zendsi.com>
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
-- Contraintes pour la table llx_immo_contact
--

ALTER TABLE llx_immo_contact ADD INDEX idx_immo_contact_fk_socpeople (fk_socpeople);
ALTER TABLE llx_immo_contact ADD CONSTRAINT llx_immo_contact_ibfk_1 FOREIGN KEY (fk_socpeople) REFERENCES llx_socpeople (rowid);
