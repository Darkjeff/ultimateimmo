<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2014      Florian Henry        <florian.henry@open-concept.pro>
 * Copyright (C) 2018-2019 Philippe GRAND 		<philippe.grand@atoo-net.com>
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
 * \file htdocs/compta/ventilation/card.php
 * \ingroup compta
 * \brief Page fiche ventilation
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

dol_include_once("/ultimateimmo/class/immocost.class.php");
dol_include_once("/ultimateimmo/class/immoproperty.class.php");
dol_include_once("/ultimateimmo/class/immoreceipt.class.php");
dol_include_once("/ultimateimmo/class/immocost_type.class.php");
dol_include_once("/ultimateimmo/class/immocost_detail.class.php");
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/lib/immocost.lib.php');
dol_include_once('/ultimateimmo/class/html.formultimateimmo.class.php');
dol_include_once('/ultimateimmo/class/immospreadzone.class.php');

$res = dol_include_once("/ultimateimmo/core/modules/ultimateimmo/modules_ultimateimmo.php");
if (!$res)
	die("Include of ultimateimmo");

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "bills", "compta"));

// Get parameters
$mesg = '';
$id = GETPOST('id', 'int');
$action = GETPOST('action');


$htmlimmo = new FormUltimateimmo($db);
$ChargeDet = new ImmoCost_Detail($db);

/*
 * Actions
 */

/*
 * Add ventil charge
 */

if ($action == 'ventil') {
	$mesLignesCochees = GETPOST('mesCasesCochees');

	$cpt = 0;
	$error = 0;

	$db->begin();

	foreach ($mesLignesCochees as $maLigneCochee => $localid) {
		$ChargeDet = new ImmoCost_Detail($db);

		// main info loyer
		$ChargeDet->amount = GETPOST('amount_' . $localid);
		$ChargeDet->fk_cost_type = GETPOST('typecharge');
		$ChargeDet->fk_immocost = $id;
		$ChargeDet->fk_property = $localid;

		$result = $ChargeDet->create($user);

		if ($result < 0) {
			setEventMessages($ChargeDet->error, $ChargeDet->errors, 'errors');
			$error++;
		}
		$cpt++;
	}

	if (empty($error)) {
		$charge = new ImmoCost($db);
		$result = $charge->fetch($id);
		if ($result < 0) {
			setEventMessages($charge->error, $charge->errors, 'errors');
			$error++;
		}
		$charge->dispatch = 1;
		$result = $charge->update($user);
		if ($result < 0) {
			setEventMessages($charge->error, $charge->errors, 'errors');
			$error++;
		}

	}

	if (empty($error)) {
		$db->commit();
		setEventMessages($langs->transnoentities("ReceiptPaymentsAdded"), null, 'mesgs');
		Header("Location: " . dol_buildpath('/ultimateimmo/cost/immocost_card.php', 1) . "?id=" . $id);
	} else {
		$db->rollback();
	}
}

/*
 * View
 *
 */

$form = new Form($db);
$loyer = new Immoreceipt($db);

$help_url = '';
llxheader('', $langs->trans("newventilcharge"), $help_url);

$charge = new ImmoCost ($db);
$result = $charge->fetch($id);

if ($result < 0) {
	setEventMessages($charge->error, $charge->errors, 'errors');
}

$local = new ImmoProperty($db);
$result = $local->fetch($charge->fk_property);

if ($result < 0) {
	setEventMessages($charge->error, $charge->errors, 'errors');
}

if (!empty($local->id)) {
	$result = $local->fetchAllByBuilding();

	if ($result < 0) {
		setEventMessages($local->error, $local->errors, 'errors');
	}
}

if ($id > 0) {
	$head = immocostPrepareHead($charge);

	dol_fiche_head($head, 'repartition', $langs->trans("ImmoCost"), -1, 'ultimateimmo@ultimateimmo');

	// Object Charge
	// ------------------------------------------------------------
	$linkback = '<a href="' . dol_buildpath('/ultimateimmo/cost/immocost_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';

	dol_banner_tab($charge, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

	/*
	 * List properties
	 */

	$i = 0;
	$total = 0;
	print '<form action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="ventil">';


	print '<br><div class="fichecenter"><div class="underbanner clearboth"></div>';
	print '<table class="border tableforfield" width="100%"><tbody>';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans('Property') . '</td>';
	print '<td>' . $langs->trans('ImmoCostVentilNameProperty') . '</td>';
	print '<td>' . $langs->trans('MilliemeTheo') . '</td>';
	print '<td>' . $langs->trans('MilliemeCalc') . '</td>';
	print '<td>' . $langs->trans('ImmoCostVentilAmount') . '</td>';
	print '<td>' . $langs->trans('ImmoCostVentilSelect') . '</td>';
	print "</tr>\n";

	$i = 0;
	if (count($local->lines) > 0) {
		$amount = 0;
		$loc_array = array();
		// Calcul amount par appartement
		foreach ($local->lines as $local_line) {
			// Trouve les loyer existant sur cee lieu pour cette période
			$result = $loyer->fetchByLocalId($local_line->id, array(
				'insidedateloyer' => $charge->datec
			));
			if ($result < 0) {
				setEventMessages($loyer->error, $loyer->errors, 'errors');
			}
			if (count($loyer->lines) > 0) {
				$loc_array[$local_line->id] = $local_line->id;
			}
		}

		//Récupére le milliéme
		$millieme_value=array();
		$millieme_value_all=array();
		$millieme_not_rent_allocate=0;
		$millieme_not_rent_allocate_cnt=0;
		$spreadData = new ImmoSpreadZone($db);
		if (count($local->lines) > 0) {
			foreach ($local->lines as $local_line) {
				$data = $spreadData->fetchAll('', '', 0, 0, array('t.fk_property_parent' => $local->id, 't.fk_property_child' => $local_line->id));
				if (!is_array($data) && $data < 0) {
					setEventMessages($spreadData->error, $spreadData->errors, 'errors');
				} elseif (count($data) > 1) {
					setEventMessages('ProblemInTableSpread', null, 'errors');
				} elseif (!empty($data)) {
					$millieme_value_all[$local_line->id] = reset($data)->percent_application;
					if (array_key_exists($local_line->id, $loc_array)) {
						//building is rent so we get the millieme
						$millieme_value[$local_line->id] = reset($data)->percent_application;
					} else {
						//building is not rent so we get it to affect this millieme to all other that are rented
						$millieme_not_rent_allocate += (int) reset($data)->percent_application;
						$millieme_not_rent_allocate_cnt++;
					}
				}
			}
		}
		if (!empty($millieme_value) && $millieme_not_rent_allocate_cnt>0) {
			foreach ($millieme_value as $localid=>$millieme_ori) {
				//We reafect millimeme the not located rent to to all other rented
				$millieme_value[$localid] = $millieme_ori + ($millieme_not_rent_allocate/count($loc_array));
			}
		}


		// Affichage
		foreach ($local->lines as $local_line) {
			//var_dump($local_line);

			print '<tr class="oddeven">';
			print '<td>' . $local_line->label . '</td>';
			print '<td>';
			if (array_key_exists($local_line->id, $loc_array)) {
				print ' ' . img_picto($langs->trans('Louer'), 'statut4');
			} else {
				print ' ' . img_picto($langs->trans('Vide'), 'statut0');
			}
			print '</td>';
			print '<td>' . ((array_key_exists($local_line->id, $millieme_value_all)) ? price($millieme_value_all[$local_line->id]) : ''). '</td>';
			print '<td>' . ((array_key_exists($local_line->id, $millieme_value)) ? price($millieme_value[$local_line->id]) : ''). '</td>';


			if (array_key_exists($local_line->id, $loc_array)) {
				if (array_key_exists($local_line->id, $millieme_value)) {
					$amount = ($charge->amount / 1000) * $millieme_value[$local_line->id];
				} elseif (count($loc_array) > 0) {
					$amount = $charge->amount / count($loc_array);
				}
				print '<td><input name="amount_' . $local_line->id . '" value="' . price($amount) . '" size="30"></td>';
			} else {
				print '<td><input name="amount_' . $local_line->id . '" value="" size="30"></td>';
			}

			// Colonne choix appartement
			print '<td align="center">';

			print '<input type="checkbox" name="mesCasesCochees[]" value="' . $local_line->id . '"' . ((array_key_exists($local_line->id, $loc_array)) ? "checked" : "") . '/>';
			print '</td>';
			print '</tr>';

			$i++;
		}
	}
	print '<tr><td colspan="5" align="center">' . $langs->trans('Type') . $ChargeDet->showInputField(array(), 'fk_cost_type', '', '', '', '', 0).'</td></tr>';
	print '<tr><td colspan="5" align="center"><input class="button" type="submit" value="' . $langs->trans("ImmoCostVentilAddRepartition") . '" name="addrepartition"></td></tr>';

	print "</tbody></table></div>";

	print '</form>';
}

llxFooter();

$db->close();
