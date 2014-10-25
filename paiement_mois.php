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

$textprevyear = "<a href=\"paiement_mois.php?year=" . ($year_current - 1) . "\">" . img_previous () . "</a>";
$textnextyear = " <a href=\"paiement_mois.php?year=" . ($year_current + 1) . "\">" . img_next () . "</a>";

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
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Appartement").'</td>';
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

$sql = "SELECT ll.nom AS nom_local,";
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
$sql .= " WHERE lp.date_paiement >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_paiement <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.local_id = ll.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}

$sql .= " GROUP BY ll.nom";

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
print '<tr class="liste_titre"><td width=150>'.$langs->trans("Total").'</td>';
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

$sql = "SELECT 'Total loyer' AS 'Total',";
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
$sql .= " WHERE lp.date_paiement >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND lp.date_paiement <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND lp.local_id = ll.rowid ";
if ($user->id != 1) {
	$sql .= " AND ll.proprietaire_id=".$user->id;
}

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

$db->close();
llxFooter();