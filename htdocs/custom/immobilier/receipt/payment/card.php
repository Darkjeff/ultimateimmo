<?php
/* Copyright (C) 2004		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2013		Olivier Geffroy			<jeff@jeffinfo.com>
 * Copyright (C) 2016		Alexandre Spangaro		<aspangaro@zendsi.com>
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
 * \file       immobilier/receipt/payment/card.php
 * \ingroup    immobilier
 * \brief      Page to add payment on a receipt
 */
// Dolibarr environment
$res = @include ("../../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

dol_include_once("/immobilier/class/immopayment.class.php");
dol_include_once("/immobilier/class/immoreceipt.class.php");

// Langs
$langs->load("immobilier@immobilier");
$langs->load("bills");

$mesg = '';
$id = GETPOST('id', 'int');
$receipt_id = GETPOST('receipt', 'int');
$action = GETPOST('action');
$cancel = GETPOST('cancel');

// Actions
if ($action == 'add')
{
	if ($cancel)
	{
		$loc = DOL_URL_ROOT.'/custom/immobilier/receipt/card.php?id='.$chid;
		header("Location: ".$loc);
		exit;
	}

	$datepaie = @dol_mktime(0,0,0, GETPOST("paiemonth"), GETPOST("paieday"), GETPOST("paieyear"));
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie")) . '</div>';
		$action = 'create';
	} else {
		$paie = new Immopayment($db);
		
		$paie->fk_contract		= GETPOST("fk_contract");
		$paie->fk_property		= GETPOST("fk_property");
		$paie->fk_renter		= GETPOST("fk_renter");
		$paie->amount			= GETPOST("amount");
		$paie->comment			= GETPOST("comment");
		$paie->date_payment		= $datepaie;
		$paie->fk_receipt		= GETPOST("fk_receipt");
    	$paie->fk_bank			= GETPOST("accountid");
		$paie->fk_typepayment	= GETPOST("fk_typepayment");
    	$paie->num_payment		= GETPOST("num_payment");
		$paie->fk_owner			= $user->id;
		
		$id = $paie->create($user);
		header("Location: " . DOL_URL_ROOT . "/custom/immobilier/receipt/card.php?id=" . $paie->fk_receipt);
		if ($id > 0) {
		} else {
			$mesg = '<div class="error">' . $paie->error . '</div>';
		}
	}
}

/*
 *	Delete paiement
 */

if ($action == 'delete') {
	
	if ($id){
		$paie = new Immopayment($db);
		
		
		$paie->id = $id;
		
		$id = $paie->delete($user);
		
	}
	
	header("Location: " . DOL_URL_ROOT . "/custom/immobilier/receipt/card.php?id=" . $receipt_id);
}


/***** Update ******/

if ($action == 'maj') {
	$datepaie = @dol_mktime(0, 0, 0, GETPOST("paiemonth"), GETPOST("paieday"), GETPOST("paieyear"));
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie")) . '</div>';
		$action = 'update';
	} else {
		$paie = new Immopayment($db);
		
		$result = $paie->fetch($id);
		
		$paie->amount		= GETPOST("amount");
		$paie->comment		= GETPOST("comment");
		$paie->date_payment	= $datepaie;
		
		$result = $paie->update($user);
		header("Location: " . DOL_URL_ROOT . "/custom/immobilier/receipt/card.php?id=" . $receipt_id);
		
	}
}

if ($action == 'addall') {
	$datepaie = @dol_mktime(0, 0, 0, GETPOST("paiemonth"), GETPOST("paieday"), GETPOST("paieyear"));
	if (! $datepaie) {
		$mesg = '<div class="error">' . $langs->trans("ErrorFieldRequired", $langs->transnoentities("Datepaie")) . '</div>';
		$action = 'createall';
	} else {
	$datapost = $_POST;
	foreach ( $datapost as $key => $value ) {
		if (strpos($key, 'receipt_') !== false) {
		
			$tmp_array = explode('_', $key);
			
			if (count($tmp_array) > 0) {
				$reference = $tmp_array[1];
				$amount= GETPOST('incomeprice_'.$reference);
			
				if (! empty($reference) && !empty($amount)) {
					$paie = new Immopayment($db);

					$paie->fk_contract		= GETPOST('fk_contract_'.$reference);
					$paie->fk_property		= GETPOST('fk_property_'.$reference);
					$paie->fk_renter		= GETPOST('fk_renter_'.$reference);
					$paie->amount			= price2num($amount);
					$paie->comment			= GETPOST('comment');
					$paie->date_payment		= $datepaie;
					$paie->fk_receipt		= GETPOST('receipt_'.$reference);
					$paie->fk_bank			= GETPOST("accountid");
					$paie->fk_typepayment	= GETPOST("fk_typepayment");
					$paie->num_payment		= GETPOST("num_payment");
					$paie->fk_owner			= $user->id;
					
					$result = $paie->create ($user);

					if ($result<0) {
						setEventMessages($paie->error, null, 'errors');
					}
				}
			}
		}
	}
	
	
		
	}
}

/*
 * View
 */
 

 // Add realtime total information
 /*
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
            						var emetteur = ('.$facture->type.' == '.Facture::TYPE_CREDIT_NOTE.') ? \''.dol_escape_js(dol_escape_htmltag($conf->global->MAIN_INFO_SOCIETE_NOM)).'\' : jQuery(\'#thirdpartylabel\').val();
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

			//Add js for AutoFill
			print ' $(document).ready(function () {';
			print ' 	$(".AutoFillAmout").on(\'click touchstart\', function(){
							$("input[name="+$(this).data(\'rowname\')+"]").val($(this).data("value")).trigger("change");
						});';
			print '	});'."\n";

			print '	</script>'."\n";
		}
 
 */
 
$form = new Form($db);

llxHeader();

if ($action == 'create')
{
	$receipt = new Immoreceipt($db);
	$result = $receipt->fetch($id);

	$total = $receipt->amount_total;

	print load_fiche_titre($langs->trans("DoPayment"));

	if ($mesg)
	{
		print "<div class=\"error\">$mesg</div>";
	}

	print '<form name="add_payment" action="'.$_SERVER['PHP_SELF'].'" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="fk_contract" value="' . $receipt->fk_contract . '">';
	print '<input type="hidden" name="fk_property" value="' . $receipt->fk_property . '">';
	print '<input type="hidden" name="fk_renter" value="' . $receipt->fk_renter . '">';
	print '<input type="hidden" name="fk_receipt" value="' . $id . '">';

    dol_fiche_head();

	print '<table cellspacing="0" class="border" width="100%" cellpadding="2">';

	print '<tr class="liste_titre"><td colspan="3">'.$langs->trans("Receipt").'</td>';

	print '<tr><td>' . $langs->trans("NomAppartement") . '</td><td>' . $receipt->nomlocal . '</td></tr>';
	print '<tr><td>' . $langs->trans("Renter") . '</td><td>' . $receipt->nomlocataire . '</td></tr>';
	print '<tr><td>' . $langs->trans("RefLoyer") . '</td><td>' . $receipt->name . '</td></tr>';
	print '<tr><td>' . $langs->trans("Amount") . '</td><td colspan="2">'.price($receipt->amount_total,0,$outputlangs,1,-1,-1,$conf->currency).'</td></tr>';

	$sql = "SELECT sum(p.amount) as total";
	$sql.= " FROM ".MAIN_DB_PREFIX."immo_payment as p";
	$sql.= " WHERE p.fk_receipt = ".$id;
	$resql = $db->query($sql);
	if ($resql)
	{
		$obj=$db->fetch_object($resql);
		$sumpaid = $obj->total;
		$db->free();
	}
	print '<tr><td>'.$langs->trans("AlreadyPaid").'</td><td colspan="2">'.price($sumpaid,0,$outputlangs,1,-1,-1,$conf->currency).'</td></tr>';
	print '<tr><td valign="top">'.$langs->trans("RemainderToPay").'</td><td colspan="2">'.price($total-$sumpaid,0,$outputlangs,1,-1,-1,$conf->currency).'</td></tr>';

	print '<tr class="liste_titre">';
	print '<td colspan="3">'.$langs->trans("Payment").'</td>';
	print '</tr>';

	print '<tr><td class="fieldrequired">' . $langs->trans("Date") . '</td>';
	print '<td>';
	print $form->select_date(! empty($datepaie) ? $datepaie : '-1', 'paie', 0, 0, 0, 'card', 1, 1);
	print '</td>';

	print '<tr><td class="fieldrequired">' . $langs->trans("Amount") . '</td>';
	print '<td><input name="amount" size="30" value="' . $paie->amount . '"</td></tr>';

	print '<tr><td class="fieldrequired">'.$langs->trans("PaymentMode").'</td><td colspan="2">';
	$form->select_types_paiements(isset($_POST["fk_typepayment"])?$_POST["fk_typepayment"]:$paie->fk_typepayment, "fk_typepayment");
	print "</td>\n";
	print '</tr>';

	print '<tr>';
	print '<td class="fieldrequired">'.$langs->trans('AccountToCredit').'</td>';
	print '<td colspan="2">';
	$form->select_comptes(isset($_POST["accountid"])?$_POST["accountid"]:$paie->accountid, "accountid", 0, '',1);  // Show open bank account list
	print '</td></tr>';

	// Number
	print '<tr><td>'.$langs->trans('Numero');
	print ' <em>('.$langs->trans("ChequeOrTransferNumber").')</em>';
	print '</td>';
	print '<td colspan="2"><input name="num_payment" type="text" value="'.GETPOST('num_payment').'"></td></tr>'."\n";

	print '<tr>';
	print '<td valign="top">'.$langs->trans("Comments").'</td>';
	print '<td valign="top" colspan="2"><textarea name="comment" wrap="soft" cols="60" rows="'.ROWS_3.'"></textarea></td>';
	print '</tr>';
	
	print '</table>';

    dol_fiche_end();

	print '<br><div class="center">';
	print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print "</form>\n";
}

/* *************************************************************************** */
/*                                                                             */
/* Mode add all payments                                                       */
/*                                                                             */
/* *************************************************************************** */

if ($action == 'createall') {

	print '<form name="fiche_payment" method="post" action="' . $_SERVER["PHP_SELF"] . '">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="addall">';
	
	print '<table class="border" width="100%">';

	print "<tr class=\"liste_titre\">";
	
	print '<td align="left">';
	print $langs->trans("DatePayment");
	print '</td><td align="left">';
	print $langs->trans("Comment");
	print '</td><td align="left">';
	print $langs->trans("PaymentMode");
	print '</td><td align="left">';
	print $langs->trans("AccountToCredit");
	print '</td><td align="left">';
	print $langs->trans("Numero");
	print '</td>';
	print "</tr>\n";
	
	print '<tr ' . $bc[$var] . ' valign="top">';
	
	// Due date
	
	print '<td align="center">';
	print $form->select_date(! empty($datepaie) ? $datepaie : '-1', 'paie', 0, 0, 0, 'card', 1);
	print '</td>';
	
	// Comment
	print '<td><input name="comment" size="30" value="' . GETPOST('comment') . '"</td>';
	
	// Payment mode
	print '<td align="center">';
	print $form->select_types_paiements(isset($_POST["fk_typepayment"])?$_POST["fk_typepayment"]:$paie->fk_typepayment, "fk_typepayment");
	print '</td>';
	
	// AccountToCredit
	print '<td align="center">';
	print $form->select_comptes(isset($_POST["accountid"])?$_POST["accountid"]:$paie->accountid, "accountid", 0, '',1);  // Show open bank account list
	print '</td>';

	// num_payment
	print '<td><input name="num_payment" size="30" value="' . GETPOST('num_payment') . '"</td>';
	
	
	print "</tr>\n";
	
	/*
	 * List receipt
	 */
	$sql = "SELECT rec.rowid as reference, rec.name as receiptname, loc.nom as nom, l.address  , l.name as local, loc.statut as statut, rec.amount_total as total, rec.paiepartiel, rec.balance ,  rec.fk_renter as reflocataire, rec.fk_property as reflocal, rec.fk_contract as refcontract , c.preavis";
	$sql .= " FROM " . MAIN_DB_PREFIX . "immo_receipt rec";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_renter as loc";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_property as l";
	$sql .= " , " . MAIN_DB_PREFIX . "immo_contrat as c";
	$sql .= " WHERE rec.paye = 0 AND loc.rowid = rec.fk_renter AND l.rowid = rec.fk_property AND  c.rowid = rec.fk_contract and c.preavis =0 ";
	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		
		$i = 0;
		$total = 0;
		
		print '<br><table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		print '<td>' . $langs->trans('ReceiptName') . '</td>';
		print '<td>' . $langs->trans('Nomlocal') . '</td>';
		print '<td>' . $langs->trans('Renter') . '</td>';
		print '<td align="right">' . $langs->trans('montant_tot') . '</td>';
		print '<td align="right">' . $langs->trans('payed') . '</td>';
		print '<td align="right">' . $langs->trans('due') . '</td>';
		print '<td align="right">' . $langs->trans('income') . '</td>';
		print "</tr>\n";
		
		if ($num > 0) {
			$var = True;
			
			while ( $i < $num ) {
				$objp = $db->fetch_object($resql);
				$var = ! $var;
				print '<tr ' . $bc[$var] . '>';
				
				print '<td>' . $objp->receiptname . '</td>';
				print '<td>' . $objp->local . '</td>';
				print '<td>' . $objp->nom . '</td>';
				
				print '<td align="right">' . price($objp->total) . '</td>';
				print '<td align="right">' . price($objp->paiepartiel) . '</td>';
				print '<td align="right">' . price($objp->balance) . '</td>';
				
					print '<input type="hidden" name="fk_contract_' . $objp->reference . '" size="10" value="' . $objp->refcontract . '">';
					print '<input type="hidden" name="fk_property_' . $objp->reference . '" size="10" value="' . $objp->reflocal . '">';
					print '<input type="hidden" name="fk_renter_' . $objp->reference . '" size="10" value="' . $objp->reflocataire . '">';
					print '<input type="hidden" name="receipt_' . $objp->reference . '" size="10" value="' . $objp->reference . '">';
				
				// Colonne imput income
				print '<td align="right">';
			print '<input type="text" name="incomeprice_' . $objp->reference . '" id="incomeprice_' . $objp->reference . '" size="6" value="" class="flat">';
			print '</td>';
				
				
	
				print '</tr>';
				
				$i ++;
			}
		}
		$var = ! $var;
		
		print "</table>\n";
		$db->free($resql);
	} 

	else {
		dol_print_error($db);
	}
	print '<div class="tabsAction">' . "\n";
	print '<div class="inline-block divButAction"><input type="submit"  name="button_addallpaiement" id="button_addallpaiement" class="butAction" value="' . $langs->trans("Payed") . '" /></div>';
	print '</div>';
	print '</form>';
	
}


/* *************************************************************************** */
/*                                                                             */
/* Mode fiche                                                                  */
/*                                                                             */
/* *************************************************************************** */
if ($action == 'update') {
	$receipt = new Immoreceipt($db);
	$result = $receipt->fetch($receipt_id);

	$paie = new Immopayment($db);

	$result = $paie->fetch($id);
	
	print '<form action="card.php" method="post">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="maj">';
	print '<input type="hidden" name="id" value="' . GETPOST("id") . '">' . "\n";
	print '<input type="hidden" name="receipt" value="' . $receipt_id . '">' . "\n";
	
	print '<table class="border" width="100%">';
	
	print '<input type="hidden" name="fk_contract" size="10" value="' . $receipt->fk_contract . '">';
	print '<input type="hidden" name="fk_property" size="10" value="' . $receipt->fk_property . '">';
	print '<input type="hidden" name="fk_renter" size="10" value="' . $receipt->fk_renter . '">';
	print '<input type="hidden" name="fk_receipt" size="10" value="' . $id . '">';

	print '<tr><td class="titlefield">' . $langs->trans("NomAppartement") . '</td><td>' . $receipt->nomlocal . '</td></tr>';

	print '<tr><td>' . $langs->trans("NomLocataire") . '</td><td>' . $receipt->nomlocataire . '</td></tr>';

	print '<tr><td>' . $langs->trans("RefLoyer") . '</td><td>' . $receipt->name . '</td></tr>';
	
	print '<tr><td>' . $langs->trans("Amount") . '</td>';
	print '<td><input name="amount" size="30" value="' . round($paie->amount,2) . '"</td></tr>';
	
	print '<tr><td>' . $langs->trans("Comment") . '</td>';
	print '<td><input name="comment" size="10" value="' . $paie->comment . '"</td></tr>';
	
	print '<tr><td>' . $langs->trans("DatePaiement") . '</td>';
	print '<td align="left">';
	print $form->select_date(! empty($paie->date_payment) ? $paie->date_payment : '-1', 'paie', 0, 0, 0, 'card', 1);
	print '</td>';
	
	print '</table>';

	dol_fiche_end();

	print '<div align="center">';
	print '<input type="submit" value="'.$langs->trans("AddProperty").'" name="bouton" class="button" />';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="'.$langs->trans("Cancel").'" class="button" onclick="history.go(-1)" />';
	print '</div>';

	print '</form>';
}

llxFooter();

$db->close();
