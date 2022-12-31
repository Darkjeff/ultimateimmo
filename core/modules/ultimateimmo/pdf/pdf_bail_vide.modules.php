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
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

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
	function write_file($object, $outputlangs, $file = '', $socid = null, $courrier = null)
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
		if ($object->specimen) {
			$dir = $conf->ultimateimmo->dir_output."/";
			$file = $dir . "/SPECIMEN.pdf";
		} else {
			$objectref = dol_sanitizeFileName($object->ref);
			$dir = $conf->ultimateimmo->dir_output . "/rent/" . $objectref;
			$file = $dir . "/" . $objectref . ".pdf";
		}

		if (! file_exists($dir)) {
			if (dol_mkdir($dir) < 0) {
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}

		if (file_exists($dir)) {
			// Add pdfgeneration hook
			if (! is_object($hookmanager)) {
				include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
				$hookmanager=new HookManager($this->db);
			}
			$hookmanager->initHooks(array('pdfgeneration'));
			$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
			global $action;
			$reshook=$hookmanager->executeHooks('beforePDFCreation', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks

			// Set nblignes with the new facture lines content after hook
			//$nblignes = count($object->lines);
			$nblignes=0;
			//$nbpayments = count($object->getListOfPayments()); TODO : add method

			// Create pdf instance
			$pdf=pdf_getInstance($this->format);
			$default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
			$pdf->SetAutoPageBreak(1, 0);

			$heightforinfotot = 50+(4*$nbpayments);	// Height reserved to output the info and total part and payment part
			$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
			$heightforfooter = $this->marge_basse + (empty($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS)?12:22);	// Height reserved to output the footer (value include bottom margin)

			if (class_exists('TCPDF')) {
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}
			$pdf->SetFont(pdf_getPDFFont($outputlangs));

			// Set path to the background PDF File
			if (! empty($conf->global->MAIN_ADD_PDF_BACKGROUND)) {
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

			$paiement = new Immopayment($this->db);
			$result = $paiement->fetch_by_loyer($object->id);

			if (! empty($object->id)) {
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

				if ($resql) {
					$num = $this->db->num_rows($resql);
					while ( $i < $num ) {
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

				if ($resql) {
					$num = $this->db->num_rows($resql);
					while ( $j < $num ) {
						$objproperty = $this->db->fetch_object($resql);
						$j++;
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
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Consistance'), 1, 'L', 1);
				$posYL = $pdf->getY();

				$pdf->SetXY($posX+$widthrecbox/3, $posY);
				$pdf->MultiCell($widthrecbox*2/3, 3, $outputlangs->convToOutputCharset('Désignation des locaux et équipements privatifs:'), 1, 'L', 1);
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
				$pdf->SetXY($posX+2, $posYL+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posYL+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Appartement'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Maison individuelle'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('...........'), 0, 'L');
				$posY = $pdf->getY()+2;

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', $default_font_size + 1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Dépendances'), 1, 'L', 1);
				$posY = $pdf->getY()+1;

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Garage n°'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Place de stationnement n°'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX+2, $posY+1.5);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Cave n°'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX+2, $posY+1.5);
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
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_GARDIENNAGE == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Gardiennage'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_INTERPHONE == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('lnterphone'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ASCENSEUR == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Ascenseur'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_VIDEORDURES == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Vide-ordures'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ANTENNETVCOLLECTIVE == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Antenne TV collective'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_ESPACESVERTS == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Espace(s) vert(s)'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_CHAUFFAGECOLLECTIF == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
				$pdf->SetXY($posX+6, $posY+0.5);
				$pdf->MultiCell($widthrecbox/3, 3, $outputlangs->convToOutputCharset('Chauffage Collectif'), 0, 'L');
				$posY = $pdf->getY();

				$pdf->rect($posX+2, $posY+1.5, 2, 2);
				$pdf->SetXY($posX-2, $posY+0.2);
				if ($conf->global->ULTIMATE_IMMO_EQUIPEMENT_EAUCHAUDECOLLECTIVE == 1) {
					$pdf->MultiCell($posX, 3, $outputlangs->convToOutputCharset('X'), 0, 'C');
				}
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
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

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
				$text = $outputlangs->transnoentities("<strong>MONTANT OU LOYER (voir conditions générales chapitre 1) :<br /><br />
				Il est fixé librement entre les parties en application de l'article 17 a) et de l'article 17 b) de la loi.<br />
				Cependant pour les baux contractés entre le 01.08.2013 et le 31.07.2014 et UNIQUEMENT dans les communes mentionnées par l'annexe du décret n°2013-689 OU 30.07.2013 fixant un montant maximum d'évolution des loyers, conformément à l'article 18 de la loi, le loyer des logements vacants définis à l'article 17 b) ne peut excéder le dernier loyer appliqué au précédent locataire révisé dans les limites prévues à l'article 17 d), sauf cas suivants: </strong><br>
				- Lorsque le bailleur a réalisé, depuis la conclusion du dernier contrat, des travaux d'amélioration portant sur les parties privatives ou communes d'un montant au moins égal à la moitié de la dernière année de loyer, la hausse du loyer annuel ne peut excéder 15% du coût réel des travaux toutes taxes comprises;<br>
				- Lorsque le dernier loyer appliqué au précédent locataire est manifestement sous-évalué, la hausse du nouveau loyer ne peut excéder la plus élevée des deux limites suivantes<br>
				1. La moitié de la différence entre le montant moyen d'un loyer représentatif des loyers habituellement constatés dans le voisinage pour des logements comparables déterminé selon les modalités prévues à l'article 19 de la loi du 06.07 1989 et le dernier loyer appliqué au précédent locataire;<br>
				2. Une majoration du loyer annuel égale à 15% du coût réel des travaux toutes taxes comprises, dans le cas où le bailleur a réalisé depuis la fin du dernier contrat de location des travaux d'amélioration portant sur les parties privatives ou communes d'un montant au moins égal à la moitié de la dernière année de loyer.<br /><br />
				<strong>Le montant du loyer sera payable au domicile du bailleur ou de la personne qu'il aura mandaté à cet effet.</strong><br /><br />
				<strong>RÉVISION OU LOYER</strong> art. 17-1-1) de la loi du 06.07.1989<br /><br />
				La variation annuelle du loyer ne peut excéder, à la hausse, la variation sur un an de l'indice de référence des loyers publié par l'I.N.S.E.E. dont les éléments de référence sont indiqués en page 8.<br>
				Après sa date de prise d'effet, le bailleur dispose d'un an pour manifester sa volonté d'appliquer la révision du loyer. À défaut le bailleur est réputé avoir renoncé à la révision du loyer pour l'année écoulée. Si le bailleur manifeste sa volonté de réviser le loyer, dans un délai d'un an, cette révision prend effet à compter de sa demande. ");
				$pdf->writeHTMLCell($widthbox, 3, $posX, $posY, $outputlangs->convToOutputCharset($text), 1, 0, 0, true, 'J');

				$posY = $pdf->getY()+200;

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

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
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('CONDITIONS GÉNÉRALES'), 1, 'C');

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posYL = $pdf->getY();
				$posYR = $pdf->getY();
				$pdf->SetXY($posX, $posYL);
				$pdf->SetFillColor(255, 255, 127);

				$text = $outputlangs->transnoentities("I Durée - résiliation - renouvellement");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->convToOutputCharset($text), 1, 'C', 1);
				$posYL = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities(" <strong><U>A/CONTRAT DURÉE MINIMALE DE 3 OU 6 ANS</U></strong><br>
			    DURÉE INITIALE (art 10 et 13 de la loi) Le contrat est conclu pour une durée AU MOINS ÉGALE à 3 ans (bailleur 'personne physique' ou 'société civile familiale') ou à 6 ans (bailleur 'personne morale')<br>
			    <strong>RÉSILIATION - CONGÉ</strong> (articles 13 et 15 de la loi) : <br>
			    Il pourra être résilié par lettre recommandée avec avis de réception ou par acte d'huissier ou par remise en main propre contre récépissé ou émargement<br>
			    <U>PAR LE LOCATAIRE</U>, à tout moment, en prévenant le bailleur 3 mois à l'avance, délai ramené à 1 mois en cas de location dans les territoires mentionnés au 1<sup>er</sup> alinéa du 1 l'article 17, en cas d'obtention d'un premier emploi, de mutation, de perte d'emploi ou de nouvel emploi consécutif â une perte d'emploi, ou en cas de congé émanant d'un locataire qui s'est vu attribuer un logement social (art. L.35/2 du CCH). ou dont l'état de santé, constaté par un certificat médical, justifie un changement de domicile, ou d'un locataire bénéficiaire du revenu de solidarité active ou de l'allocation adulte handicapé<br>
			    <U>PAR LE BAILLEUR</U>, en prévenant le locataire 6 mois au moins avant le terme du contrat. Le congé devra être fondé, soit sur sa décision de reprendre ou de vendre le logement, soit sur un motif légitime et sérieux, notamment l'inexécution par le locataire de l'une des obligations principales lui incombant <br>
			    Le congé devra être indiqué le motif allégué et:<br>
			    en cas de reprise, les nom et adresse du bénéficiaire de la reprise qui ne peut être que l'une des personnes prévues à l'art 15-1 de la loi,<br>
			    en cas de vente, le prix et les conditions de la vente projetée, ce congé valant offre de vente au profit du locataire. Le congé devra en outre respecter le formalisme de l'article 15-11 de la loi du 06.07.1989<br> <strong>RENOUVELLEMENT</strong> (articles 10, 11, 13 et 17 §c de la loi):<br>
			    1) 6 mois au moins avant le terme du contrat, le bailleur pourra faire une proposition de renouvellement par lettre recommandée avec avis de réception ou par acte d'huissier <br>
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
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posYL = $pdf->getY();
				$posYR = $pdf->getY();
				$pdf->SetXY($posX, $posYL);
				$pdf->SetFillColor(255, 255, 127);

				$text = $outputlangs->transnoentities("II - Cautionnement");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->convToOutputCharset($text), 1, 'C', 1);
				$posYL = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities(" Le cas échéant, le bailleur peut demander qu'un tiers se porte caution et s'engage à exécuter, en cas de défaillance du locataire, les obligations résultant du contrat de location. Toutefois, aucun cautionnement ne peut être demandé, à peine de nullité, par le bailleur qui a souscrit une assurance garantissant les obligations locatives ou toute autre forme de garantie sauf en cas de logement loué à un étudiant ou un apprenti (art. 22-1 de la loi 89-462 du 06.07.1989 modifié par la loi du 24.03.2014).<br>
				Les formalités suivantes sont rendues obligatoires sous peine de nullité du cautionnement :<br>
				- le bailleur remet à la caution un exemplaire du contrat de location;<br>
				- la personne qui se porte caution doit, sur l'acte de caution et de sa main<br>
				· indiquer le montant du loyer, et le cas échéant les conditions de sa révision, tels qu'ils figurent au contrat de location,
				· reconnaître la portée et la nature de son engagement.
				· limiter la durée de son engagement. recopier l'article 22-1 avant dernier alinéa de la loi du 06 07.1989.
				Le bailleur a une obligation au moins annuelle d'information de la caution personne physique en cas de cautionnement illimité (art. 2293 modifié du code civil) ou si le bailleur est un bailleur professionnel (art L.341-1 du code de la consommation).");
				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX, $posYL, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYL = $pdf->getY();

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX, $posYL);
				$text = $outputlangs->transnoentities("III - Dépôt de garantie");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYL = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities(" Conformément aux articles 22 de la 1oi, le dépôt de garantie éventuellement demandé par le bailleur au locataire afin de garantir la bonne exécution de ses obligations ne pourra excéder 1 mois de loyer net de charges (loi n°2008-I II du 08.02.2008). Au moment de la signature du bail, le dépôt de garantie est versé au bailleur directement par le locataire ou par l'intermédiaire d'un tiers. Non productif d'intérêts, il ne sera révisable ni en cours de contrat initial, ni lors du renouvellement éventuel.<br>
				Il sera rendu au locataire dans un délai de 2 mois à compter de la remise en mains propres des clés ou de leur envoi en lettre recommandée avec accusé de réception au bailleur ou à son mandataire ( 1 mois s'il y a conformité entre les états des lieux d'entrée et de sortie), déduction faite, le cas échéant, des sommes restant dues au bailleur et des sommes dont celui-ci pourrait être tenu pour responsable, aux lieu et place du locataire, sous réserve qu'elles soient dûment justifiées. <br>
				Si la location se situe dans un immeuble collectif, le bailleur procède à un arrêté de comptes provisoire et peut, lorsqu'elle est dûment justifiée, conserver une provision ne pouvant excéder 20% du montant du dépôt de garantie jusqu'à l'arrêté des comptes de l'immeuble.
				La régularisation définitive et la restitution du solde, déduction faite, le cas échéant, des sommes restant au bailleur et des sommes dont celui-ci pourrait être tenu aux lieux et places du locataire, sont effectués dans le mois qui suit l'approbation définitive des comptes de l'immeuble. Les parties peuvent convenir de solder immédiatement les comptes. <br>
				Les intérêts dus en cas de retard seront de 10% du loyer hors charges par mois de retard.<br>  ");
				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX, $posYL, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');

				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("En cas de vente ou donation du logement pendant la durée du bail, la restitution du dépôt de garantie incombe au nouveau bailleur et toute convention entre l'acquéreur et le vendeur pendant la vente sur le sort du dépôt de garantie est inopposable au locataire (art.22 de la loi n°89-462 du 06.07.1989 modifié par la loi n° 2009-323 du 25.03.2009).<br>
				Ce dépôt ne pourra sous aucun prétexte être affecté par le locataire au paiement des derniers mois de loyers.");

				$pdf->writeHTMLCell($widthbox/2 -2, 3, $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("IV - Charges - Contribution du Locataire au Partage des Économies de Charges");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYR = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("En sus du loyer, le locataire remboursera au bailleur sa quote-part des charges réglementaires, conformément à la liste fixée par le décret n°87-713 du 26 août 1987.<br>
				Les charges récupérables, sommes accessoires au loyer principal, sont exigibles en contrepartie : <br>
				- des services rendus liés à l'usage des différents éléments de la chose louée,<br>
				- des dépenses d'entretien courant et des menues réparations sur les éléments d'usage commun de la chose louée,<br>
				- des impositions qui correspondent à des services dont le locataire profite directement.<br>
				Ces charges, seront réglées en même temps que le loyer principal, par provisions mensuelles ou trimestrielles et feront l'objet d'une régularisation annuelle.<br>
				Le montant des charges sera fixé chaque année par le bailleur en fonction des dépenses réellement exposées l'année précédente ou du budget prévisionnel, le montant de chaque provision étant réajusté en conséquence.<br>
				Un mois avant l'échéance de la régularisation annuelle, le bailleur adressera au locataire un décompte par nature de charges, ainsi que, dans les immeubles collectifs, le mode de répartition entre tous les locataires, et le cas échéant, une note d'information sur les modalités de calculs des charges de chauffage et d'eau chaude collectifs. Pendant six mois, les pièces justificatives seront tenues è la disposition du locataire. <br>
				Conformément au nouvel article 23-1 de la loi du 06.07.1989 issu de la 1oi n° 2009-323 du 25.03.2009, le bailleur peut, sous certaines conditions décret n° 2009-/439) et arrêté NOR DEVU/0925487 du 23 novembre 2009) demander au locataire, et après concertation avec ce dernier, une contribution pour la réalisation de certains travaux, d'économie d'énergie. Son montant est payable mensuellement à compter de la fin des travaux et pour une période déterminée (le cas échéant, indiqués en page 8).");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("V - Confort - Habitabilité - Décence du Logement");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYR = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("<strong>Les locaux d'habitation doivent répondre aux normes d'habitabilité définies à l'article 6 de la loi du 06.07.1989 modifié par l'article 99 de la loi 2005-157 du 23.02.2005.</strong><br>
				Les travaux (prévus à l'article Ier de la loi du 12.07.1967) destinés à adapter totalement ou partiellement aux normes de salubrité, de sécurité, d'équipement el de confort n'ont pour but exclusif que de les mettre en conformité avec tout ou partie des dispositions des articles I à 4 du décret du 30.01 2002 susvisé sans en dépasser les caractéristiques qui y sont définies.<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

				// print TEXT
				$posYL = $pdf->getY();
				$posYR = $pdf->getY();
				$pdf->SetXY($posX, $posYL);

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("<strong> > LOCAUX VACANTS CONSTRUITS AVANT LE 01.09.48 </strong>:<br> conformément à l'article 25 de la loi n• 86-1290 du 23 12.1986 modifiée, les locaux vacants à comp1er du 23. 12 1986 (hormis ceux, classés en catégorie IV) ne sont pas soumis aux dispositions de la loi n°48-1360 du 01.09.1948. Ils sont désormais régis par les chapitres Ier à III du Titre ler de la loi n° 89-462 du 06.07.1989.<br>
				Si les locaux loués depuis le 23.12.1986 ne satisfont pas aux normes minimales de confort et d'habitabilité fixées par le décret du 06.03 1987 après avis de la commission nationale de concertation, le locataire peut, dans le délai d'un an à compter de la date de prise d'effet du contrat de location initial, demander au propriétaire leur mise en conformité avec ces normes sans qu'il soit porté atteinte à la validité du contrat de location en cours. À défaut d'accord entre les parties, le juge saisi détermine, le cas échéant, la nature des travaux à réaliser et le délai de leur exécution, qu'il peut même d'office assortir d'une astreinte; il peut également se prononcer sur une demande de modification du loyer fixé par le bailleur ou proposé par le locataire. À défaut de mise aux normes effectuée dans les conditions précitées, le loyer des locaux soumis au présent article est fixé conformément à l'art. 17 § b de la loi du 06.07.1989.<br>
				Les dispositions du présent article ne sont pas applicables aux locaux classés en catégories IV.<br>
				<strong> > ARTICLE 20-1 DE LA LOI OU 06.07.1989 modifié par la loi n°2009323 du 25.03.2009</strong> :<br>
				si le logement loué ne satisfait pas aux dispositions des alinéas 1er et 2ème alinéas de l'article 6, le locataire peut demander au propriétaire leur mise en conformité sans qu'il soit porté atteinte è la validité du contrat en cours. À défaut d'accord entre les parties, ou à défaut de réponse du propriétaire dans un délai de 2 mois, la commission départementale de conciliation peut être saisie et rendre un avis dans les conditions fixées à l'article 20. La saisine de la commission ou la remise de son avis ne constitue pas un préalable à la saisine du juge par l'une ou l'autre partie. Le juge saisi par l'une ou l'autre partie détermine, le cas échéant, la nature des travaux à réaliser et le délai de leur exécution. Il peut réduire le montant du loyer ou suspendre, avec ou sans consignation, son paiement et la durée du bail jusqu'à l'exécution de ces travaux. Le juge transmet au représentant de l'État dans te département l'ordonnance ou le jugement constatant que le logement loué ne satisfait pas aux dispositions des Ier et 2ème alinéas de l'article 6.<br>
				<strong> > ARTICLE 6 § n DE LA LOI DU 06.07.1989</strong>: <br>
				si le logement répond aux normes minimales de décence fixées par le décret du 30.01.2002, les parties peuvent convenir par une clause expresse des travaux que le locataire exécutera ou fera exécuter et des modali1és de leur imputation sur le loyer, cette clause prévoit la durée de cette imputation et, en cas de départ anticipé du locataire, les modalités de son dédommagement sur justification des dépenses effectuées.<br>
				<strong> > ARTICLE 17 § e DE LA LOI DU 06.07.1989</strong> : <br>
				au-delà des caractéristiques définies par les dispositions du décret du 30.01 2002, les parties peuvent convenir, par une clause expresse, de travaux d'amélioration du logement que le bailleur fera exécuter. Cette clause fixe la majoration du loyer consécutive à la réalisation de ces travaux.
				");
				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX, $posYL, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("VI - Réglementation Relative à la Sécurité des Personnes et des Biens");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYR = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("Un dossier de diagnostic technique, fourni par le bailleur, est annexé au présent contrat de location (ordonnance n°2005-655 du 08.06.2005 art. 22 Ill (JORF 09.06.2005)). <br>
				Ce dossier comprend :<br>
				le diagnostic de performance énergétique (DPE) prévu à l'article L 134-1 du code de la construction et de l'habitation;<br>
				le locataire ne peut se prévaloir à l'encontre du bailleur des informa1ions contenues dans ce diagnostic de performance énergétique qui n'a qu'une valeur informative le propriétaire bailleur tient le DPE à la disposition de tout candidat locataire;<br>
				le constat de risque d'exposition au plomb prévu à l'article L. 1334-5 et L 1334-7 du code de la santé publique; <br>
				le cas échéant, l'état des risques naturels, miniers et technologiques dans les zones mentionnées au l de l'article L. 125-5 du code de l'environnement. <br>
				Pour les immeubles bâtis dont le permis de construire a été délivré avant le 01 07.1997, le bailleur met à la disposition du locataire le dossier amiante partie privative (article R. 1334-29-4-1 du code de la santé publique).<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("VII - État des Lieux");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYR = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("Conformément à l'article 3-2 de la loi, un état des lieux est établi lors de la remise et de la restitution des clés au locataire. Il est établi par les parties ou par un tiers mandaté par elles, contradictoirement et amiablement, en autant d'exemplaire que de parties et est annexé au bail. En cas d'intervention d'un tiers, les honoraires sont partagés entre le bailleur et le locataire (leur montant doit être inférieur ou égal à un plafond fixé ultérieurement par voie règlementaire).<br>
				Si l'état des lieux ne peut être établi dans les conditions prévues ci-dessous, il l'est, sur l'initiative de la partie la plus diligente, par un huissier de justice à frais partagés par moitié entre le bailleur el le locataire et à un coût fixé par décret en Conseil d'État. A défaut d'état des lieux, la présomption de l'article l 731 du code civil ne peut être invoquée par celle des parties qui aura fait obstacle à l'établissement de l'acte ou qui a refusé la remise de son exemplaire. En cas d'état des lieux incomplet, le locataire peut demander la modification dans un délai de 10 jours à compter de son établissement.<br>
				Pendant le 1er mois de la période de chauffe, le locataire peut demander que l'état des lieux soit complé1é par l'état des éléments de chauffage.<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("VIII - Obligations du Bailleur");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYR = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("<strong>Le bailleur est tenu des obligations principales suivantes </strong>:<br>
				1. Préciser sur le contrat de location ses noms et domicile (ou dénomination sociale et siège social), et, le cas échéant ceux de son mandataire. En cas de vente ou de transmission des locaux, le nouveau bailleur est tenu de notifier par lettre recommandée avec accusé de réception au locataire ses nom et domicile (ou dénomination sociale et siège social), et, le cas échéant ceux de son mandataire.<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');
				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

				// print TEXT
				$posYL = $pdf->getY();
				$posYR = $pdf->getY();
				$pdf->SetXY($posX, $posYL);

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities(" 2. Communiquer au locataire, lorsque l'immeuble est soumis au statut de la copropriété, les extraits de règlement de copropriété concernant la destination de l'immeuble, la jouissance et l'usage des parties privatives et communes et précisant la quote-part afférente au 1ot loué dans chacune des catégories de charges.<br>
				3. Annexer au contrat une information sur les modalités de réceptions des services de télévision (article 3-2). <br>
				4. Remettre au locataire un logement décent (caractéristiques correspondantes définies par le décret n° 2002-120 du 30.01.02) ne laissant apparaître de risques manifestes pouvant porter atteinte à la sécurité physique ou à la santé et doté des éléments le rendant conforme à l'usage d'habitation (le manquement à cette obligation peut entraîner des travaux de mise en conformité en application des dispositions du chapitre V ci-avant).<br>
				Délivrer au locataire le logement en bon état d'usage et de réparations, ainsi que les équipements mentionnés au contrat en bon état de fonctionnement (hormis les travaux faisant l'objet de la clause expresse stipulée en page 6 conformément aux dispositions du chapitre V.<br>
				5. Assurer au locataire la Jouissance paisible du logement et, sans préjudice des dispositions de l'article 1721 du code civil, le garantir des vices ou défauts de nature à y faire obstacle (hormis ceux qui, consignés dans l'état des lieux, auraient fait l'objet de la clause expresse stipulée en page 8 conformément aux dispositions du chapitre V).<br>
				6. Entretenir les locaux en état de servir à l'usage prévu et y faire toutes les réparations nécessaires autres que locatives.<br>
				7. Ne pas s'opposer aux aménagements réalisés par le locataire et ne constituant pas une transformation de la chose louée.<br>
				8. Transmettre gratuitement une quittance au locataire lorsqu'il en fait la demande, le bailleur ou son mandataire étant libre de choisir les modalités de celle remise. Avec l'accord exprès du locataire, le bailleur peul procéder à la transmission dématérialisée de la quittance.<br>
				9. Délivrer un reçu dans tous les cas où le LOCATAIRE effectue un paiement partiel.");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX, $posYL, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYL = $pdf->getY();
				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX, $posYL);
				$text = $outputlangs->transnoentities("IX - Obligations du Locataire");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYL = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("<strong>Le locataire est tenu des obligations principales suivantes</strong>:<br>
				Payer le loyer et les charges récupérables aux termes convenus. Le paiement mensuel est de droit s'il en fait la demande.<br>
				2. User PAISIBLEMENT des locaux et équipements loués suivant la destination prévue au contrat.<br>
				3. Répondre des dégradations et pertes survenant pendant la durée du contrat dans les locaux dont il a la jouissance exclusive, à moins qu'il ne prouve qu'elles ont eu lieu par cas de force majeure, par la faute du bailleur ou par le fait d'un tiers qu'il n'a pas introduit dans le logement.<br> Prendre à sa charge l'entretien courant du logement et des équipements mentionnés au contrat, les menues réparations et l'ensemble des réparations locatives définies par le décret n° 87-712 du 26 août 1987, sauf si elles sont occasionnées par vétusté, malfaçon, vice de construction, cas fortuit ou force majeure. <br>
				4. Souscrire un contrat d'entretien auprès d'une entreprise spécialisée (ou en rembourser le coût au bailleur si ce dernier en assure le paiement) pour faire :<br>");
				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX, $posYL, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');

				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("5. entretenir au moins une fois par an les équipements individuels de chauffage (chauffage gaz, brûleurs gaz, ...) et en justifier à première demande du bailleur <br>
				6. Sans que cette information engage sa responsabilité lorsque les dégâts ne sont pas de son fait personnel, informer immédiatement le bailleur de tout sinistre et dégradation se produisant dans les lieux loués, même s'il n'en résulte aucun dommage apparent.<br>
				7. Ne pas transformer sans l'accord écrit du bailleur les locaux loués et leurs équipements ; le bailleur pourra, si le locataire a méconnu cette obligation, exiger la remise en l'état des locaux et des équipements au départ du locataire ou conserver les transformations effectuées sans que le locataire puisse réclamer une indemnité pour les frais engagés ; le bailleur aura toutefois la faculté d'exiger aux frais du locataire la remise immédiate des lieux en l'état si les transformations mettent en péril le bon fonctionnement des équipements ou la sécurité du local. Les aménagements ne constituant pas une transformation des locaux loués, c'est-à-dire les changements peu importants non susceptibles de nuire à l'immeuble et qui n'ont rien d'irréversibles, ne nécessitent pas l'accord du bailleur.<br>
				8. Permettre l'accès aux lieux loués pour la préparation el l'exécution de travaux d'amélioration des parties communes ou des parties privatives du même immeuble, ainsi que les travaux nécessaires au maintien en état et à l'entretien normal des locaux, de travaux d'amélioration de la performance énergétique. Les deux derniers alinéas de l'article 1724 du code civil étant applicables à ces travaux. Les modalités de ces travaux sont précisées à l'article 7e de la loi.<br>
				Le locataire devra laisser visiter les locaux loués chaque fois que cela sera rendu nécessaire pour des réparations ou la sécurité de l'immeuble, ces visites devant s'effectuer, sauf urgence, les jours ouvrables après que le locataire en ait été préalablement averti.<br>
				9. Respecter le règlement intérieur de l'immeuble, affiché dans les parties communes des immeubles collectifs.<br>
				Exécuter strictement toutes les dispositions du règlement de copropriété dont des extraits lui ont été communiqués par le bailleur en application de l'article 3 de la loi. <br>
				S'assurer contre les risques locatifs dont il doit répondre en sa qualité de locataire (incendie, dégât des eaux, ...) et en justifier au bailleur à la remise des clefs, en lui transmettant 1'attestation émise par son assureur ou son représentant. Il devra en justifier ainsi chaque année, à la demande du bailleur.<br>
				À défaut, de la remise de l'attestation d'assurance et après un mois à compter de la mise en demeure non suivie d'effet, le bailleur pourra demander la résiliation du contrat en application de la clause résolutoire ou souscrire une assurance pour le compte du locataire, récupérable auprès de celui-ci. Une copie du contrat d'assurances est transmise au locataire lors de la souscription et à chaque renouvellement de contrat.<br>
				10. Ne pas céder le contrat de location, ni sous-louer le logement sauf avec l'accord écrit du bailleur, y compris sur le prix du loyer. En cas de cessation du contrat principal, le sous-locataire ne pourra se prévaloir d'aucun droit à l'encontre du bailleur, ni d'aucun titre d'occupation.<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, 3, $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

				// print TEXT
				$posYL = $pdf->getY();
				$posYR = $pdf->getY();
				$pdf->SetXY($posX, $posYL);

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities(" 11. Laisser visiter, en vue de la vente ou de la location, les lieux loués deux heures par jour pendant les jours ouvrables ; l'horaire de visite sera défini par accord entre les parties, à défaut d'accord, les visites auront lieu entre 17h et 19h.<br>
				12. S'assurer que le bailleur et le cas échéant, son mandataire, sont informés de l'existence de son conjoint ou du partenaire auquel il est lié par un PACS, à défaut, nonobstant les dispositions des articles 515-4 et 1751 du code civil, les notifications ou significations faites par le bailleur sont de plein droit opposables au partenaire lié par PACS au locataire ou au conjoint dont l'existence n'a pas été préalablement portée à la connaissance du bailleur.");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX, $posYL, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYL = $pdf->getY();

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX, $posYL);
				$text = $outputlangs->transnoentities("X - Clause Résolutoire et Clauses Pénales");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYL = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("Le présent contrat sera RÉSILIÉ IMMEDIATEMENT ET DE PLEIN DROIT, c'est-à-dire sans qu'il soit besoin de faire ordonner cette résolution en justice:<br>
				Deux mois après un commandement demeuré infructueux à défaut de paiement aux termes convenus de toute ou partie du loyer et des charges dûment justifiées ou en cas de non-versement du dépôt de garantie éventuellement prévu au contrat.<br>
				Lorsqu'une caution garantit les obligations du présent contrat de location, le commandement de payer est s1gnifié à la caution dans un délai de 15 jours, à compter de la s1gnification du commandement au locataire. À défaut, la caution ne peut être tenue au paiement des pénalités ou intérêts de retard. <br>
				Les frais et honoraires exposés par le bailleur pour la délivrance des commandements ou la mise en recouvrement des sommes qui lui sont dues, seront mis à la charge du locataire, sous réserve de l'appréciation des tribunaux, conformément à l'article 700 du code de procédure civile.<br>
				Il est bien entendu qu'en cas de paiement par chèque, le loyer ne sera considéré comme réglé qu'après encaissement. <br>
				Un mois après un commandement demeuré infructueux à défaut d'assurance contre les risques locatifs.<br>
				En cas de troubles de voisinage constituant le non-respect de la jouissance paisible des lieux loués, constatés par une décision de justice passée en force de chose jugée.<br>
				Une fois acquis au bailleur le bénéfice de la clause résolutoire, le locataire devra libérer immédiatement les lieux; s'il s'y refuse, le bailleur devra préalablement à toute expulsion faire constater la résiliation du bail par le juge des référés. En outre, et sans qu'il soit dérogé à la précédente clause résolutoire, le locataire s'engage formellement à respecter les deux clauses pénales qui suivent:<br>
				1- En cas de non-paiement du loyer ou de ses accessoires aux termes convenus, et dès le premier acte d'huissier, le locataire supportera une majoration de plein droit sur le montant des sommes dues, calculée selon le taux d'intérêt légal, en dédommagement du préjudice subi par le bailleur, et ce sans qu'une mise en demeure soit nécessaire, en dérogation à l'article 1230 du code civil<br>
				2- Si le locataire déchu de tout droit d'occupation ne libère pas les lieux, résiste à une ordonnance d'expulsion ou obtient des délais pour son départ, il devra verser par jour de retard, outre les charges, une indemnité conventionnelle d'occupation égale à deux fois le loyer quotidien, ceci jusqu'à complet déménagement et restitution des clés. Cette indemnité est destinée à dédommager le bailleur du préjudice provoqué par l'occupation abusive des lieux loués faisant obstacle à l'exercice des droits du bailleur.<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX, $posYL, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("XI - Solidarité - Indivisibilité - Élection de Domicile");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYR = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("Pour l'exécution de toutes les obligations résultant du présent contrat, il y aura solidarité et indivisibilité entre <br>
				les parties ci-dessus désignées sous le vocable 'le LOCATAIRE'; <br>
				les héritiers ou représentants du LOCATAIRE venant à décéder (sous réserve de l'art. 802 du code civil) et toutes les personnes pouvant se prévaloir de la transmission du contrat en vertu de l'article 14 de la loi.<br>
				Les parties signataires font élection de domicile : le bailleur en sa demeure et le locataire dans les lieux loués pour la durée effective du présent bail.<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				// print TEXT
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("XII - Frais - Honoraires");
				$pdf->MultiCell($widthbox/2 -2, 3, $outputlangs->transnoentities($text), 1, 'C', 1);
				$posYR = $pdf->getY();

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX+$widthbox/2, $posYR);
				$text = $outputlangs->transnoentities("Le montant et modalités de répartition des honoraires sont indiquées en page 8.<br>");

				$pdf->writeHTMLCell($widthbox/2 -2, '', $posX+$widthbox/2, $posYR, $outputlangs->transnoentities($text), 1, 2, 0, true, 'J');
				$posYR = $pdf->getY();

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->SetTextColor(0, 0, 0);
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0, 0, 0));
				// Conditions contrat
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("Outre les conditions générales, le présent contrat de location est consenti et accepté aux prix, charges et conditions suivants : ");
				$pdf->MultiCell($widthbox, 3, $outputlangs->transnoentities($text), 1, 'C', 0);
				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posx=$this->marge_gauche;
				$posy = $pdf->getY();
				$hautcadre = 36;
				$widthrecbox = $this->page_largeur-$this->marge_droite-$this->marge_gauche;
				$pdf->SetXY($posx, $posy);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('DURÉE INITIALE DU CONTRAT'), 1, 'C', 1);
				$posy=$pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetTextColor(0, 0, 0);
				$pdf->Rect($posx+2, $posy+1.5, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+2, $posy+1.5);

				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posx+6, $posy+0.5);
				$text = $outputlangs->transnoentities("<strong>BAILLEUR «PERSONNE PHYSIQUE» OU «SOCIÉTÉ CIVILE FAMILIALE»</strong> :");
				$pdf->writeHTMLCell($widthrecbox, 3, $posx+6, $posy+0.5, $text, 0, 'L');
				$posy = $pdf->getY();

				$pdf->SetTextColor(0, 0, 0);
				$pdf->Rect($posx+4, $posy+4, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+8, $posy+3);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('3 Ans au moins, soit....... ans.'), 0, 'L');
				$posy = $pdf->getY();

				$pdf->SetTextColor(0, 0, 0);
				$pdf->Rect($posx+4, $posy+1.5, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+8, $posy+1.5);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("INFÉRIEURE À 3 ANS (mais d'au moins 12 mois), soit:....... mois, durée motivée par l'événement suivant : "), 0, 'L');
				$posy = $pdf->getY();
				$pdf->SetXY($posx+8, $posy+1.5);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("RAISONS PROFESSIONNELLES OU FAMILIALES DU BAILLEUR : "), 0, 'L');
				$posy = $pdf->getY();

				$pdf->Rect($posx+2, $posy+1.5, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+2, $posy+1.5);
				$pdf->SetXY($posx+6, $posy+0.5);
				$text = $outputlangs->transnoentities("<strong>BAILLEUR« PERSONNE MORALE» :");
				$pdf->writeHTMLCell($widthrecbox, 3, $posx+6, $posy+0.5, $text, 0, 'L');
				$posy = $pdf->getY();

				$pdf->SetTextColor(0, 0, 0);
				$pdf->Rect($posx+4, $posy+4, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+8, $posy+3);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('6 Ans au moins, soit....... ans.'), 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posx=$this->marge_gauche;
				$posy = $pdf->getY();
				$pdf->SetXY($posx, $posy);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("DATE DE PRISE D'EFFET"), 1, 'C', 1);
				$posy=$pdf->getY();
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posx+4, $posy);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("le contrat prendra effet le : ").dol_print_date($object->date_start), 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY();
				$pdf->SetXY($posx, $posy+1);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("RENOUVELLEMENT - CONGÉ (préavis par le bailleur)"), 1, 'C', 1);
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("S'il veut renouveler ou résilier le contrat, le bailleur devra avertir le locataire dans les conditions de forme et de délai prévues au chapitre I des Conditions Générales, soit au plus tard le :");
				$pdf->MultiCell($widthbox, 3, $text, 1, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY();
				$pdf->SetXY($posx, $posy);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("ANCIEN LOYER"), 1, 'C', 1);
				$posy = $pdf->getY();
				$hautcadre = 12;
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("(uniquement si l'ancien locataire a quitté les lieux moins de 18 mois avant la signature du bail) en date du :");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size-3);
				$text2 = $outputlangs->transnoentities("Sommes en toutes lettres -------------- ");
				$text3 = $outputlangs->transnoentities("Sommes en chiffres");
				$pdf->MultiCell($widthbox, 3, $text2.' '.$text3, 0, 'C');
				$posy = $pdf->getY();
				$pdf->SetFont('', 'B', $default_font_size);
				$text = $outputlangs->transnoentities("Montant de l'ancien loyer : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY()+2;

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY();
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("MONTANT DES PAIEMENTS"), 1, 'C', 1);
				$hautcadre = 40;
				$posy = $pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', '', $default_font_size-3);
				$text2 = $outputlangs->transnoentities("Sommes en toutes lettres -------------- ");
				$text3 = $outputlangs->transnoentities("Sommes en chiffres");
				$pdf->MultiCell($widthbox, 3, $text2.' '.$text3, 0, 'C');
				$posy = $pdf->getY();
				$pdf->SetFont('', 'B', $default_font_size);
				$text = $outputlangs->transnoentities("Loyer mensuel : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$pdf->SetFont('', 'B', $default_font_size);
				$text = $outputlangs->transnoentities("Provisions sur charges : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$pdf->SetFont('', 'B', $default_font_size);
				$text = $outputlangs->transnoentities("TOTAL MENSUEL : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$pdf->SetFont('', 'B', $default_font_size);
				$text = $outputlangs->transnoentities("Contribution au partage des économies de charges (CG Chapitre IV)");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size);
				$text = $outputlangs->transnoentities("Montant mensuel des travaux : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size);
				$text = $outputlangs->transnoentities("Fin des travaux le : _____________");
				$text2 = $outputlangs->transnoentities("1ère échéance le : _____________");
				$text3 = $outputlangs->transnoentities("Dernière échéance le : ______________");
				$pdf->MultiCell($widthbox, 3, $text.'  '.$text2.'  '.$text3, 0, 'L');
				$posy = $pdf->getY();
				$text = $outputlangs->transnoentities("Nature des travaux : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY()+4;
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("TERMES DE PAIEMENT"), 1, 'C', 1);
				$hautcadre = 14;
				$posy = $pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', '', $default_font_size);
				$text = $outputlangs->transnoentities("Cette somme sera payable d'avance et en totalité le  ____________  de chaque mois, ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$text = $outputlangs->transnoentities("entre les mains : ");
				$text2 = $outputlangs->transnoentities("soit du bailleur, ");

				$pdf->SetTextColor(0, 0, 0);
				$pdf->Rect($posx+30, $posy+1, 2, 2, 'D', array('all' => $style));
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+34, $posy);
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->Rect($posx+60, $posy+1, 2, 2, 'D', array('all' => $style));
				$text3 = $outputlangs->transnoentities("soit de : ");
				$pdf->SetXY($posx+64, $posy);
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY()+4;
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("INDICE DE RÉFÉRENCE DES LOYERS"), 1, 'C', 1);
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size);
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$text = $outputlangs->transnoentities("Le loyer sera révisé chaque année le  : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$text = $outputlangs->transnoentities("INDICE DE RÉFÉRENCE : ___________");
				$text2 = $outputlangs->transnoentities("Trimestre : ___________");
				$text3 = $outputlangs->transnoentities("Valeur : ___________");
				$pdf->MultiCell($widthbox, 3, $text.'  '.$text2.'  '.$text3, 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY()+4;
				$hautcadre = 10;
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("DÉPÔT DE GARANTIE"), 1, 'C', 1);
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size);
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', '', $default_font_size-3);
				$text2 = $outputlangs->transnoentities("Sommes en toutes lettres -------------- ");
				$text3 = $outputlangs->transnoentities("Sommes en chiffres");
				$pdf->MultiCell($widthbox, 3, $text2.' '.$text3, 0, 'C');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY()+6;
				$hautcadre = 56;
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("HONORAIRES (Partagés entre le bailleur et le locataire)"), 1, 'C', 1);
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size);
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', 'B', $default_font_size-1);
				$pdf->SetXY($posx, $posy);
				$text = $outputlangs->transnoentities("Répartitions des honoraires: ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posx+70, $posy);
				$text2 = $outputlangs->transnoentities("Bailleur");
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->SetXY($posx+120, $posy);
				$text3 = $outputlangs->transnoentities("Locataire");
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');
				$pdf->SetFont('', 'B', $default_font_size-1);
				$pdf->SetXY($posx, $posy+4);
				$text = $outputlangs->transnoentities(" - de visite : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posx+70, $posy+4);
				$text2 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->SetXY($posx+120, $posy+4);
				$text3 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');
				$pdf->SetFont('', 'B', $default_font_size-1);
				$pdf->SetXY($posx, $posy+8);
				$text = $outputlangs->transnoentities(" - de constitution du dossier : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posx+70, $posy+8);
				$text2 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->SetXY($posx+120, $posy+8);
				$text3 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');
				$pdf->SetFont('', 'B', $default_font_size-1);
				$pdf->SetXY($posx, $posy+12);
				$text = $outputlangs->transnoentities(" - de rédaction du contrat : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posx+70, $posy+12);
				$text2 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->SetXY($posx+120, $posy+12);
				$text3 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');
				$pdf->SetFont('', 'B', $default_font_size-1);
				$pdf->SetXY($posx, $posy+16);
				$text = $outputlangs->transnoentities(" - de réalisation de l'état des lieux : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posx+70, $posy+16);
				$text2 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->SetXY($posx+120, $posy+16);
				$text3 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');
				$pdf->SetFont('', 'B', $default_font_size-1);
				$pdf->SetXY($posx, $posy+20);
				$text = $outputlangs->transnoentities("RÉMUNÉRATION TOTALE : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posx+70, $posy+20);
				$text2 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->SetXY($posx+120, $posy+20);
				$text3 = $outputlangs->transnoentities("€ TTC");
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-3);
				$pdf->SetXY($posx, $posy+24);
				$text = $outputlangs->transnoentities("Article 5-1 de la loi : « I. la rémunération des personnes mandatées pour se livrer ou prêter leur concours à l'entremise ou à la négociation d'une mise en location d'un logement, tel que défini aux articles 2 et 25-3. est à la charge exclusive du bailleur, à l'exception des honoraires liés aux prestations mentionnées aux deuxième et troisième alinéas du présent I.<br>
				« Les honoraires des personnes mandatées pour effectuer la visite du preneur, constituer son dossier et rédiger un bail sont partagés entre le bailleur et le preneur. le montant toutes taxes comprises imputé au preneur pour ces prestations ne peut excéder celui imputé au bailleur et demeure inférieur ou égal à un plafond par mètre carré de surface habitable de la chose louée fixé par voie réglementaire el révisable chaque année, dans des conditions définies par décret. Ces honoraires sont dus à la signature du bail.<br>
				« Les honoraires des personnes mandatées pour réaliser un état des lieux sont partagés entre le bailleur et le preneur. le montant toutes taxes comprises imputé au locataire pour celle prestation ne peut excéder celui imputé au bailleur et demeure inférieur ou égal à un plafond par mètre carré de surface habitable de la chose louée fixé par voie réglementaire et révisable chaque année, dans des conditions définies par décret. Ces honoraires sont dus à compter de la réalisation de la prestation».");
				$pdf->writeHTMLCell($widthrecbox, 3, $posx, $posy+24, $text, 0, 2, 0, true, 'J');

				$posY = $pdf->getY()+2;

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$pdf->SetTextColor(0, 0, 0);
				$pdf->setTopMargin($tab_top_newpage);
				if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posx=$this->marge_gauche;
				$posy = $pdf->getY();
				$hautcadre = 16;
				$pdf->SetXY($posx, $posy);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("TRAVAUX RÉCENTS"), 1, 'C', 1);
				$posy = $pdf->getY();
				$pdf->SetFont('', '', $default_font_size);
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("Travaux effectués dans le logement depuis la fin du dernier contrat ou du dernier renouvellement : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$text = $outputlangs->transnoentities("Nature :")."\n\n";
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$text = $outputlangs->transnoentities("Montant :");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY();
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("CLAUSE EXPRESSE DE TRAVAUX"), 1, 'C', 1);
				$hautcadre = 22;
				$posy = $pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', '', $default_font_size);
				$text = $outputlangs->transnoentities("Travaux entraînant une modification de loyer (C.G. Chapitre V): ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$posy = $pdf->getY();
				$text2 = $outputlangs->transnoentities("Travaux effectués par : ");
				$text3 = $outputlangs->transnoentities("Le locataire");
				$text4 = $outputlangs->transnoentities("Le bailleur");

				$pdf->SetTextColor(0, 0, 0);
				$pdf->Rect($posx+40, $posy+1, 2, 2, 'D', array('all' => $style));
				$pdf->MultiCell($widthbox, 3, $text2, 0, 'L');
				$pdf->SetXY($posx+44, $posy);
				$pdf->MultiCell($widthbox, 3, $text3, 0, 'L');
				$pdf->Rect($posx+70, $posy+1, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+74, $posy);
				$pdf->MultiCell($widthbox, 3, $text4, 0, 'L');
				$text = $outputlangs->transnoentities("Nature :")."\n\n";
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$text = $outputlangs->transnoentities("Montant :");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY();
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("CLAUSE(S) PARTICULIÈRE(S)"), 1, 'C', 1);
				$hautcadre = 22;
				$posy = $pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->MultiCell($widthbox, 3, '', 0, 'L');
				$posy = $pdf->getY()+16;

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("CLÉS REMISES)"), 1, 'C', 1);

				$hautcadre = 8;
				$posy = $pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->SetFont('', '', $default_font_size);
				$text = $outputlangs->transnoentities("Nombre de clés remises au locataire :  ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY()+4;
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("DOCUMENTS ANNEXÉS"), 1, 'C', 1);
				$hautcadre = 34;
				$posy = $pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));
				$pdf->MultiCell($widthbox, 3, '', 0, 'L');
				$pdf->SetFont('', '', $default_font_size);
				$text = $outputlangs->transnoentities("État des lieux établi lors de la remise des clés au locataire (contradictoire ou par huissier). ");
				$pdf->SetTextColor(0, 0, 0);
				$pdf->Rect($posx+5, $posy+1, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+10, $posy);
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->Rect($posx+5, $posy+7, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+10, $posy+6);
				$text = $outputlangs->transnoentities("Liste des réparations locatives fixées par décret n° 87-712 du 26 août 1987. ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->Rect($posx+5, $posy+13, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+10, $posy+12);
				$text = $outputlangs->transnoentities("Liste des charges récupérables fixées par décret n° 87-712 du 26 août 1987. ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->Rect($posx+5, $posy+19, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+10, $posy+18);
				$text = $outputlangs->transnoentities("Éléments constitutifs du dossier de diagnostic technique (art.3.1 de la loi n°89-462). ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->Rect($posx+5, $posy+25, 2, 2, 'D', array('all' => $style));
				$pdf->SetXY($posx+10, $posy+24);
				$text = $outputlangs->transnoentities("Acte de caution solidaire, le cas échéant. ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+5, $posy+30);
				$text = $outputlangs->transnoentities("Nom de la caution : ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');

				$pdf->SetFillColor(255, 255, 127);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$posy = $pdf->getY()+4;
				$pdf->SetXY($posx, $posy+2);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset("SIGNATURES DES PARTIES"), 1, 'C', 1);
				$hautcadre = 80;
				$posy = $pdf->getY();
				$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre, 'D', array('all' => $style));

				$pdf->SetFont('', 'U', $default_font_size);
				$text = $outputlangs->transnoentities("RAYÉS NULS ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+15, $posy+5);
				$pdf->SetFont('', '', $default_font_size-1);
				$text = $outputlangs->transnoentities("Mots");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+15, $posy+10);
				$text = $outputlangs->transnoentities("Lignes");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+45, $posy+2);
				$text = $outputlangs->transnoentities("Fait et signé à ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+45, $posy+6);
				$text = $outputlangs->transnoentities("le ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+45, $posy+10);
				$text = $outputlangs->transnoentities("en");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+90, $posy+10);
				$text = $outputlangs->transnoentities("originaux dont un remis à chacune des parties qui le reconnait ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', 'B', $default_font_size+2);
				$pdf->SetXY($posx+5, $posy+16);
				$text = $outputlangs->transnoentities("LE BAILLEUR OU\n SON MANDATAIRE ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+70, $posy+16);
				$text = $outputlangs->transnoentities("LE(S) LOCATAIRE(S) ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+140, $posy+16);
				$text = $outputlangs->transnoentities("LA CAUTION");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posx+5, $posy+26);
				$text = $outputlangs->transnoentities("Signature précédée de la mention\n manuscrite « lu et approuvé » ");
				$pdf->MultiCell($widthbox, 3, $text, 0, 'L');
				$pdf->SetXY($posx+65, $posy+26);
				$text = $outputlangs->transnoentities("Signature précédée de la mention manuscrite « lu et approuvé » ");
				$pdf->MultiCell($widthbox/3, 3, $text, 0, 'L');
				$pdf->SetXY($posx+128, $posy+22);
				$text = $outputlangs->transnoentities("Signature précédée de la mention manuscrite « lu et approuvé ». Reconnais avoir reçu un exemplaire pour lequel je me porte caution par acte séparé et annexé");
				$pdf->MultiCell($widthbox/3, 3, $text, 0, 'L');

				$posY = $pdf->getY()+50;

				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('', '', $default_font_size-1);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('Paraphes :'), 0, 'R');

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();
			}
			$this->db->free($resql);

			$pdf->Close();

			$pdf->Output($file, 'F');
			if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

			return 1; // Pas d'erreur
		} else {
			$this->error=$outputlangs->transnoentities("ErrorCanNotCreateDir", $dir);
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

		foreach ($this->cols as $colKey => $colDef) {
			if (!$this->getColumnStatus($colKey)) continue;

			// get title label
			$colDef['title']['label'] = !empty($colDef['title']['label'])?$colDef['title']['label']:$outputlangs->transnoentities($colDef['title']['textkey']);

			// Add column separator
			if (!empty($colDef['border-left'])) {
				$pdf->line($colDef['xStartPos'], $tab_top, $colDef['xStartPos'], $tab_top + $tab_height);
			}

			if (empty($hidetop)) {
				$pdf->SetXY($colDef['xStartPos'] + $colDef['title']['padding'][3], $tab_top-8 + $colDef['title']['padding'][0]);

				$textWidth = $colDef['width'] - $colDef['title']['padding'][3] -$colDef['title']['padding'][1];
				$pdf->MultiCell($textWidth, $tab_height+8, $colDef['title']['label'], '', $colDef['title']['align']);
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
		if ($object->statut==ImmoReceipt::STATUS_DRAFT && (! empty($conf->global->FACTURE_DRAFT_WATERMARK)) ) {
			  pdf_watermark($pdf, $outputlangs, $this->page_hauteur, $this->page_largeur, 'mm', $conf->global->FACTURE_DRAFT_WATERMARK);
		}

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$w = 110;

		$posy=$this->marge_haute;
		$posx=$this->page_largeur-$this->marge_droite-$w;

		$pdf->SetXY($this->marge_gauche, $posy);

		// Logo
		if (empty($conf->global->PDF_DISABLE_MYCOMPANY_LOGO)) {
			$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
			if ($this->emetteur->logo) {
				if (is_readable($logo)) {
					$height=pdf_getHeightForLogo($logo);
					$pdf->Image($logo, $this->marge_gauche, $posy, 0, $height);	// width=0 (auto)
				} else {
					$pdf->SetTextColor(200, 0, 0);
					$pdf->SetFont('', 'B', $default_font_size - 2);
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
				}
			} else {
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

		if ($object->ref_client) {
			$posy+=4;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("RefCustomer")." : " . $outputlangs->convToOutputCharset($object->ref_client), '', 'R');
		}
		$posy=$pdf->getY()+2;

		if ($object->thirdparty->code_client) {
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
		if ($current_y < $pdf->getY()) {
			$top_shift = $pdf->getY() - $current_y;
		}

		if ($showaddress) {
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
			$pdf->SetXY($this->marge_gauche, $posy);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 3);
			$pdf->SetXY($this->marge_gauche+6, $posy);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('HABITATION PRINCIPALE'), 0, 'L');

			// PROFESSIONNEL ET HABITATION PRINCIPALE
			$pdf->rect($this->marge_gauche+80, $posy, 4, 4);
			$pdf->SetXY($this->marge_gauche+80, $posy);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size + 3);
			$pdf->SetXY($this->marge_gauche, $posy);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset('PROFESSIONNEL ET HABITATION PRINCIPALE'), 0, 'R');

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
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		} elseif (empty($reshook)) {
			$this->cols = array_replace($this->cols, $hookmanager->resArray); // array_replace is used to preserve keys
		} else {
			$this->cols = $hookmanager->resArray;
		}
	}
}
