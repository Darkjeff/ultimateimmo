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
	
		// class

dol_include_once ( "/immobilier/class/charge.class.php" );
	
	// Securite acces client

llxHeader ( "", "", 'Immobilier' );

/*
* Locaux en location
*
*/
$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$filtre = GETPOST ( "filtre" );

// view
$form = new Form ( $db );
$charge_static = new Charge ( $db );


$sql = "SELECT ch.rowid as reference, ch.local_id as idlocal , ch.type as type, ch.libelle as libelle, ch.fournisseur as fournisseur, ch.montant_ttc , ch.date_acq";
$sql.= ", ll.rowid as llid, ll.nom as nomlocal";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ch";
$sql.= " , ".MAIN_DB_PREFIX."immo_local as ll";
$sql.= " WHERE ch.local_id = ll.rowid";
if (GETPOST ( "search_libelle" ))
$sql .= " AND ch.libelle LIKE '%" . GETPOST ( "search_libelle" ) . "%'";
if (GETPOST ( "search_fournisseur" ))
$sql .= " AND ch.fournisseur LIKE '%" . GETPOST ( "search_fournisseur" ) . "%'";
if (GETPOST ( "search_local" ))
$sql .= " AND ll.nom LIKE '%" . GETPOST ( "search_local" ) . "%'";
if ($user->id != 1) {
	$sql .= " AND ch.proprietaire_id=".$user->id;
}
$sql .= " ORDER BY ch.date_acq DESC " . $db->plimit ( $limit + 1, $offset );

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	
	$var = true;
	
	$param = '';
	
	print_barre_liste ( $langs->trans ( "Charges" ), $page, $_SERVER ["PHP_SELF"], $param, $sortfield, $sortorder, '', $num_lignes );
	/*
 * Boutons d'actions
 */
 
 	print '<form method="GET" action="' . $_SERVER ["PHP_SELF"] . '">';
	
	print '<table class="noborder" width="100%">';
	print '<tr></tr>';
 
 
	print '<a class="butAction" href="charge/fiche_charge.php?action=create">Ajouter une nouvelle charge</a>';
	

	print '<tr class="liste_titre">';
	print '<td class="liste_titre">&nbsp;</td>';
	print_liste_field_titre ( $langs->trans ( "Libelle" ), "charges.php", "libelle", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Fournisseur" ), "charges.php", "fournisseur", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Local" ), "charges.php", "idlocal", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Montant TTC" ), "charges.php", "montant_ttc", "", $param, "", $sortfield, $sortorder );
	print_liste_field_titre ( $langs->trans ( "Date acquittement" ), "charges.php", "date_acq", "", $param, "", $sortfield, $sortorder );
	print '<td>&nbsp;</td>';
	print "</tr>\n";
	
		print '<tr class="liste_titre">';
		print '<td class="liste_titre">&nbsp;</td>';

	// libelle
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_libelle" value="' . GETPOST ( "search_libelle" ) . '"></td>';
	// fournisseur
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_fournisseur" value="' . GETPOST ( "search_fournisseur" ) . '"></td>';
	// local
	print '<td class="liste_titre"><input type="text" class="flat" size="15" name="search_local" value="' . GETPOST ( "search_local" ) . '"></td>';
	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre">&nbsp;</td>';
	print '<td class="liste_titre" align="right">';
	print '<input type="image" class="liste_titre" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" name="button_search" value="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '">';
	print '</td>';
	print "</tr>\n";
	
		$var = True;
	
	
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";
		
				// ref
		print '<td width="60">';
		$charge_static->id = $objp->reference;
		$charge_static->nom = $objp->reference;
		$charge_static->ref = $objp->reference;
		print $charge_static->getNomUrl ( 1, '20' );
		print '</td>';
		
		
		print '<td>' . stripslashes ( nl2br ( $objp->libelle ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->fournisseur ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->nomlocal ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->montant_ttc ) ) . '</td>';
		print '<td width="110" align="left">' . dol_print_date ( $db->jdate ( $objp->date_acq ), 'day' ) . '</td>';
	
		
		print "</tr>";
		$i ++;
	}
	print "</table>";
} else {
	print $db->error ();
}

$db->close();
llxFooter();