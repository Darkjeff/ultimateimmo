<?php
/* Copyright (C) 2007-2012  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2014       Juanjo Menent       <jmenent@2byte.es>
 * Copyright (C) 2015       Florian Henry       <florian.henry@open-concept.pro>
 * Copyright (C) 2015       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
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
 * \file    immobilier/calss/immoreceipt.class.php
 * \ingroup immobilier
 * \brief   This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *          Put some comments here
 */

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

/**
 * Class Immoreceipt
 *
 * Put here description of your class
 */
class Immoreceipt extends CommonObject
{
	/**
	 * @var string Error code (or message)
	 * @deprecated
	 * @see Immoreceipt::errors
	 */
	public $error;
	/**
	 * @var string[] Error codes (or messages)
	 */
	public $errors = array();
	/**
	 * @var string Id to identify managed objects
	 */
	public $element = 'immoreceipt';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'immo_receipt';

	/**
	 * @var ImmoreceiptLine[] Lines
	 */
	public $lines = array();

	/**
	 * @var int ID
	 */
	public $id;
	/**
	 */
	
	public $fk_contract;
	public $fk_property;
	public $name;
	public $fk_renter;
	public $amount_total;
	public $rent;
	public $balance;
	public $paiepartiel;
	public $charges;
	public $vat;
	public $echeance = '';
	public $commentaire;
	public $statut;
	public $date_rent = '';
	public $date_start = '';
	public $date_end = '';
	public $fk_owner;
	public $owner_name;
	public $paye;
	
	public $renter_id;
	public $nomlocataire;
	public $prenomlocataire;
	public $emaillocataire;
	public $nomlocal;
	public $property_id;

	/**
	 */
	

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 *
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$error = 0;

		// Clean parameters
		
		if (isset($this->fk_contract)) {
			 $this->fk_contract = trim($this->fk_contract);
		}
		if (isset($this->fk_property)) {
			 $this->fk_property = trim($this->fk_property);
		}
		if (isset($this->name)) {
			 $this->name = trim($this->name);
		}
		if (isset($this->fk_renter)) {
			 $this->fk_renter = trim($this->fk_renter);
		}
		if (isset($this->amount_total)) {
			 $this->amount_total = trim($this->amount_total);
		}
		if (isset($this->rent)) {
			 $this->rent = trim($this->rent);
		}
		if (isset($this->balance)) {
			 $this->balance = trim($this->balance);
		}
		if (isset($this->paiepartiel)) {
			 $this->paiepartiel = trim($this->paiepartiel);
		}
		if (isset($this->charges)) {
			 $this->charges = trim($this->charges);
		}
		if (isset($this->vat)) {
			 $this->vat = trim($this->vat);
		}
		if (isset($this->commentaire)) {
			 $this->commentaire = trim($this->commentaire);
		}
		if (isset($this->statut)) {
			 $this->statut = trim($this->statut);
		}
		if (isset($this->fk_owner)) {
			 $this->fk_owner = trim($this->fk_owner);
		}
		if (isset($this->paye)) {
			 $this->paye = trim($this->paye);
		}

		

		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element . '(';
		
		$sql.= 'fk_contract,';
		$sql.= 'fk_property,';
		$sql.= 'name,';
		$sql.= 'fk_renter,';
		$sql.= 'amount_total,';
		$sql.= 'rent,';
		$sql.= 'balance,';
		$sql.= 'paiepartiel,';
		$sql.= 'charges,';
		$sql.= 'vat,';
		$sql.= 'echeance,';
		$sql.= 'commentaire,';
		$sql.= 'statut,';
		$sql.= 'date_rent,';
		$sql.= 'date_start,';
		$sql.= 'date_end,';
		$sql.= 'fk_owner,';
		$sql.= 'paye';

		
		$sql .= ') VALUES (';
		
		$sql .= ' '.(! isset($this->fk_contract)?'NULL':$this->fk_contract).',';
		$sql .= ' '.(! isset($this->fk_property)?'NULL':$this->fk_property).',';
		$sql .= ' '.(! isset($this->name)?'NULL':"'".$this->db->escape($this->name)."'").',';
		$sql .= ' '.(! isset($this->fk_renter)?'NULL':$this->fk_renter).',';
		$sql .= ' '.(! isset($this->amount_total)?'NULL':"'".$this->amount_total."'").',';
		$sql .= ' '.(! isset($this->rent)?'NULL':"'".$this->rent."'").',';
		$sql .= ' '.(empty($this->balance)?'0':"'".$this->balance."'").',';
		$sql .= ' '.(empty($this->paiepartiel)?'0':"'".$this->paiepartiel."'").',';
		$sql .= ' '.(! isset($this->charges)?'NULL':"'".$this->charges."'").',';
		$sql .= ' '.(empty($this->vat)?'0':"'".$this->vat."'").',';
		$sql .= ' '.(! isset($this->echeance) || dol_strlen($this->echeance)==0?'NULL':"'".$this->db->idate($this->echeance)."'").',';
		$sql .= ' '.(! isset($this->commentaire)?'NULL':"'".$this->db->escape($this->commentaire)."'").',';
		$sql .= ' '.(! isset($this->statut)?'NULL':"'".$this->db->escape($this->statut)."'").',';
		$sql .= ' '.(! isset($this->date_rent) || dol_strlen($this->date_rent)==0?'NULL':"'".$this->db->idate($this->date_rent)."'").',';
		$sql .= ' '.(! isset($this->date_start) || dol_strlen($this->date_start)==0?'NULL':"'".$this->db->idate($this->date_start)."'").',';
		$sql .= ' '.(! isset($this->date_end) || dol_strlen($this->date_end)==0?'NULL':"'".$this->db->idate($this->date_end)."'").',';
		$sql .= ' '.(! isset($this->fk_owner)?'NULL':$this->fk_owner).',';
		$sql .= ' '.(! isset($this->paye)?'NULL':$this->paye);

		
		$sql .= ')';

		$this->db->begin();

		$resql = $this->db->query($sql);
		if (!$resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}

		if (!$error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);

			if (!$notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action to call a trigger.

				//// Call triggers
				//$result=$this->call_trigger('MYOBJECT_CREATE',$user);
				//if ($result < 0) $error++;
				//// End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			$this->db->rollback();

			return - 1 * $error;
		} else {
			$this->db->commit();

			return $this->id;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id  Id object
	 * @param string $ref Ref
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.fk_contract,";
		$sql .= " t.fk_property,";
		$sql .= " t.name,";
		$sql .= " t.fk_renter,";
		$sql .= " t.amount_total,";
		$sql .= " t.rent,";
		$sql .= " t.balance,";
		$sql .= " t.paiepartiel,";
		$sql .= " t.charges,";
		$sql .= " t.vat,";
		$sql .= " t.echeance,";
		$sql .= " t.commentaire,";
		$sql .= " t.statut,";
		$sql .= " t.date_rent,";
		$sql .= " t.date_start,";
		$sql .= " t.date_end,";
		$sql .= " t.fk_owner,";
		$sql .= " t.paye,";
		$sql .= " t.model_pdf,";
		$sql .= " lc.rowid as renter_id,";
		$sql .= " lc.nom as nomlocataire,";
		$sql .= " lc.mail as emaillocataire,";
		$sql .= " ll.name as nomlocal,";
		$sql .= " ll.rowid as property_id,";
		$sql .= " ic.tva as addtva";

		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immo_renter as lc ON t.fk_renter = lc.rowid';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immo_property as ll ON t.fk_property = ll.rowid';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immo_contrat as ic ON t.fk_contract = ic.rowid';
		
		if (null !== $ref) {
			$sql .= ' WHERE t.ref = ' . '\'' . $ref . '\'';
		} else {
			$sql .= ' WHERE t.rowid = ' . $id;
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				$this->id				= $obj->rowid;

				$this->fk_contract		= $obj->fk_contract;
				$this->fk_property		= $obj->fk_property;
				$this->name				= $obj->name;
				$this->fk_renter		= $obj->fk_renter;
				$this->amount_total		= $obj->amount_total;
				$this->rent				= $obj->rent;
				$this->balance			= $obj->balance;
				$this->paiepartiel		= $obj->paiepartiel;
				$this->charges			= $obj->charges;
				$this->vat				= $obj->vat;
				$this->echeance			= $this->db->jdate($obj->echeance);
				$this->commentaire		= $obj->commentaire;
				$this->statut			= $obj->statut;
				$this->date_rent		= $this->db->jdate($obj->date_rent);
				$this->date_start		= $this->db->jdate($obj->date_start);
				$this->date_end			= $this->db->jdate($obj->date_end);
				$this->fk_owner			= $obj->fk_owner;
				$this->paye				= $obj->paye;
				$this->renter_id		= $obj->renter_id;
				$this->nomlocataire 	= $obj->nomlocataire;
				$this->emaillocataire	= $obj->emaillocataire;
				$this->nomlocal			= $obj->nomlocal;
				$this->property_id		= $obj->property_id;
				$this->modelpdf			= $obj->model_pdf;
				$this->addtva			= $obj->addtva;
			}
			$this->db->free($resql);

			if ($numrows) {
				return 1;
			} else {
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
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
	public function fetchAll()
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.fk_contract,";
		$sql .= " t.fk_property,";
		$sql .= " t.name,";
		$sql .= " t.fk_renter,";
		$sql .= " t.amount_total,";
		$sql .= " t.rent,";
		$sql .= " t.balance,";
		$sql .= " t.paiepartiel,";
		$sql .= " t.charges,";
		$sql .= " t.vat,";
		$sql .= " t.echeance,";
		$sql .= " t.commentaire,";
		$sql .= " t.statut,";
		$sql .= " t.date_rent,";
		$sql .= " t.date_start,";
		$sql .= " t.date_end,";
		$sql .= " t.fk_owner,";
		$sql .= " t.paye,";
		$sql .= " lc.rowid as renter_id,";
		$sql .= " lc.nom as nomlocataire,";
		$sql .= " lc.prenom as prenomlocataire,";
		$sql .= " ll.name as nomlocal,";
		$sql .= " ll.rowid as property_id,";
		$sql .= " soc.rowid as soc_id,";
		$sql .= " soc.nom as owner_name";

		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immo_renter as lc ON t.fk_renter = lc.rowid';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immo_property as ll ON t.fk_property = ll.rowid';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'societe as soc ON soc.rowid = t.fk_owner';

		
		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$i = 0;
			$obj = '';
			$num = $this->db->num_rows($resql);
			$data = array();
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object($resql);
					
					
					$data[$i] =	array(
									'fk_contract' => $obj->fk_contract,
									'fk_property' => $obj->fk_property,
									'name' => $obj->name,
									'fk_renter' => $obj->fk_renter,
									'amount_total' => $obj->amount_total,
									'rent' => $obj->rent,
									'balance' => $obj->balance,
									'paiepartiel' => $obj->paiepartiel,
									'charges' => $obj->charges,
									'vat' => $obj->vat,
									'echeance' => $this->db->jdate($obj->echeance),
									'commentaire' => $obj->commentaire,
									'statut' => $obj->statut,
									'date_rent' => $this->db->jdate($obj->date_rent),
									'date_start' => $this->db->jdate($obj->date_start),
									'date_end' => $this->db->jdate($obj->date_end),
									'fk_owner' => $obj->fk_owner,
									'owner_name' => $obj->owner_name,
									'paye' => $obj->paye,
									'renter_id' => $obj->renter_id,
									'nomlocataire' => $obj->nomlocataire,
									'prenomlocataire' => $obj->prenomlocataire,
									'nomlocal' => $obj->nomlocal,
									'property_id' => $obj->property_id,
									);
					$i ++;
				}
			}
			
			return $data;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		// Clean parameters
		if (isset($this->fk_contract)) {
			 $this->fk_contract = trim($this->fk_contract);
		}
		if (isset($this->fk_property)) {
			 $this->fk_property = trim($this->fk_property);
		}
		if (isset($this->name)) {
			 $this->name = trim($this->name);
		}
		if (isset($this->fk_renter)) {
			 $this->fk_renter = trim($this->fk_renter);
		}
		if (isset($this->amount_total)) {
			 $this->amount_total = trim($this->amount_total);
		}
		if (isset($this->rent)) {
			 $this->rent = trim($this->rent);
		}
		if (isset($this->balance)) {
			 $this->balance = trim($this->balance);
		}
		if (isset($this->paiepartiel)) {
			 $this->paiepartiel = trim($this->paiepartiel);
		}
		if (isset($this->charges)) {
			 $this->charges = trim($this->charges);
		}
		if (isset($this->vat)) {
			 $this->vat = trim($this->vat);
		}
		if (isset($this->commentaire)) {
			 $this->commentaire = trim($this->commentaire);
		}
		if (isset($this->statut)) {
			 $this->statut = trim($this->statut);
		}
		if (isset($this->fk_owner)) {
			 $this->fk_owner = trim($this->fk_owner);
		}
		if (isset($this->paye)) {
			 $this->paye = trim($this->paye);
		}

		// Check parameters
		// Put here code to add a control on parameters values

		// Update request
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';

		$sql .= ' fk_contract = '.(isset($this->fk_contract)?$this->fk_contract:"null").',';
		$sql .= ' fk_property = '.(isset($this->fk_property)?$this->fk_property:"null").',';
		$sql .= ' name = '.(isset($this->name)?"'".$this->db->escape($this->name)."'":"null").',';
		$sql .= ' fk_renter = '.(isset($this->fk_renter)?$this->fk_renter:"null").',';
		$sql .= ' amount_total = '.(isset($this->amount_total)?$this->amount_total:"null").',';
		$sql .= ' rent = '.(isset($this->rent)?$this->rent:"null").',';
		$sql .= ' balance = '.(isset($this->balance)?$this->balance:"null").',';
		$sql .= ' paiepartiel = '.(isset($this->paiepartiel)?$this->paiepartiel:"null").',';
		$sql .= ' charges = '.(isset($this->charges)?$this->charges:"null").',';
		$sql .= ' vat = '.(isset($this->vat)?$this->vat:"null").',';
		$sql .= ' echeance = '.(! isset($this->echeance) || dol_strlen($this->echeance) != 0 ? "'".$this->db->idate($this->echeance)."'" : 'null').',';
		$sql .= ' commentaire = '.(isset($this->commentaire)?"'".$this->db->escape($this->commentaire)."'":"null").',';
		$sql .= ' statut = '.(isset($this->statut)?"'".$this->db->escape($this->statut)."'":"null").',';
		$sql .= ' date_rent = '.(! isset($this->date_rent) || dol_strlen($this->date_rent) != 0 ? "'".$this->db->idate($this->date_rent)."'" : 'null').',';
		$sql .= ' date_start = '.(! isset($this->date_start) || dol_strlen($this->date_start) != 0 ? "'".$this->db->idate($this->date_start)."'" : 'null').',';
		$sql .= ' date_end = '.(! isset($this->date_end) || dol_strlen($this->date_end) != 0 ? "'".$this->db->idate($this->date_end)."'" : 'null').',';
		$sql .= ' fk_owner = '.(isset($this->fk_owner)?$this->fk_owner:"null").',';
		$sql .= ' paye = '.(isset($this->paye)?$this->paye:"null");

        
		$sql .= ' WHERE rowid=' . $this->id;

		$this->db->begin();

		$resql = $this->db->query($sql);
		if (!$resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}

		if (!$error && !$notrigger) {
			// Uncomment this and change MYOBJECT to your own tag if you
			// want this action calls a trigger.

			//// Call triggers
			//$result=$this->call_trigger('MYOBJECT_MODIFY',$user);
			//if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
			//// End call triggers
		}

		// Commit or rollback
		if ($error) {
			$this->db->rollback();

			return - 1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user      User that deletes
	 * @param bool $notrigger false=launch triggers after, true=disable triggers
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$error = 0;

		$this->db->begin();

		if (!$error) {
			if (!$notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.

				//// Call triggers
				//$result=$this->call_trigger('MYOBJECT_DELETE',$user);
				//if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
				//// End call triggers
			}
		}

		if (!$error) {
			$sql = 'DELETE FROM ' . MAIN_DB_PREFIX . $this->table_element;
			$sql .= ' WHERE rowid=' . $this->id;

			$resql = $this->db->query($sql);
			if (!$resql) {
				$error ++;
				$this->errors[] = 'Error ' . $this->db->lasterror();
				dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
			}
		}

		// Commit or rollback
		if ($error) {
			$this->db->rollback();

			return - 1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	/**
	 * Load an object from its id and create a new one in database
	 *
	 * @param int $fromid Id of object to clone
	 *
	 * @return int New id of clone
	 */
	public function createFromClone($fromid)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		global $user;
		$error = 0;
		$object = new Immoreceipt($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		// Reset object
		$object->id = 0;

		// Clear fields
		// ...

		// Create clone
		$result = $object->create($user);

		// Other options
		if ($result < 0) {
			$error ++;
			$this->errors = $object->errors;
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}

		// End
		if (!$error) {
			$this->db->commit();

			return $object->id;
		} else {
			$this->db->rollback();

			return - 1;
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
		$this->id = 0;
		
		$this->fk_contract = '';
		$this->fk_property = '';
		$this->name = '';
		$this->fk_renter = '';
		$this->amount_total = '';
		$this->rent = '';
		$this->balance = '';
		$this->paiepartiel = '';
		$this->charges = '';
		$this->vat = '';
		$this->echeance = '';
		$this->commentaire = '';
		$this->statut = '';
		$this->date_rent = '';
		$this->date_start = '';
		$this->date_end = '';
		$this->fk_owner = '';
		$this->paye = '';

		
	}
	
	/**
	 * Renvoie nom clicable (avec eventuellement le picto)
	 *
	 * @param int $withpicto picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 * @param int $maxlen libelle
	 * @return string avec URL
	 */
	function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;

		$result = '';

		$lien = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/receipt/card.php?id=' . $this->id . '">';
		$lienfin = '</a>';

		if ($withpicto)
			$result .= ($lien . img_object ( $langs->trans ( "ShowRent" ) . ': ' . $this->id, 'bill' ) . $lienfin . ' ');
			if ($withpicto && $withpicto != 2)
				$result .= ' ';
				if ($withpicto != 2)
					$result .= $lien . ($maxlen ? dol_trunc ( $this->id, $maxlen ) : $this->id) . $lienfin;

		return $result;
	}
	
	/**
	 * 
	 * @param unknown $id
	 * @param array $filter
	 */
	public function fetchByLocalId($id,$filter=array()) {
	
		$sql = "SELECT il.rowid as reference, il.fk_contract , il.fk_property, il.name as nomrenter, il.fk_renter, il.amount_total,";
		$sql .= " il.rent, il.charges,   il.echeance, il.commentaire, il.statut, il.paye ,";
		$sql .= " il.date_start , il.date_end, il.fk_owner, il.paiepartiel ";
		$sql .= " , lc.nom as nomlocataire , ll.name as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . $this->table_element." as il ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_renter as lc ON il.fk_renter = lc.rowid";
		$sql .= " INNER JOIN  " . MAIN_DB_PREFIX . "immo_property as ll ON il.fk_property = ll.rowid ";
		$sql .= " WHERE il.fk_property = " . $id;
	
		if (count($filter>0)) {
			foreach($filter as $key=>$value) {
				if ($key=='insidedaterenter') {
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
				
			while ($obj = $this->db->fetch_object($resql)) {
	
	
				$line = new immoreceiptLine();
	
				$line->id = $obj->reference;
				$line->ref = $obj->reference;
				$line->fk_contract = $obj->fk_contract;
				$line->fk_property = $obj->fk_property;
				$line->nomlocal = $obj->nomlocal;
				$line->nom = $obj->nomrenter;
				$line->fk_renter = $obj->fk_renter;
				$line->nomlocataire = $obj->nomlocataire;
				$line->amount_total = $obj->amount_total;
				$line->rent = $obj->rent;
				$line->charges = $obj->charges;
				$line->echeance = $this->db->jdate ( $obj->echeance );
				$line->commentaire = $obj->commentaire;
				$line->statut = $obj->statut;
				$line->date_start = $this->db->jdate ( $obj->date_start );
				$line->date_end = $this->db->jdate ( $obj->date_end );
				$line->encours = $obj->encours;
				$line->regul = $obj->regul;
				$line->fk_owner = $obj->fk_owner;
				$line->paye = $obj->paye;
				$line->paiepartiel = $obj->paiepartiel;
	
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
	 * 
	 * @param unknown $user
	 * @return number
	 */
	public function set_paid($user) {
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
	 * 
	 * @param number $mode
	 * @return string
	 */
	public function getLibStatut($mode = 0) {
		return $this->LibStatut ( $this->paye, $mode );
	}
	
	/**
	 * Renvoi le libelle d'un statut donne
	 *
	 * @param int $statut statut
	 * @param int $mode long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 * @return string Label
	 */
	public function LibStatut($statut, $mode = 0) {
		global $langs;
		$langs->load ( 'customers' );
		$langs->load ( 'bills' );
	
		if ($mode == 0) {
			if ($statut == 0)
				return $langs->trans ( "Unpaid" );
				if ($statut == 1)
					return $langs->trans ( "Paid" );
		}
		if ($mode == 1) {
			if ($statut == 0)
				return $langs->trans ( "Unpaid" );
				if ($statut == 1)
					return $langs->trans ( "Paid" );
		}
		if ($mode == 2) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "Unpaid" ), 'statut1' ) . ' ' . $langs->trans ( "Unpaid" );
				if ($statut == 1)
					return img_picto ( $langs->trans ( "Paid" ), 'statut6' ) . ' ' . $langs->trans ( "Paid" );
		}
		if ($mode == 3) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "Unpaid" ), 'statut1' );
				if ($statut == 1)
					return img_picto ( $langs->trans ( "Paid" ), 'statut6' );
		}
		if ($mode == 4) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "Unpaid" ), 'statut1' ) . ' ' . $langs->trans ( "Unpaid" );
				if ($statut == 1)
					return img_picto ( $langs->trans ( "Paid" ), 'statut6' ) . ' ' . $langs->trans ( "Paid" );
		}
		if ($mode == 5) {
			if ($statut == 0)
				return $langs->trans ( "Unpaid" ) . ' ' . img_picto ( $langs->trans ( "Unpaid" ), 'statut1' );
				if ($statut == 1)
					return $langs->trans ( "Paid" ) . ' ' . img_picto ( $langs->trans ( "Paid" ), 'statut6' );
		}
	
		return "Error, mode/status not found";
	}

	/**
	 * Give information on the object
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function info($id) {
		global $langs;

		$sql = "SELECT";
		$sql .= " r.rowid, r.datec, r.tms, r.fk_user_author, r.fk_user_modif";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as r";
		$sql .= " WHERE r.rowid = " . $id;

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->user_creation = $obj->fk_user_author;
				$this->date_creation = $this->db->jdate($obj->datec);
				$this->user_modification = $obj->fk_user_modif;
				if(! empty($this->user_modification)) $this->date_modification = $this->db->jdate($obj->tms);
			}
			$this->db->free($resql);

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
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
	
	public $fk_contract;
	public $fk_property;
	public $name;
	public $fk_renter;
	public $amount_total;
	public $rent;
	public $balance;
	public $paiepartiel;
	public $charges;
	public $vat;
	public $echeance = '';
	public $commentaire;
	public $statut;
	public $date_rent = '';
	public $date_start = '';
	public $date_end = '';
	public $fk_owner;
	public $paye;
	public $renter_id;
	public $nomlocataire;
	public $nomlocal;
	public $property_id;

	/**
	 * @var mixed Sample line property 2
	 */
	
}
