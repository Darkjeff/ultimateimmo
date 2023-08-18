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
 * \file        class/immoreceipt.class.php
 * \ingroup     ultimateimmo
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
	public $table_element = 'ultimateimmo_immoreceipt';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $fk_element='fk_receipt';

	/**
	 * @var ImmoreceiptLine[] Lines
	 */
	public $lines = array();

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
	public $picto = 'immoreceipt@ultimateimmo';

	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;

	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;

	/**
	 * Credit note status
	 */
	const STATUS_CANCELED = 9;

	/**
     * Credit note invoice
     */
    const TYPE_CREDIT_NOTE = 2;

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

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid'         => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -2, 'noteditable' => 1, 'notnull' => 1, 'index' => 1, 'position' => 1, 'comment' => 'Id'),
		'ref'           => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => 1, 'visible' => 1, 'noteditable' => 0, 'default' => '', 'notnull' => 1, 'showoncombobox' => 1, 'index' => 1, 'position' => 10, 'searchall' => 1, 'comment' => 'Reference of object'),
		'entity'        => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'visible' => 0, 'notnull' => 1, 'default' => 1, 'index' => 1, 'position' => 20),
		'fk_rent'       => array('type' => 'integer:ImmoRent:ultimateimmo/class/immorent.class.php', 'label' => 'ImmoRent', 'enabled' => 1, 'visible' => 1, 'position' => 30, 'notnull' => -1, 'searchall' => 1, 'foreignkey' => 'ultimateimmo_immorent.rowid'),
		'fk_property'   => array('type' => 'integer:ImmoProperty:ultimateimmo/class/immoproperty.class.php', 'label' => 'Property', 'visible' => 1, 'enabled' => 1, 'position' => 35, 'notnull' => -1, 'index' => 1, 'foreignkey' => 'ultimateimmo_immoproperty.rowid', 'searchall' => 1, 'help' => "LinkToProperty"),
		'fk_renter'     => array('type' => 'integer:ImmoRenter:ultimateimmo/class/immorenter.class.php', 'label' => 'Renter', 'enabled' => 1, 'visible' => 1, 'position' => 40, 'notnull' => -1, 'index' => 1, 'searchall' => 1, 'foreignkey' => 'ultimateimmo_immorenter.rowid', 'help' => "LinkToRenter"),
		'fk_owner'      => array('type' => 'integer:ImmoOwner:ultimateimmo/class/immoowner.class.php', 'label' => 'Owner', 'visible' => 1, 'enabled' => 1, 'position' => 45, 'notnull' => -1, 'index' => 1, 'searchall' => 1, 'help' => "LinkToOwner"),
		'fk_soc' 		=> array('type' => 'integer:Societe:societe/class/societe.class.php:1:(status:=:1)', 'label' => 'ThirdParty', 'visible' => 1, 'enabled' => 1, 'position' => 46, 'notnull' => -1, 'index' => 1, 'help' => 'LinkToThirdparty'),
		/*'fk_payment' => array('type'=>'integer:ImmoPayment:ultimateimmo/class/immopayment.class.php', 'label'=>'ImmoPayment', 'enabled'=>1, 'visible'=>1, 'position'=>48, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToPayment",),*/
		'note_public'   => array('type' => 'html', 'label' => 'NotePublic', 'enabled' => 1, 'visible' => 0, 'position' => 50),
		'note_private'  => array('type' => 'html', 'label' => 'NotePrivate', 'enabled' => 1, 'visible' => 0, 'position' => 55),
		'date_echeance' => array('type' => 'date', 'label' => 'Echeance', 'enabled' => 1, 'visible' => 1, 'position' => 56, 'notnull' => -1, 'default' => 'null'),
		'date_start'    => array('type' => 'date', 'label' => 'DateStart', 'enabled' => 1, 'visible' => -1, 'position' => 57, 'notnull' => -1),
		'date_end'      => array('type' => 'date', 'label' => 'DateEnd', 'enabled' => 1, 'visible' => -1, 'position' => 58, 'notnull' => -1),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'visible' => -2, 'position' => 59, 'notnull' => -1),
		'date_validation'  => array('type' => 'datetime', 'label' => 'DateValidation', 'enabled' => 1, 'visible' => -2, 'position' => 60, 'notnull' => -1),
		'label'         => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'visible' => 1, 'position' => 30, 'searchall' => 1, 'css' => 'minwidth200', 'help' => 'ImmoPaymentLabelInfo', 'showoncombobox' => 1),
		'rentamount'    => array('type' => 'price', 'label' => 'RentAmount', 'enabled' => 1, 'visible' => 1, 'position' => 65, 'notnull' => -1, 'isameasure' => '1', 'help' => 'ImmoPaymentRentAmountInfo'),
		'chargesamount' => array('type' => 'price', 'label' => 'ChargesAmount', 'enabled' => 1, 'visible' => 1, 'position' => 70, 'notnull' => -1, 'isameasure' => '1', 'help' => 'ImmoPaymentChargeAmountInfo'),
		'total_amount'  => array('type' => 'price', 'label' => 'TotalAmount', 'enabled' => 1, 'visible' => 5, 'default' => 'null', 'position' => 75, 'searchall' => 0, 'isameasure' => 1, 'help' => 'ImmoPaymentTotalAmountInfo'),
		'partial_payment' => array('type' => 'price', 'label' => 'PartialPayment', 'enabled' => 1, 'visible' => 5, 'position' => 80, 'notnull' => -1, 'default' => 'null', 'isameasure' => 1, 'help' => "Help text for partial payment"),
		'balance'       => array('type' => 'price', 'label' => 'Balance', 'enabled' => 1, 'visible' => 5, 'position' => 85, 'notnull' => -1, 'default' => 'null', 'isameasure' => 1, 'help' => "Help text"),
		'paye'          => array('type' => 'integer', 'label' => 'Paye', 'enabled' => 1, 'visible' => 1, 'position' => 90, 'notnull' => 1, 'arrayofkeyval' => array('0' => 'UnPaidReceipt', '1' => 'PaidReceipt', '2' => 'PartiallyPaidReceipt')),
		'vat_amount'    => array('type' => 'price', 'label' => 'VatAmount', 'enabled' => 1, 'visible' => 1, 'position' => 95, 'notnull' => -1,),
		'vat_tx'        => array('type' => 'integer', 'label' => 'VatTx', 'enabled' => 1, 'visible' => 1, 'position' => 96, 'notnull' => -1),
		'tms'           => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -2, 'position' => 501, 'notnull' => 1),
		//'fk_statut' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>-2, 'position'=>509, 'notnull'=>-1,),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1, 'visible' => -2, 'position' => 510, 'notnull' => 1, 'foreignkey' => 'llx_user.rowid'),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'enabled' => 1, 'visible' => -2, 'position' => 511, 'notnull' => -1, 'foreignkey' => 'llx_user.rowid',),
		'fk_user_valid' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserValid', 'enabled' => 1, 'visible' => -2, 'position' => 512, 'notnull' => -1),
		'import_key'    => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => 1, 'visible' => -2, 'position' => 1000, 'notnull' => -1),
		'model_pdf'     => array('type' => 'varchar(128)', 'label' => 'ModelPdf', 'enabled' => 1, 'visible' => -2, 'position' => 1010, 'notnull' => -1, 'index' => 1, 'searchall' => 1),
		'last_main_doc' => array('type' => 'varchar(255)', 'label' => 'LastMainDoc', 'enabled' => 1, 'visible' => -2, 'position' => 1020, 'notnull' => -1),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'position' => 1000, 'notnull' => -1, 'default' => '0', 'index' => 1, 'arrayofkeyval' => array('0' => 'ImmoReceiptStatusDisabled', '1' => 'ImmoReceiptStatusActive', '-1' => 'Cancel')),
	);

	/**
     * @var DoliDB Database handler.
     */
    public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

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

	public $fk_property;

	public $fk_renter;

	public $fk_owner;

	public $fk_soc;

	public $note_public;

	public $note_private;

	/**
     * @var integer|string date_echeance
     */
	public $date_echeance;

	/**
     * @var integer|string date_start
     */
	public $date_start;

	/**
     * @var integer|string date_end
     */
	public $date_end;

	/**
     * @var integer|string date_creation
     */
	public $date_creation;

	/**
     * @var integer|string date_validation
     */
	public $date_validation;

	public $label;

	public $rentamount;

	public $chargesamount;

	public $total_amount;

	public $partial_payment;

	public $balance;

	//public $fk_payment;

	public $paye;

	public $vat_amount;

	public $vat_tx;

	public $tms;

	//public $fk_statut;

	/**
     * @var int ID
     */
	public $fk_user_creat;

	/**
     * @var int ID
     */
	public $fk_user_modif;

	/**
     * @var int ID
     */
	public $fk_user_valid;

	/**
     * @var string import_key
     */
	public $import_key;

	public $model_pdf;

	public $last_main_doc;

	/**
	 * @var int Status
	 */
	public $status;
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
		global $conf, $langs, $user;

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
		foreach ($this->fields as $key => $val) {
			if (is_array($this->fields['status']['arrayofkeyval'])) {
				foreach ($this->fields['status']['arrayofkeyval'] as $key2 => $val2) {
					$this->fields['status']['arrayofkeyval'][$key2] = $langs->trans($val2);
				}
			}
			if (is_array($this->fields['paye']['arrayofkeyval'])) {
				foreach ($this->fields['paye']['arrayofkeyval'] as $key3 => $val3) {
					$this->fields['paye']['arrayofkeyval'][$key3] = $langs->trans($val3);
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
		global $langs, $conf;

		$error = 0;

		$now = dol_now();

		$fieldvalues = $this->setSaveQuery();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation'] = $this->db->idate($now);
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
			if (empty($this->fields[$key]['ref']) && $values[$key] == '') $values[$key] = '(PROV' . $this->id . ')'; //is that ok ?

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
			if ($res) {
				$error = 0;

				$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);

				// Load object modReceipt
				$module = (!empty($conf->global->ULTIMATEIMMO_ADDON_NUMBER) ? $conf->global->ULTIMATEIMMO_ADDON_NUMBER : 'mod_ultimateimmo_standard');

				if (substr($module, 0, 17) == 'mod_ultimateimmo_' && substr($module, -3) == 'php') {
					$module = substr($module, 0, dol_strlen($module) - 4);
				}
				$result = dol_buildpath('/ultimateimmo/core/modules/ultimateimmo/' . $module . '.php', 1);

				if ($result >= 0) {
					dol_include_once('/ultimateimmo/core/modules/ultimateimmo/mod_ultimateimmo_standard.php');
					$modCodeUltimateimmo = new $module();

					if (!empty($modCodeUltimateimmo->code_auto)) {
						// Force the ref to a draft value if numbering module is an automatic numbering
						$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . " SET ref ='(PROV" . $this->id . ")' WHERE (ref = '(PROV)' OR ref = '') AND rowid = " . $this->id;
						$resqlupdate = $this->db->query($sql);

						if ($resqlupdate === false) {
							$error++;
							$this->errors[] = "Error ".$this->db->lasterror();
						} else {
							$this->ref = '(PROV' . $this->id . ')';
						}
					}
				}
			}
			if ($res === false) {
				$error++;
				$this->errors[] = "Error ".$this->db->lasterror();
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
					$this->errors[] = "Error ".$this->db->lasterror();
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
		$this->total_amount = (float)$this->rentamount + (float)$this->chargesamount;
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid = 0)
	{
		global $langs, $hookmanager, $extrafields;

		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetchLines();

		$objsoc = new Societe($this->db);

		// Change socid if needed
		if (!empty($socid) && $socid != $object->socid) {
			if ($objsoc->fetch($socid) > 0) {
				$object->socid = $objsoc->id;
			}
		} else {
			$objsoc->fetch($object->socid);
		}

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
			$this->error = $object->error;
			$this->errors = array_merge($this->errors, $object->errors);
			$error++;
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

		if (!$error) {
			// Hook of thirdparty module
			if (is_object($hookmanager)) {
				$parameters = array('objFrom' => $this, 'clonedObj' => $object);
				$action = '';
				$reshook = $hookmanager->executeHooks('createFrom', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
				if ($reshook < 0) $error++;
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
		$array = array_splice($array, 0, count($array), $array[0]);
		$array = implode(', t.', $array);

		$sql = 'SELECT ' . $array . ',';
		$sql .= ' lc.rowid as renter_id,';
		$sql .= ' lc.email as emaillocataire,';
		$sql .= ' cp.libelle as payment_label, cp.code as payment_code';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immorenter as lc ON t.fk_renter = lc.rowid';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immoproperty as ll ON t.fk_property = ll.rowid';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immorent as ic ON t.fk_rent = ic.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immopayment as pm ON t.fk_payment = pm.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_paiement as cp ON pm.fk_mode_reglement = cp.id';

		if (!empty($id)) $sql .= ' WHERE t.rowid = ' . $id;
		else $sql .= ' WHERE t.ref = ' . $this->quote($ref, $this->fields['ref']);

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$res = $this->db->query($sql);
		if ($res) {
			if ($obj = $this->db->fetch_object($res)) {
				if ($obj) {
					$this->id = $id;
					$this->set_vars_by_db($obj);

					if ($obj->status == self::STATUS_DRAFT) {
						$this->brouillon = 1;
					}

					$this->fk_mode_reglement  = $obj->fk_mode_reglement;
					$this->mode_reglement_code = $obj->payment_code;
					$this->mode_reglement = $obj->payment_label;
					$this->date_rent = $this->db->jdate($obj->date_rent);
					$this->date_start = $this->db->jdate($obj->date_start);
					$this->date_end = $this->db->jdate($obj->date_end);
					$this->date_creation = $this->db->jdate($obj->date_creation);
					$this->date_echeance = $this->db->jdate($obj->date_echeance);
					$this->tms = $this->db->jdate($obj->tms);

					$this->setVarsFromFetchObj($obj);
					//var_dump($obj);exit;
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
		//if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
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
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid' || $key=='t.fk_rent') {
					$sqlwhere[] = $key.'='.$value;
				} elseif ($key== 'finddate') {
					$sqlwhere[] = 't.date_start>=\''.$this->db->idate($value['dtstart']).'\'';
					$sqlwhere[] = 't.date_end<=\''.$this->db->idate($value['dtend']).'\'';
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
			$sql .= ' AND (' . implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .=  ' ' . $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
            $i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
			    $obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 *
	 * @param unknown $id
	 * @param array $filter
	 */
	public function fetchByLocalId($id, $filter=array())
	{
		$sql = "SELECT il.rowid as reference, il.fk_rent , il.fk_property, il.label as nomrenter, il.fk_renter, il.total_amount,";
		$sql .= " il.rentamount, il.chargesamount, il.date_echeance, il.note_public, il.status, il.paye ,";
		$sql .= " il.date_start , il.date_end, il.fk_owner, il.partial_payment ";
		$sql .= " , lc.firstname as nomlocataire , ll.label as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . $this->table_element." as il ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as lc ON il.fk_renter = lc.rowid";
		$sql .= " INNER JOIN  " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll ON il.fk_property = ll.rowid ";
		$sql .= " WHERE il.fk_property = " . $id;

		if (is_array($filter) && count($filter)>0)
		{
			foreach($filter as $key=>$value)
			{
				if ($key=='insidedaterenter')
				{
					$sql .= " AND il.date_start<='".$this->db->idate($value)."' AND il.date_end>='".$this->db->idate($value)."'";
				}
			}
		}

		dol_syslog(get_class($this) . "::fetchByLocalId sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {

			$this->line = array ();
			$num = $this->db->num_rows($resql);
			$this->lines=array();

			while ($obj = $this->db->fetch_object($resql))
			{
				$line = new immoreceiptLine();

				$line->id = $obj->reference;
				$line->ref = $obj->reference;
				$line->fk_rent = $obj->fk_rent;
				$line->fk_property = $obj->fk_property;
				$line->nomlocal = $obj->nomlocal;
				$line->label = $obj->nomrenter;
				$line->fk_renter = $obj->fk_renter;
				$line->nomlocataire = $obj->nomlocataire;
				$line->total_amount = $obj->total_amount;
				$line->rentamount = $obj->rentamount;
				$line->chargesamount = $obj->chargesamount;
				$line->date_echeance = $this->db->jdate ( $obj->date_echeance );
				$line->note_public = $obj->note_public;
				$line->status = $obj->status;
				$line->date_start = $this->db->jdate ( $obj->date_start );
				$line->date_end = $this->db->jdate ( $obj->date_end );
				$line->encours = $obj->encours;
				$line->regul = $obj->regul;
				$line->fk_owner = $obj->fk_owner;
				$line->paye = $obj->paye;
				$line->partial_payment = $obj->partial_payment;
				$line->fk_payment = $obj->fk_payment;

				$this->lines[] = $line;

			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetchByLocalId " . $this->error, LOG_ERR);
			return - 1;
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
		$this->total_amount = (float)$this->rentamount + (float)$this->chargesamount;
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
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("ultimateimmo@ultimateimmo");

		if (empty($conf->global->ULTIMATEIMMO_ADDON_NUMBER))
		{
			$conf->global->ULTIMATEIMMO_ADDON_NUMBER = 'mod_ultimateimmo_standard';
		}

		if (!empty($conf->global->ULTIMATEIMMO_ADDON_NUMBER))
		{
			$mybool = false;

			$file = $conf->global->ULTIMATEIMMO_ADDON_NUMBER.".php";
			$classname = $conf->global->ULTIMATEIMMO_ADDON_NUMBER;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."ultimateimmo/core/modules/ultimateimmo/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			$obj = new $classname();
			$numref = $obj->getNextValue($this);

			if ($numref != "")
			{
				return $numref;
			}
			else
			{
				$this->error = $obj->error;
				$this->errors = array_merge($this->errors,$obj->errors);
				//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
				return -1;
			}
		}
		else
		{
			$this->error = $langs->trans("Error")." ".$langs->trans("Error_ULTIMATEIMMO_ADDON_NUMBER_NotDefined");
			$this->errors[] = $langs->trans("Error")." ".$langs->trans("Error_ULTIMATEIMMO_ADDON_NUMBER_NotDefined");
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->ultimateimmo->write))))
		{
			$this->error='ErrorPermissionDenied';
			dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
			return -1;
		}*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $this->getNextNumRef();
			if ((int)$num<0) {
				$error++;
			}
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (empty($error)) {
			// Validate
			$sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element;
			$sql .= " SET ref = '" . $this->db->escape($num) . "',";
			$sql .= " status = " . self::STATUS_VALIDATED . ",";
			$sql .= " date_validation = '" . $this->db->idate($now) . "',";
			$sql .= " fk_user_valid = " . $user->id;
			$sql .= " WHERE rowid = " . $this->id;

			dol_syslog(get_class($this) . "::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);

			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}
		}

		// Trigger calls
		if (!$error && !$notrigger) {
			// Call trigger
			//$result = $this->call_trigger('IMMORECEIPT_VALIDATE', $user);
			if ($result < 0) {
				$error++;
			}
			// End call triggers
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE ' . MAIN_DB_PREFIX . "ecm_files set filename = CONCAT('" . $this->db->escape($this->newref) . "', SUBSTR(filename, " . (strlen($this->ref) + 1) . ")), filepath = 'immoreceipt/" . $this->db->escape($this->newref) . "'";
				$sql .= " WHERE filename LIKE '" . $this->db->escape($this->ref) . "%' AND filepath = 'immoreceipt/" . $this->db->escape($this->ref) . "' and entity = " . $conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++;
					$this->error = $this->db->lasterror();
				}
				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->ultimateimmo->dir_output . '/immoreceipt/' . $oldref;
				$dirdest = $conf->ultimateimmo->dir_output . '/immoreceipt/' . $newref;

				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this) . "::validate() rename dir " . $dirsource . " into " . $dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->ultimateimmo->dir_output . '/immoreceipt/' . $newref, 'files', 1, '^' . preg_quote($oldref, '/'));
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

		// global $dolibarr_main_authentication, $dolibarr_main_demo;
		// global $menumanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1;   // Force disable tooltips

		$result = '';

		$label = '<u>' . $langs->trans("ImmoReceipt") . '</u>';
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Label') . ':</b> ' . $this->label;
		if (isset($this->status)) {
			$label .= '<br><b>' . $langs->trans("Status") . ":</b> " . $this->getLibStatut(5);
		}

		$url = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1) . '?id=' . $this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowImmoReceipt");
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

		global $action, $hookmanager;
		$hookmanager->initHooks(array('immoreceiptdao'));
		$parameters = array('id' => $this->id, 'getnomurl' => $result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$langs->load("ultimateimmo@ultimateimmo");

		if (!dol_strlen($modele)) {
			$modele = 'quittance';

			if ($this->model_pdf) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->ULTIMATEIMMO_ADDON_PDF)) {
				$modele = $conf->global->ULTIMATEIMMO_ADDON_PDF;
			}
		}

		$modelpath = "ultimateimmo/core/modules/ultimateimmo/doc/";

		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
	}


	/**
	 *  Return label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
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
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("mymodule");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
		}

		$statusType = 'status' . $status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
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
		$sql = 'SELECT t.rowid, t.date_creation as datec, t.tms as datem,';
		$sql .= ' t.fk_user_creat, t.fk_user_modif';
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
					$this->user_creation = $cuser;
				}

				if ($obj->fk_user_valid) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture) {
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
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
		//$this->initAsSpecimenCommon();
		$now = dol_now();

		// Load array of rents rentids
		$num_rents = 0;
		$rentids = array();
		$sql = "SELECT rowid";
		$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immorent";
		$sql .= " WHERE entity IN (" . getEntity('product') . ")";
		$resql = $this->db->query($sql);
		if ($resql) {
			$num_rents = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num_rents) {
				$i++;
				$row = $this->db->fetch_row($resql);
				$rentids[$i] = $row[0];
			}
		}

		// Initialise parameters
		$this->rowid = 0;
		$this->ref = 'SPECIMEN';
		$this->specimen = 1;
		$this->label = 'IMMORECEIPT SPECIMEN';
		$this->date_echeance = $now;
		$this->date_creation = $now;
		$this->date_start = $now;
		$this->date_end = $now + (3600 * 24 * 365);
		$this->rentamount = 1000;
		$this->note_public = 'This is a comment';

		// Lines
		$nbp = 5;
		$xnbp = 0;
		while ($xnbp < $nbp) {

			$line = new immoreceiptLine();

			$line->nomlocal = 'nomlocal';
			$line->label = 'nomrenter';
			$line->nomlocataire = 'M & Mme Locator';
			$line->total_amount = 1000;
			$line->rentamount = 800;
			$line->chargesamount = 200;
			$line->date_echeance = $this->db->jdate($now);
			$line->note_public = 'blablabla';
			$line->date_start = $this->db->jdate($now);
			$line->date_end = $this->db->jdate($now);
			$line->encours = 2500;
			$line->regul = 1500;
			$line->partial_payment = 500;

			if ($num_rents > 0) {
				$rentid = mt_rand(1, $num_rents);
				$line->fk_rent = $rentids[$rentid];
			}

			$this->lines[$xnbp] = $line;
			$xnbp++;
		}
	}


	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
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
	 * @param unknown $user
	 * @return number
	 */
	public function set_paid($user)
	{
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element.' SET';
		$sql .= ' paye=1';
		$sql .= ' WHERE rowid = ' . $this->id;
		$return = $this->db->query ( $sql );
		$this->db->commit ();
		if ($return)
			return 1;
			else
				return - 1;
	}

	/**
	 * 	Return amount of payments already done
	 *  @param 		int 	$multicurrency 	Return multicurrency_amount instead of amount
	 *	@return		int						Amount of payment already done, <0 if KO
	 */
	function getSommePaiement()
	{
		$table = 'ultimateimmo_immopayment';
		$field = 'fk_receipt';

		$sql = 'SELECT SUM(amount) as amount';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $table;
		$sql .= ' WHERE ' . $field . ' = ' . $this->id;
		$sql .= ' GROUP BY ' . $field;

		dol_syslog(get_class($this) . "::getSommePaiement", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			$this->db->free($resql);
			return $obj->amount;
		} else {
			$this->error = "Error ".$this->db->lasterror();
			return -1;
		}
	}
}

/**
 * Class ImmoreceiptLine
 */
class ImmoreceiptLine
{
	/**
	 * @var int ID
	 */
	public $id;
	/**
	 * @var mixed Sample line property 1
	 */

	public $fk_rent;
	public $fk_property;
	public $label;
	public $fk_renter;
	public $total_amount;
	public $rentamount;
	public $balance;
	public $partial_payment;
	public $fk_payment;
	public $chargesamount;
	public $vat_amount;
	public $date_echeance = '';
	public $note_public;
	public $status;
	public $date_rent = '';
	public $date_start = '';
	public $date_end = '';
	public $fk_owner;
	public $paye;
	public $renter_id;
	public $nomlocataire;
	public $nomlocal;
	public $property_id;
}
