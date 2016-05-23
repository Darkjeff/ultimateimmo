<?php
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
 * \file htdocs/compta/ventilation/card.php
 * \ingroup compta
 * \brief Page fiche ventilation
 */


// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

dol_include_once ( "/immobilier/class/rent.class.php" );
require_once ('../core/lib/immobilier.lib.php');
dol_include_once ( '/immobilier/class/html.immobilier.php' );

// Langs
$langs->load ( "immobilier@immobilier" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action' );

$html = new Form ( $db );
$htmlimmo = new FormImmobilier ( $db );




if (GETPOST ( "action" ) == 'add') {
	
	$datect = @dol_mktime ( $_POST ["datecthour"], $_POST ["datectmin"], $_POST ["datectsec"], $_POST ["datectmonth"], $_POST ["datectday"], $_POST ["datectyear"] );
	
	$contrat = new Rent ( $db );
	
	$contrat->local_id = GETPOST ( "local_id" );
	$contrat->locataire_id = GETPOST ( "locataire_id" );
	$contrat->date_entree = $datect;
	$contrat->montant_tot = GETPOST ( "montant_tot" );
	$contrat->loy = GETPOST ( "loy" );
	$contrat->charges = GETPOST ( "charges" );
	$contrat->tva = GETPOST ( "tva" );
	$contrat->periode = GETPOST ( "periode" );
	$contrat->depot = GETPOST ( "depot" );
	$contrat->commentaire = GETPOST ( "commentaire" );
	$contrat->proprietaire_id = GETPOST ( "proprietaire_id" );
	
	$e_contrat = $contrat;
	
	$res = $contrat->create ( $user );
	if ($res == 0) {
		
	} else {
		if ($res == - 3) {
			$_error = 1;
			$action = "create";
		}
		if ($res == - 4) {
			$_error = 2;
			$action = "create";
		}
	}
	Header ( "Location: " . DOL_URL_ROOT . "/custom/immobilier/contrat.php" );
} elseif (GETPOST ( "action" ) == 'maj') {
	
	$error=0;
	
	$datect = dol_mktime ( 0,0,0, GETPOST('datectmonth','int'), GETPOST('datectday','int'), GETPOST('datectyear','int') );
	$datectend = dol_mktime ( 0,0,0, GETPOST('datectendmonth','int'), GETPOST('datectendday','int'), GETPOST('datectendyear','int') );
	
	
	//if ((GETPOST('preavis')>0) && dol_strlen ( $datectend ) != 0) {
	//	setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Date fin")), 'errors');
	//	$error++;
	//}
	
	if (!$error) {
		$contrat = new Rent ( $db );
		$contrat->fetch($id);
		
		$contrat->local_id = GETPOST ( "local_id" );
		$contrat->locataire_id = GETPOST ( "locataire_id" );
		$contrat->date_entree = $datect;
		$contrat->preavis = GETPOST('preavis');
		$contrat->date_fin_preavis =$datectend;
		$contrat->montant_tot = GETPOST ( "montant_tot" );
		$contrat->loy = GETPOST ( "loy" );
		$contrat->charges = GETPOST ( "charges" );
		$contrat->tva = GETPOST ( "tva" );
		$contrat->periode = GETPOST ( "periode" );
		$contrat->depot = GETPOST ( "depot" );
		$contrat->commentaire = GETPOST ( "commentaire" );
		$contrat->proprietaire_id = GETPOST ( "proprietaire_id" );
		
		$e_contrat = $contrat;
		
		$res = $contrat->update ();
		
		if ($res >= 0) {
			setEventMessage ( $langs->trans ( "SocialContributionAdded" ), 'mesgs' );
		} else
			setEventMessage ( $contrat->error, 'errors' );
		
		header ( "Location: " . DOL_URL_ROOT . "/custom/immobilier/contrat/fiche_contrat.php?id=" . $contrat->id );
	
	}
}
/*
 * View
 *
 */

if (GETPOST ( "action" ) == 'create') {
	llxheader ( '', $langs->trans ( "addcontrat" ), '' );
	
	
	
	$nbligne = 0;
	
	print '<form name="add" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="25%">local</td>';
	print '<td>';
	print $htmlimmo->select_local($contrat->local_id, 'local_id');
	print '</td></tr>';
	print '<tr><td>locataire</td>';
	print '<td>';
	print $htmlimmo->select_locataire($contrat->locataire_id, 'locataire_id');
	print '</td></tr>';
	print '<tr><td>date</td>';
	print '<td align="left">';
	print $html->select_date ( ! empty ( $datect ) ? $datect : '-1', 'datect', 0, 0, 0, 'fiche_contrat', 1 );
	print '</td></tr>';
	print '<tr><td>montant</td>';
	print '<td><input name="montant_tot" size="30" value="' . $contrat->montant_tot . '"</td></tr>';
	print '<tr><td>loyer</td>';
	print '<td><input name="loy" size="70" value="' . $contrat->loy . '"</td></tr>';
	print '<tr><td>charges</td>';
	print '<td><input name="charges" size="10" value="' . $contrat->charges . '"</td></tr>';
	print '<tr><td>tva</td>';
	print '<td><input name="tva" size="10" value="' . $contrat->tva . '"</td></tr>';
	print '<tr><td>periode</td>';
	print '<td><input name="periode" size="10" value="' . $contrat->periode . '"</td></tr>';
	print '<tr><td>depot</td>';
	print '<td><input name="depot" size="10" value="' . $contrat->depot . '"</td></tr>';
	print '<tr><td>commentaire</td>';
	print '<td><input name="commentaire" size="10" value="' . $contrat->commentaire . '"</td></tr>';
	print '<tr><td>proprietaire</td>';
	print '<td><input name="proprietaire_id" size="10" value="' . $contrat->proprietaire_id . '"</td></tr>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

if ($id > 0) {
	
	llxheader ( '', $langs->trans ( "changecontrat" ), '' );
	
	$nbligne = 0;
	
	$contrat = new Rent ( $db );
	$result = $contrat->fetch ( $id );
	
	$head = contrat_prepare_head ( $contrat );
	
	dol_fiche_head ( $head, 'maininfo', $langs->trans ( "ImoContratDetail" ), 0, 'agreement' );
	
	print '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td>local</td>';
	print '<td>';
	print $htmlimmo->select_local($contrat->local_id, 'local_id');
	print '</td></tr>';

	print '<tr><td>locataire</td>';
	print '<td>' . $contrat->nomlocataire . '</td>';
	print '</tr>';
	print '<tr><td>date</td>';
	print '<td align="left">';
	print $html->select_date ( $contrat->date_entree, 'datect', 0, 0, 0, 'fiche_contrat', 1 );
	print '</td></tr>';
	print '<tr><td>date fin</td>';
	print '<td align="left">';
	print $html->select_date ( $contrat->date_fin_preavis, 'datectend', 0, 0, 0, 'fiche_contrat', 1 );
	print '</td></tr>';
	print '<tr><td>preavis</td>';
	if ($contrat->preavis) {
		$checked='checked="checked"';
	} else {
		$checked='';
	}
	print '<td><input type="checkbox" name="preavis" '.$checked.' value="1"></td></tr>';
	print '<tr><td>montant</td>';
	print '<td><input name="montant_tot" size="10" value="' . $contrat->montant_tot . '"</td></tr>';
	print '<tr><td>loyer</td>';
	print '<td><input name="loy" size="10" value="' . $contrat->loy . '"</td></tr>';
	print '<tr><td>charges</td>';
	print '<td><input name="charges" size="10" value="' . $contrat->charges . '"</td></tr>';
	print '<tr><td>tva</td>';
	print '<td><input name="tva" size="10" value="' . $contrat->tva . '"</td></tr>';
	print '<tr><td>periode</td>';
	print '<td><input name="periode" size="10" value="' . $contrat->periode . '"</td></tr>';
	print '<tr><td>depot</td>';
	print '<td><input name="depot" size="10" value="' . $contrat->depot . '"</td></tr>';
	print '<tr><td>commentaire</td>';
	print '<td><input name="commentaire" size="90" value="' . $contrat->commentaire . '"</td></tr>';
	print '<tr><td>proprietaire</td>';
	print '<td><input name="proprietaire_id" size="10" value="' . $contrat->proprietaire_id . '"</td></tr>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"><a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=outrenter">' . $langs->trans ( "outrenter" ) . '</a></td></tr>';

	
	print '</table>';
	print '</form>';
}

llxFooter();

$db->close();
