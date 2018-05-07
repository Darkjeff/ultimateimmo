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
	/**
     * @var DoliDb Database handler
     */
    public $db;

	/**
     * @var string model name
     */
    public $name;

	/**
     * @var string model description (short text)
     */
    public $description;

    /**
     * @var int 	Save the name of generated file as the main doc when generating a doc with this template
     */
    public $update_main_doc_field;

	/**
     * @var string document type
     */
    public $type;

	/**
     * @var array() Minimum version of PHP required by module.
	 * e.g.: PHP ≥ 5.3 = array(5, 3)
     */
	public $phpmin = array(5, 2);

	/**
     * Dolibarr version of the loaded document
     * @public string
     */
	public $version = 'dolibarr';

    public $page_largeur;
    public $page_hauteur;
    public $format;
	public $marge_gauche;
	public $marge_droite;
	public $marge_haute;
	public $marge_basse;

    public $emetteur;	// Objet societe qui emet
	
	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db) 
	{
		global $conf, $langs, $mysoc;
		
		$langs->load("immobilier@immobilier");
		
		$this->db = $db;
		$this->name = 'chargefourn';
		$this->description = $langs->trans('ChargeFournisseur');
		$this->update_main_doc_field = 1;		// Save the name of generated file as the main doc when generating a doc with this template
		
		// Dimension page pour format A4 en portrait
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array (
				$this->page_largeur,
				$this->page_hauteur 
		);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;
		
		$this->option_logo = 0;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_escompte = 0;                // Affiche si il y a eu escompte
		$this->option_credit_note = 0;             // Support credit notes
		$this->option_freetext = 1;				   // Support add of a personalised text
		$this->option_draft_watermark = 1;		   // Support add of a watermark on drafts
		
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
	function write_file(&$object, $outputlangs, $filedir='', $filename='', $year='') 
	{
		global $user, $langs, $conf, $mysoc;
		
		if (! is_object($outputlangs))
			$outputlangs = $langs;
		// Translations
		$outputlangs->loadLangs(array("main", "dict", "immobilier@immobilier"));
		
		// Filter
		$year = GETPOST("year",'int');
		if ($year == 0) 
		{
			$current_year = strftime("%Y", time());
			$start_year = $current_year;
		} 
		else 
		{
			$current_year = $year;
			$start_year = $year;
		}
		
		// Definition of $dir and $file
		if ($object->specimen)
		{
			$dir = $conf->immobilier->dir_output."/receipt";
			$file = $dir . "/SPECIMEN.pdf";
		}
		else
		{
			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->immobilier->dir_output . "/receipt/" . $objectref;
			$file = $dir . "/" . $objectref . ".pdf";
		}
		
		if (! file_exists($dir)) 
		{
			if (dol_mkdir($dir) < 0) 
			{
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}
		
		if (file_exists($dir)) 
		{
			// Add pdfgeneration hook
			if (! is_object($hookmanager))
			{
				include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
				$hookmanager=new HookManager($this->db);
			}
			$hookmanager->initHooks(array('pdfgeneration'));
			$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
			global $action;
			$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
			
			// Create pdf instance
			$pdf=pdf_getInstance($this->format);
			$default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
			
			if (class_exists('TCPDF')) 
			{
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
			$sql .= " FROM " . MAIN_DB_PREFIX . "immobilier_immocost as ic";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immoproperty as ll ON ic.local_id = ll.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immobuilding as ii ON ll.immeuble_id = ii.rowid";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "immobilier_immotypologie as it ON it.rowid = ic.type AND ic.type=23";
			$sql .= " WHERE ic.date_acq >= '" . $this->db->idate(dol_get_first_day($year, 1, false)) . "'";
			$sql .= "  AND ic.date_acq <= '" . $this->db->idate(dol_get_last_day($year, 12, false)) . "'";
			if ($user->id != 1) {
				$sql .= " AND ic.owner_id=" . $user->id;
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