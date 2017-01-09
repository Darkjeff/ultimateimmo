<?php
/* Copyright (C) 2015-2016 Alexandre Spangaro  <aspangaro.dolibarr@gmail.com>
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
print 'Version : ' . $conf->global->IMMOBILIER_LAST_VERSION_INSTALL;

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre"><td colspan="2">' . $langs->trans("Authors") . '</td>';
print '</tr>';

// Olivier Geoffroy
print '<tr><td style="text-align:center"><img src="../img/jeffinfo.png" width="250"></td>';
print '<td><b>Olivier Geffroy</b>&nbsp;-&nbsp;Consultant Informatique';
print '<br>' . $langs->trans("Email") . ' : jeff@jeffinfo.com <br>' . $langs->trans("Phone") . ' : +33 6 08 63 27 40';
print '<br><a target="_blank" href="http://jeffinfo.com">http://www.jeffinfo.com/</a>';
print '<br><a title="Jeffinfo Facebook" target="_blank" href="https://www.facebook.com/pages/Jeffinfo/165397806821029"><img src="../img/fb.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="http://twiter.com/zendsi"><img src="../img/tweet.png" width="20"></a>&nbsp;';
print '<a target="_blank" href="https://plus.google.com/+OlivierGeffroy/posts"><img src="../img/google+.png" width="20"></a>';
print '</td></tr>';

// Alexandre Spangaro
print '<tr><td width="25%" style="text-align:center"><img src="../img/asilib.png"></td>';
print '<td><b>Alexandre Spangaro</b>&nbsp;-&nbsp;Développeur';
print '<br>Asilib - Votre gestion informatique en toute liberté !<br>' . $langs->trans("Email") . ' : aspangaro.dolibarr@gmail.com';
print '<br><a target="_blank" title="Twitter" alt="Twitter" href="http://twitter.com/alexspangaro"><img src="../img/tweet.png" width="20"></a>&nbsp;<a target="_blank" title="Linkedin" alt="Linkedin" href="https://fr.linkedin.com/in/aspangaro"><img src="../img/link.png" width="20"></a>';
print '<br>&nbsp;';
print '</td></tr>';

print '</table>';

print '<BR>';

$buffer = file_get_contents('../ChangeLog');
echo Markdown($buffer);

print '<BR>';
print '<BR>';
print '<a href="' . '../LICENCE' . '">License GPL</a>';

llxFooter();

$db->close();
