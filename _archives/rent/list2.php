<?php
/* Copyright (C) 2013-2015 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
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
 * \file    	immobilier/rent/list.php
 * \ingroup 	Immobilier
 * \brief   	List of rent
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

// Class
require_once '../class/immorent.class.php';
require_once '../core/lib/immobilier.lib.php';

// Securite acces client
if ($user->societe_id > 0)
	accessforbidden ();

$search_name = GETPOST('search_name');
$search_property = GETPOST('search_property', 'alpha');
$search_status = (GETPOST('search_status', 'alpha') != '' ? GETPOST('search_status', 'alpha') : GETPOST('statut', 'alpha'));

if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter")) // Both test must be present to be compatible with all browsers
{
	$search_name = "";
	$search_property = "";
	$search_status = "";
}

$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$sql = "SELECT c.rowid as reference, loc.nom as nom, l.address as address , l.name as local,  c.montant_tot as total, c.encours as encours, c.preavis as preavis";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter as loc";
$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
$sql .= " , " . MAIN_DB_PREFIX . "immo_property as l";
$sql .= " WHERE loc.rowid = c.fk_renter and l.rowid = c.fk_property  ";
if ($user->id != 1) {
	$sql .= " AND c.proprietaire_id=".$user->id;
}
if (strlen(trim($search_name))) {
	$sql .= " AND loc.nom like '%" . $search_name . "%'";
}
if (strlen(trim($search_property))) {
	$sql .= " AND l.name like '%" . $search_property . "%'";
}
if (strlen(trim($search_status))) {
	$sql .= " AND loc.statut like '" . $search_status . "%'";
}

$sql .= " ORDER BY loc.nom ASC " . $db->plimit ( $limit + 1, $offset );

if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$nbtotalofrecords = $object->fetchAll($sortorder, $sortfield, 0, 0, $filter);
} else {
	$nbtotalofrecords = 0;
}

$result = $object->fetchAll($sortorder, $sortfield, $limit, $offset, $filter);
	
print_barre_liste ($langs->trans("ListProperties"), $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $result, $nbtotalofrecords);
	
	
/*
 * View
 */

llxHeader ( '', $langs->trans("Rent") );
 
$form = new Form ( $db );
$object = new Rent ( $db, GETPOST ( 'id' ) );
 
print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
	
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Contrat").'</td>';
print '<td>'.$langs->trans("Locataire").'</td>';
print '<td>'.$langs->trans("Location").'</td>';
print '<td>'.$langs->trans("LoyerTotal").'</td>';
print '<td>'.$langs->trans("EncoursLoyer").'</td>';
print '<td>'.$langs->trans("state").'</td>';
print '<td>&nbsp;</td>';
print "</tr>\n";
	
print '<td align="right">&nbsp;</td>';
print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_name" value="' . GETPOST("search_lastname") . '"></td>';
print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_property" value="' . GETPOST("search_lastname") . '"></td>';
print '<td align="right">&nbsp;</td>';
print '<td align="right">&nbsp;</td>';
print '<td class="liste_titre" align="left">';
print $form->selectarray('search_status', $object->status_array, $object->statut);
print '</td>';
print '<td align="right">';
print '<input type="image" class="liste_titre" name="button_search" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" alt="' . $langs->trans("Search") . '">';
print '</td>';
print '<td align="center">&nbsp;</td>';
print "</tr>\n";
	

	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$code_statut = '';
 
 if ($objp->preavis == 1 ){
 $code_statut = 'color: red';
 } else {
  $code_statut = 'color:blue';
  }
		
		
		$var = ! $var;
		print "<tr $bc[$var]>";
		print '<td width="60">';
		$object->id = $objp->reference;
		$object->nom = $objp->reference;
		print $object->getNomUrl ( 1, '20' );
		print '</td>';
		
		
		print '<td align="left" style="' . $code_statut . '">';
		print $objp->nom;
		print '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->local ) ) . '</td>';
		print '<td>' . price($objp->total) . '</td>';
		print '<td>' . price($objp->encours) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->preavis ) ) . '</td>';
		print '<td align="center">';
		if ($user->admin) {
			print '<a href="./list.php?action=delete&id=' . $line->id . '">';
			print img_delete();
			print '</a>';
		}
		print '</td>' . "\n";
		
		print "</tr>";
		$i ++;
	}
	print "</table></form>";
} else {
	print $db->error ();
}

llxFooter();

$db->close();
