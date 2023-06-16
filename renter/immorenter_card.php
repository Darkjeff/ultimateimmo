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
 *    \file       immorenter_card.php
 *        \ingroup    ultimateimmo
 *        \brief      Page to create/edit/view immorenter
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
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/security2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/lib/immorenter.lib.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'myobjectcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$socid = GETPOST('socid', 'int');

$contactLinkid = 0;
$userLinkid = 0;

// Initialize technical objects
$object = new ImmoRenter($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->ultimateimmo->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('immorentercard', 'globalcard'));     // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_' . $key, 'alpha')) $search[$key] = GETPOST('search_' . $key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Include fetch and fetch_thirdparty but not fetch_optionals

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'mymodule', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);


$sql = 'SELECT sp.rowid as contactId, u.rowid as userId FROM ' . MAIN_DB_PREFIX . 'socpeople as sp ';
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'user as u ON u.fk_socpeople=sp.rowid';
$sql .= ' WHERE sp.fk_soc=' . (int)$object->fk_soc;
$sql .= ' AND sp.email="' . $db->escape($object->email) . '"';
$resql = $db->query($sql);
if ($resql < 0) {
	setEventMessages($db->lasterror, null, 'errors');
} else {
	$num = $db->num_rows($resql);
	if ($num > 0) {
		while ($obc = $db->fetch_object($resql)) {
			$contactLinkid = $obc->contactId;
			$userLinkid = $obc->userId;
		}
	}
}


$permissiontoread = $user->rights->ultimateimmo->read;
$permissiontoadd = $user->rights->ultimateimmo->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->ultimateimmo->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->ultimateimmo->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->ultimateimmo->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->ultimateimmo->multidir_output[isset($object->entity) ? $object->entity : 1];

/*
 * Actions
 *
 */

$parameters = array('id' => $id, 'rowid' => $id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/ultimateimmo/renter/immorenter_card.php', 1) . '?id=' . ($id > 0 ? $id : '__ID__');
		}
	}
	$triggermodname = 'ULTIMATEIMMO_IMMORENTER_MODIFY';

	// Actions cancel, add, update or delete
	include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, 'IMMORENTER_MODIFY');
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'IMMORENTER_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_IMMORENTER_TO';
	$trackid = 'immorenter' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';


	if ($action == 'createContactAndUser') {
		$contact = new Contact($db);
		if (empty($contactLinkid)) {
			$contact->socid = $object->fk_soc;
			$contact->statut = 1;
			$contact->email = $object->email;
			$contact->firstname = $object->firstname;
			$contact->lastname = $object->lastname;
			$contact->birthday = $object->birth;
			$contact->country_id = $object->country_id;
			$contact->phone_pro = $object->phone;
			$contact->phone_perso = $object->phone;
			$contact->phone_mobile = $object->phone_mobile;
			$resultCreate = $contact->create($user);
			if ($resultCreate < 0) {
				setEventMessages($contact->error, $contact->errors, 'errors');
			} else {
				$contactLinkid = $contact->id;
			}
		}
		if (!empty($contactLinkid)) {
			$resultFetch = $contact->fetch($contactLinkid);
			if ($resultFetch < 0) {
				setEventMessages($contact->error, $contact->errors, 'errors');
			}
		}
		if (empty($userLinkid)) {
			$db->begin();
			$password = getRandomPassword(true, null, 10);
			$login=dol_buildlogin($contact->lastname, $contact->firstname);
			// Creation user
			$nuser = new User($db);
			$result = $nuser->create_from_contact($contact, $login); // Do not use GETPOST(alpha)

			if ($result > 0) {

				$result2 = $nuser->setPassword($user, $password, 0, 0, 1); // Do not use GETPOST(alpha)
				if ($result2) {
					setEventMessages($langs->trans('UltmImmoCreateUserSuccess', $login, $password),null);
					$userLinkid = $nuser->id;
					$db->commit();
				} else {
					setEventMessages($nuser->error, $nuser->errors, 'errors');
					$db->rollback();
				}
			} else {
				setEventMessages($nuser->error, $nuser->errors, 'errors');
				$db->rollback();
			}
		}
	}
}


/*
 * View
 *
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

llxHeader('', $langs->trans('ImmoRenter'), '');

if ($conf->use_javascript_ajax) {
	print "\n" . '<script type="text/javascript" language="javascript">';
	/*print 'jQuery(document).ready(function () {
				jQuery("#selectcountry_id").change(function() {
					document.formsoc.action.value="create";
					document.formsoc.submit();
				});

				initfieldrequired();
			})';*/
	print '</script>' . "\n";
}

// Part to create
if ($action == 'create') {

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("ImmoRenter")), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
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

		if (array_key_exists('enabled', $val) && isset($val['enabled']) && !$val['enabled']) continue;    // We don't want this field

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

		if ($val['label'] == 'Civility') {
			// We set civility_id, civility_code and civility for the selected civility
			$object->civility_id = GETPOST("civility_id", 'int') ? GETPOST('civility_id', 'int') : $object->civility_id;

			if ($object->civility_id) {
				$tmparray = array();
				$tmparray = $object->getCivilityLabel($object->civility_id, 'all');
				if (in_array($tmparray['code'], $tmparray)) $object->civility_code = $tmparray['code'];
				if (in_array($tmparray['label'], $tmparray)) $object->civility = $tmparray['label'];
			}
			// civility
			print $object->select_civility(GETPOSTISSET("civility_id") != '' ? GETPOST("civility_id", 'int') : $object->civility_id, 'civility_id');
		} /*elseif ($val['label'] == 'BirthDay') {
			print $form->selectDate(($object->birth ? $object->birth : -1), 'birth', '', '', 1, 'formsoc');
		}*/ elseif ($val['label'] == 'BirthCountry') {
			// We set country_id, country_code and country for the selected country
			$object->country_id = GETPOST('country_id', 'int') ? GETPOST('country_id', 'int') : $object->country_id;
			if ($object->country_id) {
				$tmparray = $object->getCountry($object->country_id, 'all');
				$object->country_code = $tmparray['code'];
				$object->country = $tmparray['label'];
			}
			// Country
			print $form->select_country(GETPOSTISSET('country_id') ? GETPOST('country_id', 'alpha') : $object->country_id, 'country_id');
			if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
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

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("ImmoRenter"), '', 'object_' . $object->picto);

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
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
		print '"';
		print '>';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '</td>';
		print '<td>';

		if ($val['label'] == 'Civility') {
			// We set civility_id, civility_code and civility for the selected civility
			$object->civility_id = GETPOST('civility_id', 'int') ? GETPOST('civility_id', 'int') : $object->civility_id;
			if ($object->civility_id) {
				$tmparray = $object->getCivilityLabel($object->civility_id, 'all');
				$object->civility_code = $tmparray['code'];
				$object->civility = $tmparray['label'];
			}
			print $object->select_civility(GETPOSTISSET("civility_id") != '' ? GETPOST("civility_id", 'int') : $object->civility_id);
		} /*elseif ($val['label'] == 'BirthDay') {
			print $form->selectDate(($object->birth ? $object->birth : -1), 'birth', '', '', 1, '');
		}*/ elseif ($val['label'] == 'ImmoBirthCountry') {
			// We set country_id, country_code and country for the selected country
			$object->country_id = GETPOST('country_id', 'int') ? GETPOST('country_id', 'int') : $object->country_id;
			if ($object->country_id) {
				$tmparray = $object->getCountry($object->country_id, 'all');
				$object->country_code = $tmparray['code'];
				$object->country = $tmparray['label'];
			}
			// Country
			print $form->select_country((GETPOST('country_id') != '' ? GETPOST('country_id') : $object->country_id));
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

	$head = immorenterPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("ImmoRenter"), -1, 'contact');

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteImmoRenter'), $langs->trans('ConfirmDeleteImmoRenter'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id . '&lineid=' . $lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('CloneImmoRenter'), $langs->trans('ConfirmCloneImmoRenter', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
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

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1) . '?restore_lastsearch_values=0' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';
	// Thirdparty
	$morehtmlref .= '<br>' . $langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	if (empty($conf->global->MAIN_DISABLE_OTHER_LINK) && $object->thirdparty->id > 0) $morehtmlref .= ' (<a href="' . dol_buildpath('/ultimateimmo/rent/immorent_list.php', 1) . '?socid=' . $object->thirdparty->id . '&search_fk_soc=' . urlencode($object->thirdparty->id) . '">' . $langs->trans("OtherRents") . '</a>)';

	// Project
	if (!empty($conf->projet->enabled)) {
		$langs->load("projects");
		$morehtmlref .= '<br>' . $langs->trans('Project') . ' ';
		if ($permissiontoadd) {
			if ($action != 'classify') $morehtmlref .= '<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
			$morehtmlref .= ' : ';
			if ($action == 'classify') {
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
				$morehtmlref .= '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '">';
				$morehtmlref .= '<input type="hidden" name="action" value="classin">';
				$morehtmlref .= '<input type="hidden" name="token" value="' . newToken() . '">';
				$morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
				$morehtmlref .= '<input type="submit" class="button valignmiddle" value="' . $langs->trans("Modify") . '">';
				$morehtmlref .= '</form>';
			} else {
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
			}
		} else {
			if (!empty($object->fk_project)) {
				$proj = new Project($db);
				$proj->fetch($object->fk_project);
				$morehtmlref .= ': ' . $proj->getNomUrl();
			} else {
				$morehtmlref .= '';
			}
		}
	}
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">' . "\n";

	// Common attributes
	$object->fields = dol_sort_array($object->fields, 'position');
	$keyforbreak = 'civility_id'; // We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
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
		if ($val['type'] == 'text' || $val['type'] == 'html'
		) print ' tdtop';
		print '">';
		if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
		else print $langs->trans($val['label']);
		print '<td>';
		print '<td class="valuefield fieldname_' . $key;
		if ($val['type'] == 'text') print ' wordbreak';
		print '">';
		print '<td>';

		if ($val['label'] == 'Owner') {
			$staticowner = new ImmoOwner($db);
			$staticowner->fetch($object->fk_owner);
			if ($staticowner->ref) {
				$staticowner->ref = $staticowner->getNomUrl(0);
			}
			print $staticowner->ref;
		} elseif ($val['label'] == 'ImmoRent') {
			$staticrent = new ImmoRent($db);
			$staticrent->fetch($object->fk_rent);
			$staticproperty = new ImmoProperty($db);
			$staticproperty->fetch($staticrent->fk_property);
			if ($staticrent->ref) {
				$staticrent->ref = $staticrent->getNomUrl(0);
			}
			print $staticrent->ref;
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
		if ($val['label'] == 'Civility') {
			print '">';
			// We set civility_id, civility_code and civility for the selected civility
			if ($object->civility_id) {
				$tmparray = $object->getCivilityLabel($object->civility_id, 'all');
				$object->civility_code = $tmparray['code'];
				$object->civility = $tmparray['label'];
			}
			print $langs->trans('Civility') . '</td><td>';
			print $object->civility;
		} elseif ($val['label'] == 'ImmoBirthCountry') {
			print '">';
			if ($object->country_id) {
				include_once(DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php');
				$tmparray = getCountry($object->country_id, 'all');
				$object->country_code = $tmparray['code'];
				$object->country = $tmparray['label'];
			}
			print $langs->trans('Country') . '</td><td>';
			print $object->country;
		} else {
			if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';
			print '">';
			if (!empty($val['help'])) print $form->textwithpicto($langs->trans($val['label']), $langs->trans($val['help']));
			else print $langs->trans($val['label']);
			print '</td>';
			print '<td>';
			print $object->showOutputField($val, $key, $value, '', '', '', 0);
		}
		//print dol_escape_htmltag($object->$key, 1, 1);
		print '</td>';
		print '</tr>';
	}
	if (!empty($contactLinkid)) {
		print '<tr><td class="titlefield fieldname_contactLinkid">';
		print $langs->trans('Contact') . ' / ' . $langs->trans('User');
		print '</td>';

		print '<td>';
		$contact=new Contact($db);
		$resultFetch = $contact->fetch($contactLinkid);
		if ($resultFetch < 0) {
			setEventMessages($contact->error, $contact->errors, 'errors');
		} else {
			print $contact->getNomUrl();
		}
		if (!empty($userLinkid)) {
			$nuser = new User($db);
			$resultFetch = $nuser->fetch($userLinkid);
			if ($resultFetch < 0) {
				setEventMessages($nuser->error, $nuser->errors, 'errors');
			} else {
				print ' / ' . $nuser->getNomUrl();
			}
		}
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

	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#addline' : '#line_' . GETPOST('lineid', 'int')) . '" method="POST">
    	<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">
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

	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">' . "\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
		if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

		if (empty($reshook)) {
			// Send
			print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>' . "\n";

			if ($permissiontoadd) {
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a>' . "\n";
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Modify') . '</a>' . "\n";
			}

			if ($permissiontoadd && getDolGlobalString('ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE')
				&& !empty($object->fk_soc)
				&& !empty($object->civility_id)
				&& !empty($object->firstname)
				&& !empty($object->lastname)
				&& empty($userLinkid)
			) {
				//TODO and contact /user not already created
				print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=createContactAndUser">' . $langs->trans("UltmImmoCreateContactAndUser") . '</a>' . "\n";
			}

			// Clone
			if ($permissiontoadd) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&socid=' . $object->socid . '&action=clone&object=myobject">' . $langs->trans("ToClone") . '</a>' . "\n";
			}

			if ($permissiontodelete) {
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

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		// Documents
		$relativepath = '/renter/' . dol_sanitizeFileName($object->ref);
		$filedir = $conf->ultimateimmo->dir_output . $relativepath;
		$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		$genallowed = $permissiontoread;    // If you can read, you can build the PDF to read content
		$delallowed = $permissiontodelete;    // If you can create/edit, you can remove a file on card
		print $formfile->showdocuments('ultimateimmo', $relativepath, $filedir, $urlsource, 0, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $object->default_lang, '', $object);

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('immorenter'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		$MAXEVENT = 10;

		$morehtmlright = '<a href="' . dol_buildpath('/ultimateimmo/renter/immorenter_agenda.php', 1) . '?id=' . $object->id . '">';
		$morehtmlright .= $langs->trans("SeeAll");
		$morehtmlright .= '</a>';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, 'immorenter', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

		print '</div></div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail = 'immorenter';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->ultimateimmo->dir_output . '/renter';
	$trackid = 'immo' . $object->id;

	include DOL_DOCUMENT_ROOT . '/core/tpl/card_presend.tpl.php';

}

// End of page
llxFooter();
$db->close();
