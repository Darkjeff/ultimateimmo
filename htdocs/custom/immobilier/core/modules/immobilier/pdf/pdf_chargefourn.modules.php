<?php
/**
 * Copyright (C) 2012-2013 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2018 Philippe GRAND 	<philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file immobilier/core/modules/immobilier/pdf/pdf_chargefourn.module.php
 * \ingroup immobilier
 * \brief PDF for immobilier
 */
dol_include_once('/immobilier/core/modules/immobilier/modules_immobilier.php');
dol_include_once('/immobilier/class/immorenter.class.php');
dol_include_once('/immobilier/class/local.class.php');
dol_include_once('/immobilier/class/immorent.class.php');
dol_include_once('/immobilier/class/immopayment.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php');

class pdf_chargefourn extends ModelePDFImmobilier 
{
	var $emetteur; // Objet societe qui emet
	
	/**
	 * \brief		Constructor
	 * \param		db		Database handler
	 */
	function __construct($db) 
	{
		global $conf, $langs, $mysoc;
		
		$langs->load("immobilier@immobilier");
		
		$this->db = $db;
		$this->name = 'chargefourn';
		$this->description = $langs->trans('ChargeFournisseur');
		
		// Dimension page pour format A4 en portrait
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array (
				$this->page_largeur,
				$this->page_hauteur 
		);
		$this->marge_gauche = 15;
		$this->marge_droite = 15;
		$this->marge_haute = 10;
		$this->marge_basse = 10;
		$this->unit = 'mm';
		$this->oriantation = 'P';
		$this->espaceH_dispo = $this->page_largeur - ($this->marge_gauche + $this->marge_droite);
		$this->milieu = $this->espaceH_dispo / 2;
		$this->espaceV_dispo = $this->page_hauteur - ($this->marge_haute + $this->marge_basse);
		
		// Get source company
		$this->emetteur = $mysoc;
		if (! $this->emetteur->country_code)
			$this->emetteur->country_code = substr($langs->defaultlang, - 2); // By default, if was not defined
	}
	
	/**
	 * \brief Fonction generant le document sur le disque
	 * \param agf		Objet document a generer (ou id si ancienne methode)
	 * outputlangs	Lang object for output language
	 * file		Name of file to generate
	 * \return int 1=ok, 0=ko
	 */
	function write_file(&$object, $outputlangs, $filedir, $filename, $year) {
		global $user, $langs, $conf, $mysoc;
		
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		
		if (! is_object($outputlangs))
			$outputlangs = $langs;
		// Translations
		$outputlangs->loadLangs(array("main", "dict", "immobilier@immobilier"));
		
		// Filter
		$year = GETPOST("year",'int');
		if ($year == 0) {
			$current_year = strftime("%Y", time());
			$start_year = $current_year;
		} else {
			$current_year = $year;
			$start_year = $year;
		}
		
		// Definition of $dir and $file
		//$dir = $filedir;
		//$file = $dir. $filename;
		// Definition of $dir and $file
		if ($object->specimen)
		{
			$dir = $conf->immobilier->dir_output;
			$file = $dir . "/SPECIMEN.pdf";
		}
		else
		{
			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->immobilier->dir_output . "/" . $objectref;
			$file = $dir . "/" . $objectref . ".pdf";
		}
		
		if (! file_exists($dir)) {
			if (dol_mkdir($dir) < 0) 
			{
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}
		
		if (file_exists($dir)) 
		{
			
			$pdf = pdf_getInstance($this->format, $this->unit, $this->orientation);
			
			if (class_exists('TCPDF')) {
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}
			
			$pdf->Open();
			$pagenb = 0;
			
			$pdf->SetTitle($outputlangs->convToOutputCharset($loyer->nom));
			$pdf->SetSubject($outputlangs->transnoentities("ChargeFournisseur"));
			$pdf->SetCreator("Dolibarr " . DOL_VERSION . ' (Immobilier module)');
			$pdf->SetAuthor($outputlangs->convToOutputCharset($user->fullname));
			$pdf->SetKeyWords($outputlangs->convToOutputCharset($loyer->nom) . " " . $outputlangs->transnoentities("Document"));
			if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION)
				$pdf->SetCompression(false);
			
			$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right
			$pdf->SetAutoPageBreak(1, 0);
			
			$pdf->AddPage();
			$pagenb ++;
			$this->_pagehead($pdf, $outputlangs);
			$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 9);
			$pdf->MultiCell(0, 3, '', 0, 'J');
			$pdf->SetTextColor(0, 0, 0);
			
			$posY = $this->marge_haute;
			$posX = $this->marge_gauche;
			
			$year = $current_year;
			//var_dump($year);
			// Total par immeuble
			$sql = "SELECT ";
			$sql .= " SUM(ic.montant_ttc) as total,";
			$sql .= " ii.nom as nomimmeuble";
			$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immolocal as ll ON ic.local_id = ll.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immoproperty as ii ON ll.immeuble_id = ii.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immotypologie as it ON it.rowid = ic.type AND ic.type=23";
			$sql .= " WHERE ic.date_acq >= '" . $this->db->idate(dol_get_first_day($year, 1, false)) . "'";
			$sql .= "  AND ic.date_acq <= '" . $this->db->idate(dol_get_last_day($year, 12, false)) . "'";
			if ($user->id != 1) {
				$sql .= " AND ic.proprietaire_id=" . $user->id;
			}
			$sql .= " GROUP BY ii.nom";
			$sql .= " ORDER BY ii.nom";
			
				
			dol_syslog(get_class($this) . ':: sql total immeuble=' . $sql);
			$resql = $this->db->query($sql);
			if ($resql) {
				
				while ( $obj = $this->db->fetch_object($resql) ) {
					$posX = $this->marge_gauche;
					
					$str = $obj->nomimmeuble;
					$posY = $pdf->GetY() + 1;
					$pdf->SetXY($posX, $posY);
					//$pdf->writeCell(30, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 1, false, false, 'L', true);
					$pdf->MultiCell(40, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
					
					$str = price($obj->total, 0, $outputlangs, 1, - 1, - 1, $conf->currency);
					$posX += 40;
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell(30, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
				}
			}
			
			// Total par fournisseur
			$sql = "SELECT ";
			$sql .= " SUM(ic.montant_ttc) as total,";
			$sql .= " ic.fournisseur";
			$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immolocal as ll ON ic.local_id = ll.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immoproperty as ii ON ll.immeuble_id = ii.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immotypologie as it ON it.rowid = ic.type AND ic.type=23";
			$sql .= " WHERE ic.date_acq >= '" . $this->db->idate(dol_get_first_day($year, 1, false)) . "'";
			$sql .= "  AND ic.date_acq <= '" . $this->db->idate(dol_get_last_day($year, 12, false)) . "'";
			if ($user->id != 1) {
				$sql .= " AND ic.proprietaire_id=" . $user->id;
			}
			$sql .= " GROUP BY ic.fournisseur";
			$sql .= " ORDER BY ic.fournisseur";
			
			
			dol_syslog(get_class($this) . ':: sql=' . $sql);
			$resql = $this->db->query($sql);
			if ($resql) {
			
				while ( $obj = $this->db->fetch_object($resql) ) {
					$posX = $this->marge_gauche;
						
					$str = $obj->fournisseur;
					$posY = $pdf->GetY() + 1;
					$pdf->SetXY($posX, $posY);
					//$pdf->writeCell(30, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 1, false, false, 'L', true);
					$pdf->MultiCell(40, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
						
					$str = price($obj->total, 0, $outputlangs, 1, - 1, - 1, $conf->currency);
					$posX += 40;
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell(30, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
				}
			}

			// On recupere les infos des charges
			$sql = "SELECT ic.date_acq,";
			$sql .= " ic.fournisseur,";
			$sql .= " ic.libelle,";
			$sql .= " ic.montant_ttc,";
			$sql .= " ii.nom as nomimmeuble";
			//$sql .= " ,it.type as travauxlib";
			$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge as ic";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immolocal as ll ON ic.local_id = ll.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immoproperty as ii ON ll.immeuble_id = ii.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immotypologie as it ON it.rowid = ic.type AND ic.type=23";
			$sql .= " WHERE ic.date_acq >= '" . $this->db->idate(dol_get_first_day($year, 1, false)) . "'";
			$sql .= "  AND ic.date_acq <= '" . $this->db->idate(dol_get_last_day($year, 12, false)) . "'";
			if ($user->id != 1) {
				$sql .= " AND ic.proprietaire_id=" . $user->id;
			}
			$sql .= " ORDER BY ii.nom,ic.fournisseur,ic.date_acq";
			
			dol_syslog(get_class($this) . ':: sql=' . $sql);
			$resql = $this->db->query($sql);
			if ($resql) {
				$num = $this->db->num_rows($resql);
				
				while ( $obj = $this->db->fetch_object($resql) ) {
					$posX = $this->marge_gauche;
	
					$str = $obj->nomimmeuble;
					
					$posY = $pdf->GetY() + 1;
					$pdf->SetXY($posX, $posY);
					//$pdf->writeCell(30, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 1, false, false, 'L', true);
					$pdf->MultiCell(40, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
					//$pdf->writeHTMLCell(30, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 0, false, true, 'L', true);
		
					/*$str = $obj->travauxlib;
					$posX = $pdf->GetX();
					$pdf->SetXY($posX, $posY);
					$pdf->writeHTMLCell(50, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 1, false, true, 'L', true);*/
					
					$str = $obj->fournisseur;
					$posX +=40;
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell(30, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
					//$pdf->writeHTMLCell(30, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 0, false, true, 'L', true);
					
					$str = dol_print_date($this->db->jdate($obj->date_acq), 'daytext');
					$posX += 30;
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell(30, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
					//$pdf->writeHTMLCell(30, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 0, false, true, 'L', true);
					
					$str = price($obj->montant_ttc, 0, $outputlangs, 1, - 1, - 1, $conf->currency);
					$posX += 30;
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell(30, 4, $outputlangs->convToOutputCharset($str), 1, 'L');
					//$pdf->writeHTMLCell(30, 4, $posX, $posY, $outputlangs->convToOutputCharset($str), 1, 0, false, true, 'L', true);
	
				}
			}
			$this->db->free($resql);
			
			$pdf->Close();
			
			$pdf->Output($file, 'F');
			if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));
			
			return 1; // Pas d'erreur
		} else {
			$this->error = $langs->trans("ErrorConstantNotDefined", "AGF_OUTPUTDIR");
			return 0;
		}
		$this->error = $langs->trans("ErrorUnknown");
		return 0; // Erreur par defaut
	}
	
	/**
	 * \brief Show header of page
	 * \param pdf Object PDF
	 * \param object Object invoice
	 * \param showaddress 0=no, 1=yes
	 * \param outputlangs		Object lang for output
	 */
	function _pagehead(&$pdf, $outputlangs) 
	{
		global $conf, $langs;
		
		$outputlangs->load("main");
		
		pdf_pagehead($pdf, $outputlangs, $pdf->page_hauteur);
	}
}