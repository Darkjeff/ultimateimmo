<?php
/* Copyright (C) 2013 		Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016  Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file    htdocs/custom/immobilier/core/lib/immobilier.lib.php
 * \ingroup Immobilier
 * \brief   Library of immobilier 
 */
$langs->load('immobilier@immobilier');

/**
 * Prepare array with renters list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function renter_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/renter/card.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/renter/bank.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Bank");
	$head[$h][2] = 'bank';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/renter/bilan.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Bilan");
	$head[$h][2] = 'bilan';
	$hselected = $h;
	$h ++;
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    $upload_dir = $conf->immobilier->dir_output . '/renter/' . $object->id;
    $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
    $head[$h][0] = dol_buildpath('/immobilier/renter/document.php', 1) . '?id=' . $object->id;
    $head[$h][1] = $langs->trans("Documents");
	if($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
    $head[$h][2] = 'document';
    $h++;

	$head[$h][0] = dol_buildpath('/immobilier/property/letter_by_properties.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Letter");
	$head[$h][2] = 'letter';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/renter/info.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_renter');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_renter','remove');

	return $head;
}

/**
 * Prepare array with rents list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function rent_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/rent/card.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$hselected = $h;
	$h ++;
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    $upload_dir = $conf->immobilier->dir_output . '/rent/' . $object->id;
    $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
    $head[$h][0] = dol_buildpath('/immobilier/rent/document.php', 1) . '?id=' . $object->id;
    $head[$h][1] = $langs->trans("Documents");
	if($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
    $head[$h][2] = 'document';
    $h++;
	
	$head[$h][0] = dol_buildpath('/immobilier/rent/info.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_rent');
	
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

/**
 * Prepare array with properties list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function property_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/property/card.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$hselected = $h;
	$h ++;
	
	/*
	$head[$h][0] = dol_buildpath('/immobilier/achat/achat.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Achat");
	$head[$h][2] = 'Achat';
	$hselected = $h;
	$h ++;
	*/
	
	$head[$h][0] = dol_buildpath('/immobilier/property/dpe.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("DPE");
	$head[$h][2] = 'dpe';
	$hselected = $h;
	$h ++;
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    $upload_dir = $conf->immobilier->dir_output . '/property/' . $object->id;
    $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
    $head[$h][0] = dol_buildpath('/immobilier/property/document.php', 1) . '?id=' . $object->id;
    $head[$h][1] = $langs->trans("Documents");
	if($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
    $head[$h][2] = 'document';
    $h++;

	$head[$h][0] = dol_buildpath('/immobilier/property/info.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;

	complete_head_from_modules($conf,$langs,$object,$head,$h,'immobilier_property');

	complete_head_from_modules($conf,$langs,$object,$head,$h,'immobilier_property','remove');

	return $head;
}

function charge_prepare_head($object)
{
	global $langs, $conf, $user;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/immobilier/charge/fiche_charge.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Fiche");
	$head[$h][2] = 'fiche';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/charge/document.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("document");
	$head[$h][2] = 'document';
	$hselected = $h;
	$h ++;

    $head[$h][0] = dol_buildpath('/immobilier/charge/ventil_charge.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("repartition");
	$head[$h][2] = 'repartition';
	$hselected = $h;
	$h ++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_immeuble');

	return $head;
}

/**
 * Return head table for contact tabs screen
 *
 * @param object $object contact
 * @return array head table of tabs
 */
function contact_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/immobilier/contact/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/immobilier/contact/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_contact');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_contact', 'remove');
	
	return $head;
}

/**
 *  Return array head with list of tabs to view object informations
 *
 *  @return	array		head
 */
function immobilier_admin_prepare_head()
{
    global $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/immobilier/admin/public.php", 1);
    $head[$h][1] = $langs->trans("PublicSite");
    $head[$h][2] = 'public';
    $h++;

	$head[$h][0] = dol_buildpath("/immobilier/admin/gmaps.php", 1);
    $head[$h][1] = $langs->trans("Google Maps");
    $head[$h][2] = 'gmaps';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,'',$head,$h,'immobilier_admin');

    $head[$h][0] = dol_buildpath("/immobilier/admin/about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    complete_head_from_modules($conf,$langs,'',$head,$h,'immobilier_admin','remove');

    return $head;
}

