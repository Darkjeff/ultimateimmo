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
	 *    Return name of property type
	 *
	 *    @param      string	$code       Code of property type
	 *    @return     string     			traduct name of property type
	 */
	function getPropertyTypeLabel($rowid)
	{
		global $db,$langs;

		if (! $rowid) return '';

		$sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_ultimateimmo_immoproperty_type";
		$sql.= " WHERE rowid='$rowid'";

		dol_syslog("ImmoProperty.class::getPropertyTypeLabel", LOG_DEBUG);
		$resql=$db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);

			if ($num)
			{
				$obj = $db->fetch_object($resql);
				$label=($obj->label!='-' ? $obj->label : '');
				return $label;
			}
			else
			{
				return $langs->trans("NotDefined");
			}
		}
	}
	
	/**
	 *    Retourne le nom traduit de la forme juridique
	 *
	 *    @param      string	$rowid      rowid de la forme juridique
	 *    @return     string     			Nom traduit du code juridique
	 */
	function getLabelFormeJuridique($rowid)
	{
		global $db,$langs;

		if (! $rowid) return '';

		$sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_ultimateimmo_juridique";
		$sql.= " WHERE rowid='$rowid'";

		dol_syslog("html.formultimateimmo.class::getLabelFormeJuridique", LOG_DEBUG);
		$resql=$db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);

			if ($num)
			{
				$obj = $db->fetch_object($resql);
				$label=($obj->label!='-' ? $obj->label : '');
				return $label;
			}
			else
			{
				return $langs->trans("NotDefined");
			}
		}
	}
	
	/**
	 *    Retourne le nom traduit de la date de construction
	 *
	 *    @param      string	$rowid      rowid de la date de construction
	 *    @return     string     			Nom traduit de la date de construction
	 */
	function getLabelBuiltDate($rowid)
	{
		global $db,$langs;

		if (! $rowid) return '';

		$sql = "SELECT label FROM ".MAIN_DB_PREFIX."c_ultimateimmo_builtdate";
		$sql.= " WHERE rowid='$rowid'";

		dol_syslog("html.formultimateimmo.class::getLabelBuiltDate", LOG_DEBUG);
		$resql=$db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);

			if ($num)
			{
				$obj = $db->fetch_object($resql);
				$label=($obj->label!='-' ? $obj->label : '');
				return $label;
			}
			else
			{
				return $langs->trans("NotDefined");
			}
		}
	}

}