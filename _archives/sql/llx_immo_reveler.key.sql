ALTER TABLE llx_immo_relever ADD CONSTRAINT llx_immo_relever_ibfk_fk_compteur_local FOREIGN KEY (fk_compteur_local) REFERENCES llx_immo_compteur_local (rowid) ON DELETE CASCADE;
