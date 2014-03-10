
--
-- Structure de la table  llx_immo_compteur_local
--

ALTER TABLE llx_immo_compteur_local ADD CONSTRAINT llx_immo_compteur_local_ibfk_fk_local FOREIGN KEY (fk_local) REFERENCES llx_immo_local (rowid) ON DELETE CASCADE;
ALTER TABLE llx_immo_compteur_local ADD CONSTRAINT llx_immo_compteur_local_ibfk_fk_compteur FOREIGN KEY (fk_compteur) REFERENCES llx_immo_compteur (rowid) ON DELETE CASCADE;
