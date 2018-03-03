<?php
/* Copyright (C) 2011 Laurent Destailleur <eldy@users.sourceforge.net>
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
 * or see http://www.gnu.org/
 */

$object=$GLOBALS['object'];

global $db,$conf,$mysoc,$langs,$user,$hookmanager,$extrafields;

require_once(DOL_DOCUMENT_ROOT ."/core/class/html.formcompany.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/class/html.formfile.class.php");
require_once(DOL_DOCUMENT_ROOT ."/core/lib/company.lib.php");

$form=new Form($GLOBALS['db']);
$formcompany=new FormCompany($GLOBALS['db']);
$formadmin=new FormAdmin($GLOBALS['db']);
$formfile=new FormFile($GLOBALS['db']);


// Load object modCodeTiers
$module=$conf->global->SOCIETE_CODECLIENT_ADDON;
if (! $module) dolibarr_error('',$langs->trans("ErrorModuleThirdPartyCodeInCompanyModuleNotDefined"));
if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
{
    $module = substr($module, 0, dol_strlen($module)-4);
}
// Load object modCodeClient
$dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
foreach ($dirsociete as $dirroot)
{
    $res=dol_include_once($dirroot.$module.".php");
    if ($res) break;
}
$modCodeClient = new $module;
// We verified if the tag prefix is used
if ($modCodeClient->code_auto)
{
    $prefixCustomerIsUsed = $modCodeClient->verif_prefixIsUsed();
}

$module=$conf->global->SOCIETE_CODECLIENT_ADDON;
if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
{
    $module = substr($module, 0, dol_strlen($module)-4);
}
$dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
foreach ($dirsociete as $dirroot)
{
    $res=dol_include_once($dirroot.$module.'.php');
    if ($res) break;
}
$modCodeFournisseur = new $module($db);
// On verifie si la balise prefix est utilisee
if ($modCodeFournisseur->code_auto)
{
    $prefixSupplierIsUsed = $modCodeFournisseur->verif_prefixIsUsed();
}


if ($_POST["name"])
{
    $object->client=1;

    $object->lastname=$_POST["name"];
    $object->firstname=$_POST["firstname"];
    $object->particulier=0;
    $object->prefix_comm=$_POST["prefix_comm"];
    $object->client=$_POST["client"]?$_POST["client"]:$object->client;
    $object->code_client=$_POST["code_client"];
    $object->fournisseur=$_POST["fournisseur"]?$_POST["fournisseur"]:$object->fournisseur;
    $object->code_fournisseur=$_POST["code_fournisseur"];
    $object->adresse=$_POST["address"]; // TODO obsolete
    $object->address=$_POST["address"];
    $object->zip=$_POST["zipcode"];
    $object->town=$_POST["town"];
    $object->state_id=$_POST["departement_id"];
    $object->phone=$_POST["phone"];
    $object->fax=$_POST["fax"];
    $object->email=$_POST["email"];
    $object->url=$_POST["url"];
    $object->capital=$_POST["capital"];
    $object->barcode=$_POST["barcode"];
    $object->idprof1=$_POST["idprof1"];
    $object->idprof2=$_POST["idprof2"];
    $object->idprof3=$_POST["idprof3"];
    $object->idprof4=$_POST["idprof4"];
    $object->typent_id=$_POST["typent_id"];
    $object->effectif_id=$_POST["effectif_id"];

    $object->tva_assuj = $_POST["assujtva_value"];
    $object->status= $_POST["status"];

    //Local Taxes
    $object->localtax1_assuj       = $_POST["localtax1assuj_value"];
    $object->localtax2_assuj       = $_POST["localtax2assuj_value"];

    $object->tva_intra=$_POST["tva_intra"];

    $object->commercial_id=$_POST["commercial_id"];
    $object->default_lang=$_POST["default_lang"];

    // We set country_id, country_code and label for the selected country
    $object->country_id=$_POST["country_id"]?$_POST["country_id"]:$mysoc->country_id;
    if ($object->country_id)
    {
        $tmparray=getCountry($object->country_id,'all');
        $object->country_code=$tmparray['code'];
        $object->country     =$tmparray['label'];
    }
    $object->forme_juridique_code=$_POST['forme_juridique_code'];
}

?>

<!-- BEGIN PHP TEMPLATE CARD_EDIT.TPL.PHP PATIENT -->

<?php
print_fiche_titre($langs->trans("EditPatient"));

dol_htmloutput_errors($GLOBALS['error'],$GLOBALS['errors']);

print '<form action="'.$_SERVER["PHP_SELF"].'?socid='.$object->id.'" method="post" name="formsoc">';
print '<input type="hidden" name="action" value="update">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="socid" value="'.$object->id.'">';
print '<input type="hidden" name="private" value="0">';
print '<input type="hidden" name="status" value="'.$object->status.'">';
print '<input type="hidden" name="client" value="'.$object->client.'">';
if ($modCodeClient->code_auto || $modCodeFournisseur->code_auto) print '<input type="hidden" name="code_auto" value="1">';


dol_fiche_head('');

print '<table class="border" width="100%">';

// Name
print '<tr><td class="titlefield"><span class="fieldrequired">'.$langs->trans('PatientName').'</span></td><td colspan="3"><input type="text" size="40" maxlength="60" name="name" value="'.$object->name.'"></td>';


// Prospect/Customer
print '<tr><td>'.fieldLabel('ProspectCustomer','customerprospect',1).'</td>';
print '<td class="maxwidthonsmartphone">';
$nothingvalue=0;
$prospectonly=2;
if (! empty($conf->global->SOCIETE_DISABLE_PROSPECTS))
{
    print '<input type="hidden" name="client" value="3">';
    print $langs->trans("Patient");
}
else
{
    if (! empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) $nothingvalue=1;  // if feature to disable customer is on, nothing will keep value 1 in database.
    if (! empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) $prospectonly=3;  // if feature to disable customer is on, nothing will keep value 3 in database.
    print '<select class="flat" name="client" id="customerprospect">';
    if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) print '<option value="'.$prospectonly.'"'.($object->client==$prospectonly?' selected':'').'>'.$langs->trans('Prospect').'</option>';
    if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print '<option value="3"'.($object->client==3?' selected':'').'>'.$langs->trans('ProspectCustomer').'</option>';
    if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) print '<option value="1"'.($object->client==1?' selected':'').'>'.$langs->trans('Customer').'</option>';
    print '<option value="'.$nothingvalue.'"'.($object->client==$nothingvalue?' selected':'').'>'.$langs->trans('NorProspectNorCustomer').'</option>';
    print '</select>';
}
print '</td>';
print '<td width="25%">'.fieldLabel('CustomerCode','customer_code').'</td><td width="25%">';

print '<table class="nobordernopadding"><tr><td>';
if ((!$object->code_client || $object->code_client == -1) && $modCodeClient->code_auto)
{
    $tmpcode=$object->code_client;
    if (empty($tmpcode) && ! empty($object->oldcopy->code_client)) $tmpcode=$object->oldcopy->code_client; // When there is an error to update a thirdparty, the number for supplier and customer code is kept to old value.
    if (empty($tmpcode) && ! empty($modCodeClient->code_auto)) $tmpcode=$modCodeClient->getNextValue($object,0);
    print '<input type="text" name="code_client" id="customer_code" size="16" value="'.dol_escape_htmltag($tmpcode).'" maxlength="15">';
}
else if ($object->codeclient_modifiable())
{
    print '<input type="text" name="code_client" id="customer_code" size="16" value="'.$object->code_client.'" maxlength="15">';
}
else
{
    print $object->code_client;
    print '<input type="hidden" name="code_client" value="'.$object->code_client.'">';
}
print '</td><td>';
$s=$modCodeClient->getToolTip($langs,$object,0);
print $form->textwithpicto('',$s,1);
print '</td></tr></table>';

print '</td></tr>';

// Supplier
if (! empty($conf->fournisseur->enabled) && ! empty($user->rights->fournisseur->lire))
{
    print '<tr>';
    print '<td>'.fieldLabel('Supplier','fournisseur',1).'</td><td class="maxwidthonsmartphone">';
    print $form->selectyesno("fournisseur",$object->fournisseur,1);
    print '</td>';
    print '<td>'.fieldLabel('SupplierCode','supplier_code').'</td><td>';

    print '<table class="nobordernopadding"><tr><td>';
    if ((!$object->code_fournisseur || $object->code_fournisseur == -1) && $modCodeFournisseur->code_auto)
    {
        $tmpcode=$object->code_fournisseur;
        if (empty($tmpcode) && ! empty($object->oldcopy->code_fournisseur)) $tmpcode=$object->oldcopy->code_fournisseur; // When there is an error to update a thirdparty, the number for supplier and customer code is kept to old value.
        if (empty($tmpcode) && ! empty($modCodeFournisseur->code_auto)) $tmpcode=$modCodeFournisseur->getNextValue($object,1);
        print '<input type="text" name="code_fournisseur" id="supplier_code" size="16" value="'.dol_escape_htmltag($tmpcode).'" maxlength="15">';
    }
    else if ($object->codefournisseur_modifiable())
    {
        print '<input type="text" name="code_fournisseur" id="supplier_code" size="16" value="'.$object->code_fournisseur.'" maxlength="15">';
    }
    else
    {
        print $object->code_fournisseur;
        print '<input type="hidden" name="code_fournisseur" value="'.$object->code_fournisseur.'">';
    }
    print '</td><td>';
    $s=$modCodeFournisseur->getToolTip($langs,$object,1);
    print $form->textwithpicto('',$s,1);
    print '</td></tr></table>';

    print '</td></tr>';
}

// Status
print '<tr><td>'.fieldLabel('Status','status').'</td><td colspan="3">';
print $form->selectarray('status', array('0'=>$langs->trans('ActivityCeased'),'1'=>$langs->trans('InActivity')),1);
print '</td></tr>';

// Barcode
if ($conf->global->MAIN_MODULE_BARCODE)
{
    print '<tr><td class="tdtop">'.$langs->trans('Gencod').'</td><td colspan="3"><input type="text" name="barcode" value="'.$object->barcode.'">';
    print '</td></tr>';
}

// Address
print '<tr><td class="tdtop">'.$langs->trans('Address').'</td><td colspan="3"><textarea name="address" class="quatrevingtpercent" rows="3" wrap="soft">';
print $object->address;
print '</textarea></td></tr>';

// Zip / Town
print '<tr><td>'.$langs->trans('Zip').'</td><td>';
print $formcompany->select_ziptown($object->zip,'zipcode',array('town','selectcountry_id','departement_id'),6);
print '</td><td>'.$langs->trans('Town').'</td><td>';
print $formcompany->select_ziptown($object->town,'town',array('zipcode','selectcountry_id','departement_id'));
print '</td></tr>';

// Country
print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">';
print $form->select_country($object->country_id,'country_id');
if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
print '</td></tr>';

// State
if (empty($conf->global->SOCIETE_DISABLE_STATE))
{
    print '<tr><td>'.$langs->trans('State').'</td><td colspan="3">';
    $formcompany->select_departement($object->state_id,$object->country_code);
    print '</td></tr>';
}

// Phone / Fax
print '<tr><td>'.$langs->trans('PhonePerso').'</td><td><input type="text" name="phone" value="'.$object->phone.'"></td>';
print '<td>'.$langs->trans('PhoneMobile').'</td><td><input type="text" name="fax" value="'.$object->fax.'"></td></tr>';

// EMail / Web
print '<tr><td>'.$langs->trans('EMail').($conf->global->SOCIETE_MAIL_REQUIRED?'*':'').'</td><td colspan="3"><input type="text" name="email" size="32" value="'.$object->email.'"></td>';
print '</tr>';

// Prof ids
$i=1; $j=0;
while ($i <= 6)
{
    $key='CABINETMED_SHOW_PROFID'.$i;
	if (empty($conf->global->$key)) { $i++; continue; }

	$idprof=$langs->transcountry('ProfId'.$i,$object->country_code);
	if ($idprof!='-')
	{
		$key='idprof'.$i;

		if (($j % 2) == 0) print '<tr>';

		$idprof_mandatory ='SOCIETE_IDPROF'.($i).'_MANDATORY';
		if(empty($conf->global->$idprof_mandatory))
			print '<td><label for="'.$key.'">'.$idprof.'</label></td><td>';
		else
			print '<td><span class="fieldrequired"><label for="'.$key.'">'.$idprof.'</label></td><td>';

		print $formcompany->get_input_id_prof($i,$key,$object->$key,$object->country_code);
		print '</td>';
		if (($j % 2) == 1) print '</tr>';
		$j++;
	}
	$i++;
}
if ($j % 2 == 1) print '<td colspan="2"></td></tr>';
/*
print '<tr>';
// Height
$idprof=$langs->trans('HeightPeople');
print '<td>'.$idprof.'</td><td>';
print '<input type="text" name="idprof1" size="6" maxlength="6" value="'.$object->idprof1.'">';
print '</td>';
// Weight
$idprof=$langs->trans('Weight');
print '<td>'.$idprof.'</td><td>';
print '<input type="text" name="idprof2" size="6" maxlength="6" value="'.$object->idprof2.'">';
print '</td>';
print '</tr>';
print '<tr>';
// Date ot birth
$idprof=$langs->trans('DateToBirth');
print '<td>'.$idprof.'</td><td colspan="3">';
print '<input type="text" name="idprof3" size="18" maxlength="32" value="'.$object->idprof3.'"> ('.$conf->format_date_short_java.')';
print '</td>';
print '</tr>';
*/

// Num secu
print '<tr>';
print '<td class="nowrap">'.$langs->trans('PatientVATIntra').'</td>';
print '<td class="nowrap" colspan="3">';
$s ='<input type="text" class="flat" name="tva_intra" size="18" maxlength="20" value="'.$object->tva_intra.'">';
print $s;
print '</td></tr>';

// Sexe
print '<tr><td>'.$langs->trans("Gender").'</td><td colspan="3">';
print $form->selectarray("typent_id",$formcompany->typent_array(0, "AND code in ('TE_UNKNOWN', 'TE_HOMME', 'TE_FEMME')"), $object->typent_id);
if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
print '</td>';
print '</tr>';

print '<tr><td>'.$langs->trans('ActivityBranch').'</td><td colspna="3">';
print $formcompany->select_juridicalstatus($object->forme_juridique_code, $object->country_code, "AND (f.module = 'cabinetmed' OR f.code > '100000')");
print '</td>';
print '</tr>';

// Default language
if (! empty($conf->global->MAIN_MULTILANGS))
{
	print '<tr><td>'.$langs->trans("DefaultLang").'</td><td colspan="3">'."\n";
	print $formadmin->select_language($object->default_lang,'default_lang',0,0,1);
	print '</td>';
	print '</tr>';
}


// Categories
if (! empty($conf->categorie->enabled)  && ! empty($user->rights->categorie->lire))
{
    // Customer
    if ($object->prospect || $object->client) {
        print '<tr><td>' . fieldLabel('CustomersCategoriesShort', 'custcats') . '</td>';
        print '<td colspan="3">';
        $cate_arbo = $form->select_all_categories(Categorie::TYPE_CUSTOMER, null, null, null, null, 1);
        $c = new Categorie($db);
        $cats = $c->containing($object->id, Categorie::TYPE_CUSTOMER);
        foreach ($cats as $cat) {
            $arrayselected[] = $cat->id;
        }
        print $form->multiselectarray('custcats', $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
        print "</td></tr>";
    }

    // Supplier
    if ($object->fournisseur) {
        print '<tr><td>' . fieldLabel('SuppliersCategoriesShort', 'suppcats') . '</td>';
        print '<td colspan="3">';
        $cate_arbo = $form->select_all_categories(Categorie::TYPE_SUPPLIER, null, null, null, null, 1);
        $c = new Categorie($db);
        $cats = $c->containing($object->id, Categorie::TYPE_SUPPLIER);
        foreach ($cats as $cat) {
            $arrayselected[] = $cat->id;
        }
        print $form->multiselectarray('suppcats', $cate_arbo, $arrayselected, '', 0, '', 0, '90%');
        print "</td></tr>";
    }
}

// Other attributes
$parameters=array('colspan' => ' colspan="3"', 'colspanvalue' => '3');
$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
if (empty($reshook) && ! empty($extrafields->attribute_label))
{
    print $object->showOptionals($extrafields,'edit');
}

// Webservices url/key
if (!empty($conf->syncsupplierwebservices->enabled)) {
    print '<tr><td>'.fieldLabel('WebServiceURL','webservices_url').'</td>';
    print '<td><input type="text" name="webservices_url" id="webservices_url" size="32" value="'.$object->webservices_url.'"></td>';
    print '<td>'.fieldLabel('WebServiceKey','webservices_key').'</td>';
    print '<td><input type="text" name="webservices_key" id="webservices_key" size="32" value="'.$object->webservices_key.'"></td></tr>';
}

// Logo
print '<tr class="hideonsmartphone">';
print '<td>'.fieldLabel('Logo','photoinput').'</td>';
print '<td colspan="3">';
if ($object->logo) print $form->showphoto('societe',$object);
$caneditfield=1;
if ($caneditfield)
{
    if ($object->logo) print "<br>\n";
    print '<table class="nobordernopadding">';
    if ($object->logo) print '<tr><td><input type="checkbox" class="flat" name="deletephoto photodelete" id="photodelete"> '.$langs->trans("Delete").'<br><br></td></tr>';
    //print '<tr><td>'.$langs->trans("PhotoFile").'</td></tr>';
    print '<tr><td><input type="file" class="flat" name="photo" id="photoinput"></td></tr>';
    print '</table>';
}
print '</td>';
print '</tr>';

print '</table>';

dol_fiche_end();

print '<center>';
print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
print ' &nbsp; &nbsp; ';
print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
print '</center>';

print '</form>';
?>

<!-- END PHP TEMPLATE -->
