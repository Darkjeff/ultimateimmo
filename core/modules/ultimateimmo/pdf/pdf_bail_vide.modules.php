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
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/class/immoowner_type.class.php');
dol_include_once('/ultimateimmo/class/immopayment.class.php');
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
			
			$ownertype = new ImmoOwner_Type($this->db);
			$result = $ownertype->fetch($object->fk_owner_type);

			$property = new ImmoProperty($this->db);
			$result = $property->fetch($object->fk_property);

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
				$posY = $this->marge_haute + $hautcadre +50;
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
				$text .= "\n";
				$text .= 'Fait à ' . $owner->town . ' le ' . dol_print_date(dol_now(), 'daytext') . "\n";				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 10);
				$pdf->SetXY($posX, $posY-12);
				$pdf->MultiCell($widthbox, 0, $outputlangs->convToOutputCharset($text), 0, 'L');
				
				// Le contrat type de location ou de colocation contient les éléments suivants :
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('I. DESIGNATION DES PARTIES'), 1, 'C');

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');

				$text = $outputlangs->transnoentities(" Le présent contrat est conclu entre les soussignés :\n\n");
				// [nom et prénom, ou dénomination du bailleur/ domicile ou siège social/ qualité du bailleur (personne physique, personne morale (1))/ adresse électronique (facultatif)] (2)
				$text .= $outputlangs->convToOutputCharset($owner->getFullName($outputlangs))."\n";
				$carac_emetteur .= $owner->address . "\n";
				$carac_emetteur .= $owner->zip . ' ' . $owner->town."\n";
				$text .=  $carac_emetteur."\n"; 
				$text .= 'En tant que '.$objp->label.' désigné (s) ci-après le bailleur'."\n\n" ;	
				
				/*$text .= $outputlangs->transnoentities("
- le cas échéant, représenté par le mandataire :
- [nom ou raison sociale et adresse du mandataire ainsi que l'activité exercée] ;
- le cas échéant, [numéro et lieu de délivrance de la carte professionnelle/ nom et adresse du garant] (3).\n\n");*/
				//- [nom et prénom du ou des locataires ou, en cas de colocation, des colocataires, adresse électronique (facultatif)]
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
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('II. OBJET DU CONTRAT'), 1, 'C');

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$sql = "SELECT ip.rowid, ip.fk_property_type, pt.rowid, pt.ref, pt.label ";
				$sql .= " FROM " .MAIN_DB_PREFIX."ultimateimmo_immoproperty as ip";
				$sql .= " JOIN " .MAIN_DB_PREFIX."ultimateimmo_immoproperty_type as pt ";
				$sql .= " WHERE pt.rowid = ip.fk_property_type";

				dol_syslog(get_class($this) . ':: pdf_bail_vide', LOG_DEBUG);
				$resql = $this->db->query($sql);
				
				if ($resql) 
				{
					$num = $this->db->num_rows($resql);
					while ( $j < $num ) 
					{
						$objproperty = $this->db->fetch_object($resql);
						$j++;
					}
				}
				//var_dump($property);exit;
				$text = $outputlangs->transnoentities("Le présent contrat a pour objet la location d'un logement ainsi déterminé :
A. Consistance du logement
- localisation du logement : ").$property->address.' '.$outputlangs->transnoentities("/ bâtiment : ").$property->building.' '.$outputlangs->transnoentities("/escalier : ").$property->staircase.' '. $outputlangs->transnoentities("/étage : ").$property->numfloor.' '. $outputlangs->transnoentities("/porte : ").$property->numdoor."\n" ;
				$text .= $property->zip.' '.$property->town.' '.$property->country."\n";
				$text .= $outputlangs->transnoentities("- type d'habitat : ").$objproperty->label."\n";
$text .= $outputlangs->transnoentities("- régime juridique de l'immeuble : ").$property->juridique."\n" ;;
$text .= $outputlangs->transnoentities("- période de construction : ").$property->datep."\n" ;
$text .= $outputlangs->transnoentities("- surface habitable : [...] m2 ;
- nombre de pièces principales : [...] ;
- le cas échéant, Autres parties du logement : [exemples : grenier, comble aménagé ou non, terrasse, balcon, loggia,
jardin etc.] ;
- le cas échéant, Eléments d'équipements du logement : [exemples : cuisine équipée, détail des installations sanitaires
etc.] ;
- modalité de production de chauffage : [individuel ou collectif] (4) ;
- modalité de production d'eau chaude sanitaire : [individuelle ou collective] (5).
B. Destination des locaux : [usage d'habitation ou usage mixte professionnel et d'habitation]
C. Le cas échéant, Désignation des locaux et équipements accessoires de l'immeuble à usage privatif du
locataire : [exemples : cave, parking, garage etc.]
D. Le cas échéant, Enumération des locaux, parties, équipements et accessoires de l'immeuble à usage
commun : [Garage à vélo, ascenseur, espaces verts, aires et équipements de jeux, laverie, local poubelle,
gardiennage, autres prestations et services collectifs etc.]
E. Le cas échéant, Equipement d'accès aux technologies de l'information et de la communication : [exemples :
modalités de réception de la télévision dans l'immeuble, modalités de raccordement internet etc.]");
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
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('III. DATE DE PRISE D\'EFFET ET DUREE DU CONTRAT'), 1, 'C');

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities(" La durée du contrat et sa date de prise d'effet sont ainsi définies :
A. Date de prise d'effet du contrat : [...]
B. Durée du contrat : [durée minimale de trois ou six ans selon la qualité du bailleur] ou [durée réduite et minimale d'un an lorsqu'un événement précis (6) le justifie]
C. Le cas échéant, Evénement et raison justifiant la durée réduite du contrat de location : [...]
En l'absence de proposition de renouvellement du contrat, celui-ci est, à son terme, reconduit tacitement pour 3 ou 6 ans et dans les mêmes conditions. Le locataire peut mettre fin au bail à tout moment, après avoir donné congé. Le bailleur, quant à lui, peut mettre fin au bail à son échéance et après avoir donné congé, soit pour reprendre le logement en vue de l'occuper lui-même ou une personne de sa famille, soit pour le vendre, soit pour un motif sérieux et légitime.");
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
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('IV. CONDITIONS FINANCIERES'), 1, 'C');

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$period = $outputlangs->transnoentities('');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				$text = $outputlangs->transnoentities(" Les parties conviennent des conditions financières suivantes :
A. Loyer
1° Fixation du loyer initial :
a) Montant du loyer mensuel : [...] (7) ;
b) Le cas échant, Modalités particulières de fixation initiale du loyer applicables dans certaines zones tendues (8) :
- le loyer du logement objet du présent contrat est soumis au décret fixant annuellement le montant maximum d'évolution des loyers à la relocation : [Oui/ Non].
- le loyer du logement objet du présent contrat est soumis au loyer de référence majoré fixé par arrêté préfectoral : [Oui/Non].
- montant du loyer de référence : [...] €/ m2/ Montant du loyer de référence majoré : [...] €/ m2 ;
- le cas échéant Complément de loyer : [si un complément de loyer est prévu, indiquer le montant du loyer de base, nécessairement égal au loyer de référence majoré, le montant du complément de loyer et les caractéristiques du logement justifiant le complément de loyer].
c) Le cas échéant, informations relatives au loyer du dernier locataire : [montant du dernier loyer acquitté par le précédent locataire, date de versement et date de la dernière révision du loyer] (9).
2° Le cas échéant, Modalités de révision :
a) Date de révision : [...].
b) Date ou trimestre de référence de l'IRL : [...].
B. Charges récupérables
1. Modalité de règlement des charges récupérables : [Provisions sur charges avec régularisation annuelle ou paiement périodique des charges sans provision/ En cas de colocation, les parties peuvent convenir de la récupération des charges par le bailleur sous la forme d'un forfait].
2. Le cas échéant, Montant des provisions sur charges ou, en cas de colocation, du forfait de charge : [...].
3. Le cas échéant, En cas de colocation et si les parties en conviennent, modalités de révision du forfait de charges :
[...] (10).
C. Le cas échéant, contribution pour le partage des économies de charges : (11)
1. Montant et durée de la participation du locataire restant à courir au jour de la signature du contrat : [...].
2. Eléments propres à justifier les travaux réalisés donnant lieu à cette contribution : [...].
D. Le cas échéant, En cas de colocation souscription par le bailleur d'une assurance pour le compte des colocataires (12) : [Oui/ Non]");
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
		$title=$outputlangs->transnoentities("Bail");
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
			/*if ($owner->country_id)
			{
				$tmparray=$owner->getCountry($owner->country_id,'all');
				$owner->country_code=$tmparray['code'];
				$owner->country=$tmparray['label'];
			}*/
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
