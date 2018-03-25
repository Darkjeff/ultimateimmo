<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018 Philippe GRAND <philippe.grand@atoo-net.com>
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
 * \file    modulebuilder/template/core/boxes/immobilierwidget1.php
 * \ingroup immobilier
 * \brief   Widget provided by Immobilier
 *
 * Put detailed description here.
 */

/** Includes */
include_once DOL_DOCUMENT_ROOT . "/core/boxes/modules_boxes.php";

/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class immobilierwidget1 extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "immobilierbox";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "immobilier@immobilier";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('immobilier');

	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $user, $conf, $langs;
		
		// Load traductions files requiredby by page
		$langs->loadLangs(array("immobilier@immobilier","boxes"));

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("RenterWidget");

		$this->param = $param;

		//$this->enabled = $conf->global->FEATURES_LEVEL > 0;         // Condition when module is enabled or not
		$this->hidden = ! ($user->rights->immobilier->read);   // Condition when module is visible by user (test on permission)
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $langs;

		// Use configuration value for max lines count
		$this->max = $max;

		dol_include_once('/immobilier/class/immorenter.class.php');						

		// Populate the head at runtime
		$text = $langs->trans("ImmobilierBoxDescription", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => 'http://example.com',
			// Sublink icon placed after the text
			'subpicto' => 'object_immobilier@immobilier',
			// Sublink icon HTML alt text
			'subtext' => '',
			// Sublink HTML target
			'target' => '',
			// HTML class attached to the picto and link
			'subclass' => 'center',
			// Limit and truncate with "…" the displayed text lenght, 0 = disabled
			'limit' => 0,
			// Adds translated " (Graph)" to a hidden form value's input (?)
			'graph' => false
		);
		
		/*if ($user->rights->immobilier->read)
		{		*/
			// Initialize technical objects
			$object=new ImmoRenter($this->db);
			
			$sql = "SELECT t.ref, t.lastname";		
			$sql.= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
			$sql.= " WHERE t.entity IN (".getEntity('immorenter').")";
			
			$result = $this->db->query($sql);
			if ($result) {
				$num = $this->db->num_rows($result);
				
				$line = 0;
				while ($line < $num)
				{
					$objp = $this->db->fetch_object($result);
					
					$object->ref=$objp->ref;
					$object->lastname=$objp->lastname;
					
					$this->info_box_contents[$line][] = array(
						'td' => '',
						'text' => $object->getNomUrl(1),
						'asis' => 1,
					);

					$this->info_box_contents[$line][] = array(
						'td' => '',
						'text' => $object->lastname,
						'asis' => 1,
					);

					$line++;
				}
			}
		//}
	}

	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head Array with properties of box title
	 * @param array $contents Array with properties of box lines
	 * @return void
	 */
	public function showBox($head = null, $contents = null, $nooutput=0)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
}
