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
 * \file ultimateimmo/compteur/stats.php
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
dol_include_once('/ultimateimmo/class/immocompteur.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other", "bills"));

$object = new ImmoCompteur($db);
$properties = new ImmoProperty($db);
$form = new Form($db);

$optioncss  = GETPOST('optioncss', 'aZ'); // Option for the css output (always '' except when 'print')

$search=array();
if (GETPOSTISSET('search_fk_immoproperty') && GETPOST('search_fk_immoproperty', 'int') != -1) {
	$search['fk_immoproperty'] = GETPOST('search_fk_immoproperty', 'int');
} else {
	$search['fk_immoproperty']=0;
}
if (GETPOSTISSET('search_compteur_type_id') && GETPOST('search_compteur_type_id', 'int') != -1) {
	$search['compteur_type_id'] = GETPOST('search_compteur_type_id', 'int');
} else {
	$search['compteur_type_id']=0;
}

/*
 * View
 */
llxHeader('', $langs->trans('MenuImmoCompteurList') . ' ' .$langs->trans('MenuImmoCompteurStats'));

print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table


print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";
if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';


print '<table class="tagtable nobottomiftotal liste">'."\n";


$sql = 'SELECT ';
$sql .= $object->getFieldList('t');
$sql .= ',ict.label as label_compteur';
$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ultimateimmo_immocompteur_type as ict ON ict.rowid=t.compteur_type_id";
$sql .= " WHERE 1=1";
if (!empty($search['fk_immoproperty'])) {
	$sql .=" AND t.fk_immoproperty=".(int) $search['fk_immoproperty'];
}
if (!empty($search['compteur_type_id'])) {
	$sql .=" AND t.compteur_type_id=".(int) $search['compteur_type_id'];
}
$sql .= $db->order('t.fk_immoproperty,date_relever');

//$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip ON t.fk_immoproperty = ip.rowid";
$result_data=array();

$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num = $db->num_rows($resql);

	while ($obj = $db->fetch_object($resql)) {
		$result_data[$i] = $obj;
		$i++;
	}
	$db->free($resql);
} else {
	print $db->lasterror(); // affiche la derniere erreur sql
}


// Fields title search
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
print '<td class="left">';
print $object->showInputField($object->fields['fk_immoproperty'], 'fk_immoproperty', $search['fk_immoproperty'], '', '', 'search_', 'maxwidth150', 1);
print '</td>';
print '<td colspan="1"></td>';


print '<td class="left">';
print $object->showInputField($object->fields['compteur_type_id'], 'compteur_type_id', $search['compteur_type_id'], '', '', 'search_', 'maxwidth150', 1);
print '</td>';
print '<td colspan="6"></td>';
// Action column
print '<td class="liste_titre maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';
print '</tr>';
// Fields title
// --------------------------------------------------------------------
print '<tr class="liste_titre">';

print '<td class="left">' . $langs->trans("Building") . '</td>';
print '<td class="left">' . $langs->trans("Date") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurType") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurStatement") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurNbDays") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurConsumption") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurDailyConsumption") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurMonthlyConsumption") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurYearlyConsumption") . '</td>';
print '<td class="left">' . $langs->trans("Year") . '</td>';
print '<tr class="oddeven">';
$i=0;
foreach ($result_data as $obj) {
	$result = $properties->fetch($obj->fk_immoproperty);
	if ($result < 0) {
		setEventMessages($properties->error, $properties->errors, 'errors');
	}

	//Property
	print '<td class="left">' . $properties->getNomUrl() . '</td>';

	//Date relever
	print '<td class="left">' . dol_print_date($obj->date_relever) . '</td>';

	//Type de compteur
	print '<td class="left">' . $obj->label_compteur . '</td>';

	//Relever
	print '<td class="left">' . $obj->qty . '</td>';

	//Nb Jours
	print '<td class="left">';
	if (array_key_exists($i+1, $result_data) && $result_data[$i+1]->fk_immoproperty==$obj->fk_immoproperty) {
		$day_diff = num_between_day($db->jdate($obj->date_relever), $db->jdate($result_data[$i+1]->date_relever));
		print $day_diff;
	} else {
		$day_diff=1;
	}
	print '</td>';

	//Consomation
	print '<td class="left">';
	if (array_key_exists($i+1, $result_data) && $result_data[$i+1]->compteur_type_id==$obj->compteur_type_id) {
		$rel_diff = $result_data[$i+1]->qty-$obj->qty;
		print $rel_diff;
	} else {
		$rel_diff=0;
	}
	print '</td>';

	//Consommation jour
	/*print '<td class="left">';
	if (array_key_exists($i+1, $result_data) && $result_data[$i+1]->fk_immoproperty==$obj->fk_immoproperty) {
		$rel_diff = $result_data[$i+1]->qty-$obj->qty;
		print $rel_diff;
	} else {
		$rel_diff=0;
	}
	print '</td>';*/

	//Consommation jour arrondi a deux chiffres apres la virgule
	print '<td class="left">';
	$conso_jour=0;
	if (empty($day_diff)) {
		$day_diff = 1;
	}
	$conso_jour=number_format($rel_diff/$day_diff,2);
	print price($conso_jour);
	print '</td>';

	//Consommation mois
	print '<td class="left">';
	$conso_mois=$conso_jour*30;
	print price($conso_mois);
	print '</td>';

	//Consommation annuelle
	print '<td class="left">';
	$conso_year=$conso_jour*365;
	print price($conso_year);
	print '</td>';

	//Année
	print '<td class="left">';
	print dol_print_date($obj->date_relever, '%Y');
	print '</td>';

	print '</tr>';
	$i++;
}
print "</table>\n";
print "</form>\n";

llxFooter();

$db->close();
