
--
-- Structure de la table  llx_immo_compteur
--

CREATE TABLE IF NOT EXISTS  llx_immo_compteur  (
   rowid  integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   type   integer NOT NULL DEFAULT 0,
   label  varchar(100) NOT NULL DEFAULT ''
)ENGINE=InnoDB;
