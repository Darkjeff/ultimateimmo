<?php
/* Copyright (C) 2015-2016  Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
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
 * \file 		immobilier/cost/list.php
 * \ingroup 	Immobilier
 * \brief 		List of cost
 */

// Dolibarr environment
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
	// class
dol_include_once("/immobilier/class/immocost.class.php");



// Securite acces client
if ($user->societe_id > 0)
	accessforbidden();

$search_libelle = GETPOST('search_libelle');
$search_fournisseur = GETPOST('search_fournisseur');
$search_date = GETPOST('search_date');
$search_local = GETPOST('search_local');


if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) // Both test must be present to be compatible with all browsers
{
	$search_libelle = "";
	$search_fournisseur;
	$search_date = "";
	$search_local = "";
}

$filter = array ();
if (! empty($search_libelle)) {
	$filter['t.libelle'] = $search_libelle;
	$param .= "&amp;search_libelle=" . urlencode($search_libelle);
}
if (! empty($search_fournisseur)) {
	$filter['t.fournisseur'] = $search_fournisseur;
	$param .= "&amp;search_fournisseur=" . urlencode($search_fournisseur);
}
if (! empty($search_date)) {
	$filter['t.date_acq'] = $search_date;
	$param .= "&amp;search_date=" . urlencode($search_date);
}
if (! empty($search_local)) {
	$filter['t.local_id'] = $search_local;
	$param .= "&amp;search_local=" . urlencode($search_local);
}


// view
$form = new Form($db);
$object = new Immocost($db);
$formimmo = new FormImmobilier($db);


llxHeader('', $langs->trans("Cost"));

$sortorder = GETPOST("sortorder");
$sortfield = GETPOST("sortfield");
$page = GETPOST("page");
if (! $sortorder)
	$sortorder = "ASC";
if (! $sortfield)
	$sortfield = "t.libelle";

if ($page == - 1) {
	$page = 0;
}

$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
$limit = $conf->liste_limit;

if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$nbtotalofrecords = $object->fetchAll($sortorder, $sortfield, 0, 0, $filter);
} else {
	$nbtotalofrecords = 0;
}

$result = $object->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);


print_barre_liste($langs->trans("ListCosts"), $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $result, $nbtotalofrecords);
print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
	
print '<table class="noborder" width="100%">';
print "<tr class=\"liste_titre\">";

print_liste_field_titre($langs->trans('libelle'), $_SERVER['PHP_SELF'], 't.libelle', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('fournisseur'), $_SERVER['PHP_SELF'], 't.fournisseur', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('local'), $_SERVER['PHP_SELF'], 't.local_id', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Amount'), $_SERVER['PHP_SELF'], 't.montant_ttc', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Date'), $_SERVER['PHP_SELF'], 't.date_acq', '', $param, '', $sortfield, $sortorder);
print "</tr>\n";
	
// Filters
print '<tr class="liste_titre">';
print '<td class="liste_titre">';
print '<input class="flat" size="15" type="text" name="search_libelle" value="' . $search_libelle . '">';
print '</td>';
	
print '<td class="liste_titre">';
print '<input class="flat" size="15" type="text" name="search_fournisseur" value="' . $search_fournisseur . '">';
print '</td>';
	
print '<td class="liste_titre">';
print '<input class="flat" size="7" type="text" name="search_local" value="' . $search_local . '">';
print '</td>';
	
print '<td class="liste_titre">';
print '<input class="flat" size="15" type="text" name="search_date" value="' . $search_date . '">';
print '</td>';

	
print '<td class="liste_titre" align="right">';
print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
print '&nbsp;<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("Search"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
print '</td>';
	
	
$var = true;
	
$coststatic = new Immocost($db);

if (count($object->lines) > 0) {
	foreach ( $object->lines as $line ) {

	$coststatic->id = $line->id;
	$coststatic->libelle = $line->libelle;
	
	$var = ! $var;
	print "<tr " . $bc[$var] . ">";
	print '<td>' . $coststatic->getNomUrl(1) . '</td>';
	print '<td>' . stripslashes(nl2br($line->libelle)) . '</td>';
	print '<td>' . $line->fournisseur . '</td>';
	print '<td>' . $line->local_id . '</td>';
	print '<td>' . $line->montant_ttc . '</td>';
	print '<td>' . $line->date_acq . '</td>';
	print '<td align="center">';
		if ($user->admin) {
			print '<a href="./list.php?action=delete&id=' . $line->id . '">';
			print img_delete();
			print '</a>';
		}
		print '</td>' . "\n";
	
	print "</tr>\n";
	
	$i ++;
	}
} else {
		print '<tr ' . $bc[false] . '>' . '<td colspan="10">' . $langs->trans("NoRecordFound") . '</td></tr>';
}
print "</table>";
	
print "</form>";

llxFooter();

$db->close();