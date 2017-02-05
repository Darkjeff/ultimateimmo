<?php
/* Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
 * Copyright (C) 2015-2016	Alexandre Spangaro 	<aspangaro@zendsi.com>
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
 * \file	immobilier/renter/list.php
 * \ingroup immobilier
 * \brief 	list of renter
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once '../class/immorenter.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

$langs->load('immobilier@immobilier');

// Security check
if (! $user->rights->immobilier->renter->read)
	accessforbidden();

llxHeader('', $langs->trans("RentersList"));

$sortorder = GETPOST('sortorder', 'alpha');
$sortfield = GETPOST('sortfield', 'alpha');
$page = GETPOST('page', 'alpha');

// Search criteria
$search_name = GETPOST("search_name");
$search_firstname = GETPOST("search_firstname");
$search_civ = GETPOST("search_civ");
$search_soc = GETPOST("search_soc");
$search_tel = GETPOST("search_tel");
$search_mail = GETPOST("search_mail");
$search_owner_name = GETPOST("search_owner_name");
$search_namefirstname = GETPOST("search_namefirstname");

// Do we click on purge search criteria ?
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter"))
{
	$search_name = '';
	$search_firstname = '';
	$search_civ = '';
	$search_soc = '';
	$search_tel = '';
	$search_mail = '';
	$search_owner_name = '';
}

$filter = array ();
if (! empty($search_name)) {
	$filter ['s.nom'] = $search_name;
}
if (! empty($search_firstname)) {
	$filter ['s.prenom'] = $search_firstname;
}
if (! empty($search_civ)) {
	$filter ['civ.code'] = $search_civ;
}
if (! empty($search_soc)) {
	$filter ['so.nom'] = $search_soc;
}
if (! empty($search_owner_name)) {
	$filter ['soc.nom'] = $search_owner_name;
}
if (! empty($search_tel)) {
	$filter ['s.tel1'] = $search_tel;
}
if (! empty($search_mail)) {
	$filter ['s.mail'] = $search_mail;
}
if (! empty($search_namefirstname)) {
	$filter ['naturalsearch'] = $search_namefirstname;
}

if (! $sortorder)
	$sortorder = "DESC";
if (! $sortfield)
	$sortfield = "s.rowid";

if ($page == - 1) {
	$page = 0;
}

$limit = $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$formcompagny = new FormCompany($db);

$renter = new Renter($db);
$thirdparty_static = new Societe($db);

// Count total nb of records
$nbtotalofrecords = 0;

if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$nbtotalofrecords = $renter->fetch_all($sortorder, $sortfield, 0, 0, $filter);
}

$result = $renter->fetch_all($sortorder, $sortfield, $limit, $offset, $filter);

if ($result >= 0) {
	$option='';
	if (! empty($search_name))
		$option .= '&search_name=' . $search_name;
	if (! empty($search_firstname))
		$option .= '&search_firstname=' . $search_name;
	if (! empty($search_civ))
		$option .= '&search_civ=' . $search_civ;
	if (! empty($search_soc))
		$option .= '&search_soc=' . $search_soc;
	if (! empty($search_tel))
		$option .= '&search_tel=' . $search_tel;
	if (! empty($search_mail))
		$option .= '&search_mail=' . $search_mail;
	
	print_barre_liste($langs->trans("RentersList"), $page, $_SERVER ['PHP_SELF'], $option, $sortfield, $sortorder, '', $result, $nbtotalofrecords);
	
	print '<form method="get" action="' . $_SERVER ['PHP_SELF'] . '" name="search_form">' . "\n";
	if (! empty($sortfield))
		print '<input type="hidden" name="sortfield" value="' . $sortfield . '"/>';
	if (! empty($sortorder))
		print '<input type="hidden" name="sortorder" value="' . $sortorder . '"/>';
	if (! empty($page))
		print '<input type="hidden" name="page" value="' . $page . '"/>';
	
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	/*print_liste_field_titre($langs->trans("Id"), $_SERVER ['PHP_SELF'], "s.rowid", "", $option, '', $sortfield, $sortorder);*/
	print_liste_field_titre($langs->trans("NomPrenom"), $_SERVER ['PHP_SELF'], "s.nom", "", $option, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Civility"), $_SERVER ['PHP_SELF'], "civ.code", "", $option, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Company"), $_SERVER ['PHP_SELF'], "so.nom", "", $option, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Phone"), $_SERVER ['PHP_SELF'], "s.tel1", "", $option, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Email"), $_SERVER ['PHP_SELF'], "s.mail", "", $option, '', $sortfield, $sortorder);	
	print_liste_field_titre($langs->trans("Owner"), $_SERVER["PHP_SELF"], "soc.nom", "", $param, '', $sortfield, $sortorder);
	print_liste_field_titre('');
	print "</tr>\n";
	

	print '<tr class="liste_titre">';
	
	//print '<td>&nbsp;</td>';
	
	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_name" value="' . $search_name . '" size="8">';
	print '<input type="text" class="flat" name="search_firstname" value="' . $search_firstname . '" size="8">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print $formcompagny->select_civility($search_civ, 'search_civ');
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_soc" value="' . $search_soc . '" size="20">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_tel" value="' . $search_tel . '" size="10">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_mail" value="' . $search_mail . '" size="20">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="7" type="text" name="search_owner_name" value="' . $search_owner_name . '">';
	print '</td>';
	
	print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
	print '&nbsp; ';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/searchclear.png" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
	print '</td>';
	
	print "</tr>\n";
	
	$var = true;
	foreach ( $renter->lines as $line ) {
		
		// Affichage liste des locataires
		$var = ! $var;
		print "<tr $bc[$var]>";
		/*print '<td><a href="card.php?id=' . $line->rowid . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . $line->rowid . '</a></td>';*/
		print '<td><a href="card.php?id=' . $line->rowid . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($line->nom) . ' ' . ucfirst($line->prenom) . '</a></td>';
		
		$contact_static = new Contact($db);
		$contact_static->civility_id = $line->civilite;
		
		print '<td>' . $contact_static->getCivilityLabel() . '</td>';
		print '<td>';
		if ($line->socid) {
			print '<a href="' . dol_buildpath('/comm/card.php', 1) . '?socid=' . $line->socid . '">';
			print img_object($langs->trans("ShowCompany"), "company") . ' ' . dol_trunc($line->socname, 20) . '</a>';
		} else {
			print '&nbsp;';
		}
		print '</td>';
		print '<td>' . dol_print_phone($line->tel1) . '</td>';
		print '<td>' . dol_print_email($line->mail, $line->rowid, $line->socid, 'AC_EMAIL', 25) . '</td>';
		//echo $line->owner_name;
		$thirdparty_static->id=$line->fk_owner;
		$thirdparty_static->name=$line->owner_name;
		print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
		print '<td align="center">';
		if ($user->admin) {
			print '<a href="./list.php?action=delete&id=' . $line->id . '">';
			print img_delete();
			print '</a>';
		}
		print '</td>' . "\n";
		print '<td>&nbsp;</td>';
		print "</tr>\n";
	}
	
	print "</table>";
	print '</form>';
} else {
	setEventMessage($renter->error, 'errors');
}

llxFooter();
$db->close();