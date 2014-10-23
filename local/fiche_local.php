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
 * \file htdocs/custom/immobilier/fiche_local.php
 * \ingroup immobilier
 * \brief Page fiche local
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");
	

dol_include_once ( "/immobilier/class/local.class.php" );
dol_include_once ( '/immobilier/core/lib/immobilier.lib.php' );
dol_include_once ( '/immobilier/class/html.immobilier.php' );
dol_include_once ( '/immobilier/class/immeuble.class.php' );

// Langs
$langs->load ( "immobilier@immobilier" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action' );

$html = new Form ( $db );
$htmlimmo = new FormImmobilier ( $db );

/*
 * Actions
 */


if (GETPOST ( "action" ) == 'add') {
	$local = new Local ( $db );
	
	$local->nom = GETPOST ( "nom" );
	$local->adresse = GETPOST ( "adresse" );
	$local->commentaire = GETPOST ( "commentaire" );
	$local->statut = GETPOST ( "statut" );
	$local->immeuble_id = GETPOST ( "immeuble_id" );
	
	$e_local = $local;
	
	$res = $local->create ( $user );
	if ($res == 0) {
		Header ( "Location: " . DOL_URL_ROOT . "/immobilier/local.php" );
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
	$local = new Local ( $db, GETPOST ( 'id' ) );
	
	$local->nom = GETPOST ( "nom" );
	$local->adresse = GETPOST ( "adresse" );
	$local->commentaire = GETPOST ( "commentaire" );
	$local->statut = GETPOST ( "statut" );
	$local->immeuble_id = GETPOST ( "immeuble_id" );
	
	$e_local = $local;
	
	$res = $local->update ();
	header ( "Location: " . DOL_URL_ROOT . "/immobilier/local/fiche_local.php?id=" . $local->id );
	if ($res >= 0) {
		setEventMessage ( $langs->trans ( "SocialContributionAdded" ), 'mesgs' );
	} else
		dol_print_error ( $db );
}

/*
 * Cr�ation d'un local
 *
 */

if ($action == 'create') {
	llxheader ( '', $langs->trans ( "addpropertie" ), '' );
	
	
	$nbligne = 0;
	
	print '<form action="fiche_local.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">'.$langs->trans("NomLocal").'</td>';
	print '<td><input name="nom" size="30" value="' . $local->nom . '"</td>';
	print '<td width="20%">'.$langs->trans("Immeuble").'</td>';
	print '<td>';
	print $htmlimmo->select_immeuble($local->immeuble_id, 'immeuble_id');
	print '</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Adresse").'</td>';
	print '<td><input name="adresse" size="30" value="' . $local->adresse . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("superficie").'</td>';
	print '<td><input name="superficie" size="10" value="' . $local->superficie . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("type").'</td>';
	print '<td><input name="type" size="10" value="' . $local->type . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Commentaire").'</td>';
	print '<td><input name="commentaire" size="10" value="' . $local->commentaire . '"</td></tr>';
	
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
} 

elseif ($action == 'update') {
	llxheader ( '', $langs->trans ( "changepropertie" ), '' );
	

	$nbligne = 0;
	
	$local = new Local ( $db, GETPOST ( 'id' ) );
	
	$head = local_prepare_head ( $local );
		$immeuble = new Immeuble($db);
	$result = $immeuble->fetch($local->immeuble_id);
	if ($result<0) {
		setEventMessage($immeuble->error,'errors');
	}
	
	dol_fiche_head ( $head, 'maininfo', $langs->trans ( "ImoLocalDetail" ), 0, 'propertie' );
	
	print '<form action="fiche_local.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">'.$langs->trans("NomLocal").'</td>';
	print '<td><input name="nom" size="30" value="' . $local->nom . '"</td>';
	print '<td width="20%">'.$langs->trans("Immeuble").'</td>';
	print '<td>';
	print $htmlimmo->select_immeuble($local->immeuble_id, 'immeuble_id');
	print '</td></tr>';
	
	
	
  print '<tr><td width="20%">'.$langs->trans("superficie").'</td>';
	print '<td><input name="superficie" size="10" value="' . $local->superficie . '"</td>';
	print '<td>' . $immeuble->numero . ' ' . $immeuble->street . '</td>';
	print '<td>' . $immeuble->zipcode . ' ' . $immeuble->town . '</td></tr>';

	print '<tr><td width="20%">'.$langs->trans("type").'</td>';
	print '<td><input name="type" size="10" value="' . $local->type . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Commentaire").'</td>';
	print '<td><input name="commentaire" size="10" value="' . $local->commentaire . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Statut").'</td>';
	print '<td>';
	print $html->selectarray('statut', $local->status_array, $local->statut);
	print '</td></tr>';
	
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
} else {
	
	llxheader ( '', $langs->trans ( "ImoLocalDetail" ), '' );
	
	$local = new Local ( $db, GETPOST ( 'id' ) );
	
	$head = local_prepare_head ( $local );
	dol_fiche_head ( $head, 'maininfo', $langs->trans ( "ImoLocalDetail" ), 0, 'propertie' );
	
	
	
	
	$immeuble = new Immeuble($db);
	$result = $immeuble->fetch($local->immeuble_id);
	if ($result<0) {
		setEventMessage($immeuble->error,'errors');
	}
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">'.$langs->trans("NomLocal").'</td>';
	print '<td>' . $local->nom . '</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Adresse").'</td>';
	print '<td>' . $local->adresse . '</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Commentaire").'</td>';
	print '<td>'.$local->commentaire . '</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Statut").'</td>';
	print '<td>'.$local->statut.'</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Immeuble").'</td>';
	print '<td>'.$immeuble->nom.'</td></tr>';
	print '</table>';
}

$db->close ();

llxFooter ('');
