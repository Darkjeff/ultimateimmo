<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2018-2019 Philippe GRAND 	    <philippe.grand@atoo-net.com>
 * Copyright (C) 2020      Thomas OURSEL         <contact@ogest.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file htdocs/compta/ventilation/index.php
 * \ingroup compta
 * \brief Page accueil ventilation
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


// Class
require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");


// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo","other","bills"));

// Filter
$year = $_GET ["year"];
if ($year == 0) {
	$year_current = strftime ( "%Y", time () );
	$year_start = $year_current;
} else {
	$year_current = $year;
	$year_start = $year;
}

/*
 * View
 */
llxHeader ( '', 'Compta - Ventilation' );

$textprevyear = '<a href="' .dol_buildpath('/ultimateimmo/cost/cost_renter.php',1) . '?year=' . ($year_current - 1) . '">' . img_previous () . '</a>';
$textnextyear = '<a href="' .dol_buildpath('/ultimateimmo/cost/cost_renter.php',1) . '?year=' . ($year_current + 1) . '">' . img_next () . '</a>';

print_fiche_titre ( $langs->trans("RenterCost")." ".$textprevyear." ".$langs->trans("Year")." ".$year_start." ".$textnextyear);

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;
$months_list = [];
for($month_num = 1; $month_num <= 12 ; $month_num++)
{
	$months_list[$month_num] = date('F', mktime(0, 0, 0, $month_num, 10));
}

print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr><tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=10%>'.$langs->trans("Paiement Charge locataire").'</td>';
foreach($months_list as $month_name)
{
	print '<td align="right">'.$langs->trans($month_name).'</td>';
}

print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ii.label AS nom_immeuble,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=1 then lo.chargesamount else 0 end),2) AS Janvier,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=2 then lo.chargesamount else 0 end),2) AS Fevrier,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=3 then lo.chargesamount else 0 end),2) AS Mars,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=4 then lo.chargesamount else 0 end),2) AS Avril,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=5 then lo.chargesamount else 0 end),2) AS Mai,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=6 then lo.chargesamount else 0 end),2) AS Juin,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=7 then lo.chargesamount else 0 end),2) AS Juillet,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=8 then lo.chargesamount else 0 end),2) AS Aout,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=9 then lo.chargesamount else 0 end),2) AS Septembre,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=10 then lo.chargesamount else 0 end),2) AS Octobre,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=11 then lo.chargesamount else 0 end),2) AS Novembre,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=12 then lo.chargesamount else 0 end),2) AS Decembre,";
$sql .= "  ROUND(SUM(lo.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as lo";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ii";
$sql .= " WHERE lo.date_echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lo.date_echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lo.fk_property = ll.rowid AND ll.fk_property = ii.fk_property  ";
//$sql .= "  AND lo.paye = 1 ";
$sql .= " GROUP BY ll.fk_property, ii.label";

$resql = $db->query ( $sql );
if ($resql) {
	$i = 0;
	$num = $db->num_rows ( $resql );

	while ( $i < $num ) {

		$row = $db->fetch_row ( $resql );

		print '<tr><td>' . $row [0] . '</td>';
		print '<td align="right">' . $row [1] . '</td>';
		print '<td align="right">' . $row [2] . '</td>';
		print '<td align="right">' . $row [3] . '</td>';
		print '<td align="right">' . $row [4] . '</td>';
		print '<td align="right">' . $row [5] . '</td>';
		print '<td align="right">' . $row [6] . '</td>';
		print '<td align="right">' . $row [7] . '</td>';
		print '<td align="right">' . $row [8] . '</td>';
		print '<td align="right">' . $row [9] . '</td>';
		print '<td align="right">' . $row [10] . '</td>';
		print '<td align="right">' . $row [11] . '</td>';
		print '<td align="right">' . $row [12] . '</td>';
		print '<td align="right"><b>' . $row [13] . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=10%>'.$langs->trans("Charges Réel Locataire").'</td>';
print '<td align="right">'.$langs->trans("January").'</td>';
print '<td align="right">'.$langs->trans("February").'</td>';
print '<td align="right">'.$langs->trans("March").'</td>';
print '<td align="right">'.$langs->trans("April").'</td>';
print '<td align="right">'.$langs->trans("May").'</td>';
print '<td align="right">'.$langs->trans("June").'</td>';
print '<td align="right">'.$langs->trans("July").'</td>';
print '<td align="right">'.$langs->trans("August").'</td>';
print '<td align="right">'.$langs->trans("September").'</td>';
print '<td align="right">'.$langs->trans("October").'</td>';
print '<td align="right">'.$langs->trans("November").'</td>';
print '<td align="right">'.$langs->trans("December").'</td>';
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';


$sql = "SELECT ii.label AS nom_immeuble,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=1 then ic.amount else 0 end),2) AS Janvier,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=2 then ic.amount else 0 end),2) AS Fevrier,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=3 then ic.amount else 0 end),2) AS Mars,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=4 then ic.amount else 0 end),2) AS Avril,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=5 then ic.amount else 0 end),2) AS Mai,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=6 then ic.amount else 0 end),2) AS Juin,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=7 then ic.amount else 0 end),2) AS Juillet,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=8 then ic.amount else 0 end),2) AS Aout,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=9 then ic.amount else 0 end),2) AS Septembre,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=10 then ic.amount else 0 end),2) AS Octobre,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=11 then ic.amount else 0 end),2) AS Novembre,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=12 then ic.amount else 0 end),2) AS Decembre,";
$sql .= "  ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ii";
$sql .= " WHERE ic.date_creation >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_creation <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.fk_cost_type = it.rowid ";
$sql .= "  AND it.famille = 'Charge récupérable/locative' ";
$sql .= "  AND ic.fk_property = ll.rowid AND ll.fk_property = ii.fk_property ";
$sql .= " GROUP BY ll.fk_property, ii.label";


$resql = $db->query ( $sql );
if ($resql) {
	$i = 0;
	$num = $db->num_rows ( $resql );

	while ( $i < $num ) {

		$row = $db->fetch_row ( $resql );

		print '<tr class="oddeven"><td>' . $row [0] . '</td>';
		print '<td align="right">' . $row [1] . '</td>';
		print '<td align="right">' . $row [2] . '</td>';
		print '<td align="right">' . $row [3] . '</td>';
		print '<td align="right">' . $row [4] . '</td>';
		print '<td align="right">' . $row [5] . '</td>';
		print '<td align="right">' . $row [6] . '</td>';
		print '<td align="right">' . $row [7] . '</td>';
		print '<td align="right">' . $row [8] . '</td>';
		print '<td align="right">' . $row [9] . '</td>';
		print '<td align="right">' . $row [10] . '</td>';
		print '<td align="right">' . $row [11] . '</td>';
		print '<td align="right">' . $row [12] . '</td>';
		print '<td align="right"><b>' . $row [13] . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}


print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

$value_array=array();

$sql = "SELECT ii.label AS nom_immeuble,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=1 then lo.chargesamount else 0 end),2) AS Janvier,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=2 then lo.chargesamount else 0 end),2) AS Fevrier,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=3 then lo.chargesamount else 0 end),2) AS Mars,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=4 then lo.chargesamount else 0 end),2) AS Avril,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=5 then lo.chargesamount else 0 end),2) AS Mai,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=6 then lo.chargesamount else 0 end),2) AS Juin,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=7 then lo.chargesamount else 0 end),2) AS Juillet,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=8 then lo.chargesamount else 0 end),2) AS Aout,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=9 then lo.chargesamount else 0 end),2) AS Septembre,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=10 then lo.chargesamount else 0 end),2) AS Octobre,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=11 then lo.chargesamount else 0 end),2) AS Novembre,";
$sql .= "  ROUND(SUM(case when MONTH(lo.date_echeance)=12 then lo.chargesamount else 0 end),2) AS Decembre,";
$sql .= "  ROUND(SUM(lo.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as lo";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ii";
$sql .= " WHERE lo.date_echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lo.date_echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lo.fk_property = ll.rowid AND ll.fk_property = ii.fk_property ";
//$sql .= "  AND lo.paye = 1 ";
$sql .= " GROUP BY lo.fk_property, ii.label";

$resqlencaissement = $db->query ( $sql );

$sql = "SELECT ii.label AS nom_immeuble,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=1 then ic.amount else 0 end),2) AS Janvier,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=2 then ic.amount else 0 end),2) AS Fevrier,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=3 then ic.amount else 0 end),2) AS Mars,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=4 then ic.amount else 0 end),2) AS Avril,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=5 then ic.amount else 0 end),2) AS Mai,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=6 then ic.amount else 0 end),2) AS Juin,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=7 then ic.amount else 0 end),2) AS Juillet,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=8 then ic.amount else 0 end),2) AS Aout,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=9 then ic.amount else 0 end),2) AS Septembre,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=10 then ic.amount else 0 end),2) AS Octobre,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=11 then ic.amount else 0 end),2) AS Novembre,";
$sql .= "  ROUND(SUM(case when MONTH(ic.date_creation)=12 then ic.amount else 0 end),2) AS Decembre,";
$sql .= "  ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll";
$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_building as ii";
$sql .= " WHERE ic.date_creation >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_creation <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.fk_cost_type = it.rowid ";
$sql .= "  AND it.famille = 'Charge récupérable/locative' ";
$sql .= "  AND ic.fk_property = ll.rowid AND ll.fk_property = ii.fk_property ";
$sql .= " GROUP BY ic.fk_property, ii.label";

$resqlpaiement = $db->query ( $sql );
if ($resqlpaiement && $resqlencaissement) {
	$i = 0;
	$num = $db->num_rows ( $resqlencaissement );

	while ( $i < $num ) {

		$rowencaissement = $db->fetch_row ( $resqlencaissement );
		$rowpaiement = $db->fetch_row ( $resqlpaiement );

		$value_array[$rowencaissement [0]][0] =  $rowencaissement [0];
		for ($j = 1; $j <= 13; $j++) {
			$value_array[$rowencaissement [0]][$j] = ($rowencaissement [$j] - $rowpaiement [$j]);
		}
		$i ++;
	}
	$db->free ( $resqlencaissement );
	$db->free ( $resqlpaiement );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}

print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=10%>'.$langs->trans("Differentiel Charge").'</td>';
print '<td align="right">'.$langs->trans("January").'</td>';
print '<td align="right">'.$langs->trans("February").'</td>';
print '<td align="right">'.$langs->trans("March").'</td>';
print '<td align="right">'.$langs->trans("April").'</td>';
print '<td align="right">'.$langs->trans("May").'</td>';
print '<td align="right">'.$langs->trans("June").'</td>';
print '<td align="right">'.$langs->trans("July").'</td>';
print '<td align="right">'.$langs->trans("August").'</td>';
print '<td align="right">'.$langs->trans("September").'</td>';
print '<td align="right">'.$langs->trans("October").'</td>';
print '<td align="right">'.$langs->trans("November").'</td>';
print '<td align="right">'.$langs->trans("December").'</td>';
print '<td align="right"><b>'.$langs->trans("Total").'</b></td></tr>';


foreach( $value_array as $key=>$val) {


	print '<tr class="oddeven"><td>' . $key. '</td>';
	print '<td align="right">' . $val[1] . '</td>';
	print '<td align="right">' . $val[2] . '</td>';
	print '<td align="right">' . $val[3] . '</td>';
	print '<td align="right">' . $val[4] . '</td>';
	print '<td align="right">' . $val[5] . '</td>';
	print '<td align="right">' . $val[6] . '</td>';
	print '<td align="right">' . $val[7] . '</td>';
	print '<td align="right">' . $val[8] . '</td>';
	print '<td align="right">' . $val[9] . '</td>';
	print '<td align="right">' . $val[10] . '</td>';
	print '<td align="right">' . $val[11]. '</td>';
	print '<td align="right">' . $val[12] . '</td>';
	print '<td align="right"><b>' . $val[13] . '</b></td>';
	print '</tr>';
	$i ++;
}
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';


llxFooter('');
$db->close();

?>
