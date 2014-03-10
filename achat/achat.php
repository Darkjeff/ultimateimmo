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
dol_include_once ( '/immobilier/class/html.immobilier.php' );

// Langs
$langs->load ( "immobilier@immobilier" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action' );

$html = new Form ( $db );
$htmlimmo = new FormImmobilier ( $db );

/*
 * Cr�ation d'un courrier
 *
 */


	llxheader ( '', $langs->trans ( "letter" ), '' );
	
	$html = new Form ( $db );
	$nbligne = 0;
	
	$locataire = new Locataire ( $db, GETPOST ( 'id' ) );
	
	$head = locataire_prepare_head ( $locataire );
	
	dol_fiche_head ( $head, 'letter', $langs->trans ( "letter" ), 0, 'renter' );
	
	print '<form action="fiche_locataire.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">'.$langs->trans("NomLocataire").'</td>';
	print '<td>' . $locataire->nom . '</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Address").'</td>';
	print '<td>' . $locataire->adresse . '</td></tr>';


	print '</table>';
	
	
print_barre_liste ( $langs->trans ( "ListLetter" ), "", "", "", "", "", '', 0 );

print '<table>';
print '<tr>';
//letters
print '<td>';

//add letter
print '<form action="'.$_SERVER['SELF'].'" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
print '<input type="hidden" name="action" value="addletter">';
print '<input type="hidden" name="id" value="' . $id . '">' . "\n";
print '<table>';
print '<tr><td>';
print $langs->trans('CreateLetterForThisRenter');
print '</td></tr>';
print '<tr><td>';
print $htmlimmo->select_type_letter('');
print '<td>';
print $langs->trans('Dateletter');
print '</td>';
print '<td>';
print $html->select_date( '', 'dtletter', '', '', '', 'addletter' );
print '</td><td>';

print '<input type="submit" value="'.$langs->trans('Add').'"/>';
print '</td></tr><tr></tr>';
print '</table>';
print '</form>';
	
//Liste letter existants
print '<table class="noborder">';

print '<tr class="liste_titre">';
print '<td>';
print $langs->trans('Dateletter');
print '</td>';
print '<td>';
print $langs->trans('Compteur');
print '</td>';
print '<td>';
print $langs->trans('IndexCompteur');
print '</td>';
print '<td>';
print $langs->trans('ConsoCompteur');
print '</td>';
print '<td>';
print $langs->trans('Commentaire');
print '</td>';
print '<td>';
print '</td>';
print '</tr>';

	
	



$db->close ();

llxFooter ('');
?>