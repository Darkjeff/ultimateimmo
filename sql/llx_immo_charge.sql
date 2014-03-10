
--
-- Structure de la table  llx_immo_charge 
--

CREATE TABLE IF NOT EXISTS  llx_immo_charge  (
   rowid   integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   local_id   integer NOT NULL DEFAULT 0,
   type   integer NOT NULL DEFAULT 0,
   libelle  varchar(100) NOT NULL DEFAULT '',
   fournisseur  varchar(200) NOT NULL DEFAULT '',
   nouveau_fournisseur  varchar(200) NOT NULL DEFAULT '',
   montant_ht  double(24,8) NOT NULL DEFAULT 0,
   montant_tva  double(24,8) NOT NULL DEFAULT 0,
   montant_ttc double(24,8) NOT NULL DEFAULT 0,
   date_acq  datetime DEFAULT NULL,
   periode_du  datetime DEFAULT NULL,
   periode_au  datetime DEFAULT NULL,
   commentaire  text,
   proprietaire_id integer NOT NULL DEFAULT 1
)ENGINE=InnoDB;
