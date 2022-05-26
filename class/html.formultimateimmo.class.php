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
		global $db, $langs;

		if (!$rowid) return '';

		$sql = "SELECT label FROM " . MAIN_DB_PREFIX . "c_ultimateimmo_builtdate";
		$sql .= " WHERE rowid='$rowid'";

		dol_syslog("html.formultimateimmo.class::getLabelBuiltDate", LOG_DEBUG);
		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);

			if ($num) {
				$obj = $db->fetch_object($resql);
				$label = ($obj->label != '-' ? $obj->label : '');
				return $label;
			} else {
				return $langs->trans("NotDefined");
			}
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *      Return array with list of possible values for built date
	 *
	 *      @param	string	$order		Sort order by : 'code', 'rowid'...
	 *      @param  int		$activeonly 0=all status of buit date, 1=only the active
	 *		@param	string	$code		code of built date
	 *      @return array       		Array list of built date (id->label if option=0, code->label if option=1)
	 */
	public function builtDateList($order = '', $activeonly = 0, $code = '')
	{
		if (empty($order)) {
			$order = 'code';
		}

		$tab = array();
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.label";
		$sql .= " FROM " . $this->db->prefix() . "c_ultimateimmo_builtdate as tc";
		$sql .= " WHERE tc.active > 0";
		$sql .= $this->db->order($order, 'ASC');		
		//print "sql=".$sql;
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);
				
				if (!$order) {
					$key = $obj->rowid;
				} else {
					$key = $obj->code;
				}

				$tab[$key] = $obj->label != '' ? $obj->label : '';

				$i++;
			}
			return $tab;
		} else {
			$this->error = $this->db->lasterror();
			
			return null;
		}
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
	public function selectBuiltDate($selected = '', $htmlname = 'builtdate', $sortorder = 'code', $showempty = 0, $morecss = '', $output = 1, $forcehidetooltip = 0)
	{
		global $user, $langs, $object;

		$out = '';
		if (is_object($object) && method_exists($object, 'builtDateList')) {
			$lesDates = $this->builtDateList();

			$out .= '<select id="' . $htmlname . '" class="flat valignmiddle' . ($morecss ? ' ' . $morecss : '') . '" name="' . $htmlname . '">';

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

			if ($user->admin && ($forcehidetooltip > 0)) {
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