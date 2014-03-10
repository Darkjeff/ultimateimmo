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
 *  \file       dev/skeletons/quitance.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2013-11-01 12:06
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Quitance extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='quitance';			//!< Id that identify managed objects
	var $table_element='quitance';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $date='';
	var $date_de='';
	var $date_fin='';
	var $fk_loc;
	var $fk_bails;
	var $aquiter;
	var $total_ht;
	var $fk_payement;

    


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
        
		if (isset($this->fk_loc)) $this->fk_loc=trim($this->fk_loc);
		if (isset($this->fk_bails)) $this->fk_bails=trim($this->fk_bails);
		if (isset($this->aquiter)) $this->aquiter=trim($this->aquiter);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->fk_payement)) $this->fk_payement=trim($this->fk_payement);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."quitance(";
		
		$sql.= "rowid,";
		$sql.= "date,";
		$sql.= "date_de,";
		$sql.= "date_fin,";
		$sql.= "fk_loc,";
		$sql.= "fk_bails,";
		$sql.= "aquiter,";
		$sql.= "total_ht,";
		$sql.= "fk_payement";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->rowid)?'NULL':"'".$this->rowid."'").",";
		$sql.= " ".(! isset($this->date) || dol_strlen($this->date)==0?'NULL':$this->db->idate($this->date)).",";
		$sql.= " ".(! isset($this->date_de) || dol_strlen($this->date_de)==0?'NULL':$this->db->idate($this->date_de)).",";
		$sql.= " ".(! isset($this->date_fin) || dol_strlen($this->date_fin)==0?'NULL':$this->db->idate($this->date_fin)).",";
		$sql.= " ".(! isset($this->fk_loc)?'NULL':"'".$this->fk_loc."'").",";
		$sql.= " ".(! isset($this->fk_bails)?'NULL':"'".$this->fk_bails."'").",";
		$sql.= " ".(! isset($this->aquiter)?'NULL':"'".$this->aquiter."'").",";
		$sql.= " ".(! isset($this->total_ht)?'NULL':"'".$this->total_ht."'").",";
		$sql.= " ".(! isset($this->fk_payement)?'NULL':"'".$this->fk_payement."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."quitance");

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
		
		$sql.= " t.date,";
		$sql.= " t.date_de,";
		$sql.= " t.date_fin,";
		$sql.= " t.fk_loc,";
		$sql.= " t.fk_bails,";
		$sql.= " t.aquiter,";
		$sql.= " t.total_ht,";
		$sql.= " t.fk_payement";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."quitance as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->date = $this->db->jdate($obj->date);
				$this->date_de = $this->db->jdate($obj->date_de);
				$this->date_fin = $this->db->jdate($obj->date_fin);
				$this->fk_loc = $obj->fk_loc;
				$this->fk_bails = $obj->fk_bails;
				$this->aquiter = $obj->aquiter;
				$this->total_ht = $obj->total_ht;
				$this->fk_payement = $obj->fk_payement;

                
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
        
		if (isset($this->fk_loc)) $this->fk_loc=trim($this->fk_loc);
		if (isset($this->fk_bails)) $this->fk_bails=trim($this->fk_bails);
		if (isset($this->aquiter)) $this->aquiter=trim($this->aquiter);
		if (isset($this->total_ht)) $this->total_ht=trim($this->total_ht);
		if (isset($this->fk_payement)) $this->fk_payement=trim($this->fk_payement);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."quitance SET";
        
		$sql.= " date=".(dol_strlen($this->date)!=0 ? "'".$this->db->idate($this->date)."'" : 'null').",";
		$sql.= " date_de=".(dol_strlen($this->date_de)!=0 ? "'".$this->db->idate($this->date_de)."'" : 'null').",";
		$sql.= " date_fin=".(dol_strlen($this->date_fin)!=0 ? "'".$this->db->idate($this->date_fin)."'" : 'null').",";
		$sql.= " fk_loc=".(isset($this->fk_loc)?$this->fk_loc:"null").",";
		$sql.= " fk_bails=".(isset($this->fk_bails)?$this->fk_bails:"null").",";
		$sql.= " aquiter=".(isset($this->aquiter)?$this->aquiter:"null").",";
		$sql.= " total_ht=".(isset($this->total_ht)?$this->total_ht:"null").",";
		$sql.= " fk_payement=".(isset($this->fk_payement)?$this->fk_payement:"null")."";

        
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."quitance";
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

		$object=new Quitance($this->db);

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
		
		$this->date='';
		$this->date_de='';
		$this->date_fin='';
		$this->fk_loc='';
		$this->fk_bails='';
		$this->aquiter='';
		$this->total_ht='';
		$this->fk_payement='';

		
	}

/**
 *  Put here description of your class
 */
 }
{
class Quitancedet extends CommonObject
{
    var $db;                            //!< To store db handler
    var $error;                         //!< To return error code (or message)
    var $errors=array();                //!< To return several error codes (or messages)
    var $element='quitancedet';         //!< Id that identify managed objects
    var $table_element='quitancedet';       //!< Name of table without prefix where object is stored

    var $id;
    
    var $fk_quitance;
    var $ref_label;
    var $datec='';
    var $note;
    var $debit;
    var $credit;

    


    /**
     *  Constructor
     *
     *  @param  DoliDb      $db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param  User    $user        User that creates
     *  @param  int     $notrigger   0=launch triggers after, 1=disable triggers
     *  @return int                  <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
        global $conf, $langs;
        $error=0;

        // Clean parameters
        
        if (isset($this->fk_quitance)) $this->fk_quitance=trim($this->fk_quitance);
        if (isset($this->ref_label)) $this->ref_label=trim($this->ref_label);
        if (isset($this->note)) $this->note=trim($this->note);
        if (isset($this->debit)) $this->debit=trim($this->debit);
        if (isset($this->credit)) $this->credit=trim($this->credit);

        

        // Check parameters
        // Put here code to add control on parameters values

        // Insert request
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."quitance_det(";
        
        $sql.= "rowid,";
        $sql.= "fk_quitance,";
        $sql.= "ref_label,";
        $sql.= "datec,";
        $sql.= "note,";
        $sql.= "debit,";
        $sql.= "credit";

        
        $sql.= ") VALUES (";
        
        $sql.= " ".(! isset($this->rowid)?'NULL':"'".$this->rowid."'").",";
        $sql.= " ".(! isset($this->fk_quitance)?'NULL':"'".$this->fk_quitance."'").",";
        $sql.= " ".(! isset($this->ref_label)?'NULL':"'".$this->ref_label."'").",";
        $sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':$this->db->idate($this->datec)).",";
        $sql.= " ".(! isset($this->note)?'NULL':"'".$this->db->escape($this->note)."'").",";
        $sql.= " ".(! isset($this->debit)?'NULL':"'".$this->debit."'").",";
        $sql.= " ".(! isset($this->credit)?'NULL':"'".$this->credit."'")."";

        
        $sql.= ")";

        $this->db->begin();

        dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

        if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."quitance_det");

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
     *  @param  int     $id    Id object
     *  @return int             <0 if KO, >0 if OK
     */
    function fetch($id)
    {
        global $langs;
        $sql = "SELECT";
        $sql.= " t.rowid,";
        
        $sql.= " t.fk_quitance,";
        $sql.= " t.ref_label,";
        $sql.= " t.datec,";
        $sql.= " t.note,";
        $sql.= " t.debit,";
        $sql.= " t.credit";

        
        $sql.= " FROM ".MAIN_DB_PREFIX."quitance_det as t";
        $sql.= " WHERE t.rowid = ".$id;

        dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
                $this->fk_quitance = $obj->fk_quitance;
                $this->ref_label = $obj->ref_label;
                $this->datec = $this->db->jdate($obj->datec);
                $this->note = $obj->note;
                $this->debit = $obj->debit;
                $this->credit = $obj->credit;

                
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
     *  @param  User    $user        User that modifies
     *  @param  int     $notrigger   0=launch triggers after, 1=disable triggers
     *  @return int                  <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
        global $conf, $langs;
        $error=0;

        // Clean parameters
        
        if (isset($this->fk_quitance)) $this->fk_quitance=trim($this->fk_quitance);
        if (isset($this->ref_label)) $this->ref_label=trim($this->ref_label);
        if (isset($this->note)) $this->note=trim($this->note);
        if (isset($this->debit)) $this->debit=trim($this->debit);
        if (isset($this->credit)) $this->credit=trim($this->credit);

        

        // Check parameters
        // Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."quitance_det SET";
        
        $sql.= " fk_quitance=".(isset($this->fk_quitance)?$this->fk_quitance:"null").",";
        $sql.= " ref_label=".(isset($this->ref_label)?$this->ref_label:"null").",";
        $sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
        $sql.= " note=".(isset($this->note)?"'".$this->db->escape($this->note)."'":"null").",";
        $sql.= " debit=".(isset($this->debit)?$this->debit:"null").",";
        $sql.= " credit=".(isset($this->credit)?$this->credit:"null")."";

        
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
     *  @param  User    $user        User that deletes
     *  @param  int     $notrigger   0=launch triggers after, 1=disable triggers
     *  @return int                  <0 if KO, >0 if OK
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
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."quitance_det";
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
     *  Load an object from its id and create a new one in database
     *
     *  @param  int     $fromid     Id of object to clone
     *  @return int                 New id of clone
     */
    function createFromClone($fromid)
    {
        global $user,$langs;

        $error=0;

        $object=new Quitancedet($this->db);

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
     *  Initialise object with example values
     *  Id must be 0 if object instance is a specimen
     *
     *  @return void
     */
    function initAsSpecimen()
    {
        $this->id=0;
        
        $this->fk_quitance='';
        $this->ref_label='';
        $this->datec='';
        $this->note='';
        $this->debit='';
        $this->credit='';

        
    }

}

}
?>
