<?php
/* Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
 * Copyright (C) 2015-2016	Alexandre Spangaro  <aspangaro@zendsi.com>
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
 * \file	immobilier/class/immoimmorenter.class.php
 * \ingroup immobilier
 * \brief	Manage renter object
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");
require_once (DOL_DOCUMENT_ROOT . "/contact/class/contact.class.php");

/**
 * Renter Class
 */
class Renter extends CommonObject {
	var $db;
	var $error;
	var $errors = array ();
	var $element = 'immorenter';
	var $table_element = 'immo_renter';
	var $id;
	protected $ismultientitymanaged = 1; // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	var $nom;
	var $prenom;
	var $fonction;
	var $tel1;
	var $tel2;
	var $mail;
	var $note;
	var $date_birth;
	var $place_birth;
	var $socid;
	var $socname;
	var $fk_socpeople;
	var $fk_owner;
	var $owner_name;
	var $lines = array ();
	var $tms;
	// Statut show if the property is enabled '1' or not '0'
	var $statut;
	
	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	function __construct($db) {
		$this->db = $db;
		return 1;
	}

	/**
     * 	Return label for a status (disable, enable)
     *
     *  @param	int		$mode       0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label
     *  @return string        		Label
     */
    public function getLibStatut($mode=0)
    {
        return $this->LibStatut($this->statut,$mode);
    }

    /**
     *  Return label for status given
     *
     *  @param	int		$statut        	Id status
     *  @param  int		$mode          	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label
     *  @return string 			       	Label of the status
     */
   function LibStatut($statut) {
		global $langs;
		

		if ($statut == 0)
			return img_picto ( $langs->trans ( "Inactive" ), 'statut8' ) . ' ' . $langs->trans ( "Inactive" );
		if ($statut == 1)
			return img_picto ( $langs->trans ( "Active" ), 'statut4' ) . ' ' . $langs->trans ( "Active" );

		return "Error, mode/status not found";
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
		if (isset($this->nom))
			$this->nom = $this->db->escape(trim($this->nom));
		if (isset($this->prenom))
			$this->prenom = $this->db->escape(trim($this->prenom));
		if (isset($this->fonction))
			$this->fonction = $this->db->escape(trim($this->fonction));
		if (isset($this->tel1))
			$this->tel1 = $this->db->escape(trim($this->tel1));
		if (isset($this->tel2))
			$this->tel2 = $this->db->escape(trim($this->tel2));
		if (isset($this->mail))
			$this->mail = $this->db->escape(trim($this->mail));
		if (isset($this->note))
			$this->note = $this->db->escape(trim($this->note));
		if (isset($this->place_birth))
			$this->place_birth = $this->db->escape(trim($this->place_birth));
			
		// Check parameters
		// Put here code to add control on parameters value
		$this->nom = mb_strtoupper($this->nom, 'UTF-8');
		if ((strpos($this->prenom, "-") !== false) || (strpos($this->prenom, " ") !== false)) {
			$this->prenom = ucwords(strtolower($this->prenom));
			$this->prenom = preg_replace('#-(\w)#e', "'-'.strtoupper('$1')", $this->prenom);
		} else {
			$this->prenom = ucfirst(mb_strtolower($this->prenom, 'UTF-8'));
		}
		
		if (empty($this->civilite)) {
			$error ++;
			$this->errors [] = $langs->trans("AgfCiviliteMandatory");
		}
		
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_renter(";
		$sql .= " nom,";
		$sql .= " prenom,";
		$sql .= " civilite,";
		$sql .= " fk_user_author,";
		$sql .= " fk_user_mod,";
		$sql .= " datec,";
		$sql .= " fk_soc,";
		$sql .= " fk_owner,";
		$sql .= " fonction,";
		$sql .= " tel1,";
		$sql .= " tel2,";
		$sql .= " mail,";
		$sql .= " note,";
		$sql .= " fk_socpeople,";
		$sql .= " entity,";
		$sql .= " date_birth,";
		$sql .= " place_birth,";
		$sql .= " tms,";
		$sql .= " statut";
		$sql .= ") VALUES (";
		
		$sql .= " " . (isset($this->nom) ? "'" . $this->nom . "'" : "null") . ", ";
		$sql .= " " . (isset($this->prenom) ? "'" . $this->prenom . "'" : "null") . ", ";
		$sql .= " " . (isset($this->civilite) ? "'" . $this->civilite . "'" : "null") . ", ";
		$sql .= ' ' . $user->id . ", ";
		$sql .= ' ' . $user->id . ", ";
		$sql .= "'" . $this->db->idate(dol_now()) . "', ";
		$sql .= " " . (isset($this->socid) ? $this->db->escape($this->socid) : "null") . ", ";
		$sql .= " " . (isset($this->fk_owner) ? $this->db->escape($this->fk_owner) : "null") . ", ";
		$sql .= " " . (isset($this->fonction) ? "'" . $this->fonction . "'" : "null") . ", ";
		$sql .= " " . (isset($this->tel1) ? "'" . $this->tel1 . "'" : "null") . ", ";
		$sql .= " " . (isset($this->tel2) ? "'" . $this->tel2 . "'" : "null") . ", ";
		$sql .= " " . (isset($this->mail) ? "'" . $this->mail . "'" : "null") . ", ";
		$sql .= " " . (isset($this->note) ? "'" . $this->note . "'" : "null") . ", ";
		$sql .= " " . (isset($this->fk_socpeople) ? $this->db->escape($this->fk_socpeople) : "null") . ", ";
		$sql .= " " . $conf->entity . ",";
		$sql .= " " . (! isset($this->date_birth) || dol_strlen($this->date_birth) == 0 ? 'NULL' : "'" . $this->db->idate($this->date_birth) . "'") . ", ";
		$sql .= " " . (isset($this->place_birth) ? "'" . $this->place_birth . "'" : "null") . ", ";
		$sql .= "" .(dol_strlen($this->tms) != 0 ? "'".$this->db->idate($this->tms)."'" : "'".$this->db->idate(dol_now())."'").',';
		$sql .= " ".$this->statut;
		$sql .= ")";

		if (! $error) {
			$this->db->begin();

			dol_syslog(get_class($this) . "::create", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror();
			}

			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "immo_renter");
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
	function fetch($id) {
		global $langs;

		$sql = "SELECT";
		$sql .= " so.rowid as socid,";
		$sql .= " so.nom as socname,";
		$sql .= " civ.code as civilite_code,";
		$sql .= " s.rowid,";
		$sql .= " s.nom,";
		$sql .= " s.prenom,";
		$sql .= " s.civilite,";
		$sql .= " s.fk_soc,";
		$sql .= " s.fk_owner,";
		$sql .= " s.fonction,";
		$sql .= " s.tel1 as phone_pro,";
		$sql .= " s.tel2 as phone_mobile,";
		$sql .= " s.mail as email,";
		$sql .= " s.note,";
		$sql .= " s.fk_socpeople,";
		$sql .= " s.date_birth,";
		$sql .= " s.place_birth,";
		$sql .= " s.statut";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON s.fk_soc = so.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_civility as civ";
		$sql .= " ON s.civilite = civ.code";
		$sql .= " WHERE s.rowid = " . $id;
		$sql .= " AND s.entity IN (" . getEntity('immo_renter') . ")";

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				if (! (empty($obj->fk_socpeople))) {
					$contact = new Contact($this->db);
					$result = $contact->fetch($obj->fk_socpeople);
					
					if ($result > 0) {
						
						$this->id 				= $obj->rowid;
						$this->ref 				= $obj->rowid; // use for next prev refs
						
						$this->nom 				= $contact->lastname;
						$this->prenom			= $contact->firstname;
						$this->name				= strtoupper($contact->lastname) . ' ' . $contact->firstname;
						$this->civilite			= $contact->civility_id;
						$this->socid			= $contact->socid;
						$this->fk_owner			= $contact->fk_owner;
						$this->socname			= $contact->socname;
						$this->fonction			= $contact->poste;
						$this->phone_pro		= $contact->phone_pro;
						$this->phone_mobile		= $contact->phone_mobile;
						$this->email			= $contact->email;
						$this->note				= $obj->note;
						$this->fk_socpeople		= $obj->fk_socpeople;
						$this->date_birth		= $contact->birthday;
						$this->place_birth		= $obj->place_birth;
						$this->statut 			= $contact->statut;
					}
				} else {
					$this->id				= $obj->rowid;
					$this->ref				= $obj->rowid; // use for next prev refs
					
					$this->nom				= $obj->nom;
					$this->prenom			= $obj->prenom;
					$this->name				= strtoupper($obj->nom) . ' ' . $obj->prenom;
					$this->civilite			= $obj->civilite;
					$this->socid			= $obj->socid;
					$this->fk_owner			= $obj->fk_owner;
					$this->socname			= $obj->socname;
					$this->fonction			= $obj->fonction;
					$this->phone_pro		= $obj->phone_pro;
					$this->phone_mobile		= $obj->phone_mobile;
					$this->email			= $obj->email;
					$this->note				= $obj->note;
					$this->place_birth		= $obj->place_birth;
					$this->fk_socpeople		= 0;
					$this->date_birth		= $this->db->jdate($obj->date_birth);
					$this->statut 			= $obj->statut;
				}
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
	 * Load all objects in memory from database
	 *
	 * @param string $sortorder order
	 * @param string $sortfield field
	 * @param int $limit page
	 * @param int $offset
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetchAll() {
		global $langs;

		$sql = "SELECT";
		$sql .= " so.rowid as socid, so.nom as socname,";
		$sql .= " civ.code as civilitecode,";
		$sql .= " s.rowid, s.nom, s.prenom, s.civilite, s.fk_soc, s.fonction, s.statut,";
		$sql .= " s.tel1 as phone_pro, s.tel2 as phone_mobile, s.mail as email, s.note, s.date_birth, s.place_birth";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON s.fk_soc = so.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_civility as civ";
		$sql .= " ON s.civilite = civ.code";
		
		dol_syslog(get_class($this) . "::fetch_all", LOG_DEBUG);
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
					
									'id' => $obj->rowid,
									'NomPrenom' => $obj->nom.' '.$obj->prenom,
									'Civility' => $obj->civilite,
									'Company_id' => $obj->socid,
									'Company' => $obj->socname,
									'Phone' => $obj->phone_pro,
									'Phonemobile' => $obj->phone_mobile,
									'Email' => $obj->email,
									'Statut' => $obj->statut
									);
					
					$i ++;
				}
			}

			return $data; 
		}else {
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
		$sql .= " s.rowid, s.datec, s.tms, s.fk_user_author, s.fk_user_mod";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter as s";
		$sql .= " WHERE s.rowid = " . $id;

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
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
		if (isset($this->nom))
			$this->nom = $this->db->escape(trim($this->nom));
		if (isset($this->prenom))
			$this->prenom = $this->db->escape(trim($this->prenom));
		if (isset($this->fonction))
			$this->fonction = $this->db->escape(trim($this->fonction));
		if (isset($this->tel1))
			$this->tel1 = $this->db->escape(trim($this->tel1));
		if (isset($this->tel2))
			$this->tel2 = $this->db->escape(trim($this->tel2));
		if (isset($this->mail))
			$this->mail = $this->db->escape(trim($this->mail));
		if (isset($this->note))
			$this->note = $this->db->escape(trim($this->note));
		if (isset($this->place_birth))
			$this->place_birth = $this->db->escape(trim($this->place_birth));

		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_renter SET";
		$sql .= " nom=" . (isset($this->nom) ? "'" . $this->nom . "'" : "null") . ",";
		$sql .= " prenom=" . (isset($this->prenom) ? "'" . $this->prenom . "'" : "null") . ",";
		$sql .= " civilite=" . (isset($this->civilite) ? "'" . $this->civilite . "'" : "null") . ",";
		$sql .= " fk_user_mod=" . $user->id . ",";
		$sql .= " fk_soc=" . (isset($this->socid) ? $this->socid : "null") . ",";
		$sql .= " fonction=" . (isset($this->fonction) ? "'" . $this->fonction . "'" : "null") . ",";
		$sql .= " tel1=" . (isset($this->tel1) ? "'" . $this->tel1 . "'" : "null") . ",";
		$sql .= " tel2=" . (isset($this->tel2) ? "'" . $this->tel2 . "'" : "null") . ",";
		$sql .= " mail=" . (isset($this->mail) ? "'" . $this->mail . "'" : "null") . ",";
		$sql .= ' statut = '.(isset($this->statut)?$this->statut:"null").',';
		$sql .= " note=" . (isset($this->note) ? "'" . $this->note . "'" : "null") . ",";
		$sql .= " fk_socpeople=" . (isset($this->fk_socpeople) ? $this->fk_socpeople : "null") . ", ";
		$sql .= " fk_owner=" . (isset($this->fk_owner) ? $this->fk_owner : "null") . ", ";
		$sql .= " date_birth=" . (! isset($this->date_birth) || dol_strlen($this->date_birth) == 0 ? "null" : "'" . $this->db->idate($this->date_birth) . "',");
		$sql .= " place_birth=" . (isset($this->place_birth) ? "'" . $this->place_birth . "'" : "null");
		$sql .= " WHERE rowid = " . $this->id;

		$this->db->begin();

		dol_syslog(get_class($this) . "::update", LOG_DEBUG);
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
	 * @param User $user that delete
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function remove($id) {
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "immo_renter";
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

	/**
	 * Search renter
	 *
	 * @param string $lastname lastname
	 * @param string $firstname firstname
	 * @param int $socid thirdparty id
	 * @return int <0 if KO, >0 if OK
	 */
	function searchByLastNameFirstNameSoc($lastname, $firstname, $socid) {

		global $conf;

		$sql = "SELECT";
		$sql .= " s.rowid";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter as s";
		$sql .= " WHERE (s.fk_soc=" . $socid;
		//contact is in a company witch child $socid
		$sql .= " OR (s.fk_soc IN (SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE parent=" . $socid. "))";
		//contact is in a company witch share the same mother company than $socid
		$sql .= " OR (s.fk_soc IN (SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE parent IN (SELECT parent FROM " . MAIN_DB_PREFIX . "societe WHERE rowid=" . $socid. "))))";
		$sql .= " AND UPPER(s.nom)='" . strtoupper(trim($lastname)) . "'";
		$sql .= " AND UPPER(s.prenom)='" . strtoupper(trim($firstname)) . "'";
		$sql .= " AND s.entity IN (".$conf->entity.')';

		$num = 0;

		dol_syslog(get_class($this) . "::searchByLastNameFirstNameSoc");
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
		} else {
			$this->error = $this->db->lasterror();
			dol_syslog(get_class($this) . "::searchByLastNameFirstNameSoc " . $this->error, LOG_ERR);
			return - 1;
		}

		$this->db->free($resql);

		$sql = "SELECT";
		$sql .= " s.rowid";
		$sql .= " FROM " . MAIN_DB_PREFIX . "socpeople as s";
		$sql .= " WHERE s.fk_soc=" . $socid;
		$sql .= " AND s.entity IN (" . getEntity('agsession').')';
		$sql .= " AND UPPER(s.lastname)='" . strtoupper($lastname) . "'";
		$sql .= " AND UPPER(s.firstname)='" . strtoupper($firstname) . "'";

		dol_syslog(get_class($this) . "::searchByLastNameFirstNameSoc");
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$num = + $this->db->num_rows($resql);
			}
		} else {
			$this->error = $this->db->lasterror();
			dol_syslog(get_class($this) . "::searchByLastNameFirstNameSoc " . $this->error, LOG_ERR);
			return - 1;
		}
		dol_syslog(get_class($this) . "::searchByLastNameFirstNameSoc num=" . $num);
		return $num;
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

		return '<a href="../renter/card.php?id=' . $this->id . '">' . img_object($langs->trans("ShowDetails"), "user") . ' ' . strtoupper($this->nom) . ' ' . $this->prenom . '</a>';
	}
	

	
}
class renterLine {
	var $socid;
	var $socname;
	var $civilitecode;
	var $rowid;
	var $nom;
	var $prenom;
	var $civilite;
	var $fk_soc;
	var $fonction;
	var $tel1;
	var $tel2;
	var $mail;
	var $note;
	var $fk_socpeople;
	var $date_birth;
	var $place_birth;
	function __construct() {
		return 1;
	}
}