<?php
/* Copyright (C) 2013 		Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016  Alexandre Spangaro <aspangaro@zendsi.com>
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
 * \file 		immobilier/property/list.php
 * \ingroup 	Immobilier
 * \brief 		List of properties
 */

// Dolibarr environment
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
// Class
require_once '../class/immoproperty.class.php';
require_once '../class/html.formimmobilier.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

// Securite acces client
if ($user->societe_id > 0)
	accessforbidden();

$search_name = GETPOST('search_name');
$search_status = (GETPOST('search_status', 'alpha') != '' ? GETPOST('search_status', 'alpha') : GETPOST('statut', 'alpha'));
$search_address = GETPOST('search_address', 'alpha');
$search_zip = GETPOST('search_zip', 'int');
$search_town = GETPOST('search_town', 'alpha');
$search_owner_name = GETPOST('search_owner_name', 'alpha');
$search_property = GETPOST('search_property', 'alpha');
$search_fk_type_property = GETPOST('search_fk_type_property', 'int');

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) // Both test must be present to be compatible with all browsers
{
	$search_name = "";
	$search_status = "";
	$search_address = "";
	$search_zip = "";
	$search_town = "";
	$search_owner_name = "";
	$search_property = "";
	$search_fk_type_property = "";
}

if ($search_status==-1) $search_status='';
if ($search_fk_type_property==-1) $search_fk_type_property='';

$filter = array ();
if (! empty($search_name)) {
	$filter['t.name'] = $search_name;
	$param .= "&amp;search_name=" . urlencode($search_name);
}
if (! empty($search_status)) {
	$filter['t.statut'] = $search_status;
	$param .= "&amp;search_status=" . urlencode($search_status);
}
if (! empty($search_address)) {
	$filter['t.address'] = $search_address;
	$param .= "&amp;search_address=" . urlencode($search_address);
}
if (! empty($search_zip)) {
	$filter['t.statut'] = $search_zip;
	$param .= "&amp;search_zip=" . urlencode($search_zip);
}
if (! empty($search_town)) {
	$filter['t.town'] = $search_town;
	$param .= "&amp;search_town=" . urlencode($search_town);
}
if (! empty($search_fk_type_property)) {
	$filter['t.fk_type_property'] = $search_fk_type_property;
	$param .= "&amp;search_fk_type_property=" . urlencode($search_fk_type_property);
}
if (! empty($search_owner_name)) {
	$filter['soc.nom'] = $search_owner_name;
	$param .= "&amp;search_owner_name=" . urlencode($search_owner_name);
}
if (! empty($search_property)) {
	$filter['t2.name'] = $search_property;
	$param .= "&amp;search_property=" . urlencode($search_property);
}

/*
 * View
 */
$form = new Form($db);
$object = new Immoproperty($db);
$formimmo = new FormImmobilier($db);

llxHeader('', $langs->trans("Properties"));

$sortorder = GETPOST("sortorder");
$sortfield = GETPOST("sortfield");
$page = GETPOST("page");
if (! $sortorder)
	$sortorder = "ASC";
if (! $sortfield)
	$sortfield = "t.name";

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

print_barre_liste($langs->trans("ListProperties"), $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $result, $nbtotalofrecords);
print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
	
print '<table class="noborder" width="100%">';
print "<tr class=\"liste_titre\">";
print_liste_field_titre($langs->trans('Property'), $_SERVER['PHP_SELF'], 't.name', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Address'), $_SERVER['PHP_SELF'], 't.address', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Zip'), $_SERVER['PHP_SELF'], 't.zip', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Town'), $_SERVER['PHP_SELF'], 't.town', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans('Country'), $_SERVER['PHP_SELF'], 't.fk_country', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("TypeProperty"), $_SERVER["PHP_SELF"], "tp.label", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Building"), $_SERVER["PHP_SELF"], "t.fk_property", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Owner"), $_SERVER["PHP_SELF"], "soc.nom", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre($langs->trans("Status"), $_SERVER["PHP_SELF"], "", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'maxwidthsearch ');
print "</tr>\n";
	
// Filters
print '<tr class="liste_titre">';
print '<td class="liste_titre">';
print '<input class="flat" size="7" type="text" name="search_name" value="' . $search_name . '">';
print '</td>';
	
print '<td class="liste_titre">';
print '<input class="flat" size="15" type="text" name="search_address" value="' . $search_address . '">';
print '</td>';
	
print '<td class="liste_titre">';
print '<input class="flat" size="5" type="text" name="search_zip" value="' . $search_zip . '">';
print '</td>';
	
print '<td class="liste_titre">';
print '<input class="flat" size="7" type="text" name="search_town" value="' . $search_town . '">';
print '</td>';
	
// Country
print '<td class=liste_titre"></td>';

// Type property
print '<td class=liste_titre">';
print $formimmo->select_type_property($search_fk_type_property, 'search_fk_type_property', 't.active=1');
print '</td>';

print '<td class="liste_titre">';
print '<input class="flat" size="7" type="text" name="search_property" value="' . $search_property . '">';
print '</td>';

print '<td class="liste_titre">';
print '<input class="flat" size="7" type="text" name="search_owner_name" value="' . $search_owner_name . '">';
print '</td>';

// Status
print '<td class="liste_titre"></td>';
	
print '<td class="liste_titre" align="right">';
print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
print '&nbsp;<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("Search"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
print '</td>';
	
$var = true;
	
$propertystatic = new Immoproperty($db);
$thirdparty_static = new Societe($db);

if (count($object->lines) > 0) {

	foreach ( $object->lines as $line ) {

	$propertystatic->id = $line->id;
	$propertystatic->name = $line->name;

	$var = ! $var;
	print "<tr " . $bc[$var] . ">";
	print '<td>' . $propertystatic->getNomUrl(1) . '</td>';
	print '<td>' . stripslashes(nl2br($line->address)) . '</td>';
	print '<td>' . stripslashes(nl2br($line->zip)) . '</td>';
	print '<td>' . stripslashes(nl2br($line->town)) . '</td>';
	print '<td>' . getCountry($line->fk_pays,1) . '</td>';
	print '<td>' . $line->type_label . '</td>';

	print '<td>' . $line->property . '</td>';
	$thirdparty_static->id=$line->fk_owner;
    $thirdparty_static->name=$line->owner_name;
	print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
	print '<td>' . $line->statut . '</td>';
	print '<td align="center">';
		if ($user->rights->immobilier->property->delete) {
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
