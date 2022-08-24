<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2022 Philippe GRAND  <philippe.grand@atoo-net.com>
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
	 * Validated status (need to be paid)
	 */
	const STATUS_VALIDATED = 1;

	/**
	 * Credit note invoice
	 */
	const TYPE_CREDIT_NOTE = 2;

	/**
	 * Credit note status
	 */
	const STATUS_CANCELED = 9;

	const STATUS_UNPAID = 10;
	const STATUS_PAID = 11;

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
		'fk_soc' 		=> array('type' => 'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)', 'picto'=>'company', 'css' => 'minwidth300 widthcentpercentminusxx maxwidth500', 'label' => 'LinkedToDolibarrThirdParty', 'visible' => 1, 'enabled' => 1, 'position' => 42, 'notnull' => -1, 'index' => 1, 'help' => 'SetLinkToThirdparty'),
		'fk_owner'      => array('type' => 'integer:ImmoOwner:ultimateimmo/class/immoowner.class.php', 'label' => 'Owner', 'visible' => 1, 'enabled' => 1, 'position' => 45, 'notnull' => -1, 'index' => 1, 'searchall' => 1, 'help' => "LinkToOwner"),
		/*'fk_payment' => array('type'=>'integer:ImmoPayment:ultimateimmo/class/immopayment.class.php', 'label'=>'ImmoPayment', 'enabled'=>1, 'visible'=>1, 'position'=>48, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToPayment",),*/
		//'fk_bank' => array('type'=>'integer', 'label'=>'Bank', 'enabled'=>1, 'visible'=>1, 'position'=>46, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToBank",),
		'note_public'   => array('type' => 'html', 'label' => 'NotePublic', 'enabled' => 1, 'visible' => 0, 'position' => 50),
		'note_private'  => array('type' => 'html', 'label' => 'NotePrivate', 'enabled' => 1, 'visible' => 0, 'position' => 55),
		'date_echeance' => array('type' => 'date', 'label' => 'Echeance', 'enabled' => 1, 'visible' => 1, 'position' => 56, 'notnull' => -1, 'default' => 'null'),
		'date_start'    => array('type' => 'date', 'label' => 'DateStartPeriod', 'enabled' => 1, 'visible' => -1, 'position' => 57, 'notnull' => -1),
		'date_end'      => array('type' => 'date', 'label' => 'DateEndPeriod', 'enabled' => 1, 'visible' => -1, 'position' => 58, 'notnull' => -1),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'visible' => -2, 'position' => 59, 'notnull' => -1),
		'date_validation'  => array('type' => 'datetime', 'label' => 'DateValidation', 'enabled' => 1, 'visible' => -2, 'position' => 60, 'notnull' => -1),
		'label'         => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'visible' => 1, 'position' => 30, 'searchall' => 1, 'css' => 'minwidth200', 'help' => 'ImmoPaymentLabelInfo', 'showoncombobox' => 1),
		'rentamount'    => array('type' => 'price', 'label' => 'RentAmount', 'enabled' => 1, 'visible' => 1, 'position' => 65, 'notnull' => -1, 'isameasure' => '1', 'help' => "ImmoPaymentRentAmountInfo"),
		'chargesamount' => array('type' => 'price', 'label' => 'ChargesAmount', 'enabled' => 1, 'visible' => 1, 'position' => 70, 'notnull' => -1, 'isameasure' => '1', 'help' => "ImmoPaymentChargeAmountInfo"),
		'total_amount'  => array('type' => 'price', 'label' => 'TotalAmount', 'enabled' => 1, 'visible' => 1, 'default' => 'null', 'position' => 75, 'searchall' => 0, 'isameasure' => 1, 'help' => 'ImmoPaymentTotalAmountInfo'),
		'partial_payment' => array('type' => 'price', 'label' => 'PartialPayment', 'enabled' => 1, 'visible' => 1, 'position' => 80, 'notnull' => -1, 'default' => 'null', 'isameasure' => 1, 'help' => "Help text for partial payment"),
		'balance'       => array('type' => 'price', 'label' => 'Balance', 'enabled' => 1, 'visible' => 1, 'position' => 85, 'notnull' => -1, 'default' => 'null', 'isameasure' => 1, 'help' => "Help text"),
		'paye'          => array('type' => 'varchar(64)', 'label' => 'Paye', 'enabled' => 1, 'visible' => 1, 'position' => 90, 'notnull' => 1, 'arrayofkeyval' => array('0' => 'UnPaidReceipt', '1' => 'PaidReceipt', '2' => 'PartiallyPaidReceipt')),
		'vat_tx'        => array('type' => 'double(6,3)', 'label' => 'VatTx', 'enabled' => 1, 'visible' => 1, 'position' => 95, 'notnull' => -1),
		'vat_amount'    => array('type' => 'price', 'label' => 'VatAmount', 'enabled' => 1, 'visible' => 1, 'position' => 96, 'notnull' => -1,),
		'tms'           => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -2, 'position' => 501, 'notnull' => 1),
		//'fk_statut' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>-2, 'position'=>509, 'notnull'=>-1,),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'picto'=>'user', 'enabled' => 1, 'visible' => -2, 'position' => 510, 'notnull' => 1, 'foreignkey' => 'llx_user.rowid'),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'picto'=>'user', 'enabled' => 1, 'visible' => -2, 'position' => 511, 'notnull' => -1, 'foreignkey' => 'llx_user.rowid'),
		'fk_user_valid' => array('type' => 'integer', 'label' => 'UserValid', 'picto'=>'user', 'enabled' => 1, 'visible' => -2, 'position' => 512, 'notnull' => -1),
		'import_key'    => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => 1, 'visible' => -2, 'position' => 1000, 'notnull' => -1),
		'model_pdf'     => array('type' => 'varchar(128)', 'label' => 'ModelPdf', 'enabled' => 1, 'visible' => -2, 'position' => 1010, 'notnull' => -1, 'index' => 1, 'searchall' => 1),
		'last_main_doc' => array('type' => 'varchar(255)', 'label' => 'LastMainDoc', 'enabled' => 1, 'visible' => -2, 'position' => 1020, 'notnull' => -1),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'position' => 1000, 'notnull' => -1, 'default' => '0', 'index' => 1, 'arrayofkeyval' => array('0' => 'Draft', '1' => 'Validate', '2' => 'Cancel')),
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

	//public $fk_bank;

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
		$this->status = self::STATUS_DRAFT;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
				if (is_array($this->fields['paye']['arrayofkeyval'])) {
					foreach ($this->fields['paye']['arrayofkeyval'] as $key3 => $val3) {
						$this->fields['paye']['arrayofkeyval'][$key3] = $langs->trans($val3);
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
		if ($result > 0 && !empty($object->table_element_line)) {
			$object->fetchLines();
		}

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
	 * @param	string $morewhere		More SQL filters (' AND ...')
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
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immorenter as lc ON t.fk_renter = lc.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immoproperty as ll ON t.fk_property = ll.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immorent as ic ON t.fk_rent = ic.rowid';
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
						$this->brouillon = -1;
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
		//var_dump(__file__.' '.__line__);
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines=array();

		// Load lines with object ImmoReceiptLine
		$result = $this->fetchLinesCommon();
		return $result;
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

		$sql = 'SELECT';
		$sql .= $this->getFieldList();
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		} else {
			$sql .= ' WHERE 1 = 1';
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				}
				elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				}
				elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				}
				else {
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
			while ($i < min($limit, $num))
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
	public function fetchByLocalId($id, $filter = array())
	{
		$sql = "SELECT il.rowid as reference, il.fk_rent , il.fk_property, il.label as nomrenter, il.fk_renter, il.total_amount,";
		$sql .= " il.rentamount, il.chargesamount, il.date_echeance, il.note_public, il.status, il.paye ,";
		$sql .= " il.date_start , il.date_end, il.fk_owner, il.partial_payment ";
		$sql .= " , lc.firstname as nomlocataire , ll.label as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . $this->table_element . " as il ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as lc ON il.fk_renter = lc.rowid";
		$sql .= " INNER JOIN  " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as ll ON il.fk_property = ll.rowid ";
		$sql .= " WHERE il.fk_property = " . $id;

		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 'insidedaterenter') {
					$sql .= " AND il.date_start<='" . $this->db->idate($value) . "' AND il.date_end>='" . $this->db->idate($value) . "'";
				}
			}
		}

		dol_syslog(get_class($this) . "::fetchByLocalId sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {

			$this->line = array();
			$num = $this->db->num_rows($resql);
			$this->lines = array();

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new immoreceiptLine($this->db);

				$line->id = $obj->reference;
				$line->ref = $obj->reference;
				$line->fk_rent = $obj->fk_rent;
				$line->fk_property = $obj->fk_property;
				$line->nomlocal = $obj->nomlocal;
				$line->label = $obj->nomrenter;
				//$line->fk_bank = $obj->fk_bank;
				$line->fk_renter = $obj->fk_renter;
				$line->nomlocataire = $obj->nomlocataire;
				$line->total_amount = $obj->total_amount;
				$line->rentamount = $obj->rentamount;
				$line->chargesamount = $obj->chargesamount;
				$line->date_echeance = $this->db->jdate($obj->date_echeance);
				$line->note_public = $obj->note_public;
				$line->status = $obj->status;
				$line->date_start = $this->db->jdate($obj->date_start);
				$line->date_end = $this->db->jdate($obj->date_end);
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
			return -1;
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
				//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
				return "";
			}
		}
		else
		{
			print $langs->trans("Error")." ".$langs->trans("Error_ULTIMATEIMMO_ADDON_NUMBER_NotDefined");
			return "";
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
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

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

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', $this->picto) . '<u>' . $langs->trans("ImmoReceipt") . '</u>';
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
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('immoreceiptdao'));
		$parameters = array('id' => $this->id, 'getnomurl' => $result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

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
	 *  Retourne le libelle du statut d'une charge (impaye, payee)
	 *
	 *  @param	int		$mode       	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=short label + picto, 6=Long label + picto
	 *  @param  double	$alreadypaid	0=No payment already done, >0=Some payments were already done (we recommand to put here amount paid if you have it, 1 otherwise)
	 *  @return	string        			Label
	 */
	public function getLibStatut($mode = 0, $alreadypaid = -1)
	{
		return $this->LibStatut($this->paye, $mode, $alreadypaid);
	}

    /**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @param  double	$alreadypaid	0=No payment already done, >0=Some payments were already done (we recommand to put here amount paid if you have it, 1 otherwise)
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0, $alreadypaid = 1)
	{
		// phpcs:enable
		global $langs;

		// Load translation files required by the page
		$langs->loadLangs(array("customers", "bills"));

		// We reinit status array to force to redefine them because label may change according to properties values.
		$this->labelStatus = array();
		$this->labelStatusShort = array();
		//$alreadypaid = $this->getSommePaiement();

		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("mymodule");
			$this->labelStatus[self::STATUS_UNPAID] = $langs->transnoentitiesnoconv('Unpaid');
			$this->labelStatus[self::STATUS_PAID] = $langs->transnoentitiesnoconv('Paid');
			if ($status == self::STATUS_UNPAID && $alreadypaid > 0) {
				$this->labelStatus[self::STATUS_UNPAID] = $langs->transnoentitiesnoconv("BillStatusStarted");
			}
			$this->labelStatusShort[self::STATUS_UNPAID] = $langs->transnoentitiesnoconv('Unpaid');
			$this->labelStatusShort[self::STATUS_PAID] = $langs->transnoentitiesnoconv('Paid');
			if ($status == self::STATUS_UNPAID && $alreadypaid > 0) {
				$this->labelStatusShort[self::STATUS_UNPAID] = $langs->transnoentitiesnoconv("BillStatusStarted");
			}
		}

		$statusType = 'status1';
		if ($status == 0 && $alreadypaid > 0) {
			$statusType = 'status3';
		}
		if ($status == 1) {
			$statusType = 'status6';
		}

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

			$line = new immoreceiptLine($this->db);

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
	 * 	Create an array of owner lines
	 *
	 * 	@return int		>0 if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new ImmoReceiptLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_immoreceipt = '.$this->id));

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
	 * Make tag Receipt as paid completely
	 * @param 	User    $user       Object user making change
	 * @return 	int					<0 if KO, >0 if OK
	 */
	public function setPaid($user)
	{
		$this->db->begin();
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';
		$sql .= " paye = 1";
		$sql .= " WHERE rowid = " . ((int) $this->id);
		$return = $this->db->query($sql);
		if ($return) {
			return 1;
		} else {
			return -1;
		}
	}

	/**
	 *    Remove tag paid on Receipt
	 *
	 *    @param	User	$user       Object user making change
	 *    @return	int					<0 if KO, >0 if OK
	 */
	public function setUnpaid($user)
	{
		$sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element . ' SET';
		$sql .= " paye = 0";
		$sql .= " WHERE rowid = " . ((int) $this->id);
		$return = $this->db->query($sql);
		if ($return) {
			return 1;
		} else {
			return -1;
		}
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
//var_dump($this->id); exit; 

		$sql = 'SELECT SUM(amount) as amount';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $table;
		$sql .= ' WHERE ' . $field . ' = ' . $this->id;
//echo $sql; 
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

	/**
	 * Return if a receipt is late or not
	 *
	 * @return boolean     True if late, False if not late
	 */
	public function hasDelay()
	{
		//Only valid receipts
		if ($this->status != self::STATUS_VALIDATED) {
			return false;
		}
		if (!$this->date_echeance) {
			return false;
		}

		$now = dol_now();

		return $this->date_echeance < $now;
	}

	/**
	 *	Insert receiptsubscription into database and eventually add links to banks, mailman, etc...
	 *
	 *	@param	int	        $date        		Date of effect of receiptsubscription
	 *	@param	double		$amount     		Amount of receiptsubscription 
	 *	@param	int			$accountid			Id bank account. NOT USED.
	 *	@param	string		$operation			Code of payment mode (if Id bank account provided). Example: 'CB', ... NOT USED.
	 *	@param	string		$label				Label operation (if Id bank account provided).
	 *	@param	string		$num_chq			Numero cheque (if Id bank account provided)
	 *	@param	string		$emetteur_nom		Name of cheque writer
	 *	@param	string		$emetteur_banque	Name of bank of cheque
	 *	@param	int     	$datesubend			Date end receiptsubscription
	 *	@return int         					rowid of record added, <0 if KO
	 */
	public function receiptsubscription($id)
	{

		$error = 0;

		$this->db->begin();

		// Create subscription
		$subscription = new ImmoReceipt($this->db);
		$subscription->fk_renter = $this->id;
		// Load object
		if ($id > 0) {
			$rowid = $subscription->fetch($id);
		}
		if ($rowid > 0) {

			if (!$error) {
				$this->db->commit();
				return $rowid;
			} else {
				$this->db->rollback();
				return -2;
			}
		} else {
			$this->error = $subscription->error;
			$this->errors = $subscription->errors;
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *	Do complementary actions after receiptsubscription recording.
	 *
	 *	@param	int			$subscriptionid			Id of updated receiptsubscription
	 *  @param	string		$option					Which action ('bankdirect', 'bankviainvoice', 'invoiceonly', ...)
	 *	@param	int			$accountid				Id bank account
	 *	@param	int			$datesubscription		Date of receiptsubscription
	 *	@param	int			$paymentdate			Date of payment
	 *	@param	string		$operation				Code of type of operation (if Id bank account provided). Example 'CB', ...
	 *	@param	string		$label					Label operation (if Id bank account provided)
	 *	@param	double		$amount     			Amount of receiptsubscription 
	 *	@param	string		$num_chq				Numero cheque (if Id bank account provided)
	 *	@param	string		$emetteur_nom			Name of cheque writer
	 *	@param	string		$emetteur_banque		Name of bank of cheque
	 *  @param	string		$autocreatethirdparty	Auto create new thirdparty if renter not yet linked to a thirdparty and we request an option that generate invoice.
	 *  @param  string      $ext_payment_id         External id of payment (for example Stripe charge id)
	 *  @param  string      $ext_payment_site       Name of external paymentmode (for example 'stripe')
	 *	@return int									<0 if KO, >0 if OK
	 */
	public function receiptSubscriptionComplementaryActions($subscriptionid, $option, $accountid, $datesubscription, $paymentdate, $operation, $label, $amount, $num_chq, $emetteur_nom = '', $emetteur_banque = '', $autocreatethirdparty = 0, $ext_payment_id = '', $ext_payment_site = '')
	{
		global $conf, $langs, $user, $mysoc;

		$error = 0;

		$this->invoice = null; // This will contains invoice if an invoice is created

		dol_syslog("receiptSubscriptionComplementaryActions subscriptionid=" . $subscriptionid . " option=" . $option . " accountid=" . $accountid . " datesubscription=" . $datesubscription . " paymentdate=" .
		$paymentdate . " label=" . $label . " amount=" . $amount . " num_chq=" . $num_chq . " autocreatethirdparty=" . $autocreatethirdparty);

		// Insert into bank account directly (if option choosed for) + link to llx_subscription if option is 'bankdirect'
		if ($option == 'bankdirect' && $accountid) {
			require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

			$acct = new Account($this->db);
			$result = $acct->fetch($accountid);
			
			$dateop = $paymentdate;

			$insertid = $acct->addline($dateop, $operation, $label, $amount, $num_chq, '', $user, $emetteur_nom, $emetteur_banque);
			if ($insertid > 0) {
				$url = dol_buildpath('/ultimateimmo/renter/immorenter_card.php', 1) . '?id=' . $this->id;
				$inserturlid = $acct->add_url_line($insertid, $this->id, $url, $this->getFullname($langs), 'renter');
				if ($inserturlid > 0) {
					// Update table ultimateimmo_immoreceipt
					$sql = "UPDATE ".MAIN_DB_PREFIX."ultimateimmo_immoreceipt SET fk_bank=".((int) $insertid);
					$sql .= " WHERE rowid=".((int) $subscriptionid);

					dol_syslog("subscription::subscription", LOG_DEBUG);
					$resql = $this->db->query($sql);
					if (!$resql) {
						$error++;
						$this->error = $this->db->lasterror();
						$this->errors[] = $this->error;
					}
				} else {
					$error++;
					$this->error = $acct->error;
					$this->errors = $acct->errors;
				}
			} else {
				$error++;
				$this->error = $acct->error;
				$this->errors = $acct->errors;
			}
		}

		// If option choosed, we create invoice
		if (($option == 'bankviainvoice' && $accountid) || $option == 'invoiceonly') {
			require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
			require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/paymentterm.class.php';

			$invoice = new Facture($this->db);
			$customer = new RenterSoc($this->db);
			
			if (!$error) {
				if (!($this->fk_soc > 0)) { // If not yet linked to a company
					if ($autocreatethirdparty) {
						// Create a linked thirdparty to member
						$companyalias = '';
						$fullname = $this->getFullName($langs);

						if ($this->morphy == 'mor') {
							$companyname = $this->company;
							if (!empty($fullname)) {
								$companyalias = $fullname;
							}
						} else {
							$companyname = $fullname;
							if (!empty($this->company)) {
								$companyalias = $this->company;
							}
						}

						$result = $customer->create_from_renter($this->fk_renter, $companyname, $companyalias);
						if ($result < 0) {
							$this->error = $customer->error;
							$this->errors = $customer->errors;
							$error++;
						} else {
							$this->fk_soc = $result;
						}
					} else {
						$langs->load("errors");
						$this->error = $langs->trans("ErrorRenterNotLinkedToAThirpartyLinkOrCreateFirst");
						$this->errors[] = $this->error;
						$error++;
					}
				}
			}
			if (!$error) {
				$result = $customer->fetch($this->fk_soc);
				if ($result <= 0) {
					$this->error = $customer->error;
					$this->errors = $customer->errors;
					$error++;
				}
			}

			if (!$error) {
				// Create draft invoice
				$invoice->type = Facture::TYPE_STANDARD;
				$invoice->cond_reglement_id = $customer->cond_reglement_id;
				if (empty($invoice->cond_reglement_id)) {
					$paymenttermstatic = new PaymentTerm($this->db);
					$invoice->cond_reglement_id = $paymenttermstatic->getDefaultId();
					if (empty($invoice->cond_reglement_id)) {
						$error++;
						$this->error = 'ErrorNoPaymentTermRECEPFound';
						$this->errors[] = $this->error;
					}
				}
				$invoice->socid = $this->fk_soc;
				//$invoice->date = $datesubscription;
				$invoice->date = dol_now();
				
				// Possibility to add external linked objects with hooks
				$invoice->linked_objects['subscription'] = $subscriptionid;
				if (!empty($_POST['other_linked_objects']) && is_array($_POST['other_linked_objects'])) {
					$invoice->linked_objects = array_merge($invoice->linked_objects, $_POST['other_linked_objects']);
				}

				$result = $invoice->create($user);
				if ($result <= 0) {
					$this->error = $invoice->error;
					$this->errors = $invoice->errors;
					$error++;
				} else {
					$this->invoice = $invoice;
				}
			}

			if (!$error) {
				// Add line to draft invoice
				$idprodsubscription = 0;
				if (!empty($conf->global->ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS) && (isModEnabled('product') || isModEnabled('service'))) {
					$idprodsubscription = $conf->global->ULTIMATEIMMO_PRODUCT_ID_FOR_RECEIPTS;
				}

				$vattouse = 0;
				if (isset($conf->global->ULTIMATEIMMO_VAT_FOR_RECEIPTS) && $conf->global->ULTIMATEIMMO_VAT_FOR_RECEIPTS == 'defaultforfoundationcountry') {
					$vattouse = get_default_tva($mysoc, $mysoc, $idprodsubscription);
				}
				//var_dump($vattouse, $idprodsubscription, $mysoc, $customer);exit;
				$result = $invoice->addline($label, 0, 1, $vattouse, 0, 0, $idprodsubscription, 0, $datesubscription, '', 0, 0, '', 'TTC', $amount, 1);
				if ($result <= 0) {
					$this->error = $invoice->error;
					$this->errors = $invoice->errors;
					$error++;
				}
			}

			if (!$error) {
				// Validate invoice
				$result = $invoice->validate($user);
				if ($result <= 0) {
					$this->error = $invoice->error;
					$this->errors = $invoice->errors;
					$error++;
				}
			}

			if (!$error) {
				// TODO Link invoice with subscription ?
			}

			// Add payment onto invoice
			if (!$error && $option == 'bankviainvoice' && $accountid) {
				require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
				require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
				require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

				$amounts = array();
				$amounts[$invoice->id] = price2num($amount);

				/*$sql = 'SELECT d.rowid as recid, d.paye, d.total_amount, pd.amount, d.ref';
				$sql .= ' FROM ' . MAIN_DB_PREFIX . 'ultimateimmo_immopayment as pd,' . MAIN_DB_PREFIX . 'ultimateimmo_immoreceipt as d';
				$sql .= ' WHERE pd.fk_receipt = d.rowid';
				$sql .= ' AND d.entity = ' . $conf->entity;
				$sql .= ' AND pd.rowid = ' . $id;*/

				//$receipt = new ImmoReceipt($this->db);
				
				$paiement = new Paiement($this->db);
				
				$paiement->datepaye = $paymentdate;
				$paiement->amounts = $amounts;
				$paiement->paiementcode = $operation;
				$paiement->paiementid = dol_getIdFromCode($this->db, $operation, 'c_paiement', 'code', 'id', 1);
				$paiement->num_payment = $num_chq;
				$paiement->note_public = $label;
				$paiement->ext_payment_id = $ext_payment_id;
				$paiement->ext_payment_site = $ext_payment_site;

				if (!$error) {
					// Create payment line for invoice
					$paiement_id = $paiement->create($user);
					if (!$paiement_id > 0) {
						$this->error = $paiement->error;
						$this->errors = $paiement->errors;
						$error++;
					}
				}
				//var_dump($paiement);exit;
				if (!$error) {
					// Add transaction into bank account
					$bank_line_id = $paiement->addPaymentToBank($user, 'payment', '(ImmoReceiptPayment)', $accountid, $emetteur_nom, $emetteur_banque);
					if (!($bank_line_id > 0)) {
						$this->error = $paiement->error;
						$this->errors = $paiement->errors;
						$error++;
					}
				}

				/*if (!$error && !empty($bank_line_id)) {
					// Update fk_bank into ultimateimmo_immoreceipt table
					$sql = 'UPDATE '.MAIN_DB_PREFIX.'ultimateimmo_immoreceipt SET fk_bank='.((int) $bank_line_id);
					$sql .= ' WHERE rowid='.((int) $receipt->id);

					$result = $this->db->query($sql);
					if (!$result) {
						$error++;
					}
				}*/

				if (!$error) {
					// Set invoice as paid
					$invoice->setPaid($user);
				}
			}

			if (!$error) {
				// Define output language
				$outputlangs = $langs;
				$newlang = '';
				$lang_id = GETPOST('lang_id');
				if ($conf->global->MAIN_MULTILANGS && empty($newlang) && !empty($lang_id)) {
					$newlang = $lang_id;
				}
				if ($conf->global->MAIN_MULTILANGS && empty($newlang)) {
					$newlang = $customer->default_lang;
				}
				if (!empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}
				// Generate PDF (whatever is option MAIN_DISABLE_PDF_AUTOUPDATE) so we can include it into email
				//if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))

				$invoice->generateDocument($invoice->model_pdf, $outputlangs);
			}
		}

		if ($error) {
			return -1;
		} else {
			return 1;
		}
	}
}

/**
 * Class ImmoreceiptLine
 */
class ImmoreceiptLine extends CommonObjectLine
{
	// To complete with content of an object MyObjectLine
	// We should have a field rowid, fk_myobject and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

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

