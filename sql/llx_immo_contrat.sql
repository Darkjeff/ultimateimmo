-- --------------------------------------------------------

--
-- Structure de la table  llx_immo_contrat 
--

CREATE TABLE IF NOT EXISTS  llx_immo_contrat  (
   rowid   integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   local_id   integer NOT NULL,
   locataire_id   integer NOT NULL,
   date_entree  datetime NOT NULL,
   date_fin_preavis  datetime NOT NULL,
   preavis  integer NOT NULL DEFAULT 0,
   date_prochain_loyer  timestamp NOT NULL,
   date_derniere_regul  timestamp NULL DEFAULT NULL,
   montant_tot  double(24,8) NOT NULL DEFAULT 0,
   loy  double(24,8) NOT NULL DEFAULT 0,
   charges  double(24,8) NOT NULL DEFAULT 0,
   tva  double(24,8) NOT NULL DEFAULT 0,
   encours  double(24,8) NOT NULL DEFAULT 0,
   periode  varchar(50) NOT NULL DEFAULT '1 month',
   depot  double(24,8) NOT NULL DEFAULT 0,
   date_der_rev  datetime NOT NULL DEFAULT '2009-01-01 00:00:00',
   commentaire  text NOT NULL,
   proprietaire_id integer NOT NULL DEFAULT '1'
)ENGINE=InnoDB;
