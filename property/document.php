<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/property/document.php
 * \ingroup immobilier
 * \brief   Document of property
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

// Class
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

require_once ('../core/lib/immobilier.lib.php');
require_once ('../class/immoproperty.class.php');

// Langs
$langs->load("immobilier@immobilier" );
$langs->load("other");

$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( "action" );

// Security check
if (! $user->rights->immobilier->property->read)
	accessforbidden();

// Get parameters
$sortfield = GETPOST ( "sortfield", 'alpha' );
$sortorder = GETPOST ( "sortorder", 'alpha' );
$page = GETPOST ( "page", 'int' );
if ($page == - 1) {
	$page = 0;
}
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder)
	$sortorder = "ASC";
if (! $sortfield)
	$sortfield = "name";

$object = new Immoproperty($db);
$result = $object->fetch($id);

$upload_dir = $conf->immobilier->dir_output . '/property/' . dol_sanitizeFileName($object->id);
$modulepart = 'immobilier';

/*
 * Actions
 */

if (GETPOST ( "sendit" ) && ! empty ( $conf->global->MAIN_UPLOAD_DOC )) {
	dol_add_file_process ( $upload_dir, 0, 1, 'userfile' );
}

if ($action == 'delete') {
	$file = $upload_dir . '/' . GETPOST ( "urlfile" ); // Do not use urldecode here ($_GET and $_REQUEST are already decoded by PHP).
	$ret = dol_delete_file ( $file );
	if ($ret)
		setEventMessage ( $langs->trans ( "FileWasRemoved", GETPOST ( 'urlfile' ) ) );
	else
		setEventMessage ( $langs->trans ( "ErrorFailToDeleteFile", GETPOST ( 'urlfile' ) ), 'errors' );
}

/*
 * View
 */
$form = new Form($db);

llxheader('', $langs->trans("PropertyCard") . ' | ' . $langs->trans("Files"));

if ($id > 0)
{
	/*
	 * Affichage onglets
	 */
	$head = property_prepare_head($object);
	dol_fiche_head($head, 'document', $langs->trans("PropertyCard"), 0, 'building@immobilier');
	
	$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

	immo_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

	print '<div class="underbanner clearboth"></div>';
	
	// Construit liste des fichiers
	$filearray=dol_dir_list($upload_dir,"files",0,'','(\.meta|_preview\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
	$totalsize=0;
	foreach($filearray as $key => $file)
	{
		$totalsize+=$file['size'];
	}

	print '<table class="border"width="100%">';
	// Nbre fichiers
    print '<tr><td>'.$langs->trans("NbOfAttachedFiles").'</td><td colspan="3">'.count($filearray).'</td></tr>';
    // Total taille
	print '<tr><td>'.$langs->trans("TotalSizeOfAttachedFiles").'</td><td colspan="3">'.$totalsize.' '.$langs->trans("bytes").'</td></tr>';
    print '</table>';

    print '</div>';
	
    $modulepart = 'immobilier';
    $permission = $user->rights->immobilier->property->write;
    $param = '&id=' . $object->id;
    include_once DOL_DOCUMENT_ROOT . '/core/tpl/document_actions_post_headers.tpl.php';
}
else
{
	print $langs->trans("UnkownError");
}

llxFooter();
$db->close();
