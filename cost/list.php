<?php
/* Copyright (C) 2013-2016 	Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2016 		Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/cost/list.php
 * \ingroup immobilier
 * \brief   Cost list
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
	
// Class
require_once '../class/immocost.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// Securite acces client

llxHeader("", "", 'Immobilier');

/*
 * Locaux en location
 */
$page = $_GET["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;
$sortorder = GETPOST('sortorder', 'alpha');
$sortfield = GETPOST('sortfield', 'alpha');

$filtre = GETPOST("filtre");

// view
$form = new Form($db);
$charge_static = new Immocost($db);
$formfile = new FormFile($db);

if (empty($sortorder))
	$sortorder = "DESC";
if (empty($sortfield))
	$sortfield = "ch.date";

$sql = "SELECT ch.rowid as reference, ch.fk_property as idlocal , ch.type as type, ch.label as libelle, ch.amount_ht , ch.amount_vat , ch.amount , ch.date";
$sql .= ", ll.rowid as llid, ll.name as nomlocal";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_cost as ch";
$sql .= " , " . MAIN_DB_PREFIX . "immo_property as ll";
$sql .= " WHERE ch.fk_property = ll.rowid";
if (GETPOST("search_libelle"))
	$sql .= " AND ch.libelle LIKE '%" . GETPOST("search_libelle") . "%'";
if (GETPOST("search_fournisseur"))
	$sql .= " AND ch.fournisseur LIKE '%" . GETPOST("search_fournisseur") . "%'";
if (GETPOST("search_local"))
	$sql .= " AND ll.nom LIKE '%" . GETPOST("search_local") . "%'";
if ($user->id != 1) {
	$sql .= " AND ch.proprietaire_id=" . $user->id;
}
$sql .= " ORDER BY " . $sortfield . " " . $sortorder . " " . $db->plimit($limit + 1, $offset);

$result = $db->query($sql);
if ($result) {
	$num_lignes = $db->num_rows($result);
	$i = 0;
	
	$var = true;
	
	$param = '';
	
	print_barre_liste($langs->trans("Charges"), $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num_lignes);

/*
 * Boutons d'actions
 */
	
	print '<form method="GET" action="' . $_SERVER["PHP_SELF"] . '">';
	
	print '<table class="noborder" width="100%">';
	
	print '<tr class="liste_titre">';
	print_liste_field_titre($langs->trans("Id"), $_SERVER['PHP_SELF'], "ch.rowid", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Libelle"), $_SERVER['PHP_SELF'], "ch.libelle", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Local"), $_SERVER['PHP_SELF'], "ch.idlocal", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Montant TTC"), $_SERVER['PHP_SELF'], "ch.amount", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Date acquittement"), $_SERVER['PHP_SELF'], "ch.date", "", $param, "", $sortfield, $sortorder);
	print_liste_field_titre($langs->trans("Dispatch"), $_SERVER['PHP_SELF'], "dispatch", "", $param, "", $sortfield, $sortorder);
	print '<td>&nbsp;</td>';
	print "</tr>\n";

	print '<tr class="liste_titre">';
	print '<td class="liste_titre">&nbsp;</td>';

	// libelle
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_libelle" value="' . GETPOST("search_libelle") . '"></td>';

	// local
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_local" value="' . GETPOST("search_local") . '"></td>';
	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" name="button_search" value="' . dol_escape_htmltag($langs->trans("Search")) . '" title="' . dol_escape_htmltag($langs->trans("Search")) . '">';
	print '</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";

	$var = True;

	while ( $i < min($num_lignes, $limit) ) {
		$objp = $db->fetch_object($result);
		$var = ! $var;
		print "<tr $bc[$var]>";

		// Ref
		print '<td width="60">';
		$charge_static->id = $objp->reference;
		$charge_static->nom = $objp->reference;
		$charge_static->ref = $objp->reference;
		print $charge_static->getNomUrl(1, '20');

		$filedir=$conf->immobilier->dir_output . '/' . dol_sanitizeFileName($charge_static->id);
		$file_list=dol_dir_list($filedir, 'files');
		if (count($file_list)>0) {
			print img_pdf();
		}

		print '</td>';

		print '<td>' . stripslashes(nl2br($objp->libelle)) . '</td>';
		print '<td>' . stripslashes(nl2br($objp->nomlocal)) . '</td>';
		print '<td>' . stripslashes(nl2br($objp->amount)) . '</td>';
		print '<td width="110" align="left">' . dol_print_date($db->jdate($objp->date), 'day') . '</td>';

		print '<td align="right" nowrap="nowrap">';
		print $charge_static->LibStatut ( $objp->dispatch, 5 );
		print "</td>";

		print '<td></td>';
		print "</tr>";
		$i ++;
	}
	print "</table>";
} else {
	print $db->error();
}

/**
 * Actions buttons
 */
print '<div class="tabsAction">';

print '<div class="inline-block divButAction"><a class="butAction" href="./card.php?action=create">'.$langs->trans("AddANewCost")."</a></div>";

print "</div>";

llxFooter();

$db->close();
