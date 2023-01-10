<?php
/* Copyright (C) 2001-2006  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021  Philippe GRAND          <philippe.grand@atoo-net.com>
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
 *	\file       htdocs/compta/paiement.php
 *	\ingroup    facture
 *	\brief      Payment page for customers invoices
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");
if (!$res && file_exists("../../../../main.inc.php")) $res = @include("../../../../main.inc.php");
if (!$res) die("Include of main fails");

dol_include_once('/ultimateimmo/class/immopayment.class.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/lib/immopayment.lib.php');
//require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

// Load translation files required by the page
$langs->loadLangs(array('companies', 'bills', 'banks'));

$id			= GETPOST('id', 'int');
$ref		= GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$confirm	= GETPOST('confirm', 'alpha');

$accountid	= GETPOST('accountid', 'int');
$paymentnum	= GETPOST('num_paiement', 'alpha');
$socid      = GETPOST('socid', 'int');

$sortfield	= GETPOST('sortfield', 'alpha');
$sortorder	= GETPOST('sortorder', 'alpha');
$page		= GETPOST('page', 'int');

$amounts = array();
$amountsresttopay = array();
$addwarning = 0;

$receipt = new ImmoReceipt($db);
$receipt->fetch($id);

$object = new ImmoPayment($db);
$object->fetch($receipt->fk_payment);


$renter = new ImmoRenter($db);
$renter->fetch($receipt->fk_renter);

$owner = new ImmoOwner($db);
$owner->fetch($receipt->fk_owner);

$rent = new ImmoRent($db);
$rent->fetch($receipt->fk_rent);

$property = new ImmoProperty($db);
$property->fetch($receipt->fk_property);

// Security check
if ($renter->fk_soc > 0) {
	$socid = $renter->fk_soc;
}
$usercanread = $user->rights->ultimateimmo->read;
$usercancreate = $user->rights->ultimateimmo->write;
$usercandelete = $user->rights->ultimateimmo->delete || ($usercancreate && $object->status == 0);

// Load object
if ($id > 0) {
	$ret = $receipt->fetch($id);
}
//var_dump($_POST);
// Initialize technical object to manage hooks of paiements. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('paiementcard', 'globalcard'));

//var_dump($_POST);exit;
/*
 * Actions
 */

$parameters = array('socid' => $socid);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

$form = new Form($db);
if ($action == 'add_payment') {
	$error = 0;
//var_dump($id);exit;
	if (GETPOST('cancel')) {
		$loc = dol_buildpath("/ultimateimmo/receipt/immoreceipt_card.php", 1) . '?id=' . $id;
		header("Location: " . $loc);
		exit;
	}

	if (!GETPOST('fk_mode_reglement') > 0) {
		$mesg = $langs->trans("ErrorFieldRequired", $langs->transnoentities("PaymentMode"));
		setEventMessages($mesg, null, 'errors');
		$error++;
	}
	if (GETPOST('reyear') == '') {
		$mesg = $langs->trans("ErrorFieldRequired", $langs->transnoentities("Date"));
		setEventMessages($mesg, null, 'errors');
		$error++;
	}
	if (!empty($conf->banque->enabled) && $accountid <= 0) {
		$mesg = $langs->trans("ErrorFieldRequired", $langs->transnoentities("AccountToCredit"));
		setEventMessages($mesg, null, 'errors');
		$error++;
	}

	if (!$error) {
		$date_payment = dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));
		$paymentid = 0;

		// Read possible payments
		foreach ($_POST as $key => $value) {
			if (substr($key, 0, 7) == 'amount_') {
				$other_id = substr($key, 7);
				$amounts[$other_id] = price2num($_POST[$key]);
			}
		}

		if (count($amounts) <= 0) {
			$error++;
			$errmsg = 'ErrorNoPaymentDefined';
			setEventMessages($errmsg, null, 'errors');
		}

		if (!$error) {
			$db->begin();

			// Create a line of payments
			$payment = new ImmoPayment($db);
			$receipt = new ImmoReceipt($db);
			$result = $receipt->fetch($id);

			$payment->ref          = $receipt->ref;
			$payment->rowid        = $id;
       		$payment->fk_receipt   = $receipt->rowid;
			$payment->fk_rent	   = $receipt->fk_rent;
			$payment->fk_property  = $receipt->fk_property;
			$payment->fk_renter	   = $receipt->fk_renter;
			$payment->fk_payment   = $receipt->fk_payment;
			$payment->date_payment = $date_payment;
			$payment->amounts      = $amounts;   // Tableau de montant
			$payment->fk_mode_reglement  = GETPOST('fk_mode_reglement', 'int');
			$payment->fk_bank  = GETPOST('fk_bank', 'int');
			$payment->num_payment  = GETPOST('num_payment', 'int');
			$payment->note_public  = GETPOST('note_public', 'string');

			if (!$error) {
				$paymentid = $payment->create($user);
				if ($paymentid < 0) {
					$errmsg = $payment->errors;
					setEventMessages(null, $errmsg, 'errors');
					$error++;
				}
			}

			if (!$error) {
				$label = '(CustomerReceiptPayment)';
				if (GETPOST('type') == ImmoReceipt::TYPE_CREDIT_NOTE) $label = '(CustomerReceiptPaymentBack)';
				$result = $payment->addPaymentToBank($user, 'immopayment', $label, $_POST['accountid'], '', '');
				if ($result <= 0) {
					$errmsg = $payment->errors;
					setEventMessages(null, $errmsg, 'errors');
					$error++;
				}
			}

			if (!$error) {
				$db->commit();
				$loc = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $id;
				header('Location: ' . $loc);
				exit;
			} else {
				$db->rollback();
				$errmsg = $payment->errors;
				setEventMessages(null, $errmsg, 'errors');
			}
		}
	}

	$_GET["action"] = 'create';
}


/*
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans("Payment"));

// Form to create immoreceipt payment
if (GETPOST('action', 'aZ09') == 'create') {

			// Add realtime total information
	if (!empty($conf->use_javascript_ajax)) {
		print "\n" . '<script type="text/javascript">';
		//Add js for AutoFill
		print ' $(document).ready(function () {';
		print ' 	$(".AutoFillAmout").on(\'click touchstart\', function(){
						$("input[name="+$(this).data(\'rowname\')+"]").val($(this).data("value")).trigger("change");
					});';
		print '	});'."\n";
		print "\n" . '</script>';
	}


	$receipt = new ImmoReceipt($db);
	$result = $receipt->fetch($id);

	$total = $receipt->total_amount;

	if ($result >= 0) {
		//$ret = $paiement->fetch_thirdparty();
		$title = '';
		if ($receipt->type != ImmoReceipt::TYPE_CREDIT_NOTE) $title .= $langs->trans("EnterPaymentReceivedFromCustomer");
		if ($receipt->type == ImmoReceipt::TYPE_CREDIT_NOTE) $title .= $langs->trans("EnterPaymentDueToCustomer");
		print load_fiche_titre($title);

		print '<form id="payment_form" name="add_payment" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="add_payment">';
		print '<input type="hidden" name="id" value="' . $id . '">';
		print '<input type="hidden" name="socid" value="' . $renter->fk_soc . '">';

		print dol_get_fiche_head(array(), '');

		print '<table class="border centpercent">' . "\n";

		$paymentstatic = new ImmoPayment($db);
		$paymentstatic->fetch($receipt->fk_payment);

		// Reference
		$tmpref = GETPOST('ref', 'alpha') ? GETPOST('ref', 'alpha') : $receipt->id;
		print '<tr><td class="titlefieldcreate"><span class="fieldrequired">' . $langs->trans('Reference') . '</span></td><td>' . $tmpref . "</td></tr>\n";

		// Date payment
		print '<tr><td>' . $langs->trans("Date") . "</td><td colspan=\"2\">" . dol_print_date($receipt->date_echeance, 'day') . "</td></tr>\n";
		$rent = new ImmoRent($db);
		$rent->fetch($receipt->fk_rent);
		$staticproperty = new ImmoProperty($db);
		$staticproperty->fetch($receipt->fk_property);
		if ($rent->ref) {
			$rent->ref = $rent->getNomUrl(0) . ' - ' . $staticproperty->label;
		}

		print '<tr><td>' . $langs->trans("ImmoRent") . "</td><td colspan=\"2\">" . $rent->ref . "</td></tr>\n";
		print '<tr><td>' . $langs->trans("Property") . "</td><td colspan=\"2\">" . $staticproperty->address.' '.$staticproperty->zip .' '.$staticproperty->town . "</td></tr>\n";

		$staticrenter = new ImmoRenter($db);
		$staticrenter->fetch($receipt->fk_renter);
		if ($staticrenter->ref) {
			$staticrenter->ref = $staticrenter->getNomUrl(0) . ' - ' . $staticrenter->getFullName($langs);
		}
		print '<tr><td>' . $langs->trans("Renter") . "</td><td colspan=\"2\">" . $staticrenter->ref . "</td></tr>\n";

		// Total amount
		print '<tr><td>' . $langs->trans("Amount") . "</td><td colspan=\"2\">" . price($receipt->total_amount, 0, $outputlangs, 1, -1, -1, $conf->currency) . '</td></tr>';

		$sql = "SELECT sum(p.amount) as total";
		$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
		$sql .= " WHERE p.fk_receipt = " . $id;
		//print_r($sql);exit;
		$resql = $db->query($sql);
		if ($resql) {
			$obj = $db->fetch_object($resql);
			$sumpaid = $obj->total;
			$db->free();
		}

		print '<tr><td>' . $langs->trans("AlreadyPaid") . '</td><td colspan="2">' . price($sumpaid, 0, $outputlangs, 1, -1, -1, $conf->currency) . '</td></tr>';
		print '<tr><td class="tdtop">' . $langs->trans("RemainderToPay") . '</td><td colspan="2">' . price($receipt->total_amount - $sumpaid, 0, $outputlangs, 1, -1, -1, $conf->currency) . '</td></tr>';

		print '<tr class="liste_titre">';
		print "<td colspan=\"3\">" . $langs->trans("Payment") . '</td>';
		print '</tr>';

		print '<tr><td><span class="fieldrequired">' . $langs->trans('Date') . '</span></td><td>';
		$date_payment = dol_mktime(12, 0, 0, $_POST['remonth'], $_POST['reday'], $_POST['reyear']);
		$datepayment = empty($conf->global->MAIN_AUTOFILL_DATE) ? (empty($_POST["remonth"]) ? -1 : $date_payment) : 0;
		print $form->selectDate($datepayment, '', '', '', '', "add_payment", 1, 1);
		print '</td></tr>';

		print '<tr><td class="fieldrequired">' . $langs->trans("PaymentMode") . '</td><td colspan="2">';
		$form->select_types_paiements((GETPOST('fk_mode_reglement') ? GETPOST('fk_mode_reglement') : $paymentstatic->fk_mode_reglement), 'fk_mode_reglement');
		print "</td>\n";
		print '</tr>';

		// Bank account
		print '<tr>';
		print '<td class="fieldrequired">' . $langs->trans('AccountToCredit') . '</td>';
		print '<td colspan="2">';
		$form->select_comptes(isset($_POST["accountid"]) ? $_POST["accountid"] : $paymentstatic->accountid, "accountid", 0, '', 1);  // Show open bank account list
		print '</td></tr>';

		// Cheque number
		print '<tr><td>' . $langs->trans('Numero');
		print '(' . $langs->trans("ChequeOrTransferNumber") . ')';
		print '</td>';
		print '<td><input name="num_paiement" type="text" value="' . $paymentnum . '"></td></tr>';

		// Check transmitter
		print '<tr><td class="' . (GETPOST('fk_mode_reglement') == 'CHQ' ? 'fieldrequired ' : '') . 'fieldrequireddyn">' . $langs->transnoentities('CheckTransmitter');
		print '</td>';
		print '<td><input id="fieldchqemetteur" name="chqemetteur" size="30" type="text" value="' . GETPOST('chqemetteur', 'alphanohtml') . '"></td></tr>';

		// Bank name
		print '<tr><td>';
		print '(' . $langs->transnoentities("ChequeBank") . ')';
		print '</td>';
		print '<td><input name="chqbank" size="30" type="text" value="' . GETPOST('chqbank', 'alphanohtml') . '"></td></tr>';

		// Comments
		print '<tr><td>' . $langs->transnoentities('Comments') . '</td>';
		print '<td class="tdtop">';
		print '<textarea name="note_public" wrap="soft" class="quatrevingtpercent" rows="' . ROWS_3 . '">' . GETPOST('note_public', 'none') . '</textarea></td></tr>';

		print '</table>';

		print dol_get_fiche_end();

		/*
		 * Autres charges impayees
		 */

		$num = 1;
		$i = 0;

		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td class="left">' . $langs->trans("ImmoReceipt") . '</td>';
		print '<td class="left">' . $langs->trans("ImmoRent") . '</td>';
		print '<td class="right">' . $langs->trans("Amount") . '</td>';
		print '<td class="right">' . $langs->trans("AlreadyPaid") . '</td>';
		print '<td class="right">' . $langs->trans("RemainderToPay") . '</td>';
		print '<td class="center">' . $langs->trans("Amount") . '</td>';
		print "</tr>\n";

		$total = 0;
		$totalrecu = 0;

		while ($i < $num) {
			$objp = $receipt;

			print '<tr class="oddeven">';

			print '<td class="left">' . $objp->ref . "</td>";

			$sql = "SELECT rent.rowid, rent.ref as contract";
			$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as pmt";
			$sql .= ", " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as rcpt";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent ON rcpt.fk_rent = rent.rowid";
			$sql .= " WHERE pmt.fk_receipt = " . $id;
			$sql .= " AND pmt.fk_rent = rent.rowid";
			//print_r($sql);exit;
			$resql = $db->query($sql);
			if ($resql) {
				$obj = $db->fetch_object($resql);
				$contract = $obj->contract;
				//var_dump($obj);exit;
				$db->free();
			}

			print '<td class="left">' . $rent->getNomUrl(0) . "</td>";

			print '<td class="right">' . price($objp->total_amount) . "</td>";

			print '<td class="right">' . price($sumpaid) . "</td>";

			print '<td class="right">' . price($objp->total_amount - $sumpaid) . "</td>";

			print '<td class="center">';
			if ($sumpaid < $objp->total_amount) {
				$namef = "amount_" . $objp->id;
				if (!empty($conf->use_javascript_ajax)) {
					print img_picto("Auto fill", 'rightarrow', "class='AutoFillAmout' data-rowname='".$namef."' data-value='".($objp->total_amount - $sumpaid)."'");
				}
				print '<input type="text" size="8" name="' . $namef . '" required="required">';
			} else {
				$errmsg = $langs->trans("AlreadyPaid");
				setEventMessages($errmsg, null, 'errors');
				print $errmsg;
			}
			print "</td>";
			print "</tr>\n";
			$i++;
		}

		print "</table>";

		print '<br><div class="center">';
		print '<input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
		print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
		print '</div>';

		print "</form>\n";
	}
}
llxFooter();

$db->close();
