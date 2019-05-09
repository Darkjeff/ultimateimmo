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

dol_include_once('/ultimateimmo/core/modules/immorent/modules_immorent.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immopayment.class.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php');
require_once (DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');

class pdf_bail_vide extends ModelePDFImmorent
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
		
		if (! is_object($outputlangs)) $outputlangs=$langs;

		// Translations
		$outputlangs->loadLangs(array("main", "ultimateimmo@ultimateimmo", "companies" , "errors"));

		if (! is_object($outputlangs))
			$outputlangs = $langs;

		if ($conf->ultimateimmo->dir_output)
		{
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

				$heightforinfotot = 20;	// Height reserved to output the info and total part
				$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
				$heightforfooter = $this->marge_basse + (empty($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS)?12:22);	// Height reserved to output the footer (value include bottom margin)
				$pdf->SetAutoPageBreak(1, 0);
				
				if (class_exists('TCPDF'))
				{
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				
				// Set path to the background PDF File
				if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
				{
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);
				
				//Generation de l entete du fichier
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

				$owner = new ImmoOwner($this->db);
				$result = $owner->fetch($object->fk_owner);

				$property = new ImmoProperty($this->db);
				$result = $property->fetch($object->fk_property);

				$paiement = new Immopayment($this->db);
				$result = $paiement->fetch_by_loyer($object->id);
				
				$tab_height = 130;
				$tab_height_newpage = 150;
				$tab_width = $this->page_largeur-$this->marge_gauche-$this->marge_droite;
				
				// Affiche notes
				$notetoshow=empty($object->note_public)?'':$object->note_public;
				if (! empty($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_ORDER_NOTE))
				{
					// Get first sale rep
					if (is_object($object->thirdparty))
					{
						$salereparray=$object->thirdparty->getSalesRepresentatives($user);
						$salerepobj=new User($this->db);
						$salerepobj->fetch($salereparray[0]['id']);
						if (! empty($salerepobj->signature)) $notetoshow=dol_concatdesc($notetoshow, $salerepobj->signature);
					}
				}
				
				$pagenb = $pdf->getPage();
				if ($notetoshow && empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS))
				{
					$pageposbeforenote = $pagenb;
					if($desc_incoterms)
					{
						$tab_top_note +=4;
					}

					$substitutionarray=pdf_getSubstitutionArray($outputlangs, null, $object);
					complete_substitutions_array($substitutionarray, $outputlangs, $object);
					$notetoshow = make_substitutions($notetoshow, $substitutionarray, $outputlangs);

					$pdf->startTransaction();

					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
					$pdf->writeHTMLCell($tab_width, 3, $this->marge_gauche+1, $tab_top_note, dol_htmlentitiesbr($notetoshow), 0, 1);
					// Description
					$pageposafternote=$pdf->getPage();
					$posyafter = $pdf->GetY();
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top_note;

					// Rect prend une longueur en 3eme et 4eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->RoundedRect($this->marge_gauche, $tab_top_note-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', $this->border_style, $bgcolor);

					if ($pageposafternote > $pageposbeforenote)
					{
						$pdf->rollbackTransaction(true);

						// prepair pages to receive notes
						while ($pagenb < $pageposafternote)
						{
							$pdf->AddPage();
							$pagenb++;
							if (! empty($tplidx)) $pdf->useTemplate($tplidx);
							if (empty($conf->global->ULTIMATE_FICHINTER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
							// $this->_pagefoot($pdf,$object,$outputlangs,1);
							$pdf->setTopMargin($tab_top_newpage);
							// The only function to edit the bottom margin of current page to set it.
							$pdf->setPageOrientation('', 1, $heightforfooter + $heightforfreetext);
						}

						// back to start
						$pdf->setPage($pageposbeforenote);
						$pdf->setPageOrientation('', 1, $heightforfooter + $heightforfreetext);
						$pdf->SetFont('','', $default_font_size - 1);
						$pdf->writeHTMLCell($tab_width, 3, $this->marge_gauche+1, $tab_top_note, dol_htmlentitiesbr($notetoshow), 0, 1);
						$pageposafternote=$pdf->getPage();

						$posyafter = $pdf->GetY();
						$nexY = $pdf->GetY();

						if ($posyafter > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+20)))	// There is no space left for total+free text
						{
							$pdf->AddPage('','',true);
							$pagenb++;
							$pageposafternote++;
							$pdf->setPage($pageposafternote);
							$pdf->setTopMargin($tab_top_newpage);
							// The only function to edit the bottom margin of current page to set it.
							$pdf->setPageOrientation('', 1, $heightforfooter + $heightforfreetext);
							//$posyafter = $tab_top_newpage;
						}


						// apply note frame to previus pages
						$i = $pageposbeforenote;
						while ($i < $pageposafternote)
						{
							$pdf->setPage($i);

							$pdf->SetDrawColor(128,128,128);
							// Draw note frame
							if ($i > $pageposbeforenote)
							{
								$height_note = $this->page_hauteur - ($tab_top_newpage + $heightforfooter);
								$pdf->RoundedRect($this->marge_gauche, $tab_top_newpage-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', array());
							}
							else
							{
								$height_note = $this->page_hauteur - ($tab_top_note + $heightforfooter);
								$pdf->RoundedRect($this->marge_gauche, $tab_top_note-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', array());
							}

							// Add footer
							$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
							$this->_pagefoot($pdf,$object,$outputlangs,1);

							$i++;
						}

						// apply note frame to last page
						$pdf->setPage($pageposafternote);
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						if (empty($conf->global->ULTIMATE_FICHINTER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						$height_note=$posyafter-$tab_top_newpage;
						$pdf->RoundedRect($this->marge_gauche, $tab_top_newpage-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', array());
					}
					else // No pagebreak
					{
						$pdf->commitTransaction();
						$posyafter = $pdf->GetY();
						$height_note=$posyafter-$tab_top_note;
						$pdf->RoundedRect($this->marge_gauche, $tab_top_note-1, $tab_width, $height_note+1, $roundradius, $round_corner = '1111', 'S', array());

						if($posyafter > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+20)) )
						{
							// not enough space, need to add page
							$pdf->AddPage('','',true);
							$pagenb++;
							$pageposafternote++;
							$pdf->setPage($pageposafternote);
							if (! empty($tplidx)) $pdf->useTemplate($tplidx);
							if (empty($conf->global->ULTIMATE_ORDERS_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

							$posyafter = $tab_top_newpage;
						}
					}

					$tab_height = $tab_height - $height_note;
					$tab_top = $posyafter + 10;

					}
					else
					{
						$height_note=0;
					}

					$iniY = $tab_top + 7;
					$curY = $tab_top + 8;
					$nexY = $tab_top + 8;

					$nblines = count($object->lines);

					// Use new auto column system
					$this->prepareArrayColumnField($object,$outputlangs,$hidedetails,$hidedesc,$hideref);

					// Loop on each lines
					$pageposbeforeprintlines=$pdf->getPage();
					$pagenb = $pageposbeforeprintlines;
					$line_number=1;
					for ($i = 0; $i < $nblines; $i++)
					{
						$objectligne = $object->lines[$i];

						$valide = $objectligne->id ? $objectligne->fetch($objectligne->id) : 0;

						if ($valide > 0 || $object->specimen)
						{
							// Description of intervention line
							$curX = $this->getColumnContentXStart('desc');
							$text_length = $tab_width;
							$curY = $nexY;
							$pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
							$pdf->SetTextColorArray($textcolor);

							$pdf->setTopMargin($tab_top_newpage);
							//If we aren't on last lines footer space needed is on $heightforfooter
							if ($i != $nblines-1)
							{
								$bMargin=$heightforfooter;
							}
							else
							{
								//We are on last item, need to check all footer (freetext, ...)
								$bMargin=$heightforfooter+$heightforfreetext+$heightforinfotot;
							}
							$pdf->setPageOrientation('', 1,  $bMargin);	// The only function to edit the bottom margin of current page to set it.
							$pageposbefore=$pdf->getPage();

							$showpricebeforepagebreak=1;
							$posYStartDescription=0;
							$posYAfterDescription=0;

							$pdf->startTransaction();

							// Description of product line
							$desc=dol_htmlentitiesbr($objectligne->desc,1);
							$posYStartDescription = $curY;
							$pdf->writeHTMLCell($text_length, 0, $curX, $curY, $desc, 0, 1, 0);
							$posYAfterDescription=$pdf->GetY();

							$pageposafter=$pdf->getPage();

							if ($pageposafter > $pageposbefore)	// There is a pagebreak
							{
								$pdf->rollbackTransaction(true);
								$pageposafter=$pageposbefore;

								$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.
								$desc=dol_htmlentitiesbr($objectligne->desc,1);
								$posYStartDescription = $curY;
								$pdf->writeHTMLCell($text_length, 0, $curX, $curY, $desc, 0, 1, 0);
								$posYAfterDescription=$pdf->GetY();
								$pageposafter=$pdf->getPage();

								if ($posYAfterDescription > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
								{
									if ($i == ($nblines-1))	// No more lines, and no space left to show total, so we create a new page
									{
										$pdf->AddPage('','',true);
										if (! empty($tplidx)) $pdf->useTemplate($tplidx);
										if (empty($conf->global->ULTIMATE_FICHINTER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
										$pdf->setPage($pageposafter+1);
									}
								}
								else
								{
									// We found a page break
									$showpricebeforepagebreak=1;
								}
							}
							else	// No pagebreak
							{
								$pdf->commitTransaction();
							}

							$nexY = $pdf->GetY()+2;
							$pageposafter=$pdf->getPage();
							$pdf->setPage($pageposbefore);
							$pdf->setTopMargin($this->marge_haute);
							$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

							// We suppose that a too long description is moved completely on next page
							if ($pageposafter > $pageposbefore) {
								$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
							}
					
					//  DESIGNATION DES PARTIES
				/*	$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('I. DESIGNATION DES PARTIES'), 1, 'C');
					$posY = $pdf->getY();
					
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
					$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset("Le présent contrat est conclu entre les soussignés :
	- [nom et prénom, ou dénomination du bailleur/ domicile ou siège social/ qualité du bailleur (personne physique,
	personne morale (1))/ adresse électronique (facultatif)] (2) désigné (s) ci-après le bailleur ;
	- le cas échéant, représenté par le mandataire :
	- [nom ou raison sociale et adresse du mandataire ainsi que l'activité exercée] ;
	- le cas échéant, [numéro et lieu de délivrance de la carte professionnelle/ nom et adresse du garant] (3).
	- [nom et prénom du ou des locataires ou, en cas de colocation, des colocataires, adresse électronique (facultatif)]
	désigné (s) ci-après le locataire
	Il a été convenu ce qui suit :"), 1, 'C');
					$posY = $pdf->getY();
					//   OBJET DU CONTRAT
					$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('II. OBJET DU CONTRAT'), 1, 'C');
					$posY = $pdf->getY();
					
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
					$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset("Le présent contrat a pour objet la location d'un logement ainsi déterminé :
	A. Consistance du logement
	- localisation du logement : [exemples : adresse/ bâtiment/ étage/ porte etc.] ;
	- type d'habitat : [immeuble collectif ou individuel] ;
	- régime juridique de l'immeuble : [mono propriété ou copropriété] ;
	- période de construction : [exemples : avant 1949, de 1949 à 1974, de 1975 à 1989, de 1989 à 2005, depuis 2005] ;
	- surface habitable : [...] m2 ;
	- nombre de pièces principales : [...] ;
	- le cas échéant, Autres parties du logement : [exemples : grenier, comble aménagé ou non, terrasse, balcon, loggia,
	jardin etc.] ;
	- le cas échéant, Eléments d'équipements du logement : [exemples : cuisine équipée, détail des installations sanitaires
	etc.] ;
	- modalité de production de chauffage : [individuel ou collectif] (4) ;
	- modalité de production d'eau chaude sanitaire : [individuelle ou collective] (5)"), 1, 'C');
					$posY = $pdf->getY();
					$pageposafter=$pdf->getPage();*/

					// Detect if some page were added automatically and output _tableau for past pages
						while ($pagenb < $pageposafter)
						{
							$pdf->setPage($pagenb);
							if ($pagenb == $pageposbeforeprintlines)
							{
								$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1);
							}
							else
							{
								$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 0, 1);
							}
							$this->_pagefoot($pdf,$object,$outputlangs,1);
							$pagenb++;
							$pdf->setPage($pagenb);
							$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
							if (empty($conf->global->ULTIMATE_FICHINTER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
						if (isset($object->lines[$i+1]->pagebreak) && $object->lines[$i+1]->pagebreak)
						{
							if ($pagenb == $pageposafter)
							{
								$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1);
							}
							else
							{
								$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 0, 1);
							}
							$this->_pagefoot($pdf,$object,$outputlangs,1);
							// New page
							$pdf->AddPage();
							if (! empty($tplidx)) $pdf->useTemplate($tplidx);
							$pagenb++;
							if (empty($conf->global->ULTIMATE_FICHINTER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
					}
				}

				// Show square
				if ($pagenb == $pageposbeforeprintlines)
				{
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforcustomercomment - $heightforagreement - $heightforfreetext - $heightforfooter - 4, 0, $outputlangs, 0, 0);
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforcustomercomment - $heightforagreement - $heightforfreetext - $heightforfooter -4, 0, $outputlangs, 0, 0);
				}
				$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforcustomercomment - $heightforagreement - $heightforfreetext - $heightforfooter + 1;

				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));
			
				$this->result = array('fullpath'=>$file);

				return 1;
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","FICHEINTER_OUTPUTDIR");
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
		$outputlangs->loadLangs(array("main", "bills", "propal", "companies"));

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
		$title=$outputlangs->transnoentities("Quittance");
		$pdf->MultiCell($w, 3, $title, '', 'R');

		$pdf->SetFont('', 'B', $default_font_size);

		$posy+=5;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$textref=$outputlangs->transnoentities("Ref")." : " . $outputlangs->convToOutputCharset($object->ref);
		if ($object->statut == ImmoReceipt::STATUS_DRAFT)
		{
			$pdf->SetTextColor(128, 0, 0);
			$textref.=' - '.$outputlangs->transnoentities("NotValidated");
		}
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

		if ($object->type != 2)
		{
			$posy+=3;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("DateDue")." : " . dol_print_date($object->date_lim_reglement, "day", false, $outputlangs, true), '', 'R');
		}

		if ($object->thirdparty->code_client)
		{
			$posy+=3;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("CustomerCode")." : " . $outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		$posy+=1;

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
			if ($owner->country_id)
			{
				$tmparray=$owner->getCountry($owner->country_id,'all');
				$owner->country_code=$tmparray['code'];
				$owner->country=$tmparray['label'];
			}
			$carac_emetteur = pdf_build_address($outputlangs, $owner, $object->thirdparty, '', 0, 'source', $object);

			// Show sender
			$posy=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 42;
			$posy+=$top_shift;
			$posx=$this->marge_gauche;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->page_largeur-$this->marge_droite-80;

			$hautcadre=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 38 : 40;
			$widthrecbox=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 92 : 82;


			// Show sender frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 5);
			$pdf->SetXY($posx, $posy-5);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Bailleur'), 1, 'C');
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
			$posy=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 42;
			$posy+=$top_shift;
			$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->marge_gauche;

			// Bloc Locataire
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 5);
			$pdf->SetXY($posx, $posy-5);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Locataire Destinataire'), 1, 'C');
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
		return pdf_pagefoot($pdf, $outputlangs, 'INVOICE_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext);
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

	    // PHOTO
        $rank = $rank + 10;
        $this->cols['photo'] = array(
            'rank' => $rank,
            'width' => (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)?20:$conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH), // in mm
            'status' => false,
            'title' => array(
                'textkey' => 'Photo',
                'label' => ' '
            ),
            'content' => array(
                'padding' => array(0,0,0,0), // Like css 0 => top , 1 => right, 2 => bottom, 3 => left
            ),
            'border-left' => false, // remove left line separator
        );

	    if (! empty($conf->global->MAIN_GENERATE_INVOICES_WITH_PICTURE) && !empty($this->atleastonephoto))
	    {
	        $this->cols['photo']['status'] = true;
	    }


	    $rank = $rank + 10;
	    $this->cols['vat'] = array(
	        'rank' => $rank,
	        'status' => false,
	        'width' => 16, // in mm
	        'title' => array(
	            'textkey' => 'VAT'
	        ),
	        'border-left' => true, // add left line separator
	    );

	    if (empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT) && empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT_COLUMN))
	    {
	        $this->cols['vat']['status'] = true;
	    }

	    $rank = $rank + 10;
	    $this->cols['subprice'] = array(
	        'rank' => $rank,
	        'width' => 19, // in mm
	        'status' => true,
	        'title' => array(
	            'textkey' => 'PriceUHT'
	        ),
	        'border-left' => true, // add left line separator
	    );

	    $rank = $rank + 10;
	    $this->cols['qty'] = array(
	        'rank' => $rank,
	        'width' => 16, // in mm
	        'status' => true,
	        'title' => array(
	            'textkey' => 'Qty'
	        ),
	        'border-left' => true, // add left line separator
	    );

	    $rank = $rank + 10;
	    $this->cols['progress'] = array(
	        'rank' => $rank,
	        'width' => 19, // in mm
	        'status' => false,
	        'title' => array(
	            'textkey' => 'Progress'
	        ),
	        'border-left' => true, // add left line separator
	    );

	    if($this->situationinvoice)
	    {
	        $this->cols['progress']['status'] = true;
	    }

	    $rank = $rank + 10;
	    $this->cols['unit'] = array(
	        'rank' => $rank,
	        'width' => 11, // in mm
	        'status' => false,
	        'title' => array(
	            'textkey' => 'Unit'
	        ),
	        'border-left' => true, // add left line separator
	    );
	    if($conf->global->PRODUCT_USE_UNITS){
	        $this->cols['unit']['status'] = true;
	    }

	    $rank = $rank + 10;
	    $this->cols['discount'] = array(
	        'rank' => $rank,
	        'width' => 13, // in mm
	        'status' => false,
	        'title' => array(
	            'textkey' => 'ReductionShort'
	        ),
	        'border-left' => true, // add left line separator
	    );
	    if ($this->atleastonediscount){
	        $this->cols['discount']['status'] = true;
	    }

	    $rank = $rank + 10;
	    $this->cols['totalexcltax'] = array(
	        'rank' => $rank,
	        'width' => 26, // in mm
	        'status' => true,
	        'title' => array(
	            'textkey' => 'TotalHT'
	        ),
	        'border-left' => true, // add left line separator
	    );


	    $parameters=array(
	        'object' => $object,
	        'outputlangs' => $outputlangs,
	        'hidedetails' => $hidedetails,
	        'hidedesc' => $hidedesc,
	        'hideref' => $hideref
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
