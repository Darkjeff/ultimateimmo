<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018 Philippe GRAND 	<philippe.grand@atoo-net.com>
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
 * \file        class/immoreceipt.class.php
 * \ingroup     immobilier
 * \brief       This file is a CRUD class file for ImmoReceipt (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ImmoReceipt
 */
class ImmoReceipt extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'immoreceipt';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'immobilier_immoreceipt';
	/**
	 * @var int  Does immoreceipt support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	 */
	public $ismultientitymanaged = 0;
	/**
	 * @var int  Does immoreceipt support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;
	/**
	 * @var string String with name of icon for immoreceipt. Must be the part after the 'object_' into object_immoreceipt.png
	 */
	public $picto = 'immoreceipt@immobilier';


	/**
	 *  'type' if the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'position'=>1, 'notnull'=>1, 'index'=>1, 'comment'=>"Id",),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1, 'searchall'=>1, 'comment'=>"Reference of object", 'showoncombobox'=>'1',),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>30, 'notnull'=>-1, 'searchall'=>1, 'help'=>"Help text",),
		'rentamount' => array('type'=>'double(24,8)', 'label'=>'RentAmount', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'isameasure'=>'1',),
		'chargesamount' => array('type'=>'double(24,8)', 'label'=>'ChargesAmount', 'enabled'=>1, 'visible'=>1, 'position'=>42, 'notnull'=>-1, 'isameasure'=>'1', 'help'=>"Help text",),
		'total_amount' => array('type'=>'double(24,8)', 'label'=>'TotalAmount', 'enabled'=>1, 'visible'=>1, 'position'=>40, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'balance' => array('type'=>'double(24,8)', 'label'=>'Balance', 'enabled'=>1, 'visible'=>1, 'position'=>41, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'paiepartiel' => array('type'=>'double(24,8)', 'label'=>'PaiePartiel', 'enabled'=>1, 'visible'=>1, 'position'=>42, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'echeance' => array('type'=>'double(24,8)', 'label'=>'Echeance', 'enabled'=>1, 'visible'=>1, 'position'=>43, 'notnull'=>-1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help text",),
		'vat' => array('type'=>'double(24,8)', 'label'=>'Vat', 'enabled'=>1, 'visible'=>1, 'position'=>48, 'notnull'=>-1,),
		'paye' => array('type'=>'integer', 'label'=>'Paye', 'enabled'=>1, 'visible'=>-1, 'position'=>49, 'notnull'=>-1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes')),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php', 'label'=>'ThirdParty', 'enabled'=>1, 'visible'=>1, 'position'=>50, 'notnull'=>-1, 'index'=>1, 'help'=>"LinkToThirparty",),
		'fk_contract' => array('type'=>'integer', 'label'=>'Contract', 'enabled'=>1, 'visible'=>1, 'position'=>52, 'notnull'=>-1,),
		'fk_property' => array('type'=>'integer', 'label'=>'Property', 'enabled'=>1, 'visible'=>1, 'position'=>54, 'notnull'=>-1,),
		'fk_renter' => array('type'=>'integer', 'label'=>'Renter', 'enabled'=>1, 'visible'=>1, 'position'=>56, 'notnull'=>-1, 'index'=>1,),
		'fk_owner' => array('type'=>'integer', 'label'=>'Owner', 'enabled'=>1, 'visible'=>1, 'position'=>58, 'notnull'=>-1,),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>1, 'visible'=>-1, 'position'=>60, 'notnull'=>-1,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'visible'=>-1, 'position'=>61, 'notnull'=>-1,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'visible'=>-1, 'position'=>62, 'notnull'=>-1,),
		'date_rent' => array('type'=>'date', 'label'=>'DateRent', 'enabled'=>1, 'visible'=>-1, 'position'=>70, 'notnull'=>1,),
		'date_start' => array('type'=>'date', 'label'=>'DateStart', 'enabled'=>1, 'visible'=>-1, 'position'=>72, 'notnull'=>1,),
		'date_end' => array('type'=>'date', 'label'=>'DateEnd', 'enabled'=>1, 'visible'=>-1, 'position'=>74, 'notnull'=>1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'position'=>500, 'notnull'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'position'=>501, 'notnull'=>1,),
		'fk_user_creat' => array('type'=>'integer', 'label'=>'UserAuthor', 'enabled'=>1, 'visible'=>-2, 'position'=>510, 'notnull'=>1, 'foreignkey'=>'llx_user.rowid',),
		'fk_user_modif' => array('type'=>'integer', 'label'=>'UserModif', 'enabled'=>1, 'visible'=>-2, 'position'=>511, 'notnull'=>-1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'visible'=>-2, 'position'=>1000, 'notnull'=>-1,),
		'model_pdf' => array('type'=>'varchar(128)', 'label'=>'ModelPdf', 'enabled'=>1, 'visible'=>1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'searchall'=>1,),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>1, 'visible'=>1, 'position'=>1000, 'notnull'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Active', '-1'=>'Cancel')),
	);
	public $rowid;
	public $ref;
	public $label;
	public $rentamount;
	public $chargesamount;
	public $total_amount;
	public $balance;
	public $paiepartiel;
	public $vat;
	public $echeance;
	public $paye;
	public $fk_soc;
	public $fk_contract;
	public $fk_property;
	public $fk_renter;
	public $fk_owner;
	public $description;
	public $note_public;
	public $note_private;
	public $date_rent;
	public $date_start;
	public $date_end;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $model_pdf;
	public $status;
	
	
	public $renter_id;
	public $nomlocataire;
	public $prenomlocataire;
	public $emaillocataire;
	public $nomlocal;
	public $property_id;
	// END MODULEBUILDER PROPERTIES



	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	//public $table_element_line = 'immoreceiptdet';
	/**
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	//public $fk_element = 'fk_immoreceipt';
	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	//public $class_element_line = 'ImmoReceiptline';
	/**
	 * @var array  Array of child tables (child tables to delete before deleting a record)
	 */
	//protected $childtables=array('immoreceiptdet');
	/**
	 * @var ImmoReceiptLine[]     Array of subtable lines
	 */
	//public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $user;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible']=0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled']=0;

		// Unset fields that are disabled
		foreach($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone and object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $hookmanager, $langs;
	    $error = 0;

	    dol_syslog(__METHOD__, LOG_DEBUG);

	    $object = new self($this->db);

	    $this->db->begin();

	    // Load source object
	    $object->fetchCommon($fromid);
	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);

	    // Clear fields
	    $object->ref = "copy_of_".$object->ref;
	    $object->title = $langs->trans("CopyOf")." ".$object->title;
	    // ...

	    // Create clone
		$object->context['createfromclone'] = 'createfromclone';
	    $result = $object->createCommon($user);
	    if ($result < 0) {
	        $error++;
	        $this->error = $object->error;
	        $this->errors = $object->errors;
	    }

	    // End
	    if (!$error) {
	        $this->db->commit();
	        return $object;
	    } else {
	        $this->db->rollback();
	        return -1;
	    }
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.fk_contract,";
		$sql .= " t.fk_property,";
		$sql .= " t.label,";
		$sql .= " t.fk_renter,";
		$sql .= " t.total_amount,";
		$sql .= " t.rentamount,";
		$sql .= " t.balance,";
		$sql .= " t.paiepartiel,";
		$sql .= " t.chargesamount,";
		$sql .= " t.vat,";
		$sql .= " t.echeance,";
		$sql .= " t.note_public,";
		$sql .= " t.status,";
		$sql .= " t.date_rent,";
		$sql .= " t.date_start,";
		$sql .= " t.date_end,";
		$sql .= " t.fk_owner,";
		$sql .= " t.paye,";
		$sql .= " t.model_pdf,";
		$sql .= " lc.rowid as renter_id,";
		$sql .= " lc.lastname as nomlocataire,";
		$sql .= " lc.email as emaillocataire,";
		$sql .= " ll.label as nomlocal,";
		$sql .= " ll.rowid as property_id,";
		$sql .= " ic.tva as addtva";

		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immobilier_immorenter as lc ON t.fk_renter = lc.rowid';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immobilier_immoproperty as ll ON t.fk_property = ll.rowid';
		$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'immobilier_immocontrat as ic ON t.fk_contract = ic.rowid';
		
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

				$this->id				= $obj->rowid;

				$this->fk_contract		= $obj->fk_contract;
				$this->fk_property		= $obj->fk_property;
				$this->label			= $obj->label;
				$this->fk_renter		= $obj->fk_renter;
				$this->total_amount		= $obj->total_amount;
				$this->rentamount		= $obj->rentamount;
				$this->balance			= $obj->balance;
				$this->paiepartiel		= $obj->paiepartiel;
				$this->charges			= $obj->charges;
				$this->vat				= $obj->vat;
				$this->echeance			= $this->db->jdate($obj->echeance);
				$this->note_public		= $obj->note_public;
				$this->status			= $obj->status;
				$this->date_rent		= $this->db->jdate($obj->date_rent);
				$this->date_start		= $this->db->jdate($obj->date_start);
				$this->date_end			= $this->db->jdate($obj->date_end);
				$this->fk_owner			= $obj->fk_owner;
				$this->paye				= $obj->paye;
				$this->renter_id		= $obj->renter_id;
				$this->nomlocataire 	= $obj->nomlocataire;
				$this->emaillocataire	= $obj->emaillocataire;
				$this->nomlocal			= $obj->nomlocal;
				$this->property_id		= $obj->property_id;
				$this->modelpdf			= $obj->model_pdf;
				$this->addtva			= $obj->addtva;
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
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	/*public function fetchLines()
	{
		$this->lines=array();

		// Load lines with object ImmoReceiptLine

		return count($this->lines)?1:0;
	}*/

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *	@param	string	$option						On what the link point to ('nolink', ...)
     *  @param	int  	$notooltip					1=Disable tooltip
     *  @param  string  $morecss            		Add more css on link
     *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *	@return	string								String with URL
	 */
	function getNomUrl($withpicto=0, $option='', $notooltip=0, $morecss='', $save_lastsearch_value=-1)
	{
		global $db, $conf, $langs;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;

        if (! empty($conf->dol_no_mouse_hover)) $notooltip=1;   // Force disable tooltips

        $result = '';
        $companylink = '';

        $label = '<u>' . $langs->trans("ImmoReceipt") . '</u>';
        $label.= '<br>';
        $label.= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $url = dol_buildpath('/immobilier/receipt/immoreceipt_card.php',1).'?id='.$this->id;

        if ($option != 'nolink')
        {
	        // Add param to save lastsearch_values or not
	        $add_save_lastsearch_values=($save_lastsearch_value == 1 ? 1 : 0);
	        if ($save_lastsearch_value == -1 && preg_match('/list\.php/',$_SERVER["PHP_SELF"])) $add_save_lastsearch_values=1;
	        if ($add_save_lastsearch_values) $url.='&save_lastsearch_values=1';
        }

        $linkclose='';
        if (empty($notooltip))
        {
            if (! empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
            {
                $label=$langs->trans("ShowImmoReceipt");
                $linkclose.=' alt="'.dol_escape_htmltag($label, 1).'"';
            }
            $linkclose.=' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose.=' class="classfortooltip'.($morecss?' '.$morecss:'').'"';
        }
        else $linkclose = ($morecss?' class="'.$morecss.'"':'');

		$linkstart = '<a href="'.$url.'"';
		$linkstart.=$linkclose.'>';
		$linkend='</a>';

		$result .= $linkstart;
		if ($withpicto) $result.=img_object(($notooltip?'':$label), ($this->picto?$this->picto:'generic'), ($notooltip?(($withpicto != 2) ? 'class="paddingright"' : ''):'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip?0:1);
		if ($withpicto != 2) $result.= $this->ref;
		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		return $result;
	}

	/**
	 *  Retourne le libelle du status d'un user (actif, inactif)
	 *
	 *  @param	int		$mode          0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *  @return	string 			       Label of status
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->status,$mode);
	}

	/**
	 *  Return the status
	 *
	 *  @param	int		$status        	Id status
	 *  @param  int		$mode          	0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       	Label of status
	 */
	static function LibStatut($status,$mode=0)
	{
		global $langs;

		if ($mode == 0)
		{
			$prefix='';
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 1)
		{
			if ($status == 1) return $langs->trans('Enabled');
			if ($status == 0) return $langs->trans('Disabled');
		}
		if ($mode == 2)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 3)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5');
		}
		if ($mode == 4)
		{
			if ($status == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($status == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 5)
		{
			if ($status == 1) return $langs->trans('Enabled').' '.img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return $langs->trans('Disabled').' '.img_picto($langs->trans('Disabled'),'statut5');
		}
		if ($mode == 6)
		{
			if ($status == 1) return $langs->trans('Enabled').' '.img_picto($langs->trans('Enabled'),'statut4');
			if ($status == 0) return $langs->trans('Disabled').' '.img_picto($langs->trans('Disabled'),'statut5');
		}
	}

	/**
	 *	Charge les informations d'ordre info dans l'objet commande
	 *
	 *	@param  int		$id       Id of order
	 *	@return	void
	 */
	function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql.= ' fk_user_creat, fk_user_modif';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql.= ' WHERE t.rowid = '.$id;
		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation   = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture   = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);

		}
		else
		{
			dol_print_error($this->db);
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
		$this->initAsSpecimenCommon();
	}


	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, paramerts come from the schedule job setup field 'Parameters'
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	//public function doScheduledJob($param1, $param2, ...)
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error='';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}
}

/**
 * Class ImmoReceiptLine. You can also remove this and generate a CRUD class for lines objects.
 */
/*
class ImmoReceiptLine
{
	// @var int ID
	public $id;
	// @var mixed Sample line property 1
	public $prop1;
	// @var mixed Sample line property 2
	public $prop2;
}
*/