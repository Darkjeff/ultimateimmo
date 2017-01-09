<?php
/* Copyright (C) 2016		Alexandre Spangaro		<aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/property/note.php
 * \ingroup immobilier
 * \brief   Note of property
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once '../core/lib/immobilier.lib.php';
require_once '../class/immoproperty.class.php';

$action = GETPOST('action');

$langs->load("immobilier@immobilier");

$id = GETPOST('rowid')?GETPOST('rowid','int'):GETPOST('id','int');

// Security check
if (! $user->rights->immobilier->property->read)
	accessforbidden();

$object = new Immoproperty($db);
if ($id > 0) $object->fetch($id);

$permissionnote=$user->rights->immobilier->property->write;  // Used by the include of actions_setnotes.inc.php


/*
 *  Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php'; // Must be include, not include_once


/*
 *  View
 */

llxHeader('', $langs->trans("PropertyCard") . ' | ' . $langs->trans("Notes"));

$form = new Form($db);

if ($id > 0)
{
    /*
     * Show tabs
     */
	$head = property_prepare_head($object);
	dol_fiche_head($head, 'note', $langs->trans("PropertyCard"), 0, 'building@immobilier');

	$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

	immo_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

	print '<div class="fichecenter">';

    //$colwidth='25';
    $cssclass='titlefield';
    $permission = $user->rights->immobilier->property->write;  // Used by the include of notes.tpl.php
    include DOL_DOCUMENT_ROOT.'/core/tpl/notes.tpl.php';

	print '</div>';
			
	print '<div style="clear:both"></div>';

    dol_fiche_end();
}

llxFooter();
$db->close();

