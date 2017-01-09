<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015 Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/class/immocost.class.php
 * \ingroup immobilier
 * \brief   Manage cost object
 */

/**
 * \class charge
 * \brief Classe permettant la gestion des paiements des loyers
 */
require_once 'commonobjectimmobilier.class.php';

/**
 * Class Immocost
 *
 */

class Immocost extends CommonObjectImmobilier
{
	/**
	 * @var string Error code (or message)
	 * @deprecated
	 * @see Immoproperty::errors
	 */
	public $error;
	/**
	 * @var string[] Error codes (or messages)
	 */
	public $errors = array();
	/**
	 * @var string Id to identify managed objects
	 */
	public $element = 'immocost';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'immo_cost';

	/**
	 * @var ImmopropertyLine[] Lines
	 */
	public $lines = array();

	/**
	 * @var int ID
	 */
	public $id;
	
	public $entity;
	public $fk_property;
	public $type;
	public $label;
	public $amount_ht;
	public $amount_vat;
	public $amount;
	public $date;
	public $date_start;
	public $date_end;
	public $note_public;
	public $fk_owner;
	public $fk_soc;
	public $dispatch;
	public $socid;
	public $socname;
	public $property_id;
	public $nomlocal;
	
	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	function __construct($db) {
		$this->db = $db;
		return 1;
	}
	
	
	function fetch($id) {
		$sql = "SELECT ic.rowid as reference, ic.fk_property,so.rowid as socid, so.nom as socname,";
		$sql .= "ic.type, ic.label, ";
		$sql .= "ic.amount_ht, ic.amount_vat , ic.amount , ic.date ,";
		$sql .= "ic.date_start ,ic.date_end , ic.note_public , ic.dispatch, ";
		$sql .= "ic.fk_owner,";
		$sql .= " ll.name as nomlocal,";
		$sql .= " ll.rowid as property_id";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_cost as ic ";
		$sql .= ' LEFT JOIN llx_immo_property as ll ON ic.fk_property = ll.rowid';
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON ic.fk_soc = so.rowid";
		$sql .= " WHERE ic.rowid = " . $id;
		//echo $sql;
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->fk_property = $obj->fk_property;
				$this->type = $obj->type;
				$this->label = $obj->label;
				$this->amount_ht = $obj->amount_ht;
				$this->amount_vat = $obj->amount_vat;
				$this->amount = $obj->amount;
				$this->date = $this->db->jdate ( $obj->date );
				$this->date_start = $this->db->jdate ( $obj->date_start );
				$this->date_end = $this->db->jdate ( $obj->date_end );
				$this->note_public = $obj->note_public;
				$this->dispatch = $obj->dispatch;
				$this->fk_owner = $obj->fk_owner;
				$this->socid = $obj->socid;
				$this->socname = $obj->socname;
				$this->nomlocal = $obj->nomlocal;
				$this->property_id = $obj->property_id;
				return 1;
			} else {
				return 0;
			}
			$this->db->free ( $resql );
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	public function fetchAll($sortorder='', $sortfield='', $limit=0, $offset=0, array $filter = array(), $filtermode='AND')
	{
		global $langs;
		$langs->load('immobilier@immobilier');
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.fk_property,";
		$sql .= " t.type,";
		$sql .= " t.label,";
		$sql .= " t.amount_ht,";
		$sql .= " t.amount_vat,";
		$sql .= " t.amount,";
		$sql .= " t.date,";
		$sql .= " t.date_start,";
		$sql .= " t.date_end,";
		$sql .= " t.note_public,";
		$sql .= " t.fk_owner,";
		$sql .= " t.dispatch,";
		$sql .= " t.fk_soc";
	
		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON t.fk_soc = so.rowid";

		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key=='t.fk_property' ||  $key=='t.label' || $key=='t.date') {
					$sqlwhere [] = $key . ' = ' . $this->db->escape($value);					
				} else {
					$sqlwhere [] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
				
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
				$line = new ImmocostLine();

				$line->id = $obj->rowid;
				
				$line->fk_property = $obj->fk_property;
				$line->type = $obj->type;
				$line->label = $obj->label;
				$line->amount_ht = $obj->amount_ht;
				$line->amount_vat = $obj->amount_vat;
				$line->amount = $obj->amount;
				$line->date = $obj->date;
				$line->date_start = $obj->date_start;
				$line->date_end = $obj->date_end;
				$line->note_public = $obj->note_public;
				$line->fk_owner = $obj->fk_owner;
				$line->socid = $obj->socid;
				$line->socname = $obj->socname;
				$line->dispatch = $obj->dispatch;
				
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
	
	
	
	function create($user) {
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_cost (";
		$sql .= " fk_property,";
		$sql .= " type,"; 
		$sql .= " label,";
		$sql .= " amount_ht,";
		$sql .= " amount_vat,";
		$sql .= " amount,";
		$sql .= " date,";
		$sql .= " date_start,";
		$sql .= " date_end,";
		$sql .= " note_public,";
		$sql .= " fk_soc,";
		$sql .= " fk_owner ";
		$sql .= " ) VALUES (";
		$sql .= " '" . $this->fk_property . "',";
		$sql .= " '" . $this->db->escape($this->type) . "',";
		$sql .= " '" . $this->db->escape($this->label) . "',";
		$sql .= " '" . $this->amount_ht . "',";
		$sql .= " '" . $this->amount_vat . "',";
		$sql .= " '" . $this->amount . "',";
		$sql .= " " . ($this->date ? "'" . $this->db->idate ( $this->date ) . "'" : "null") . ",";
		$sql .= " " . ($this->date_start ? "'" . $this->db->idate ( $this->date_start ) . "'" : "null") . ",";
		$sql .= " " . ($this->date_end ? "'" . $this->db->idate ( $this->date_end ) . "'" : "null") . ",";
		$sql .= " '" . $this->db->escape($this->note_public) . "',";
		$sql .= " " . (isset($this->socid) ? $this->db->escape($this->socid) : "null") . ", ";
		$sql .= " '" . $this->db->escape($user->id) . "'";
		$sql .= ")";
		 
		echo $sql;
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror();
		}
		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "immo_charge");
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}
	
	
	function update($user) {
		$this->db->begin ();
		
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_cost ";
		$sql .= " SET label ='" . $this->db->escape ( $this->label ) . "',";
		$sql .= " fk_property ='" . $this->db->escape ( $this->fk_property ) . "',";
		$sql .= " type ='" . $this->db->escape ( $this->type ) . "',";
		$sql .= " amount ='" . $this->db->escape ( $this->amount ) . "',";
		$sql .= "fk_soc ='" . $this->db->escape ( $this->fk_soc ) . "',";
		$sql .= " fk_owner ='" . $user->id . "',";
		$sql .= " note_public ='" . $this->db->escape ( $this->note_public ) . "',";
		$sql .= " date=" . (dol_strlen($this->date) != 0 ? "'" . $this->db->idate($this->date) . "'" : 'null').',';
		$sql .= " date_start=" . (dol_strlen($this->date_start) != 0 ? "'" . $this->db->idate($this->date_start) . "'" : 'null').',';
		$sql .= " date_end=" . (dol_strlen($this->date_end) != 0 ? "'" . $this->db->idate($this->date_end) . "'" : 'null').',';
		$sql .= " dispatch=" . $this->db->escape ( $this->dispatch );
		$sql .= " WHERE rowid =" . $this->id;
		
		//echo $sql;
		dol_syslog ( get_class ( $this ) . "::update sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->db->commit ();
			return 1;
		} else {
			$this->error = $this->db->error ();
			$this->db->rollback ();
			return - 1;
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
	
function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;
		
		$result = '';
		
		if (empty ( $this->ref ))
			$this->ref = $this->nom;
		
		$lien = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/cost/card.php?id=' . $this->id . '">';
		$lienfin = '</a>';
		
		if ($withpicto)
			$result .= ($lien . img_object ( $langs->trans ( "ShowCost" ) . ': ' . $this->nom, 'bill' ) . $lienfin . ' ');
		if ($withpicto && $withpicto != 2)
			$result .= ' ';
		if ($withpicto != 2)
			$result .= $lien . ($maxlen ? dol_trunc ( $this->ref, $maxlen ) : $this->ref) . $lienfin;
		return $result;
	}
	
	function set_dispatch($user) {
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'immo_cost SET';
		$sql .= ' dispatch=1';
		$sql .= ' WHERE rowid = ' . $this->id;
		$return = $this->db->query ( $sql );
		$this->db->commit ();
		if ($return)
			return 1;
		else
			return - 1;
	}
	function getLibStatut($mode = 0) {
		return $this->LibStatut ( $this->dispatch, $mode );
	}
	
	/**
	 * Renvoi le label d'un statut donne
	 *
	 * @param int $statut statut
	 * @param int $mode long, 1=label court, 2=Picto + label court, 3=Picto, 4=Picto + label long, 5=label court + Picto
	 * @return string Label
	 */
	function LibStatut($dispatch, $mode = 0) {
		global $langs;
		$langs->load ( 'customers' );
		$langs->load ( 'bills' );
		
		if ($mode == 0) {
			if ($dispatch == 0)
				return $langs->trans ( "ToDispatch" );
			if ($dispatch == 1)
				return $langs->trans ( "Dispatch" );
		}
		if ($mode == 1) {
			if ($dispatch == 0)
				return $langs->trans ( "ToDispatch" );
			if ($dispatch == 1)
				return $langs->trans ( "Dispatch" );
		}
		if ($mode == 2) {
			if ($dispatch == 0)
				return img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' ) . ' ' . $langs->trans ( "ToDispatch" );
			if ($dispatch == 1)
				return img_picto ( $langs->trans ( "Paid" ), 'statut6' ) . ' ' . $langs->trans ( "Dispatch" );
		}
		if ($mode == 3) {
			if ($dispatch == 0)
				return img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' );
			if ($dispatch == 1)
				return img_picto ( $langs->trans ( "Dispatch" ), 'statut6' );
		}
		if ($mode == 4) {
			if ($dispatch == 0)
				return img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' ) . ' ' . $langs->trans ( "ToDispatch" );
			if ($dispatch == 1)
				return img_picto ( $langs->trans ( "Dispatch" ), 'statut6' ) . ' ' . $langs->trans ( "Dispatch" );
		}
		if ($mode == 5) {
			if ($dispatch == 0)
				return $langs->trans ( "ToDispatch" ) . ' ' . img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' );
			if ($dispatch == 1)
				return $langs->trans ( "Dispatch" ) . ' ' . img_picto ( $langs->trans ( "Dispatch" ), 'statut6' );
		}
		
		return "Error, mode/status not found";
	}
	
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Immocost($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->dispatch=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}

	
	
	
	
	
	
	
		
}
