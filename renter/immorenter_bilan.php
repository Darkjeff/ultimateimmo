<?php
/* Copyright (C) 2013-2016 Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2022 Philippe GRAND 	    <philippe.grand@atoo-net.com>
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
 * \file 		ultimateimmo/renter/bilan.php
 * \ingroup 	ultimateimmo
 * \brief 		Page fiche locataire
 */
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");
if (!$res) die("Include of main fails");

// Class
dol_include_once("/ultimateimmo/class/immorent.class.php");
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/lib/immorenter.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other"));

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');

$mesg = '';

$limit = $conf->liste_limit;

/*
 * Bilan Renter
 */
$object = new ImmoRenter($db);
$object->fetch($id, $ref);

$wikihelp = 'EN:Module_Ultimateimmo_EN#Owners|FR:Module_Ultimateimmo_FR#Configuration_des_locataires';
llxheader('', $langs->trans("Renter") . ' | ' . $langs->trans("Bilan"), $wikihelp);

$object->fetch_thirdparty();

if (isModEnabled('notification')) $langs->load("mails");
$head = immorenterPrepareHead($object);

print dol_get_fiche_head($head, 'bilan',  $langs->trans("Renter"), 0, 'user');

// Object card
// ------------------------------------------------------------
$linkback = '<a href="' . dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

$morehtmlref = '<div class="refidno">';

// Thirdparty
$morehtmlref .= '<br>' . $langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $object->thirdparty->id > 0) $morehtmlref .= ' (<a href="' . dol_buildpath('/ultimateimmo/rent/immorent_list.php', 1) . '?socid=' . $object->thirdparty->id . '&search_fk_soc=' . urlencode($object->thirdparty->id) . '">' . $langs->trans("OtherRents") . '</a>)';

$morehtmlref .= '</div>';

dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';

$sql = "(SELECT l.date_start as date , l.total_amount as debit, 0 as credit , l.label as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
$sql .= " WHERE l.fk_renter =" . $id;
$sql .= ")";
$sql .= "UNION (SELECT p.date_payment as date, 0 as debit, p.amount as credit, p.note_public as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
$sql .= " WHERE p.fk_renter =" . $id;
$sql .= ")";
$sql .= "ORDER BY date";

$result = $db->query($sql);
if ($result) {
	$num_lignes = $db->num_rows($result);
	$i = 0;

	print '<table class="border tableforfield" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("Date") . '</td>';
	print '<td>' . $langs->trans("Debit") . '</td>';
	print '<td>' . $langs->trans("Credit") . '</td>';
	print '<td>' . $langs->trans("Description") . '</td>';
	print "</tr>\n";

	$sql2 = "SELECT SUM(l.total_amount) as debit, 0 as credit ";
	$sql2 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
	$sql2 .= " WHERE l.fk_renter =" . $id;

	$sql3 .= "SELECT 0 as debit , sum(p.amount) as credit";
	$sql3 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
	$sql3 .= " WHERE p.fk_renter =" . $id;

	$result2 = $db->query($sql2);
	$result3 = $db->query($sql3);

	$objp2 = $db->fetch_object($result2);
	$objp3 = $db->fetch_object($result3);

	while ($i < min($num_lignes, $limit)) {
		$objp = $db->fetch_object($result);
		print '<tr class="oddeven">';
		print '<td>' . dol_print_date($db->jdate($objp->date), 'day') . '</td>';
		print '<td align="left">' . price($objp->debit) . '</td>';
		print '<td align="left">' . price($objp->credit) . '</td>';
		print '<td>' . $objp->des . '</td>';

		print "</tr>";
		$i++;
	}

	// Total
	print '<tr class="oddeven">';
	print '<td>' . $langs->trans("Total") . '</td>';
	print '<td align="left">' . price($objp2->debit) . '</td>';
	print '<td align="left">' . price($objp3->credit) . '</td>';
	print '<td>' . price(($objp3->credit) - ($objp2->debit)) . '</td>';
	print "</tr>";
} else {
	print $db->error();
}

print '</table>';

/*
if ($result2) {
	$num_lignes = $db->num_rows ( $result2 );
	$i = 0;
	/*
	print '<table class="border" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>&nbsp;</td>';
	print '<td>' . $langs->trans ( "debit" ) . '</td>';
	print '<td>' . $langs->trans ( "credit" ) . '</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";
	
	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		
		$objp2 = $db->fetch_object ( $result2 );
		$var = ! $var;
		print "<tr $bc[$var]>";
		
		print '<td>&nbsp; Total</td>';
		print '<td>' . $objp2->debit . '</td>';
		print '<td>' . $objp2->credit . '</td>';
		print '<td>&nbsp;</td>';
		
		print "</tr>";
		$i ++;
	}
	print '</table>';
} else {
	print $db->error ();
}


*/
llxFooter();

$db->close();
