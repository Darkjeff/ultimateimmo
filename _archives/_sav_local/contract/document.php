<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville  <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Barilley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2009 Regis Houssin         <regis.houssin@capnetworks.com>
 * Copyright (C) 2005      Simon TOSSER          <simon@kornog-computing.com>
 * Copyright (C) 2011      Juanjo Menent         <jmenent@2byte.es>
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
 * \file htdocs/compta/sociales/document.php
 * \ingroup tax
 * \brief Page with attached files on social contributions
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

dol_include_once ( "/immobilier/class/immorent.class.php" );
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

$object = new Rent ( $db );
$object->fetch ( $id );

$upload_dir = $conf->tax->dir_output . '/' . dol_sanitizeFileName ( $object->ref );
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
	
	$contrat = new Rent ( $db );
	$result = $contrat->fetch ( $id );
	$head = contrat_prepare_head ( $contrat );
	
	dol_fiche_head ( $head, 'document', $langs->trans ( "ImoDocument" ), 0, 'agreement' );
	
	// Construit liste des fichiers
	$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower ( $sortorder ) == 'desc' ? SORT_DESC : SORT_ASC), 1 );
	$totalsize = 0;
	foreach ( $filearray as $key => $file ) {
		$totalsize += $file ['size'];
	}
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="25%">local</td>';
	print '<td><input name="local_id" size="10" value="' . $contrat->local_id . '"</td></tr>';
	print '<tr><td>locataire</td>';
	print '<td><input name="locataire_id" size="10" value="' . $contrat->locataire_id . '"</td></tr>';
	print '<tr><td>date</td>';
	print '<td><input name="date_entree" size="20" value="' . $contrat->date_entree . '"</td></tr>';
	print '<tr><td>date fin</td>';
	print '<td><input name="date_fin_preavis" size="20" value="' . $contrat->date_fin_preavis . '"</td></tr>';
	print '<tr><td>preavis</td>';
	print '<td><input name="preavis" size="10" value="' . $contrat->preavis . '"</td></tr>';
	print '<tr><td>montant</td>';
	print '<td><input name="montant_tot" size="10" value="' . $contrat->montant_tot . '"</td></tr>';
	print '<tr><td>loyer</td>';
	print '<td><input name="loy" size="10" value="' . $contrat->loy . '"</td></tr>';
	print '<tr><td>charges</td>';
	print '<td><input name="charges" size="10" value="' . $contrat->charges . '"</td></tr>';
	print '<tr><td>tva</td>';
	print '<td><input name="tva" size="10" value="' . $contrat->tva . '"</td></tr>';
	print '<tr><td>periode</td>';
	print '<td><input name="periode" size="10" value="' . $contrat->periode . '"</td></tr>';
	print '<tr><td>depot</td>';
	print '<td><input name="depot" size="10" value="' . $contrat->depot . '"</td></tr>';
	print '<tr><td>commentaire</td>';
	print '<td><input name="commentaire" size="90" value="' . $contrat->commentaire . '"</td></tr>';
	print '<tr><td>proprietaire</td>';
	print '<td><input name="proprietaire_id" size="10" value="' . $contrat->proprietaire_id . '"</td></tr>';
	print '<tr><td>' . $langs->trans ( "NbOfAttachedFiles" ) . '</td><td colspan="3">' . count ( $filearray ) . '</td></tr>';
	print '<tr><td>' . $langs->trans ( "TotalSizeOfAttachedFiles" ) . '</td><td colspan="3">' . $totalsize . ' ' . $langs->trans ( "bytes" ) . '</td></tr>';
	print '</table>';
	
	print '</div>';
	
	// Affiche formulaire upload
	$formfile = new FormFile ( $db );
	$formfile->form_attach_new_file ( DOL_URL_ROOT . '/custom/immobilier/contrat/document.php?id=' . $object->id, '', 0, 0 );
	
	// List of document
	// $param='&id='.$object->id;
	$formfile->list_of_documents ( $filearray, $object, 'immobilier', $param );
} else {
	print $langs->trans ( "UnkownError" );
}

llxFooter();

$db->close();
