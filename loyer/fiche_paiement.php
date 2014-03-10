<?PHP
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
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

dol_include_once ( "/immobilier/class/paie.class.php" );
dol_include_once ( "/immobilier/class/loyer.class.php" );

// Langs
$langs->load ( "immobilier@immobilier" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action' );

// Actions

if (GETPOST ( "action" ) == 'add') {
	$datepaie = @dol_mktime ( $_POST ["paiehour"], $_POST ["paiemin"], $_POST ["paiesec"], $_POST ["paiemonth"], $_POST ["paieday"], $_POST ["paieyear"] );
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans ( "ErrorFieldRequired", $langs->transnoentities ( "Datepaie" ) ) . '</div>';
		$action = 'create';
	} else {
		$paie = new Paie ( $db );
		
		$paie->contrat_id = $_POST ["contrat_id"];
		$paie->local_id = $_POST ["local_id"];
		$paie->locataire_id = $_POST ["locataire_id"];
		$paie->montant = $_POST ["montant"];
		$paie->commentaire = $_POST ["commentaire"];
		$paie->date_paiement = $datepaie;
		$paie->loyer_id = $_POST ["loyer_id"];
		
		$id = $paie->create ( $user );
		header ( "Location: " . DOL_URL_ROOT . "/immobilier/loyer/fiche_loyer.php?id=" . $paie->loyer_id );
		if ($id > 0) {
		} else {
			$mesg = '<div class="error">' . $paie->error . '</div>';
		}
	}
}

if (GETPOST ( "action" ) == 'maj') {
	$datepaie = @dol_mktime ( $_POST ["paiehour"], $_POST ["paiemin"], $_POST ["paiesec"], $_POST ["paiemonth"], $_POST ["paieday"], $_POST ["paieyear"] );
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans ( "ErrorFieldRequired", $langs->transnoentities ( "Datepaie" ) ) . '</div>';
		$action = 'update';
	} else {
		$paie = new Paie ( $db );
		
		$result = $paie->fetch ( $id );
		
		$paie->montant = $_POST ["montant"];
		$paie->commentaire = $_POST ["commentaire"];
		$paie->date_paiement = $datepaie;
		
		$result = $paie->update ( $user );
		header ( "Location: " . DOL_URL_ROOT . "/immobilier/loyer/fiche_loyer.php?id=" . $paie->loyer_id );
		if ($id > 0) {
		} else {
			$mesg = '<div class="error">' . $paie->error . '</div>';
		}
	}
}

/*
 * View
 *
 */

$form = new Form ( $db );

llxheader ( '', $langs->trans ( "newpaiement" ), '' );

if (GETPOST ( "action" ) == 'create') {
	$loyer = new Loyer ( $db );
	$result = $loyer->fetch ( $id );
	
	print '<form action="fiche_paiement.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<input type="hidden" name="contrat_id" size="10" value="' . $loyer->contrat_id . '">';
	print '<input type="hidden" name="local_id" size="10" value="' . $loyer->local_id . '">';
	print '<input type="hidden" name="locataire_id" size="10" value="' . $loyer->locataire_id . '">';
	print '<input type="hidden" name="loyer_id" size="10" value="' . $id . '">';
	
	print '<tr><td width="20%">' . $langs->trans ( "NomAppartement" ) . '</td><td>' . $loyer->nomlocal . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "NomLocataire" ) . '</td><td>' . $loyer->nomlocataire . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "RefLoyer" ) . '</td><td>' . $loyer->nom . '</td></tr>';
	;
	
	print '<tr><td width="20%">' . $langs->trans ( "Montant" ) . '</td>';
	print '<td><input name="montant" size="30" value="' . $paie->montant . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "Commentaire" ) . '</td>';
	print '<td><input name="commentaire" size="10" value="' . $paie->commentaire . '"</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "DatePaiement" ) . '</td>';
	print '<td align="left">';
	print $form->select_date ( ! empty ( $datepaie ) ? $datepaie : '-1', 'paie', 0, 0, 0, 'fiche_paiement', 1 );
	print '</td>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */
if (GETPOST ( "action" ) == 'update') {
	$paie = new Paie ( $db );
	$result = $paie->fetch ( $id );
	
	print '<form action="fiche_paiement.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">' . $langs->trans ( "NomLoyer" ) . '</td><td>' . $paie->nomloyer . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "NomAppartement" ) . '</td><td>' . $paie->nomlocal . '</td></tr>';
	print '<tr><td width="20%">' . $langs->trans ( "NomLocataire" ) . '</td><td>' . $paie->nomlocataire . '</td></tr>';
	
	print '<tr><td width="20%">montant </td>';
	print '<td><input name="montant" size="30" value="' . $paie->montant . '"</td></tr>';
	print '<tr><td width="20%">Commentaire </td>';
	print '<td><input name="commentaire" size="50" value="' . $paie->commentaire . '"</td></tr>';
	print '<tr><td width="20%">date_paiement</td>';
	print '<td align="left">';
	print $form->select_date ( $paie->date_paiement, 'paie', 0, 0, 0, 'fiche_paiement', 1 );
	print '</td>';
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

$db->close ();

llxFooter ( "<em>Derni&egrave;re modification $Date: 2010/01/28 13:27:35 $ r&eacute;vision $Revision: 1.12 $</em>" );
?>
