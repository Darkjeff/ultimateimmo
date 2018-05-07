<?php
/* Copyright (C) 2013-2016	Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016  Alexandre Spangaro <aspangaro@zendsi.com>
 * Copyright (C) 2018 Philippe GRAND 	<philippe.grand@atoo-net.com>
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
 * \file		immobilier/core/modules/modules_immobilier.php
 * \ingroup		Immobilier
 * \brief		File that contain parent class for projects models
 * 				and parent class for projects numbering models
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commondocgenerator.class.php");

/**
 *	Parent class for immobilier models
 */
abstract class ModelePDFImmobilier extends CommonDocGenerator 
{
	var $error = '';
	
	/**
	 * Return list of active generation modules
	 *
	 * @param DoliDB $db handler
	 * @param string $maxfilenamelength length of value to show
	 * @return array of templates
	 */
	static function liste_modeles($db, $maxfilenamelength = 0) 
	{
		global $conf;

		$type='immobilier';
		$liste=array();

		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$liste=getListOfModels($db,$type,$maxfilenamelength);

		return $liste;
	}
}

/**
 *  Classe mere des modeles de numerotation des references de Immobilier
 */
abstract class ModeleNumRefImmobilier
{
	var $error='';

	/**
	 *  Return if a module can be used or not
	 *
	 *  @return		boolean     true if module can be used
	 */
	function isEnabled()
	{
		return true;
	}

	/**
	 *  Renvoi la description par defaut du modele de numerotation
	 *
	 *  @return     string      Texte descripif
	 */
	function info()
	{
		global $langs;
		$langs->load("immobilier@immobilier");
		return $langs->trans("NoDescription");
	}

	/**
	 *  Renvoi un exemple de numerotation
	 *
	 *  @return     string      Example
	 */
	function getExample()
	{
		global $langs;
		$langs->load("immobilier@immobilier");
		return $langs->trans("NoExample");
	}

	/**
	 *  Test si les numeros deja en vigueur dans la base ne provoquent pas de
	 *  de conflits qui empechera cette numerotation de fonctionner.
	 *
	 *  @return     boolean     false si conflit, true si ok
	 */
	function canBeActivated()
	{
		return true;
	}

	/**
	 *  Renvoi prochaine valeur attribuee
	 *
	 *	@param	Societe		$objsoc		Object third party
	 *	@param	Receipt		$object		Object receipt
	 *	@return	string					Valeur
	 */
	function getNextValue($objsoc, $object)
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**
	 *  Renvoi version du module numerotation
	 *
	 *  @return     string      Valeur
	 */
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental') return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr') return DOL_VERSION;
		if ($this->version) return $this->version;
		return $langs->trans("NotAvailable");
	}
}

/**
 * \brief Crée un document PDF
 * \param db objet base de donnees
 * \param modele modele à utiliser
 * \param		outputlangs		objet lang a utiliser pour traduction
 * \return int <0 if KO, >0 if OK
 */
function immobilier_pdf_create($db, $id, $message, $typeModele, $outputlangs, $file) 
{
	global $conf, $langs;
	$langs->load ( 'immobilier@immobilier' );
	
	// Charge le modele
	$nomModele = dol_buildpath ( '/immobilier/core/modules/immobilier/pdf/pdf_' . $typeModele . '.modules.php' );
	
	if (file_exists ( $nomModele )) {
		require_once ($nomModele);
		
		$classname = "pdf_" . $typeModele;
		
		$obj = new $classname ( $db );
		$obj->message = $message;
		
		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$sav_charset_output = $outputlangs->charset_output;
		if ($obj->write_file ( $id, $outputlangs, $file, $socid, $courrier ) > 0) {
			$outputlangs->charset_output = $sav_charset_output;
			return 1;
		} else {
			$outputlangs->charset_output = $sav_charset_output;
			dol_print_error ( $db, "pdf_create Error: " . $obj->error );
			return - 1;
		}
	} else {
		dol_print_error ( '', $langs->trans ( "Error" ) . " " . $langs->trans ( "ErrorFileDoesNotExists", $file ) );
		return - 1;
	}
}

/**
 * \brief Crée un document PDF
 * \param db objet base de donnees
 * \param modele modele à utiliser
 * \param		outputlangs		objet lang a utiliser pour traduction
 * \return int <0 if KO, >0 if OK
 */
function chargefourn_pdf_create($db, $year, $typeModele, $outputlangs, $filedir, $filename) {
	global $conf, $langs;
	$langs->load ( 'immobilier@immobilier' );

	// Charge le modele
	$nomModele = dol_buildpath ( '/immobilier/core/modules/immobilier/pdf/pdf_' . $typeModele . '.modules.php' );

	if (file_exists ( $nomModele )) {
		require_once ($nomModele);

		$classname = "pdf_" . $typeModele;

		$obj = new $classname ( $db );
		$obj->message = $message;

		// We save charset_output to restore it because write_file can change it if needed for
		// output format that does not support UTF8.
		$sav_charset_output = $outputlangs->charset_output;
		if ($obj->write_file ( $year, $outputlangs, $filedir, $filename) > 0) {
			$outputlangs->charset_output = $sav_charset_output;
			return 1;
		} else {
			$outputlangs->charset_output = $sav_charset_output;
			dol_print_error ( $db, "pdf_create Error: " . $obj->error );
			return - 1;
		}
	} else {
		dol_print_error ( '', $langs->trans ( "Error" ) . " " . $langs->trans ( "ErrorFileDoesNotExists", $file ) );
		return - 1;
	}
}
