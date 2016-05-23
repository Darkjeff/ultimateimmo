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
 * \class loyer
 * \brief Classe permettant la gestion des loyer
 */
class Receipt {
	var $db;
	var $id;
	var $ref;
	var $fk_contract;
	var $fk_property;
	var $nomlocal;
	var $name;
	var $fk_renter;
	var $nomlocataire;
	var $amount_total;
	var $rent;
	var $charges;
	var $echeance;
	var $commentaire;
	var $statut;
	var $date_rent;
	var $date_start;
	var $date_end;
	var $balance;
	var $vat;
	var $fk_owner;
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
		$sql = "SELECT il.rowid as reference, il.fk_contract , il.fk_property, il.name as nomloyer, il.fk_renter, il.amount_total, il.name as name ,";
		$sql .= " il.rent, il.charges,   il.echeance, il.commentaire, il.statut, il.paye ,";
		$sql .= " il.date_start , il.date_end, il.fk_owner, il.paiepartiel ";
		$sql .= " , lc.nom as nomlocataire , ll.name as nomlocal ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_receipt as il ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as lc ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_property as ll ";
		$sql .= " WHERE il.fk_renter = lc.rowid AND il.fk_property = ll.rowid AND il.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->reference;
				$this->ref = $obj->reference;
				$this->name = $obj->name;
				$this->fk_contract = $obj->fk_contract;
				$this->fk_property = $obj->fk_property;
				$this->nomlocal = $obj->nomlocal;
				$this->nomloyer = $obj->nomloyer;
				$this->locataire_id = $obj->fk_renter;
				$this->nomlocataire = $obj->nomlocataire;
				$this->amount_total = $obj->amount_total;
				$this->rent = $obj->rent;
				$this->charges = $obj->charges;
				$this->echeance = $this->db->jdate ( $obj->echeance );
				$this->commentaire = $obj->commentaire;
				$this->statut = $obj->statut;
				$this->date_start = $this->db->jdate ( $obj->date_start );
				$this->date_end = $this->db->jdate ( $obj->date_end );
				$this->fk_owner = $obj->fk_owner;
				$this->paye = $obj->paye;
								
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
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_receipt (";
		$sql .= "fk_contract,";
		$sql .= " fk_property,";
		$sql .= " name,";
		$sql .= " fk_renter,";
		$sql .= " amount_total,";
		$sql .= " rent,";
		$sql .= " charges,";
		$sql .= " echeance ,";
		$sql .= " commentaire,";
		$sql .= " statut,";
		$sql .= " date_start,";
		$sql .= "date_end ,";
		$sql .= " fk_owner";
		$sql .= ") VALUES (";
		$sql .= " '" . $this->fk_contract . "',";
		$sql .= " '" . $this->fk_property . "',";
		$sql .= " '" . $this->name . "',";
		$sql .= " '" . $this->fk_renter . "',";
		$sql .= " '" . $this->amount_total . "',";
		$sql .= " '" . $this->rent . "',";
		$sql .= " '" . $this->charges . "',";
		$sql .= " " . ($this->echeance ? "'" . $this->db->idate ( $this->echeance ) . "'" : "null") . ",";
		$sql .= " '" . $this->commentaire . "',";
		$sql .= " '" . $this->statut . "',";
		$sql .= " " . ($this->date_start ? "'" . $this->db->idate ( $this->date_start ) . "'" : "null") . ",";
		$sql .= " " . ($this->date_end ? "'" . $this->db->idate ( $this->date_end ) . "'" : "null") . ",";
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
		
		$lien = '<a href="' . DOL_URL_ROOT . '/custom/immobilier/receipt/card.php?id=' . $this->id . '">';
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
