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
 * \file htdocs/custom/immobilier/locataire/document.php
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

dol_include_once ( "/immobilier/class/locataire.class.php" );
dol_include_once ( '/immobilier/core/lib/immobilier.lib.php' );

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

$object = new Locataire ( $db );
$object->fetch ( $id );

$upload_dir = $conf->immobilier->dir_output . '/locataire/' . dol_sanitizeFileName ( $object->id );
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

llxheader ( '', $langs->trans ( "adddocument" ), '' );
$form = new Form ( $db );

if ($object->id) {
	
	$locataire = new Locataire ( $db, GETPOST ( 'id' ) );
	$head = locataire_prepare_head ( $locataire );
	
	dol_fiche_head ( $head, 'document', $langs->trans ( "ImoDocument" ), 0, 'renter' );
	
	// Construit liste des fichiers
	$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower ( $sortorder ) == 'desc' ? SORT_DESC : SORT_ASC), 1 );
	$totalsize = 0;
	foreach ( $filearray as $key => $file ) {
		$totalsize += $file ['size'];
	}
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">' . $langs->trans ( "nomlocataire" ) . '</td>';
	print '<td><input name="nom" size="40" value="' . $locataire->nom . '"</td></tr>';
	print '<tr><td>' . $langs->trans ( "NbOfAttachedFiles" ) . '</td><td colspan="3">' . count ( $filearray ) . '</td></tr>';
	print '<tr><td>' . $langs->trans ( "TotalSizeOfAttachedFiles" ) . '</td><td colspan="3">' . $totalsize . ' ' . $langs->trans ( "bytes" ) . '</td></tr>';
	print '</table>';
	
	print '</div>';
	
	// Affiche formulaire upload
	$formfile = new FormFile ( $db );
	$formfile->form_attach_new_file ($_SERVER["PHP_SELF"].'?id=' . $object->id, '', 0, 0,1,50,$locataire );
	
	// List of document
	// $param='&id='.$object->id;
	$formfile->list_of_documents ( $filearray, $object, 'immobilier/locataire', $param );
} else {
	print $langs->trans ( "UnkownError" );
}

llxFooter ();

$db->close ();
?>
