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

dol_include_once ( "/immobilier/class/local.class.php" );
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


	llxheader ( '', $langs->trans ( "DPE" ), '' );
	
	$html = new Form ( $db );
	$nbligne = 0;
	
	$local = new Local ( $db, GETPOST ( 'id' ) );
	
	$head = local_prepare_head ( $local );
	
	dol_fiche_head ( $head, 'DPE', $langs->trans ( "DPE" ), 0, 'DPE' );
	
	print '<form action="fiche_locataire.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">'.$langs->trans("Nom").'</td>';
	print '<td>' . $local->nom . '</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("Superficie").'</td>';
	print '<td>' . $local->superficie . '</td></tr>';


print '<tr><td width="20%">'.$langs->trans("DateDPE").'</td>';
	print '<td><input name="date_dpe" size="10" value="' . $local->date_dpe . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("dpe_ep").'</td>';
	print '<td><input name="dpe_ep" size="10" value="' . $local->dpe_ep . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("dpe_ges").'</td>';
	print '<td><input name="dpe_ges" size="10" value="' . $local->dpe_ges . '"</td></tr>';
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';


	print '</table>';

		print '</form>';

	



$db->close ();

llxFooter ('');
?>