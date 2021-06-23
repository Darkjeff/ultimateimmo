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

$limit = $conf->liste_limit;

/*
 * Bilan Renter
 */
$object = new ImmoRenter($db);
$object->fetch($id, $ref);

llxheader ( '', $langs->trans("Renter").' | '.$langs->trans("Bilan"), '' );

$object->fetch_thirdparty();

$head=immorenterPrepareHead($object);

dol_fiche_head($head, 'bilan',  $langs->trans("Renter"), 0, 'user');

$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

//immo_banner_tab($object, 'id', $linkback, 1, 'rowid', 'name');

print '<table class="border centpercent">';

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

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;

	print '<table class="border tableforfield" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans ( "Date" ) . '</td>';
	print '<td>' . $langs->trans ( "Debit" ) . '</td>';
	print '<td>' . $langs->trans ( "Credit" ) . '</td>';
	print '<td>' . $langs->trans ( "Description" ) . '</td>';
	print "</tr>\n";

	$sql2 = "SELECT SUM(l.total_amount) as debit, 0 as credit ";
	$sql2 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as l";
	$sql2 .= " WHERE l.fk_renter =" . $id;

	$sql3 .= "SELECT 0 as debit , sum(p.amount) as credit";
	$sql3 .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
	$sql3 .= " WHERE p.fk_renter =" . $id;

	$result2 = $db->query ( $sql2 );
	$result3 = $db->query ( $sql3 );

	$objp2 = $db->fetch_object ( $result2 );
	$objp3 = $db->fetch_object ( $result3 );

	$var = ! $var;

	$var = True;
	while ( $i < min ( $num_lignes, $limit ) )
	{
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";

		print '<td>' . dol_print_date ( $db->jdate ( $objp->date ), 'day' ) . '</td>';
		print '<td align="right">' . price($objp->debit) . '</td>';
		print '<td align="right">' . price($objp->credit) . '</td>';
		print '<td>' . $objp->des . '</td>';

		print "</tr>";
		$i ++;
	}

	// Total
	print "<tr $bc[$var]>";
	print '<td>' . $langs->trans("Total") . '</td>';
	print '<td align="right">' . price($objp2->debit) . '</td>';
	print '<td align="right">' . price($objp3->credit) . '</td>';
	print '<td>' . price(($objp3->credit)-($objp2->debit)). '</td>';
	print "</tr>";
} else {
	print $db->error ();
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
