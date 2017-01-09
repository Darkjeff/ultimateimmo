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
$res = @include ("../main.inc.php");
if (! $res && file_exists("../main.inc.php"))
	$res = @include ("../main.inc.php");
if (! $res && file_exists("../../main.inc.php"))
	$res = @include ("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php"))
	$res = @include ("../../../main.inc.php");
if (! $res)
	die("Include of main fails");
	
	// Class
require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
$res = dol_include_once ( "/immobilier/core/modules/immobilier/modules_immobilier.php" );	

// Langs
$langs->load("immobilier@immobilier");
$langs->load("bills");
$langs->load("other");

$model='chargefourn';
$action=GETPOST('action');


// Filter
$year = GETPOST('year');
if ($year == 0) {
	$year_current = strftime("%Y", time());
	$year_start = $year_current;
} else {
	$year_current = $year;
	$year_start = $year;
}

$filename = 'chargefourn_' . dol_sanitizeFileName($year_start) . '.pdf';
$filedir = $conf->immobilier->dir_output . '/chargefourn/';



if ($action == 'builddoc') {
	
	$result = chargefourn_pdf_create($db, $year_start, $model, $langs,$filedir,$filename);
	
	if ($result <= 0) {
		dol_print_error($db, $result);
		exit();
	} else {
		header('Location: ' . $_SERVER["PHP_SELF"] . '?year=' . $year_start);
		exit();
	}
}

/*
 * View
 */
llxHeader('', 'Immobilier - charge par mois');
$formfile = new FormFile($db);

$textprevyear = "<a href=\"chargeappart.php?year=" . ($year_current - 1) . "\">" . img_previous() . "</a>";
$textnextyear = " <a href=\"chargeappart.php?year=" . ($year_current + 1) . "\">" . img_next() . "</a>";

print load_fiche_titre("Charges $textprevyear " . $langs->trans("Year") . " $year_start $textnextyear");

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;

$var = true;
print '<table class="noborder" width="100%">';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr><tr><td colspan=2>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=350>' . $langs->trans("Type") . '</td>';
print '<td align="center">' . $langs->trans("Building") . '</td>';
print '<td align="center">' . $langs->trans("January") . '</td>';
print '<td align="center">' . $langs->trans("February") . '</td>';
print '<td align="center">' . $langs->trans("March") . '</td>';
print '<td align="center">' . $langs->trans("April") . '</td>';
print '<td align="center">' . $langs->trans("May") . '</td>';
print '<td align="center">' . $langs->trans("June") . '</td>';
print '<td align="center">' . $langs->trans("July") . '</td>';
print '<td align="center">' . $langs->trans("August") . '</td>';
print '<td align="center">' . $langs->trans("September") . '</td>';
print '<td align="center">' . $langs->trans("October") . '</td>';
print '<td align="center">' . $langs->trans("November") . '</td>';
print '<td align="center">' . $langs->trans("December") . '</td>';
print '<td align="center"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT ll.nom AS property, ii.nom AS nom_immeuble,";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=1,id.montant,0)),2) AS 'Janvier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=2,id.montant,0)),2) AS 'Fevrier',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=3,id.montant,0)),2) AS 'Mars',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=4,id.montant,0)),2) AS 'Avril',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=5,id.montant,0)),2) AS 'Mai',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=6,id.montant,0)),2) AS 'Juin',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=7,id.montant,0)),2) AS 'Juillet',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=8,id.montant,0)),2) AS 'Aout',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=9,id.montant,0)),2) AS 'Septembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=10,id.montant,0)),2) AS 'Octobre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=11,id.montant,0)),2) AS 'Novembre',";
$sql .= "  ROUND(SUM(IF(MONTH(ic.date_acq)=12,id.montant,0)),2) AS 'Decembre',";
$sql .= "  ROUND(SUM(id.montant),2) as 'Total'";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_chargedet as id";
$sql .= " , " . MAIN_DB_PREFIX . "immo_charge as ic";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll";
$sql .= " , " . MAIN_DB_PREFIX . "immo_property as ii";
$sql .= " WHERE ic.date_acq >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ic.date_acq <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= "  AND id.local_id = ll.rowid AND ll.immeuble_id = ii.rowid AND ic.rowid = id.charge_id ";
if ($user->id != 1) {
	$sql .= " AND ic.proprietaire_id=" . $user->id;
}

$sql .= " GROUP BY ll.immeuble_id,id.local_id";

$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);
	
	while ( $i < $num ) {
		
		$row = $db->fetch_row($resql);
		
		print '<tr><td>' . $row[0] . '</td>';
		print '<td align="right">' . $row[1] . '</td>';
		print '<td align="right">' . $row[2] . '</td>';
		print '<td align="right">' . $row[3] . '</td>';
		print '<td align="right">' . $row[4] . '</td>';
		print '<td align="right">' . $row[5] . '</td>';
		print '<td align="right">' . $row[6] . '</td>';
		print '<td align="right">' . $row[7] . '</td>';
		print '<td align="right">' . $row[8] . '</td>';
		print '<td align="right">' . $row[9] . '</td>';
		print '<td align="right">' . $row[10] . '</td>';
		print '<td align="right">' . $row[11] . '</td>';
		print '<td align="right">' . $row[12] . '</td>';
		print '<td align="right">' . $row[13] . '</td>';
		print '<td align="right"><b>' . $row[14] . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free($resql);
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr><tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width=350>' . $langs->trans("Total") . '</td>';
print '<td align="center">' . $langs->trans("January") . '</td>';
print '<td align="center">' . $langs->trans("February") . '</td>';
print '<td align="center">' . $langs->trans("March") . '</td>';
print '<td align="center">' . $langs->trans("April") . '</td>';
print '<td align="center">' . $langs->trans("May") . '</td>';
print '<td align="center">' . $langs->trans("June") . '</td>';
print '<td align="center">' . $langs->trans("July") . '</td>';
print '<td align="center">' . $langs->trans("August") . '</td>';
print '<td align="center">' . $langs->trans("September") . '</td>';
print '<td align="center">' . $langs->trans("October") . '</td>';
print '<td align="center">' . $langs->trans("November") . '</td>';
print '<td align="center">' . $langs->trans("December") . '</td>';
print '<td align="center"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT 'Total charge' AS 'Total',";
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
$sql .= " WHERE ic.date_acq >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ic.date_acq <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
if ($user->id != 1) {
	$sql .= " AND ic.proprietaire_id=" . $user->id;
}

$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);
	
	while ( $i < $num ) {
		
		$row = $db->fetch_row($resql);
		
		print '<tr><td>' . $row[0] . '</td>';
		print '<td align="right">' . $row[1] . '</td>';
		print '<td align="right">' . $row[2] . '</td>';
		print '<td align="right">' . $row[3] . '</td>';
		print '<td align="right">' . $row[4] . '</td>';
		print '<td align="right">' . $row[5] . '</td>';
		print '<td align="right">' . $row[6] . '</td>';
		print '<td align="right">' . $row[7] . '</td>';
		print '<td align="right">' . $row[8] . '</td>';
		print '<td align="right">' . $row[9] . '</td>';
		print '<td align="right">' . $row[10] . '</td>';
		print '<td align="right">' . $row[11] . '</td>';
		print '<td align="right">' . $row[12] . '</td>';
		print '<td align="right"><b>' . $row[13] . '</b></td>';
		print '</tr>';
		$i ++;
	}
	$db->free($resql);
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}

print "</table>\n";

print '</td></tr></table>';

$var = true;
	if (is_file($filedir.$filename)) {
	print '&nbsp';
	print '<table class="border" width="100%">';
	print '<tr class="liste_titre"><td colspan=3>' . $langs->trans("LinkedDocuments") . '</td></tr>';
	// afficher
	$legende = $langs->trans("Ouvrir");
	print '<tr><td width="200" align="center">' . $langs->trans("ChargeFourn") . '</td><td> ';
	print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=immobilier&file=chargefourn/chargefourn_' . $year_start . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
	print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';
	print '</td></tr></table>';
}

print "<div class=\"tabsAction\">\n";
print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=builddoc&year=' . $year_start . '">' . $langs->trans('GenererPDF') . '</a>';
print '</div>';

llxFooter();

$db->close();
