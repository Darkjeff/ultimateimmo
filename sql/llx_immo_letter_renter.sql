
--
-- Structure de la table  llx_immo_letter_renter
--

CREATE TABLE IF NOT EXISTS llx_immo_letter_renter  (
   rowid  integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
   fk_locataire integer NOT NULL DEFAULT 0,
   date_letter  datetime NOT NULL,
   statut  varchar(20)   NOT NULL DEFAULT '',
   object  varchar(50)   NOT NULL DEFAULT '',
   texte  text  
   
)ENGINE=InnoDB;
