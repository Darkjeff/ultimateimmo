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
 * \file immobilier/core/modules/immobilier/pdf/pdf_quitance.module.php
 * \ingroup immobilier
 * \brief PDF for immobilier
 */
 
dol_include_once('/immobilier/core/modules/immobilier/modules_immobilier.php');
dol_include_once('/immobilier/class/immoreceipt.class.php');
dol_include_once('/immobilier/class/immorenter.class.php');
dol_include_once('/immobilier/class/immoproperty.class.php');
dol_include_once('/immobilier/class/immorent.class.php');
dol_include_once('/immobilier/class/immoowner.class.php');
dol_include_once('/immobilier/class/immopayment.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php');

class pdf_quittance extends ModelePDFImmobilier 
{
	var $emetteur; // Objet societe qui emet
	
	/**
	 * \brief Constructor
	 * \param db Database handler
	 */
	function __construct($db) 
	{
		global $conf, $langs, $mysoc;
		
		$langs->load("immobilier@immobilier");
		
		$this->db = $db;
		$this->name = 'quittance';
		$this->description = $langs->trans('Quittance');
		
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
	 * \param agf Objet document a generer (ou id si ancienne methode)
	 * outputlangs Lang object for output language
	 * file Name of file to generate
	 * \return int 1=ok, 0=ko
	 */
	function write_file(&$object, $outputlangs, $file, $socid, $courrier) {
		global $user, $langs, $conf, $mysoc;
		
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		
		if (! is_object($outputlangs))
			$outputlangs = $langs;
		
		if (! is_object($receipt)) {
			$id = $receipt;
			$receipt = new Immoreceipt($this->db);
			$ret = $receipt->fetch($id);
		}
		
		// dol_syslog ( "pdf_quittance::debug loyer=" . var_export ( $receipt, true ) );
		
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
			if (create_exdir($dir) < 0) {
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}
		
		if (file_exists($dir)) {
			
			$pdf = pdf_getInstance($this->format, $this->unit, $this->orientation);
			
			if (class_exists('TCPDF')) {
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}
			
			$pdf->Open();
			$pagenb = 0;
			
			$pdf->SetTitle($outputlangs->convToOutputCharset($receipt->nom));
			$pdf->SetSubject($outputlangs->transnoentities("Quittance"));
			$pdf->SetCreator("Dolibarr " . DOL_VERSION . ' (Immobilier module)');
			$pdf->SetAuthor($outputlangs->convToOutputCharset($user->fullname));
			$pdf->SetKeyWords($outputlangs->convToOutputCharset($receipt->nom) . " " . $outputlangs->transnoentities("Document"));
			if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION)
				$pdf->SetCompression(false);
			
			$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right
			$pdf->SetAutoPageBreak(1, 0);
			
			// On recupere les infos societe
			$renter = new ImmoRenter($this->db);
			$result = $renter->fetch($receipt->fk_renter);
			
			$owner = new ImmoOwner($this->db);
			$result = $owner->fetch($receipt->fk_owner);
			
			$property = new ImmoProperty($this->db);
			$result = $property->fetch($receipt->fk_property);
			
			//$paiement = new Immopayment($this->db);
			//$result = $paiement->fetch_by_loyer($receipt->id);
			
			if (! empty($receipt->id)) {
				// New page
				$pdf->AddPage();
				$pagenb ++;
				$this->_pagehead($pdf, $agf, 1, $outputlangs);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 9);
				$pdf->MultiCell(0, 3, '', 0, 'J');
				$pdf->SetTextColor(0, 0, 0);
				
				$posY = $this->marge_haute;
				$posX = $this->marge_gauche;
				
				// Bloc Owner
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 15);
				$pdf->SetXY($posX, $posY + 3);
				$pdf->MultiCell(80, 3, $outputlangs->convToOutputCharset('Bailleur'), 1, 'C');
				
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$this->str = $owner->getFullName($outputlangs) . "\n";
				$this->str .= $owner->address . "\n";
				$this->str .= $owner->zip . ' ' . $owner->town;
				$this->str .= ' - ' . $owner->country . "\n\n";
				if ($owner->phone) {
					$this->str .= $outputlangs->transnoentities('Téléphone') . ' ' . $owner->phone . "\n";
				}
				if ($owner->fax) {
					$this->str .= $outputlangs->transnoentities('Fax') . ' ' . $owner->fax . "\n";
				}
				if ($owner->email) {
					$this->str .= $outputlangs->transnoentities('EMail') . ' ' . $owner->email . "\n";
				}
				if ($owner->url) {
					$this->str .= $outputlangs->transnoentities('Url') . ' ' . $owner->url . "\n";
				}
				
				$pdf->MultiCell(80, 20, $outputlangs->convToOutputCharset($this->str), 1, 'L');
				
				// Bloc Locataire
				$posX = $this->page_largeur - $this->marge_droite - 80;
				$posY = $pdf->getY() - 20;
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 15);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell(80, 3, $outputlangs->convToOutputCharset('Locataire Destinataire'), 1, 'C');
				
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$this->str = $renter->civilite . ' ' .$renter->nom. ' '.$renter->prenom. "\n";
				$this->str .= $property->name . "\n";
				$this->str .= $property->address . "\n";
				$this->str .= $property->zip . ' ' . $property->town;
				$pdf->MultiCell(80, 20, $outputlangs->convToOutputCharset($this->str), 1, 'L');
				
				$text .= "\n";
				$text .= 'Fait à ' . $owner->town . ' le ' . dol_print_date(dol_now(), 'daytext') . "\n";
				
				$pdf->MultiCell($widthbox, 0, $outputlangs->convToOutputCharset($text), 0, 'L');
				
				// Bloc Quittance de loyer
				$posX = $this->marge_gauche;
				$posY = $pdf->getY() + 10;
				$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetXY($posX, $posY);
				if ($receipt->paye != 1 ) {
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Appel de loyer'), 1, 'C');
				} else {
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Quittance de loyer'), 1, 'C');
				}
				
				
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
				
				$period = 'Loyer ' . dol_print_date($receipt->echeance, '%b %Y');
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($period), 1, 'C');
				
				/*
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'ID', 13);
				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);
	
				$numquittance = 'Quittance n°: ' . 'ILQ' . $receipt->id;
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset($numquittance), 1, 'ID');
				*/

				$posY = $pdf->getY();
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
				$pdf->SetXY($posX, $posY);

				$montantpay = 0;
				if (! empty($receipt->paiepartiel)) {
					$montantpay = $receipt->paiepartiel;
				}
				$text = 'Reçu de ' . $renter->civilite . '' .$renter->prenom. ' '.$renter->nom. ' la somme de ' . price($montantpay) . '€' . "\n";
				;

				$dtpaiement = $paiement->date_paiement;
				if (empty($dtpaiement)) {
					$dtpaiement = $receipt->echeance;
				}
				$text .= 'le ' . dol_print_date($dtpaiement, 'daytext') . ' pour loyer et accessoires des locaux sis à : ' . $property->address . ' en paiement du terme du ' . dol_print_date($receipt->date_start, 'daytext') . ' au ' . dol_print_date($receipt->date_end, 'daytext') . "\n";

				/*
				$pdf->MultiCell($widthbox, 0, $outputlangs->convToOutputCharset($text), 1, 'L');

				$posY = $pdf->getY();
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 15);
				$pdf->SetXY($posX, $posY);
				$pdf->MultiCell($widthbox, 3, $outputlangs->convToOutputCharset('Détail'), 1, 'C');
				*/

				$posY = $pdf->getY();
				$pdf->SetXY($posX, $posY);

				$text = '<table>';
				$text .= '<tr>';
				$text .= '<td colspan="2">';

				$text .= ' - Loyer nu : ' . price($receipt->rent) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				if ($receipt->vat > 0) {
				$text .= ' - TVA : ' . price($receipt->vat) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				} 
				$text .= ' - Charges / Provisions de Charges : ' . price($receipt->charges) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				$text .= ' - Montant total du terme : ' . price($receipt->amount_total) . ' ' . $langs->trans("Currency" . $conf->currency) . "<BR>";
				$text .= '</td>';
				$text .= '</tr>';
				
				$sql = "SELECT p.rowid, p.fk_receipt, date_payment as dp, p.amount, p.comment as type, il.amount_total ";
				$sql .= " FROM " . MAIN_DB_PREFIX . "immobilier_immopayment as p";
				$sql .= ", " . MAIN_DB_PREFIX . "immobilier_immoreceipt as il ";
				$sql .= " WHERE p.fk_receipt = " . $receipt->id;
				$sql .= " AND p.fk_receipt = il.rowid";
				$sql .= " ORDER BY dp DESC";
				
				// print $sql;
				dol_syslog(get_class($this) . ':: Paiement', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql) {
					$num = $this->db->num_rows($resql);
					$i = 0;
					$total = 0;
					$text .= '<tr>';
					$text .= '<td align="left">' . $langs->trans("DatePayment") . '</td>';
					//$text .= '<td align="left">' . $langs->trans("Commentaire") . '</td>';
					$text .= '<td align="right">' . $langs->trans("Amount") . '</td>';
					$text .= "</tr>";
					$var = True;
					while ( $i < $num ) {
						$objp = $this->db->fetch_object($resql);
						
						$text .= '<tr>';
						
						$text .= '<td>' . dol_print_date($this->db->jdate($objp->dp), 'day') . "</td>";
						//$text .= '<td>' . $objp->type . "</td>";
						$text .= '<td align="right">' . $objp->type .' '. price($objp->amount) . ' ' . $langs->trans("Currency" . $conf->currency) . "</td>";
						$text .= "</tr>";
						$totalpaye += $objp->amount;
						$i ++;
					}
					
					if ($receipt->paye == 0) {
						$text .= "<br><tr><td align=\"left\">" . $langs->trans("AlreadyPaid") . " :</td><td align=\"right\">" . price($totalpaye) . " " . $langs->trans("Currency" . $conf->currency) . "</td></tr>";
						$text .= "<tr><td align=\"left\">" . $langs->trans("AmountExpected") . " :</td><td align=\"right\">" . price($receipt->amount_total) . " " . $langs->trans("Currency" . $conf->currency) . "</td></tr>";
						
						$resteapayer = $receipt->amount_total - $totalpaye;
						
						$text .= "<tr><td align=\"left\">" . $langs->trans("RemainderToPay") . " :</td>";
						$text .= "<td align=\"right\">" . price($resteapayer, 2) . " " . $langs->trans("Currency" . $conf->currency) . "</td></tr>";
					}
					
					$this->db->free($resql);
				}
				$text .= "</table>";
				$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 13);
				$pdf->writeHTMLCell($widthbox, 0, $posX, $posY, dol_htmlentitiesbr($text), 1, 1);
				
				// Tableau Loyer et solde
				$sql = "SELECT il.name, il.balance";
				$sql .= " FROM " . MAIN_DB_PREFIX . "immobilier_immoreceipt as il ";
				$sql .= " WHERE il.balance<>0 AND paye=0 AND date_start<'" . $this->db->idate($receipt->date_start) . "'";
				$sql .= " AND fk_property=" . $receipt->fk_property . " AND fk_renter=" . $receipt->fk_renter;
				$sql .= " ORDER BY echeance ASC";
				
				dol_syslog(get_class($this) . ':: loyerAntierieur sql=' . $sql, LOG_DEBUG);
				$resql = $this->db->query($sql);
				
				if ($resql) {
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
						dol_syslog(get_class($this) . ':: loyerAntierieur sql=' . $sql, LOG_DEBUG);
						$resql = $this->db->query($sql);
						
						$i = 0;
						$total = 0;
						$var = True;
						while ( $i < $num ) {
							$objp = $this->db->fetch_object($resql);
							
							$text .= '<tr>';
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
				$sql .= " FROM " . MAIN_DB_PREFIX . "immobilier_immoreceipt as il ";
				$sql .= " WHERE il.balance<>0 AND paye=0 AND date_start<='" . $this->db->idate($receipt->date_start) . "'";
				$sql .= " AND fk_property=" . $receipt->fk_property . " AND fk_renter=" . $receipt->fk_renter;
				$sql .= " GROUP BY fk_property,fk_renter";
				
				// print $sql;
				dol_syslog(get_class($this) . ':: total somme due', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql) {
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
							$title = 'Total somme à rembouser';
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
				$sql .= " WHERE local_id=" . $receipt->local_id . " AND locataire_id=" . $receipt->locataire_id;
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
	 * \param outputlangs Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showaddress = 1, $outputlangs) {
		global $conf, $langs;
		
		$outputlangs->load("main");
		
		pdf_pagehead($pdf, $outputlangs, $pdf->page_hauteur);
	}
}