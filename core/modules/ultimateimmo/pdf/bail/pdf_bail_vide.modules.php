<?php
/**
 * Copyright (C) 2012-2013 Florian Henry  <florian.henry@open-concept.pro>
 * Copyright (C) 2018-2019 Philippe GRAND <philippe.grand@atoo-net.com>
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
 * \file ultimateimmo/core/modules/ultimateimmo/pdf/pdf_bail_vide.module.php
 * \ingroup ultimateimmo
 * \brief PDF for ultimateimmo
 */

dol_include_once('/ultimateimmo/core/modules/ultimateimmo/modules_ultimateimmo.php');
dol_include_once('/ultimateimmo/class/html.formultimateimmo.class.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immoproperty_type.class.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immoowner_type.class.php');
dol_include_once('/ultimateimmo/class/immopayment.class.php');
dol_include_once('/ultimateimmo/class/myultimateimmo.class.php');
dol_include_once('/ultimateimmo/lib/ultimateimmo.lib.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');

class pdf_bail_vide extends ModelePDFUltimateimmo
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
	 * e.g.: PHP ≥ 5.4 = array(5, 4)
     */
	public $phpmin = array(5, 4); 

	/**
     * Dolibarr version of the loaded document
     * @public string
     */
	public $version = 'dolibarr';

	/**
     * @var int page_largeur
     */
    public $page_largeur;
	
	/**
     * @var int page_hauteur
     */
    public $page_hauteur;
	
	/**
     * @var array format
     */
    public $format;
	
	/**
     * @var int marge_gauche
     */
	public $marge_gauche;
	
	/**
     * @var int marge_droite
     */
	public $marge_droite;
	
	/**
     * @var int marge_haute
     */
	public $marge_haute;
	
	/**
     * @var int marge_basse
     */
	public $marge_basse;

	/**
	 * Issuer
	 * @var Societe
	 */
    public $emetteur;	// Objet societe qui emet

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf, $langs, $mysoc;

		$langs->load("ultimateimmo@ultimateimmo");

		$this->db = $db;
		$this->name = 'bail_vide';
		$this->description = $langs->trans('EmptyHousing');
		$this->update_main_doc_field = 1;		// Save the name of generated file as the main doc when generating a doc with this template

		// Dimension page pour format A4 en portrait
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;                    // Affiche logo
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
	 * \param agf Objet document a generer (ou id si ancienne methode)
	 * outputlangs Lang object for output language
	 * file Name of file to generate
	 * \return int 1=ok, 0=ko
	 */
	function write_file($object, $outputlangs, $file='', $socid=null, $courrier=null)
	{
		global $user, $langs, $conf, $mysoc, $hookmanager;

		// Translations
		$outputlangs->loadLangs(array("main", "ultimateimmo@ultimateimmo", "companies"));

		if (! is_object($outputlangs))
			$outputlangs = $langs;

		/*if (! is_object($object)) {
			$id = $object;
			$object = new Immoreceipt($this->db);
			$ret = $object->fetch($id);
		}*/

		// dol_syslog ( "pdf_quittance::debug loyer=" . var_export ( $object, true ) );

		// Definition of $dir and $file
		if ($object->specimen)
		{
			$dir = $conf->ultimateimmo->dir_output."/";
			$file = $dir . "/SPECIMEN.pdf";
		}
		else
		{
			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->ultimateimmo->dir_output . "/rent/" . $objectref;
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
			
			// Set nblignes with the new facture lines content after hook
			$nblignes = count($object->lines);
			//$nbpayments = count($object->getListOfPayments()); TODO : add method

			// Create pdf instance
			$pdf=pdf_getInstance($this->format);
			$default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
			$pdf->SetAutoPageBreak(1, 0);

			$heightforinfotot = 50+(4*$nbpayments);	// Height reserved to output the info and total part and payment part
			$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
			$heightforfooter = $this->marge_basse + (empty($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS)?12:22);	// Height reserved to output the footer (value include bottom margin)

			if (class_exists('TCPDF'))
			{
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}
			$pdf->SetFont(pdf_getPDFFont($outputlangs));
			
			// Set path to the background PDF File
			if (! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
			{
				$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
				$tplidx = $pdf->importPage(1);
			}

			$pdf->Open();
			$pagenb = 0;
			
			if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

			$pdf->SetTitle($outputlangs->convToOutputCharset($object->label));
			$pdf->SetSubject($outputlangs->transnoentities("EmptyHousing"));
			$pdf->SetCreator("Dolibarr " . DOL_VERSION . ' (ultimateimmo module)');
			$pdf->SetAuthor($outputlangs->convToOutputCharset($user->firstname)." ".$outputlangs->convToOutputCharset($user->lastname));
			$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->label) . " " . $outputlangs->transnoentities("Document"));
			if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

			$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right

			// On recupere les infos societe
			$renter = new ImmoRenter($this->db);
			$result = $renter->fetch($object->fk_renter);
			
			$rent = new ImmoRent($this->db);
			$result = $rent->fetch($object->fk_rent);

			$owner = new ImmoOwner($this->db);
			$result = $owner->fetch($object->fk_owner);
			
			$ownertype = new ImmoOwner_Type($this->db);
			$result = $ownertype->fetch($object->fk_owner_type);

			$property = new ImmoProperty($this->db);
			$result = $property->fetch($object->fk_property);
			
			$propertytype = new ImmoProperty_Type($this->db);
			$result = $propertytype->fetch($object->fk_property_type);

			$paiement = new Immopayment($this->db);
			$result = $paiement->fetch_by_loyer($object->id);

			if (! empty($object->id))
			{
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->SetTextColor(0, 0, 0);

				$tab_top = 90;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?42:10);
				$tab_height = 130;
				$tab_height_newpage = 150;
				
				$hautcadre=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 38 : 40;
				$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
				$posY = $this->marge_haute + $hautcadre +100;
				$posX = $this->marge_gauche;	
				
				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;
				
				$sql = "SELECT io.rowid, io.fk_owner_type, it.rowid, it.ref, it.label ";
				$sql .= " FROM " .MAIN_DB_PREFIX."ultimateimmo_immoowner as io";
				$sql .= " JOIN " .MAIN_DB_PREFIX."ultimateimmo_immoowner_type as it ";
				$sql .= " WHERE it.rowid = io.fk_owner_type";

				dol_syslog(get_class($this) . ':: pdf_bail_vide', LOG_DEBUG);
				$resql = $this->db->query($sql);
				
				if ($resql) 
				{
					$num = $this->db->num_rows($resql);
					while ( $i < $num ) 
					{
						$objp = $this->db->fetch_object($resql);
						$i++;
					}
				}
				//$text .= "\n";
				//$text .= 'Fait à ' . $owner->town . ' le ' . dol_print_date(dol_now(), 'daytext') . "\n";				
				/*$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 10);
				$pdf->SetXY($posX, $posY-12);
				$pdf->MultiCell($widthbox, 0, $outputlangs->convToOutputCharset($text), 0, 'L');*/
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', $default_font_size + 4);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('LOCAUX'), 1, 'C');
				
				$sql = "SELECT ip.rowid, ip.fk_property_type, ip.juridique as juridique, ip.datebuilt, pt.rowid as property_type_id, pt.ref, pt.label as type_habitat, ir.rowid, ir.fk_property, ir.location_type_id, ir.date_start, rt.rowid, rt.label as location_type, ij.rowid ";
				$sql .= " FROM " .MAIN_DB_PREFIX."ultimateimmo_immoproperty as ip";
				$sql .= " , " .MAIN_DB_PREFIX."ultimateimmo_immoproperty_type as pt ";
				$sql .= " , " .MAIN_DB_PREFIX."ultimateimmo_immorent as ir ";
				$sql .= " , " .MAIN_DB_PREFIX."c_ultimateimmo_immorent_type as rt ";
				$sql .= " , " .MAIN_DB_PREFIX."c_ultimateimmo_juridique as ij ";
				$sql .= " WHERE ip.fk_property_type = pt.rowid AND ip.rowid = ir.fk_property AND ir.rowid = ".$object->id;

				dol_syslog(get_class($this) . ':: pdf_bail_vide', LOG_DEBUG);
				$resql = $this->db->query($sql);
				
				if ($resql) 
				{
					$num = $this->db->num_rows($resql);
					while ( $j < $num ) 
					{
						$objproperty = $this->db->fetch_object($resql);
						$j++;
						//var_dump($objproperty);exit;
					}
				}
				
				$formultimateimmo = new FormUltimateimmo($code);

				$text = $outputlangs->transnoentities("Adresse : ").$property->address.' '.$outputlangs->transnoentities("/ bâtiment : ").$property->building.' '.$outputlangs->transnoentities("/escalier : ").$property->staircase.' '. $outputlangs->transnoentities("/étage : ").$property->numfloor.' '. $outputlangs->transnoentities("/porte : ").$property->numdoor."\n" ;
				$text .= $property->zip.' '.$property->town.' '.$property->country."\n";
				$widthrecbox=$this->page_largeur-$this->marge_gauche-$this->marge_droite;
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', $default_font_size + 1);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, $hautcadre/2, $text, 1, 'L', 1);
				
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Consistance'), 1, 'L',1);
				$posYL = $pdf->getY();

				$pdf->SetXY($posX+$widthrecbox/3, $posY);
				$pdf->MultiCell($widthrecbox*2/3, 3, $outputlangs->convToOutputCharset('Désignation des locaux et équipements privatifs:'), 1, 'L',1);
				$posYR = $pdf->getY();
				$pdf->SetXY($posX, $posYR);
				$pdf->MultiCell($widthrecbox/3, $hautcadre, '', 1, 'L', 1);
				$pdf->SetXY($posX+$widthrecbox/3, $posYR);
				$pdf->MultiCell($widthrecbox*2/3, $hautcadre, '', 1, 'L', 1);
				
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthrecbox/3+2, $posYR+4);
				$text = $outputlangs->transnoentities("- Nombre de pièces principales : ") .$property->numroom."\n";
				$pdf->MultiCell(80, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posX+$widthrecbox/3+2, $posYR+8);
				$text = $outputlangs->transnoentities("(destinées au séjour ou au sommeil, éventuellement chambres isolées .... au sens de l'article R. 111-1 al.3 du CCH)")."\n";
				$pdf->MultiCell(60, 3, $text, 0, 'L');
				$posYR = $pdf->getY();
				
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthrecbox/3+2, $posYR+4);
				$text = $outputlangs->transnoentities("- Surface ou volume habitable : ") .$property->area."\n";
				$pdf->MultiCell(80, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posX+$widthrecbox/3+2, $posYR+8);
				$text = $outputlangs->transnoentities("(au sens de l'article R. 111-2 al.2 et 3 du CCH)")."\n";
				$pdf->MultiCell(60, 3, $text, 0, 'L');
				
				$pdf->rect($posX+2, $posYL+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posYL+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posYL+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Appartement'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Maison individuelle'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('...........'), 0, 'L');
				$posY = $pdf->getY()+2;
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', $default_font_size + 1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Dépendances'), 1, 'L',1);
				$posY = $pdf->getY()+1;
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Garage n°'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Place de stationnement n°'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Cave n°'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('...........'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->SetFont('', 'B', $default_font_size);
				$pdf->SetXY($posX+6, $posY+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Énumération des parties et équipements communs'), 0, 'C');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Gardiennage'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('lnterphone'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Ascenseur'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Vide-ordures'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Antenne TV collective'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Espace(s) vert(s)'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Chauffage Collectif'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY ($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Eau chaude collective'), 0, 'L');
				$posY = $pdf->getY();
				
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');
				
				/*$text .= $outputlangs->transnoentities("- type d'habitat : ").$formultimateimmo->getPropertyTypeLabel($objproperty->property_type_id)."\n";
				
				$text .= $outputlangs->transnoentities("- régime juridique de l'immeuble : ").$formultimateimmo->getLabelFormeJuridique($objproperty->juridique)."\n" ;
				
				$text .= $outputlangs->transnoentities("- période de construction : ").$formultimateimmo->getLabelBuiltDate($property->datebuilt)."\n" ;

				$text .= $outputlangs->transnoentities("- surface habitable : ") .$property->area."\n";
				
				$text .= $outputlangs->transnoentities("- nombre de pièces principales : ") .$property->numroom."\n";
				$text .= $outputlangs->transnoentities("- le cas échéant, Autres parties du logement : [exemples : grenier, comble aménagé ou non, terrasse, balcon, loggia, jardin etc.] ;
- le cas échéant, Eléments d'équipements du logement : [exemples : cuisine équipée, détail des installations sanitaires etc.] ;
- modalité de production de chauffage : [individuel ou collectif] (4) ;
- modalité de production d'eau chaude sanitaire : [individuelle ou collective] (5).
B. Destination des locaux : [usage d'habitation ou usage mixte professionnel et d'habitation]
C. Le cas échéant, Désignation des locaux et équipements accessoires de l'immeuble à usage privatif du locataire : [exemples : cave, parking, garage etc.]
D. Le cas échéant, Enumération des locaux, parties, équipements et accessoires de l'immeuble à usage commun : [Garage à vélo, ascenseur, espaces verts, aires et équipements de jeux, laverie, local poubelle, gardiennage, autres prestations et services collectifs etc.]
E. Le cas échéant, Equipement d'accès aux technologies de l'information et de la communication : [exemples : modalités de réception de la télévision dans l'immeuble, modalités de raccordement internet etc.]");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');*/

				/*$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');

				$text = $outputlangs->transnoentities(" Le présent contrat est conclu entre les soussignés :\n\n");
				// [nom et prénom, ou dénomination du bailleur/ domicile ou siège social/ qualité du bailleur (personne physique, personne morale (1))/ adresse électronique (facultatif)] (2)
				$text .= $outputlangs->convToOutputCharset($owner->getFullName($outputlangs))."\n";
				$carac_emetteur .= $owner->address . "\n";
				$carac_emetteur .= $owner->zip . ' ' . $owner->town."\n";
				$text .=  $carac_emetteur."\n"; 
				$text .= 'En tant que '.$objp->label.' désigné (s) ci-après le bailleur'."\n" ;	
				
				if (!empty($conf->global->ULTIMATEIMMO_MANDATAIRE_DETAILS)){ 
				$text .= $outputlangs->transnoentities("
- le cas échéant, représenté par le mandataire :
- [nom ou raison sociale et adresse du mandataire ainsi que l'activité exercée] ;
- le cas échéant, [numéro et lieu de délivrance de la carte professionnelle/ nom et adresse du garant] (3).\n\n");}
				if (!empty($conf->global->ULTIMATEIMMO_COLOCATAIRE_DETAILS)){ 
				$text .= $outputlangs->transnoentities("[nom et prénom du ou des locataires ou, en cas de colocation, des colocataires, adresse électronique (facultatif)]\n\n");}
				$renter = new ImmoRenter($this->db);
				$result = $renter->fetch($object->fk_renter);
				$carac_client_name= $outputlangs->convToOutputCharset($renter->getFullName($outputlangs));
				$text .=  $carac_client_name."\n";
				
				$property = new ImmoProperty($this->db);
				$result = $property->fetch($object->fk_property);
				//$carac_client .= $property->label . "\n";
				$carac_client .= $property->address . "\n";
				$carac_client .= $property->zip . ' ' . $property->town."\n\n";
				$text .=  $carac_client;
				$text .= $outputlangs->transnoentities("désigné (s) ci-après le locataire\n
Il a été convenu ce qui suit :\n\n");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');*/
								
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $tab_top_newpage);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('FIXATION - RÉVISION DU LOYER'), 1, 'C');

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', $default_font_size-1);
				$posY = $pdf->getY()+4;
				$pdf->SetXY($posX, $posY);				
				$text = $outputlangs->transnoentities("<strong> MONTANT OU LOYER (voir conditions générales chapitre 1) :<br> 
				Il est fixé librement entre les parties en application de l'article 17 a) et de l'article 17 b) de la loi Cependant pour les baux contractés entre le 01.08.2013 et le 31.07.2014 et UNIQUEMENT dans les communes menlionnées par l'annexe du décret n°2013-689 OU 30.07.2013 fixant un montant maximum d'évolution des loyers, conformément à l'article 18 de la loi, le loyer des logements vacants définis à l'article 17 b) ne peut excéder le dernier loyer appliqué au précédent locataire révisé dans les limites prévues à l'article 17 d), sauf cas suivants: </strong><br>
				• Lorsque le bailleur a réalisé, depuis la conclusion du dernier contrat, des travaux d'amélioration portant sur les parties privatives ou communes d'un montant au moins égal à la moitié de la dernière année de loyer, la hausse du loyer annuel ne peut excéder l5% du coût réel des travaux toutes taxes comprises;<br> 
				. Lorsque le dernier loyer appliqué au précédent locataire est manifestement sous-évalué, la hausse du nouveau loyer ne peut excéder la plus élevée des deux limites suivantes<br> 
				l La moitié de la différence entre le montant moyen d'un loyer représentatif des loyers habituellement constatés dans le voisinage pour des logements comparables déterminé selon les modalités prévues à l'article 19 de la loi du 06.07 1989 et le dernier loyer appliqué au précédent locataire;<br>
				2. Une majoration du loyer annuel égale à 15% du coût réel des travaux toutes taxes comprises, dans le cas où le bailleur a réalisé depuis la fin du dernier contrat de location des travaux d'amélioration portant sur les parties privatives ou communes d'un montant au moins égal à la moitié de la dernière année de loyer.<br>
				<strong>Le montant du loyer sera payable au domicile du bailleur ou de la personne qu'il aura mandaté à cet effet.</strong><br> 
				<strong>RÉVISION OU LOYER</strong> art. 17-1-1) de la loi du 06.07.1989: La variation annuelle du loyer ne peut excéder, à la hausse, la variation sur un an de l'indice de référence des loyers publié par l'I.N.S.E.E. dont les éléments de référence sont indiqués en page 5.<br>
				Après sa date de prise d'effet, le bailleur dispose d'un an pour manifester sa volonté d'appliquer la révision du loyer. À défaut le bailleur est réputé avoir renoncé à la révision du loyer pour l'année écoulée : Si le bailleur manifeste sa volonté de réviser le loyer, dans un délai d'un an, cette révision prend effet à compter de sa demande. ");
				$pdf->writeHTMLCell($widthbox, 3, $posX, $posY, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$posY = $pdf->getY()+200;
				
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $tab_top_newpage);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('CONDITIONS GENERALES'), 1, 'C');
				
				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posYL = $pdf->getY();
				$posYR = $pdf->getY();
				$pdf->SetXY($posX, $posYL);
				$pdf->SetFillColor(255, 255, 127);
				
				$text = $outputlangs->transnoentities(" Durée - résiliation - renouvellement");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->convToOutputCharset($text), 1, 'C', 1);
				$posYL = $pdf->getY();
				
				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities(" <strong><U>A/CONTRAT DURÉE MINIMALE DE 3 OU 6 ANS</U></strong><br>
			    DURÉE INITIALE (art 10 et 13 de la loi) Le contrat est conclu pour une durée AU MOINS ÉGALE à 3 ans (bailleur 'personne physique' ou 'société civile familiale') ou à 6 ans (bailleur 'personne morale')<br> 
			    <strong>RÉSILIATION - CONGÉ</strong> (articles 13 et 15 de la loi) : <br>
			    Il pourra être résilié par lettre recommandée avec avis de réception ou par acte d'huissier ou par remise en main propre contre récépissé ou émargement<br> 
			    <U>PAR LE LOCATAIRE</U>, à tout moment, en prévenant le bailleur 3 mois à l'avance, délai ramené à 1 mois en cas de location dans les territoires mentionnés au 1er alinéa du 1 l'article 17, en cas d'obtention d'un premier emploi, de mutation, de perte d'emploi ou de nouvel emploi consécutif â une perte d'emploi, ou en cas de congé émanant d'un locataire qui s'est vu attribuer un logement social (arl. L.35/2 du CCH). ou dont l'état de santé, constaté par un certificat médical, justifie un changement de domicile, ou d'un locataire bénéficiaire du revenu de solidarité active ou de l'allocation adulte handicapé<br> 
			    <U>PAR LE BAILLEUR</U>, en prévenant le locataire 6 mois au moins avant le terme du contrat. Le congé devra être fondé, soit sur sa décision de reprendre ou de vendre le logement, soit sur un motif légitime et sérieux, notamment l'inexécution par le locataire de l'une des obligations principales lui incombant <br>
			    Le congé devra être indiqué le motif allégué et:<br> 
			    en cas de reprise, les nom et adresse du bénéficiaire de la reprise qui ne peut être que l'une des personnes prévues à l'art 15-1 de la loi,<br> 
			    en cas de vente, le prix et les conditions de la vente projetée, ce congé valant offre de vente au profit du locataire. Le congé devra en outre respecter le formalisme de l'article 15-11 de la loi du 06.07.1989<br> <strong>RENOUVELLEMENT</strong> (articles 10, 11, 13 et 17 §c de la loi):<br>
			    1) 6 mois au moins avant le terme du contrat, le bailleur pourra faire une proposiuon de renouvellement par lettre recommandée avec avis de réception ou par acte d'huissier <br>
			    <U>soit à l'effet de proposer un nouveau contrat d'une durée réduite (au moins égale à un an)</U> pour raisons professionnelles ou familiales justifiées (bailleur 'personne physique' ou 'société civile familiale') :<br> 
			    <U>soit à l'effet de réévaluer le loyer</U> pour le cas où ce dernier serait manifestement sous-évalué, le contrat étant renouvelé pour une durée AU MOINS ÉGALE à 3 ans (bailleur 'personne physique' ou 'société civile familiale') ou à 6 ans (bailleur 'personne morale') dans ce cas, le bailleur pourra proposer au locataire un nouveau loyer fixé par référence aux loyers habituellement constatés dans le voisinage pour des logements comparables, dans les conditions fixées à l'article 19 de la loi <br>
			    2) À défaut de congé motivé donné dans les conditions de forme et de délai prévues ci-avant, le contrat parvenu à son terme sera renouvelé pour une durée AU MOINS ÉGALE à 3 ans (bailleur 'personne physique' ou 'société civile familiale') ou à 6 ans (bailleur 'personne morale')<br>  ");
				$pdf->writeHTMLCell($widthbox/2 -2, 3, $posX, $posYL, $outputlangs->convToOutputCharset($text), 1, 0, 0, true, 'J');
				
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities(" <strong>TACITE RECONDUCTION </strong>(articles 10 et 13 de la loi) :<br> À défaut de renouvellement ou de congé motivé donné dans les conditions de forme et de délai prévues ci-avant, le contrat parvenu à son terme sera reconduit tacitement aux CONDITIONS ANTÉRIEURES, pour une durée ÉGALE à 3 ans (bailleur 'personne physique' ou à 6 ans (bailleur 'personne morale')<br>
				<strong><U>B/CONTRAT D'UNE DURÉE INFÉRIEURE À 3 ANS</U></strong><br> 
				POUR RAISONS PROFESSIONNELLES OU FAMILIALES JUSTIFIÉES (art 11 et 13 de la loi):<br> 
				<strong>DURÉE INITIALE</strong> : Les parties peuvent conclure un contrat d'une durée inférieure à 3 ans, mais d'au moins une année, quand un événement précis justifie que le bailleur 'personne physique' ou 'société civile familiale' ait à reprendre le local pour des raisons professionnelles ou familiales mentionnées au contrat<br> 
				<strong>CONGÉ</strong>. Le congé devra être notifié par lettre recommandée avec avis de réception ou par acte d'huissier ou par remise en main propre contre récépissé ou émargement<br> 
				<U>PAR LE LOCATAIRE</U>, à tout moment, en prévenant le bailleur 3 mois à l'avance, délai ramené à 1 mois en cas de location dans les territoires mentionnés au 1er alinéa du 1 de l'article 17, en cas d'obtention d'un premier emploi, de mutation, de perte d'emploi ou de nouvel emploi consécutif à une perte d'emploi, ou en cas de congé émanant d'un locataire qui s'est vu attribuer un logement social (art. L.35/2 du CCH), ou dont l'état de santé, constaté par un certificat médical, justifie un changement de domicile, ou d'un locataire bénéficiaire du revenu de solidarité active ou de l'allocation adulte handicapé <br>
				<U>PAR LE BAILLEUR</U>, en confirmant la réalisation de l'événement familial ou professionnel au moins 2 mois avant le terme du contrat. Si la réalisation de l'événement est différée, le bailleur pourra, dans le même délai, proposer le report du terme du contrat, ce report n'étant possible qu'une seule fois. Lorsque l'événement s'est produit et est confirmé, le locataire est déchu de plein droit de tout titre d'occupation au terme prévu dans le contrat. <br>
				<strong>TRANSFORMATION EN CONTRAT DE 3 ANS</strong> : Lorsque l'événement ne s'est pas produit ou n'est pas confirmé, le contrat de location est réputé être de 3 ans<br>
				<strong><U>C/RÉSILIATION SUITE À L'ABANDON DU LOGEMENT PAR LE LOCATAIRE</strong></U> <br>
				Lorsque des éléments laissent supposer que le logement est abandonné par ses occupants, le bailleur peut mettre en demeure le locataire par acte d'huissier de Justifier qu'il occupe le logement. S'il n'a pas été déféré à cette mise en demeure un mois après signification, l'huissier peut constater l'abandon du logement dans un procès-verbal des opérations.<br>
				La résiliation du bail est constatée par le juge dans les conditions prévues par voie réglementaire	");
				
				$pdf->writeHTMLCell($widthbox/2 -2, 3, $posX+$widthbox/2, $posYR, $outputlangs->convToOutputCharset($text), 1, 0, 0, true, 'J');
				
				$posY = $pdf->getY()+210;
				
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities(" 1. Montant total annuel récupérable au titre de l'assurance pour compte des colocataires : [...] (13).
2. Montant récupérable par douzième : [...].
E. Modalités de paiement
- périodicité du paiement : [... (14)] ;
- paiement [à échoir/ à terme échu] ;
- date ou période de paiement : [...] ;
- le cas échéant, Lieu de paiement : [...] ;
- le cas échéant, Montant total dû à la première échéance de paiement pour une période complète de location :
[détailler la somme des montants relatifs au loyer, aux charges récupérable, à la contribution pour le partage des économies de charges et, en cas de colocation, à l'assurance récupérable pou le compte des colocataires].
F. Le cas échéant, exclusivement lors d'un renouvellement de contrat, modalités de réévaluation d'un loyer manifestement sous-évalué
1. Montant de la hausse ou de la baisse de loyer mensuelle : [...].
2. Modalité d'application annuelle de la hausse : [par tiers ou par sixième selon la durée du contrat et le montant de la hausse de loyer].
");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');				
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				$posY = $pdf->getY();
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('V. TRAVAUX'), 1, 'C');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities(" A. Le cas échéant, Montant et nature des travaux d'amélioration ou de mise en conformité avec les caractéristiques de
décence effectués depuis la fin du dernier contrat de location ou depuis le dernier renouvellement : [...] (15)
B. Le cas échéant, Majoration du loyer en cours de bail consécutive à des travaux d'amélioration entrepris par le
bailleur : [nature des travaux, modalités d'exécution, délai de réalisation ainsi que montant de la majoration du loyer]
(16)
C. Le cas échéant, Diminution de loyer en cours de bail consécutive à des travaux entrepris par le locataire : [durée de cette diminution et, en cas de départ anticipé du locataire, modalités de son dédommagement sur justification des dépenses effectuées].");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $tab_top_newpage);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('VI. GARANTIES'), 1, 'C');

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("Le cas échéant, Montant du dépôt de garantie de l'exécution des obligations du locataire/ Garantie autonome :
[inférieur ou égal à un mois de loyers hors charges].");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				$posY = $pdf->getY();
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('VII. LE CAS ECHEANT, CLAUSE DE SOLIDARITE'), 1, 'C');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("Modalités particulières des obligations en cas de pluralité de locataires : [clause prévoyant la solidarité des locataires et l'indivisibilité de leurs obligations en cas de pluralité de locataires].");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				$posY = $pdf->getY();
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('VIII. LE CAS ECHEANT, CLAUSE RESOLUTOIRE'), 1, 'C');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("Modalités de résiliation de plein droit du contrat : [clause prévoyant la résiliation de plein droit du contrat de location pour un défaut de paiement du loyer ou des charges aux termes convenus, le non versement du dépôt de garantie, la non-souscription d'une assurance des risques locatifs ou le non-respect de l'obligation d'user paisiblement des locaux loués, résultant de troubles de voisinage constatés par une décision de justice passée en force de chose jugée].");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $tab_top_newpage);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('IX. LE CAS ECHEANT, HONORAIRES DE LOCATION'), 1, 'C');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities(" A. Dispositions applicables
Il est rappelé les dispositions du I de l'article 5 (I) de la loi du 6 juillet 1989, alinéas 1 à 3 : La rémunération des personnes mandatées pour se livrer ou prêter leur concours à l'entremise ou à la négociation d'une mise en location d'un logement, tel que défini aux articles 2 et 25-3, est à la charge exclusive du bailleur, à l'exception des honoraires liés aux prestations mentionnées aux deuxième et troisième alinéas du présent I.
Les honoraires des personnes mandatées pour effectuer la visite du preneur, constituer son dossier et rédiger un bail sont partagés entre le bailleur et le preneur. Le montant toutes taxes comprises imputé au preneur pour ces prestations ne peut excéder celui imputé au bailleur et demeure inférieur ou égal à un plafond par mètre carré de surface habitable
de la chose louée fixé par voie réglementaire et révisable chaque année, dans des conditions définies par décret. Ces honoraires sont dus à la signature du bail.
Les honoraires des personnes mandatées pour réaliser un état des lieux sont partagés entre le bailleur et le preneur.
Le montant toutes taxes comprises imputé au locataire pour cette prestation ne peut excéder celui imputé au bailleur et demeure inférieur ou égal à un plafond par mètre carré de surface habitable de la chose louée fixé par voie réglementaire et révisable chaque année, dans des conditions définies par décret. Ces honoraires sont dus à compter de la réalisation de la prestation.
Plafonds applicables :
- montant du plafond des honoraires imputables aux locataires en matière de prestation de visite du preneur, de constitution de son dossier et de rédaction de bail : [...] €/ m2 de surface habitable ;
- montant du plafond des honoraires imputables aux locataires en matière d'établissement de l'état des lieux d'entrée :
[...] €/ m2 de surface habitable.");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("B. Détail et répartition des honoraires
1. Honoraires à la charge du bailleur :
- prestations de visite du preneur, de constitution de son dossier et de rédaction de bail : [détail des prestations effectivement réalisées et montant des honoraires toutes taxes comprises dus à la signature du bail] ;
- le cas échéant, Prestation de réalisation de l'état des lieux d'entrée : [montant des honoraires toutes taxes comprises dus à compter de la réalisation de la prestation] ;
- autres prestations : [détail des prestations et conditions de rémunération].
2. Honoraires à la charge du locataire :
- prestations de visite du preneur, de constitution de son dossier et de rédaction de bail : [détail des prestations effectivement réalisées et montant des honoraires toutes taxes comprises dus à la signature du bail] ;
- le cas échéant, Prestation de réalisation de l'état des lieux d'entrée : [montant des honoraires toutes taxes comprises dus à compter de la réalisation de la prestation].");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$posY = $pdf->getY();
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$posY = $pdf->getY();
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('X. AUTRES CONDITIONS PARTICULIERES'), 1, 'C');

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("[A définir par les parties]");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posX, $tab_top_newpage);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('XI. ANNEXES'), 1, 'C');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("Sont annexées et jointes au contrat de location les pièces suivantes :
A. Le cas échéant, un extrait du règlement concernant la destination de l'immeuble, la jouissance et l'usage des parties privatives et communes, et précisant la quote-part afférente au lot loué dans chacune des catégories de charges
B. Un dossier de diagnostic technique comprenant
- un diagnostic de performance énergétique ;
- un constat de risque d'exposition au plomb pour les immeubles construits avant le 1er janvier 1949 ;
- une copie d'un état mentionnant l'absence ou la présence de matériaux ou de produits de la construction contenant de l'amiante (18) ;
- un état de l'installation intérieure d'électricité et de gaz, dont l'objet est d'évaluer les risques pouvant porter atteinte à la sécurité des personnes (19) ;
- le cas échéant, un état des risques naturels et technologiques pour le zones couvertes par un plan de prévention des risques technologiques ou par un plan de prévention des risques naturels prévisibles, prescrit ou approuvé, ou dans des zones de sismicité (20).
C. Une notice d'information relative aux droits et obligations des locataires et des bailleurs
D. Un état des lieux (21)
E. Le cas échéant, Une autorisation préalable de mise en location (22)
F. Le cas échéant, Les références aux loyers habituellement constatés dans le voisinage pour des logements comparables (23)");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("Le [date], à [lieu],");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("Signature du bailleur [ou de son mandataire, le cas échéant]");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities("Signature du locataire");
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($text), 1, 'L');
			}
			$this->db->free($resql);

			$pdf->Close();

			$pdf->Output($file, 'F');
			if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

			return 1; // Pas d'erreur
		}
		else
		{
			$this->error=$outputlangs->transnoentities("ErrorCanNotCreateDir",$dir);
			return 0;
		}
	}

	
	/**
	 *   Show table for lines
	 *
	 *   @param		PDF			$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		Hide top bar of array
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @return	void
	 */
	private function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0)
	{
		global $conf;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		//title line
		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, $roundradius, $round_corner = '0110', 'S', $this->border_style, array());

		foreach ($this->cols as $colKey => $colDef)
		{
		    if(!$this->getColumnStatus($colKey)) continue;

		    // get title label
		    $colDef['title']['label'] = !empty($colDef['title']['label'])?$colDef['title']['label']:$outputlangs->transnoentities($colDef['title']['textkey']);

		    // Add column separator
		    if(!empty($colDef['border-left'])){
		        $pdf->line($colDef['xStartPos'], $tab_top, $colDef['xStartPos'], $tab_top + $tab_height);
		    }

		    if (empty($hidetop))
		    {
		      $pdf->SetXY($colDef['xStartPos'] + $colDef['title']['padding'][3], $tab_top-8 + $colDef['title']['padding'][0] );

		      $textWidth = $colDef['width'] - $colDef['title']['padding'][3] -$colDef['title']['padding'][1];
		      $pdf->MultiCell($textWidth,$tab_height+8,$colDef['title']['label'],'',$colDef['title']['align']);
		    }
		}
		// Output Rect
		$this->printRect($pdf, $this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height+1, 0, 0);	// Rect prend une longueur en 3eme param et 4eme param
	}

	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	private function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $conf, $langs;

		// Translations
		$outputlangs->loadLangs(array("main", "bills", "propal", "companies", "dict"));

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);

		// Show Draft Watermark
		if($object->statut==ImmoReceipt::STATUS_DRAFT && (! empty($conf->global->FACTURE_DRAFT_WATERMARK)) )
        {
		      pdf_watermark($pdf, $outputlangs, $this->page_hauteur, $this->page_largeur, 'mm', $conf->global->FACTURE_DRAFT_WATERMARK);
        }

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$w = 110;

		$posy=$this->marge_haute;
        $posx=$this->page_largeur-$this->marge_droite-$w;

		$pdf->SetXY($this->marge_gauche, $posy);

		// Logo
		if (empty($conf->global->PDF_DISABLE_MYCOMPANY_LOGO))
		{
			$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
			if ($this->emetteur->logo)
			{
				if (is_readable($logo))
				{
				    $height=pdf_getHeightForLogo($logo);
					$pdf->Image($logo, $this->marge_gauche, $posy, 0, $height);	// width=0 (auto)
				}
				else
				{
					$pdf->SetTextColor(200, 0, 0);
					$pdf->SetFont('', 'B', $default_font_size - 2);
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
				}
			}
			else
			{
				$text=$this->emetteur->name;
				$pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
			}
		}

		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$title=$outputlangs->transnoentities("CONTRAT DE LOCATION");
		$pdf->MultiCell($w, 3, $title, '', 'R');
		$posy = $pdf->getY();
		$pdf->SetFont('', '', $default_font_size + 1);
		$pdf->SetXY($posx, $posy);
		$title=$outputlangs->transnoentities("loi n° 89-462 du 6 juillet 1989");
		$pdf->MultiCell($w, 3, $title, '', 'R');
		$posy = $pdf->getY();
		$pdf->SetXY($posx, $posy);
		$title=$outputlangs->transnoentities("LOCAUX VACANTS NON MEUBLÉS");
		$pdf->MultiCell($w, 3, $title, '', 'R');

		$pdf->SetFont('', 'B', $default_font_size);

		$posy+=5;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$textref=$outputlangs->transnoentities("Ref")." : " . $outputlangs->convToOutputCharset($object->ref);

		$pdf->MultiCell($w, 4, $textref, '', 'R');

		$posy+=1;
		$pdf->SetFont('', '', $default_font_size - 2);

		if ($object->ref_client)
		{
			$posy+=4;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("RefCustomer")." : " . $outputlangs->convToOutputCharset($object->ref_client), '', 'R');
		}
		$posy=$pdf->getY()+2;

		if ($object->thirdparty->code_client)
		{
			$posy+=3;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("CustomerCode")." : " . $outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		$posy=$pdf->getY()+2;

		$top_shift = 0;
		// Show list of linked objects
		$current_y = $pdf->getY();
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, $w, 3, 'R', $default_font_size);
		if ($current_y < $pdf->getY())
		{
			$top_shift = $pdf->getY() - $current_y;
		}

		if ($showaddress)
		{
			// Sender properties
			$owner = new ImmoOwner($this->db);
			$result = $owner->fetch($object->fk_owner);
			/*if ($owner->country_id)
			{
				$tmparray=$owner->getCountry($owner->country_id,'all');
				$owner->country_code=$tmparray['code'];
				$owner->country=$tmparray['label'];
			}*/
			$carac_emetteur = pdf_build_address($outputlangs, $owner, $object->thirdparty, '', 0, 'source', $object);
			
			// HABITATION PRINCIPALE
			$pdf->rect($this->marge_gauche, $posy, 4, 4);
			$pdf->SetXY ($this->marge_gauche, $posy);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 3);
			$pdf->SetXY($this->marge_gauche+6, $posy);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('HABITATION PRINCIPALE'), 0, 'L');
			
			// PROFESSIONNEL ET HABITATION PRINCIPALE
			$pdf->rect($this->marge_gauche+88, $posy, 4, 4);
			$pdf->SetXY ($this->marge_gauche+88, $posy);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 3);
			$pdf->SetXY($this->marge_gauche, $posy);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('PROFESSIONNEL ET HABITATION PRINCIPAL'), 0, 'R');
			
			$posy=$pdf->getY()+2;

			// Show sender
			$posy=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 46;
			$posy+=$top_shift;
			$posx=$this->marge_gauche;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->page_largeur-$this->marge_droite-80;

			$hautcadre=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 38 : 40;
			$widthrecbox=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 92 : 82;

			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size);
			$pdf->SetXY($posx, $posy-10);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Entre les soussignés  '), 0, 'L');
			
			// Show sender frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 4);
			$pdf->SetXY($posx, $posy-5);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('BAILLEUR '), 1, 'C');
			$posy=$pdf->getY();
			$pdf->SetXY($posx, $posy);
			$pdf->SetFillColor(230, 230, 230);
			$pdf->MultiCell($widthrecbox, $hautcadre, "", 1, 'R', 1);
			$pdf->SetTextColor(0, 0, 60);

			// Show sender name
			$pdf->SetXY($posx+2, $posy+3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell($widthrecbox-2, 4, $outputlangs->convToOutputCharset($owner->getFullName($outputlangs)), 0, 'L');
			$posy=$pdf->getY();

			// Show sender information
			$pdf->SetXY($posx+2, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox-2, 4, $carac_emetteur, 0, 'L');
			$posy=$pdf->getY();
			
			$pdf->SetXY($posx, $posy+4);
			$pdf->SetFont('', 'I', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('dénommé "LE BAILLEUR" '), 0, 'R');
			$posy=$pdf->getY();

			//Recipient name
			$renter = new ImmoRenter($this->db);
			$result = $renter->fetch($object->fk_renter);
			$carac_client_name= $outputlangs->convToOutputCharset($renter->getFullName($outputlangs));
			
			$property = new ImmoProperty($this->db);
			$result = $property->fetch($object->fk_property);
			$carac_client .= $property->label . "\n";
			$carac_client .= $property->address . "\n";
			$carac_client .= $property->zip . ' ' . $property->town;

			// Show recipient
			$widthrecbox=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 92 : 100;
			if ($this->page_largeur < 210) $widthrecbox=84;	// To work with US executive format
			$posy=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 46;
			$posy+=$top_shift;
			$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->marge_gauche;

			// Bloc Locataire
			$pdf->SetTextColor(0, 0, 0);
			$texte = $outputlangs->convToOutputCharset('(le cas échéant)');
			$pdf->SetFont('', '', $default_font_size + 4);
			$pdf->SetXY($posx, $posy-5);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('MANDATAIRE').' '.$texte, 1, 'C');
			
			$posy=$pdf->getY();
			$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$pdf->SetXY($posx+2, $posy+3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell($widthrecbox, 2, '', 0, 'L');

			$posy = $pdf->getY();

			// Show recipient information
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->SetXY($posx+2, $posy);
			$pdf->MultiCell($widthrecbox, 4, '', 0, 'L');
			
			$pdf->SetFont('', '', $default_font_size-3);
			$pdf->SetXY($posx, $posy+28);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('le cas échéant. avec le concours de (préciser négociateur ou agent commercial)'), 0, 'C');
			
			$posy = $pdf->getY()+12;
			
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size);
			$pdf->SetXY($this->marge_gauche, $posy-10);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Et'), 0, 'L');
			
			// Bloc Locataire
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 4);
			$posx=$this->marge_gauche;
			$widthrecbox = $this->page_largeur-$this->marge_droite-$this->marge_gauche;
			$pdf->SetXY($posx, $posy-5);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('LOCATAIRE(S)'), 1, 'C');
			$posy=$pdf->getY();
			$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$pdf->SetXY($posx+2, $posy+3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell($widthrecbox, 2, $carac_client_name, 0, 'L');

			$posy = $pdf->getY();

			// Show recipient information
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->SetXY($posx+2, $posy);
			$pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');
			
			$posy = $pdf->getY();
			
			$pdf->SetXY($posx, $posy+16);
			$pdf->SetFont('', 'I', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('dénommé(s) "LE LOCATAIRE" (au singulier) '), 0, 'R');
			
			$posy=$pdf->getY();
			
			$pdf->SetXY($posx, $posy+2);
			$pdf->SetFont('', 'I', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Il a été convenu et arrêté ce qui suit: le bailleur loue les locaux et équipement ci-après désignés au locataire qui les accepte aux conditions suivantes '), 0, 'L');
						
		}

		$pdf->SetTextColor(0, 0, 0);
		return $top_shift;
	}

	/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *   	@param	PDF			$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	private function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_ultimate_pagefoot($pdf, $outputlangs, 'INVOICE_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext);
	}

	/**
	 *  Define Array Column Field
	 *
	 *  @param	object			$object    		common object
	 *  @param	outputlangs		$outputlangs    langs
     *  @param	int			   $hidedetails		Do not show line details
     *  @param	int			   $hidedesc		Do not show desc
     *  @param	int			   $hideref			Do not show ref
     *  @return	null
     */
    public function defineColumnField($object, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0)
    {
	    global $conf, $hookmanager;

	    // Default field style for content
	    $this->defaultContentsFieldsStyle = array(
	        'align' => 'R', // R,C,L
	        'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	    );

	    // Default field style for content
	    $this->defaultTitlesFieldsStyle = array(
	        'align' => 'C', // R,C,L
	        'padding' => array(0.5,0,0.5,0), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	    );

	    /*
	     * For exemple
	    $this->cols['theColKey'] = array(
	        'rank' => $rank, // int : use for ordering columns
	        'width' => 20, // the column width in mm
	        'title' => array(
	            'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
	            'label' => ' ', // the final label : used fore final generated text
	            'align' => 'L', // text alignement :  R,C,L
	            'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	        ),
	        'content' => array(
	            'align' => 'L', // text alignement :  R,C,L
	            'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	        ),
	    );
	    */

	    $rank=0; // do not use negative rank
	    $this->cols['desc'] = array(
	        'rank' => $rank,
	        'width' => false, // only for desc
	        'status' => true,
	        'title' => array(
	            'textkey' => 'Designation', // use lang key is usefull in somme case with module
	            'align' => 'L',
	            // 'textkey' => 'yourLangKey', // if there is no label, yourLangKey will be translated to replace label
	            // 'label' => ' ', // the final label
	            'padding' => array(0.5,0.5,0.5,0.5), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
	        ),
	        'content' => array(
	            'align' => 'L',
	        ),
	    );

	    $reshook=$hookmanager->executeHooks('defineColumnField', $parameters, $this);    // Note that $object may have been modified by hook
	    if ($reshook < 0)
	    {
	        setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	    }
	    elseif (empty($reshook))
	    {
	        $this->cols = array_replace($this->cols, $hookmanager->resArray); // array_replace is used to preserve keys
	    }
	    else
	    {
	        $this->cols = $hookmanager->resArray;
	    }
	}
}
