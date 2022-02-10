<?php
/* Copyright (C) 2015       Alexandre Spangaro      <aspangaro@open-dsi.fr>
 * Copyright (C) 2018-2020  Philippe GRAND  		<philippe.grand@atoo-net.com>
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
 *	    \file       htdocs/custom/ultimateimmo/receipt/payment/card.php
 *		\ingroup    ultimateimmo
 *		\brief      Tab payment of a receipt
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

dol_include_once('/ultimateimmo/class/immopayment.class.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/lib/immopayment.lib.php');
//require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

// Load translation files required by the page
$langs->loadLangs(array("bills","banks","companies"));

// Security check
$id=GETPOST('rowid')?GETPOST('rowid', 'int'):GETPOST('id', 'int');
$action=GETPOST('action', 'aZ09');
$confirm=GETPOST('confirm');
if ($user->societe_id) $socid=$user->societe_id;
// TODO Add rule to restrict access payment
//$result = restrictedArea($user, 'facture', $id,'');

$receipt=new ImmoReceipt($db);
$receipt->fetch($id);

$object = new ImmoPayment($db);
$object->fetch($receipt->fk_payment);

if ($id > 0)
{
	$result=$object->fetch($id);
	if (! $result) dol_print_error($db, 'Failed to get payment id '.$id);
}

$usercanread = $user->rights->ultimateimmo->read;
$usercancreate = $user->rights->ultimateimmo->write;
$usercandelete = $user->rights->ultimateimmo->delete || ($usercancreate && $object->status == 0);


/*
 * Actions
 */

// Delete payment
if ($action == 'confirm_delete' && $confirm == 'yes' && $usercandelete)
{
	$db->begin();

	$result = $object->delete($user);
	if ($result > 0)
	{
        $db->commit();
        header("Location: ".dol_buildpath('/ultimateimmo/payment/immopayment_card.php',1));
        exit;
	}
	else
	{
		setEventMessages($object->error, $object->errors, 'errors');
        $db->rollback();
	}
}

// Create payment
if ($action == 'confirm_valide' && $confirm == 'yes' && $usercancreate)
{
	$db->begin();

	$result=$object->valide();

	if ($result > 0)
	{
		$db->commit();

		$receipts=array();	// TODO Get all id of receipts linked to this payment
		foreach($receipts as $id)
		{
			$rec = new ImmoReceipt($db);
			$rec->fetch($id);

			$outputlangs = $langs;
			if (! empty($_REQUEST['lang_id']))
			{
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($_REQUEST['lang_id']);
			}
			if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
				$rec->generateDocument($rec->model_pdf, $outputlangs);
			}
		}

		header('Location: card.php?id='.$object->id);
		exit;
	}
	else
	{
		setEventMessages($object->error, $object->errors, 'errors');
		$db->rollback();
	}
}

if ($action == 'addall') 
{
	$date_payment = @dol_mktime(0, 0, 0, GETPOST("remonth"), GETPOST("reday"), GETPOST("reyear"));
	if (! $date_payment) 
	{
		$mesg = $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie"));
		setEventMessages($mesg, null, 'errors');
		$action = 'createall';
	} 
	else 
	{
		$datapost = $_POST;
		foreach ( $datapost as $key => $value ) 
		{
			if (strpos($key, 'receipt_') !== false) 
			{			
				$tmp_array = explode('_', $key);
				
				if (count($tmp_array) > 0) {
					$reference = $tmp_array[1];
					$amount= GETPOST('incomeprice_'.$reference);
				
					if (! empty($reference) && !empty($amount)) 
					{
						$payment = new Immopayment($db);

						$payment->fk_rent			= GETPOST('fk_rent'.$reference);
						$payment->fk_property		= GETPOST('fk_property_'.$reference);
						$payment->fk_renter			= GETPOST('fk_renter_'.$reference);
						$payment->amount			= price2num($amount);
						$payment->note_public		= GETPOST('note_public');
						$payment->date_payment		= $date_payment;
						$payment->fk_receipt		= GETPOST('receipt_'.$reference);
						$payment->fk_bank			= GETPOST("accountid");
						$payment->fk_mode_reglement	= GETPOST("fk_mode_reglement");
						$payment->num_payment		= GETPOST("num_payment");
						$payment->fk_owner			= $user->id;
						
						$result = $payment->create ($user);

						if ($result<0) 
						{
							setEventMessages($payment->error, null, 'errors');
						}
					}
				}
			}
		}				
	}
}


/*
 * View
 */

llxHeader();

$form = new Form($db);

$h=0;

$head[$h][0] = dol_buildpath('/ultimateimmo/receipt/payment/card.php',1).'?id='.$id;
$head[$h][1] = $langs->trans("Card");
$hselected = $h;
$h++;

dol_fiche_head($head, $hselected, $langs->trans("CustomerReceiptPayment"), -1, 'payment');

/*
 * Confirm deleting of the payment
 */
if ($action == 'delete')
{
	print $form->formconfirm('card.php?id='.$object->id, $langs->trans("DeletePayment"), $langs->trans("ConfirmDeletePayment"), 'confirm_delete', '', 0, 2);
}

/*
 * Confirm validation of the payment
 */
if ($action == 'valide')
{
	$recid = GETPOST('recid', 'int');
	print $form->formconfirm('card.php?id='.$object->id.'&amp;receipt='.$recid, $langs->trans("ValidatePayment"), $langs->trans("ConfirmValidatePayment"), 'confirm_valide', '', 0, 2);
}


dol_banner_tab($object, 'id', '', 1, 'rowid', 'id');

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';

print '<table class="border" width="100%">';

// Date
print '<tr><td class="titlefield">'.$langs->trans('Date').'</td><td>'.dol_print_date($object->date_payment, 'day').'</td></tr>';

// Mode reglement
$sql = "SELECT p.rowid, p.date_payment as dp, p.fk_mode_reglement, c.code as type_code, c.libelle as mode_reglement_label";
$sql .= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immopayment as p";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_paiement as c ON p.fk_mode_reglement = c.id";
$sql .= " WHERE p.rowid = ".$id;
$sql .= " AND p.entity IN (" . getEntity($object->element).")";
$sql .= ' ORDER BY dp';
$resql = $db->query($sql);
if ($resql)
{
	$obj=$db->fetch_object($resql);
	$fk_mode_reglement = $obj->mode_reglement_label;
	$db->free();
}
print '<tr><td>'.$langs->trans('Mode').'</td><td>'.$fk_mode_reglement.'</td></tr>';

// Amount
print '<tr><td>'.$langs->trans('Amount').'</td><td>'.price($object->amount, 0, $outputlangs, 1, -1, -1, $conf->currency).'</td></tr>';

// Note
print '<tr><td>'.$langs->trans('Note').'</td><td>'.nl2br($object->note_public).'</td></tr>';

// Bank account
if (! empty($conf->banque->enabled))
{
    if ($object->bank_account)
    {
    	$bankline=new AccountLine($db);
    	$bankline->fetch($object->bank_line);

    	print '<tr>';
    	print '<td>'.$langs->trans('BankTransactionLine').'</td>';
		print '<td>';
		print $bankline->getNomUrl(1, 0, 'showall');
    	print '</td>';
    	print '</tr>';
    }
}

print '</table>';

/* *************************************************************************** */
/*                                                                             */
/* Mode add all payments                                                       */
/*                                                                             */
/* *************************************************************************** */

if ($action == 'createall') 
{

	print '<form name="fiche_payment" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addall">';
	
	print '<table class="border" width="100%">';

	print "<tr class=\"liste_titre\">";
	
	print '<td align="left">';
	print $langs->trans("DatePayment");
	print '</td><td align="left">';
	print $langs->trans("Comment");
	print '</td><td align="left">';
	print $langs->trans("PaymentMode");
	print '</td><td align="left">';
	print $langs->trans("AccountToCredit");
	print '</td><td align="left">';
	print $langs->trans("Numero");
	print '</td>';
	print "</tr>\n";
	
	print '<tr class="oddeven" valign="top">';
	
	// Due date	
	print '<td align="center">';
	print $form->select_date(! empty($date_payment) ? $date_payment : '-1', 'payment', 0, 0, 0, 'card', 1);
	print '</td>';
	
	// Comment
	print '<td><input name="comment" size="30" value="' . GETPOST('note_public') . '"</td>';
	
	// Payment mode
	print '<td align="center">';
	print $form->select_types_paiements(GETPOST('fk_mode_reglement','int')?GETPOST('fk_mode_reglement','int'):$payment->fk_mode_reglement, "fk_mode_reglement");
	print '</td>';
	
	// AccountToCredit
	print '<td align="center">';
	print $form->select_comptes(isset($_POST["accountid"])?$_POST["accountid"]:$payment->accountid, "accountid", 0, '',1);  // Show open bank account list
	print '</td>';

	// num_payment
	print '<td><input name="num_payment" size="30" value="' . GETPOST('num_payment') . '"</td>';
	
	
	print "</tr>\n";
	
	/*
	 * List receipt
	 */
	$sql = "SELECT rec.rowid as reference, rec.ref as receiptname, loc.lastname as nom, l.address  , l.label as local, loc.status as status, rec.total_amount as total, rec.partial_payment, rec.balance ,  rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_rent as refcontract , c.preavis";
	$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt rec";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as l";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immorent as c";
	$sql .= " WHERE rec.paye = 0 AND loc.rowid = rec.fk_renter AND l.rowid = rec.fk_property AND  c.rowid = rec.fk_rent and c.preavis =0 ";
	$resql = $db->query($sql);
	if ($resql) 
	{
		$num = $db->num_rows($resql);
		
		$i = 0;
		$total = 0;
		
		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans('ReceiptName') . '</td>';
		print '<td>' . $langs->trans('Nomlocal') . '</td>';
		print '<td>' . $langs->trans('Renter') . '</td>';
		print '<td align="right">' . $langs->trans('montant_tot') . '</td>';
		print '<td align="right">' . $langs->trans('payed') . '</td>';
		print '<td align="right">' . $langs->trans('due') . '</td>';
		print '<td align="right">' . $langs->trans('income') . '</td>';
		print "</tr>\n";
		
		if ($num > 0) {
			
			while ( $i < $num ) 
			{
				$objp = $db->fetch_object($resql);
				print '<tr class="oddeven">';
				
				print '<td>' . $objp->receiptname . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->nom . '</td>';
				
				print '<td align="right">' . price($objp->total) . '</td>';
				print '<td align="right">' . price($objp->paiepartiel) . '</td>';
				print '<td align="right">' . price($objp->balance) . '</td>';
				
					print '<input type="hidden" name="fk_rent' . $objp->reference . '" size="10" value="' . $objp->refcontract . '">';
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


/*
 * List of receipt paid
 */

$disable_delete = 0;
$sql = 'SELECT d.rowid as recid, d.paye, d.total_amount as d_amount, pd.amount, d.ref';
$sql.= ' FROM '.MAIN_DB_PREFIX.'ultimateimmo_immopayment as pd,'.MAIN_DB_PREFIX.'ultimateimmo_immoreceipt as d';
$sql.= ' WHERE pd.fk_receipt = d.rowid';
$sql.= ' AND d.entity = '.$conf->entity;
$sql.= ' AND pd.rowid = '.$id;

dol_syslog("dol_buildpath('/ultimateimmo/receipt/payment/card.php',1)", LOG_DEBUG);
$resql=$db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);

	$i = 0;
	$total = 0;
	print '<br><table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans('ImmoReceipt').'</td>';
    print '<td class="right">'.$langs->trans('ExpectedToPay').'</td>';
	//print '<td class="center">'.$langs->trans('Status').'</td>';
	print '<td class="right">'.$langs->trans('PayedByThisPayment').'</td>';
	print "</tr>\n";

	if ($num > 0)
	{
		while ($i < $num)
		{
			$objp = $db->fetch_object($resql);

			print '<tr class="oddeven">';
			// Ref
			print '<td>';
			$receipt->fetch($objp->recid);
			//var_dump($objp);exit;
			print $receipt->getNomUrl(1);
			print "</td>\n";
			// Expected to pay
			print '<td class="right">'.price($objp->d_amount).'</td>';
			// Status
			//print '<td class="center">'.$receipt->getLibStatut(4, $objp->amount).'</td>';
			// Amount payed
			print '<td class="right">'.price($objp->amount).'</td>';
			print "</tr>\n";
			if ($objp->paye == 1) {
                // If at least one invoice is paid, disable delete
				$disable_delete = 1;
			}
			$total = $total + $objp->amount;
			$i++;
		}
	}


	print "</table>\n";
	$db->free($resql);
}
else
{
	dol_print_error($db);
}

print '</div>';

dol_fiche_end();


/*
 * Actions buttons
 */
print '<div class="tabsAction">';

/*
if (! empty($conf->global->BILL_ADD_PAYMENT_VALIDATION))
{
	if ($user->societe_id == 0 && $object->statut == 0 && $_GET['action'] == '')
	{
		if ($user->rights->facture->paiement)
		{
			print '<a class="butAction" href="card.php?id='.$_GET['id'].'&amp;facid='.$objp->facid.'&amp;action=valide">'.$langs->trans('Valid').'</a>';
		}
	}
}
*/

if ($_GET['action'] == '')
{
	if ($usercandelete)
	{
		if (! $disable_delete)
		{
			print '<a class="butActionDelete" href="card.php?id='.$_GET['id'].'&amp;action=delete">'.$langs->trans('Delete').'</a>';
		}
		else
		{
			print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("CantRemovePaymentWithOneInvoicePaid")).'">'.$langs->trans('Delete').'</a>';
		}
	}
}

print '</div>';



llxFooter();

$db->close();
