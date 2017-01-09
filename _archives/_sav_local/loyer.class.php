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
 * \file    immobilier/class/loyer.class.php
 * \ingroup immobilier
 * \brief   Manage rent object
 */

/**
 * Rent class
 */
class Loyer {
	var $db;
	var $id;
	var $ref;
	var $contrat_id;
	var $local_id;
	var $nomlocal;
	var $nom;
	var $locataire_id;
	var $nomlocataire;
	var $montant_tot;
	var $loy;
	var $charges;
	var $charge_ex;
	var $CommCharge;
	var $echeance;
	var $commentaire;
	var $statut;
	var $periode_du;
	var $periode_au;
	var $encours;
	var $regul;
	var $proprietaire_id;
	var $paye;
	var $paiepartiel;
	public $lines=array();
	
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
		$sql = "SELECT il.rowid as reference, il.contrat_id , il.local_id, il.nom as nomloyer, il.locataire_id, il.montant_tot,";
		$sql .= " il.loy, il.charges, il.charge_ex , il.CommCharge,  il.echeance, il.commentaire, il.statut, il.paye ,";
		$sql .= " il.periode_du , il.periode_au, il.proprietaire_id,il.paiepartiel ";
		$sql .= " , lc.nom as nomlocataire , ll.nom as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as il ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_locataire as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_local as ll ";
		$sql .= " WHERE il.locataire_id = lc.rowid AND il.local_id = ll.rowid AND il.rowid = " . $id;
		
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
				$this->nom = $obj->nomloyer;
				$this->locataire_id = $obj->locataire_id;
				$this->nomlocataire = $obj->nomlocataire;
				$this->montant_tot = $obj->montant_tot;
				$this->loy = $obj->loy;
				$this->charges = $obj->charges;
				$this->charge_ex = $obj->charge_ex;
				$this->CommCharge = $obj->CommCharge;
				$this->echeance = $this->db->jdate ( $obj->echeance );
				$this->commentaire = $obj->commentaire;
				$this->statut = $obj->statut;
				$this->periode_du = $this->db->jdate ( $obj->periode_du );
				$this->periode_au = $this->db->jdate ( $obj->periode_au );
				$this->encours = $obj->encours;
				$this->regul = $obj->regul;
				$this->proprietaire_id = $obj->proprietaire_id;
				$this->paye = $obj->paye;
				$this->paiepartiel = $obj->paiepartiel;
				
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
	
	function fetchByLocalId($id,$filter=array()) {
		
		$sql = "SELECT il.rowid as reference, il.contrat_id , il.local_id, il.nom as nomloyer, il.locataire_id, il.montant_tot,";
		$sql .= " il.loy, il.charges, il.charge_ex , il.CommCharge,  il.echeance, il.commentaire, il.statut, il.paye ,";
		$sql .= " il.periode_du , il.periode_au, il.proprietaire_id,il.paiepartiel ";
		$sql .= " , lc.nom as nomlocataire , ll.nom as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as il ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immo_locataire as lc ON il.locataire_id = lc.rowid";
		$sql .= " INNER JOIN  " . MAIN_DB_PREFIX . "immo_local as ll ON il.local_id = ll.rowid ";
		$sql .= " WHERE il.local_id = " . $id;
		
		if (count($filter>0)) {
			foreach($filter as $key=>$value) {
				if ($key=='insidedateloyer') {
					$sql .= " AND il.periode_du<='".$this->db->idate($value)."' AND il.periode_au>='".$this->db->idate($value)."'";
				}
			}
		}
	
		dol_syslog(get_class($this) . "::fetchByLocalId sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$this->line = array ();
			$num = $this->db->num_rows($resql);
			$this->lines=array();
			
			while ($obj = $this->db->fetch_object($resql)) {
				
				
				$line = new LoyerLine();
				
				$line->id = $obj->reference;
				$line->ref = $obj->reference;
				$line->contrat_id = $obj->contrat_id;
				$line->local_id = $obj->local_id;
				$line->nomlocal = $obj->nomlocal;
				$line->nom = $obj->nomloyer;
				$line->locataire_id = $obj->locataire_id;
				$line->nomlocataire = $obj->nomlocataire;
				$line->montant_tot = $obj->montant_tot;
				$line->loy = $obj->loy;
				$line->charges = $obj->charges;
				$line->charge_ex = $obj->charge_ex;
				$line->CommCharge = $obj->CommCharge;
				$line->echeance = $this->db->jdate ( $obj->echeance );
				$line->commentaire = $obj->commentaire;
				$line->statut = $obj->statut;
				$line->periode_du = $this->db->jdate ( $obj->periode_du );
				$line->periode_au = $this->db->jdate ( $obj->periode_au );
				$line->encours = $obj->encours;
				$line->regul = $obj->regul;
				$line->proprietaire_id = $obj->proprietaire_id;
				$line->paye = $obj->paye;
				$line->paiepartiel = $obj->paiepartiel;
				
				$this->lines[] = $line;

			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetchByLocalId " . $this->error, LOG_ERR);
			return - 1;
		}
	}
	
	
	function create($user) {
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_loyer (";
		$sql .= "contrat_id,";
		$sql .= " local_id,";
		$sql .= " nom,";
		$sql .= " locataire_id,";
		$sql .= " montant_tot,";
		$sql .= " loy,";
		$sql .= " charges,";
		$sql .= " charge_ex,";
		$sql .= " CommCharge,";
		$sql .= " echeance ,";
		$sql .= " commentaire,";
		$sql .= " statut,";
		$sql .= " periode_du,";
		$sql .= "periode_au ,";
		$sql .= " encours,";
		$sql .= " regul,";
		$sql .= " proprietaire_id";
		$sql .= ") VALUES (";
		$sql .= " '" . $this->contrat_id . "',";
		$sql .= " '" . $this->local_id . "',";
		$sql .= " '" . $this->nom . "',";
		$sql .= " '" . $this->locataire_id . "',";
		$sql .= " '" . $this->montant_tot . "',";
		$sql .= " '" . $this->loy . "',";
		$sql .= " '" . $this->charges . "',";
		$sql .= " '" . $this->charge_ex . "',";
		$sql .= " '" . $this->CommCharge . "',";
		$sql .= " " . ($this->echeance ? "'" . $this->db->idate ( $this->echeance ) . "'" : "null") . ",";
		$sql .= " '" . $this->commentaire . "',";
		$sql .= " '" . $this->statut . "',";
		$sql .= " " . ($this->periode_du ? "'" . $this->db->idate ( $this->periode_du ) . "'" : "null") . ",";
		$sql .= " " . ($this->periode_au ? "'" . $this->db->idate ( $this->periode_au ) . "'" : "null") . ",";
		$sql .= " '" . $this->encours . "',";
		$sql .= " '" . $this->regul . "',";
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
		
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_loyer ";
		$sql .= " SET nom = '" . $this->db->escape ( $this->nom ) . "',";
		$sql .= "  montant_tot= '" . $this->db->escape ( $this->montant_tot ) . "',";
		$sql .= "  loy = '" . $this->db->escape ( $this->loy ) . "',";
		$sql .= "  charges = '" . $this->db->escape ( $this->charges ) . "',";
		$sql .= "  charge_ex = '" . $this->db->escape ( $this->charge_ex ) . "',";
		$sql .= "  CommCharge = '" . $this->db->escape ( $this->CommCharge ) . "',";
		$sql .= "  echeance = '" . $this->db->idate ( $this->echeance ) . "',";
		$sql .= "  commentaire = '" . $this->db->escape ( $this->commentaire ) . "',";
		$sql .= "  statut = '" . $this->db->escape ( $this->statut ) . "',";
		$sql .= "  periode_du = '" . $this->db->idate ( $this->periode_du ) . "',";
		$sql .= "  periode_au= '" . $this->db->idate ( $this->periode_au ) . "',";
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
	
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."immo_loyer";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}
	
	/**
	 * Renvoie nom clicable (avec eventuellement le picto)
	 *
	 * @param int $withpicto picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 * @param int $maxlen libelle
	 * @return string avec URL
	 */
	function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;
		
		$result = '';
		
		if (empty ( $this->ref ))
			$this->ref = $this->nom;
		
		$lien = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/loyer/fiche_loyer.php?id=' . $this->id . '">';
		$lienfin = '</a>';
		
		if ($withpicto)
			$result .= ($lien . img_object ( $langs->trans ( "ShowRent" ) . ': ' . $this->nom, 'bill' ) . $lienfin . ' ');
		if ($withpicto && $withpicto != 2)
			$result .= ' ';
		if ($withpicto != 2)
			$result .= $lien . ($maxlen ? dol_trunc ( $this->ref, $maxlen ) : $this->ref) . $lienfin;
		return $result;
	}
	function select_nom_loyer($selected = '', $htmlname = 'nomloyer', $useempty = 0, $maxlen = 40, $help = 1) {
		global $db, $langs, $user;
		$sql = "SELECT l.nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as l";
		$sql .= " GROUP BY l.nom";
		$sql .= " ORDER BY l.nom ASC";
		
		dol_syslog ( "Form::select_nom_loyer sql=" . $sql, LOG_DEBUG );
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
					print '<option value="' . $obj->nom . '"';
					if ($obj->nom == $selected)
						print ' selected="selected"';
					print '>' . dol_trunc ( $obj->nom, $maxlen );
					$i ++;
				}
				print '</select>';
			}
		} else {
			dol_print_error ( $db, $db->lasterror () );
		}
	}
	function set_paid($user) {
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'immo_loyer SET';
		$sql .= ' paye=1';
		$sql .= ' WHERE rowid = ' . $this->id;
		$return = $this->db->query ( $sql );
		$this->db->commit ();
		if ($return)
			return 1;
		else
			return - 1;
	}
	function getLibStatut($mode = 0) {
		return $this->LibStatut ( $this->paye, $mode );
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
			if ($statut == 0)
				return $langs->trans ( "Unpaid" );
			if ($statut == 1)
				return $langs->trans ( "Paid" );
		}
		if ($mode == 1) {
			if ($statut == 0)
				return $langs->trans ( "Unpaid" );
			if ($statut == 1)
				return $langs->trans ( "Paid" );
		}
		if ($mode == 2) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "Unpaid" ), 'statut1' ) . ' ' . $langs->trans ( "Unpaid" );
			if ($statut == 1)
				return img_picto ( $langs->trans ( "Paid" ), 'statut6' ) . ' ' . $langs->trans ( "Paid" );
		}
		if ($mode == 3) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "Unpaid" ), 'statut1' );
			if ($statut == 1)
				return img_picto ( $langs->trans ( "Paid" ), 'statut6' );
		}
		if ($mode == 4) {
			if ($statut == 0)
				return img_picto ( $langs->trans ( "Unpaid" ), 'statut1' ) . ' ' . $langs->trans ( "Unpaid" );
			if ($statut == 1)
				return img_picto ( $langs->trans ( "Paid" ), 'statut6' ) . ' ' . $langs->trans ( "Paid" );
		}
		if ($mode == 5) {
			if ($statut == 0)
				return $langs->trans ( "Unpaid" ) . ' ' . img_picto ( $langs->trans ( "Unpaid" ), 'statut1' );
			if ($statut == 1)
				return $langs->trans ( "Paid" ) . ' ' . img_picto ( $langs->trans ( "Paid" ), 'statut6' );
		}
		
		return "Error, mode/status not found";
	}
}

class LoyerLine {
	var $id;
	var $ref;
	var $contrat_id;
	var $local_id;
	var $nomlocal;
	var $nom;
	var $locataire_id;
	var $nomlocataire;
	var $montant_tot;
	var $loy;
	var $charges;
	var $charge_ex;
	var $CommCharge;
	var $echeance;
	var $commentaire;
	var $statut;
	var $periode_du;
	var $periode_au;
	var $encours;
	var $regul;
	var $proprietaire_id;
	var $paye;
	var $paiepartiel;
	function __construct() {
		return 1;
	}
}
