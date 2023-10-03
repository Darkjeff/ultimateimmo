<?php
/*
 * Copyright (C) 2019-2022 Fabien Fernandes Alves   <fabien@code42.fr>
 * Copyright (C) 2019-2022 Florian HENRY   <florian.henry@scopen.fr>
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
 *    \file       htdocs/ultimateimmo/template/index.php
 *    \ingroup    ultimateimmo
 *    \brief      Home page of ultimateimmo top menu
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) { $res=@include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--;
}
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) { $res=@include substr($tmp, 0, ($i+1))."/main.inc.php";
}
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) { $res=@include dirname(substr($tmp, 0, ($i+1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) { $res=@include "../main.inc.php";
}
if (! $res && file_exists("../../main.inc.php")) { $res=@include "../../main.inc.php";
}
if (! $res && file_exists("../../../main.inc.php")) { $res=@include "../../../main.inc.php";
}
if (! $res) { die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
dol_include_once('ultimateimmo/class/ultimateimmo_infobox.class.php');
dol_include_once('ultimateimmo/class/ultimateimmo_modele_boxes.class.php');
dol_include_once('ultimateimmo/lib/dashboard.lib.php');

// If not defined, we select menu "home"
$_GET['mainmenu']=GETPOST('mainmenu', 'aZ09')?GETPOST('mainmenu', 'aZ09'):'home';
$action=GETPOST('action', 'aZ09');

$hookmanager->initHooks(array('index'));

// Fixed global boxes
$globalboxes = array();

// Color theme : (#96BBBB, #F2E3BC, #618985, #C19875)

if ($user->rights->ultimateimmo->read) {
    $globalboxes[] = array('name' => $langs->trans('MenuImmoOwner'), 'color' =>'#C19875',
        'url' => dol_buildpath('/ultimateimmo/owner/immowner_list.php', 1),
        'url_add' => dol_buildpath('/ultimateimmo/owner/immoowner_card.php?action=create', 1),
        'right' => $user->rights->ultimateimmo->read,
        'lines' => array(
            array('title' => $langs->trans('MenuImmoActiveOwner'), 'value' => getOwnerNumber(1), 'url' => dol_buildpath('/ultimateimmo/owner/immoowner_list.php', 1).'?search_status=1'),
			array('title' => $langs->trans('MenuImmoNotActiveOwner'), 'value' => getOwnerNumber(9), 'url' => dol_buildpath('/ultimateimmo/owner/immoowner_list.php', 1).'?search_status=9'),
        )
    );
}


if ($user->rights->ultimateimmo->read) {
    $globalboxes[] = array('name' => strtoupper($langs->trans('ImmoProperties')), 'color' =>'#96BBBB',
        'url' => dol_buildpath('/ultimateimmo/property/immoproperty_list.php', 1),
        'url_add' => dol_buildpath('/ultimateimmo/property/immoproperty_card.php?action=create', 1),
        'right' => $user->rights->ultimateimmo->read,
        'lines' => array(
            array('title' => $langs->trans('ImmoActiveProperties'), 'value' => getPropertiesNumber(1), 'url' => dol_buildpath('/ultimateimmo/property/immoproperty_list.php', 1).'?search_status=1'),
			array('title' => $langs->trans('ImmoNotActiveProperties'), 'value' => getPropertiesNumber(9), 'url' => dol_buildpath('/ultimateimmo/property/immoproperty_list.php', 1).'?search_status=9'),
        )
    );
}

if ($user->rights->ultimateimmo->read) {
    $globalboxes[] = array('name' => strtoupper($langs->trans('MenuImmoRent')), 'color' =>'#F2E3BC',
        'url' => dol_buildpath('/ultimateimmo/property/immorent_list.php', 1),
        'url_add' => dol_buildpath('/ultimateimmo/rent/immorent_card.php?action=create', 1),
        'right' => $user->rights->ultimateimmo->read,
        'lines' => array(
            array('title' => $langs->trans('MenuImmoActiveRent'), 'value' => getRentNumber(1), 'url' => dol_buildpath('/ultimateimmo/rent/immorent_list.php', 1).'?search_preavis=1'),
			array('title' => $langs->trans('MenuImmoNotActiveRent'), 'value' => getRentNumber(2), 'url' => dol_buildpath('/ultimateimmo/rent/immorent_list.php', 1).'?search_preavis=2'),
        )
    );
}

if ($user->rights->ultimateimmo->read) {
    $globalboxes[] = array('name' => strtoupper($langs->trans('MenuImmoRenter')), 'color' =>'#C19875',
        'url' => dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1),
        'url_add' => dol_buildpath('/ultimateimmo/renter/immorenter_card.php?action=create', 1),
        'right' => $user->rights->ultimateimmo->read,
        'lines' => array(
            array('title' => $langs->trans('MenuImmoActiveRenter'), 'value' => getRenterNumber(1), 'url' => dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1).'?search_status=1'),
			array('title' => $langs->trans('MenuImmoNotActiveRenter'), 'value' => getRenterNumber(0), 'url' => dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1).'?search_status=0'),
        )
    );
}


/*
 * Actions
 */

// Check if company name is defined (first install)
if (!isset($conf->global->MAIN_INFO_SOCIETE_NOM) || empty($conf->global->MAIN_INFO_SOCIETE_NOM))
{
    header("Location: ".DOL_URL_ROOT."/admin/index.php?mainmenu=home&leftmenu=setup&mesg=setupnotcomplete");
    exit;
}
if (count($conf->modules) <= (empty($conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING)?1:$conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING))	// If only user module enabled
{
    header("Location: ".DOL_URL_ROOT."/admin/index.php?mainmenu=home&leftmenu=setup&mesg=setupnotcomplete");
    exit;
}
if (GETPOST('addbox'))	// Add box (when submit is done from a form when ajax disabled)
{
    $zone=GETPOST('areacode', 'aZ09');
    $userid=GETPOST('userid', 'int');
    $boxorder=GETPOST('boxorder', 'aZ09');
    $boxorder.=GETPOST('boxcombo', 'aZ09');

    $result=UltimateImmoInfoBox::saveboxorder($db, $zone, $boxorder, $userid);
    if ($result > 0) setEventMessages($langs->trans("BoxAdded"), null);
}

/*
 * View
 */

if (! is_object($form)) $form=new Form($db);

// Translations
$langs->loadLangs(array("admin", "ultimateimmo@gultimateimmo"));

// Title
$title = $langs->trans("UltimateImmoDashboard");
if (! empty($conf->global->MAIN_APPLICATION_TITLE)) $title=$langs->trans("HomeArea").' - '.$conf->global->MAIN_APPLICATION_TITLE;

llxHeader('', $title);
$resultboxes = UltimateImmoGetBoxesArea($user, "0");    // Load $resultboxes (selectboxlist + boxactivated + boxlista + boxlistb)
$morehtmlright = $resultboxes['selectboxlist'];

print load_fiche_titre($langs->trans("UltimateImmoDashboard"), $morehtmlright, 'ultimateimmo_minimized@ultimateimmo');
print '<div class="dashboardBtnContainer">'.$button.'</div>';

/*
 * Demo text
 */
if ($conf->global->ULTIMATEIMMO_DEMO_ACTIVE == 1 && !empty($conf->global->ULTIMATEIMMO_DEMO_HOME)) {
    print '<div class="ultimateimmo-demo-div">';
    print $conf->global->ULTIMATEIMMO_DEMO_HOME;
    print '</div>';
    print '<div class="clearboth"></div>';
}

/*
 * Global synthesis
 */

print '<div class="fichecenter ultimateimmo-grid">';

foreach ($globalboxes as $globalbox) {

    print '<div class="ultimateimmo-card">';
    print '<div class="ultimateimmo-left-side" style="background-color: '.$globalbox['color'].';"><i class="fa '.$globalbox['icon'].' icon"></i></div>';
    print '<div class="ultimateimmo-right-side"><div class="inner"><b style="color: '.$globalbox['color'].';">'.$globalbox['name'].'</b>';
    if (!empty($globalbox['url_add']) && $globalbox['right'])
        print '<a href="'.$globalbox['url_add'].'" class="ultimateimmo-rounded-btn"><i class="fa fa-plus-circle fa-2x" style="color: '.$globalbox['color'].';"></i></a>';
    foreach ($globalbox['lines'] as $line) {
        print '<div class="line-info">'.$line['title'].' : <a href="'.$line['url'].'"><span style="background-color: '.$globalbox['color'].'">' . $line['value'] . '</span></a></div>';
    }
    print '</div></div>';
    print '</div>';
}

print '</div>';

// Separator
print '<div class="clearboth"></div>';

print '<div class="fichecenter fichecenterbis">';

/*
 * Show boxes
 */


$globalboxes=[];
if ($user->rights->ultimateimmo->read) {

	$sql = "SELECT loc.lastname as nom, ";
	$sql .= " SUM(rec.balance) as totalbalance";
	$sql .= " FROM " . MAIN_DB_PREFIX . "ultimateimmo_immoreceipt as rec";
	//$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immopayment as p ON rec.rowid = p.fk_receipt";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorenter as loc ON loc.rowid = rec.fk_renter";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immoproperty as prop ON prop.rowid = rec.fk_property";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ultimateimmo_immorent as rent ON rent.rowid = rec.fk_rent";
	$sql .= " WHERE rec.paye <> 1 AND rent.preavis = 1  ";
	$sql .= " GROUP BY loc.lastname";
	$sql .= " ORDER BY SUM(rec.balance) DESC";

	$resql = $db->query($sql);

	if ($resql) {
		$num = $db->num_rows($resql);
		$lineData = [];
		if ($num > 0) {
			while ($objp = $db->fetch_object($resql)) {
				$lineData[]=array(
					'title' => $objp->nom,
					'value' => price($objp->totalbalance),
					'url' => dol_buildpath('custom/ultimateimmo/payment/immopayment_card.php', 1).'?action=createall&search_renter='.urlencode($objp->nom));
			}

			$globalboxes[] = array('name' => strtoupper($langs->trans('RenterLetToPay')), 'color' => '#C19875',
				'url' => dol_buildpath('/ultimateimmo/payment/immopayment_card.php', 1),
				'right' => $user->rights->ultimateimmo->read,
				'lines' => $lineData
			);
		}
	}
}

foreach ($globalboxes as $globalbox) {

    print '<div class="ultimateimmo-card-list">';
    print '<div class="ultimateimmo-left-side" style="background-color: '.$globalbox['color'].';"><i class="fa '.$globalbox['icon'].' icon"></i></div>';
    print '<div class="ultimateimmo-right-side"><div class="inner"><b style="color: '.$globalbox['color'].';">'.$globalbox['name'].'</b>';
    if (!empty($globalbox['url_add']) && $globalbox['right'])
        print '<a href="'.$globalbox['url_add'].'" class="ultimateimmo-rounded-btn"><i class="fa fa-plus-circle fa-2x" style="color: '.$globalbox['color'].';"></i></a>';
    foreach ($globalbox['lines'] as $line) {
        print '<div class="line-info">'.$line['title'].' : <a href="'.$line['url'].'"><span style="background-color: '.$globalbox['color'].'">' . $line['value'] . '</span></a></div>';
    }
    print '</div></div>';
    print '</div>';
}


// End of page
llxFooter();
$db->close();

/**
 * Get array with HTML tabs with boxes of a particular area including personalized choices of user.
 *
 * @param   User    $user           Object User
 * @param   String  $areacode       Code of area for pages ('0'=value for Home page)
 * @return  array
 */
function UltimateImmoGetBoxesArea($user, $areacode)
{
    global $conf,$langs,$db;

    $confuserzone='MAIN_BOXES_'.$areacode;

    // $boxactivated will be array of boxes enabled into global setup
    // $boxidactivatedforuser will be array of boxes choosed by user

    $selectboxlist='';
    $boxactivated=UltimateImmoInfoBox::listBoxes($db, 'activated', $areacode, (empty($user->conf->$confuserzone)?null:$user), array(), 0);  // Search boxes of common+user (or common only if user has no specific setup)
    $boxidactivatedforuser=array();
    foreach ($boxactivated as $box)
    {
        if (empty($user->conf->$confuserzone) || $box->fk_user == $user->id) $boxidactivatedforuser[$box->id]=$box->id; // We keep only boxes to show for user
    }

    // Define selectboxlist
    $arrayboxtoactivatelabel=array();
    if (! empty($user->conf->$confuserzone))
    {
        $boxorder='';
        $langs->load("boxes");  // Load label of boxes
        foreach ($boxactivated as $box)
        {
            if (! empty($boxidactivatedforuser[$box->id])) continue;    // Already visible for user
            $label=$langs->transnoentitiesnoconv($box->boxlabel);
            if (preg_match('/graph/', $box->class) && empty($conf->browser->phone))
            {
                $label=$label.' <span class="fa fa-bar-chart"></span>';
            }
            $arrayboxtoactivatelabel[$box->id]=$label; // We keep only boxes not shown for user, to show into combo list
        }
        foreach ($boxidactivatedforuser as $boxid)
        {
            if (empty($boxorder)) $boxorder.='A:';
            $boxorder.=$boxid.',';
        }

        // Class Form must have been already loaded
        $selectboxlist.='<!-- Form with select box list -->'."\n";
        $selectboxlist.='<form id="addbox" name="addbox" method="POST" action="'.$_SERVER["PHP_SELF"].'" style="display:inline">';
        if ((float) DOL_VERSION >= 11) {
            $selectboxlist.='<input type="hidden" name="token" value="' . newToken() . '">';
        } else {
            $selectboxlist='<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        }
        $selectboxlist.='<input type="hidden" name="addbox" value="addbox">';
        $selectboxlist.='<input type="hidden" name="userid" value="'.$user->id.'">';
        $selectboxlist.='<input type="hidden" name="areacode" value="'.$areacode.'">';
        $selectboxlist.='<input type="hidden" name="boxorder" value="'.$boxorder.'">';
        $selectboxlist.=Form::selectarray('boxcombo', $arrayboxtoactivatelabel, -1, $langs->trans("ChooseBoxToAdd").'...', 0, 0, '', 0, 0, 0, 'ASC', 'maxwidth150onsmartphone', 0, 'hidden selected', 0, 1);

        if (empty($conf->use_javascript_ajax)) $selectboxlist.=' <input type="submit" class="button" value="'.$langs->trans("AddBox").'">';
        $selectboxlist.='</form>';
        if (! empty($conf->use_javascript_ajax))
        {
            include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            $selectboxlist.=ajax_combobox("boxcombo");
        }
    }
    // Javascript code for dynamic actions
    if (! empty($conf->use_javascript_ajax))
    {
        $box_file = dol_buildpath('/ultimateimmo/ajax/box.php', 1);

        $selectboxlist.='<script type="text/javascript" language="javascript">

             // To update list of activated boxes
             function updateBoxOrder(closing) {
                 var left_list = cleanSerialize(jQuery("#boxhalfleft").sortable("serialize"));
                 var right_list = cleanSerialize(jQuery("#boxhalfright").sortable("serialize"));
                 var boxorder = \'A:\' + left_list + \'-B:\' + right_list;
                 if (boxorder==\'A:A-B:B\' && closing == 1)  // There is no more boxes on screen, and we are after a delete of a box so we must hide title
                 {
                     jQuery.ajax({
                         url: \''.$box_file.'?closing=0&boxorder=\'+boxorder+\'&zone='.$areacode.'&userid=\'+'.$user->id.',
                         async: false
                     });
                     // We force reload to be sure to get all boxes into list
                     window.location.search=\'mainmenu='.GETPOST("mainmenu", "aZ09").'&leftmenu='.GETPOST('leftmenu', "aZ09").'&action=delbox\';
                 }
                 else
                 {
                     jQuery.ajax({
                         url: \''.$box_file.'?closing=\'+closing+\'&boxorder=\'+boxorder+\'&zone='.$areacode.'&userid=\'+'.$user->id.',
                         async: true
                     });
                 }
             }

             jQuery(document).ready(function() {
                 jQuery("#boxcombo").change(function() {
                 var boxid=jQuery("#boxcombo").val();
                     if (boxid > 0) {
                         var left_list = cleanSerialize(jQuery("#boxhalfleft").sortable("serialize"));
                         var right_list = cleanSerialize(jQuery("#boxhalfright").sortable("serialize"));
                         var boxorder = \'A:\' + left_list + \'-B:\' + right_list;
                         jQuery.ajax({
                             url: \''.$box_file.'?boxorder=\'+boxorder+\'&boxid=\'+boxid+\'&zone='.$areacode.'&userid='.$user->id.'\',
                             async: false
                         });
                         window.location.search=\'mainmenu='.GETPOST("mainmenu", "aZ09").'&leftmenu='.GETPOST('leftmenu', "aZ09").'&action=addbox&boxid=\'+boxid;
                     }
                 });';
        if (! count($arrayboxtoactivatelabel)) $selectboxlist.='jQuery("#boxcombo").hide();';
        $selectboxlist.='

                 jQuery("#boxhalfleft, #boxhalfright").sortable({
                     handle: \'.boxhandle\',
                     revert: \'invalid\',
                     items: \'.boxdraggable\',
                     containment: \'document\',
                     connectWith: \'#boxhalfleft, #boxhalfright\',
                     stop: function(event, ui) {
                         updateBoxOrder(1);  /* 1 to avoid message after a move */
                     }
                 });

                 jQuery(".boxclose").click(function() {
                     var self = this;    // because JQuery can modify this
                     var boxid=self.id.substring(8);
                     var label=jQuery(\'#boxlabelentry\'+boxid).val();
                     console.log("We close box "+boxid);
                     jQuery(\'#boxto_\'+boxid).remove();
                     if (boxid > 0) jQuery(\'#boxcombo\').append(new Option(label, boxid));
                     updateBoxOrder(1);  /* 1 to avoid message after a remove */
                 });

             });'."\n";

        $selectboxlist.='</script>'."\n";
    }

    // Define boxlista and boxlistb
    $nbboxactivated=count($boxidactivatedforuser);
    $boxlista = '';
    $boxlistb = '';

    if ($nbboxactivated)
    {
        $langs->load("boxes");
        $langs->load("projects");

        $emptybox=new UltimateImmoModeleBoxes($db);

        $boxlista.="\n<!-- Box left container -->\n";

        // Define $box_max_lines
        $box_max_lines=5;
        if (! empty($conf->global->ULTIMATEIMMO_BOXES_MAXLINES)) $box_max_lines=$conf->global->ULTIMATEIMMO_BOXES_MAXLINES;

        $ii=0;
        foreach ($boxactivated as $key => $box)
        {
			var_dump($box);
            if ((! empty($user->conf->$confuserzone) && $box->fk_user == 0) || (empty($user->conf->$confuserzone) && $box->fk_user != 0)) continue;
            if (empty($box->box_order) && $ii < ($nbboxactivated / 2)) $box->box_order='A'.sprintf("%02d", ($ii+1)); // When box_order was not yet set to Axx or Bxx and is still 0
            if (preg_match('/^A/i', $box->box_order)) // column A
            {
                $ii++;
                //print 'box_id '.$boxactivated[$ii]->box_id.' ';
                //print 'box_order '.$boxactivated[$ii]->box_order.'<br>';
                // Show box
                $box->loadBox($box_max_lines);
                $boxlista.= $box->outputBox();
            }
        }

        if (empty($conf->browser->phone))
        {
            $emptybox->box_id='A';
            $emptybox->info_box_head=array();
            $emptybox->info_box_contents=array();
            $boxlista.= $emptybox->outputBox(array(), array());
        }
        $boxlista.= "<!-- End box left container -->\n";

        $boxlistb.= "\n<!-- Box right container -->\n";

        $ii=0;
        foreach ($boxactivated as $key => $box)
        {
            if ((! empty($user->conf->$confuserzone) && $box->fk_user == 0) || (empty($user->conf->$confuserzone) && $box->fk_user != 0)) continue;
            if (empty($box->box_order) && $ii < ($nbboxactivated / 2)) $box->box_order='B'.sprintf("%02d", ($ii+1)); // When box_order was not yet set to Axx or Bxx and is still 0
            if (preg_match('/^B/i', $box->box_order)) // colonne B
            {
                $ii++;
                //print 'box_id '.$boxactivated[$ii]->box_id.' ';
                //print 'box_order '.$boxactivated[$ii]->box_order.'<br>';
                // Show box
                $box->loadBox($box_max_lines);
                $boxlistb.= $box->outputBox();
            }
        }

        if (empty($conf->browser->phone))
        {
            $emptybox->box_id='B';
            $emptybox->info_box_head=array();
            $emptybox->info_box_contents=array();
            $boxlistb.= $emptybox->outputBox(array(), array());
        }

        $boxlistb.= "<!-- End box right container -->\n";
    }

    return array('selectboxlist'=>count($boxactivated)?$selectboxlist:'', 'boxactivated'=>$boxactivated, 'boxlista'=>$boxlista, 'boxlistb'=>$boxlistb);
}
