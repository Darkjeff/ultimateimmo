<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2019 	Philippe GRAND 	    <philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/property/equipement.php
 * \ingroup ultimateimmo
 * \brief   Equipement page
 */
// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

dol_include_once('/ultimateimmo/lib/immoproperty.lib.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/html.formimmobilier.class.php');

$langs->load("ultimateimmo@ultimateimmo");

$mesg = '';
$id = GETPOST('rowid') ? GETPOST('rowid', 'int') : GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

// Security check
if (! $user->rights->ultimateimmo->read) {
	accessforbidden();
}

$object = new ImmoProperty($db);
$result = $object->fetch($id);

if ($result < 0) {
	setEventMessages(null, $object->errors, 'errors');
}

/*
 * View
 */

$html = new Form($db);
$htmlimmo = new FormImmobilier($db);

llxheader('', $langs->trans("Property") . ' | ' . $langs->trans("Equipements"), '');

$head = immopropertyPrepareHead($object);

dol_fiche_head($head, 'equipement', $langs->trans("Property"), 0, 'building@ultimateimmo');

if ($result) {
	if ($mesg)
		print $mesg . "<br>";
	
	$linkback = '<a href="' .dol_buildpath('/ultimateimmo/property/immoproperty_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	dol_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

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