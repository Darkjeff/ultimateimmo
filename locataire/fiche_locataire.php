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

dol_include_once ( "/immobilier/class/locataire.class.php" );
dol_include_once ( '/immobilier/lib/immobilier.lib.php' );

// Langs
$langs->load ( "immobilier@immobilier" );

$mesg = '';
$action = GETPOST ( 'action' );




if (GETPOST ( "action" ) == 'add') {
	$locataire = new Locataire ( $db );
	
	$locataire->nom = GETPOST ( "nom" );
	$locataire->telephone = GETPOST ( "telephone" );
	$locataire->email = GETPOST ( "email" );
	$locataire->adresse = GETPOST ( "adresse" );
	$locataire->commentaire = GETPOST ( "commentaire" );
	$locataire->statut = GETPOST ( "statut" );
	
	
	$e_local = $locataire;
	
	$res = $locataire->create ($user);
	if ($res == 0) {
		Header ( "Location: " . DOL_URL_ROOT . "/immobilier/locataire.php" );
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
} elseif (GETPOST ( "action" ) == 'maj') {
	$locataire = new Locataire ( $db, GETPOST ( 'id' ) );
	
	$locataire->nom = GETPOST ( "nom" );
	$locataire->telephone = GETPOST ( "telephone" );
	$locataire->email = GETPOST ( "email" );
	$locataire->adresse = GETPOST ( "adresse" );
	$locataire->commentaire = GETPOST ( "commentaire" );
	$locataire->statut = GETPOST ( "statut" );

	
	$e_locataire = $locataire;
	
	$res = $locataire->update ($user);
	if ($res >= 0) {
		Header ( "Location: " . DOL_URL_ROOT . "/immobilier/locataire.php" );
	} else
		$action = 'update';
}

/*
 * Cr�ation d'un locataire
 *
 */

if ($action == 'create') {
	llxheader ( '', $langs->trans ( "addlocataire" ), '' );
	
	$html = new Form ( $db );
	$nbligne = 0;
	
	print '<form action="fiche_locataire.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">Nom du locataire</td>';
	print '<td><input name="nom" size="40" value="' . $locataire->nom . '"</td></tr>';
	print '<tr><td width="20%">telephone</td>';
	print '<td><input name="telephone" size="15" value="' . $locataire->telephone . '"</td></tr>';
	print '<tr><td width="20%">email</td>';
	print '<td><input name="email" size="10" value="' . $locataire->email . '"</td></tr>';
	print '<tr><td width="20%">Adresse </td>';
	print '<td><input name="adresse" size="30" value="' . $locataire->adresse . '"</td></tr>';
	print '<tr><td width="20%">Commentaire </td>';
	print '<td><input name="commentaire" size="70" value="' . $locataire->commentaire . '"</td></tr>';
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
} 

elseif ($action == 'update') {
	llxheader ( '', $langs->trans ( "changelocataire" ), '' );
	
	$html = new Form ( $db );
	$nbligne = 0;
	
	$locataire = new Locataire ( $db, GETPOST ( 'id' ) );
	
	$head = locataire_prepare_head ( $locataire );
	
	dol_fiche_head ( $head, 'maininfo', $langs->trans ( "ImoLocataireDetail" ), 0, 'renter' );
	
	print '<form action="fiche_locataire.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">Nom du locataire</td>';
	print '<td><input name="nom" size="40" value="' . $locataire->nom . '"</td></tr>';
	print '<tr><td width="20%">telephone</td>';
	print '<td><input name="telephone" size="15" value="' . $locataire->telephone . '"</td></tr>';
	print '<tr><td width="20%">email</td>';
	print '<td><input name="email" size="10" value="' . $locataire->email . '"</td></tr>';
	print '<tr><td width="20%">Adresse </td>';
	print '<td><input name="adresse" size="30" value="' . $locataire->adresse . '"</td></tr>';
	print '<tr><td width="20%">Commentaire </td>';
	print '<td><input name="commentaire" size="70" value="' . $locataire->commentaire . '"</td></tr>';
	print '<tr><td width="20%">statut</td>';
	print '<td>';
	print $html->selectarray('statut', $locataire->status_array, $locataire->statut);
	print '</td></tr>';
	
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
}

$db->close ();

llxFooter ('');
?>
