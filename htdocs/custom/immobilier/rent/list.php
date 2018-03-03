<?php
/* Copyright (C) 2013-2015 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015-2017 Alexandre Spangaro	<aspangaro@zendsi.com>
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
 * \file    	immobilier/rent/list.php
 * \ingroup 	Immobilier
 * \brief   	List of rent
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

dol_include_once("/immobilier/class/immorent.class.php");
dol_include_once("/immobilier/class/immoproperty.class.php");
dol_include_once("/immobilier/class/immorenter.class.php");
dol_include_once("/immobilier/core/lib/immobilier.lib.php");

$langs->load("immobilier@immobilier");

$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');

// Security check
if ($user->societe_id > 0) 	accessforbidden ();

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
if (!$sortfield) $sortfield="c.rowid";

$search_renter		= GETPOST('search_name', 'alpha');
$search_property	= GETPOST('search_property', 'alpha');
$search_status		= GETPOST('search_status', 'alpha') != '' ? GETPOST('search_status', 'alpha') : GETPOST('statut', 'alpha');

$arrayfields=array(
	'c.rowid'=>array('label'=>$langs->trans("Contract"), 'checked'=>1),
    'loc.nom'=>array('label'=>$langs->trans("Renter"), 'checked'=>1),
    'l.name'=>array('label'=>$langs->trans("Property"), 'checked'=>1),
    'c.montant_total'=>array('label'=>$langs->trans("AmountTC"), 'checked'=>1),
    'c.encours'=>array('label'=>$langs->trans("Income"), 'checked'=>1),
	'c.statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1)
);

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Purge search criteria
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter"))
{
	$search_renter = "";
	$search_property = "";
	$search_status = "";
    $search_array_options=array();
}

/*
 * View
 */
$form = new Form($db);
$object = new Rent($db);

llxHeader('', $langs->trans("Rents"));

$sql = "SELECT c.rowid as contract_id, c.montant_tot as amount_total, c.encours as encours, c.preavis as preavis, c.statut as statut"; 
$sql .= ", loc.rowid as renter_id, loc.nom as renter_lastname";
$sql .= ", l.name as property_name, l.rowid as property_id";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_contrat as c";
$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as loc";
$sql .= " , " . MAIN_DB_PREFIX . "immo_property as l";
$sql .= " WHERE loc.rowid = c.fk_renter and l.rowid = c.fk_property";
if ($search_renter)		$sql .= natural_search("loc.nom", $search_renter);
if ($search_property)	$sql .= natural_search("l.name", $search_property);
if ($search_statut)		$sql .= natural_search("c.statut", $search_statut);
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
	if ($search_renter)		$params.= '&amp;search_renter='.urlencode($search_renter);
	if ($search_property)	$params.= '&amp;search_property='.urlencode($search_property);
	if ($search_status)		$params.= '&amp;search_status='.urlencode($search_status);
    if ($optioncss)			$param.='&optioncss='.$optioncss;

	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

	$title = $langs->trans("ListRents");
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_rent');

	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields

    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['c.rowid']['checked']))		print_liste_field_titre($arrayfields['c.rowid']['label'], $_SERVER["PHP_SELF"],"c.rowid","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['loc.nom']['checked']))		print_liste_field_titre($arrayfields['loc.nom']['label'], $_SERVER["PHP_SELF"],"loc.nom","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['l.name']['checked']))			print_liste_field_titre($arrayfields['l.name']['label'], $_SERVER["PHP_SELF"],"l.name", "", $param,'align="left"',$sortfield,$sortorder);
	if (! empty($arrayfields['c.montant_tot']['checked']))	print_liste_field_titre($arrayfields['c.montant_tot']['label'],$_SERVER["PHP_SELF"],'c.montant_tot','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['c.encours']['checked']))		print_liste_field_titre($arrayfields['c.encours']['label'],$_SERVER["PHP_SELF"],'c.encours','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['c.statut']['checked']))		print_liste_field_titre($arrayfields['c.statut']['label'],$_SERVER["PHP_SELF"],'c.statut','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
	print "</tr>\n";

	// Filters
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['c.rowid']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['loc.nom']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="20" name="search_renter" value="' .$search_renter. '"></td>';
	if (! empty($arrayfields['l.name']['checked']))			print '<td class="liste_titre"><input type="text" class="flat" size="10" name="search_property" value="' .$search_property. '"></td>';
	if (! empty($arrayfields['c.montant_tot']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['c.encours']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['c.statut']['checked']))		print '<td class="liste_titre">'.$form->selectarray('search_status', $object->status_array, $object->statut).'</td>';
	
	// Action column
	print '<td class="liste_titre" align="middle">';
	$searchpicto=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
	print $searchpicto;
	print '</td>';

	print "</tr>\n";

	$rentstatic = new Rent($db);
	$propertystatic = new Immoproperty($db);

	if ($num > 0)
	{
        $i=0;
		while ( $i < min($num, $limit) ) 
		{
			$obj = $db->fetch_object($resql);
			$code_statut = '';

			if ($objp->preavis == 1 ){
				$code_statut = 'color: red';
			} else {
				$code_statut = 'color:blue';
			}

			print '<tr class="oddeven">';

			if (! empty($arrayfields['c.rowid']['checked'])) {
				$rentstatic->id = $obj->contract_id;
				$rentstatic->nom = $obj->contract_id;
				print '<td>' . $rentstatic->getNomUrl(1);
			}

			if (! empty($arrayfields['loc.nom']['checked'])) {
				print '<td align="left" style="' . $code_statut . '">';
				print '<a href="../renter/card.php?id=' . $obj->renter_id . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($obj->renter_lastname) . ' ' . ucfirst($obj->renter_lastname) . '</a>';		
				print '</td>';
			}

			if (! empty($arrayfields['l.name']['checked'])) {
				$propertystatic->id = $obj->property_id;
				$propertystatic->name = stripslashes(nl2br($obj->property_name));
				print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
			}

			if (! empty($arrayfields['c.montant_tot']['checked'])) {
				print '<td align="right">' . price($obj->amount_total) . '</td>';
			}

			if (! empty($arrayfields['c.encours']['checked'])) {
				print '<td>' . price($obj->encours) . '</td>';
			}

			if (! empty($arrayfields['c.statut']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->statut)) . '</td>';
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
		print '<tr class="oddeven">'.'<td colspan="9" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
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
