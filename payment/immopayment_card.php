<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2022 Philippe GRAND  <philippe.grand@atoo-net.com>
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
 *   	\file       immopayment_card.php
 *		\ingroup    ultimateimmo
 *		\brief      Page to create/edit/view immopayment
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

include_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
include_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php';
dol_include_once('/ultimateimmo/class/immopayment.class.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/lib/immopayment.lib.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
if (!empty($conf->banque->enabled)) require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
if (!empty($conf->accounting->enabled)) {
	require_once DOL_DOCUMENT_ROOT . '/accountancy/class/accountingjournal.class.php';
}

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo","other", "contracts", "bills"));

// Get parameters
$id			= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$socid 		= GETPOST('socid', 'int');
$accountid	= GETPOST('accountid', 'int');
$fk_mode_reglement	= GETPOST("fk_mode_reglement");
$receipt_id = GETPOST('receipt', 'int');
$search_loyer = GETPOST('search_loyer', 'alpha');
$search_local = GETPOST('search_local', 'alpha');
$search_renter = GETPOST('search_renter', 'alpha');

$paymentmonth = GETPOST("paymentmonth", 'int');
$paymentday = GETPOST("paymentday", 'int');
$paymentyear = GETPOST("paymentyear", 'int');

$button_search_x = GETPOST('button_search_x', 'alpha');
$button_createpdf = GETPOST('button_createpdf', 'alpha');

$createpdf='';

// Array of ids of elements selected into a list
$toselect   = GETPOST('toselect', 'array');

// Initialize technical objects
$object = new ImmoPayment($db);

$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->ultimateimmo->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('immopaymentcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('immopayment');
$search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$result = restrictedArea($user, 'ultimateimmo', $id);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

if (!empty($button_search_x)) {
	$action = 'createall';
}
$hidegeneratedfilelistifempty = 1;
if (!empty($button_createpdf)) {
	$action = 'createpdf';
	$hidegeneratedfilelistifempty = 0;
}

/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$usercancreate = $user->rights->ultimateimmo->write;
	$usercandelete = $user->rights->ultimateimmo->delete;
	$backurlforlist = dol_buildpath('/ultimateimmo/payment/immopayment_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/ultimateimmo/payment/immopayment_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	// Actions cancel, add, update or delete
	include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

	// payments conditions
	if ($action == 'setconditions' && $usercancreate) {
		$object->fetch($id);
		$object->cond_reglement_code = 0; // To clean property
		$object->cond_reglement_id = 0; // To clean property

		$error = 0;

		$db->begin();

		if (!$error) {
			$result = $object->setPaymentTerms(GETPOST('cond_reglement_id', 'int'));
			if ($result < 0) {
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}

		if (!$error) {
			if ($object->date_echeance < $object->date) $object->date_echeance = $object->date;
			$result = $object->update($user);
			if ($result < 0) {
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}

		if ($error) {
			$db->rollback();
		} else {
			$db->commit();
		}
	}

	// payment mode
	elseif ($action == 'setmode' && $usercancreate)
	{
		$result = $object->setPaymentMethods(GETPOST('mode_reglement_id', 'int'));
	}

	// bank account
	elseif ($action == 'setbankaccount' && $usercancreate) {
		$result = $object->setBankAccount(GETPOST('fk_account', 'int'));
	}

	// Actions Create
	/*if ($action == 'add') {
		if ($cancel) {
			$loc = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $receipt->id;
			header("Location: " . $loc);
			exit;
		}

		$date_payment = dol_mktime(0, 0, 0, $paymentmonth, $paymentday, $paymentyear);
		
		/*if ($date_payment == '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Datepaie')), null, 'errors');
			$action = 'create';
		} else {
			//$payment = new ImmoPayment($db);
			$object->ref = ''; // TODO
			$object->fk_rent			= GETPOST("fk_rent");
			$object->fk_property		= GETPOST("fk_property");
			$object->fk_renter			= GETPOST("fk_renter");
			$object->amount			= GETPOST("amount");
			$object->note_public		= GETPOST("note_public");
			$object->date_payment		= $date_payment;
			$object->fk_receipt		= GETPOST("fk_receipt");
			$object->fk_account		= GETPOST("accountid");
			$object->fk_mode_reglement = GETPOST("fk_mode_reglement");
			$object->num_payment		= GETPOST("num_payment");
			$object->fk_owner			= $user->id;

			//var_dump($object);exit;
			if ($date_payment == '') {
				setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Datepaie')), null, 'errors');
				$action = 'create';
			}
			$id = $object->create($user);
			header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $object->fk_receipt);
			if ($id > 0) {
				$label = '(CustomerReceiptPayment)';
				if (GETPOST('type') == ImmoReceipt::TYPE_CREDIT_NOTE) $label = '(CustomerReceiptPaymentBack)';
				$result = $object->addPaymentToBank($user, 'immopayment', $label, $object->fk_bank, '', '');
				if ($result <= 0) {
					$errmsg = $object->errors;
					setEventMessages(null, $errmsg, 'errors');
					$error++;
				}
			} else {
				setEventMessages(null, $object->errors, 'errors');
			}
		//}
	}*/

	/*
 	 *	Delete paiement
 	 */
	/*if ($action == 'delete' && $usercandelete) {
		if ($id) {
			$payment = new ImmoPayment($db);
			$payment->id = $id;
			$id = $payment->delete($user);
			//var_dump($payment);exit;
		}
		header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $receipt_id);
	}*/

	// Update 
	/*if ($action == 'maj') {
		$date_payment = @dol_mktime(0, 0, 0, GETPOST("paymentmonth"), GETPOST("paymentday"), GETPOST("paymentyear"));
		if ($date_payment == '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Datepaie')), null, 'errors');
			$action = 'update';
		} else {
			$payment = new ImmoPayment($db);

			$result = $payment->fetch($id);

			$payment->amount		= GETPOST("amount");
			$payment->note_public	= GETPOST("note_public");
			$payment->date_payment	= $date_payment;

			$result = $payment->update($user);
			header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $receipt_id);
		}
	}*/

	if ($action == 'addall' && empty($button_search_x) && empty($button_createpdf)) {
		$date_payment = dol_mktime(12, 0, 0, GETPOST("paymentmonth"), GETPOST("paymentday"), GETPOST("paymentyear"));

		if ($date_payment == '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('Datepaie')), null, 'errors');
			$action = 'createall';
		} else {
			$datapost = $_POST;
			foreach ($datapost as $key => $value) {
				if (strpos($key, 'receipt_') !== false) {
					$tmp_array = explode('_', $key);

					if (count($tmp_array) > 0) {
						$reference = $tmp_array[1];
						$amount = GETPOST('incomeprice_' . $reference);

						if (!empty($reference) && !empty($amount)) {
							$payment = new ImmoPayment($db);

							$payment->fk_rent			= GETPOST('fk_rent_' . $reference);
							$payment->fk_property		= GETPOST('fk_property_' . $reference);
							$payment->fk_renter			= GETPOST('fk_renter_' . $reference);
							$payment->amount			= price2num($amount);
							$payment->amounts			= array($payment->amount);
							$payment->note_public		= GETPOST('note_public');
							$payment->date_payment		= $date_payment;
							$payment->fk_receipt		= GETPOST('receipt_' . $reference);
							$payment->fk_account		= GETPOST("accountid");
							$payment->fk_mode_reglement	= GETPOST("fk_mode_reglement");
							$payment->num_payment		= GETPOST("num_payment");
							$payment->fk_owner			= $user->id;

							$result = $payment->create($user);
							//var_dump($payment);exit;
							if ($result < 0) {
								setEventMessages(null, $payment->errors, 'errors');
							} else {
								$label = '(CustomerReceiptPayment)';
								if (GETPOST('type') == ImmoReceipt::TYPE_CREDIT_NOTE) $label = '(CustomerReceiptPaymentBack)';
								$result = $payment->addPaymentToBank($user, 'immopayment', $label, $payment->fk_account, '', '');
								
								if ($result <= 0) {
									setEventMessages(null, $payment->errors, 'errors');
									$error++;
								}
							}
						}
					}
				}
			}
		}
		header("Location: " . dol_buildpath('/ultimateimmo/payment/immopayment_list.php', 1));
	}

	/*if ($action == 'update') {
		$date_payment = dol_mktime(12, 0, 0, GETPOST("paymentmonth"), GETPOST("paymentday"), GETPOST("paymentyear"));

		$payment = new ImmoPayment($db);
		$result = $payment->fetch($id);

		$rent = new ImmoRent($db);
		$rent->fetch($payment->fk_rent);

		$payment->ref 		= GETPOST('ref');

		$payment->fk_rent 		= GETPOST("fk_rent");
		$payment->fk_property 	= GETPOST("fk_property");
		$payment->fk_renter 	= GETPOST("fk_renter");
		$payment->fk_soc 		= GETPOST("fk_soc");
		$payment->fk_owner 		= GETPOST("fk_owner");
		$payment->date_echeance = $date_echeance;
		$payment->note_public 	= GETPOST("note_public");
		$payment->status 		= GETPOST("status");
		$payment->date_payment 	= $date_payment;
		$payment->fk_mode_reglement = $fk_mode_reglement;

		$result = $payment->update($user);

		if ($result < 0) {
			setEventMessages(null, $payment->errors, 'errors');
		} else {
			header("Location: " . dol_buildpath('/ultimateimmo/payment/immopayment_card.php', 1) . '?id=' . $payment->id);
		}
	}*/

	if ($action == 'createpdf') {
		$action = 'createall';
		$createpdf = 'createpdf';
	}

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	// Actions to send emails
	$trigger_name = 'IMMOPAYMENT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_IMMOPAYMENT_TO';
	$trackid = 'immopayment' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}


/*
 * View
 *
 */

$form = new Form($db);
$formfile = new FormFile($db);
$thirdpartystatic = new Societe($db);
$bankaccountstatic = new Account($db);

//$result = $object->fetch($id, $ref);
/*if ($result < 0) {
	dol_print_error($db, 'Payement ' . $id . ' not found in database');
	exit;
}*/
if ($id) {
	$object = new ImmoPayment($db);
	$result = $object->fetch($id);
	if ($result <= 0) {
		dol_print_error($db);
		exit;
	}
}

llxHeader('', $langs->trans("ImmoPayment"), '');

$arrayofselected = is_array($toselect) ? $toselect : array();

// Part to create
if ($action == 'create') {
	$receipt = new Immoreceipt($db);
	$result = $receipt->fetch($id);

	$total = $receipt->total_amount;

	// Update fields properties in realtime
	if (!empty($conf->use_javascript_ajax)) {
		print "\n".'<script type="text/javascript">';
		print '$(document).ready(function () {
            			setPaymentType();
            			$("#selectpaymenttype").change(function() {
            				setPaymentType();
            			});
            			function setPaymentType()
            			{
							console.log("setPaymentType");
            				var code = $("#selectpaymenttype option:selected").val();
                            if (code == \'CHQ\' || code == \'VIR\')
            				{
            					if (code == \'CHQ\')
			                    {
			                        $(\'.fieldrequireddyn\').addClass(\'fieldrequired\');
			                    }
            					if ($(\'#fieldchqemetteur\').val() == \'\')
            					{
            						var emetteur = jQuery(\'#thirdpartylabel\').val();
            						$(\'#fieldchqemetteur\').val(emetteur);
            					}
            				}
            				else
            				{
            					$(\'.fieldrequireddyn\').removeClass(\'fieldrequired\');
            					$(\'#fieldchqemetteur\').val(\'\');
            				}
            			}
			';

		print '	});'."\n";

		print '	</script>'."\n";
	}

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="fk_rent" value="' . $receipt->fk_rent . '">';
	print '<input type="hidden" name="fk_property" value="' . $receipt->fk_property . '">';
	print '<input type="hidden" name="fk_renter" value="' . $receipt->fk_renter . '">';
	print '<input type="hidden" name="fk_receipt" value="' . $id . '">';
	print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("ImmoPayment")), '', 'object_' . $object->picto);

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	// Common attributes
	$object->fields = dol_sort_array($object->fields, 'position');
	
	foreach ($object->fields as $key => $val) {
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1) continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) continue;	// We don't want this field

		print '<tr id="field_' . $key . '">';
		print '<td';
		print ' class="titlefieldcreate';
		if ($val['notnull'] > 0) print ' fieldrequired';
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '"';
		print '>';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '</td>';

		print '<td>';
		
		if ($val['label'] == 'DatePayment') {
			print $form->selectDate((empty($date_payment) ?-1 : $date_payment), "date_payment", '', '', '', 'add', 1, 1);
		} elseif ($val['label'] == 'TypePayment') {
			// Payment mode
			if ($object->fk_mode_reglement) $selected = $object->fk_mode_reglement;
			else $selected = '';
			$form->select_types_paiements($selected, 'paiementtype', 'CRDT', 0, 1);
		} elseif ($val['label'] == 'BankAccount') {
			//BankAccount
			if (!empty($conf->banque->enabled)) {
				/*if ($receipt->type != 2) print '<span class="fieldrequired">' . $langs->trans('AccountToCredit') . '</span>';
				if ($receipt->type == 2) print '<span class="fieldrequired">' . $langs->trans('AccountToDebit') . '</span>';*/

				$form->select_comptes($accountid, 'accountid', 0, '', 2);
			}
		} else {
			if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
			elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
			else $value = GETPOST($key, 'alpha');
			print $object->showInputField($val, $key, $value, '', '', '', 0);
		}
		print '</td>';
		print '</tr>';
	}

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

	print '</table>' . "\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("ImmoPayment"), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	$object->fields = dol_sort_array($object->fields, 'position');

	foreach ($object->fields as $key => $val) {
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4) {
			continue;
		}

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) {
			continue; // We don't want this field
		}

		print '<tr class="field_' . $key . '"><td';
		print ' class="titlefieldcreate';
		if (isset($val['notnull']) && $val['notnull'] > 0) {
			print ' fieldrequired';
		}
		if (preg_match('/^(text|html)/', $val['type'])) {
			print ' tdtop';
		}
		print '">';
		if (!empty($val['help'])) {
			print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		} else {
			print $langs->trans($val['label']);
		}
		print '</td>';
		print '<td class="valuefieldcreate">';

		if ($val['label'] == 'BankAccount') {
			//BankAccount
			if (!empty($conf->banque->enabled)) {
				$form->select_comptes($accountid, 'accountid', 0, '', 2);
			}
		} elseif ($val['label'] == 'TypePayment') {
			// Payment mode
			if ($object->fk_mode_reglement) $selected = $object->fk_mode_reglement;
			else $selected = '';
			$form->select_types_paiements($selected, 'fk_mode_reglement', 'CRDT', 0, 1);
		} else {
			if (!empty($val['picto'])) {
				print img_picto('', $val['picto'], '', false, 0, 0, '', 'pictofixedwidth');
			}
			if (in_array($val['type'], array('int', 'integer'))) {
				$value = GETPOSTISSET($key) ? GETPOST($key, 'int') : $object->$key;
			} elseif ($val['type'] == 'double') {
				$value = GETPOSTISSET($key) ? price2num(GETPOST($key, 'alphanohtml')) : $object->$key;
			} elseif (preg_match('/^(text|html)/', $val['type'])) {
				$tmparray = explode(':', $val['type']);
				if (!empty($tmparray[1])) {
					$check = $tmparray[1];
				} else {
					$check = 'restricthtml';
				}
				$value = GETPOSTISSET($key) ? GETPOST($key, $check) : $object->$key;
			} elseif ($val['type'] == 'price') {
				$value = GETPOSTISSET($key) ? price2num(GETPOST($key)) : price2num($object->$key);
			} elseif ($key == 'lang') {
				$value = GETPOSTISSET($key) ? GETPOST($key, 'aZ09') : $object->lang;
			} else {
				$value = GETPOSTISSET($key) ? GETPOST($key, 'alpha') : $object->$key;
			}
			//var_dump($val.' '.$key.' '.$value);
			if (!empty($val['noteditable'])) {
				print $object->showOutputField($val, $key, $value, '', '', '', 0);
			} else {
				if ($key == 'lang') {
					print img_picto('', 'language', 'class="pictofixedwidth"');
					print $formadmin->select_language($value, $key, 0, null, 1, 0, 0, 'minwidth300', 2);
				} else {
					print $object->showInputField($val, $key, $value, '', '', '', 0);
				}
			}
			print '</td>';
			print '</tr>';
		}
	}

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals($object->id, $extralabels);

	$head = immopaymentPrepareHead($object);

	print dol_get_fiche_head($head, 'card', $langs->trans("ImmoPayment"), -1, 'payment');

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteImmoPayment'), $langs->trans('ConfirmDeleteImmoPayment'), 'confirm_delete', '', 0, 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx') {
		$formquestion = array();
		/*
	        $formquestion = array(
	            // 'text' => $langs->trans("ConfirmClone"),
	            // array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
	            // array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
	            // array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1)));
	    }*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	if (!$formconfirm) {
		$parameters = array('lineid' => '');
		$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if (empty($reshook)) {
			$formconfirm .= $hookmanager->resPrint;
		} elseif ($reshook > 0) {
			$formconfirm = $hookmanager->resPrint;
		}
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/ultimateimmo/payment/immopayment_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($_SESSION['last_restore']) ? 1 : 0) . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';
	$payment = new ImmoPayment($db);
	$payment->fetch($id);
	$receipt = new ImmoReceipt($db);
	$result = $receipt->fetch($payment->fk_rent);
	$morehtmlref .= $receipt->label;
	$morehtmlref .= '</div>';

	//$morehtmlleft = '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><span class="fas fa-money-check-alt infobox-bank_account" style="" title="No photo"></span></div></div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, '', 0, $morehtmlleft);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">' . "\n";

	// Common attributes
	$keyforbreak = 'date_payment';

	//$object->fields = dol_sort_array($object->fields, 'position');

	foreach ($object->fields as $key => $val) {
		if (!empty($keyforbreak) && $key == $keyforbreak) break; // key used for break on second column

		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4 && abs($val['visible']) != 5) continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) continue;	// We don't want this field
		if (in_array($key, array('ref', 'status'))) continue;	// Ref and status are already in dol_banner

		$value = $object->$key;

		print '<tr><td';
		print ' class="titlefield fieldname_' . $key;
		//if ($val['notnull'] > 0) print ' fieldrequired';     // No fieldrequired on the view output
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '">';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '</td>';
		print '<td class="valuefield fieldname_' . $key;
		if ($val['type'] == 'text') print ' wordbreak';
		print '">';
		print '<td>';

		if ($val['label'] == 'Owner') {
			$staticowner = new ImmoOwner($db);
			$staticowner->fetch($object->fk_owner);
			if ($staticowner->ref) {
				$staticowner->ref = $staticowner->getNomUrl(0) . ' - ' . $staticowner->getFullName($langs, 0);
			}
			print $staticowner->ref;
		} elseif ($val['label'] == 'Property') {
			$staticproperty = new ImmoProperty($db);
			$staticproperty->fetch($object->fk_property);
			if ($staticproperty->ref) {
				$staticproperty->ref = $staticproperty->getNomUrl(0) . ' - ' . $staticproperty->label;
			}
			print $staticproperty->ref;
		} elseif ($val['label'] == 'Renter') {
			$staticrenter = new ImmoRenter($db);
			$staticrenter->fetch($object->fk_renter);
			if ($staticrenter->ref) {
				$staticrenter->ref = $staticrenter->getNomUrl(0) . ' - ' . $staticrenter->getFullName($langs);
			}
			print $staticrenter->ref;
		} else {
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		}
		//print dol_escape_htmltag($object->$key, 1, 1);
		print '</td>';
		print '</tr>';

		if (!empty($keyforbreak) && $key == $keyforbreak) break;						// key used for break on second column
	}

	print '</table>';

	// We close div and reopen for second column
	print '</div>';
	print '<div class="fichehalfright">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">';

	$alreadyoutput = 1;
	foreach ($object->fields as $key => $val) {
		if ($alreadyoutput) {
			if (!empty($keyforbreak) && $key == $keyforbreak) {
				$alreadyoutput = 0; // key used for break on second column
			} else {
				continue;
			}
		}

		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4 && abs($val['visible']) != 5) continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !$val['enabled']) continue;	// We don't want this field
		if (in_array($key, array('ref', 'status'))) continue;	// Ref and status are already in dol_banner

		$value = $object->$key;

		print '<tr><td';
		print ' class="titlefield fieldname_' . $key;
		//if ($val['notnull'] > 0) print ' fieldrequired';		// No fieldrequired in the view output

		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '">';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '</td>';
		print '<td>';
		if ($val['label'] == 'TypePayment') {

			if ($object->fk_mode_reglement) {
				$tmparray = $object->setPaymentMethods($object->fk_mode_reglement, 'int');
				$object->mode_code = $tmparray['code'];
				$object->mode_payment = $tmparray['libelle'];
			}
			// Payment mode
			print $object->mode_payment;
			//$form->form_modes_reglement($_SERVER['PHP_SELF'].'?id='.$object->id, $object->fk_mode_reglement, 'none', '', -1);
		} else {
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		}

		//var_dump($val.' '.$key.' '.$value);
		print '</td>';
		print '</tr>';
	}

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div><br>';

	print dol_get_fiche_end();

	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
		if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

		if (empty($reshook)) {
			// Send
			print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>' . "\n";

			if ($user->rights->ultimateimmo->write) {
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a>' . "\n";
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Modify') . '</a>' . "\n";
			}

			/*
    		if ($user->rights->ultimateimmo->create)
    		{
    			if ($object->status == 1)
    		 	{
    		 		print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=disable">'.$langs->trans("Disable").'</a>'."\n";
    		 	}
    		 	else
    		 	{
    		 		print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=enable">'.$langs->trans("Enable").'</a>'."\n";
    		 	}
    		}
    		*/

			if ($user->rights->ultimateimmo->delete) {
				print '<a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a>' . "\n";
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Delete') . '</a>' . "\n";
			}
		}
		print '</div>' . "\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	/*if ($action != 'presend')
	{
	    print '<div class="fichecenter"><div class="fichehalfleft">';
	    print '<a name="builddoc"></a>'; // ancre

	    // Documents
	    $relativepath = '/payment/' . dol_sanitizeFileName($object->ref).'/';
	    $filedir = $conf->ultimateimmo->dir_output . $relativepath;
	    $urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
	    $genallowed = $user->rights->ultimateimmo->read;	// If you can read, you can build the PDF to read content
	    $delallowed = $user->rights->ultimateimmo->write;	// If you can create/edit, you can remove a file on card
	    print $formfile->showdocuments('ultimateimmo', $relativepath, $filedir, $urlsource, 0, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);

	    // Show links to link elements
	    $linktoelem = $form->showLinkToObjectBlock($object, null, array('immopayment'));
	    $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


	    print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	    $MAXEVENT = 10;

	    $morehtmlright = '<a href="'.dol_buildpath('/ultimateimmo/payment/immopayment_info.php', 1).'?id='.$object->id.'">';
	    $morehtmlright.= $langs->trans("SeeAll");
	    $morehtmlright.= '</a>';

	    // List of actions on element
	    include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
	    $formactions = new FormActions($db);
	    $somethingshown = $formactions->showactions($object, 'immopayment', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

	    print '</div></div></div>';
	}*/

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'immopayment';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->ultimateimmo->dir_output . '/payment';
	$trackid = 'immo' . $object->id;

	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}


/* *************************************************************************** */
/*                                                                             */
/* Mode add all payments                                                       */
/*                                                                             */
/* *************************************************************************** */

if ($action == 'createall') {

	$param = '';

	/*
	 * List receipt
	 */
	$sql = "SELECT rec.rowid as reference, rec.label as receiptname, loc.lastname as nom, prop.address, prop.label as local, prop.fk_owner as proprio, loc.status as status, rec.total_amount as total, rec.partial_payment, rec.balance, rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_rent as refcontract";
	$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as rec";
	//$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p ON rec.rowid = p.fk_receipt";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc ON loc.rowid = rec.fk_renter";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as prop ON prop.rowid = rec.fk_property";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent ON rent.rowid = rec.fk_rent";
	$sql .= " WHERE rec.paye <> 1 ";
	//print_r($sql);exit;

	if (!empty($search_loyer)) {
		$sql .=  natural_search('rec.label', $search_loyer);
		$param .= '&search_loyer='.$search_loyer;
	}
	if (!empty($search_local)) {
		$sql .=  natural_search('prop.label', $search_local);
		$param .= '&search_local='.$search_local;
	}
	if (!empty($search_renter)) {
		$sql .=  natural_search('loc.lastname', $search_renter);
		$param .= '&search_renter='.$search_renter;
	}

	if ($createpdf == 'createpdf') {

		if (empty($diroutputmassaction)) {
			dol_print_error(null, 'include of actions_massactions.inc.php is done but var $diroutputmassaction was not defined');
			exit;
		}

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

		// Create empty PDF
		$formatarray = pdf_getFormat();
		$page_largeur = $formatarray['width'];
		$page_hauteur = $formatarray['height'];
		$format = array($page_largeur, $page_hauteur);

		$pdf = pdf_getInstance($format);

		if (class_exists('TCPDF'))
		{
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
		}
		$outputlangs = $langs;
		$newlang = '';
		if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
		//if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang = $objecttmp->thirdparty->default_lang;
		if (!empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
		}
		$pdf->SetFont(pdf_getPDFFont($outputlangs));

		if (!empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

		// Create output dir if not exists
		dol_mkdir($diroutputmassaction);

		$object->sqlquerymassgen = $sql;

		$result = $object->generateDocument('etatpaiement:etatpaiement_' . dol_sanitizeFileName(dol_print_date(dol_now())), $outputlangs);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$action = 'createall';
	}

	print '<form name="fiche_payment" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="addall">';

	print '<table class="border" width="100%">';

	print "<tr class=\"liste_titre\">";

	print '<td class="left">';
	print $langs->trans("DatePayment");
	print '</td><td class="left">';
	print $langs->trans("Comment");
	print '</td><td class="left">';
	print $langs->trans("PaymentMode");
	print '</td><td class="left">';
	print $langs->trans("BankAccount");
	print '</td><td class="left">';
	print $langs->trans("Numero");
	print '</td>';
	print "</tr>\n";

	print '<tr class="oddeven" valign="top">';
	$payment = new ImmoPayment($db);
	$result = $payment->fetch($id, $ref);

	// Due date	
	print '<td class="left">';
	print $form->selectDate(!empty($date_payment) ? $date_payment : '-1', 'payment', 0, 0, 0, 'addall', 1);
	print '</td>';

	// note_public
	print '<td><input name="note_public" size="30" value="' . GETPOST('note_public') . '"</td>';

	// Payment mode
	print '<td class="left">';
	print $form->select_types_paiements(GETPOST('fk_mode_reglement', 'int') ? GETPOST('fk_mode_reglement', 'int') : $payment->fk_mode_reglement, "fk_mode_reglement");
	print '</td>';

	// AccountToCredit
	print '<td class="left">';
	print $form->select_comptes(GETPOSTISSET('accountid', 'int') ? GETPOST('accountid', 'int') : $payment->fk_bank, "accountid", 0, '', 1);  // Show open bank account list
	print '</td>';
	
	// num_payment
	print '<td><input name="num_payment" size="30" value="' . GETPOST('num_payment') . '"</td>';

	print "</tr>\n";

	$resql = $db->query($sql);

	if ($resql) {
		$num = $db->num_rows($resql);

		$i = 0;
		$total = $total_montant_tot = $total_payed = $total_due = 0;

		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td><input type="text" name="search_loyer" id="search_loyer" value="'.$search_loyer.'"></td>';
		print '<td><input type="text" name="search_local" id="search_local" value="'.$search_local.'"></td>';
		print '<td><input type="text" name="search_renter" id="search_renter" value="'.$search_renter.'"></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td></td>';
		// Action column
		print '<td class="liste_titre maxwidthsearch">';
		$searchpicto = $form->showFilterButtons();
		print $searchpicto;
		print '</td>';
		print "</tr>\n";

		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans('NomLoyer') . '</td>';
		print '<td>' . $langs->trans('Nomlocal') . '</td>';
		print '<td>' . $langs->trans('Renter') . '</td>';
		print '<td>' . $langs->trans('Owner') . '</td>';
		print '<td class="right">' . $langs->trans('TotalAmount') . '</td>';
		print '<td class="right">' . $langs->trans('PartialPayment') . '</td>';
		print '<td class="right">' . $langs->trans('Balance') . '</td>';
		print '<td class="right">' . $langs->trans('income') . '</td>';
		print '<td>';
		print $form->showCheckAddButtons('checkforselect', 1);
		print '</td>';
		print "</tr>\n";

		if ($num > 0) {
			while ($i < $num) {
				$objp = $db->fetch_object($resql);
				print '<tr class="oddeven">';
	
				print '<td>' . $objp->receiptname . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->nom . '</td>';
				print '<td>' . $objp->proprio . '</td>';

				print '<td class="right">' .  price($objp->total, 0, '', 1, -1, -1, $conf->currency) . '</td>';
				print '<td class="right">' . price($objp->partial_payment, 0, '', 1, -1, -1, $conf->currency) . '</td>';
				print '<td class="right">' .price($objp->balance, 0, '', 1, -1, -1, $conf->currency)  . '</td>';

				print '<input type="hidden" name="fk_rent_' . $objp->reference . '" size="10" value="' . $objp->refcontract . '">';
				print '<input type="hidden" name="fk_property_' . $objp->reference . '" size="10" value="' . $objp->reflocal . '">';
				print '<input type="hidden" name="fk_renter_' . $objp->reference . '" size="10" value="' . $objp->reflocataire . '">';
				print '<input type="hidden" name="fk_owner_' . $objp->reference . '" size="10" value="' . $objp->proprio . '">';
				print '<input type="hidden" name="receipt_' . $objp->reference . '" size="10" value="' . $objp->reference . '">';

				// Colonne imput income
				print '<td class="right">';
				print '<input type="text" name="incomeprice_' . $objp->reference . '" id="incomeprice_' . $objp->reference . '" size="6" value="" class="flat">';
				print '</td>';
				
				// Action column
				print '<td class="nowrap center">';
				if (in_array($objp->reference, $arrayofselected)) $selected = 1;
				print '<input id="cb' . $objp->reference . '" class="flat checkforselect" type="checkbox" name="toselect[]" value="' . $objp->reference . '"' . ($selected ? ' checked="checked"' : '') . '>';
				print '</td>';

				print '</tr>';

				$i++;

				$total_montant_tot += $objp->total;
				$total_payed += $objp->partial_payment;
				$total_due += $objp->balance;
			}
		}

		// Show total line
		print '<tr class="liste_total">';
		print '<td class="left">' . $langs->trans("Total") . '</td>';
		print '<td colspan="3"></td>';
		print '<td class="right">' . price($total_montant_tot, 0, '', 1, -1, -1, $conf->currency) . '</td>';
		print '<td class="right">'.price($total_payed, 0, '', 1, -1, -1, $conf->currency).'</td>';
		print '<td class="right">'.price($total_due, 0, '', 1, -1, -1, $conf->currency).'</td>';
		print '<td class="left"></td>';
		print '<td class="left"></td>';
		print '</tr>';

		print "</table>\n";
		$db->free($resql);
	} else {
		dol_print_error($db);
	}
	print '<div class="tabsAction">' . "\n";
	print '<div class="inline-block divButAction"><input type="submit"  name="button_addallpayment" id="button_addallpayment" class="butAction" value="' . $langs->trans("ValidatePayment") . '" /></div>';
	print '<div class="inline-block divButAction"><input type="submit"  name="button_createpdf" id="button_createpdf" class="butAction" value="' . $langs->trans("CreatePDF") . '" /></div>';
	print '</div>';
	print '</form>';

	$hidegeneratedfilelistifempty = 1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) {
		$hidegeneratedfilelistifempty = 0;
	}

	// Show list of available documents
	$urlsource = $_SERVER['PHP_SELF'].'&action=createall';

	$filedir = $conf->ultimateimmo->dir_output . '/rentmassgen/';
	$genallowed = $user->rights->ultimateimmo->write;
	$delallowed = $user->rights->ultimateimmo->delete;
	$title = '';

	print $formfile->showdocuments('ultimateimmo', 'rentmassgen', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '', '', '', null, $hidegeneratedfilelistifempty, 'remove_file');
}

/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */
if ($action == 'update') {
	$receipt = new ImmoReceipt($db);
	$result = $receipt->fetch($receipt_id);

	$payment = new ImmoPayment($db);

	$result = $payment->fetch($id);

	print '<form action="card.php" method="post">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";
	print '<input type="hidden" name="receipt" value="' . $receipt_id . '">' . "\n";

	print '<table class="border" width="100%">';

	print '<input type="hidden" name="fk_contract" size="10" value="' . $receipt->fk_contract . '">';
	print '<input type="hidden" name="fk_property" size="10" value="' . $receipt->fk_property . '">';
	print '<input type="hidden" name="fk_renter" size="10" value="' . $receipt->fk_renter . '">';
	print '<input type="hidden" name="fk_receipt" size="10" value="' . $id . '">';

	print '<tr><td class="titlefield">' . $langs->trans("NomAppartement") . '</td><td>' . $receipt->nomlocal . '</td></tr>';

	print '<tr><td>' . $langs->trans("NomLocataire") . '</td><td>' . $receipt->nomlocataire . '</td></tr>';

	print '<tr><td>' . $langs->trans("RefLoyer") . '</td><td>' . $receipt->name . '</td></tr>';

	print '<tr><td>' . $langs->trans("Amount") . '</td>';
	print '<td><input name="amount" size="30" value="' . round($payment->amount, 2) . '"</td></tr>';

	print '<tr><td>' . $langs->trans("Comment") . '</td>';
	print '<td><input name="comment" size="10" value="' . $payment->comment . '"</td></tr>';

	print '<tr><td>' . $langs->trans("DatePaiement") . '</td>';
	print '<td align="left">';
	print $form->selectDate(!empty($payment->date_payment) ? $payment->date_payment : '-1', 'payment', 0, 0, 0, 'card', 1);
	print '</td>';

	print '</table>';
	
	print dol_get_fiche_end();

	print '<div align="center">';
	print '<input type="submit" value="' . $langs->trans("AddProperty") . '" name="bouton" class="button" />';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="' . $langs->trans("Cancel") . '" class="button" onclick="history.go(-1)" />';
	print '</div>';

	print '</form>';
}

// End of page
llxFooter();
$db->close();
