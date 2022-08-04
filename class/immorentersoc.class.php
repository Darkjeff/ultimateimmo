<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2018-2022 Philippe Grand       <philippe.grand@atoo-net.com>
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
 *	\file       htdocs/custom/ultimateimmo/class/immorentersoc.class.php
 *	\ingroup    ultimateimmo
 *	\brief      Surcharge de la classe Societe
 */

include_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
dol_include_once('/ultimateimmo/class/immorenter.class.php');


/**
 *	Class to manage thirdparties of renters
 */
class RenterSoc extends Societe
{

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Create a third party into database from a renter object
	 *
	 *  @param	ImmoRenter	$renter			Object renter
	 * 	@param	string		$socname		Name of third party to force
	 *	@param	string		$socalias		Alias name of third party to force
	 *  @param	string		$customercode	Customer code
	 *  @return int							<0 if KO, id of created account if OK
	 */
	public function create_from_renter(ImmoRenter $renter, $socname = '', $socalias = '', $customercode = '')
	{
		// phpcs:enable
		global $conf, $user, $langs;
		
		dol_syslog(get_class($this)."::create_from_renter", LOG_DEBUG);

		$name = $socname ? $socname : $renter->societe;
		if (empty($name)) {
			$name = $renter->getFullName($langs);
		}

		$alias = $socalias ? $socalias : '';

		// Positionne parametres
		$this->name = $name;
		$this->name_alias = $alias;
		$this->address = $renter->address;
		$this->zip = $renter->zip;
		$this->town = $renter->town;
		$this->country_code = $renter->country_code;
		$this->country_id = $renter->country_id;
		$this->phone = $renter->phone; // Prof phone
		$this->email = $renter->email;
		$this->socialnetworks = $renter->socialnetworks;
		$this->entity = $renter->entity;

		$this->client = 1; // A renter is a customer by default
		$this->code_client = ($customercode ? $customercode : -1);
		$this->code_fournisseur = -1;
		$this->typent_code = ($renter->morphy == 'phy' ? 'TE_PRIVATE' : 0);
		$this->typent_id = $this->typent_code ? dol_getIdFromCode($this->db, $this->typent_code, 'c_typent', 'id', 'code') : 0;

		$this->db->begin();

		// Cree et positionne $this->id
		$result = $this->create($user);

		if ($result >= 0) {
			// Auto-create contact on thirdparty creation
			if (!empty($conf->global->THIRDPARTY_DEFAULT_CREATE_CONTACT)) {
				// Fill fields needed by contact
				$this->name_bis = $renter->lastname;
				$this->firstname = $renter->firstname;
				$this->civility_id = $renter->civility_id;

				dol_syslog("We ask to create a contact/address too", LOG_DEBUG);
				$result = $this->create_individual($user);

				if ($result < 0) {
					setEventMessages($this->error, $this->errors, 'errors');
					$this->db->rollback();
					return -1;
				}
			}

			$sql = "UPDATE ".MAIN_DB_PREFIX."ultimateimmo_immorenter";
			$sql .= " SET fk_soc = ".((int) $this->id);
			$sql .= " WHERE rowid = ".((int) $renter->id);

			$resql = $this->db->query($sql);
			if ($resql) {
				$this->db->commit();
				return $this->id;
			} else {
				$this->error = $this->db->error();

				$this->db->rollback();
				return -1;
			}
		} else {
			// $this->error deja positionne
			dol_syslog(get_class($this)."::create_from_renter - 2 - ".$this->error." - ".join(',', $this->errors), LOG_ERR);

			$this->db->rollback();
			return $result;
		}
	}

}
