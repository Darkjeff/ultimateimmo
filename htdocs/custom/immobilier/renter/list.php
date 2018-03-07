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
 * \file    	immobilier/renter/list.php
 * \ingroup 	Immobilier
 * \brief   	List of renter
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
dol_include_once('/immobilier/class/immorenter.class.php');
dol_include_once('/core/lib/company.lib.php');

$langs->load("immobilier@immobilier");

$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');

// Security check
if ($user->societe_id > 0) 	accessforbidden ();
if (! $user->rights->immobilier->renter->read)
	accessforbidden();

// Load variable for pagination
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');
if (empty($page) || $page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) $sortorder="ASC";
if (!$sortfield) $sortfield="s.rowid";


$search_property	= GETPOST('search_property', 'alpha');

$arrayfields=array(
	's.nom'=>array('label'=>$langs->trans("Renter"), 'checked'=>1),
    's.tel1'=>array('label'=>$langs->trans("Phone"), 'checked'=>1),
    's.tel2'=>array('label'=>$langs->trans("PhoneMobile"), 'checked'=>1),
    's.mail'=>array('label'=>$langs->trans("Email"), 'checked'=>1),
    's.statut'=>array('label'=>$langs->trans("Statut"), 'checked'=>1),
    's.owner'=>array('label'=>$langs->trans("Owner"), 'checked'=>0)
   );

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Purge search criteria
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter"))
{
	
	$search_renter = "";
	$search_array_options=array();
}

/*
 * View
 */
$form = new Form($db);
$object = new ImmoRenter($db);

llxHeader('', $langs->trans("Renter"));

$sql = "SELECT";
$sql .= " so.rowid as socid, so.nom as socname,";
$sql .= " civ.code as civilitecode,";
$sql .= " s.rowid as renter_id, s.nom as renter_lastname, s.prenom, s.civilite, s.fk_soc, s.fonction, s.statut,";
$sql .= " s.tel1 as phone_pro, s.tel2 as phone_mobile, s.mail as email, s.note, s.date_birth, s.place_birth";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter as s";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
$sql .= " ON s.fk_soc = so.rowid";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_civility as civ";
$sql .= " ON s.civilite = civ.code";
//$sql .= " WHERE s.statut > 0";
if ($search_renter)	$sql .= natural_search("s.nom", $search_property);
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
	if ($search_renter)	$params.= '&amp;search_renter='.urlencode($search_renter);
    if ($optioncss)			$param.='&optioncss='.$optioncss;

	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	print '<input type="hidden" name="page" value="'.$page.'">';

	$title = $langs->trans("ListRenters");
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_renter', 0, '', '', $limit);

	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields

    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['s.nom']['checked']))		print_liste_field_titre($arrayfields['s.nom']['label'], $_SERVER["PHP_SELF"],"s.nom","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['s.tel1']['checked']))		print_liste_field_titre($arrayfields['s.tel1']['label'], $_SERVER["PHP_SELF"],"s.tel1","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['s.tel2']['checked']))			print_liste_field_titre($arrayfields['s.tel2']['label'], $_SERVER["PHP_SELF"],"s.tel2", "", $param,'align="left"',$sortfield,$sortorder);
	if (! empty($arrayfields['s.mail']['checked']))	print_liste_field_titre($arrayfields['s.mail']['label'],$_SERVER["PHP_SELF"],'s.mail','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['s.statut']['checked']))		print_liste_field_titre($arrayfields['s.statut']['label'],$_SERVER["PHP_SELF"],'s.statut','',$param,'align="right"',$sortfield,$sortorder);
	if (! empty($arrayfields['s.owner']['checked']))		print_liste_field_titre($arrayfields['s.owner']['label'],$_SERVER["PHP_SELF"],'s.owner','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
	print "</tr>\n";

	// Filters
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['s.nom']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="20" name="search_renter" value="' .$search_renter. '"></td>';
	if (! empty($arrayfields['s.tel1']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['s.tel2']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['s.mail']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['s.statut']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['s.owner']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
		
	// Action column
	print '<td class="liste_titre" align="middle">';
	$searchpicto=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
	print $searchpicto;
	print '</td>';

	print "</tr>\n";

	$renterstatic = new ImmoRenter($db);

	if ($num > 0)
	{
        $i=0;
		while ( $i < min($num, $limit) ) 
		{
			$obj = $db->fetch_object($resql);
			$code_statut = '';

			if ($objp->status == 1 ){
				$code_statut = 'color: red';
			} else {
				$code_statut = 'color:blue';
			}

			print '<tr class="oddeven">';

			if (! empty($arrayfields['s.nom']['checked'])) {
				print '<td align="left" style="' . $obj->nom . '">';
				print '<a href="../renter/card.php?id=' . $obj->renter_id . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($obj->renter_lastname) . '</a>';		
				print '</td>';
				}
			if (! empty($arrayfields['s.tel1']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->phone_pro)) . '</td>';
			}
			if (! empty($arrayfields['s.tel2']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->phone_mobile)) . '</td>';
			}
			if (! empty($arrayfields['s.mail']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->email)) . '</td>';
			}
			if (! empty($arrayfields['s.statut']['checked'])) {
				print '<td align="right" nowrap="nowrap">';
				print $renterstatic->LibStatut($obj->statut, 5) . '</td>';
			}
			if (! empty($arrayfields['s.owner']['checked'])) {
				print '<td>' . stripslashes(nl2br($obj->owner)) . '</td>';
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
