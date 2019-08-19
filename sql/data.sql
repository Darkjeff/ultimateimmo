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

INSERT INTO llx_c_ultimateimmo_immoreceipt_status(rowid,code,label,active) VALUES (0, 'STATUS_DRAFT', 'Brouillon', 1),(1, 'STATUS_VALIDATED', 'Validée', 1);

INSERT INTO llx_c_ultimateimmo_immorent_type(rowid,code,label,active) VALUES (0, 'EMPTY_HOUSING', 'Logement vide', 1),(1, 'FURNISHED_APARTMENT', 'Logement meublé', 1);

INSERT INTO llx_c_ultimateimmo_immoproperty_type(rowid,code,label,active) VALUES (0, 'IMMO_COL', 'Immeuble collectif', 1),(1, 'IMMO_INDIV', 'Maison individuelle', 1);

INSERT INTO llx_c_ultimateimmo_juridique(rowid,code,label,active) VALUES (0, 'MONO_PROP', 'Mono propriété', 1),(1, 'CO_PROP', 'Copropriété', 1);

INSERT INTO llx_c_ultimateimmo_builtdate(rowid,code,label,active) VALUES (0, 'BUILT1', 'avant 1949', 1),(1, 'BUILT2', 'de 1949 à 1974', 1),(2, 'BUILT3', 'de 1975 à 1989', 1),(3, 'BUILT4', 'de 1989 à 2005', 1),(4, 'BUILT5', 'depuis 2005', 1);

INSERT INTO llx_ultimateimmo_immoproperty_type(rowid,ref,label,date_creation,fk_user_creat,status) VALUES (0, 'APA', 'Apartment', '2019-08-19 17:39:57', 1, 1),(1, 'HOU', 'Individual house', '2019-08-19 17:39:57', 1, 1),(2, 'LOC', 'Business premises', '2019-08-19 17:39:57', 1, 1),(3, 'SHO', 'Shop', '2019-08-19 17:39:57', 1, 1),(4, 'GAR', 'Garage', '2019-08-19 17:39:57', 1, 1),(5, 'BUL', 'Building', '2019-08-19 17:39:57', 1, 1);







