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
 * \file    immobilier/rent/list.php
 * \ingroup immobilier
 * \brief   List of rent
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

dol_include_once ( "/immobilier/class/rent.class.php" );
require_once ('../core/lib/immobilier.lib.php');
require_once '../class/immoproperty.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

// Securite acces client
if ($user->societe_id > 0)
	accessforbidden ();

llxHeader ( '', 'Contrat' );

/*
 * Locaux en location
 */
$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$sql = "SELECT c.rowid as reference, loc.rowid as locId, loc.nom as nom, loc.prenom as prenom, l.address as address , l.rowid as localId, l.name as local, soc.rowid as soc_id, soc.nom as owner_name, c.montant_tot as total, c.encours as encours, c.preavis as preavis";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter as loc";
$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
$sql .= " , " . MAIN_DB_PREFIX . "immo_property as l";
$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'societe as soc ON soc.rowid = l.fk_owner';
$sql .= " WHERE loc.rowid = c.fk_renter and l.rowid = c.fk_property";
if ($user->id != 1) {
	$sql .= " AND c.proprietaire_id=".$user->id;
}
if (strlen(trim($_GET["search_nom"]))) {
	$sql .= " AND loc.nom like '%" . $_GET["search_nom"] . "%'";
}
if (strlen(trim($_GET["search_local"]))) {
	$sql .= " AND l.name like '%" . $_GET["search_local"] . "%'";
}
if (strlen(trim($_GET["search_statut"]))) {
	$sql .= " AND c.preavis = '" . $_GET["search_statut"] . "'";
}
if (strlen(trim($_GET["search_owner_name"]))) {
	$sql .= " AND soc.nom like '%" . $_GET["search_owner_name"] . "%'";
}

$sql .= " ORDER BY loc.nom ASC " . $db->plimit ( $limit + 1, $offset );

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;

	$search_owner_name = GETPOST('search_owner_name', 'alpha');
	if (! empty($search_owner_name)) {
		$filter['soc.nom'] = $search_owner_name;
		$param .= "&amp;search_owner_name=" . urlencode($search_owner_name);
	}

	print_barre_liste ( "contrat", $page, $_SERVER["PHP_SELF"], "", $sortfield, $sortorder, '', $num_lignes );

	$html = new Form($db);
	$contrat = new Rent($db, GETPOST ( 'id' ) );

	print '<form method="GET" action="' . $_SERVER["PHP_SELF"] . '">';

	print '<table class="noborder" width="100%">';
	print '<tr></tr>';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Contrat").'</td>';
	print '<td>'.$langs->trans("Locataire").'</td>';
	print '<td>'.$langs->trans("Location").'</td>';
	print '<td>'.$langs->trans("Owner").'</td>';
	print '<td>'.$langs->trans("LoyerTotal").'</td>';
	print '<td>'.$langs->trans("EncoursLoyer").'</td>';
	print '<td>'.$langs->trans("Status").'</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";

	print '<tr class="liste_titre">';
	print '<td align="right">&nbsp;</td>';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_nom" value="' . GETPOST("search_lastname") . '"></td>';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_local" value="' . GETPOST("search_lastname") . '"></td>';
	print '<td class="liste_titre">';
	print '<input class="flat" size="7" type="text" name="search_owner_name" value="' . $search_owner_name . '">';
	print '</td>';
	print '<td align="right">&nbsp;</td>';
	print '<td align="right">&nbsp;</td>';
	print '<td class="liste_titre" align="left">';
	print $html->selectarray('search_statut', array(''=>'', '0'=>'0','1'=>'1'), $contrat->statut);
	print '</td>';
	print '<td align="right">';
	print '<input type="image" class="liste_titre" name="button_search" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" alt="' . $langs->trans("Search") . '">';
	print '</td>';
	print "</tr>\n";

	$propertystatic = new Immoproperty($db);	
	$thirdparty_static = new Societe($db);

	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$code_statut = '';

		$propertystatic->id = $objp->localId;
		$propertystatic->name = $objp->local;

		if ($objp->preavis == 1 ){
			$code_statut = 'color: red';
		} else {
			$code_statut = 'color:blue';
		}

		$var = ! $var;
		print "<tr $bc[$var]>";
		print '<td>';
		$contrat->id = $objp->reference;
		$contrat->nom = $objp->reference;
		print $contrat->getNomUrl ( 1, '20' );
		print '</td>';

		print '<td align="left" style="' . $code_statut . '">';
		print '<a href="../renter/card.php?id=' . $objp->locId . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($objp->nom) . ' ' . ucfirst($objp->prenom) . '</a>';

		print '<td>' . $propertystatic->getNomUrl(1) . '</td>';

		$thirdparty_static->id=$objp->fk_owner;
		$thirdparty_static->name=$objp->owner_name;
		print '<td>' . $thirdparty_static->getNomUrl(1) . '</td>';
		
		print '<td align="right">' . price($objp->total) . '</td>';
		print '<td align="right">' . price($objp->encours) . '</td>';
		print '<td>' . $contrat->LibStatut($objp->preavis) . '</td>';
		print '<td align="center">';
		if ($user->admin) {
			print '<a href="./list.php?action=delete&id=' . $objp->id . '">';
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
