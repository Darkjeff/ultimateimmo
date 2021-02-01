<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
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
 * \file    immobilier/property/diagnostic.php
 * \ingroup immobilier
 * \brief   Diagnostic
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

llxheader('', $langs->trans("Property") . ' | ' . $langs->trans("Diagnostic"), '');

$head = property_prepare_head($object);

dol_fiche_head($head, 'diagnostic', $langs->trans("Property"), 0, 'building@immobilier');

if ($result) {
	if ($mesg)
		print $mesg . "<br>";
	
	$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

	immo_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

	print '<div class="fichecenter">';

	print '<table class="border tableforfield" width="100%">';

	// Build date
	print '<tr>';
	print '<td class="titlefield">' . $langs->trans("BuildDate") . '</td>';
	print '<td>' . dol_print_date($object->datep,"day") . '</td>';
	print '</tr>';

	// Target
	print '<tr>';
	print '<td>'.$langs->trans("Target").'</td>';
	if ($object->target == 0) $target = $langs->trans("PropertyForRent"); else $target = $langs->trans("PropertyForSale");  
	print '<td>'.$target.'</td>';
	print '</tr>';

	print '<tr>';
	print '<td>'.$langs->trans("DiagnosticsNecessary").'</td>';	
	print '<td>';
	print '- ' . $langs->trans("DPE") . '<br>';
	if ($object->target == 0) print '- ' . $langs->trans("SurfaceHabitable") . '<br>';
	if ($object->target == 0) print '- ' . $langs->trans("ERNMT") . '<br>';
	if ($object->target == 0 && dol_print_date($object->datep,"day") < '01/01/1949') '- ' . print $langs->trans("Plomb") . '<br>';
	if ($object->target == 0 && dol_print_date($object->datep,"day") < '01/07/1997') '- ' . print $langs->trans("DAPP") . '<br>';
	if ($object->target == 0 && dol_print_date($object->datep,"day") < '01/07/1997') '- ' . print $langs->trans("DAPP") . '<br>';
	if ($object->target == 0 && dol_print_date($object->datep,"day") < '01/07/1997') '- ' . print $langs->trans("DAPP") . '<br>';
	print '</tr>';
	print '</table>';
	
	print '</div>';
			
	print '<div style="clear:both"></div>';
	
	dol_fiche_end();
}

$db->close();

llxFooter('');