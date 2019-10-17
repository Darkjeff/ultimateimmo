<?php
/* Copyright (C) 2001-2006  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005       Marc Barilley / Ocebo   <marc@ocebo.com>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2007       Franky Van Liedekerke   <franky.van.liedekerke@telenet.be>
 * Copyright (C) 2012       Cédric Salvador         <csalvador@gpcsolutions.fr>
 * Copyright (C) 2014       Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2014       Teddy Andreotti         <125155@supinfo.com>
 * Copyright (C) 2015       Juanjo Menent           <jmenent@2byte.es>
 * Copyright (C) 2018-2019  Frédéric France         <frederic.france@netlogic.fr>
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
 *	\file       htdocs/compta/paiement.php
 *	\ingroup    facture
 *	\brief      Payment page for customers invoices
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

dol_include_once('/ultimateimmo/class/immopayment.class.php');
dol_include_once('/ultimateimmo/class/immoreceipt.class.php');
dol_include_once('/ultimateimmo/class/immoproperty.class.php');
dol_include_once('/ultimateimmo/class/immorenter.class.php');
dol_include_once('/ultimateimmo/class/immorent.class.php');
dol_include_once('/ultimateimmo/class/immoowner.class.php');
dol_include_once('/ultimateimmo/lib/immopayment.lib.php');
//require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

// Load translation files required by the page
$langs->loadLangs(array('companies', 'bills', 'banks'));

$id			= GETPOST('id', 'int');
$ref		= GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$confirm	= GETPOST('confirm', 'alpha');
$recid		= GETPOST('recid', 'int');

$accountid	= GETPOST('accountid', 'int');
$paymentnum	= GETPOST('num_paiement', 'alpha');
$socid      = GETPOST('socid', 'int');

$sortfield	= GETPOST('sortfield', 'alpha');
$sortorder	= GETPOST('sortorder', 'alpha');
$page		= GETPOST('page', 'int');

$amounts=array();
$amountsresttopay=array();
$addwarning=0;

$object = new ImmoPayment($db);
//$staticPaiement = new Paiement($db);

$receipt=new ImmoReceipt($db);
$receipt->fetch($recid);

$renter=new ImmoRenter($db);
$renter->fetch($receipt->fk_renter);

$owner=new ImmoOwner($db);
$owner->fetch($receipt->fk_owner);

$rent=new ImmoRent($db);
$rent->fetch($receipt->fk_rent);

$property=new ImmoProperty($db);
$property->fetch($receipt->fk_property);

// Security check
if ($renter->fk_soc > 0)
{
    $socid = $renter->fk_soc;
}
$usercanread = $user->rights->ultimateimmo->read;
$usercancreate = $user->rights->ultimateimmo->write;
$usercandelete = $user->rights->ultimateimmo->delete || ($usercancreate && $object->status == 0);

// Load object
if ($recid > 0)
{
	$ret=$receipt->fetch($recid);
}
//var_dump($_POST);
// Initialize technical object to manage hooks of paiements. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('paiementcard','globalcard'));


/*
 * Actions
 */

$parameters=array('socid'=>$socid);
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	if ($action == 'add_paiement' || ($action == 'confirm_paiement' && $confirm == 'yes'))
	{
	    $error = 0;

	    $date_payment = dol_mktime(12, 0, 0, GETPOST('remonth', 'int'), GETPOST('reday', 'int'), GETPOST('reyear', 'int'));
	    $paiement_id = 0;
	    $totalpayment = 0;
	    $atleastonepaymentnotnull = 0;

	    // Generate payment array and check if there is payment higher than invoice and payment date before invoice date
	    $tmpreceipt=new ImmoReceipt($db);
	    foreach ($_POST as $key => $value)
	    {
			if (substr($key, 0, 7) == 'amount_' && GETPOST($key) != '')
	        {	
						
	            $cursorrecid = substr($key, 7);
				
	            $amounts[$cursorrecid] = price2num(trim(GETPOST($key)));
	            $totalpayment = $totalpayment + $amounts[$cursorrecid];
				//var_dump($totalpayment);
	            if (! empty($amounts[$cursorrecid])) $atleastonepaymentnotnull++;
	            $result=$tmpreceipt->fetch($cursorrecid);
	            if ($result <= 0) dol_print_error($db);
	            $amountsresttopay[$cursorrecid]=price2num($tmpreceipt->total_amount  - $tmpreceipt->getSommePaiement());
	            if ($amounts[$cursorrecid])
	            {
		            // Check amount
		            if ($amounts[$cursorrecid] && (abs($amounts[$cursorrecid]) > abs($amountsresttopay[$cursorrecid])))
		            {
		                $addwarning=1;
		                $formquestion['text'] = img_warning($langs->trans("PaymentHigherThanReminderToPay")).' '.$langs->trans("HelpPaymentHigherThanReminderToPay");
		            }
		            // Check date
		            if ($date_payment && ($date_payment < $tmpreceipt->date))
		            {
		            	$langs->load("errors");
		                //$error++;
		                setEventMessages($langs->transnoentities("WarningPaymentDateLowerThanInvoiceDate", dol_print_date($date_payment, 'day'), dol_print_date($tmpreceipt->date, 'day'), $tmpreceipt->ref), null, 'warnings');
		            }
	            }

	            $formquestion[$i++]=array('type' => 'hidden','name' => $key,  'value' => $_POST[$key]);
	        }
	    }

	    // Check parameters
	    if (! GETPOST('fk_mode_reglement'))
	    {
	        setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('TypePayment')), null, 'errors');
	        $error++;
	    }

	    if (! empty($conf->banque->enabled))
	    {
	        // If bank module is on, account is required to enter a payment
	        if (GETPOST('accountid') <= 0)
	        {
	            setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('AccountToCredit')), null, 'errors');
	            $error++;
	        }
	    }

	    if (empty($totalpayment) && empty($atleastonepaymentnotnull))
	    {
	        setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->trans('PaymentAmount')), null, 'errors');
	        $error++;
	    }

	    if (empty($date_payment))
	    {
	        setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Date')), null, 'errors');
	        $error++;
	    }

		// Check if payments in both currency
		/*if ($totalpayment > 0)
		{
			setEventMessages($langs->transnoentities('ErrorPaymentInBothCurrency'), null, 'errors');
	        $error++;
		}*/
	}

	/*
	 * Action add_paiement
	 */
	if ($action == 'add_paiement')
	{
	    if ($error)
	    {
	        $action = 'create';
	    }
	    // Le reste propre a cette action s'affiche en bas de page.
	}

	/*
	 * Action confirm_paiement
	 */
	if ($action == 'confirm_paiement' && $confirm == 'yes')
	{
	    $error=0;

	    $date_payment = dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));

	    $db->begin();

	    $thirdparty = new Societe($db);
	    if ($socid > 0) $thirdparty->fetch($socid);

	    // Clean parameters amount if payment is for a credit note
	    foreach ($amounts as $key => $value)	// How payment is dispatched
	    {
	        $tmpreceipt = new ImmoReceipt($db);
	        $tmpreceipt->fetch($key);
	        if ($tmpreceipt->type == ImmoReceipt::TYPE_CREDIT_NOTE)
	        {
	            $newvalue = price2num($value, 'MT');
	            $amounts[$key] = - abs($newvalue);
	        }
	    }

	    if (! empty($conf->banque->enabled))
	    {
	    	// Si module bank actif, un compte est obligatoire lors de la saisie d'un paiement
	    	if (GETPOST('accountid', 'int') <= 0)
	    	{
	    		setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentities('AccountToCredit')), null, 'errors');
	    		$error++;
	    	}
	    }

	    // Creation of payment line
	    $paiement = new ImmoPayment($db);
		//var_dump($paiement);exit;
	    $paiement->date_payment = $date_payment;
	    $paiement->amounts      = $amounts;   // Array with all payments dispatching with invoice id
	    //$paiement->fk_paiement   = dol_getIdFromCode($db, GETPOST('fk_mode_reglement'), 'c_paiement', 'code', 'id', 1);
	    $paiement->num_payment  = GETPOST('num_payment', 'alpha');
	    $paiement->note_public  = GETPOST('note_public', 'alpha');
		$paiement->ref  = GETPOST('ref', 'alpha');
		$paiement->fk_mode_reglement  = GETPOST('fk_mode_reglement', 'alpha');
		$paiement->check_transmitter  = GETPOST('check_transmitter', 'alpha');
		$paiement->chequebank  = GETPOST('chequebank', 'alpha');

	    if (! $error)
	    {
	        // Create payment and update this->multicurrency_amounts if this->amounts filled or
	        // this->amounts if this->multicurrency_amounts filled.
	        $paiement_id = $paiement->create($user, (GETPOST('closepaidreceipts')=='on'?1:0), $thirdparty);    // This include closing invoices and regenerating documents
			//var_dump($paiement_id);exit;
	    	if ($paiement_id < 0)
	        {
	            setEventMessages($paiement->error, $paiement->errors, 'errors');
	            $error++;
	        }
	    }

	    if (! $error)
	    {
	    	$label='(CustomerReceiptPayment)';
	    	if (GETPOST('type') == ImmoReceipt::TYPE_CREDIT_NOTE) $label='(CustomerReceiptPaymentBack)';  // Refund of a credit note
	        $result=$paiement->addPaymentToBank($user, 'payment', $label, GETPOST('accountid'), GETPOST('chqemetteur'), GETPOST('chqbank'));
	        if ($result < 0)
	        {
	            setEventMessages($paiement->error, $paiement->errors, 'errors');
	            $error++;
	        }
	    }

	    if (! $error)
	    {
	        $db->commit();

	        // If payment dispatching on more than one invoice, we stay on summary page, otherwise jump on invoice card
	        $receiptid=0;
			
	        foreach ($paiement->amounts as $key => $amount)
	        {
	            $recid = $key;
	            if (is_numeric($amount) && $amount <> 0)
	            {
	                if ($receiptid != 0) $receiptid=-1; // There is more than one invoice payed by this payment
	                else $receiptid=$recid;
	            }
	        }
	        if ($receiptid > 0) $loc = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1).'?recid=' .$receiptid;		
	        else $loc = DOL_URL_ROOT.'/compta/paiement/card.php?id='.$paiement_id;
	        header('Location: '.$loc);
	        exit;
	    }
	    else
	    {
	        $db->rollback();
	    }
	}
}


/*
 * View
 */

$form=new Form($db);

llxHeader('', $langs->trans("Payment"));


if ($action == 'create' || $action == 'confirm_paiement' || $action == 'add_paiement')
{
	$receipt = new ImmoReceipt($db);
	$result = $receipt->fetch($recid);
	
	$paiement = new ImmoPayment($db);
	$paiement->fetch($receipt->fk_payment);

	if ($result >= 0)
	{		
		//$ret = $paiement->fetch_thirdparty();
		$title='';
		if ($receipt->type != ImmoReceipt::TYPE_CREDIT_NOTE) $title.=$langs->trans("EnterPaymentReceivedFromCustomer");
		if ($receipt->type == ImmoReceipt::TYPE_CREDIT_NOTE) $title.=$langs->trans("EnterPaymentDueToCustomer");
		print load_fiche_titre($title);

		// Initialize data for confirmation (this is used because data can be change during confirmation)
		if ($action == 'add_paiement')
		{
			$i=0;

			$formquestion[$i++]=array('type' => 'hidden','name' => 'recid', 'value' => $receipt->id);
			$formquestion[$i++]=array('type' => 'hidden','name' => 'socid', 'value' => $receipt->socid);
			$formquestion[$i++]=array('type' => 'hidden','name' => 'type',  'value' => $receipt->type);
		}

		// Invoice with Paypal transaction
		// TODO add hook possibility (regis)
		if (! empty($conf->paypalplus->enabled) && $conf->global->PAYPAL_ENABLE_TRANSACTION_MANAGEMENT && ! empty($receipt->ref_int))
		{
			if (! empty($conf->global->PAYPAL_BANK_ACCOUNT)) $accountid=$conf->global->PAYPAL_BANK_ACCOUNT;
			$paymentnum=$paiement->ref;
		}

		// Add realtime total information
		if (! empty($conf->use_javascript_ajax))
		{
			print "\n".'<script type="text/javascript" language="javascript">';
			print '$(document).ready(function () {
            			setPaiementCode();

            			$("#selectpaiementcode").change(function() {
            				setPaiementCode();
            			});

            			function setPaiementCode()
            			{
            				var code = $("#selectpaiementcode option:selected").val();

                            if (code == \'CHQ\' || code == \'VIR\')
            				{
            					if (code == \'CHQ\')
			                    {
			                        $(\'.fieldrequireddyn\').addClass(\'fieldrequired\');
			                    }
            					if ($(\'#fieldchqemetteur\').val() == \'\')
            					{
            						var emetteur = ('.$receipt->type.' == '.ImmoReceipt::TYPE_CREDIT_NOTE.') ? \''.dol_escape_js(dol_escape_htmltag($conf->global->MAIN_INFO_SOCIETE_NOM)).'\' : jQuery(\'#thirdpartylabel\').val();
            						$(\'#fieldchqemetteur\').val(emetteur);
            					}
            				}
            				else
            				{
            					$(\'.fieldrequireddyn\').removeClass(\'fieldrequired\');
            					$(\'#fieldchqemetteur\').val(\'\');
            				}
            			}

						function _elemToJson(selector)
						{
							var subJson = {};
							$.map(selector.serializeArray(), function(n,i)
							{
								subJson[n["name"]] = n["value"];
							});

							return subJson;
						}
						function callForResult(imgId)
						{
							var json = {};
							var form = $("#payment_form");

							json["invoice_type"] = $("#invoice_type").val();
            				json["amountPayment"] = $("#amountpayment").attr("value");
							json["amounts"] = _elemToJson(form.find("input.amount"));
							json["remains"] = _elemToJson(form.find("input.remain"));

							if (imgId != null) {
								json["imgClicked"] = imgId;
							}

							$.post("'.DOL_URL_ROOT.'/compta/ajaxpayment.php", json, function(data)
							{
								json = $.parseJSON(data);

								form.data(json);

								for (var key in json)
								{
									if (key == "result")	{
										if (json["makeRed"]) {
											$("#"+key).addClass("error");
										} else {
											$("#"+key).removeClass("error");
										}
										json[key]=json["label"]+" "+json[key];
										$("#"+key).text(json[key]);
									} else {console.log(key);
										form.find("input[name*=\""+key+"\"]").each(function() {
											$(this).attr("value", json[key]);
										});
									}
								}
							});
						}
						$("#payment_form").find("input.amount").change(function() {
							callForResult();
						});
						$("#payment_form").find("input.amount").keyup(function() {
							callForResult();
						});
			';

			print '	});'."\n";
		}

		//Add js for AutoFill
		if (! empty($conf->use_javascript_ajax))
		{
			print "\n".'<script type="text/javascript" language="javascript">';
			print ' $(document).ready(function () {';
			print ' 	$(".AutoFillAmout").on(\'click touchstart\', function(){
							$("input[name="+$(this).data(\'rowname\')+"]").val($(this).data("value")).trigger("change");
						});';
			print '	});'."\n";

			print '	</script>'."\n";
		}

		print '<form id="payment_form" name="add_paiement" action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="add_paiement">';
		print '<input type="hidden" name="recid" value="'.$recid.'">';
		print '<input type="hidden" name="socid" value="'.$renter->fk_soc.'">';
		print '<input type="hidden" name="thirdpartylabel" id="thirdpartylabel" value="'.dol_escape_htmltag($renter->thirdparty->name).'">';

		dol_fiche_head();
		$result=$renter->fetch_thirdparty();
		
		print '<table class="border" width="100%">';
		
		$object = new ImmoPayment($db);
		$object->fetch($receipt->fk_payment);
		
		// Common attributes
		$object->fields = dol_sort_array($object->fields, 'position');
		$tab = $object->fields;
		unset($tab['amount'], $tab['status']);
		foreach($tab as $key => $val)
		{		
			$array = array_diff_key($tab, ['fk_rent' => $key, 'fk_receipt' => $key, 'fk_renter' => $key, 'fk_owner' => $key, 'fk_property' => $key]);
			//var_dump($array);
			// Discard if extrafield is a hidden field on form
			if (abs($val['visible']) != 1) continue;
			
			if (array_key_exists('enabled', $val) && isset($val['enabled']) && ! $val['enabled']) continue;	// We don't want this field

			print '<tr id="field_'.$key.'">';
			print '<td';
			print ' class="titlefieldcreate';
			if ($val['notnull'] > 0) print ' fieldrequired';
			if ($val['type'] == 'text' || $val['type'] == 'html') print ' tdtop';

			print '"';
			print '>';
			print $langs->trans($val['label']);
			print '</td>';
			print '<td>';

			if ($val['label'] == 'Ref')
			{			
				// Reference
				$tmpref = GETPOST('ref','alpha')?GETPOST('ref','alpha'):"(PROV)".$recid;
				print $tmpref;
			}
			elseif ($val['label'] == 'Renter')
			{
				//fk_renter
				print $renter->getNomUrl(4);
			}
			elseif ($val['label'] == 'DatePayment')
			{
				// DateCreation
				$datepayment = dol_mktime(12, 0, 0, $_POST['remonth'], $_POST['reday'], $_POST['reyear']);
				$datepayment= ($datepayment == '' ? (empty($conf->global->MAIN_AUTOFILL_DATE)?-1:'') : $datepayment);
				print $form->selectDate($datepayment, '', '', '', 0, "add_paiement", 1, 1, 0, '', '', $receipt->date);
			}
			elseif ($val['label'] == 'TypePayment')
			{
				// Payment mode
				$form->select_types_paiements((GETPOST('fk_mode_reglement')?GETPOST('fk_mode_reglement'):$paiement->fk_mode_reglement), 'fk_mode_reglement', '', 2);
			}
			elseif ($val['label'] == 'BankAccount')
			{
				//BankAccount
				if (! empty($conf->banque->enabled))
				{
					if ($receipt->type != 2) print '<span class="fieldrequired">'.$langs->trans('AccountToCredit').'</span>';
					if ($receipt->type == 2) print '<span class="fieldrequired">'.$langs->trans('AccountToDebit').'</span>';
					
					$form->select_comptes($accountid, 'accountid', 0, '', 2);
					
				}
			}
			elseif ($val['label'] == 'ThirdParty')
			{
				// ThirdParty
				print $renter->thirdparty->getNomUrl(4);
			}
			elseif ($val['label'] == 'Contract')
			{
				// Contract
				print $rent->getNomUrl(4);
			}
			elseif ($val['label'] == 'ImmoReceipt')
			{
				// ImmoReceipt
				print $receipt->getNomUrl(4);
			}
			elseif ($val['label'] == 'Owner')
			{
				// Owner
				print $owner->getNomUrl(4);
			}
			elseif ($val['label'] == 'Property')
			{
				// Property
				print $property->getNomUrl(4);
			}
			else
			{
				if (in_array($val['type'], array('int', 'integer'))) $value = GETPOST($key, 'int');
				elseif ($val['type'] == 'text' || $val['type'] == 'html') $value = GETPOST($key, 'none');
				else $value = GETPOST($key, 'alpha');
				print $object->showInputField($val, $key, $value, '', '', '', 0);
			}
			print '</td>';
			print '</tr>';
		}
		// Reference
		//$tmpref= GETPOST('ref','alpha')?GETPOST('ref','alpha'):"(PROV)".$recid;
        //print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans('Reference').'</span></td><td>'.$tmpref."</td></tr>\n";
	
        // Third party
       // print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans('Renter').'</span></td><td>'.$renter->thirdparty->getNomUrl(4)."</td></tr>\n";

        // Date payment
       /* print '<tr><td><span class="fieldrequired">'.$langs->trans('Date').'</span></td><td>';
        $datepayment = dol_mktime(12, 0, 0, $_POST['remonth'], $_POST['reday'], $_POST['reyear']);
        $datepayment= ($datepayment == '' ? (empty($conf->global->MAIN_AUTOFILL_DATE)?-1:'') : $datepayment);
        print $form->selectDate($datepayment, '', '', '', 0, "add_paiement", 1, 1, 0, '', '', $receipt->date);
        print '</td></tr>';*/      

        // Bank account
       /* print '<tr>';
        if (! empty($conf->banque->enabled))
        {
            if ($receipt->type != 2) print '<td><span class="fieldrequired">'.$langs->trans('AccountToCredit').'</span></td>';
            if ($receipt->type == 2) print '<td><span class="fieldrequired">'.$langs->trans('AccountToDebit').'</span></td>';
            print '<td>';
            $form->select_comptes($accountid, 'accountid', 0, '', 2);
            print '</td>';
        }
        else
        {
            print '<td>&nbsp;</td>';
        }
        print "</tr>\n";*/

        // Cheque number
        /*print '<tr><td>'.$langs->trans('Numero');
        print ' <em>('.$langs->trans("ChequeOrTransferNumber").')</em>';
        print '</td>';
        print '<td><input name="num_paiement" type="text" value="'.$paymentnum.'"></td></tr>';

        // Check transmitter
        print '<tr><td class="'.(GETPOST('fk_mode_reglement')=='CHQ'?'fieldrequired ':'').'fieldrequireddyn">'.$langs->trans('CheckTransmitter');
        print ' <em>('.$langs->trans("ChequeMaker").')</em>';
        print '</td>';
        print '<td><input id="fieldchqemetteur" name="chqemetteur" size="30" type="text" value="'.GETPOST('chqemetteur', 'alphanohtml').'"></td></tr>';

        // Bank name
        print '<tr><td>'.$langs->trans('Bank');
        print ' <em>('.$langs->trans("ChequeBank").')</em>';
        print '</td>';
        print '<td><input name="chqbank" size="30" type="text" value="'.GETPOST('chqbank', 'alphanohtml').'"></td></tr>';*/
	

		// Comments
		/*print '<tr><td>'.$langs->trans('Comments').'</td>';
		print '<td class="tdtop">';
		print '<textarea name="comment" wrap="soft" class="quatrevingtpercent" rows="'.ROWS_3.'">'.GETPOST('comment', 'none').'</textarea></td></tr>';*/

        print '</table>';

		dol_fiche_end();


        /*
         * List of unpaid receipts
         */

        $sql = 'SELECT DISTINCT f.rowid as recid, f.ref, f.total_amount,';
		$sql.= ' p.rowid, p.fk_receipt, p.date_payment as dp, p.amount,';
        $sql.= ' f.date_creation as dc, f.fk_soc as socid';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ultimateimmo_immoreceipt as f';
		$sql.= ', '.MAIN_DB_PREFIX.'ultimateimmo_immopayment as p';
		$sql.= ' WHERE p.fk_receipt = f.rowid';
		$sql.= ' AND f.entity IN ('.getEntity($object->element).')';
        $sql.= ' AND f.fk_soc = '.$socid;
		
		// Can pay receipts of all child of parent company
		/*if(!empty($conf->global->FACTURE_PAYMENTS_ON_DIFFERENT_THIRDPARTIES_BILLS) && !empty($receipt->thirdparty->parent)) {
			$sql.= ' OR f.fk_soc IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'societe WHERE parent = '.$receipt->thirdparty->parent.')';
		}
		// Can pay receipts of all child of myself
		if(!empty($conf->global->FACTURE_PAYMENTS_ON_SUBSIDIARY_COMPANIES)){
			$sql.= ' OR f.fk_soc IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'societe WHERE parent = '.$receipt->thirdparty->id.')';
		}
        $sql.= ' AND f.paye = 0';
        $sql.= ' AND f.fk_statut = 1'; // Statut=0 => not validated, Statut=2 => canceled
       */
	    $sql.=' GROUP BY f.rowid';
        // Sort invoices by date and serial number: the older one comes first
        $sql.=' ORDER BY f.date_creation ASC, f.ref ASC';

        $resql = $db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);
            if ($num > 0)
            {
				$arraytitle=$langs->trans('ImmoReceipt');
				if ($receipt->type == 2) $arraytitle=$langs->trans("CreditNotes");
				$alreadypayedlabel=$langs->trans('Received');
				if ($receipt->type == 2) { $alreadypayedlabel=$langs->trans("PaidBack"); }
				$remaindertopay=$langs->trans('RemainderToTake');
				if ($receipt->type == 2) { $remaindertopay=$langs->trans("RemainderToPayBack"); }

                $i = 0;
                //print '<tr><td colspan="3">';
                print '<br>';
                print '<table class="noborder" width="100%">';
				
                print '<tr class="liste_titre">';
				//print '<td align="left">'.$langs->trans('Ref.paiement').'</td>';
                print '<td>'.$arraytitle.'</td>';
                print '<td align="center">'.$langs->trans('Date').'</td>';
                print '<td align="center">'.$langs->trans('DateMaxPayment').'</td>';
                print '<td class="right">'.$langs->trans('AmountTTC').'</td>';
                print '<td class="right">'.$alreadypayedlabel.'</td>';
                print '<td class="right">'.$remaindertopay.'</td>';
                print '<td class="right">'.$langs->trans('PaymentAmount').'</td>';
                print '<td class="right">&nbsp;</td>';
                print "</tr>\n";

                $total=0;
                $totalrecu=0;
                $totalrecucreditnote=0;
                $totalrecudeposits=0;

                while ($i < $num)
                {
                    $objp = $db->fetch_object($resql);

                    $sign=1;
                    if ($receipt->type == ImmoReceipt::TYPE_CREDIT_NOTE) $sign=-1;

					$soc = new Societe($db);
					$soc->fetch($objp->socid);
					
                    $receipt=new ImmoReceipt($db);
                    $receipt->fetch($objp->recid);
                    $paiement = $receipt->getSommePaiement();

					print '<tr class="oddeven">';
					/*print '<td>';
					$payment->ref = $payment->id.'_'.$receipt->ref;
					print $payment->getNomUrl(1, '');
					print "</td>\n";*/

					print '<td>';
                    print $receipt->getNomUrl(1, '');
                    if ($objp->socid != $receipt->thirdparty->id) print ' - '.$soc->getNomUrl(1).' ';
                    print "</td>\n";

                    // Date
                   	print '<td align="center">'.dol_print_date($db->jdate($objp->dc), 'day')."</td>\n";

                    // Due date
                    if ($objp->dlr > 0 )
                    {
                        print '<td align="center">';
                        print dol_print_date($db->jdate($objp->dlr), 'day');

                        if ($receipt->hasDelay())
                        {
                            print img_warning($langs->trans('Late'));
                        }

                        print '</td>';
                    }
                    else
                    {
                        print '<td align="center"></td>';
                    }

					// Price
                    print '<td class="right" '.(($receipt->id==$recid)?' style="font-weight: bold" ':'').'>'.price($sign * $objp->total_amount ).'</td>';

                    // Received or paid back
					$payment = new ImmoPayment($db);
					$result = $payment->fetch($objp->rowid);
					//var_dump($payment);exit;
                    $alreadypayed=price2num($paiement, 'MT');
                    $remaintopay=price2num($receipt->total_amount - $paiement, 'MT');
                    //print '<td class="right">'.price($sign * $paiement);
					print '<td class="right">'.price($sign * $alreadypayed);
                    if ($deposits) print '+'.price($deposits);
                    print '</td>';

                    // Remain to take or to pay back
                    print '<td class="right">'.price($sign * $remaintopay).'</td>';
                   // $test= price(price2num($objp->total_amount  - $paiement - $deposits));

                    // Amount
                    print '<td class="right nowraponall">';

                    // Add remind amount
                    $namef = 'amount_'.$objp->recid;
                    $nameRemain = 'remain_'.$objp->recid;
					//var_dump($_POST["rowid"]);exit;
					
                    if ($action != 'add_paiement')
                    {
                        if (!empty($conf->use_javascript_ajax))
							print img_picto("Auto fill", 'rightarrow', "class='AutoFillAmout' data-rowname='".$namef."' data-value='".($sign * $remaintopay)."'");
                        print '<input type="text" class="maxwidth75 amount" name="'.$namef.'" value="'.dol_escape_htmltag(GETPOST($namef)).'">';
                        print '<input type="hidden" class="remain" name="'.$nameRemain.'" value="'.$remaintopay.'">';
                    }
                    else
                    {						
                        print '<input type="text" class="maxwidth75" name="'.$namef.'_disabled" value="'.dol_escape_htmltag(GETPOST($namef)).'" disabled>';
                        print '<input type="hidden" name="'.$namef.'" value="'.dol_escape_htmltag(GETPOST($namef)).'">';
                    }
                    print "</td>";

                    // Warning
                    print '<td align="center" width="16">';
                    //print "xx".$amounts[$receipt->id]."-".$amountsresttopay[$receipt->id]."<br>";
                    if ($amounts[$receipt->id] && (abs($amounts[$receipt->id]) > abs($amountsresttopay[$receipt->id])))
                    {
                        print ' '.img_warning($langs->trans("PaymentHigherThanReminderToPay"));
                    }
                    print '</td>';

					$parameters=array();
					$reshook=$hookmanager->executeHooks('printObjectLine', $parameters, $objp, $action); // Note that $action and $object may have been modified by hook

                    print "</tr>\n";

                    $total+=$objp->total;
                    $total_amount +=$objp->total_amount ;
                    $totalrecu+=$paiement;
                    $totalrecudeposits+=$deposits;
                    $i++;
                }

                if ($i > 1)
                {
                    // Print total
                    print '<tr class="liste_total">';
                    print '<td colspan="4" class="left">'.$langs->trans('TotalTTC').'</td>';
					print '<td class="right"><b>'.price($sign * $total_amount ).'</b></td>';
                    print '<td class="right"><b>'.price($sign * $totalrecu);
                    if ($totalrecudeposits) print '+'.price($totalrecudeposits);
                    print '</b></td>';
                    print '<td class="right"><b>'.price($sign * price2num($total_amount  - $totalrecu - $totalrecucreditnote - $totalrecudeposits, 'MT')).'</b></td>';
                    print '<td class="right" id="result" style="font-weight: bold;"></td>';		// Autofilled
                    print '<td align="center">&nbsp;</td>';
                    print "</tr>\n";
                }
                print "</table>";
                //print "</td></tr>\n";
            }
            $db->free($resql);
        }
        else
		{
            dol_print_error($db);
        }


        // Bouton Enregistrer
        if ($action != 'add_paiement')
        {
        	$checkboxlabel=$langs->trans("ClosePaidInvoicesAutomatically");
        	if ($receipt->type == 2) $checkboxlabel=$langs->trans("ClosePaidCreditNotesAutomatically");
        	$buttontitle=$langs->trans('ToMakePayment');
        	if ($receipt->type == 2) $buttontitle=$langs->trans('ToMakePaymentBack');

        	print '<br><div class="center">';
        	print '<input type="checkbox" checked name="closepaidreceipts"> '.$checkboxlabel;
            /*if (! empty($conf->prelevement->enabled))
            {
                $langs->load("withdrawals");
                if (! empty($conf->global->WITHDRAW_DISABLE_AUTOCREATE_ONPAYMENTS)) print '<br>'.$langs->trans("IfInvoiceNeedOnWithdrawPaymentWontBeClosed");
            }*/
            print '<br><input type="submit" class="button" value="'.dol_escape_htmltag($buttontitle).'"><br><br>';
            print '</div>';
        }

        // Form to confirm payment
        if ($action == 'add_paiement')
        {
            $preselectedchoice=$addwarning?'no':'yes';
			
            print '<br>';
            if (!empty($totalpayment)) $text=$langs->trans('ConfirmCustomerPayment', $totalpayment, $langs->trans("Currency".$conf->currency));
			/*if (!empty($multicurrency_totalpayment))
			{
				$text.='<br>'.$langs->trans('ConfirmCustomerPayment', $multicurrency_totalpayment, $langs->trans("paymentInInvoiceCurrency"));
			}
            if (GETPOST('closepaidreceipts'))
            {
                $text.='<br>'.$langs->trans("AllCompletelyPayedInvoiceWillBeClosed");
                print '<input type="hidden" name="closepaidreceipts" value="'.GETPOST('closepaidreceipts').'">';
            }*/
			
            print $form->formconfirm($_SERVER['PHP_SELF'].'?recid='.$recid.'&socid='.$renter->fk_soc.'&type='.$receipt->type, $langs->trans('ReceivedCustomersPayments'), $text, 'confirm_paiement', $formquestion, $preselectedchoice);
        }

        print "</form>\n";
    }
}


/**
 *  Show list of payments
 
if (! GETPOST('action', 'aZ09'))
{
    if (empty($page) || $page == -1) $page = 0;
    $limit = GETPOST('limit', 'int')?GETPOST('limit', 'int'):$conf->liste_limit;
    $offset = $limit * $page ;

    if (! $sortorder) $sortorder='DESC';
    if (! $sortfield) $sortfield='p.date_creation';

    $sql = 'SELECT p.date_creation as dc, p.amount, f.total_amount as rec_amount, f.ref';
    $sql.=', f.rowid as recid, c.libelle as paiement_type, p.num_payment';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'ultimateimmo_immopayment as p LEFT JOIN '.MAIN_DB_PREFIX.'c_paiement as c ON p.fk_paiement = c.id';
    $sql.= ', '.MAIN_DB_PREFIX.'ultimateimmo_immoreceipt as f';
    $sql.= ' WHERE p.fk_receipt = f.rowid';
    $sql.= ' AND f.entity IN (' . getEntity($object->element).')';
    if ($socid)
    {
        $sql.= ' AND f.fk_soc = '.$socid;
    }

    $sql.= ' ORDER BY '.$sortfield.' '.$sortorder;
    $sql.= $db->plimit($limit+1, $offset);
    $resql = $db->query($sql);

    if ($resql)
    {
        $num = $db->num_rows($resql);
        $i = 0;
		
        print_barre_liste($langs->trans('Payments'), $page, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, '', $num);
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre">';
        print_liste_field_titre('Receipt', $_SERVER["PHP_SELF"], 'ref', '', '', '', $sortfield, $sortorder);
        print_liste_field_titre('Date', $_SERVER["PHP_SELF"], 'dc', '', '', '', $sortfield, $sortorder);
        print_liste_field_titre('Type', $_SERVER["PHP_SELF"], 'libelle', '', '', '', $sortfield, $sortorder);
        print_liste_field_titre('Amount', $_SERVER["PHP_SELF"], 'rec_amount', '', '', '', $sortfield, $sortorder, 'right ');
		print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'maxwidthsearch ');
        print "</tr>\n";

        while ($i < min($num, $limit))
        {
            $objp = $db->fetch_object($resql);

            print '<tr class="oddeven">';
            print '<td><a href="'.dol_buildpath('/ultimateimmo/payment/immopayment_list.php', 1).'?recid='.$objp->recid.'">'.$objp->ref."</a></td>\n";
            print '<td>'.dol_print_date($db->jdate($objp->dc))."</td>\n";
            print '<td>'.$objp->paiement_type.' '.$objp->num_paiement."</td>\n";
            print '<td class="right">'.price($objp->amount).'</td><td>&nbsp;</td>';

			$parameters=array();
			$reshook=$hookmanager->executeHooks('printObjectLine', $parameters, $objp, $action); // Note that $action and $object may have been modified by hook

            print '</tr>';
            $i++;
        }
        print '</table>';
    }
}*/

llxFooter();

$db->close();
