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

INSERT INTO llx_c_ultimateimmo_immoreceipt_status(rowid,code,label,active) VALUES (1, 'STATUS_DRAFT', 'Brouillon', 1);
INSERT INTO llx_c_ultimateimmo_immoreceipt_status(rowid,code,label,active) VALUES (2, 'STATUS_VALIDATED', 'Validée', 1);

INSERT INTO llx_c_ultimateimmo_immorent_type(rowid,code,label,active) VALUES (1, 'EMPTY_HOUSING', 'Logement vide', 1);
INSERT INTO llx_c_ultimateimmo_immorent_type(rowid,code,label,active) VALUES (2, 'FURNISHED_APARTMENT', 'Logement meublé', 1);

INSERT INTO llx_c_ultimateimmo_immoproperty_type(rowid,code,label,active) VALUES (1, 'APA', 'Apartment', 1);
INSERT INTO llx_c_ultimateimmo_immoproperty_type(rowid,code,label,active) VALUES (2, 'HOU', 'Individual house', 1);
INSERT INTO llx_c_ultimateimmo_immoproperty_type(rowid,code,label,active) VALUES (3, 'LOC', 'Business premises', 1);
INSERT INTO llx_c_ultimateimmo_immoproperty_type(rowid,code,label,active) VALUES (4, 'SHO', 'Shop', 1);
INSERT INTO llx_c_ultimateimmo_immoproperty_type(rowid,code,label,active) VALUES (5, 'GAR', 'Garage', 1);
INSERT INTO llx_c_ultimateimmo_immoproperty_type(rowid,code,label,active) VALUES (6, 'BUL', 'Building', 1);

INSERT INTO llx_c_ultimateimmo_juridique(rowid,code,label,active) VALUES (1, 'MONO_PROP', 'Mono propriété', 1);
INSERT INTO llx_c_ultimateimmo_juridique(rowid,code,label,active) VALUES (2, 'CO_PROP', 'Copropriété', 1);

INSERT INTO llx_c_ultimateimmo_builtdate(rowid,code,label,active) VALUES (1, 'BUILT1', 'avant 1949', 1);
INSERT INTO llx_c_ultimateimmo_builtdate(rowid,code,label,active) VALUES (2, 'BUILT2', 'de 1949 à 1974', 1);
INSERT INTO llx_c_ultimateimmo_builtdate(rowid,code,label,active) VALUES (3, 'BUILT3', 'de 1975 à 1989', 1);
INSERT INTO llx_c_ultimateimmo_builtdate(rowid,code,label,active) VALUES (4, 'BUILT4', 'de 1989 à 2005', 1);
INSERT INTO llx_c_ultimateimmo_builtdate(rowid,code,label,active) VALUES (5, 'BUILT5', 'depuis 2005', 1);

INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (1, 'COSTTYPE001', 'Entretien de l’immeuble et des équipements', 'Charge récupérable/locative', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (2, 'COSTTYPE002', 'Consommations communes', 'Charge récupérable/locative', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (3, 'COSTTYPE003', 'Consommations personnelles', 'Charge récupérable/locative', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (4, 'COSTTYPE004', 'Taxe d’enlèvement des ordures ménagères', 'Charge récupérable/locative', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (5, 'COSTTYPE005', 'Autres', 'Charge récupérable/locative', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (6, 'COSTTYPE006', 'Frais d’administration et de gestion', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (7, 'COSTTYPE007', 'Primes d’assurance', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (8, 'COSTTYPE008', 'Dépenses de réparation, d’entretien et d’amélioration', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (9, 'COSTTYPE009', 'Charges récupérables non récupérées au départ du locataire', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (10, 'COSTTYPE010', 'Indemnités d’éviction, frais de relogement', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (11, 'COSTTYPE011', 'Taxes foncières, taxes annexes', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (12, 'COSTTYPE012', 'Autres', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (13, 'COSTTYPE013', 'Charge non déductible', 'Charge non déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (14, 'COSTTYPE014', 'Intérêts d’emprunt', 'Charge déductible', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (15, 'COSTTYPE015', 'Provisions pour charge', 'Syndic de copropriété', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (16, 'COSTTYPE016', 'Arrêté des comptes : Charges récupérables/locatives', 'Syndic de copropriété', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (17, 'COSTTYPE017', 'Arrêté des comptes : Charges déductibles', 'Syndic de copropriété', 1, 1, NOW());
INSERT INTO llx_ultimateimmo_immocost_type(rowid, ref, label, famille, fk_user_create, status, date_creation) VALUES (18, 'COSTTYPE018', 'Arrêté des comptes : Charges non déductibles', 'Syndic de copropriété', 1, 1, NOW());

INSERT INTO llx_c_gestionparc_boxes_def VALUES (1, 'widgetNbRent.php@ultimateimmo', '1', now());
INSERT INTO llx_c_gestionparc_boxes_def VALUES (2, 'widgetNbRenter.php@ultimateimmo', '1', now());






