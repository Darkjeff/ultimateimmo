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
 * \file        class/immoowner.class.php
 * \ingroup     ultimateimmo
 * \brief       This file is a CRUD class file for ImmoOwner (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
//require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
dol_include_once('/ultimateimmo/class/commonobjectultimateimmo.class.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ImmoOwner
 */
class ImmoOwner extends CommonObjectUltimateImmo
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'immoowner';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'ultimateimmo_immoowner';

	/**
	 * @var int Field with ID of parent key if this field has a parent
	 */
	public $fk_element='fk_owner';

	/**
	 * @var int  Does immoowner support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does immoowner support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for immoowner. Must be the part after the 'object_' into object_immoowner.png
	 */
	public $picto = 'immoowner@ultimateimmo';

	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
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
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */
	//'arrayofkeyval' => array('0' => 'MME', '1' => 'MLE', '2' => 'MR')
	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid'         => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -2, 'noteditable' => 1, 'notnull' => 1, 'index' => 1, 'position' => 1, 'comment' => 'Id'),
		'ref'           => array('type' => 'varchar(128)', 'label' => 'RefOwner', 'enabled' => 1, 'visible' => 1, 'noteditable' => 0, 'default' => '', 'notnull' => 1,  'default'=>'(PROV)', 'index' => 1, 'position' => 10, 'searchall' => 1, 'comment' => 'Reference of object'),
		'entity'        => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'visible' => 0, 'notnull' => 1, 'default' => 1, 'index' => 1, 'position' => 20),
		'fk_soc' 	=> array('type' => 'integer:Societe:societe/class/societe.class.php:1:(status:=:1)', 'label' => 'ThirdParty', 'visible' => 1, 'enabled' => 1, 'position' => 30, 'notnull' => -1, 'index' => 1, 'help' => 'LinkToThirdparty'),
		'societe' 	=> array('type' => 'varchar(128)', 'label' => 'Societe', 'visible' => 1, 'enabled' => 1, 'position' => 34, 'notnull' => -1,),
		'fk_owner_type' => array('type' => 'integer:ImmoOwner_Type:ultimateimmo/class/immoowner_type.class.php', 'label' => 'MenuImmoOwnerType', 'enabled' => 1, 'visible' => 1, 'position' => 32, 'notnull' => -1, 'index' => 1, 'help' => "LinkToOwnerType",),
		'note_public' 	=> array('type' => 'html', 'label' => 'NotePublic', 'visible' => -1, 'enabled' => 1, 'position' => 40, 'notnull' => -1,),
		'note_private' 	=> array('type' => 'html', 'label' => 'NotePrivate', 'visible' => -1, 'enabled' => 1, 'position' => 45, 'notnull' => -1,),
		'civility_id'	=> array('type' => 'integer', 'label' => 'Civility', 'visible' => -1, 'enabled' => 1, 'position' => 50, 'notnull' => 1,),
		'firstname' 	=> array('type' => 'varchar(255)', 'label' => 'Firstname', 'visible' => -1, 'enabled' => 1, 'position' => 55, 'notnull' => 1, 'showoncombobox' => 1,),
		'lastname' 	=> array('type' => 'varchar(255)', 'label' => 'Lastname', 'visible' => -1, 'enabled' => 1, 'position' => 60, 'notnull' => 1, 'searchall' => 1, 'showoncombobox' => 1,),
		'address' 	=> array('type' => 'varchar(255)', 'label' => 'Address', 'enabled' => 1, 'visible' => 1, 'position' => 61, 'notnull' => -1,),
		'zip' 		=> array('type' => 'varchar(32)', 'label' => 'Zip', 'enabled' => 1, 'visible' => 1, 'position' => 62, 'notnull' => -1,),
		'town' 		=> array('type' => 'varchar(64)', 'label' => 'Town', 'enabled' => 1, 'visible' => 1, 'position' => 63, 'notnull' => -1,),
		'country_id' 	=> array('type' => 'integer', 'label' => 'Country', 'enabled' => 1, 'visible' => 1, 'position' => 64, 'notnull' => -1,),
		'email' 	=> array('type' => 'varchar(255)', 'label' => 'Email', 'visible' => -1, 'enabled' => 1, 'position' => 65, 'notnull' => 1, 'searchall' => 1,),
		'birth' 	=> array('type' => 'date', 'label' => 'BirthDay', 'visible' => -1, 'enabled' => 1, 'position' => 70, 'notnull' => -1,),
		'phone' 	=> array('type' => 'varchar(30)', 'label' => 'Phone', 'visible' => -1, 'enabled' => 1, 'position' => 75, 'notnull' => -1,),
		'phone_mobile' 	=> array('type' => 'varchar(30)', 'label' => 'PhoneMobile', 'visible' => -1, 'enabled' => 1, 'position' => 80, 'notnull' => -1,),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'visible' => -2, 'enabled' => 1, 'position' => 500, 'notnull' => 1,),
		'tms' 		=> array('type' => 'timestamp', 'label' => 'DateModification', 'visible' => -2, 'enabled' => 1, 'position' => 501, 'notnull' => 1,),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'visible' => -2, 'enabled' => 1, 'position' => 510, 'notnull' => 1, 'foreignkey' => 'llx_user.rowid',),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'visible' => -2, 'enabled' => 1, 'position' => 511, 'notnull' => -1, 'foreignkey' => 'llx_user.rowid',),
		'import_key' 	=> array('type' => 'varchar(14)', 'label' => 'ImportId', 'visible' => -2, 'enabled' => 1, 'position' => 1000, 'notnull' => -1,),
		'status' 	=> array('type' => 'integer', 'label' => 'Status', 'visible' => 1, 'enabled' => 1, 'position' => 1000, 'notnull' => 1, 'index' => 1, 'default'=>1,
			'arrayofkeyval' => array(0 => 'Draft', 1 => 'Actif', 9 => 'Canceled')

		),

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

	public $fk_soc;

	public $societe;

	public $fk_owner_type;

	public $note_public;

	public $note_private;

	public $civility_id;	// In fact we store civility_code
	public $civility_code;
	public $civility;

	public $firstname;

	public $lastname;

	public $address;

	public $zip;

	public $town;

	public $country_id;

	public $email;

	public $birth;

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
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'immoownerdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_immoowner';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ImmoOwnerline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables = array('immoownerdet');
	/**
	 * @var ImmoOwnerLine[]     Array of subtable lines
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
		$object->ref = "copy_of_" . $object->ref;
		$object->title = $langs->trans("CopyOf") . " " . $object->title;
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
	protected function set_vars_by_db(&$obj)
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
		if (empty($id) && empty($ref)) return false;

		global $langs;

		$array = preg_split("/[\s,]+/", $this->get_field_list());
		$array[0] = 't.rowid';
		$array = array_splice($array, 0, count($array), array($array[0]));
		$array = implode(', t.', $array);

		$sql = 'SELECT ' . $array . ',';

		$sql .= 'country.rowid as country_id, country.code as country_code, country.label as country, civility.rowid as civility_id, civility.code as civility_code, civility.label as civility';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_country as country ON t.country_id = country.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_civility as civility ON t.civility_id = civility.rowid';

		if (!empty($id)) $sql .= ' WHERE t.rowid = ' . $id;
		else $sql .= ' WHERE t.ref = ' . $this->quote($ref, $this->fields['ref']);
		if ($morewhere) $sql .= $morewhere;

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$res = $this->db->query($sql);

		if ($res) {
			if ($obj = $this->db->fetch_object($res)) {
				if ($obj) {
					$this->id = $obj->rowid;
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
				$this->error = $this->db->lasterror();
				$this->errors[] = $this->error;
				return -1;
			}
		} else {
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
	public function fetchLines()
	{
		$this->lines = array();

		// Load lines with object ImmoOwnerLine
		$result = $this->fetchLinesCommon();
		return $result;
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
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				} elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);

		if ($resql) {

			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num))
			{
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
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

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = '';

		$label = '<u>' . $langs->trans("ImmoOwner") . '</u>';
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Lastname') . ':</b> ' . $this->civility .' '.$this->firstname .' '.$this->lastname;
		if (isset($this->status)) {
			$label .= '<br><b>' . $langs->trans("Status") . ":</b> " . $this->getLibStatut(5);
		}

		$url = dol_buildpath('/ultimateimmo/owner/immoowner_card.php', 1) . '?id=' . $this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowImmoOwner");
				$linkclose .= ' alt="' . dol_escape_htmltag($label, 1) . '"';
			}
			$linkclose .= ' title="' . dol_escape_htmltag($label, 1) . '"';
			$linkclose .= ' class="classfortooltip' . ($morecss ? ' ' . $morecss : '') . '"';
		} else $linkclose = ($morecss ? ' class="' . $morecss . '"' : '');

		$linkstart = '<a href="' . $url . '"';
		$linkstart .= $linkclose . '>';
		$linkend = '</a>';

		$result .= $linkstart;
		if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="' . (($withpicto != 2) ? 'paddingright ' : '') . 'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		if ($withpicto != 2) $result .= $this->civility .' '.$this->firstname .' '.$this->lastname;
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('immoownerdao'));
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
	function LibStatut($status, $mode = 0)
	{
		global $langs;

		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("mymodule");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Actif');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Actif');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
		}

		$statusType = 'status'.$status;
		if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
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
	 * 	Create an array of owner lines
	 *
	 * 	@return int		>0 if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new ImmoOwnerLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_myobject = '.$this->id));

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

	/**
	 *    Return civility label of contact
	 *
	 *    @return	string      			Translated name of civility
	 */
	public function getPropertyLabel()
	{
		global $langs;

		$code = ($this->civility_code ? $this->civility_code : (!empty($this->civility_id) ? $this->civility : (!empty($this->civilite) ? $this->civilite : '')));
		if (empty($code)) return '';

		$langs->load("dict");
		return $langs->getLabelFromKey($this->db, "Civility".$code, "c_civility", "code", "label", $code);
	}
}


/**
 * Class ImmoOwnerLine. You can also remove this and generate a CRUD class for lines objects.
 */

class ImmoOwnerLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
