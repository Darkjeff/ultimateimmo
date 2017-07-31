<?php
/*
 * Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012-2014  Florian Henry   <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file agefodd/contact/card.php
 * \ingroup agefodd
 * \brief list of contact
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

dol_include_once('/agefodd/class/agefodd_contact.class.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden();

llxHeader('', $langs->trans("AgfContact"));

$sortorder = GETPOST('sortorder', 'alpha');
$sortfield = GETPOST('sortfield', 'alpha');
$page = GETPOST('page', 'int');
$arch = GETPOST('arch', 'int');

if (empty($sortorder))
	$sortorder = "ASC";
if (empty($sortfield))
	$sortfield = "s.lastname, s.firstname";
if (empty($arch))
	$arch = 0;

if ($page == - 1) {
	$page = 0;
}

$limit = $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$agf = new Agefodd_contact($db);

// Count total nb of records
$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$nbtotalofrecords = $agf->fetch_all($sortorder, $sortfield, 0, 0, $arch, $filter);
}

$result = $agf->fetch_all($sortorder, $sortfield, $limit, $offset, $arch);
if ($result < 0) {
	setEventMessage($agf->errors, 'errors');
}

$linenum = count($agf->lines);

print_barre_liste($langs->trans("AgfContact"), $page, $_SERVER ['PHP_SELF'], "&arch=" . $arch, $sortfield, $sortorder, "", $linenum, $nbtotalofrecords);

print '<div width=100%" align="right">';
if ($arch == 2) {
	print '<a href="' . $_SERVER ['PHP_SELF'] . '?arch=0">' . $langs->trans("AgfCacherContactsArchives") . '</a>' . "\n";
} else {
	print '<a href="' . $_SERVER ['PHP_SELF'] . '?arch=2">' . $langs->trans("AgfAfficherContactsArchives") . '</a>' . "\n";
}
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print_liste_field_titre($langs->trans("Id"), $_SERVER ['PHP_SELF'], "s.rowid", '', "&arch=" . $arch, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Name"), $_SERVER ['PHP_SELF'], "s.lastname", "", "&arch=" . $arch, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Firstname"), $_SERVER ['PHP_SELF'], "s.firstname", "", "&arch=" . $arch, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("AgfCivilite"), "c_liste.php", "s.civility", "", "&arch=" . $arch, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Company"), $_SERVER ['PHP_SELF'], "soc.nom", "", "&arch=" . $arch, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Phone"), $_SERVER ['PHP_SELF'], "s.phone", "", "&arch=" . $arch, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("PhoneMobile"), $_SERVER ['PHP_SELF'], "s.phone", "", "&arch=" . $arch, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Mail"), $_SERVER ['PHP_SELF'], "s.email", "", "&arch=" . $arch, '', $sortfield, $sortorder);
print "</tr>\n";

if (! empty($linenum)) {
	$var = true;
	$i = 0;
	while ( $i < $linenum ) {
		// Affichage liste des formateurs
		$var = ! $var;
		($agf->lines [$i]->archive == 1) ? $style = ' style="color:gray;"' : $style = '';
		print "<tr $bc[$var]>";
		print '<td><span style="background-color:' . $bgcolor . ';"><a href="card.php?id=' . $agf->lines [$i]->id . '"' . $style . '>' . img_object($langs->trans("AgfEditerFicheFormateur"), "user") . ' ' . $agf->lines [$i]->id . '</a></span></td>';
		print '<td' . $style . '>' . $agf->lines [$i]->lastname . '</td>';
		print '<td' . $style . '>' . $agf->lines [$i]->firstname . '</td>';
		print '<td' . $style . '>' . $agf->lines [$i]->civilite . '</td>';
		print '<td' . $style . '>';
		if ($agf->lines [$i]->socid) {
			print '<a href="' . DOL_URL_ROOT . '/comm/card.php?socid=' . $agf->lines [$i]->socid . '">';
			print img_object($langs->trans("ShowCompany"), "company") . ' ' . dol_trunc($agf->lines [$i]->socname, 20) . '</a>';
		} else
			print '&nbsp;';
		print '</td>';
		print '<td' . $style . '>' . dol_print_phone($agf->lines [$i]->phone) . '</td>';
		print '<td' . $style . '>' . dol_print_phone($agf->lines [$i]->phone_mobile) . '</td>';
		print '<td' . $style . '>';
		if ($agf->lines [$i]->archive == 0)
			print dol_print_email($agf->lines [$i]->email, $agf->lines [$i]->spid, "", 'AC_EMAIL', 25);
		else
			print '<a href="mailto:' . $agf->lines [$i]->email . '"' . $style . '>' . $agf->lines [$i]->email . '</a>';
		print '</td>';
		print "</tr>\n";
		
		$i ++;
	}
}
print "</table>";

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit') {
	if ($user->rights->agefodd->creer) {
		print '<a class="butAction" href="card.php?action=create">' . $langs->trans('Create') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Create') . '</a>';
	}
}

print '</div>';
