<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2021 	Philippe GRAND 	    <philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/property/spread.php
 * \ingroup ultimateimmo
 * \brief   Equipement page
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

global $db, $langs, $user, $conf;

// Libraries
dol_include_once('/ultimateimmo/lib/immoproperty.lib.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/html.formultimateimmo.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immospreadzone.class.php');

// Translations
$langs->loadLangs(array("admin", "ultimateimmo@ultimateimmo"));

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$id = GETPOST('rowid') ? GETPOST('rowid', 'int') : GETPOST('id', 'int');

// Security check
if (! $user->rights->ultimateimmo->read) {
	accessforbidden();
}

$object = new ImmoProperty($db);
$owner = new ImmoOwner($db);
$result = $object->fetch($id);
if ($result<0) {
	setEventMessages($object->error,$object->errors,'errors');
} else {
	$result = $object->fetchAllByBuilding();
	if ($result < 0) {
		setEventMessages($object->error, $object->errors, 'errors');
	}
}

/*
 * Action
 */
if ($action=='save') {
	if (!empty($object->lines)) {
		$total=0;
		foreach($object->lines as $property) {
			$total +=(int)GETPOST('property_'.$property->id,'int');

		}
		if ($total!==1000) {
			setEventMessages($langs->trans('TotalMustBeMille',$total), null, 'errors');
		} else {

			foreach ($object->lines as $property) {
				$spreadData = new ImmoSpreadZone($db);
				$data = $spreadData->fetchAll('', '', 0, 0, array('t.fk_property_parent' => $object->id, 't.fk_property_child' => $property->id));
				if (!is_array($data) && $data < 0) {
					setEventMessages($spreadData->error, $spreadData->errors, 'errors');
				} elseif (count($data) > 1) {
					setEventMessages('ProblemInTableSpread', null, 'errors');
				} elseif (!empty($data)) {
					reset($data)->percent_application = GETPOST('property_' . $property->id, 'int');
					$result = reset($data)->update($user);
					if ($result < 0) {
						setEventMessages(reset($data)->error, reset($data)->errors, 'errors');
					}
				} else {
					$spreadDataNew = new ImmoSpreadZone($db);
					$spreadDataNew->fk_property_parent=$object->id;
					$spreadDataNew->fk_property_child=$property->id;
					$spreadDataNew->percent_application=GETPOST('property_' . $property->id, 'int');
					$resultCreate = $spreadDataNew->create($user);
					if ($result < 0) {
						setEventMessages($spreadDataNew->error, $spreadDataNew->errors, 'errors');
					}

				}
			}
		}

	}
}

/*
 * View
 */

$html = new Form($db);
$htmlimmo = new FormUltimateimmo($db);


if ($result < 0) {
	setEventMessages(null, $object->errors, 'errors');
}

$page_name = $langs->trans("Property") . '|' . $langs->trans("Equipement");
llxheader('', $langs->trans($page_name), '');

// Configuration header
$head = immopropertyPrepareHead($object);

dol_fiche_head($head, 'spreading', $langs->trans("Property"), -1, 'company');

// Subheader
$linkback = '<a href="' . dol_buildpath('/ultimateimmo/property/immoproperty_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

$morehtmlref = '<div class="refidno">';
// Ref owner
$staticImmoowner = new ImmoOwner($db);
$staticImmoowner->fetch($object->fk_owner);
$morehtmlref .= $html->editfieldkey("RefOwner", 'ref_owner', $staticImmoowner->ref, $object, 0, 'string', '', 0, 1);
$morehtmlref .= $html->editfieldval("RefOwner", 'ref_owner', $staticImmoowner->ref . ' - ' . $staticImmoowner->getFullName($langs), $object, $permissiontoadd, 'string', '', null, null, '', 1);

$morehtmlref .= '</div>';

dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';


if (!empty($object->lines)) {

	print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '?id='.$object->id.'">';
	print '<input type="hidden" name="token" value="' . newToken() . '">';
	print '<input type="hidden" name="action" value="save">';
	print '<input type="hidden" name="id" value="' . $object->id . '">';

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("Property") . '</td>' . "\n";
	print '<td align="center" width="100">' . $langs->trans("Millieme") . '</td>' . "\n";
	print '</tr>';


	foreach($object->lines as $property) {

		print '<tr class="oddeven">';
		print '<td>' . $property->label;
		if (!empty($property->address)) {
			print ' ('.$property->address.')';
		}
		if (!empty($property->area)) {
			print ' - '.$property->area;
		}
		if (!empty($property->fk_owner)) {
			$result=$owner->fetch($property->fk_owner);
			if ($result<0) {
				setEventMessages($owner->error,$owner->errors,'errors');
			}
			if (!empty($owner->id)) {
				print ' - '.$owner->firstname . ' ' . $owner->lastname;
			}
		}
		print '</td>';
		print '<td align="center" width="100">';

		$value_millieme=GETPOST('property_'.$property->id,'int');
		$spreadData = new ImmoSpreadZone($db);
		$data = $spreadData->fetchAll('','',0,0, array('t.fk_property_parent'=>$object->id, 't.fk_property_child'=>$property->id));
		if (!is_array($data) && $data<0) {
			setEventMessages($spreadData->error,$spreadData->errors,'errors');
		} elseif (count($data)>1) {
			setEventMessages('ProblemInTableSpread',null,'errors');
		} elseif (!empty($data) && $action!=='save') {
			$value_millieme=reset($data)->percent_application;
		}
		print '<input type="number" name="property_'.$property->id.'" value="'.$value_millieme.'">';
		print '</td></tr>';

	}

	print '</table>';

	print $form->buttonsSaveCancel("Save");
	print '</form>';
}

// Footer
llxFooter();
// Close database handler
$db->close();
