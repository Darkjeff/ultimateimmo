<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *  \file       dev/skeletons/immobails.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2014-03-15 09:42
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Immobails extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='immobails';			//!< Id that identify managed objects
	var $table_element='immobails';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $fk_prop;
	var $fk_loc;
	var $fk_logement;
	var $fk_mandat;
	var $Type;
	var $Date_location='';
	var $Depot_garantie;
	var $loy;
	var $date_entree='';
	var $date_fin_preavis='';
	var $date_fin='';
	var $montant_tot;
	var $charges;
	var $date_der_rev='';
	var $commentaire;
	var $tms='';
	var $entity;

    


    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->fk_prop)) $this->fk_prop=trim($this->fk_prop);
		if (isset($this->fk_loc)) $this->fk_loc=trim($this->fk_loc);
		if (isset($this->fk_logement)) $this->fk_logement=trim($this->fk_logement);
		if (isset($this->fk_mandat)) $this->fk_mandat=trim($this->fk_mandat);
		if (isset($this->Type)) $this->Type=trim($this->Type);
		if (isset($this->Depot_garantie)) $this->Depot_garantie=trim($this->Depot_garantie);
		if (isset($this->loy)) $this->loy=trim($this->loy);
		if (isset($this->montant_tot)) $this->montant_tot=trim($this->montant_tot);
		if (isset($this->charges)) $this->charges=trim($this->charges);
		if (isset($this->commentaire)) $this->commentaire=trim($this->commentaire);
		if (isset($this->entity)) $this->entity=trim($this->entity);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."immo_bails(";
		
		$sql.= "fk_prop,";
		$sql.= "fk_loc,";
		$sql.= "fk_logement,";
		$sql.= "fk_mandat,";
		$sql.= "Type,";
		$sql.= "Date_location,";
		$sql.= "Depot_garantie,";
		$sql.= "loy,";
		$sql.= "date_entree,";
		$sql.= "date_fin_preavis,";
		$sql.= "date_fin,";
		$sql.= "montant_tot,";
		$sql.= "charges,";
		$sql.= "date_der_rev,";
		$sql.= "commentaire,";
		$sql.= "entity";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_prop)?'NULL':"'".$this->fk_prop."'").",";
		$sql.= " ".(! isset($this->fk_loc)?'NULL':"'".$this->fk_loc."'").",";
		$sql.= " ".(! isset($this->fk_logement)?'NULL':"'".$this->fk_logement."'").",";
		$sql.= " ".(! isset($this->fk_mandat)?'NULL':"'".$this->fk_mandat."'").",";
		$sql.= " ".(! isset($this->Type)?'NULL':"'".$this->db->escape($this->Type)."'").",";
		$sql.= " ".(! isset($this->Date_location) || dol_strlen($this->Date_location)==0?'NULL':$this->db->idate($this->Date_location)).",";
		$sql.= " ".(! isset($this->Depot_garantie)?'NULL':"'".$this->db->escape($this->Depot_garantie)."'").",";
		$sql.= " ".(! isset($this->loy)?'NULL':"'".$this->loy."'").",";
		$sql.= " ".(! isset($this->date_entree) || dol_strlen($this->date_entree)==0?'NULL':$this->db->idate($this->date_entree)).",";
		$sql.= " ".(! isset($this->date_fin_preavis) || dol_strlen($this->date_fin_preavis)==0?'NULL':$this->db->idate($this->date_fin_preavis)).",";
		$sql.= " ".(! isset($this->date_fin) || dol_strlen($this->date_fin)==0?'NULL':$this->db->idate($this->date_fin)).",";
		$sql.= " ".(! isset($this->montant_tot)?'NULL':"'".$this->montant_tot."'").",";
		$sql.= " ".(! isset($this->charges)?'NULL':"'".$this->charges."'").",";
		$sql.= " ".(! isset($this->date_der_rev) || dol_strlen($this->date_der_rev)==0?'NULL':$this->db->idate($this->date_der_rev)).",";
		$sql.= " ".(! isset($this->commentaire)?'NULL':"'".$this->db->escape($this->commentaire)."'").",";
		$sql.= " ".(! isset($this->entity)?'NULL':"'".$this->entity."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."immo_bails");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.fk_prop,";
		$sql.= " t.fk_loc,";
		$sql.= " t.fk_logement,";
		$sql.= " t.fk_mandat,";
		$sql.= " t.Type,";
		$sql.= " t.Date_location,";
		$sql.= " t.Depot_garantie,";
		$sql.= " t.loy,";
		$sql.= " t.date_entree,";
		$sql.= " t.date_fin_preavis,";
		$sql.= " t.date_fin,";
		$sql.= " t.montant_tot,";
		$sql.= " t.charges,";
		$sql.= " t.date_der_rev,";
		$sql.= " t.commentaire,";
		$sql.= " t.tms,";
		$sql.= " t.entity";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."immo_bails as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->fk_prop = $obj->fk_prop;
				$this->fk_loc = $obj->fk_loc;
				$this->fk_logement = $obj->fk_logement;
				$this->fk_mandat = $obj->fk_mandat;
				$this->Type = $obj->Type;
				$this->Date_location = $this->db->jdate($obj->Date_location);
				$this->Depot_garantie = $obj->Depot_garantie;
				$this->loy = $obj->loy;
				$this->date_entree = $this->db->jdate($obj->date_entree);
				$this->date_fin_preavis = $this->db->jdate($obj->date_fin_preavis);
				$this->date_fin = $this->db->jdate($obj->date_fin);
				$this->montant_tot = $obj->montant_tot;
				$this->charges = $obj->charges;
				$this->date_der_rev = $this->db->jdate($obj->date_der_rev);
				$this->commentaire = $obj->commentaire;
				$this->tms = $this->db->jdate($obj->tms);
				$this->entity = $obj->entity;

                
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->fk_prop)) $this->fk_prop=trim($this->fk_prop);
		if (isset($this->fk_loc)) $this->fk_loc=trim($this->fk_loc);
		if (isset($this->fk_logement)) $this->fk_logement=trim($this->fk_logement);
		if (isset($this->fk_mandat)) $this->fk_mandat=trim($this->fk_mandat);
		if (isset($this->Type)) $this->Type=trim($this->Type);
		if (isset($this->Depot_garantie)) $this->Depot_garantie=trim($this->Depot_garantie);
		if (isset($this->loy)) $this->loy=trim($this->loy);
		if (isset($this->montant_tot)) $this->montant_tot=trim($this->montant_tot);
		if (isset($this->charges)) $this->charges=trim($this->charges);
		if (isset($this->commentaire)) $this->commentaire=trim($this->commentaire);
		if (isset($this->entity)) $this->entity=trim($this->entity);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."immo_bails SET";
        
		$sql.= " fk_prop=".(isset($this->fk_prop)?$this->fk_prop:"null").",";
		$sql.= " fk_loc=".(isset($this->fk_loc)?$this->fk_loc:"null").",";
		$sql.= " fk_logement=".(isset($this->fk_logement)?$this->fk_logement:"null").",";
		$sql.= " fk_mandat=".(isset($this->fk_mandat)?$this->fk_mandat:"null").",";
		$sql.= " Type=".(isset($this->Type)?"'".$this->db->escape($this->Type)."'":"null").",";
		$sql.= " Date_location=".(dol_strlen($this->Date_location)!=0 ? "'".$this->db->idate($this->Date_location)."'" : 'null').",";
		$sql.= " Depot_garantie=".(isset($this->Depot_garantie)?"'".$this->db->escape($this->Depot_garantie)."'":"null").",";
		$sql.= " loy=".(isset($this->loy)?$this->loy:"null").",";
		$sql.= " date_entree=".(dol_strlen($this->date_entree)!=0 ? "'".$this->db->idate($this->date_entree)."'" : 'null').",";
		$sql.= " date_fin_preavis=".(dol_strlen($this->date_fin_preavis)!=0 ? "'".$this->db->idate($this->date_fin_preavis)."'" : 'null').",";
		$sql.= " date_fin=".(dol_strlen($this->date_fin)!=0 ? "'".$this->db->idate($this->date_fin)."'" : 'null').",";
		$sql.= " montant_tot=".(isset($this->montant_tot)?$this->montant_tot:"null").",";
		$sql.= " charges=".(isset($this->charges)?$this->charges:"null").",";
		$sql.= " date_der_rev=".(dol_strlen($this->date_der_rev)!=0 ? "'".$this->db->idate($this->date_der_rev)."'" : 'null').",";
		$sql.= " commentaire=".(isset($this->commentaire)?"'".$this->db->escape($this->commentaire)."'":"null").",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " entity=".(isset($this->entity)?$this->entity:"null")."";

        
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."immo_bails";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Immobails($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

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


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->fk_prop='';
		$this->fk_loc='';
		$this->fk_logement='';
		$this->fk_mandat='';
		$this->Type='';
		$this->Date_location='';
		$this->Depot_garantie='';
		$this->loy='';
		$this->date_entree='';
		$this->date_fin_preavis='';
		$this->date_fin='';
		$this->montant_tot='';
		$this->charges='';
		$this->date_der_rev='';
		$this->commentaire='';
		$this->tms='';
		$this->entity='';

		
	}

}