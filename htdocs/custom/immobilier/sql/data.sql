-- ========================================================================
-- Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
-- Copyright (C) 2015      Alexandre Spangaro   <aspangaro@zendsi.com>
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

insert into llx_c_immobilier_type_property (id,code,label) values (1, 'apa', 'Apartment');
insert into llx_c_immobilier_type_property (id,code,label) values (2, 'hou', 'Individual house');
insert into llx_c_immobilier_type_property (id,code,label) values (3, 'loc', 'Business premises');
insert into llx_c_immobilier_type_property (id,code,label) values (4, 'sho', 'Shop');
insert into llx_c_immobilier_type_property (id,code,label) values (5, 'gar', 'Garage');
insert into llx_c_immobilier_type_property (id,code,label) values (6, 'bul', 'Building');

INSERT INTO  llx_immobilier_typologie  ( rowid ,  type ,  famille ) VALUES
(11, 'Entretien de l’immeuble et des équipements', 'Charge récupérable/locative'),
(12, 'Consommations communes', 'Charge récupérable/locative'),
(13, 'Consommations personnelles', 'Charge récupérable/locative'),
(14, 'Taxe d’enlèvement des ordures ménagères', 'Charge récupérable/locative'),
(19, 'Autres', 'Charge récupérable/locative'),
(21, 'Frais d’administration et de gestion', 'Charge déductible'),
(22, 'Primes d’assurance', 'Charge déductible'),
(23, 'Dépenses de réparation, d’entretien et d’amélioration', 'Charge déductible'),
(24, 'Charges récupérables non récupérées au départ du locataire', 'Charge déductible'),
(25, 'Indemnités d’éviction, frais de relogement', 'Charge déductible'),
(26, 'Taxes foncières, taxes annexes', 'Charge déductible'),
(29, 'Autres', 'Charge déductible'),
(31, 'Charge non déductible', 'Charge non déductible'),
(41, 'Intérêts d’emprunt', 'Charge déductible'),
(91, 'Provisions pour charge', 'Syndic de copropriété'),
(92, 'Arrêté des comptes : Charges récupérables/locatives', 'Syndic de copropriété'),
(93, 'Arrêté des comptes : Charges déductibles', 'Syndic de copropriété'),
(94, 'Arrêté des comptes : Charges non déductibles', 'Syndic de copropriété');

INSERT INTO  llx_c_immobilier_type_compteur (rowid,code,intitule,sort,active) VALUES (1,'EAU','Eau',1,1);
INSERT INTO  llx_c_immobilier_type_compteur (rowid,code,intitule,sort,active) VALUES (2,'ELEC','Electricité',2,1);
