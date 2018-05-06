<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018 Philippe GRAND 	<philippe.grand@atoo-net.com>
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
 * \file        class/immoreceipt.class.php
 * \ingroup     immobilier
 * \brief       This file is a CRUD class file for ImmoReceipt (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ImmoReceipt
 */
class ImmoReceipt extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'immoreceipt';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'immobilier_immoreceipt';
	/**
	 * @var int  Does immoreceipt support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does immoreceipt support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for immoreceipt. Must be the part after the 'object_' into object_immoreceipt.png
	 */
	public $picto = 'immoreceipt@immobilier';


	/**
	 *  'type' if the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object", 'showoncombobox'=>'1',),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'visible'=>0, 'enabled'=>1, 'position'=>20, 'default'=>1, 'notnull'=>1, 'index'=>1,),
		'fk_rent' => array('type'=>'integer:ImmoRent:immobilier/class/immorent.class.php', 'label'=>'Contract', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1, 'foreignkey'=> 'immobilier_immorent.rowid',),
		'fk_property' => array('type'=>'integer:ImmoProperty:immobilier/class/immoproperty.class.php', 'label'=>'Property', 'visible'=>1, 'enabled'=>1, 'position'=>35, 'notnull'=>-1, 'index'=>1,'foreignkey'=> 'immobilier_immoproperty.rowid', 'searchall'=>1, 'help'=>"LinkToProperty", ),
		'fk_renter' => array('type'=>'integer:ImmoRenter:immobilier/class/immorenter.class.php', 'label'=>'Renter', 'visible'=>1, 'enabled'=>1, 'position'=>40, 'notnull'=>-1, 'index'=>1,'foreignkey'=> 'immobilier_immorenter.rowid', 'searchall'=>1, 'help'=>"LinkToRenter", ),
		'fk_owner' => array('type'=>'integer:ImmoOwner:immobilier/class/immoowner.class.php', 'label'=>'Owner', 'enabled'=>1, 'visible'=>1, 'position'=>45, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToOwner",),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'ThirdParty', 'visible'=>1, 'enabled'=>1, 'position'=>46, 'notnull'=>-1, 'index'=>1, 'searchall'=>1, 'help'=>"LinkToThirparty",),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'visible'=>-1, 'position'=>50, 'notnull'=>-1,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'visible'=>-1, 'position'=>55, 'notnull'=>-1,),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>60, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text",),
		'rentamount' => array('type'=>'price', 'label'=>'RentAmount', 'enabled'=>1, 'visible'=>1, 'position'=>65, 'notnull'=>-1,),
		'chargesamount' => array('type'=>'price', 'label'=>'ChargesAmount', 'enabled'=>1, 'visible'=>1, 'position'=>70, 'notnull'=>-1, 'isameasure'=>'1', 'help'=>"Help text",),
		'total_amount' => array('type'=>'price', 'label'=>'TotalAmount', 'enabled'=>1, 'visible'=>1, 'position'=>75, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'balance' => array('type'=>'price', 'label'=>'Balance', 'enabled'=>1, 'visible'=>1, 'position'=>80, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'paiepartiel' => array('type'=>'price', 'label'=>'PaiePartiel', 'enabled'=>1, 'visible'=>1, 'position'=>85, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'echeance' => array('type'=>'date', 'label'=>'Echeance', 'enabled'=>1, 'visible'=>1, 'position'=>90, 'notnull'=>-1, 'default'=>'null',),
		'vat' => array('type'=>'integer', 'label'=>'Vat', 'enabled'=>1, 'visible'=>1, 'position'=>95, 'notnull'=>-1, 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes'),),
		'paye' => array('type'=>'integer', 'label'=>'Paye', 'enabled'=>1, 'visible'=>-1, 'position'=>100, 'notnull'=>-1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes'),),
		'date_rent' => array('type'=>'date', 'label'=>'DateRent', 'enabled'=>1, 'visible'=>-1, 'position'=>110, 'notnull'=>1,),
		'date_start' => array('type'=>'date', 'label'=>'DateStart', 'enabled'=>1, 'visible'=>-1, 'position'=>120, 'notnull'=>1,),
		'date_end' => array('type'=>'date', 'label'=>'DateEnd', 'enabled'=>1, 'visible'=>-1, 'position'=>130, 'notnull'=>1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'model_pdf' => array('type'=>'varchar(128)', 'label'=>'ModelPdf', 'enabled'=>1, 'visible'=>1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'searchall'=>1,),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Active', '-1'=>'Cancel')),
	);
	public $rowid;
	public $ref;
	public $entity;
	public $label;
	public $fk_rent;
	public $fk_property;
	public $fk_renter;
	public $fk_owner;
	public $fk_soc;
	public $note_public;
	public $note_private;
	public $rentamount;
	public $chargesamount;
	public $total_amount;
	public $balance;
	public $paiepartiel;
	public $vat;
	public $echeance;
	public $paye;
	public $date_rent;
	public $date_start;
	public $date_end;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $model_pdf;
	public $status;
	
	
	public $renter_id;
	public $nomlocataire;
	public $prenomlocataire;
	public $emaillocataire;
	public $nomlocal;
	public $property_id;
	// END MODULEBUILDER PROPERTIES



	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'immoreceiptdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_immoreceipt';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ImmoReceiptline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('immoreceiptdet');
	/**
	 * @var ImmoReceiptLine[]     Array of subtable lines
	 */
	//public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $user;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID)) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled)) $this->fields['entity']['enabled']=0;
	}
	
	/**
	 *	Return next contract ref
	 *
	 *	@param	Societe		$soc	Thirdparty object
	 *	@return string				Free reference for contract
	 */
	function getNextNumRef($soc)
	{
		global $langs, $conf;
		$langs->load("sendings");

		if (!empty($conf->global->IMMOBILIER_ADDON_NUMBER))
		{
			$mybool = false;

			$file = $conf->global->IMMOBILIER_ADDON_NUMBER.".php";
			$classname = $conf->global->IMMOBILIER_ADDON_NUMBER;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

			foreach ($dirmodels as $reldir) {

				$dir = dol_buildpath($reldir."immobilier/core/modules/immobilier/");

				// Load file with numbering class (if found)
				$mybool|=@include_once $dir.$file;
			}

			if (! $mybool)
			{
				dol_print_error('',"Failed to include file ".$file);
				return '';
			}

			$obj = new $classname();
			$numref = "";
			$numref = $obj->getNextValue($soc,$this);

			if ( $numref != "")
			{
				return $numref;
			}
			else
			{
				dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
				return "";
			}
		}
		else
		{
			print $langs->trans("Error")." ".$langs->trans("Error_IMMOBILIER_ADDON_NUMBER_NotDefined");
			return "";
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone and object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $hookmanager, $langs;
	    $error = 0;

	    dol_syslog(__METHOD__, LOG_DEBUG);

	    $object = new self($this->db);

	    $this->db->begin();

	    // Load source object
	    $object->fetchCommon($fromid);
	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);

	    // Clear fields
	    $object->ref = "copy_of_".$object->ref;
	    $object->title = $langs->trans("CopyOf")." ".$object->title;
	    // ...

	    // Create clone
		$object->context['createfromclone'] = 'createfromclone';
	    $result = $object->createCommon($user);
	    if ($result < 0) {
	        $error++;
	        $this->error = $object->error;
	        $this->errors = $object->errors;
	    }

	    // End
	    if (!$error) {
	        $this->db->commit();
	        return $object;
	    } else {
	        $this->db->rollback();
	        return -1;
	    }
	}
	
	/**
	 * Function to concat keys of fields
	 *
	 * @return string
	 
	private function get_field_list()
	{
	    $keys = array_keys($this->fields);
	    return implode(',', $keys);
	}*/
	
	/**
	 * Function to load data from a SQL pointer into properties of current object $this
	 *
	 * @param   stdClass    $obj    Contain data of object from database
	 
	protected function setVarsFromFetchObj(&$obj)
	{
		foreach ($this->fields as $field => $info)
		{
			if($this->isDate($info))
			{
				if(empty($obj->{$field}) || $obj->{$field} === '0000-00-00 00:00:00' || $obj->{$field} === '1000-01-01 00:00:00') $this->{$field} = 0;
				else $this->{$field} = strtotime($obj->{$field});
			}
			elseif($this->isArray($info))
			{
				$this->{$field} = @unserialize($obj->{$field});
				// Hack for data not in UTF8
				if($this->{$field } === FALSE) @unserialize(utf8_decode($obj->{$field}));
			}
			elseif($this->isInt($info))
			{
				if ($field == 'rowid') $this->id = (int) $obj->{$field};
				else $this->{$field} = (int) $obj->{$field};
			}
			elseif($this->isFloat($info))
			{
				$this->{$field} = (double) $obj->{$field};
			}
			elseif($this->isNull($info))
			{
				$val = $obj->{$field};
				// zero is not null
				$this->{$field} = (is_null($val) || (empty($val) && $val!==0 && $val!=='0') ? null : $val);
			}
			else
			{
				$this->{$field} = $obj->{$field};
			}
		}*/
	
	/**
	 * Load object in memory from the database
	 *
	 * @param	int    $id				Id object
	 * @param	string $ref				Ref
	 * @param	string	$morewhere		More SQL filters (' AND ...')
	 * @return 	int         			<0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchCommon($id, $ref = null, $morewhere = '')
	{
		if (empty($id) && empty($ref)) return false;
		
		$array = preg_split("/[\s,]+/", $this->getFieldList());
		$array[0] = 't.rowid';
		$array = array_splice($array, 0, count($array), $array[0]);
		$array = implode(', t.', $array);

		
		$sql = 'SELECT '.$array.',';		
		$sql.= ' lc.rowid as renter_id,';
		$sql.= ' lc.lastname as nomlocataire,';
		$sql.= ' lc.email as emaillocataire,';
		$sql.= ' ll.label as nomlocal,';
		$sql.= ' ll.rowid as property_id,';
		$sql.= ' ic.vat as addtva';	
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element . ' as t';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'immobilier_immorenter as lc ON t.fk_renter = lc.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'immobilier_immoproperty as ll ON t.fk_property = ll.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'immobilier_immorent as ic ON t.fk_rent = ic.rowid';

		if(!empty($id)) $sql.= ' WHERE t.rowid = '.$id;
		else $sql.= ' WHERE t.ref = '.$this->quote($ref, $this->fields['ref']);
		if ($morewhere) $sql.=$morewhere;

		$res = $this->db->query($sql);
		if ($res)
		{
			$obj = $this->db->fetch_object($res);
			if ($obj)
			{
				$this->setVarsFromFetchObj($obj);
				return $this->id;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			$this->error = $this->db->lasterror();
			$this->errors[] = $this->error;
			return -1;
		}
	}
	
	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	/*public function fetchLines()
	{
		$this->lines=array();

		// Load lines with object ImmoReceiptLine

		return count($this->lines)?1:0;
	}*/

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *	@param	string	$option						On what the link point to ('nolink', ...)
     *  @param	int  	$notooltip					1=Disable tooltip
     *  @param  string  $morecss            		Add more css on link
     *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *	@return	string								String with URL
	 */
	function getNomUrl($withpicto=0, $option='', $notooltip=0, $morecss='', $save_lastsearch_value=-1)
	{
		global $db, $conf, $langs;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;

        if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

        $result = '';
        $companylink = '';

        $label = '<u>' . $langs->trans("ImmoReceipt") . '</u>';
        $label.= '<br>';
        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $url = dol_buildpath('/immobilier/receipt/immoreceipt_card.php',1).'?id='.$this->id;

        if ($option != 'nolink')
        {
	        // Add param to save lastsearch_values or not
	        $add_save_lastsearch_values=($save_lastsearch_value == 1 ? 1 : 0);
	        if ($save_lastsearch_value == -1 && preg_match('/list\.php/',$_SERVER["PHP_SELF"])) $add_save_lastsearch_values=1;
	        if ($add_save_lastsearch_values) $url.='&save_lastsearch_values=1';
        }

        $linkclose='';
        if (empty($notooltip))
        {
            if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
            {
                $label=$langs->trans("ShowImmoReceipt");
                $linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
            }
            $linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';
        }
        else $linkclose = ($morecss?' class="'.$morecss.'"':'');

		$linkstart = '<a href="'.$url.'"';
		$linkstart.=$linkclose.'>';
		$linkend='</a>';

		$result .= $linkstart;
		if ($withpicto) $result.=img_object(($notooltip?'':$label), ($this->picto?$this->picto:'generic'), ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
		if ($withpicto != 2) $result.= $this->ref;
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		return $result;
	}

	/**
	 *  Retourne le libelle du status d'un user (actif, inactif)
	 *
	 *  @param	int		$mode          0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *  @return	string 			       Label of status
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->status,$mode);
	}

	/**
	 *  Return the status
	 *
	 *  @param	int		$status        	Id status
	 *  @param  int		$mode          	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       	Label of status
	 */
	static function LibStatut($status,$mode=0)
	{
		global $langs;

		if ($mode == 0)
		{
			$prefix='';
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 1)
		{
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 2)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 3)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5');
		}
		if ($mode == 4)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 5)
		{
			if ($status == 1) return $langs->trans('Enabled').' '.img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return $langs->trans('Disabled').' '.img_picto($langs->trans('Disabled'),'statut5');
		}
		if ($mode == 6)
		{
			if ($status == 1) return $langs->trans('Enabled').' '.img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return $langs->trans('Disabled').' '.img_picto($langs->trans('Disabled'),'statut5');
		}
	}

	/**
	 *	Charge les informations d'ordre info dans l'objet commande
	 *
	 *	@param  int		$id       Id of order
	 *	@return	void
	 */
	function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql.= ' fk_user_creat, fk_user_modif';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' WHERE t.rowid = '.$id;
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture   = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);

		}
		else
		{
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}


	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, paramerts come from the schedule job setup field 'Parameters'
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	//public function doScheduledJob($param1, $param2, ...)
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error='';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}

/**
 * Class ImmoReceiptLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class ImmoReceiptLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
*/