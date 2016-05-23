<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * \file htdocs/compta/ventilation/liste.php
 * \ingroup compta
 * \brief Page de ventilation des lignes de facture
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");
	
	
dol_include_once ( "/immobilier/class/rent.class.php" );
require_once ('../core/lib/immobilier.lib.php');


	// Securite acces client
if ($user->societe_id > 0)
	accessforbidden ();
	
	

llxHeader ( '', 'Contrat' );

/*
* Locaux en location
*
*/
$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$sql = "SELECT c.rowid as reference, loc.nom as nom, l.adresse as adresse , l.nom as local, loc.statut as statut, c.montant_tot as total, c.encours as encours, c.preavis as preavis";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_locataire as loc";
$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as l";
$sql .= " WHERE loc.rowid = c.locataire_id and l.rowid = c.local_id  ";
if ($user->id != 1) {
	$sql .= " AND c.proprietaire_id=".$user->id;
}
if (strlen(trim($_GET["search_nom"]))) {
	$sql .= " AND loc.nom like '%" . $_GET["search_nom"] . "%'";
}
if (strlen(trim($_GET["search_local"]))) {
	$sql .= " AND l.nom like '%" . $_GET["search_local"] . "%'";
}
if (strlen(trim($_GET["search_statut"]))) {
	$sql .= " AND loc.statut like '" . $_GET["search_statut"] . "%'";
}

$sql .= " ORDER BY loc.nom ASC " . $db->plimit ( $limit + 1, $offset );

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	
	print_barre_liste ( "contrat", $page, "contrat/contrat.php", "", $sortfield, $sortorder, '', $num_lignes );
	
	
	/*
 * Boutons d'actions
 */
 $html = new Form ( $db );
 $contrat = new Rent ( $db, GETPOST ( 'id' ) );
 
 
 
 	print '<form method="GET" action="contrat.php">';
	
	
	
	
	print '<a class="butAction" href="contrat/fiche_contrat.php?action=create">' .$langs->trans("newcontract").'</a>';
	
	print '<table class="noborder" width="100%">';
	print '<tr></tr>';
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
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_nom" value="' . GETPOST("search_lastname") . '"></td>';
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_local" value="' . GETPOST("search_lastname") . '"></td>';
	print '<td align="right">&nbsp;</td>';
	print '<td align="right">&nbsp;</td>';
	 print '<td class="liste_titre" align="left">';
	print $html->selectarray('search_statut', $contrat->status_array, $contrat->statut);
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
		$contrat->id = $objp->reference;
		$contrat->nom = $objp->reference;
		print $contrat->getNomUrl ( 1, '20' );
		print '</td>';
		
		
		print '<td align="left" style="' . $code_statut . '">';
		print $objp->nom;
		print '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->local ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->total ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->encours ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->preavis ) ) . '</td>';
		print '<td align="right"><a href="contrat/fiche_contrat.php?id=' . $objp->reference . '">';
		print img_edit ();
		print '</a></td>';
		
		print "</tr>";
		$i ++;
	}
	print "</table></form>";
} else {
	print $db->error ();
}

llxFooter();

$db->close();
