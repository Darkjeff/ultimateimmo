<?php
/* Copyright (C) 2017 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2021 Philippe GRAND <philippe.grand@atoo-net.com>
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
 * \file        class/immorent.class.php
 * \ingroup     ultimateimmo
 * \brief       This file is a CRUD class file for ImmoRent (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ImmoRent
 */
class ImmoRent extends CommonObject
{
	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'immorent';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'ultimateimmo_immorent';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $fk_element = 'fk_rent';
	/**
	 * @var int  Does immorent support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does immorent support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for immorent. Must be the part after the 'object_' into object_immorent.png
	 */
	public $picto = 'immorent@ultimateimmo';


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
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid'            => array('type'     => 'integer', 'label' => 'TechnicalID', 'visible' => -1, 'enabled' => 1,
									'position' => 1, 'notnull' => 1, 'index' => 1, 'comment' => "Id",),
		'ref'              => array('type'      => 'varchar(128)', 'label' => 'Ref', 'visible' => 2, 'enabled' => 1,
									'position'  => 10, 'notnull' => 1, 'default' => '(PROV)', 'index' => 1,
									'searchall' => 1, 'comment' => "Reference of object", 'showoncombobox' => 1,),
		'entity'           => array('type'     => 'integer', 'label' => 'Entity', 'visible' => 0, 'enabled' => 1,
									'position' => 20, 'notnull' => 1, 'index' => 1,),
		'fk_property'      => array('type'      => 'integer:ImmoProperty:ultimateimmo/class/immoproperty.class.php',
									'label'     => 'Property', 'visible' => 1, 'enabled' => 1, 'position' => 25,
									'notnull'   => -1, 'index' => 1, 'foreignkey' => 'ultimateimmo_immoproperty.rowid',
									'searchall' => 1, 'help' => "LinkToProperty", 'showoncombobox' => 1,),
		'fk_owner'         => array('type'       => 'integer:ImmoOwner:ultimateimmo/class/immoowner.class.php',
									'label'      => 'Owner', 'visible' => 1, 'enabled' => 1, 'position' => 30,
									'notnull'    => -1, 'index' => 1, 'searchall' => 1,
									'foreignkey' => 'ultimateimmo_immoowner.rowid', 'help' => "LinkToOwner",),
		'fk_renter'        => array('type'      => 'integer:ImmoRenter:ultimateimmo/class/immorenter.class.php',
									'label'     => 'Renter', 'visible' => 1, 'enabled' => 1, 'position' => 40,
									'notnull'   => -1, 'index' => 1, 'foreignkey' => 'ultimateimmo_immorenter.rowid',
									'searchall' => 1, 'help' => "LinkToRenter",),
		'fk_account'       => array('type'    => 'integer:Account:compta/bank/class/account.class.php',
									'label'   => 'BankAccount', 'visible' => 1, 'enabled' => 1, 'position' => 40,
									'notnull' => -1, 'index' => 1, 'foreignkey' => 'bank_account.id', 'searchall' => 1,
									'help'    => "LinkToAccount",),
		'fk_soc'           => array('type'       => 'integer:Societe:societe/class/societe.class.php',
									'label'      => 'ThirdParty', 'visible' => 1, 'enabled' => 1, 'position' => 42,
									'notnull'    => -1, 'index' => 1, 'searchall' => 1, 'help' => "LinkToThirdparty",
									'foreignkey' => 'societe.rowid',),
		'location_type_id' => array('type'          => 'integer', 'label' => 'ImmorentType', 'enabled' => 1,
									'visible'       => 1, 'position' => 44, 'notnull' => -1,
									'arrayofkeyval' => array('1' => 'EmptyHousing', '2' => 'FurnishedApartment')),
		'vat'              => array('type'          => 'integer', 'label' => 'VAT', 'visible' => -1, 'enabled' => 1,
									'position'      => 45, 'notnull' => -1, 'index' => 1,
									'arrayofkeyval' => array('0' => 'No', '1' => 'Yes')),
		'note_public'      => array('type'     => 'html', 'label' => 'NotePublic', 'visible' => -1, 'enabled' => 1,
									'position' => 50, 'notnull' => -1,),
		'note_private'     => array('type'     => 'html', 'label' => 'NotePrivate', 'visible' => -1, 'enabled' => 1,
									'position' => 55, 'notnull' => -1,),
		'rentamount'       => array('type'     => 'price', 'label' => 'RentAmount', 'visible' => 1, 'enabled' => 1,
									'position' => 60, 'notnull' => -1, 'isameasure' => 1,),
		'chargesamount'    => array('type'     => 'price', 'label' => 'ChargesAmount', 'visible' => 1, 'enabled' => 1,
									'position' => 65, 'notnull' => -1, 'isameasure' => 1,),
		'totalamount'      => array('type'     => 'price', 'label' => 'TotalAmount', 'visible' => 1, 'enabled' => 1,
									'position' => 70, 'notnull' => -1, 'isameasure' => 1,),
		'deposit'          => array('type'     => 'price', 'label' => 'Deposit', 'visible' => 1, 'enabled' => 1,
									'position' => 75, 'notnull' => -1,),
		'encours'          => array('type'     => 'price', 'label' => 'Encours', 'visible' => 1, 'enabled' => 1,
									'position' => 80, 'notnull' => -1,),
		'periode'          => array('type'     => 'varchar(128)', 'label' => 'ImmoRentPeriod', 'visible' => -1, 'enabled' => 1,
									'position' => 85, 'notnull' => -1, 'help' => 'ImmoRentPeriodInfo',),
		'preavis'          => array('type'          => 'integer', 'label' => 'ImmoRentNoticePeriod', 'visible' => 1, 'enabled' => 1,
									'position'      => 85, 'notnull' => -1,
									'arrayofkeyval' => array('1' => 'No', '2' => 'Yes')),
		'date_start'       => array('type'     => 'date', 'label' => 'DateStartRent', 'visible' => -1, 'enabled' => 1,
									'position' => 90, 'notnull' => -1,),
		'date_end'         => array('type'     => 'date', 'label' => 'date_end', 'visible' => -1, 'enabled' => 1,
									'position' => 95, 'notnull' => -1,),
		'date_next_rent'   => array('type'     => 'date', 'label' => 'DateNextRent', 'visible' => -1, 'enabled' => 1,
									'position' => 100, 'notnull' => -1,),
		'date_last_regul'  => array('type'     => 'date', 'label' => 'DateLastRegul', 'visible' => -1, 'enabled' => 1,
									'position' => 110, 'notnull' => -1,),
		'date_last_regul_charge'  => array('type'     => 'date', 'label' => 'DateLastRegulCharge', 'visible' => -1, 'enabled' => 1,
									'position' => 115, 'notnull' => -1,),
		'date_creation'    => array('type'     => 'datetime', 'label' => 'DateCreation', 'visible' => -1,
									'enabled'  => 1, 'position' => 500,),
		'tms'              => array('type'    => 'timestamp', 'label' => 'DateModification', 'visible' => -2,
									'enabled' => 1, 'position' => 501, 'notnull' => 1,),
		'fk_user_creat'	   => array('type'	=> 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => 1,
							   'visible' => -2, 'notnull' => 1, 'position' => 510, 'foreignkey' => 'user.rowid'),
		'fk_user_modif'    => array('type'     => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'visible' => -2, 'enabled' => 1,
									'position' => 511, 'notnull' => -1, 'foreignkey' => 'user.rowid',),
		'import_key'       => array('type'     => 'varchar(14)', 'label' => 'ImportId', 'visible' => -2, 'enabled' => 1,
									'position' => 1000, 'notnull' => -1,),
		'model_pdf'        => array('type'      => 'varchar(128)', 'label' => 'ModelPdf', 'enabled' => 1,
									'visible'   => -2, 'position' => 1010, 'notnull' => -1, 'index' => 1,
									'searchall' => 1,),
		'status'           => array('type'          => 'integer', 'label' => 'Status', 'visible' => 1, 'enabled' => 1,
									'position'      => 1000, 'notnull' => 1, 'index' => 1,
									'arrayofkeyval' => array('0' => 'ImmoRentStatusDisabled', '1' => 'ImmoRentStatusActive')),
	);
	public $rowid;
	public $ref;
	public $entity;
	public $fk_property;
	public $fk_owner;
	public $fk_renter;
	public $fk_account;
	public $fk_soc;
	public $location_type_id;
	public $nomlocataire;
	public $vat;
	public $note_public;
	public $note_private;
	public $rentamount;
	public $chargesamount;
	public $totalamount;
	public $deposit;
	public $encours;
	public $periode;
	public $preavis;
	public $date_start;
	public $date_end;
	public $date_next_rent;
	public $date_last_regul;
	public $date_last_regul_charge;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $model_pdf;
	public $status;


	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'immorentdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_immorent';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ImmoRentline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('immorentdet');
	/**
	 * @var ImmoRentLine[]     Array of subtable lines
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

		// Translate some data
		$this->fields['vat']['arrayofkeyval'] = array(1 => $langs->trans('No'), 2 => $langs->trans('Yes'));
		$this->fields['location_type_id']['arrayofkeyval'] = array(1 => $langs->trans('EmptyHousing'), 2 => $langs->trans('FurnishedApartment'));
	}

	/**
	 * Create object into database
	 *
	 * @param User $user User that creates
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		$this->ref='(PROV)';
		$resultCreate = $this->createCommon($user, $notrigger);
		if ($resultCreate<0) {
			return -1;
		}
		$this->fetch($this->id);
		$this->ref=$this->id;
		if (empty($this->preavis)) {
			$this->preavis=1;
		}
		$resultUpd = $this->update($user);
		if ($resultUpd<0) {
			return -1;
		}
		return $resultCreate;
	}

	/**
	 * Clone and object into another one
	 *
	 * @param User $user User that creates
	 * @param int $fromid Id of object to clone
	 * @return    mixed                New object created, <0 if KO
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
	 * Load object in memory from the database
	 *
	 * @param int $id Id object
	 * @param string $ref Ref
	 * @param string $morewhere morewhere
	 * @return int         <0 if KO, 0 if not found, >0 if OK
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

		$sql .= ' soc.rowid as socid, soc.nom as name,';
		$sql .= ' rentr.lastname as nomlocataire,';
		$sql .= ' rentr.firstname as firstname_renter,';
		$sql .= ' prop.label as nomlocal';
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immorenter as rentr ON t.fk_renter = rentr.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'ultimateimmo_immoproperty as prop ON t.fk_property = prop.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'c_ultimateimmo_immorent_type as rent_t ON t.location_type_id = rent_t.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'societe as soc ON t.fk_soc = soc.rowid';
		//$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."bank as b ON t.fk_account = b.rowid";
		//$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."bank_account as ba ON b.fk_account = ba.rowid";

		if (!empty($id)) $sql .= ' WHERE t.rowid = ' . $id;
		else $sql .= ' WHERE t.ref = ' . $this->quote($ref, $this->fields['ref']);
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
					$this->socid = $obj->name;

					$this->location_type_id = $obj->location_type_id;
					$this->location_type_code = $obj->location_type_code;
					$this->location_type = $obj->location_type;

					$this->setVarsFromFetchObj($obj);

					return $this->id;
				} else {
					return 0;
				}
			} else {
				$this->error = "Error " . $this->db->lasterror();
				$this->errors[] = "Error " . $this->db->lasterror();
				return -1;
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			$this->errors[] = "Error " . $this->db->lasterror();
			return -1;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * Function to concat keys of fields
	 *
	 * @return string
	 */
	private function get_field_list()
	{
		// phpcs:enable
		$keys = array_keys($this->fields);
		return implode(',', $keys);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 * Function to load data into current object this
	 *
	 * @param stdClass $obj Contain data of object from database
	 * @return void
	 */
	private function set_vars_by_db(&$obj)
	{
		// phpcs:enable
		foreach ($this->fields as $field => $info) {
			if ($this->isDate($info)) {
				if (empty($obj->{$field}) || $obj->{$field} === '0000-00-00 00:00:00' || $obj->{$field} === '1000-01-01 00:00:00') $this->{$field} = 0;
				else $this->{$field} = strtotime($obj->{$field});
			} elseif ($this->isArray($info)) {
				$this->{$field} = @unserialize($obj->{$field});
				// Hack for data not in UTF8
				if ($this->{$field} === false) @unserialize(utf8_decode($obj->{$field}));
			} elseif ($this->isInt($info)) {
				$this->{$field} = (int) $obj->{$field};
			} elseif ($this->isFloat($info)) {
				$this->{$field} = (float) $obj->{$field};
			} else {
				$this->{$field} = $obj->{$field};
			}
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int $id Id object
	 * @param string $ref Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		//if ($result > 0 && ! empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Update object into database
	 *
	 * @param User $user User that modifies
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user User that deletes
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 * @param int $withpicto Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 * @param string $option On what the link point to ('nolink', ...)
	 * @param int $notooltip 1=Disable tooltip
	 * @param string $morecss Add more css on link
	 * @param int $save_lastsearch_value -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 * @return    string                                String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $db, $conf, $langs;
		global $dolibarr_main_authentication, $dolibarr_main_demo;
		global $menumanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1;   // Force disable tooltips

		$result = '';
		$companylink = '';
		$staticproperty = new ImmoProperty($db);
		$staticproperty->fetch($this->fk_property);

		$label = '<u>' . $langs->trans("ImmoRent") . '</u>';
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;
		$label .= '<br>';
		$label .= '<b>' . $langs->trans('ImmoProperty') . ':</b> ' . $staticproperty->label;
		if (isset($this->status)) {
			$label .= '<br><b>' . $langs->trans("Status") . ":</b> " . $this->getLibStatut(5);
		}

		$url = dol_buildpath('/ultimateimmo/rent/immorent_card.php', 1) . '?id=' . $this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowImmoRent");
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
		if ($withpicto != 2) $result .= ($staticproperty->label) ? $this->ref . ' (' . $staticproperty->label . ')' : $this->ref;
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('immorentdao'));
		$parameters = array('id' => $this->id, 'getnomurl' => $result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Retourne le libelle du status d'un user (actif, inactif)
	 *
	 * @param int $mode 0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 * @return    string                   Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 * @param int $status Id status
	 * @param int $mode 0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 * @return string                    Label of status
	 */
	public static function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
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
	 *  Create a document onto disk according to template module.
	 *
	 * @param string $modele Force model to use ('' to not force)
	 * @param Translate $outputlangs Object langs to use for output
	 * @param int $hidedetails Hide details of lines
	 * @param int $hidedesc Hide description
	 * @param int $hideref Hide ref
	 * @param null|array $moreparams Array to provide more information
	 * @return     int                        0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$langs->load("ultimateimmo@ultimateimmo");

		if (!dol_strlen($modele)) {
			$modele = 'bail_vide';

			if ($this->model_pdf) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->ULTIMATEIMMO_ADDON_PDF)) {
				$modele = $conf->global->ULTIMATEIMMO_ADDON_PDF;
			}
		}

		$modelpath = "ultimateimmo/core/modules/ultimateimmo/pdf/";

		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
	}

	/**
	 *    Charge les informations d'ordre info dans l'objet commande
	 *
	 * @param int $id Id of order
	 * @return    void
	 */
	public function info($id)
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
				if ($obj->fk_user_creat) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_creat);
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

				$this->date_creation = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation = $this->db->jdate($obj->datev);
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
	 * @return    int            0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
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
}
/**
 * Class ImmoRentLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class ImmoRentLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
*/
