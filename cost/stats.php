<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2018-2019 Philippe GRAND 	    <philippe.grand@atoo-net.com>
 * Copyright (C) 2020      Thomas OURSEL         <contact@ogest.fr>
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
 * \file ultimateimmo/cost/stats.php
 * \ingroup compta
 * \brief Page accueil ventilation
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}
// Class
require_once DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php";

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other", "bills"));

// Filter
$year = GETPOST("year", 'int');
if ($year == 0) {
	$year_current = strftime("%Y", time());
	$year_start = $year_current;
} else {
	$year_current = $year;
	$year_start = $year;
}

$type_stats = GETPOST("type_stats", 'alpha');
if (empty($type_stats)) {
	$type_stats = 'cost_type';
}

/*
 * View
 */
llxHeader('', 'Immobilier - charge par mois');

$textprevyear = '<a href="' . dol_buildpath('/ultimateimmo/cost/stats.php', 1) . '?type_stats='.$type_stats.'&year=' . ($year_current - 1) . '">' . img_previous() . '</a>';
$textnextyear = '<a href="' . dol_buildpath('/ultimateimmo/cost/stats.php', 1) . '?type_stats='.$type_stats.'&year=' . ($year_current + 1) . '">' . img_next() . '</a>';

print load_fiche_titre($langs->trans("CostStatsTitle") . $textprevyear . $langs->trans("Year") . " $year_start $textnextyear");

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;
$months_list = [];
for ($month_num = 1; $month_num <= 12; $month_num++) {
	$months_list[$month_num] = date('F', mktime(0, 0, 0, $month_num, 10));
}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre oddeven"><td width=10%>';
if ($type_stats=='cost_type') {
	print $langs->trans("Type");
} elseif ($type_stats=='fourn_type') {
	print $langs->trans("Supplier");
}
print '</td>';
print '<td class="left" width=10%>' . $langs->trans("Building") . '</td>';
foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td class="right">' . $langs->trans("Total") . '</td></tr>';

if ($type_stats=='cost_type') {
	$fields = "it.label";
} elseif ($type_stats=='fourn_type') {
	$fields = "soc.nom";
}
$sql = 'SELECT '.$fields.' AS label , ib.label AS nom_immeuble';
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)=' . $month_num . ' then ic.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
if ($type_stats=='cost_type') {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it ON ic.fk_cost_type = it.rowid";
} elseif ($type_stats=='fourn_type') {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as soc ON ic.fk_soc=soc.rowid";
}
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip ON ic.fk_property = ip.rowid";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ip.rowid = ib.fk_property";
$sql .= " WHERE ic.date_start >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= ' GROUP BY  '.$fields.', ib.label';
$sql .= $db->order('ib.label');

$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);

	while ($i < $num) {
		$row = $db->fetch_row($resql);
		$total = 0;

		print '<tr class="oddeven"><td>' . $row [0] . '</td>';
		print '<td class="left">' . $row [1] . '</td>';
		foreach ($months_list as $month_num => $month_name) {
			print '<td align="right">' . price($row [$month_num + 1]) . '</td>';
			$total += $row [$month_num + 1];
		}
		print '<td align="right"><b>' . price($total) . '</b></td>';
		print '</tr>';
		$i++;
	}
	$db->free($resql);
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}
print "</table>\n";

print "<br>";

print '<table class="noborder  oddeven" width="100%">';
print '<tr class="liste_titre"><td width=10%>' . $langs->trans("Total") . '</td>';
print '<td class="left" width=10%></td>';
foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td class="right">' . $langs->trans("Total") . '</td></tr>';

$sql = "SELECT 'Total charge' AS Total";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)=' . $month_num . ' then ic.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic";
if ($type_stats=='cost_type') {
	$sql .= " INNER JOIN  " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it ON ic.fk_cost_type = it.rowid";
} elseif ($type_stats=='fourn_type') {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as soc ON ic.fk_soc=soc.rowid";
}
$sql .= " WHERE ic.date_start >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";


$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);

	while ($i < $num) {
		$row = $db->fetch_row($resql);
		$total = 0;

		print '<tr class="oddeven"><td width=10%>' . $row[0] . '</td>';
		print '<td class="left" width=10%>';
		foreach ($months_list as $month_num => $month_name) {
			print '<td align="right">' . price($row [$month_num]) . '</td>';
			$total += $row [$month_num];
		}
		print '<td align="right"><b>' . price($total) . '</b></td>';
		print '</tr>';
		$i++;
	}
	$db->free($resql);
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}
//Total Ligne
print '<tr class="oddeven"><td>' . $langs->trans('Average') . '</td>';
print '<td align="right"></td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right"></td>';
}
print '<td align="right"><b>'.price($total/12).'</b></td>';
print '</tr>';
print "</table>\n";

print '</td></tr></table>';

llxFooter();

$db->close();
