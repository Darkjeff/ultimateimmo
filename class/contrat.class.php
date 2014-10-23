<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 * \class local
 * \brief Classe permettant la gestion des locaux
 */
class Contrat {
	var $db;
	var $id;
	var $ref;
	var $local_id;
	var $locataire_id;
	var $nomlocal;
	var $nomlocataire;
	var $date_entree;
	var $date_fin_preavis;
	var $preavis;
	var $date_prochain_loyer;
	var $date_dernier_regul;
	var $montant_tot;
	var $loy;
	var $charges;
	var $tva;
	var $encours;
	var $periode;
	var $depot;
	var $date_der_rev;
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
		$sql = "SELECT ic.rowid as reference, ic.local_id, ic.locataire_id,";
		$sql .= " ic.date_entree, ic.date_fin_preavis, ic.preavis ,";
		$sql .= " ic.date_prochain_loyer, ic.date_derniere_regul, ic.montant_tot ,";
		$sql .= " ic.loy, ic.charges, ic.tva, ic.encours , ic.periode, ic.depot ,";
		$sql .= " ic.date_der_rev, ic.commentaire, ic.proprietaire_id ";
		$sql .= " , lc.nom as nomlocataire , ll.nom as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_contrat as ic ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_locataire as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll ";
		$sql .= "WHERE ic.locataire_id = lc.rowid AND ic.local_id = ll.rowid AND ic.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->local_id = $obj->local_id;
				$this->nomlocal = $obj->nomlocal;
				$this->locataire_id = $obj->locataire_id;
				$this->nomlocataire = $obj->nomlocataire;
				$this->date_entree = $this->db->jdate ( $obj->date_entree );
				$this->date_fin_preavis = $this->db->jdate ( $obj->date_fin_preavis );
				$this->preavis = $obj->preavis;
				$this->date_prochain_loyer = $obj->date_prochain_loyer;
				$this->date_derniere_regul = $obj->date_derniere_regul;
				$this->montant_tot = $obj->montant_tot;
				$this->loy = $obj->loy;
				$this->charges = $obj->charges;
				$this->tva = $obj->tva;
				$this->encours = $obj->encours;
				$this->periode = $obj->periode;
				$this->depot = $obj->depot;
				$this->date_der_rev = $obj->date_der_rev;
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
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_contrat (";
		$sql .= "local_id,";
		$sql .= " locataire_id,";
		$sql .= " date_entree,";
		$sql .= " montant_tot,";
		$sql .= " loy,";
		$sql .= " charges,";
		$sql .= " tva,";
		$sql .= " periode,";
		$sql .= " depot,";
		$sql .= " commentaire, ";
		$sql .= " proprietaire_id";
		$sql .= ") VALUES (";
		$sql .= " '" . $this->local_id . "',";
		$sql .= " '" . $this->locataire_id . "',";
		$sql .= " " . ($this->date_entree ? "'" . $this->db->idate ( $this->date_entree ) . "'" : "null") . ",";
		$sql .= " '" . $this->montant_tot . "',";
		$sql .= " '" . $this->loy . "',";
		$sql .= " '" . $this->charges . "',";
		$sql .= " '" . $this->tva . "',";
		$sql .= " '" . $this->periode . "',";
		$sql .= " '" . $this->depot . "',";
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
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_contrat ";
		$sql .= "SET local_id = '" . $this->db->escape ( $this->local_id ) . "',";
		$sql .= " locataire_id = '" . $this->db->escape ( $this->locataire_id ) . "',";
		$sql .= " date_entree = " . (dol_strlen ( $this->date_entree ) != 0 ? "'" . $this->db->idate ( $this->date_entree ) . "'" : 'null'). ",";
		$sql .= " date_fin_preavis = " . (dol_strlen ( $this->date_fin_preavis ) != 0 ? "'" . $this->db->idate ( $this->date_fin_preavis ) . "'" : 'null') . ",";
		$sql .= " preavis = '" . $this->db->escape ( $this->preavis ) . "',";
		$sql .= " date_derniere_regul = " . (dol_strlen ( $this->date_derniere_regul ) != 0 ? "'" . $this->db->idate ( $this->date_derniere_regul ) . "'" : 'null') . ",";
		$sql .= " montant_tot = '" . $this->db->escape ( $this->montant_tot ) . "',";
		$sql .= " loy = '" . $this->db->escape ( $this->loy ) . "',";
		$sql .= " charges = '" . $this->db->escape ( $this->charges ) . "',";
		$sql .= " tva = '" . $this->db->escape ( $this->tva ) . "',";
		$sql .= " periode = '" . $this->db->escape ( $this->periode ) . "',";
		$sql .= " depot = '" . $this->db->escape ( $this->depot ) . "',";
		$sql .= " date_der_rev = '" . $this->db->idate ( $this->date_der_rev ) . "',";
		$sql .= " commentaire = '" . $this->db->escape ( $this->commentaire ) . "',";
		$sql .= " proprietaire_id ='" . $user->id . "'";
		$sql .= " WHERE rowid = " . $this->id;
		
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
	function select_nom_contrat($selected = '', $htmlname = 'actionnomcontrat', $useempty = 0, $maxlen = 40, $help = 1) {
		global $db, $langs, $user;
		$sql = "SELECT ic.rowid, il.nom as nomlocal";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_contrat as ic ";
		$sql .= ", " . MAIN_DB_PREFIX . "immo_local as il";
		$sql .= " WHERE ic.local_id = il.rowid AND preavis= 0 ";
		$sql .= " ORDER BY il.nom ASC";
		dol_syslog ( "Form::select_nom_contrat sql=" . $sql, LOG_DEBUG );
		$resql = $db->query ( $sql );
		if ($resql) {
			$num = $db->num_rows ( $resql );
			if ($num) {
				print '<select class="flat" name="' . $htmlname . '">';
				$i = 0;
				
				if ($useempty)
					print '<option value="0">&nbsp;</option>';
				while ( $i < $num ) {
					$obj = $db->fetch_object ( $resql );
					print '<option value="' . $obj->rowid . '"';
					if ($obj->rowid == $selected)
						print ' selected="selected"';
					print '>' . dol_trunc ( $obj->nomlocal, $maxlen );
					$i ++;
				}
				print '</select>';
			}
		} else {
			dol_print_error ( $db, $db->lasterror () );
		}
	}
}

?>
