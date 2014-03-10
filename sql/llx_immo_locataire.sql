--
-- Structure de la table  llx_immo_locataire 
--

CREATE TABLE IF NOT EXISTS  llx_immo_locataire  (
   rowid   integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   nom  varchar(50)  NOT NULL DEFAULT '',
   telephone  varchar(15)   NOT NULL DEFAULT '',
   email  varchar(50)   NOT NULL DEFAULT '',
   adresse  varchar(300)   NOT NULL DEFAULT '',
   commentaire  text   NOT NULL,
   statut  varchar(10)   NOT NULL DEFAULT 'Actif',
   solde   double(28,4) NOT NULL DEFAULT 0,
   proprietaire_id  integer NOT NULL DEFAULT 1
)ENGINE=InnoDB;
