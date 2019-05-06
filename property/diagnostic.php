<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2019 Philippe GRAND 	<philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/property/diagnostic.php
 * \ingroup ultimateimmo
 * \brief   Diagnostic
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
dol_include_once('/ultimateimmo/class/html.formultimateimmo.class.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo"));

$mesg = '';
$id = GETPOST('rowid') ? GETPOST('rowid', 'int') : GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');

// Security check
if (! $user->rights->ultimateimmo->read) {
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
$htmlimmo = new FormUltimateimmo($db);

llxheader('', $langs->trans("Property") . ' | ' . $langs->trans("Diagnostic"), '');

$head = immopropertyPrepareHead($object);

dol_fiche_head($head, 'diagnostic', $langs->trans("Property"), -1, 'building@ultimateimmo');

if ($result) {
	if ($mesg)
		print $mesg . "<br>";

	$linkback = '<a href="' .dol_buildpath('/ultimateimmo/property/immoproperty_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	dol_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";

	// Build date
	print '<tr>';
	print '<td class="titlefield">' . $langs->trans("BuildDate") . '</td>';
	print '<td>' . dol_print_date($db->jdate($object->datep), 'day') . '</td>';
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
	if ($object->target == 0 && dol_print_date($db->jdate($object->datep), 'day') < '01/01/1949') '- ' . print $langs->trans("Plomb") . '<br>';
	if ($object->target == 0 && dol_print_date($db->jdate($object->datep), 'day') < '01/07/1997') '- ' . print $langs->trans("DAPP") . '<br>';
	if ($object->target == 0 && dol_print_date($db->jdate($object->datep), 'day') < '01/07/1997') '- ' . print $langs->trans("DAPP") . '<br>';
	if ($object->target == 0 && dol_print_date($db->jdate($object->datep), 'day') < '01/07/1997') '- ' . print $langs->trans("DAPP") . '<br>';
	print '</tr>';
	print '</table>';
	print '</div>';
	print '<div class="fichehalfright">';
	print '<div class="ficheaddleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">';

	dol_fiche_end();
}

// End of page
llxFooter();
$db->close();