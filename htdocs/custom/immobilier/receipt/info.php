<?php
/* Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro@zendsi.com>
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
 * \file    immobilier/receipt/card.php
 * \ingroup immobilier
 * \brief   Info of receipt's card
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../core/lib/immobilier.lib.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once ('../class/immoreceipt.class.php');

$langs->load("immobilier@immobilier");

$id = GETPOST('rowid')?GETPOST('rowid','int'):GETPOST('id','int');

// Security check
if (! $user->rights->immobilier->rent->read)
	accessforbidden();

/*
 * View
 */

llxHeader('' , $langs->trans("ReceiptCard") . ' | ' . $langs->trans("Info"));

if ($id)
{
	$object = new Immoreceipt($db);
	$object->fetch($id);
	$object->info($id);

	$head = receipt_prepare_head($object);

	$linkback = '<a href="./list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

	dol_fiche_head($head, 'info', $langs->trans("ReceiptCard"), 0, 'rent@immobilier');

	immo_banner_tab($object, 'rowid', $linkback, 1, 'rowid', 'name');

	print '<div class="fichecenter">';

	print '<div class="underbanner clearboth"></div>';

	print '<br>';

    dol_print_object_info($object);

	print '</div>';
	
	dol_fiche_end();
}

$db->close();

llxFooter();
