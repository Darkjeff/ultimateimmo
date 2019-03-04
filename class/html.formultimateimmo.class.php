<?php
/* Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
 * Copyright (C) 2015 		Alexandre Spangaro  <aspangaro@zendsi.com>
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
 * \file 	ultimateimmo/class/html.formultimateimmo.class.php
 * \brief 	Fichier de la classe des fonctions predefinie de composants html ultimateimmo
 */

/**
 * Class to manage building of HTML components
 */
class FormUltimateimmo extends Form {
	public  $error;
	
	/**
	 * Constructor
	 *
	 * @param DoliDB $db handler
	 */
	function __construct($db) {
		global $langs;
		$this->db = $db;
		return 1;
	}
	
	/**
	 * affiche un champs select contenant la liste des contact déjà référencés.
	 *
	 * @param int $selectid Id de la session selectionner
	 * @param string $htmlname Name of HTML control
	 * @param string $filter SQL part for filter
	 * @param int $showempty empty field
	 * @param int $forcecombo use combo box
	 * @param array $event
	 * @return string The HTML control
	 */
	public function select_immo_contact($selectid = '', $htmlname = 'contact', $filter = '', $showempty = 0, $forcecombo = 0, $event = array()) {
		global $conf, $langs;
		
		$sql = "SELECT";
		$sql .= " c.rowid, ";
		$sql .= " s.lastname, s.firstname, s.civility, ";
		$sql .= " soc.nom as socname";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_contact as c";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople as s ON c.fk_socpeople = s.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as soc ON soc.rowid = s.fk_soc";
		$sql .= " WHERE c.archive = 0";
		if (! empty($filter)) {
			$sql .= ' AND ' . $filter;
		}
		$sql .= " ORDER BY socname";
		
		dol_syslog(get_class($this) . "::select_immo_contact", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			if ($conf->use_javascript_ajax && $conf->global->IMMO_CONTACT_USE_SEARCH_TO_SELECT && ! $forcecombo) {
				$out .= ajax_combobox($htmlname, $event);
			}
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($result);
			$i = 0;
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object($result);
					$label = $obj->firstname . ' ' . $obj->name;
					if ($obj->socname)
						$label .= ' (' . $obj->socname . ')';
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
			$this->db->free($result);
			return $out;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::select_immo_contact " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	/**
	 * Return list of all contacts (for a third party or all)
	 *
	 * @param int $socid ot third party or 0 for all
	 * @param string $selected contact pre-selectionne
	 * @param string $htmlname of HTML field ('none' for a not editable field)
	 * @param int $showempty empty value, 1=add an empty value
	 * @param string $exclude of contacts id to exclude
	 * @param string $limitto that are not id in this array list
	 * @param string $showfunction function into label
	 * @param string $moreclass class to class style
	 * @param string $showsoc company into label
	 * @param int $forcecombo use combo box
	 * @param array $event Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid',
	 *        'params'=>array('add-customer-contact'=>'disabled')))
	 * @param bool $options_only only (for ajax treatment)
	 * @param bool $supplier only
	 * @return int if KO, Nb of contact in list if OK
	 */
	public function select_contacts_custom($socid, $selected = '', $htmlname = 'contactid', $showempty = 0, $exclude = '', $limitto = '', $showfunction = 0, $moreclass = '', $showsoc = 0, $forcecombo = 0, $event = array(), $options_only = false, $supplier=0) {
		print $this->selectcontactscustom($socid, $selected, $htmlname, $showempty, $exclude, $limitto, $showfunction, $moreclass, $options_only, $showsoc, $forcecombo, $event);
		return $this->num;
	}
	
	/**
	 * Return list of all contacts (for a third party or all)
	 *
	 * @param int $socid ot third party or 0 for all
	 * @param string $selected contact pre-selectionne
	 * @param string $htmlname of HTML field ('none' for a not editable field)
	 * @param int $showempty empty value, 1=add an empty value, 2=add line 'Internal' (used by user edit)
	 * @param string $exclude of contacts id to exclude
	 * @param string $limitto contact ti display in max
	 * @param string $showfunction function into label
	 * @param string $moreclass class to class style
	 * @param bool $options_only only (for ajax treatment)
	 * @param string $showsoc company into label
	 * @param int $forcecombo use combo box
	 * @param array $event Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid',
	 *        'params'=>array('add-customer-contact'=>'disabled')))
	 * @param bool $supplier only
	 * @return int if KO, Nb of contact in list if OK
	 */
	public function selectcontactscustom($socid, $selected = '', $htmlname = 'contactid', $showempty = 0, $exclude = '', $limitto = 0, $showfunction = 0, $moreclass = '', $options_only = false, $showsoc = 0, $forcecombo = 0, $event = array(), $supplier=0) {
		global $conf, $langs, $user;
		
		$langs->load('companies');
		
		$out = '';
		
		// On recherche les societes
		$sql = "SELECT DISTINCT sp.rowid, sp.lastname, sp.firstname, sp.poste";
		if ($showsoc > 0) {
			$sql .= " , s.nom as company";
		}
		$sql .= " FROM " . MAIN_DB_PREFIX . "socpeople as sp";
		
		if (empty($supplier)) {
			$sql .= " LEFT OUTER JOIN  " . MAIN_DB_PREFIX . "societe as s ON s.rowid=sp.fk_soc ";
		}else {
			$sql .= " INNER JOIN  " . MAIN_DB_PREFIX . "societe as s ON s.rowid=sp.fk_soc and s.fournisseur=1";
		}
		
		// Limit contact visibility to contact of thirdparty saleman
		if (empty($user->rights->societe->client->voir)) {
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON s.rowid = sc.fk_soc AND sc.fk_user = " . $user->id;
		}
		
		$sql .= " WHERE sp.entity IN (" . getEntity('societe', 1) . ")";
		if ($socid > 0) {
			$sql .= " AND (sp.fk_soc IN (SELECT rowid FROM  " . MAIN_DB_PREFIX . "societe WHERE parent IN (SELECT parent FROM " . MAIN_DB_PREFIX . "societe WHERE rowid=" . $socid . '))';
			$sql .= " OR (sp.fk_soc=" . $socid . ")";
			$sql .= " OR (sp.fk_soc IN (SELECT parent FROM llx_societe WHERE rowid=" . $socid . "))";
			$sql .= " OR (sp.fk_soc IN (SELECT rowid FROM " . MAIN_DB_PREFIX . "societe WHERE parent=" . $socid . ")))";
		}
		
		if (! empty($conf->global->CONTACT_HIDE_INACTIVE_IN_COMBOBOX))
			$sql .= " AND sp.statut<>0 ";
		
		
		$sql .= " ORDER BY sp.lastname ASC";
		
		dol_syslog(get_class($this) . "::selectcontactscustom");
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			
			if ($conf->use_javascript_ajax && $conf->global->CONTACT_USE_SEARCH_TO_SELECT && ! $forcecombo && ! $options_only) {
				$out .= ajax_combobox($htmlname, $event, $conf->global->CONTACT_USE_SEARCH_TO_SELECT);
				
				if ($num > $limitto && ! empty($limitto)) {
					$num = $limitto;
				}
			}
			
			if ($htmlname != 'none' || $options_only)
				$out .= '<select class="flat' . ($moreclass ? ' ' . $moreclass : '') . '" id="' . $htmlname . '" name="' . $htmlname . '">';
			if ($showempty == 1)
				$out .= '<option value="0"' . ($selected == '0' ? ' selected="selected"' : '') . '></option>';
			if ($showempty == 2)
				$out .= '<option value="0"' . ($selected == '0' ? ' selected="selected"' : '') . '>' . $langs->trans("Internal") . '</option>';
			
			$i = 0;
			if ($num) {
				include_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
				$contactstatic = new Contact($this->db);
				
				while ( $i < $num ) {
					$obj = $this->db->fetch_object($resql);
					
					$contactstatic->id = $obj->rowid;
					$contactstatic->lastname = $obj->lastname;
					$contactstatic->firstname = $obj->firstname;
					
					if ($htmlname != 'none') {
						$disabled = 0;
						if (is_array($exclude) && count($exclude) && in_array($obj->rowid, $exclude))
							$disabled = 1;
						if ($selected && $selected == $obj->rowid) {
							$out .= '<option value="' . $obj->rowid . '"';
							if ($disabled)
								$out .= ' disabled="disabled"';
							$out .= ' selected="selected">';
							$out .= $contactstatic->getFullName($langs);
							if ($showfunction && $obj->poste)
								$out .= ' (' . $obj->poste . ')';
							if (($showsoc > 0) && $obj->company)
								$out .= ' - (' . $obj->company . ')';
							$out .= '</option>';
						} else {
							$out .= '<option value="' . $obj->rowid . '"';
							if ($disabled)
								$out .= ' disabled="disabled"';
							$out .= '>';
							$out .= $contactstatic->getFullName($langs);
							if ($showfunction && $obj->poste)
								$out .= ' (' . $obj->poste . ')';
							if (($showsoc > 0) && $obj->company)
								$out .= ' - (' . $obj->company . ')';
							$out .= '</option>';
						}
					} else {
						if ($selected == $obj->rowid) {
							$out .= $contactstatic->getFullName($langs);
							if ($showfunction && $obj->poste)
								$out .= ' (' . $obj->poste . ')';
							if (($showsoc > 0) && $obj->company)
								$out .= ' - (' . $obj->company . ')';
						}
					}
					$i ++;
				}
			} else {
				$out .= '<option value="-1"' . ($showempty == 2 ? '' : ' selected="selected"') . ' disabled="disabled">' . $langs->trans($socid ? "NoContactDefinedForThirdParty" : "NoContactDefined") . '</option>';
			}
			if ($htmlname != 'none' || $options_only) {
				$out .= '</select>';
			}
			
			$this->num = $num;
			return $out;
		} else {
			dol_print_error($this->db);
			return - 1;
		}
	}
	
	/**
	 * Display list of property type
	 *
	 * @param int $selectid Id de la session selectionner
	 * @param string $htmlname Name of HTML control
	 * @param string $filter SQL part for filter
	 * @param int $showempty empty field
	 * @param int $forcecombo use combo box
	 * @param array $event
	 * @return string The HTML control
	 */
	public function select_type_property($selectid, $htmlname = 'property_type', $filter = '', $showempty = 1) {
		global $conf, $langs;
		
		$sql = "SELECT t.id, t.code, t.label";
		$sql .= " FROM " . MAIN_DB_PREFIX . "c_immo_type_property as t";
		if (! empty($filter)) {
			$sql .= ' WHERE ' . $filter;
		}
		$sql .= " ORDER BY t.label";
		
		dol_syslog(get_class($this) . "::select_type_property", LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($result);
			$i = 0;
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object($result);
					$label = ($obj->code && $langs->transnoentitiesnoconv($obj->code)!=$obj->code?$langs->transnoentitiesnoconv($obj->code):($obj->label!='-'?$obj->label:''));

					if ($selectid > 0 && $selectid == $obj->id) {
						$out .= '<option value="' . $obj->id . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->id . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
			$this->db->free($result);
			return $out;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::select_type_property " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	/**
	 * 
	 * @param unknown $selectid
	 * @param string $htmlname
	 * @param number $showempty
	 * @param array $event
	 * @return string
	 */
	public function select_property($selectid, $htmlname = 'property', $showempty = 0, $event = array(), $sql_filer='')
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, name";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_property";
		$sql .= " WHERE statut= 1";
		//if ($user->id != 1) {
		//	$sql .= " AND proprietaire_id=" . $user->id;
		//}
		if (!empty($sql_filer)) {
			$sql.=' AND '.$sql_filer;
		}
		
		$sql .= " ORDER BY name";
		
		dol_syslog(get_class($this) . "::select_property sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->name;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	function select_type($selectid, $htmlname = 'type', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, type";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_typologie";
		$sql .= " ORDER BY type";
		
		dol_syslog(get_class($this) . "::select_type sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->type;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	
	
	/**
	 * 
	 * @param unknown $selectid
	 * @param string $htmlname
	 * @param number $showempty
	 * @param array $event
	 */
	public function select_renter($selectid, $htmlname = 'renter', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, nom, prenom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_renter";
	//	$sql .= " WHERE statut= 'Actif'";
	//	if ($user->id != 1) {
	//		$sql .= " AND proprietaire_id=" . $user->id;
	//	}
		$sql .= " ORDER BY nom";
		
		dol_syslog(get_class($this) . "::select_renter sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = ucfirst($obj->prenom) . ' ' . strtoupper($obj->nom);
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	function select_supplier($selectid, $htmlname = 'supplier', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT DISTINCT supplier ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_cost ";
		$sql .= " ORDER BY supplier";
		
		dol_syslog(get_class($this) . "::select_supplier sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->supplier;
					
					if (($selectid != '') && $selectid == $obj->supplier) {
						$out .= '<option value="' . $obj->supplier . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->supplier . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	
	
	
	
	
	
	/**
	 * 
	 * @param string $selected
	 * @param string $htmlname
	 * @param number $useempty
	 * @param number $maxlen
	 * @param number $help
	 */
	public function select_nom_loyer($selected = '', $htmlname = 'nomloyer', $useempty = 0, $maxlen = 40, $help = 1) {
		global $langs, $user;
		$sql = "SELECT l.nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as l";
		$sql .= " GROUP BY l.nom";
		$sql .= " ORDER BY l.nom ASC";
	
		dol_syslog ( "Form::select_nom_loyer sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$num = $this->db->num_rows ( $resql );
			if ($num) {
				print '<select class="flat" name="' . $htmlname . '">';
				$i = 0;
	
				if ($useempty)
					print '<option value="0">&nbsp;</option>';
					while ( $i < $num ) {
						$obj = $this->db->fetch_object ( $resql );
						print '<option value="' . $obj->nom . '"';
						if ($obj->nom == $selected)
							print ' selected="selected"';
							print '>' . dol_trunc ( $obj->nom, $maxlen );
							$i ++;
					}
					print '</select>';
			}
		} else {
			dol_print_error($this->db);
		}
	}

}