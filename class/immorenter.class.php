<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021 Philippe GRAND 	<philippe.grand@atoo-net.com>
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
 * \file        class/immorenter.class.php
 * \ingroup     ultimateimmo
 * \brief       This file is a CRUD class file for ImmoRenter (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ImmoRenter
 */
class ImmoRenter extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'immorenter';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'ultimateimmo_immorenter';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $fk_element='fk_renter';
	/**
	 * @var int  Does immorenter support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does immorenter support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for immorenter. Must be the part after the 'object_' into object_immorenter.png
	 */
	public $picto = 'immorenter@ultimateimmo';

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

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid'         => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'noteditable' => 1, 'notnull' => 1, 'index' => 1, 'position' => 1, 'comment' => 'Id'),
		'ref'           => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => 1, 'visible' => 1, 'noteditable' => 0, 'default' => '', 'notnull' => 1,  'default'=>'(PROV)', 'index' => 1, 'position' => 10, 'searchall' => 1, 'comment' => 'Reference of object'),
		'entity'        => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'visible' => 0, 'notnull' => 1, 'default' => 1, 'index' => 1, 'position' => 20),
		'fk_rent' => array('type' => 'integer:ImmoRent:ultimateimmo/class/immorent.class.php', 'label' => 'ImmoRent', 'visible' => 0, 'enabled' => 0, 'position' => 25, 'notnull' => -1, 'index' => 1, 'foreignkey' => 'ultimateimmo_immorent.rowid', 'searchall' => 1, 'help' => "LinkToRent",),
		'fk_owner' => array('type' => 'integer:ImmoOwner:ultimateimmo/class/immoowner.class.php', 'label' => 'Owner', 'enabled' => 0, 'visible' => 0, 'position' => 30, 'notnull' => -1, 'index' => 1, 'foreignkey'=>'ultimateimmo_immoowner.fk_owner', 'help' => "LinkToOwner",),
		'fk_soc' 		=> array('type' => 'integer:Societe:societe/class/societe.class.php:1:(status:=:1)', 'label' => 'ThirdParty', 'visible' => 1, 'enabled' => 1, 'position' => 35, 'notnull' => -1, 'index' => 1, 'help' => 'LinkToThirdparty'),
		'societe' => array('type' => 'varchar(128)', 'label' => 'Societe', 'visible' => 1, 'enabled' => 1, 'position' => 36, 'notnull' => -1,),
		'note_public' => array('type' => 'html', 'label' => 'NotePublic', 'visible' => 1, 'enabled' => 1, 'position' => 40, 'notnull' => -1,),
		'note_private' => array('type' => 'html', 'label' => 'NotePrivate', 'visible' => 1, 'enabled' => 1, 'position' => 50, 'notnull' => -1,),
		'civility_id' => array('type' => 'integer', 'label' => 'Civility', 'visible' => -1, 'enabled' => 1, 'position' => 60, 'notnull' => 1,),
		'firstname' => array('type' => 'varchar(255)', 'label' => 'Firstname', 'visible' => 1, 'enabled' => 1, 'position' => 65, 'showoncombobox'=>1, 'notnull' => -1, 'searchall' => 1,),
		'lastname' => array('type' => 'varchar(255)', 'label' => 'Lastname', 'visible' => 1, 'enabled' => 1, 'position' => 70, 'showoncombobox'=>1, 'notnull' => -1, 'searchall' => 1,),
		'email' => array('type' => 'varchar(255)', 'label' => 'Email', 'visible' => 1, 'enabled' => 1, 'position' => 75, 'notnull' => -1,),
		'birth' => array('type' => 'date', 'label' => 'BirthDay', 'visible' => 1, 'enabled' => 1, 'position' => 80, 'notnull' => 1,),
		'country_id' => array('type' => 'integer:Ccountry:core/class/ccountry.class.php', 'label' => 'ImmoCountry', 'enabled' => 1, 'visible' => 1, 'position' => 82, 'notnull' => -1,),
		'town' => array('type' => 'varchar(255)', 'label' => 'BirthCountry', 'enabled' => 1, 'visible' => 1, 'position' => 83, 'notnull' => -1,),
		'phone' => array('type' => 'varchar(30)', 'label' => 'Phone', 'visible' => -1, 'enabled' => 1, 'position' => 85, 'notnull' => -1,),
		'phone_mobile' => array('type' => 'varchar(30)', 'label' => 'PhoneMobile', 'visible' => 1, 'enabled' => 1, 'position' => 90, 'notnull' => -1,),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'visible' => -2, 'enabled' => 1, 'position' => 500, 'notnull' => 1,),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'visible' => -2, 'enabled' => 1, 'position' => 501, 'notnull' => 1,),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1, 'visible' => -2, 'notnull' => 1, 'position' => 510, 'foreignkey' => 'user.rowid'),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'enabled' => 1, 'visible' => -2, 'notnull' => -1, 'position' => 511),
		'import_key'    => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => 1, 'visible' => -2, 'notnull' => -1, 'index' => 0, 'position' => 1000),
		'status'        => array('type' => 'smallint', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'notnull' => 1, 'default' => 0, 'index' => 1, 'position' => 1000, 'arrayofkeyval' => array(0 => 'ImmoRenterStatusDisabled', 1 => 'ImmoRenterStatusActive', 9 => 'Canceled')),

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

	public $fk_rent;

	public $fk_owner;

	public $fk_soc;

	public $societe;

	public $note_public;

	public $note_private;

	public $civility_id;
	public $civility_code;
	public $civility;

	public $firstname;

	public $lastname;

	public $email;

	public $birth;

	public $country_id;

	public $phone;

	public $phone_mobile;

	/**
     * @var integer|string date_creation
     */
	public $date_creation;

	/**
     * @var integer tms
     */
	public $tms;

	/**
     * @var int ID
     */
	public $fk_user_creat;

	/**
     * @var int ID
     */
	public $fk_user_modif;

	/**
     * @var string import_key
     */
	public $import_key;

	/**
	 * @var int Status
	 */
	public $status;


	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'immorenterdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_immorenter';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ImmoRenterline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('immorenterdet');
	/**
	 * @var ImmoRenterLine[]     Array of subtable lines
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

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->mymodule->myobject->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
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
		global $langs;
		dol_syslog(get_class($this) . "::createCommon create", LOG_DEBUG);

		$error = 0;

		$now = dol_now();

		$fieldvalues = $this->setSaveQuery();

		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation'] = $this->db->idate($now);
		if (array_key_exists('birth', $fieldvalues) && empty($fieldvalues['birth'])) $fieldvalues['birth'] = $this->db->jdate($this->birth);
		if (array_key_exists('fk_user_creat', $fieldvalues) && !($fieldvalues['fk_user_creat'] > 0)) $fieldvalues['fk_user_creat'] = $user->id;
		unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into insert.
		if (array_key_exists('ref', $fieldvalues)) $fieldvalues['ref'] = dol_string_nospecial($fieldvalues['ref']); // If field is a ref, we sanitize data

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
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && empty($values[$key])) {
				if (isset($this->fields[$key]['default'])) $values[$key] = $this->fields[$key]['default'];
				else $values[$key] = 'null';
			}
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
				$this->errors[] = "Error ".$this->db->lasterror();
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
					$this->error = "Error ".$this->db->lasterror();
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
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetchLines();

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		$object->ref = empty($this->fields['ref']['default']) ? "copy_of_" . $object->ref : $this->fields['ref']['default'];
		$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf") . " " . $object->label : $this->fields['label']['default'];
		$object->status = self::STATUS_DRAFT;
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0) {
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option) {
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->element]['unique'][$shortkey])) {
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
				if ($this->copy_linked_contact($object, 'external') < 0)
					$error++;
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
	public function getFieldList($alias='')
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
		foreach ($this->fields as $field => $info) {
			if ($this->isDate($info)) {
				if (empty($obj->{$field}) || $obj->{$field} === '0000-00-00 00:00:00' || $obj->{$field} === '1000-01-01 00:00:00') $this->{$field} = 0;
				else $this->{$field} = strtotime($obj->{$field});
			} elseif ($this->isArray($info)) {
				$this->{$field} = @unserialize($obj->{$field});
				// Hack for data not in UTF8
				if ($this->{$field} === FALSE) @unserialize(utf8_decode($obj->{$field}));
			} elseif ($this->isInt($info)) {
				$this->{$field} = (int) $obj->{$field};
			} elseif ($this->isFloat($info)) {
				$this->{$field} = (float) $obj->{$field};
			}
			/*elseif($this->isNull($info))
	        {
	            $val = $obj->{$field};
	            // zero is not null
	            $this->{$field} = (is_null($val) || (empty($val) && $val!==0 && $val!=='0') ? null : $val);
	        }*/ else {
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
		if (empty($id) && empty($ref) && empty($morewhere)) return -1;

		global $langs;

		$array = preg_split("/[\s,]+/", $this->getFieldList());
		$array[0] = 't.rowid';
		$array = array_splice($array, 0, count($array), array($array[0]));
		$array = implode(', t.', $array);

		$sql = 'SELECT ' . $array . ',';

		$sql .= 'country.rowid as country_id, country.code as country_code, country.label as country, civility.rowid as civility_id, civility.code as civility_code, civility.label as civility';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_country as country ON t.country_id = country.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_civility as civility ON t.civility_id = civility.rowid';

		if (!empty($id)) $sql .= ' WHERE t.rowid = ' . $id;
		elseif (!empty($ref)) $sql .= ' WHERE t.ref = ' . $this->quote($ref, $this->fields['ref']);
		else $sql .= ' WHERE 1 = 1'; // usage with empty id and empty ref is very rare
		//print_r($sql);exit;
		if (empty($id) && isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' AND entity IN (' . getEntity($this->table_element) . ')';
		if ($morewhere) $sql .= $morewhere;
		$sql .= ' LIMIT 1'; // This is a fetch, to be sure to get only one record

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$res = $this->db->query($sql);
		if ($res) {
			if ($obj = $this->db->fetch_object($res)) {
				if ($obj) {
					$this->id = $id;
					$this->set_vars_by_db($obj);

					$this->date_creation = $this->db->jdate($obj->date_creation);
					$this->tms = $this->db->jdate($obj->tms);

					$this->birth = $this->db->jdate($obj->birth);

					$this->civility_id    = $obj->civility_id;
					$this->civility_code  = $obj->civility_code;
					if ($langs->trans("Civility" . $obj->civility_code) != "Civility" . $obj->civility_code) {
						$this->civility = $langs->transnoentitiesnoconv("Civility" .  $obj->civility_code);
					} else {
						$this->civility = $obj->civility;
					}

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
		if ($result > 0 && !empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		// Load lines with object ImmoRenterLine
		return count($this->lines) ? 1 : 0;
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      	User that modifies
	 * @param  bool $notrigger 	false=launch triggers after, true=disable triggers
	 * @return int             	<0 if KO, >0 if OK
	 */
	public function updateCommon(User $user, $notrigger = false)
	{
		global $conf, $langs, $object;
		dol_syslog(get_class($this) . "::updateCommon update", LOG_DEBUG);

		$error = 0;

		$now = dol_now();

		$fieldvalues = $this->setSaveQuery();

		if (array_key_exists('date_modification', $fieldvalues) && empty($fieldvalues['date_modification'])) $fieldvalues['date_modification'] = $this->db->idate($now);
		if (array_key_exists('birth', $fieldvalues) && empty($fieldvalues['birth'])) $fieldvalues['birth'] = $this->db->jdate($object->birth);
		if (array_key_exists('fk_user_modif', $fieldvalues) && !($fieldvalues['fk_user_modif'] > 0)) $fieldvalues['fk_user_modif'] = $user->id;
		unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into update.
		if (array_key_exists('ref', $fieldvalues)) $fieldvalues['ref'] = dol_string_nospecial($fieldvalues['ref']); // If field is a ref, we sanitize data

		$keys = array();
		$values = array();
		$tmp = array();
		foreach ($fieldvalues as $k => $v) {
			$keys[$k] = $k;
			$value = $this->fields[$k];
			$values[$k] = $this->quote($v, $value);
			$tmp[] = $k . '=' . $this->quote($v, $this->fields[$k]);
		}

		// Clean and check mandatory
		foreach ($keys as $key) {
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && $values[$key] == '-1') $values[$key] = '';		// This is an implicit foreign key field
			if (!empty($this->fields[$key]['foreignkey']) && $values[$key] == '-1') $values[$key] = '';					// This is an explicit foreign key field

			//var_dump($key.'-'.$values[$key].'-'.($this->fields[$key]['notnull'] == 1));
			/*
			if ($this->fields[$key]['notnull'] == 1 && empty($values[$key]))
			{
				$error++;
				$this->errors[]=$langs->trans("ErrorFieldRequired", $this->fields[$key]['label']);
			}*/
		}

		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET ' . implode(',', $tmp) . ' WHERE rowid=' . $this->id;

		$this->db->begin();
		if (!$error) {
			$res = $this->db->query($sql);
			if ($res === false) {
				$error++;
				$this->errors[] = "Error ".$this->db->lasterror();
			}
		}

		// Update extrafield
		if (!$error && empty($conf->global->MAIN_EXTRAFIELDS_DISABLED) && is_array($this->array_options) && count($this->array_options) > 0) {
			$result = $this->insertExtraFields();
			if ($result < 0) {
				$error++;
			}
		}

		// Triggers
		if (!$error && !$notrigger) {
			// Call triggers
			$result = $this->call_trigger(strtoupper(get_class($this)) . '_MODIFY', $user);
			if ($result < 0) {
				$error++;
			} //Do also here what you must do to rollback action if trigger fail
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
	 *    Set link to a third party
	 *
	 *    @param     int	$socid		Id of user to link to
	 *    @return    int						1=OK, -1=KO
	 */
	function setThirdPartyId($socid)
	{
		global $conf, $langs;

		$this->db->begin();

		// Remove link to third party onto any other renters
		if ($socid > 0) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "ultimateimmo_immorenter SET fk_soc = null";
			$sql .= " WHERE fk_soc = '" . $socid . "'";
			$sql .= " AND entity = " . $conf->entity;
			dol_syslog(get_class($this) . "::setThirdPartyId", LOG_DEBUG);
			$resql = $this->db->query($sql);
		}

		// Add link to third party for current renter
		$sql = "UPDATE " . MAIN_DB_PREFIX . "ultimateimmo_immorenter SET fk_soc = " . ($socid > 0 ? $socid : 'null');
		$sql .= " WHERE rowid = " . $this->id;

		dol_syslog(get_class($this) . "::setThirdPartyId", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->db->commit();
			return 1;
		} else {
			$this->error = "Error " . $this->db->error();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this) . "::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->myobject->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->myobject->myobject_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		$staticReceipt = new ImmoReceipt($this->db);

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $staticReceipt->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element;
			$sql .= " SET ref = '" . $this->db->escape($num) . "',";
			$sql .= " status = " . self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) $sql .= ", date_validation = '" . $this->db->idate($now) . "',";
			if (!empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = " . $user->id;
			$sql .= " WHERE rowid = " . $this->id;

			dol_syslog(get_class($this) . "::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = "Error ".$this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('IMMORENTER_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE ' . MAIN_DB_PREFIX . "ecm_files set filename = CONCAT('" . $this->db->escape($this->newref) . "', SUBSTR(filename, " . (strlen($this->ref) + 1) . ")), filepath = 'immorenter/" . $this->db->escape($this->newref) . "'";
				$sql .= " WHERE filename LIKE '" . $this->db->escape($this->ref) . "%' AND filepath = 'immorenter/" . $this->db->escape($this->ref) . "' and entity = " . $conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++;
					$this->error = "Error ".$this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->ultimateimmo->dir_output . '/immorenter/' . $oldref;
				$dirdest = $conf->ultimateimmo->dir_output . '/immorenter/' . $newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this) . "::validate() rename dir " . $dirsource . " into " . $dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->ultimateimmo->dir_output . '/immorenter/' . $newref, 'files', 1, '^' . preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^' . preg_quote($oldref, '/') . '/', $newref, $dirsource);
							$dirsource = $fileentry['path'] . '/' . $dirsource;
							$dirdest = $fileentry['path'] . '/' . $dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->mymodule_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'IMMORENTER_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->mymodule_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'IMMORENTER_CLOSE');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->mymodule->mymodule_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'IMMORENTER_REOPEN');
	}

	/**
	 *  Return name of contact with link (and eventually picto)
	 *	Use $this->id, $this->lastname, $this->firstname, this->civility_id
	 *
	 *	@param		int			$withpicto					Include picto with link
	 *	@param		string		$option						Where the link point to
	 *	@param		int			$maxlen						Max length of
	 *  @param		string		$moreparam					Add more param into URL
     *  @param      int     	$save_lastsearch_value		-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *	@param		int			$notooltip					1=Disable tooltip
	 *	@return		string									String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $maxlen = 0, $moreparam = '', $save_lastsearch_value = -1, $notooltip = 0)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = ''; $label = '';

		$label = '<u>' . $langs->trans("ImmoRenter") . '</u>';
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Lastname') . ':</b> ' . $this->civility .' '.$this->firstname .' '.$this->lastname;
		$label .= '<br><b>'.$langs->trans("EMail").':</b> '.$this->email;
		if (isset($this->status)) {
			$label .= '<br><b>' . $langs->trans("Status") . ":</b> " . $this->getLibStatut(5);
		}

		$url = dol_buildpath('/ultimateimmo/renter/immorenter_card.php', 1) . '?id=' . $this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowImmoRenter");
				$linkclose .= ' alt="' . dol_escape_htmltag($label, 1) . '"';
			}
			$linkclose .= ' title="' . dol_escape_htmltag($label, 1) . '"';
			$linkclose .= ' class="classfortooltip"';
		}

			$linkstart = '<a href="' . $url . '"';
			$linkstart .= $linkclose . '>';
			$linkend = '</a>';

		$result .= $linkstart;
		if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="' . (($withpicto != 2) ? 'paddingright ' : '') . 'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		if ($withpicto != 2)  $result .= ($maxlen ?dol_trunc($this->getFullName($langs), $maxlen) : $this->getFullName($langs));
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('immorenterdao'));
		$parameters = array('id' => $this->id, 'getnomurl' => $result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Retourne le libelle du status d'un user (actif, inactif)
	 *
	 *  @param	int		$mode          0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *  @return	string 			       Label of status
	 */
	function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the status
	 *
	 *  @param	int		$status        	Id status
	 *  @param  int		$mode          	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       	Label of status
	 */
	static function LibStatut($status, $mode = 0)
	{
		global $langs;

		if ($mode == 0) {
			$prefix = '';
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 1) {
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 2) {
			if ($status == 1) return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
		}
		if ($mode == 3) {
			if ($status == 1) return img_picto($langs->trans('Enabled'), 'statut4');
			if ($status == 0) return img_picto($langs->trans('Disabled'), 'statut5');
		}
		if ($mode == 4) {
			if ($status == 1) return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
		}
		if ($mode == 5) {
			if ($status == 1) return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
			if ($status == 0) return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
		}
		if ($mode == 6) {
			if ($status == 1) return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
			if ($status == 0) return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
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
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' WHERE t.rowid = ' . $id;
		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture) {
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture   = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
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
	 * CAN BE A CRON TASK
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		// ...

		return 0;
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
	function getCountry($searchkey, $withcode = '', $dbtouse = 0, $outputlangs = '', $entconv = 1, $searchlabel = '')
	{
		global $db, $langs;

		$result = '';

		// Check parameters
		if (empty($searchkey) && empty($searchlabel)) {
			if ($withcode === 'all') return array('id' => '', 'code' => '', 'label' => '');
			else return '';
		}
		if (!is_object($dbtouse)) $dbtouse = $db;
		if (!is_object($outputlangs)) $outputlangs = $langs;

		$sql = "SELECT rowid, code, label FROM " . MAIN_DB_PREFIX . "c_country";
		if (is_numeric($searchkey)) $sql .= " WHERE rowid=" . $searchkey;
		elseif (!empty($searchkey)) $sql .= " WHERE code='" . $db->escape($searchkey) . "'";
		else $sql .= " WHERE label='" . $db->escape($searchlabel) . "'";

		$resql = $dbtouse->query($sql);
		if ($resql) {
			$obj = $dbtouse->fetch_object($resql);
			if ($obj) {
				$label = ((!empty($obj->label) && $obj->label != '-') ? $obj->label : '');
				if (is_object($outputlangs)) {
					$outputlangs->load("dict");
					if ($entconv) $label = ($obj->code && ($outputlangs->trans("Country" . $obj->code) != "Country" . $obj->code)) ? $outputlangs->trans("Country" . $obj->code) : $label;
					else $label = ($obj->code && ($outputlangs->transnoentitiesnoconv("Country" . $obj->code) != "Country" . $obj->code)) ? $outputlangs->transnoentitiesnoconv("Country" . $obj->code) : $label;
				}
				if ($withcode == 1) $result = $label ? "$obj->code - $label" : "$obj->code";
				else if ($withcode == 2) $result = $obj->code;
				else if ($withcode == 3) $result = $obj->rowid;
				else if ($withcode === 'all') $result = array('id' => $obj->rowid, 'code' => $obj->code, 'label' => $label);
				else $result = $label;
			} else {
				$result = 'NotDefined';
			}
			$dbtouse->free($resql);
			return $result;
		} else dol_print_error($dbtouse, '');
		return 'Error';
	}

	/**
	 *     Return civility label, code or id from an id, code or label
	 *
	 *    @param      int		$searchkey      Id or code of civility to search
	 *    @param      string	$withcode   	'0'=Return label,
	 *    										'1'=Return code + label,
	 *    										'2'=Return code from id,
	 *    										'3'=Return id from code,
	 * 	   										'all'=Return array('id'=>,'code'=>,'label'=>)
	 *    @param      DoliDB	$dbtouse       	Database handler (using in global way may fail because of conflicts with some autoload features)
	 *    @param      Translate	$outputlangs	Langs object for output translation
	 *    @param      int		$entconv       	0=Return value without entities and not converted to output charset, 1=Ready for html output
	 *    @param      int		$searchlabel    Label of civility to search (warning: searching on label is not reliable)
	 *    @return     mixed       				String with civility code or translated civility name or Array('id','code','label')
	 */
	public function getCivilityLabel($searchkey, $withcode = '', $dbtouse = 0, $outputlangs = '', $entconv = 1, $searchlabel = '')
	{
		global $db, $langs;

		$result = '';

		// Check parameters
		if (empty($searchkey) && empty($searchlabel)) {
			if ($withcode === 'all') return array('id' => '', 'code' => '', 'label' => '');
			else return '';
		}
		if (!is_object($dbtouse)) $dbtouse = $db;
		if (!is_object($outputlangs)) $outputlangs = $langs;

		$sql = "SELECT rowid, code, label FROM " . MAIN_DB_PREFIX . "c_civility";
		if (is_numeric($searchkey)) $sql .= " WHERE rowid=" . $searchkey;
		elseif (!empty($searchkey)) $sql .= " WHERE code='" . $db->escape($searchkey) . "'";
		else $sql .= " WHERE label='" . $db->escape($searchlabel) . "'";

		$resql = $dbtouse->query($sql);
		if ($resql) {
			$obj = $dbtouse->fetch_object($resql);
			if ($obj) {
				$label = ((!empty($obj->label) && $obj->label != '-') ? $obj->label : '');
				if (is_object($outputlangs)) {
					$outputlangs->load("dict");
					if ($entconv) $label = ($obj->code && ($outputlangs->trans("Civility" . $obj->code) != "Civility" . $obj->code)) ? $outputlangs->trans("Civility" . $obj->code) : $label;
					else $label = ($obj->code && ($outputlangs->transnoentitiesnoconv("Civility" . $obj->code) != "Civility" . $obj->code)) ? $outputlangs->transnoentitiesnoconv("Civility" . $obj->code) : $label;
				}
				if ($withcode == 1) $result = $label ? "$obj->code - $label" : "$obj->code";
				else if ($withcode == 2) $result = $obj->code;
				else if ($withcode == 3) $result = $obj->rowid;
				else if ($withcode === 'all') $result = array('id' => $obj->rowid, 'code' => $obj->code, 'label' => $label);
				else $result = $label;
			} else {
				$result = 'NotDefined';
			}
			$dbtouse->free($resql);
			return $result;
		} else dol_print_error($dbtouse, '');
		return 'Error';
	}

	/**
	 *  Return combo list with people title
	 *
	 *  @param  string	$selected   	Id or Code or Label of preselected
	 * 	@param	string	$htmlname		Name of HTML select combo field
	 *  @param  string	$htmloption     More html options on select object
	 *  @param	integer	$maxlength		Max length for labels (0=no limit)
	 *  @param  string  $morecss        Add more css on SELECT element
	 *  @param	string	$usecodeaskey	''=Use id as key (default), 'code3'=Use code on 3 alpha as key, 'code2"=Use code on 2 alpha as key
	 *  @return	string					String with HTML select
	 */
	public function select_civility($selected = '', $htmlname = 'civility_id', $htmloption = '', $maxlength = 0, $morecss = 'maxwidth100', $usecodeaskey = '')
	{
		// phpcs:enable
		global $conf, $langs, $user;
		$langs->load("dict");

		$out = '';
		$civilityArray = array();

		$sql = "SELECT rowid, code, label, active FROM " . MAIN_DB_PREFIX . "c_civility";
		$sql .= " WHERE active = 1";

		dol_syslog("Form::select_civility", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$out .= '<select class="flat' . ($morecss ? ' ' . $morecss : '') . '" name="' . $htmlname . '" id="' . $htmlname . '">';
			$out .= '<option value="">&nbsp;</option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);

					$civilityArray[$i]['rowid'] = $obj->rowid;
					$civilityArray[$i]['code'] = $obj->code;
					$civilityArray[$i]['label'] = ($obj->code && $langs->transnoentitiesnoconv("Civility" . $obj->code) != "Civility" . $obj->code ? $langs->transnoentitiesnoconv("Civility" . $obj->code) : ($obj->label != '-' ? $obj->label : ''));
					$label[$i] = dol_string_unaccent($civilityArray[$i]['label']);
					$i++;
				}
				foreach ($civilityArray as $row) {
					if ($selected && $selected != '-1' && ($selected == $row['rowid'] || $selected == $row['code'] || $selected == $row['label'])) {
						$out .= '<option value="' . ($usecodeaskey ? ($usecodeaskey == 'code2' ? $row['code'] : $row['code']) : $row['rowid']) . '" selected>';
					} else {
						$out .= '<option value="' . ($usecodeaskey ? ($usecodeaskey == 'code2' ? $row['code'] : $row['code']) : $row['rowid']) . '">';
					}
					// Si traduction existe, on l'utilise, sinon on prend le libelle par defaut
					if ($row['label']) $out .= dol_trunc($row['label'], $maxlength, 'middle');
					else $out .= '&nbsp;';
					if ($row['code']) $out .= ' (' . $row['code'] . ')';
					$out .= '</option>';
				}
			}
			$out .= '</select>';
			if ($user->admin) $out .= info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
		} else {
			dol_print_error($this->db);
		}

		return $out;
	}

}

/**
 * Class ImmoRenterLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class ImmoRenterLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
*/
