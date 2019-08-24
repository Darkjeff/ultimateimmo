<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2018-2019 	Philippe GRAND 	    <philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/property/equipement.php
 * \ingroup ultimateimmo
 * \brief   Equipement page
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

global $db, $langs, $user, $conf;

// Libraries
dol_include_once('/ultimateimmo/lib/immoproperty.lib.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/html.formultimateimmo.class.php');

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

/*
 * Action
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_set_const($db, $code, 1, 'chaine', 0, '', $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

if (preg_match('/del_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_del_const($db, $code, $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}


/*
 * View
 */

$html = new Form($db);
$htmlimmo = new FormUltimateimmo($db);
$object = new ImmoProperty($db);
$result = $object->fetch($id);

if ($result < 0) {
	setEventMessages(null, $object->errors, 'errors');
}

$page_name = $langs->trans("Property").'|'.$langs->trans("Equipement");
llxheader('', $langs->trans($page_name), '');

// Subheader
$linkback = '<a href="' .dol_buildpath('/ultimateimmo/property/immoproperty_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

// Configuration header
$head = immopropertyPrepareHead($object);
dol_fiche_head($head, 'equipement', $langs->trans("Property"), 0, 'building@ultimateimmo');

dol_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';
print '<table class="border centpercent">'."\n";

// Addresses area
print_fiche_titre($langs->trans("EnumerationOfPartsAndCommonEquipment"),'','').'<br>';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// UltimateImmoEquipementGardiennage.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementGardiennage").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_GARDIENNAGE');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_GARDIENNAGE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_GARDIENNAGE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_GARDIENNAGE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_GARDIENNAGE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';	

// UltimateImmoEquipementInterphone.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementInterphone").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_INTERPHONE');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_INTERPHONE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_INTERPHONE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_INTERPHONE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_INTERPHONE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// UltimateImmoEquipementAscenseur.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementAscenseur").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_ASCENSEUR');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ASCENSEUR == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_ASCENSEUR">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ASCENSEUR == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_ASCENSEUR">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// UltimateImmoEquipementVideOrdures.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementVideOrdures").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_VIDEORDURES');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_VIDEORDURES == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_VIDEORDURES">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_VIDEORDURES == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_VIDEORDURES">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// UltimateImmoEquipementAntenneTVcollective.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementAntenneTVcollective").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_ANTENNETVCOLLECTIVE');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ANTENNETVCOLLECTIVE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_ANTENNETVCOLLECTIVE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ANTENNETVCOLLECTIVE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_ANTENNETVCOLLECTIVE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// UltimateImmoEquipementEspacesverts.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementEspacesverts").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_ESPACESVERTS');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ESPACESVERTS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_ESPACESVERTS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ESPACESVERTS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_ESPACESVERTS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// UltimateImmoEquipementChauffageCollectif.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementChauffageCollectif").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_CHAUFFAGECOLLECTIF');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_CHAUFFAGECOLLECTIF == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_CHAUFFAGECOLLECTIF">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_CHAUFFAGECOLLECTIF == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_CHAUFFAGECOLLECTIF">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// UltimateImmoEquipementEauChaudeCollective.
print '<tr class="oddeven">';
print '<td>'.$langs->trans("UltimateImmoEquipementEauChaudeCollective").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_IMMO_EQUIPEMENT_EAUCHAUDECOLLECTIVE');
}
else
{
	if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_EAUCHAUDECOLLECTIVE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_IMMO_EQUIPEMENT_EAUCHAUDECOLLECTIVE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_IMMO_EQUIPEMENT_EAUCHAUDECOLLECTIVE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_IMMO_EQUIPEMENT_EAUCHAUDECOLLECTIVE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';


print '</table>';

// Footer
llxFooter();
// Close database handler
$db->close();
?>