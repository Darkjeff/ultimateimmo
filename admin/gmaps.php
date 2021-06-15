<?php
/* Copyright (C) 2008-2011 	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2016		Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2021  Philippe GRAND  	<philippe.grand@atoo-net.com>
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
 *     	\file       htdocs/custom/ultimateimmo/admin/gmaps.php
 *		\ingroup    ultimateimmo
 *		\brief      File of main public page for ultimateimmo module
 */

define('NOCSRFCHECK',1);

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

require_once(DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT . '/core/class/html.formadmin.class.php');
require_once(DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php');
require_once '../lib/ultimateimmo.lib.php';

if (!$user->admin)
    accessforbidden();

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "admin", "other"));

$def = array();
$action = GETPOST('action', 'alpha');
$actiontest = GETPOST('test', 'alpha');
$actionsave = GETPOST('save', 'alpha');

/*
 * Actions
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

if ($actionsave) {
    $db->begin();

    $res = 0;
    $res += dolibarr_set_const($db, 'GOOGLE_GMAPS_ZOOM_LEVEL', trim($_POST["GOOGLE_GMAPS_ZOOM_LEVEL"]), 'chaine', 0, '', $conf->entity);
    $res += dolibarr_set_const($db, 'GOOGLE_API_SERVERKEY', trim($_POST["GOOGLE_API_SERVERKEY"]), 'chaine', 0, '', $conf->entity);

    if ($res == 2) {
        $db->commit();
        $mesg = "<font class=\"ok\">" . $langs->trans("SetupSaved") . "</font>";
    } else {
        $db->rollback();
        $mesg = "<font class=\"error\">" . $langs->trans("Error") . "</font>";
    }
}


/*
 * View
 */

$form = new Form($db);
$formadmin = new FormAdmin($db);
$formother = new FormOther($db);

$page_name = "UltimateimmoSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = ultimateimmoAdminPrepareHead();
dol_fiche_head($head, 'gmaps', $langs->trans("ModuleUltimateimmoName"), -1, 'building@ultimateimmo');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameters") . '</td>' . "\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">' . $langs->trans("Value") . '</td>' . "\n";
print '</tr>';

print '<tr class="oddeven">';
print '<td>' . $langs->trans("ULTIMATEIMMO_USE_GOOGLE") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax) {
    print ajax_constantonoff('ULTIMATEIMMO_USE_GOOGLE');
} else {
    if ($conf->global->ULTIMATEIMMO_USE_GOOGLE == 0) {
        print '<a href="' . $_SERVER['PHP_SELF'] . '?action=set_ULTIMATEIMMO_USE_GOOGLE">' . img_picto($langs->trans("Disabled"), 'off') . '</a>';
    } else if ($conf->global->ULTIMATEIMMO_USE_GOOGLE == 1) {
        print '<a href="' . $_SERVER['PHP_SELF'] . '?action=del_ULTIMATEIMMO_USE_GOOGLE">' . img_picto($langs->trans("Enabled"), 'on') . '</a>';
    }
}
print '</td></tr>';

print '<tr class="oddeven">';
print '<td>' . $langs->trans("GoogleZoomLevel") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">';
print '<input class="flat" name="GOOGLE_GMAPS_ZOOM_LEVEL" id="GOOGLE_GMAPS_ZOOM_LEVEL" value="' . (isset($_POST["GOOGLE_GMAPS_ZOOM_LEVEL"]) ? $_POST["GOOGLE_GMAPS_ZOOM_LEVEL"] : $conf->global->GOOGLE_GMAPS_ZOOM_LEVEL) . '" size="2">';
print '</td></tr>';

print '</table>';

print '<br>';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameter") . ' (' . $langs->trans("ParametersForGoogleAPIv3Usage", "Geocoding") . ')' . "</td>";
print "<td>" . $langs->trans("Value") . "</td>";
print "<td>" . $langs->trans("Note") . "</td>";
print "</tr>";
// Google login
print '<tr class="oddeven">';
print '<td>' . $langs->trans("GOOGLE_API_SERVERKEY") . "</td>";
print "<td>";
print '<input class="flat" type="text" size="64" name="GOOGLE_API_SERVERKEY" value="' . $conf->global->GOOGLE_API_SERVERKEY . '">';
print '</td>';
print '<td>';
print $langs->trans("KeepEmptyYoUsePublicQuotaOfAPI", "Geocoding API") . '<br>';
print $langs->trans("AllowGoogleToLoginWithKey", "https://code.google.com/apis/console/", "https://code.google.com/apis/console/") . '<br>';
print "</td>";
print "</tr>";

print '</table>';

print info_admin($langs->trans("EnableAPI", "https://code.google.com/apis/console/", "https://code.google.com/apis/console/", "Geocoding API"));

dol_fiche_end();

print '<div align="center">';
print "<input type=\"submit\" name=\"save\" class=\"button\" value=\"" . $langs->trans("Save") . "\">";
print "</div>";

print "</form>\n";


dol_htmloutput_mesg($mesg);

// Show message
$message = '';
//$urlgooglehelp='<a href="http://www.google.com/calendar/embed/EmbedHelper_en.html" target="_blank">http://www.google.com/calendar/embed/EmbedHelper_en.html</a>';
//$message.=$langs->trans("GoogleSetupHelp",$urlgooglehelp);
//print info_admin($message);

llxFooter();

$db->close();
