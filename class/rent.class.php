<?php
/* Copyright (C) 2013-2014  Florian Henry   	<florian.henry@open-concept.pro>
 * Copyright (C) 2015-2016	Alexandre Spangaro  <aspangaro.dolibarr@gmail.com>
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
 * \file	immobilier/class/rent.class.php
 * \ingroup immobilier
 * \brief	Manage rent object
 */

/**
 * \class local
 * \brief Classe permettant la gestion des locaux
 */
class Rent {
	var $db;
	var $id;
	var $rowid;
	var $fk_property;
	var $fk_renter;
	var $nomlocal;
	var $nomlocataire;
	var $date_entree;
	var $date_fin_preavis;
	var $preavis;
	var $date_prochain_loyer;
	var $date_dernier_regul;
	var $montant_tot;
	var $loyer;
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
		$sql = "SELECT ic.rowid as reference, ic.fk_property, ic.fk_renter,";
		$sql .= " ic.date_start, ic.date_end, ic.preavis ,";
		$sql .= " ic.date_prochain_loyer, ic.date_derniere_regul, ic.montant_tot ,";
		$sql .= " ic.loyer, ic.charges, ic.tva, ic.encours , ic.periode, ic.depot ,";
		$sql .= " ic.date_der_rev, ic.commentaire ";
		$sql .= " , lc.nom as nomlocataire , ll.name as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_contrat as ic ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_property as ll ";
		$sql .= "WHERE ic.fk_renter = lc.rowid AND ic.fk_property = ll.rowid AND ic.rowid = " . $id;

		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->rowid;
				$this->reference = $obj->reference;
				$this->fk_property = $obj->fk_property;
				$this->nomlocal = $obj->nomlocal;
				$this->fk_renter = $obj->fk_renter;
				$this->nomlocataire = $obj->nomlocataire;
				$this->date_start = $this->db->jdate ( $obj->date_start );
				$this->date_end = $this->db->jdate ( $obj->date_end );
				$this->preavis = $obj->preavis;
				$this->date_prochain_loyer = $obj->date_prochain_loyer;
				$this->date_derniere_regul = $obj->date_derniere_regul;
				$this->montant_tot = $obj->montant_tot;
				$this->loyer = $obj->loyer;
				$this->charges = $obj->charges;
				$this->tva = $obj->tva;
				$this->encours = $obj->encours;
				$this->periode = $obj->periode;
				$this->depot = $obj->depot;
				$this->date_der_rev = $obj->date_der_rev;
				$this->commentaire = $obj->commentaire;
			}
			$this->db->free($resql);

			return 1;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			return -1;
		}
	}
	
	function create($user) {
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_contrat (";
		$sql .= "fk_property,";
		$sql .= " fk_renter,";
		$sql .= " date_start,";
		$sql .= " montant_tot,";
		$sql .= " loyer,";
		$sql .= " charges,";
		$sql .= " tva,";
		$sql .= " periode,";
		$sql .= " depot,";
		$sql .= " commentaire ";
		$sql .= ") VALUES (";
		$sql .= " '" . $this->fk_property . "',";
		$sql .= " '" . $this->fk_renter . "',";
		$sql .= " " . ($this->date_start ? "'" . $this->db->idate ( $this->date_start ) . "'" : "null") . ",";
		$sql .= " '" . $this->montant_tot . "',";
		$sql .= " '" . $this->loyer . "',";
		$sql .= " '" . $this->charges . "',";
		$sql .= " '" . $this->tva . "',";
		$sql .= " '" . $this->periode . "',";
		$sql .= " '" . $this->depot . "',";
		$sql .= " '" . $this->commentaire . "'";
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
		$sql .= "SET fk_property = '" . $this->db->escape ( $this->fk_property ) . "',";
		$sql .= " fk_renter = '" . $this->db->escape ( $this->fk_renter ) . "',";
		$sql .= " date_start = " . (dol_strlen ( $this->date_start ) != 0 ? "'" . $this->db->idate ( $this->date_start ) . "'" : 'null'). ",";
		$sql .= " date_end = " . (dol_strlen ( $this->date_end ) != 0 ? "'" . $this->db->idate ( $this->date_end ) . "'" : 'null') . ",";
		$sql .= " preavis = '" . $this->db->escape ( $this->preavis ) . "',";
		$sql .= " date_derniere_regul = " . (dol_strlen ( $this->date_derniere_regul ) != 0 ? "'" . $this->db->idate ( $this->date_derniere_regul ) . "'" : 'null') . ",";
		$sql .= " montant_tot = '" . $this->db->escape ( $this->montant_tot ) . "',";
		$sql .= " loyer = '" . $this->db->escape ( $this->loyer ) . "',";
		$sql .= " charges = '" . $this->db->escape ( $this->charges ) . "',";
		$sql .= " tva = '" . $this->db->escape ( $this->tva ) . "',";
		$sql .= " periode = '" . $this->db->escape ( $this->periode ) . "',";
		$sql .= " depot = '" . $this->db->escape ( $this->depot ) . "',";
		$sql .= " date_der_rev = '" . $this->db->idate ( $this->date_der_rev ) . "',";
		$sql .= " commentaire = '" . $this->db->escape ( $this->commentaire ) . "'";
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
		$sql .= ", " . MAIN_DB_PREFIX . "immo_property as il";
		$sql .= " WHERE ic.fk_property = il.rowid AND preavis= 0 ";
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
	
	function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;
		
		$result = '';
		
		if (empty($this->rowid))
			$this->rowid = $this->rowid;
		
		$lien = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/rent/card.php?id=' . $this->id . '">';
		$lienfin = '</a>';
		
		if ($withpicto)
			$result .= ($lien . img_object($langs->trans("ShowProperty") . ': ' . $this->id, 'bill') . $lienfin . ' ');
		if ($withpicto && $withpicto != 2)
			$result .= ' ';
		if ($withpicto != 2)
			$result .= $lien . ($maxlen ? dol_trunc($this->id, $maxlen) : $this->id) . $lienfin;
		return $result;
	}

	/**
	 * Renvoi le libelle d'un statut donne
	 *
	 * @param int $statut statut
	 * @param int $mode long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 * @return string Label
	 */
	 
	function LibStatut($statut) {
		global $langs;
		

		if ($statut == 0)
				return img_picto ( $langs->trans ( "Inactive" ), 'statut1' ) . ' ' . $langs->trans ( "Inactive" );
			if ($statut == 1)
				return img_picto ( $langs->trans ( "Active" ), 'statut6' ) . ' ' . $langs->trans ( "Active" );

		
		return "Error, mode/status not found";
	}

	/**
	 * Give information on the object
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function info($id) {
		global $langs;

		$sql = "SELECT";
		$sql .= " s.rowid, s.datec, s.tms, s.fk_user_author, s.fk_user_mod";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_contrat as s";
		$sql .= " WHERE s.rowid = " . $id;

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->date_creation = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->tms);
				$this->user_modification = $obj->fk_user_mod;
				$this->user_creation = $obj->fk_user_author;
			}
			$this->db->free($resql);

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}
}
