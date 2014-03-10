--
-- Structure de la table  llx_immo_paie 
--

CREATE TABLE IF NOT EXISTS  llx_immo_paie  (
   rowid  integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   contrat_id  integer NOT NULL,
   local_id  integer NOT NULL,
   locataire_id  integer NOT NULL,
   montant  double(24,8) NOT NULL DEFAULT 0,
   commentaire  text,
   date_paiement  datetime DEFAULT NULL,
   proprietaire_id  integer NOT NULL DEFAULT 1,
   loyer_id  integer NOT NULL
)ENGINE=InnoDB;

