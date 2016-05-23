<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015 Alexandre Spangaro <aspangaro.dolibarr@gmail.com>
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
 * \file    immobilier/class/immocost.class.php
 * \ingroup immobilier
 * \brief   Manage cost object
 */

/**
 * \class charge
 * \brief Classe permettant la gestion des paiements des loyers
 */
require_once 'commonobjectimmobilier.class.php';

/**
 * Class Immocost
 *
 */

class Immocost extends CommonObjectImmobilier
{
	/**
	 * @var string Error code (or message)
	 * @deprecated
	 * @see Immoproperty::errors
	 */
	public $error;
	/**
	 * @var string[] Error codes (or messages)
	 */
	public $errors = array();
	/**
	 * @var string Id to identify managed objects
	 */
	public $element = 'immocost';
	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'immo_cost';

	/**
	 * @var ImmopropertyLine[] Lines
	 */
	public $lines = array();

	/**
	 * @var int ID
	 */
	public $id;
	
	public $entity;
	public $local_id;
	public $type;
	public $libelle;
	public $fournisseur;
	public $nouveau_fournisseur;
	public $montant_ht;
	public $montant_tva;
	public $montant_ttc;
	public $date_acq;
	public $periode_du;
	public $periode_au;
	public $commentaire;
	public $proprietaire_id;
	public $dispatch;
	
	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
		return 1;
	}
	
	
	function fetch($id) {
		$sql = "SELECT ic.rowid as reference, ic.local_id,";
		$sql .= "ic.type, ic.libelle, ic.fournisseur, ic.nouveau_fournisseur,";
		$sql .= "ic.montant_ht, ic.montant_tva , ic.montant_ttc , ic.date_acq ,";
		$sql .= "ic.periode_du ,ic.periode_au , ic.commentaire , ic.dispatch, ";
		$sql .= "ic.proprietaire_id ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic ";
		$sql .= "WHERE ic.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->local_id = $obj->local_id;
				$this->type = $obj->type;
				$this->libelle = $obj->libelle;
				$this->fournisseur = $obj->fournisseur;
				$this->nouveau_fournisseur = $obj->nouveau_fournisseur;
				$this->montant_ht = $obj->montant_ht;
				$this->montant_tva = $obj->montant_tva;
				$this->montant_ttc = $obj->montant_ttc;
				$this->date_acq = $this->db->jdate ( $obj->date_acq );
				$this->periode_du = $this->db->jdate ( $obj->periode_du );
				$this->periode_au = $this->db->jdate ( $obj->periode_au );
				$this->commentaire = $obj->commentaire;
				$this->dispatch = $obj->dispatch;
				$this->proprietaire_id = $obj->proprietaire_id;
				
				1;
			} else {
				return 0;
			}
			$this->db->free ( $resql );
		} else {
			$this->error = $this->db->error ();
			return - 1;
		}
	}
	
	public function fetchAll($sortorder='', $sortfield='', $limit=0, $offset=0, array $filter = array(), $filtermode='AND')
	{
		global $langs;
		$langs->load('immobilier@immobilier');
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';
		
		$sql .= " t.local_id,";
		$sql .= " t.type,";
		$sql .= " t.libelle,";
		$sql .= " t.fournisseur,";
		$sql .= " t.nouveau_fournissseur,";
		$sql .= " t.montant_ht,";
		$sql .= " t.montant_tva,";
		$sql .= " t.montant_ttc,";
		$sql .= " t.date_acq,";
		$sql .= " t.periode_du,";
		$sql .= " t.periode_au,";
		$sql .= " t.commentaire,";
		$sql .= " t.proprietaire_id,";
		$sql .= " t.dispatch";
		
		
		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';
		

		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key=='t.local_id' || $key=='t.fournisseur' || $key=='t.libelle' || $key=='t.date_acq') {
					$sqlwhere [] = $key . ' = ' . $this->db->escape($value);					
				} else {
					$sqlwhere [] = $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
				
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' WHERE ' . implode(' '.$filtermode.' ', $sqlwhere);
		}
		
		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield,$sortorder);
		}
		if (!empty($limit)) {
		 $sql .=  ' ' . $this->db->plimit($limit + 1, $offset);
		}
		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new ImmocostLine();

				$line->id = $obj->rowid;
				
				$line->local_id = $obj->local_id;
				$line->type = $obj->type;
				$line->libelle = $obj->libelle;
				$line->fournisseur = $obj->fournisseur;
				$line->nouveau_fournissseur = $obj->nouveau_fournissseur;
				$line->montant_ht = $obj->montant_ht;
				$line->montant_tva = $obj->montant_tva;
				$line->montant_ttc = $obj->montant_ttc;
				$line->date_acq = $obj->date_acq;
				$line->periode_du = $obj->periode_du;
				$line->periode_au = $obj->periode_au;
				$line->commentaire = $obj->commentaire;
				$line->proprietaire_id = $obj->proprietaire_id;
				$line->dispatch = $obj->dispatch;
				
				$this->lines[] = $line;
			}
			$this->db->free($resql);

			return $num;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}
	
	
	
	function create($user) {
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_charge (";
		$sql .= " local_id,";
		$sql .= " type,"; 
		$sql .= " libelle,";
		$sql .= " fournisseur,";
		$sql .= " nouveau_fournisseur,";
		$sql .= " montant_ht,";
		$sql .= " montant_tva,";
		$sql .= " montant_ttc,";
		$sql .= " date_acq,";
		$sql .= " periode_du,";
		$sql .= " periode_au,";
		$sql .= " commentaire,";
		$sql .= " proprietaire_id ";
		$sql .= " ) VALUES (";
		$sql .= " '" . $this->local_id . "',";
		$sql .= " '" . $this->db->escape($this->type) . "',";
		$sql .= " '" . $this->db->escape($this->libelle) . "',";
		$sql .= " '" . $this->db->escape($this->fournisseur) . "',";
		$sql .= " '" . $this->db->escape($this->nouveau_fournisseur) . "',";
		$sql .= " '" . $this->montant_ht . "',";
		$sql .= " '" . $this->montant_tva . "',";
		$sql .= " '" . $this->montant_ttc . "',";
		$sql .= " " . ($this->date_acq ? "'" . $this->db->idate ( $this->date_acq ) . "'" : "null") . ",";
		$sql .= " " . ($this->periode_du ? "'" . $this->db->idate ( $this->periode_du ) . "'" : "null") . ",";
		$sql .= " " . ($this->periode_au ? "'" . $this->db->idate ( $this->periode_au ) . "'" : "null") . ",";
		$sql .= " '" . $this->db->escape($this->commentaire) . "',";
		$sql .= " '" . $this->db->escape($user->id) . "'";
		$sql .= ")";
		 
		
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror();
		}
		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "immo_charge");
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}
	
	
	function update($user) {
		$this->db->begin ();
		
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_charge ";
		$sql .= " SET libelle ='" . $this->db->escape ( $this->libelle ) . "',";
		$sql .= " fournisseur ='" . $this->db->escape ( $this->fournisseur ) . "',";
		$sql .= " nouveau_fournisseur ='" . $this->db->escape ( $this->nouveau_fournisseur ) . "',";
		$sql .= " local_id ='" . $this->db->escape ( $this->local_id ) . "',";
		$sql .= " type ='" . $this->db->escape ( $this->type ) . "',";
		$sql .= " montant_ttc ='" . $this->db->escape ( $this->montant_ttc ) . "',";
		$sql .= " proprietaire_id ='" . $user->id . "',";
		$sql .= " commentaire ='" . $this->db->escape ( $this->commentaire ) . "',";
		$sql .= " date_acq=" . (dol_strlen($this->date_acq) != 0 ? "'" . $this->db->idate($this->date_acq) . "'" : 'null').',';
		$sql .= " periode_du=" . (dol_strlen($this->periode_du) != 0 ? "'" . $this->db->idate($this->periode_du) . "'" : 'null').',';
		$sql .= " periode_au=" . (dol_strlen($this->periode_au) != 0 ? "'" . $this->db->idate($this->periode_au) . "'" : 'null').',';
		$sql .= " dispatch=" . $this->db->escape ( $this->dispatch );
		$sql .= " WHERE rowid =" . $this->id;
		
		dol_syslog ( get_class ( $this ) . "::update sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->db->commit ();
			return 1;
		} else {
			$this->error = $this->db->error ();
			$this->db->rollback ();
			return - 1;
		}
	}
	
function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;
		
		$result = '';
		
		if (empty ( $this->ref ))
			$this->ref = $this->nom;
		
		$lien = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/cost/card.php?id=' . $this->id . '">';
		$lienfin = '</a>';
		
		if ($withpicto)
			$result .= ($lien . img_object ( $langs->trans ( "ShowCost" ) . ': ' . $this->nom, 'bill' ) . $lienfin . ' ');
		if ($withpicto && $withpicto != 2)
			$result .= ' ';
		if ($withpicto != 2)
			$result .= $lien . ($maxlen ? dol_trunc ( $this->ref, $maxlen ) : $this->ref) . $lienfin;
		return $result;
	}
	
	function set_dispatch($user) {
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'immo_cost SET';
		$sql .= ' dispatch=1';
		$sql .= ' WHERE rowid = ' . $this->id;
		$return = $this->db->query ( $sql );
		$this->db->commit ();
		if ($return)
			return 1;
		else
			return - 1;
	}
	function getLibStatut($mode = 0) {
		return $this->LibStatut ( $this->dispatch, $mode );
	}
	
	/**
	 * Renvoi le libelle d'un statut donne
	 *
	 * @param int $statut statut
	 * @param int $mode long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 * @return string Label
	 */
	function LibStatut($statut, $mode = 0) {
		global $langs;
		$langs->load ( 'customers' );
		$langs->load ( 'bills' );
		
		if ($mode == 0) {
			if ($dispatch == 0)
				return $langs->trans ( "ToDispatch" );
			if ($dispatch == 1)
				return $langs->trans ( "Dispatch" );
		}
		if ($mode == 1) {
			if ($statut == 0)
				return $langs->trans ( "ToDispatch" );
			if ($statut == 1)
				return $langs->trans ( "Dispatch" );
		}
		if ($mode == 2) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' ) . ' ' . $langs->trans ( "ToDispatch" );
			if ($statut == 1)
				return img_picto ( $langs->trans ( "Paid" ), 'statut6' ) . ' ' . $langs->trans ( "Dispatch" );
		}
		if ($mode == 3) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' );
			if ($statut == 1)
				return img_picto ( $langs->trans ( "Dispatch" ), 'statut6' );
		}
		if ($mode == 4) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' ) . ' ' . $langs->trans ( "ToDispatch" );
			if ($statut == 1)
				return img_picto ( $langs->trans ( "Dispatch" ), 'statut6' ) . ' ' . $langs->trans ( "Dispatch" );
		}
		if ($mode == 5) {
			if ($statut == 0)
				return $langs->trans ( "ToDispatch" ) . ' ' . img_picto ( $langs->trans ( "ToDispatch" ), 'statut1' );
			if ($statut == 1)
				return $langs->trans ( "Dispatch" ) . ' ' . img_picto ( $langs->trans ( "Dispatch" ), 'statut6' );
		}
		
		return "Error, mode/status not found";
	}	
}
