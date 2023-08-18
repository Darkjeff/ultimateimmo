<?php
/* Copyright (C) 2013-2016 Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2019 Philippe GRAND 	    <philippe.grand@atoo-net.com>
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
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

// Class
dol_include_once ( "/ultimateimmo/class/immorent.class.php" );
require_once ('../class/immorenter.class.php');
require_once ('../lib/immorenter.lib.php');

// Langs
$langs->load ( "ultimateimmo@ultimateimmo" );

$id = GETPOST ( 'id', 'int' );
$ref = GETPOST('ref', 'alpha');

$mesg = '';

$limit = 500;
$from_date = dol_mktime(0, 0, 0, GETPOST('frmdtmonth', 'int'), GETPOST('frmdtday', 'int'), GETPOST('frmdtyear', 'int'));
$to_date = dol_mktime(23, 59, 59, GETPOST('todtmonth', 'int'), GETPOST('todtday', 'int'), GETPOST('todtyear', 'int'));


// Purge search criteria
if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
	$from_date='';
	$to_date='';
}

$form = new Form($db);


/*
 * Bilan Renter
 */
$object = new ImmoRenter($db);
$object->fetch($id, $ref);

llxheader ( '', $langs->trans("Renter").' | '.$langs->trans("Bilan"), '' );

$object->fetch_thirdparty();

$head=immorenterPrepareHead($object);

print dol_get_fiche_head($head, 'bilan',  $langs->trans("Renter"), 1, 'user');

$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

//immo_banner_tab($object, 'id', $linkback, 1, 'rowid', 'name');

print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="id" value="'.$id.'">';


print '<table class="border centpercent">';

print '<div class="underbanner clearboth"></div>';

print '<tr><td class="left">' . $langs->trans("From") . '</td><td class="left">';
print $form->selectDate($from_date, 'frmdt', 0, 0, 1, 'stats', 1, 0);
print '</td></tr>';
print '<tr><td class="left">' . $langs->trans("To") . '</td><td class="left">';
print $form->selectDate($to_date, 'todt', 0, 0, 1, 'stats', 1, 0);
print '</td></tr>';
print '<tr><td class="left"></td><td class="left">';
print $form->showFilterButtons();
print '</td></tr>';

$sql = "(SELECT l.date_start as date , l.total_amount as debit, 0 as credit , l.label as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
$sql .= " WHERE  l.fk_renter =" . $id;
if (!empty($to_date) && !empty($from_date)) {
	$sql .= " AND l.date_start BETWEEN '" . $db->idate($from_date) . "' AND '" . $db->idate($to_date) . "'";
}
$sql .= ")";
$sql .= "UNION (SELECT p.date_payment as date, 0 as debit, p.amount as credit, p.note_public as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
$sql .= " WHERE p.fk_renter =" . $id;
if (!empty($to_date) && !empty($from_date)) {
	$sql .= " AND p.date_payment BETWEEN '".$db->idate($from_date)."' AND '".$db->idate($to_date)."'";
}

$sql .= ")";

$sql .= " ORDER BY date";
$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;

	print '<table class="tagtable liste" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans ( "Date" ) . '</td>';
	print '<td>' . $langs->trans ( "Debit" ) . '</td>';
	print '<td>' . $langs->trans ( "Credit" ) . '</td>';
	print '<td>' . $langs->trans ( "Description" ) . '</td>';
	print "</tr>\n";

	$sql2 = "SELECT SUM(l.total_amount) as debit, 0 as credit ";
	$sql2 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
	$sql2 .= " WHERE l.fk_renter =" . $id;
	if (!empty($to_date) && !empty($from_date)) {
		$sql2 .= " AND l.date_start BETWEEN '" . $db->idate($from_date) . "' AND '" . $db->idate($to_date) . "'";
	}

	$sql3 = "SELECT 0 as debit , sum(p.amount) as credit";
	$sql3 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
	$sql3 .= " WHERE p.fk_renter =" . $id;
	if (!empty($to_date) && !empty($from_date)) {
		$sql3 .= " AND p.date_payment BETWEEN '".$db->idate($from_date)."' AND '".$db->idate($to_date)."'";
	}

	$result2 = $db->query ( $sql2 );
	$result3 = $db->query ( $sql3 );

	$objp2 = $db->fetch_object ( $result2 );
	$objp3 = $db->fetch_object ( $result3 );


	while ( $objp = $db->fetch_object ( $result ))
	{

		print '<tr class="oddeven">';

		print '<td>' . dol_print_date ( $db->jdate ( $objp->date ), 'day' ) . '</td>';
		print '<td>' . price($objp->debit) . '</td>';
		print '<td>' . price($objp->credit) . '</td>';
		print '<td>' . $objp->des . '</td>';

		print "</tr>";
	}

	// Total
	print '<tr class="liste_total">';
	print '<td>' . $langs->trans("Total") . '</td>';
	print '<td >' . price($objp2->debit) . '</td>';
	print '<td>' . price($objp3->credit) . '</td>';
	print '<td>' . price(($objp3->credit)-($objp2->debit)). '</td>';
	print "</tr>";
} else {
	print $db->error ();
}

print '</table>';
print '</form>';


llxFooter();

$db->close();
