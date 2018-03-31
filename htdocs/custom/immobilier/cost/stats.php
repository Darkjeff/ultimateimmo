<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2018 	   Philippe GRAND 	    <philippe.grand@atoo-net.com>
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
$langs->loadLangs(array("immobilier@immobilier","other","bills"));

// Filter
$year=$_GET["year"];
if ($year == 0 )
{
	$year_current = strftime("%Y",time());
	$year_start = $year_current;
}
else
{
	$year_current = $year;
	$year_start = $year;
}

/*
 * View
 */
llxHeader ( '', 'Immobilier - charge par mois' );

$textprevyear = '<a href="' .dol_buildpath('/immobilier/cost/stats.php',1) . '?year=' . ($year_current - 1) . '">' . img_previous () . '</a>';
$textnextyear = '<a href="' .dol_buildpath('/immobilier/cost/stats.php',1) . '?year=' . ($year_current + 1) . '">' . img_next () . '</a>';

print load_fiche_titre ( "Charges $textprevyear " . $langs->trans ( "Year" ) . " $year_start $textnextyear" );

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre oddeven"><td width=250>'.$langs->trans("Type").'</td>';
print '<td align="center">'.$langs->trans("Building").'</td>';
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
print '<td align="center">'.$langs->trans("Total").'</td></tr>';

$sql = "SELECT it.type AS type_charge, ib.name AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=1,ic.amount,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=2,ic.amount,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=3,ic.amount,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=4,ic.amount,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=5,ic.amount,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=6,ic.amount,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=7,ic.amount,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=8,ic.amount,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=9,ic.amount,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=10,ic.amount,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=11,ic.amount,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=12,ic.amount,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(ic.amount),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immobilier_immocost as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immobilier_immotypologie as it";
$sql .= " , " . MAIN_DB_PREFIX . "immobilier_immoproperty as ii";
$sql .= " , " . MAIN_DB_PREFIX . "immobilier_immobuilding as ib";
$sql .= " WHERE ic.datec >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.datec <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.cost_type = it.rowid";
$sql .= "  AND ic.fk_property = ii.rowid AND ii.fk_property = ib.fk_property";


$sql .= " GROUP BY ii.fk_property, it.type";

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
        print '<td align="right">' . $row [13] . '</td>';
		print '<td align="right">' . $row [14] . '</td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}
print "</table>\n";

print "<br>";

print '<table class="noborder  oddeven" width="100%">';
print '<tr class="liste_titre"><td width=350>'.$langs->trans("Total").'</td>';
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
print '<td align="center">'.$langs->trans("Total").'</td></tr>';

$sql = "SELECT 'Total charge' AS 'Total',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=1,ic.amount,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=2,ic.amount,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=3,ic.amount,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=4,ic.amount,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=5,ic.amount,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=6,ic.amount,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=7,ic.amount,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=8,ic.amount,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=9,ic.amount,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=10,ic.amount,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=11,ic.amount,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.datec)=12,ic.amount,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(ic.amount),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immobilier_immocost as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immobilier_immotypologie as it";
$sql .= " WHERE ic.datec >= '" . $db->idate ( dol_get_first_day ( $y, 1, false ) ) . "'";
$sql .= "  AND ic.datec <= '" . $db->idate ( dol_get_last_day ( $y, 12, false ) ) . "'";
$sql .= "  AND ic.cost_type = it.rowid";


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
		print '<td align="right">' . $row [13] . '</td>';
		print '</tr>';
		$i ++;
	}
	$db->free ( $resql );
} else {
	print $db->lasterror (); // affiche la derniere erreur sql
}

print "</table>\n";

print '</td></tr></table>';

llxFooter();

$db->close();
