-- ========================================================================
-- Copyright (C) 2018-2019  Philippe GRAND 	<philippe.grand@atoo-net.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_rowid (rowid);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_ref (ref);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_entity (entity);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_preavis (preavis);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_vat (vat);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_fk_soc (fk_soc);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_fk_property (fk_property);
ALTER TABLE llx_ultimateimmo_immorent ADD CONSTRAINT llx_ultimateimmo_immorent_fk_property FOREIGN KEY (fk_property) REFERENCES llx_ultimateimmo_immoproperty(rowid);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_fk_renter (fk_renter);
ALTER TABLE llx_ultimateimmo_immorent ADD INDEX idx_ultimateimmo_immorent_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_ultimateimmo_immorent ADD UNIQUE INDEX uk_ultimateimmo_immorent_fieldxyz(fieldx, fieldy);

--ALTER TABLE llx_ultimateimmo_immorent ADD CONSTRAINT llx_ultimateimmo_immorent_field_id FOREIGN KEY (fk_field) REFERENCES llx_myotherobject(rowid);

