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
dol_include_once ( "/immobilier/class/rent.class.php" );
require_once '../class/immoproperty.class.php';


$action = GETPOST('action', 'alpha');

// validateRent

// write income

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
				$sql1 .= "(SELECT SUM(il.solde)";
				$sql1 .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as il";
				$sql1 .= " WHERE ic.rowid = il.fk_contrat";
				$sql1 .= " GROUP BY il.fk_contrat )";
				
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


// Securite acces client
if ($user->societe_id > 0)
	accessforbidden();
	
$search_renter = GETPOST('search_renter');
$search_property = GETPOST('search_property');
$search_rent = GETPOST('search_rent');

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) // Both test must be present to be compatible with all browsers
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

$sortorder = GETPOST("sortorder");
$sortfield = GETPOST("sortfield");
$page = GETPOST("page");
if (! $sortorder)
	$sortorder = "DESC";
if (! $sortfield)
	$sortfield = "t.echeance";

if ($page == - 1) {
	$page = 0;
}

$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
$limit = $conf->liste_limit;

if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$nbtotalofrecords = $object->fetchAll($sortorder, $sortfield, 0, 0, $filter);
} else {
	$nbtotalofrecords = 0;
}

$result = $object->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);
if ($result < 0) {
	setEventMessages(null, $object->errors, 'errors');
} else {
	
	print_barre_liste($langs->trans("ListReceipt"), $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $result, $nbtotalofrecords);
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
	
	 $contrat = new Rent ( $db);
	 
	 $propertystatic = new Immoproperty($db);
	
	if (count($object->lines) > 0) {
		foreach ( $object->lines as $line ) {
			
			$receiptstatic->id = $line->id;
			$receiptstatic->name = $line->name;
			
			$var = ! $var;
			print "<tr " . $bc[$var] . ">";
			print '<td>' . $receiptstatic->getNomUrl(1) . '</td>';
			
			print '<td align="left" style="' . $code_statut . '">';
			print '<a href="../renter/card.php?id=' . $line->renter_id . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($line->nomlocataire) . ' ' . strtoupper($line->prenomlocataire) . '</a>';		
			print '</td>';
			
			$propertystatic->id = $line->property_id;
			$propertystatic->name = stripslashes(nl2br($line->nomlocal));
			print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
			print '<td>' . stripslashes(nl2br($line->name)) . '</td>';
	// due date
		
		print '<td>' . dol_print_date($line->echeance, 'day') . '</td>';
		
		// amount
		
		print '<td align="left" width="100">' . price($line->amount_total) . '</td>';
		print '<td align="left" width="100">' . price($line->paiepartiel) . '</td>';
		
		// Affiche statut de la facture
		print '<td align="right" nowrap="nowrap">';
		print $receiptstatic->LibStatut($line->paye, 5);
		print "</td>";
		
		$thirdparty_static->id=$line->fk_owner;
		$thirdparty_static->name=$line->owner_name;
		print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
			
			
			
			print '<td align="center">';
		if ($user->admin) {
			print '<a href="./list.php?action=delete&id=' . $line->id . '">';
			print img_delete();
			print '</a>';
		}
		print '</td>' . "\n";
			
			print "</tr>\n";
			
			$i ++;
		}
	} else {
		print '<tr ' . $bc[false] . '>' . '<td colspan="7">' . $langs->trans("NoRecordFound") . '</td></tr>';
	}
	print "</table>";
	
	print "</form>";
	$db->free($resql);
}

llxFooter();

$db->close();
