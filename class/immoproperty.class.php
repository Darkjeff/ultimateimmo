<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021 Philippe GRAND  <philippe.grand@atoo-net.com>
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

	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid'         => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -2, 'noteditable' => 1, 'notnull' => 1, 'index' => 1, 'position' => 1, 'comment' => 'Id'),
		'ref'           => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => 1, 'visible' => 1, 'noteditable' => 0, 'default' => '', 'notnull' => 1,  'default'=>'(PROV)', 'index' => 1, 'position' => 10, 'searchall' => 1, 'comment' => 'Reference of object'),
		'entity'        => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'visible' => 0, 'notnull' => 1, 'default' => 1, 'index' => 1, 'position' => 20),
		'property_type_id' => array('type' => 'integer', 'label' => 'ImmoProperty_Type', 'enabled' => 1, 'visible' => -1, 'position' => 20, 'notnull' => 1),
		'fk_property'   => array('type' => 'integer:ImmoProperty:ultimateimmo/class/immoproperty.class.php', 'label' => 'PropertyParent', 'enabled' => 1, 'visible' => -1, 'position' => 25, 'notnull' => -1), 
		'label' => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'visible' => 1, 'position' => 30, 'showoncombobox' => 1, 'searchall' => 1, 'css' => 'minwidth200', 'help' => 'Help text',),
		'juridique_id'  => array('type' => 'integer', 'label' => 'Juridique', 'enabled' => 1, 'visible' => 1, 'position' => 32, 'notnull' => -1, 'arrayofkeyval' => array('1' => 'MonoPropriete', '2' => 'Copropriete')),
		'datebuilt'     => array('type' => 'integer', 'label' => 'DateBuilt', 'enabled' => 1, 'visible' => 1, 'position' => 35, 'notnull' => -1,),
		'target'        => array('type' => 'integer', 'label' => 'Target', 'enabled' => 1, 'visible' => 1, 'position' => 40, 'notnull' => -1, 'arrayofkeyval' => array('1' => 'Location', '2' => 'Vente', '3' => 'Autre'), 'comment' => "Rent or sale"),
		'fk_owner'      => array('type' => 'integer:ImmoOwner:ultimateimmo/owner/class/immoowner.class.php:1:status=1', 'label' => 'Owner', 'enabled' => 1, 'visible' => 1, 'position' => 45, 'notnull' => 1, 'index' => 1, 'help' => "LinkToOwner"),
		'fk_soc' 		=> array('type' => 'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)', 'picto'=>'company', 'label' => 'ThirdParty', 'visible' => 1, 'enabled' => 1, 'position' => 46, 'notnull' => -1, 'index' => 1, 'help' => 'LinkToThirparty'),
		'address'       => array('type' => 'varchar(255)', 'label' => 'Address', 'enabled' => 1, 'visible' => 1, 'position' => 60, 'notnull' => -1),
		'zip'           => array('type' => 'varchar(32)', 'label' => 'Zip', 'enabled' => 1, 'visible' => 1, 'position' => 95, 'notnull' => -1),
		'town'          => array('type' => 'varchar(64)', 'label' => 'Town', 'enabled' => 1, 'visible' => 1, 'position' => 100, 'notnull' => -1),
		'country_id' => array('type' => 'varchar:c_country:label:code:rowid', 'label' => 'Country', 'enabled' => 1, 'visible' => 1, 'position' => 110, 'notnull' => -1,),
		'building'      => array('type' => 'varchar(32)', 'label' => 'Building', 'enabled' => 1, 'visible' => 1, 'position' => 65, 'notnull' => -1),
		'staircase'     => array('type' => 'varchar(8)', 'label' => 'Staircase', 'enabled' => 1, 'visible' => 1, 'position' => 70, 'notnull' => -1),
		'numfloor'      => array('type' => 'varchar(8)', 'label' => 'NumFloor', 'enabled' => 1, 'visible' => 1, 'position' => 75, 'notnull' => -1),
		'numflat'       => array('type' => 'varchar(8)', 'label' => 'NumFlat', 'enabled' => 1, 'visible' => 1, 'position' => 80, 'notnull' => -1),
		'numdoor'       => array('type' => 'varchar(8)', 'label' => 'NumDoor', 'enabled' => 1, 'visible' => 1, 'position' => 85, 'notnull' => -1),
		'area'          => array('type' => 'varchar(8)', 'label' => 'Area', 'enabled' => 1, 'visible' => 1, 'position' => 90, 'notnull' => -1),
		'numroom'       => array('type' => 'integer', 'label' => 'NumberOfRoom', 'enabled' => 1, 'visible' => 1, 'position' => 92, 'notnull' => -1),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'visible' => -2, 'position' => 500, 'notnull' => 1, 'default'=>'CURRENT_TIMESTAMP',),
		'tms'           => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -2, 'position' => 501, 'notnull' => 1),
		'fk_user_creat' => array('type' => 'integer', 'label' => 'UserAuthor', 'picto'=>'user', 'enabled' => 1, 'visible' => -2, 'position' => 510, 'notnull' => 1),
		'fk_user_modif' => array('type' => 'integer', 'label' => 'UserModif', 'picto'=>'user', 'enabled' => 1, 'visible' => -2, 'position' => 511, 'notnull' => -1),
		'import_key'    => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => 1, 'visible' => -2, 'position' => 1000, 'notnull' => -1),
		'status'        => array('type' => 'smallint', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'notnull' => 1, 'default' => 0, 'index' => 1, 'position' => 1000, 'arrayofkeyval' => array(0 => 'Draft', 1 => 'Validated', 9 => 'Canceled')),
		'note_public'   => array('type' => 'html', 'label' => 'NotePublic', 'enabled' => 1, 'visible' => -1, 'position' => 50, 'notnull' => -1),
		'note_private'  => array('type' => 'html', 'label' => 'NotePrivate', 'enabled' => 1, 'visible' => -1, 'position' => 55, 'notnull' => -1),
	);

	/**
	 * @var int ID
	 */
	public $rowid;

	/**
	 * @var string Ref
	 */
	public $ref;

	/**
	 * @var int Entity
	 */
	public $entity;

	/**
	 * @var int Property_type_id
	 */
	public $property_type_id;
	
	/**
	 * @var int fk_property
	 */
	public $fk_property;

	/**
     * @var string label
     */
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

	/**
	 * @var int Status
	 */
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
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data
		$this->fields['status']['arrayofkeyval'] = array(1 => $langs->trans('PropertyTypeStatusActive'), 9 => $langs->trans('PropertyTypeStatusCancel'));
		$this->fields['juridique_id']['arrayofkeyval'] = array(1 => $langs->trans('MonoPropriete'), 2 => $langs->trans('Copropriete'));
		$this->fields['target']['arrayofkeyval'] = array(1 => $langs->trans('UltimateLocation'), 2 => $langs->trans('Vente'), 3 => $langs->trans('Autre'));
		/*$this->fields['datebuilt']['arrayofkeyval'] = array(1 => $langs->trans('DateBuilt1'), 2 => $langs->trans('DateBuilt2'), 3 => $langs->trans('DateBuilt3'), 4 => $langs->trans('DateBuilt4'), 5 => $langs->trans('DateBuilt5'));*/
		$this->fields['property_type_id']['arrayofkeyval'] = array(1 => $langs->trans('APA'), 2 => $langs->trans('HOU'), 3 => $langs->trans('LOC'), 4 => $langs->trans('SHO'), 5 => $langs->trans('GAR'), 6 => $langs->trans('BUL'));
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

		$now = dol_now();

		$fieldvalues = $this->setSaveQuery();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation'] = $this->db->idate($now);
		//if (array_key_exists('birth', $fieldvalues) && empty($fieldvalues['birth'])) $fieldvalues['birth'] = $this->db->jdate($object->birth);
		if (array_key_exists('fk_user_creat', $fieldvalues) && !($fieldvalues['fk_user_creat'] > 0)) $fieldvalues['fk_user_creat'] = $user->id;
		unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into insert.

		$keys = array();
		$values = array();
		foreach ($fieldvalues as $k => $v) {
			$keys[$k] = $k;
			$value = $this->fields[$k];
			$values[$k] = $this->quote($v, $value);
		}

		// Clean and check mandatory
		foreach ($keys as $key) {
			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && $values[$key] == '-1') $values[$key] = '';
			if (!empty($this->fields[$key]['foreignkey']) && $values[$key] == '-1') $values[$key] = '';

			//var_dump($key.'-'.$values[$key].'-'.($this->fields[$key]['notnull'] == 1));
			if (isset($this->fields[$key]['notnull']) && $this->fields[$key]['notnull'] == 1 && !isset($values[$key]) && is_null($this->fields[$key]['default'])) {
				$error++;
				$this->errors[] = $langs->trans("ErrorFieldRequired", $this->fields[$key]['label']);
			}

			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && empty($values[$key])) $values[$key] = 'null';
			if (!empty($this->fields[$key]['foreignkey']) && empty($values[$key])) $values[$key] = 'null';
		}

		if ($error) return -1;

		$this->db->begin();

		if (!$error) {
			$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element;
			$sql .= ' (' . implode(", ", $keys) . ')';
			$sql .= ' VALUES (' . implode(", ", $values) . ')';

			$res = $this->db->query($sql);
			if ($res === false) {
				$error++;
				$this->errors[] = $this->db->lasterror();
			}
		}

		if (!$error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
			$this->birth = $this->db->jdate($res->birth);
		}

		// If we have a field ref with a default value of (PROV)
		if (!$error) {
			if (key_exists('ref', $this->fields) && $this->fields['ref']['notnull'] > 0 && !is_null($this->fields['ref']['default']) && $this->fields['ref']['default'] == '(PROV)') {
				$sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element . " SET ref = '(PROV" . $this->id . ")' WHERE (ref = '(PROV)' OR ref = '') AND rowid = " . $this->id;
				$resqlupdate = $this->db->query($sql);

				if ($resqlupdate === false) {
					$error++;
					$this->errors[] = $this->db->lasterror();
				} else {
					$this->ref = '(PROV' . $this->id . ')';
				}
			}
		}

		// Create extrafields
		if (!$error) {
			$result = $this->insertExtraFields();
			if ($result < 0) $error++;
		}

		// Create lines
		if (!empty($this->table_element_line) && !empty($this->fk_element)) {
			$num = (is_array($this->lines) ? count($this->lines) : 0);
			for ($i = 0; $i < $num; $i++) {
				$line = $this->lines[$i];

				$keyforparent = $this->fk_element;
				$line->$keyforparent = $this->id;

				// Test and convert into object this->lines[$i]. When coming from REST API, we may still have an array
				//if (! is_object($line)) $line=json_decode(json_encode($line), false);  // convert recursively array into object.
				if (!is_object($line)) $line = (object) $line;

				$result = $line->create($user, 1);
				if ($result < 0) {
					$this->error = $this->db->lasterror();
					$this->db->rollback();
					return -1;
				}
			}
		}

		// Triggers
		if (!$error && !$notrigger) {
			// Call triggers
			$result = $this->call_trigger(strtoupper(get_class($this)) . '_CREATE', $user);
			if ($result < 0) {
				$error++;
			}
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
		$resultcreate = $this->createCommon($user, $notrigger);

		return $resultcreate;
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
		global $langs, $extrafields;
	    $error = 0;

	    dol_syslog(__METHOD__, LOG_DEBUG);

	    $object = new self($this->db);

	    $this->db->begin();

	    // Load source object
	    $result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) {
			$object->fetchLines();
		}

	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);


	    // Clear fields
	    if (property_exists($object, 'ref')) {
			$object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		}
		if (property_exists($object, 'label')) {
			$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		}
		if (property_exists($object, 'status')) {
			$object->status = self::STATUS_DRAFT;
		}
		if (property_exists($object, 'date_creation')) {
			$object->date_creation = dol_now();
		}
		if (property_exists($object, 'date_modification')) {
			$object->date_modification = null;
		}
	    // ...
	    // Clear extrafields that are unique
	    if (is_array($object->array_options) && count($object->array_options) > 0)
	    {
	    	$extrafields->fetch_name_optionals_label($this->table_element);
	    	foreach ($object->array_options as $key => $option)
	    	{
	    		$shortkey = preg_replace('/options_/', '', $key);
	    		if (!empty($extrafields->attributes[$this->element]['unique'][$shortkey]))
	    		{
	    			//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
	    			unset($object->array_options[$key]);
	    		}
	    	}
	    }

	    // Create clone
		$object->context['createfromclone'] = 'createfromclone';
	    $result = $object->createCommon($user);
	    if ($result < 0) {
	        $error++;
	        $this->error = $object->error;
	        $this->errors = $object->errors;
	    }

		if (!$error) {
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0) {
				$error++;
			}
		}

		if (!$error) {
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid) {
				if ($this->copy_linked_contact($object, 'external') < 0) {
					$error++;
				}
			}
		}

	    unset($object->context['createfromclone']);

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
	        /*elseif($this->isNull($info))
	        {
	            $val = $obj->{$field};
	            // zero is not null
	            $this->{$field} = (is_null($val) || (empty($val) && $val!==0 && $val!=='0') ? null : $val);
	        }*/
	        else
	        {
	            $this->{$field} = $obj->{$field};
	        }

	    }
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param	int     $id				Id object
	 * @param	string  $ref			Ref
	 * @param	string	$morewhere		More SQL filters (' AND ...')
	 * @return 	int         			<0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchCommon($id, $ref = null, $morewhere = '')
	{
		if (empty($id) && empty($ref) && empty($morewhere)) return -1;

		global $langs;

		$array = preg_split("/[\s,]+/", $this->get_field_list());
		$array[0] = 't.rowid';
		$array = array_splice($array, 0, count($array), array($array[0]));
		$array = implode(', t.', $array);

		$sql = 'SELECT ' . $array . ',';
		
		$sql .= ' country.rowid as country_id, country.code as country_code, country.label as country, j.rowid as juridique_id, j.code as juridique_code, j.label as juridique, tp.rowid as property_type_id, tp.code as type_code, tp.label as type';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_ultimateimmo_immoproperty_type as tp ON t.property_type_id = tp.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_country as country ON t.country_id = country.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_ultimateimmo_juridique as j ON t.juridique_id = j.rowid';
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s ON s.rowid = t.fk_soc";

		if (!empty($id)) $sql .= ' WHERE t.rowid = ' . $id;
		else $sql .= ' WHERE t.ref = ' . $this->quote($ref, $this->fields['ref']);
		if ($morewhere) $sql .= $morewhere;
		//print_r($sql);exit;
		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$res = $this->db->query($sql);
		if ($res) {
			if ($obj = $this->db->fetch_object($res)) {
				if ($obj) {
					$this->id = $id;
					$this->set_vars_by_db($obj);
					
					$this->date_creation = $this->db->jdate($obj->date_creation);
					$this->tms = $this->db->jdate($obj->tms);

					/*$this->juridique_id	= $obj->juridique_id;
					$this->juridique_code = $obj->juridique_code;
					$this->juridique=$obj->juridique;*/
					
					//var_dump($obj);exit;
					$this->property_type_id	= $obj->property_type_id;
					$this->type_code = $obj->type_code;
					$this->type = $obj->type;

					$this->country_id	= $obj->country_id;
					$this->country_code	= $obj->country_code;
					if ($langs->trans("Country" . $obj->country_code) != "Country" . $obj->country_code) {
						$this->country = $langs->transnoentitiesnoconv("Country" . $obj->country_code);
					} else {
						$this->country = $obj->country;
					}
					$this->setVarsFromFetchObj($obj);
					
					return $this->id;
				} else {
					return 0;
				}
			} else {
				$this->error = "Error ".$this->db->lasterror();
	            $this->errors[] = "Error ".$this->db->lasterror();
				return -1;
			}
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = "Error ".$this->db->lasterror();
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
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
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
		if ($resql) {
			$this->line = array();
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {

				$line = new ImmopropertyLine($this->db);
				
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
			return -1;
		}
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		// Load lines with object ImmoPropertyLine

		return count($this->lines) ? 1 : 0;
	}

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
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}

	/**
     *  Return a link to the object card (with optionaly the picto)
     *
     *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
     *  @param  string  $option                     On what the link point to ('nolink', ...)
     *  @param  int     $notooltip                  1=Disable tooltip
     *  @param  string  $morecss                    Add more css on link
     *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
     *  @return	string                              String with URL
     */
    public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
    {
        global $conf, $langs, $hookmanager;

        if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

        $result = '';

		$label = img_picto('', $this->picto) . '<u>' . $langs->trans("ImmoProperty") . '</u>';
        $label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;
		$label .= '<br>';
        $label .= '<b>' . $langs->trans('Label') . ':</b> ' . $this->label;
        if (isset($this->status)) {
        	$label.= '<br><b>' . $langs->trans("Status").":</b> ".$this->getLibStatut(5);
        }

        $url = dol_buildpath('/ultimateimmo/property/immoproperty_card.php', 1).'?id='.$this->id;

        if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

        $linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowImmoOwner");
				$linkclose .= ' alt="' . dol_escape_htmltag($label, 1) . '"';
			}
			$linkclose .= ' title="' . dol_escape_htmltag($label, 1) . '"';
			$linkclose .= ' class="classfortooltip' . ($morecss ? ' ' . $morecss : '') . '"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink') {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink') {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->label;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('immopropertydao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

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
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("mymodule");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
		}

		$statusType = 'status'.$status;
		if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql .= ' WHERE t.rowid = '.$id;
		$result = $this->db->query($sql);
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
					$this->user_creation = $cuser;
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
					$this->user_cloture = $cluser;
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

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *      Return array with list of possible values for built date
	 *
	 *      @param	string	$order		Sort order by : 'position', 'code', 'rowid'...
	 *      @param  int		$option     0=Return array id->label, 1=Return array code->label
	 *      @param  int		$activeonly 0=all status of buit date, 1=only the active
	 *		@param	string	$code		code of built date
	 *      @return array       		Array list of built date (id->label if option=0, code->label if option=1)
	 */
	public function builtDateList($order = 'code', $option = 0, $activeonly = 0, $code = '')
	{
		// phpcs:enable
		global $langs;

		if (empty($order)) {
			$order = 'code';
		}

		$tab = array();
		$sql = "SELECT DISTINCT tc.rowid, tc.code, tc.label";
		$sql .= " FROM " . $this->db->prefix() . "c_ultimateimmo_builtdate as tc";
		$sql .= " WHERE tc.element='" . $this->db->escape($this->element) . "'";
		if ($activeonly == 1) {
			$sql .= " AND tc.active=1"; // only the active dates
		}
		if (!empty($code)) {
			$sql .= " AND tc.code='" . $this->db->escape($code) . "'";
		}
		$sql .= $this->db->order($order, 'ASC');

		//print "sql=".$sql;
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);

				$transkey = "BuiltDate_" . $this->element . $obj->code;
				$builtDateLabel = ($langs->trans($transkey) != $transkey ? $langs->trans($transkey) : $obj->label);
				if (empty($option)) {
					$tab[$obj->rowid] = $builtDateLabel;
				} else {
					$tab[$obj->code] = $builtDateLabel;
				}
				$i++;
			}
			return $tab;
		} else {
			$this->error = $this->db->lasterror();
			//dol_print_error($this->db);
			return null;
		}
	}

	/**
	 *  Return a select list with built dates
	 *
	 *  @param	object		$object         	Object to use to find date of building
	 *  @param  string		$selected       	Default selected value
	 *  @param  string		$htmlname			HTML select name
	 *  @param  string		$sortorder			Sort criteria ('position', 'code', ...)
	 *  @param  int			$showempty      	1=Add en empty line
	 *  @param  string      $morecss        	Add more css to select component
	 *  @param  int      	$output         	0=return HTML, 1= direct print
	 *  @param	int			$forcehidetooltip	Force hide tooltip for admin
	 *  @return	string|void						Depending on $output param, return the HTML select list (recommended method) or nothing
	 */
	public function selectBuiltDate($object, $selected = '', $htmlname = 'builtdate', $sortorder = 'code', $showempty = 0, $morecss = '', $output = 1, $forcehidetooltip = 0)
	{
		global $user, $langs;

		$out = '';
		if (is_object($object) && method_exists($object, 'builtDateList')) {
			$lesDates = $object->builtDateList($sortorder, 0, 1);
			//var_dump($lesDates);exit;
			$out .= '<select class="flat valignmiddle' . ($morecss ? ' ' . $morecss : '') . '" name="' . $htmlname . '" id="' . $htmlname . '">';
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
			if ($user->admin && empty($forcehidetooltip)) {
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
	 
	/*function getPropertyTypeLabel($searchkey, $withcode = '', $dbtouse = 0, $outputlangs = '', $entconv = 1, $searchlabel = '')
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
	}*/

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
	 * 	Create an array of owner lines
	 *
	 * 	@return int		>0 if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new ImmopropertyLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_immoowner = '.$this->id));

		if (is_numeric($result))
		{
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
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

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}

/**
 * Class ImmopropertyLine
 */
class ImmopropertyLine extends CommonObjectLine
{
	// To complete with content of an object MyObjectLine
	// We should have a field rowid, fk_myobject and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	public $id;

	public $entity;
	public $property_type_id;
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
	public $date_creation = '';
	public $tms = '';
	public $fk_user_creat;
	public $fk_user_modif;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}
}