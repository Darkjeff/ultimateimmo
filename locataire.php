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
	
	
		// class

dol_include_once ( "/immobilier/class/locataire.class.php" );
	
	// Securite acces client
if ($user->societe_id > 0)
	accessforbidden ();

llxHeader ( '', 'Ventilation' );

/*
* Locaux en location
*
*/
$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$locataire_static = new Locataire ( $db );

$sql = "SELECT loc.rowid, loc.nom as nom, loc.adresse as adresse , loc.telephone as telephone, loc.email as email, loc.statut as statut, loc.solde as solde";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_locataire as loc";
$sql .= " WHERE statut = 'Actif' ";
if ($user->id != 1) {
	$sql .= " AND loc.proprietaire_id=".$user->id;
}
$sql .= " ORDER BY loc.nom ASC " . $db->plimit ( $limit + 1, $offset );

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	
	print_barre_liste ( "Locataires", $page, "locataire.php", "", $sortfield, $sortorder, '', $num_lignes );
	/*
 * Boutons d'actions
 */
	print '<table class="noborder" width="100%">';
	print '<a class="butAction" href="locataire/fiche_locataire.php?action=create">nouveau locataire</a>';
	print '<td></td>';
	
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("NomLocataire").'</td>';
	print '<td>'.$langs->trans("adresse").'</td>';
	print '<td>'.$langs->trans("telephone").'</td>';
	print '<td>'.$langs->trans("email").'</td>';
	print '<td>'.$langs->trans("solde").'</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";
	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";
		
		print '<td width="80">';
		$locataire_static->id = $objp->rowid;
		$locataire_static->nom = $objp->rowid;
		print $locataire_static->getNomUrl ( 0, '20' );
		print '</td>';
		
		print '<td>' . stripslashes ( nl2br ( $objp->nom ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->adresse ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->telephone ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->email ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->solde ) ) . '</td>';
		
		
		print "</tr>";
		$i ++;
	}
	print "</table>";
} else {
	print $db->error ();
}
$db->close ();

llxFooter ( '' );
?>
