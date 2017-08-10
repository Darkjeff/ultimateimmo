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
 * \file    	immobilier/property/list.php
 * \ingroup 	Immobilier
 * \brief   	List of property
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
dol_include_once('/immobilier/class/immoproperty.class.php');
dol_include_once('/core/lib/company.lib.php');

$langs->load("immobilier@immobilier");

$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');

// Security check
if ($user->societe_id > 0) 	accessforbidden ();
if (! $user->rights->immobilier->property->read)
	accessforbidden();

// Load variable for pagination
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');
if ($page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) $sortorder="ASC";
if (!$sortfield) $sortfield="l.name";


$search_property	= GETPOST('search_property', 'alpha');

$arrayfields=array(
	'l.name'=>array('label'=>$langs->trans("Property"), 'checked'=>1),
    'l.address'=>array('label'=>$langs->trans("address"), 'checked'=>1),
    'l.zip'=>array('label'=>$langs->trans("zip"), 'checked'=>1),
    'l.town'=>array('label'=>$langs->trans("town"), 'checked'=>1),
    'co.label'=>array('label'=>$langs->trans("country"), 'checked'=>1),
    'tp.label'=>array('label'=>$langs->trans("TypeProperty"), 'checked'=>1),
    'b.name'=>array('label'=>$langs->trans("Building"), 'checked'=>1),
    'soc.nom'=>array('label'=>$langs->trans("Owner"), 'checked'=>1),
    'l.statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1)
);

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Purge search criteria
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter"))
{
	
	$search_property = "";
	$search_array_options=array();
}

/*
 * View
 */
$form = new Form($db);
$object = new Immoproperty($db);

llxHeader('', $langs->trans("Property"));

$sql = "SELECT l.rowid as property_id, l.name, l.address, l.zip, l.town"; 
$sql .= ",  l.statut, tp.label as type_property, co.label as country";
$sql .= ", l.name as property_name, l.fk_owner, b.name as building_name, soc.nom as owner";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_property as l";
$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'c_immo_type_property as tp ON tp.id = l.fk_type_property';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'immo_building as b ON b.rowid = l.fk_property';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'societe as soc ON soc.rowid = l.fk_owner';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_country as co ON co.rowid = l.fk_pays';
$sql .= " WHERE tp.id <> 6";
if ($search_property)	$sql .= natural_search("l.name", $search_property);
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
	if ($search_property)	$params.= '&amp;search_property='.urlencode($search_property);
    if ($optioncss)			$param.='&optioncss='.$optioncss;

	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

	$title = $langs->trans("ListProperties");
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_property');

	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields

    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['l.name']['checked']))		print_liste_field_titre($arrayfields['l.name']['label'], $_SERVER["PHP_SELF"],"l.name","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['l.address']['checked']))		print_liste_field_titre($arrayfields['l.address']['label'], $_SERVER["PHP_SELF"],"l.address","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['l.zip']['checked']))			print_liste_field_titre($arrayfields['l.zip']['label'], $_SERVER["PHP_SELF"],"l.zip", "", $param,'align="left"',$sortfield,$sortorder);
	if (! empty($arrayfields['l.town']['checked']))	print_liste_field_titre($arrayfields['l.town']['label'],$_SERVER["PHP_SELF"],'l.town','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['co.label']['checked']))		print_liste_field_titre($arrayfields['co.label']['label'],$_SERVER["PHP_SELF"],'co.label','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['tp.label']['checked']))		print_liste_field_titre($arrayfields['tp.label']['label'],$_SERVER["PHP_SELF"],'tp.label','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['b.name']['checked']))		print_liste_field_titre($arrayfields['b.name']['label'],$_SERVER["PHP_SELF"],'b.name','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['soc.nom']['checked']))		print_liste_field_titre($arrayfields['soc.nom']['label'],$_SERVER["PHP_SELF"],'soc.nom','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['l.statut']['checked']))		print_liste_field_titre($arrayfields['l.statut']['label'],$_SERVER["PHP_SELF"],'l.statut','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
	print "</tr>\n";

	// Filters
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['l.name']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="20" name="search_property" value="' .$search_property. '"></td>';
	if (! empty($arrayfields['l.address']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['l.zip']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['l.town']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['co.label']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['tp.label']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['b.name']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['soc.nom']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['l.statut']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	
	// Action column
	print '<td class="liste_titre" align="middle">';
	$searchpicto=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
	print $searchpicto;
	print '</td>';

	print "</tr>\n";

	$var = true;

	$propertystatic = new Immoproperty($db);
	$thirdparty_static = new Societe($db);

	if ($num > 0)
	{
        $i=0;
    	$var=true;
		while ( $i < min($num, $limit) ) 
		{
			$obj = $db->fetch_object($resql);
			$code_statut = '';

			if ($objp->status == 1 ){
				$code_statut = 'color: red';
			} else {
				$code_statut = 'color:blue';
			}

			$var = ! $var;
			print "<tr ".$bc[$var].">";

			if (! empty($arrayfields['l.name']['checked'])) {
				$propertystatic->id = $obj->property_id;
				$propertystatic->name = stripslashes(nl2br($obj->property_name));
				print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
			}
			if (! empty($arrayfields['l.address']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->address)) . '</td>';
			}
			if (! empty($arrayfields['l.zip']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->zip)) . '</td>';
			}
			if (! empty($arrayfields['l.town']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->town)) . '</td>';
			}
			if (! empty($arrayfields['co.label']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->country)) . '</td>';
			}
			if (! empty($arrayfields['tp.label']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->type_property)) . '</td>';
			}
			if (! empty($arrayfields['b.name']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->building)) . '</td>';
			}
			if (! empty($arrayfields['soc.nom']['checked'])) {
				//print '<td>' . stripslashes(nl2br($obj->owner)) . '</td>';
				$thirdparty_static->id=$obj->fk_owner;
				$thirdparty_static->name=$obj->owner;
				print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
			}
			
			if (! empty($arrayfields['l.statut']['checked'])) {
			
				print '<td align="right" nowrap="nowrap">';
				print $propertystatic->LibStatut($obj->statut, 5);
				print "</td>";
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
