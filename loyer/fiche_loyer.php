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

// Class

dol_include_once ( "/immobilier/class/loyer.class.php" );

$res = dol_include_once ( "/immobilier/core/modules/immobilier/modules_immobilier.php" );
if (! $res)
	die ( "Include of immobilier" );
dol_include_once ( "/immobilier/class/contrat.class.php" );

// Langs
$langs->load ( "immobilier@immobilier" );
$langs->load ( "compta" );
$langs->load ( "bills" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action' );

// Actions

/*
 * 	Classify paid
 */
if (GETPOST ( "action" ) == 'paid') {
	$loyer = new Loyer ( $db );
	$loyer->fetch ( $id );
	$result = $loyer->set_paid ( $user );
	Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
}

/*
 *	Delete rental
 */
if (GETPOST ( "action" ) == 'confirm_delete' && $_REQUEST ["confirm"] == 'yes') {
	$loyer = new Loyer ( $db );
	$loyer->fetch ( $id );
	$result = $loyer->delete ( $user );
	if ($result > 0) {
		header ( "Location: index.php" );
		exit ();
	} else {
		$mesg = '<div class="error">' . $loyer->error . '</div>';
	}
}

/*
 * Action generate quitance
*/
if (GETPOST ( "action" ) == 'quittance') {
	// Define output language
	$outputlangs = $langs;
	
	$file = 'quittance_' . $id . '.pdf';
	
	$result = immobilier_pdf_create ( $db, $id, '', 'quittance', $outputlangs, $file );
	
	if ($result > 0) {
		Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
		exit ();
	} else {
		setEventMessage ( $agf->error, 'errors' );
	}
}

/*
 * Add rental
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

if (GETPOST ( "action" ) == 'maj') 

{
	$dateech = @dol_mktime ( $_POST ["echhour"], $_POST ["echmin"], $_POST ["echsec"], $_POST ["echmonth"], $_POST ["echday"], $_POST ["echyear"] );
	$dateperiod = @dol_mktime ( $_POST ["periodhour"], $_POST ["periodmin"], $_POST ["periodsec"], $_POST ["periodmonth"], $_POST ["periodday"], $_POST ["periodyear"] );
	$dateperiodend = @dol_mktime ( $_POST ["periodendhour"], $_POST ["periodendmin"], $_POST ["periodendsec"], $_POST ["periodendmonth"], $_POST ["periodendday"], $_POST ["periodendyear"] );
	/*if (! $dateech)
	{
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("DateDue")).'</div>';
		$action = 'update';
	}
	elseif (! $dateperiod)
	{
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Period")).'</div>';
		$action = 'update';
	}
		elseif (! $dateperiodend)
	{
		$mesg='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Periodend")).'</div>';
		$action = 'update';
	}
	else
	{
	*/
	$loyer = new Loyer ( $db );
	$result = $loyer->fetch ( $id );
	
	$loyer->nom = GETPOST ( 'nom' ); // $_POST["nom"];
	$loyer->montant_tot = $_POST ["montant_tot"];
	$loyer->loy = $_POST ["loy"];
	$loyer->charges = $_POST ["charges"];
	$loyer->charge_ex = $_POST ["charge_ex"];
	$loyer->echeance = $dateech;
	$loyer->commentaire = $_POST ["commentaire"];
	$loyer->statut = $_POST ["statut"];
	$loyer->periode_du = $dateperiod;
	$loyer->periode_au = $dateperiodend;
	
	$result = $loyer->update ( $user );
	header ( "Location: " . DOL_URL_ROOT . "/custom/immobilier/loyer/fiche_loyer.php?id=" . $loyer->id );
	if ($id > 0) {
		// $mesg='<div class="ok">'.$langs->trans("SocialContributionAdded").'</div>';
	} else {
		$mesg = '<div class="error">' . $loyer->error . '</div>';
	}
	// }
}

/*
 * View
 *
 */

$form = new Form ( $db );

llxheader ( '', $langs->trans ( "newrental" ), '' );

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
	if ($user->id != 1) {
	$sql .= " AND c.proprietaire_id=".$user->id;
}
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
/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */

if ($id > 0) {
	// if (GETPOST("action") == 'update')
	
	$loyer = new Loyer ( $db );
	$result = $loyer->fetch ( $id );
	
	print '<form action="fiche_loyer.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	/*
		* Nom du loyer
		*/
	
	print '<tr><td width="20%">' . $langs->trans ( "NomLoyer" ) . '</td>';
	print '<td><input name="nom" size="20" value="' . $loyer->nom . '"</td></tr>';
	
	/*
		* Contrat
		*/
	
	print '<tr><td width="20%">' . $langs->trans ( "contrat_id" ) . '</td>';
	print '<td>' . $loyer->contrat_id . '</td></tr>';
	
	/*
		* Nom Appartement
		*/
	
	print '<tr><td width="20%">' . $langs->trans ( "nomlocal" ) . ' </td>';
	print '<td>' . $loyer->nomlocal . '</td></tr>';
	
	/*
		* nom locataire
		*/
	
	print '<tr><td width="20%">' . $langs->trans ( "nomlocataire" ) . '</td>';
	print '<td>' . $loyer->nomlocataire . '</td></tr>';
	
	// Amount
	
	print '<tr><td width="20%">' . $langs->trans ( "montant_tot" ) . '</td>';
	print '<td><input name="montant_tot" size="10" value="' . $loyer->montant_tot . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "loy" ) . '</td>';
	print '<td><input name="loy" size="10" value="' . $loyer->loy . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "charges" ) . '</td>';
	print '<td><input name="charges" size="10" value="' . $loyer->charges . '"</td>';
	print '<tr><td width="20%">' . $langs->trans ( "charge_ex" ) . '</td>';
	print '<td><input name="charge_ex" size="10" value="' . $loyer->charge_ex . '"</td>';
	$rowspan = 5;
	print '<td rowspan="' . $rowspan . '" valign="top">';
	
	/*
		* Paiements
		*/
	$sql = "SELECT p.rowid, p.loyer_id, date_paiement as dp, p.montant, p.commentaire as type, il.montant_tot as amount";
	$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as p";
	$sql .= ", " . MAIN_DB_PREFIX . "immo_loyer as il ";
	$sql .= " WHERE p.loyer_id = " . $id;
	$sql .= " AND p.loyer_id = il.rowid";
	$sql .= " ORDER BY dp DESC";
	
	// print $sql;
	$resql = $db->query ( $sql );
	if ($resql) {
		$num = $db->num_rows ( $resql );
		$i = 0;
		$total = 0;
		echo '<table class="nobordernopadding" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans ( "Date" ) . '</td><td>' . $langs->trans ( "Type" ) . '</td>';
		print '<td align="right">' . $langs->trans ( "Amount" ) . '</td><td>&nbsp;</td></tr>';
		
		$var = True;
		while ( $i < $num ) {
			$objp = $db->fetch_object ( $resql );
			$var = ! $var;
			print "<tr " . $bc [$var] . "><td>";
			print '<a href="' . DOL_URL_ROOT . '/custom/immobilier/loyer/fiche_paiement.php?action=update&id=' . $objp->rowid . '">' . img_object ( $langs->trans ( "Payment" ), "payment" ) . '</a> ';
			print dol_print_date ( $db->jdate ( $objp->dp ), 'day' ) . "</td>\n";
			print "<td>" . $objp->type . "</td>\n";
			print '<td align="right">' . price ( $objp->montant ) . "</td><td>&nbsp;" . $langs->trans ( "Currency" . $conf->currency ) . "</td>\n";
			print "</tr>";
			$totalpaye += $objp->montant;
			$i ++;
		}
		
		if ($loyer->paye == 0) {
			print "<tr><td colspan=\"2\" align=\"right\">" . $langs->trans ( "AlreadyPaid" ) . " :</td><td align=\"right\"><b>" . price ( $totalpaye ) . "</b></td><td>&nbsp;" . $langs->trans ( "Currency" . $conf->currency ) . "</td></tr>\n";
			print "<tr><td colspan=\"2\" align=\"right\">" . $langs->trans ( "AmountExpected" ) . " :</td><td align=\"right\" bgcolor=\"#d0d0d0\">" . price ( $loyer->montant_tot ) . "</td><td bgcolor=\"#d0d0d0\">&nbsp;" . $langs->trans ( "Currency" . $conf->currency ) . "</td></tr>\n";
			
			$resteapayer = $loyer->montant_tot - $totalpaye;
			
			print "<tr><td colspan=\"2\" align=\"right\">" . $langs->trans ( "RemainderToPay" ) . " :</td>";
			print "<td align=\"right\" bgcolor=\"#f0f0f0\"><b>" . price ( $resteapayer ) . "</b></td><td bgcolor=\"#f0f0f0\">&nbsp;" . $langs->trans ( "Currency" . $conf->currency ) . "</td></tr>\n";
		}
		print "</table>";
		$db->free ( $resql );
	} else {
		dol_print_error ( $db );
	}
	print "</td>";
	
	print "</tr>";
	
	// Due date
	
	print '<tr><td width="20%">' . $langs->trans ( "echeance" ) . '</td>';
	print '<td align="left">';
	print $form->select_date ( $loyer->echeance, 'ech', 0, 0, 0, 'fiche_loyer', 1 );
	print '</td>';
	print '<tr><td width="20%">' . $langs->trans ( "periode_du" ) . '</td>';
	print '<td align="left">';
	print $form->select_date ( $loyer->periode_du, 'period', 0, 0, 0, 'fiche_loyer', 1 );
	print '</td>';
	print '<tr><td width="20%">' . $langs->trans ( "periode_au" ) . '</td>';
	print '<td align="left">';
	print $form->select_date ( $loyer->periode_au, 'periodend', 0, 0, 0, 'fiche_loyer', 1 );
	print '</td>';
	print '<tr><td width="20%">' . $langs->trans ( "commentaire" ) . '</td>';
	print '<td><input name="commentaire" size="70" value="' . $loyer->commentaire . '"</td></tr>';
	
	// Status loyer
	
	print '<tr><td width="20%">statut</td>';
	print '<td align="left" nowrap="nowrap">';
	print $loyer->LibStatut ( $loyer->paye, 5 );
	print "</td></tr>";
	
	print '<tr><td colspan="2">&nbsp;</td></tr>';
	
	print '</table>';
	
	if (GETPOST ( "action" ) == 'update') {
		print '<br><div align="center">';
		print '<input type="submit" class="button" name="save" value="' . $langs->trans ( "Save" ) . '">';
		print ' &nbsp; ';
		print '<input type="submit" class="button" name="cancel" value="' . $langs->trans ( "Cancel" ) . '">';
		print '</div';
	}
	
	if (GETPOST ( "action" ) == 'update')
		print "</form>\n";
	
	if (is_file ( $conf->immobilier->dir_output . '/quittance_' . $id . '.pdf' )) {
		print '&nbsp';
		print '<table class="border" width="100%">';
		print '<tr class="liste_titre"><td colspan=3>' . $langs->trans ( "LinkedDocuments" ) . '</td></tr>';
		// afficher
		$legende = $langs->trans ( "Ouvrir" );
		print '<tr><td width="200" align="center">' . $langs->trans ( "Quittance" ) . '</td><td> ';
		print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=immobilier&file=quittance_' . $id . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
		print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';
		print '</td></tr></table>';
	}
	
	print '</div>';
	
	/*
		*   Boutons actions
		*/
	
	print "<div class=\"tabsAction\">\n";
	
	// Edit
	
	print "<a class=\"butAction\" href=\"" . DOL_URL_ROOT . "/immobilier/loyer/fiche_loyer.php?id=$loyer->id&amp;action=update\">" . $langs->trans ( "Modify" ) . "</a>";
	
	// Emettre paiement
	if ($object->paye == 0 && ((price2num ( $loyer->montant_tot ) < 0 && round ( $resteapayer ) < 0) || (price2num ( $loyer->montant_tot ) > 0 && round ( $resteapayer ) > 0))) {
		print "<a class=\"butAction\" href=\"" . DOL_URL_ROOT . "/immobilier/loyer/fiche_paiement.php?id=$id&amp;action=create\">" . $langs->trans ( "DoPayment" ) . "</a>";
	}
	
	// Classify 'paid'
	if ($loyer->paye == 0 && round ( $resteapayer ) <= 0) {
		print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=paid&id=' . $id . '">' . $langs->trans ( 'ClassifyPaid' ) . '</a>';
	}
	
	// Delete
	print "<a class=\"butActionDelete\" href=\"" . DOL_URL_ROOT . "/immobilier/loyer/fiche_loyer.php?id=$object->id&amp;action=delete\">" . $langs->trans ( "Delete" ) . "</a>";
	
	// generate quittance
	
	print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=quittance&id=' . $id . '">' . $langs->trans ( 'GenererQuittance' ) . '</a>';
	
	print "</div>";
}

$db->close();
llxFooter();