--
-- Structure de la table  llx_immo_local 
--

CREATE TABLE IF NOT EXISTS  llx_immo_local  (
   rowid  integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   immeuble_id  integer NOT NULL DEFAULT 0,
   nom  varchar(50)   NOT NULL DEFAULT '',
   adresse  varchar(300)   NOT NULL DEFAULT '',
   commentaire  text   NOT NULL,
   statut  varchar(10)   NOT NULL DEFAULT 'Actif',
   superficie   double(28,4) NOT NULL DEFAULT 0,
   proprietaire_id integer NOT NULL DEFAULT 1
)ENGINE=InnoDB;
