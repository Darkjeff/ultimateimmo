<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021 Philippe GRAND  <philippe.grand@atoo-net.com>
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
 *    \file       immoreceipt_card.php
 *        \ingroup    ultimateimmo
 *        \brief      Page to create/edit/view immoreceipt
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');
include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php');
include_once(DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php');
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';
if (!empty($conf->accounting->enabled)) {
	require_once DOL_DOCUMENT_ROOT . '/accountancy/class/accountingjournal.class.php';
}
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/lib/immoreceipt.lib.php');
dol_include_once('/ultimateimmo/core/modules/ultimateimmo/modules_ultimateimmo.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immopayment.class.php');

// Load translation files required by the page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other", "compta", "bills", "contracts"));

// Get parameters
$id = (GETPOST('id', 'int') ? GETPOST('id', 'int') : GETPOST('recid', 'int'));
$rowid = GETPOST('rowid', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$toselect = GETPOST('toselect', 'array');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'immoreceiptcard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$search_fk_soc = GETPOST('search_fk_soc', 'alpha');

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
$userid = GETPOST('userid', 'int');
$begin = GETPOST('begin');
if (!$sortorder) $sortorder = "ASC";
if (!$sortfield) $sortfield = "own.lastname";
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	$page = 0;
}
$offset = $limit * $page;

// Initialize technical objects
$object = new ImmoReceipt($db);
$immorent = new ImmoRent($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->ultimateimmo->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('immoreceiptcard', 'globalcard'));     // Note that conf->hooks_modules contains array
//var_dump($object->fields);exit;
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_' . $key, 'alpha')) $search[$key] = GETPOST('search_' . $key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

$permissiontoread = $user->rights->ultimateimmo->read;
$permissiontoadd = $user->rights->ultimateimmo->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->ultimateimmo->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->ultimateimmo->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->ultimateimmo->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->ultimateimmo->multidir_output[isset($object->entity) ? $object->entity : 1];


/**
 * Actions
 *
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {

	if ($action == 'regulcharge_confirm') {
		//$dt_start = dol_mktime(0, 0, 0, GETPOST('dt_startmonth', 'int'), GETPOST('dt_startday', 'int'), GETPOST('dt_startyear', 'int'));
		$dt_start =GETPOST('dt_start','int');
		//$dt_end = dol_mktime(23, 59, 59, GETPOST('dt_endmonth', 'int'), GETPOST('dt_endday', 'int'), GETPOST('dt_endyear', 'int'));
		$dt_end =GETPOST('dt_end','int');
		$chargeAmount = GETPOST('total_charge', 'int');
		$receipt = new ImmoReceipt($db);
		$resultData = $receipt->fetchAll('', '', 0, 0, array('t.fk_rent' => $object->fk_rent, 'finddate' => array('dtstart' => $dt_start, 'dtend' => $dt_end)));
		$totalChargeAllocated = 0;
		if (!is_array($resultData) && $resultData < 0) {
			setEventMessages($receipt->error, $receipt->errors, 'errors');
		} else {
			if (count($resultData) > 0) {
				foreach ($resultData as $receiptUnit) {
					$totalChargeAllocated += (float)$receiptUnit->chargesamount;
				}
			}
		}
		$newCharge = price2num($chargeAmount) - $totalChargeAllocated;
		$object->chargesamount = $newCharge;
		$object->note_private = $langs->trans('NoteRegulCharge', dol_print_date($dt_start), dol_print_date($dt_end), price($totalChargeAllocated) . ' €');
		$object->note_private = dol_concatdesc($object->note_private, $langs->trans('NoteRegulTotalCharge', $chargeAmount) . ' €');
		$object->note_private = dol_concatdesc($object->note_private, $langs->trans('NoteRegulLetToPaid', $newCharge) . ' €');
		$object->note_private = dol_concatdesc($object->note_private, $langs->trans('RegulChargeStepDesc', GETPOST('first_qty'),GETPOST('end_qty')) . ' €');
		$result = $object->update($user);

		if ($result < 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$object->fetch($object->id);
		$rent = new ImmoRent($db);
		$result = $rent->fetch($object->fk_rent);
		if ($result < 0) {
			setEventMessages($rent->error, $rent->errors, 'errors');
		} else {
			$rent->date_last_regul_charge = $dt_end;
			$result = $rent->update($user);
			if ($result < 0) {
				setEventMessages($rent->error, $rent->errors, 'errors');
			}
		}
	}


	/**
	 *    Classify paid
	 */
	if ($action == 'paid') {
		$receipt = new ImmoReceipt($db);
		$receipt->fetch($id);
		$result = $receipt->set_paid($user);
		Header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id);
	}

	/**
	 *    Delete rental
	 */
	if ($action == 'confirm_delete' && $_REQUEST["confirm"] == 'yes' && $permissiontodelete) {
		$receipt = new ImmoReceipt($db);
		$receipt->fetch($id);
		$result = $receipt->delete($user);
		if ($result > 0) {
			header("Location:" . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1));
			exit();
		} else {
			$langs->load("errors");
			setEventMessages(null, $receipt->errors, 'errors');
		}
	} // Delete payment
	elseif ($action == 'confirm_delete_paiement' && $confirm == 'yes' && $permissiontodelete) {
		$receipt->fetch($id);
		if ($receipt->status == ImmoReceipt::STATUS_VALIDATED && $receipt->paye == 0) {
			$paiement = new ImmoPayment($db);
			$result = $paiement->fetch(GETPOST('paiement_id'));
			if ($result > 0) {
				$result = $paiement->delete($user); // If fetch ok and found
				header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
			}
			if ($result < 0) {
				setEventMessages(null, $paiement->errors, 'errors');
			}
		}
	}

	// Validation
	if ($action == 'confirm_validate' && $confirm == 'yes' && $permissiontoadd) {
		$result = $object->validate($user);

		if ($result >= 0) {
			if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
				// Define output language
				$outputlangs = $langs;
				$newlang = '';
				if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
				if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang = $object->thirdparty->default_lang;
				if (!empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}
				$model = $object->model_pdf;

				$ret = $object->fetch($id); // Reload to get new records
				$object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
			}
		} else {
			$langs->load("errors");
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}

	/**
	 * Action generate quittance
	 */
	if ($action == 'quittance') {
		// Define output language
		$outputlangs = $langs;

		$file = 'quittance_' . $id . '.pdf';

		$result = ultimateimmo_pdf_create($db, $id, '', 'quittance', $outputlangs, $file);

		//$result = generateDocument( 'quittance', $outputlangs,0,0,0,null);

		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id);
			exit();
		} else {
			setEventMessages($langs->trans("ErrorFieldRequired"), null, 'errors');
		}
	}

	/**
	 * Action generate charge locative
	 */
	if ($action == 'chargeloc') {
		// Define output language
		$outputlangs = $langs;

		$file = 'chargeloc_' . $id . '.pdf';

		$result = ultimateimmo_pdf_create($db, $id, '', 'chargeloc', $outputlangs, $file);

		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id);
			exit();
		} else {
			setEventMessages($langs->trans("ErrorFieldRequired"), null, 'errors');
		}
	}

	$error = 0;

	$backurlforlist = dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . ($id > 0 ? $id : '__ID__');
		}
	}
	$triggermodname = 'ULTIMATEIMMO_IMMORECEIPT_MODIFY';    // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, delete or clone
	include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';        // Must be include, not include_once

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

	// Action clone object
	if ($action == 'confirm_clone' && $confirm == 'yes' && $permissiontoadd) {
		$objectutil = dol_clone($object, 1);   // To avoid to denaturate loaded object when setting some properties for clone. We use native clone to keep this->db valid.
		$objectutil->date = dol_mktime(12, 0, 0, GETPOST('newdatemonth', 'int'), GETPOST('newdateday', 'int'), GETPOST('newdateyear', 'int'));
		$objectutil->socid = $socid;

		$result = $objectutil->createFromClone($user, $id);
		if ($result > 0) {
			header("Location: " . $_SERVER['PHP_SELF'] . '?recid=' . $result);
			exit();
		} else {
			$langs->load("errors");
			if (count($object->errors) > 0) setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		}
	}

	/*
	 * Add rental
	 */
	if ($action == 'add' && !$cancel) {
		$error = 0;

		$date_echeance = dol_mktime(12, 0, 0, GETPOST("date_echeancemonth"), GETPOST("date_echeanceday"), GETPOST("date_echeanceyear"));
		$date_start = dol_mktime(12, 0, 0, GETPOST("date_startmonth"), GETPOST("date_startday"), GETPOST("date_startyear"));
		$date_end = dol_mktime(12, 0, 0, GETPOST("date_endmonth"), GETPOST("date_endday"), GETPOST("date_endyear"));

		$object->ref = '(PROV)';
		$object->label = GETPOST("label");
		$object->date_start = $date_start;
		$object->date_end = $date_end;
		$object->date_echeance = $date_echeance;
		$object->fk_rent = GETPOST("fk_rent");
		$object->fk_property = GETPOST("fk_property");
		$object->fk_renter = GETPOST("fk_renter");
		$object->fk_owner = GETPOST("fk_owner");
		$object->fk_soc = GETPOST("fk_soc");
		$object->fk_owner = GETPOST("fk_owner");
		$object->note_public = GETPOST("note_public");
		$object->note_private = GETPOST("note_private");
		$object->date_creation = GETPOST("date_creation");
		$object->date_validation = GETPOST("date_validation");
		$object->rentamount = GETPOST("rentamount");
		$object->chargesamount = GETPOST("chargesamount");
		$object->partial_payment = GETPOST("partial_payment");
		$object->fk_payment = GETPOST("fk_payment");
		$object->paye = GETPOST("paye");
		$object->vat_amount = GETPOST("vat_amount");
		$object->vat_tx = GETPOST("vat_tx");
		//$object->fk_statut = GETPOST("fk_statut");
		$object->fk_user_creat = GETPOST("fk_user_creat");
		$object->fk_user_modif = GETPOST("fk_user_modif");
		$object->fk_user_valid = GETPOST("fk_user_valid");
		$object->model_pdf = GETPOST("modelpdf");
		$object->last_main_doc = GETPOST("last_main_doc");
		$object->status = GETPOST("status");

		if ($date_echeance == '' || $date_start == '' || $date_end == '') {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Date")), null, 'errors');
			$action = "create";
			$error++;
		}

		if (!$error) {
			$db->begin();

			$ret = $object->create($user);
			if ($ret > 0) {
				$db->commit();
				header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1));
				exit();
			} else {
				$db->rollback();
				setEventMessages(null, $object->errors, 'errors');
				$action = "create";
			}
		}
		$action = 'create';
	}

	/**
	 * Add all rental
	 */

	if ($action == 'addall') {
		$error = 0;
		$date_echeance = dol_mktime(12, 0, 0, GETPOST('echmonth', 'int'), GETPOST('echday', 'int'), GETPOST('echyear', 'int'));

		$dateperiod = dol_mktime(12, 0, 0, GETPOST('periodmonth', 'int'), GETPOST('periodday', 'int'), GETPOST('periodyear', 'int'));

		$dateperiodend = dol_mktime(12, 0, 0, GETPOST('periodendmonth', 'int'), GETPOST('periodendday', 'int'), GETPOST('periodendyear', 'int'));

		if (empty($date_echeance)) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("DateDue")), null, 'errors');
			Header("Location: " . $_SERVER["PHP_SELF"] . "?action=createall");
			exit;
			$error++;
		} elseif (empty($dateperiod)) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Periode_du")), null, 'errors');
			Header("Location: " . $_SERVER["PHP_SELF"] . "?action=createall");
			exit;
			$error++;
		} elseif (empty($dateperiodend)) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Periode_au")), null, 'errors');
			Header("Location: " . $_SERVER["PHP_SELF"] . "?action=createall");
			exit;
			$error++;
		} else {
			$mesLignesCochees = GETPOST('mesCasesCochees');
			if (!empty($mesLignesCochees)) {
				foreach ($mesLignesCochees as $maLigneCochee) {
					$receipt = new ImmoReceipt($db);

					$maLigneCourante = preg_split("/[\_,]/", $maLigneCochee);

					$monId = $maLigneCourante[0];
					$monLocal = $maLigneCourante[1];
					$monLocataire = $maLigneCourante[2];
					$monMontant = $maLigneCourante[3];
					$monLoyer = $maLigneCourante[4];
					$mesCharges = $maLigneCourante[5];
					$maTVA = $maLigneCourante[6];
					$monProprio = $maLigneCourante[7];
					$socProprio = $maLigneCourante[8];

					// main info rent
					$receipt->label = GETPOST('label', 'alpha');
					$receipt->date_echeance = $date_echeance;
					$receipt->date_start = $dateperiod;
					$receipt->date_end = $dateperiodend;

					// main info contract
					$receipt->ref = '(PROV)';
					$receipt->fk_rent = $monId;
					$receipt->fk_property = $monLocal;
					$receipt->fk_renter = $monLocataire;

					if ($maTVA == 'Oui') {
						$receipt->total_amount = $monMontant * 1.2;
						$receipt->vat_amount = $monMontant * 0.2;
					} else {
						$receipt->total_amount = $monMontant;
						$receipt->vat_amount = 0;
					}

					$receipt->rentamount = $monLoyer;
					$receipt->chargesamount = $mesCharges;
					$receipt->fk_owner = $monProprio;
					$receipt->fk_soc = $socProprio;
					$receipt->status = 0;
					$receipt->paye = 0;
					$result = $receipt->create($user);

					if ($result < 0) {
						setEventMessages(null, $receipt->errors, 'errors');
						$action = 'createall';
						$error++;
					}
				}
			}
		}

		if (empty($error)) {
			setEventMessages($langs->trans("ReceiptPaymentsAdded"), null, 'mesgs');
			Header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1));
			exit();
		}
	}

	/*
	 * Edit Receipt
	 */

	if ($action == 'update') {
		$date_echeance = dol_mktime(12, 0, 0, GETPOST("date_echeancemonth", 'int'), GETPOST("date_echeanceday", 'int'), GETPOST("date_echeanceyear", 'int'));
		$date_start = dol_mktime(12, 0, 0, GETPOST("date_startmonth", 'int'), GETPOST("date_startday", 'int'), GETPOST("date_startyear", 'int'));
		$date_end = dol_mktime(12, 0, 0, GETPOST("date_endmonth", 'int'), GETPOST("date_endday", 'int'), GETPOST("date_endyear", 'int'));

		$receipt = new ImmoReceipt($db);
		$result = $receipt->fetch($id);

		$receipt->label = GETPOST('label');
		if ($receipt->vat_tx != 0) {
			$rentamount = price2num(GETPOST("rentamount"));
			$chargesamount = price2num(GETPOST("chargesamount"));
			$receipt->total_amount = ($rentamount + $chargesamount) * 1.2;
		} else {
			$rentamount = price2num(GETPOST("rentamount"));
			$chargesamount = price2num(GETPOST("chargesamount"));
			$receipt->total_amount = $rentamount + $chargesamount;
		}
		$receipt->rentamount = GETPOST("rentamount");
		$receipt->chargesamount = GETPOST("chargesamount");
		if ($receipt->vat_tx != 0) {
			$rentamount = price2num(GETPOST("rentamount"));
			$chargesamount = price2num(GETPOST("chargesamount"));
			$receipt->vat_amount = ($rentamount + $chargesamount) * 0.2;
		} else {
			$receipt->vat_amount = 0;
		}

		$receipt->fk_rent = GETPOST("fk_rent");
		$receipt->fk_property = GETPOST("fk_property");
		$receipt->fk_renter = GETPOST("fk_renter");
		$receipt->fk_soc = GETPOST("fk_soc");
		$receipt->fk_owner = GETPOST("fk_owner");
		$receipt->fk_mode_reglement = GETPOST("fk_mode_reglement");
		$receipt->mode_code = GETPOST("mode_code");
		$receipt->mode_payment = GETPOST("mode_payment");
		$receipt->date_echeance = $date_echeance;
		$receipt->note_public = GETPOST("note_public");
		$receipt->status = GETPOST("status");
		$receipt->date_start = $date_start;
		$receipt->date_end = $date_end;

		$result = $receipt->update($user);
		header("Location: " . dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $receipt->id);
		if ($id > 0) {
			// $mesg='<div class="ok">'.$langs->trans("SocialContributionAdded").'</div>';
		} else {
			$mesg = '<div class="error">' . $receipt->error . '</div>';
		}
	}

	// Action to build doc
	include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

	// Build doc
	if ($action == 'builddoc' && $permissiontoadd) {
		// Save last template used to generate document
		if (GETPOST('model')) $object->setDocModel($user, GETPOST('model', 'alpha'));

		$outputlangs = $langs;
		if (GETPOST('lang_id', 'aZ09')) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang(GETPOST('lang_id', 'aZ09'));
		}
		$result = $object->generateDocument($object->model_pdf, $outputlangs);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		}
	}

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, 'IMMORECEIPT_MODIFY');
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'IMMORECEIPT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_IMMORECEIPT_TO';
	$trackid = 'immoreceipt' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}

/*
 * View
 *
 */

$form = new Form($db);
$formfile = new FormFile($db);
$paymentstatic = new ImmoPayment($db);
$bankaccountstatic = new Account($db);

llxHeader('', $langs->trans("MenuNewImmoReceipt"), '');

// Load object modReceipt
$module = (!empty($conf->global->ULTIMATEIMMO_ADDON_NUMBER) ? $conf->global->ULTIMATEIMMO_ADDON_NUMBER : 'mod_ultimateimmo_standard');

if (substr($module, 0, 17) == 'mod_ultimateimmo_' && substr($module, -3) == 'php') {
	$module = substr($module, 0, dol_strlen($module) - 4);
}
$result = dol_buildpath('/ultimateimmo/core/modules/ultimateimmo/' . $module . '.php');

if ($result >= 0) {
	dol_include_once('/ultimateimmo/core/modules/ultimateimmo/mod_ultimateimmo_standard.php');
	$modCodeReceipt = new $module();
}

// Part to create
if ($action == 'create') {
	print load_fiche_titre($langs->transnoentitiesnoconv("MenuNewImmoReceipt"), '', 'object_' . $object->picto);

	$year_current = strftime("%Y", dol_now());
	$pastmonth = strftime("%m", dol_now());
	$pastmonthyear = $year_current;
	if ($pastmonth == 0) {
		$pastmonth = 12;
		$pastmonthyear--;
	}

	print '<form name="fiche_loyer" method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">' . "\n";

	// Common attributes
	$object->fields = dol_sort_array($object->fields, 'position');

	foreach ($object->fields as $key => $val) {
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3) continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) continue;    // We don't want this field

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

		if ($val['label'] == 'Ref'
		) {
			// Reference
			if (!empty($modCodeReceipt->code_auto)) {
				$tmpcode = "(PROV)";
			} else {
				$tmpcode = '<input name="ref" class="maxwidth100" maxlength="128" value="' . dol_escape_htmltag(GETPOST('ref') ? GETPOST('ref') : $tmpcode) . '">';
			}
			print $tmpcode;
		} elseif ($val['label'] == 'DateCreation') {
			// DateCreation
			print $form->selectDate(($object->date_creation ? $object->date_creation : -1), "date_creation", 0, 0, 0, "", 1, 1, 1);
		} elseif ($val['label'] == 'DateStart') {
			// date_start
			print $form->selectDate(($object->date_start ? $object->date_start : -1), "date_start", 0, 0, 0, "", 1, 1, 1);
		} elseif ($val['label'] == 'DateEnd') {
			// date_end
			print $form->selectDate(($object->date_end ? $object->date_end : -1), "date_end", 0, 0, 0, "", 1, 1, 1);
		} elseif ($val['label'] == 'Echeance') {
			// Echeance
			print $form->selectDate(($object->date_echeance ? $object->date_echeance : -1),
				"date_echeance", 0, 0, 0, "", 1, 1, 1);
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

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
	print '&nbsp; ';
	print '<input type="' . ($backtopage ? "submit" : "button") . '" class="button" name="cancel" value="' . dol_escape_htmltag($langs->trans("Cancel")) . '"' . ($backtopage ? '' : ' onclick="javascript:history.go(-1)"') . '>';    // Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';
}
/* *************************************************************************** */
/*                                                                             */
/* Mode add all contracts                                                      */
/*                                                                             */
/* *************************************************************************** */

// List of mass actions available
$arrayofmassactions = array(
	//'validate'=>$langs->trans("Validate"),
	//'generate_doc'=>$langs->trans("ReGeneratePDF"),
	//'builddoc'=>$langs->trans("PDFMerge"),
	//'presend'=>$langs->trans("SendByMail"),
);
if ($permissiontodelete) $arrayofmassactions['predelete'] = '<span class="fa fa-trash paddingrightonly"></span>' . $langs->trans("Delete");
if (GETPOST('nomassaction', 'int') || in_array($massaction, array('presend', 'predelete'))) $arrayofmassactions = array();
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

if ($action == 'createall') {
	print '<form name="fiche_loyer" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	if ($optioncss != '') print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="addall">';
	print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
	print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';

	print dol_get_fiche_head(null);

	print '<table class="border centpercent">';

	print '<tr><td class="fieldrequired titlefieldcreate">' . $langs->trans('NomLoyer') . '</td><td>';
	//Rent name
	print '<input name="label" size="30" value="' . GETPOST('label') . '"';
	print '</td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans('Echeance') . '</td><td>';
	print $form->selectDate(!empty($date_echeance) ? $date_echeance : '-1', 'ech', '', '', 0, "fiche_loyer", 1, 1, 0, '', '', $object->date);
	print '</td></tr>';
	print '<tr><td class="">' . $langs->trans('Periode_du') . '</td><td>';
	print $form->selectDate(!empty($dateperiod) ? $dateperiod : '-1', 'period', '', '', 0, "fiche_loyer", 1, 1, 0, '', '', $object->date);
	print '</td></tr>';
	print '<tr><td class="fieldrequired">' . $langs->trans('Periode_au') . '</td><td>';
	print $form->selectDate(!empty($dateperiodend) ? $dateperiodend : '-1', 'periodend', '', '', 0, "fiche_loyer", 1, 1, 0, '', '', $object->date);
	print '</td></tr>';
	print '</table>';
	print dol_get_fiche_end();


	/*
	 * List of contracts
	 */
	$sql = "SELECT rent.rowid as contractid, rent.ref as contract, loc.lastname as rentername, own.lastname as ownername, own.firstname as ownerfirstname, prop.ref as
	localref, prop.address, prop.label as local, rent.totalamount as total, rent.rentamount , rent.chargesamount,
	rent.fk_renter as reflocataire, rent.fk_property as reflocal, rent.preavis as preavis,
	rent.vat, prop.fk_owner, own.rowid, own.fk_soc, prop.fk_owner, rent.periode";
	$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as prop";
	$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoowner as own";
	$sql .= " WHERE preavis = 1 AND loc.rowid = rent.fk_renter AND prop.rowid = rent.fk_property AND own.rowid = prop.fk_owner ";
	//print_r($sql);exit;
	$sql .= $db->order($sortfield, $sortorder);
	// Count total nb of records
	$nbtotalofrecords = '';
	if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
		$resql = $db->query($sql);
		$nbtotalofrecords = $db->num_rows($resql);
		if (($page * $limit) > $nbtotalofrecords)    // if total resultset is smaller then paging size (filtering), goto and load page 0
		{
			$page = 0;
			$offset = 0;
		}
	}

	$sql .= $db->plimit($limit + 1, $offset);

	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);

		$arrayofselected = is_array($toselect) ? $toselect : array();

		$i = 0;
		$total = 0;

		//print_barre_liste($titre, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'address', 0, $newcardbutton, '', $limit, 0, 0, 1);

		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans('Contract') . '</td>';
		print '<td>' . $langs->trans('Property') . '</td>';
		print '<td>' . $langs->trans('Nomlocal') . '</td>';
		print '<td>' . $langs->trans('Renter') . '</td>';
		print '<td>' . $langs->trans('RenterName') . '</td>';
		print '<td>' . $langs->trans('Owner') . '</td>';
		print '<td>' . $langs->trans('OwnerLastName') . '</td>';
		print '<td>' . $langs->trans('OwnerFirstName') . '</td>';
		print '<td class="right">' . $langs->trans('RentAmount') . '</td>';
		print '<td class="right">' . $langs->trans('ChargesAmount') . '</td>';
		print '<td class="right">' . $langs->trans('TotalAmount') . '</td>';
		print '<td class="right">' . $langs->trans('VATIsUsed') . '</td>';
		print '<td class="right">' . $langs->trans('Select');
		print $form->showCheckAddButtons('checkforselect', 1);
		print '</td>';
		print "</tr>\n";

		if ($num > 0) {
			while ($i < $num) {
				$objp = $db->fetch_object($resql);
				print '<tr class="oddeven">';

				if ($objp->fk_soc) {
					$company = new Societe($db);
					$result = $company->fetch($objp->fk_soc);
				}

				print '<td>' . $objp->contract . '</td>';
				print '<td>' . $objp->localref . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->reflocataire . '</td>';
				print '<td>' . $objp->rentername . '</td>';
				print '<td>' . $objp->fk_owner . '</td>';
				print '<td>' . $objp->ownername . '</td>';
				print '<td>' . $objp->ownerfirstname . '</td>';

				print '<td class="right">' . price($objp->rentamount) . '</td>';
				print '<td class="right">' . price($objp->chargesamount) . '</td>';
				print '<td class="right">' . price($objp->total) . '</td>';
				print '<td class="right">' . yn($objp->vat) . '</td>';


				// Colonne choix contrat
				print '<td class="center">';

				print '<input class="checkforselect flat" type="checkbox" name="mesCasesCochees[]" value="' . $objp->contractid . '_' . $objp->localref . '_' . $objp->reflocataire . '_' . $objp->total . '_' . $objp->rentamount . '_' . $objp->chargesamount . '_' . $objp->vat . '_' . $objp->fk_owner . '_' . $objp->fk_soc . '"' . (($objp->localref && ($objp->periode == 'mensuel')) ? ' checked="checked"' : "") . '/>';
				print '</td>';
				print '</tr>' . "\n";

				$i++;
			}
		}

		print "</table>\n";
		$db->free($resql);
	} else {
		dol_print_error($db);
	}
	print '<div class="center"><input type="submit" class="button" value="' . $langs->trans("MenuAllReceiptperContract") . '"></div>';

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("MenuNewImmoReceipt", $langs->transnoentitiesnoconv("ImmoReceipt")));

	$receipt = new ImmoReceipt($db);
	$result = $receipt->fetch($id);

	print '<form name="fiche_loyer" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">' . "\n";

	// Common attributes
	$object->fields = dol_sort_array($object->fields, 'position');

	foreach ($object->fields as $key => $val) {
		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4) continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) continue;    // We don't want this field

		print '<tr><td';
		print ' class="titlefieldcreate';
		if ($val['notnull'] > 0) print ' fieldrequired';
		if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
		print '">';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '</td>';
		print '<td>';

		if ($val['label'] == 'PartialPayment') {
			if ($object->getSommePaiement()) {
				$totalpaye = price($object->getSommePaiement(), 0, $outputlangs, 1, -1, -1, $conf->currency);
				print '<input name="partial_payment" class="flat" size="8" value="' . $totalpaye . '">';
			}
		} elseif ($val['label'] == 'Balance') {
			$balance = $object->total_amount - $object->getSommePaiement();
			if ($balance >= 0) {
				$balance = price($balance, 0, $outputlangs, 1, -1, -1, $conf->currency);
				print '<input name="balance" class="flat" size="8" value="' . $balance . '">';
			}
		} elseif ($val['label'] == 'Paye') {
			if ($totalpaye == 0) {
				$object->paye = $langs->trans('UnPaidReceipt');
				print '<input name="unpaidreceipt" class="flat" size="25" value="' . $object->paye . '">';
			} elseif ($balance == 0) {
				$object->paye = $langs->trans('PaidReceipt');
				print '<input name="paidreceipt" class="flat" size="25" value="' . $object->paye . '">';
			} else {
				$object->paye = $langs->trans('PartiallyPaidReceipt');
				print '<input name="partiallypaidreceipt" class="flat" size="25" value="' . $object->paye . '">';
			}
		} else {
			if (in_array($val['type'], array('int', 'integer'))) $value = GETPOSTISSET($key) ? GETPOST($key, 'int') : $object->$key;
			elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOSTISSET($key) ? GETPOST($key, 'none') : $object->$key;
			else $value = GETPOSTISSET($key) ? GETPOST($key, 'alpha') : $object->$key;
			//var_dump($val.' '.$key.' '.$value);
			if ($val['noteditable']) print $object->showOutputField($val, $key, $value, '', '', '', 0);
			else print $object->showInputField($val, $key, $value, '', '', '', 0);
		}
		print '</td>';
		print '</tr>';
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
	$res = $object->fetch_optionals();

	$soc = new Societe($db);
	$soc->fetch($object->socid);

	$object = new ImmoReceipt($db);
	$result = $object->fetch($id);

	$head = immoreceiptPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("ImmoReceipt"), -1, 'bill');

	$totalpaye = $object->getSommePaiement();

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?recid=' . $object->id, $langs->trans('DeleteImmoReceipt'), $langs->trans('ConfirmDeleteImmoReceipt'), 'confirm_delete', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array(
			array('type' => 'other', 'name' => 'socid', 'label' => $langs->trans("SelectThirdParty"), 'value' => $form->select_company($object->fk_soc, 'socid', '(s.client=1 OR s.client=2 OR s.client=3)', 1)),
			array('type' => 'date', 'name' => 'newdate', 'label' => $langs->trans("Date"), 'value' => dol_now())
		);
		// Ask confirmation to clone
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?recid=' . $object->id, $langs->trans('CloneImmoReceipt'), $langs->trans('ConfirmCloneImmoReceipt', $object->ref), 'confirm_clone', $formquestion, 'yes', 1, 250);
	}

	// Confirmation of validation
	if ($action == 'validate') {
		$error = 0;

		// We verifie whether the object is provisionally numbering
		$ref = substr($object->ref, 1, 4);
		if ($ref == 'PROV') {
			$numref = $object->getNextNumRef($soc);
			if (empty($numref)) {
				$error++;
				setEventMessages(null, $object->errors, 'errors');
			}
		} else {
			$numref = $object->ref;
		}

		$text = $langs->trans('ConfirmValidateReceipt', $numref);

		if (!empty($conf->notification->enabled)) {
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('ULTIMATEIMMO_VALIDATE', $object->socid, $object);
		}

		if (!$error)
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?recid=' . $object->id, $langs->trans('ValidateReceipt'), $text, 'confirm_validate', $formquestion, 0, 1, 220);
	}

	if ($action == 'regulcharge') {
		//find last Regul charge
		$rent = new ImmoRent($db);
		$result = $rent->fetch($object->fk_rent);
		if ($result < 0) {
			setEventMessages($rent->error, $rent->errors, 'errors');
		} else {
			$dt_startCpt = array();
			$sql = "SELECT DISTINCT date_relever FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocompteur WHERE fk_immoproperty=" . (int)$rent->fk_property;
			$sql .= " AND date_relever>='" . $db->idate($rent->date_last_regul_charge) . "'";

			$resql = $db->query($sql);
			if (!$resql) {
				setEventMessages($db->lasterror, null, 'errors');
			} else {
				while ($bjDt = $db->fetch_object($resql)) {
					$dt_startCpt[$db->jdate($bjDt->date_relever)] = dol_print_date($db->jdate($bjDt->date_relever));
				}

				dol_include_once('/ultimateimmo/class/immocompteur_cost.class.php');
				$cptCost = new ImmoCompteur_Cost($db);
				$resultCptCost = $cptCost->fetchAll('','',0,0,array('t.status'=>$cptCost::STATUS_VALIDATED));
				if (!is_array($resultCptCost) && $resultCptCost<0) {
					setEventMessages($cptCost->error,$cptCost->errors,'errors');
				} else {
					$cost_cpt=array();
					if (!empty($resultCptCost)) {
						foreach($resultCptCost as $cptc) {
							//TODO get compteur type
							$cost_cpt[$cptc->id] = $cptc->cost_year. ' ( '.price($cptc->amount) .')';
						}
					}

					$formquestion = array(
						'text' => $langs->trans("RegulCharge"),
						array('type' => 'select', 'label' => $langs->trans("DateLastCptDoneSinceLastRegul", dol_print_date($rent->date_last_regul_charge)), 'name' => 'dt_start', 'values' => $dt_startCpt),
						array('type' => 'select', 'label' => $langs->trans("DateEnd"), 'name' => 'dt_end', 'values' => $dt_startCpt),
						array('type' => 'select', 'label' => $langs->trans("CostCpt"), 'name' => 'cost_cpt', 'values' => $cost_cpt),
					);

					$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('RegulCharge'), $text, 'regulcharge_step2', $formquestion, 0, 1, 250, 650);
				}
			}
		}
	}

	if ($action == 'regulcharge_step2') {

		$rent = new ImmoRent($db);
		$result = $rent->fetch($object->fk_rent);
		if ($result < 0) {
			setEventMessages($rent->error, $rent->errors, 'errors');
		} else {
			$dtStart = GETPOST('dt_start');
			$dtEnd = GETPOST('dt_end');
			//Calcul Total Charge
			$sql = "SELECT qty FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocompteur WHERE fk_immoproperty=" . (int)$rent->fk_property;
			$sql .= " AND date_relever='" . $db->idate($dtStart) . "'";
			$firstQty=0;
			$resql = $db->query($sql);
			if (!$resql) {
				setEventMessages($db->lasterror, null, 'errors');
			} else {
				$objQty = $db->fetch_object($resql);
				$firstQty = $objQty->qty;
			}

			$sql = "SELECT qty FROM " . MAIN_DB_PREFIX . "ultimateimmo_immocompteur WHERE fk_immoproperty=" . (int)$rent->fk_property;
			$sql .= " AND date_relever='" . $db->idate($dtEnd) . "'";
			$endQty=0;
			$resql = $db->query($sql);
			if (!$resql) {
				setEventMessages($db->lasterror, null, 'errors');
			} else {
				$objQty = $db->fetch_object($resql);
				$endQty = $objQty->qty;
			}

			$diffCpt = $endQty-$firstQty;

			dol_include_once('/ultimateimmo/class/immocompteur_cost.class.php');
			$cptCost = new ImmoCompteur_Cost($db);
			$result = $cptCost->fetch(GETPOST('cost_cpt','int'));
			if ($result < 0) {
				setEventMessages($cptCost->error, $cptCost->errors, 'errors');
			}
			$estimatedCost =0;
			if (!empty($cptCost->amount)) {
				$estimatedCost = $diffCpt*$cptCost->amount;
			}



			$formquestion = array(
				'text' => $langs->trans("RegulChargeStep2",$firstQty,$endQty,$diffCpt,$cptCost->amount),
				array('type' => 'text', 'name' => 'total_charge', 'label' => $langs->trans("TotalCharge"), 'value' => $estimatedCost),
				array('type' => 'hidden', 'name' => 'first_qty', 'value' => $firstQty),
				array('type' => 'hidden', 'name' => 'end_qty', 'value' => $endQty),
				array('type' => 'hidden', 'name' => 'dt_start', 'value' => $dtStart),
				array('type' => 'hidden', 'name' => 'dt_end', 'value' => $dtEnd),
			);

			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('RegulCharge'), $text, 'regulcharge_confirm', $formquestion, 0, 1, 250, 650);
		}
	}


	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$object->fetch_thirdparty();

	$morehtmlref = '<div class="refidno">';
	// Ref renter
	$staticImmorenter = new ImmoRenter($db);
	$staticImmorenter->fetch($object->fk_renter);
	$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $staticImmorenter->ref, $object, $permissiontoadd, 'string', '', 0, 1);
	$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $staticImmorenter->ref . ' - ' . $staticImmorenter->getFullName($langs), $object, $permissiontoadd, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref .= '<br>' . $langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1, 'renter');
	if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $object->thirdparty->id > 0) $morehtmlref .= ' (<a href="' . dol_buildpath('/ultimateimmo/receipt/immoreceipt_list.php', 1) . '?socid=' . $object->thirdparty->id . '&search_fk_soc=' . urlencode($object->thirdparty->id) . '">' . $langs->trans("OtherReceipts") . '</a>)';
	$morehtmlref .= '</div>';

	$object->totalpaye = $totalpaye;   // To give a chance to dol_banner_tab to use already paid amount to show correct status

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, '', 0, '', '');

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">' . "\n";

	// Common attributes
	$keyforbreak = 'date_echeance';

	foreach ($object->fields as $key => $val) {
		if (!empty($keyforbreak) && $key == $keyforbreak) break; // key used for break on second column

		// Discard if extrafield is a hidden field on form
		if (abs($val['visible']) != 1 && abs($val['visible']) != 3 && abs($val['visible']) != 4 && abs($val['visible']) != 5) continue;

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !verifCond($val['enabled'])) continue;    // We don't want this field
		if (in_array($key, array('ref', 'status'))) continue;    // Ref and status are already in dol_banner

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
				$staticowner->ref = $staticowner->getNomUrl(0, '', 0);
			}
			print $staticowner->ref;
		} elseif ($val['label'] == 'Renter') {
			$staticrenter = new ImmoRenter($db);
			$staticrenter->fetch($object->fk_renter);
			if ($staticrenter->ref) {
				$staticrenter->ref = $staticrenter->getNomUrl(0);
			}
			print $staticrenter->ref;
		} elseif (
			$val['label'] == 'Property'
		) {
			$staticproperty = new ImmoProperty($db);
			$staticproperty->fetch($object->fk_property);
			if ($staticproperty->ref) {
				$staticproperty->ref = $staticproperty->getNomUrl(0);
			}
			print $staticproperty->ref;
		} else {
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		}
		//print dol_escape_htmltag($object->$key, 1, 1);
		print '</td>';
		print '</tr>';
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

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !$val['enabled']) continue;    // We don't want this field
		if (in_array($key, array('ref', 'status'))) continue;    // Ref and status are already in dol_banner

		$value = $object->$key;

		print '<tr><td';
		print ' class="titlefield fieldname_' . $key;
		//if ($val['notnull'] > 0) print ' fieldrequired';		// No fieldrequired in the view output

		if ($val['label'] == 'PartialPayment') {
			if ($object->getSommePaiement()) {
				$totalpaye = price($object->getSommePaiement(), 0, $outputlangs, 1, -1, -1, $conf->currency);
				print $totalpaye;
			}
		} elseif ($val['label'] == 'Balance') {
			$balance = $object->total_amount - $object->getSommePaiement();
			if ($balance >= 0) {
				print price($balance, 0, $outputlangs, 1, -1, -1, $conf->currency);
			}
		} elseif ($val['label'] == 'Paye') {
			if ($totalpaye == 0) {
				print $object->paye = $langs->trans('UnPaidReceipt');
			} elseif ($balance == 0) {
				print $object->paye = $langs->trans('PaidReceipt');
			} else {
				print $object->paye = $langs->trans('PartiallyPaidReceipt');
			}
		} else {
			if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
			print '">';
			if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
			else print $langs->trans($val['label']);
			print '</td>';
			print '<td>';
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		}
		//var_dump($val.' '.$key.' '.$value);
		print '</td>';
		print '</tr>';
	}

	// Other attributes
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

	// Add symbol of currency
	$cursymbolbefore = $cursymbolafter = '';
	if ($object->multicurrency_code) {
		$currency_symbol = $langs->getCurrencySymbol($object->multicurrency_code);
		$listofcurrenciesbefore = array('$', '£', 'S/.', '¥');
		if (in_array($currency_symbol, $listofcurrenciesbefore)) $cursymbolbefore .= $currency_symbol;
		else {
			$tmpcur = $currency_symbol;
			$cursymbolafter .= ($tmpcur == $currency_symbol ? ' ' . $tmpcur : $tmpcur);
		}
	} else {
		$cursymbolafter = $langs->getCurrencySymbol($conf->currency);
	}

	// List of payments
	$sql = "SELECT p.rowid,p.fk_rent, p.fk_receipt, p.date_payment as dp, p.amount, p.fk_mode_reglement, c.code as type_code, c.libelle as mode_reglement_label, r.partial_payment, ";
	$sql .= ' ba.rowid as baid, ba.ref as baref, ba.label, ba.number as banumber, ba.account_number, ba.fk_accountancy_journal';
	$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as r";
	$sql .= ", " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "bank as b ON p.fk_account = b.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "bank_account as ba ON b.fk_account = ba.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_paiement as c ON p.fk_mode_reglement = c.id";
	$sql .= " WHERE r.rowid = '" . $id . "'";
	$sql .= " AND p.fk_receipt = r.rowid";
	$sql .= " AND r.entity IN (" . getEntity($object->element) . ")";
	$sql .= ' ORDER BY dp';

	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);

		$i = 0;
		$total = 0;
		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans("RefPayment") . '</td>';
		print '<td>' . $langs->trans("Date") . '</td>';
		print '<td>' . $langs->trans("Type") . '</td>';
		if (!empty($conf->banque->enabled)) {
			print '<td class="liste_titre right">' . $langs->trans('BankAccount') . '</td>';
		}
		print '<td class="right">' . $langs->trans("Amount") . '</td>';
		if ($user->admin) print '<td>&nbsp;</td>';
		print '</tr>';

		while ($i < $num) {
			$objp = $db->fetch_object($resql);

			$paymentstatic->id = $objp->rowid;
			$paymentstatic->fk_rent = $objp->fk_rent;
			$paymentstatic->datepaye = $db->jdate($objp->dp);
			$paymentstatic->ref = $objp->ref;
			$paymentstatic->num_paiement = $objp->num_paiement;

			print '<tr class="oddeven"><td>';
			print '<a href="' . dol_buildpath('/ultimateimmo/receipt/payment/card.php', 1) . '?id=' . $objp->rowid . "&amp;receipt=" . $id . '">' . img_object($langs->trans("Payment"), "payment") . ' ' . $objp->rowid . '</a></td>';
			print '<td>' . dol_print_date($db->jdate($objp->dp), 'day') . '</td>';
			$paymentstatic->fk_mode_reglement = $objp->mode_reglement_label;
			$paymentstatic->type_code = $objp->type_code;
			$paymentstatic->mode_reglement_label = $objp->mode_reglement_label;
			print '<td>' . $paymentstatic->fk_mode_reglement . '</td>';

			if (!empty($conf->banque->enabled)) {
				$bankaccountstatic->id = $objp->baid;
				$bankaccountstatic->ref = $objp->baref;
				$bankaccountstatic->label = $objp->baref;
				$bankaccountstatic->number = $objp->banumber;

				if (!empty($conf->accounting->enabled)) {
					$bankaccountstatic->account_number = $objp->account_number;

					$accountingjournal = new AccountingJournal($db);
					$accountingjournal->fetch($objp->fk_accountancy_journal);
					$bankaccountstatic->accountancy_journal = $accountingjournal->getNomUrl(0, 1, 1, '', 1);
				}

				print '<td class="right">';
				if ($bankaccountstatic->id)
					print $bankaccountstatic->getNomUrl(1, 'transactions');
				print '</td>';
			}
			print '<td class="right">' . $cursymbolbefore . price($objp->amount, 0, $outputlangs) . ' ' . $cursymbolafter . "</td>\n";

			print '<td class="right">';
			if ($user->admin) {
				print '<a href="' . dol_buildpath('/ultimateimmo/payment/immopayment_card.php', 1) . '?id=' . $objp->rowid . "&amp;action=delete&amp;receipt=" . $id . '">';
				print img_delete();
				print '</a>';
			}
			print '</td>';
			print '</tr>';
			$totalpaye = $object->getSommePaiement();

			$i++;
		}

		if ($object->paye == 0) {
			print '<tr><td colspan="4" class="right">' . $langs->trans("AlreadyPaid") . ' :</td><td class="right"><b>' . $cursymbolbefore . price($totalpaye, 0, $outputlangs) . ' ' . $cursymbolafter . '</b>' . "</td><td>&nbsp;</td></tr>\n";
			print '<tr><td colspan="4" class="right">' . $langs->trans("AmountExpected") . ' :</td><td class="right">' . $cursymbolbefore . price($object->total_amount, 0, $outputlangs) . ' ' . $cursymbolafter . "</td><td>&nbsp;</td></tr>\n";

			$remaintopay = $object->total_amount - $object->getSommePaiement();

			print '<tr><td colspan="4" class="right">' . $langs->trans("RemainderToPay") . ' :</td>';
			print '<td class="right"' . ($remaintopay ? ' class="amountremaintopay"' : '') . '>' . $cursymbolbefore . price($remaintopay, 0, $outputlangs) . ' ' . $cursymbolafter . "</td><td>&nbsp;</td></tr>\n";
		}
		print '</table>';
		$db->free($resql);
	} else {
		dol_print_error($db);
	}

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div><br>';

	print dol_get_fiche_end();

	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#addline' : '#line_' . GETPOST('lineid', 'int')) . '" method="POST">
			<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">
			<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
			<input type="hidden" name="mode" value="">
			<input type="hidden" name="id" value="' . $object->id . '">
			';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form
				$object->formAddObjectLine(1, $mysoc, $soc);

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}

	if (is_file($conf->ultimateimmo->dir_output . '/receipt/quittance_' . $id . '.pdf')) {
		print '&nbsp';
		print '<table class="border" width="100%">';
		print '<tr class="liste_titre"><td colspan=3>' . $langs->trans("LinkedDocuments") . '</td></tr>';
		// afficher
		$legende = $langs->trans("Ouvrir");
		print '<tr><td width="200" class="center">' . $langs->trans("Quittance") . '</td><td> ';
		print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=ultimateimmo&file=quittance_' . $id . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
		print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" class="absmiddle" hspace="2px" ></a>';
		print '</td></tr></table>';
	}

	print '</div>';

	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
		if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

		if (empty($reshook)) {
			//Regul charge
			if ($receipt->paye == 0 && $permissiontoadd) {
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '&action=regulcharge">' . $langs->trans('RegulCharge') . '</a>' . "\n";
			}

			// Validate
			if ($object->status == ImmoReceipt::STATUS_DRAFT) {
				if ($permissiontoadd) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?recid=' . $object->id . '&amp;action=validate">' . $langs->trans('Validate') . '</a></div>';
				} else {
					print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Validate') . '</a></div>';
				}
			}

			// Send
			print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?recid=' . $id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>' . "\n";

			// Modify
			if ($permissiontoadd) {
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?recid=' . $id . '&amp;action=edit">' . $langs->trans("Modify") . '</a>' . "\n";
			} else {
				print '<a class="butActionRefused classfortooltip" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Modify') . '</a>' . "\n";
			}

			////// generate pdf
			print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=builddoc&id=' . $id . '">' . $langs->trans('Quittance') . '</a></div>';

			// Create payment
			if ($receipt->paye == 0 && $permissiontoadd) {
				if ($remaintopay == 0) {
					print '<div class="inline-block divButAction"><span class="butActionRefused" title="' . $langs->trans("DisabledBecauseRemainderToPayIsZero") . '">' . $langs->trans('DoPayment') . '</span></div>';
				} else {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . dol_buildpath('/ultimateimmo/receipt/payment/paiement.php', 1) . '?id=' . $id . '&amp;action=create">' . $langs->trans('DoPayment') . '</a></div>';
				}
			}

			// Classify 'paid'
			if ($receipt->paye == 0 && round($remaintopay) <= 0) {
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=paid&id=' . $id . '">' . $langs->trans('ClassifyPaid') . '</a></div>';
			}

			// Clone
			if ($permissiontoadd) {
				print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '&amp;socid=' . $object->fk_soc . '&amp;action=clone&amp;object=immoreceipt">' . $langs->trans("ToClone") . '</a></div>';
			}

			if ($permissiontodelete) {
				print '<div class="inline-block divButAction"><a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '&amp;action=delete">' . $langs->trans('Delete') . '</a></div>' . "\n";
			} else {
				print '<a class="butActionRefused classfortooltip" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Delete') . '</a>' . "\n";
			}
		}
		print '</div>' . "\n";
	}

	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		// Documents generes
		$objref = dol_sanitizeFileName($object->ref);
		$relativepath = $objref . '/' . $objref . '.pdf';
		$filedir = $conf->ultimateimmo->dir_output . '/receipt' . '/' . $objref;
		$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
		$delallowed = $permissiontodelete; // If you can create/edit, you can remove a file on card
		print $formfile->showdocuments('ultimateimmo', 'receipt/' . $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('immoreceipt'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		$MAXEVENT = 10;

		$morehtmlright = '<a href="' . dol_buildpath('/ultimateimmo/receipt/immoreceipt_agenda.php', 1) . '?id=' . $object->id . '">';
		$morehtmlright .= $langs->trans("SeeAll");
		$morehtmlright .= '</a>';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

		print '</div></div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail = 'immoreceipt';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->ultimateimmo->dir_output . '/receipt';
	$trackid = 'immo' . $object->id;

	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
