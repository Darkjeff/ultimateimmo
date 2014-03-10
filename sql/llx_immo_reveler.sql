-- --------------------------------------------------------

--
-- Structure de la table  llx_immo_contrat 
--

CREATE TABLE IF NOT EXISTS  llx_immo_relever  (
   rowid  integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   fk_compteur_local integer NOT NULL,
   date_reveler datetime NOT NULL,
   index_reveler integer,
   consomation_relever integer,
   comment_relever varchar(200)
)ENGINE=InnoDB;
