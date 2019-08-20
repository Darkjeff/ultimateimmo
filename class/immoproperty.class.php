<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019 Philippe GRAND  <philippe.grand@atoo-net.com>
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
 * \file        class/immoproperty.class.php
 * \ingroup     ultimateimmo
 * \brief       This file is a CRUD class file for ImmoProperty (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ImmoProperty
 */
class ImmoProperty extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'immoproperty';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'ultimateimmo_immoproperty';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $fk_element='fk_property';
	/**
	 * @var ImmopropertyLine[] Lines
	 */
	public $lines = array();
	
	//public $fieldsforcombobox='ref';
	/**
	 * @var int  Does immoproperty support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does immoproperty support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for immoproperty. Must be the part after the 'object_' into object_immoproperty.png
	 */
	public $picto = 'immoproperty@ultimateimmo';


	/**
	 *  'type' if the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'showoncombobox' if field must be shown into the label of combobox
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object",),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>1, 'visible'=>-1, 'position'=>15, 'notnull'=>1, 'index'=>1,),
		'type_property_id' => array('type'=>'integer', 'label'=>'ImmoProperty_Type', 'enabled'=>1, 'visible'=>-1, 'position'=>20, 'notnull'=>1, 'arrayofkeyval'=>array('1'=>'APA', '2'=>'HOU', '3'=>'LOC', '4'=>'SHO', '5'=>'GAR', '6'=>'BUL')),
		'fk_property' => array('type'=>'integer:ImmoProperty:ultimateimmo/class/immoproperty.class.php', 'label'=>'PropertyParent', 'enabled'=>1, 'visible'=>-1, 'position'=>25, 'notnull'=>-1,),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text", 'showoncombobox'=>'1',),
		'juridique_id' => array('type'=>'integer', 'label'=>'Juridique', 'enabled'=>1, 'visible'=>1, 'position'=>32, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'MonoPropriete', '2'=>'Copropriete'),),
		'datebuilt' => array('type'=>'integer', 'label'=>'DateBuilt', 'enabled'=>1, 'visible'=>1, 'position'=>35, 'notnull'=>-1, 'arrayofkeyval'=>array('1'=>'DateBuilt1', '2'=>'DateBuilt2', '3'=>'DateBuilt3', '4'=>'DateBuilt4', '5'=>'DateBuilt5')),
		'target' => array('type'=>'integer', 'label'=>'Target', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'arrayofkeyval'=>array('0'=>'Location', '1'=>'Vente', '-1'=>'Autre'), 'comment'=>"Rent or sale",),
		'fk_owner' => array('type'=>'integer:ImmoOwner:ultimateimmo/class/immoowner.class.php', 'label'=>'Owner', 'enabled'=>1, 'visible'=>1, 'position'=>45, 'notnull'=>1, 'index'=>1, 'help'=>"LinkToOwner",),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'ThirdParty', 'visible'=>1, 'enabled'=>1, 'position'=>46, 'notnull'=>-1, 'index'=>1, 'searchall'=>1, 'help'=>"LinkToThirparty",),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'visible'=>-1, 'position'=>50, 'notnull'=>-1,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'visible'=>-1, 'position'=>55, 'notnull'=>-1,),
		'address' => array('type'=>'varchar(255)', 'label'=>'Address', 'enabled'=>1, 'visible'=>1, 'position'=>60, 'notnull'=>-1,),
		'building' => array('type'=>'varchar(32)', 'label'=>'Building', 'enabled'=>1, 'visible'=>1, 'position'=>65, 'notnull'=>-1,),
		'staircase' => array('type'=>'varchar(8)', 'label'=>'Staircase', 'enabled'=>1, 'visible'=>1, 'position'=>70, 'notnull'=>-1,),		
		'numfloor' => array('type'=>'varchar(8)', 'label'=>'NumFloor', 'enabled'=>1, 'visible'=>1, 'position'=>75, 'notnull'=>-1,),
		'numflat' => array('type'=>'varchar(8)', 'label'=>'NumFlat', 'enabled'=>1, 'visible'=>1, 'position'=>80, 'notnull'=>-1,),
		'numdoor' => array('type'=>'varchar(8)', 'label'=>'NumDoor', 'enabled'=>1, 'visible'=>1, 'position'=>85, 'notnull'=>-1,),
		'area' => array('type'=>'varchar(8)', 'label'=>'Area', 'enabled'=>1, 'visible'=>1, 'position'=>90, 'notnull'=>-1,),
		'numroom' => array('type'=>'integer', 'label'=>'NumberOfRoom', 'enabled'=>1, 'visible'=>1, 'position'=>92, 'notnull'=>-1,),
		'zip' => array('type'=>'varchar(32)', 'label'=>'Zip', 'enabled'=>1, 'visible'=>1, 'position'=>95, 'notnull'=>-1,),
		'town' => array('type'=>'varchar(64)', 'label'=>'Town', 'enabled'=>1, 'visible'=>1, 'position'=>100, 'notnull'=>-1,),
		'country_id' => array('type'=>'integer', 'label'=>'Country', 'enabled'=>1, 'visible'=>1, 'position'=>110, 'notnull'=>-1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1,),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Active', '-1'=>'Cancel')),
	);
	public $rowid;
	public $ref;
	public $entity;
	public $type_property_id;
	public $fk_property;
	public $label;
	public $juridique_id;
	public $datebuilt;
	public $target;
	public $fk_owner;
	public $fk_soc;
	public $note_public;
	public $note_private;
	public $address;
	public $building;
	public $staircase;
	public $numfloor;
	public $numflat;
	public $numdoor;
	public $area;
	public $numroom;
	public $zip;
	public $town;
	public $country_id;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;
	// END MODULEBUILDER PROPERTIES



	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'immopropertydet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_immoproperty';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ImmoPropertyline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('immopropertydet');
	/**
	 * @var ImmoPropertyLine[]     Array of subtable lines
	 */
	//public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $user, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']=0;
		
		// Translate some data
		$this->fields['status']['arrayofkeyval']=array(0=>$langs->trans('Draft'), 1=>$langs->trans('Active'), -1=>$langs->trans('Cancel'));
		$this->fields['juridique_id']['arrayofkeyval']=array(1=>$langs->trans('MonoPropriete'), 2=>$langs->trans('Copropriete'));
		$this->fields['datebuilt']['arrayofkeyval']=array(1=>$langs->trans('DateBuilt1'), 2=>$langs->trans('DateBuilt2'), 3=>$langs->trans('DateBuilt3'), 4=>$langs->trans('DateBuilt4'), 5=>$langs->trans('DateBuilt5'));
		$this->fields['type_property_id']['arrayofkeyval']=array(1=>$langs->trans('APA'), 2=>$langs->trans('HOU'), 3=>$langs->trans('LOC'), 4=>$langs->trans('SHO'), 5=>$langs->trans('GAR'), 6=>$langs->trans('BUL'));
	}
	
	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function createCommon(User $user, $notrigger = false)
	{
		global $langs, $object;

		$error = 0;

		$now=dol_now();

		$fieldvalues = $this->setSaveQuery();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation']=$this->db->idate($now);
		if (array_key_exists('fk_user_creat', $fieldvalues) && ! ($fieldvalues['fk_user_creat'] > 0)) $fieldvalues['fk_user_creat']=$user->id;
		unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into insert.

		$keys=array();
		$values = array();
		foreach ($fieldvalues as $k => $v) {
			$keys[$k] = $k;
			$value = $this->fields[$k];
			$values[$k] = $this->quote($v, $value);
		}

		// Clean and check mandatory
		foreach($keys as $key)
		{
			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && $values[$key] == '-1') $values[$key]='';
			if (! empty($this->fields[$key]['foreignkey']) && $values[$key] == '-1') $values[$key]='';

			//var_dump($key.'-'.$values[$key].'-'.($this->fields[$key]['notnull'] == 1));
			if (isset($this->fields[$key]['notnull']) && $this->fields[$key]['notnull'] == 1 && ! isset($values[$key]) && is_null($val['default']))
			{
				$error++;
				$this->errors[]=$langs->trans("ErrorFieldRequired", $this->fields[$key]['label']);
			}

			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && empty($values[$key])) $values[$key]='null';
			if (! empty($this->fields[$key]['foreignkey']) && empty($values[$key])) $values[$key]='null';
		}

		if ($error) return -1;

		$this->db->begin();

		if (! $error)
		{
			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.$this->table_element;
			$sql.= ' ('.implode( ", ", $keys ).')';
			$sql.= ' VALUES ('.implode( ", ", $values ).')';

			$res = $this->db->query($sql);
			if ($res===false) {
				$error++;
				$this->errors[] = $this->db->lasterror();
			}
		}

		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
		}

		// Create extrafields
		if (! $error)
		{
			$result=$this->insertExtraFields();
			if ($result < 0) $error++;
		}

		// Triggers
		if (! $error && ! $notrigger)
		{
			// Call triggers
			$result=$this->call_trigger(strtoupper(get_class($this)).'_CREATE',$user);
			if ($result < 0) { $error++; }
			// End call triggers
		}

		// Commit or rollback
		if ($error) {
			$this->db->rollback();
			return -1;
		} else {
			$this->db->commit();
			return $this->id;
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
	 */
	private function get_field_list()
	{
	    $keys = array_keys($this->fields);
	    return implode(',', $keys);
	}
	
	/**
	 * Function to load data into current object this
	 *
	 * @param   stdClass    $obj    Contain data of object from database
	 */
	private function set_vars_by_db(&$obj)
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
	            $this->{$field} = (int) $obj->{$field};
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

	    }
	}

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
		
		global $langs;
		
		$array = preg_split("/[\s,]+/", $this->get_field_list());
		$array[0] = 't.rowid';
		$array = array_splice($array, 0, count($array), array($array[0]));
		$array = implode(', t.', $array);

		$sql = 'SELECT '.$array.',';
		$sql.= ' c.rowid as country_id, c.code as country_code, c.label as country, j.rowid as juridique_id, j.code as juridique_code, j.label as juridique, tp.rowid as type_property_id, tp.code as type_code, tp.label as type';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_ultimateimmo_immoproperty_type as tp ON t.type_property_id = tp.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as c ON t.country_id = c.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_ultimateimmo_juridique as j ON t.juridique_id = j.rowid';

		if(!empty($id)) $sql.= ' WHERE t.rowid = '.$id;
		else $sql.= ' WHERE t.ref = '.$this->quote($ref, $this->fields['ref']);
		if ($morewhere) $sql.=$morewhere;
		
		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$res = $this->db->query($sql);
		if ($res)
		{
    		if ($obj = $this->db->fetch_object($res))
    		{
    		    if ($obj)
    		    {
        			$this->id = $id;
        			$this->set_vars_by_db($obj);

        			$this->date_creation = $this->db->jdate($obj->date_creation);
        			$this->tms = $this->db->jdate($obj->tms);
					
					$this->juridique_id	= $obj->juridique_id;
					$this->juridique_code = $obj->juridique_code;
					$this->juridique=$obj->juridique;
					
					$this->type_property_id	= $obj->type_property_id;
					$this->type_code = $obj->type_code;				
					$this->type=$obj->type;
					
        			$this->country_id	= $obj->country_id;
					$this->country_code	= $obj->country_code;
					if ($langs->trans("Country".$obj->country_code) != "Country".$obj->country_code)
						$this->country = $langs->transnoentitiesnoconv("Country".$obj->country_code);
					else
						$this->country=$obj->country;
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
	
	 function fetchAllByBuilding($activ = 1) 
	 {
		global $user;
		
		$sql = "SELECT * ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as l";
		$sql .= " WHERE l.status = " . $activ . "  ";
		$sql .= " AND l.fk_property = " . $this->id;
		$sql .= " ORDER BY label";
		
		dol_syslog(get_class($this) . "::fetchAllByBuilding sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) 
		{			
			$this->line = array ();
			$num = $this->db->num_rows($resql);
			
			while ($obj = $this->db->fetch_object($resql)) {
							
				$line = new ImmopropertyLine();
				
				$line->id = $obj->rowid;
				$line->fk_property = $obj->fk_property;
				$line->label = $obj->label;
				$line->address = $obj->address;
				$line->status = $obj->status;
				$line->area = $obj->area;
				$line->fk_owner = $obj->fk_owner;
				
				$this->lines[] = $line;

			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetchAllByBuilding " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	/*public function fetchLines()
	{
		$this->lines=array();

		// Load lines with object ImmoPropertyLine

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

        $label = '<u>' . $langs->trans("ImmoProperty") . '</u>';
        $label.= '<br>';
        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		$label.= '<br>';
        $label.= '<b>' . $langs->trans('Label') . ':</b> ' . $this->label;

        $url = dol_buildpath('/ultimateimmo/property/immoproperty_card.php',1).'?id='.$this->id;

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
                $label=$langs->trans("ShowImmoProperty");
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
	 *    Return country label, code or id from an id, code or label
	 *
	 *    @param      int		$searchkey      Id or code of country to search
	 *    @param      string	$withcode   	'0'=Return label,
	 *    										'1'=Return code + label,
	 *    										'2'=Return code from id,
	 *    										'3'=Return id from code,
	 * 	   										'all'=Return array('id'=>,'code'=>,'label'=>)
	 *    @param      DoliDB	$dbtouse       	Database handler (using in global way may fail because of conflicts with some autoload features)
	 *    @param      Translate	$outputlangs	Langs object for output translation
	 *    @param      int		$entconv       	0=Return value without entities and not converted to output charset, 1=Ready for html output
	 *    @param      int		$searchlabel    Label of country to search (warning: searching on label is not reliable)
	 *    @return     mixed       				String with country code or translated country name or Array('id','code','label')
	 */
	function getCountry($searchkey, $withcode='', $dbtouse=0, $outputlangs='', $entconv=1, $searchlabel='')
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

		$sql = "SELECT rowid, code, label FROM ".MAIN_DB_PREFIX."c_country";
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
					if ($entconv) $label=($obj->code && ($outputlangs->trans("Country".$obj->code)!="Country".$obj->code))?$outputlangs->trans("Country".$obj->code):$label;
					else $label=($obj->code && ($outputlangs->transnoentitiesnoconv("Country".$obj->code)!="Country".$obj->code))?$outputlangs->transnoentitiesnoconv("Country".$obj->code):$label;
				}
				if ($withcode == 1) $result=$label?"$obj->code - $label":"$obj->code";
				else if ($withcode == 2) $result=$obj->code;
				else if ($withcode == 3) $result=$obj->rowid;
				else if ($withcode === 'all') $result=array('id'=>$obj->rowid,'code'=>$obj->code,'label'=>$label);
				else $result=$label;
			}
			else
			{
				$result='NotDefined';
			}
			$dbtouse->free($resql);
			return $result;
		}
		else dol_print_error($dbtouse,'');
		return 'Error';
	}
	
	/**
	 *    Return ImmoProperty_Type label, code or id from an id, code or label
	 *
	 *    @param      int		$searchkey      Id or code of ImmoProperty_Type to search
	 *    @param      string	$withcode   	'0'=Return label,
	 *    										'1'=Return code + label,
	 *    										'2'=Return code from id,
	 *    										'3'=Return id from code,
	 * 	   										'all'=Return array('id'=>,'code'=>,'label'=>)
	 *    @param      DoliDB	$dbtouse       	Database handler (using in global way may fail because of conflicts with some autoload features)
	 *    @param      Translate	$outputlangs	Langs object for output translation
	 *    @param      int		$entconv       	0=Return value without entities and not converted to output charset, 1=Ready for html output
	 *    @param      int		$searchlabel    Label of ImmoProperty_Type to search (warning: searching on label is not reliable)
	 *    @return     mixed       				Integer with ImmoProperty_Type id or String with ImmoProperty_Type code or translated ImmoProperty_Type name or Array('id','code','label') or 'NotDefined'
	 */
	function getPropertyTypeLabel($searchkey, $withcode = '', $dbtouse = 0, $outputlangs = '', $entconv = 1, $searchlabel = '')
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

		$sql = "SELECT rowid, code, label FROM ".MAIN_DB_PREFIX."c_ultimateimmo_immoproperty_type";
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
					if ($entconv) $label=($obj->code && ($outputlangs->trans("ImmoProperty_Type".$obj->code)!="ImmoProperty_Type".$obj->code))?$outputlangs->trans("ImmoProperty_Type".$obj->code):$label;
					else $label=($obj->code && ($outputlangs->transnoentitiesnoconv("ImmoProperty_Type".$obj->code)!="ImmoProperty_Type".$obj->code))?$outputlangs->transnoentitiesnoconv("ImmoProperty_Type".$obj->code):$label;
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
	 * CAN BE A CRON TASK
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		$this->output = '';
		$this->error='';

		dol_syslog(__METHOD__, LOG_DEBUG);

		// ...

		return 0;
	}
}

/**
 * Class ImmopropertyLine
 */
class ImmopropertyLine
{
	/**
	 * @var int ID
	 */
	public $id;

	public $entity;
	public $type_property_id;
	public $fk_property;
	public $fk_owner;
	public $label;
	public $address;
	public $building;
	public $staircase;
	public $numfloor;
	public $numdoor;
	public $area;
	public $numroom;
	public $zip;
	public $town;
	public $country_id;
	public $status;
	public $note_private;
	public $note_public;
	public $datep = '';
	public $tms = '';
	public $fk_user_creat;
	public $fk_user_modif;
}