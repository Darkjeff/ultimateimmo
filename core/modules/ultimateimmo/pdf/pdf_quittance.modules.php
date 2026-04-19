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
 * \file ultimateimmo/core/modules/ultimateimmo/pdf/pdf_quitance.module.php
 * \ingroup ultimateimmo
 * \brief PDF for ultimateimmo
 */

dol_include_once('/ultimateimmo/core/modules/ultimateimmo/modules_ultimateimmo.php');
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

class pdf_quittance extends ModelePDFUltimateimmo
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
		$this->name = 'quittance';
		$this->description = $langs->trans('Quittance');
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
			$dir = $conf->ultimateimmo->dir_output . "/receipt/" . $objectref;
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
			$pdf->SetSubject($outputlangs->transnoentities("Quittance"));
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

			$paiement = new ImmoPayment($this->db);
			$result = $paiement->fetch_by_loyer($object->rowid);

			if (! empty($object->id))
			{
				// New page
				$pdf->AddPage();
				$pagenb ++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 9);
				$pdf->MultiCell(0, 3, '', 0, 'J');
				$pdf->SetTextColor(0, 0, 0);
				
				$hautcadre=!empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 38 : 40;
				$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
				$posY = $this->marge_haute + $hautcadre +50;
				$posX = $this->marge_gauche;			

				
				$text .= "\n";
				$text .= 'Fait à ' . $owner->town . ' le ' . dol_print_date(dol_now(), 'daytext') . "\n";				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 10);
				$pdf->SetXY($posX, $posY-12);
				$pdf->MultiCell($widthbox, 0, $outputlangs->convToOutputCharset($text), 0, 'L');
				
				// Bloc Quittance de loyer
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetXY($posX, $posY);
				if ($object->paye != 1 ) {
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Appel de loyer'), 1, 'C');
				} else {
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Quittance de loyer'), 1, 'C');
				}

				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = $object->label;
				$pdf->MultiCell($widthbox, 3, $period, 1, 'C', 0);

				/*
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'ID', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$numquittance = 'Quittance n°: ' . 'ILQ' . $object->id;
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($numquittance), 1, 'ID');
				*/

				$posY = $pdf->getY();
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$pdf->SetXY($posX, $posY);

				$amountalreadypaid = 0;
				if ($object->getSommePaiement())
				{
					$amountalreadypaid = price($object->getSommePaiement(), 0, $outputlangs, 1, -1, -1, $conf->currency);
				}	

				$text = 'Reçu de ' . $renter->civilite . '' .$renter->firstname. ' '.$renter->lastname. ' la somme de ' . $amountalreadypaid . "\n";
				;
//var_dump($paiement);exit;
				$dtpaiement = $paiement->date_payment;

				if (empty($dtpaiement)) {
					$dtpaiement = $object->echeance;
				}
				$text .= 'le ' . dol_print_date($dtpaiement, 'day') . ' pour loyer et accessoires des locaux sis à : ' . $property->address . ' en paiement du terme du ' . dol_print_date($object->date_start, 'daytext') . ' au ' . dol_print_date($object->date_end, 'daytext') . "\n";

				$pdf->MultiCell($widthbox, 0, $outputlangs->convToOutputCharset($text), 1, 'L');

				$posY = $pdf->getY();
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Détail'), 1, 'C');

				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$text = '<table>';
				$text .= '<tr>';
				$text .= '<td colspan="2">';

				$text .= ' - Loyer nu : ' . price($object->rentamount) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				if ($object->vat > 0) {
				$text .= ' - TVA : ' . price($object->vat) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				}
				$text .= ' - Charges / Provisions de Charges : ' . price($object->chargesamount) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				$text .= ' - Montant total du terme : ' . price($object->total_amount) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				$text .= '</td>';
				$text .= '</tr>';

				$sql = "SELECT p.rowid, p.fk_receipt, p.date_payment as dp, p.amount, p.note_public as type, il.total_amount ";
				$sql .= " FROM " .MAIN_DB_PREFIX."ultimateimmo_immopayment as p";
				$sql .= ", " .MAIN_DB_PREFIX."ultimateimmo_immoreceipt as il ";
				$sql .= " WHERE p.fk_receipt = ".$object->id;
				//$sql .= " AND p.fk_receipt = il.rowid";
				$sql .= " GROUP by p.rowid ";
				$sql .= " ORDER BY dp DESC";

				dol_syslog(get_class($this) . ':: Paiement', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql) 
				{
					$num = $this->db->num_rows($resql);
					$i = 0;
					$total = 0;
					$text .= '<tr>';
					$text .= '<td align="left">' . $langs->trans("DatePayment") . '</td>';
					//$text .= '<td align="left">' . $langs->trans("Commentaire") . '</td>';
					$text .= '<td align="right">' . $langs->trans("Amount") . '</td>';
					$text .= "</tr>";

					while ( $i < $num ) 
					{
						$objp = $this->db->fetch_object($resql);

						$text .= '<tr>';

						$text .= '<td>' . dol_print_date($this->db->jdate($objp->dp), 'day') . "</td>";
						//$text .= '<td>' . $objp->type . "</td>";
						$text .= '<td align="right">' . $objp->type .' '. price($objp->amount) . ' ' . $langs->trans("Currency" . $conf->currency) . "</td>";
						$text .= "</tr>";
						$totalpaye += $objp->amount;
						$i ++;
					}

					if ($object->status == 0)
					{
						$text .= "<br><tr><td align=\"left\">" . $langs->trans("AlreadyPaid") . " :</td><td align=\"right\">" . price($totalpaye) . " " . $langs->trans("Currency" . $conf->currency) . "</td></tr>";
						$text .= "<tr><td align=\"left\">" . $langs->trans("AmountExpected") . " :</td><td align=\"right\">" . price($object->total_amount) . " " . $langs->trans("Currency" . $conf->currency) . "</td></tr>";

						$resteapayer = $object->total_amount - $totalpaye;

						$text .= "<tr><td align=\"left\">" . $langs->trans("RemainderToPay") . " :</td>";
						$text .= "<td align=\"right\">" . price($resteapayer, 2) . " " . $langs->trans("Currency" . $conf->currency) . "</td></tr>";
					}

					$this->db->free($resql);
				}
				$text .= "</table>";
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$pdf->writeHTMLCell($widthbox, 0, $posX, $posY, dol_htmlentitiesbr($text), 1, 1);

				// Tableau Loyer et solde
				$sql = "SELECT il.label, il.balance";
				$sql .= " FROM " .MAIN_DB_PREFIX."ultimateimmo_immoreceipt as il";
				$sql .= " WHERE il.balance<>0 AND paye=0 AND date_start<'" . $this->db->idate($object->date_start) . "'";
				$sql .= " AND fk_property=" . $object->fk_property . " AND fk_renter=" . $object->fk_renter;
				$sql .= " ORDER BY echeance ASC";

				dol_syslog(get_class($this) . ':: loyerAnterieur sql=' . $sql, LOG_DEBUG);
				$resql = $this->db->query($sql);

				if ($resql)
				{
					$num = $this->db->num_rows($resql);

					if ($num > 0) {

						// $pdf->addPage();
						$posY = $pdf->getY();
						$pdf->SetXY($posX, $posY);

						// Bloc Solde Anterieur
						// $posX = $this->marge_gauche;
						// $posY = $this->marge_haute;

						$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;

						$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
						$pdf->SetXY($posX, $posY);
						$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Rappel Solde Anterieur'), 1, 'C');

						$text = '<table>';

						// print $sql;
						dol_syslog(get_class($this) . ':: loyerAnterieur sql=' . $sql, LOG_DEBUG);
						$resql = $this->db->query($sql);

						$i = 0;
						$total = 0;
						while ( $i < $num )
						{
							$objp = $this->db->fetch_object($resql);

							$text .= '<tr class="oddeven">';
							$text .= '<td>' . $objp->name . "</td>";
							$text .= "<td align=\"right\">" . price($objp->balance) . ' ' . $langs->trans("Currency" . $conf->currency) . "</td>";
							$text .= "</tr>";

							$i ++;
						}

						$this->db->free($resql);

						$text .= "</table>";

						$posY = $pdf->getY();

						$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
						$pdf->writeHTMLCell($widthbox, 0, $posX, $posY, dol_htmlentitiesbr($text), 1, 1);
					}
				}

				// Bloc total somme due

				// Tableau total somme due
				$sql = "SELECT SUM(il.balance) as total";
				$sql .= " FROM " .MAIN_DB_PREFIX."ultimateimmo_immoreceipt as il";
				$sql .= " WHERE il.balance<>0 AND paye=0 AND date_start<='" . $this->db->idate($object->date_start) . "'";
				$sql .= " AND fk_property=".$object->fk_property." AND fk_renter=".$object->fk_renter;
				$sql .= " GROUP BY fk_property,fk_renter";

				// print $sql;
				dol_syslog(get_class($this) . ':: total somme due', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql)
				{
					$num = $this->db->num_rows($resql);

					if ($num > 0) {

						$objp = $this->db->fetch_object($resql);

						$posX = $this->marge_gauche;
						$posY = $pdf->getY();
						$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;

						$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
						$pdf->SetXY($posX, $posY);

						if ($objp->total > 0) {
							$title = 'Total somme due';
						} else {
							$title = 'Total somme à rembourser';
						}

						$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($title), 1, 'C');

						$text = '<table>';

						$i = 0;
						$total = 0;

						$text .= "<td align=\"right\">" . price($objp->total) . ' ' . $langs->trans("Currency" . $conf->currency) . "</td>";

						$this->db->free($resql);

						$text .= "</table>";

						$posY = $pdf->getY();

						$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
						$pdf->writeHTMLCell($widthbox, 0, $posX, $posY, dol_htmlentitiesbr($text), 1);
					}
				}

				// Bloc total Charge

				// Tableau total Charge
				/*$sql = " SELECT YEAR(echeance) as annee, SUM(charges) as accompte,";
				$sql .= " SUM(charge_ex) as charge,";
				$sql .= " GROUP_CONCAT(CommCharge SEPARATOR '$') as CommCharge";
				$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer";
				$sql .= " WHERE local_id=" . $object->local_id . " AND locataire_id=" . $object->locataire_id;
				$sql .= " GROUP BY YEAR(echeance)";

				// print $sql;
				dol_syslog(get_class($this) . ':: total charge sql=' . $sql, LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql) {
					$num = $this->db->num_rows($resql);

					if ($num > 0) {

						$pdf->addPage();
						$posX = $this->marge_gauche;
						$posY = $this->marge_haute;

						$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;

						$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
						$pdf->SetXY($posX, $posY);

						$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Récapitulatif charges locatives'), 1, 'C');

						$text = '<table>';

						$text .= '<tr>';
						$text .= "<th align=\"left\">Année</th>";
						$text .= "<th align=\"left\">Charges Locatives</th>";
						$text .= "<th align=\"left\">Paiement</th>";
						$text .= "<th align=\"left\">Reste a payer</th>";
						$text .= "<th align=\"left\">Commentaire</th>";

						$text .= "</tr>";

						while ( $objp = $this->db->fetch_object($resql) ) {

							$text .= '<tr>';
							$text .= "<td align=\"left\">" . $objp->annee . "</td>";
							$text .= "<td align=\"left\">" . price($objp->charge) . ' ' . $langs->trans("Currency" . $conf->currency) . "</td>";
							$text .= "<td align=\"left\">" . price($objp->accompte) . ' ' . $langs->trans("Currency" . $conf->currency) . "</td>";
							$text .= "<td align=\"left\">" . price($objp->charge - $objp->accompte) . ' ' . $langs->trans("Currency" . $conf->currency) . "</td>";

							if (! empty($objp->CommCharge)) {
								$comm_array = explode('$', $objp->CommCharge);
							}
							$commentaire_charge = array ();
							if (is_array($comm_array) && count($comm_array) > 0) {
								foreach ( $comm_array as $txt_com ) {
									if (! empty($txt_com)) {
										$commentaire_charge[] = $txt_com;
									}
								}
							}

							$text .= "<td align=\"left\">" . implode('<br>', $commentaire_charge) . "</td>";

							$text .= "</tr>";
						}
						$this->db->free($resql);

						$text .= "</table>";

						$posY = $pdf->getY();

						$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
						$pdf->writeHTMLCell($widthbox, 0, $posX, $posY, dol_htmlentitiesbr($text), 1);
					}
				}*/
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
		if($object->status==ImmoReceipt::STATUS_DRAFT && (! empty($conf->global->FACTURE_DRAFT_WATERMARK)) )
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
		if ($object->status == ImmoReceipt::STATUS_DRAFT)
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
