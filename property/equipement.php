<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/property/equipement.php
 * \ingroup immobilier
 * \brief   Equipement page
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

llxheader('', $langs->trans("PropertyCard") . ' | ' . $langs->trans("Equipements"), '');

$head = property_prepare_head($object);

dol_fiche_head($head, 'equipement', $langs->trans("PropertyCard"), 0, 'building@immobilier');

if ($result) {
	if ($mesg)
		print $mesg . "<br>";
	
	$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

	immo_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
			
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border tableforfield" width="100%">';

	// ADSL
	print '<tr>';
	print '<td class="titlefield">' . $langs->trans("ADSL") . '</td>';
	print '<td>';
	if (! empty($conf->use_javascript_ajax))
	{
		print ajax_constantonoff('DISPLAY_MARGIN_RATES');
	}
	else
	{
		if (empty($conf->global->DISPLAY_MARGIN_RATES))
		{
			print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_DISPLAY_MARGIN_RATES">'.img_picto($langs->trans("Disabled"),'off').'</a>';
		}
		else
		{
			print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_DISPLAY_MARGIN_RATES">'.img_picto($langs->trans("Enabled"),'on').'</a>';
		}
	}
	print '</td>';
	print '</tr>';

	print '</table>';
	print '</div>';
	print '<div class="fichehalfright"><div class="ficheaddleft">';
		   
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border tableforfield" width="100%">';

	// ADSL
	print '<tr>';
	print '<td class="titlefield">' . $langs->trans("Cable") . '</td>';
	print '<td>' . dol_print_date($object->datep,"day") . '</td>';
	print '</tr>';
	
	print "</table>\n";
	print '</div>';
			
	print '</div></div>';
	print '<div style="clear:both"></div>';
	
	dol_fiche_end();
}

$db->close();

llxFooter('');