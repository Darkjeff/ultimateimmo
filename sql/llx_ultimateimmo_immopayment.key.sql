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
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_rowid (rowid);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_ref (ref);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_fk_contract (fk_contract);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_fk_property (fk_property);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_fk_renter (fk_renter);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_fk_bank (fk_bank);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_fk_typepayment (fk_typepayment);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_fk_owner (fk_owner);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_fk_receipt (fk_receipt);
ALTER TABLE llx_ultimateimmo_immopayment ADD CONSTRAINT llx_ultimateimmo_immopayment_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_ultimateimmo_immopayment ADD INDEX idx_ultimateimmo_immopayment_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_ultimateimmo_immopayment ADD UNIQUE INDEX uk_ultimateimmo_immopayment_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_ultimateimmo_immopayment ADD CONSTRAINT llx_ultimateimmo_immopayment_fk_field FOREIGN KEY (fk_field) REFERENCES llx_ultimateimmo_myotherobject(rowid);

