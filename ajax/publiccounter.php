<?php
/* Copyright (C) 2022 Florian HENRY <florian.henry@scopen.fr>
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
 *       \file       dolipad/ajax/inter_event.php
 *       \brief      manage technicien actions on intervention
 */

if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1'); // Disables token renewal
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
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

dol_include_once('/ultimateimmo/class/immocompteur.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');



$action = GETPOST('action', 'aZ09');
$immoProperty = GETPOST('immoProperty','int');
$counterValue = GETPOST('counterValue','int');

$langs->load('ultimateimmo@ultimateimmo');


top_httphead();
$ret = 0;
$errors = array();
//print '<!-- Ajax page called with url '.dol_escape_htmltag($_SERVER["PHP_SELF"]).'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]).' -->'."\n";

$renter = new ImmoRenter($db);


$sql = 'SELECT renter.rowid as renterId FROM ' . MAIN_DB_PREFIX . 'socpeople as sp ';
$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'user as u ON u.fk_socpeople=sp.rowid';
$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . $renter->table_element . ' as renter ON renter.fk_soc=sp.fk_soc';
$sql .= ' WHERE sp.fk_soc=' . (int)$user->socid;
$sql .= ' AND u.rowid="' . $user->id . '"';
$sql .= ' AND sp.email="' . $user->email . '"';
$sql .= ' AND sp.rowid="' . $user->contact_id . '"';
$resql = $db->query($sql);
if ($resql < 0) {
	$errors[] = $db->lasterror;
	dol_syslog(' Error=' . var_export($errors, true), LOG_ERR);
	print json_encode($errors);
	header('HTTP/1.1 500 Internal Server Error');
	exit();
} else {
	$num = $db->num_rows($resql);
	if ($num > 0) {
		while ($obc = $db->fetch_object($resql)) {
			$renterId = $obc->renterId;
		}
	}
}

$sql = "SELECT DISTINCT rec.rowid as reference, rec.label as receiptname,rec.ref as receiptref, loc.lastname as nom, ";
$sql .= " prop.address, prop.label as local, loc.status as status, rec.total_amount as total, rec.partial_payment, ";
$sql .= " rec.balance, rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_owner,";
$sql .= " rec.fk_rent as refcontract, rent.preavis,";
$sql .= " rec.date_echeance, rent.preavis";
$sql .= " ,prop.rowid as propid";
$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as rec";
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc ON loc.rowid = rec.fk_renter AND loc.rowid=" . (int)$renterId;
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as prop ON prop.rowid = rec.fk_property AND prop.rowid=". (int) $immoProperty;
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent ON rent.rowid = rec.fk_rent AND rent.preavis=1";
$sql .= $db->order('rec.date_echeance', 'DESC');
$resql = $db->query($sql);
if ($resql < 0) {
	$errors[] = $db->lasterror;
	dol_syslog(' Error=' . var_export($errors, true), LOG_ERR);
	print json_encode($errors);
	header('HTTP/1.1 500 Internal Server Error');
	exit();
} else {
	$num = $db->num_rows($resql);
	if ($num <= 0) {
		$errors[] = $langs->trans('CounterNotAllowedInput');
		dol_syslog(' Error=' . var_export($errors, true), LOG_ERR);
		print json_encode($errors);
		header('HTTP/1.1 500 Internal Server Error');
		exit();
	}
}


if ($action == 'validCounterInput') {
	$counter = new ImmoCompteur($db);
	$counter->compteur_type_id=1;
	$counter->date_relever=dol_now();
	$counter->fk_immoproperty=$immoProperty;
	$counter->qty=$counterValue;




	$resultCreate = $counter->create($user);
	if ($resultCreate < 0) {
		$errors = array_merge($errors, $counter->errors);
		$errors[] = $counter->error;
		dol_syslog(' Error=' . var_export($errors, true), LOG_ERR);
		print json_encode($errors);
		header('HTTP/1.1 500 Internal Server Error');
		exit();
	}
	$ret = array(1);
}

print json_encode($ret);
