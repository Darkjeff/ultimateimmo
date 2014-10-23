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
 * \file htdocs/compta/ventilation/immeuble.php
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

dol_include_once ( "/immobilier/class/immeuble.class.php" );



	// Securite acces client
if ($user->societe_id > 0)
	accessforbidden ();


llxHeader ( '', 'Properties' );

$immeuble_static = new Immeuble ( $db );

/*
* Immeuble
*
*/
$page = $_GET ["page"];
if ($page < 0)
	$page = 0;
$limit = $conf->liste_limit;
$offset = $limit * $page;

$sql = "SELECT i.rowid , i.nom , i.nb_locaux as nblocaux, i.numero, i.street, i.zipcode , i.town";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_immeuble as i";
$sql .= " WHERE i.statut = 'Actif' ";
if ($user->id != 1) {
	$sql .= " AND i.proprietaire_id=".$user->id;
}
$sql .= " ORDER BY i.nom ASC " . $db->plimit ( $limit + 1, $offset );

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	
	print_barre_liste ( "Immeubles", $page, "immeuble.php", "", $sortfield, $sortorder, '', $num_lignes );

		print '<a class="butAction" href="immeuble/fiche_immeuble.php?action=create">nouveau bien</a>';

	
	print '<table class="noborder" width="100%">';
	
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("NomImmeuble").'</td>';
	print '<td>'.$langs->trans("NombreLocaux").'</td>';
	print '<td>'.$langs->trans("numero").'</td>';
	print '<td>'.$langs->trans("street").'</td>';
	print '<td>'.$langs->trans("zipcode").'</td>';
	print '<td>'.$langs->trans("town").'</td>';
	print '<td>&nbsp;</td>';
	print "</tr>\n";
	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";
		print '<td width="80">';
		$immeuble_static->id = $objp->rowid;
		$immeuble_static->nom = $objp->rowid;
		print $immeuble_static->getNomUrl ( 0, '20' );
		print '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->nom ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->nblocaux ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->numero ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->street ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->zipcode ) ) . '</td>';
		print '<td>' . stripslashes ( nl2br ( $objp->town ) ) . '</td>';
		
		
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
