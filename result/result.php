<?php
/* Copyright (C) 2001-2006 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2018-2019 Philippe GRAND 		<philippe.grand@atoo-net.com>
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
 *
 */

/**
 * \file ultimateimmo/result/result.php
 * \ingroup compta
 * \brief Page accueil ventilation
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
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
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immobuilding.class.php');

$search_owner=GETPOST('search_owner','int');
if ($search_owner==-1) $search_owner='';

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "bills", "other"));

if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
	$search_owner = '';
}


// Filter
$year = $_GET ["year"];
if ($year == 0) {
	$year_current = strftime("%Y", time());
	$year_start = $year_current;
} else {
	$year_current = $year;
	$year_start = $year;
}

$form = new Form($db);

$dataOwner=array();
$owner = new ImmoOwner($db);
$resultFetch = $owner->fetchAll('','',0,0,array());
if (!is_array($resultFetch) && $resultFetch<0) {
	setEventMessages($owner->error,$owner->errors,'errors');
} elseif (!empty($resultFetch)) {
	foreach($resultFetch as $owner) {
		$dataOwner[$owner->id]=$owner->societe . ' '.$owner->firstname . ' '.$owner->lastname;
	}
}

/*
 * View
 */
llxHeader('', 'Immobilier - Resultat');

$textprevyear = '<a href="' . dol_buildpath('/ultimateimmo/result/result.php', 1) . '?year=' . ($year_current - 1) . '">' . img_previous() . '</a>';
$textnextyear = '<a href="' . dol_buildpath('/ultimateimmo/result/result.php', 1) . '?year=' . ($year_current + 1) . '">' . img_next() . '</a>';

print load_fiche_titre($langs->trans("ImmoResultResultat") . " " . $textprevyear . " " . $langs->trans("Year") . " " . $year_start . " " . $textnextyear, '', 'title_accountancy');

print '<table border="0" width="100%" class="notopnoleftnoright">';
print '<tr><td valign="top" width="30%" class="notopnoleft">';

$y = $year_current;
$months_list = [];
for ($month_num = 1; $month_num <= 12; $month_num++) {
	$months_list[$month_num] = date('F', mktime(0, 0, 0, $month_num, 10));
}
print '<form method="POST" id="searchFormList" action="' . $_SERVER["PHP_SELF"] . '">' . "\n";
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<div class="liste_titre liste_titre_bydiv centpercent">';
print '<div class="divsearchfield">';
print $langs->trans('Owner').$form->selectarray('search_owner', $dataOwner, $search_owner, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth100', 1);
print '</div>';
print '<td class="liste_titre" align="middle">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';
print '</div>';
print '</form>' . "\n";
//Resultat Immo
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td></tr>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">' . $langs->trans("ImmoResultResultatImmo") . '</td>';
foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT ib.label AS nom_immeuble";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(lp.date_payment)=' . $month_num . ' then lp.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(lp.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as lp ON lp.fk_property = ip.rowid ";
$sql .= "  AND lp.date_payment >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND lp.date_payment <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ib.fk_property = ip.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}

$sql .= " GROUP BY  ib.label";

$resql = $db->query($sql);
$total_month=array();
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);
	while ($i < $num) {
		$row = $db->fetch_row($resql);
		$total = 0;

		print '<tr class="oddeven"><td>' . $row[0] . '</td>';
		foreach ($months_list as $month_num => $month_name) {
			print '<td align="right">' . price($row[$month_num]) . '</td>';
			$total += $row[$month_num];
			$total_month[$month_num] += (float) $row[$month_num];
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
$total=0;
print '<tr class="liste_total"><td>' . $langs->trans('Total') . '</td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right">' . price($total_month[$month_num]) . '</td>';
	$total += $total_month[$month_num];
}
print '<td align="right"><b>' . price($total) . '</b></td>';
print '</tr>';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td></tr>';
print "\n<br>\n";

//Paiement Charges locataires
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">' . $langs->trans("ImmoCostPaiementChargelocataire") . '</td>';

foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT ib.label AS nom_immeuble";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ir.date_echeance)=' . $month_num . ' then ir.chargesamount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ir.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as ir ON ir.fk_property = ip.rowid";
$sql .= " AND ir.date_echeance >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ir.date_echeance <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ib.fk_property = ip.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}

$sql .= " GROUP BY  ib.label";

$resql = $db->query($sql);
$total_month=array();
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);
	while ($i < $num) {
		$row = $db->fetch_row($resql);
		$total = 0;

		print '<tr class="oddeven"><td>' . $row[0] . '</td>';
		foreach ($months_list as $month_num => $month_name) {
			print '<td align="right">' . price($row[$month_num]) . '</td>';
			$total += $row[$month_num];
			$total_month[$month_num] += (float) $row[$month_num];
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
$total=0;
print '<tr class="liste_total"><td>' . $langs->trans('Total') . '</td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right">' . price($total_month[$month_num]) . '</td>';
	$total += $total_month[$month_num];
}
print '<td align="right"><b>' . price($total) . '</b></td>';
print '</tr>';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

//Loyer brut encaissé
$value_array = array();

$sql = "SELECT ib.label AS nom_immeuble";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(lp.date_payment)=' . $month_num . ' then lp.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(lp.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as lp ON lp.fk_property = ip.rowid";
$sql .= " AND lp.date_payment >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND lp.date_payment <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ip.fk_property = ib.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}

$sql .= " GROUP BY ib.label";

$resqlencaissement = $db->query($sql);

$sql = "SELECT ib.label AS nom_immeuble";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ir.date_echeance)=' . $month_num . ' then ir.chargesamount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ir.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as ir ON ir.fk_property = ip.rowid";
$sql .= " AND ir.date_echeance >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ir.date_echeance <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ip.fk_property = ib.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}

$sql .= " GROUP BY ib.label";

$resqlpaiement = $db->query($sql);

if ($resqlpaiement && $resqlencaissement) {
	$i = 0;
	$num = max($db->num_rows($resqlpaiement), $db->num_rows($resqlencaissement));

	while ($i < $num) {
		$rowencaissement = $db->fetch_row($resqlencaissement);
		$rowpaiement = $db->fetch_row($resqlpaiement);
		$value_array[$rowencaissement[0]][0] = $rowencaissement[0];
		for ($j = 1; $j <= 13; $j++) {
			$value_array[$rowencaissement[0]][$j] = ($rowencaissement[$j] - $rowpaiement[$j]);
		}
		$i++;
	}
	$db->free($resqlencaissement);
	$db->free($resqlpaiement);
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}

print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">' . $langs->trans("ImmoResultLoyerBrutEncaisse") . '</td>';
foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';
$total_month=array();
foreach ($value_array as $key => $val) {
	$total = 0;
	print '<tr class="oddeven"><td>' . $key . '</td>';
	foreach ($months_list as $month_num => $month_name) {
		print '<td align="right">' . price($val[$month_num]) . '</td>';
		$total += $val[$month_num];
		$total_month[$month_num] += (float) $val[$month_num];
	}
	print '<td align="right"><b>' . price($total) . '</b></td>';
	print '</tr>';
	$i++;
}
//Total Ligne
$total=0;
print '<tr class="liste_total"><td>' . $langs->trans('Total') . '</td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right">' . price($total_month[$month_num]) . '</td>';
	$total += $total_month[$month_num];
}
print '<td align="right"><b>' . price($total) . '</b></td>';
print '</tr>';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

//Charges Déductibles
print '<tr><td colspan=2>';
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">' . $langs->trans("ImmoResultChargesDeductibles") . '</td>';

foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT ib.label AS nom_immeuble";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)=' . $month_num . ' then ic.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM llx_ultimateimmo_immoproperty as ip
        INNER JOIN llx_ultimateimmo_immocost as ic ON ic.fk_property = ip.rowid AND ic.date_start >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "' AND
				ic.date_start <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'
         INNER JOIN llx_ultimateimmo_immocost_type as it
                   ON ic.fk_cost_type = it.rowid AND it.famille = 'Charge déductible'
         INNER JOIN llx_ultimateimmo_building as ib
                    ON ib.fk_property = ip.fk_property ";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}
$sql .= " GROUP BY  ib.label";

$resql = $db->query($sql);
$total_month=array();
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);

	while ($i < $num) {
		$row = $db->fetch_row($resql);
		$total = 0;

		print '<tr class="oddeven"><td>' . $row[0] . '</td>';
		foreach ($months_list as $month_num => $month_name) {
			print '<td align="right">' . price($row[$month_num]) . '</td>';
			$total += $row[$month_num];
			$total_month[$month_num] += (float) $row[$month_num];
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
$total=0;
print '<tr class="liste_total"><td>' . $langs->trans('Total') . '</td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right">' . price($total_month[$month_num]) . '</td>';
	$total += $total_month[$month_num];
}
print '<td align="right"><b>' . price($total) . '</b></td>';
print '</tr>';
print "</table>\n";

// Revenu fiscal
$value_array = array();

$sql = "SELECT ib.rowid";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(lp.date_payment)=' . $month_num . ' then lp.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(lp.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as lp ON lp.fk_property = ip.rowid";
$sql .= " AND lp.date_payment >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND lp.date_payment <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ip.fk_property = ib.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}
$sql .= " GROUP BY  ib.rowid";

$resqlencaissement = $db->query($sql);

$sql = "SELECT ib.rowid";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ir.date_echeance)=' . $month_num . ' then ir.chargesamount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ir.chargesamount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as ir ON ir.fk_property = ip.rowid";
$sql .= " AND ir.date_echeance >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ir.date_echeance <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ip.fk_property = ib.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}

$sql .= " GROUP BY ib.rowid";

$resqlpaiement = $db->query($sql);

$sql = "SELECT ib.rowid";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)=' . $month_num . ' then ic.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic ON ic.fk_property = ip.rowid";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it ON ic.fk_cost_type = it.rowid AND it.famille = 'Charge déductible'";
$sql .= " AND ic.date_start >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ib.fk_property = ip.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}

$sql .= " GROUP BY ib.rowid";

$resqlcharged = $db->query($sql);


$dataEncaissement=array();
$dataPaiement=array();
$dataCharge=array();
$dataRevenueFiscal=array();
if ($resqlpaiement && $resqlencaissement && $resqlcharged) {
	$i = 0;
	while($rowencaissement = $db->fetch_object($resqlencaissement)) {
		for ($j = 1; $j <= 12; $j++) {
			$dataEncaissement[$rowencaissement->rowid][$j] = $rowencaissement->{'month_'.$j};
		}
	}
	$db->free($resqlencaissement);

	while($rowpaiement = $db->fetch_object($resqlpaiement)) {
		for ($j = 1; $j <= 12; $j++) {
			$dataPaiement[$resqlpaiement->rowid][$j] = $resqlpaiement->{'month_'.$j};
		}
	}
	$db->free($resqlpaiement);


	while($rowcharged = $db->fetch_object($resqlcharged)) {
		for ($j = 1; $j <= 12; $j++) {
			$dataCharge[$rowcharged->rowid][$j] = $rowcharged->{'month_'.$j};
		}
	}
	$db->free($resqlcharged);

	$immoData=array();
	if (!empty($dataEncaissement)) {
		foreach($dataEncaissement as $ibId=>$dataMonth) {
			//find immo
			if (!array_key_exists($ibId,$immoData)) {
				$immoBuilding = new ImmoBuilding($db);
				$resultFetchBuilding = $immoBuilding->fetch($ibId);
				if ($resultFetchBuilding < 0) {
					setEventMessages($immoBuilding->error, $immoBuilding->errors, 'errors');
				} else {
					$immoData[$ibId] = $immoBuilding->label;
				}
			}

			$dataRevenueFiscal[$immoData[$ibId]][0] = $immoData[$ibId];
			foreach($dataMonth as $monthInt=>$amount) {
				$dataRevenueFiscal[$immoData[$ibId]][$monthInt] = $amount;
				if (array_key_exists($ibId, $dataPaiement)) {
					$dataRevenueFiscal[$immoData[$ibId]][$monthInt] -= $dataPaiement[$ibId][$monthInt];
				}
				if (array_key_exists($ibId, $dataCharge)) {
					$dataRevenueFiscal[$immoData[$ibId]][$monthInt] -= $dataCharge[$ibId][$monthInt];
				}
			}
		}
	}
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}

print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">' . $langs->trans("ImmoResultRevenuFiscal") . '</td>';
foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';
$total_month=array();
foreach ($dataRevenueFiscal as $key => $val) {
	$total = 0;
	print '<tr class="oddeven"><td>' . $key . '</td>';
	foreach ($months_list as $month_num => $month_name) {
		print '<td align="right">' . price($val[$month_num]) . '</td>';
		$total += $val[$month_num];
		$total_month[$month_num] += (float) $val[$month_num];
	}
	print '<td align="right"><b>' . price($total) . '</b></td>';
	print '</tr>';
	$i++;
}
//Total Ligne
$total=0;
print '<tr class="liste_total"><td>' . $langs->trans('Total') . '</td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right">' . price($total_month[$month_num]) . '</td>';
	$total += $total_month[$month_num];
}
print '<td align="right"><b>' . price($total) . '</b></td>';
print '</tr>';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';




//Charges non Déductibles
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">' . $langs->trans("ImmoResultChargesNonDeductibles") . '</td>';

foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';

$sql = "SELECT ib.label AS nom_immeuble,ib.rowid";
foreach ($months_list as $month_num => $month_name) {
	$sql .= ', ROUND(SUM(case when MONTH(ic.date_start)=' . $month_num . ' then ic.amount else 0 end),2) AS month_' . $month_num;
}
$sql .= ", ROUND(SUM(ic.amount),2) as Total";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocost as ic ON ic.fk_property = ip.rowid";
$sql .= "  AND ic.date_start >= '" . $db->idate(dol_get_first_day($y, 1, false)) . "'";
$sql .= "  AND ic.date_start <= '" . $db->idate(dol_get_last_day($y, 12, false)) . "'";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immocost_type as it ON ic.fk_cost_type = it.rowid";
$sql .= "  AND it.famille = 'Charge non déductible' ";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_building as ib ON ic.fk_property = ib.fk_property";
if (!empty($search_owner)) {
	$sql .= ' WHERE ip.fk_owner='.(int)$search_owner;
}
$sql .= " GROUP BY  ib.label";

$resql = $db->query($sql);
$total_month=array();
$dataChargeNd=array();
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);

	while ($i < $num) {
		$row = $db->fetch_object($resql);
		$total = 0;

		print '<tr class="oddeven"><td>' . $row->nom_immeuble . '</td>';
		foreach ($months_list as $month_num => $month_name) {
			$dataChargeNd[$row->rowid][$month_num]=$row->{'month_'.$month_num};
			print '<td align="right">' . price($row->{'month_'.$month_num}) . '</td>';
			$total += $row->{'month_'.$month_num};
			$total_month[$month_num] += (float) $row->{'month_'.$month_num};
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
$total=0;
print '<tr class="liste_total"><td>' . $langs->trans('Total') . '</td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right">' . price($total_month[$month_num]) . '</td>';
	$total += $total_month[$month_num];
}
print '<td align="right"><b>' . price($total) . '</b></td>';
print '</tr>';
print "</table>\n";

//Revenu Net
$dataRevenueNet = array();

if (!empty($dataRevenueFiscal)) {
	$immoData=array();
	if (!empty($dataEncaissement)) {
		foreach($dataEncaissement as $ibId=>$dataMonth) {
			//find immo
			if (!array_key_exists($ibId,$immoData)) {
				$immoBuilding = new ImmoBuilding($db);
				$resultFetchBuilding = $immoBuilding->fetch($ibId);
				if ($resultFetchBuilding < 0) {
					setEventMessages($immoBuilding->error, $immoBuilding->errors, 'errors');
				} else {
					$immoData[$ibId] = $immoBuilding->label;
				}
			}

			$dataRevenueNet[$immoData[$ibId]][0] = $immoData[$ibId];
			foreach($dataMonth as $monthInt=>$amount) {
				$dataRevenueNet[$immoData[$ibId]][$monthInt] = $amount;
				if (array_key_exists($ibId, $dataPaiement)) {
					$dataRevenueNet[$immoData[$ibId]][$monthInt] -= $dataPaiement[$ibId][$monthInt];
				}
				if (array_key_exists($ibId, $dataCharge)) {
					$dataRevenueNet[$immoData[$ibId]][$monthInt] -= $dataCharge[$ibId][$monthInt];
				}
				if (array_key_exists($ibId, $dataCharge)) {
					$dataRevenueNet[$immoData[$ibId]][$monthInt] -= $dataChargeNd[$ibId][$monthInt];
				}
			}
		}
	}
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}
print "\n<br>\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td width="10%">' . $langs->trans("ImmoResultRevenuNet") . '</td>';
foreach ($months_list as $month_name) {
	print '<td align="right">' . $langs->trans($month_name) . '</td>';
}
print '<td align="right"><b>' . $langs->trans("Total") . '</b></td></tr>';
$total_month=array();
foreach ($dataRevenueNet as $key => $val) {
	$total = 0;
	print '<tr class="oddeven"><td>' . $key . '</td>';
	foreach ($months_list as $month_num => $month_name) {
		print '<td align="right">' . price($val[$month_num], 0, '', 1, -1, 2) . '</td>';
		$total += price($val[$month_num], 0, '', 1, -1, 2);
		$total_month[$month_num] += (float) $val[$month_num];
	}
	print '<td align="right"><b>' . price($total) . '</b></td>';
	print '</tr>';
	$i++;
}
//Total Ligne
$total=0;
print '<tr class="liste_total"><td>' . $langs->trans('Total') . '</td>';
foreach ($months_list as $month_num => $month_name) {
	print '<td align="right">' . price($total_month[$month_num]) . '</td>';
	$total += $total_month[$month_num];
}
print '<td align="right"><b>' . price($total) . '</b></td>';
print '</tr>';
print "</table>\n";
print '</td><td valign="top" width="70%" class="notopnoleftnoright">';
print '</td><td valign="top" width="70%" class="notopnoleftnoright"></td>';
print '</tr>';

print '</td></tr></table>';

// End of page
llxFooter();
$db->close();
