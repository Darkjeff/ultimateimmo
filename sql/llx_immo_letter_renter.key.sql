
--
-- Structure de la table  llx_immo_letter_renter
--

ALTER TABLE llx_immo_letter_renter ADD CONSTRAINT llx_immo_letter_renter_ibfk_fk_locataire FOREIGN KEY (fk_locataire) REFERENCES llx_immo_locataire (rowid) ON DELETE CASCADE;

