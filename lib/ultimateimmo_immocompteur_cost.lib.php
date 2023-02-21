<?php
/* Copyright (C) ---Put here your own copyright and developer email---
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/ultimateimmo_immocompteur_cost.lib.php
 * \ingroup ultimateimmo
 * \brief   Library files with common functions for ImmoCompteur_Cost
 */

/**
 * Prepare array of tabs for ImmoCompteur_Cost
 *
 * @param	ImmoCompteur_Cost	$object		ImmoCompteur_Cost
 * @return 	array					Array of tabs
 */
function immocompteur_costPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("ultimateimmo@ultimateimmo");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/ultimateimmo/immocompteur_cost_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	return $head;
}
