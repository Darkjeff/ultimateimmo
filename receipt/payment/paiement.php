<?php
/* Copyright (C) 2001-2006  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2022  Philippe GRAND          <philippe.grand@atoo-net.com>
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
 *	\file       htdocs/custom/ultimateimmo/receipt/payment/paiement.php
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
dol_include_once('/ultimateimmo/lib/immoreceipt.lib.php');
//require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

// Load translation files required by the page
$langs->loadLangs(array('companies', 'bills', 'banks'));

$id = GETPOST('rowid', 'int') ? GETPOST('rowid', 'int') : GETPOST('id', 'int');
$rowid = $id;
$ref		= GETPOST('ref', 'alphanohtml');
$action		= GETPOST('action', 'alpha');
$confirm	= GETPOST('confirm', 'alpha');

$paymentnum	= GETPOST('num_paiement', 'alpha');
$socid      = GETPOST('socid', 'int');

$sortfield	= GETPOST('sortfield', 'alpha');
$sortorder	= GETPOST('sortorder', 'alpha');
$page		= GETPOST('page', 'int');

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

$paymentstatic = new ImmoPayment($db);
$paymentstatic->fetch($receipt->fk_payment);

$errmsg = '';

$defaultdelay = 1;
$defaultdelayunit = 'm';

$paymentdate = -1;

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
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

// Create third party from a renter
if (empty($reshook) && $action == 'confirm_create_thirdparty' && $confirm == 'yes' && $user->rights->societe->creer) {
	if ($result > 0) {
		// Creation of thirdparty
		$customer = new RenterSoc($this->db);
		$result = $customer->create_from_renter($renter, GETPOST('companyname', 'alpha'), GETPOST('companyalias', 'alpha'), GETPOST('customercode', 'alpha'));

		if ($result < 0) {
			$langs->load("errors");
			setEventMessages($customer->error, $customer->errors, 'errors');
		} else {
			$action = 'receiptpayment';
		}
	} else {
		setEventMessages($renter->error, $renter->errors, 'errors');
	}
}

if (empty($reshook) && $action == 'setsocid') {
	$error = 0;
	if (!$error) {
		if (GETPOST('socid', 'int') != $renter->fk_soc) {    // If link differs from currently in database
			$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."ultimateimmo_immorenter";
			$sql .= " WHERE fk_soc = '".GETPOST('socid', 'int')."'";
			$resql = $db->query($sql);
			if ($resql) {
				$obj = $db->fetch_object($resql);
				if ($obj && $obj->rowid > 0) {
					$otherrenter = new ImmoRenter($db);
					$otherrenter->fetch($obj->rowid);
					$thirdparty = new Societe($db);
					$thirdparty->fetch(GETPOST('socid', 'int'));
					$error++;
					setEventMessages($langs->trans("ErrorMemberIsAlreadyLinkedToThisThirdParty", $otherrenter->getFullName($langs), $thirdparty->name), null, 'errors');
				}
			}

			if (!$error) {
				$result = $otherrenter->setThirdPartyId(GETPOST('socid', 'int'));
				if ($result < 0) {
					dol_print_error('', $otherrenter->error);
				}
				$action = '';
			}
		}
	}
}

$form = new Form($db);
if ($action == 'receiptpayment') {
	$error = 0;

	// receiptpayment informations
	$datereceipt = 0; // date_start
	$datesubend = 0;  // date_end
	$paymentdate = ''; // Do not use 0 here, default value is '' that means not filled where 0 means 1970-01-01
	if (GETPOST("reyear", "int") && GETPOST("remonth", "int") && GETPOST("reday", "int")) {
		$datereceipt = dol_mktime(0, 0, 0, GETPOST("remonth", "int"), GETPOST("reday", "int"), GETPOST("reyear", "int"));
	}
	if (GETPOST("endyear", 'int') && GETPOST("endmonth", 'int') && GETPOST("endday", 'int')) {
		$datesubend = dol_mktime(0, 0, 0, GETPOST("endmonth", 'int'), GETPOST("endday", 'int'), GETPOST("endyear", 'int'));
	}
	if (GETPOST("paymentyear", 'int') && GETPOST("paymentmonth", 'int') && GETPOST("paymentday", 'int')) {
		$paymentdate = dol_mktime(0, 0, 0, GETPOST("paymentmonth", 'int'), GETPOST("paymentday", 'int'), GETPOST("paymentyear", 'int'));
	}
	$amount = price2num(GETPOST("receiptpayment", 'alpha')); // Amount of receiptpayment
	$label = GETPOST("label");

	// Payment informations
	$accountid	= GETPOST('accountid', 'int');
	$operation = GETPOST("operation", "alphanohtml"); // Payment mode
	$num_chq = GETPOST("num_chq", "alphanohtml");
	$emetteur_nom = GETPOST("chqemetteur");
	$emetteur_banque = GETPOST("chqbank");
	$option = GETPOST("paymentsave");
	if (empty($option)) {
		$option = 'none';
	}
	$sendalsoemail = GETPOST("sendmail", 'alpha');

	// Check parameters
	if (!$datereceipt) {
		$error++;
		$langs->load("errors");
		$errmsg = $langs->trans("ErrorBadDateFormat", $langs->transnoentitiesnoconv("DateStartPeriod"));
		setEventMessages($errmsg, null, 'errors');
		$action = 'receiptpayment';
	}
	if (GETPOST('end') && !$datesubend) {
		$error++;
		$langs->load("errors");
		$errmsg = $langs->trans("ErrorBadDateFormat", $langs->transnoentitiesnoconv("DateEndPeriod"));
		setEventMessages($errmsg, null, 'errors');
		$action = 'receiptpayment';
	}
	if (!$datesubend) {
		$datesubend = dol_time_plus_duree(dol_time_plus_duree($datereceipt, $defaultdelay, $defaultdelayunit), -1, 'd');
	}
	if (($option == 'bankviainvoice' || $option == 'bankdirect') && !$paymentdate) {
		$error++;
		$errmsg = $langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("DatePayment"));
		setEventMessages($errmsg, null, 'errors');
		$action = 'receiptpayment';
	}

	if (GETPOST('cancel')) {
		$loc = dol_buildpath("/ultimateimmo/receipt/immoreceipt_card.php", 1) . '?id=' . $id;
		header("Location: " . $loc);
		exit;
	}

	if (!GETPOST('operation') > 0) {
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

	// Record the subscription then complementary actions
	if (!$error && $action == 'receiptpayment') {
		$db->begin();

		// Create subscription
		$crowid = $renter->receiptsubscription($datesubscription, $amount, $accountid, $operation, $label, $num_chq, $emetteur_nom, $emetteur_banque, $datesubend);
		if ($crowid <= 0) {
			$error++;
			$errmsg = $renter->error;
			setEventMessages($renter->error, $renter->errors, 'errors');
		}

		if (!$error) {
			$result = $renter->receiptSubscriptionComplementaryActions($crowid, $option, $accountid, $datesubscription, $paymentdate, $operation, $label, $amount, $num_chq, $emetteur_nom, $emetteur_banque);
			if ($result < 0) {
				$error++;
				setEventMessages($renter->error, $renter->errors, 'errors');
			} else {
				// If an invoice was created, it is into $object->invoice
			}
		}

		if (!$error) {
			$db->commit();
		} else {
			$db->rollback();
			$action = 'receiptpayment';
		}

		if (!$error) {
			setEventMessages("SubscriptionRecorded", null, 'mesgs');
		}

		// Send email
		if (!$error) {
			// Send confirmation Email
			if ($object->email && $sendalsoemail) {   // $object is 'Adherent'
				$parameters = array(
					'datesubscription' => $datesubscription,
					'amount' => $amount,
					'ccountid' => $accountid,
					'operation' => $operation,
					'label' => $label,
					'num_chq' => $num_chq,
					'emetteur_nom' => $emetteur_nom,
					'emetteur_banque' => $emetteur_banque,
					'datesubend' => $datesubend
				);
				$reshook = $hookmanager->executeHooks('sendMail', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
				if ($reshook < 0) {
					setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				}

				if (empty($reshook)) {
					$subject = '';
					$msg = '';

					// Send subscription email
					include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
					$formmail = new FormMail($db);
					// Set output language
					$outputlangs = new Translate('', $conf);
					$outputlangs->setDefaultLang(empty($renter->thirdparty->default_lang) ? $mysoc->default_lang : $renter->thirdparty->default_lang);
					// Load traductions files required by page
					$outputlangs->loadLangs(array("main", "ultimateimmo@ultimateimmo"));

					// Get email content from template
					$arraydefaultmessage = null;
					$labeltouse = $conf->global->RENTER_EMAIL_TEMPLATE_SUBSCRIPTION;

					if (!empty($labeltouse)) {
						$arraydefaultmessage = $formmail->getEMailTemplate($db, 'renter', $user, $outputlangs, 0, 1, $labeltouse);
					}

					if (!empty($labeltouse) && is_object($arraydefaultmessage) && $arraydefaultmessage->id > 0) {
						$subject = $arraydefaultmessage->topic;
						$msg     = $arraydefaultmessage->content;
					}

					$substitutionarray = getCommonSubstitutionArray($outputlangs, 0, null, $renter);
					complete_substitutions_array($substitutionarray, $outputlangs, $renter);
					$subjecttosend = make_substitutions($subject, $substitutionarray, $outputlangs);
					$texttosend = make_substitutions(dol_concatdesc($msg, $adht->getMailOnSubscription()), $substitutionarray, $outputlangs);

					// Attach a file ?
					$file = '';
					$listofpaths = array();
					$listofnames = array();
					$listofmimes = array();
					if (is_object($renter->invoice) && (!is_object($arraydefaultmessage) || intval($arraydefaultmessage->joinfiles))) {
						$invoicediroutput = $conf->facture->dir_output;
						$fileparams = dol_most_recent_file($invoicediroutput.'/'.$renter->invoice->ref, preg_quote($renter->invoice->ref, '/').'[^\-]+');
						$file = $fileparams['fullname'];

						$listofpaths = array($file);
						$listofnames = array(basename($file));
						$listofmimes = array(dol_mimetype($file));
					}

					$moreinheader = 'X-Dolibarr-Info: send_an_email by adherents/subscription.php'."\r\n";

					$result = $renter->send_an_email($texttosend, $subjecttosend, $listofpaths, $listofmimes, $listofnames, "", "", 0, -1, '', $moreinheader);
					if ($result < 0) {
						$errmsg = $renter->error;
						setEventMessages($renter->error, $renter->errors, 'errors');
					} else {
						setEventMessages($langs->trans("EmailSentToRenter", $renter->email), null, 'mesgs');
					}
				}
			} else {
				setEventMessages($langs->trans("NoEmailSentToRenter"), null, 'mesgs');
			}
		}

		// Clean some POST vars
		if (!$error) {
			$_POST["receiptpayment"] = '';
			$_POST["accountid"] = '';
			$_POST["operation"] = '';
			$_POST["label"] = '';
			$_POST["num_chq"] = '';
		}
	}

	/*if (!$error) {
		//$paymentdate = dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));
		$paymentid = 0;

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
			$payment->date_payment = $paymentdate;
			$payment->amount      = GETPOST("amount");
			$payment->fk_mode_reglement  = GETPOST('operation', 'int');
			$payment->fk_account  = GETPOST('fk_bank', 'int');
			$payment->num_payment  = GETPOST('num_payment', 'int');
			$payment->note_public  = GETPOST('note_public', 'string');
			//var_dump($payment);exit;
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
	}*/

	$_GET["action"] = 'create';
}


/*
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans("Payment"));

// Form to create immoreceipt payment
if (GETPOST('action', 'aZ09') == 'create') {
	$receipt = new ImmoReceipt($db);
	$result = $receipt->fetch($id);

	$total = $receipt->total_amount;

	// Define default choice for complementary actions
	$bankdirect = 0; // 1 means option by default is write to bank direct with no invoice
	$invoiceonly = 0; // 1 means option by default is invoice only
	$bankviainvoice = 0; // 1 means option by default is write to bank via invoice
	if (GETPOST('paymentsave')) {
		if (GETPOST('paymentsave') == 'bankdirect') {
			$bankdirect = 1;
		}
		if (GETPOST('paymentsave') == 'invoiceonly') {
			$invoiceonly = 1;
		}
		if (GETPOST('paymentsave') == 'bankviainvoice') {
			$bankviainvoice = 1;
		}
	} else {
		if (!empty($conf->global->ULTIMATEIMMO_BANK_USE) && $conf->global->ULTIMATEIMMO_BANK_USE == 'bankviainvoice' && !empty($conf->banque->enabled) && !empty($conf->societe->enabled) && isModEnabled('facture')) {
			$bankviainvoice = 1;
		} elseif (!empty($conf->global->ULTIMATEIMMO_BANK_USE) && $conf->global->ULTIMATEIMMO_BANK_USE == 'bankdirect' && !empty($conf->banque->enabled)) {
			$bankdirect = 1;
		} elseif (!empty($conf->global->ULTIMATEIMMO_BANK_USE) && $conf->global->ULTIMATEIMMO_BANK_USE == 'invoiceonly' && !empty($conf->banque->enabled) && !empty($conf->societe->enabled) && isModEnabled('facture')) {
			$invoiceonly = 1;
		}
	}

	print "\n\n<!-- Form add subscription -->\n";

	if ($conf->use_javascript_ajax) {
		//var_dump($bankdirect.'-'.$bankviainvoice.'-'.$invoiceonly.'-'.empty($conf->global->ULTIMATEIMMO_BANK_USE));
		print "\n".'<script type="text/javascript">';
		print '$(document).ready(function () {
					$(".bankswitchclass, .bankswitchclass2").'.(($bankdirect || $bankviainvoice) ? 'show()' : 'hide()').';
					$("#none, #invoiceonly").click(function() {
						$(".bankswitchclass").hide();
						$(".bankswitchclass2").hide();
					});
					$("#bankdirect, #bankviainvoice").click(function() {
						$(".bankswitchclass").show();
						$(".bankswitchclass2").show();
					});
					$("#selectoperation").change(function() {
						var code = $(this).val();
						if (code == "CHQ")
						{
							$(".fieldrequireddyn").addClass("fieldrequired");
							if ($("#fieldchqemetteur").val() == "")
							{
								$("#fieldchqemetteur").val($("#memberlabel").val());
							}
						}
						else
						{
							$(".fieldrequireddyn").removeClass("fieldrequired");
						}
					});
					';
		if (GETPOST('paymentsave')) {
			print '$("#'.GETPOST('paymentsave', 'aZ09').'").prop("checked", true);';
		}
		print '});';
		print '</script>'."\n";
	}

	// Confirm create third party
	if ($action == 'create_thirdparty') {
		$companyalias = '';
		$fullname = $renter->getFullName($langs);

		if ($renter->morphy == 'mor') {
			$companyname = $renter->societe;
			if (!empty($fullname)) {
				$companyalias = $fullname;
			}
		} else {
			$companyname = $fullname;
			if (!empty($renter->societe)) {
				$companyalias = $renter->societe;
			}
		}

		// Create a form array
		$formquestion = array(
			array('label' => $langs->trans("NameToCreate"), 'type' => 'text', 'name' => 'companyname', 'value' => $companyname, 'morecss' => 'minwidth300', 'moreattr' => 'maxlength="128"'),
			array('label' => $langs->trans("AliasNames"), 'type' => 'text', 'name' => 'companyalias', 'value' => $companyalias, 'morecss' => 'minwidth300', 'moreattr' => 'maxlength="128"')
		);
		// If customer code was forced to "required", we ask it at creation to avoid error later
		if (!empty($conf->global->MAIN_COMPANY_CODE_ALWAYS_REQUIRED)) {
			$tmpcompany = new Societe($db);
			$tmpcompany->name = $companyname;
			$tmpcompany->get_codeclient($tmpcompany, 0);
			$customercode = $tmpcompany->code_client;
			$formquestion[] = array(
				'label' => $langs->trans("CustomerCode"),
				'type' => 'text',
				'name' => 'customercode',
				'value' => $customercode,
				'morecss' => 'minwidth300',
				'moreattr' => 'maxlength="128"',
			);
		}
		// @todo Add other extrafields mandatory for thirdparty creation

		print $form->formconfirm($_SERVER["PHP_SELF"] . "?rowid=" . $renter->id, $langs->trans("CreateDolibarrThirdParty"), $langs->trans("ConfirmCreateThirdParty"), "confirm_create_thirdparty", $formquestion, 1);
	}

	if ($result >= 0) {
		//$ret = $paiement->fetch_thirdparty();
		/*$title = '';
		if ($receipt->type != ImmoReceipt::TYPE_CREDIT_NOTE) $title .= $langs->trans("EnterPaymentReceivedFromCustomer");
		if ($receipt->type == ImmoReceipt::TYPE_CREDIT_NOTE) $title .= $langs->trans("EnterPaymentDueToCustomer");
		print load_fiche_titre($title);*/

		$head = immoreceiptPrepareHead($receipt);

		print '<form id="payment_form" name="receiptpayment" action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
		print '<input type="hidden" name="token" value="' . newToken() . '">';
		print '<input type="hidden" name="action" value="receiptpayment">';
		print '<input type="hidden" name="id" value="' . $id . '">';
		print '<input type="hidden" name="socid" value="' . $renter->fk_soc . '">';

		print dol_get_fiche_head($head, 'payment', $langs->trans("ImmoPayment"), -1, 'bill');

		$linkback = '<a href="' . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

		$receipt->fetch_thirdparty();

		$morehtmlref = '<div class="refidno">';
		// Ref renter
		$staticImmorenter = new ImmoRenter($db);
		$staticImmorenter->fetch($receipt->fk_renter);
		$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $staticImmorenter->ref, $receipt, $usercancreate, 'string', '', 0, 1);
		$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $staticImmorenter->ref . ' - ' . $staticImmorenter->getFullName($langs), $receipt, $usercancreate, 'string', '', null, null, '', 1);
		// Thirdparty
		$morehtmlref .= '<br>' . $langs->trans('ThirdParty') . ' : ' . (is_object($receipt->thirdparty) ? $receipt->thirdparty->getNomUrl(1) : '');
		if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $receipt->thirdparty->id > 0) $morehtmlref .= ' (<a href="' . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1) . '?socid=' . $receipt->thirdparty->id . '&search_fk_soc=' . urlencode($receipt->thirdparty->id) . '">' . $langs->trans("OtherReceipts") . '</a>)';
		$morehtmlref .= '</div>';

		dol_banner_tab($receipt, 'ref', $linkback, 1, 'rowid', 'ref', $morehtmlref, '', 0, '', '');

		print '<table class="border centpercent">' . "\n";

		$paymentstatic = new ImmoPayment($db);
		$paymentstatic->fetch($receipt->fk_payment);
		
		// Date payment
		if (GETPOST('paymentyear') && GETPOST('paymentmonth') && GETPOST('paymentday')) {
			$paymentdate = dol_mktime(0, 0, 0, GETPOST('paymentmonth'), GETPOST('paymentday'), GETPOST('paymentyear'));
		}

		// Reference
		$tmpref = GETPOST('ref', 'alpha') ? GETPOST('ref', 'alpha') : $receipt->id;
		print '<tr><td class="titlefieldcreate"><span class="fieldrequired">' . $langs->trans('Reference') . '</span></td><td>' . $tmpref . "</td></tr>\n";
		//var_dump($paymentstatic);exit;
		// Date start period
		print '<tr><td>' . $langs->trans("DateStartPeriod") . "</td><td colspan=2>" . dol_print_date($receipt->date_start, 'day') . "</td></tr>\n";

		// Date start period
		print '<tr><td>' . $langs->trans("DateEndPeriod") . "</td><td colspan=2>" . dol_print_date($receipt->date_end, 'day') . "</td></tr>\n";

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
		print '<tr><td>' . $langs->trans("Amount") . "</td><td colspan=\"2\">" . price($receipt->total_amount, 0, $outputlangs, 1, -1, -1, $langs->trans("Currency".$conf->currency)) . '</td></tr>';

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

		print '<tr><td>' . $langs->trans("AlreadyPaid") . '</td><td colspan="2">' . price($sumpaid, 0, $outputlangs, 1, -1, -1, $langs->trans("Currency".$conf->currency)) . '</td></tr>';
		print '<tr><td class="tdtop">' . $langs->trans("RemainderToPay") . '</td><td colspan="2">' . price($receipt->total_amount - $sumpaid, 0, $outputlangs, 1, -1, -1, $langs->trans("Currency".$conf->currency)) . '</td></tr>';

		print '<tr class="liste_titre">';
		print "<td colspan=\"3\">" . $langs->trans("Payment") . '</td>';
		print '</tr>';

		if ((!empty($conf->banque->enabled) || isModEnabled('facture'))) {
			$company = new Societe($db);
			if ($renter->fk_soc) {
				$result = $company->fetch($renter->fk_soc);
			}

			// Title payments
			//print '<tr><td colspan="2"><b>'.$langs->trans("Payment").'</b></td></tr>';

			// No more action
			print '<tr><td class="tdtop fieldrequired">' . $langs->trans('MoreActions');
			print '</td>';
			print '<td>';
			print '<input type="radio" class="moreaction" id="none" name="paymentsave" value="none"' . (empty($bankdirect) && empty($invoiceonly) && empty($bankviainvoice) ? ' checked' : '') . '>';
			print '<label for="none"> ' . $langs->trans("None") . '</label><br>';
			// Add entry into bank accoun
			if (!empty($conf->banque->enabled)) {
				print '<input type="radio" class="moreaction" id="bankdirect" name="paymentsave" value="bankdirect"' . (!empty($bankdirect) ? ' checked' : '');
				print '><label for="bankdirect">  ' . $langs->trans("MoreActionBankDirect") . '</label><br>';
			}
			// Add invoice with no payments
			if (!empty($conf->societe->enabled) && isModEnabled('facture')) {
				print '<input type="radio" class="moreaction" id="invoiceonly" name="paymentsave" value="invoiceonly"' . (!empty($invoiceonly) ? ' checked' : '');
				//if (empty($object->fk_soc)) print ' disabled';
				print '><label for="invoiceonly"> ' . $langs->trans("MoreActionInvoiceOnly");
				if ($renter->fk_soc) {
					print ' (' . $langs->trans("ThirdParty") . ': ' . $company->getNomUrl(1) . ')';
				} else {
					print ' (';
					if (empty($renter->fk_soc)) {
						print img_warning($langs->trans("NoThirdPartyAssociatedToMember"));
					}
					print $langs->trans("NoThirdPartyAssociatedToMember");
					print ' - <a href="' . $_SERVER["PHP_SELF"] . '?rowid=' . $renter->id . '&amp;action=create_thirdparty">';
					print $langs->trans("CreateDolibarrThirdParty");
					print '</a>)';
				}
				if (empty($conf->global->ULTIMATEIMMO_VAT_FOR_RECEIPTS) || $conf->global->ULTIMATEIMMO_VAT_FOR_RECEIPTS != 'defaultforfoundationcountry') {
					print '. <span class="opacitymedium">' . $langs->trans("NoVatOnSubscription", 0) . '</span>';
				}
				if (!empty($conf->global->ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS) && (!empty($conf->product->enabled) || !empty($conf->service->enabled))) {
					$prodtmp = new Product($db);
					$result = $prodtmp->fetch($conf->global->ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS);
					if ($result < 0) {
						setEventMessage($prodtmp->error, 'errors');
					}
					print '. ' . $langs->transnoentitiesnoconv("ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS", $prodtmp->getNomUrl(1)); // must use noentitiesnoconv to avoid to encode html into getNomUrl of product
				}
				print '</label><br>';
			}
			// Add invoice with payments
			if (!empty($conf->banque->enabled) && !empty($conf->societe->enabled) && isModEnabled('facture')) {
				print '<input type="radio" class="moreaction" id="bankviainvoice" name="paymentsave" value="bankviainvoice"' . (!empty($bankviainvoice) ? ' checked' : '');
				//if (empty($object->fk_soc)) print ' disabled';
				print '><label for="bankviainvoice">  ' . $langs->trans("MoreActionBankViaInvoice");
				if ($renter->fk_soc) {
					print ' (' . $langs->trans("ThirdParty") . ': ' . $company->getNomUrl(1) . ')';
				} else {
					print ' (';
					if (empty($renter->fk_soc)) {
						print img_warning($langs->trans("NoThirdPartyAssociatedToMember"));
					}
					print $langs->trans("NoThirdPartyAssociatedToMember");
					print ' - <a href="' . $_SERVER["PHP_SELF"] . '?rowid=' . $renter->id . '&amp;action=create_thirdparty">';
					print $langs->trans("CreateDolibarrThirdParty");
					print '</a>)';
				}
				if (empty($conf->global->ULTIMATEIMMO_VAT_FOR_RECEIPTS) || $conf->global->ULTIMATEIMMO_VAT_FOR_RECEIPTS != 'defaultforfoundationcountry') {
					print '. <span class="opacitymedium">' . $langs->trans("NoVatOnSubscription", 0) . '</span>';
				}
				if (!empty($conf->global->ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS) && (!empty($conf->product->enabled) || !empty($conf->service->enabled))) {
					$prodtmp = new Product($db);
					$result = $prodtmp->fetch($conf->global->ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS);
					if ($result < 0) {
						setEventMessage($prodtmp->error, 'errors');
					}
					print '. ' . $langs->transnoentitiesnoconv("ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS", $prodtmp->getNomUrl(1)); // must use noentitiesnoconv to avoid to encode html into getNomUrl of product
				}
				print '</label><br>';
			}
			print '</td></tr>';

			// Bank account
			print '<tr class="bankswitchclass"><td class="fieldrequired">' . $langs->trans("FinancialAccount") . '</td><td>';
			print img_picto('', 'bank_account');
			$form->select_comptes(GETPOST('accountid'), 'accountid', 0, '', 2, '', 0, 'minwidth200');
			print "</td></tr>\n";

			// Payment mode
			print '<tr><td class="fieldrequired">' . $langs->trans("PaymentMode") . '</td><td colspan="2">';
			$form->select_types_paiements((GETPOST('operation') ? GETPOST('operation') : $paymentstatic->fk_mode_reglement), 'operation');
			print "</td>\n";
			print '</tr>';

			// Date of payment
			print '<tr><td><span class="fieldrequired">' . $langs->trans('DatePayment') . '</span></td><td>';
			$paymentdate = dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));
			$datepayment = empty($conf->global->MAIN_AUTOFILL_DATE) ? empty(GETPOST('remonth') ? -1 : $paymentdate) : 0;
			print $form->selectDate($datepayment, '', '', '', '', "receiptpayment", 1, 1);
			print '</td></tr>';
			/*print '<tr class="bankswitchclass"><td class="fieldrequired">'.$langs->trans("DatePayment").'</td><td>';
			//$paymentdate = empty($conf->global->MAIN_AUTOFILL_DATE) ? empty(GETPOST('remonth') ? -1 : $paymentdate) : 0;
				print $form->selectDate(isset($paymentdate) ? $paymentdate : -1, '', 0, 0, 1, 'receiptpayment', 1, 1);
				print "</td></tr>\n";*/

			print '<tr class="bankswitchclass2"><td>' . $langs->trans('Numero');
			print ' <em>(' . $langs->trans("ChequeOrTransferNumber") . ')</em>';
			print '</td>';
			print '<td><input id="fieldnum_chq" name="num_chq" type="text" size="8" value="' . (!GETPOST('num_chq') ? '' : GETPOST('num_chq')) . '"></td></tr>';

			print '<tr class="bankswitchclass2 fieldrequireddyn"><td>' . $langs->trans('CheckTransmitter');
			print ' <em>(' . $langs->trans("ChequeMaker") . ')</em>';
			print '</td>';
			print '<td><input id="fieldchqemetteur" name="chqemetteur" size="32" type="text" value="' . (!GETPOST('chqemetteur') ? '' : GETPOST('chqemetteur')) . '"></td></tr>';

			print '<tr class="bankswitchclass2"><td>' . $langs->trans('Bank');
			print ' <em>(' . $langs->trans("ChequeBank") . ')</em>';
			print '</td>';
			print '<td><input id="chqbank" name="chqbank" size="32" type="text" value="' . (!GETPOST('chqbank') ? '' : GETPOST('chqbank')) . '"></td></tr>';
		}

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
				$namef = "amount";
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
