<?php
/* Copyright (C) 2004-2018 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013-2016 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro   <aspangaro@zendsi.com>
 * Copyright (C) 2018 Philippe GRAND 			<philippe.grand@atoo-net.com>
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
 * 	\defgroup   immobilier     Module Immobilier
 *  \brief      Immobilier module descriptor.
 *
 *  \file       htdocs/immobilier/core/modules/modImmobilier.class.php
 *  \ingroup    immobilier
 *  \brief      Description and activation file for module Immobilier
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


// The class name should start with a lower case mod for Dolibarr to pick it up
// so we ignore the Squiz.Classes.ValidClassName.NotCamelCaps rule.
// @codingStandardsIgnoreStart
/**
 *  Description and activation class for module Immobilier
 */
class modImmobilier extends DolibarrModules
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
		$this->rights_class = 'immobilier';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','interface','other'
		// It is used to group modules by family in module setup page
		$this->family = "financial";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';
		// Gives the possibility to the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));

		// Module label (no space allowed), used if translation string 'ModuleImmobilierName' not found (MyModue is name of module).
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleImmobilierDesc' not found (MyModue is name of module).
		$this->description = "ImmobilierDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "ImmobilierDescription (Long)";
		$editors = array('ATOO.NET', 'Jeffinfo SARL');
		$this->editor_name = implode (',',$editors);
		$editor_url = array('https://www.atoo-net.com', 'https://www.jeffinfo.com/');
		$this->editor_url = implode (', &nbsp;', $editor_url);

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Key used in llx_const table to save module status enabled/disabled (where IMMOBILIER is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='company';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /immobilier/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /immobilier/core/modules/barcode)
		// for specific css file (eg: /immobilier/css/immobilier.css.php)
		$this->module_parts = array(
		                        	'triggers' => 1,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
									'login' => 0,                                    	// Set this to 1 if module has its own login method file (core/login)
									'substitutions' => 1,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
									'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
									'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (theme)
		                        	'tpl' => 0,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
									'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
									'models' => 1,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
									'css' => array('/immobilier/css/immobilier.css.php'),	// Set this to relative path of css file if module has its own css file
	 								'js' => array('/immobilier/js/immobilier.js.php'),          // Set this to relative path of js file if module must load a js on all pages
									'hooks' => array('data'=>array('index', 'searchform', 'thirdpartycard','commcard','categorycard','contactcard','actioncard','agendathirdparty','projectthirdparty','infothirdparty','thirdpartybancard','consumptionthirdparty','thirdpartynotification','thirdpartymargins','thirdpartycustomerprice','searchform', 'globalcard'), 'entity'=>'0') 	// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
		                        );

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/immobilier/temp","/immobilier/subdir");
		$this->dirs = array (
			'/immobilier',
			'/immobilier/cost',
			'/immobilier/rent',
			'/immobilier/local',
			'/immobilier/photo',
			'/immobilier/property',
			'/immobilier/owner',
			'/immobilier/receipt',			
			'/immobilier/renter'
		);

		// Config pages. Put here list of php page, stored into immobilier/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@immobilier");

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = array();		// List of module class names as string that must be enabled if this module is enabled
		$this->requiredby = array();	// List of module ids to disable if this one is disabled
		$this->conflictwith = array();	// List of module class names as string this module is in conflict with
		$this->langfiles = array("immobilier@immobilier");
		$this->phpmin = array(5,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(4,0);	// Minimum version of Dolibarr required by module
		$this->warnings_activation = array();                     // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array();                 // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'ImmobilierWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('IMMOBILIER_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('IMMOBILIER_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$this->const = array(
			1=>array('IMMOBILIER_MYCONSTANT', 'chaine', 'avalue', 'This is a constant to add', 1, 'allentities', 1)
		);


		if (! isset($conf->immobilier) || ! isset($conf->immobilier->enabled))
		{
			$conf->immobilier=new stdClass();
			$conf->immobilier->enabled=0;
		}


		// Array to add new pages in new tabs
        $this->tabs = array();
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@immobilier:$user->rights->immobilier->read:/immobilier/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
        // $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@immobilier:$user->rights->othermodule->read:/immobilier/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        // $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
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
		$this->dictionaries=array();
        /* Example:
        $this->dictionaries=array(
            'langs'=>'mylangfile@immobilier',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->immobilier->enabled,$conf->immobilier->enabled,$conf->immobilier->enabled)												// Condition to show each dictionary
        );
        */


        // Boxes/Widgets
		// Add here list of php file(s) stored in immobilier/core/boxes that contains class to show a widget.
        $this->boxes = array(
        	0=>array('file'=>'immobilierwidget1.php@immobilier','note'=>'Widget provided by Immobilier','enabledbydefaulton'=>'Home'),
        	//1=>array('file'=>'immobilierwidget2.php@immobilier','note'=>'Widget provided by Immobilier'),
        	//2=>array('file'=>'immobilierwidget3.php@immobilier','note'=>'Widget provided by Immobilier')
        );


		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			0=>array('label'=>'MyJob label', 'jobtype'=>'method', 'class'=>'/immobilier/class/immorenter.class.php', 'objectname'=>'ImmoRenter', 'method'=>'doScheduledJob', 'parameters'=>'', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>true)
		);
		// Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>true),
		//                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>true)
		// );


		// Permissions
		$this->rights = array();		// Permission array used by this module

		$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read renter';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update renter';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete renter';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read owner';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update owner';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete owner';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read property';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update property';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete property';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read receipt';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update receipt';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete receipt';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete rent';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read payment';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update payment';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete payment';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read cost';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update cost';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete cost';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
			
		//$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read cost_type';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update cost_type';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete cost_type';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->immobilier->level1->level2)


		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus

		/* BEGIN MODULEBUILDER TOPMENU */
								
		$this->menu[$r++]=array('fk_menu'=>'',			                // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',			                // This is a Top menu entry
								'titre'=>'Properties',
								'mainmenu'=>'properties',
								'leftmenu'=>'',
								'url'=>'/immobilier/property/immoproperty_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->immobilier->enabled',	// Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=properties',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoProperty',
								'mainmenu'=>'properties',
								'leftmenu'=>'immobilier_immoproperty',
								'url'=>'/immobilier/property/immoproperty_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=properties,fk_leftmenu=immobilier_immoproperty',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoProperty',
								'mainmenu'=>'properties',
								'leftmenu'=>'immobilier_immoproperty',
								'url'=>'/immobilier/property/immoproperty_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=properties',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoProperty_Type',
								'mainmenu'=>'properties',
								'leftmenu'=>'immobilier_immoproperty_type',
								'url'=>'/immobilier/property/immoproperty_type_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=properties,fk_leftmenu=immobilier_immoproperty_type',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoProperty_Type',
								'mainmenu'=>'properties',
								'leftmenu'=>'immobilier_immoproperty_type',
								'url'=>'/immobilier/property/immoproperty_type_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1100+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',			                // This is a Left menu entry
								'titre'=>'ImmoRents',
								'mainmenu'=>'immorents',
								'leftmenu'=>'',
								'url'=>'/immobilier/rent/immorent_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoRent',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immorent',
								'url'=>'/immobilier/rent/immorent_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents,fk_leftmenu=immobilier_immorent',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoRent',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immorent',
								'url'=>'/immobilier/rent/immorent_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1200+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoOwner',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immoowner',
								'url'=>'/immobilier/owner/immoowner_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents,fk_leftmenu=immobilier_immoowner',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoOwner',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immoowner',
								'url'=>'/immobilier/owner/immoowner_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoOwner_Type',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immoowner_type',
								'url'=>'/immobilier/owner_type/immoowner_type_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents,fk_leftmenu=immobilier_immoowner_type',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoOwner_Type',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immoowner_type',
								'url'=>'/immobilier/owner_type/immoowner_type_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both 

		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoRenter',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immorenter',
								'url'=>'/immobilier/renter/immorenter_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immorents,fk_leftmenu=immobilier_immorenter',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoRenter',
								'mainmenu'=>'immorents',
								'leftmenu'=>'immobilier_immorenter',
								'url'=>'/immobilier/renter/immorenter_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1300+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		

		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT
		$this->menu[$r++]=array(	'fk_menu'=>'fk_mainmenu=immobilier',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoRenter',
								'mainmenu'=>'immobilier',
								'leftmenu'=>'immobilier_immorenter_list',
								'url'=>'/immobilier/immorenter_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1000+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(	'fk_menu'=>'fk_mainmenu=immobilier,fk_leftmenu=immobilier',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoRenter',
								'mainmenu'=>'immobilier',
								'leftmenu'=>'immobilier_immorenter_new',
								'url'=>'/immobilier/immorenter_page.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1000+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		*/
										
               		
		$this->menu[$r++]=array(
                				'fk_menu'=>'',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'top',			                // This is a Left menu entry
								'titre'=>'ImmoReceipts',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immoreceipt',
								'url'=>'/immobilier/receipt/immoreceipt_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both								
		
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoReceipt',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immoreceipt',
								'url'=>'/immobilier/receipt/immoreceipt_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=immobilier_immoreceipt',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoReceipt',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immoreceipt',
								'url'=>'/immobilier/receipt/immoreceipt_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both 		
		

		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoPayment',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immopayment',
								'url'=>'/immobilier/payment/immopayment_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=immobilier_immopayment',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoPayment',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immopayment',
								'url'=>'/immobilier/payment/immopayment_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immocost',
								'url'=>'/immobilier/cost/immocost_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=immobilier_immocost',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immocost',
								'url'=>'/immobilier/cost/immocost_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=immobilier_immocost',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'RenterCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immocost',
								'url'=>'/immobilier/cost/cost_renter.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=immobilier_immocost',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'StatisticsCost',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immocost',
								'url'=>'/immobilier/cost/stats.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
								
								
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'List ImmoCost_Type',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immocost_type',
								'url'=>'/immobilier/cost/immocost_type_list.php',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
		$this->menu[$r++]=array(
                				'fk_menu'=>'fk_mainmenu=immoreceipts,fk_leftmenu=immobilier_immocost_type',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
								'type'=>'left',			                // This is a Left menu entry
								'titre'=>'New ImmoCost_Type',
								'mainmenu'=>'immoreceipts',
								'leftmenu'=>'immobilier_immocost_type',
								'url'=>'/immobilier/cost/immocost_type_card.php?action=create',
								'langs'=>'immobilier@immobilier',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>1400+$r,
								'enabled'=>'$conf->immobilier->enabled',  // Define condition to show or hide menu entry. Use '$conf->immobilier->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
								'perms'=>'1',			                // Use 'perms'=>'$user->rights->immobilier->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
               		
               		
		/* END MODULEBUILDER LEFTMENU MYOBJECT */


		// Exports
		$r=1;

		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/*
		$langs->load("immobilier@immobilier");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='ImmoRenterLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='immorenter@immobilier';
		$keyforclass = 'ImmoRenter'; $keyforclassfile='/mymobule/class/immorenter.class.php'; $keyforelement='immorenter';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='immorenter'; $keyforaliasextra='extra'; $keyforelement='immorenter';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'immorenter as t';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('immorenter').')';
		$r++; */
		/* END MODULEBUILDER EXPORT MYOBJECT */
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
		$this->_load_tables('/immobilier/sql/');

		// Create extrafields
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		//$result1=$extrafields->addExtraField('myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'immobilier@immobilier', '$conf->immobilier->enabled');
		//$result2=$extrafields->addExtraField('myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'immobilier@immobilier', '$conf->immobilier->enabled');
		//$result3=$extrafields->addExtraField('myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'immobilier@immobilier', '$conf->immobilier->enabled');
		//$result4=$extrafields->addExtraField('myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1 '', 0, 0, '', '', 'immobilier@immobilier', '$conf->immobilier->enabled');
		//$result5=$extrafields->addExtraField('myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'immobilier@immobilier', '$conf->immobilier->enabled');

		$sql = array();

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
