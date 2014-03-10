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
dol_include_once ( '/immobilier/class/contrat.class.php' );
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
		$this->name = 'quitance';
		$this->description = $langs->trans ( 'Quittance' );
		
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
			$pdf->SetSubject ( $outputlangs->transnoentities ( "Quitance" ) );
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
				$pdf->MultiCell ( 100, 3, $outputlangs->convToOutputCharset ( 'Bailleur' ), 1, 'C' );
				
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
				
				$pdf->MultiCell ( 100, 20, $outputlangs->convToOutputCharset ( $this->str ), 1, 'L' );
				
				// Bloc Locataire
				$posX = $this->page_largeur - $this->marge_droite - 100;
				$posY = $pdf->getY () + 10;
				
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 15 );
				$pdf->SetXY ( $posX, $posY );
				$pdf->MultiCell ( 100, 3, $outputlangs->convToOutputCharset ( 'Locataire Destinataire' ), 1, 'C' );
				
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
				$pdf->MultiCell ( 100, 20, $outputlangs->convToOutputCharset ( $this->str ), 1, 'L' );
				
				// Bloc Quittance de loyer
				$posX = $this->marge_gauche;
				$posY = $pdf->getY () + 10;
				$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
				
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), 'B', 15 );
				$pdf->SetXY ( $posX, $posY );
				$pdf->MultiCell ( $widthbox, 3, $outputlangs->convToOutputCharset ( 'Quittance de loyer' ), 1, 'C' );
				
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 13 );
				$posY = $pdf->getY ();
				$pdf->SetXY ( $posX, $posY );
				
				$period = 'Loyer ' . dol_print_date ( $loyer->periode_du, '%b %Y' );
				$pdf->MultiCell ( $widthbox, 3, $outputlangs->convToOutputCharset ( $period ), 1, 'C' );
				
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 13 );
				$posY = $pdf->getY ();
				$pdf->SetXY ( $posX, $posY );
				
				$numquitance = 'Quittance n°:' . 'ILQ' . $loyer->id;
				$pdf->MultiCell ( $widthbox, 3, $outputlangs->convToOutputCharset ( $numquitance ), 1, 'R' );
				
				// Sous Bloc Quittance de loyer Gauche
				$posX = $this->marge_gauche;
				$posY = $pdf->getY ();
				$widthbox = ($this->page_largeur - $this->marge_gauche - $this->marge_droite) / 2;
				
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 12 );
				$pdf->SetXY ( $posX, $posY );
				$text = ' Reçu de :' . $locataire->nom . "\n";
				$text .= "\n";
				$montantpay = 0;
				if (! empty ( $loyer->paiepartiel )) {
					$montantpay = $loyer->paiepartiel;
				}
				$text .= ' la somme de :' . $montantpay . '€' . "\n";
				$text .= "\n";
				$dtpaiement = $paiement->date_paiement;
				if (empty ( $dtpaiement )) {
					$dtpaiement = $loyer->echeance;
				}
				$text .= ' le :' . dol_print_date ( $dtpaiement, 'daytext' ) . "\n";
				$text .= "\n";
				$text .= ' pour loyer et accessoires des locaux sis à :' . "\n";
				$text .= $local->adresse . "\n";
				$text .= "\n";
				$text .= 'en paiement du terme du ' . dol_print_date ( $loyer->periode_du, 'daytext' ) . "\n";
				$text .= 'au ' . dol_print_date ( $loyer->periode_au, 'daytext' ) . "\n";
				$text .= "\n";
				$text .= 'Fait à ' . $proprio->town . "\n";
				$text .= 'le ' . dol_print_date ( dol_now (), 'daytext' ) . "\n";
				$text .= "\n";
				
				$pdf->MultiCell ( $widthbox, 0, $outputlangs->convToOutputCharset ( $text ), 1, 'L' );
				
				$newpoy = $pdf->getY ();
				
				// Sous Bloc Quittance de loyer Droite
				$posX = $widthbox + $this->marge_gauche;
				$widthbox = ($this->page_largeur - $this->marge_gauche - $this->marge_droite) / 2;
				
				$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 12 );
				$pdf->SetXY ( $posX, $posY );
				$text = '<table>';
				$text .= '<tr>';
				$text .= '<td colspan="2">';
				$text .= 'Détail :' . "<BR>";
				
				$text .= ' - Loyer nu :' . $loyer->loy . '€' . "<BR>";
				$text .= ' - Charges / Provisions de Charges :' . $loyer->charges . '€' . "<BR>";
				$text .= "<BR>";
				$text .= 'Montant total du terme :' . $loyer->montant_tot . '€' . "<BR>";
				$text .= '</td>';
				$text .= '</tr>';
				
				$sql = "SELECT p.rowid, p.loyer_id, date_paiement as dp, p.montant, p.commentaire as type, il.montant_tot as amount";
				$sql .= " FROM " . MAIN_DB_PREFIX . "immo_paie as p";
				$sql .= ", " . MAIN_DB_PREFIX . "immo_loyer as il ";
				$sql .= " WHERE p.loyer_id = " . $loyer->id;
				$sql .= " AND p.loyer_id = il.rowid";
				$sql .= " ORDER BY dp DESC";
				
				// print $sql;
				dol_syslog ( get_class ( $this ) . ':: Paiement sql=' . $sql, LOG_DEBUG );
				$resql = $this->db->query ( $sql );
				if ($resql) {
					$num = $this->db->num_rows ( $resql );
					$i = 0;
					$total = 0;
					$text .= '<tr>';
					$text .= '<td align="left">' . $langs->trans ( "Date" ) . '</td>';
					$text .= '<td align="right">' . $langs->trans ( "Amount" ) . '</td>';
					$text .= '</tr><br>';
					$var = True;
					while ( $i < $num ) {
						$objp = $this->db->fetch_object ( $resql );
						
						$text .= '<tr>';
						$text .= '<td>' . dol_print_date ( $this->db->jdate ( $objp->dp ), 'day' ) . "</td>";
						$text .= '<td align="right">' . price ( $objp->montant ) . ' ' . $langs->trans ( "Currency" . $conf->currency ) . "</td>";
						$text .= "</tr>";
						$totalpaye += $objp->montant;
						$i ++;
					}
					
					if ($loyer->paye == 0) {
						$text .= "<br><tr><td align=\"left\">" . $langs->trans ( "AlreadyPaid" ) . " :</td><td align=\"right\">" . price ( $totalpaye ) . " " . $langs->trans ( "Currency" . $conf->currency ) . "</td></tr>";
						$text .= "<tr><td align=\"left\">" . $langs->trans ( "AmountExpected" ) . " :</td><td align=\"right\">" . price ( $loyer->montant_tot ) . " " . $langs->trans ( "Currency" . $conf->currency ) . "</td></tr>";
						
						$resteapayer = $loyer->montant_tot - $totalpaye;
						
						$text .= "<tr><td align=\"left\">" . $langs->trans ( "RemainderToPay" ) . " :</td>";
						$text .= "<td align=\"right\">" . price ( $resteapayer, 2 ) . " " . $langs->trans ( "Currency" . $conf->currency ) . "</td></tr>";
					}
					
					$this->db->free ( $resql );
				}
				$text .= "</table>";
				$pdf->writeHTMLCell ( $widthbox, $newpoy - $posY, $posX, $posY, dol_htmlentitiesbr ( $text ), 1 );
				
				// Tableau Loyer et solde
				$sql = "SELECT il.nom, il.solde";
				$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as il ";
				$sql .= " WHERE il.solde<>0 AND paye=0 AND periode_du<'" . $this->db->idate ( $loyer->periode_du ) . "'";
				$sql .= " AND local_id=" . $loyer->local_id . " AND locataire_id=" . $loyer->locataire_id;
				$sql .= " ORDER BY echeance ASC";
				
				dol_syslog ( get_class ( $this ) . ':: loyerAntierieur sql=' . $sql, LOG_DEBUG );
				$resql = $this->db->query ( $sql );
				
				if ($resql) {
					$num = $this->db->num_rows ( $resql );
					
					if ($num > 0) {
						
						// Bloc Solde Anterieur
						$posX = $this->marge_gauche;
						$posY = $pdf->getY () + ($newpoy - $posY) + 5;
						$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
						
						$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), 'B', 15 );
						$pdf->SetXY ( $posX, $posY );
						$pdf->MultiCell ( $widthbox, 3, $outputlangs->convToOutputCharset ( 'Solde Anterieur' ), 1, 'C' );
						
						$text = '<table>';
						
						// print $sql;
						dol_syslog ( get_class ( $this ) . ':: loyerAntierieur sql=' . $sql, LOG_DEBUG );
						$resql = $this->db->query ( $sql );
						
						$i = 0;
						$total = 0;
						$var = True;
						while ( $i < $num ) {
							$objp = $this->db->fetch_object ( $resql );
							
							$text .= '<tr>';
							$text .= '<td>' . $objp->nom . "</td>";
							$text .= "<td align=\"right\">" . $objp->solde . ' ' . $langs->trans ( "Currency" . $conf->currency ) . "</td>";
							$text .= "</tr>";
							
							$i ++;
						}
						
						$this->db->free ( $resql );
						
						$text .= "</table>";
						
						$posY = $pdf->getY ();
						
						$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), '', 13 );
						$pdf->writeHTMLCell ( $widthbox, 0, $posX, $posY, dol_htmlentitiesbr ( $text ), 1, 1 );
					}
				}
				
				// Bloc total somme due
				
				// Tableau total somme due
				$sql = "SELECT SUM(il.solde) as total";
				$sql .= " FROM " . MAIN_DB_PREFIX . "immo_loyer as il ";
				$sql .= " WHERE il.solde<>0 AND paye=0 AND periode_du<='" . $this->db->idate ( $loyer->periode_du ) . "'";
				$sql .= " AND local_id=" . $loyer->local_id . " AND locataire_id=" . $loyer->locataire_id;
				$sql .= " GROUP BY local_id,locataire_id";
				
				// print $sql;
				dol_syslog ( get_class ( $this ) . ':: loyerAntierieur sql=' . $sql, LOG_DEBUG );
				$resql = $this->db->query ( $sql );
				if ($resql) {
					$num = $this->db->num_rows ( $resql );
					
					if ($num > 0) {
						
						$objp = $this->db->fetch_object ( $resql );
						
						$posX = $this->marge_gauche;
						$posY = $pdf->getY () + 5;
						$widthbox = $this->page_largeur - $this->marge_gauche - $this->marge_droite;
						
						$pdf->SetFont ( pdf_getPDFFont ( $outputlangs ), 'B', 15 );
						$pdf->SetXY ( $posX, $posY );
						
						if ($objp->total > 0) {
							$title = 'Total somme due';
						} else {
							$title = 'Total somme à rembouser';
						}
						
						$pdf->MultiCell ( $widthbox, 3, $outputlangs->convToOutputCharset ( $title ), 1, 'C' );
						
						$text = '<table>';
						
						$i = 0;
						$total = 0;
							
						$text .= '<tr>';
						$text .= "<td align=\"right\">" . $objp->total . ' ' . $langs->trans ( "Currency" . $conf->currency ) . "</td></tr>";		
						
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