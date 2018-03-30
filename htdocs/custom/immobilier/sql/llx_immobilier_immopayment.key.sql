-- Copyright (C) ---Put here your own copyright and developer email---
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
ALTER TABLE llx_immobilier_immopayment ADD INDEX idx_immobilier_immopayment_rowid (rowid);
ALTER TABLE llx_immobilier_immopayment ADD INDEX idx_immobilier_immopayment_ref (ref);
ALTER TABLE llx_immobilier_immopayment ADD INDEX idx_immobilier_immopayment_entity (entity);
ALTER TABLE llx_immobilier_immopayment ADD INDEX idx_immobilier_immopayment_fk_soc (fk_soc);
ALTER TABLE llx_immobilier_immopayment ADD INDEX idx_immobilier_immopayment_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_immobilier_immopayment ADD UNIQUE INDEX uk_immobilier_immopayment_fieldxyz(fieldx, fieldy);

--ALTER TABLE llx_immobilier_immopayment ADD CONSTRAINT llx_immobilier_immopayment_field_id FOREIGN KEY (fk_field) REFERENCES llx_myotherobject(rowid);

