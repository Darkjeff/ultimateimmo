<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019 Philippe GRAND <philippe.grand@atoo-net.com>
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
 * \file    modulebuilder/template/core/boxes/box_immorenter.php
 * \ingroup ultimateimmo
 * \brief   Widget provided by ultimateimmo
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
class box_immorenter extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "lastrenters";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "object_immorenter";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('ultimateimmo');

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
		$langs->loadLangs(array("ultimateimmo@ultimateimmo","boxes"));

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("BoxLatestRenters");

		$this->param = $param;

		//$this->enabled = $conf->global->FEATURES_LEVEL > 0;         // Condition when module is enabled or not
		$this->hidden = ! ($user->rights->ultimateimmo->read);   // Condition when module is visible by user (test on permission)
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $langs, $user, $db;
		$langs->load("ultimateimmo@ultimateimmo");

		// Use configuration value for max lines count
		$this->max = $max;

		dol_include_once('/ultimateimmo/class/immorenter.class.php');	
		// Initialize technical objects
		$object=new ImmoRenter($this->db);		

		// Populate the head at runtime
		$text = $langs->trans("BoxTitleLastModifiedRenters", $max);
		$this->info_box_head = array(
			// Title text
			'text' => $text,
			// Add a link
			'sublink' => 'http://example.com',
			// Sublink icon placed after the text
			'subpicto' => 'object_ultimateimmo',
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
		
		if ($user->rights->ultimateimmo->read)
		{		
			
			$sql = "SELECT t.rowid, t.ref, t.firstname, t.lastname, t.email, t.phone_mobile, t.tms";		
			$sql.= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
			$sql.= " WHERE t.entity IN (".getEntity('immorenter').")";
			$sql.= " ORDER BY t.tms DESC";
			
			$result = $this->db->query($sql);
			if ($result) 
			{
				$num = $this->db->num_rows($result);
				
				$line = 0;
				while ($line < $num)
				{
					$objp = $this->db->fetch_object($result);
					$datem = $this->db->jdate($objp->tms);
					
					$object->firstname=$objp->firstname;
					$object->lastname=$objp->lastname;
					$object->id = $objp->rowid;
					$object->ref=$objp->ref;	
					$object->email=$objp->email;
					$object->phone_mobile=$objp->phone_mobile;
					
					$this->info_box_contents[$line][] = array(
						'td' => '',
						'text' => $object->getNomUrl(1),
						'asis' => 1,
					);
					
					$this->info_box_contents[$line][] = array(
						'td' => '',
						'text' => $object->firstname,
						'url' => dol_buildpath('/ultimateimmo/renter/immorenter_card.php', 1).'?id='.$objp->rowid,
					);

					$this->info_box_contents[$line][] = array(
						'td' => '',
						'text' => $object->lastname,
						'url' => dol_buildpath('/ultimateimmo/renter/immorenter_card.php', 1).'?id='.$objp->rowid,
					);
					
					$this->info_box_contents[$line][] = array(
						'td' => '',
						'text' => $object->email,
						'asis' => 1,
					);
					
					$this->info_box_contents[$line][] = array(
						'td' => '',
						'text' => $object->phone_mobile,
						'asis' => 1,
					);

					$line++;
				}
				if ($num==0)
                    $this->info_box_contents[$line][0] = array(
                        'td' => 'align="center"',
                        'text'=>$langs->trans("NoRecordedCustomers"),
                    );

                $this->db->free($result);
            } else {
                $this->info_box_contents[0][0] = array(
                    'td' => '',
                    'maxlength'=>500,
                    'text' => ($this->db->error().' sql='.$sql),
                );
            }
		}else {
			$this->info_box_contents[0][0] = array(
                'td' => 'align="left" class="nohover opacitymedium"',
                'text' => $langs->trans("ReadPermissionNotAllowed")
            );
		}
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
