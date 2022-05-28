<?php
/* Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
 * Copyright (C) 2015 		Alexandre Spangaro  <aspangaro@zendsi.com>
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
 * \file 	ultimateimmo/class/html.formultimateimmo.class.php
 * \brief 	Fichier de la classe des fonctions predefinie de composants html ultimateimmo
 */

/**
 * Class to manage building of HTML components
 */
class FormUltimateimmo extends Form 
{
	/**
     * @var DoliDB Database handler.
     */
    public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error='';
	
	/**
	 * Constructor
	 *
	 * @param DoliDB $db handler
	 */
	function __construct($db) 
	{
		$this->db = $db;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *      Return array with list of possible values for built date
	 *
	 *      @param	string	$order		Sort order by : 'code', 'rowid'...
	 *      @param  int		$activeonly 0=all status of built date, 1=only the active
	 *		@param	string	$code		code of built date
	 *      @return array       		Array list of built date (id->label if option=0, code->label if option=1)
	 */
	public function builtDateList($order = '', $activeonly = 0, $code = '')
	{
		// phpcs:enable
		global $langs;

		if (empty($order)) {
			$order = 'code';
		}

		$tab = array();
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.label";
		$sql .= " FROM " . $this->db->prefix() . "c_ultimateimmo_builtdate as tc";
		$sql .= " WHERE tc.entity IN (".getEntity('builtdate').")";
		if ($activeonly == 1) {
			$sql .= " AND tc.active=1"; // only the active types
		}
		if (!empty($code)) {
			$sql .= " AND tc.code='".$this->db->escape($code)."'";
		}
		$sql .= $this->db->order($order, 'ASC');		
		//print "sql=".$sql;
		
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
				$i = 0;
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);

					$tab[$obj->rowid] = $langs->trans($obj->label);
					$i++;
				}
			}
		} else {
			print $this->db->error();
		}
		return $tab;
	}

	/**
	 *  Return a select list with built dates
	 *
	 *  @param	object		$object         	Object to use to find date of building
	 *  @param  string		$selected       	Default selected value
	 *  @param  string		$htmlname			HTML select name
	 *  @param  string		$sortorder			Sort criteria ('code', ...)
	 *  @param  int			$showempty      	1=Add en empty line
	 *  @param  string      $morecss        	Add more css to select component
	 *  @param  int      	$output         	0=return HTML, 1= direct print
	 *  @param	int			$forcehidetooltip	Force hide tooltip for admin
	 *  @return	string|void						Depending on $output param, return the HTML select list (recommended method) or nothing
	 */
	public function selectBuiltDate($selected = '', $htmlname = 'datebuilt', $sortorder = 'code', $showempty = 0, $morecss = '', $output = 1, $forcehidetooltip = 1)
	{
		global $user, $langs;
		
		$out = '';
		if (is_object($this) && method_exists($this, 'builtDateList')) {
			$lesDates = $this->builtDateList();
			
			dol_syslog(__METHOD__ . " selected=" . $selected . ", htmlname=" . $htmlname, LOG_DEBUG);

			$out .= '<select id="' . $htmlname . '" class="flat valignmiddle' . ($morecss ? ' ' . $morecss : '') . '" name="' . $htmlname . '">';
			$showempty = 1;
			if ($showempty) {
				$out .= '<option value="0">&nbsp;</option>';
			}
			foreach ($lesDates as $key => $value) {
				$out .= '<option value="' . $key . '"';
				if ($key == $selected) {
					$out .= ' selected';
				}
				$out .= '>' . $value . '</option>';
			}
			
			$out .= "</select>";
			$forcehidetooltip = 1;
			if ($user->admin && $forcehidetooltip == 1) {
				$out .= ' ' . info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
			}

			$out .= ajax_combobox($htmlname);

			$out .= "\n";
		}
		if (empty($output)) {
			return $out;
		} else {
			print $out;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *      Return array with list of possible values for property type
	 *
	 *      @param	string	$order		Sort order by : 'code', 'rowid'...
	 *      @param  int		$activeonly 0=all status of property type, 1=only the active
	 *		@param	string	$code		code of property type
	 *      @return array       		Array list of property type (id->label if option=0, code->label if option=1)
	 */
	public function propertyTypeList($order = '', $activeonly = 0, $code = '')
	{
		// phpcs:enable
		global $langs;

		if (empty($order)) {
			$order = 'code';
		}

		$tab = array();
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.label";
		$sql .= " FROM " . $this->db->prefix() . "c_ultimateimmo_immoproperty_type as tc";
		$sql .= " WHERE tc.entity IN (".getEntity('property_type_id').")";
		if ($activeonly == 1) {
			$sql .= " AND tc.active=1"; // only the active types
		}
		if (!empty($code)) {
			$sql .= " AND tc.code='".$this->db->escape($code)."'";
		}
		$sql .= $this->db->order($order, 'ASC');		
		//print "sql=".$sql;
		
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
				$i = 0;
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);

					$tab[$obj->rowid] = $langs->trans($obj->label);
					$i++;
				}
			}
		} else {
			print $this->db->error();
		}
		return $tab;
	}

	/**
	 *  Return a select list with property type
	 *
	 *  @param	object		$object         	Object to use to find date of building
	 *  @param  string		$selected       	Default selected value
	 *  @param  string		$htmlname			HTML select name
	 *  @param  string		$sortorder			Sort criteria ('code', ...)
	 *  @param  int			$showempty      	1=Add en empty line
	 *  @param  string      $morecss        	Add more css to select component
	 *  @param  int      	$output         	0=return HTML, 1= direct print
	 *  @param	int			$forcehidetooltip	Force hide tooltip for admin
	 *  @return	string|void						Depending on $output param, return the HTML select list (recommended method) or nothing
	 */
	public function selectpropertyType($selected = '', $htmlname = 'property_type_id', $sortorder = 'code', $showempty = 0, $morecss = '', $output = 1, $forcehidetooltip = 1)
	{
		global $user, $langs;
		
		$out = '';
		if (is_object($this) && method_exists($this, 'propertyTypeList')) {
			$lesTypes = $this->propertyTypeList();
			
			dol_syslog(__METHOD__ . " selected=" . $selected . ", htmlname=" . $htmlname, LOG_DEBUG);

			$out .= '<select id="' . $htmlname . '" class="flat valignmiddle' . ($morecss ? ' ' . $morecss : '') . '" name="' . $htmlname . '">';
			$showempty = 1;
			if ($showempty) {
				$out .= '<option value="0">&nbsp;</option>';
			}
			foreach ($lesTypes as $key => $value) {
				$out .= '<option value="' . $key . '"';
				if ($key == $selected) {
					$out .= ' selected';
				}
				$out .= '>' . $value . '</option>';
			}
			
			$out .= "</select>";
			$forcehidetooltip = 1;
			if ($user->admin && $forcehidetooltip == 1) {
				$out .= ' ' . info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
			}

			$out .= ajax_combobox($htmlname);

			$out .= "\n";
		}
		if (empty($output)) {
			return $out;
		} else {
			print $out;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *      Return array with list of possible values for juridique
	 *
	 *      @param	string	$order		Sort order by : 'code', 'rowid'...
	 *      @param  int		$activeonly 0=all status of juridique, 1=only the active
	 *		@param	string	$code		code of juridique
	 *      @return array       		Array list of juridique (id->label if option=0, code->label if option=1)
	 */
	public function juridiqueList($order = '', $activeonly = 0, $code = '')
	{
		// phpcs:enable
		global $langs;

		if (empty($order)) {
			$order = 'code';
		}

		$tab = array();
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.label";
		$sql .= " FROM " . $this->db->prefix() . "c_ultimateimmo_juridique as tc";
		$sql .= " WHERE tc.entity IN (".getEntity('juridique_id').")";
		if ($activeonly == 1) {
			$sql .= " AND tc.active=1"; // only the active types
		}
		if (!empty($code)) {
			$sql .= " AND tc.code='".$this->db->escape($code)."'";
		}
		$sql .= $this->db->order($order, 'ASC');		
		//print "sql=".$sql;
		
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
				$i = 0;
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);

					$tab[$obj->rowid] = $langs->trans($obj->label);
					$i++;
				}
			}
		} else {
			print $this->db->error();
		}
		return $tab;
	}

	/**
	 *  Return a select list with juridique_id
	 *
	 *  @param	object		$object         	Object to use to find date of building
	 *  @param  string		$selected       	Default selected value
	 *  @param  string		$htmlname			HTML select name
	 *  @param  string		$sortorder			Sort criteria ('code', ...)
	 *  @param  int			$showempty      	1=Add en empty line
	 *  @param  string      $morecss        	Add more css to select component
	 *  @param  int      	$output         	0=return HTML, 1= direct print
	 *  @param	int			$forcehidetooltip	Force hide tooltip for admin
	 *  @return	string|void						Depending on $output param, return the HTML select list (recommended method) or nothing
	 */
	public function selectJuridique($selected = '', $htmlname = 'juridique_id', $sortorder = 'code', $showempty = 0, $morecss = '', $output = 1, $forcehidetooltip = 1)
	{
		global $user, $langs;
		
		$out = '';
		if (is_object($this) && method_exists($this, 'juridiqueList')) {
			$lesDates = $this->juridiqueList();
			
			dol_syslog(__METHOD__ . " selected=" . $selected . ", htmlname=" . $htmlname, LOG_DEBUG);

			$out .= '<select id="' . $htmlname . '" class="flat valignmiddle' . ($morecss ? ' ' . $morecss : '') . '" name="' . $htmlname . '">';
			$showempty = 1;
			if ($showempty) {
				$out .= '<option value="0">&nbsp;</option>';
			}
			foreach ($lesDates as $key => $value) {
				$out .= '<option value="' . $key . '"';
				if ($key == $selected) {
					$out .= ' selected';
				}
				$out .= '>' . $value . '</option>';
			}
			
			$out .= "</select>";
			$forcehidetooltip = 1;
			if ($user->admin && $forcehidetooltip == 1) {
				$out .= ' ' . info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
			}

			$out .= ajax_combobox($htmlname);

			$out .= "\n";
		}
		if (empty($output)) {
			return $out;
		} else {
			print $out;
		}
	}

}