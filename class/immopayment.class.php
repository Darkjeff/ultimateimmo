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
 * \file        class/immopayment.class.php
 * \ingroup     ultimateimmo
 * \brief       This file is a CRUD class file for ImmoPayment (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ImmoPayment
 */
class ImmoPayment extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'immopayment';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'ultimateimmo_immopayment';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $fk_element = 'fk_payment';
	/**
	 * @var ImmopaymentLine[] Lines
	 */
	public $lines = array();
	/**
	 * @var int  Does immopayment support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does immopayment support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for immopayment. Must be the part after the 'object_' into object_immopayment.png
	 */
	public $picto = 'immopayment@ultimateimmo';


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
	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'position' => 1, 'notnull' => 1, 'index' => 1, 'comment' => "Id",),
		'ref' => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => 1, 'visible' => 1, 'position' => 10, 'notnull' => 1, 'index' => 1, 'searchall' => 1, 'comment' => "Reference of object", 'showoncombobox' => '1',),
		'entity' => array('type' => 'integer', 'label' => 'Entity', 'visible' => 0, 'enabled' => 1, 'position' => 20, 'default' => 1, 'notnull' => 1, 'index' => 1,),
		'fk_rent' => array('type' => 'integer:ImmoRent:ultimateimmo/class/immorent.class.php', 'label' => 'Contract', 'enabled' => 1, 'visible' => 1, 'position' => 25, 'notnull' => -1, 'index' => 1, 'help' => "LinkToContract",),
		'fk_receipt' => array('type' => 'integer:ImmoReceipt:ultimateimmo/class/immoreceipt.class.php', 'label' => 'ImmoReceipt', 'enabled' => 1, 'visible' => 1, 'position' => 30, 'notnull' => -1, 'index' => 1, 'help' => 'ImmoPaymentReceiptInfo',),
		'fk_owner' => array('type' => 'integer:ImmoOwner:ultimateimmo/class/immoowner.class.php', 'label' => 'Owner', 'enabled' => 1, 'visible' => 1, 'position' => 35, 'notnull' => -1, 'index' => 1, 'help' => "LinkToOwner",),
		'fk_soc' => array('type' => 'integer:Societe:societe/class/societe.class.php', 'label' => 'ThirdParty', 'visible' => 1, 'enabled' => 1, 'position' => 36, 'notnull' => -1, 'index' => 1, 'searchall' => 1, 'help' => "LinkToThirdparty",),
		'fk_property' => array('type' => 'integer:ImmoProperty:ultimateimmo/class/immoproperty.class.php', 'label' => 'Property', 'enabled' => 1, 'visible' => 1, 'position' => 40, 'notnull' => -1, 'index' => 1, 'help' => "LinkToProperty",),
		'fk_renter' => array('type' => 'integer:ImmoRenter:ultimateimmo/class/immorenter.class.php', 'label' => 'Renter', 'enabled' => 1, 'visible' => 1, 'position' => 45, 'notnull' => -1, 'index' => 1, 'help' => "LinkToRenter",),
		'fk_payment' => array('type' => 'integer', 'label' => 'Payment', 'visible' => 0, 'enabled' => 1, 'position' => 48, 'default' => 1, 'notnull' => 1, 'index' => 1,),
		'note_public' => array('type' => 'html', 'label' => 'NotePublic', 'enabled' => 1, 'visible' => -1, 'position' => 50, 'notnull' => -1,),
		'date_payment' => array('type' => 'date', 'label' => 'DatePayment', 'enabled' => 1, 'visible' => -1, 'position' => 70, 'notnull' => 1,),
		'amount' => array('type' => 'price', 'label' => 'Amount', 'enabled' => 1, 'visible' => 1, 'position' => 72, 'notnull' => -1, 'default' => 'null', 'isameasure' => '1', 'help' => 'ImmoPaymentAmountInfo',),
		'fk_mode_reglement' => array('type' => 'integer', 'label' => 'TypePayment', 'enabled' => 1, 'visible' => 1, 'position' => 75, 'notnull' => -1, 'index' => 1, 'help' => 'ImmoPaymentTypeInfo',),
		'fk_account' => array('type' => 'integer:Account:compta/bank/class/account.class.php', 'label' => 'BankAccount', 'enabled' => 1, 'visible' => 1, 'position' => 80, 'notnull' => -1, 'index' => 1, 'help' => "LinkToBank",),
		'num_payment' => array('type' => 'varchar(50)', 'label' => 'NumPayment', 'enabled' => 1, 'visible' => -1, 'position' => 85, 'notnull' => -1,),
		'check_transmitter' => array('type' => 'varchar(50)', 'label' => 'CheckTransmitter', 'enabled' => 1, 'visible' => -1, 'position' => 86, 'notnull' => -1,),
		'chequebank' => array('type' => 'varchar(50)', 'label' => 'ChequeBank', 'enabled' => 1, 'visible' => -1, 'position' => 87, 'notnull' => -1,),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'visible' => -2, 'position' => 500, 'notnull' => 1,),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -2, 'position' => 501, 'notnull' => 1,),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1, 'visible' => -2, 'position' => 510, 'notnull' => 1, 'foreignkey' => 'llx_user.rowid',),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'enabled' => 1, 'visible' => -2, 'position' => 511, 'notnull' => -1, 'foreignkey' => 'llx_user.rowid',),
		'import_key' => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => 1, 'visible' => -2, 'position' => 1000, 'notnull' => -1,),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'position' => 1000, 'notnull' => 1, 'index' => 1, 'arrayofkeyval' => array('0' => 'ImmoPaymentStatusDisabled', '1' => 'ImmoPaymentStatusActive', '-1' => 'Cancel')),
	);
	public $rowid;
	public $ref;
	public $entity;
	public $fk_rent;
	public $fk_receipt;
	public $fk_owner;
	public $fk_soc;
	public $fk_property;
	public $fk_renter;
	public $note_public;
	public $amount;			    // Total amount of payment
	public $amounts=array();    // Array of amounts
	public $fk_mode_reglement;
	public $fk_account;
	public $fk_payment;
	public $num_payment;
	public $check_transmitter;
	public $chequebank;
	public $date_payment;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;
	public $nomlocal;
	public $nomlocataire;
	public $nomloyer;
	// END MODULEBUILDER PROPERTIES


	public $sqlquerymassgen = '';

	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'immopaymentdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_immopayment';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ImmoPaymentline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('immopaymentdet');
	/**
	 * @var ImmoPaymentLine[]     Array of subtable lines
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
	 * @param  int		  	   $closepaidreceipts   	1=Also close payed receipts to paid, 0=Do nothing more
	 * @param  Societe   	   $thirdparty           Thirdparty
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	/*public function createCommon(User $user, $closepaidreceipts = 0, $thirdparty = null, $notrigger = false)
	{
		global $langs, $object, $form;

		$error = 0;

		$now=dol_now();

		$fieldvalues = $this->setSaveQuery();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation']=$this->db->idate($now);
		if (array_key_exists('date_payment', $fieldvalues) && empty($fieldvalues['date_payment'])) $fieldvalues['date_payment']=$this->db->jdate($object->date_payment);
		if (array_key_exists('fk_user_creat', $fieldvalues) && ! ($fieldvalues['fk_user_creat'] > 0)) $fieldvalues['fk_user_creat']=$user->id;
		//if (array_key_exists('fk_mode_reglement', $fieldvalues) && ! ($fieldvalues['fk_mode_reglement'] > 0)) $fieldvalues['fk_mode_reglement']=$form->select_types_paiements((GETPOST('fk_mode_reglement')?GETPOST('fk_mode_reglement'):$object->fk_mode_reglement), 'fk_mode_reglement', '', 2);
		unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into insert.

		$keys=array();
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
			if (empty($this->fields[$key]['ref']) && $values[$key] == '') $values[$key] = '(PROV' . $this->id . ')';

			//var_dump($key.'-'.$values[$key].'-'.($this->fields[$key]['notnull'] == 1));
			if (isset($this->fields[$key]['notnull']) && $this->fields[$key]['notnull'] == 1 && !isset($values[$key]) && is_null($key['default'])) {
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
	}*/

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	/*public function create(User $user, $closepaidreceipts = 0, $thirdparty = null, $notrigger = false)
	{
		return $this->createCommon($user, $closepaidreceipts, $thirdparty, $notrigger);
	}*/

	/**
	 *  Create payment of receipt into database.
	 *  Use this->amounts to have list of lines for the payment
	 *
	 *  @param      User		$user			User making payment
	 *  @param      bool 		$notrigger 		false=launch triggers after, true=disable triggers
	 *  @return     int     					<0 if KO, id of payment if OK
	 */
	public function create($user, $notrigger = false)
	{
		global $conf, $langs;

		$error = 0;

		$now = dol_now();

		// Validate parameters
		if (!$this->date_payment) {
			$this->error = 'ErrorBadValueForParameterCreatePaymentReceipt';
			$this->errors[] = 'ErrorBadValueForParameterCreatePaymentReceipt';
			return -1;
		}

		// Clean parameters
		if (isset($this->fk_receipt)) 		$this->fk_receipt = trim($this->fk_receipt);
		if (isset($this->amount))			$this->amount = trim($this->amount);
		if (isset($this->fk_mode_reglement)) $this->fk_mode_reglement = trim($this->fk_mode_reglement);
		if (isset($this->num_payment))      $this->num_payment = trim($this->num_payment);
		if (isset($this->note_public))		$this->note_public = trim($this->note_public);
		if (isset($this->fk_account))			$this->fk_account = trim($this->fk_account);
		if (isset($this->fk_user_creat))	$this->fk_user_creat = trim($this->fk_user_creat);
		if (isset($this->fk_user_modif))	$this->fk_user_modif = trim($this->fk_user_modif);

		$totalamount = 0;
		foreach ($this->amounts as $key => $value)  // How payment is dispatch
		{
			$newvalue = price2num($value, 'MT');
			$this->amounts[$key] = $newvalue;
			$totalamount += $newvalue;
		}
		$totalamount = price2num($totalamount);

		// Check parameters
		if ($totalamount == 0) {
			$this->error = 'TotalAmount=0 do nothing';
			$this->errors[] = 'TotalAmount=0 do nothing';
			return -1; // On accepte les montants negatifs pour les rejets de prelevement mais pas null
		}


		$this->db->begin();

		if ($totalamount != 0) {
			$sql = "INSERT INTO " . MAIN_DB_PREFIX . "ultimateimmo_immopayment (fk_receipt, date_creation, date_payment, amount,";
			$sql .= " fk_mode_reglement, fk_property, fk_renter, fk_rent, num_payment, note_public, fk_user_creat, fk_account, ";
			$sql .= "fk_owner)";
			$sql .= " VALUES (" . $this->fk_receipt . ", '" . $this->db->idate($now) . "',";
			$sql .= " '" . $this->db->idate($this->date_payment) . "',";
			$sql .= " " . $totalamount . ",";
			$sql .= " " . $this->fk_mode_reglement . ",'" . $this->db->escape($this->fk_property) . "','" .
				$this->db->escape($this->fk_renter) . "','" . $this->db->escape($this->fk_rent) . "',  '" .
				$this->db->escape($this->num_payment) . "', '" . $this->db->escape($this->note_public) . "', " . $user->id . ",";
			$sql .= " 0,";
			$sql .= isset($this->fk_owner)?(int)$this->fk_owner:'null';
			$sql .= ")";

			dol_syslog(get_class($this) . "::create", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if ($resql) {
				$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "ultimateimmo_immopayment");
				$this->ref = $this->id;
			} else {
				$error++;
			}
		}

		if (!$error && !$notrigger) {
			// Call triggers
			$result = $this->call_trigger(strtoupper(get_class($this)) . '_CREATE', $user);
			if ($result < 0) {
				$error++;
			}
			// End call triggers
		}

		if ($totalamount != 0 && !$error) {
			$this->amount = $totalamount;
			$this->total = $totalamount;    // deprecated
			$this->db->commit();
			return $this->id;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = "Error ".$this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	/*public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}*/

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
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchCommon($id, $ref = null, $morewhere = '')
	{
		if (empty($id) && empty($ref)) return false;

		$array = preg_split("/[\s,]+/", $this->get_field_list());
		$array[0] = 't.rowid';
		$array = array_splice($array, 0, count($array), $array[0]);
		$array = implode(', t.', $array);

		$sql = 'SELECT ' . $array . ',';
		$sql .= ' cp.id as mode_id, cp.code as mode_code, cp.libelle as mode_payment,';
		$sql .= ' lc.lastname as nomlocataire,';
		$sql .= ' ll.label as nomlocal,';
		$sql .= ' lo.label as nomloyer,';
		$sql .= ' b.fk_account';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immorenter as lc ON t.fk_renter = lc.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immoowner as own ON t.fk_owner = own.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immoproperty as ll ON t.fk_property = ll.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immoreceipt as lo ON t.fk_receipt = lo.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'bank as b ON t.fk_account = b.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_paiement as cp ON t.fk_mode_reglement = cp.id AND cp.entity IN (' . getEntity('c_paiement') . ')';;

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		if (!empty($id)) $sql .= ' WHERE t.rowid = ' . $id;
		else $sql .= ' WHERE t.ref = ' . $this->quote($ref, $this->fields['ref']);
		if ($morewhere) $sql .= $morewhere;

		$res = $this->db->query($sql);
		if ($res) {
			if ($obj = $this->db->fetch_object($res)) {
				if ($obj) {
					$this->id = $obj->rowid;
					$this->set_vars_by_db($obj);


					$this->ref = $obj->rowid;

					$this->date_creation = $this->db->jdate($obj->date_creation);
					$this->tms = $this->db->jdate($obj->tms);
					$this->amount			= $obj->amount;
					$this->fk_mode_reglement = $obj->fk_mode_reglement;
					$this->num_payment		= $obj->num_payment;
					$this->mode_code 		= $obj->mode_code;
					$this->mode_payment		= $obj->mode_payment;
					$this->fk_account			= $obj->fk_account;
					$this->fk_owner 		= $obj->fk_owner;
					$this->fk_user_creat	= $obj->fk_user_creat;
					$this->fk_user_modif	= $obj->fk_user_modif;
					$this->bank_account		= $obj->fk_account;
					$this->bank_line		= $obj->fk_account;

					$this->date_payment = $this->db->jdate($obj->date_payment);

					$this->setVarsFromFetchObj($obj);


					return $this->id;
				} else {
					return 0;
				}
			} else {
				$this->error = "Error ".$this->db->lasterror();
	            $this->errors[] = "Error ".$this->db->lasterror();
				$errmsg = $this->error;
				setEventMessages($errmsg, null, 'errors');
				return -1;
			}
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = "Error ".$this->db->lasterror();
			$errmsg = $this->error;
			setEventMessages($errmsg, null, 'errors');
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
		//if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param string $sortorder Sort Order
	 * @param string $sortfield Sort field
	 * @param int    $limit     offset limit
	 * @param int    $offset    offset limit
	 * @param array  $filter    filter array
	 * @param string $filtermode filter mode (AND or OR)
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';

		$sql .= " t.fk_rent,";
		$sql .= " t.fk_property,";
		$sql .= " t.fk_renter,";
		$sql .= " t.amount,";
		$sql .= " t.comment,";
		$sql .= " t.date_payment,";
		$sql .= " t.fk_owner,";
		$sql .= " t.fk_receipt";
		$sql .= " , lc.lastname as nomlocataire , ll.label as nomlocal , lo.label as nomloyer ";

		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as lc ON t.fk_renter = lc.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll ON t.fk_property = ll.rowid ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as lo ON t.fk_receipt = lo.rowid";

		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				$sqlwhere[] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' WHERE ' . implode(' ' . $filtermode . ' ', $sqlwhere);
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .=  ' ' . $this->db->plimit($limit + 1, $offset);
		}
		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new ImmoPaymentLine();

				$line->rowid = $obj->rowid;

				$line->fk_rent = $obj->fk_rent;
				$line->fk_property = $obj->fk_property;
				$line->fk_renter = $obj->fk_renter;
				$line->amount = $obj->amount;
				$line->fk_mode_reglement = $obj->fk_mode_reglement;
				$line->note_public = $obj->note_public;
				$line->date_payment = $this->db->jdate($obj->date_payment);
				$line->fk_owner = $obj->fk_owner;
				$line->fk_receipt = $obj->fk_receipt;
				$line->nomlocataire = $obj->nomlocataire;
				$line->nomlocal = $obj->nomlocal;
				$line->nomloyer = $obj->nomloyer;

				$this->lines[] = $line;
			}
			$this->db->free($resql);

			return $num;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
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

		// Load lines with object ImmoPaymentLine

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
	function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $db, $conf, $langs;
		global $dolibarr_main_authentication, $dolibarr_main_demo;
		global $menumanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1;   // Force disable tooltips

		$result = '';
		$companylink = '';

		$label = '<u>' . $langs->trans("ImmoPayment") . '</u>';
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('ImmoReceipt') . ':</b> ' . $this->fk_receipt;
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('DatePayment') . ':</b> ' . $this->date_payment;

		$url = dol_buildpath('/ultimateimmo/payment/immopayment_card.php', 1) . '?id=' . $this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowImmoPayment");
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
		if ($withpicto != 2) $result .= $this->ref;
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
     *      Add record into bank for payment with links between this bank record and invoices of payment.
     *      All payment properties must have been set first like after a call to create().
     *
     *      @param	User	$user               Object of user making payment
     *      @param  string	$mode               'payment_quittance'
     *      @param  string	$label              Label to use in bank record
     *      @param  int		$accountid          Id of bank account to do link with
     *      @param  string	$emetteur_nom       Name of transmitter
     *      @param  string	$emetteur_banque    Name of bank
     *      @return int                 		<0 if KO, >0 if OK
     */
	public function addPaymentToBank($user, $mode, $label, $accountid, $emetteur_nom, $emetteur_banque)
	{
		global $conf;

		$error = 0;

		if (!empty($conf->banque->enabled)) {
			require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';

			$acc = new Account($this->db);
			$acc->fetch($accountid);

			$total = $this->amount;
			if ($mode == 'immopayment') $amount = $total;

			// Insert payment into llx_bank
			$bank_line_id = $acc->addline(
				$this->date_payment,
				$this->fk_mode_reglement,  // Payment mode id or code ("CHQ or VIR for example")
				$label,
				$amount,
				$this->num_payment,
				'',
				$user,
				$emetteur_nom,
				$emetteur_banque
			);

			// Update fk_account in llx_paiement.
			// On connait ainsi le paiement qui a genere l'ecriture bancaire
			if ($bank_line_id > 0) {
				$result = $this->update_fk_bank($bank_line_id);
				if ($result <= 0) {
					$error++;
					dol_print_error($this->db);
				}

				// Add link 'payment', 'payment_supplier', 'immopayment' in bank_url between payment and bank transaction
				$url = '';
				if ($mode == 'immopayment') $url = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $this->id;
				if ($url) {
					$result = $acc->add_url_line($bank_line_id, $this->id, $url, '(paiement)', $mode);
					if ($result <= 0) {
						$error++;
						dol_print_error($this->db);
					}
				}
			} else {
				$this->error = $acc->error;
				setEventMessages($this->error, null, 'errors');
				$error++;
			}
		}

		if (!$error) {
			return 1;
		} else {
			return -1;
		}
	}

	/**
	 *  Update link between the quittance payment and the generated line in llx_bank
	 *
	 *  @param	int		$id_bank         Id if bank
	 *  @return	int			             >0 if OK, <=0 if KO
	 */
	public function update_fk_bank($id_bank)
	{
		// phpcs:enable
		$sql = "UPDATE " . MAIN_DB_PREFIX . "ultimateimmo_immopayment SET fk_account = " . $id_bank . " WHERE rowid = " . $this->id;

		dol_syslog(get_class($this) . "::update_fk_bank", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			return 1;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			$this->errors[] = "Error ".$this->db->lasterror();
			return 0;
		}
	}

	/**
	 *  Change the payments methods
	 *
	 *  @param		int		$id		Id of new payment method
	 *  @return		int				>0 if OK, <0 if KO
	 */
	public function setPaymentMethods($id)
	{
		dol_syslog(get_class($this).'::setPaymentMethods('.$id.')');
		if ($this->statut >= 0 || $this->element == 'societe')
		{
			// TODO uniformize field name
			$fieldname = 'fk_mode_reglement';
			if ($this->element == 'societe') $fieldname = 'mode_reglement';
			if (get_class($this) == 'Fournisseur') $fieldname = 'mode_reglement_supplier';

			$sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
			$sql .= ' SET '.$fieldname.' = '.(($id > 0 || $id == '0') ? $id : 'NULL');
			$sql .= ' WHERE rowid='.$this->id;

			if ($this->db->query($sql))
			{
				$this->mode_reglement_id = $id;
				// for supplier
				if (get_class($this) == 'Fournisseur') $this->mode_reglement_supplier_id = $id;
				return 1;
			}
			else
			{
				dol_syslog(get_class($this).'::setPaymentMethods Erreur '.$sql.' - '.$this->db->error());
				$this->error=$this->db->error();
				return -1;
			}
		}
		else
		{
			dol_syslog(get_class($this).'::setPaymentMethods, status of the object is incompatible');
			$this->error='Status of the object is incompatible '.$this->statut;
			return -2;
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

	public function fetch_by_loyer($id)
	{
		$sql = "SELECT ip.rowid as reference, ip.fk_rent, ip.fk_property,";
		$sql .= "ip.fk_renter, ip.amount, ip.note_public, ip.date_payment,";
		$sql .= "ip.fk_owner, ip.fk_receipt";
		$sql .= " , lc.lastname as nomlocataire , ll.label as nomlocal , lo.label as nomloyer ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as ip ";
		$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll ";
		$sql .= " , " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as lo ";
		$sql .= "WHERE ip.fk_renter = lc.rowid AND ip.fk_property = ll.rowid AND ip.fk_receipt = lo.rowid AND lo.rowid = " . $id;

		dol_syslog ( get_class ( $this ) . "::fetch_by_loyer sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );

				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->fk_rent = $obj->fk_rent;
				$this->fk_property = $obj->fk_property;
				$this->nomlocal = $obj->nomlocal;
				$this->fk_renter = $obj->fk_renter;
				$this->nomlocataire = $obj->nomlocataire;
				$this->amount = $obj->amount;
				$this->note_public = $obj->note_public;
				$this->date_payment = $this->db->jdate ( $obj->date_payment );
				$this->fk_receipt = $obj->fk_receipt;
				$this->nomloyer = $obj->nomloyer;
				$this->fk_owner = $obj->fk_owner;

				1;
			} else {
				return 0;
			}
			$this->db->free ( $resql );
		} else {
			$this->error = $this->db->error ();
			return - 1;
		}
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

	/**
	 *  Create an intervention document on disk using template defined into PROJECT_ADDON_PDF
	 *
	 *  @param	string		$modele			Force template to use ('' by default)
	 *  @param	Translate	$outputlangs	Objet lang to use for translation
	 *  @param  int			$hidedetails    Hide details of lines
	 *  @param  int			$hidedesc       Hide description
	 *  @param  int			$hideref        Hide ref
	 *  @return int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		global $conf,$langs;

		$langs->load("ultimateimmo@ultimateimmo");

		if (empty($modele)) {
			$this->error='PDFModelMissing';
			$this->errors[]='PDFModelMissing';
			return -1;
		}

		$modelpath = "/ultimateimmo/core/modules/ultimateimmo/doc/";

		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref);
	}
}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */

	class ImmoPaymentLine
	{
		/**
		 * @var int rowID
		 */
		public $rowid;
		/**
		 * @var int fk_rent
		 */
		public $fk_rent;
		/**
		 * @var int fk_property
		 */
		public $fk_property;
		/**
		 * @var int fk_renter
		 */
		public $fk_renter;
		/**
		 * @var int amount
		 */
		public $amount;
		/**
		 * @var int fk_mode_reglement
		 */
		public $fk_mode_reglement;
		/**
		 * @var int note_public
		 */
		public $note_public;
		/**
		 * @var int date_payment
		 */
		public $date_payment = '';
		/**
		 * @var int fk_owner
		 */
		public $fk_owner;
		/**
		 * @var int fk_receipt
		 */
		public $fk_receipt;
	}
