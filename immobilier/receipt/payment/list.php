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
 * \file	immobilier/receipt/payment/list.php
 * \ingroup immobilier
 * \brief	List of payment receipt
 */

// Class
$res = @include ("../../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
dol_include_once("/immobilier/class/immoreceipt.class.php");
dol_include_once("/immobilier/class/immoproperty.class.php");
dol_include_once("/immobilier/class/html.formimmobilier.class.php");
dol_include_once("/immobilier/class/immorent.class.php");
dol_include_once("/immobilier/class/immorenter.class.php");
dol_include_once("/immobilier/class/immopayment.class.php");


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
if (!$sortfield) $sortfield="t.date_payment";

$search_renter 		= GETPOST('search_renter','alpha');
$search_property	= GETPOST('search_property','alpha');
$search_namerent		= GETPOST('search_namerent','alpha');
$search_amount		= GETPOST('search_amount','alpha');

$arrayfields=array(
	't.rowid'=>array('label'=>$langs->trans("Reference"), 'checked'=>1),
    'lc.nom'=>array('label'=>$langs->trans("Renter"), 'checked'=>1),
    'll.name'=>array('label'=>$langs->trans("Property"), 'checked'=>1),
	'lo.name'=>array('label'=>$langs->trans("Receipt"), 'checked'=>1),
    't.date_payment'=>array('label'=>$langs->trans("DatePayment"), 'checked'=>1),
    't.amount'=>array('label'=>$langs->trans("Amount"), 'checked'=>1),
    't.comment'=>array('label'=>$langs->trans("Comment"), 'checked'=>1)
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
    $search_renter = "";
	$search_property = "";
	$search_namerent = "";
	$search_amount = "";
    $search_array_options=array();
}


if ($action == 'delete') {
$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $id, $langs->trans('DeletePayment'), $langs->trans('ConfirmDeletePayment'), 'confirm_delete', '', 0, 1);
	print $formconfirm;
}

/*
 *	Delete Payment
 */
if ($action == 'confirm_delete' && $_REQUEST["confirm"] == 'yes') {
	$payment = new Immopayment($db);
	$payment->fetch($id);
	$result = $payment->delete($user);
	if ($result > 0) {
		header("Location: list.php");
		exit();
	} else {
		$mesg = '<div class="error">' . $payment->error . '</div>';
	}
}

/*
 * View
 */


$form = new Form($db);
$object = new Immopayment($db);

llxHeader('', $langs->trans("Payments"));

		$sql = 'SELECT t.rowid as reference, t.fk_contract, t.fk_property, t.fk_renter,';
		$sql .= " t.amount, t.comment, t.date_payment as date_payment, t.fk_owner,";
		$sql .= " t.fk_receipt as receipt_id, lc.rowid as renter_id, lc.nom as nomlocataire, lc.prenom as prenomlocataire , ll.rowid as property_id, ll.name as nomlocal , lo.name as nomloyer ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_payment as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_renter as lc ON t.fk_renter = lc.rowid";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_property as ll ON t.fk_property = ll.rowid ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_receipt as lo ON t.fk_receipt = lo.rowid";
	if ($search_renter)			$sql .= natural_search("lc.nom", $search_renter);
	if ($search_property)		$sql .= natural_search("ll.name", $search_property);
	if ($search_namerent)		$sql .= natural_search("lo.name", $search_namerent);
	if ($search_amount)			$sql .= natural_search("t.amount", $search_amount);
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
	if ($search_namerent)		$params.= '&amp;search_namerent='.urlencode($search_namerent);
	if ($search_amount)		$params.= '&amp;search_amount='.urlencode($search_amount);
    if ($optioncss)			$param.='&optioncss='.$optioncss;

    print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
    if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

	$title = $langs->trans("ListPayment");
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_payment');
	
	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
	
    print '<div class="div-table-responsive">';
    print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['t.rowid']['checked']))		print_liste_field_titre($arrayfields['t.rowid']['label'], $_SERVER["PHP_SELF"],"t.rowid","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['lc.nom']['checked']))			print_liste_field_titre($arrayfields['lc.nom']['label'], $_SERVER["PHP_SELF"],"lc.nom","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['ll.name']['checked']))		print_liste_field_titre($arrayfields['ll.name']['label'], $_SERVER["PHP_SELF"],"ll.name", "", $param,'align="left"',$sortfield,$sortorder);
	if (! empty($arrayfields['lo.name']['checked']))			print_liste_field_titre($arrayfields['lo.name']['label'],$_SERVER["PHP_SELF"],'lo.name','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.date_payment']['checked']))		print_liste_field_titre($arrayfields['t.date_payment']['label'],$_SERVER["PHP_SELF"],'t.date_payment','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.amount']['checked']))	print_liste_field_titre($arrayfields['t.amount']['label'],$_SERVER["PHP_SELF"],'t.amount','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['t.comment']['checked']))	print_liste_field_titre($arrayfields['t.comment']['label'],$_SERVER["PHP_SELF"],'t.comment','',$param,'',$sortfield,$sortorder);
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
	print "</tr>\n";
	
	// Filters
	print '<tr class="liste_titre">';
	if (! empty($arrayfields['t.rowid']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['lc.nom']['checked']))			print '<td class="liste_titre"><input type="text" class="flat" size="20" name="search_renter" value="' .$search_renter. '"></td>';
	if (! empty($arrayfields['ll.name']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="10" name="search_property" value="' .$search_property. '"></td>';
	if (! empty($arrayfields['lo.name']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="10" name="search_namerent" value="' .$search_namerent. '"></td>';
	if (! empty($arrayfields['t.date_payment']['checked']))	print '<td class="liste_titre">&nbsp;</td>';
	if (! empty($arrayfields['t.amount']['checked']))		print '<td class="liste_titre"><input type="text" class="flat" size="10" name="search_amount" value="' .$search_amount. '"></td>';
	if (! empty($arrayfields['t.comment']['checked']))		print '<td class="liste_titre">&nbsp;</td>';
	
	
	// Action column
	print '<td class="liste_titre" align="middle">';
	$searchpitco=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
	print $searchpitco;
	print '</td>';

	print "</tr>\n";

	$var = true;
	
	$receiptstatic = new Immoreceipt($db);
	$thirdparty_static = new Societe($db);
	$contratstatic = new Rent($db);
	$propertystatic = new Immoproperty($db);
	$payment = new Immopayment($db);

	if ($num > 0)
	{
        $i=0;
    	$var=true;
		while ( $i < min($num, $limit) ) 
		{
			$obj = $db->fetch_object($resql);
	
			$payment->id = $obj->reference;
			

			$var = ! $var;
			print "<tr " . $bc[$var] . ">";


			
			if (! empty($arrayfields['t.rowid']['checked'])) {
				print '<td>' ;
				print "<a href=" . DOL_URL_ROOT . "/custom/immobilier/receipt/payment/card.php?action=update&id=" . $obj->reference . "&amp;receipt=" . $obj->receipt_id . ">" . img_object($langs->trans("Payment"), "payment") . " ". $obj->reference . "</a> ";
				print '</td>';
			}

			if (! empty($arrayfields['lc.nom']['checked'])) {
				print '<td align="left" style="' . $code_statut . '">';
				print '<a href="../../renter/card.php?id=' . $obj->renter_id . '">' . img_object($langs->trans("ShowDetails"), "user") . '  ' . ucfirst($obj->nomlocataire) . '</a>';		
				print '</td>';
			}

			if (! empty($arrayfields['ll.name']['checked'])) {
				$propertystatic->id = $obj->property_id;
				$propertystatic->name = stripslashes(nl2br($obj->nomlocal));
				print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
			}

			if (! empty($arrayfields['lo.name']['checked'])) {
				$receiptstatic->id = $obj->receipt_id;
				$receiptstatic->name = stripslashes(nl2br($obj->nomloyer));
				print '<td>' . $receiptstatic->getNomUrl(1) . ' '. $obj->nomloyer . '</td>';
			}

			// Due date
			if (! empty($arrayfields['t.date_payment']['checked'])) {
				print '<td>' . dol_print_date($obj->date_payment, 'day') . '</td>';
			}

			// Amount
			if (! empty($arrayfields['t.amount']['checked'])) {
				print '<td align="right">' . price($obj->amount) . '</td>';
			}
			
			if (! empty($arrayfields['t.paiepartiel']['checked'])) {
				print '<td align="right">' . price($obj->paiepartiel) . '</td>';
			}

			if (! empty($arrayfields['t.comment']['checked'])) {
				print '<td align="right">' .$obj->comment . '</td>';
			}
		

			print '<td align="center">';
			if ($user->admin) {
				print '<a href="./list.php?action=delete&id=' . $obj->reference . '">';
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