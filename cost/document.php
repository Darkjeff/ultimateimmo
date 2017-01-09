<?php
/* Copyright (C) 2013 Olivier Geffroy  <jeff@jeffinfo.com>
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
 * \file htdocs/custom/immobilier/charge/document.php
 */

// Dolibarr environment
$res=@include("../main.inc.php");
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Class
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

dol_include_once ( "/immobilier/class/immocost.class.php" );
require_once ('../core/lib/immobilier.lib.php');

// Langs
$langs->load ( "immobilier@immobilier" );
$langs->load ( "other" );

$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( "action" );

// Security check

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

$object = new Immocost ( $db );
$object->fetch ( $id );

$upload_dir = $conf->immobilier->dir_output  .'/'. dol_sanitizeFileName ( $object->id );
$modulepart = 'immobilier';
$modulesubdir = 'charge';

/*
 * Actions
 */

if (GETPOST ( "sendit" ) && ! empty ( $conf->global->MAIN_UPLOAD_DOC )) {
	dol_add_file_process ( $upload_dir, 0, 1, 'userfile' );
}

if ($action == 'delete') {
	$file = $conf->immobilier->dir_output  . '/' . GETPOST ( "urlfile" ); // Do not use urldecode here ($_GET and $_REQUEST are already decoded by PHP).
	$ret = dol_delete_file ( $file );
	if ($ret)
		setEventMessage ( $langs->trans ( "FileWasRemoved", GETPOST ( 'urlfile' ) ) );
	else
		setEventMessage ( $langs->trans ( "ErrorFailToDeleteFile", GETPOST ( 'urlfile' ) ), 'errors' );
}

/*
 * View
 */

llxheader ( '', $langs->trans ( "adddocument" ), '' );
$form = new Form ( $db );

if ($object->id) {

	
	$head = charge_prepare_head ( $object );
	
	dol_fiche_head ( $head, 'document', $langs->trans ( "Document" ), 0, 'propertie' );
	
	// Construit liste des fichiers
	$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower ( $sortorder ) == 'desc' ? SORT_DESC : SORT_ASC), 1 );
	$totalsize = 0;
	foreach ( $filearray as $key => $file ) {
	 // var_dump($file);
	 // $file['level1name']='charge/';
		$totalsize += $file ['size'];
	}
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="25%">' . $langs->trans ( "Charge" ) . '</td>';
	print '<td>'.$object->libelle . '</td></tr>';
	print '<tr><td>' . $langs->trans ( "NbOfAttachedFiles" ) . '</td><td colspan="3">' . count ( $filearray ) . '</td></tr>';
	print '<tr><td>' . $langs->trans ( "TotalSizeOfAttachedFiles" ) . '</td><td colspan="3">' . $totalsize . ' ' . $langs->trans ( "bytes" ) . '</td></tr>';
	print '</table>';
	
	print '</div>';
	
	// Affiche formulaire upload
	$formfile = new FormFile ( $db );
	$formfile->form_attach_new_file ( DOL_URL_ROOT . '/custom/immobilier/cost/document.php?id=' . $object->id, '', 0, 0 );
	
	// List of document
	// $param='&id='.$object->id;
		//var_dump($filearray);
$formfile->list_of_documents ( $filearray, $object, $modulepart, $param );
	
	
} else {
	print $langs->trans ( "UnkownError" );
}

llxFooter();

$db->close();
