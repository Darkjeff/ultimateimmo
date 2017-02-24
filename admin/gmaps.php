<?php
/* Copyright (C) 2008-2011 	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2016		Alexandre Spangaro	<aspangaro@zendsi.com>
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
 *     	\file       htdocs/custom/immobilier/admin/gmaps.php
 *		\ingroup    immobilier
 *		\brief      File of main public page for immobilier module
 */

define('NOCSRFCHECK',1);

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php');
require_once ('../core/lib/immobilier.lib.php');

if (!$user->admin)
    accessforbidden();

$langs->load("immobilier@immobilier");
$langs->load("admin");
$langs->load("other");

$def = array();
$action = GETPOST('action','alpha');
$actiontest=$_POST["test"];
$actionsave=$_POST["save"];

/*
 * Actions
 */

if ($actionsave)
{
    $db->begin();

	$res=0;
    $res+=dolibarr_set_const($db,'GOOGLE_GMAPS_ZOOM_LEVEL',trim($_POST["GOOGLE_GMAPS_ZOOM_LEVEL"]),'chaine',0,'',$conf->entity);
	$res+=dolibarr_set_const($db,'GOOGLE_API_SERVERKEY',trim($_POST["GOOGLE_API_SERVERKEY"]),'chaine',0,'',$conf->entity);

    if ($res == 2)
    {
        $db->commit();
        $mesg = "<font class=\"ok\">".$langs->trans("SetupSaved")."</font>";
    }
    else
    {
        $db->rollback();
        $mesg = "<font class=\"error\">".$langs->trans("Error")."</font>";
    }
}

if ($action == 'setimmobiliergoogle')
{
	$setimmobiliergoogle = GETPOST('value', 'int');
    $res = dolibarr_set_const($db, "IMMOBILIER_USE_GOOGLE", $setimmobiliergoogle, 'yesno', 0, '', $conf->entity);
    if (! $res > 0)
        $error ++;

        if (! $error) {
            setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
        } else {
            setEventMessages($langs->trans("Error"), null, 'mesgs');
        }
}

/*
 * View
 */

$form=new Form($db);
$formadmin=new FormAdmin($db);
$formother=new FormOther($db);

llxHeader('',$langs->trans("ImmobilierSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ImmobilierSetup"),$linkback,'setup');

$head = immobilier_admin_prepare_head();
dol_fiche_head($head, 'gmaps', $langs->trans("Module113050Name"), 0, 'building@immobilier');

print '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="update">';

$var=false;
print "<table class=\"noborder\" width=\"100%\">";

print "<tr class=\"liste_titre\">";
print '<td>'.$langs->trans("Parameters")."</td>";
print "<td>".$langs->trans("Value")."</td>";
print "</tr>";

$var = ! $var;
print "<tr " . $bc[$var] . ">";
print '<td>' . $langs->trans("IMMOBILIER_USE_GOOGLE") . '</td>';
if (! empty($conf->global->IMMOBILIER_USE_GOOGLE)) {
    print '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=setimmobiliergoogle&value=0">';
    print img_picto($langs->trans("Activated"), 'switch_on');
    print '</a></td>';
} else {
    print '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=setimmobiliergoogle&value=1">';
    print img_picto($langs->trans("Disabled"), 'switch_off');
    print '</a></td>';
}
print '</tr>';

//print '<br>';
print '<tr '.$bc[$var].'><td>'.$langs->trans("GoogleZoomLevel").'</td><td>';
print '<input class="flat" name="GOOGLE_GMAPS_ZOOM_LEVEL" id="GOOGLE_GMAPS_ZOOM_LEVEL" value="'.(isset($_POST["GOOGLE_GMAPS_ZOOM_LEVEL"])?$_POST["GOOGLE_GMAPS_ZOOM_LEVEL"]:$conf->global->GOOGLE_GMAPS_ZOOM_LEVEL).'" size="2">';
print '</td></tr>';

print '</table>';

print '<br>';

print "<table class=\"noborder\" width=\"100%\">";

print "<tr class=\"liste_titre\">";
print '<td>'.$langs->trans("Parameter").' ('.$langs->trans("ParametersForGoogleAPIv3Usage","Geocoding").')'."</td>";
print "<td>".$langs->trans("Value")."</td>";
print "<td>".$langs->trans("Note")."</td>";
print "</tr>";
// Google login
print "<tr ".$bc[$var].">";
print '<td>'.$langs->trans("GOOGLE_API_SERVERKEY")."</td>";
print "<td>";
print '<input class="flat" type="text" size="64" name="GOOGLE_API_SERVERKEY" value="'.$conf->global->GOOGLE_API_SERVERKEY.'">';
print '</td>';
print '<td>';
print $langs->trans("KeepEmptyYoUsePublicQuotaOfAPI","Geocoding API").'<br>';
print $langs->trans("AllowGoogleToLoginWithKey","https://code.google.com/apis/console/","https://code.google.com/apis/console/").'<br>';
print "</td>";
print "</tr>";

print '</table>';

print info_admin($langs->trans("EnableAPI","https://code.google.com/apis/console/","https://code.google.com/apis/console/","Geocoding API"));

dol_fiche_end();

print '<div align="center">';
print "<input type=\"submit\" name=\"save\" class=\"button\" value=\"".$langs->trans("Save")."\">";
print "</div>";

print "</form>\n";


dol_htmloutput_mesg($mesg);

// Show message
$message='';
//$urlgooglehelp='<a href="http://www.google.com/calendar/embed/EmbedHelper_en.html" target="_blank">http://www.google.com/calendar/embed/EmbedHelper_en.html</a>';
//$message.=$langs->trans("GoogleSetupHelp",$urlgooglehelp);
//print info_admin($message);

llxFooter();

$db->close();
