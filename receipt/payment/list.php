<?php
/* Copyright (C) 2013 Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015 Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file htdocs/custom/immobilier/receipt/payment/list.php
 * \ingroup Immobilier
 * \brief List of payments
 */

// Dolibarr environment
$res = @include ("../../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
	// Class
require_once '../../class/immoproperty.class.php';
require_once '../../class/immopayment.class.php';
require_once '../../class/html.formimmobilier.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

// Securite acces client
if ($user->societe_id > 0)
	accessforbidden();

$search_ref = GETPOST('search_ref');
$search_date_payment = GETPOST('search_date_payment');
$search_nomlocataire =  GETPOST('search_nomlocataire');
$search_nomlocal = GETPOST('search_nomlocal');
$search_nomloyer = GETPOST('search_nomloyer');
$search_amount = GETPOST('search_amount');
$search_comment = GETPOST('search_comment');


if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) // Both test must be present to be compatible with all browsers
{
	$search_ref = "";
	$search_date_payment = "";
	$search_nomlocataire = "";
	$search_nomlocal = "";
	$search_nomloyer = "";
	$search_amount = "";
	$search_comment = "";
}




$filter = array ();
if (! empty($search_ref)) {
	$filter['t.rowid'] = $search_ref;
	$param .= "&amp;search_ref=" . urlencode($search_ref);
}
if (! empty($search_date_payment)) {
	$filter['t.date_payment'] = $search_date_payment;
	$param .= "&amp;search_name=" . urlencode($search_date_payment);
}
if (! empty($search_nomlocataire)) {
	$filter['lc.nom'] = $search_nomlocataire;
	$param .= "&amp;search_status=" . urlencode($search_nomlocataire);
}
if (! empty($search_nomlocal)) {
	$filter['ll.name'] = $search_nomlocal;
	$param .= "&amp;search_address=" . urlencode($search_nomlocal);
}
if (! empty($search_nomloyer)) {
	$filter['lo.name'] = $search_nomloyer;
	$param .= "&amp;search_zip=" . urlencode($search_nomloyer);
}
if (! empty($search_amount)) {
	$filter['t.amount'] = $search_amount;
	$param .= "&amp;search_town=" . urlencode($search_amount);
}
if (! empty($search_comment)) {
	$filter['t.comment'] = $search_comment;
	$param .= "&amp;search_fk_type_property=" . urlencode($search_comment);
}
/*
 * View
 */
$form = new Form($db);
$object = new Immopayment($db);
$formimmo = new FormImmobilier($db);

llxHeader('', $langs->trans("Payments"));

$sortorder = GETPOST("sortorder");
$sortfield = GETPOST("sortfield");
$page = GETPOST("page");
if (! $sortorder)
	$sortorder = "DESC";
if (! $sortfield)
	$sortfield = "t.date_payment";

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
if ($result < 0) {
	setEventMessages(null, $object->errors, 'errors');
} else {
	
	print_barre_liste($langs->trans("ListPayment"), $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $result, $nbtotalofrecords);
	print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
	
	print '<table class="noborder" width="100%">';
	print "<tr class=\"liste_titre\">";
	print_liste_field_titre($langs->trans('ref'), $_SERVER['PHP_SELF'], 't.rowid', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('DatePayment'), $_SERVER['PHP_SELF'], 't.date_payment', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('nomlocataire'), $_SERVER['PHP_SELF'], 'lc.nom', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('nomlocal'), $_SERVER['PHP_SELF'], 'll.name', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('nomloyer'), $_SERVER['PHP_SELF'], 'lo.name', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans('amount'), $_SERVER['PHP_SELF'], 't.amount', '', $param, '', $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("comment"), $_SERVER["PHP_SELF"], "", "", $param, '', $sortfield, $sortorder);
	print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'maxwidthsearch ');
	print "</tr>\n";
	
	// Filters
	print '<tr class="liste_titre">';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_ref" value="' . $search_ref . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_date_payment" value="' . $search_date_payment . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_nomlocataire" value="' . $search_nomlocataire . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="7" type="text" name="search_nomlocal" value="' . $search_nomlocal . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_nomloyer" value="' . $search_nomloyer . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_amount" value="' . $search_amount . '">';
	print '</td>';
	
	print '<td class="liste_titre">';
	print '<input class="flat" size="15" type="text" name="search_comment" value="' . $search_comment . '">';
	print '</td>';
	

	
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" name="button_search" src="' . img_picto($langs->trans("Search"), 'search.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="' . img_picto($langs->trans("Search"), 'searchclear.png', '', '', 1) . '" value="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '" title="' . dol_escape_htmltag($langs->trans("RemoveFilter")) . '">';
	print '</td>';
	
	$var = true;
	
	$paymentstatic = new Immopayment($db);
	
	if (count($object->lines) > 0) {
		foreach ( $object->lines as $line ) {
			
			$paymentstatic->id = $line->id;
			
			
			$var = ! $var;
			print "<tr " . $bc[$var] . ">";
			//print '<td>' . $paymentstatic->getNomUrl(1) . '</td>';
			print '<td>' . $line->id . '</td>';
			print '<td>' . dol_print_date($line->date_payment, 'day') . '</td>';
			print '<td>' . stripslashes(nl2br($line->nomlocataire)) . '</td>';
			print '<td>' . stripslashes(nl2br($line->nomlocal)) . '</td>';
			print '<td>' . stripslashes(nl2br($line->nomloyer)) . '</td>';
			print '<td>' . price($line->amount) . '</td>';
			print '<td colspan="2">' . $line->comment . '</td>';
			
			print "</tr>\n";
			
			$i ++;
		}
	} else {
		print '<tr ' . $bc[false] . '>' . '<td colspan="7">' . $langs->trans("NoRecordFound") . '</td></tr>';
	}
	print "</table>";
	
	print "</form>";
	$db->free($resql);
}

llxFooter();

$db->close();
