<?php
/* Copyright (C) 2004-2018 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013-2016 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015-2018 Alexandre Spangaro   <aspangaro@zendsi.com>
 * Copyright (C) 2018-2020 Philippe GRAND       <philippe.grand@atoo-net.com>
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
 * 	\defgroup   ultimateimmo     Module Ultimateimmo
 *  \brief      ultimateimmo 	 module descriptor.
 *
 *  \file       htdocs/ultimateimmo/core/modules/modUltimateimmo.class.php
 *  \ingroup    ultimateimmo
 *  \brief      Description and activation file for module Ultimateimmo
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


// The class name should start with a lower case mod for Dolibarr to pick it up
// so we ignore the Squiz.Classes.ValidClassName.NotCamelCaps rule.
// @codingStandardsIgnoreStart
/**
 *  Description and activation class for module Ultimateimmo
 */
class modUltimateimmo extends DolibarrModules
{
	// @codingStandardsIgnoreEnd
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs,$conf;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 113050;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'ultimateimmo';
		// Gives the possibility to the module, to provide his own family info and position of this family.
		$this->familyinfo = array(
            'atoonet' => array(
                'position' => '001',
                'label' => $langs->trans("AtooNet")
            )
        );

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','interface','other'
		// It is used to group modules by family in module setup page
		$this->family = "Atoo.Net";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '01';
		// Gives the possibility to the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));

		// Module label (no space allowed), used if translation string 'ModuleUltimateimmoName' not found (MyModue is name of module).
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleUltimateimmoDesc' not found (MyModue is name of module).
		$this->description = "Solution pour les professionnels de l'immobilier";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "Solution logicielle très puissante pour toutes les agences immobilières et autres professionnels de l'immobilier";
		$editors = array('ATOO.NET', 'Jeffinfo SARL');
		$this->editor_name = implode (',',$editors);
		$editor_url = array('https://www.atoo-net.com', 'https://www.jeffinfo.com/');
		$this->editor_url = implode (', &nbsp;', $editor_url);

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '10.0.x';
		// Key used in llx_const table to save module status enabled/disabled (where ULTIMATEIMMO is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='company';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /ultimateimmo/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /ultimateimmo/core/modules/barcode)
		// for specific css file (eg: /ultimateimmo/css/ultimateimmo.css.php)
		$this->module_parts = array(
									'triggers' => 1,								 	// Set this to 1 if module has its own trigger directory (core/triggers)
									'login' => 0,								    	// Set this to 1 if module has its own login method file (core/login)
									'substitutions' => 1,				            	// Set this to 1 if module has its own substitution function file (core/substitutions)
									'menus' => 0,								    	// Set this to 1 if module has its own menus handler directory (core/menus)
									'theme' => 0,								    	// Set this to 1 if module has its own theme directory (theme)
									'tpl' => 0,								      	// Set this to 1 if module overwrite template dir (core/tpl)
									'barcode' => 0,								  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
									'models' => 1,								   	// Set this to 1 if module has its own models directory (core/modules/xxx)
									'css' => array('/ultimateimmo/css/ultimateimmo.css.php'),	// Set this to relative path of css file if module has its own css file
	 								'js' => array('/ultimateimmo/js/ultimateimmo.js.php'),          // Set this to relative path of js file if module must load a js on all pages
									'hooks' => array('data'=>array('index', 'searchform', 'thirdpartycard','commcard','categorycard','contactcard','actioncard','agendathirdparty','projectthirdparty','infothirdparty','thirdpartybancard','consumptionthirdparty','thirdpartynotification','thirdpartymargins','thirdpartycustomerprice','searchform', 'globalcard'), 'entity'=>'0') 	// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
								);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/ultimateimmo/temp","/ultimateimmo/subdir");
		$this->dirs = array (
			'/ultimateimmo',
			'/ultimateimmo/cost',
			'/ultimateimmo/rent',
			'/ultimateimmo/local',
			'/ultimateimmo/photo',
			'/ultimateimmo/property',
			'/ultimateimmo/owner',
			'/ultimateimmo/receipt',
			'/ultimateimmo/renter'
		);

		// Config pages. Put here list of php page, stored into ultimateimmo/admin directory, to use to setup module.
		$this->config_page_url = array("immoreceipt.php@ultimateimmo");

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = array("modSociete", "modBanque");		// List of module class names as string that must be enabled if this module is enabled
		$this->requiredby = array();	// List of module ids to disable if this one is disabled
		$this->conflictwith = array();	// List of module class names as string this module is in conflict with
		$this->langfiles = array("ultimateimmo@ultimateimmo");
		$this->phpmin = array(5,5);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(9,0);	// Minimum version of Dolibarr required by module
		$this->warnings_activation = array();				     // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array();				 // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'UltimateimmoWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('ULTIMATEIMMO_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//							   1=>array('ULTIMATEIMMO_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$this->const = array();
		$r=0;

		$this->const[$r][0] = "ULTIMATEIMMO_ADDON_PDF";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "quittance";
		$this->const[$r][3] = 'Name of the ultimateimmo generation manager in PDF format';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATEIMMO_ADDON_NUMBER";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "mod_ultimateimmo_standard";
		$this->const[$r][3] = 'Name for numbering manager for ultimateimmo';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATEIMMO_ADDON_PDF_ODT_PATH";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "DOL_DATA_ROOT/doctemplates/ultimateimmo";
		$this->const[$r][3] = "";
		$this->const[$r][4] = 0;
		$r++;

		if (! isset($conf->ultimateimmo) || ! isset($conf->ultimateimmo->enabled))
		{
			$conf->ultimateimmo=new stdClass();
			$conf->ultimateimmo->enabled=0;
		}


		// Array to add new pages in new tabs
		$this->tabs = array();
		// Example:
		//$this->tabs[] = array('data'=>'thirdparty:+tabimmoowner_card:ImmoOwner:ultimateimmo@ultimateimmo:/ultimateimmo/owner/immoowner_card.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@ultimateimmo:$user->rights->othermodule->read:/ultimateimmo/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');												     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view


		// Dictionaries
		$this->dictionaries=array(
			'langs'=>'ultimateimmo@ultimateimmo',
			'tabname'=>array(
				MAIN_DB_PREFIX."c_ultimateimmo_diagnostic", 
				MAIN_DB_PREFIX."c_ultimateimmo_immorent_type",
				MAIN_DB_PREFIX."c_ultimateimmo_immoproperty_type",
				MAIN_DB_PREFIX."c_ultimateimmo_juridique",
				MAIN_DB_PREFIX."c_ultimateimmo_builtdate"
			),
			'tablib'=>array("DiagnosticImmo", 
							"ImmorentType",
							"ImmoProperty_Type",
							"Juridique",
							"BuiltDate"
							),
			'tabsql'=>array('SELECT d.rowid as rowid, d.code, d.label, d.active FROM '.MAIN_DB_PREFIX.'c_ultimateimmo_diagnostic as d', 
			'SELECT t.rowid as rowid, t.code, t.label, t.active FROM '.MAIN_DB_PREFIX.'c_ultimateimmo_immorent_type as t',
			'SELECT tp.rowid as rowid, tp.code, tp.label, tp.active FROM '.MAIN_DB_PREFIX.'c_ultimateimmo_immoproperty_type as tp',
			'SELECT t.rowid as rowid, t.code, t.label, t.active FROM '.MAIN_DB_PREFIX.'c_ultimateimmo_juridique as t',
			'SELECT t.rowid as rowid, t.code, t.label, t.active FROM '.MAIN_DB_PREFIX.'c_ultimateimmo_builtdate as t'
			),
			'tabsqlsort'=>array(
				"label ASC", "label ASC", "label ASC", "label ASC", "label ASC"
			),
			'tabfield'=>array(
				"code,label", "code,label", "code,label", "code,label", "code,label"
			),
			'tabfieldvalue'=>array(
				"code,label", "code,label", "code,label", "code,label", "code,label"
			),
			'tabfieldinsert'=>array(
				"code,label", "code,label", "code,label", "code,label", "code,label"
			),
			'tabrowid'=>array(
				"rowid", "rowid", "rowid", "rowid", "rowid"
			),
			'tabcond'=>array(
				$conf->ultimateimmo->enabled, 
				$conf->ultimateimmo->enabled,
				$conf->ultimateimmo->enabled,
				$conf->ultimateimmo->enabled,
				$conf->ultimateimmo->enabled
			)
		);

		// Boxes/Widgets
		// Add here list of php file(s) stored in ultimateimmo/core/boxes that contains class to show a widget.
		$this->boxes = array(
			0=>array('file'=>'box_immorenter.php','note'=>'Widget provided by Ultimateimmo','enabledbydefaulton'=>'Home')
			//1=>array('file'=>'immobilierwidget2.php@ultimateimmo','note'=>'Widget provided by ultimateimmo'),
			//2=>array('file'=>'immobilierwidget3.php@ultimateimmo','note'=>'Widget provided by ultimateimmo')
		);


		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			0=>array('label'=>'MyJob label', 'jobtype'=>'method', 'class'=>'/ultimateimmo/class/immorenter.class.php', 'objectname'=>'ImmoRenter', 'method'=>'doScheduledJob', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>true)
		);
		// Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>true),
		//								1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>true)
		// );


		// Permissions
		$this->rights = array();		// Permission array used by this module

		$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read ultimateimmo';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update ultimateimmo';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete ultimateimmo';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
			
		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read renter';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update renter';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete renter';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read owner';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update owner';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete owner';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read property';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update property';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete property';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read receipt';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update receipt';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete receipt';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read payment';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update payment';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete payment';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read cost';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update cost';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete cost';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read cost_type';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update cost_type';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete cost_type';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->ultimateimmo->level1->level2)


		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus

		$this->menu[$r++]=array('fk_menu'=>'',							// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',							// This is a Top menu entry
								'titre'=>'ImmoProperties',
								'mainmenu'=>'properties',
								'leftmenu'=>'',
								'url'=>'/ultimateimmo/property/immoproperty_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',	// Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=properties',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuImmoProperty',
								'mainmenu'=>'properties',
								'leftmenu'=>'ultimateimmo_immoproperty',
								'url'=>'/ultimateimmo/property/immoproperty_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=properties,fk_leftmenu=ultimateimmo_immoproperty',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoProperty',
								'mainmenu'=>'properties',
								'leftmenu'=>'ultimateimmo_immoproperty_new',
								'url'=>'/ultimateimmo/property/immoproperty_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=properties,fk_leftmenu=ultimateimmo_immoproperty',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoProperty',
								'mainmenu'=>'properties',
								'leftmenu'=>'ultimateimmo_immoproperty_list',
								'url'=>'/ultimateimmo/property/immoproperty_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
								'fk_menu'=>'',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',							// This is a Left menu entry
								'titre'=>'MenuImmoOwner',
								'mainmenu'=>'immoowners',
								'leftmenu'=>'',
								'url'=>'/ultimateimmo/owner/immoowner_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoowners',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuImmoOwnerType',
								'mainmenu'=>'immoowners',
								'leftmenu'=>'ultimateimmo_immoowner_type',
								'url'=>'/ultimateimmo/owner_type/immoowner_type_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoowners,fk_leftmenu=ultimateimmo_immoowner_type',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoOwnerType',
								'mainmenu'=>'immoowners',
								'leftmenu'=>'ultimateimmo_immoowner_type_new',
								'url'=>'/ultimateimmo/owner_type/immoowner_type_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both 
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoowners,fk_leftmenu=ultimateimmo_immoowner_type',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoOwnerType',
								'mainmenu'=>'immoowners',
								'leftmenu'=>'ultimateimmo_immoowner_type_list',
								'url'=>'/ultimateimmo/owner_type/immoowner_type_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both 
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoowners',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuImmoOwner',
								'mainmenu'=>'immoowners',
								'leftmenu'=>'ultimateimmo_immoowner',
								'url'=>'/ultimateimmo/owner/immoowner_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoowners,fk_leftmenu=ultimateimmo_immoowner',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoOwner',
								'mainmenu'=>'immoowners',
								'leftmenu'=>'ultimateimmo_immoowner_new',
								'url'=>'/ultimateimmo/owner/immoowner_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoowners,fk_leftmenu=ultimateimmo_immoowner',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoOwner',
								'mainmenu'=>'immoowners',
								'leftmenu'=>'ultimateimmo_immoowner_list',
								'url'=>'/ultimateimmo/owner/immoowner_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
								'fk_menu'=>'',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',							// This is a Left menu entry
								'titre'=>'ImmoRents',
								'mainmenu'=>'immorents',
								'leftmenu'=>'',
								'url'=>'/ultimateimmo/rent/immorent_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immorents',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuImmoRent',
								'mainmenu'=>'immorents',
								'leftmenu'=>'ultimateimmo_immorent',
								'url'=>'/ultimateimmo/rent/immorent_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immorents,fk_leftmenu=ultimateimmo_immorent',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoRent',
								'mainmenu'=>'immorents',
								'leftmenu'=>'ultimateimmo_immorent_new',
								'url'=>'/ultimateimmo/rent/immorent_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immorents,fk_leftmenu=ultimateimmo_immorent',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoRent',
								'mainmenu'=>'immorents',
								'leftmenu'=>'ultimateimmo_immorent_list',
								'url'=>'/ultimateimmo/rent/immorent_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
								'fk_menu'=>'',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',							// This is a Left menu entry
								'titre'=>'MenuImmoRenter',
								'mainmenu'=>'immorenters',
								'leftmenu'=>'',
								'url'=>'/ultimateimmo/renter/immorenter_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immorenters',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuImmoRenter',
								'mainmenu'=>'immorenters',
								'leftmenu'=>'ultimateimmo_immorenter',
								'url'=>'/ultimateimmo/renter/immorenter_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immorenters,fk_leftmenu=ultimateimmo_immorenter',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoRenter',
								'mainmenu'=>'immorenters',
								'leftmenu'=>'ultimateimmo_immorenter_new',
								'url'=>'/ultimateimmo/renter/immorenter_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immorenters,fk_leftmenu=ultimateimmo_immorenter',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoRenter',
								'mainmenu'=>'immorenters',
								'leftmenu'=>'ultimateimmo_immorenter_list',
								'url'=>'/ultimateimmo/renter/immorenter_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immorenters,fk_leftmenu=ultimateimmo_immorenter',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuImmoRenterStats',
								'mainmenu'=>'immorenters',
								'leftmenu'=>'ultimateimmo_immorenter_stats',
								'url'=>'/ultimateimmo/renter/immorenter_stats.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
								'fk_menu'=>'',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',							// This is a Left menu entry
								'titre'=>'ImmoReceipts',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immoreceipt',
								'url'=>'/ultimateimmo/receipt/immoreceipt_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both								
		
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoReceipt',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immoreceipt',
								'url'=>'/ultimateimmo/receipt/immoreceipt_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immoreceipt',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoReceipt',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immoreceipt_create',
								'url'=>'/ultimateimmo/receipt/immoreceipt_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both 
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immoreceipt',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuAllReceiptperContract',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immoreceipt_createall',
								'url'=>'/ultimateimmo/receipt/immoreceipt_card.php?action=createall',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both 
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immoreceipt',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuValidateReceipt',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immoreceipt_validaterent',
								'url'=>'/ultimateimmo/receipt/immoreceipt_list.php?action=validaterent',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both 

		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immoreceipt',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuStatisticPayment',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immoreceipt',
								'url'=>'/ultimateimmo/receipt/payment/stats.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both 

		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuImmoPayment',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immopayment',
								'url'=>'/ultimateimmo/payment/immopayment_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immopayment',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoPayment',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immopayment_new',
								'url'=>'/ultimateimmo/payment/immopayment_card.php?action=createall',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immopayment',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoPayment',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immopayment_list',
								'url'=>'/ultimateimmo/payment/immopayment_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immocost',
								'url'=>'/ultimateimmo/cost/immocost_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immocost',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immocost_new_cost',
								'url'=>'/ultimateimmo/cost/immocost_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immocost',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuRenterCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immocost_renter_cost',
								'url'=>'/ultimateimmo/cost/cost_renter.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
								
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immocost',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuStatisticsCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immocost_stats_cost',
								'url'=>'/ultimateimmo/cost/stats.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immocost',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuStatisticsResult',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immocost_stats_result',
								'url'=>'/ultimateimmo/result/result.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
								
								
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuListImmoCostType',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immocost_type',
								'url'=>'/ultimateimmo/cost/immocost_type_list.php',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
								'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=ultimateimmo_immocost_type',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',							// This is a Left menu entry
								'titre'=>'MenuNewImmoCostType',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'ultimateimmo_immocost_type',
								'url'=>'/ultimateimmo/cost/immocost_type_card.php?action=create',
								'langs'=>'ultimateimmo@ultimateimmo',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1500+$r,
								'enabled'=>'$conf->ultimateimmo->enabled',  // Define condition to show or hide menu entry. Use '$conf->ultimateimmo->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',							// Use 'perms'=>'$user->rights->ultimateimmo->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
		
	}

	/**
	 *	Function called when module is enabled.
	 *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *	It also creates data directories
	 *
	 *	@param      string	$options    Options when enabling module ('', 'noboxes')
	 *	@return     int             	1 if OK, 0 if KO
	 */
	public function init($options='')
	{
		global $langs;
		$this->_load_tables('/ultimateimmo/sql/');

		// Create extrafields
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		//$result1=$extrafields->addExtraField('myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'ultimateimmo@ultimateimmo', '$conf->ultimateimmo->enabled');
		//$result2=$extrafields->addExtraField('myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'ultimateimmo@ultimateimmo', '$conf->ultimateimmo->enabled');
		//$result3=$extrafields->addExtraField('myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'ultimateimmo@ultimateimmo', '$conf->ultimateimmo->enabled');
		//$result4=$extrafields->addExtraField('myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1 '', 0, 0, '', '', 'ultimateimmo@ultimateimmo', '$conf->ultimateimmo->enabled');
		//$result5=$extrafields->addExtraField('myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'ultimateimmo@ultimateimmo', '$conf->ultimateimmo->enabled');

		$sql = array(
				"INSERT IGNORE INTO ".MAIN_DB_PREFIX."c_ultimateimmo_immoreceipt_status (rowid, code, label, active) VALUES
					(0, 'STATUS_DRAFT', '".$langs->trans("Draft")."', 1),
					(1, 'STATUS_VALIDATED', '".$langs->trans("Validate")."', 1);"
		);

		return $this->_init($sql, $options);
	}

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted
	 *
	 *	@param      string	$options    Options when enabling module ('', 'noboxes')
	 *	@return     int             	1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

}
