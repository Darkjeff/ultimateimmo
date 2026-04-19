<?php
/* Copyright (C) 2018-2021 Philippe GRAND <philippe.grand@atoo-net.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    ultimateimmo/lib/ultimateimmo.lib.php
 * \ingroup ultimateimmo
 * \brief   Library files with common functions for Ultimateimmo
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function ultimateimmoAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("ultimateimmo@ultimateimmo");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/immoreceipt.php", 1);
	$head[$h][1] = $langs->trans("Quittances&Baux");
	$head[$h][2] = 'quittance';
	$h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/gmaps.php", 1);
	$head[$h][1] = $langs->trans("Google Maps");
	$head[$h][2] = 'gmaps';
	$h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/public.php", 1);
	$head[$h][1] = $langs->trans("PublicSite");
	$head[$h][2] = 'public';
	$h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/property_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsProperty");
    $head[$h][2] = 'attributes_property';
    $h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/renter_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsRenter");
    $head[$h][2] = 'attributes_renter';
    $h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/owner_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsOwner");
    $head[$h][2] = 'attributes_owner';
    $h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/payment_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsPayment");
    $head[$h][2] = 'attributes_payment';
    $h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/receipt_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsReceipt");
    $head[$h][2] = 'attributes_receipt';
    $h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/rent_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsRent");
    $head[$h][2] = 'attributes_rent';
    $h++;

	$head[$h][0] = dol_buildpath("/ultimateimmo/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@ultimateimmo:/ultimateimmo/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@ultimateimmo:/ultimateimmo/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'ultimateimmo');

	return $head;
}

/**
 *  Show footer of page for PDF generation
 *
 *	@param	TCPDF		$pdf     		The PDF factory
 *  @param  Translate	$outputlangs	Object lang for output
 * 	@param	string		$paramfreetext	Constant name of free text
 * 	@param	Societe		$fromcompany	Object company
 * 	@param	int			$marge_basse	Margin bottom we use for the autobreak
 * 	@param	int			$marge_gauche	Margin left (no more used)
 * 	@param	int			$page_hauteur	Page height (no more used)
 * 	@param	Object		$object			Object shown in PDF
 * 	@param	int			$showdetails	Show company adress details into footer (0=Nothing, 1=Show address, 2=Show managers, 3=Both)
 *  @param	int			$hidefreetext	1=Hide free text, 0=Show free text
 * 	@return	int							Return height of bottom margin including footer text
 */
function pdf_ultimate_pagefoot(&$pdf, $outputlangs, $paramfreetext, $fromcompany, $marge_basse, $marge_gauche, $page_hauteur, $object, $showdetails = 0, $hidefreetext = 0)
{
	global $conf, $user, $mysoc;

	$outputlangs->load("dict");
	$line = '';

	$dims = $pdf->getPageDimensions();

	// Line of free text
	if (empty($hidefreetext) && !empty($conf->global->$paramfreetext)) {
		$substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
		// More substitution keys
		$substitutionarray['__FROM_NAME__'] = $fromcompany->name;
		$substitutionarray['__FROM_EMAIL__'] = $fromcompany->email;
		complete_substitutions_array($substitutionarray, $outputlangs, $object);
		$newfreetext = make_substitutions($conf->global->$paramfreetext, $substitutionarray, $outputlangs);

		// Make a change into HTML code to allow to include images from medias directory.
		// <img alt="" src="/dolibarr_dev/htdocs/viewimage.php?modulepart=medias&amp;entity=1&amp;file=image/ldestailleur_166x166.jpg" style="height:166px; width:166px" />
		// become
		// <img alt="" src="'.DOL_DATA_ROOT.'/medias/image/ldestailleur_166x166.jpg" style="height:166px; width:166px" />
		$newfreetext = preg_replace('/(<img.*src=")[^\"]*viewimage\.php[^\"]*modulepart=medias[^\"]*file=([^\"]*)("[^\/]*\/>)/', '\1' . DOL_DATA_ROOT . '/medias/\2\3', $newfreetext);

		$line .= $outputlangs->convToOutputCharset($newfreetext);
	}

	// First line of company infos
	$line1 = "";
	$line2 = "";
	$line3 = "";
	$line4 = "";

	if ($showdetails == 1 || $showdetails == 3) {
		// Company name
		if ($fromcompany->name) {
			$line1 .= ($line1 ? " - " : "") . $outputlangs->transnoentities("RegisteredOffice") . ": " . $fromcompany->name;
		}
		// Address
		if ($fromcompany->address) {
			$line1 .= ($line1 ? " - " : "") . str_replace("\n", ", ", $fromcompany->address);
		}
		// Zip code
		if ($fromcompany->zip) {
			$line1 .= ($line1 ? " - " : "") . $fromcompany->zip;
		}
		// Town
		if ($fromcompany->town) {
			$line1 .= ($line1 ? " " : "") . $fromcompany->town;
		}
		// Phone
		if ($fromcompany->phone) {
			$line2 .= ($line2 ? " - " : "") . $outputlangs->transnoentities("Phone") . ": " . $fromcompany->phone;
		}
		// Fax
		if ($fromcompany->fax) {
			$line2 .= ($line2 ? " - " : "") . $outputlangs->transnoentities("Fax") . ": " . $fromcompany->fax;
		}

		// URL
		if ($fromcompany->url) {
			$line2 .= ($line2 ? " - " : "") . $fromcompany->url;
		}
		// Email
		if ($fromcompany->email) {
			$line2 .= ($line2 ? " - " : "") . $fromcompany->email;
		}
	}
	if ($showdetails == 2 || $showdetails == 3 || ($fromcompany->country_code == 'DE')) {
		// Managers
		if ($fromcompany->managers) {
			$line2 .= ($line2 ? " - " : "") . $fromcompany->managers;
		}
	}

	// Line 3 of company infos
	// Juridical status
	if ($fromcompany->forme_juridique_code) {
		$line3 .= ($line3 ? " - " : "") . $outputlangs->convToOutputCharset(getFormeJuridiqueLabel($fromcompany->forme_juridique_code));
	}
	// Capital
	if ($fromcompany->capital) {
		$tmpamounttoshow = price2num($fromcompany->capital); // This field is a free string
		if (is_numeric($tmpamounttoshow) && $tmpamounttoshow > 0) $line3 .= ($line3 ? " - " : "") . $outputlangs->transnoentities("CapitalOf", price($tmpamounttoshow, 0, $outputlangs, 0, 0, 0, $conf->currency));
		else $line3 .= ($line3 ? " - " : "") . $outputlangs->transnoentities("CapitalOf", $tmpamounttoshow, $outputlangs);
	}
	// Prof Id 1
	if ($fromcompany->idprof1 && ($fromcompany->country_code != 'FR' || !$fromcompany->idprof2)) {
		$field = $outputlangs->transcountrynoentities("ProfId1", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) $field = $reg[1];
		$line3 .= ($line3 ? " - " : "") . $field . ": " . $outputlangs->convToOutputCharset($fromcompany->idprof1);
	}
	// Prof Id 2
	if ($fromcompany->idprof2) {
		$field = $outputlangs->transcountrynoentities("ProfId2", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) $field = $reg[1];
		$line3 .= ($line3 ? " - " : "") . $field . ": " . $outputlangs->convToOutputCharset($fromcompany->idprof2);
	}

	// Line 4 of company infos
	// Prof Id 3
	if ($fromcompany->idprof3) {
		$field = $outputlangs->transcountrynoentities("ProfId3", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) $field = $reg[1];
		$line4 .= ($line4 ? " - " : "") . $field . ": " . $outputlangs->convToOutputCharset($fromcompany->idprof3);
	}
	// Prof Id 4
	if ($fromcompany->idprof4) {
		$field = $outputlangs->transcountrynoentities("ProfId4", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) $field = $reg[1];
		$line4 .= ($line4 ? " - " : "") . $field . ": " . $outputlangs->convToOutputCharset($fromcompany->idprof4);
	}
	// Prof Id 5
	if ($fromcompany->idprof5) {
		$field = $outputlangs->transcountrynoentities("ProfId5", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) $field = $reg[1];
		$line4 .= ($line4 ? " - " : "") . $field . ": " . $outputlangs->convToOutputCharset($fromcompany->idprof5);
	}
	// Prof Id 6
	if ($fromcompany->idprof6) {
		$field = $outputlangs->transcountrynoentities("ProfId6", $fromcompany->country_code);
		if (preg_match('/\((.*)\)/i', $field, $reg)) $field = $reg[1];
		$line4 .= ($line4 ? " - " : "") . $field . ": " . $outputlangs->convToOutputCharset($fromcompany->idprof6);
	}
	// IntraCommunautary VAT
	if ($fromcompany->tva_intra != '') {
		$line4 .= ($line4 ? " - " : "") . $outputlangs->transnoentities("VATIntraShort") . ": " . $outputlangs->convToOutputCharset($fromcompany->tva_intra);
	}

	$pdf->SetFont('', '', 7);
	$pdf->SetDrawColor(224, 224, 224);

	// The start of the bottom of this page footer is positioned according to # of lines
	$freetextheight = 0;
	if ($line)	// Free text
	{
		//$line="sample text<br>\nfd<strong>sf</strong>sdf<br>\nghfghg<br>";
		if (empty($conf->global->PDF_ALLOW_HTML_FOR_FREE_TEXT)) {
			$width = 20000;
			$align = 'L';	// By default, ask a manual break: We use a large value 20000, to not have automatic wrap. This make user understand, he need to add CR on its text.
			if (!empty($conf->global->MAIN_USE_AUTOWRAP_ON_FREETEXT)) {
				$width = 200;
				$align = 'C';
			}
			$freetextheight = $pdf->getStringHeight($width, $line);
		} else {
			$freetextheight = pdfGetHeightForHtmlContent($pdf, dol_htmlentitiesbr($line, 1, 'UTF-8', 0));      // New method (works for HTML content)
			//print '<br>'.$freetextheight;exit;
		}
	}

	$marginwithfooter = $marge_basse + $freetextheight + (!empty($line1) ? 3 : 0) + (!empty($line2) ? 3 : 0) + (!empty($line3) ? 3 : 0) + (!empty($line4) ? 3 : 0);
	$posy = $marginwithfooter + 0;

	if ($line)	// Free text
	{
		$pdf->SetXY($dims['lm'], -$posy);
		if (empty($conf->global->PDF_ALLOW_HTML_FOR_FREE_TEXT))   // by default
		{
			$pdf->MultiCell(0, 3, $line, 0, $align, 0);
		} else {
			$pdf->writeHTMLCell($pdf->page_largeur - $pdf->margin_left - $pdf->margin_right, $freetextheight, $dims['lm'], $dims['hk'] - $marginwithfooter, dol_htmlentitiesbr($line, 1, 'UTF-8', 0));
		}
		$posy -= $freetextheight;
	}

	$pdf->SetY(-$posy);
	$pdf->line($dims['lm'], $dims['hk'] - $posy, $dims['wk'] - $dims['rm'], $dims['hk'] - $posy);
	$posy--;

	if (!empty($line1)) {
		$pdf->SetFont('', 'B', 7);
		$pdf->SetXY($dims['lm'], -$posy);
		$pdf->MultiCell($dims['wk'] - $dims['rm'] - $dims['lm'], 2, $line1, 0, 'C', 0);
		$posy -= 3;
		$pdf->SetFont('', '', 7);
	}

	if (!empty($line2)) {
		$pdf->SetFont('', 'B', 7);
		$pdf->SetXY($dims['lm'], -$posy);
		$pdf->MultiCell($dims['wk'] - $dims['rm'] - $dims['lm'], 2, $line2, 0, 'C', 0);
		$posy -= 3;
		$pdf->SetFont('', '', 7);
	}

	if (!empty($line3)) {
		$pdf->SetXY($dims['lm'], -$posy);
		$pdf->MultiCell($dims['wk'] - $dims['rm'] - $dims['lm'], 2, $line3, 0, 'C', 0);
	}

	if (!empty($line4)) {
		$posy -= 3;
		$pdf->SetXY($dims['lm'], -$posy);
		$pdf->MultiCell($dims['wk'] - $dims['rm'] - $dims['lm'], 2, $line4, 0, 'C', 0);
	}

	// Show page nb only on iso languages (so default Helvetica font)
	/*if (strtolower(pdf_getPDFFont($outputlangs)) == 'helvetica')
	{*/
	$pdf->SetXY($dims['wk'] - $dims['rm'] - 15, -$posy);
	//print 'xxx'.$pdf->PageNo().'-'.$pdf->getAliasNbPages().'-'.$pdf->getAliasNumPage();exit;
	$pdf->MultiCell(15, 2, $pdf->PageNo() . '/' . $pdf->getAliasNbPages(), 0, 'R', 0);
	//}

	return $marginwithfooter;
}

/**
 * @param $title string Title
 * @param $addJsSign bool add Js Lib For Sign Doc
 * @param $addQuagga bool add Js and CSS Lib For Scanner Bar Code
 * @return void
 */
function llxHeaderUltimateImmoPublic($title = '', $addJsSign = false, $addQuagga = false)
{
	global $conf;

	$conf->dol_hide_leftmenu = 1;
	$conf->dol_hide_leftmenu = 1;
	$bootstrapCss = array('/ultimateimmo/includes/bootstrap-5.3.1/css/bootstrap.min.css',
		'/ultimateimmo/includes/bootstrap-5.3.1/css/bootstrap-grid.min.css',
		'/ultimateimmo/includes/bootstrap-5.3.1/css/bootstrap-reboot.min.css',
		'/ultimateimmo/includes/bootstrap-5.3.1/css/bootstrap-utilities.min.css');
	$moreCss = $bootstrapCss;
	$bootstrapJs = array('/ultimateimmo/includes/bootstrap-5.3.1/js/bootstrap.bundle.min.js');
	$moreJs = $bootstrapJs;
	$moreJs[] = DOL_URL_ROOT . '/includes/jquery/plugins/jSignature/jSignature.js';

	$head = '<meta name="apple-mobile-web-app-title" content="DoliPad"/>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width, initial-scale=1"/>';
	top_htmlhead($head, $title, 0, 0, $moreJs, $moreCss);
	print '<body>';
}
