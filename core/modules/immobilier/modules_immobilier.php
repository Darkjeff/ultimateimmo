<?php
/**
 * Copyright (C) 2010 Regis Houssin <regis@dolibarr.fr>
 * Copyright (C) 2012 Florian Henry <florian.henry@open-concept.pro>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 * \file immobilier/modules/immobilier/modules_agefodd.php
 * \ingroup project
 * \brief File that contain parent class for projects models
 * and parent class for projects numbering models
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commondocgenerator.class.php");

/**
 * \class ModelePDFCommandes
 * \brief Classe mere des modeles de commandes
 */
abstract class ModelePDFImmobilier extends CommonDocGenerator {
	var $error = '';
	
	/**
	 * Return list of active generation modules
	 *
	 * @param DoliDB $db handler
	 * @param string $maxfilenamelength length of value to show
	 * @return array of templates
	 */
	static function liste_modeles($db, $maxfilenamelength = 0) {
		global $conf;
		
		$type = 'immobilier';
		$liste = array ();
		
		$liste [] = 'immobilier';
		
		return $liste;
	}
}

/**
 * \brief Crée un document PDF
 * \param db objet base de donnee
 * \param modele modele à utiliser
 * \param		outputlangs		objet lang a utiliser pour traduction
 * \return int <0 if KO, >0 if OK
 */
function immobilier_pdf_create($db, $id, $message, $typeModele, $outputlangs, $file) {
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

?>