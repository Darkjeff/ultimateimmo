<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
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
 * \file htdocs/compta/class/BookKeeping.class.php
 * \ingroup compta
 * \brief Fichier de la classe des comptes comptable
 * \version $Id: BookKeeping.class.php,v 1.3 2011/08/03 00:46:33 eldy Exp $
 */

/**
 * \class charge
 * \brief Classe permettant la gestion des paiements des loyers
 */
class Charge {
	var $db;
	var $id;
	var $ref;
	var $local_id;
	var $type;
	var $libelle;
	var $fournisseur;
	var $nouveau_fournisseur;
	var $montant_ht;
	var $montant_tva;
	var $montant_ttc;
	var $date_acq;
	var $periode_du;
	var $periode_au;
	var $commentaire;
	var $proprietaire_id;
	
	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	function __construct($db) {
		$this->db = $db;
		return 1;
	}
	function fetch($id) {
		$sql = "SELECT ic.rowid as reference, ic.local_id,";
		$sql .= "ic.type, ic.libelle, ic.fournisseur, ic.nouveau_fournisseur,";
		$sql .= "ic.montant_ht, ic.montant_tva , ic.montant_ttc , ic.date_acq ,";
		$sql .= "ic.periode_du ,ic.periode_au , ic.commentaire , ";
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
		$sql .= " '" . $this->type . "',";
		$sql .= " '" . $this->libelle . "',";
		$sql .= " '" . $this->fournisseur . "',";
		$sql .= " '" . $this->nouveau_fournisseur . "',";
		$sql .= " '" . $this->montant_ht . "',";
		$sql .= " '" . $this->montant_tva . "',";
		$sql .= " '" . $this->montant_ttc . "',";
		$sql .= " " . ($this->date_acq ? "'" . $this->db->idate ( $this->date_acq ) . "'" : "null") . ",";
		$sql .= " " . ($this->periode_du ? "'" . $this->db->idate ( $this->periode_du ) . "'" : "null") . ",";
		$sql .= " " . ($this->periode_au ? "'" . $this->db->idate ( $this->periode_au ) . "'" : "null") . ",";
		$sql .= " '" . $this->commentaire . "',";
		$sql .= " '" . $user->id . "'";
		$sql .= ")";
		 
		
		dol_syslog ( get_class ( $this ) . "::create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->db->commit ();
			return 1;
		} else {
			$this->error = $this->db->error ();
			$this->db->rollback ();
			dol_syslog ( get_class ( $this ) . "::create error=" . $this->error, LOG_DEBUG );
			return - 1;
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
		$sql .= " proprietaire_id ='" . $user->id . "'";
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
		
		$lien = '<a href="' . DOL_URL_ROOT . '/immobilier/charge/fiche_charge.php?id=' . $this->id . '">';
		$lienfin = '</a>';
		
		if ($withpicto)
			$result .= ($lien . img_object ( $langs->trans ( "ShowRent" ) . ': ' . $this->nom, 'bill' ) . $lienfin . ' ');
		if ($withpicto && $withpicto != 2)
			$result .= ' ';
		if ($withpicto != 2)
			$result .= $lien . ($maxlen ? dol_trunc ( $this->ref, $maxlen ) : $this->ref) . $lienfin;
		return $result;
	}
	
	
	
	
	
	
	
	
	
	
	
}

?>
