--
-- Structure de la table llx_immo_immeuble
--

CREATE TABLE IF NOT EXISTS llx_immo_immeuble (
  rowid integer NOT NULL AUTO_INCREMENT PRIMARY KEY,
  adresse_id integer NOT NULL DEFAULT '0',
  commentaire text NOT NULL,
  nb_locaux integer NOT NULL DEFAULT '0',
  statut varchar(10) NOT NULL DEFAULT 'Actif',
  proprietaire_id integer NOT NULL DEFAULT '1',
  nom varchar(100) NOT NULL,
  numero text NOT NULL,
  street text NOT NULL,
  zipcode text NOT NULL,
  town text NOT NULL,
  fk_departement integer NOT NULL DEFAULT '0',
  fk_pays integer NOT NULL DEFAULT '0'
)ENGINE=InnoDB;
