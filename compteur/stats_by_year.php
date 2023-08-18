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
 * \file ultimateimmo/compteur/stats_by_year.php
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
dol_include_once('/ultimateimmo/class/immocompteur_type.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/html.formultimateimmo.class.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other", "bills"));

$object = new ImmoCompteur($db);
$compteur_type=new ImmoCompteur_Type($db);
$properties = new ImmoProperty($db);
$form = new Form($db);
$formImmo = new FormUltimateimmo($db);

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
	$search['compteur_type_id']=1;
	$res=$compteur_type->fetchAll('','',0,0,array('active'=>1));
	if (!is_array($res) && $res<0) {
		setEventMessages($compteur_type->error,$compteur_type->errors,'errors');
	}else {
		$search['compteur_type_id']=reset($res)->id;
	}

}
if (GETPOSTISSET('search_year')) {
	$search['year'] = GETPOST('search_year', 'int');
} else {
	$search['year']=dol_print_date(dol_now(),'%Y');
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

$moreforfilter = '';
$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.= $langs->trans('Year') . ': ';
$moreforfilter.= $formImmo->selectYearCompteur($search['year'],'search_year');
$moreforfilter.= '</div>';

if (!empty($moreforfilter)) {
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

print '<table class="tagtable nobottomiftotal liste">'."\n";

$sql = 'SELECT ';
$sql .= $object->getFieldList('t');
$sql .= ',ict.label as label_compteur';
$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
$sql .= " INNER JOIN ".MAIN_DB_PREFIX."c_ultimateimmo_immocompteur_type as ict ON ict.rowid=t.compteur_type_id";
$sql .= " WHERE 1=1";
if (!empty($search['fk_immoproperty'])) {
	$sql .=" AND t.fk_immoproperty=".(int) $search['fk_immoproperty'];
}
if (!empty($search['compteur_type_id'])) {
	$sql .=" AND t.compteur_type_id=".(int) $search['compteur_type_id'];
}
if (!empty($search['year'])) {
	$sql .=" AND YEAR(t.date_relever)=".(int) $search['year'];
}
$sql .= $db->order('t.fk_immoproperty,date_relever');

//$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ip ON t.fk_immoproperty = ip.rowid";
$result_data=array();

$resql = $db->query($sql);
if ($resql) {

	$num = $db->num_rows($resql);

	while ($obj = $db->fetch_object($resql)) {
		$newObj= new stdClass();
		if (array_key_exists($obj->fk_immoproperty,$result_data)) {
			if ($obj->date_relever<$result_data[$obj->fk_immoproperty]->dt_first) {
				$newObj->dt_first = $obj->date_relever;
				$newObj->value_cpt_dt_first = $obj->qty;
			} else {
				$newObj = $result_data[$obj->fk_immoproperty];
			}
			if ($obj->date_relever>$result_data[$obj->fk_immoproperty]->dt_last) {
				$newObj->dt_last = $obj->date_relever;
				$newObj->value_cpt_dt_last = $obj->qty;
			}
		} else {
			$newObj->dt_first = $obj->date_relever;
			$newObj->value_cpt_dt_first = $obj->qty;
			$newObj->dt_last = $obj->date_relever;
			$newObj->value_cpt_dt_last = $obj->qty;
		}
		$newObj->fk_immoproperty=$obj->fk_immoproperty;

		$result_data[$obj->fk_immoproperty] = $newObj;
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

print '<td class="left">';
print $object->showInputField($object->fields['compteur_type_id'], 'compteur_type_id', $search['compteur_type_id'], '', '', 'search_', 'maxwidth150', 1);
print '</td>';
print '<td colspan="5"></td>';
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
//print '<td class="left">' . $langs->trans("ImmoCompteurType") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurDtFirst") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurDtFirstValue") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurDtLast") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurDtLastValue") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurDiff") . '</td>';
print '<td class="left">' . $langs->trans("ImmoCompteurDtDiff") . '</td>';
print '<td class="left"></td>';

print '<tr class="oddeven">';
$i=0;
foreach ($result_data as $obj) {
	$result = $properties->fetch($obj->fk_immoproperty);
	if ($result < 0) {
		setEventMessages($properties->error, $properties->errors, 'errors');
	}

	//Property
	print '<td class="left">' . $properties->getNomUrl() . '</td>';

	//ImmoCompteurDtFirst
	print '<td class="left">' . dol_print_date($db->jdate($obj->dt_first)) . '</td>';

	//ImmoCompteurDtFirstValue
	print '<td class="left">' . $obj->value_cpt_dt_first . '</td>';

	//ImmoCompteurDtLast
	print '<td class="left">' . dol_print_date($db->jdate($obj->dt_last)) . '</td>';

	//ImmoCompteurDtLast
	print '<td class="left">' . $obj->value_cpt_dt_last . '</td>';

	//ImmoCompteurDiff
	print '<td class="left">' . ($obj->value_cpt_dt_last  - $obj->value_cpt_dt_first). '</td>';

	//ImmoCompteurDtDiff
	print '<td class="left">' . price((($db->jdate($obj->dt_last) - $db->jdate($obj->dt_first))) / (60*60*24)). '</td>';

	print '<td class="left"></td>';

	print '</tr>';
	$i++;
}
print "</table>\n";
print "</form>\n";

llxFooter();

$db->close();
