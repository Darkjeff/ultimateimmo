<?php
/* Copyright (C) 2022 Florian Henry  <florian.henry@scopen.fr>

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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       ultimateimmo/class/commonobjectultimateimmo.class.php
 *	\ingroup    core
 *	\brief      File of parent class of all ultimateimmo classes
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
/**
 *	special field type for ultimateimmo
 */
class CommonObjectUltimateImmo extends CommonObject
{
	/**
	 * Return HTML string to put an input field into a page
	 * Code very similar with showInputField of extra fields
	 *
	 * @param  array   		$val	       Array of properties for field to show (used only if ->fields not defined)
	 * @param  string  		$key           Key of attribute
	 * @param  string|array	$value         Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value, for array type must be array)
	 * @param  string  		$moreparam     To add more parameters on html input tag
	 * @param  string  		$keysuffix     Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  		$keyprefix     Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string|int	$morecss       Value for css to define style/length of field. May also be a numeric.
	 * @param  int			$nonewbutton   Force to not show the new button on field that are links to object
	 * @return string
	 */
	public function showInputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = 0, $nonewbutton = 0)
	{
		global $form;
		if ($val['label']=='Civility') {
			$this->civility_id	= GETPOST("civility_id", 'int') ? GETPOST('civility_id', 'int') : $this->civility_id;

			if ($this->civility_id) {
				$tmparray = array();
				$tmparray = $this->getCivilityLabel($this->civility_id, 'all');
				if (in_array($tmparray['code'], $tmparray)) $this->civility_code = $tmparray['code'];
				if (in_array($tmparray['label'], $tmparray)) $this->civility = $tmparray['label'];
			}

			// civility
			print $this->select_civility(GETPOSTISSET("civility_id") != '' ? GETPOST("civility_id", 'int') : $this->civility_id, 'civility_id');
		} elseif ($val['label']=='Country') {
			$this->country_id = GETPOST('country_id', 'int') ? GETPOST('country_id', 'int') : $this->country_id;
			if ($this->country_id) {
				$tmparray = $this->getCountry($this->country_id, 'all');
				$this->country_code = $tmparray['code'];
				$this->country = $tmparray['label'];
			}
			// Country
			print $form->select_country((GETPOST('country_id') != '' ? GETPOST('country_id') : $this->country_id));
		} else {
			return parent::showInputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss, $nonewbutton);
		}
	}

	/**
	 * Return HTML string to show a field into a page
	 * Code very similar with showOutputField of extra fields
	 *
	 * @param  array   $val		       Array of properties of field to show
	 * @param  string  $key            Key of attribute
	 * @param  string  $value          Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
	 * @param  string  $moreparam      To add more parametes on html input tag
	 * @param  string  $keysuffix      Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  $keyprefix      Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  mixed   $morecss        Value for css to define size. May also be a numeric.
	 * @return string
	 */
	public function showOutputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = '')
	{

		if ($val['label'] == 'Civility') {
			if ($this->civility_id) {
				$tmparray = $this->getCivilityLabel($this->civility_id, 'all');
				$this->civility_code = $tmparray['code'];
				$this->civility = $tmparray['label'];
			}
			print $this->civility;
		} elseif ($val['label']=='MenuImmoOwnerType') {

			dol_include_once('/ultimateimmo/class/immoowner_type.class.php');

			$staticownertype = new ImmoOwner_Type($this->db);
			$staticownertype->fetch($this->fk_owner_type);
			if ($staticownertype->ref) {
				print $staticownertype->ref;
			}
		} elseif ($val['label']=='Country') {
			if ($this->country_id) {
				$tmparray = $this->getCountry($this->country_id, 'all');
				$this->country_code = $tmparray['code'];
				$this->country = $tmparray['label'];
				// Country
				print $this->country;
			}
		} else {
			return parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
		}
	}
}
