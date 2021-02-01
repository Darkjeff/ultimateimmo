<?php
/* Copyright (C) 2013 		Olivier Geffroy    <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016  Alexandre Spangaro <aspangaro@zendsi.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    htdocs/custom/immobilier/core/lib/immobilier.lib.php
 * \ingroup Immobilier
 * \brief   Library of immobilier 
 */
$langs->load('immobilier@immobilier');

/**
 * Prepare array with renters list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function renter_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/renter/card.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/renter/bank.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Bank");
	$head[$h][2] = 'bank';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/renter/bilan.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Bilan");
	$head[$h][2] = 'bilan';
	$hselected = $h;
	$h ++;
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    $upload_dir = $conf->immobilier->dir_output . '/renter/' . $object->id;
    $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
    $head[$h][0] = dol_buildpath('/immobilier/renter/document.php', 1) . '?id=' . $object->id;
    $head[$h][1] = $langs->trans("Documents");
	if($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
    $head[$h][2] = 'document';
    $h++;

	$head[$h][0] = dol_buildpath('/immobilier/property/letter_by_properties.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Letter");
	$head[$h][2] = 'letter';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/renter/info.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_renter');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_renter','remove');

	return $head;
}
/**
 * Prepare array with rents list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function rent_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/rent/card.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$hselected = $h;
	$h ++;
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    $upload_dir = $conf->immobilier->dir_output . '/rent/' . $object->id;
    $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
    $head[$h][0] = dol_buildpath('/immobilier/rent/document.php', 1) . '?id=' . $object->id;
    $head[$h][1] = $langs->trans("Documents");
	if($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
    $head[$h][2] = 'document';
    $h++;
	
	$head[$h][0] = dol_buildpath('/immobilier/rent/info.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_rent');
	
	return $head;
}

/**
 * Return head table for contact tabs screen
 *
 * @param object $object contact
 * @return array head table of tabs
 */
function local_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/local/fiche_local.php', 1) . '?action=update&id=' . $object->id;
	$head[$h][1] = $langs->trans("maininfo");
	$head[$h][2] = 'maininfo';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/local/document.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("document");
	$head[$h][2] = 'document';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/local/photos.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Photo");
	$head[$h][2] = 'photo';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/compteur/relever_compteur.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("ReleverCompteur");
	$head[$h][2] = 'compteurrelever';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/DPE/dpe.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("DPE");
	$head[$h][2] = 'DPE';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/local/bilan_local.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_local');
	
	return $head;
}

/**
 * Prepare array with properties list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function property_prepare_head($object)
{
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array();
	
	$head[$h][0] = dol_buildpath('/immobilier/property/card.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$hselected = $h;
	$h ++;
	
	if (empty($conf->global->MAIN_DISABLE_NOTES_TAB))		
	{		
		$nbNote = (empty($object->note_private)?0:1)+(empty($object->note_public)?0:1);		
		$head[$h][0] = dol_buildpath('/immobilier/property/note.php', 1) . '?id=' . $object->id;		
		$head[$h][1] = $langs->trans("Notes");		
		if($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';		
		$head[$h][2] = 'note';		
		$h ++;		
	}

	$head[$h][0] = dol_buildpath('/immobilier/property/equipement.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Equipements");
	$head[$h][2] = 'equipement';
	$hselected = $h;
	$h ++;
	
	$head[$h][0] = dol_buildpath('/immobilier/property/diagnostic.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Diagnostic");
	$head[$h][2] = 'diagnostic';
	$hselected = $h;
	$h ++;
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
    $upload_dir = $conf->immobilier->dir_output . '/property/' . $object->id;
    $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
    $head[$h][0] = dol_buildpath('/immobilier/property/document.php', 1) . '?id=' . $object->id;
    $head[$h][1] = $langs->trans("Documents");
	if($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
    $head[$h][2] = 'document';
    $h++;

	$head[$h][0] = dol_buildpath('/immobilier/property/info.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Info");
	$head[$h][2] = 'info';
	$hselected = $h;
	$h ++;

	complete_head_from_modules($conf,$langs,$object,$head,$h,'immobilier_property');

	complete_head_from_modules($conf,$langs,$object,$head,$h,'immobilier_property','remove');

	return $head;
}

/**
 * Return head table for contact tabs screen
 *
 * @param object $object contact
 * @return array head table of tabs
 */
function charge_prepare_head($object)
{
	global $langs, $conf, $user;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/immobilier/cost/card.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("Fiche");
	$head[$h][2] = 'fiche';
	$hselected = $h;
	$h ++;

	$head[$h][0] = dol_buildpath('/immobilier/cost/document.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("document");
	$head[$h][2] = 'document';
	$hselected = $h;
	$h ++;

    $head[$h][0] = dol_buildpath('/immobilier/cost/ventil_cost.php', 1) . '?id=' . $object->id;
	$head[$h][1] = $langs->trans("repartition");
	$head[$h][2] = 'repartition';
	$hselected = $h;
	$h ++;

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_immeuble');

	return $head;
}

/**
 * Return head table for contact tabs screen
 *
 * @param object $object contact
 * @return array head table of tabs
 */
function contact_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/immobilier/contact/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/immobilier/contact/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_contact');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_contact', 'remove');
	
	return $head;
}

/**
 * Return head table for contact tabs screen
 *
 * @param object $object contact
 * @return array head table of tabs
 */
function receipt_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/immobilier/receipt/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/immobilier/receipt/mails.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Email");
	$head [$h] [2] = 'mail';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/immobilier/receipt/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_contact');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'immobilier_contact', 'remove');
	
	return $head;
}

/**
 *  Return array head with list of tabs to view object informations
 *
 *  @return	array		head
 */
function immobilier_admin_prepare_head()
{
    global $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/immobilier/admin/public.php", 1);
    $head[$h][1] = $langs->trans("PublicSite");
    $head[$h][2] = 'public';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,'',$head,$h,'immobilier_admin');

	$head[$h][0] = dol_buildpath("/immobilier/admin/gmaps.php", 1);
    $head[$h][1] = $langs->trans("Google Maps");
    $head[$h][2] = 'gmaps';
    $h++;

    $head[$h][0] = dol_buildpath("/immobilier/admin/property_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFieldsProperty");
    $head[$h][2] = 'attributes';
    $h++;

    $head[$h][0] = dol_buildpath("/immobilier/admin/about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    complete_head_from_modules($conf,$langs,'',$head,$h,'immobilier_admin','remove');

    return $head;
}

/**
 *  Show tab footer of a card
 *
 *  @param	object	$object			Object to show
 *  @param	string	$paramid   		Name of parameter to use to name the id into the URL next/previous link
 *  @param	string	$morehtml  		More html content to output just before the nav bar
 *  @param	int		$shownav	  	Show Condition (navigation is shown if value is 1)
 *  @param	string	$fieldid   		Nom du champ en base a utiliser pour select next et previous (we make the select max and min on this field)
 *  @param	string	$fieldref   	Nom du champ objet ref (object->ref) a utiliser pour select next et previous
 *  @param	string	$morehtmlref  	More html to show after ref
 *  @param	string	$moreparam  	More param to add in nav link url.
 *	@param	int		$nodbprefix		Do not include DB prefix to forge table name
 *	@param	string	$morehtmlleft	More html code to show before ref
 *	@param	string	$morehtmlright	More html code to show before navigation arrows
 *  @return	void
 */
function immo_banner_tab($object, $paramid, $morehtml='', $shownav=1, $fieldid='rowid', $fieldref='ref', $morehtmlref='', $moreparam='', $nodbprefix=0, $morehtmlleft='', $morehtmlright='')
{
	global $conf, $form, $user, $langs;

	$maxvisiblephotos=1;
	$showimage=1;

	$modulepart='unknown';
	if ($object->element == 'immoproperty') $modulepart='immoproperty';
	if ($object->element == 'immorenter') $modulepart='immorenter';

	print '<div class="arearef heightref valignmiddle" width="100%">';
    $width=80; $cssclass='photoref';
    $nophoto='/public/theme/common/nophoto.png';
	$morehtmlleft.='<div class="floatleft inline-block valignmiddle divphotoref"><img class="photo'.$modulepart.($cssclass?' '.$cssclass:'').'" alt="No photo" border="0"'.($width?' width="'.$width.'"':'').($height?' height="'.$height.'"':'').' src="'.DOL_URL_ROOT.$nophoto.'"></div>';

    $morehtmlright.=$object->getLibStatut(2,0);

	if (! empty($object->name_alias)) $morehtmlref.='<div class="refidno">'.$object->name_alias.'</div>';      // For thirdparty
	if (! empty($object->label))      $morehtmlref.='<div class="refidno">'.$object->label.'</div>';           // For product
	
	$morehtmlref.='<div class="refidno">';
    $morehtmlref.=$object->getBannerAddress('refaddress',$object);
    $morehtmlref.='</div>';
	
	print $form->showrefnav($object, $paramid, $morehtml, $shownav, $fieldid, $fieldref, $morehtmlref, $moreparam, $nodbprefix, $morehtmlleft, $morehtmlright);
	print '</div>';
	print '<div class="underrefbanner clearboth"></div>';
}
