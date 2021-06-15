<?php
/* Copyright (C) 2018-2019 Philippe GRAND 	<philippe.grand@atoo-net.com>
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
 * \file    lib/immoowner_type.lib.php
 * \ingroup ultimateimmo
 * \brief   Library files with common functions for ImmoOwner_Type
 */

/**
 * Prepare array of tabs for ImmoOwner_Type
 *
 * @param	ImmoOwner_Type	$object		ImmoOwner_Type
 * @return 	array					Array of tabs
 */
function immoowner_typePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("ultimateimmo@ultimateimmo");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/ultimateimmo/owner_type/immoowner_type_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	if (isset($object->fields['note_public']) || isset($object->fields['note_private']))
	{
		$nbNote = 0;
		if (!empty($object->note_private)) $nbNote++;
		if (!empty($object->note_public)) $nbNote++;
		$head[$h][0] = dol_buildpath('/ultimateimmo/owner_type/immoowner_type_note.php', 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Notes');
		if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
		$head[$h][2] = 'note';
		$h++;
	}

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@ultimateimmo:/ultimateimmo/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@ultimateimmo:/ultimateimmo/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immoowner_type@ultimateimmo');

	return $head;
}
