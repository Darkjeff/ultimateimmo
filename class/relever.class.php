<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014 Florian Henry <florian.hery@open-concept.pro>
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
 *  \file       dev/skeletons/immorelever.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2014-03-03 11:06
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Immorelever extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='immorelever';			//!< Id that identify managed objects
	var $table_element='immo_relever';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $fk_compteur_local;
	var $date_reveler='';
	var $index_reveler;
	var $consomation_relever;
	var $comment_relever;
	
	var $lines=array();

    


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
        
		if (isset($this->fk_compteur_local)) $this->fk_compteur_local=trim($this->fk_compteur_local);
		if (isset($this->index_reveler)) $this->index_reveler=trim($this->index_reveler);
		if (isset($this->consomation_relever)) $this->consomation_relever=trim($this->consomation_relever);
		if (isset($this->comment_relever)) $this->comment_relever=trim($this->comment_relever);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."immo_relever(";
		
		$sql.= "fk_compteur_local,";
		$sql.= "date_reveler,";
		$sql.= "index_reveler,";
		$sql.= "consomation_relever,";
		$sql.= "comment_relever";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_compteur_local)?'NULL':"'".$this->fk_compteur_local."'").",";
		$sql.= " ".(! isset($this->date_reveler) || dol_strlen($this->date_reveler)==0?'NULL':$this->db->idate($this->date_reveler)).",";
		$sql.= " ".(! isset($this->index_reveler)?'NULL':"'".$this->index_reveler."'").",";
		$sql.= " ".(! isset($this->consomation_relever)?'NULL':"'".$this->consomation_relever."'").",";
		$sql.= " ".(! isset($this->comment_relever)?'NULL':"'".$this->db->escape($this->comment_relever)."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."immo_relever");

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
		
		$sql.= " t.fk_compteur_local,";
		$sql.= " t.date_reveler,";
		$sql.= " t.index_reveler,";
		$sql.= " t.consomation_relever,";
		$sql.= " t.comment_relever";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."immo_relever as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->fk_compteur_local = $obj->fk_compteur_local;
				$this->date_reveler = $this->db->jdate($obj->date_reveler);
				$this->index_reveler = $obj->index_reveler;
				$this->consomation_relever = $obj->consomation_relever;
				$this->comment_relever = $obj->comment_relever;

                
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
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch_all_by_local($localid)
    {
    	global $langs;
    	$sql = "SELECT";
    	$sql.= " t.rowid,";
    
    	$sql.= " t.fk_compteur_local,";
    	$sql.= " t.date_reveler,";
    	$sql.= " t.index_reveler,";
    	$sql.= " t.consomation_relever,";
    	$sql.= " t.comment_relever,";
    	$sql.= " dict.code,";
    	$sql.= " cmpt.label";
    
    
    	$sql.= " FROM ".MAIN_DB_PREFIX."immo_relever as t";
    	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."immo_compteur_local as cmptloc ON cmptloc.rowid=t.fk_compteur_local";
    	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."immo_compteur as cmpt ON cmpt.rowid=cmptloc.fk_compteur";
    	$sql.= " INNER JOIN ".MAIN_DB_PREFIX."immo_dict_type_compteur as dict ON dict.rowid=cmpt.type";
    	$sql.= " WHERE cmptloc.fk_local = ".$localid;
    
    	dol_syslog(get_class($this)."::fetch_all_by_local sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		$num=$this->db->num_rows($resql);
    		$this->lines=array();
    		while ($obj = $this->db->fetch_object($resql))
    		{
    			$line = new  Immorelever($this->db);
    
    			$line->id    = $obj->rowid;
    			$line->label_compteur = $obj->code.'-'.$obj->label;
    			$line->fk_compteur_local = $obj->fk_compteur_local;
    			$line->date_reveler = $this->db->jdate($obj->date_reveler);
    			$line->index_reveler = $obj->index_reveler;
    			$line->consomation_relever = $obj->consomation_relever;
    			$line->comment_relever = $obj->comment_relever;
    
    			$this->lines[]=$line;
    
    		}
    		$this->db->free($resql);
    
    		return $num;
    	}
    	else
    	{
    		$this->error="Error ".$this->db->lasterror();
    		dol_syslog(get_class($this)."::fetch_all_by_local ".$this->error, LOG_ERR);
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
        
		if (isset($this->fk_compteur_local)) $this->fk_compteur_local=trim($this->fk_compteur_local);
		if (isset($this->index_reveler)) $this->index_reveler=trim($this->index_reveler);
		if (isset($this->consomation_relever)) $this->consomation_relever=trim($this->consomation_relever);
		if (isset($this->comment_relever)) $this->comment_relever=trim($this->comment_relever);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."immo_relever SET";
        
		$sql.= " fk_compteur_local=".(isset($this->fk_compteur_local)?$this->fk_compteur_local:"null").",";
		$sql.= " date_reveler=".(dol_strlen($this->date_reveler)!=0 ? "'".$this->db->idate($this->date_reveler)."'" : 'null').",";
		$sql.= " index_reveler=".(isset($this->index_reveler)?$this->index_reveler:"null").",";
		$sql.= " consomation_relever=".(isset($this->consomation_relever)?$this->consomation_relever:"null").",";
		$sql.= " comment_relever=".(isset($this->comment_relever)?"'".$this->db->escape($this->comment_relever)."'":"null")."";

        
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."immo_relever";
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

		$object=new Immorelever($this->db);

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
		
		$this->fk_compteur_local='';
		$this->date_reveler='';
		$this->index_reveler='';
		$this->consomation_relever='';
		$this->comment_relever='';	
	}
}
