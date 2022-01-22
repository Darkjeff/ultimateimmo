<?php
/* Copyright (C) 2010-2012	Regis Houssin		<regis.houssin@capnetworks.com>
 * Copyright (C) 2010		Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2018-2022  Philippe GRAND       <philippe.grand@atoo-net.com>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file       ultimateimmo/core/modules/ultimateimmo/mod_ultimateimmo_payment.php
 *	\ingroup    ultimateimmo
 *	\brief      File of class to manage ultimateimmo numbering rules payment
 */

dol_include_once('/ultimateimmo/core/modules/ultimateimmo/modules_ultimateimmo.php');


/**
 * 	Class to manage the numbering module payment for ultimateimmo references
 */
class mod_ultimateimmo_payment extends ModeleNumRefUltimateimmo
{
	/**
     * Dolibarr version of the loaded document
     * @public string
     */
	public $version = 'dolibarr';	// 'development', 'experimental', 'dolibarr'

	public $prefix = 'PAY';

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var string model name
	 */
	public $name = 'payment';
    
	/**
	 * @var int Automatic numbering
	 */
	public $code_auto = 1;



    /**
     *  Return description of numbering module
     *
     *  @return     string      Text with description
     */
    function info()
    {
    	global $langs;
      	return $langs->trans("SimpleNumRefModelDesc", $this->prefix);
    }


    /**
     *  Return an example of numbering module values
     *
     * 	@return     string      Example
     */
    public function getExample()
    {
        return $this->prefix."2022-0001";
    }


    /**
     *  Checks if the numbers already in force in the data base do not
     *  cause conflicts that would prevent this numbering from working.
	 *
	 *  @return     boolean     false if conflict, true if ok
	 */
	public function canBeActivated()
    {
    	global $conf, $langs, $db;

        $coyymm = ''; $max = '';

		$posindice = 8;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
        $sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immopayment";
		$sql.= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
        $sql.= " AND entity = ".$conf->entity;
		
        $resql = $db->query($sql);
        if ($resql)
        {
            $row = $db->fetch_row($resql);
            if ($row) { $coyymm = substr($row[0], 0, 6); $max = $row[0]; }
        }
        if (! $coyymm || preg_match('/'.$this->prefix.'[0-9][0-9][0-9][0-9]/i', $coyymm))
        {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		return true;
    }


	/**
	 * 	Return next free value
	 *
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
	public function getNextValue($object)
    {
		global $db, $conf;

		// D'abord on recupere la valeur max
		$posindice = 8;
		$sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
		$sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immopayment";
		$sql.= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
		$sql.= " AND entity = ".$conf->entity;

		$resql = $db->query($sql);
		if ($resql)
		{
			$obj = $db->fetch_object($resql);
			if ($obj) $max = intval($obj->max);
			else $max = 0;
		}
		else
		{
			dol_syslog("mod_ultimateimmo_payment::getNextValue", LOG_DEBUG);
			return -1;
		}

		$date = empty($object->date_creation) ? dol_now() : $object->date_creation;
		$yymm = strftime("%y%m",$date);

		if ($max >= (pow(10, 4) - 1)) $num = $max+1;	// If counter > 9999, we do not format on 4 chars, we take number as it is
		else $num = sprintf("%04s", $max+1);

		dol_syslog("mod_ultimateimmo_payment::getNextValue return ".$this->prefix.$yymm."-".$num);
		return $this->prefix.$yymm."-".$num;
    }


    /**
     * 	Return next reference not yet used as a reference
     *
     *  @param	Societe	$objsoc     Object third party
     *  @param  object	$object	Object object
     *  @return string      		Next not used reference
     */
    function ultimateimmo_get_num($object = '')
    {
        return $this->getNextValue($object);
    }
}

