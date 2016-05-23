<?php
/* Copyright (C) 2015    Alexandre Spangaro  <aspangaro.dolibarr@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    /immobilier/admin/about.php
 * \ingroup immobilier
 * \brief   about immobilier module page
 */

// Dolibarr environment
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once ('../core/lib/immobilier.lib.php');
require_once '../includes/php_markdown/markdown.php';

// Translations
$langs->load("immobilier@immobilier");


// Access control
if (! $user->admin)
	accessforbidden();
	
// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
*/

/*
 * View
*/
llxHeader('', $langs->trans('ImmobilierSetup'));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans('ImmobilierSetup'), $linkback);

// Configuration header
$head = immobilier_admin_prepare_head();
dol_fiche_head($head, 'about', $langs->trans("Module113050Name"), 0, "building@immobilier");

// About page goes here
print 'Version : ' . $conf->global->IMMOBILIER_LAST_VERION_INSTALL;
print '<BR><a href="' . dol_buildpath('/immobilier/ChangeLog', 1) . '">Change Log</a>';

print '<BR><BR><BR><BR>--------------------------------';
print '<BR><a href="http://www.adefinir.com" target="_blanck">Lien Documentation Utilisateur Français</a>';
print '<BR>--------------------------------';


$buffer .= file_get_contents(dol_buildpath('/immobilier/README-FR', 0));
$buffer .= "\n------------------------------------------\n";
$buffer .= file_get_contents(dol_buildpath('/immobilier/README-EN', 0));
print Markdown($buffer);

print '<BR>';

print '<a href="' . dol_buildpath('/immobilier/LICENCE', 1) . '">License GPL</a>';

llxFooter();

$db->close();
