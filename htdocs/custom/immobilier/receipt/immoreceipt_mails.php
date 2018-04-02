<?php
/* Copyright (C) 2013-2015 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2015-2016 Alexandre Spangaro	<aspangaro@zendsi.com>
 * Copyright (C) 2016		Jamelbaz			<jamelbaz@gmail.com>
 * Copyright (C) 2018 Philippe GRAND 	   <philippe.grand@atoo-net.com>
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
 * \file 		htdocs/compta/ventilation/card.php
 * \ingroup 	compta
 * \brief 		Page fiche ventilation
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


require_once '../core/lib/immobilier.lib.php';
dol_include_once("/immobilier/class/immoreceipt.class.php");

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// Load traductions files requiredby by page
$langs->loadLangs(array("immobilier@immobilier","other","companies","products","admin","mails","errors"));

$action=GETPOST('action','alpha');
$id = GETPOST('id', 'int');

if (! $user->admin) accessforbidden();

$usersignature=$user->signature;
// For action = test or send, we ensure that content is not html, even for signature, because this we want a test with NO html.
if ($action == 'test' || $action == 'send')
{
	$usersignature=dol_string_nohtmltag($usersignature);
}

$substitutionarrayfortest=array(
'__LOGIN__' => $user->login,
'__ID__' => 'TESTIdRecord',
'__EMAIL__' => 'TESTEMail',
'__LASTNAME__' => 'TESTLastname',
'__FIRSTNAME__' => 'TESTFirstname',
'__SIGNATURE__' => (($user->signature && empty($conf->global->MAIN_MAIL_DO_NOT_USE_SIGN))?$usersignature:''),
//'__PERSONALIZED__' => 'TESTPersonalized'	// Hiden because not used yet
);
complete_substitutions_array($substitutionarrayfortest, $langs);



/*
 * Actions
 */

/*
 * Add file in email form
 */
if (GETPOST('addfile') || GETPOST('addfilehtml'))
{
	require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

	// Set tmp user directory
	$vardir=$conf->user->dir_output."/".$user->id;
	$upload_dir = $vardir.'/temp';
	dol_add_file_process($upload_dir,0,0);

	if ($_POST['addfile'])     $action='test';
	if ($_POST['addfilehtml']) $action='testhtml';
}

/*
 * Remove file in email form
 */
if (! empty($_POST['removedfile']) || ! empty($_POST['removedfilehtml']))
{
	// Set tmp user directory
	$vardir=$conf->user->dir_output."/".$user->id;
	$upload_dir = $vardir.'/temp';

	$keytodelete=isset($_POST['removedfile'])?$_POST['removedfile']:$_POST['removedfilehtml'];
	$keytodelete--;

	$listofpaths=array();
	$listofnames=array();
	$listofmimes=array();
	if (! empty($_SESSION["listofpaths"])) $listofpaths=explode(';',$_SESSION["listofpaths"]);
	if (! empty($_SESSION["listofnames"])) $listofnames=explode(';',$_SESSION["listofnames"]);
	if (! empty($_SESSION["listofmimes"])) $listofmimes=explode(';',$_SESSION["listofmimes"]);

	if ($keytodelete >= 0)
	{
		$pathtodelete=$listofpaths[$keytodelete];
		$filetodelete=$listofnames[$keytodelete];
		$result = dol_delete_file($pathtodelete,1);
		if ($result)
		{
			setEventMessages(array($langs->trans("FileWasRemoved"), $filetodelete), null, 'mesgs');

			include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
			$formmail = new FormMail($db);
			$formmail->remove_attached_files($keytodelete);
		}
	}
	if ($_POST['removedfile'] || $action='send')     $action='test';
	if ($_POST['removedfilehtml'] || $action='sendhtml') $action='testhtml';
}

/*
 * Send mail
 */
if (($action == 'send' || $action == 'sendhtml') && ! GETPOST('addfile') && ! GETPOST('addfilehtml') && ! GETPOST('removedfile') && ! GETPOST('cancel'))
{
	$error=0;

	$email_from='';
	if (! empty($_POST["fromname"])) $email_from=$_POST["fromname"].' ';
	if (! empty($_POST["frommail"])) $email_from.='<'.$_POST["frommail"].'>';

	$errors_to  = $_POST["errorstomail"];
	$sendto     = $_POST["sendto"];
	$sendtocc   = $_POST["sendtocc"];
	$sendtoccc  = $_POST["sendtoccc"];
	$subject    = $_POST['subject'];
	$body       = $_POST['message'];
	$deliveryreceipt= $_POST["deliveryreceipt"];
	$trackid    = GETPOST('trackid');
	
	//Check if we have to decode HTML
	if (!empty($conf->global->FCKEDITOR_ENABLE_MAILING) && dol_textishtml(dol_html_entity_decode($body, ENT_COMPAT | ENT_HTML401))) {
		$body=dol_html_entity_decode($body, ENT_COMPAT | ENT_HTML401);
	}

	// Create form object
	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
	$formmail = new FormMail($db);

	$attachedfiles=$formmail->get_attached_files();
	$filepath = $attachedfiles['paths'];
	$filename = $attachedfiles['names'];
	$mimetype = $attachedfiles['mimes'];

	if (empty($_POST["frommail"]))
	{
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("MailFrom")), null, 'errors');
		$action='test';
		$error++;
	}
	if (empty($sendto))
	{
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("MailTo")), null, 'errors');
		$action='test';
		$error++;
	}
	if (! $error)
	{
		// Is the message in HTML?
		$msgishtml=0;	// Message is not HTML
		if ($action == 'sendhtml') $msgishtml=1;	// Force message to HTML

		// Pratique les substitutions sur le sujet et message
		$subject=make_substitutions($subject,$substitutionarrayfortest);
		$body=make_substitutions($body,$substitutionarrayfortest);

		require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
        $mailfile = new CMailFile(
            $subject,
            $sendto,
            $email_from,
            $body,
            $filepath,
            $mimetype,
            $filename,
            $sendtocc,
            $sendtoccc,
            $deliveryreceipt,
            $msgishtml,
            $errors_to,
        	'',
        	$trackid	
        );

		$result=$mailfile->sendfile();

		if ($result)
		{
			setEventMessages($langs->trans("MailSuccessfulySent",$mailfile->getValidAddress($email_from,2),$mailfile->getValidAddress($sendto,2)), null, 'mesgs');
		}
		else
		{
			setEventMessages($langs->trans("ResultKo").'<br>'.$mailfile->error.' '.$result, null, 'errors');
		}

		$action='';
	}
}





/*
 * View
 */

// List of sending methods
$listofmethods=array();
$listofmethods['mail']='PHP mail function';
//$listofmethods['simplemail']='Simplemail class';
$listofmethods['smtps']='SMTP/SMTPS socket library';

llxheader('', $langs->trans("SendEmailReceit"), '');

$receipt = new Immoreceipt($db);
$result = $receipt->fetch($id);
	
$head = receipt_prepare_head($receipt);
dol_fiche_head($head, 'mail', $langs->trans("ReceiptSendMail"), 0, 'rent@immobilier');

	// Show email send test form

		//print load_fiche_titre($action == 'testhtml'?$langs->trans("DoTestSendHTML"):$langs->trans("DoTestSend"));

		// Cree l'objet formulaire mail
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
		$formmail = new FormMail($db);
		$formmail->clear_attached_files();
		$formmail->fromname = (isset($_POST['fromname'])?$_POST['fromname']:$conf->global->MAIN_MAIL_EMAIL_FROM);
		$formmail->frommail = (isset($_POST['frommail'])?$_POST['frommail']:$conf->global->MAIN_MAIL_EMAIL_FROM);
		$formmail->trackid='test';
		$formmail->withfromreadonly=0;
		$formmail->withsubstit=0;
		$formmail->withfrom=1;
		$formmail->witherrorsto=1;
		$formmail->withto=(! empty($_POST['sendto'])?$_POST['sendto']:($receipt->emaillocataire));
		$formmail->withtocc=(! empty($_POST['sendtocc'])?$_POST['sendtocc']:1);       // ! empty to keep field if empty
		$formmail->withtoccc=(! empty($_POST['sendtoccc'])?$_POST['sendtoccc']:1);    // ! empty to keep field if empty
		$formmail->withtopic=(isset($_POST['subject'])?$_POST['subject']:$langs->trans("SendReceipt"));
		$formmail->withtopicreadonly=0;
		$formmail->withfile=1;
		$formmail->withbody=(isset($_POST['message'])?$_POST['message']:$langs->transnoentities("PredefinedMailSendReceipt"));
		$formmail->withbodyreadonly=0;
		$formmail->withcancel=1;
		$formmail->withdeliveryreceipt=1;
		$formmail->withfckeditor=($action == 'testhtml'?1:0);
		$formmail->ckeditortoolbar='dolibarr_mailings';
		
		// file
		// Tableau des substitutions
		$formmail->add_attached_files(DOL_DATA_ROOT . '/immobilier/quittance_' . $id . '.pdf', 'quittance', 'application/pdf');
		// Tableau des parametres complementaires du post
		$formmail->param["action"]=($action == 'testhtml'?"sendhtml":"send");
		$formmail->param["models"]="body";
		$formmail->param["mailid"]=0;
		$formmail->param["returnurl"]=$_SERVER["PHP_SELF"]."?id=$id";

		// Init list of files
       /* if (GETPOST("mode")=='init')
		{
			
		}
		
		if (is_file($conf->immobilier->dir_output . '/quittance_' . $id . '.pdf')) {
			//print 'tst';
			print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=immobilier&file=quittance_' . $id . '.pdf" alt="' . $legende . '" title="' . $legende . '">';
			print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';
		}*/

		print $formmail->get_form(($action == 'testhtml'?'addfilehtml':'addfile'),($action == 'testhtml'?'removefilehtml':'removefile'));

		print '<br>';




llxFooter();

$db->close();
