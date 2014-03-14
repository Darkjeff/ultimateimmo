<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
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
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

/**
 * \file $HeadURL: https://192.168.22.4/dolidev/trunk/agefodd/lib/agefodd.lib.php $
 * \brief Page fiche d'une operation sur CCA
 * \version		$Id$
 */
$langs->load('immobilier@immobilier');

/**
 * Return head table for training tabs screen
 *
 * @param object $object training
 * @return array head table of tabs
 */
function locataire_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/locataire/fiche_locataire.php', 1) . '?action=update&id=' . $object->id;
	$head[$h][1] = $langs->trans("maininfo");
	$head[$h][2] = 'maininfo';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/locataire/document.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("document");
	$head[$h][2] = 'document';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/letter/letter_by_properties.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("letter");
	$head[$h][2] = 'letter';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/locataire/bilan_locataire.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_locataire');
	
	return $head;
}
function mandat_prepare_head($object)
{
    global $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/immobilier/mandat/fiche.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("fiche");
    $head[$h][2] = 'fiche';
    $h++;
    
    $head[$h][0] = dol_buildpath('/immobilier/mandat/document.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("documents");
    $head[$h][2] = 'documents';
    $h++;

    $head[$h][0] = dol_buildpath('/immobilier/mandat/liste.php',1).'?site_view=1&search_site='.$object->id;
    $head[$h][1] = $langs->trans("liste");
    $head[$h][2] = 'liste';
    $h++;

    $head[$h][0] = dol_buildpath('/imobilier/mandat/info.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Info");
    $head[$h][2] = 'info';
    $h++;

    //complete_head_from_modules($conf,$langs,$object,$head,$h,'agefodd_site');

    return $head;
}
function contrat_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/contrat/fiche_contrat.php', 1) . '?action=update&id=' . $object->id;
	$head[$h][1] = $langs->trans("maininfo");
	$head[$h][2] = 'maininfo';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/contrat/document.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("document");
	$head[$h][2] = 'document';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/contrat/bilan_contrat.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_contrat');
	
	return $head;
}

function local_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/local/fiche_local.php', 1) . '?action=update&id=' . $object->id;
	$head[$h][1] = $langs->trans("maininfo");
	$head[$h][2] = 'maininfo';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/local/document.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("document");
	$head[$h][2] = 'document';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/local/photos.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Photo");
	$head[$h][2] = 'photo';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/compteur/relever_compteur.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("ReleverCompteur");
	$head[$h][2] = 'compteurrelever';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/DPE/dpe.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("DPE");
	$head[$h][2] = 'DPE';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/local/bilan_local.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_local');
	
	return $head;
}

function immeuble_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/immeuble/fiche_immeuble.php', 1) . '?action=update&id=' . $object->id;
	$head[$h][1] = $langs->trans("Fiche");
	$head[$h][2] = 'fiche';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/achat/achat.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Achat");
	$head[$h][2] = 'Achat';
	$hselected = $h;
	$h ++;
	
	
	$head[$h][0] = dol_buildpath('/immobilier/DPE/dpe.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("DPE");
	$head[$h][2] = 'DPE';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/immeuble/document.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("document");
	$head[$h][2] = 'document';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/immeuble/bilan_immeuble.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_immeuble');
	
	return $head;
}

?>
