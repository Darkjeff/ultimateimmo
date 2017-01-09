<?php
/**
 * Copyright (C) 2012-2013 Florian Henry <florian.henry@open-concept.pro>
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
dol_include_once ( '/immobilier/core/modules/immobilier/immobilier_modules.php' );
dol_include_once ( '/immobilier/class/loyer.class.php' );
dol_include_once ( '/immobilier/class/locataire.class.php' );
dol_include_once ( '/immobilier/class/local.class.php' );
dol_include_once ( '/immobilier/class/immorent.class.php' );
dol_include_once ( '/immobilier/class/paie.class.php' );
dol_include_once ( '/adherents/class/adherent.class.php' );
require_once (DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php');

class pdf_quittance extends ModelePDFImmobilier {
	var $emetteur; // Objet societe qui emet
	
	/**
	 * \brief		Constructor
	 * \param		db		Database handler
	 */
	function __construct($db) {

		global $conf, $langs, $mysoc;
		
		$langs->load ( "immobilier@immobilier" );
		
		$this->db = $db;
		$this->name = 'chargeloc';
		$this->description = $langs->trans ( 'chargeloc' );
		
		// Dimension page pour format A4 en portrait
		$this->type = 'pdf';
		$formatarray = pdf_getFormat ();
		$this->page_largeur = $formatarray ['width'];
		$this->page_hauteur = $formatarray ['height'];
		$this->format = array (
			$this->page_largeur,$this->page_hauteur 
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
			$this->emetteur->country_code = substr ( $langs->defaultlang, - 2 ); // By default, if was not defined
	}

	/**
	 * \brief Fonction generant le document sur le disque
	 * \param agf		Objet document a generer (ou id si ancienne methode)
	 * outputlangs	Lang object for output language
	 * file		Name of file to generate
	 * \return int 1=ok, 0=ko
	 */
	function write_file($loyer, $outputlangs, $file, $socid, $courrier) {

		global $user, $langs, $conf, $mysoc;
		
		$default_font_size = pdf_getPDFFontSize ( $outputlangs );
		
		if (! is_object ( $outputlangs ))
			$outputlangs = $langs;
		
		if (! is_object ( $loyer )) {
			$id = $loyer;
			$loyer = new Loyer ( $this->db );
			$ret = $loyer->fetch ( $id );
		}
		
		// dol_syslog ( "pdf_quittance::debug loyer=" . var_export ( $loyer, true ) );
		
		// Definition of $dir and $file
		$dir = $conf->immobilier->dir_output;
		$file = $dir . '/' . $file;
		
		if (! file_exists ( $dir )) {
			if (create_exdir ( $dir ) < 0) {
				$this->error = $langs->trans ( "ErrorCanNotCreateDir", $dir );
				return 0;
			}
		}
		
		if (file_exists ( $dir )) {
			
			$pdf = pdf_getInstance ( $this->format, $this->unit, $this->orientation );
			
			if (class_exists ( 'TCPDF' )) {
				$pdf->setPrintHeader ( false );
				$pdf->setPrintFooter ( false );
			}
			
			$pdf->Open ();
			$pagenb = 0;
			
			$pdf->SetTitle ( $outputlangs->convToOutputCharset ( $loyer->nom ) );
			$pdf->SetSubject ( $outputlangs->transnoentities ( "Chargeloc" ) );
			$pdf->SetCreator ( "Dolibarr " . DOL_VERSION . ' (Immobilier module)' );
			$pdf->SetAuthor ( $outputlangs->convToOutputCharset ( $user->fullname ) );
			$pdf->SetKeyWords ( $outputlangs->convToOutputCharset ( $loyer->nom ) . " " . $outputlangs->transnoentities ( "Document" ) );
			if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION)
				$pdf->SetCompression ( false );
			
			$pdf->SetMargins ( $this->marge_gauche, $this->marge_haute, $this->marge_droite ); // Left, Top, Right
			$pdf->SetAutoPageBreak ( 1, 0 );
			
			// On recupere les infos societe
			$locataire = new Locataire ( $this->db );
			$result = $locataire->fetch ( $loyer->locataire_id );
			
			$proprio = new Adherent ( $this->db );
			$result = $proprio->fetch ( $loyer->proprietaire_id );
			
			$local = new Local ( $this->db );
			$result = $local->fetch ( $loyer->local_id );
			
			$paiement = new Paie ( $this->db );
			$result = $paiement->fetch_by_loyer ( $loyer->id );
			
			if (! empty ( $loyer->id )) {
				// New page
				$pdf->AddPage ();
				$pagenb ++;
				$this->_pagehead ( $pdf, $agf, 1, $outputlangs );
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 9 );
				$pdf->MultiCell ( 0, 3, '', 0, 'J' );
				$pdf->SetTextColor ( 0, 0, 0 );
				
				$posY = $this->marge_haute;
				$posX = $this->marge_gauche;
				
				// Bloc Owner
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 15 );
				$pdf->SetXY ( $posX, $posY + 3 );
				$pdf->MultiCell ( 80, 3, $outputlangs->convToOutputCharset ( 'Bailleur' ), 1, 'C' );
				
				$posY = $pdf->getY ();
				$pdf->SetXY ( $posX, $posY );
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 13 );
				$this->str = $proprio->getFullName ( $outputlangs ) . "\n";
				$this->str .= $proprio->address . "\n";
				$this->str .= $proprio->zip . ' ' . $proprio->town;
				$this->str .= ' - ' . $proprio->country . "\n\n";
				if ($proprio->phone) {
					$this->str .= $outputlangs->transnoentities ( 'Téléphone' ) . ' ' . $proprio->phone . "\n";
				}
				if ($proprio->fax) {
					$this->str .= $outputlangs->transnoentities ( 'Fax' ) . ' ' . $proprio->fax . "\n";
				}
				if ($proprio->email) {
					$this->str .= $outputlangs->transnoentities ( 'EMail' ) . ' ' . $proprio->email . "\n";
				}
				if ($proprio->url) {
					$this->str .= $outputlangs->transnoentities ( 'Url' ) . ' ' . $proprio->url . "\n";
				}
				
				$pdf->MultiCell ( 80, 20, $outputlangs->convToOutputCharset ( $this->str ), 1, 'L' );
				
				// Bloc Locataire
				$posX = $this->page_largeur - $this->marge_droite - 80;
				$posY = $pdf->getY () - 20 ;
				
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 15 );
				$pdf->SetXY ( $posX, $posY );
				$pdf->MultiCell ( 80, 3, $outputlangs->convToOutputCharset ( 'Locataire Destinataire' ), 1, 'C' );
				
				$posY = $pdf->getY ();
				$pdf->SetXY ( $posX, $posY );
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 13 );
				$this->str = $locataire->nom . "\n";
				$this->str .= $local->nom . "\n";
				if (! empty ( $locataire->adresse )) {
					$this->str .= $locataire->adresse . "\n";
				} else {
					$this->str .= $local->adresse . "\n";
				}
				$pdf->MultiCell ( 80, 20, $outputlangs->convToOutputCharset ( $this->str ), 1, 'L' );
				
				$text .= "\n";
				$text .= 'Fait à ' . $proprio->town . ' le ' . dol_print_date ( dol_now (), 'daytext' ) . "\n";
			
				
				
				$pdf->MultiCell ( $widthbox, 0, $outputlangs->convToOutputCharset ( $text ), 0, 'L' );
				
				
				
				
				
				// Bloc total Charge
				
				// Tableau total Charge
				$sql = " SELECT YEAR(echeance) as annee, SUM(charges) as accompte,";
				$sql .= " SUM(charge_ex) as charge,";
				$sql .= " GROUP_CONCAT(CommCharge SEPARATOR '$') as CommCharge";
				$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer";
				$sql .= " WHERE local_id=" . $loyer->local_id . " AND locataire_id=" . $loyer->locataire_id;
				$sql .= " GROUP BY YEAR(echeance)";
				
				// print $sql;
				dol_syslog ( get_class ( $this ) . ':: total charge sql=' . $sql, LOG_DEBUG );
				$resql = $this->db->query ( $sql );
				if ($resql) {
					$num = $this->db->num_rows ( $resql );
						
					if ($num > 0) {
						
						
						$posX = $this->marge_gauche;
						$posY = $this->marge_haute;
						
				
						
						
						$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
						
						$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), 'B', 15 );
						$pdf->SetXY ( $posX, $posY );
						
						$pdf->MultiCell ( $widthbox, 3, $outputlangs->convToOutputCharset ( 'Récapitulatif charges locatives' ), 1, 'C' );
						
						
						$text = '<table>';
				
						$text .= '<tr>';
						$text .= "<th align=\"left\">Année</th>";
						$text .= "<th align=\"left\">Charges Locatives</th>";
						$text .= "<th align=\"left\">Paiement</th>";
						$text .= "<th align=\"left\">Reste a payer</th>";
						$text .= "<th align=\"left\">Commentaire</th>";
						
						$text .= "</tr>";
						
						
						while($objp = $this->db->fetch_object ( $resql )) {
				
							$text .= '<tr>';
							$text .= "<td align=\"left\">" . $objp->annee. "</td>";
							$text .= "<td align=\"left\">" . price($objp->charge) .' '. $langs->trans ( "Currency" . $conf->currency )."</td>";
							$text .= "<td align=\"left\">" . price($objp->accompte) . ' '. $langs->trans ( "Currency" . $conf->currency )."</td>";
							$text .= "<td align=\"left\">" . price($objp->charge-$objp->accompte) .' '. $langs->trans ( "Currency" . $conf->currency ). "</td>";
							
							if (!empty($objp->CommCharge)) {
								$comm_array=explode('$',$objp->CommCharge);
							}
							$commentaire_charge=array();
							if (is_array($comm_array) && count($comm_array)>0) {
								foreach ($comm_array as $txt_com) {
									if (!empty($txt_com)) {
										$commentaire_charge[]=$txt_com;
									}
								}
							}
							
							$text .= "<td align=\"left\">" . implode('<br>',$commentaire_charge) . "</td>";
							
									
							$text .= "</tr>";
						}
						$this->db->free ( $resql );
				
						$text .= "</table>";
				
						$posY = $pdf->getY ();
				
						$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 13 );
						$pdf->writeHTMLCell ( $widthbox, 0, $posX, $posY, dol_htmlentitiesbr ( $text ), 1 );
					}
				}
			}
			
			$pdf->Close ();
			
			$pdf->Output ( $file, 'F' );
			if (! empty ( $conf->global->MAIN_UMASK ))
				@chmod ( $file, octdec ( $conf->global->MAIN_UMASK ) );
			
			return 1; // Pas d'erreur
		} else {
			$this->error = $langs->trans ( "ErrorConstantNotDefined", "AGF_OUTPUTDIR" );
			return 0;
		}
		$this->error = $langs->trans ( "ErrorUnknown" );
		return 0; // Erreur par defaut
	}

	/**
	 * \brief Show header of page
	 * \param pdf Object PDF
	 * \param object Object invoice
	 * \param showaddress 0=no, 1=yes
	 * \param outputlangs		Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showaddress = 1, $outputlangs) {

		global $conf, $langs;
		
		$outputlangs->load ( "main" );
		
		pdf_pagehead ( $pdf, $outputlangs, $pdf->page_hauteur );
	}
}