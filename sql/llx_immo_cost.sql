-- ===================================================================
-- Copyright (C) 2016      Olivier Geffroy      <jeff@jeffinfo.com>
-- Copyright (C) 2016      Alexandre Spangaro   <aspangaro.dolibarr@gmail.com>
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
--

CREATE TABLE IF NOT EXISTS `llx_immo_cost` (
  `rowid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `fk_property` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `label` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `supplier` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `new_supplier` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount_ht` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount_vat` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `date` datetime DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `note_public` text COLLATE utf8_unicode_ci,
  `fk_owner` int(11) NOT NULL DEFAULT '1',
  `dispatch` smallint(8) NOT NULL DEFAULT '0',
  `fk_user_author` int(11) NOT NULL,
  `fk_user_modif` int(11) NOT NULL,
  `fk_soc` int(11) NOT NULL,
  PRIMARY KEY (`rowid`)
) ENGINE=InnoDB;

