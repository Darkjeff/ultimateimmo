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
 *   	\file       immoreceipt_card.php
 *		\ingroup    ultimateimmo
 *		\brief      Page to create/edit/view immoreceipt
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
dol_include_once('/ultimateimmo/lib/immorenter.lib.php');
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
$rowid 		= GETPOST('rowid', 'int');
$ref        = GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$toselect   = GETPOST('toselect', 'array');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'immoreceiptcard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$search_fk_soc = GETPOST('search_fk_soc', 'alpha');

$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
$userid = GETPOST('userid', 'int');
$begin = GETPOST('begin');
if (!$sortorder) $sortorder = "ASC";
if (!$sortfield) $sortfield = "own.lastname";
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) { $page = 0; }
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

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

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

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

$permissiontoread = $user->rights->ultimateimmo->read;
$permissiontoadd = $user->rights->ultimateimmo->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->ultimateimmo->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->ultimateimmo->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->ultimateimmo->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->ultimateimmo->multidir_output[isset($object->entity) ? $object->entity : 1];


/*
 * View
 *
 */

$form = new Form($db);
$formfile = new FormFile($db);
$paymentstatic = new ImmoPayment($db);
$bankaccountstatic = new Account($db);

llxHeader('', $langs->trans("Renter").' | '.$langs->trans("MenuNewImmoReceipt"), '');

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


// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$soc = new Societe($db);
	$soc->fetch($object->socid);

	$object = new ImmoReceipt($db);
	$result = $object->fetch($id);

	$objectrenter = new ImmoRenter($db);
	$objectrenter->fetch($id, $ref);

	$head = immorenterPrepareHead($objectrenter);
	
	print dol_get_fiche_head($head, 'receipt', $langs->trans("ImmoReceipt"), 0, 'user');

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

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
			<input type="hidden" name="token" value="' . newToken() . '">
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
		var_dump($object);exit;
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
			// Validate
			if ($object->status == ImmoReceipt::STATUS_DRAFT) {
				if ($permissiontoadd) {
					print '<div class="inline-block divButAction"><a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=validate">' . $langs->trans('Validate') . '</a></div>';
				} else {
					print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">' . $langs->trans('Validate') . '</a></div>';
				}
			}

			// Send
			print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>' . "\n";

			// Modify
			if ($permissiontoadd) {
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $id . '&amp;action=edit">' . $langs->trans("Modify") . '</a>' . "\n";
			} else {
				print '<a class="butActionRefused classfortooltip" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Modify') . '</a>' . "\n";
			}

			// generate pdf
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
			if ($receipt->paye == 1 && round($remaintopay) <= 0) {
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
		$relativepath = '/receipt/' . dol_sanitizeFileName($object->ref);
		$filedir = $conf->ultimateimmo->dir_output . $relativepath;
		$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		$genallowed = $permissiontoread;	// If you can read, you can build the PDF to read content
		$delallowed = $permissiontodelete;	// If you can create/edit, you can remove a file on card
		
		print $formfile->showdocuments('ultimateimmo', $relativepath, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang, 0, $object);

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('immoreceipt'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		$MAXEVENT = 10;

		$morehtmlright = '<a href="' . dol_buildpath('/ultimateimmo/receipt/immoreceipt_info.php', 1) . '?id=' . $object->id . '">';
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
	$diroutput = $conf->ultimateimmo->dir_output . '/receipt/';
	$trackid = 'immo' . $object->id;
	
	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
