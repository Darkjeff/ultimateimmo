<?php
/* Copyright (C) 2012-2014		Florian Henry			<florian.henry@open-concept.pro>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file agefodd/class/agefodd_contact.class.php
 * \ingroup agefodd
 * \brief Manage contact object
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");

/**
 * Contact Class
 */
class Agefodd_contact extends CommonObject {
	var $db;
	var $error;
	var $errors = array ();
	var $element = 'agefodd';
	var $table_element = 'agefodd_contact';
	var $id;
	var $spid;
	var $lines = array ();
	
	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	function __construct($DB) {
		$this->db = $DB;
		return 1;
	}
	
	/**
	 * Create object into database
	 *
	 * @param User $user that create
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		// Check parameters
		// Put here code to add control on parameters value
		
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_contact(";
		$sql .= "fk_socpeople, fk_user_author,fk_user_mod, entity, datec";
		$sql .= ") VALUES (";
		$sql .= ' ' . $this->spid . ', ';
		$sql .= ' ' . $user->id . ',';
		$sql .= ' ' . $user->id . ',';
		$sql .= ' ' . $conf->entity . ',';
		$sql .= "'".$this->db->idate(dol_now())."'";
		$sql .= ")";
		
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::create", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror();
		}
		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "agefodd_contact");
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
	
	/**
	 * Load object in memory from database
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch($id, $type = 'socid') {
		global $langs;
		
		$sql = "SELECT";
		$sql .= " c.rowid,";
		$sql .= " s.rowid as spid , s.lastname, s.firstname, s.civility as civilite, s.address, s.zip, s.town, c.archive";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_contact as c";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople as s ON c.fk_socpeople = s.rowid";
		if ($type == 'socid') {
			$sql .= " WHERE s.fk_soc = " . $id;
		} else {
			$sql .= " WHERE c.rowid = " . $id;
		}
		$sql .= " AND c.entity IN (" . getEntity('agsession') . ")";
		
		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->ref = $obj->rowid; // Use for next prev ref
				$this->spid = $obj->spid;
				$this->lastname = $obj->lastname;
				$this->firstname = $obj->firstname;
				$this->civilite = $obj->civilite;
				$this->address = $obj->address;
				$this->zip = $obj->zip;
				$this->town = $obj->town;
				$this->archive = $obj->archive;
			}
			$this->db->free($resql);
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	/**
	 * Load object in memory from database
	 *
	 * @param string $sortorder Sort Order
	 * @param string $sortfield Sort field
	 * @param int $limit offset limit
	 * @param int $offset offset limit
	 * @param int $arch archive
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all($sortorder, $sortfield, $limit = '', $offset, $arch = 0) {
		global $langs;
		
		$sql = "SELECT";
		$sql .= " c.rowid, c.fk_socpeople,";
		$sql .= " s.rowid as spid , s.lastname, s.firstname, s.civility, s.phone, s.email, s.phone_mobile,";
		$sql .= " soc.nom as socname, soc.rowid as socid, c.archive";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_contact as c";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople as s ON c.fk_socpeople = s.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as soc ON soc.rowid = s.fk_soc";
		$sql .= " WHERE c.entity IN (" . getEntity('agsession') . ")";
		if ($arch == 0 || $arch == 1)
			$sql .= " AND c.archive = " . $arch;
		$sql .= " ORDER BY " . $sortfield . " " . $sortorder . " ";
		if (! empty($limit)) {
			$sql .= $this->db->plimit($limit + 1, $offset);
		}
		
		dol_syslog(get_class($this) . "::fetch_all ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->lines = array ();
			$num = $this->db->num_rows($resql);
			$i = 0;
			
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object($resql);
					
					$line = new AgfContactLine();
					
					$line->id = $obj->rowid;
					$line->ref = $obj->rowid; // Use for next prev ref
					$line->spid = $obj->spid;
					$line->socid = $obj->socid;
					$line->socname = $obj->socname;
					$line->lastname = $obj->lastname;
					$line->firstname = $obj->firstname;
					$line->civilite = $obj->civility;
					$line->phone = $obj->phone;
					$line->email = $obj->email;
					$line->phone_mobile = $obj->phone_mobile;
					$line->fk_socpeople = $obj->fk_socpeople;
					$line->archive = $obj->archive;
					
					$this->lines [$i] = $line;
					
					$i ++;
				}
			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_all " . $this->error, LOG_ERR);
			return - 1;
		}
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
		$sql .= " c.rowid, c.datec, c.tms, c.fk_user_mod, c.fk_user_author";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_contact as c";
		$sql .= " WHERE c.rowid = " . $id;
		
		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->date_creation = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->tms);
				$this->user_modification = $obj->fk_user_mod;
				$this->user_creation = $obj->fk_user_author;
			}
			$this->db->free($resql);
			
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	/**
	 * Update object into database
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function update($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		// Check parameters
		// Put here code to add control on parameters values
		
		// Update request
		if (! isset($this->archive))
			$this->archive = 0;
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_contact SET";
		$sql .= " fk_user_mod=" . $user->id . ",";
		$sql .= " archive=" . $this->archive . " ";
		$sql .= " WHERE rowid = " . $this->id;
		
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::update ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror();
		}
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::update " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
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
	 * @param int $id to delete
	 * @return int <0 if KO, >0 if OK
	 */
	function remove($id) {
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_contact";
		$sql .= " WHERE rowid = " . $id;
		
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::remove", LOG_DEBUG);
		$resql = $this->db->query($sql);
		
		if ($resql) {
			$this->db->commit();
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			dol_syslog(get_class($this) . "::remove " . $this->error, LOG_ERR);
			$this->db->rollback();
			return - 1;
		}
	}
}

/**
 * Contact line Class
 */
class AgfContactLine {
	var $id;
	var $ref;
	var $spid;
	var $socid;
	var $socname;
	var $lastname;
	var $firstname;
	var $civilite;
	var $phone;
	var $email;
	var $phone_mobile;
	var $fk_socpeople;
	var $archive;
	function __construct() {
		return 1;
	}
}