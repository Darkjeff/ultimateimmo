<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2005-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
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
 *       \file       htdocs/compta/facture/class/facturestats.class.php
 *       \ingroup    factures
 *       \brief      Fichier de la classe de gestion des stats des factures
 */
include_once DOL_DOCUMENT_ROOT . '/core/class/stats.class.php';
dol_include_once ( "/immobilier/class/immo_chargedet.class.php" );
dol_include_once("/immobilier/class/charge.class.php");
include_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

/**
 *	Class to manage stats for invoices (customer and supplier)
 */
class ChargeStats extends Stats
{
    var $local_id;
    var $userid;

    public $table_element;
    var $from;
    var $field;
    var $where;
	

	/**
     * 	Constructor
     *
	 * 	@param	DoliDB		$db			Database handler
	 * 	@param 	int			$socid		Id third party for filter
	 * 	@param 	string		$mode	   	Option ('customer', 'supplier')
     * 	@param	int			$userid    	Id user for filter (creation user)
	 */
		function __construct($db) {
		$this->db = $db;
		$this->local_id = $local_id;
		return 1;
	}





	/**
	 * 	Return the invoices amount by month for a year
	 *
	 *	@param	int		$year	Year to scan
	 *	@return	array			Array with amount by month
	 */
	function getAmountByMonth($year)
	{
		global $user;

		$sql = "SELECT date_format(date_acq,'%Y') as dm,  SUM(icd.montant) as total ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_chargedet as icd ";
		$sql.= " WHERE ic.date_acq BETWEEN '".$this->db->idate(dol_get_first_day($year))."' AND '".$this->db->idate(dol_get_last_day($year))."'";
		$sql.= " AND icd.charge_id = ic.rowid";
        $sql.= " GROUP BY dm";
        $sql.= $this->db->order('dm','DESC');

		$res=$this->_getAmountByMonth($year, $sql);
		//var_dump($res);print '<br>';
		return $res;
	}

	/**
	 *	Return average amount
	 *
	 *	@param	int		$year	Year to scan
	 *	@return	array			Array of values
	 */


	/**
	 *	Return nb, total and average
	 *
	 *	@return	array	Array of values
	 */
	function getAllByYear()
	{
		global $user;

		$sql = "SELECT date_format(date_acq,'%Y') as year,  SUM(icd.montant) as total ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic ";
		$sql .= " , " . MAIN_DB_PREFIX . "immo_chargedet as icd ";
		$sql .= " WHERE icd.charge_id = ic.rowid";
	//	$sql.= " AND icd.local_id =".$this->local_id;
		$sql.= " GROUP BY year";
        $sql.= $this->db->order('year','DESC');

		return $this->_getAllByYear($sql);
	}	
}
