<?php
/* Copyright (C) 2013-2017	Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2017	Alexandre Spangaro	<aspangaro@zendsi.com>
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
 * \file	immobilier/cost/list.php
 * \ingroup immobilier
 * \brief	List of cost
 */

// Class
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
dol_include_once("/immobilier/class/immocost.class.php");
dol_include_once('/immobilier/class/immoproperty.class.php');
dol_include_once("/immobilier/class/html.formimmobilier.class.php");
include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';


$langs->load("immobilier@immobilier");


$action = GETPOST('action', 'alpha');
$mesg = '';
$action = GETPOST('action');
$massaction=GETPOST('massaction','alpha');
$cancel = GETPOST('cancel');
$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');

$mesg = '';


// Security check
if ($user->societe_id > 0) accessforbidden();

// Load variable for pagination
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');
if ($page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) $sortorder="DESC";
if (!$sortfield) $sortfield="t.datec";

$search_label 		= GETPOST('search_label','alpha');
$search_supplier	= GETPOST('search_supplier','alpha');
$search_property	= GETPOST('search_property','alpha');

$arrayfields=array(
	't.rowid'=>array('label'=>$langs->trans("Reference"), 'checked'=>1),
    't.label'=>array('label'=>$langs->trans("label"), 'checked'=>1),
    'll.name'=>array('label'=>$langs->trans("Property"), 'checked'=>1),
	't.amount'=>array('label'=>$langs->trans("AmountTC"), 'checked'=>1),
    't.amount_ht'=>array('label'=>$langs->trans("AmountHT"), 'checked'=>0),
    't.vat'=>array('label'=>$langs->trans("VAT"), 'checked'=>0),
    't.datec'=>array('label'=>$langs->trans("Date"), 'checked'=>1),
    't.dispatch'=>array('label'=>$langs->trans("Dispatch"), 'checked'=>0),
    't.vat'=>array('label'=>$langs->trans("VAT"), 'checked'=>0),
   	'soc.nom'=>array('label'=>$langs->trans("Supplier"), 'checked'=>1)
);

/*
 * Actions
 */

if (GETPOST('cancel')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction=''; }

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Purge search criteria
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter"))		// Both test must be present to be compatible with all browsers
{
    $search_label = "";
    $search_supplier = "";
	$search_property = "";
	$search_array_options=array();
}


if ($action == 'delete') {
$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $id, $langs->trans('DeleteCost'), $langs->trans('ConfirmDeleteCost'), 'confirm_delete', '', 0, 1);
	print $formconfirm;
}

/*
 *	Delete Cost
 */
if ($action == 'confirm_delete' && $_REQUEST["confirm"] == 'yes') {
	$cost = new Immocost($db);
	$cost->fetch($id);
	$result = $cost->delete($user);
	if ($result > 0) {
		header("Location: list.php");
		exit();
	} else {
		$mesg = '<div class="error">' . $cost->error . '</div>';
	}
}

/*
 * View
 */


$form = new Form($db);
$object = new Immocost($db);
//$form_loyer = new Immoreceipt($db);

llxHeader('', $langs->trans("Costs"));

$sql = "SELECT  t.rowid as reference, t.fk_property as idlocal , t.cost_type as cost_type, t.label as libelle, t.amount_ht , t.amount_vat , t.amount , t.datec , t.fk_soc, t.dispatch";
$sql .= ", ll.rowid as property_id, ll.name as nomlocal,";
$sql .= " soc.rowid as soc_id,";
$sql .= " soc.nom as company";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_cost as t";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_property as ll ON t.fk_property = ll.rowid";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as soc ON soc.rowid = t.fk_soc";


	if ($search_label)			$sql .= natural_search("t.label", $search_label);
	if ($search_property)		$sql .= natural_search("ll.name", $search_property);
	if ($search_supplier)			$sql .= natural_search("soc.nom", $search_supplier);
	$sql .= $db->order($sortfield, $sortorder);

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
}	
$sql .= $db->plimit($limit + 1, $offset);

//print $sql;
$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
    
	$arrayofselected=is_array($toselect)?$toselect:array();

	$param="";

    if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
	if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;
	if ($search_label)		$params.= '&amp;search_label='.urlencode($search_label);
	if ($search_supplier)		$params.= '&amp;search_supplier='.urlencode($search_supplier);
	if ($search_property)	$params.= '&amp;search_property='.urlencode($search_property);
	if ($optioncss)			$param.='&optioncss='.$optioncss;

    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

	$title = $langs->trans("ListCosts");
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_cost');
	
	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
	
    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['t.rowid']['checked']))		print_liste_field_titre($arrayfields['t.rowid']['label'], $_SERVER["PHP_SELF"],"t.rowid","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.label']['checked']))			print_liste_field_titre($arrayfields['t.label']['label'], $_SERVER["PHP_SELF"],"t.label","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['ll.name']['checked']))		print_liste_field_titre($arrayfields['ll.name']['label'], $_SERVER["PHP_SELF"],"ll.name", "", $param,'align="left"',$sortfield,$sortorder);
	if (! empty($arrayfields['t.amount']['checked']))			print_liste_field_titre($arrayfields['t.amount']['label'],$_SERVER["PHP_SELF"],'t.amount','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.datec']['checked']))		print_liste_field_titre($arrayfields['t.datec']['label'],$_SERVER["PHP_SELF"],'t.datec','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['soc.nom']['checked']))		print_liste_field_titre($arrayfields['soc.nom']['label'],$_SERVER["PHP_SELF"],'soc.nom','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
	print "</tr>\n";
	
	// Filters
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['t.rowid']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['t.label']['checked']))			print '<td class="liste_titre"><input type="text" class="flat" size="20" name="search_label" value="' .$search_label. '"></td>';
	if (! empty($arrayfields['ll.name']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="10" name="search_property" value="' .$search_property. '"></td>';
	if (! empty($arrayfields['t.amount']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['t.datec']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['soc.nom']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="20" name="search_supplier" value="' .$search_supplier. '"></td>';
	
	// Action column
	print '<td class="liste_titre" align="middle">';
	$searchpitco=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
	print $searchpitco;
	print '</td>';

	print "</tr>\n";

	$var = true;
	
	$coststatic = new Immocost($db);
	$thirdparty_static = new Societe($db);
	$propertystatic = new Immoproperty($db);

	if ($num > 0)
	{
        $i=0;
    	$var=true;
		while ( $i < min($num, $limit) ) 
		{
			$obj = $db->fetch_object($resql);
	
			$coststatic->id = $obj->reference;
			//$receiptstatic->name = $obj->name;

			$var = ! $var;
			print "<tr " . $bc[$var] . ">";


			
			if (! empty($arrayfields['t.rowid']['checked'])) {
				print '<td>' . $coststatic->getNomUrl(1)  ;
			}
			
			$filedir=$conf->immobilier->dir_output . '/' . dol_sanitizeFileName($coststatic->id);
		$file_list=dol_dir_list($filedir, 'files');
		if (count($file_list)>0) {
			print img_pdf();
		}
			
			print '</td>';

		if (! empty($arrayfields['t.label']['checked'])) {
		print '<td>' . $obj->libelle . '</td>';
		}


			if (! empty($arrayfields['ll.name']['checked'])) {
				$propertystatic->id = $obj->property_id;
				$propertystatic->name = stripslashes(nl2br($obj->nomlocal));
				print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
			}

		
			// Amount
			if (! empty($arrayfields['t.amount']['checked'])) {
				print '<td align="right">' . price($obj->amount) . '</td>';
			}
			
		if (! empty($arrayfields['t.datec']['checked'])) {
				print '<td align="right">' . dol_print_date($obj->datec) . '</td>';
			}
			

		

			if (! empty($arrayfields['soc.nom']['checked'])) {
				$thirdparty_static->id=$obj->fk_soc;
				$thirdparty_static->name=$obj->company;
				print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
			}

			print '<td align="center">';
			if ($user->admin) {
				print '<a href="./list.php?action=delete&id=' . $obj->id . '">';
				print img_delete();
				print '</a>';
			}
			print '</td>' . "\n";

			print "</tr>\n";
				
			$i ++;
		}
	}
	else
	{
		print '<tr '.$bc[false].'>'.'<td colspan="9" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
	}
	
	$db->free($resql);

	print '</table>'."\n";
	print '</div>';

	print '</form>'."\n";
} else {
	dol_print_error($db);
}

llxFooter();
$db->close();