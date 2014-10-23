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

	// class
	
dol_include_once ( "/immobilier/class/charge.class.php" );
dol_include_once ( '/immobilier/lib/immobilier.lib.php' );
dol_include_once ( '/immobilier/class/html.immobilier.php' );

// langs

$langs->load ( "immobilier@immobilier" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action' );

$html = new Form ( $db );
$htmlimmo = new FormImmobilier ( $db );

if (GETPOST ( "action" ) == 'add') {
	
	$dateacq = @dol_mktime ( $_POST ["acqhour"], $_POST ["acqmin"], $_POST ["acqsec"], $_POST ["acqmonth"], $_POST ["acqday"], $_POST ["acqyear"] );
	$datedu = @dol_mktime ( $_POST ["duhour"], $_POST ["dumin"], $_POST ["dusec"], $_POST ["dumonth"], $_POST ["duday"], $_POST ["duyear"] );
	$dateau = @dol_mktime ( $_POST ["auhour"], $_POST ["aumin"], $_POST ["ausec"], $_POST ["aumonth"], $_POST ["auday"], $_POST ["auyear"] );
	
	$charge = new Charge ( $db );
	
	$charge->local_id = GETPOST ( "local_id" );
	$charge->libelle = GETPOST ( "libelle" );
	
	$fournisseur = GETPOST ( "fournisseur" );
	$nouveau_fournisseur = GETPOST ( "nouveau_fournisseur" );
	
	if (! empty ( $nouveau_fournisseur )) {
		$charge->fournisseur = $nouveau_fournisseur;
	} elseif (! empty ( $fournisseur )) {
		$charge->fournisseur = $fournisseur;
	}
	$charge->local_id = GETPOST ( "local_id" );
	$charge->type = GETPOST ( "type" );
	$charge->montant_ttc = GETPOST ( "montant_ttc" );
	$charge->date_acq = $dateacq;
	$charge->periode_du = $datedu;
	$charge->periode_au = $dateau;
	$charge->proprietaire_id = GETPOST ( "proprietaire_id" );
	
	$e_charge = $charge;
	
	
	$res = $charge->create ( $user );
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
	Header ( "Location: " . DOL_URL_ROOT . "/immobilier/charges.php" );
} elseif (GETPOST ( "action" ) == 'maj') {
	
	$error = 0;
	
	$dateacq = @dol_mktime ( $_POST ["acqhour"], $_POST ["acqmin"], $_POST ["acqsec"], $_POST ["acqmonth"], $_POST ["acqday"], $_POST ["acqyear"] );
	$datedu = @dol_mktime ( $_POST ["duhour"], $_POST ["dumin"], $_POST ["dusec"], $_POST ["dumonth"], $_POST ["duday"], $_POST ["duyear"] );
	$dateau = @dol_mktime ( $_POST ["auhour"], $_POST ["aumin"], $_POST ["ausec"], $_POST ["aumonth"], $_POST ["auday"], $_POST ["auyear"] );
	
	$fournisseur = GETPOST ( "fournisseur" );
	$nouveau_fournisseur = GETPOST ( "nouveau_fournisseur" );
	
	if (! $error) {
		$charge = new Charge ( $db );
		$charge->fetch ( $id );
		
		$charge->local_id = GETPOST ( "local_id" );
		$charge->libelle = GETPOST ( "libelle" );
		// $charge->fournisseur = GETPOST ( "fournisseur" );
		// $charge->nouveau_fournisseur = GETPOST ( "nouveau_fournisseur" );
		
		if (! empty ( $nouveau_fournisseur )) {
			$charge->fournisseur = $nouveau_fournisseur;
		} elseif (! empty ( $fournisseur )) {
			$charge->fournisseur = $fournisseur;
		}
		
		$charge->local_id = GETPOST ( "local_id" );
		$charge->type = GETPOST ( "type" );
		$charge->montant_ttc = GETPOST ( "montant_ttc" );
		$charge->date_acq = $dateacq;
		$charge->periode_du = $datedu;
		$charge->periode_au = $dateau;
		$charge->proprietaire_id = GETPOST ( "proprietaire_id" );
		
		$e_charge = $charge;
		
		$res = $charge->update ();
		
		if ($res >= 0) {
			setEventMessage ( $langs->trans ( "SocialContributionAdded" ), 'mesgs' );
		} else
			setEventMessage ( $charge->error, 'errors' );
		
		header ( "Location: " . DOL_URL_ROOT . "/immobilier/charge/fiche_charge.php?id=" . $charge->id );
	}
}
/*
 * View
 *
 */

if (GETPOST ( "action" ) == 'create') {
	
	llxheader ( '', $langs->trans ( "addcharge" ), '' );
	
	$nbligne = 0;
	
	print '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">libelle</td>';
	print '<td><input name="libelle" size="30" value="' . $charge->libelle . '"</td></tr>';
	print '<tr><td width="20%">fournisseur</td>';
	print '<td>';
	print $htmlimmo->select_fournisseur ( $charge->fournisseur, 'fournisseur', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">ou nouveau_fournisseur</td>';
	print '<td><input name="nouveau_fournisseur" size="30" value="' . $charge->nouveau_fournisseur . '"</td></tr>';
	print '<tr><td width="20%">local</td>';
	print '<td>';
	print $htmlimmo->select_propertie ( $charge->local_id, 'local_id' );
	print '</td></tr>';
	print '<td width="20%">type</td>';
	print '<td>';
	print $htmlimmo->select_type ( $charge->type, 'type' );
	print '</td></tr>';
	print '<tr><td width="20%">montant_ttc</td>';
	print '<td><input name="montant_ttc" size="30" value="' . $charge->montant_ttc . '"</td></tr>';
	print '<tr><td width="20%">date</td>';
	print '<td align="left">';
	print $html->select_date ( ! empty ( $dateacq ) ? $dateacq : '-1', 'acq', 0, 0, 0, 'fiche_charge', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">periode_du</td>';
	print '<td align="left">';
	print $html->select_date ( ! empty ( $datedu ) ? $datedu : '-1', 'du', 0, 0, 0, 'fiche_charge', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">periode_au</td>';
	print '<td align="left">';
	print $html->select_date ( ! empty ( $dateau ) ? $dateau : '-1', 'au', 0, 0, 0, 'fiche_charge', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">commentaire</td>';
	print '<td><input name="commentaire" size="120" value="' . $contrat->commentaire . '"</td></tr>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

if ($id > 0) {
	
	llxheader ( '', $langs->trans ( "changecharge" ), '' );
	
	$nbligne = 0;
	
	$charge = new Charge ( $db );
	$result = $charge->fetch ( $id );
	
	// $head = contrat_prepare_head ( $contrat );
	
	// dol_fiche_head ( $head, 'maininfo', $langs->trans ( "ImoContratDetail" ), 0, 'agreement' );
	
	print '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">libelle</td>';
	print '<td><input name="libelle" size="30" value="' . $charge->libelle . '"</td></tr>';
	print '<tr><td width="20%">fournisseur</td>';
	print '<td>';
	print $htmlimmo->select_fournisseur ( $charge->fournisseur, 'fournisseur', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">nouveau_fournisseur</td>';
	print '<td><input name="nouveau_fournisseur" size="30" value="' . $charge->nouveau_fournisseur . '"</td></tr>';
	print '<tr><td width="20%">local</td>';
	print '<td>';
	print $htmlimmo->select_propertie ( $charge->local_id, 'local_id' );
	print '</td></tr>';
	print '<tr><td width="20%">type</td>';
	print '<td>';
	print $htmlimmo->select_type ( $charge->type, 'type' );
	print '</td></tr>';
	
	print '<tr><td width="20%">montant_ttc</td>';
	print '<td><input name="montant_ttc" size="30" value="' . $charge->montant_ttc . '"</td></tr>';
	print '<tr><td width="20%">date</td>';
	print '<td align="left">';
	print $html->select_date ( $charge->date_acq, 'acq', 0, 0, 0, 'fiche_charge', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">periode_du</td>';
	print '<td align="left">';
	print $html->select_date ( $charge->periode_du, 'du', 0, 0, 0, 'fiche_charge', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">periode_au</td>';
	print '<td align="left">';
	print $html->select_date ( $charge->periode_au, 'au', 0, 0, 0, 'fiche_charge', 1 );
	print '</td></tr>';
	print '<tr><td width="20%">commentaire</td>';
	print '<td><input name="commentaire" size="80" value="' . $contrat->commentaire . '"</td></tr>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

llxFooter ( '' );
$db->close ();
?>
