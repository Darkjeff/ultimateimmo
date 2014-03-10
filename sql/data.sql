
INSERT INTO  llx_immo_typologie  ( rowid ,  type ,  famille ) VALUES
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
(92, 'Arrété des comptes : Charges récupérables/locatives', 'Syndic de copropriété'),
(93, 'Arrété des comptes : Charges déductibles', 'Syndic de copropriété'),
(94, 'Arrété des comptes : Charges non déductibles', 'Syndic de copropriété');

INSERT INTO  llx_immo_dict_type_compteur (rowid,code,intitule,sort,active) VALUES (1,'EAU','EAU',1,1);
INSERT INTO  llx_immo_dict_type_compteur (rowid,code,intitule,sort,active) VALUES (2,'ELEC','Electricité',2,1);
