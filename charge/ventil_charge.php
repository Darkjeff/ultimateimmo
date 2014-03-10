<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
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
 * $Id: fiche.php 8 2011-01-21 15:50:38Z hregis $
 * $Source: /cvsroot/dolibarr/dolibarr/htdocs/compta/ventilation/fiche.php,v $
 */

/**
 * \file htdocs/compta/ventilation/fiche.php
 * \ingroup compta
 * \brief Page fiche ventilation
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

dol_include_once ( "/immobilier/class/loyer.class.php" );

$res = dol_include_once ( "/immobilier/core/modules/immobilier/modules_immobilier.php" );
if (! $res)
	die ( "Include of immobilier" );
dol_include_once ( "/immobilier/class/contrat.class.php" );
$langs->load ( "immobilier@immobilier" );
$langs->load ( "compta" );
$langs->load ( "bills" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action' );

// Actions


/*
 * Add ventil charge
 */

if (GETPOST ( "action" ) == 'add') 

{
	$dateech = @dol_mktime ( $_POST ["echhour"], $_POST ["echmin"], $_POST ["echsec"], $_POST ["echmonth"], $_POST ["echday"], $_POST ["echyear"] );
	$dateperiod = @dol_mktime ( $_POST ["periodhour"], $_POST ["periodmin"], $_POST ["periodsec"], $_POST ["periodmonth"], $_POST ["periodday"], $_POST ["periodyear"] );
	$dateperiodend = @dol_mktime ( $_POST ["periodendhour"], $_POST ["periodendmin"], $_POST ["periodendsec"], $_POST ["periodendmonth"], $_POST ["periodendday"], $_POST ["periodendyear"] );
	
	if (empty ( $dateech )) {
		setEventMessage ( $langs->trans ( "ErrorFieldRequired", $langs->transnoentities ( "DateDue" ) ), 'errors' );
		$action = 'create';
	} elseif (empty ( $dateperiod )) {
		$mesg = '<div class="error">' . $langs->trans ( "ErrorFieldRequired", $langs->transnoentities ( "Period" ) ) . '</div>';
		$action = 'create';
	} elseif (empty ( $dateperiodend )) {
		$mesg = '<div class="error">' . $langs->trans ( "ErrorFieldRequired", $langs->transnoentities ( "Periodend" ) ) . '</div>';
		$action = 'create';
	} else {
		
		$mesLignesCochees = GETPOST ( 'mesCasesCochees' );
		
		$cpt = 0;
		
		foreach ( $mesLignesCochees as $maLigneCochee ) {
			
			$loyer = new Loyer ( $db );
			
			$maLigneCourante = split ( "_", $maLigneCochee );
			$monId = $maLigneCourante [0];
			$monLocal = $maLigneCourante [1];
			$monLocataire = $maLigneCourante [2];
			$monMontant = $maLigneCourante [3];
			$monLoyer = $maLigneCourante [4];
			$monCharges = $maLigneCourante [5];
			// $monNumLigne = $maLigneCourante[6];
			
			// main info loyer
			$loyer->nom = GETPOST ( 'nom', 'alpha' );
			$loyer->echeance = $dateech;
			$loyer->periode_du = $dateperiod;
			$loyer->periode_au = $dateperiodend;
			
			// main info contrat
			
			$loyer->contrat_id = $monId;
			$loyer->local_id = $monLocal;
			$loyer->locataire_id = $monLocataire;
			$loyer->montant_tot = $monMontant;
			$loyer->loy = $monLoyer;
			$loyer->charges = $monCharges;
			
			$result = $loyer->create ( $user );
			if ($result > 0) {
				setEventMessage ( $langs->trans ( "SocialContributionAdded" ), 'mesgs' );
			} else {
				dol_print_error ( $db );
			}
			$cpt ++;
		}
	}
}


/*
 * View
 *
 */

$form = new Form ( $db );

llxheader ( '', $langs->trans ( "newventilcharge" ), '' );

if (GETPOST ( "action" ) == 'create') {
	print '<form name="fiche_loyer" method="post" action="' . $_SERVER ["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print "<tr class=\"liste_titre\">";
	
	print '<td align="left">';
	print $langs->trans ( "NomLoyer" );
	print '</td><td align="center">';
	print $langs->trans ( "echeance" );
	print '</td><td align="center">';
	print $langs->trans ( "periode_du" );
	print '</td><td align="center">';
	print $langs->trans ( "periode_au" );
	print '</td><td align="left">';
	print '&nbsp;';
	print '</td>';
	print "</tr>\n";
	
	print '<tr ' . $bc [$var] . ' valign="top">';
	
	/*
		* Nom du loyer
		*/
	print '<td><input name="nom" size="30" value="' . $loyer->nom . '"</td>';
	
	// Due date
	
	print '<td align="center">';
	print $form->select_date ( ! empty ( $dateech ) ? $dateech : '-1', 'ech', 0, 0, 0, 'fiche_loyer', 1 );
	print '</td>';
	print '<td align="center">';
	print $form->select_date ( ! empty ( $dateperiod ) ? $dateperiod : '-1', 'period', 0, 0, 0, 'fiche_loyer', 1 );
	print '</td>';
	print '<td align="center">';
	print $form->select_date ( ! empty ( $dateperiodend ) ? $dateperiodend : '-1', 'periodend', 0, 0, 0, 'fiche_loyer', 1 );
	print '</td>';
	
	print '<td align="center"><input type="submit" class="button" value="' . $langs->trans ( "Add" ) . '"></td></tr>';
	
	print '</table>';
	
	/*
* List agreement
*/
	
	$sql = "SELECT c.rowid as reference, loc.nom as nom, l.adresse as adresse , l.nom as local, loc.statut as statut, c.montant_tot as total, c.loy , c.charges, c.locataire_id as reflocataire, c.local_id as reflocal, c.preavis as preavis";
	$sql .= " FROM " . MAIN_DB_PREFIX . "immo_locataire as loc";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_local as l";
	$sql .= " WHERE preavis = 0 AND loc.rowid = c.locataire_id and l.rowid = c.local_id  ";
	$resql = $db->query ( $sql );
	if ($resql) {
		$num = $db->num_rows ( $resql );
		
		$i = 0;
		$total = 0;
		
		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans ( 'contrat_id' ) . '</td>';
		print '<td>' . $langs->trans ( 'local_id' ) . '</td>';
		print '<td>' . $langs->trans ( 'nomlocal' ) . '</td>';
		print '<td>' . $langs->trans ( 'locataire_id' ) . '</td>';
		print '<td>' . $langs->trans ( 'nomlocataire' ) . '</td>';
		print '<td align="right">' . $langs->trans ( 'montant_tot' ) . '</td>';
		print '<td align="right">' . $langs->trans ( 'loy' ) . '</td>';
		print '<td align="right">' . $langs->trans ( 'charges' ) . '</td>';
		print '<td align="right">' . $langs->trans ( 'select' ) . '</td>';
		print "</tr>\n";
		
		if ($num > 0) {
			$var = True;
			
			while ( $i < $num ) {
				$objp = $db->fetch_object ( $resql );
				$var = ! $var;
				print '<tr ' . $bc [$var] . '>';
				
				print '<td>' . $objp->reference . '</td>';
				print '<td>' . $objp->reflocal . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->reflocataire . '</td>';
				print '<td>' . $objp->nom . '</td>';
				
				print '<td align="right">' . price ( $objp->total ) . '</td>';
				print '<td align="right">' . price ( $objp->loy ) . '</td>';
				print '<td align="right">' . price ( $objp->charges ) . '</td>';
				// Colonne choix contrat
				print '<td align="center">';
				
				print '<input type="checkbox" name="mesCasesCochees[]" value="' . $objp->reference . '_' . $objp->reflocal . '_' . $objp->reflocataire . '_' . $objp->total . '_' . $objp->loy . '_' . $objp->charges . '"' . ($objp->reflocal ? ' checked="checked"' : "") . '/>';
				print '</td>';
				print '</tr>';
				
				$i ++;
			}
		}
		$var = ! $var;
		
		print "</table>\n";
		$db->free ( $resql );
	} 

	else {
		dol_print_error ( $db );
	}
	print '</form>';
}

llxFooter ();

$db->close ();

?>
