
--
-- Structure de la table  llx_immo_compteur_local
--

CREATE TABLE IF NOT EXISTS llx_immo_compteur_local  (
   rowid  integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   fk_local integer NOT NULL DEFAULT 0,
   fk_compteur integer NOT NULL DEFAULT 0
)ENGINE=InnoDB;
