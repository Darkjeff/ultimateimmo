<?php
/* Copyright (C) 2015-2016  Alexandre Spangaro <aspangaro@zendsi.com>
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
 * or see http://www.gnu.org/
 */

/**
 * \file    	immobilier/rent/info.php
 * \ingroup 	Immobilier
 * \brief   	Info of rent's card
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../core/lib/immobilier.lib.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once ('../class/immorent.class.php');

$langs->load("immobilier@immobilier");

$id = GETPOST('rowid')?GETPOST('rowid','int'):GETPOST('id','int');

// Security check
if (! $user->rights->immobilier->rent->read)
	accessforbidden();

/*
 * View
 */

llxheader('', $langs->trans("RentCard") . ' | ' . $langs->trans("Infos"), '');

if ($id)
{
	$object = new Rent($db);
	$object->fetch($id);
	$object->info($id);

	$head = rent_prepare_head($object);

	dol_fiche_head($head, 'info', $langs->trans("RentCard"), 0, 'rent@immobilier');

	print '<table class="border" width="100%">';

	$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

	/*
	// Ref
	print '<tr><td class="titlefield">'.$langs->trans("Ref").'</td><td>';
	print $form->showrefnav($object, 'id', $linkback, 1, 'rowid', 'ref', '');
	print '</td></tr>';

	print '<tr>';
	print '<td>' . $langs->trans("NameProperty") . '</td>';
	print '<td>' . $object->nomlocal . '</td>';
	print '</tr>';
			
	print '<tr>';
	print '<td>' . $langs->trans("Renter") . '</td>';
	print '<td>' . $object->nomlocataire . ' ' . $object->firstname_renter . '</td>';
	print '</tr>';
	print '</table>';
	*/

	print '<div class="fichecenter">';

	print '<div class="underbanner clearboth"></div>';

	print '<br>';

    dol_print_object_info($object);

	print '</div>';
	
	dol_fiche_end();
}

$db->close();

llxFooter();
