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
 *  \file       dev/skeletons/logement.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2013-05-16 11:44
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	pour gere les biens immo
 */
class Logement extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='logement';			//!< Id that identify managed objects
	var $table_element='logement';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $ref;
	var $adresse;
	var $town;
	var $fk_departement;
	var $fk_pays;
	var $fk_mandat;
	var $datec='';
	var $nb_piece;
	var $descriptif;
	var $superficie;
	var $dpe;
	var $loyer;
	var $charges;
	var $caution;
	var $Honoraire;
	var $Assurance;
	var $tms='';
	var $entity;
var $imgWidth;
    var $imgHeight;
    
var $nbphoto;

    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        $this->nbphoto = 0;
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
        
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->adresse)) $this->adresse=trim($this->adresse);
		if (isset($this->town)) $this->town=trim($this->town);
		if (isset($this->fk_departement)) $this->fk_departement=trim($this->fk_departement);
		if (isset($this->fk_pays)) $this->fk_pays=trim($this->fk_pays);
		if (isset($this->fk_mandat)) $this->fk_mandat=trim($this->fk_mandat);
		if (isset($this->nb_piece)) $this->nb_piece=trim($this->nb_piece);
		if (isset($this->descriptif)) $this->descriptif=trim($this->descriptif);
		if (isset($this->superficie)) $this->superficie=trim($this->superficie);
		if (isset($this->dpe)) $this->dpe=trim($this->dpe);
		if (isset($this->loyer)) $this->loyer=trim($this->loyer);
		if (isset($this->charges)) $this->charges=trim($this->charges);
		if (isset($this->caution)) $this->caution=trim($this->caution);
		if (isset($this->Honoraire)) $this->Honoraire=trim($this->Honoraire);
		if (isset($this->Assurance)) $this->Assurance=trim($this->Assurance);
		if (isset($this->entity)) $this->entity=trim($this->entity);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."logement(";
		
		$sql.= "ref,";
		$sql.= "adresse,";
		$sql.= "town,";
		$sql.= "fk_departement,";
		$sql.= "fk_pays,";
		$sql.= "fk_mandat,";
		$sql.= "datec,";
		$sql.= "nb_piece,";
		$sql.= "descriptif,";
		$sql.= "superficie,";
		$sql.= "dpe,";
		$sql.= "loyer,";
		$sql.= "charges,";
		$sql.= "caution,";
		$sql.= "Honoraire,";
		$sql.= "Assurance,";
		$sql.= "entity";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->adresse)?'NULL':"'".$this->db->escape($this->adresse)."'").",";
		$sql.= " ".(! isset($this->town)?'NULL':"'".$this->db->escape($this->town)."'").",";
		$sql.= " ".(! isset($this->fk_departement)?'NULL':"'".$this->fk_departement."'").",";
		$sql.= " ".(! isset($this->fk_pays)?'NULL':"'".$this->fk_pays."'").",";
		$sql.= " ".(! isset($this->fk_mandat)?'NULL':"'".$this->fk_mandat."'").",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?'NULL':$this->db->idate($this->datec)).",";
		$sql.= " ".(! isset($this->nb_piece)?'NULL':"'".$this->nb_piece."'").",";
		$sql.= " ".(! isset($this->descriptif)?'NULL':"'".$this->db->escape($this->descriptif)."'").",";
		$sql.= " ".(! isset($this->superficie)?'NULL':"'".$this->superficie."'").",";
		$sql.= " ".(! isset($this->dpe)?'NULL':"'".$this->db->escape($this->dpe)."'").",";
		$sql.= " ".(! isset($this->loyer)?'NULL':"'".$this->loyer."'").",";
		$sql.= " ".(! isset($this->charges)?'NULL':"'".$this->charges."'").",";
		$sql.= " ".(! isset($this->caution)?'NULL':"'".$this->caution."'").",";
		$sql.= " ".(! isset($this->Honoraire)?'NULL':"'".$this->Honoraire."'").",";
		$sql.= " ".(! isset($this->Assurance)?'NULL':"'".$this->Assurance."'").",";
		$sql.= " ".(! isset($this->entity)?'NULL':"'".$this->entity."'")."";

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."logement");

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
		
		$sql.= " t.ref,";
		$sql.= " t.adresse,";
		$sql.= " t.town,";
		$sql.= " t.fk_departement,";
		$sql.= " t.fk_pays,";
		$sql.= " t.fk_mandat,";
		$sql.= " t.datec,";
		$sql.= " t.nb_piece,";
		$sql.= " t.descriptif,";
		$sql.= " t.superficie,";
		$sql.= " t.dpe,";
		$sql.= " t.loyer,";
		$sql.= " t.charges,";
		$sql.= " t.caution,";
		$sql.= " t.Honoraire,";
		$sql.= " t.Assurance,";
		$sql.= " t.tms,";
		$sql.= " t.entity";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."logement as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->ref = $obj->ref;
				$this->adresse = $obj->adresse;
				$this->town = $obj->town;
				$this->fk_departement = $obj->fk_departement;
				$this->fk_pays = $obj->fk_pays;
				$this->fk_mandat = $obj->fk_mandat;
				$this->datec = $this->db->jdate($obj->datec);
				$this->nb_piece = $obj->nb_piece;
				$this->descriptif = $obj->descriptif;
				$this->superficie = $obj->superficie;
				$this->dpe = $obj->dpe;
				$this->loyer = $obj->loyer;
				$this->charges = $obj->charges;
				$this->caution = $obj->caution;
				$this->Honoraire = $obj->Honoraire;
				$this->Assurance = $obj->Assurance;
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
        
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->adresse)) $this->adresse=trim($this->adresse);
		if (isset($this->town)) $this->town=trim($this->town);
		if (isset($this->fk_departement)) $this->fk_departement=trim($this->fk_departement);
		if (isset($this->fk_pays)) $this->fk_pays=trim($this->fk_pays);
		if (isset($this->fk_mandat)) $this->fk_mandat=trim($this->fk_mandat);
		if (isset($this->nb_piece)) $this->nb_piece=trim($this->nb_piece);
		if (isset($this->descriptif)) $this->descriptif=trim($this->descriptif);
		if (isset($this->superficie)) $this->superficie=trim($this->superficie);
		if (isset($this->dpe)) $this->dpe=trim($this->dpe);
		if (isset($this->loyer)) $this->loyer=trim($this->loyer);
		if (isset($this->charges)) $this->charges=trim($this->charges);
		if (isset($this->caution)) $this->caution=trim($this->caution);
		if (isset($this->Honoraire)) $this->Honoraire=trim($this->Honoraire);
		if (isset($this->Assurance)) $this->Assurance=trim($this->Assurance);
		if (isset($this->entity)) $this->entity=trim($this->entity);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."logement SET";
        
		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " adresse=".(isset($this->adresse)?"'".$this->db->escape($this->adresse)."'":"null").",";
		$sql.= " town=".(isset($this->town)?"'".$this->db->escape($this->town)."'":"null").",";
		$sql.= " fk_departement=".(isset($this->fk_departement)?$this->fk_departement:"null").",";
		$sql.= " fk_pays=".(isset($this->fk_pays)?$this->fk_pays:"null").",";
		$sql.= " fk_mandat=".(isset($this->fk_mandat)?$this->fk_mandat:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " nb_piece=".(isset($this->nb_piece)?$this->nb_piece:"null").",";
		$sql.= " descriptif=".(isset($this->descriptif)?"'".$this->db->escape($this->descriptif)."'":"null").",";
		$sql.= " superficie=".(isset($this->superficie)?$this->superficie:"null").",";
		$sql.= " dpe=".(isset($this->dpe)?"'".$this->db->escape($this->dpe)."'":"null").",";
		$sql.= " loyer=".(isset($this->loyer)?$this->loyer:"null").",";
		$sql.= " charges=".(isset($this->charges)?$this->charges:"null").",";
		$sql.= " caution=".(isset($this->caution)?$this->caution:"null").",";
		$sql.= " Honoraire=".(isset($this->Honoraire)?$this->Honoraire:"null").",";
		$sql.= " Assurance=".(isset($this->Assurance)?$this->Assurance:"null").",";
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."logement";
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

		$object=new Logement($this->db);

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
		
		$this->ref='';
		$this->adresse='';
		$this->town='';
		$this->fk_departement='';
		$this->fk_pays='';
		$this->fk_mandat='';
		$this->datec='';
		$this->nb_piece='';
		$this->descriptif='';
		$this->superficie='';
		$this->dpe='';
		$this->loyer='';
		$this->charges='';
		$this->caution='';
		$this->Honoraire='';
		$this->Assurance='';
		$this->tms='';
		$this->entity='';

		
	}
   function liste_photos($dir,$nbmax=0)
    {
        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $nbphoto=0;
        $tabobj=array();

        $dir_osencoded=dol_osencode($dir);
        $handle=@opendir($dir_osencoded);
        if (is_resource($handle))
        {
            while (($file = readdir($handle)) != false)
            {
                if (! utf8_check($file)) $file=utf8_encode($file);  // readdir returns ISO
                if (dol_is_file($dir.$file) && preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $dir.$file))
                {
                    $nbphoto++;

                    // On determine nom du fichier vignette
                    $photo=$file;
                    $photo_vignette='';
                    if (preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $photo, $regs))
                    {
                        $photo_vignette=preg_replace('/'.$regs[0].'/i', '', $photo).'_small'.$regs[0];
                    }

                    $dirthumb = $dir.'thumbs/';

                    // Objet
                    $obj=array();
                    $obj['photo']=$photo;
                    if ($photo_vignette && dol_is_file($dirthumb.$photo_vignette)) $obj['photo_vignette']=$photo_vignette;
                    else $obj['photo_vignette']="";

                    $tabobj[$nbphoto-1]=$obj;

                    // On continue ou on arrete de boucler ?
                    if ($nbmax && $nbphoto >= $nbmax) break;
                }
            }

            closedir($handle);
        }

        return $tabobj;
    }

    /**
     *  Efface la photo du produit et sa vignette
     *
     *  @param  string      $file        Chemin de l'image
     *  @return void
     */
    function delete_photo($file)
    {
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $dir = dirname($file).'/'; // Chemin du dossier contenant l'image d'origine
        $dirthumb = $dir.'/thumbs/'; // Chemin du dossier contenant la vignette
        $filename = preg_replace('/'.preg_quote($dir,'/').'/i','',$file); // Nom du fichier

        // On efface l'image d'origine
        dol_delete_file($file);

        // Si elle existe, on efface la vignette
        if (preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i',$filename,$regs))
        {
            $photo_vignette=preg_replace('/'.$regs[0].'/i','',$filename).'_small'.$regs[0];
            if (file_exists(dol_osencode($dirthumb.$photo_vignette)))
            {
                dol_delete_file($dirthumb.$photo_vignette);
            }
        }
    }

    /**
     *  Load size of image file
     *
     *  @param  string  $file        Path to file
     *  @return void
     */
    function get_image_size($file)
    {
        $file_osencoded=dol_osencode($file);
        $infoImg = getimagesize($file_osencoded); // Get information on image
        $this->imgWidth = $infoImg[0]; // Largeur de l'image
        $this->imgHeight = $infoImg[1]; // Hauteur de l'image
    }
    
function add_photo($sdir, $file, $maxWidth = 160, $maxHeight = 120)
    {
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $dir = $sdir .'/'. get_exdir($this->id,2) . $this->id ."/photos";

        dol_mkdir($dir);

        $dir_osencoded=$dir;
        if (is_dir($dir_osencoded))
        {
            $originImage = $dir . '/' . $file['name'];

            // Cree fichier en taille origine
            $result=dol_move_uploaded_file($file['tmp_name'], $originImage, 1);

            if (file_exists(dol_osencode($originImage)))
            {
                // Cree fichier en taille vignette
                $this->add_thumb($originImage,$maxWidth,$maxHeight);
            }
        }
    }
    function show_photos($sdir,$size=0,$nbmax=0,$nbbyrow=5,$showfilename=0,$showaction=0,$maxHeight=120,$maxWidth=160)
    {
        global $conf,$user,$langs;

        include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
        include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';

        $pdir = get_exdir($this->id,2) . $this->id ."/photos/";
        $dir = $sdir . '/'. $pdir;
        $dirthumb = $dir.'thumbs/';
        $pdirthumb = $pdir.'thumbs/';

        $return ='<!-- Photo -->'."\n";
        $nbphoto=0;

        $dir_osencoded=dol_osencode($dir);
        if (file_exists($dir_osencoded))
        {
            $handle=opendir($dir_osencoded);
            if (is_resource($handle))
            {
                while (($file = readdir($handle)) != false)
                {
                    $photo='';

                    if (! utf8_check($file)) $file=utf8_encode($file);  // To be sure file is stored in UTF8 in memory

                    if (dol_is_file($dir.$file) && preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $dir.$file))
                    {
                        $nbphoto++;
                        $photo = $file;
                        $viewfilename = $file;

                        if ($size == 1) {   // Format vignette
                            // On determine nom du fichier vignette
                            $photo_vignette='';
                            if (preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $photo, $regs)) {
                                $photo_vignette=preg_replace('/'.$regs[0].'/i', '', $photo)."_small".$regs[0];
                                if (! dol_is_file($dirthumb.$photo_vignette)) $photo_vignette='';
                            }

                            // Get filesize of original file
                            $imgarray=dol_getImageSize($dir.$photo);

                            if ($nbbyrow && $nbphoto == 1) $return.= '<table width="100%" valign="top" align="center" border="0" cellpadding="2" cellspacing="2">';

                            if ($nbbyrow && ($nbphoto % $nbbyrow == 1)) $return.= '<tr align=center valign=middle border=1>';
                            if ($nbbyrow) $return.= '<td width="'.ceil(100/$nbbyrow).'%" class="photo">';

                            $return.= "\n";
                            $return.= '<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'" class="aphoto" target="_blank">';

                            // Show image (width height=$maxHeight)
                            // Si fichier vignette disponible et image source trop grande, on utilise la vignette, sinon on utilise photo origine
                            $alt=$langs->transnoentitiesnoconv('File').': '.$pdir.$photo;
                            $alt.=' - '.$langs->transnoentitiesnoconv('Size').': '.$imgarray['width'].'x'.$imgarray['height'];
                            if ($photo_vignette && $imgarray['height'] > $maxHeight) {
                                $return.= '<!-- Show thumb -->';
                                $return.= '<img class="photo" border="0" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdirthumb.$photo_vignette).'" title="'.dol_escape_htmltag($alt).'">';
                            }
                            else {
                                $return.= '<!-- Show original file -->';
                                $return.= '<img class="photo" border="0" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'" title="'.dol_escape_htmltag($alt).'">';
                            }

                            $return.= '</a>'."\n";

                            if ($showfilename) $return.= '<br>'.$viewfilename;
                            if ($showaction)
                            {
                                $return.= '<br>';
                                // On propose la generation de la vignette si elle n'existe pas et si la taille est superieure aux limites
                                if ($photo_vignette && preg_match('/(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$/i', $photo) && ($product->imgWidth > $maxWidth || $product->imgHeight > $maxHeight))
                                {
                                    $return.= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$_GET["id"].'&amp;action=addthumb&amp;file='.urlencode($pdir.$viewfilename).'">'.img_picto($langs->trans('GenerateThumb'),'refresh').'&nbsp;&nbsp;</a>';
                                }
                                if ($user->rights->produit->creer || $user->rights->service->creer)
                                {
                                    // Link to resize
                                   // $return.= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('gestimmo').'&id='.$_GET["id"].'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"),DOL_URL_ROOT.'/theme/common/transform-crop-and-resize','',1).'</a> &nbsp; ';

                                    // Link to delete
                                    $return.= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$_GET["id"].'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
                                    $return.= img_delete().'</a>';
                                }
                            }
                            $return.= "\n";

                            if ($nbbyrow) $return.= '</td>';
                            if ($nbbyrow && ($nbphoto % $nbbyrow == 0)) $return.= '</tr>';

                        }

                        if ($size == 0) {     // Format origine
                            $return.= '<img class="photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'">';

                            if ($showfilename) $return.= '<br>'.$viewfilename;
                            if ($showaction)
                            {
                                if ($user->rights->produit->creer || $user->rights->service->creer)
                                {
                                    // Link to resize
                                    $return.= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('produit|service').'&id='.$_GET["id"].'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"),DOL_URL_ROOT.'/theme/common/transform-crop-and-resize','',1).'</a> &nbsp; ';

                                    // Link to delete
                                    $return.= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$_GET["id"].'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
                                    $return.= img_delete().'</a>';
                                }
                            }
                        }

                        // On continue ou on arrete de boucler ?
                        if ($nbmax && $nbphoto >= $nbmax) break;
                    }
                }
            }

            if ($nbbyrow && $size==1)
            {
                // Ferme tableau
                while ($nbphoto % $nbbyrow)
                {
                    $return.= '<td width="'.ceil(100/$nbbyrow).'%">&nbsp;</td>';
                    $nbphoto++;
                }

                if ($nbphoto) $return.= '</table>';
            }

            closedir($handle);
        }

        $this->nbphoto = $nbphoto;

        return $return;
    }
function is_photo_available($sdir)
    {
        include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';

        $pdir = get_exdir($this->id,2) . $this->id ."/photos/";
        $dir = $sdir . '/'. $pdir;

        $nbphoto=0;

        $dir_osencoded=dol_osencode($dir);
        if (file_exists($dir_osencoded))
        {
            $handle=opendir($dir_osencoded);
            if (is_resource($handle))
            {
                while (($file = readdir($handle)) != false)
                {
                    if (! utf8_check($file)) $file=utf8_encode($file);  // To be sure data is stored in UTF8 in memory
                    if (dol_is_file($dir.$file)) return true;
                }
            }
        }
        return false;
    }
function info($id)
    {
        global $langs;

        $sql = "SELECT";
        $sql.= " p.rowid, p.datec, p.tms, p.fk_user_author";
        $sql.= " FROM ".MAIN_DB_PREFIX."logement as p";
        $sql.= " WHERE p.rowid = ".$id;

        dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);
                $this->id = $obj->rowid;
                $this->date_creation = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->tms);
               // $this->user_modification = $obj->fk_user_mod;
                $this->user_creation = $obj->fk_user_author;
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
// update fk_mandat dans logement
  function update_fk_mandat($id_mandat)
    {
        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element.' set fk_mandat = '.$id_mandat;
        $sql.= ' WHERE rowid = '.$this->id;
        $this->db->begin();
        dol_syslog(get_class($this).'::update_fk_mandat sql='.$sql);
       // $this->db->begin();
        $result = $this->db->query($sql);
        
        if ($result)
        {
            $this->db->commit();   
            return 1;
            
        }
        else
        {
            $this->error=$this->db->lasterror();
            dol_syslog(get_class($this).'::update_fk_mandat '.$this->error);
            return -1;
        }
    }
    function getbiensUrl($withpicto=0,$option='',$maxlength=0)
    {
        global $langs;

        $result='';

       
        {
            $lien = '<a href="'.DOL_URL_ROOT.'/gestimmo/biens/fiche.php?id='.$this->id.'">';
            $lienfin='</a>';
        }
        $newref=$this->ref;
        if ($maxlength) $newref=dol_trunc($newref,$maxlength,'middle');

        if ($withpicto) {
            // TODO changer le picto
           $result.=($lien.img_object($langs->trans("ShowProduct").' '.$this->ref,'product').$lienfin.' ');
            
        }
        $result.=$lien.$newref.$lienfin;
        return $result;
    }

}
?>
    
