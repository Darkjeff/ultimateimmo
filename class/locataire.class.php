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
 * \class Locataire
 * \brief Classe permettant la gestion des locataires
 */
class Locataire {
	var $db;
	var $id;
	var $rowid;
	var $nom;
	var $telephone;
	var $email;
	var $adresse;
	var $commentaire;
	var $statut;
	var $solde;
	var $proprietaire_id;
	var $status_array;
	
	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	function __construct($db, $rowid = '') {
		$this->db = $db;
		
		$this->status_array = array('Actif'=>'Actif','Inactif'=>'Inactif');
		
		if ($rowid != '')
			return $this->fetch ( $rowid );
	}
	function fetch($rowid = null, $nom = null) {
		if ($rowid || $nom) {
			$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immo_locataire WHERE ";
			if ($rowid) {
				$sql .= " rowid = '" . $rowid . "'";
			} elseif ($nom) {
				$sql .= " nom = '" . $nom . "'";
			}
			
			dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
			$result = $this->db->query ( $sql );
			if ($result) {
				$obj = $this->db->fetch_object ( $result );
			} else {
				return null;
			}
		}
		
		$this->id = $obj->rowid;
		$this->rowid = $obj->rowid;
		$this->nom = $obj->nom;
		$this->telephone = $obj->telephone;
		$this->email = $obj->email;
		$this->adresse = $obj->adresse;
		$this->commentaire = $obj->commentaire;
		$this->statut = $obj->statut;
		$this->proprietaire_id = $obj->proprietaire_id;
		
		return $obj->rowid;
	}
	function create($user) {
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_locataire (nom, telephone, email, adresse, commentaire, statut, proprietaire_id)";
		$sql .= " VALUES ('" . $this->nom . "', '" . $this->telephone . "','" . $this->email . "','" . $this->adresse . "','" . $this->commentaire . "','Actif', '". $user->id . "')";
		
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "immo_locataire" );
		}
	}
	function update($user) {
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_locataire SET nom = '" . addslashes ( $this->nom ) . "', telephone = '" . addslashes ( $this->telephone ) . "', email = '" . addslashes ( $this->email ) . "', adresse = '" . addslashes ( $this->adresse ) . "', commentaire = '" . addslashes ( $this->commentaire ) . "', statut = '" . addslashes ( $this->statut ) . "' WHERE rowid = '" . $this->rowid . "'";
		
		if ($this->db->query ( $sql )) {
			return $this->rowid;
		} else {
			dol_print_error ( $this->db );
			return - 1;
		}
	}
	function select_nom_locataire($selected = '', $htmlname = 'actionnomlocataire', $useempty = 0, $maxlen = 40, $help = 1) {
		global $db, $langs, $user;
		$sql = "SELECT l.rowid, l.nom as nomloc";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_locataire as l";
		if ($user->id != 1) {
			$sql .= " WHERE proprietaire_id=".$user->id;
		}
		$sql .= " ORDER BY l.nom ASC";
		dol_syslog ( "Form::select_nom_locataire sql=" . $sql, LOG_DEBUG );
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
					print '>' . dol_trunc ( $obj->nomloc, $maxlen );
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
		
		if (empty ( $this->reference ))
			$this->reference = $this->nom;
		
		$lien = '<a href="' . DOL_URL_ROOT . '/immobilier/locataire/fiche_locataire.php?action=update&id=' . $this->reference . '">';
		$lienfin = '</a>';
		
		if ($withpicto)
			$result .= ($lien . img_object ( $langs->trans ( "ShowProperty" ) . ': ' . $this->nom, 'bill' ) . $lienfin . ' ');
		if ($withpicto && $withpicto != 2)
			$result .= ' ';
		if ($withpicto != 2)
			$result .= $lien . ($maxlen ? dol_trunc ( $this->id, $maxlen ) : $this->id) . $lienfin;
		return $result;
	}	
}