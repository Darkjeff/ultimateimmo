<?php
/* Copyright (C) 2018 Philippe GRAND <philippe.grand@atoo-net.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    immobilier/lib/immobilier.lib.php
 * \ingroup immobilier
 * \brief   Library files with common functions for Immobilier
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function immobilierAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("immobilier@immobilier");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/immobilier/admin/immobilier.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
	
	$head[$h][0] = dol_buildpath("/immobilier/admin/property_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsProperty");
    $head[$h][2] = 'attributes';
    $h++;
	
	$head[$h][0] = dol_buildpath("/immobilier/admin/renter_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsRenter");
    $head[$h][2] = 'attributes';
    $h++;
	
	$head[$h][0] = dol_buildpath("/immobilier/admin/owner_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsOwner");
    $head[$h][2] = 'attributes';
    $h++;
	
	$head[$h][0] = dol_buildpath("/immobilier/admin/payment_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsPayment");
    $head[$h][2] = 'attributes';
    $h++;
	
	$head[$h][0] = dol_buildpath("/immobilier/admin/receipt_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsReceipt");
    $head[$h][2] = 'attributes';
    $h++;
	
	$head[$h][0] = dol_buildpath("/immobilier/admin/rent_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsRent");
    $head[$h][2] = 'attributes';
    $h++;
	
	$head[$h][0] = dol_buildpath("/immobilier/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@immobilier:/immobilier/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@immobilier:/immobilier/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier');

	return $head;
}
