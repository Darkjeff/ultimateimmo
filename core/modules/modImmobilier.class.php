<?php
/* Copyright (C) 2010 Regis Houssin  <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \defgroup Immobilier Immobilier module
 * \brief Module to manage breakdown
 * \version	$Id: modVentilation.class.php,v 1.3 2010/05/04 06:37:58 hregis Exp $
 */

/**
 * \file htdocs/includes/modules/modVentilation.class.php
 * \ingroup compta
 * \brief Fichier de description et activation du module Immobilier
 */
include_once (DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php");

/**
 * \class modVentilation
 * \brief Classe de description et activation du module Ventilation
 */
class modImmobilier extends DolibarrModules {
	/**
	 * \brief	Constructeur.
	 * definit les noms, constantes et boites
	 * \param	DB	handler d'acces base
	 */
	function modImmobilier($DB) {
		$this->db = $DB;
		$this->numero = 161000;
		
		$this->family = "financial";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace ( '/^mod/i', '', get_class ( $this ) );
		$this->description = "Gestion immobilier";
		
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.0';
		
		$this->const_name = 'MAIN_MODULE_' . strtoupper ( $this->name );
		$this->special = 0;
		// $this->picto = '';
		
		// Defined if the directory /mymodule/inc/triggers/ contains triggers or not
		// $this->triggers = 1;
		
		// Data directories to create when module is enabled
		$this->dirs = array (
		'/immobilier',
		'/immobilier/locataire',
		'/immobilier/local',
		'/immobilier/photo',
		'/immobilier/immeuble',
		'/immobilier/contrat',
		'/immobilier/charge',
		'/immobilier/quittance'
		);
		
		// Config pages
		$this->config_page_url = array ();
		
		// Dependencies
		$this->depends = array (); // List of modules id that must be enabled if this module is enabled
		$this->requiredby = array (); // List of modules id to disable if this one is disabled
		$this->phpmin = array (
		5,
		2 
		); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array (
		3,
		3 
		); // Minimum version of Dolibarr required by module
		$this->langfiles = array (
		"immobilier@immobilier" 
		);
		
		
		// Dictionnaries
		$this->dictionnaries=array(
			'langs'=>'immobilier@immobilier',
			'tabname'=>array(MAIN_DB_PREFIX."immo_dict_type_compteur",MAIN_DB_PREFIX."immo_dict_type_letter"),		// List of tables we want to see into dictonnary editor
			'tablib'=>array("TypeCompteurDict","TypeLetterDict"),								// Label of tables
			'tabsql'=>array('SELECT f.rowid as rowid, f.intitule, f.sort, f.active FROM '.MAIN_DB_PREFIX.'immo_dict_type_compteur as f',
			'SELECT f.rowid as rowid, f.intitule, f.object , f.texte, f.sort, f.active FROM '.MAIN_DB_PREFIX.'immo_dict_type_letter as f'
			),	// Request to select fields
			'tabsqlsort'=>array('sort ASC','sort ASC'),					// Sort order
			'tabfield'=>array("intitule,sort","intitule,object,texte,sort"),						// List of fields (result of select to show dictionnary)
			'tabfieldvalue'=>array("intitule,sort","intitule,object,texte,sort"),				// List of fields (list of fields to edit a record)
			'tabfieldinsert'=>array("intitule,sort","intitule,object,texte,sort"),				// List of fields (list of fields for insert)
			'tabrowid'=>array("rowid","rowid"),										// Name of columns with primary key (try to always name it 'rowid')
			'tabcond'=>array('$conf->immobilier->enabled','$conf->immobilier->enabled')	// Condition to show each dictionnary
		);
		
		
		// Constantes
		$this->const = array ();
		
		// Boxes
		$this->boxes = array ();
		
		// Permissions
		$this->rights = array ();
		
		// Main menu entries
		$this->menus = array (); // List of menus to add
		$r = 0;
		
		$this->menu [$r] = array (
		'fk_menu' => 0,
		'type' => 'top',
		'titre' => 'Immobilier',
		'mainmenu' => 'immobilier',
		'leftmenu' => '1',
		'url' => '/immobilier/loyermois.php',
		'langs' => 'immobilier@immobilier',
		'position' => 100,
		'perms' => 1,
		'enabled' => '$conf->immobilier->enabled',
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=0',
		'type' => 'left',
		'titre' => 'ParcImmobilier',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/local_all.php',
		'langs' => 'immobilier@immobilier',
		'position' => 101,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=1',
		'type' => 'left',
		'titre' => 'Properties',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/immeuble.php',
		'langs' => 'immobilier@immobilier',
		'position' => 102,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=1',
		'type' => 'left',
		'titre' => 'Apart',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/local.php',
		'langs' => 'immobilier@immobilier',
		'position' => 103,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=0',
		'type' => 'left',
		'titre' => 'Renter',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/locataire.php',
		'langs' => 'immobilier@immobilier',
		'position' => 110,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=4',
		'type' => 'left',
		'titre' => 'Actif',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/locataire.php',
		'langs' => 'immobilier@immobilier',
		'position' => 111,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=4',
		'type' => 'left',
		'titre' => 'Out',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/locataire_past.php',
		'langs' => 'immobilier@immobilier',
		'position' => 112,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=0',
		'type' => 'left',
		'titre' => 'Contracts',
		'mainmenu' => 'ventilation',
		'url' => '/immobilier/contrat.php',
		'langs' => 'immobilier@immobilier',
		'position' => 120,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=7',
		'type' => 'left',
		'titre' => 'Actif',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/contrat.php',
		'langs' => 'immobilier@immobilier',
		'position' => 121,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
		'fk_menu' => 'r=7',
		'type' => 'left',
		'titre' => 'History',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/contrat_past.php',
		'langs' => 'immobilier@immobilier',
		'position' => 122,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		$this->menu [$r] = array (
		'fk_menu' => 'r=0',
		'type' => 'left',
		'titre' => 'RentAndPayment',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/loyer.php',
		'langs' => 'immobilier@immobilier',
		'position' => 130,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		$this->menu [$r] = array (
		'fk_menu' => 'r=10',
		'type' => 'left',
		'titre' => 'RentPerMonth',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/loyermois.php',
		'langs' => 'immobilier@immobilier',
		'position' => 131,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		$this->menu [$r] = array (
		'fk_menu' => 'r=10',
		'type' => 'left',
		'titre' => 'PaymentPerMonth',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/paiement_mois.php',
		'langs' => 'immobilier@immobilier',
		'position' => 132,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		$this->menu [$r] = array (
		'fk_menu' => 'r=0',
		'type' => 'left',
		'titre' => 'CostAndCharges',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/charges.php',
		'langs' => 'immobilier@immobilier',
		'position' => 140,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
				$this->menu [$r] = array (
		'fk_menu' => 'r=13',
		'type' => 'left',
		'titre' => 'CostPerMonth',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/charge/chargemois.php',
		'langs' => 'immobilier@immobilier',
		'position' => 141,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
						$this->menu [$r] = array (
		'fk_menu' => 'r=13',
		'type' => 'left',
		'titre' => 'TypeCharge',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/charge/typecharge.php',
		'langs' => 'immobilier@immobilier',
		'position' => 142,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		$this->menu [$r] = array (
		'fk_menu' => 'r=0',
		'type' => 'left',
		'titre' => 'Tools',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/lignes.php',
		'langs' => 'immobilier@immobilier',
		'position' => 150,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
		$this->menu [$r] = array (
		'fk_menu' => 'r=16',
		'type' => 'left',
		'titre' => 'Result',
		'mainmenu' => 'immobilier',
		'url' => '/immobilier/resultat.php',
		'langs' => 'immobilier@immobilier',
		'position' => 151,
		'enabled' => 1,
		'perms' => 1,
		'target' => '',
		'user' => 0 
		);
		$r ++;
	}
	
	/**
	 * \brief Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 * It also creates data directories.
	 * \return int 1 if OK, 0 if KO
	 */
	function init() {
		$sql = array ();
		
		$result = $this->load_tables ();
		
		return $this->_init ( $sql );
	}
	
	/**
	 * \brief		Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted.
	 * \return int 1 if OK, 0 if KO
	 */
	function remove() {
		$sql = array ();
		
		return $this->_remove ( $sql );
	}
	
	/**
	 * \brief		Create tables and keys required by module
	 * This function is called by this->init.
	 * \return		int		<=0 if KO, >0 if OK
	 */
	function load_tables() {
		return $this->_load_tables ( '/immobilier/sql/' );
	}
}
?>
