<?php
/* Copyright (C) 2015-2016  Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
require_once ('../class/rent.class.php');

$langs->load("immobilier@immobilier");

$id = GETPOST('rowid')?GETPOST('rowid','int'):GETPOST('id','int');

// Security check
if (! $user->rights->immobilier->rent->read)
	accessforbidden();

/*
 * View
 */

llxHeader();

if ($id)
{
	$object = new Rent($db);
	$object->fetch($id);
	$object->info($id);

	$head = rent_prepare_head($object);

	dol_fiche_head($head, 'info', $langs->trans("Rent"), 0, 'rent@immobilier');

    print '<table width="100%"><tr><td>';
    dol_print_object_info($object);
    print '</td></tr></table>';

    print '</div>';
}

$db->close();

llxFooter();
