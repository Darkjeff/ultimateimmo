<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021 Philippe GRAND 		<philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/admin/immoreceipt.php
 * \ingroup ultimateimmo
 * \brief   immoreceipt setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
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
if (!$res && file_exists("../../main.inc.php")) $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php")) $res = @include("../../../main.inc.php");
if (!$res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';
dol_include_once('/ultimateimmo/lib/ultimateimmo.lib.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/core/modules/ultimateimmo/doc/pdf_bail_vide.module.php');
dol_include_once('/ultimateimmo/core/modules/ultimateimmo/doc/pdf_quittance.module.php');

// Translations
$langs->loadLangs(array("admin", "errors", "ultimateimmo@ultimateimmo"));

// Access control
if (!$user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'ultimateimmo';

if (empty($conf->global->ULTIMATEIMMO_ADDON_NUMBER)) {
	$conf->global->ULTIMATEIMMO_ADDON_NUMBER = 'mod_ultimateimmo_standard';
}


/*
 * Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

if ($action == 'updateMask') {
	$maskconstimmo = GETPOST('maskconstimmo', 'alpha');
	$maskimmo = GETPOST('maskimmo', 'alpha');
	if (!empty($maskconstimmo))
		$res = dolibarr_set_const($db, $maskconstimmo, $maskimmo, 'chaine', 0, '', $conf->entity);

	if (isset($res)) {
		if ($res > 0)
			setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
		else
			setEventMessages($langs->trans("Error"), null, 'errors');
	}
} else if ($action == 'set_param') {
	$freetext = GETPOST('ULTIMATEIMMO_FREE_TEXT', 'none');	// No alpha here, we want exact string
	$res = dolibarr_set_const($db, "ULTIMATEIMMO_FREE_TEXT", $freetext, 'chaine', 0, '', $conf->entity);
	if ($res <= 0) {
		$error++;
		setEventMessages($langs->trans("Error"), null, 'errors');
	}

	$draft = GETPOST('ULTIMATEIMMO_DRAFT_WATERMARK', 'alpha');
	$res = dolibarr_set_const($db, "ULTIMATEIMMO_DRAFT_WATERMARK", trim($draft), 'chaine', 0, '', $conf->entity);
	if ($res <= 0) {
		$error++;
		setEventMessages($langs->trans("Error"), null, 'errors');
	}

	$dataTypeCost = GETPOST('ULTIMATEIMMO_TYPECOST_ADJUST', 'array');
	$res = dolibarr_set_const($db, "ULTIMATEIMMO_TYPECOST_ADJUST", implode(',',array_values($dataTypeCost)), 'chaine', 0, '', $conf->entity);
	if ($res <= 0) {
		$error++;
		setEventMessages($langs->trans("Error"), null, 'errors');
	}

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
} else if ($action == 'specimen') {
	$modele = GETPOST('module', 'alpha');

	$receipt = new ImmoReceipt($db);
	$receipt->initAsSpecimen();
	//var_dump($receipt->initAsSpecimen());exit;
	// Search template files
	$file = '';
	$classname = '';
	$filefound = 0;
	$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

	foreach ($dirmodels as $reldir) {
		$file = dol_buildpath($reldir . "ultimateimmo/core/modules/ultimateimmo/doc/pdf_" . $modele . ".modules.php", 0);

		if (file_exists($file)) {
			$filefound = 1;
			$classname = "pdf_" . $modele;
			break;
		}
	}

	if ($filefound) {
		require_once $file;

		$module = new $classname($db);

		if ($module->write_file($receipt, $langs) > 0) {
			header("Location: " . DOL_URL_ROOT . "/document.php?modulepart=ultimateimmo&file=SPECIMEN.pdf");
			return;
		} else {
			setEventMessages($module->error, null, 'errors');
			dol_syslog($module->error, LOG_ERR);
		}
	} else {
		setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	}
}

// Activate a model
else if ($action == 'set') {
	$ret = addDocumentModel($value, $type, $label, $scandir);
} else if ($action == 'del') {
	$ret = delDocumentModel($value, $type);
	if ($ret > 0) {
		if ($conf->global->ULTIMATEIMMO_ADDON_PDF == "$value") dolibarr_del_const($db, 'ULTIMATEIMMO_ADDON_PDF', $conf->entity);
	}
}

// Set default model
else if ($action == 'setdoc') {
	if (dolibarr_set_const($db, "ULTIMATEIMMO_ADDON_PDF", $value, 'chaine', 0, '', $conf->entity)) {
		// La constante qui a ete lue en avant du nouveau set
		// on passe donc par une variable pour avoir un affichage coherent
		$conf->global->ULTIMATEIMMO_ADDON_PDF = $value;
	}

	// On active le modele
	$ret = delDocumentModel($value, $type);
	if ($ret > 0) {
		$ret = addDocumentModel($value, $type, $label, $scandir);
	}
} else if ($action == 'setmodel') {
	dolibarr_set_const($db, "ULTIMATEIMMO_ADDON_NUMBER", $value, 'chaine', 0, '', $conf->entity);
}

/**
 * View
 */

$form = new Form($db);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$page_name = "UltimateimmoSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = UltimateimmoAdminPrepareHead();

print dol_get_fiche_head($head, 'quittance', $langs->trans("ModuleUltimateimmoName"), -1, "building@ultimateimmo");


/**
 * ultimateimmo numbering model
 */

print load_fiche_titre($langs->trans("UltimateimmoNumberingModeles"), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td width="100">' . $langs->trans("Name") . '</td>';
print '<td>' . $langs->trans("Description") . '</td>';
print '<td class="nowrap">' . $langs->trans("Example") . '</td>';
print '<td align="center" width="60">' . $langs->trans("Status") . '</td>';
print '<td align="center" width="16">' . $langs->trans("ShortInfo") . '</td>';
print '</tr>' . "\n";

clearstatcache();

foreach ($dirmodels as $reldir) {
	$dir = dol_buildpath($reldir . "ultimateimmo/core/modules/ultimateimmo/");

	if (is_dir($dir)) {
		$handle = opendir($dir);
		if (is_resource($handle)) {
			while (($file = readdir($handle)) !== false) {
				if (substr($file, 0, 17) == 'mod_ultimateimmo_' && substr($file, dol_strlen($file) - 3, 3) == 'php') {
					$file = substr($file, 0, dol_strlen($file) - 4);

					require_once $dir . $file . '.php';

					$module = new $file($db);

					if ($module->isEnabled()) {
						// Show modules according to features level
						if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
						if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

						print '<tr class="oddeven"><td>' . $module->nom . "</td><td>\n";
						print $module->info();
						print '</td>';

						// Show example of numbering model
						print '<td class="nowrap">';
						$tmp = $module->getExample();
						if (preg_match('/^Error/', $tmp)) {
							$langs->load("errors");
							print '<div class="error">' . $langs->trans($tmp) . '</div>';
						} elseif ($tmp == 'NotConfigured') print $langs->trans($tmp);
						else print $tmp;
						print '</td>' . "\n";

						print '<td align="center">';
						if ($conf->global->ULTIMATEIMMO_ADDON_NUMBER == "$file") {
							print img_picto($langs->trans("Activated"), 'switch_on');
						} else {
							print '<a href="' . $_SERVER["PHP_SELF"] . '?action=setmodel&amp;value=' . $file . '&amp;scan_dir=' . $module->scandir . '&amp;label=' . urlencode($module->name) . '&token='.newToken().'">';
							print img_picto($langs->trans("Disabled"), 'switch_off');
							print '</a>';
						}
						print '</td>';

						$receipt = new ImmoReceipt($db);
						$receipt->initAsSpecimen();

						// Info
						$htmltooltip = '';
						$htmltooltip .= '' . $langs->trans("Version") . ': <b>' . $module->getVersion() . '</b><br>';
						$receipt->type = 0;
						$nextval = $module->getNextValue($mysoc, $receipt);
						if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
							$htmltooltip .= '' . $langs->trans("NextValue") . ': ';
							if ($nextval) {
								if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured')
									$nextval = $langs->trans($nextval);
								$htmltooltip .= $nextval . '<br>';
							} else {
								$htmltooltip .= $langs->trans($module->error) . '<br>';
							}
						}

						print '<td align="center">';
						print $form->textwithpicto('', $htmltooltip, 1, 0);
						print '</td>';

						print "</tr>\n";
					}
				}
			}
			closedir($handle);
		}
	}
}
print "</table><br>\n";

/*
 *  Documents models for ultimateimmo
 */

print load_fiche_titre($langs->trans("TemplatePDFUltimateimmo"), '', '');

// Defini tableau def des modeles
$type = 'ultimateimmo';
$def = array();

$sql = "SELECT nom";
$sql .= " FROM " . MAIN_DB_PREFIX . "document_model";
$sql .= " WHERE type = '" . $type . "'";
$sql .= " AND entity = " . $conf->entity;

$resql = $db->query($sql);
if ($resql) {
	$i = 0;
	$num_rows = $db->num_rows($resql);
	while ($i < $num_rows) {
		$array = $db->fetch_array($resql);
		array_push($def, $array[0]);
		$i++;
	}
} else {
	dol_print_error($db);
}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Name") . '</td>';
print '<td>' . $langs->trans("Description") . '</td>';
print '<td align="center" width="60">' . $langs->trans("Status") . "</td>\n";
print '<td align="center" width="60">' . $langs->trans("Default") . "</td>\n";
print '<td align="center" width="80">' . $langs->trans("ShortInfo") . '</td>';
print '<td align="center" width="80">' . $langs->trans("Preview") . '</td>';
print "</tr>\n";

clearstatcache();

foreach ($dirmodels as $reldir) {
	foreach (array('', '/doc') as $valdir) {
		$dir = dol_buildpath($reldir . "ultimateimmo/core/modules/ultimateimmo" . $valdir);

		if (is_dir($dir)) {
			$handle = opendir($dir);
			if (is_resource($handle)) {
				while (($file = readdir($handle)) !== false) {
					$filelist[] = $file;
				}
				closedir($handle);
				arsort($filelist);

				foreach ($filelist as $file) {
					if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file)) {

						if (file_exists($dir . '/' . $file)) {
							$name = substr($file, 4, dol_strlen($file) - 16);
							$classname = substr($file, 0, dol_strlen($file) - 12);

							require_once $dir . '/' . $file;
							$module = new $classname($db);

							$modulequalified = 1;
							if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) $modulequalified = 0;
							if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modulequalified = 0;

							if ($modulequalified) {
								print '<tr class="oddeven"><td width="100">';
								print(empty($module->name) ? $name : $module->name);
								print "</td><td>\n";
								if (method_exists($module, 'info')) print $module->info($langs);
								else print $module->description;
								print '</td>';

								// Active
								if (in_array($name, $def)) {
									print "<td align=\"center\">\n";
									print '<a href="' . $_SERVER["PHP_SELF"] . '?action=del&amp;value=' . $name . '">';
									print img_picto($langs->trans("Enabled"), 'switch_on');
									print '</a>';
									print "</td>";
								} else {
									print "<td align=\"center\">\n";
									print '<a href="' . $_SERVER["PHP_SELF"] . '?action=set&amp;value=' . $name . '&amp;scan_dir=' . $module->scandir . '&amp;label=' . urlencode($module->name) . '&token='.newToken().'">' . img_picto($langs->trans("Disabled"), 'switch_off') . '</a>';
									print "</td>";
								}

								// Default
								print "<td align=\"center\">";
								if ($conf->global->ULTIMATEIMMO_ADDON_PDF == $name) {
									print img_picto($langs->trans("Default"), 'on');
								} else {
									print '<a href="' . $_SERVER["PHP_SELF"] . '?action=setdoc&amp;value=' . $name . '&amp;scan_dir=' . $module->scandir . '&amp;label=' . urlencode($module->name) . '&token='.newToken().'" alt="' . $langs->trans("Default") . '">' . img_picto($langs->trans("Disabled"), 'off') . '</a>';
								}
								print '</td>';

								// Info
								$htmltooltip =    '' . $langs->trans("Name") . ': ' . $module->name;
								$htmltooltip .= '<br>' . $langs->trans("Type") . ': ' . ($module->type ? $module->type : $langs->trans("Unknown"));
								$htmltooltip .= '<br>' . $langs->trans("Width") . '/' . $langs->trans("Height") . ': ' . $module->page_largeur . '/' . $module->page_hauteur;
								$htmltooltip .= '<br><br><u>' . $langs->trans("FeaturesSupported") . ':</u>';
								$htmltooltip .= '<br>' . $langs->trans("Logo") . ': ' . yn($module->option_logo, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("PaymentMode") . ': ' . yn($module->option_modereg, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("PaymentConditions") . ': ' . yn($module->option_condreg, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("MultiLanguage") . ': ' . yn($module->option_multilang, 1, 1);
								$htmltooltip .= '<br>' . $langs->trans("WatermarkOnDraftOrders") . ': ' . yn($module->option_draft_watermark, 1, 1);

								print '<td align="center">';
								print $form->textwithpicto('', $htmltooltip, -1, 0);
								print '</td>';

								// Preview
								print '<td align="center">';
								if ($module->type == 'pdf') {
									print '<a href="' . $_SERVER["PHP_SELF"] . '?action=specimen&module=' . $name . '&token='.newToken().'">' . img_object($langs->trans("Preview"), 'intervention') . '</a>';
								} else {
									print img_object($langs->trans("PreviewNotAvailable"), 'generic');
								}
								print '</td>';

								print "</tr>\n";
							}
						}
					}
				}
			}
		}
	}
}

print '</table>';

print '<br>';

/*
 * Other options
 *
 */
print load_fiche_titre($langs->trans("OtherOptions"), '', '');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="set_param">';

print "<table class=\"noborder\" width=\"100%\">";
print "<tr class=\"liste_titre\">";
print "<td>" . $langs->trans("Parameter") . "</td>\n";
print "</tr>";

$substitutionarray = pdf_getSubstitutionArray($langs, null, null, 2);
$substitutionarray['__(AnyTranslationKey)__'] = $langs->trans("Translation");
$htmltext = '<i>' . $langs->trans("AvailableVariables") . ':<br>';
foreach ($substitutionarray as $key => $val)	$htmltext .= $key . '<br>';
$htmltext .= '</i>';

print '<tr><td>';
print $form->textwithpicto($langs->trans("FreeLegalTextOnReceipts"), $langs->trans("AddCRIfTooLong") . '<br><br>' . $htmltext, 1, 'help', '', 0, 2, 'freetexttooltip') . '<br>';
$variablename = 'ULTIMATEIMMO_FREE_TEXT';
if (empty($conf->global->PDF_ALLOW_HTML_FOR_FREE_TEXT)) {
	print '<textarea name="' . $variablename . '" class="flat" cols="120">' . $conf->global->$variablename . '</textarea>';
} else {
	include_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
	$doleditor = new DolEditor($variablename, $conf->global->$variablename, '', 80, 'dolibarr_notes');
	print $doleditor->Create();
}
print "</td></tr>\n";

print '<tr><td>';
print $form->textwithpicto($langs->trans("WatermarkOnDraftImmoCards"), $htmltext, 1, 'help', '', 0, 2, 'watermarktooltip') . '<br>';
print '<input size="50" class="flat" type="text" name="ULTIMATEIMMO_DRAFT_WATERMARK" value="' . $conf->global->ULTIMATEIMMO_DRAFT_WATERMARK . '">';
print "</td></tr>\n";

print '<tr><td>';
print $form->textwithpicto($langs->trans("TypeCostForAdjust"), $langs->trans("") . '<br><br>' . $htmltext, 1, 'help', '', 0, 2, 'freetexttooltip') . '<br>';
$variablename = 'ULTIMATEIMMO_TYPECOST_ADJUST';
dol_include_once('/ultimateimmo/class/immocost_type.class.php');
$immocosttype=new ImmoCost_Type($db);
$dataArray=array();
$resultImmoCostType=$immocosttype->fetchAll('','',0,0,array('t.status'=>1));
if (!is_array($resultImmoCostType) && $resultImmoCostType<0) {
	setEventMessages($immocosttype->error, $immocosttype->errors, 'errors');
} elseif(is_array($resultImmoCostType) && count($resultImmoCostType)>0) {
	foreach ($resultImmoCostType as $costType) {
		$dataArray[$costType->id]=$costType->label;
	}
}
if (!empty($dataArray)) {
	if (empty($conf->global->{$variablename})) {
		$selectedArray=array();
	} else {
		$selectedArray=explode(',', $conf->global->{$variablename});
	}
	print $form::multiselectarray($variablename,$dataArray,$selectedArray);
}

print "</td></tr>\n";

print '</table>';

print '<div class="center"><input type="submit" class="button" value="' . $langs->trans("Modify") . '"></div>';

print '</form>';

/*
 * Bail_libre options
 *
 */

print load_fiche_titre($langs->trans("OtherOptions"));

// Addresses
print load_fiche_titre($langs->trans("- le cas échéant, représenté par le mandataire"), '', '') . '<br>';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameters") . '</td>' . "\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">' . $langs->trans("Value") . '</td>' . "\n";
print '</tr>';

// add also details for contact address.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("ShowMandataireDetails") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('ULTIMATEIMMO_MANDATAIRE_DETAILS');
} else {
	if ($conf->global->ULTIMATEIMMO_MANDATAIRE_DETAILS == 0) {
		print '<a href="' . $_SERVER['PHP_SELF'] . '?action=set_ULTIMATEIMMO_MANDATAIRE_DETAILS&token='.newToken().'">' . img_picto($langs->trans("Disabled"), 'off') . '</a>';
	} else if ($conf->global->ULTIMATEIMMO_MANDATAIRE_DETAILS == 1) {
		print '<a href="' . $_SERVER['PHP_SELF'] . '?action=del_ULTIMATEIMMO_MANDATAIRE_DETAILS&token='.newToken().'">' . img_picto($langs->trans("Enabled"), 'on') . '</a>';
	}
}
print '</td></tr>';

// add also details for contact address.
print '<tr class="oddeven">';
print '<td>' . $langs->trans("ShowColocataireDetails") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax) {
	print ajax_constantonoff('ULTIMATEIMMO_COLOCATAIRE_DETAILS');
} else {
	if ($conf->global->ULTIMATEIMMO_COLOCATAIRE_DETAILS == 0) {
		print '<a href="' . $_SERVER['PHP_SELF'] . '?action=set_ULTIMATEIMMO_COLOCATAIRE_DETAILS&token='.newToken().'">' . img_picto($langs->trans("Disabled"), 'off') . '</a>';
	} else if ($conf->global->ULTIMATEIMMO_COLOCATAIRE_DETAILS == 1) {
		print '<a href="' . $_SERVER['PHP_SELF'] . '?action=del_ULTIMATEIMMO_COLOCATAIRE_DETAILS&token='.newToken().'">' . img_picto($langs->trans("Enabled"), 'on') . '</a>';
	}
}
print '</td></tr>';
print '</table>';

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
