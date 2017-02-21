<?php
/* Copyright (C) 2013-2015 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015      Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
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
 * \file immobilier/receipt/list.php
 * \ingroup immobilier
 * \brief List of rent
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
	// Class
dol_include_once("/immobilier/class/immoreceipt.class.php");
dol_include_once("/immobilier/class/renter.class.php");
dol_include_once("/immobilier/class/immoproperty.class.php");
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once '../class/html.formimmobilier.class.php';
dol_include_once('/immobilier/class/immorent.class.php');
require_once '../class/immoproperty.class.php';

// Langs

$action = GETPOST('action', 'alpha');
$mesg = '';
$action = GETPOST('action');
$cancel = GETPOST('cancel');
$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'int');
$search_ref = GETPOST("search_ref");
$search_renter = GETPOST('search_renter');
$search_property = GETPOST('search_property');
$search_rent = GETPOST('search_rent');
// Security check
if ($user->societe_id > 0)
	accessforbidden();
// Load variable for pagination	
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield)
	$sortfield = "t.date_rent";
if (! $sortorder)
	$sortorder = "DESC";

/*
 * Actions
 */

if ($action == 'validaterent') {
	
	$error = 0;
	
	$db->begin();
	
	$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt as lo ";
	$sql1 .= " SET lo.paiepartiel=";
	$sql1 .= "(SELECT SUM(p.amount)";
	$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_payment as p";
	$sql1 .= " WHERE lo.rowid = p.fk_receipt";
	$sql1 .= " GROUP BY p.fk_receipt )";
	
	// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
	$resql1 = $db->query($sql1);
	if (! $resql1) {
		$error ++;
		setEventMessage($db->lasterror(), 'errors');
	} else {
		
		$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
		$sql1 .= " SET paye=1";
		$sql1 .= " WHERE amount_total=paiepartiel";
		
		// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
		$resql1 = $db->query($sql1);
		if (! $resql1) {
			$error ++;
			setEventMessage($db->lasterror(), 'errors');
		}
		
		if (! $error) {
			$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_receipt ";
			$sql1 .= " SET balance=amount_total-paiepartiel";
			
			// dol_syslog ( get_class ( $this ) . ":: loyer.php action=" . $action . " sql1=" . $sql1, LOG_DEBUG );
			$resql1 = $db->query($sql1);
			if (! $resql1) {
				$error ++;
				setEventMessage($db->lasterror(), 'errors');
			}
			
			if (! $error) {
				$sql1 = "UPDATE " . MAIN_DB_PREFIX . "immo_contrat as ic";
				$sql1 .= " SET ic.encours=";
				$sql1 .= "(SELECT SUM(il.balance)";
				$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as il";
				$sql1 .= " WHERE ic.rowid = il.fk_contract";
				$sql1 .= " GROUP BY il.fk_contract )";
				
				$resql1 = $db->query($sql1);
			if (! $resql1) {
				$error ++;
				setEventMessage($db->lasterror(), 'errors');
			}
				
				$db->commit();
				
				setEventMessage('Loyer mis a jour avec succes', 'mesgs');
			}
		} else {
			$db->rollback();
			setEventMessage($db->lasterror(), 'errors');
		}
	}
}

if ($action == 'delete') {
$formconfirm = $html->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $id, $langs->trans('DeleteReceipt'), $langs->trans('ConfirmDeleteReceipt'), 'confirm_delete', '', 0, 1);
	print $formconfirm;
}

/*
 *	Delete rental
 */
if ($action == 'confirm_delete' && $_REQUEST["confirm"] == 'yes') {
	$receipt = new Immoreceipt($db);
	$receipt->fetch($id);
	$result = $receipt->delete($user);
	if ($result > 0) {
		header("Location: list.php");
		exit();
	} else {
		$mesg = '<div class="error">' . $receipt->error . '</div>';
	}
}

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") ||GETPOST("button_removefilter")) // All test are required to be compatible with all browsers
{
	$search_renter = "";
	$search_property = "";
	$search_rent = "";
	}
	
$filter = array ();
if (! empty($search_renter)) {
	$filter['lc.nom'] = $search_renter;
	$param .= "&amp;search_renter=" . urlencode($search_renter);
}
if (! empty($search_property)) {
	$filter['ll.name'] = $search_property;
	$param .= "&amp;search_property=" . urlencode($search_property);
}
if (! empty($search_rent)) {
	$filter['t.name'] = $search_rent;
	$param .= "&amp;search_rent=" . urlencode($search_rent);
}


/*
 * View
 */

$form = new Form($db);
$object = new Immoreceipt($db);
$form_loyer = new Immoreceipt($db);

llxHeader('', $langs->trans("Receipt"));

	$sql = "SELECT t.rowid as receipt_id, t.fk_contract, t.fk_property, t.name as name, t.fk_renter, t.amount_total as amount_total, t.rent as rent, t.balance,";
	$sql .= " t.paiepartiel as paiepartiel, t.charges, t.vat, t.echeance as echeance, t.commentaire, t.statut as receipt_statut, t.date_rent,";
	$sql .= " t.date_start, t.date_end, t.fk_owner, t.paye as paye, lc.rowid as renter_id, lc.nom as nomlocataire, lc.prenom as prenomlocataire,";
	$sql .= " ll.name as nomlocal, ll.rowid as property_id, soc.rowid as soc_id, soc.nom as owner_name";
	$sql .= ' FROM llx_immo_receipt as t';
	$sql .= ' INNER JOIN llx_immo_renter as lc ON t.fk_renter = lc.rowid';
	$sql .= ' INNER JOIN llx_immo_property as ll ON t.fk_property = ll.rowid';
	$sql .= ' LEFT JOIN llx_societe as soc ON soc.rowid = t.fk_owner';
	if (strlen(trim($search_renter)))			$sql .= natural_search("lc.nom", $search_renter);
	$sql .= $db->order($sortfield, $sortorder);
// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
}	
$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);
    $params='';
	
	print_barre_liste($texte, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_receipt');
	print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
	
	print '<table class="noborder" width="100%">';
	print "<tr class=\"liste_titre\">";
	print_liste_field_titre($langs->trans('Reference'), $_SERVER['PHP_SELF'], 't.rowid', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('Renter'), $_SERVER['PHP_SELF'], 'lc.nom', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('Property'), $_SERVER['PHP_SELF'], 'll.name', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('Rent'), $_SERVER['PHP_SELF'], 't.name', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('Echeance'), $_SERVER['PHP_SELF'], 't.echeance', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Montant total"), $_SERVER["PHP_SELF"], 't.amount_total', '',$param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("re&ccedilu"), $_SERVER["PHP_SELF"], "t.paiepartiel", "", $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Paiement"), $_SERVER["PHP_SELF"], "t.paye", "", $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Owner"), $_SERVER["PHP_SELF"], "t.fk_owner", "", $param, '', $sortfield, $sortorder);
	print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'maxwidthsearch ');
	print "</tr>\n";
	
// Filters
	print '<tr class="liste_titre">';
	
	print '<td class=liste_titre"></td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_renter" value="' . $search_renter . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_property" value="' . $search_property . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="7" type="text" name="search_name" value="' . $search_rent . '">';
	print '</td>';
	
	print '<td class=liste_titre"></td>';
	print '<td class=liste_titre"></td>';
	print '<td class=liste_titre"></td>';
	print '<td class=liste_titre"></td>';
	print '<td class=liste_titre"></td>';
	
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("Search"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
	print '</td>';
	
	$var = true;
	
	$receiptstatic = new Immoreceipt($db);
	
	$thirdparty_static = new Societe($db);
	
	 $contrat = new Rent($db);
	 
	 $propertystatic = new Immoproperty($db);
	
	while ( $i < min($num, $limit) ) 
	{
		$obj = $db->fetch_object($resql);
			
			$receiptstatic->id = $obj->receipt_id;
			$receiptstatic->name = $obj->name;
			
			$var = ! $var;
			print "<tr " . $bc[$var] . ">";
			print '<td>' . $receiptstatic->getNomUrl(1) . '</td>';
			
			print '<td align="left" style="' . $code_statut . '">';
			print '<a href="../renter/card.php?id=' . $obj->renter_id . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($obj->nomlocataire) . ' ' . ucfirst($obj->nomlocataire) . '</a>';		
			print '</td>';
			
			$propertystatic->id = $obj->property_id;
			$propertystatic->name = stripslashes(nl2br($obj->nomlocal));
			print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
			print '<td>' . stripslashes(nl2br($obj->name)) . '</td>';
	// due date
		
		print '<td>' . dol_print_date($obj->echeance, 'day') . '</td>';
		
		// amount
		
		print '<td align="left" width="100">' . price($obj->amount_total) . '</td>';
		print '<td align="left" width="100">' . price($obj->paiepartiel) . '</td>';
		
		// Affiche statut de la facture
		print '<td align="right" nowrap="nowrap">';
		print $receiptstatic->LibStatut($obj->paye, 5);
		print "</td>";
		
		$thirdparty_static->id=$obj->fk_owner;
		$thirdparty_static->name=$obj->owner_name;
		print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
			
			
			
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
	} else {
		print '<tr ' . $bc[false] . '>' . '<td colspan="7">' . $langs->trans("NoRecordFound") . '</td></tr>';
		dol_print_error($db);
	}
	print "</table>";
	
	print "</form>";
	$db->free($resql);


llxFooter();

$db->close();
