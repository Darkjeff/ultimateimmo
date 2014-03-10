<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file htdocs/compta/class/BookKeeping.class.php
 * \ingroup compta
 * \brief Fichier de la classe des comptes comptable
 * \version $Id: BookKeeping.class.php,v 1.3 2011/08/03 00:46:33 eldy Exp $
 */

/**
 * \class local
 * \brief Classe permettant la gestion des paiements des loyers
 */
class Paie {
	var $db;
	var $id;
	var $ref;
	var $contrat_id;
	var $local_id;
	var $nomlocal;
	var $locataire_id;
	var $nomlocataire;
	var $montant;
	var $commentaire;
	var $date_paiement;
	var $proprietaire_id;
	var $loyer_id;
	var $nomloyer;
	
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
		$sql = "SELECT ip.rowid as reference, ip.contrat_id, ip.local_id,";
		$sql .= "ip.locataire_id, ip.montant, ip.commentaire, ip.date_paiement,";
		$sql .= "ip.proprietaire_id, ip.loyer_id";
		$sql .= " , lc.nom as nomlocataire , ll.nom as nomlocal , lo.nom as nomloyer ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as ip ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_locataire as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_loyer as lo ";
		$sql .= "WHERE ip.locataire_id = lc.rowid AND ip.local_id = ll.rowid AND ip.loyer_id = lo.rowid AND ip.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->contrat_id = $obj->contrat_id;
				$this->local_id = $obj->local_id;
				$this->nomlocal = $obj->nomlocal;
				$this->locataire_id = $obj->locataire_id;
				$this->nomlocataire = $obj->nomlocataire;
				$this->montant = $obj->montant;
				$this->commentaire = $obj->commentaire;
				$this->date_paiement = $this->db->jdate ( $obj->date_paiement );
				$this->loyer_id = $obj->loyer_id;
				$this->nomloyer = $obj->nomloyer;
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
	function fetch_by_loyer($id) {
		$sql = "SELECT ip.rowid as reference, ip.contrat_id, ip.local_id,";
		$sql .= "ip.locataire_id, ip.montant, ip.commentaire, ip.date_paiement,";
		$sql .= "ip.proprietaire_id, ip.loyer_id";
		$sql .= " , lc.nom as nomlocataire , ll.nom as nomlocal , lo.nom as nomloyer ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as ip ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_locataire as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_loyer as lo ";
		$sql .= "WHERE ip.locataire_id = lc.rowid AND ip.local_id = ll.rowid AND ip.loyer_id = lo.rowid AND lo.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch_by_loyer sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->contrat_id = $obj->contrat_id;
				$this->local_id = $obj->local_id;
				$this->nomlocal = $obj->nomlocal;
				$this->locataire_id = $obj->locataire_id;
				$this->nomlocataire = $obj->nomlocataire;
				$this->montant = $obj->montant;
				$this->commentaire = $obj->commentaire;
				$this->date_paiement = $this->db->jdate ( $obj->date_paiement );
				$this->loyer_id = $obj->loyer_id;
				$this->nomloyer = $obj->nomloyer;
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
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_paie ";
		$sql .= " (contrat_id, local_id, locataire_id, montant, commentaire, date_paiement, loyer_id, proprietaire_id)";
		$sql .= " VALUES (" . $this->contrat_id . ",'" . $this->local_id . "','" . $this->locataire_id . "','" . $this->montant . "','" . $this->commentaire . "','" . $this->db->idate ( $this->date_paiement ) . "','" . $this->loyer_id . "'," . $user->id . ")";
		
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "immo_paie" );
		}
	}
	function update($user) {
		$this->db->begin ();
		
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_paie ";
		$sql .= " SET montant ='" . $this->db->escape ( $this->montant ) . "',";
		$sql .= " commentaire ='" . $this->db->escape ( $this->commentaire ) . "',";
		$sql .= " date_paiement ='" . $this->db->idate ( $this->date_paiement ) . "',";
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
}

?>
