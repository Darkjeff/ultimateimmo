<?php
/* Copyright (C) 2013 Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015 Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file htdocs/property/dpe.php
 * \ingroup immobilier
 * \brief Page for dpe card from property
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../core/lib/immobilier.lib.php');
require_once ('../class/immoproperty.class.php');
require_once ('../class/html.formimmobilier.class.php');

$langs->load("immobilier@immobilier");

$mesg = '';
$id = GETPOST('rowid') ? GETPOST('rowid', 'int') : GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

// Security check
if (! $user->rights->immobilier->property->read) {
	accessforbidden();
}

$object = new Immoproperty($db);
$result = $object->fetch($id);

if ($result < 0) {
	setEventMessages(null, $object->errors, 'errors');
}

/*
 * View
 */

$html = new Form($db);
$htmlimmo = new FormImmobilier($db);

llxheader('', $langs->trans("DPE"), '');

$head = property_prepare_head($object);

dol_fiche_head($head, 'dpe', $langs->trans("PropertyCard"), 0, 'building@immobilier');

if ($result) {
	if ($mesg)
		print $mesg . "<br>";
	
	print '<table class="border" width="100%">';
	$linkback = '<a href="./list.php' . (! empty($socid) ? '?id=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';
	
	print '<tr>';
	print '<td width="25%">' . $langs->trans("NameProperty") . '</td>';
	print '<td colspan="2">' . $object->name . '</td>';
	print '</tr>';
	
	print '</table>';
	
	dol_fiche_end();
}

$db->close();

llxFooter('');