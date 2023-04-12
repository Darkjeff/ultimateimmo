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

		$sql = "SELECT t.rowid, t.code, t.label, t.active FROM ".MAIN_DB_PREFIX."c_ultimateimmo_immoproperty_type as t";
		if (! empty($filter)) {
			$sql .= ' WHERE ' . $filter;
		}
		$sql .= " ORDER BY t.label";

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
	 *    Return ImmoProperty_Type label of a property
	 *
	 *    @return   string              	Translated name of ImmoProperty_Type (translated with transnoentitiesnoconv)
	 */
	public function getPropertyTypeLabel()
	{
		global $langs;
		$langs->load("dict");

		$code=(empty($this->type_property_id)?'':$this->type_property_id);
		if (empty($code)) return '';
		return $langs->getLabelFromKey($this->db, "ImmoProperty_Type".$code, "c_ultimateimmo_immoproperty_type", "code", "label", $code);
	}

	/**
	 *  Return combo list with juridique forme
	 *
	 *  @param  string	$selected   	Juridique code preselected
	 * 	@param	string	$htmlname		Name of HTML select combo field
	 *  @param  string  $morecss        Add more css on SELECT element
	 *  @return	string					String with HTML select
	 */
	public function select_juridique($selected = '', $htmlname = 'juridique_id', $morecss = 'maxwidth100', $usecodeaskey = '', $showempty = 1, $disablefavorites = 0, $addspecialentries = 0)
	{
		global $db, $langs;

		$langs->load("dict");

		$out='';
		$juridiqueArray=array();
		$favorite=array();
		$label=array();
		$atleastonefavorite=0;

		if (! $rowid) return '';

		$sql = "SELECT rowid, code, label FROM ".MAIN_DB_PREFIX."c_ultimateimmo_juridique";
		$sql.= " WHERE active > 0";

		dol_syslog(get_class($this)."::select_juridique", LOG_DEBUG);
		$resql=$db->query($sql);
		if ($resql)
		{
			$out.= '<select id="select'.$htmlname.'" class="flat maxwidth200onsmartphone selectjuridique'.($morecss?' '.$morecss:'').'" name="'.$htmlname.'" '.$htmloption.'>';
			$num = $db->num_rows($resql);
			$i = 0;
			if ($num)
			{
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$juridiqueArray[$i]['rowid'] = $obj->rowid;
					$juridiqueArray[$i]['code'] = $obj->code;
					$juridiqueArray[$i]['label'] = $obj->label;
					$juridiqueArray[$i]['favorite'] = $obj->favorite;
					$favorite[$i]					= $obj->favorite;
					$label[$i] = dol_string_unaccent($juridiqueArray[$i]['label']);
					$i++;
				}

				if (empty($disablefavorites)) array_multisort($favorite, SORT_DESC, $label, SORT_ASC, $juridiqueArray);
				else $juridiqueArray = dol_sort_array($juridiqueArray, 'label');

				if ($showempty)
				{
					$out.='<option value="">&nbsp;</option>'."\n";
				}

				foreach ($juridiqueArray as $row)
				{
					//if (empty($showempty) && empty($row['rowid'])) continue;
					if (empty($row['rowid'])) continue;

					if (empty($disablefavorites) && $row['favorite'] && $row['code']) $atleastonefavorite++;
					if (empty($row['favorite']) && $atleastonefavorite)
					{
						$atleastonefavorite=0;
						$out.= '<option value="" disabled class="selectoptiondisabledwhite">--------------</option>';
					}
					if ($selected && $selected != '-1' && ($selected == $row['rowid'] || $selected == $row['code'] || $selected == $row['label']) )
					{
						$foundselected=true;
						$out.= '<option value="'.($usecodeaskey?($usecodeaskey=='code2'?$row['code']:$row['rowid']):$row['rowid']).'" selected>';
					}
					else
					{
						$out.= '<option value="'.($usecodeaskey?$row['code']:$row['rowid']).'">';
					}
					if ($row['label']) $out.= dol_trunc($row['label'], $maxlength, 'middle');
					else $out.= '&nbsp;';
					if ($row['code']) $out.= ' ('.$row['code'] . ')';
					$out.= '</option>';
				}
			}
			$out.= '</select>';
		}
		else
		{
			dol_print_error($this->db);
		}

		// Make select dynamic
		include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
		$out .= ajax_combobox('select'.$htmlname);

		return $out;
	}

	/**
	 *    Return Juridique label, code or id from an id, code or label
	 *
	 *    @param      int		$searchkey      Id or code of Juridique to search
	 *    @param      string	$withcode   	'0'=Return label,
	 *    										'1'=Return code + label,
	 *    										'2'=Return code from id,
	 *    										'3'=Return id from code,
	 * 	   										'all'=Return array('id'=>,'code'=>,'label'=>)
	 *    @param      DoliDB	$dbtouse       	Database handler (using in global way may fail because of conflicts with some autoload features)
	 *    @param      Translate	$outputlangs	Langs object for output translation
	 *    @param      int		$entconv       	0=Return value without entities and not converted to output charset, 1=Ready for html output
	 *    @param      int		$searchlabel    Label of Juridique to search (warning: searching on label is not reliable)
	 *    @return     mixed       				Integer with Juridique id or String with Juridique code or translated Juridique name or Array('id','code','label') or 'NotDefined'
	 */
	public function getJuridique($searchkey, $withcode = '', $dbtouse = 0, $outputlangs = '', $entconv = 1, $searchlabel = '')
	{
		global $db,$langs;

		$result='';

		// Check parameters
		if (empty($searchkey) && empty($searchlabel))
		{
			if ($withcode === 'all') return array('id'=>'','code'=>'','label'=>'');
			else return '';
		}
		if (! is_object($dbtouse)) $dbtouse=$db;
		if (! is_object($outputlangs)) $outputlangs=$langs;

		$sql = "SELECT rowid, code, label FROM ".MAIN_DB_PREFIX."c_ultimateimmo_juridique";
		if (is_numeric($searchkey)) $sql.= " WHERE rowid=".$searchkey;
		elseif (! empty($searchkey)) $sql.= " WHERE code='".$db->escape($searchkey)."'";
		else $sql.= " WHERE label='".$db->escape($searchlabel)."'";

		$resql=$dbtouse->query($sql);
		if ($resql)
		{
			$obj = $dbtouse->fetch_object($resql);
			if ($obj)
			{
				$label=((! empty($obj->label) && $obj->label!='-')?$obj->label:'');
				if (is_object($outputlangs))
				{
					$outputlangs->load("dict");
					if ($entconv) $label=($obj->code && ($outputlangs->trans("Juridique".$obj->code)!="Juridique".$obj->code))?$outputlangs->trans("Juridique".$obj->code):$label;
					else $label=($obj->code && ($outputlangs->transnoentitiesnoconv("Juridique".$obj->code)!="Juridique".$obj->code))?$outputlangs->transnoentitiesnoconv("Juridique".$obj->code):$label;
				}
				if ($withcode == 1) $result=$label?"$obj->code - $label":"$obj->code";
				elseif ($withcode == 2) $result=$obj->code;
				elseif ($withcode == 3) $result=$obj->rowid;
				elseif ($withcode === 'all') $result=array('id'=>$obj->rowid,'code'=>$obj->code,'label'=>$label);
				else $result=$label;
			}
			else
			{
				$result='NotDefined';
			}
			$dbtouse->free($resql);
			return $result;
		}
		else dol_print_error($dbtouse, '');
		return 'Error';
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

	public function selectYearImmoCost($selected = '', $htmlname = 'selectyearcost')
	{
		global $conf;
		if (empty($conf->global->ULTIMATEIMMO_TYPECOST_ADJUST)) {
			setEventMessage('Missing configuration TypeCostForAdjust','errors');
		} else {
			$dataYear=array();
			$sql = 'SELECT DISTINCT YEAR(date_start) as yearcost FROM ' . MAIN_DB_PREFIX . 'ultimateimmo_immocost WHERE fk_cost_type IN (' . $conf->global->ULTIMATEIMMO_TYPECOST_ADJUST . ')';
			$sql .= 'ORDER BY date_start DESC';
			$resql=$this->db->query($sql);

			if ($resql) {
				while($obj=$this->db->fetch_object($resql)) {

					$dataYear[$obj->yearcost] = $obj->yearcost;
				}
			} else {
				setEventMessage($this->db->lasterror,'errors');
			}

			print $this::selectarray($htmlname,$dataYear,$selected, 1, 0, 0, '', 0, 0, 0, '', 'minwidth100');
		}
	}

	public function selectYearCompteur($selected = '', $htmlname = 'year')
	{
		dol_include_once('/ultimateimmo/class/immocompteur.class.php');
		$object = new ImmoCompteur($this->db);
		$dataYear=array();
		$sql = 'SELECT DISTINCT YEAR(date_relever) as yearrelever FROM ' . MAIN_DB_PREFIX . $object->table_element;
		$sql .= ' ORDER BY date_relever DESC';
		$resql=$this->db->query($sql);

		if ($resql) {
			while($obj=$this->db->fetch_object($resql)) {
				$dataYear[$obj->yearrelever] = $obj->yearrelever;
			}
		} else {
			setEventMessage($this->db->lasterror,'errors');
		}

		return $this::selectarray($htmlname,$dataYear,$selected,0,0,0,'',0,0,0,'','minwidth100');

	}
}
