-- ===================================================================
-- Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2018-2022 Philippe GRAND  <philippe.grand@atoo-net.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================

CREATE TABLE llx_ultimateimmo_payment_receipt
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_paiement     integer,								-- type of payment in llx_c_paiement
  fk_receipt      integer,
  amount          double(24,8)     DEFAULT 0			-- amount paid in Dolibarr currency
)ENGINE=innodb;
