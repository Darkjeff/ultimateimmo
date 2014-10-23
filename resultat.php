<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
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
 * \file htdocs/compta/ventilation/index.php
 * \ingroup compta
 * \brief Page accueil ventilation
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");


// Class
require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");


// Langs
$langs->load ( "immobilier@immobilier" );
$langs->load ( "bills" );
$langs->load ( "other" );

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

$textprevyear = "<a href=\"resultat.php?year=" . ($year_current - 1) . "\">" . img_previous () . "</a>";
$textnextyear = " <a href=\"resultat.php?year=" . ($year_current + 1) . "\">" . img_next () . "</a>";

print_fiche_titre ( $langs->trans("Encaissement")." ".$textprevyear." ".$langs->trans("Year")." ".$year_start." ".$textnextyear);

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;

$var = true;
print '<table class="noborder" width="100%">';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr><tr><td colspan=2>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Encaissement").'</td>';
print '<td align="center">'.$langs->trans("January").'</td>';
print '<td align="center">'.$langs->trans("February").'</td>';
print '<td align="center">'.$langs->trans("March").'</td>';
print '<td align="center">'.$langs->trans("April").'</td>';
print '<td align="center">'.$langs->trans("May").'</td>';
print '<td align="center">'.$langs->trans("June").'</td>';
print '<td align="center">'.$langs->trans("July").'</td>';
print '<td align="center">'.$langs->trans("August").'</td>';
print '<td align="center">'.$langs->trans("September").'</td>';
print '<td align="center">'.$langs->trans("October").'</td>';
print '<td align="center">'.$langs->trans("November").'</td>';
print '<td align="center">'.$langs->trans("December").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=1,lp.montant,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=2,lp.montant,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=3,lp.montant,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=4,lp.montant,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=5,lp.montant,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=6,lp.montant,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=7,lp.montant,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=8,lp.montant,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=9,lp.montant,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=10,lp.montant,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=11,lp.montant,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=12,lp.montant,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lp.montant),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as lp";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lp.date_paiement >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_paiement <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.local_id = ll.rowid AND ll.immeuble_id = ii.rowid";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";

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
print '</tr><tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Paiement Charge locataire").'</td>';
print '<td align="center">'.$langs->trans("January").'</td>';
print '<td align="center">'.$langs->trans("February").'</td>';
print '<td align="center">'.$langs->trans("March").'</td>';
print '<td align="center">'.$langs->trans("April").'</td>';
print '<td align="center">'.$langs->trans("May").'</td>';
print '<td align="center">'.$langs->trans("June").'</td>';
print '<td align="center">'.$langs->trans("July").'</td>';
print '<td align="center">'.$langs->trans("August").'</td>';
print '<td align="center">'.$langs->trans("September").'</td>';
print '<td align="center">'.$langs->trans("October").'</td>';
print '<td align="center">'.$langs->trans("November").'</td>';
print '<td align="center">'.$langs->trans("December").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=1,lo.charges,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=2,lo.charges,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=3,lo.charges,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=4,lo.charges,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=5,lo.charges,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=6,lo.charges,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=7,lo.charges,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=8,lo.charges,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=9,lo.charges,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=10,lo.charges,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=11,lo.charges,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=12,lo.charges,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lo.charges),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as lo";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lo.echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lo.echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lo.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";

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

$value_array=array();

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=1,lp.montant,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=2,lp.montant,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=3,lp.montant,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=4,lp.montant,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=5,lp.montant,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=6,lp.montant,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=7,lp.montant,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=8,lp.montant,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=9,lp.montant,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=10,lp.montant,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=11,lp.montant,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=12,lp.montant,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lp.montant),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as lp";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lp.date_paiement >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_paiement <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.local_id = ll.rowid AND ll.immeuble_id = ii.rowid";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";

$resqlencaissement = $db->query ( $sql );

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=1,lo.charges,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=2,lo.charges,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=3,lo.charges,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=4,lo.charges,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=5,lo.charges,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=6,lo.charges,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=7,lo.charges,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=8,lo.charges,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=9,lo.charges,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=10,lo.charges,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=11,lo.charges,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=12,lo.charges,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lo.charges),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as lo";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lo.echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lo.echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lo.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";

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
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Loyer brut encaissé").'</td>';
print '<td align="center">'.$langs->trans("January").'</td>';
print '<td align="center">'.$langs->trans("February").'</td>';
print '<td align="center">'.$langs->trans("March").'</td>';
print '<td align="center">'.$langs->trans("April").'</td>';
print '<td align="center">'.$langs->trans("May").'</td>';
print '<td align="center">'.$langs->trans("June").'</td>';
print '<td align="center">'.$langs->trans("July").'</td>';
print '<td align="center">'.$langs->trans("August").'</td>';
print '<td align="center">'.$langs->trans("September").'</td>';
print '<td align="center">'.$langs->trans("October").'</td>';
print '<td align="center">'.$langs->trans("November").'</td>';
print '<td align="center">'.$langs->trans("December").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';


foreach( $value_array as $key=>$val) {


	print '<tr><td>' . $key. '</td>';
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











print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Charges Déductibles").'</td>';
print '<td align="center">'.$langs->trans("January").'</td>';
print '<td align="center">'.$langs->trans("February").'</td>';
print '<td align="center">'.$langs->trans("March").'</td>';
print '<td align="center">'.$langs->trans("April").'</td>';
print '<td align="center">'.$langs->trans("May").'</td>';
print '<td align="center">'.$langs->trans("June").'</td>';
print '<td align="center">'.$langs->trans("July").'</td>';
print '<td align="center">'.$langs->trans("August").'</td>';
print '<td align="center">'.$langs->trans("September").'</td>';
print '<td align="center">'.$langs->trans("October").'</td>';
print '<td align="center">'.$langs->trans("November").'</td>';
print '<td align="center">'.$langs->trans("December").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=1,ic.montant_ttc,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=2,ic.montant_ttc,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=3,ic.montant_ttc,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=4,ic.montant_ttc,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=5,ic.montant_ttc,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=6,ic.montant_ttc,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=7,ic.montant_ttc,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=8,ic.montant_ttc,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=9,ic.montant_ttc,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=10,ic.montant_ttc,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=11,ic.montant_ttc,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=12,ic.montant_ttc,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(ic.montant_ttc),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immo_typologie as it";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE ic.date_acq >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_acq <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.type = it.rowid ";
$sql .= "  AND it.famille = 'Charge déductible' ";
$sql .= "  AND ic.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";


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



$value_array=array();

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=1,lp.montant,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=2,lp.montant,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=3,lp.montant,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=4,lp.montant,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=5,lp.montant,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=6,lp.montant,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=7,lp.montant,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=8,lp.montant,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=9,lp.montant,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=10,lp.montant,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=11,lp.montant,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=12,lp.montant,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lp.montant),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as lp";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lp.date_paiement >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_paiement <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.local_id = ll.rowid AND ll.immeuble_id = ii.rowid";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";

$resqlencaissement = $db->query ( $sql );

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=1,lo.charges,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=2,lo.charges,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=3,lo.charges,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=4,lo.charges,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=5,lo.charges,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=6,lo.charges,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=7,lo.charges,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=8,lo.charges,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=9,lo.charges,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=10,lo.charges,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=11,lo.charges,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=12,lo.charges,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lo.charges),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as lo";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lo.echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lo.echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lo.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
$sql .= " GROUP BY ll.immeuble_id";

$resqlpaiement = $db->query ( $sql );


$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=1,ic.montant_ttc,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=2,ic.montant_ttc,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=3,ic.montant_ttc,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=4,ic.montant_ttc,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=5,ic.montant_ttc,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=6,ic.montant_ttc,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=7,ic.montant_ttc,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=8,ic.montant_ttc,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=9,ic.montant_ttc,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=10,ic.montant_ttc,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=11,ic.montant_ttc,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=12,ic.montant_ttc,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(ic.montant_ttc),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immo_typologie as it";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE ic.date_acq >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_acq <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.type = it.rowid ";
$sql .= "  AND it.famille = 'Charge déductible' ";
$sql .= "  AND ic.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";



$resqlcharged = $db->query ( $sql );



if ($resqlpaiement && $resqlencaissement && $resqlcharged ) {
	$i = 0;
	$num = $db->num_rows ( $resqlencaissement );

	while ( $i < $num ) {

		$rowencaissement = $db->fetch_row ( $resqlencaissement );
		$rowpaiement = $db->fetch_row ( $resqlpaiement );
		$rowcharged = $db->fetch_row ( $resqlcharged );

		$value_array[$rowencaissement [0]][0] =  $rowencaissement [0];
		for ($j = 1; $j <= 13; $j++) {
			$value_array[$rowencaissement [0]][$j] = ($rowencaissement [$j] - $rowpaiement [$j] - $rowcharged [$j]);
		}
		$i ++;
	}
	$db->free ( $resqlencaissement );
	$db->free ( $resqlpaiement );
	$db->free ( $resqlcharged );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}

print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Revenu Fiscal").'</td>';
print '<td align="center">'.$langs->trans("January").'</td>';
print '<td align="center">'.$langs->trans("February").'</td>';
print '<td align="center">'.$langs->trans("March").'</td>';
print '<td align="center">'.$langs->trans("April").'</td>';
print '<td align="center">'.$langs->trans("May").'</td>';
print '<td align="center">'.$langs->trans("June").'</td>';
print '<td align="center">'.$langs->trans("July").'</td>';
print '<td align="center">'.$langs->trans("August").'</td>';
print '<td align="center">'.$langs->trans("September").'</td>';
print '<td align="center">'.$langs->trans("October").'</td>';
print '<td align="center">'.$langs->trans("November").'</td>';
print '<td align="center">'.$langs->trans("December").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';


foreach( $value_array as $key=>$val) {


	print '<tr><td>' . $key. '</td>';
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





print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Charges non Déductibles").'</td>';
print '<td align="center">'.$langs->trans("January").'</td>';
print '<td align="center">'.$langs->trans("February").'</td>';
print '<td align="center">'.$langs->trans("March").'</td>';
print '<td align="center">'.$langs->trans("April").'</td>';
print '<td align="center">'.$langs->trans("May").'</td>';
print '<td align="center">'.$langs->trans("June").'</td>';
print '<td align="center">'.$langs->trans("July").'</td>';
print '<td align="center">'.$langs->trans("August").'</td>';
print '<td align="center">'.$langs->trans("September").'</td>';
print '<td align="center">'.$langs->trans("October").'</td>';
print '<td align="center">'.$langs->trans("November").'</td>';
print '<td align="center">'.$langs->trans("December").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';

$sql = "SELECT ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=1,ic.montant_ttc,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=2,ic.montant_ttc,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=3,ic.montant_ttc,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=4,ic.montant_ttc,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=5,ic.montant_ttc,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=6,ic.montant_ttc,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=7,ic.montant_ttc,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=8,ic.montant_ttc,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=9,ic.montant_ttc,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=10,ic.montant_ttc,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=11,ic.montant_ttc,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=12,ic.montant_ttc,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(ic.montant_ttc),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immo_typologie as it";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE ic.date_acq >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_acq <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.type = it.rowid ";
$sql .= "  AND it.famille = 'Charge non déductible' ";
$sql .= "  AND ic.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}
$sql .= " GROUP BY ll.immeuble_id";


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





$value_array=array();

$sql = "SELECT 'Total' AS 'Total',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=1,lp.montant,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=2,lp.montant,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=3,lp.montant,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=4,lp.montant,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=5,lp.montant,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=6,lp.montant,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=7,lp.montant,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=8,lp.montant,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=9,lp.montant,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=10,lp.montant,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=11,lp.montant,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lp.date_paiement)=12,lp.montant,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lp.montant),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as lp";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lp.date_paiement >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_paiement <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.local_id = ll.rowid AND ll.immeuble_id = ii.rowid";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}


$resqlencaissement = $db->query ( $sql );

$sql = "SELECT 'Total' AS 'Total',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=1,lo.charges,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=2,lo.charges,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=3,lo.charges,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=4,lo.charges,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=5,lo.charges,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=6,lo.charges,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=7,lo.charges,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=8,lo.charges,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=9,lo.charges,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=10,lo.charges,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=11,lo.charges,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(lo.echeance)=12,lo.charges,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(lo.charges),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as lo";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE lo.echeance >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lo.echeance <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lo.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}


$resqlpaiement = $db->query ( $sql );


$sql = "SELECT 'Total' AS 'Total',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=1,ic.montant_ttc,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=2,ic.montant_ttc,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=3,ic.montant_ttc,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=4,ic.montant_ttc,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=5,ic.montant_ttc,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=6,ic.montant_ttc,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=7,ic.montant_ttc,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=8,ic.montant_ttc,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=9,ic.montant_ttc,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=10,ic.montant_ttc,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=11,ic.montant_ttc,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=12,ic.montant_ttc,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(ic.montant_ttc),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immo_typologie as it";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE ic.date_acq >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_acq <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.type = it.rowid ";
$sql .= "  AND it.famille = 'Charge déductible' ";
$sql .= "  AND ic.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}




$resqlcharged = $db->query ( $sql );




$sql = "SELECT 'Total' AS 'Total',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=1,ic.montant_ttc,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=2,ic.montant_ttc,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=3,ic.montant_ttc,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=4,ic.montant_ttc,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=5,ic.montant_ttc,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=6,ic.montant_ttc,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=7,ic.montant_ttc,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=8,ic.montant_ttc,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=9,ic.montant_ttc,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=10,ic.montant_ttc,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=11,ic.montant_ttc,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=12,ic.montant_ttc,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(ic.montant_ttc),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immo_typologie as it";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_immeuble as ii";
$sql .= " WHERE ic.date_acq >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.date_acq <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.type = it.rowid ";
$sql .= "  AND it.famille = 'Charge non déductible' ";
$sql .= "  AND ic.local_id = ll.rowid AND ll.immeuble_id = ii.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}



$resqlchargend = $db->query ( $sql );




if ($resqlpaiement && $resqlencaissement && $resqlcharged && $resqlchargend  ) {
	$i = 0;
	$num = $db->num_rows ( $resqlencaissement );

	while ( $i < $num ) {

		$rowencaissement = $db->fetch_row ( $resqlencaissement );
		$rowpaiement = $db->fetch_row ( $resqlpaiement );
		$rowcharged = $db->fetch_row ( $resqlcharged );
		$rowchargend = $db->fetch_row ( $resqlchargend );

		$value_array[$rowencaissement [0]][0] =  $rowencaissement [0];
		for ($j = 1; $j <= 13; $j++) {
			$value_array[$rowencaissement [0]][$j] = ($rowencaissement [$j] - $rowpaiement [$j] - $rowcharged [$j] - $rowchargend [$j]);
		}
		$i ++;
	}
	$db->free ( $resqlencaissement );
	$db->free ( $resqlpaiement );
	$db->free ( $resqlcharged );
	$db->free ( $resqlchargend );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}

print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Revenu Net").'</td>';
print '<td align="center">'.$langs->trans("January").'</td>';
print '<td align="center">'.$langs->trans("February").'</td>';
print '<td align="center">'.$langs->trans("March").'</td>';
print '<td align="center">'.$langs->trans("April").'</td>';
print '<td align="center">'.$langs->trans("May").'</td>';
print '<td align="center">'.$langs->trans("June").'</td>';
print '<td align="center">'.$langs->trans("July").'</td>';
print '<td align="center">'.$langs->trans("August").'</td>';
print '<td align="center">'.$langs->trans("September").'</td>';
print '<td align="center">'.$langs->trans("October").'</td>';
print '<td align="center">'.$langs->trans("November").'</td>';
print '<td align="center">'.$langs->trans("December").'</td>';
print '<td align="center"><b>'.$langs->trans("Total").'</b></td></tr>';


foreach( $value_array as $key=>$val) {


	print '<tr><td>' . $key. '</td>';
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










print '</td></tr></table>';

$db->close ();

llxFooter ( '' );

?>
