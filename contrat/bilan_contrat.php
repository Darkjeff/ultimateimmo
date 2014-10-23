<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * \file htdocs/custom/immobilier/fiche_locataire.php
 * \ingroup immobilier
 * \brief Page fiche locataire
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
dol_include_once ( "/immobilier/class/contrat.class.php" );
dol_include_once ( '/immobilier/core/lib/immobilier.lib.php' );

// Langs
$langs->load ( "immobilier@immobilier" );

$id = GETPOST ( 'id', 'int' );

$mesg = '';

$limit = $conf->liste_limit;

/*
* loyer et paiement par contrat
*
*/

llxheader ( '', $langs->trans ( "bilancontrat" ), '' );

$contrat = new Contrat ( $db );
$result = $contrat->fetch ( $id );
$head = contrat_prepare_head ( $contrat );

dol_fiche_head ( $head, 'info', $langs->trans ( "Imoinfo" ), 0, 'agreement' );

$sql = "(SELECT l.periode_du as date , l.montant_tot as debit, 0 as credit , l.nom as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as l";
$sql .= " WHERE l.contrat_id =" . $id;
$sql .= ")";
$sql .= "UNION (SELECT p.date_paiement as date, 0 as debit , p.montant as credit, p.commentaire as des";
$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as p";
$sql .= " WHERE p.contrat_id =" . $id;
$sql .= ")";
$sql .= "ORDER BY date";

$result = $db->query ( $sql );
if ($result) {
	$num_lignes = $db->num_rows ( $result );
	$i = 0;
	
	print '<table class="border" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans ( "date" ) . '</td>';
	print '<td>' . $langs->trans ( "debit" ) . '</td>';
	print '<td>' . $langs->trans ( "credit" ) . '</td>';
	print '<td>' . $langs->trans ( "description" ) . '</td>';
	print "</tr>\n";
	
	$var = True;
	while ( $i < min ( $num_lignes, $limit ) ) {
		
		$objp = $db->fetch_object ( $result );
		$var = ! $var;
		print "<tr $bc[$var]>";
		
		print '<td>' . dol_print_date ( $db->jdate ( $objp->date ), 'day' ) . '</td>';
		print '<td>' . $objp->debit . '</td>';
		print '<td>' . $objp->credit . '</td>';
		print '<td>' . $objp->des . '</td>';
		
		print "</tr>";
		$i ++;
	}
	print '</table>';
} else {
	print $db->error ();
}

llxFooter ( '' );
$db->close ();
?>
