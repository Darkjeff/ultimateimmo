<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy          <jeff@jeffinfo.com>
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
 * \ingroup immobilier
 * \brief List Appt or House
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");
	
	// class

dol_include_once ( "/immobilier/class/local.class.php" );



	// Securite acces client

llxHeader ( "", "", 'Immobilier' );

/*
* Locaux en location
*
*/

$local_static = new Local ( $db );


$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$sql = "SELECT l.rowid as reference, l.nom as nom, l.adresse as adresse , l.commentaire as commentaire, l.statut as statut, l.immeuble_id as immeuble, u.lastname , u.firstname";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_local as l";
$sql .= " , " . MAIN_DB_PREFIX . "user as u";
$sql .= " WHERE l.statut = 'Actif' AND l.proprietaire_id = u.rowid ";
if ($user->id != 1) {
	$sql .= " AND l.proprietaire_id=".$user->id;
}
$sql .= " ORDER BY l.nom ASC " . $db->plimit ( $limit + 1, $offset );

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	
	print_barre_liste ( "Locaux en location", $page, "local/local.php", "", $sortfield, $sortorder, '', $num_lignes );
	/*
 * Boutons d'actions
 */
	print '<a class="butAction" href="local/fiche_local.php?action=create">nouvelle location</a>';
	
	print '<table class="noborder" width="100%">';
	print '<tr></tr>';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Owner").'</td>';
	print '<td>'.$langs->trans("Nom").'</td>';
	print '<td>'.$langs->trans("adresse").'</td>';
	print '<td>'.$langs->trans("commentaire").'</td>';
	print '<td>'.$langs->trans("immeuble").'</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";
	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";
		print '<td width="60">';
		$local_static->id = $objp->reference;
		$local_static->nom = $objp->reference;
		print $local_static->getNomUrl ( 1, '20' );
		print '</td>';
		
		print '<td>' ;
		$owner =  $objp->lastname . ' '. $objp->firstname ;
		print $owner;
		print '</td>';
		
		print '<td>' . stripslashes ( nl2br ( $objp->nom ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->adresse ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->commentaire ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->immeuble ) ) . '</td>';
		
	
		
		print '<td align="right"><a href="local/fiche_local.php?action=update&id=' . $objp->reference . '">';
		print img_edit ();
		print '</a></td>';
		
		print "</tr>";
		$i ++;
	}
	print "</table>";
} else {
	print $db->error ();
}

llxFooter();

$db->close();
