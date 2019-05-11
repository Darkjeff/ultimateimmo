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
	
	/**
	 * Display list of property type
	 *
	 *  @param  string	$selected   	Title preselected
	 * 	@param	string	$htmlname		Name of HTML select combo field
	 *  @param  string  $morecss        Add more css on SELECT element
	 *  @return	string					String with HTML select
	 */
	public function select_type_property($selected='',$htmlname='type_property_id',$morecss='maxwidth100') 
	{
		global $conf,$langs,$user;
		$langs->load("dict", "ultimateimmo@ultimateimmo");
		
		$out='';

		$sql = "SELECT rowid, code, label, active FROM ".MAIN_DB_PREFIX."c_ultimateimmo_immoproperty_type as t";
		$sql.= " WHERE active = 1";
		
		dol_syslog(get_class($this) . "::select_type_property", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) 
		{			
			$out.= '<select class="flat'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'" id="'.$htmlname.'">';
			$out.= '<option value="">&nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					if ($selected == $obj->code)
					{
						$out.= '<option value="'.$obj->code.'" selected>';
					}
					else
					{
						$out.= '<option value="'.$obj->code.'">';
					}
					// Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
					$out.= ($langs->trans("ImmoProperty_Type".$obj->code)!="ImmoProperty_Type".$obj->code ? $langs->trans("ImmoProperty_Type".$obj->code) : ($obj->label!='-'?$obj->label:''));
					$out.= '</option>';
					$i++;
				}
			}
			$out.= '</select>';
			if ($user->admin) $out.= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
		}
		else
		{
			dol_print_error($this->db);
		}
		
		return $out;
	}
	
	/**
	 * 
	 * @param unknown $selectid
	 * @param string $htmlname
	 * @param number $showempty
	 * @param array $event
	 * @return string
	 */
	public function select_property($selectid, $htmlname = 'property', $showempty = 0, $event = array(), $sql_filer='')
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, name";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_property";
		$sql .= " WHERE statut= 1";
		//if ($user->id != 1) {
		//	$sql .= " AND proprietaire_id=" . $user->id;
		//}
		if (!empty($sql_filer)) {
			$sql.=' AND '.$sql_filer;
		}
		
		$sql .= " ORDER BY name";
		
		dol_syslog(get_class($this) . "::select_property sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->name;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	function select_type($selectid, $htmlname = 'type', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, type";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_typologie";
		$sql .= " ORDER BY type";
		
		dol_syslog(get_class($this) . "::select_type sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->type;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	
	
	/**
	 * 
	 * @param unknown $selectid
	 * @param string $htmlname
	 * @param number $showempty
	 * @param array $event
	 */
	public function select_renter($selectid, $htmlname = 'renter', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, nom, prenom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter";
	//	$sql .= " WHERE statut= 'Actif'";
	//	if ($user->id != 1) {
	//		$sql .= " AND proprietaire_id=" . $user->id;
	//	}
		$sql .= " ORDER BY nom";
		
		dol_syslog(get_class($this) . "::select_renter sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = ucfirst($obj->prenom) . ' ' . strtoupper($obj->nom);
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	function select_supplier($selectid, $htmlname = 'supplier', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT DISTINCT supplier ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_cost ";
		$sql .= " ORDER BY supplier";
		
		dol_syslog(get_class($this) . "::select_supplier sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->supplier;
					
					if (($selectid != '') && $selectid == $obj->supplier) {
						$out .= '<option value="' . $obj->supplier . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->supplier . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	
	
	
	
	
	/**
	 * 
	 * @param string $selected
	 * @param string $htmlname
	 * @param number $useempty
	 * @param number $maxlen
	 * @param number $help
	 */
	public function select_nom_loyer($selected = '', $htmlname = 'nomloyer', $useempty = 0, $maxlen = 40, $help = 1) {
		global $langs, $user;
		$sql = "SELECT l.nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as l";
		$sql .= " GROUP BY l.nom";
		$sql .= " ORDER BY l.nom ASC";
	
		dol_syslog ( "Form::select_nom_loyer sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$num = $this->db->num_rows ( $resql );
			if ($num) {
				print '<select class="flat" name="' . $htmlname . '">';
				$i = 0;
	
				if ($useempty)
					print '<option value="0">&nbsp;</option>';
					while ( $i < $num ) {
						$obj = $this->db->fetch_object ( $resql );
						print '<option value="' . $obj->nom . '"';
						if ($obj->nom == $selected)
							print ' selected="selected"';
							print '>' . dol_trunc ( $obj->nom, $maxlen );
							$i ++;
					}
					print '</select>';
			}
		} else {
			dol_print_error($this->db);
		}
	}

}