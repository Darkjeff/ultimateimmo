<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/mymodule.lib.php
 *	\ingroup	mymodule
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function gestimmoAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("gestimmo@gestimmo");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/gestimmo/admin/admin_gestimmo.php", 1);
	$head[$h][1] = $langs->trans("administration");
	$head[$h][2] = 'administration';
	$h++;
	$head[$h][0] = dol_buildpath("/gestimmo/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'gestimmo');

	return $head;
}
function gestimmo_prepare_head()
{
	global $langs, $conf;
	$langs->load("gestimmo@gestimmo");

	$h = 0;
	$head = array();

	$head[$h][0] = DOL_URL_ROOT."/gestimmo/mandat_page.php";
	$head[$h][1] = $langs->trans("Mandats");
	$head[$h][2] = 'Mandat';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/gestimmo/biens/index.php";
	$head[$h][1] = $langs->trans("Biens");
	$head[$h][2] = 'Biens';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/gestimmo/bails_page.php";
	$head[$h][1] = $langs->trans("Loyer");
	$head[$h][2] = 'Loyers';
	$h++;
	$head[$h][0] = DOL_URL_ROOT."/gestimmo/gestion.php";
	$head[$h][1] = $langs->trans("Gestion");
	$head[$h][2] = 'Gestions';
	$h++;

	return $head;
}
function biens_prepare_head($object)
{
    global $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/gestimmo/biens/fiche.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Card");
    $head[$h][2] = 'card';
    $h++;
    $head[$h][0] = dol_buildpath('/gestimmo/biens/photos2.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Photo");
    $head[$h][2] = 'photo';
    $h++;
    $head[$h][0] = dol_buildpath('/gestimmo/biens/document.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("documents");
    $head[$h][2] = 'documents';
    $h++;

    $head[$h][0] = dol_buildpath('/gestimmo/biens/liste.php',1).'?site_view=1&search_site='.$object->id;
    $head[$h][1] = $langs->trans("liste");
    $head[$h][2] = 'liste';
    $h++;

    $head[$h][0] = dol_buildpath('/gestimmo/biens/info.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Info");
    $head[$h][2] = 'info';
    $h++;
    return $head;
    }
function mandat_prepare_head($object)
{
    global $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/gestimmo/mandat/fiche.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("fiche");
    $head[$h][2] = 'fiche';
    $h++;
    
    $head[$h][0] = dol_buildpath('/gestimmo/mandat/document.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("documents");
    $head[$h][2] = 'documents';
    $h++;

    $head[$h][0] = dol_buildpath('/gestimmo/mandat/liste.php',1).'?site_view=1&search_site='.$object->id;
    $head[$h][1] = $langs->trans("liste");
    $head[$h][2] = 'liste';
    $h++;

    $head[$h][0] = dol_buildpath('/gestimmo/mandat/info.php',1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Info");
    $head[$h][2] = 'info';
    $h++;

    //complete_head_from_modules($conf,$langs,$object,$head,$h,'agefodd_site');

    return $head;
}

