--
-- Structure de la table  llx_immo_typologie 
--

CREATE TABLE IF NOT EXISTS  llx_immo_typologie  (
   rowid integer NOT NULL  AUTO_INCREMENT PRIMARY KEY,
   type varchar(200) NOT NULL DEFAULT '',
   famille varchar(100) NOT NULL
)ENGINE=InnoDB;

