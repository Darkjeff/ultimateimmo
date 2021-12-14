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

class pdf_etatpaiement extends ModelePDFUltimateimmo
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
		$this->name = 'etatpaiement';
		$this->description = $langs->trans('etatpaiement');
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

		$this->widthrecbox = 39;
		$this->widthrecboxamount = 22;

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
	function write_file($object, $outputlangs, $file='')
	{
		global $user, $langs, $conf, $mysoc, $hookmanager;

		if (! is_object($outputlangs))
			$outputlangs = $langs;


		// Translations
		$outputlangs->loadLangs(array("main", "ultimateimmo@ultimateimmo", "companies"));


		// Definition of $dir and $file
		if ($object->specimen)
		{
			$dir = $conf->ultimateimmo->dir_output."/";
			$file = $dir . "/SPECIMEN.pdf";
		}
		else
		{
			$dir = $conf->ultimateimmo->dir_output . "/rentmassgen";
			$file = $dir . "/" . $file . ".pdf";
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
			/*$hookmanager->initHooks(array('pdfgeneration'));
			$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
			global $action;
			$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
*/
			// Set nblignes with the new facture lines content after hook
			//$nblignes = count($object->lines);
			$nblignes=0;
			//$nbpayments = count($object->getListOfPayments()); TODO : add method

			// Create pdf instance
			$pdf=pdf_getInstance($this->format);
			$default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
			$pdf->SetAutoPageBreak(1, 0);

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
			$pdf->SetSubject($outputlangs->transnoentities("etatpaiement"));
			$pdf->SetCreator("Dolibarr " . DOL_VERSION . ' (ultimateimmo module)');
			$pdf->SetAuthor($outputlangs->convToOutputCharset($user->firstname)." ".$outputlangs->convToOutputCharset($user->lastname));
			$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->label) . " " . $outputlangs->transnoentities("Document"));
			if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

			$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right

			if (! empty($object->sqlquerymassgen))
			{
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);

				$pagenb++;
				$pdf->SetFillColor(224, 224, 224);

				$curY = $this->_pagehead($pdf, $object, 1, $outputlangs);
				$posX = $this->marge_gauche;

				dol_syslog(get_class($this), LOG_DEBUG);
				$resql = $this->db->query($object->sqlquerymassgen);
				$posY = $curY;
				$h=10;

				$rentpermage = 25;

				if ($resql)
				{
					$num = $this->db->num_rows($resql);
					if ($num>0) {

						$i= 0;

						while ($obj = $this->db->fetch_object($resql))	{
							$i++;
							if ($i>$rentpermage) {
								$pdf->AddPage('', '', true);
								$posY = $this->_pagehead($pdf, $object, 1, $outputlangs);
								$i=0;
							} else {
								$posY = $pdf->getY();
							}

							$pdf->SetXY($posX, $posY);
							$pdf->MultiCell($this->widthrecbox, $h, $obj->receiptname, 1, 'L',0);

							$pdf->SetXY($posX+$this->widthrecbox, $posY);
							$pdf->MultiCell($this->widthrecbox, $h, $obj->local, 1, 'L',0);

							$pdf->SetXY($posX+($this->widthrecbox*2), $posY);
							$pdf->MultiCell($this->widthrecbox, $h, $obj->nom, 1, 'L',0);

							$pdf->SetXY($posX+($this->widthrecbox*3), $posY);
							$pdf->MultiCell($this->widthrecboxamount, $h, price($obj->total), 1, 'L',0);

							$pdf->SetXY($posX+($this->widthrecbox*3)+($this->widthrecboxamount), $posY);
							$pdf->MultiCell($this->widthrecboxamount, $h, price($obj->partial_payment), 1, 'L',0);

							$pdf->SetXY($posX+($this->widthrecbox*3)+($this->widthrecboxamount*2), $posY);
							$pdf->MultiCell($this->widthrecboxamount, $h, price($obj->balance), 1, 'L',0);

						}
					}
				}
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
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

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$w = 110;

		$posy=$this->marge_haute;
		$posX=$this->page_largeur-$this->marge_droite-$w;

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
		$pdf->SetXY($posX, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$title=$outputlangs->transnoentities("Etat de paiement");
		$pdf->MultiCell($w, 3, $title, '', 'R');


		$pdf->SetTextColor(0, 0, 0);

		$pdf->SetFont('', 'B', $default_font_size);

		$pdf->SetXY($this->marge_gauche, $pdf->getY());
		$curY =  $pdf->getY();

		$posX =$this->marge_droite;

		$posY = $curY;
		$pdf->SetXY($posX, $curY);
		$pdf->MultiCell($this->widthrecbox, 3, $outputlangs->convToOutputCharset('Loyer'), 1, 'L',1);

		$pdf->SetXY($posX+$this->widthrecbox, $posY);
		$pdf->MultiCell($this->widthrecbox, 3, $outputlangs->convToOutputCharset('Local'), 1, 'L',1);

		$pdf->SetXY($posX+($this->widthrecbox*2), $posY);
		$pdf->MultiCell($this->widthrecbox, 3, $outputlangs->convToOutputCharset('Locataire'), 1, 'L',1);

		$pdf->SetXY($posX+($this->widthrecbox*3), $posY);
		$pdf->MultiCell($this->widthrecboxamount, 3, $outputlangs->convToOutputCharset('Motant'), 1, 'L',1);

		$pdf->SetXY($posX+($this->widthrecbox*3)+($this->widthrecboxamount), $posY);
		$pdf->MultiCell($this->widthrecboxamount, 3, $outputlangs->convToOutputCharset('Perçus'), 1, 'L',1);

		$pdf->SetXY($posX+($this->widthrecbox*3)+($this->widthrecboxamount*2), $posY);
		$pdf->MultiCell($this->widthrecboxamount, 3, $outputlangs->convToOutputCharset('Reste'), 1, 'L',1);

		$pdf->SetFont('', '', $default_font_size - 1);
		$pdf->SetTextColor(0, 0, 0);

		return $pdf->getY();
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
