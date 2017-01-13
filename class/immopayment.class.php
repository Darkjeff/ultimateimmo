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
 * \file    immobilier/immopayment.class.php
 * \ingroup immobilier
 * \brief   This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *          Put some comments here
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class Immopayment
 *
 * Put here description of your class
 */
class Immopayment extends CommonObject
{
	/**
	 * @var string Error code (or message)
	 * @deprecated
	 * @see Immopayment::errors
	 */
	public $error;
	/**
	 * @var string[] Error codes (or messages)
	 */
	public $errors = array();
	/**
	 * @var string Id to identify managed objects
	 */
	public $element = 'immopayment';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'immo_payment';

	/**
	 * @var ImmopaymentLine[] Lines
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
	public $fk_renter;
	public $amount;
	public $fk_typepayment;
	public $num_payment;
	public $fk_bank;
	public $comment;
	public $date_payment = '';
	public $fk_owner;
	public $fk_receipt;
	public $nomlocal;
	public $nomlocataire;
	public $nomloyer;

	/**
	 */
	

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct( $db)
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
		if (isset($this->fk_contract))		$this->fk_contract = trim($this->fk_contract);
		if (isset($this->fk_property))		$this->fk_property = trim($this->fk_property);
		if (isset($this->fk_renter))		$this->fk_renter = trim($this->fk_renter);
		if (isset($this->amount))			$this->amount = trim($this->amount);
		if (isset($this->fk_bank))			$this->fk_bank=trim($this->fk_bank);
		if (isset($this->fk_typepayment))   $this->fk_typepayment=trim($this->fk_typepayment);
		if (isset($this->num_payment))      $this->num_payment=trim($this->num_payment);
		if (isset($this->comment))			$this->comment = trim($this->comment);
		if (isset($this->fk_owner))			$this->fk_owner = trim($this->fk_owner);
		if (isset($this->fk_receipt))		$this->fk_receipt = trim($this->fk_receipt);

		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element . '(';
		
		$sql.= 'fk_contract,';
		$sql.= 'fk_property,';
		$sql.= 'fk_renter,';
		$sql.= 'amount,';
		$sql.= 'fk_bank,';
		$sql.= 'fk_typepayment,';
		$sql.= 'num_payment,';
		$sql.= 'comment,';
		$sql.= 'date_payment,';
		$sql.= 'fk_owner,';
		$sql.= 'fk_receipt';

		$sql .= ') VALUES (';

		$sql .= ' '.(! isset($this->fk_contract)?'NULL':$this->fk_contract).',';
		$sql .= ' '.(! isset($this->fk_property)?'NULL':$this->fk_property).',';
		$sql .= ' '.(! isset($this->fk_renter)?'NULL':$this->fk_renter).',';
		$sql .= ' '.(! isset($this->amount)?'NULL':"'".$this->amount."'").',';
		$sql .= ' '.(! isset($this->fk_bank)?'NULL':"'".$this->fk_bank."'").',';
		$sql .= ' '.(! isset($this->fk_typepayment)?'NULL':"'".$this->db->escape($this->fk_typepayment)."'").',';
		$sql .= ' '.(! isset($this->num_payment)?'NULL':"'".$this->db->escape($this->num_payment)."'").',';		
		$sql .= ' '.(! isset($this->comment)?'NULL':"'".$this->db->escape($this->comment)."'").',';
		$sql .= ' '.(! isset($this->date_payment) || dol_strlen($this->date_payment)==0?'NULL':"'".$this->db->idate($this->date_payment)."'").',';
		$sql .= ' '.(! isset($this->fk_owner)?'NULL':$this->fk_owner).',';
		$sql .= ' '.(! isset($this->fk_receipt)?'NULL':$this->fk_receipt);

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
		$sql .= " t.fk_renter,";
		$sql .= " t.amount,";
		$sql .= " t.fk_bank,";
		$sql .= " t.fk_typepayment,";
		$sql .= " p.libelle as typepayment_label,";
		$sql .= " t.num_payment,";
		$sql .= " t.comment,";
		$sql .= " t.date_payment,";
		$sql .= " t.fk_owner,";
		$sql .= " t.fk_receipt,";
		$sql .= " lc.nom as nomlocataire,";
		$sql .= " ll.name as nomlocal,";
		$sql .= " lo.name as nomloyer ";

		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as lc";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_property as ll";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_receipt as lo";
		$sql .= " , " . MAIN_DB_PREFIX . "c_paiement as p";		
		
		$sql .= " WHERE t.fk_renter = lc.rowid";
		$sql .= " AND t.fk_property = ll.rowid";
		$sql .= " AND t.fk_receipt = lo.rowid";
		$sql .= " AND t.fk_typepayment = p.id";
		
		if (!empty($ref)) {
			$sql .= ' AND t.ref = ' . '\'' . $ref . '\'';
		} else {
			$sql .= ' AND t.rowid = ' . $id;
		}
		
		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;
				
				$this->fk_contract			= $obj->fk_contract;
				$this->fk_property			= $obj->fk_property;
				$this->fk_renter			= $obj->fk_renter;
				$this->amount				= $obj->amount;
				$this->fk_bank				= $obj->fk_bank;
				$this->fk_typepayment		= $obj->fk_typepayment;
				$this->typepayment_label	= $obj->typepayment_label;
				$this->num_payment			= $obj->num_payment;
				$this->comment				= $obj->comment;
				$this->date_payment			= $this->db->jdate($obj->date_payment);
				$this->fk_owner				= $obj->fk_owner;
				$this->fk_receipt			= $obj->fk_receipt;
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
	public function fetchAll($sortorder='', $sortfield='', $limit=0, $offset=0, array $filter = array(), $filtermode='AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.fk_contract,";
		$sql .= " t.fk_property,";
		$sql .= " t.fk_renter,";
		$sql .= " t.amount,";
		$sql .= " t.comment,";
		$sql .= " t.date_payment,";
		$sql .= " t.fk_owner,";
		$sql .= " t.fk_receipt";
		$sql .= " , lc.nom as nomlocataire , ll.name as nomlocal , lo.name as nomloyer ";

		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "immo_renter as lc ON t.fk_renter = lc.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "immo_property as ll ON t.fk_property = ll.rowid ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "immo_receipt as lo ON t.fk_receipt = lo.rowid";
		
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				$sqlwhere [] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' WHERE ' . implode(' '.$filtermode.' ', $sqlwhere);
		}
		
		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield,$sortorder);
		}
		if (!empty($limit)) {
		 $sql .=  ' ' . $this->db->plimit($limit + 1, $offset);
		}
		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new ImmopaymentLine();

				$line->id = $obj->rowid;
				
				$line->fk_contract = $obj->fk_contract;
				$line->fk_property = $obj->fk_property;
				$line->fk_renter = $obj->fk_renter;
				$line->amount = $obj->amount;
				$line->comment = $obj->comment;
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
		if (isset($this->fk_renter)) {
			 $this->fk_renter = trim($this->fk_renter);
		}
		if (isset($this->amount)) {
			 $this->amount = trim($this->amount);
		}
		if (isset($this->comment)) {
			 $this->comment = trim($this->comment);
		}
		if (isset($this->fk_owner)) {
			 $this->fk_owner = trim($this->fk_owner);
		}
		if (isset($this->fk_receipt)) {
			 $this->fk_receipt = trim($this->fk_receipt);
		}

		// Check parameters
		// Put here code to add a control on parameters values

		// Update request
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';
		
		$sql .= ' fk_contract = '.(isset($this->fk_contract)?$this->fk_contract:"null").',';
		$sql .= ' fk_property = '.(isset($this->fk_property)?$this->fk_property:"null").',';
		$sql .= ' fk_renter = '.(isset($this->fk_renter)?$this->fk_renter:"null").',';
		$sql .= ' amount = '.(isset($this->amount)?$this->amount:"null").',';
		$sql .= ' comment = '.(isset($this->comment)?"'".$this->db->escape($this->comment)."'":"null").',';
		$sql .= ' date_payment = '.(! isset($this->date_payment) || dol_strlen($this->date_payment) != 0 ? "'".$this->db->idate($this->date_payment)."'" : 'null').',';
		$sql .= ' fk_owner = '.(isset($this->fk_owner)?$this->fk_owner:"null").',';
		$sql .= ' fk_receipt = '.(isset($this->fk_receipt)?$this->fk_receipt:"null");

        
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
		$object = new Immopayment($this->db);

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
		$this->fk_renter = '';
		$this->amount = '';
		$this->comment = '';
		$this->date_payment = '';
		$this->fk_owner = '';
		$this->fk_receipt = '';

		
	}

	function fetch_by_loyer($id) {
		$sql = "SELECT ip.rowid as reference, ip.fk_contract, ip.fk_property,";
		$sql .= "ip.fk_renter, ip.amount, ip.comment, ip.date_payment,";
		$sql .= "ip.fk_owner, ip.fk_receipt";
		$sql .= " , lc.nom as nomlocataire , ll.name as nomlocal , lo.nom as nomloyer ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_payment as ip ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_property as ll ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_receipt as lo ";
		$sql .= "WHERE ip.fk_renter = lc.rowid AND ip.fk_property = ll.rowid AND ip.fk_receipt = lo.rowid AND lo.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch_by_loyer sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->fk_contract = $obj->fk_contract;
				$this->fk_property = $obj->fk_property;
				$this->nomlocal = $obj->nomlocal;
				$this->fk_renter = $obj->fk_renter;
				$this->nomlocataire = $obj->nomlocataire;
				$this->amount = $obj->amount;
				$this->comment = $obj->comment;
				$this->date_paiement = $this->db->jdate ( $obj->date_payment );
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
	 * Renvoie nom clicable (avec eventuellement le picto)
	 *
	 * @param int $withpicto picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 * @param int $maxlen libelle
	 * @return string avec URL
	 */
	function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;
	
		$result = '';
	
		if (empty ( $this->id ))
			$this->id = $this->id;
	
			$lien = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/receipt/payment/card.php?action=update&id=' . $this->id . '">';
			$lienfin = '</a>';
	
			if ($withpicto)
				$result .= ($lien . img_object ( $langs->trans ( "ShowPayment" ) . ': ' . $this->id, 'bill' ) . $lienfin . ' ');
				if ($withpicto && $withpicto != 2)
					$result .= ' ';
					if ($withpicto != 2)
						$result .= $lien . ($maxlen ? dol_trunc ( $this->ref, $maxlen ) : $this->ref) . $lienfin;
		return $result;
	}
}

/**
 * Class ImmopaymentLine
 */
class ImmopaymentLine
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
	public $fk_renter;
	public $amount;
	public $comment;
	public $date_payment = '';
	public $fk_owner;
	public $fk_receipt;

	/**
	 * @var mixed Sample line property 2
	 */
	
}
