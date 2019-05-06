-- ============================================================================
-- Copyright (C) 2014-2019   Philippe Grand		<philippe.grand@atoo-net.com>
-- Copyright (C) 2014-2017   Regis Houssin		<regis.houssin@capnetworks.com>
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

INSERT INTO llx_c_ultimateimmo_immoreceipt_status(rowid,code,label,active) VALUES (0,'STATUS_DRAFT','Brouillon',1);

INSERT INTO llx_c_ultimateimmo_immoreceipt_status(rowid,code,label,active) VALUES (1,'STATUS_VALIDATED','Validée',1);

INSERT INTO llx_c_ultimateimmo_immorent_type(rowid,code,label,active) VALUES (1, 'EMPTY_HOUSING', 'Logement vide', 1),(2, 'FURNISHED_APARTMENT', 'Logement meublé', 1);







