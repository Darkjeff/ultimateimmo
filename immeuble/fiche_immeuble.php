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
	
dol_include_once ( "/immobilier/class/immeuble.class.php" );
dol_include_once ( '/immobilier/core/lib/immobilier.lib.php' );
dol_include_once ( '/immobilier/class/html.immobilier.php' );
dol_include_once('/core/lib/function.lib.php');
dol_include_once('/core/class/html.formcompany.class.php');
dol_include_once('/core/class/html.form.class.php');
dol_include_once('/core/class/doleditor.class.php');


// Langs
$langs->load ( "immobilier@immobilier" );

$mesg = '';
$id = GETPOST ( 'id', 'int' );
$action=GETPOST('action','alpha');

$html = new Form ( $db );
$htmlimmo = new FormImmobilier ( $db );

/*
 * Actions
 */


if (GETPOST ( "action" ) == 'add') {

	$immeuble = new Immeuble ( $db );
	
	
	$immeuble->nom = GETPOST ( "nom" );
	$immeuble->nblocaux = GETPOST ( "nblocaux" );
	
	
	$immeuble->numero = GETPOST ( "numero" );
	$immeuble->street = GETPOST ( "street" );
	$immeuble->zipcode = GETPOST ( "zipcode" );
	$immeuble->town = GETPOST ( "town" );
	$immeuble->longitude = GETPOST ( "longitude" );
	$immeuble->latitude = GETPOST ( "latitude" );
	
	$immeuble->statut = GETPOST ( "statut" );
	$immeuble->commentaire = GETPOST ( "commentaire" );
	
	
	$e_immeuble = $immeuble;
	
	$res = $immeuble->create ( $user );
	if ($res == 0) {
		Header ( "Location: " . DOL_URL_ROOT . "/immobilier/immeuble.php" );
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
print 'toto';
	$immeuble = new Immeuble ( $db, GETPOST ( 'id' ) );
	
	
		
	$immeuble->nom = GETPOST ( "nom" );
	$immeuble->nblocaux = GETPOST ( "nblocaux" );
	$immeuble->commentaire = GETPOST ( "commentaire" );
	$immeuble->statut = GETPOST ( "statut" );
	$immeuble->numero = GETPOST ( "numero" );
	$immeuble->street = GETPOST ( "street" );
	$immeuble->zipcode = GETPOST ( "zipcode" );
	$immeuble->town = GETPOST ( "town" );
	
	$immeuble->longitude = GETPOST ( "longitude" );
	$immeuble->latitude = GETPOST ( "latitude" );
	
	$e_immeuble = $immeuble;
	
	$res = $immeuble->update ();
	header ( "Location: " . DOL_URL_ROOT . "/immobilier/immeuble/fiche_immeuble.php?id=" . $immeuble->id );
	if ($res >= 0) {
		setEventMessage ( $langs->trans ( "SocialContributionAdded" ), 'mesgs' );
	} else
		dol_print_error ( $db );
}

/*
 * Cr�ation d'un immeuble
 */
	$formimmo = new FormImmobilier($db);
if ($action == 'create') {
	{   $title = ($action == 'create' ? $langs->trans("Creation Immeuble") : $langs->trans("Visu bail"));
llxHeader('',$title);
	

	
	$nbligne = 0;
	
	print '<form action="fiche_immeuble.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	
	print '<table class="border" width="100%">';
	


	
	print '<tr><td width="20%">'.$langs->trans("nom").'</td>';
	print '<td><input name="nom" size="70" value="' . $immeuble->nom . '"</td>';
	print '<td width="20%">'.$langs->trans("nblocaux").'</td>';
	print '<td><input name="nblocaux" size="10" value="' . $immeuble->nblocaux . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("numero").'</td>';
  print '<td><input name="numero" size="10" value="' . $immeuble->numero . '"</td>';
  print '<td width="20%">'.$langs->trans("street").'</td>';
  print '<td><input name="street" size="80" value="' . $immeuble->street . '"</td></tr>';
 
 print '<td>'.$langs->trans('CP').'</td><td>';
               print $formimmo->select_depville($immeuble->fk_departement,'zipcode',array('town','selectcountry_id'),6).'';
               
			    print '</td><td>'.$langs->trans('Ville').'</td><td>';
               
               print $formimmo->select_depville($immeuble->town,'town',array('zipcode','selectcountry_id')).'</tr>';
/*
                print '<tr><td>'.$langs->trans("Pays").'</td>';
                print '<td>'.$form->select_pays($immeuble->fk_pays,'country_id').'</td></tr>';
 */
 
 print '<tr><td width="20%">'.$langs->trans("longitude").'</td>';
  print '<td><input name="longitude" size="10" value="' . $immeuble->longitude . '"</td>';
  print '<td width="20%">'.$langs->trans("latitude").'</td>';
  print '<td><input name="latitude" size="10" value="' . $immeuble->latitude . '"</td></tr>';
 
 
 
  print '<tr><td width="20%">'.$langs->trans("commentaire").'</td>';
  print '<td><input name="commentaire" size="80" value="' . $immeuble->commentaire . '"</td></tr>';
	
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';
} 
} 

elseif ($action == 'update') {
	llxheader ( '', $langs->trans ( "changeImmeuble" ), '' );
	

	$nbligne = 0;
	
	$immeuble = new Immeuble ( $db, GETPOST ( 'id' ) );
	
	$head = immeuble_prepare_head ( $immeuble );
	
	dol_fiche_head ( $head, 'maininfo', $langs->trans ( "ImoImmeubleDetail" ), 0, 'propertie' );
	
	print '<form action="fiche_immeuble.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST ( "id" ) . '">' . "\n";
	
	print '<table class="border" width="100%">';
	

	print '<tr><td width="20%">'.$langs->trans("nom").'</td>';
	print '<td><input name="nom" size="70" value="' . $immeuble->nom . '"</td>';
	print '<td width="20%">'.$langs->trans("nblocaux").'</td>';
	print '<td><input name="nblocaux" size="10" value="' . $immeuble->nblocaux . '"</td></tr>';
	print '<tr><td width="20%">'.$langs->trans("numero").'</td>';
  print '<td><input name="numero" size="10" value="' . $immeuble->numero . '"</td>';
  print '<td width="20%">'.$langs->trans("street").'</td>';
  print '<td><input name="street" size="80" value="' . $immeuble->street . '"</td></tr>';
 
 print '<td>'.$langs->trans('CP').'</td><td>';
               print $formimmo->select_depville($immeuble->fk_departement,'zipcode',array('town','selectcountry_id'),6).'';
               
			    print '</td><td>'.$langs->trans('Ville').'</td><td>';
               
               print $formimmo->select_depville($immeuble->town,'town',array('zipcode','selectcountry_id')).'</tr>';
/*
                print '<tr><td>'.$langs->trans("Pays").'</td>';
                print '<td>'.$form->select_pays($immeuble->fk_pays,'country_id').'</td></tr>';
 */
 
 print '<tr><td width="20%">'.$langs->trans("Statut").'</td>';
	print '<td>';
	print $html->selectarray('statut', $immeuble->status_array, $immeuble->statut);
	print '</td></tr>';
 
  print '<tr><td width="20%">'.$langs->trans("longitude").'</td>';
  print '<td><input name="longitude" size="10" value="' . $immeuble->longitude . '"</td>';
  print '<td width="20%">'.$langs->trans("latitude").'</td>';
  print '<td><input name="latitude" size="10" value="' . $immeuble->latitude . '"</td></tr>';
 
  print '<tr><td width="20%">'.$langs->trans("commentaire").'</td>';
  print '<td><input name="commentaire" size="80" value="' . $immeuble->commentaire . '"</td></tr>';
	
	
	print '<tr><td>&nbsp;</td><td><input type="submit" class="button" value="' . $langs->trans ( "Sauvegarder" ) . '"><input type="cancel" class="button" value="' . $langs->trans ( "Cancel" ) . '"></td></tr>';
	
	print '</table>';
	print '</form>';

}else {

llxheader ( '', $langs->trans ( "changeImmeuble" ), '' );
	

	$nbligne = 0;
	
	$immeuble = new Immeuble ( $db, GETPOST ( 'id' ) );
	
	$head = immeuble_prepare_head ( $immeuble );
	
	dol_fiche_head ( $head, 'maininfo', $langs->trans ( "ImoImmeubleDetail" ), 0, 'propertie' );
	
	


}
$db->close ();

?>
