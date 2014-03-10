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


require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");

/**
 * \class immeuble
 * \brief Classe permettant la gestion des immeuble
 */
class Immeuble extends CommonObject {
	var $db;
	var $id;
	var $ref;
	var $nom;
	var $numero;
	var $street;
	var $zipcode;
	var $town;
	var $fk_departement;
	var $fk_pays;
	var $longitude;
	var $latitude;
	var $commentaire;
	var $nblocaux;
	var $statut;
	var $status_array;
	var $proprietaire_id;
	var $element='immo_immeuble';			//!< Id that identify managed objects
	var $table_element='immo_immeuble';		//!< Name of table without prefix where object is stored
	
	
	
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
		
		global $user;
		
				if ($rowid || $nom) {
			$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immo_immeuble WHERE ";
			if ($rowid) {
				$sql .= " rowid = '" . $rowid . "'";
			} elseif ($nom) {
				$sql .= " nom = '" . $nom . "'";
			}
			
			if ($user->id != 1) {
				$sql .= " AND proprietaire_id=".$user->id;
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
		$this->nblocaux = $obj->nb_locaux;
		$this->numero = $obj->numero;
		$this->street = $obj->street;
		$this->zipcode = $obj->zipcode;
		$this->town = $obj->town;
		$this->statut = $obj->statut;
		$this->fk_departement = $obj->fk_departement;
		$this->fk_pays = $obj->fk_pays;
		$this->longitude = $obj->longitude;
		$this->latitude = $obj->latitude;
		
		$this->proprietaire_id = $obj->proprietaire_id;
		
			return $obj->rowid;
	}
	function create($user) {
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_immeuble (";
		$sql .= " nom,";
		$sql .= " numero,";
		$sql .= " nb_locaux,";
		$sql .= " commentaire,";
		$sql .= " statut,";
		$sql .= " street,";
		$sql .= " zipcode,";
		$sql .= " town,";
		$sql .= " longitude,";
		$sql .= " latitude,";
		$sql .= " proprietaire_id";
		$sql .= " ) VALUES (";
		$sql .= " '" . $this->nom . "',";
		$sql .= " '" . $this->numero . "',";
		$sql .= "'" . $this->nblocaux . "',";
		$sql .= "'" . $this->commentaire . "',";
		$sql .= "'Actif',";
		$sql .= "'" . $this->street . "',";
		$sql .= "'" . $this->zipcode . "',";
		$sql .= "'" . $this->town . "',";
		$sql .= "'" . $this->longitude . "',";
		$sql .= "'" . $this->latitude . "',";
		$sql .= "'" . $user->id . "'";
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
	function update() {
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_immeuble SET nom = '" . addslashes ( $this->nom ) . "', adresse_id = '" . addslashes ( $this->adresse_id ) . "', nb_locaux = '" . addslashes ( $this->nblocaux ) . "', millieme = '" . addslashes ( $this->millieme ) . "', commentaire = '" . addslashes ( $this->commentaire ) . "', statut = '" . addslashes ( $this->statut ) . "' WHERE rowid = '" . $this->rowid . "'";
		if ($this->db->query ( $sql )) {
			return $this->rowid;
		} else {
			dol_print_error ( $this->db );
			return - 1;
		}
	}
	
	function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;
		
		$result = '';
		
		if (empty ( $this->ref ))
			$this->ref = $this->nom;
		
		$lien = '<a href="' . DOL_URL_ROOT . '/immobilier/immeuble/fiche_immeuble.php?action=update&id=' . $this->id . '">';
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

?>
