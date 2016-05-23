<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
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
 * \file htdocs/compta/ventilation/card.php
 * \ingroup compta
 * \brief Page fiche ventilation
 */
// Dolibarr environment
$res = @include ("../../main.inc.php");
if (! $res && file_exists("../../main.inc.php"))
	$res = @include ("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php"))
	$res = @include ("../../../main.inc.php");
if (! $res && file_exists("../../../../main.inc.php"))
	$res = @include ("../../../../main.inc.php");
if (! $res)
	die("Include of main fails");

dol_include_once("/immobilier/class/immopayment.class.php");
dol_include_once("/immobilier/class/immoreceipt.class.php");

// Langs
$langs->load("immobilier@immobilier");

$mesg = '';
$id = GETPOST('id', 'int');
$receipt_id = GETPOST('receipt', 'int');
$action = GETPOST('action');

// Actions

if (GETPOST("action") == 'add') {
	$datepaie = @dol_mktime($_POST["paiehour"], $_POST["paiemin"], $_POST["paiesec"], $_POST["paiemonth"], $_POST["paieday"], $_POST["paieyear"]);
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie")) . '</div>';
		$action = 'create';
	} else {
		$paie = new Immopayment($db);
		
		$paie->fk_contract = $_POST["fk_contract"];
		$paie->fk_property = $_POST["fk_property"];
		$paie->fk_renter = $_POST["fk_renter"];
		$paie->amount = $_POST["amount"];
		$paie->comment = $_POST["comment"];
		$paie->date_payment = $datepaie;
		$paie->fk_receipt = $_POST["fk_receipt"];
		$paie->fk_owner = $user->id;
		
		$id = $paie->create($user);
		header("Location: " . DOL_URL_ROOT . "/custom/immobilier/receipt/card.php?id=" . $paie->fk_receipt);
		if ($id > 0) {
		} else {
			$mesg = '<div class="error">' . $paie->error . '</div>';
		}
	}
}

/**
*	Delete paiement
**********/

if (GETPOST("action") == 'delete') {
	
	if ($id){
		$paie = new Immopayment($db);
		
		
		$paie->id = $id;
		
		$id = $paie->delete($user);
		
	}
	
	header("Location: " . DOL_URL_ROOT . "/custom/immobilier/receipt/card.php?id=" . $receipt_id);
}


/***** Update ******/

if (GETPOST("action") == 'maj') {
	$datepaie = @dol_mktime($_POST["paiehour"], $_POST["paiemin"], $_POST["paiesec"], $_POST["paiemonth"], $_POST["paieday"], $_POST["paieyear"]);
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie")) . '</div>';
		$action = 'update';
	} else {
		$paie = new Immopayment($db);
		
		$result = $paie->fetch($id);
		
		$paie->amount = $_POST["amount"];
		$paie->comment = $_POST["comment"];
		$paie->date_payment = $datepaie;
		
		$result = $paie->update($user);
		header("Location: " . DOL_URL_ROOT . "/custom/immobilier/receipt/card.php?id=" . $receipt_id);
		
	}
}

if (GETPOST("action") == 'addall') {
	$datepaie = @dol_mktime($_POST["paiehour"], $_POST["paiemin"], $_POST["paiesec"], $_POST["paiemonth"], $_POST["paieday"], $_POST["paieyear"]);
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie")) . '</div>';
		$action = 'createall';
	} else {
	$datapost = $_POST;
	foreach ( $datapost as $key => $value ) {
		if (strpos($key, 'receipt_') !== false) {
		
			$tmp_array = explode('_', $key);
			
			if (count($tmp_array) > 0) {
				$reference = $tmp_array[1];
				$amount= GETPOST('incomeprice_'.$reference);
			
				if (! empty($reference) && !empty($amount)) {
					$paie = new Immopayment($db);

					$paie->fk_contract = GETPOST('fk_contract_'.$reference);
					$paie->fk_property = GETPOST('fk_property_'.$reference);
					$paie->fk_renter = GETPOST('fk_renter_'.$reference);
					$paie->amount =price2num($amount);
					$paie->comment = GETPOST('comment');
					$paie->date_payment = $datepaie;
					$paie->fk_receipt = GETPOST('receipt_'.$reference);
					$paie->fk_owner = $user->id;
					
					$result = $paie->create ($user);

					if ($result<0) {
						setEventMessages($paie->error, null, 'errors');
					}
				}
			}
		}
	}
	
	
		
	}
}

/*
 * View
 *
 */

$form = new Form($db);

llxheader('', $langs->trans("newpaiement"), '');

if (GETPOST("action") == 'create') {
	$receipt = new Immoreceipt($db);
	$result = $receipt->fetch($id);
	
	print '<form action="card.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<input type="hidden" name="fk_contract" size="10" value="' . $receipt->fk_contract . '">';
	print '<input type="hidden" name="fk_property" size="10" value="' . $receipt->fk_property . '">';
	print '<input type="hidden" name="fk_renter" size="10" value="' . $receipt->fk_renter . '">';
	print '<input type="hidden" name="fk_receipt" size="10" value="' . $id . '">';
	
	print '<tr><td width="20%">' . $langs->trans("NomAppartement") . '</td><td>' . $receipt->nomlocal . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("NomLocataire") . '</td><td>' . $receipt->nomlocataire . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("RefLoyer") . '</td><td>' . $receipt->nom . '</td></tr>';
	;
	
	print '<tr><td width="20%">' . $langs->trans("amount") . '</td>';
	print '<td><input name="amount" size="30" value="' . $paie->amount . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("Commentaire") . '</td>';
	print '<td><input name="comment" size="10" value="' . $paie->comment . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("DatePaiement") . '</td>';
	print '<td align="left">';
	print $form->select_date(! empty($datepaie) ? $datepaie : '-1', 'paie', 0, 0, 0, 'card', 1);
	print '</td>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans("Sauvegarder") . '"><input type="cancel" class="button" value="' . $langs->trans("Cancel") . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

/* *************************************************************************** */
/*                                                                             */
/* Mode add all payments                                                                 */
/*                                                                             */
/* *************************************************************************** */

if ($action == 'createall') {

	print '<form name="fiche_payment" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addall">';
	
	print '<table class="border" width="100%">';

	print "<tr class=\"liste_titre\">";
	
	print '<td align="left">';
	print $langs->trans("DatePayment");
	print '</td><td align="center">';
	print $langs->trans("Comment");
	print '</td><td align="left">';
	print '&nbsp;';
	print '</td>';
	print "</tr>\n";
	
	print '<tr ' . $bc[$var] . ' valign="top">';
	
	// Due date
	
	print '<td align="center">';
	print $form->select_date(! empty($datepaie) ? $datepaie : '-1', 'paie', 0, 0, 0, 'card', 1);
	print '</td>';
	
	/*
	 * Comment
	 */
	print '<td><input name="comment" size="30" value="' . GETPOST('comment') . '"</td>';
	
	print "</tr>\n";
	
		/*
	 * List receipt noOk
	 */
	$sql = "SELECT rec.rowid as reference, rec.name as receiptname, loc.nom as nom, l.address  , l.name as local, loc.statut as statut, rec.amount_total as total, rec.paiepartiel, rec.balance ,  rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_contract as refcontract , c.preavis";
	$sql .= " FROM " . MAIN_DB_PREFIX . "immo_receipt rec";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as loc";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_property as l";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
	$sql .= " WHERE rec.paye = 0 AND loc.rowid = rec.fk_renter AND l.rowid = rec.fk_property AND  c.rowid = rec.fk_contract and c.preavis =0 ";
	if ($user->id != 1) {
		$sql .= " AND rec.owner_id=" . $user->id;
	}
	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		
		$i = 0;
		$total = 0;
		
		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans('NameReceipt') . '</td>';
		print '<td>' . $langs->trans('nomlocal') . '</td>';
		print '<td>' . $langs->trans('nomlocataire') . '</td>';
		print '<td align="right">' . $langs->trans('montant_tot') . '</td>';
		print '<td align="right">' . $langs->trans('payed') . '</td>';
		print '<td align="right">' . $langs->trans('due') . '</td>';
		print '<td align="right">' . $langs->trans('income') . '</td>';
		print "</tr>\n";
		
		if ($num > 0) {
			$var = True;
			
			while ( $i < $num ) {
				$objp = $db->fetch_object($resql);
				$var = ! $var;
				print '<tr ' . $bc[$var] . '>';
				
				print '<td>' . $objp->receiptname . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->nom . '</td>';
				
				print '<td align="right">' . price($objp->total) . '</td>';
				print '<td align="right">' . price($objp->paiepartiel) . '</td>';
				print '<td align="right">' . price($objp->balance) . '</td>';
				
					print '<input type="hidden" name="fk_contract_' . $objp->reference . '" size="10" value="' . $objp->refcontract . '">';
					print '<input type="hidden" name="fk_property_' . $objp->reference . '" size="10" value="' . $objp->reflocal . '">';
					print '<input type="hidden" name="fk_renter_' . $objp->reference . '" size="10" value="' . $objp->reflocataire . '">';
					print '<input type="hidden" name="receipt_' . $objp->reference . '" size="10" value="' . $objp->reference . '">';
				
				// Colonne imput income
				print '<td align="right">';
			print '<input type="text" name="incomeprice_' . $objp->reference . '" id="incomeprice_' . $objp->reference . '" size="6" value="" class="flat">';
			print '</td>';
				
				
	
				print '</tr>';
				
				$i ++;
			}
		}
		$var = ! $var;
		
		print "</table>\n";
		$db->free($resql);
	} 

	else {
		dol_print_error($db);
	}
	print '<div class="tabsAction">' . "\n";
	print '<div class="inline-block divButAction"><input type="submit"  name="button_addallpaiement" id="button_addallpaiement" class="butAction" value="' . $langs->trans("Payed") . '" /></div>';
	print '</div>';
	print '</form>';
	
}


/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */
if (GETPOST("action") == 'update') {
	$receipt = new Immoreceipt($db);
	$result = $receipt->fetch($receipt_id);
	
	$paie = new Immopayment($db);
		
	$result = $paie->fetch($id);
	
	print '<form action="card.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";
	print '<input type="hidden" name="receipt" value="' . $receipt_id . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<input type="hidden" name="fk_contract" size="10" value="' . $receipt->fk_contract . '">';
	print '<input type="hidden" name="fk_property" size="10" value="' . $receipt->fk_property . '">';
	print '<input type="hidden" name="fk_renter" size="10" value="' . $receipt->fk_renter . '">';
	print '<input type="hidden" name="fk_receipt" size="10" value="' . $id . '">';
	
	print '<tr><td width="20%">' . $langs->trans("NomAppartement") . '</td><td>' . $receipt->nomlocal . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("NomLocataire") . '</td><td>' . $receipt->nomlocataire . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("RefLoyer") . '</td><td>' . $receipt->nom . '</td></tr>';
	;
	
	print '<tr><td width="20%">' . $langs->trans("amount") . '</td>';
	print '<td><input name="amount" size="30" value="' . round($paie->amount,2) . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("Commentaire") . '</td>';
	print '<td><input name="comment" size="10" value="' . $paie->comment . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans("DatePaiement") . '</td>';
	print '<td align="left">';
	print $form->select_date(! empty($paie->date_payment) ? $paie->date_payment : '-1', 'paie', 0, 0, 0, 'card', 1);
	print '</td>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans("Sauvegarder") . '"><input type="cancel" class="button" value="' . $langs->trans("Cancel") . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

llxFooter();

$db->close();
