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

//var_dump($_POST);exit;
/*
 * Actions
 */

$parameters=array('socid'=>$socid);
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

$form=new Form($db);
if ($action == 'add_payment')
{
	$error=0;

	if ($_POST["cancel"])
	{
		$loc = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1).'?id=' .$recid;
		header("Location: ".$loc);
		exit;
	}

	

	if (! $_POST["fk_mode_reglement"] > 0)
	{
		$mesg = $langs->trans("ErrorFieldRequired", $langs->transnoentities("PaymentMode"));
		setEventMessages($mesg, null, 'errors');
		$error++;
	}
	if (GETPOST('reyear') == '')
	{
		$mesg = $langs->trans("ErrorFieldRequired", $langs->transnoentities("Date"));
		setEventMessages($mesg, null, 'errors');
		$error++;
	}
    if (! empty($conf->banque->enabled) && $accountid <= 0)
    {
        $mesg = $langs->trans("ErrorFieldRequired", $langs->transnoentities("AccountToCredit"));
		setEventMessages($mesg, null, 'errors');
        $error++;
    }

	if (! $error)
	{
		$date_payment = dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));
		$paymentid = 0;

		// Read possible payments
		foreach ($_POST as $key => $value)
		{
			if (substr($key, 0, 7) == 'amount_')
			{
				$other_recid = substr($key, 7);
				$amounts[$other_recid] = price2num($_POST[$key]);
			}
		}

        if (count($amounts) <= 0)
        {
            $error++;
            $errmsg='ErrorNoPaymentDefined';
			setEventMessages($errmsg, null, 'errors');
        }

        if (! $error)
        {
    		$db->begin();

    		// Create a line of payments
    		$payment = new ImmoPayment($db);

    		$payment->ref          = $recid;
			$payment->rowid        = $recid;
    		$payment->date_payment = $date_payment;
    		$payment->amount       = $amounts[$other_recid];   // Tableau de montant			
    		$payment->fk_mode_reglement  = $_POST["fk_mode_reglement"];
			$payment->fk_bank  = $_POST["fk_bank"];
    		$payment->num_payment  = $_POST["num_payment"];
    		$payment->note_public  = $_POST["note_public"];

    		if (! $error)
    		{
    		    $paymentid = $payment->create($user);
                if ($paymentid < 0)
                {
                    $errmsg=$payment->error;
					setEventMessages($errmsg, null, 'errors');
                    $error++;
                }
    		}

            if (! $error)
            {
				$label='(CustomerReceiptPayment)';
				if (GETPOST('type') == ImmoReceipt::TYPE_CREDIT_NOTE) $label='(CustomerReceiptPaymentBack)';
                $result=$payment->addPaymentToBank($user, 'immopayment', $label, $_POST['accountid'], '', '');
                if ($result <= 0)
                {
                    $errmsg=$payment->error;
					setEventMessages($errmsg, null, 'errors');
                    $error++;
                }
            }

    	    if (! $error)
            {
                $db->commit();
                $loc = dol_buildpath('/ultimateimmo/receipt/immoreceipt_card.php', 1).'?id=' .$recid;
                header('Location: '.$loc);
                exit;
            }
            else
            {
                $db->rollback();
				$errmsg=$payment->error;
				setEventMessages($errmsg, null, 'errors');

            }
        }
	}

	$_GET["action"]='create';
}


/*
 * View
 */

$form=new Form($db);

llxHeader('', $langs->trans("Payment"));


if (GETPOST('action', 'aZ09') == 'create')
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

		print '<form id="payment_form" name="add_payment" action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="add_payment">';
		print '<input type="hidden" name="recid" value="'.$recid.'">';
		print '<input type="hidden" name="socid" value="'.$renter->fk_soc.'">';
		print '<input type="hidden" name="thirdpartylabel" id="thirdpartylabel" value="'.dol_escape_htmltag($renter->thirdparty->name).'">';

		dol_fiche_head();
		$result=$renter->fetch_thirdparty();
		
		print '<table class="border" width="100%">';
		
		$object = new ImmoPayment($db);
		$object->fetch($receipt->fk_payment);
		
		// Reference
		$tmpref= GETPOST('ref','alpha')?GETPOST('ref','alpha'):$recid;
        print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans('Reference').'</span></td><td>'.$tmpref."</td></tr>\n";
	
        // Third party
       // print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans('Renter').'</span></td><td>'.$renter->thirdparty->getNomUrl(4)."</td></tr>\n";

        // Date payment
       print '<tr><td>'.$langs->trans("Date")."</td><td colspan=\"2\">".dol_print_date($receipt->date_echeance, 'day')."</td></tr>\n";
		
		// Total amount
		print '<tr><td>'.$langs->trans("Amount")."</td><td colspan=\"2\">".price($receipt->total_amount, 0, $outputlangs, 1, -1, -1, $conf->currency).'</td></tr>';
		
		$sql = "SELECT sum(p.amount) as total";
		$sql.= " FROM ".MAIN_DB_PREFIX."ultimateimmo_immopayment as p";
		$sql.= " WHERE p.fk_receipt = ".$recid;
		$resql = $db->query($sql);
		if ($resql)
		{
			$obj=$db->fetch_object($resql);
			$sumpaid = $obj->total;
			$db->free();
		}
		
		print '<tr><td>'.$langs->trans("AlreadyPaid").'</td><td colspan="2">'.price($sumpaid, 0, $outputlangs, 1, -1, -1, $conf->currency).'</td></tr>';
		print '<tr><td class="tdtop">'.$langs->trans("RemainderToPay").'</td><td colspan="2">'.price($receipt->total_amount-$sumpaid, 0, $outputlangs, 1, -1, -1, $conf->currency).'</td></tr>';
		
		print '<tr class="liste_titre">';
		print "<td colspan=\"3\">".$langs->trans("Payment").'</td>';
		print '</tr>';

		 print '<tr><td><span class="fieldrequired">'.$langs->trans('Date').'</span></td><td>';
        $date_payment = dol_mktime(12, 0, 0, $_POST['remonth'], $_POST['reday'], $_POST['reyear']);
        $datepayment=empty($conf->global->MAIN_AUTOFILL_DATE)?(empty($_POST["remonth"])?-1:$date_payment):0;
		print $form->selectDate($datepayment, '', '', '', '', "add_payment", 1, 1);
        print '</td></tr>';

		print '<tr><td class="fieldrequired">'.$langs->trans("PaymentMode").'</td><td colspan="2">';
		$form->select_types_paiements((GETPOST('fk_mode_reglement')?GETPOST('fk_mode_reglement'):$object->fk_mode_reglement), 'fk_mode_reglement', '', 2);
		print "</td>\n";
		print '</tr>';
		
		// Bank account
		print '<tr>';
		print '<td class="fieldrequired">'.$langs->trans('AccountToCredit').'</td>';
		print '<td colspan="2">';
		$form->select_comptes(isset($_POST["accountid"])?$_POST["accountid"]:$object->accountid, "accountid", 0, '', 1);  // Show open bank account list
		print '</td></tr>';

        // Cheque number
        print '<tr><td>'.$langs->trans('Numero');
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
        print '<td><input name="chqbank" size="30" type="text" value="'.GETPOST('chqbank', 'alphanohtml').'"></td></tr>';
	

		// Comments
		print '<tr><td>'.$langs->trans('Comments').'</td>';
		print '<td class="tdtop">';
		print '<textarea name="comment" wrap="soft" class="quatrevingtpercent" rows="'.ROWS_3.'">'.GETPOST('comment', 'none').'</textarea></td></tr>';

        print '</table>';

		dol_fiche_end();
		
	/*
 	 * Autres charges impayees
	 */
	$num = 1;
	$i = 0;

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td class="right">'.$langs->trans("Amount").'</td>';
	print '<td class="right">'.$langs->trans("AlreadyPaid").'</td>';
	print '<td class="right">'.$langs->trans("RemainderToPay").'</td>';
	print '<td class="center">'.$langs->trans("Amount").'</td>';
	print "</tr>\n";

	$total=0;
	$totalrecu=0;

	while ($i < $num)
	{
		$objp = $receipt;

		print '<tr class="oddeven">';

		print '<td class="right">'.price($objp->total_amount)."</td>";

		print '<td class="right">'.price($sumpaid)."</td>";

		print '<td class="right">'.price($objp->total_amount - $sumpaid)."</td>";

		print '<td class="center">';
		if ($sumpaid < $objp->total_amount)
		{
			$namef = "amount_".$objp->id;
			print '<input type="text" size="8" name="'.$namef.'">';
		}
		else
		{
			$errmsg=$langs->trans("AlreadyPaid");
			setEventMessages($errmsg, null, 'errors');
			print $errmsg;
		}
		print "</td>";

		print "</tr>\n";
                                                         
		$i++;
	}

	print "</table>";

	print '<br><div class="center">';
	print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print "</form>\n";
}


        /*
         * List of unpaid receipts
         

        $sql = 'SELECT DISTINCT f.rowid as recid, f.ref, f.total_amount,';
		$sql.= ' p.rowid, p.fk_receipt, p.date_payment as dp, p.amount,';
        $sql.= ' f.date_creation as dc, f.fk_soc as socid';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'ultimateimmo_immoreceipt as f';
		$sql.= ', '.MAIN_DB_PREFIX.'ultimateimmo_immopayment as p';
		$sql.= ' WHERE p.fk_receipt = f.rowid';
		$sql.= ' AND f.entity IN ('.getEntity($object->element).')';
        $sql.= ' AND f.fk_soc = '.$socid;*/
		
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
                print '<td colspan="2">'.$arraytitle.'</td>';
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

					print '<tr class="oddeven">';*/
					/*print '<td>';
					$payment->ref = $payment->id.'_'.$receipt->ref;
					print $payment->getNomUrl(1, '');
					print "</td>\n";

					print '<td colspan="2">';
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
					
                    if ($action != 'add_payment')
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
        if ($action != 'add_payment')
        {
        	$checkboxlabel=$langs->trans("ClosePaidInvoicesAutomatically");
        	if ($receipt->type == 2) $checkboxlabel=$langs->trans("ClosePaidCreditNotesAutomatically");
        	$buttontitle=$langs->trans('ToMakePayment');
        	if ($receipt->type == 2) $buttontitle=$langs->trans('ToMakePaymentBack');

        	print '<br><div class="center">';
        	print '<input type="checkbox" checked name="closepaidreceipts"> '.$checkboxlabel;*/
            /*if (! empty($conf->prelevement->enabled))
            {
                $langs->load("withdrawals");
                if (! empty($conf->global->WITHDRAW_DISABLE_AUTOCREATE_ONPAYMENTS)) print '<br>'.$langs->trans("IfInvoiceNeedOnWithdrawPaymentWontBeClosed");
            }
            print '<br><input type="submit" class="button" value="'.dol_escape_htmltag($buttontitle).'"><br><br>';
            print '</div>';
        }

        // Form to confirm payment
        if ($action == 'add_payment')
        {
            $preselectedchoice=$addwarning?'no':'yes';
			
            print '<br>';
            if (!empty($totalpayment)) $text=$langs->trans('ConfirmCustomerPayment', $totalpayment, $langs->trans("Currency".$conf->currency));*/
			/*if (!empty($multicurrency_totalpayment))
			{
				$text.='<br>'.$langs->trans('ConfirmCustomerPayment', $multicurrency_totalpayment, $langs->trans("paymentInInvoiceCurrency"));
			}
            if (GETPOST('closepaidreceipts'))
            {
                $text.='<br>'.$langs->trans("AllCompletelyPayedInvoiceWillBeClosed");
                print '<input type="hidden" name="closepaidreceipts" value="'.GETPOST('closepaidreceipts').'">';
            }
            print $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$recid.'&socid='.$renter->fk_soc.'&type='.$receipt->type, $langs->trans('ReceivedCustomersPayments'), $text, 'confirm_paiement', $formquestion, $preselectedchoice);
        }

        print "</form>\n";
    }
}*/


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
}
llxFooter();

$db->close();
