<?php
/* Copyright (C) 2013-2016 Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file    htdocs/custom/modules/modImmobilier.class.php
 * \ingroup Immobilier
 * \brief   Fichier de description et activation du module Immobilier
 */
include_once (DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php");

/**
 * \class modVentilation
 * \brief Classe de description et activation du module Ventilation
 */
class modImmobilier extends DolibarrModules
{
	/**
	 * Constructor.
	 * Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db        	
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		
		$this->db = $db;

		$this->numero = 113050;
		$this->rights_class = 'immobilier';
		
		$this->family = "financial";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace ( '/^mod/i', '', get_class ( $this ) );
		$this->description = "Gestion locative immobilière";
		
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.0.0';
		
		$this->const_name = 'MAIN_MODULE_' . strtoupper ( $this->name );
		$this->special = 0;
		$this->picto = 'building@immobilier';
		
		// Data directories to create when module is enabled
		$this->dirs = array (
			'/immobilier',
			'/immobilier/renter',
			'/immobilier/local',
			'/immobilier/photo',
			'/immobilier/property',
			'/immobilier/contrat',
			'/immobilier/charge',
			'/immobilier/quittance'
		);
		
		// Config pages
		$this->config_page_url = array('public.php@immobilier');
		
		// Dependencies
		$this->depends = array (
				'modSociete',
				'modComptabilite',
				//'modFacture',
				//'modFournisseur',
				//'modAgenda',
				'modBanque'
		);
		
		// Dependencies
		$this->depends = array (); // List of modules id that must be enabled if this module is enabled
		$this->requiredby = array (); // List of modules id to disable if this one is disabled
		$this->phpmin = array (
			5,
			3 
		); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array (
			3,
			7 
		); // Minimum version of Dolibarr required by module
		$this->langfiles = array (
			"immobilier@immobilier" 
		);

		// Dictionnaries
		$this->dictionnaries=array(
			'langs'=>'immobilier@immobilier',
			'tabname'=>array(
				MAIN_DB_PREFIX."c_immo_type_property",
				MAIN_DB_PREFIX."c_immo_type_compteur",
				MAIN_DB_PREFIX."c_immo_type_letter"
			),
			'tablib'=>array(
				"TypePropertyDict",
				"TypeCompteurDict",
				"TypeLetterDict"
			),
			'tabsql'=>array(
				'SELECT id      as rowid, code, label, active FROM '.MAIN_DB_PREFIX.'c_immo_type_property',
				'SELECT f.rowid as rowid, f.intitule, f.sort, f.active FROM '.MAIN_DB_PREFIX.'c_immo_type_compteur as f',
				'SELECT f.rowid as rowid, f.intitule, f.object , f.texte, f.sort, f.active FROM '.MAIN_DB_PREFIX.'c_immo_type_letter as f'
			),
			'tabsqlsort'=>array(
				'id ASC',
				'sort ASC',
				'sort ASC'
			),
			'tabfield'=>array(
				"code,label",
				"intitule,sort",
				"intitule,object,texte,sort"
			),
			'tabfieldvalue'=>array(
				"code,label",
				"intitule,sort",
				"intitule,object,texte,sort"
			),
			'tabfieldinsert'=>array(
				"code,label",
				"intitule,sort",
				"intitule,object,texte,sort"
			),
			'tabrowid'=>array(
				"id",
				"rowid",
				"rowid"
			),
			'tabcond'=>array(
				'$conf->immobilier->enabled',
				'$conf->immobilier->enabled',
				'$conf->immobilier->enabled'
			)
		);

		// Constantes
		$this->const = array ();
		$r = 0;
		
		$r ++;
		$this->const [$r] [0] = "IMMOBILIER_LAST_VERSION_INSTALL";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = $this->version;
		$this->const [$r] [3] = 'Last version installed to know change table to execute';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 'allentities';
		$this->const [$r] [6] = 0;

		$r ++;
		$this->const [$r] [0] = "IMMOBILIER_CONTACT_USE_SEARCH_TO_SELECT";
		$this->const [$r] [1] = "yesno";
		$this->const [$r] [2] = '1';
		$this->const [$r] [3] = 'Search contact with combobox';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		$this->const [$r] [6] = 0;

		$r ++;
		$this->const [$r] [0] = "MAIN_USE_JQUERY_DATATABLES";
		$this->const [$r] [1] = "yesno";
		$this->const [$r] [2] = '1';
		$this->const [$r] [3] = 'Use JQuery Datatables';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		$this->const [$r] [6] = 0;

		$r ++;
		$this->const [$r] [0] = "GOOGLE_GMAPS_ZOOM_LEVEL";
		$this->const [$r] [1] = "chaine";
		$this->const [$r] [2] = '10';
		$this->const [$r] [3] = 'Zoom Level on Google Maps';
		$this->const [$r] [4] = 0;
		$this->const [$r] [5] = 0;
		$this->const [$r] [6] = 0;

		// Boxes
		$this->boxes = array ();
		
		// Permissions
		$this->rights = array(); // Permission array used by this module
		$r = 0;

		$this->rights[$r][0] = 1130501;
		$this->rights[$r][1] = 'See properties';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'property';
		$this->rights[$r][5] = 'read';
		$r ++;
		
		$this->rights[$r][0] = 1130502;
		$this->rights[$r][1] = 'Update properties';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'property';
		$this->rights[$r][5] = 'write';
		$r ++;
		
		$this->rights[$r][0] = 1130503;
		$this->rights[$r][1] = 'Delete properties';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'property';
		$this->rights[$r][5] = 'delete';
		$r ++;
		
		$this->rights[$r][0] = 1130504;
		$this->rights[$r][1] = 'Export properties';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'property';
		$this->rights[$r][5] = 'export';
		$r ++;

		$this->rights[$r][0] = 1130511;
		$this->rights[$r][1] = 'See renters';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'renter';
		$this->rights[$r][5] = 'read';
		$r ++;
		
		$this->rights[$r][0] = 1130512;
		$this->rights[$r][1] = 'Update renters';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'renter';
		$this->rights[$r][5] = 'write';
		$r ++;
		
		$this->rights[$r][0] = 1130513;
		$this->rights[$r][1] = 'Delete renters';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'renter';
		$this->rights[$r][5] = 'delete';
		$r ++;
		
		$this->rights[$r][0] = 1130514;
		$this->rights[$r][1] = 'Export renters';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'renter';
		$this->rights[$r][5] = 'export';
		$r ++;

		$this->rights[$r][0] = 1130521;
		$this->rights[$r][1] = 'See rents';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'rent';
		$this->rights[$r][5] = 'read';
		$r ++;
		
		$this->rights[$r][0] = 1130522;
		$this->rights[$r][1] = 'Update rents';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'rent';
		$this->rights[$r][5] = 'write';
		$r ++;
		
		$this->rights[$r][0] = 1130523;
		$this->rights[$r][1] = 'Delete rents';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'rent';
		$this->rights[$r][5] = 'delete';
		$r ++;
		
		$this->rights[$r][0] = 1130524;
		$this->rights[$r][1] = 'Export rents';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'rent';
		$this->rights[$r][5] = 'export';
		$r ++;
		
		// Main menu entries
		$this->menus = array (); // List of menus to add
		$r = 0;

		// Properties --------------------
		$this->menu [$r] = array (
				'fk_menu' => 0,
				'type' => 'top',
				'titre' => 'Biens',
				'mainmenu' => 'biens',
				'leftmenu' => '0',
				'url' => '/immobilier/property/list.php',
				'langs' => 'immobilier@immobilier',
				'position' => 100,
				'enabled' => '$user->rights->immobilier->property->read',
				'perms' => '$user->rights->immobilier->property->read',
				'target' => '',
				'user' => 2 
		);
		$r ++;

		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=biens',
			'type' => 'left',
			'titre' => 'Bien',
			'leftmenu' => 'bien',
			'url' => '/immobilier/property/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 101,
			'enabled' => '$user->rights->immobilier->property->read',
			'perms' => '$user->rights->immobilier->property->read',
			'target' => '',
			'user' => 0 
		);
		$r ++;

		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=biens,fk_leftmenu=bien',
			'type' => 'left',
			'titre' => 'NewProperty',
			'mainmenu' => 'biens',
			'url' => '/immobilier/property/card.php?action=create',
			'langs' => 'immobilier@immobilier',
			'position' => 102,
			'enabled' => '$user->rights->immobilier->property->write',
			'perms' => '$user->rights->immobilier->property->write',
			'target' => '',
			'user' => 0
		);
		$r ++;

		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=biens,fk_leftmenu=bien',
			'type' => 'left',
			'titre' => 'List',
			'mainmenu' => 'biens',
			'url' => '/immobilier/property/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 103,
			'enabled' => '$user->rights->immobilier->property->read',
			'perms' => '$user->rights->immobilier->property->read',
			'target' => '',
			'user' => 0
		);
		$r ++;

		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=biens,fk_leftmenu=bien',
			'type' => 'left',
			'titre' => 'Statistics',
			'mainmenu' => 'biens',
			'url' => '/immobilier/property/stats.php',
			'langs' => 'immobilier@immobilier',
			'position' => 104,
			'enabled' => '$user->rights->immobilier->property->read',
			'perms' => '$user->rights->immobilier->property->read',
			'target' => '',
			'user' => 0
		);
		$r ++;

		// Renters --------------------
		$this->menu [$r] = array (
				'fk_menu' => 0,
				'type' => 'top',
				'titre' => 'Renters',
				'mainmenu' => 'renters',
				'leftmenu' => '0',
				'url' => '/immobilier/renter/list.php',
				'langs' => 'immobilier@immobilier',
				'position' => 200,
				'enabled' => '$user->rights->immobilier->renter->read',
				'perms' => '$user->rights->immobilier->renter->read',
				'target' => '',
				'user' => 2 
		);
		$r ++;

		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=renters',
			'type' => 'left',
			'titre' => 'Renter',
			'leftmenu' => 'renter',
			'url' => '/immobilier/renter/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 201,
			'enabled' => '$user->rights->immobilier->renter->read',
			'perms' => '$user->rights->immobilier->renter->read',
			'target' => '',
			'user' => 0 
		);
		$r ++;

		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=renters,fk_leftmenu=renter',
			'type' => 'left',
			'titre' => 'NewRenter',
			'mainmenu' => 'renters',
			'url' => '/immobilier/renter/card.php?action=create',
			'langs' => 'immobilier@immobilier',
			'position' => 202,
			'enabled' => '$user->rights->immobilier->renter->write',
			'perms' => '$user->rights->immobilier->renter->write',
			'target' => '',
			'user' => 0
		);
		$r ++;

		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=renters,fk_leftmenu=renter',
			'type' => 'left',
			'titre' => 'List',
			'mainmenu' => 'renters',
			'url' => '/immobilier/renter/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 203,
			'enabled' => '$user->rights->immobilier->renter->read',
			'perms' => '$user->rights->immobilier->renter->read',
			'target' => '',
			'user' => 0
		);
		$r ++;

		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=renters,fk_leftmenu=renter',
			'type' => 'left',
			'titre' => 'Statistics',
			'mainmenu' => 'renters',
			'url' => '/immobilier/renter/stats.php',
			'langs' => 'immobilier@immobilier',
			'position' => 204,
			'enabled' => '$user->rights->immobilier->renter->read',
			'perms' => '$user->rights->immobilier->renter->read',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		// Rents --------------------
		$this->menu [$r] = array (
				'fk_menu' => 0,
				'type' => 'top',
				'titre' => 'Rents',
				'mainmenu' => 'rents',
				'leftmenu' => '0',
				'url' => '/immobilier/rent/list.php',
				'langs' => 'immobilier@immobilier',
				'position' => 300,
				'enabled' => '$user->rights->immobilier->rent->read',
				'perms' => '$user->rights->immobilier->rent->read',
				'target' => '',
				'user' => 2 
		);
		$r ++;

		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=rents',
			'type' => 'left',
			'titre' => 'Rents',
			'leftmenu' => 'rents',
			'url' => '/immobilier/rent/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 301,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0 
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=rents,fk_leftmenu=rents',
			'type' => 'left',
			'titre' => 'NewRent',
			'mainmenu' => 'rents',
			'url' => '/immobilier/rent/card.php?action=create',
			'langs' => 'immobilier@immobilier',
			'position' => 302,
			'enabled' => '$user->rights->immobilier->rent->write',
			'perms' => '$user->rights->immobilier->rent->write',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=rents,fk_leftmenu=rents',
			'type' => 'left',
			'titre' => 'List',
			'mainmenu' => 'rents',
			'url' => '/immobilier/rent/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 303,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		// Receipt --------------------
		$this->menu [$r] = array (
				'fk_menu' => 0,
				'type' => 'top',
				'titre' => 'Receipt',
				'mainmenu' => 'receipt',
				'leftmenu' => '0',
				'url' => '/immobilier/receipt/list.php',
				'langs' => 'immobilier@immobilier',
				'position' => 400,
				'enabled' => '$user->rights->immobilier->rent>read',
				'perms' => '$user->rights->immobilier->rent->read',
				'target' => '',
				'user' => 2 
		);
		$r ++;

		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=receipt',
			'type' => 'left',
			'titre' => 'Receipt',
			'leftmenu' => 'receipt',
			'url' => '/immobilier/receipt/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 401,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0 
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=receipt',
			'type' => 'left',
			'titre' => 'NewReceipt',
			'mainmenu' => 'receipt',
			'url' => '/immobilier/receipt/card.php?action=create',
			'langs' => 'immobilier@immobilier',
			'position' => 402,
			'enabled' => '$user->rights->immobilier->rent->write',
			'perms' => '$user->rights->immobilier->rent->write',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=receipt',
			'type' => 'left',
			'titre' => 'allReceiptperContract',
			'mainmenu' => 'receipt',
			'url' => '/immobilier/receipt/card.php?action=createall',
			'langs' => 'immobilier@immobilier',
			'position' => 403,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=receipt',
			'type' => 'left',
			'titre' => 'List',
			'mainmenu' => 'receipt',
			'url' => '/immobilier/receipt/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 404,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=receipt',
			'type' => 'left',
			'titre' => 'Validate',
			'mainmenu' => 'receipt',
			'url' => '/immobilier/receipt/list.php?action=validaterent',
			'langs' => 'immobilier@immobilier',
			'position' => 406,
			'enabled' => '$user->rights->immobilier->rent->write',
			'perms' => '$user->rights->immobilier->rent->write',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=receipt',
			'type' => 'left',
			'titre' => 'Stats',
			'mainmenu' => 'receipt',
			'url' => '/immobilier/receipt/stats.php',
			'langs' => 'immobilier@immobilier',
			'position' => 407,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		// Payment --------------------
		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=receipt',
			'type' => 'left',
			'titre' => 'Payment',
			'leftmenu' => 'payment',
			'url' => '/immobilier/receipt/payment/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 501,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=payment',
			'type' => 'left',
			'titre' => 'NewPayment',
			'mainmenu' => 'payment',
			'url' => '/immobilier/receipt/payment/card.php?action=createall',
			'langs' => 'immobilier@immobilier',
			'position' => 502,
			'enabled' => '$user->rights->immobilier->rent->write',
			'perms' => '$user->rights->immobilier->rent->write',
			'target' => '',
			'user' => 0 
		);
		$r ++;
		
		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=payment',
			'type' => 'left',
			'titre' => 'List',
			'mainmenu' => 'payment',
			'url' => '/immobilier/receipt/payment/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 503,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0 
		);
		$r ++;
		
			$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=receipt,fk_leftmenu=payment',
			'type' => 'left',
			'titre' => 'stats',
			'mainmenu' => 'payment',
			'url' => '/immobilier/receipt/payment/stats.php',
			'langs' => 'immobilier@immobilier',
			'position' => 504,
			'enabled' => '$user->rights->immobilier->rent->read',
			'perms' => '$user->rights->immobilier->rent->read',
			'target' => '',
			'user' => 0 
		);		
		$r ++;
		
		
		// Charges --------------------
		$this->menu [$r] = array (
			'fk_menu' => 0,
			'type' => 'top',
			'titre' => 'RentalLoads',
			'mainmenu' => 'rentalloads',
			'leftmenu' => '0',
			'url' => '/immobilier/cost/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 600,
			'enabled' => '$user->rights->immobilier->renter->read',
			'perms' => '$user->rights->immobilier->renter->read',
			'target' => '',
			'user' => 2 
		);
		$r ++;
		
		$this->menu [$r] = array (
			'fk_menu' => 'fk_mainmenu=rentalloads',
			'type' => 'left',
			'titre' => 'RentalLoads',
			'leftmenu' => 'rentalloads',
			'url' => '/immobilier/cost/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 601,
			'enabled' => '$user->rights->immobilier->renter->read',
			'perms' => '$user->rights->immobilier->renter->read',
			'target' => '',
			'user' => 0 
		);
		$r ++;
		
		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=rentalloads,fk_leftmenu=rentalloads',
			'type' => 'left',
			'titre' => 'MenuNewRentalLoad',
			'mainmenu' => 'rentalloads',
			'url' => '/immobilier/cost/card.php?action=create',
			'langs' => 'immobilier@immobilier',
			'position' => 602,
			'enabled' => '$user->rights->immobilier->renter->write',
			'perms' => '$user->rights->immobilier->renter->write',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
			$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=rentalloads,fk_leftmenu=rentalloads',
			'type' => 'left',
			'titre' => 'List',
			'mainmenu' => 'rentalloads',
			'url' => '/immobilier/cost/list.php',
			'langs' => 'immobilier@immobilier',
			'position' => 603,
			'enabled' => '$user->rights->immobilier->renter->write',
			'perms' => '$user->rights->immobilier->renter->write',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
			
			$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=rentalloads,fk_leftmenu=rentalloads',
			'type' => 'left',
			'titre' => 'Stats',
			'mainmenu' => 'rentalloads',
			'url' => '/immobilier/cost/stats.php',
			'langs' => 'immobilier@immobilier',
			'position' => 604,
			'enabled' => '$user->rights->immobilier->renter->write',
			'perms' => '$user->rights->immobilier->renter->write',
			'target' => '',
			'user' => 0
		);
		$r ++;
		
		// Result --------------------
		$this->menu [$r] = array (
			'fk_menu' => 0,
			'type' => 'top',
			'titre' => 'Result',
			'mainmenu' => 'result',
			'leftmenu' => '0',
			'url' => '/immobilier/result/result.php',
			'langs' => 'immobilier@immobilier',
			'position' => 700,
			'enabled' => '$user->rights->immobilier->renter->read',
			'perms' => '$user->rights->immobilier->renter->read',
			'target' => '',
			'user' => 2 
		);
		$r ++;
		
		/*
		

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
			'titre' => 'CostPerSupplier',
			'mainmenu' => 'immobilier',
			'url' => '/immobilier/charge/chargefournisseur.php',
			'langs' => 'immobilier@immobilier',
			'position' => 142,
			'enabled' => 1,
			'perms' => 1,
			'target' => '',
			'user' => 0 
		);
		$r ++;

		$this->menu [$r] = array (
			'fk_menu' => 'r=13',
			'type' => 'left',
			'titre' => 'CostPerProperty',
			'mainmenu' => 'immobilier',
			'url' => '/immobilier/charge/chargeappart.php',
			'langs' => 'immobilier@immobilier',
			'position' => 143,
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
			'position' => 144,
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
			'fk_menu' => 'r=18',
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
		*/
	}
	
	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options Enabling module ('', 'noboxes')
	 * @return int if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$sql = array();
		
		$result = $this->loadTables();
		
		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param string $options Enabling module ('', 'noboxes')
	 * @return int if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		
		return $this->_remove($sql, $options);
	}
	
	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /immobilier/sql/
	 * This function is called by this->init
	 *
	 * @return int if KO, >0 if OK
	 */
	private function loadTables()
	{
		return $this->_load_tables('/immobilier/sql/');
	}
}
