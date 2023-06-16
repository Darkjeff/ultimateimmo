<?php
/* Copyright (C) 2015	Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2019  Philippe GRAND  <philippe.grand@atoo-net.com>
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
 *     	\file       htdocs/custom/ultimateimmo/admin/public.php
 *		\ingroup    ultimateimmo
 *		\brief      File of main public page for ultimateimmo module
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once ('../lib/ultimateimmo.lib.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("ultimateimmo@ultimateimmo", "admin"));

// Parameters
$value = GETPOST('value', 'alpha');
$action = GETPOST('action', 'alpha');

if (! $user->admin) accessforbidden();


/*
 * Actions
 */

if ($action == 'setULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE')
{
    if (GETPOST('value')) dolibarr_set_const($db, 'ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE', 1, 'chaine', 0, '', $conf->entity);
    else dolibarr_set_const($db, 'ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE', 0, 'chaine', 0, '', $conf->entity);
}

if ($action == 'setvarother') {
    $param_enable_public_interface = GETPOST('ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE', 'alpha');
    $res = dolibarr_set_const($db, 'ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE', $param_enable_public_interface, 'chaine', 0, '', $conf->entity);
    if (!$res > 0) {
        $error++;
    }

	$param_must_exists = GETPOST('ULTIMATEIMMO_EMAIL_MUST_EXISTS', 'alpha');
    $res = dolibarr_set_const($db, 'ULTIMATEIMMO_EMAIL_MUST_EXISTS', $param_must_exists, 'chaine', 0, '', $conf->entity);
    if (!$res > 0) {
        $error++;
    }

	if ($conf->global->MAIN_FEATURES_LEVEL >= 2)
    {
    	$param_show_company_logo = GETPOST('ULTIMATEIMMO_SHOW_COMPANY_LOGO', 'alpha');
    	$res = dolibarr_set_const($db, 'ULTIMATEIMMO_SHOW_COMPANY_LOGO', $param_show_company_logo, 'chaine', 0, '', $conf->entity);
    	if (!$res > 0) {
        	$error++;
    	}
    }
}


/*
 * View
 */

$form=new Form($db);

$page_name = "UltimateimmoSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = ultimateimmoAdminPrepareHead();
dol_fiche_head($head, 'public', $langs->trans("ModuleUltimateimmoName"), -1, 'building@ultimateimmo');

print '<span class="opacitymedium">'.$langs->trans("UltimateimmoEnablePublicInterface") . '</span> : <a href="' . dol_buildpath('/ultimateimmo/public/index.php', 1) . '" target="_blank" >' . dol_buildpath('/ultimateimmo/public/index.php', 2) . '</a>';
print '<br><br>';
print $langs->trans("PublicSiteDesc").'<br><br>';

dol_fiche_end();

$enabledisablehtml = $langs->trans("UltimateimmoPublicAccess").' ';
if (empty($conf->global->ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE))
{
    // Button off, click to enable
    $enabledisablehtml.='<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?action=setULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE&value=1&token='.newToken().'">';
    $enabledisablehtml.=img_picto($langs->trans("Disabled"), 'switch_off');
    $enabledisablehtml.='</a>';
}
else
{
    // Button on, click to disable
    $enabledisablehtml.='<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?action=setULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE&value=0&token='.newToken().'">';
    $enabledisablehtml.=img_picto($langs->trans("Activated"), 'switch_on');
    $enabledisablehtml.='</a>';
}
print $enabledisablehtml;
print '<input type="hidden" id="ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE" name="ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE" value="'.(empty($conf->global->ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE)?0:1).'">';

print '<br><br>';

if (! empty($conf->global->ULTIMATEIMMO_ENABLE_PUBLIC_INTERFACE))
{

    if (!$conf->use_javascript_ajax) {
        print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" enctype="multipart/form-data" >';
        print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
        print '<input type="hidden" name="action" value="setvarother">';
    }

    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>' . $langs->trans("Parameters") . '</td>';
    print '<td class="left">';
    print '</td>';
    print '<td class="center">';
    print '</td>';
    print '</tr>';

	 // Check if email exists
    /*print '<tr class="oddeven"><td>' . $langs->trans("UltimateimmoEmailMustExist") . '</td>';
    print '<td class="left">';
    if ($conf->use_javascript_ajax) {
        print ajax_constantonoff('ULTIMATEIMMO_EMAIL_MUST_EXISTS');
    } else {
        $arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
        print $form->selectarray("ULTIMATEIMMO_EMAIL_MUST_EXISTS", $arrval, $conf->global->ULTIMATEIMMO_EMAIL_MUST_EXISTS);
    }
    print '</td>';
    print '<td align="center">';
    print $form->textwithpicto('', $langs->trans("UltimateimmoEmailMustExistHelp"), 1, 'help');
    print '</td>';
    print '</tr>';

	// Show logo for company
    print '<tr class="oddeven"><td>' . $langs->trans("UltimateimmoShowCompanyLogo") . '</td>';
    print '<td class="left">';
    if ($conf->use_javascript_ajax) {
    	print ajax_constantonoff('ULTIMATEIMMO_SHOW_COMPANY_LOGO');
    } else {
    	$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
    	print $form->selectarray("ULTIMATEIMMO_SHOW_COMPANY_LOGO", $arrval, $conf->global->ULTIMATEIMMO_SHOW_COMPANY_LOGO);
    }
    print '</td>';
    print '<td align="center">';
    print $form->textwithpicto('', $langs->trans("UltimateimmoShowCompanyLogoHelp"), 1, 'help');
    print '</td>';
    print '</tr>';

	 if (!$conf->use_javascript_ajax) {
        print '<tr class="impair"><td colspan="3" align="center"><input type="submit" class="button" value="' . $langs->trans("Save") . '"></td>';
        print '</tr>';
    }*/

    print '</table><br>';

    if (!$conf->use_javascript_ajax) {
        print '</form>';
    }
}


llxFooter();

$db->close();
