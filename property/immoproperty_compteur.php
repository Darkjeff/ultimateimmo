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
 * \file 		ultimateimmo/property/immoproperty_compteur.php
 * \ingroup 	ultimateimmo
 * \brief 		Page fiche locataire
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
// Class
dol_include_once ( "/ultimateimmo/class/immoproperty.class.php" );
require_once ('../class/immoproperty.class.php');
require_once ('../lib/immoproperty.lib.php');

// Langs
$langs->load ( "ultimateimmo@ultimateimmo" );

$id = GETPOST ( 'id', 'int' );
$ref = GETPOST('ref', 'alpha');

$mesg = '';

$limit = 500;
$from_date = dol_mktime(0, 0, 0, GETPOST('frmdtmonth', 'int'), GETPOST('frmdtday', 'int'), GETPOST('frmdtyear', 'int'));
$to_date = dol_mktime(23, 59, 59, GETPOST('todtmonth', 'int'), GETPOST('todtday', 'int'), GETPOST('todtyear', 'int'));

/*
 * Bilan Compteur Property
 */
$object = new ImmoProperty($db);
$object->fetch($id, $ref);

llxheader ( '', $langs->trans("Property").' | '.$langs->trans("Bilan"), '' );

$object->fetch_thirdparty();

$head=immopropertyPrepareHead($object);

dol_fiche_head($head, 'bilan Compteur',  $langs->trans("Property"), 0, 'user');

$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

//immo_banner_tab($object, 'id', $linkback, 1, 'rowid', 'name');

print '<table class="border centpercent">';

print '<div class="underbanner clearboth"></div>';


print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";
if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';


print '<table class="tagtable nobottomiftotal liste">'."\n";


$sql = 'SELECT ';
$sql .= $object->getFieldList('t');
$sql .= ',t.label';
$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ultimateimmo_immocompteur_type as ict ON ict.rowid=t.compteur_type_id";
if (!empty($search['fk_immoproperty'])) {
	$sql .=" WHERE t.fk_immoproperty=".(int) $search['fk_immoproperty'];
}
if (!empty($search['compteur_type_id'])) {
	$sql .=" WHERE t.compteur_type_id=".(int) $search['compteur_type_id'];
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
	print '<td class="left">' . $obj->compteur_type_id . '</td>';

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

	//Consommation jour
	print '<td class="left">';
	$conso_jour=0;
	if (empty($day_diff)) {
		$day_diff = 1;
	}
	$conso_jour=$rel_diff/$day_diff;
	print price($conso_jour);
	print '</td>';

	//Consommation mois
	print '<td class="left">';
	$conso_mois=$conso_jour*30;
	print price($conso_mois);
	print '</td>';

	//Consommation annuel
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
