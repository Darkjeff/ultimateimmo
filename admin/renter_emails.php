<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2011-2012 Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2012      J. Fernando Lagrange <fernando@demo-tic.org>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2018-2022 Philippe GRAND  		<philippe.grand@atoo-net.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *   	\file       htdocs/custom/ultimateimmo/admin/renter_emails.php
 *		\ingroup    ultimateimmo
 *		\brief      Page to setup the module ultimateimmo
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
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once('../lib/ultimateimmo.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("admin", "ultimateimmo@ultimateimmo"));

if (!$user->admin) {
	accessforbidden();
}


$oldtypetonewone = array('texte' => 'text', 'chaine' => 'string'); // old type to new ones

$action = GETPOST('action', 'aZ09');

$error = 0;

// Editing global variables not related to a specific theme
$constantes = array(
	'RENTER_REMINDER_EMAIL' => array('type' => 'yesno', 'label' => $langs->trans('RENTER_REMINDER_EMAIL', $langs->transnoentities("Module2300Name"))),
	'RENTER_EMAIL_TEMPLATE_REMIND_EXPIRATION' 	=> 'emailtemplate:immorenter',
	'RENTER_MAIL_FROM' => 'string',
);



/*
 * Actions
 */

//
if ($action == 'updateall') {
	$db->begin();

	$res = 0;
	foreach ($constantes as $constname => $value) {
		$constvalue = (GETPOSTISSET('constvalue_' . $constname) ? GETPOST('constvalue_' . $constname, 'alphanohtml') : GETPOST('constvalue'));
		$consttype = (GETPOSTISSET('consttype_' . $constname) ? GETPOST('consttype_' . $constname, 'alphanohtml') : GETPOST('consttype'));
		$constnote = (GETPOSTISSET('constnote_' . $constname) ? GETPOST('constnote_' . $constname, 'restricthtml') : GETPOST('constnote'));

		$typetouse = empty($oldtypetonewone[$consttype]) ? $consttype : $oldtypetonewone[$consttype];
		$constvalue = preg_replace('/:immorenter$/', '', $constvalue);

		$res = dolibarr_set_const($db, $constname, $constvalue, $consttype, 0, $constnote, $conf->entity);
		if ($res <= 0) {
			$error++;
			$action = 'list';
		}
	}

	if ($error > 0) {
		setEventMessages('ErrorFailedToSaveDate', null, 'errors');
		$db->rollback();
	} else {
		setEventMessages('RecordModifiedSuccessfully', null, 'mesgs');
		$db->commit();
	}
}

// Action to update or add a constant
if ($action == 'update' || $action == 'add') {
	$constlineid = GETPOST('rowid', 'int');
	$constname = GETPOST('constname', 'alpha');

	$constvalue = (GETPOSTISSET('constvalue_' . $constname) ? GETPOST('constvalue_' . $constname, 'alphanohtml') : GETPOST('constvalue'));
	$consttype = (GETPOSTISSET('consttype_' . $constname) ? GETPOST('consttype_' . $constname, 'alphanohtml') : GETPOST('consttype'));
	$constnote = (GETPOSTISSET('constnote_' . $constname) ? GETPOST('constnote_' . $constname, 'restricthtml') : GETPOST('constnote'));

	$typetouse = empty($oldtypetonewone[$consttype]) ? $consttype : $oldtypetonewone[$consttype];
	$constvalue = preg_replace('/:immorenter$/', '', $constvalue);

	$res = dolibarr_set_const($db, $constname, $constvalue, $typetouse, 0, $constnote, $conf->entity);

	if (!($res > 0)) {
		$error++;
	}

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}


/*
 * View
 */

$form = new Form($db);

$wikihelp = 'EN:Module_UltimateImmo|FR:Module_UltimateImmo';
llxHeader('', $langs->trans("UltimateimmoSetup"), $wikihelp);


$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans("UltimateimmoSetup"), $linkback, 'title_setup');

// Configuration header
$head = ultimateimmoAdminPrepareHead();
print dol_get_fiche_head($head, 'emails', $langs->trans("ModuleUltimateimmoName"), -1, 'user');

// TODO Use global form
print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="updateall">';

$helptext = '*' . $langs->trans("FollowingConstantsWillBeSubstituted") . '<br>';
$helptext .= '__DOL_MAIN_URL_ROOT__, __ID__, __FIRSTNAME__, __LASTNAME__, __FULLNAME__, ';
$helptext .= '__COMPANY__, __ADDRESS__, __ZIP__, __TOWN__, __COUNTRY__, __EMAIL__, __BIRTH__, __TYPE__, ';
//$helptext.='__YEAR__, __MONTH__, __DAY__';	// Not supported

form_constantes($constantes, 3, $helptext);

print '<div class="center"><input type="submit" class="button" value="' . $langs->trans("Update") . '" name="update"></div>';
print '</form>';

print dol_get_fiche_end();

// End of page
llxFooter();
$db->close();
