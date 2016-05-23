<?php
/* Copyright (C) 2013		Olivier Geffroy		<jeff@jeffinfo.com>
 * Copyright (C) 2015-2016	Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2015 		Florian HENRY		<florian.henry@open-concept.pro>
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
 * \file    immobilier/class/immoimmoproperty.class.php
 * \ingroup immobilier
 * \brief   Manage property object
 */

// Put here all includes required by your class file
require_once 'commonobjectimmobilier.class.php';

/**
 * Class Immoproperty
 *
 */
class Immoproperty extends CommonObjectImmobilier
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
	public $element = 'immoproperty';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'immo_property';

	/**
	 * @var ImmopropertyLine[] Lines
	 */
	public $lines = array();

	/**
	 * @var int ID
	 */
	public $id;
	/**
	 */
	
	public $entity;
	public $fk_type_property;
	public $fk_property;
	public $property;
	public $fk_owner;
	public $owner_name;
	public $name;
	public $address;
	public $building;
	public $staircase;
	public $floor;
	public $numberofdoor;
	public $area;
	public $numberofpieces;
	public $zip;
	public $town;
	public $fk_pays;
	public $statut;
	public $note_private;
	public $note_public;
	public $datec = '';
	public $tms = '';
	public $fk_user_author;
	public $fk_user_modif;
	public $type_id;
	public $type_label;
	public $type_code;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;
		$this->db = $db;

		$langs->load("immobilier@immobilier");
        $this->labelstatut[0]=$langs->trans("PropertyDisabled");
        $this->labelstatut[1]=$langs->trans("PropertyEnabled");
        $this->labelstatutshort[0]=$langs->trans("Disabled");
        $this->labelstatutshort[1]=$langs->trans("Enabled");

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
    public function LibStatut($statut,$mode=0)
    {
        global $langs;

        if ($mode == 0)
        {
            return $this->labelstatut[$statut];
        }
        if ($mode == 1)
        {
            return $this->labelstatutshort[$statut];
        }
        if ($mode == 2)
        {
            if ($statut == 0) return img_picto($this->labelstatut[$statut],'statut8').' '.$this->labelstatutshort[$statut];
            if ($statut == 1) return img_picto($this->labelstatut[$statut],'statut4').' '.$this->labelstatutshort[$statut];
        }
        if ($mode == 3)
        {
            $prefix='Short';
            if ($statut == 0)  return img_picto($this->labelstatut[$statut],'statut8');
            if ($statut == 1)  return img_picto($this->labelstatut[$statut],'statut4');
        }
        if ($mode == 4)
        {
            if ($statut == 0)  return img_picto($this->labelstatut[$statut],'statut8').' '.$this->labelstatut[$statut];
            if ($statut == 1)  return img_picto($this->labelstatut[$statut],'statut4').' '.$this->labelstatut[$statut];
        }
        if ($mode == 5)
        {
            $prefix='Short';
            if ($statut == 0)  return $this->labelstatutshort[$statut].' '.img_picto($this->labelstatut[$statut],'statut8');
            if ($statut == 1)  return $this->labelstatutshort[$statut].' '.img_picto($this->labelstatut[$statut],'statut4');
        }
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
		if (isset($this->entity)) {
			 $this->entity = trim($this->entity);
		}
		if (isset($this->fk_type_property)) {
			 $this->fk_type_property = trim($this->fk_type_property);
		}
		if (isset($this->fk_property)) {
			 $this->fk_property = trim($this->fk_property);
		}
		if (isset($this->fk_owner)) {
			 $this->fk_owner = trim($this->fk_owner);
		}
		if (isset($this->name)) {
			 $this->name = trim($this->name);
		}
		if (isset($this->address)) {
			 $this->address = trim($this->address);
		}
		if (isset($this->building)) {
			 $this->building = trim($this->building);
		}
		if (isset($this->staircase)) {
			 $this->staircase = trim($this->staircase);
		}
		if (isset($this->floor)) {
			 $this->floor = trim($this->floor);
		}
		if (isset($this->numberofdoor)) {
			 $this->numberofdoor = trim($this->numberofdoor);
		}
		if (isset($this->area)) {
			 $this->area = trim($this->area);
		}
		if (isset($this->numberofpieces)) {
			 $this->numberofpieces = trim($this->numberofpieces);
		}
		if (isset($this->zip)) {
			 $this->zip = trim($this->zip);
		}
		if (isset($this->town)) {
			 $this->town = trim($this->town);
		}
		if (isset($this->fk_pays)) {
			 $this->fk_pays = trim($this->fk_pays);
		}
		if (isset($this->statut)) {
			 $this->statut = trim($this->statut);
		}
		if (isset($this->note_private)) {
			 $this->note_private = trim($this->note_private);
		}
		if (isset($this->note_public)) {
			 $this->note_public = trim($this->note_public);
		}
		if (isset($this->fk_user_author)) {
			 $this->fk_user_author = trim($this->fk_user_author);
		}
		if (isset($this->fk_user_modif)) {
			 $this->fk_user_modif = trim($this->fk_user_modif);
		}

		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . $this->table_element . '(';

		$sql.= 'entity,';
		$sql.= 'fk_type_property,';
		$sql.= 'fk_property,';
		$sql.= 'fk_owner,';
		$sql.= 'name,';
		$sql.= 'address,';
		$sql.= 'building,';
		$sql.= 'staircase,';
		$sql.= 'floor,';
		$sql.= 'numberofdoor,';
		$sql.= 'area,';
		$sql.= 'numberofpieces,';
		$sql.= 'zip,';
		$sql.= 'town,';
		$sql.= 'fk_pays,';
		$sql.= 'statut,';
		$sql.= 'note_private,';
		$sql.= 'note_public,';
		$sql.= 'datec,';
		$sql.= 'fk_user_author';

		$sql .= ') VALUES (';

		$sql .= ' '.(! isset($this->entity)?'NULL':$this->entity).',';
		$sql .= ' '.(! isset($this->fk_type_property)?'NULL':$this->fk_type_property).',';
		$sql .= ' '.(empty($this->fk_property)?'NULL':$this->fk_property).',';
		$sql .= ' '.(! isset($this->fk_owner)?'NULL':$this->fk_owner).',';
		$sql .= ' '.(! isset($this->name)?'NULL':"'".$this->db->escape($this->name)."'").',';
		$sql .= ' '.(! isset($this->address)?'NULL':"'".$this->db->escape($this->address)."'").',';
		$sql .= ' '.(! isset($this->building)?'NULL':"'".$this->db->escape($this->building)."'").',';
		$sql .= ' '.(! isset($this->staircase)?'NULL':"'".$this->db->escape($this->staircase)."'").',';
		$sql .= ' '.(! isset($this->floor)?'NULL':"'".$this->db->escape($this->floor)."'").',';
		$sql .= ' '.(! isset($this->numberofdoor)?'NULL':"'".$this->db->escape($this->numberofdoor)."'").',';
		$sql .= ' '.(! isset($this->area)?'NULL':"'".$this->db->escape($this->area)."'").',';
		$sql .= ' '.(! isset($this->numberofpieces)?'NULL':"'".$this->db->escape($this->numberofpieces)."'").',';
		$sql .= ' '.(! isset($this->zip)?'NULL':"'".$this->db->escape($this->zip)."'").',';
		$sql .= ' '.(! isset($this->town)?'NULL':"'".$this->db->escape($this->town)."'").',';
		$sql .= ' '.(! isset($this->fk_pays)?'NULL':$this->fk_pays).',';
		$sql .= ' 1,';
		$sql .= ' '.(! isset($this->note_private)?'NULL':"'".$this->db->escape($this->note_private)."'").',';
		$sql .= ' '.(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").',';
		$sql .= ' '."'".$this->db->idate(dol_now())."'".',';
		$sql .= ' '.$user->id;

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
		global $langs;
		$langs->load('immobilier@immobilier');
		
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.entity,";
		$sql .= " t.fk_type_property,";
		$sql .= " t.fk_property,";
		$sql .= " t.fk_owner,";
		$sql .= " t.name,";
		$sql .= " t.address,";
		$sql .= " t.building,";
		$sql .= " t.staircase,";
		$sql .= " t.floor,";
		$sql .= " t.numberofdoor,";
		$sql .= " t.area,";
		$sql .= " t.numberofpieces,";
		$sql .= " t.zip,";
		$sql .= " t.town,";
		$sql .= " t.fk_pays,";
		$sql .= " t.statut,";
		$sql .= " t.note_private,";
		$sql .= " t.note_public,";
		$sql .= " t.datec,";
		$sql .= " t.tms,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.fk_user_modif,";
		$sql .= " tp.id as type_id,";
		$sql .= " tp.label as type_label,";
		$sql .= " tp.code as type_code";

		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'c_immo_type_property as tp ON tp.id = t.fk_type_property';
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

				$this->id = $obj->rowid;

				$this->entity = $obj->entity;
				$this->fk_type_property = $obj->fk_type_property;
				$this->fk_property = $obj->fk_property;
				$this->fk_owner = $obj->fk_owner;
				$this->name = $obj->name;
				$this->address = $obj->address;
				$this->building = $obj->building;
				$this->staircase = $obj->staircase;
				$this->floor = $obj->floor;
				$this->numberofdoor = $obj->numberofdoor;
				$this->area = $obj->area;
				$this->numberofpieces = $obj->numberofpieces;
				$this->zip = $obj->zip;
				$this->town = $obj->town;
				$this->fk_pays = $obj->fk_pays;
				$this->statut = $obj->statut;
				$this->note_private = $obj->note_private;
				$this->note_public = $obj->note_public;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->fk_user_author = $obj->fk_user_author;
				$this->fk_user_modif = $obj->fk_user_modif;
				$this->type_id = $obj->type_id;
				$label = ($obj->type_code && $langs->transnoentitiesnoconv($obj->type_code)!=$obj->type_code?$langs->transnoentitiesnoconv($obj->type_code):($obj->type_label!='-'?$obj->type_label:''));
				$this->type_label = $label;
				$this->type_code = $obj->type_code;
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
	function fetchAll($year)
    {
        global $langs;

        $sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.entity,";
		$sql .= " t.fk_type_property,";
		$sql .= " t.fk_property,";
		$sql .= " t.fk_owner,";
		$sql .= " t.name,";
		$sql .= " t2.name as property,";
		$sql .= " t.address,";
		$sql .= " t.building,";
		$sql .= " t.staircase,";
		$sql .= " t.floor,";
		$sql .= " t.numberofdoor,";
		$sql .= " t.area,";
		$sql .= " t.numberofpieces,";
		$sql .= " t.zip,";
		$sql .= " t.town,";
		$sql .= " t.fk_pays,";
		$sql .= " t.statut,";
		$sql .= " t.note_private,";
		$sql .= " t.note_public,";
		$sql .= " t.datec,";
		$sql .= " t.tms,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.fk_user_modif,";
		$sql .= " tp.id as type_id,";
		$sql .= " tp.label as type_label,";
		$sql .= " tp.code as type_code,";
		$sql .= " soc.rowid as soc_id,";
		$sql .= " soc.nom as owner_name";
		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . 'immo_property as t';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'c_immo_type_property as tp ON tp.id = t.fk_type_property';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'immo_property as t2 ON t2.rowid = t.fk_property';
		$sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'societe as soc ON soc.rowid = t.fk_owner';
        
		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
        $resql=$this->db->query($sql);
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
									'name' => $obj->name,
									'address' => $obj->address,
									'zip' => $obj->zip,
									'town' => $obj->town,
									'fk_pays' => $obj->fk_pays,
									'statut' => $obj->statut,
									'type_label' => $obj->type_label,
									'building' => $obj->property,
									'owner_name' => $obj->owner_name,
									);
					$i ++;
				}
			}
			
			return $data;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return -1;
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
		if (isset($this->entity)) {
			 $this->entity = trim($this->entity);
		}
		if (isset($this->fk_type_property)) {
			 $this->fk_type_property = trim($this->fk_type_property);
		}
		if (isset($this->fk_property)) {
			 $this->fk_property = trim($this->fk_property);
		}
		if (isset($this->fk_owner)) {
			 $this->fk_owner = trim($this->fk_owner);
		}
		if (isset($this->name)) {
			 $this->name = trim($this->name);
		}
		if (isset($this->address)) {
			 $this->address = trim($this->address);
		}
		if (isset($this->building)) {
			 $this->building = trim($this->building);
		}
		if (isset($this->staircase)) {
			 $this->staircase = trim($this->staircase);
		}
		if (isset($this->floor)) {
			 $this->floor = trim($this->floor);
		}
		if (isset($this->numberofdoor)) {
			 $this->numberofdoor = trim($this->numberofdoor);
		}
		if (isset($this->area)) {
			 $this->area = trim($this->area);
		}
		if (isset($this->numberofpieces)) {
			 $this->numberofpieces = trim($this->numberofpieces);
		}
		if (isset($this->zip)) {
			 $this->zip = trim($this->zip);
		}
		if (isset($this->town)) {
			 $this->town = trim($this->town);
		}
		if (isset($this->fk_pays)) {
			 $this->fk_pays = trim($this->fk_pays);
		}
		if (isset($this->statut)) {
			 $this->statut = trim($this->statut);
		}
		if (isset($this->note_private)) {
			 $this->note_private = trim($this->note_private);
		}
		if (isset($this->note_public)) {
			 $this->note_public = trim($this->note_public);
		}
		if (isset($this->fk_user_modif)) {
			 $this->fk_user_modif = trim($this->fk_user_modif);
		}

		// Check parameters
		// Put here code to add a control on parameters values

		// Update request
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';

		$sql .= ' entity = '.(isset($this->entity)?$this->entity:"null").',';
		$sql .= ' fk_type_property = '.(isset($this->fk_type_property)?$this->fk_type_property:"null").',';
		$sql .= ' fk_property = '.(!empty($this->fk_property)?$this->fk_property:"null").',';
		$sql .= ' fk_owner = '.(isset($this->fk_owner)?$this->fk_owner:"null").',';
		$sql .= ' name = '.(isset($this->name)?"'".$this->db->escape($this->name)."'":"null").',';
		$sql .= ' address = '.(isset($this->address)?"'".$this->db->escape($this->address)."'":"null").',';
		$sql .= ' building = '.(isset($this->building)?"'".$this->db->escape($this->building)."'":"null").',';
		$sql .= ' staircase = '.(isset($this->staircase)?"'".$this->db->escape($this->staircase)."'":"null").',';
		$sql .= ' floor = '.(isset($this->floor)?"'".$this->db->escape($this->floor)."'":"null").',';
		$sql .= ' numberofdoor = '.(isset($this->numberofdoor)?"'".$this->db->escape($this->numberofdoor)."'":"null").',';
		$sql .= ' area = '.(isset($this->area)?"'".$this->db->escape($this->area)."'":"null").',';
		$sql .= ' numberofpieces = '.(isset($this->numberofpieces)?"'".$this->db->escape($this->numberofpieces)."'":"null").',';
		$sql .= ' zip = '.(isset($this->zip)?"'".$this->db->escape($this->zip)."'":"null").',';
		$sql .= ' town = '.(isset($this->town)?"'".$this->db->escape($this->town)."'":"null").',';
		$sql .= ' fk_pays = '.(isset($this->fk_pays)?$this->fk_pays:"null").',';
		$sql .= ' statut = '.(isset($this->statut)?$this->statut:"null").',';
		$sql .= ' note_private = '.(isset($this->note_private)?"'".$this->db->escape($this->note_private)."'":"null").',';
		$sql .= ' note_public = '.(isset($this->note_public)?"'".$this->db->escape($this->note_public)."'":"null").',';
		$sql .= ' tms = '.(dol_strlen($this->tms) != 0 ? "'".$this->db->idate($this->tms)."'" : "'".$this->db->idate(dol_now())."'").',';
		$sql .= ' fk_user_modif = '.$user->id;

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
	 *	Return clicable name (with picto eventually)
	 *
	 *  @param	int		$withpicto		0=No picto, 1=Include picto into link, 2=Only picto
	 *  @param	int		$maxlength		Max length
	 *  @return	string					Chaine avec URL
	 */
	function getNomUrl($withpicto = 0, $maxlength = 0)
	{
		global $langs;

		$result = '';

		if (empty ( $this->name ))
			$this->name = $this->name;

			$link = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/property/card.php?id=' . $this->id . '">';
			$linkend = '</a>';

			if ($withpicto)
				$result .= ($link . img_object ( $langs->trans("ShowProperty") . ': ' . $this->name, 'building@immobilier' ) . $linkend . ' ');
			if ($withpicto && $withpicto != 2)
				$result .= ' ';
			if ($withpicto != 2)
				$result .= $link . ($maxlength ? dol_trunc ( $this->name, $maxlength ) : $this->name) . $linkend;
			return $result;
	}
	
	/**
	 *	Return clicable name (with picto eventually)
	 *
	 *  @param	int		$withpicto		0=No picto, 1=Include picto into link, 2=Only picto
	 *  @param	int		$maxlength		Max length
	 *  @return	string					Chaine avec URL
	 */
	function getNomUrlOwner($withpicto = 0, $maxlength = 0)
	{
		global $langs;
		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

		$result = '';

		if (!empty ( $this->fk_owner )) {
			$societe_owner = new Societe($this->db);
			$result=$societe_owner->fetch($this->fk_owner);
			if ($result<0) {
				$this->errors[]=$societe_owner->error;
				return -1;
			}
			return $societe_owner->getNomUrl($withpicto,'', 0, 0, $maxlength);
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
		$sql .= " p.rowid, p.datec, p.tms, p.fk_user_author, p.fk_user_modif";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_property as p";
		$sql .= " WHERE p.rowid = " . $id;

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
 * Class ImmopropertyLine
 */
class ImmopropertyLine
{
	/**
	 * @var int ID
	 */
	public $id;

	public $entity;
	public $fk_type_property;
	public $fk_property;
	public $fk_owner;
	public $name;
	public $address;
	public $building;
	public $staircase;
	public $floor;
	public $numberofdoor;
	public $area;
	public $numberofpieces;
	public $zip;
	public $town;
	public $fk_pays;
	public $statut;
	public $note_private;
	public $note_public;
	public $datec = '';
	public $tms = '';
	public $fk_user_author;
	public $fk_user_modif;
}
