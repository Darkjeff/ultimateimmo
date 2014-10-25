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
 *  \file       dev/skeletons/mandat.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2013-05-16 13:41
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Mandat extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='mandat';			//!< Id that identify managed objects
	var $table_element='mandat';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $ref_interne;
	var $fk_soc;
	var $fk_biens;
	var $date_contrat='';
	var $date_cloture='';
	var $status;
	var $mise_en_service='';
	var $fin_validite='';
	var $fk_bails;
	var $fk_commercial;
	var $notes_private;
	var $notes_public;
	var $fk_user_author;
	var $datec='';
	var $fk_user_mod;
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
        
		if (isset($this->ref_interne)) $this->ref_interne=trim($this->ref_interne);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->fk_biens)) $this->fk_biens=trim($this->fk_biens);
		if (isset($this->status)) $this->status=trim($this->status);
		if (isset($this->fk_bails)) $this->fk_bails=trim($this->fk_bails);
		if (isset($this->fk_commercial)) $this->fk_commercial=trim($this->fk_commercial);
		if (isset($this->notes_private)) $this->notes_private=trim($this->notes_private);
		if (isset($this->notes_public)) $this->notes_public=trim($this->notes_public);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->entity)) $this->entity=trim($this->entity);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."immo_mandat(";
		
		$sql.= "ref_interne,";
		$sql.= "fk_soc,";
		$sql.= "fk_biens,";
		$sql.= "date_contrat,";
		$sql.= "date_cloture,";
		$sql.= "status,";
		$sql.= "mise_en_service,";
		$sql.= "fin_validite,";
		$sql.= "fk_bails,";
		$sql.= "fk_commercial,";
		$sql.= "notes_private,";
		$sql.= "notes_public,";
		$sql.= "fk_user_author,";
		$sql.= "datec,";
		$sql.= "fk_user_mod,";
		$sql.= "entity";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->ref_interne)?'NULL':"'".$this->db->escape($this->ref_interne)."'").",";
		$sql.= " ".(! isset($this->fk_soc)?'NULL':"'".$this->fk_soc."'").",";
		$sql.= " ".(! isset($this->fk_biens)?'NULL':"'".$this->fk_biens."'").",";
		$sql.= " ".(! isset($this->date_contrat) || dol_strlen($this->date_contrat)==0?'NULL':$this->db->idate($this->date_contrat)).",";
		$sql.= " ".(! isset($this->date_cloture) || dol_strlen($this->date_cloture)==0?'NULL':$this->db->idate($this->date_cloture)).",";
		$sql.= " ".(! isset($this->status)?'NULL':"'".$this->status."'").",";
		$sql.= " ".(! isset($this->mise_en_service) || dol_strlen($this->mise_en_service)==0?'NULL':$this->db->idate($this->mise_en_service)).",";
		$sql.= " ".(! isset($this->fin_validite) || dol_strlen($this->fin_validite)==0?'NULL':$this->db->idate($this->fin_validite)).",";
		$sql.= " ".(! isset($this->fk_bails)?'NULL':"'".$this->fk_bails."'").",";
		$sql.= " ".(! isset($this->fk_commercial)?'NULL':"'".$this->fk_commercial."'").",";
		$sql.= " ".(! isset($this->notes_private)?'NULL':"'".$this->db->escape($this->notes_private)."'").",";
		$sql.= " ".(! isset($this->notes_public)?'NULL':"'".$this->db->escape($this->notes_public)."'").",";
		$sql.= " ".(! isset($this->fk_user_author)?'NULL':"'".$this->fk_user_author."'").",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':$this->db->idate($this->datec)).",";
		$sql.= " ".(! isset($this->fk_user_mod)?'NULL':"'".$this->fk_user_mod."'").",";
		$sql.= " ".(! isset($this->entity)?'NULL':"'".$this->entity."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."mandat");

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
		
		$sql.= " t.ref_interne,";
		$sql.= " t.fk_soc,";
		$sql.= " t.fk_biens,";
		$sql.= " t.date_contrat,";
		$sql.= " t.date_cloture,";
		$sql.= " t.status,";
		$sql.= " t.mise_en_service,";
		$sql.= " t.fin_validite,";
		$sql.= " t.fk_bails,";
		$sql.= " t.fk_commercial,";
		$sql.= " t.notes_private,";
		$sql.= " t.notes_public,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms,";
		$sql.= " t.entity";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."immo_mandat as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->ref_interne = $obj->ref_interne;
				$this->fk_soc = $obj->fk_soc;
				$this->fk_biens = $obj->fk_biens;
				$this->date_contrat = $this->db->jdate($obj->date_contrat);
				$this->date_cloture = $this->db->jdate($obj->date_cloture);
				$this->status = $obj->status;
				$this->mise_en_service = $this->db->jdate($obj->mise_en_service);
				$this->fin_validite = $this->db->jdate($obj->fin_validite);
				$this->fk_bails = $obj->fk_bails;
				$this->fk_commercial = $obj->fk_commercial;
				$this->notes_private = $obj->notes_private;
				$this->notes_public = $obj->notes_public;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
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
        
		if (isset($this->ref_interne)) $this->ref_interne=trim($this->ref_interne);
		if (isset($this->fk_soc)) $this->fk_soc=trim($this->fk_soc);
		if (isset($this->fk_biens)) $this->fk_biens=trim($this->fk_biens);
		if (isset($this->status)) $this->status=trim($this->status);
		if (isset($this->fk_bails)) $this->fk_bails=trim($this->fk_bails);
		if (isset($this->fk_commercial)) $this->fk_commercial=trim($this->fk_commercial);
		if (isset($this->notes_private)) $this->notes_private=trim($this->notes_private);
		if (isset($this->notes_public)) $this->notes_public=trim($this->notes_public);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->entity)) $this->entity=trim($this->entity);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."immo_mandat SET";
        
		$sql.= " ref_interne=".(isset($this->ref_interne)?"'".$this->db->escape($this->ref_interne)."'":"null").",";
		$sql.= " fk_soc=".(isset($this->fk_soc)?$this->fk_soc:"null").",";
		$sql.= " fk_biens=".(isset($this->fk_biens)?$this->fk_biens:"null").",";
		$sql.= " date_contrat=".(dol_strlen($this->date_contrat)!=0 ? "'".$this->db->idate($this->date_contrat)."'" : 'null').",";
		$sql.= " date_cloture=".(dol_strlen($this->date_cloture)!=0 ? "'".$this->db->idate($this->date_cloture)."'" : 'null').",";
		$sql.= " status=".(isset($this->status)?$this->status:"null").",";
		$sql.= " mise_en_service=".(dol_strlen($this->mise_en_service)!=0 ? "'".$this->db->idate($this->mise_en_service)."'" : 'null').",";
		$sql.= " fin_validite=".(dol_strlen($this->fin_validite)!=0 ? "'".$this->db->idate($this->fin_validite)."'" : 'null').",";
		$sql.= " fk_bails=".(isset($this->fk_bails)?$this->fk_bails:"null").",";
		$sql.= " fk_commercial=".(isset($this->fk_commercial)?$this->fk_commercial:"null").",";
		$sql.= " notes_private=".(isset($this->notes_private)?"'".$this->db->escape($this->notes_private)."'":"null").",";
		$sql.= " notes_public=".(isset($this->notes_public)?"'".$this->db->escape($this->notes_public)."'":"null").",";
		$sql.= " fk_user_author=".(isset($this->fk_user_author)?$this->fk_user_author:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " fk_user_mod=".(isset($this->fk_user_mod)?$this->fk_user_mod:"null").",";
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."immo_mandat";
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

		$object=new Mandat($this->db);

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
		
		$this->ref_interne='';
		$this->fk_soc='';
		$this->fk_biens='';
		$this->date_contrat='';
		$this->date_cloture='';
		$this->status='';
		$this->mise_en_service='';
		$this->fin_validite='';
		$this->fk_bails='';
		$this->fk_commercial='';
		$this->notes_private='';
		$this->notes_public='';
		$this->fk_user_author='';
		$this->datec='';
		$this->fk_user_mod='';
		$this->tms='';
		$this->entity='';

		
	}
    /**
     *  Return clicable link of object (with eventually picto)
     *
     *  @param      int     $withpicto      Add picto into link
     *  @param      string  $option         Where point the link
     *  @param      int     $maxlength      Maxlength of ref
     *  @return     string                  String with URL
     */
function getmandatUrl($withpicto=0,$option='',$maxlength=0)
    {
        global $langs;

        $result='';

       
        {
            $lien = '<a href="'.DOL_URL_ROOT.'/immobilier/mandat/fiche.php?id='.$this->id.'">';
            $lienfin='</a>';
        }
        $newref=$this->ref;
        if ($maxlength) $newref=dol_trunc($newref,$maxlength,'middle');

        if ($withpicto) {
            // TODO changer le picto
           $result.=($lien.img_object($langs->trans("mandat").' '.$this->ref,'mandat').$lienfin.' ');
            
        }
        $result.=$lien.$newref.$lienfin;
        return $result;
    }
}
