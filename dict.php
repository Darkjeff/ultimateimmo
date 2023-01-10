<?php
/*
 * Copyright (C) 2018-2019 David Moyon              <david@code42.fr>
 * Copyright (C) 2018-2019 Adam Gendre              <adam@code42.fr>
 * Copyright (C) 2019-2020 Fabien Fernandes Alves   <fabien@code42.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *        \file       htdocs/custom/ultimateimmo/dict.php
 *        \ingroup    setup
 *        \brief      Page to administer data tables
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
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

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/accounting.lib.php';
dol_include_once('/ultimateimmo/lib/ultimateimmo.lib.php');

if (!empty($conf->accounting->enabled)) {
	include_once DOL_DOCUMENT_ROOT . '/core/class/html.formaccounting.class.php';
}

$langs->load("errors");
$langs->load("admin");
$langs->load("main");
$langs->load("companies");
$langs->load("resource");
$langs->load("holiday");
$langs->load("accountancy");
$langs->load("hrm");

$action = GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'view';
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$rowid = GETPOST('rowid', 'alpha');
$entity = GETPOST('entity', 'int');
$code = GETPOST('code', 'alpha');

$allowed = $user->rights->ultimateimmo->dict;
if ($id == 7 && !empty($user->rights->accounting->chartofaccount)) {
	$allowed = 1;     // Tax page allowed to manager of chart account
}
if ($id == 10 && !empty($user->rights->accounting->chartofaccount)) {
	$allowed = 1;    // Vat page allowed to manager of chart account
}
if (!$allowed) {
	accessforbidden();
}

$acts[0] = "activate";
$acts[1] = "disable";
$actl[0] = img_picto($langs->trans("Disabled"), 'switch_off');
$actl[1] = img_picto($langs->trans("Activated"), 'switch_on');

$listoffset = GETPOST('listoffset');
$listlimit = GETPOST('listlimit') > 0 ? GETPOST('listlimit') : 1000;    // To avoid too long dictionaries
$active = 1;

$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOST("page", 'int');
if (empty($page) || $page == -1) {
	$page = 0;
}     // If $page is not defined, or '' or -1
$offset = $listlimit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$search_code = GETPOST('search_code', 'alpha');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('admin'));

//// This page is a generic page to edit dictionaries
//// Put here declaration of dictionaries properties
//
// Sort order to show dictionary (0 is space). All other dictionaries (added by modules) will be at end of this.
//$taborder=array(9,0,4,3,2,0,1,8,19,16,27,0,5,11,0,33,34,0,6,0,29,0,7,24,28,17,35,36,0,10,23,12,13,0,14,0,22,20,18,21,0,15,30,0,26,0,);
$taborder = array(0);
$tabname = array(0);
$tablib = array(0);
$tabsql = array(0);
$tabsqlsort = array(0);
$tabfield = array(0);
$tabfieldvalue = array(0);
$tabfieldinsert = array(0);
$tabrowid = array(0);
$tabcond = array(0);
$tabhelp = array(0);
$tabfieldcheck = array(0);


// Complete all arrays with entries found into modules
complete_dictionary_with_ultimateimmo($taborder, $tabname, $tablib, $tabsql, $tabsqlsort, $tabfield, $tabfieldvalue, $tabfieldinsert, $tabrowid, $tabcond, $tabhelp, $tabfieldcheck);


// Defaut sortorder
if (empty($sortfield)) {
	$tmp1 = explode(',', $tabsqlsort[$id]);
	$tmp2 = explode(' ', $tmp1[0]);
	$sortfield = preg_replace('/^.*\./', '', $tmp2[0]);
}


/*
 * Actions
 */

if (GETPOST('button_removefilter') || GETPOST('button_removefilter.x') || GETPOST('button_removefilter_x')) {
	$search_country_id = '';
	$search_code = '';
}

// Actions add or modify an entry into a dictionary
if (GETPOST('actionadd') || GETPOST('actionmodify')) {
	$listfield = explode(',', str_replace(' ', '', $tabfield[$id]));
	$listfieldinsert = explode(',', $tabfieldinsert[$id]);
	$listfieldmodify = explode(',', $tabfieldinsert[$id]);
	$listfieldvalue = explode(',', $tabfieldvalue[$id]);

	// Check that all fields are filled
	$ok = 1;

	// Si verif ok et action add, on ajoute la ligne
	if ($ok && GETPOST('actionadd')) {
		if ($tabrowid[$id]) {
			// Recupere id libre pour insertion
			$newid = 0;
			$sql = "SELECT max(" . $tabrowid[$id] . ") newid from " . $tabname[$id];
			$result = $db->query($sql);
			if ($result) {
				$obj = $db->fetch_object($result);
				$newid = ($obj->newid + 1);
			} else {
				dol_print_error($db);
			}
		}

		// Add new entry
		$sql = "INSERT INTO " . $tabname[$id] . " (";
		// List of fields
		if ($tabrowid[$id] && !in_array($tabrowid[$id], $listfieldinsert)) {
			$sql .= $tabrowid[$id] . ",";
		}
		$sql .= $tabfieldinsert[$id];
		$sql .= ",active)";
		$sql .= " VALUES(";

		// List of values
		if ($tabrowid[$id] && !in_array($tabrowid[$id], $listfieldinsert)) {
			$sql .= $newid . ",";
		}
		$i = 0;
		foreach ($listfieldinsert as $f => $value) {
			if ($value == 'price' || preg_match('/^amount/i', $value) || $value == 'taux') {
				$_POST[$listfieldvalue[$i]] = price2num($_POST[$listfieldvalue[$i]], 'MU');
			} else if ($value == 'entity') {
				$_POST[$listfieldvalue[$i]] = getEntity($tabname[$id]);
			}
			if ($i) {
				$sql .= ",";
			}
			if ($listfieldvalue[$i] == 'sortorder')
			{
				// For column name 'sortorder', we use the field name 'position'
				$sql .= "'" . (int)$db->escape($_POST['position']) . "'";
			} elseif ($listfieldvalue[$i] == 'date_start' || $listfieldvalue[$i] == 'date_end') {
				$sql .= "'" . $db->idate(dol_mktime(12, 0, 0, GETPOST($listfieldvalue[$i]."_addmonth", 'int'), GETPOST($listfieldvalue[$i]."_addday", 'int'), GETPOST($listfieldvalue[$i]."_addyear", 'int'))) . "'";
			} elseif ($_POST[$listfieldvalue[$i]] == '' && !($listfieldvalue[$i] == 'code' && $id == 10)) {
				$sql .= "null";  // For vat, we want/accept code = ''
			} else {
				$sql .= "'" . $db->escape($_POST[$listfieldvalue[$i]]) . "'";
			}
			$i++;
		}
		$sql .= ",1)";

		dol_syslog("actionadd", LOG_DEBUG);
		$result = $db->query($sql);
		if ($result)    // Add is ok
		{
			setEventMessages($langs->transnoentities("RecordSaved"), null, 'mesgs');
			$_POST = array('id' => $id);    // Clean $_POST array, we keep only
		} else {
			if ($db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
				setEventMessages($langs->transnoentities("ErrorRecordAlreadyExists"), null, 'errors');
			} else {
				dol_print_error($db);
			}
		}
	}

	// Si verif ok et action modify, on modifie la ligne
	if ($ok && GETPOST('actionmodify')) {
		if ($tabrowid[$id]) {
			$rowidcol = $tabrowid[$id];
		} else {
			$rowidcol = "rowid";
		}

		// Modify entry
		$sql = "UPDATE " . $tabname[$id] . " SET ";
		// Modifie valeur des champs
		if ($tabrowid[$id] && !in_array($tabrowid[$id], $listfieldmodify)) {
			$sql .= $tabrowid[$id] . "=";
			$sql .= "'" . $db->escape($rowid) . "', ";
		}
		$i = 0;
		foreach ($listfieldmodify as $field) {
			if ($field == 'price' || preg_match('/^amount/i', $field) || $field == 'taux') {
				$_POST[$listfieldvalue[$i]] = price2num($_POST[$listfieldvalue[$i]], 'MU');
			} else if ($field == 'entity') {
				$_POST[$listfieldvalue[$i]] = getEntity($tabname[$id]);
			}
			if ($i) {
				$sql .= ",";
			}
			$sql .= $field . "=";
			if ($listfieldvalue[$i] == 'sortorder')        // For column name 'sortorder', we use the field name 'position'
			{
				$sql .= "'" . (int)$db->escape($_POST['position']) . "'";
			} elseif ($listfieldvalue[$i] == 'date_start' || $listfieldvalue[$i] == 'date_end') {
				$sql .= "'" . $db->idate(dol_mktime(12, 0, 0, GETPOST($listfieldvalue[$i]."_editmonth", 'int'), GETPOST($listfieldvalue[$i]."_editday", 'int'), GETPOST($listfieldvalue[$i]."_edityear", 'int'))) . "'";
			} elseif ($_POST[$listfieldvalue[$i]] == '' && !($listfieldvalue[$i] == 'code' && $id == 10)) {
				$sql .= "null";  // For vat, we want/accept code = ''
			} else {
				$sql .= "'" . $db->escape($_POST[$listfieldvalue[$i]]) . "'";
			}
			//var_dump($listfieldvalue[$i]);
			$i++;
		}
		$sql .= " WHERE " . $rowidcol . " = '" . $rowid . "'";
		if (in_array('entity', $listfieldmodify)) {
			$sql .= " AND entity = '" . getEntity($tabname[$id]) . "'";
		}

		dol_syslog("actionmodify", LOG_DEBUG);
		//print $sql;
		$resql = $db->query($sql);
		if (!$resql) {
			setEventMessages($db->error(), null, 'errors');
		}
	}
	//$_GET["id"]=GETPOST('id', 'int');       // Force affichage dictionnaire en cours d'edition
}

if (GETPOST('actioncancel')) {
	//$_GET["id"]=GETPOST('id', 'int');       // Force affichage dictionnaire en cours d'edition
}

if ($action == 'confirm_delete' && $confirm == 'yes')       // delete
{
	if ($tabrowid[$id]) {
		$rowidcol = $tabrowid[$id];
	} else {
		$rowidcol = "rowid";
	}

	$sql = "DELETE FROM " . $tabname[$id] . " WHERE " . $rowidcol . "='" . $rowid . "'" . ($entity != '' ? " AND entity = " . (int)$entity : '');

	dol_syslog("delete", LOG_DEBUG);
	$result = $db->query($sql);
	if (!$result) {
		if ($db->errno() == 'DB_ERROR_CHILD_EXISTS') {
			setEventMessages($langs->transnoentities("ErrorRecordIsUsedByChild"), null, 'errors');
		} else {
			dol_print_error($db);
		}
	}
}

// activate
if ($action == $acts[0]) {
	if ($tabrowid[$id]) {
		$rowidcol = $tabrowid[$id];
	} else {
		$rowidcol = "rowid";
	}

	if ($rowid) {
		$sql = "UPDATE " . $tabname[$id] . " SET active = 1 WHERE " . $rowidcol . "='" . $rowid . "'" . ($entity != '' ? " AND entity = " . (int)$entity : '');
	}

	$result = $db->query($sql);
	if (!$result) {
		dol_print_error($db);
	}
}

// disable
if ($action == $acts[1]) {
	if ($tabrowid[$id]) {
		$rowidcol = $tabrowid[$id];
	} else {
		$rowidcol = "rowid";
	}

	if ($rowid) {
		$sql = "UPDATE " . $tabname[$id] . " SET active = 0 WHERE " . $rowidcol . "='" . $rowid . "'" . ($entity != '' ? " AND entity = " . (int)$entity : '');
	}

	$result = $db->query($sql);
	if (!$result) {
		dol_print_error($db);
	}
}

/*
 * View
 */

$form = new Form($db);
$formadmin = new FormAdmin($db);

llxHeader();

$titre = $langs->trans("DictionarySetup");
$linkback = '';
if ($id) {
	$titre .= ' - ' . $langs->trans($tablib[$id]);
	$linkback = '<a href="' . $_SERVER['PHP_SELF'] . '">' . $langs->trans("BackToDictionaryList") . '</a>';
}
$titlepicto = 'title_setup';
if ($id == 10 && GETPOST('from') == 'accountancy') {
	$titre = $langs->trans("MenuVatAccounts");
	$titlepicto = 'title_accountancy';
}
if ($id == 7 && GETPOST('from') == 'accountancy') {
	$titre = $langs->trans("MenuTaxAccounts");
	$titlepicto = 'title_accountancy';
}

print load_fiche_titre($titre, $linkback, $titlepicto);

if (empty($id)) {
	print $langs->trans("DictionaryDescExtra") . "<br>\n";
}
print "<br>\n";


$param = '&id=' . urlencode($id);
if ($search_country_id > 0) {
	$param .= '&search_country_id=' . urlencode($search_country_id);
}
if ($search_code != '') {
	$param .= '&search_code=' . urlencode($search_country_id);
}
if ($entity != '') {
	$param .= '&entity=' . (int)$entity;
}
$paramwithsearch = $param;
if ($sortorder) {
	$paramwithsearch .= '&sortorder=' . urlencode($sortorder);
}
if ($sortfield) {
	$paramwithsearch .= '&sortfield=' . urlencode($sortfield);
}
if (GETPOST('from')) {
	$paramwithsearch .= '&from=' . urlencode(GETPOST('from', 'alpha'));
}


// Confirmation de la suppression de la ligne
if ($action == 'delete') {
	print $form->formconfirm($_SERVER["PHP_SELF"] . '?' . ($page ? 'page=' . $page . '&' : '') . 'rowid=' . $rowid . '&code=' . urlencode($code) . $paramwithsearch, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_delete', '', 0, 1);
}

/*
 * Show a dictionary
 */
if ($id) {
	// Complete requete recherche valeurs avec critere de tri
	$sql = $tabsql[$id];

	if (!preg_match('/ WHERE /', $sql)) {
		$sql .= " WHERE 1 = 1";
	}


	if ($sortfield) {
		$sql .= " ORDER BY " . $db->escape($sortfield);
		if ($sortorder) {
			$sql .= " " . strtoupper($db->escape($sortorder));
		}
		$sql .= ", ";
		// Clear the required sort criteria for the tabsqlsort to be able to force it with selected value
		$tabsqlsort[$id] = preg_replace('/([a-z]+\.)?' . $sortfield . ' ' . $sortorder . ',/i', '', $tabsqlsort[$id]);
		$tabsqlsort[$id] = preg_replace('/([a-z]+\.)?' . $sortfield . ',/i', '', $tabsqlsort[$id]);
	} else {
		$sql .= " ORDER BY ";
	}
	$sql .= $tabsqlsort[$id];
	$sql .= $db->plimit($listlimit + 1, $offset);
	//print $sql;

	if (empty($tabfield[$id])) {
		dol_print_error($db, 'The table with id ' . $id . ' has no array tabfield defined');
		exit;
	}
	$fieldlist = explode(',', $tabfield[$id]);

	print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" method="POST">';
	if ((float)DOL_VERSION >= 11) {
		print '<input type="hidden" name="token" value="' . newToken() . '">';
	} else {
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	}
	print '<input type="hidden" name="from" value="' . dol_escape_htmltag(GETPOST('from', 'alpha')) . '">';

	// Form to add a new line
	if ($tabname[$id]) {
		$alabelisused = 0;
		$withentity = null;

		$fieldlist = explode(',', $tabfield[$id]);

		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder" width="100%">';

		// Line for title
		print '<tr class="liste_titre">';
		foreach ($fieldlist as $field => $value) {
			if ($fieldlist[$field] == 'entity') {
				$withentity = getEntity($tabname[$id]);
				continue;
			}
			if ($fieldlist[$field] == 'libelle' || $fieldlist[$field] == 'label') {
				if ($id != 25) {
					$valuetoshow = $form->textwithtooltip($langs->trans("Label"), $langs->trans("LabelUsedByDefault"), 2, 1, img_help(1, ''));
				} else {
					$valuetoshow = $langs->trans("Label");
				}
			}
			if ($valuetoshow != '') {
				print '<td' . ($class ? ' class="' . $class . '"' : '') . '>';
				if (!empty($tabhelp[$id][$value]) && preg_match('/^http(s*):/i', $tabhelp[$id][$value])) {
					print '<a href="' . $tabhelp[$id][$value] . '" target="_blank">' . $valuetoshow . ' ' . img_help(1, $valuetoshow) . '</a>';
				} else if (!empty($tabhelp[$id][$value])) {
					print $form->textwithpicto($valuetoshow, $tabhelp[$id][$value]);
				} else {
					print $valuetoshow;
				}
				print '</td>';
			}
			if ($fieldlist[$field] == 'libelle' || $fieldlist[$field] == 'label') {
				$alabelisused = 1;
			}
		}

		if ($id == 4) {
			print '<td></td>';
		}
		print '<td>';
		print '<input type="hidden" name="id" value="' . $id . '">';
		if (!is_null($withentity)) {
			print '<input type="hidden" name="entity" value="' . $withentity . '">';
		}
		print '</td>';
		print '<td style="min-width: 26px;"></td>';
		print '<td style="min-width: 26px;"></td>';
		print '</tr>';

		// Line to enter new values
		print '<!-- line to add new entry --><tr class="oddeven row-selectable nodrag nodrop nohover">';

		$obj = new stdClass();
		// If data was already input, we define them in obj to populate input fields.
		if (GETPOST('actionadd')) {
			foreach ($fieldlist as $key => $val) {
				if (GETPOST($val) != '') {
					$obj->$val = GETPOST($val);
				}
			}
		}

		$tmpaction = 'create';
		$parameters = array('fieldlist' => $fieldlist, 'tabname' => $tabname[$id]);
		$reshook = $hookmanager->executeHooks('createDictionaryFieldlist', $parameters, $obj, $tmpaction);    // Note that $action and $object may have been modified by some hooks
		$error = $hookmanager->error;
		$errors = $hookmanager->errors;

		if ($id == 3) {
			unset($fieldlist[2]); // Remove field ??? if dictionary Regions
		}

		if (empty($reshook)) {
			fieldList($fieldlist, $obj, $tabname[$id], 'add');
		}

		if ($id == 4) {
			print '<td></td>';
		}
		print '<td colspan="3" align="center">';
		if ($action != 'edit') {
			print '<input type="submit" class="button" name="actionadd" value="' . $langs->trans("Add") . '">';
		}
		print '</td>';
		print "</tr>";

		$colspan = count($fieldlist) + 3;
		if ($id == 4) {
			$colspan++;
		}

		print '</table>';
		print '</div>';
	}

	print '</form>';

	print '<br>';

	print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" method="POST">';
	if ((float)DOL_VERSION >= 11) {
		print '<input type="hidden" name="token" value="' . newToken() . '">';
	} else {
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	}
	print '<input type="hidden" name="from" value="' . dol_escape_htmltag(GETPOST('from', 'alpha')) . '">';

	// List of available record in database
	dol_syslog("htdocs/admin/dict", LOG_DEBUG);
	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		$i = 0;

		// There is several pages
		if ($num > $listlimit || $page) {
			print_fleche_navigation($page, $_SERVER["PHP_SELF"], $paramwithsearch, ($num > $listlimit), '<li class="pagination"><span>' . $langs->trans("Page") . ' ' . ($page + 1) . '</span></li>');
			print '<div class="clearboth"></div>';
		}

		print '<div class="div-table-responsive">';
		print '<table class="noborder" width="100%">';
		print '</td>';
		print '</tr>';

		// Title of lines
		print '<tr class="liste_titre">';
		foreach ($fieldlist as $field => $value) {
			if ($fieldlist[$field] == 'entity') {
				continue;
			}

			// Determine le nom du champ par rapport aux noms possibles
			// dans les dictionnaires de donnees
			$showfield = 1;                                  // By defaut
			$align = "left";
			$sortable = 1;
			$valuetoshow = '';
			/*
			$tmparray=getLabelOfField($fieldlist[$field]);
			$showfield=$tmp['showfield'];
			$valuetoshow=$tmp['valuetoshow'];
			$align=$tmp['align'];
			$sortable=$tmp['sortable'];
			*/
			$valuetoshow = ucfirst($fieldlist[$field]);   // By defaut
			$valuetoshow = $langs->trans($valuetoshow);   // try to translate
			//            if ($fieldlist[$field]=='type')            { $valuetoshow=$langs->trans("Type"); }
			////            if ($fieldlist[$field]=='code')            { $valuetoshow=$langs->trans("Code"); }
			//            if ($fieldlist[$field]=='position')        { $align='right'; }
			if ($fieldlist[$field] == 'libelle' || $fieldlist[$field] == 'label') {
				//if ($id != 25) $valuetoshow=$form->textwithtooltip($langs->trans("Label"), $langs->trans("LabelUsedByDefault"),2,1,img_help(1,''));
				//else $valuetoshow=$langs->trans("Label");
				$valuetoshow = $langs->trans("Label");
			}
			// Affiche nom du champ
			if ($showfield && $fieldlist[$field] != 'code') {
				print getTitleFieldOfList($valuetoshow, 0, $_SERVER["PHP_SELF"], ($sortable ? $fieldlist[$field] : ''), ($page ? 'page=' . $page . '&' : ''), $param, "align=" . $align, $sortfield, $sortorder);
			}
		}

		print getTitleFieldOfList($langs->trans("Status"), 0, $_SERVER["PHP_SELF"], "active", ($page ? 'page=' . $page . '&' : ''), $param, 'align="center"', $sortfield, $sortorder);
		print getTitleFieldOfList('');
		print getTitleFieldOfList('');
		print '</tr>';

		if ($num) {
			// Lines with values
			while ($i < $num) {
				$obj = $db->fetch_object($resql);
				//print_r($obj);
				print '<tr class="oddeven row-selectable" id="rowid-' . $obj->rowid . '">';
				if ($action == 'edit' && ($rowid == (!empty($obj->rowid) ? $obj->rowid : $obj->code))) {
					$tmpaction = 'edit';
					$parameters = array('fieldlist' => $fieldlist, 'tabname' => $tabname[$id]);
					$reshook = $hookmanager->executeHooks('editDictionaryFieldlist', $parameters, $obj, $tmpaction);    // Note that $action and $object may have been modified by some hooks
					$error = $hookmanager->error;
					$errors = $hookmanager->errors;

					// Show fields
					if (empty($reshook)) {
						$withentity = fieldList($fieldlist, $obj, $tabname[$id], 'edit');
					}

					print '<td colspan="3" align="center">';
					print '<input type="hidden" name="page" value="' . $page . '">';
					print '<input type="hidden" name="rowid" value="' . $rowid . '">';
					if (!is_null($withentity)) {
						print '<input type="hidden" name="entity" value="' . $withentity . '">';
					}
					print '<input type="submit" class="button" name="actionmodify" value="' . $langs->trans("Modify") . '">';
					print '<input type="submit" class="button" name="actioncancel" value="' . $langs->trans("Cancel") . '">';
					print '</td>';
				} else {
					$tmpaction = 'view';
					$parameters = array('fieldlist' => $fieldlist, 'tabname' => $tabname[$id]);
					$reshook = $hookmanager->executeHooks('viewDictionaryFieldlist', $parameters, $obj, $tmpaction);    // Note that $action and $object may have been modified by some hooks

					$error = $hookmanager->error;
					$errors = $hookmanager->errors;

					if (empty($reshook)) {
						$withentity = null;

						foreach ($fieldlist as $field => $value) {
							$showfield = 1;
							$align = "left";
							$valuetoshow = $obj->{$fieldlist[$field]};

							if ($fieldlist[$field] == 'entity') {
								$withentity = $valuetoshow;
								continue;
							}

							if ($value == 'element') {
								$valuetoshow = isset($elementList[$valuetoshow]) ? $elementList[$valuetoshow] : $valuetoshow;
							} else if ($value == 'source') {
								$valuetoshow = isset($sourceList[$valuetoshow]) ? $sourceList[$valuetoshow] : $valuetoshow;
							} else if ($valuetoshow == 'all') {
								$valuetoshow = $langs->trans('All');
							}

							if ($value == 'private') {
								$valuetoshow = yn($elementList[$valuetoshow]);
							}


							$class = 'tddict';
							if ($fieldlist[$field] == 'note' && $id == 10) {
								$class .= ' tdoverflowmax200';
							}
							if ($fieldlist[$field] == 'tracking') {
								$class .= ' tdoverflowauto';
							}
							if ($fieldlist[$field] == 'position') {
								$class .= ' right';
							}
							if ($fieldlist[$field] == 'localtax1_type') {
								$class .= ' nowrap';
							}
							if ($fieldlist[$field] == 'localtax2_type') {
								$class .= ' nowrap';
							}
							// Show value for field
							if ($showfield && $fieldlist[$field] != 'code') {
								print '<!-- ' . $fieldlist[$field] . ' --><td align="' . $align . '" class="' . $class . '">' . $valuetoshow . '</td>';
							}
						}
					}

					// Can an entry be erased or disabled ?
					$iserasable = 1;
					$canbedisabled = 1;
					$canbemodified = 1;    // true by default
					if (isset($obj->code) && $id != 10) {
						if (($obj->code == '0' || $obj->code == '' || preg_match('/unknown/i', $obj->code))) {
							$iserasable = 0;
							$canbedisabled = 0;
						} else if ($obj->code == 'RECEP') {
							$iserasable = 0;
							$canbedisabled = 0;
						} else if ($obj->code == 'EF0') {
							$iserasable = 0;
							$canbedisabled = 0;
						}
					}

					if (isset($obj->type) && in_array($obj->type, array('system', 'systemauto'))) {
						$iserasable = 0;
					}
					if (in_array($obj->code, array('AC_OTH', 'AC_OTH_AUTO')) || in_array($obj->type, array('systemauto'))) {
						$canbedisabled = 0;
						$canbedisabled = 0;
					}
					$canbemodified = $iserasable;
					if ($obj->code == 'RECEP') {
						$canbemodified = 1;
					}
					if ($tabname[$id] == MAIN_DB_PREFIX . "c_actioncomm") {
						$canbemodified = 1;
					}

					// Url
					$rowidcol = $tabrowid[$id];
					// If rowidcol not defined
					if (empty($rowidcol) || in_array($id, array(6, 7, 8, 13, 17, 19, 27))) {
						$rowidcol = 'rowid';
					}
					$url = $_SERVER["PHP_SELF"] . '?' . ($page ? 'page=' . $page . '&' : '') . 'sortfield=' . $sortfield . '&sortorder=' . $sortorder . '&rowid=' . ((!empty($obj->{$rowidcol}) || $obj->{$rowidcol} == '0') ? $obj->{$rowidcol} : (!empty($obj->code) ? urlencode($obj->code) : '')) . '&code=' . (!empty($obj->code) ? urlencode($obj->code) : '');
					if (!empty($param)) {
						$url .= '&' . $param;
					}
					if (!is_null($withentity)) {
						$url .= '&entity=' . $withentity;
					}
					$url .= '&';

					// Active
					print '<td align="center" class="nowrap">';
					if ($canbedisabled) {
						print '<a href="' . $url . 'action=' . $acts[$obj->active] . '">' . $actl[$obj->active] . '</a>';
					} else {
						if (in_array($obj->code, array('AC_OTH', 'AC_OTH_AUTO'))) {
							print $langs->trans("AlwaysActive");
						} else if (isset($obj->type) && in_array($obj->type, array('systemauto')) && empty($obj->active)) {
							print $langs->trans("Deprecated");
						} else if (isset($obj->type) && in_array($obj->type, array('system')) && !empty($obj->active) && $obj->code != 'AC_OTH') {
							print $langs->trans("UsedOnlyWithTypeOption");
						} else {
							print $langs->trans("AlwaysActive");
						}
					}
					print "</td>";

					// Modify link
					if ($canbemodified) {
						print '<td align="center"><a class="reposition" href="' . $url . 'action=edit">' . img_edit() . '</a></td>';
					} else {
						print '<td>&nbsp;</td>';
					}

					// Delete link
					if ($iserasable) {
						print '<td align="center">';
						if ($user->rights->gestionparc->dict) {
							print '<a href="' . $url . 'action=delete">' . img_delete() . '</a>';
						}
						//else print '<a href="#">'.img_delete().'</a>';    // Some dictionary can be edited by other profile than admin
						print '</td>';
					} else {
						print '<td>&nbsp;</td>';
					}

					print "</tr>\n";
				}
				$i++;
			}
		}

		print '</table>';
		print '</div>';
	} else {
		dol_print_error($db);
	}


	print '</form>';
} else {
	/*
	 * Show list of dictionary to show
	 */

	$lastlineisempty = false;

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	//print '<td>'.$langs->trans("Module").'</td>';
	print '<td colspan="2">' . $langs->trans("Dictionary") . '</td>';
	print '<td>' . $langs->trans("Table") . '</td>';
	print '</tr>';

	$showemptyline = '';
	foreach ($taborder as $i) {
		if (isset($tabname[$i]) && empty($tabcond[$i])) {
			continue;
		}

		if ($i) {
			if ($showemptyline) {
				print '<tr class="oddeven row-selectable"><td width="30%">&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
				$showemptyline = 0;
			}


			$value = $tabname[$i];
			print '<tr class="oddeven row-selectable"><td width="50%">';
			if (!empty($tabcond[$i])) {
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id=' . $i . '">' . $langs->trans($tablib[$i]) . '</a>';
			} else {
				print $langs->trans($tablib[$i]);
			}
			print '</td>';
			print '<td>';
			/*if (empty($tabcond[$i]))
			 {
			 print info_admin($langs->trans("DictionaryDisabledSinceNoModuleNeedIt"),1);
			 }*/
			print '</td>';
			print '<td>' . $tabname[$i] . '</td></tr>';
			$lastlineisempty = false;
		} else {
			if (!$lastlineisempty) {
				$showemptyline = 1;
				$lastlineisempty = true;
			}
		}
	}
	print '</table>';
	print '</div>';
}

print '<br>';


llxFooter();
$db->close();


/**
 *    Show fields in insert/edit mode
 *
 * @param array $fieldlist Array of fields
 * @param Object $obj If we show a particular record, obj is filled with record fields
 * @param string $tabname Name of SQL table
 * @param string $context 'add'=Output field for the "add form", 'edit'=Output field for the "edit form", 'hide'=Output field for the "add form" but we dont want it to be rendered
 * @return string                        '' or value of entity into table
 */
function fieldList($fieldlist, $obj = '', $tabname = '', $context = '')
{
	global $conf, $langs, $db, $mysoc;
	global $form;
	global $region_id;
	global $elementList, $sourceList, $localtax_typeList;
	global $bc;

	$formadmin = new FormAdmin($db);
	$formcompany = new FormCompany($db);
	if (!empty($conf->accounting->enabled)) {
		$formaccounting = new FormAccounting($db);
	}

	$withentity = '';

	foreach ($fieldlist as $field => $value) {
		if ($fieldlist[$field] == 'entity') {
			$withentity = $obj->{$fieldlist[$field]};
			continue;
		}

		if (in_array($fieldlist[$field], array('code', 'libelle', 'type')) && $tabname == MAIN_DB_PREFIX . "c_actioncomm" && in_array($obj->type, array('system', 'systemauto'))) {
			$hidden = (!empty($obj->{$fieldlist[$field]}) ? $obj->{$fieldlist[$field]} : '');
			print '<td>';
			print '<input type="hidden" name="' . $fieldlist[$field] . '" value="' . $hidden . '">';
			print $langs->trans($hidden);
			print '</td>';
		} else {
			//    if ($fieldlist[$field]=='sortorder') $fieldlist[$field]='position';

			$classtd = '';
			$class = '';
			if ($fieldlist[$field] == 'libelle' || $fieldlist[$field] == 'label') {
				$classtd = 'centpercent';
			}
			print '<td class="' . $classtd . '">';
			$transfound = 0;
			if (in_array($fieldlist[$field], array('label', 'libelle'))) {
				$transkey = '';
				// Special case for labels
				if ($transkey && $langs->trans($transkey) != $transkey) {
					$transfound = 1;
					print $form->textwithpicto($langs->trans($transkey), $langs->trans("GoIntoTranslationMenuToChangeThis"));
				}
			}

			if ($fieldlist[$field] == 'date_start' || $fieldlist[$field] == 'date_end') {
				print $form->selectDate((isset($obj->{$fieldlist[$field]}) ? $obj->{$fieldlist[$field]} : ''), $fieldlist[$field].'_'.$context,0,0,1);
			} elseif (!$transfound && $fieldlist[$field] != 'code') {
				print '<input type="text" class="flat' . ($class ? ' ' . $class : '') . '" value="' . (isset($obj->{$fieldlist[$field]}) ? $obj->{$fieldlist[$field]} : '') . '" name="' . $fieldlist[$field] . '" required="required">';
			}
			print '</td>';
		}
	}

	return $withentity;
}

/**
 *  Add external modules to list of dictionaries
 *
 * @param array $taborder Taborder
 * @param array $tabname Tabname
 * @param array $tablib Tablib
 * @param array $tabsql Tabsql
 * @param array $tabsqlsort Tabsqlsort
 * @param array $tabfield Tabfield
 * @param array $tabfieldvalue Tabfieldvalue
 * @param array $tabfieldinsert Tabfieldinsert
 * @param array $tabrowid Tabrowid
 * @param array $tabcond Tabcond
 * @param array $tabhelp Tabhelp
 * @param array $tabfieldcheck Tabfieldcheck
 * @return int            1
 */
function complete_dictionary_with_ultimateimmo(&$taborder, &$tabname, &$tablib, &$tabsql, &$tabsqlsort, &$tabfield, &$tabfieldvalue, &$tabfieldinsert, &$tabrowid, &$tabcond, &$tabhelp, &$tabfieldcheck)
{
	global $db, $modules, $conf, $langs;

	// Search modules
	$dir = DOL_DOCUMENT_ROOT . "/custom/ultimateimmo/core/modules/";
	$i = 0; // is a sequencer of modules found
	$j = 0; // j is module number. Automatically affected if module number not defined.

	// Load modules attributes in arrays (name, numero, orders) from dir directory
	//print $dir."\n<br>";
	dol_syslog("Scan directory " . $dir . " for modules");
	$handle = @opendir(dol_osencode($dir));
	if (is_resource($handle)) {
		while (($file = readdir($handle)) !== false) {
			//print "$i ".$file."\n<br>";
			if (is_readable($dir . $file) && substr($file, 0, 3) == 'mod' && substr($file, dol_strlen($file) - 10) == '.class.php') {
				$modName = substr($file, 0, dol_strlen($file) - 10);

				if ($modName) {
					include_once $dir . $file;
					$objMod = new $modName($db);

					if ($objMod->numero > 0) {
						$j = $objMod->numero;
					} else {
						$j = 1000 + $i;
					}

					$modulequalified = 1;

					// We discard modules according to features level (PS: if module is activated we always show it)
					$const_name = 'MAIN_MODULE_' . strtoupper(preg_replace('/^mod/i', '', get_class($objMod)));
					if ($objMod->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2 && !$conf->global->$const_name) {
						$modulequalified = 0;
					}
					if ($objMod->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1 && !$conf->global->$const_name) {
						$modulequalified = 0;
					}
					//If module is not activated disqualified
					if (empty($conf->global->$const_name)) {
						$modulequalified = 0;
					}

					if ($modulequalified) {
						// Load languages files of module
						if (isset($objMod->langfiles) && is_array($objMod->langfiles)) {
							foreach ($objMod->langfiles as $langfile) {
								$langs->load($langfile);
							}
						}

						// Complete arrays
						//&$tabname,&$tablib,&$tabsql,&$tabsqlsort,&$tabfield,&$tabfieldvalue,&$tabfieldinsert,&$tabrowid,&$tabcond
						if (empty($objMod->dictionaries) && !empty($objMod->dictionnaries)) {
							$objMod->dictionaries = $objMod->dictionnaries;        // For backward compatibility
						}

						if (!empty($objMod->dictionaries)) {
							$nbtabname = $nbtablib = $nbtabsql = $nbtabsqlsort = $nbtabfield = $nbtabfieldvalue = $nbtabfieldinsert = $nbtabrowid = $nbtabcond = $nbtabfieldcheck = $nbtabhelp = 0;
							foreach ($objMod->dictionaries['tabname'] as $val) {
								$nbtabname++;
								$taborder[] = max($taborder) + 1;
								$tabname[] = $val;
							}
							foreach ($objMod->dictionaries['tablib'] as $val) {
								$nbtablib++;
								$tablib[] = $val;
							}
							foreach ($objMod->dictionaries['tabsql'] as $val) {
								$nbtabsql++;
								$tabsql[] = $val;
							}
							foreach ($objMod->dictionaries['tabsqlsort'] as $val) {
								$nbtabsqlsort++;
								$tabsqlsort[] = $val;
							}
							foreach ($objMod->dictionaries['tabfield'] as $val) {
								$nbtabfield++;
								$tabfield[] = $val;
							}
							foreach ($objMod->dictionaries['tabfieldvalue'] as $val) {
								$nbtabfieldvalue++;
								$tabfieldvalue[] = $val;
							}
							foreach ($objMod->dictionaries['tabfieldinsert'] as $val) {
								$nbtabfieldinsert++;
								$tabfieldinsert[] = $val;
							}
							foreach ($objMod->dictionaries['tabrowid'] as $val) {
								$nbtabrowid++;
								$tabrowid[] = $val;
							}
							foreach ($objMod->dictionaries['tabcond'] as $val) {
								$nbtabcond++;
								$tabcond[] = $val;
							}
							if (!empty($objMod->dictionaries['tabhelp'])) {
								foreach ($objMod->dictionaries['tabhelp'] as $val) {
									$nbtabhelp++;
									$tabhelp[] = $val;
								}
							}
							if (!empty($objMod->dictionaries['tabfieldcheck'])) {
								foreach ($objMod->dictionaries['tabfieldcheck'] as $val) {
									$nbtabfieldcheck++;
									$tabfieldcheck[] = $val;
								}
							}

							if ($nbtabname != $nbtablib || $nbtablib != $nbtabsql || $nbtabsql != $nbtabsqlsort) {
								print 'Error in descriptor of module ' . $const_name . '. Array ->dictionaries has not same number of record for key "tabname", "tablib", "tabsql" and "tabsqlsort"';
								//print "$const_name: $nbtabname=$nbtablib=$nbtabsql=$nbtabsqlsort=$nbtabfield=$nbtabfieldvalue=$nbtabfieldinsert=$nbtabrowid=$nbtabcond=$nbtabfieldcheck=$nbtabhelp\n";
							}
						}

						$j++;
						$i++;
					} else {
						dol_syslog("Module " . get_class($objMod) . " not qualified");
					}
				}
			}
		}
		closedir($handle);
	} else {
		dol_syslog("htdocs/admin/modules.php: Failed to open directory " . $dir . ". See permission and open_basedir option.", LOG_WARNING);
	}

	return 1;
}
