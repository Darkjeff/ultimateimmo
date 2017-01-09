<?php
/* Copyright (C) 2015	Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
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
 *     	\file       htdocs/custom/immobilier/admin/public.php
 *		\ingroup    immobilier
 *		\brief      File of main public page for immobilier module
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once ('../core/lib/immobilier.lib.php');

$langs->load("immobilier@immobilier");
$langs->load("members");
$langs->load("admin");

$action=GETPOST('action', 'alpha');

if (! $user->admin) accessforbidden();


/*
 * Actions
 */

if ($action == 'update')
{
	$public=GETPOST('IMMOBILIER_ENABLE_PUBLIC');

    $res=dolibarr_set_const($db, "IMMOBILIER_ENABLE_PUBLIC",$public,'chaine',0,'',$conf->entity);

    if (! $res > 0) $error++;

 	if (! $error)
    {
	    setEventMessage($langs->trans("SetupSaved"));
    }
    else
    {
	    setEventMessage($langs->trans("Error"), 'errors');
    }
}


/*
 * View
 */

$form=new Form($db);

llxHeader('',$langs->trans("ImmobilierSetup"));


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ImmobilierSetup"),$linkback,'setup');

$head = immobilier_admin_prepare_head();
dol_fiche_head($head, 'public', $langs->trans("Module113050Name"), 0, 'building@immobilier');

print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
print '<input type="hidden" name="action" value="update">';

print $langs->trans("PublicSiteDesc").'<br><br>';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td align="right">'.$langs->trans("Value").'</td>';
print "</tr>\n";
$var=true;

// Allow public
$var=! $var;
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<tr '.$bc[$var].'><td>';
print $langs->trans("EnablePublicSite");
print '</td><td align="right">';
print $form->selectyesno("IMMOBILIER_ENABLE_PUBLIC",(! empty($conf->global->IMMOBILIER_ENABLE_PUBLIC)?$conf->global->IMMOBILIER_ENABLE_PUBLIC:0),1);
print "</td></tr>\n";

print '</table>';

dol_fiche_end();

print '<center>';
print '<input type="submit" class="button" value="'.$langs->trans("Modify").'">';
print '</center>';

print '</form>';

print '<br>';

print img_picto('','object_globe.png').' '.$langs->trans('Website').':<br>';
if ($conf->multicompany->enabled) {
	$entity_qr='?entity='.$conf->entity;
} else {
	$entity_qr='';
}
print '<a target="_blank" href="'.DOL_URL_ROOT.'/public/members/new.php'.$entity_qr.'">'.DOL_MAIN_URL_ROOT.'/public/members/new.php'.$entity_qr.'</a>';


llxFooter();

$db->close();
