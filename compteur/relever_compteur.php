<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006 Laurent Destailleur  <eldy@users.sourceforge.net>
* Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
* Copyright (C) 2014      Florian HENRY      <forian.henry@open-concept.pro>
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
 * \file htdocs/custom/immobilier/relever_compteur.php
* \ingroup immobilier
* \brief Page fiche compteur
*/

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");


dol_include_once ( "/immobilier/class/local.class.php" );
dol_include_once ( "/immobilier/class/relever.class.php" );
dol_include_once ( "/immobilier/class/compteur.class.php" );
dol_include_once ( "/immobilier/class/compteur_local.class.php" );
dol_include_once ( '/immobilier/lib/immobilier.lib.php' );
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
 * Action
 */

if ($action=='addcompteur') {
	$nom_compteur=GETPOST('nom_compteur','alpha');
	$type_compteur=GETPOST('type_compteur','int');
	
	//Create Compteur
	$compteur = new Immocompteur($db);
	$compteur->label = $nom_compteur;
	$compteur->type = $type_compteur;
	$result=$compteur->create($user);
	if ($result<0) {
		setEventMessage($compteur->error,'errors');
	}
	
	//Link compteur to local
	$compteur_local = new Immocompteurlocal($db);
	$compteur_local->fk_local=$id;
	$compteur_local->fk_compteur = $compteur->id;
	$result=$compteur_local->create($user);
	if ($result<0) {
		setEventMessage($compteur_local->error,'errors');
	}
	
}elseif ($action=='addrelever') {
	$dt_relever = dol_mktime ( 0, 0, 0, GETPOST ( 'dtrelevmonth', 'int' ), GETPOST ( 'dtrelevday', 'int' ), GETPOST ( 'dtrelevyear', 'int' ) );
	$id_compteur=GETPOST('compteur','int');
	$indexcompteur=GETPOST('indexcompteur');
	$consocompteur=GETPOST('consocompteur');
	$commentcompteur=GETPOST('commentcompteur','alpha');
	
	$relever = new Immorelever($db);
	$relever->date_reveler=$dt_relever;
	$relever->index_reveler=$indexcompteur;
	$relever->consomation_relever=$consocompteur;
	$relever->comment_relever=$commentcompteur;
	$relever->fk_compteur_local=$id_compteur;
	$result=$relever->create($user);
	if ($result<0) {
		setEventMessage($relever->error,'errors');
	}
}elseif ($action=='deletereleverline') {
	
	$id_line= GETPOST('idline');
	
	$relever = new Immorelever($db);
	$result=$relever->fetch($id_line);
	if ($result<0) {
		setEventMessage($relever->error,'errors');
	}
	
	$result=$relever->delete($user);
	if ($result<0) {
		setEventMessage($relever->error,'errors');
	} else {
		Header ( "Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id );
	}
}


/*
 * View
 */


llxheader ( '', $langs->trans ( "ReleverCompteur" ), '' );

$local = new Local ( $db, $id);
$head = local_prepare_head ( $local );




$immeuble = new Immeuble($db);
$result = $immeuble->fetch($local->immeuble_id);
if ($result<0) {
	setEventMessage($immeuble->error,'errors');
}


$relever = new Immorelever($db);
$relever->fetch_all_by_local($id);
if ($result<0) {
	setEventMessage($relever->error,'errors');
}


dol_fiche_head ( $head, 'compteurrelever', $langs->trans ( "ReleverCompteur" ), 0, 'propertie' );


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

print_barre_liste ( $langs->trans ( "ReleverCompteur" ), "", "", "", "", "", '', 0 );

print '<table>';
print '<tr>';
//Gestion des compteurs
print '<td>';

//Ajouter un compteur
print '<form action="'.$_SERVER['SELF'].'" method="post">';
print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
print '<input type="hidden" name="action" value="addcompteur">';
print '<input type="hidden" name="id" value="' . $id . '">' . "\n";
print '<table>';
print '<tr><td>';
print $langs->trans('CreateCompteurForThisLocal');
print '</td></tr>';
print '<tr><td>';
print $htmlimmo->select_type_compteur('');
print '<input type="text" size="5" name="nom_compteur"/>';
print '<input type="submit" value="'.$langs->trans('Add').'"/>';
print '</td></tr>';
print '</table>';
print '</form>';

//Liste des compteurs
print '<table>';
print '<tr><td>';
print $langs->trans('ListCompteurForThisBulding');
print '</td></tr>';
$compteur_local = new Immocompteurlocal($db);
$result=$compteur_local->fetch_all_by_local($id);
if ($result<0) {
	setEventMessage($compteur_local->error,'errors');
}
foreach($compteur_local->lines as $line) {
	print '<tr><td>';
	print $line->type.'-'.$line->label;
	print '</td></tr>';
}
print '</table>';

print '</td>';

//Ajouter un relevé
print '<td>';
print $langs->trans('AddRelever');

print '<form action="'.$_SERVER['SELF'].'" method="post" name="addrelever">';
print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
print '<input type="hidden" name="action" value="addrelever">';
print '<input type="hidden" name="id" value="' . $id . '">' . "\n";
print '<table>';

print '<tr  class="liste_titre">';
print '<td>';
print $langs->trans('DateRelever');
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

print '<tr>';
print '<td>';
print $html->select_date( '', 'dtrelev', '', '', '', 'addrelever' );
print '</td>';
print '<td>';
print $htmlimmo->select_compteur_by_local('',$id,'compteur');
print '</td>';
print '<td>';
print '<input type="text" size="5" name="indexcompteur"/>';
print '</td>';
print '<td>';
print '<input type="text" size="5" name="consocompteur"/>';
print '</td>';
print '<td>';
print '<input type="text" size="20" name="commentcompteur"/>';
print '</td>';
print '<td>';
print '<input type="submit" value="'.$langs->trans('Add').'"/>';
print '</td>';
print '</tr>';

print '</table>';
print '</form>';


//Liste des relever existants
print '<table class="noborder">';

print '<tr class="liste_titre">';
print '<td>';
print $langs->trans('DateRelever');
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

$var = true;
if (is_array($relever->lines) && count($relever->lines)>0) {
	foreach($relever->lines as $line) {
		$var = ! $var;
		print '<tr '.$bc[$var].'>';
		print '<td>';
		print dol_print_date($line->date_reveler,'daytext');
		print '</td>';
		print '<td>';
		print $line->label_compteur;
		print '</td>';
		print '<td>';
		print $line->index_reveler;
		print '</td>';
		print '<td>';
		print $line->consomation_relever;
		print '</td>';
		print '<td>';
		print $line->comment_relever;
		print '</td>';
		print '<td>';
		print '<a href="'.$_SERVER['SELF'].'?action=deletereleverline&idline='. $line->id.'&id='.$id.'">DeleteLine</a>';
		print '</td>';
		print '</tr>';
	}
}

print '</table>';





print '</td>';

print '</tr>';
print '</table>';




$db->close ();

llxFooter ('');