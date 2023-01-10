<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2021 Philippe GRAND 	<philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/property/diagnostic.php
 * \ingroup ultimateimmo
 * \brief   Diagnostic
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

dol_include_once('/ultimateimmo/lib/immoproperty.lib.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/html.formultimateimmo.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo"));

// Parameters
$id = GETPOST('rowid') ? GETPOST('rowid', 'int') : GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

// Security check
if (! $user->rights->ultimateimmo->read) {
	accessforbidden();
}

/*
 * Action
 */
if (preg_match('/set_(.*)/', $action, $reg)) {
	$code = $reg[1];
	if (dolibarr_set_const($db, $code, 1, 'chaine', 0, '', $conf->entity) > 0) {
		Header("Location: " . $_SERVER["PHP_SELF"]);
		exit;
	} else {
		dol_print_error($db);
	}
}

if (preg_match('/del_(.*)/', $action, $reg)) {
	$code = $reg[1];
	if (dolibarr_del_const($db, $code, $conf->entity) > 0) {
		Header("Location: " . $_SERVER["PHP_SELF"]);
		exit;
	} else {
		dol_print_error($db);
	}
}

/*
 * View
 */

$html = new Form($db);
$htmlimmo = new FormUltimateimmo($db);
$object = new Immoproperty($db);
$result = $object->fetch($id);

if ($result < 0) {
	setEventMessages(null, $object->errors, 'errors');
}

$page_name = $langs->trans("Property") . '|' . $langs->trans("Diagnostic");
llxheader('', $page_name, '');

// Configuration header
$head = immopropertyPrepareHead($object);

dol_fiche_head($head, 'diagnostic', $langs->trans("Property"), -1, 'company');

// Subheader
$linkback = '<a href="' . dol_buildpath('/ultimateimmo/property/immoproperty_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

$morehtmlref = '<div class="refidno">';
// Ref owner
$staticImmoowner = new ImmoOwner($db);
$staticImmoowner->fetch($object->fk_owner);
$morehtmlref .= $form->editfieldkey("RefOwner", 'ref_owner', $staticImmoowner->ref, $object, $permissiontoadd, 'string', '', 0, 1);
$morehtmlref .= $form->editfieldval("RefOwner", 'ref_owner', $staticImmoowner->ref . ' - ' . $staticImmoowner->getFullName($langs), $object, $permissiontoadd, 'string', '', null, null, '', 1);

$morehtmlref .= '</div>';

dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';
print '<table class="border centpercent">' . "\n";

print '<div align="center" class="info">';
print '<em><b>' . $langs->trans("TheTechnicalDiagnosticFileDDT") . '</em></b>';
print '</div>';

// Addresses area
print load_fiche_titre($langs->trans("ObligationsOfTheSeller"), '', '') . '<br>';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameters") . '</td>' . "\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">' . $langs->trans("Value") . '</td>' . "\n";
print '</tr>';

// The state of the internal electricity installation.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticElectricityInstallation") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_ELECTRICITY_INSTALLATION_'.$id);
print '</td></tr>';

// The state of the "natural" indoor gas installation.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticGasInstallation") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_GAS_INSTALLATION_'.$id);
print '</td></tr>';

// The risk of exposure to lead (CREP).
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticLeadExposure") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_LEAD_EXPOSURE_'.$id);
print '</td></tr>';

// The condition mentioning the presence or absence of materials or products containing asbestos.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticContainingAsbestos") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_CONTAINING_ASBESTOS_'.$id);
print '</td></tr>';

// condition relating to the presence of termites in the building.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticContainingTermites") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_CONTAINING_TERMITES_'.$id);
print '</td></tr>';

// the state of natural and technological risks.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticNaturalTechnologicalRisks") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_NATURAL_TECHNOLOGICAL_RISKS_'.$id);
print '</td></tr>';

// state of non-collective sanitation facilities.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticNonCollectiveSanitationFacilities") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_NON_COLLECTIVE_SANITATION_FACILITIES_'.$id);
print '</td></tr>';

// building energy performance diagnosis (DPE).
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticEnergyPerformanceDiagnosis") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_ENERGY_PERFORMANCE_DIAGNOSIS_'.$id);
print '</td></tr>';
print '</table>';

// Addresses area
print load_fiche_titre($langs->trans("ObligationsOfTheLessor"), '', '') . '<br>';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameters") . '</td>' . "\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">' . $langs->trans("Value") . '</td>' . "\n";
print '</tr>';

// The risk of exposure to lead (CREP).
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticLeadExposure") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_LESSOR_LEAD_EXPOSURE_'.$id);
print '</td></tr>';

// the state of natural and technological risks.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticNaturalTechnologicalRisks") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_LESSOR_NATURAL_TECHNOLOGICAL_RISKS_'.$id);
print '</td></tr>';

// building energy performance diagnosis (DPE).
print '<tr class="oddeven">';
print '<td>' . $langs->trans("UltimateImmoDiagnosticEnergyPerformanceDiagnosis") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
print ajax_constantonoff('ULTIMATE_IMMO_DIAGNOSTIC_LESSOR_ENERGY_PERFORMANCE_DIAGNOSIS_'.$id);
print '</td></tr>';
print '</table>';

dol_fiche_end();

// End of page
llxFooter();
$db->close();
