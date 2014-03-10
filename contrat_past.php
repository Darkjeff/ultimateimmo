<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2004-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * $Id: liste.php 8 2011-01-21 15:50:38Z hregis $
 * $Source: /cvsroot/dolibarr/dolibarr/htdocs/compta/ventilation/liste.php,v $
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
	
	// Securite acces client
if ($user->societe_id > 0)
	accessforbidden ();

llxHeader ( '', 'immobilier' );

/*
* Locaux en location
*
*/
$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$sql = "SELECT c.rowid as reference, loc.nom as nom, l.adresse as adresse , l.nom as local, loc.statut as statut, c.montant_tot as total, c.encours as encours , c.preavis as preavis";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_locataire as loc";
$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
$sql .= " , " . MAIN_DB_PREFIX . "immo_local as l";
$sql .= " WHERE preavis = 1 AND loc.rowid = c.locataire_id and l.rowid = c.local_id  ";
if ($user->id != 1) {
	$sql .= " AND l.proprietaire_id=".$user->id;
}
$sql .= " ORDER BY loc.nom ASC " . $db->plimit ( $limit + 1, $offset );

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	
	print_barre_liste ( "contrat", $page, "contrat/contrat.php", "", $sortfield, $sortorder, '', $num_lignes );
	
	print '<a class="butAction" href="contrat/fiche_contrat.php?action=create">nouveau contrat</a>';
	
	print '<table class="noborder" width="100%">';
	print '<tr></tr>';
	print '<tr class="liste_titre">';
	print '<td>Nom</td>';
	print '<td>adresse</td>';
	print '<td>appartement</td>';
	print '<td>loyer total</td>';
	print '<td>encours loyer</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";
	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";
		print '<td>' . stripslashes ( nl2br ( $objp->nom ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->adresse ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->local ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->total ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->encours ) ) . '</td>';
		print '<td align="right"><a href="contrat/fiche_contrat.php?id=' . $objp->reference . '">';
		print img_edit ();
		print '</a></td>';
		
		print "</tr>";
		$i ++;
	}
	print "</table>";
} else {
	print $db->error ();
}
$db->close ();

llxFooter ( "<em>Derni&egrave;re modification $Date: 2009/02/20 22:54:07 $ r&eacute;vision $Revision: 1.15 $</em>" );
?>
