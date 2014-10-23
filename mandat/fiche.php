<?php
/* Copyright (C) 20013 Thierry LECERF <thierry.lecerf@t3s-it.com>
 
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
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

/**
 *  \file           /gestimmo/mandat/fiche.php
 *  \brief          Page fiche mandats
*  \version     $Id$
*/
// inclusion des finchier/class/object dolibarr

$res=@include("../../main.inc.php");                // For root directory
if (! $res) $res=@include("../../../main.inc.php"); // For "custom" directory
if (! $res) die("Include of main fails");
// Pour les biens immobilier
//dol_include_once('/gestimmo/class/logement.class.php');
// module gestimmo
dol_include_once('/immobilier/core/lib/immobilier.lib.php');
// fonction dolibarr general
//dol_include_once('/core/lib/function.lib.php');
//dol_include_once('/core/class/soc.class.php');
dol_include_once('/core/class/html.formcompany.class.php');
dol_include_once('/core/class/doleditor.class.php');
//dol_include_once('/gestimmo/class/html.formgestimmo.class.php');
// pour les mandat
dol_include_once('/immobilier/class/mandat.class.php');
dol_include_once('/immobilier/class/local.class.php');
dol_include_once('/immobilier/class/html.immobilier.php');
// Security check
//if (!$user->rights->agefodd->lire) accessforbidden();

$mesg = '';
$now=dol_now();
$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$arch=GETPOST('arch','int');

$url_return=GETPOST('url_return','alpha');
// Langs
$langs->load ( "immobilier@immobilier" );
/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" )
{
    $gestimmo = new Mandat($db);
    $gestimmo->id=$id;
    $result = $gestimmo->delete($user);

    if ($result > 0)
    {
        Header ( "Location: liste.php");
        exit;
    }
    else
    {
        dol_syslog("gestimmo::mandat::card error=".$agf->error, LOG_ERR);
        $mesg='<div class="error">'.$langs->trans("mandat ERREUR").':'.$agf->error.'</div>';
    }
}


/*
 * Actions archive/active
*/

/*
 * Action update (fiche mandat)
*/
if ($action == 'update' )
{
    if (! $_POST["cancel"] )
    {
        $agf = new Mandat($db);

        $result = $agf->fetch($id);
        if($result > 0) 
        {
           $agf->ref_interne = GETPOST('ref_interne','alpha');
           
           $agf->note_interne =GETPOST('note_interne');
           $agf->note_public=GETPOST('note_public','alpha');
           $agf->ref_interne = GETPOST('ref_interne','alpha');
           
           $agf->fk_soc=GETPOST('contact');
       //    $agf->fk_biens=GETPOST('biens');
          $mydatecloture=
          $datep  = dol_mktime(12, 0, 0, GETPOST('remonth'),  GETPOST('reday'),  GETPOST('reyear'));
        //  $datep=dol_mktime(12, 0, 0, GETPOST('cloture'), GETPOST('cloture'), GETPOST('cloture'));
      //  $agf->date_cloture=GETPOST('cloture');
        $agf->date_cloture=$datep;
          $agf->mise_en_service=GETPOST('mise_en_service');
          dol_syslog("gestimmo::site::card AFFICHE DATE=".$datep, LOG_ERR);
       //    $agf->status=GETPOST('status');
           
          // $agf->fk_bails=GETPOST('biens');
        /*
          
          
          
          
          '';
          $agf->fin_validite='';
          
          $agf->fk_commercial='';
          $agf->notes_private='';
          $agf->notes_public='';
          $agf->fk_user_author='';
          $agf->datec='';
          $agf->fk_user_mod='';
        */
        
              $result = $agf->update($user);
     
            if ($result > 0)
            {
                Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
                exit;
            }
            else
            {
                dol_syslog("gestimmo::mandat ::fiche error=".$agf->error, LOG_ERR);
                $mesg='<div class="error">'.$agf->error.'</div>';
                $action = 'edit';
            }
        }
        else
        {
            dol_syslog("gestimmo::site::card error=".$agf->error, LOG_ERR);
            $mesg='<div class="error">'.$agf->error.'</div>';
        }

    }
  }


/*
 * Action create (fiche bien immo)
*/

if ($action == 'create_confirm' )
{
    if (! $_POST["cancel"])
    {
            $agf = new Mandat($db);
            $agf->ref_interne = GETPOST('ref','alpha');
			$agf->fk_soc ="1";
			$agf->fk_biens = "0";
            $agf->descriptif = GETPOST('descriptif','alpha');
            $agf->entity = getpost('entity');
          	$agf->datec=$now;
			$agf->date_contrat=$now;
			$agf->date_creation=$now;
			$agf->date_cloture=$now;
			$agf->status="1";
			$agf->mise_en_service=$now;
			$agf->fin_validite=$now;
			$agf->fk_bails='0';
			$agf->fk_commercial='1';
			$agf->fk_user_author=$user;
			$agf->fk_user_mod='1';
			$agf->fk_user_author='1';
            $result = $agf->create($user);

        if ($result > 0)
        {
            if($url_return)
                Header ( "Location: ".$url_return);
            else
                Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$result);
            exit;
        }
        else
        {
            dol_syslog("Gestimmo::site::card error=".$agf->error, LOG_ERR);
            $mesg='<div class="error">'.$agf->error.'</div>';
        }

    }
    else
    {
        Header ( "Location: list.php");
        exit;
    }
}



/*
 * View
*/

$title = ($action == 'create' ? $langs->trans("Creation mandat") : $langs->trans("Modif Mandat"));
llxHeader('',$title);

$form = new Form($db);

dol_htmloutput_mesg($mesg);

/*
 * Action create
*/
if ($action == 'create' )
{
    print_barre_liste($langs->trans("Création Mandat Immobilier"),"", "","","","",'',0);   
   // $formcompany = new FormCompany($db);
    $formmandat = new FormImmobilier ($db);
    print_fiche_titre($langs->trans("Mandats"));

    print '<form name="create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
    print '<input type="hidden" name="action" value="create_confirm">'."\n";

    print '<input type="hidden" name="url_return" value="'.$url_return.'">'."\n";
    print '<input type="hidden" name="entity" value="1">'."\n";
    print '<table class="border" width="100%">'."\n";

    print '<tr><td width="20%"><span class="fieldrequired">'.$langs->trans("Ref Mandat").'</span></td>';
    print '<td><input name="ref" class="flat" size="50" value=""></td></tr>';

    //print '<tr><td><span class="fieldrequired">'.$langs->trans("Mandat").'</span></td>';
    //print '<td>'.$formmandat->select_mandat('','mandat','',1,1,0).'</td></tr>';
    
    
    print '</table>';
    print '</div>';


    print '<table style=noborder align="right">';
    print '<tr><td align="center" colspan=2>';
    print '<input type="submit" name="importadress" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
    print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
    print '</td></tr>';
    print '</table>';
    print '</form>';

}
else
{
    // Affichage de la fiche des mandat gere 
    if ($id)
    {       
        print_barre_liste($langs->trans("Gestion des Bien Immobilier"),"", "","","","",'',0);
        $agf = new Mandat($db);
        $result = $agf->fetch($id);

        if ($result)
        {
            $head = mandat_prepare_head($agf);

            dol_fiche_head($head, 'fiche', $langs->trans("Gestion des Mandats"), 0, 'contrat');
// reference propio 
            
$soc = new Societe($db);
$soc->fetch($agf->fk_soc);


// Affichage en mode "édition"
            if ($action == 'edit')
            {
                $formcompany = new FormCompany($db);
                // TODO 
                $formimmo = new FormImmobilier($db);
                
                
				print_barre_liste($langs->trans("Modification Mandat"),"", "","","","",'',0);
                
                // debut formulaire mis a jours mandat
                print '<form name="update" action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
                print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
                print '<input type="hidden" name="action" value="update">'."\n";
                print '<input type="hidden" name="id" value="'.$id.'">'."\n";
                print '<table class="border" width="100%">'."\n";
                // referance mandat interne
                print '<tr><td>'.$langs->trans("REF interne").'</td>';
                print '<td><input name="ref_interne" class="flat" size="50" value="'.$agf->ref_interne.'"></td></tr>';
                // numero id a cacher par la suite
        //        print '<tr><td width="30%">'.$langs->trans("Id").'</td>';
        //        print '<td>'.$agf->id.'</td></tr>';
  // TODO  warnig creation porprion obligatoire               
                // proprio
                print '<tr><td>'.$langs->trans('Proprio').'</td><td colspan="5">'.$soc->getNomUrl(1).'</td>';
             //    print '<tr><td>'.$langs->trans("Propio :").'</td>';
              //  print '<td>'.$agf->fk_soc.'</td>';
              //  print '<td>'.$formimmo->select_contacts_combobox($agf->fk_sock,$agf->fk_sock,'contact',1).'</td>';
              print '<tr><td> Selection :'.$formimmo->select_contacts_combobox($agf->fk_sock,$agf->fk_sock,'contact',1).'</td></tr>';
            print '</tr>';
 // TODO gestion des dates pour l instant simple saisie   
             
              print '<table class="liste_titre" width="75%">';
    print '<tr><td class="liste_titre""> Gestion des Dates </td></tr>';  
//    print_barre_liste($langs->trans("Gestion des Dates"),"", "","","","",'',1);
   // Date
              print '<tr><td >'.$langs->trans('Date').'</td><td colspan="2">';
             
                $form->select_date($agf->datec,'datec','','','',"update");
              print '</td>';
     // Date
               print '<td >'.$langs->trans('datecontrat').'</td><td colspan="2">';
                    $form->select_date($agf->date_contrat,'datecontrat','','','',"update");
                     $datecontrat = dol_mktime(0, 0, 0, GETPOST('datecontratmonth'), GETPOST('datecontratday'), GETPOST('datecontratyear'));
               print '</td>';
   // Date
    print '<td >'.$langs->trans('Datecloture').'</td><td colspan="2">';
    $form->select_date($agf->date_cloture,'cloture','','','',"update");
    print '</td>';
   
 // Date
    print '<td >'.$langs->trans('mise_en_service').'</td><td colspan="2">';
    $form->select_date($agf->mise_en_service,'mise_en_service','','','',"update");
    print '</td></tr>';
    print '</table>';
 /*
  // Date of proposal
    print '<tr>';
    print '<td>';
    print '<table class="nobordernopadding" width="100%"><tr><td>';
    print $langs->trans('Date');
    print '</td>';
    if ($action != 'editdate' ) print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=editdate&amp;id='.$agf->id.'">'.img_edit($langs->trans('SetDate'),1).'</a></td>';
    print '</tr></table>';
    print '</td><td colspan="3">';
    if ( $action == 'editdate')
    {
        print '<form name="editdate" action="'.$_SERVER["PHP_SELF"].'?id='.$agf->id.'" method="post">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        print '<input type="hidden" name="action" value="setdate">';
        $form->select_date($agf->datec,'re','','',0,"editdate");
        print '<input type="submit" class="button" value="'.$langs->trans('Modify').'">';
        print '</form>';
    }
    else
    {
        if ($agf->datec)
        {
            print dol_print_date($afg->datec,'daytext');
        }
        else
        {
            print '&nbsp;';
        }
    }
    print '</td>';
    print '</td></tr>';
   */
      /*          
                print '<tr><td valign="top">'.$langs->trans("Note privés").'</td><td>';
        $doleditor = new DolEditor('note_private',$agf->notes_private, '', 160, 'dolibarr_notes', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 90);
       
        $doleditor->Create();
         print '<tr><td valign="top">'.$langs->trans("Note Public").'</td><td>';
        $doleditor2 = new DolEditor('note_public',$agf->notes_public, '', 160, 'dolibarr_notes', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 90);
       
        $doleditor2->Create();
      */
       print "</td></tr>";   
	        // update_note(dol_html_entity_decode(GETPOST('note_public'), ENT_QUOTES),'_public')
     	    //  print '<tr><td>'.$langs->trans("fk_soc").'</td>';
      	    //  print '<td><input name="fk_soc" class="flat" size="10" value="'.$agf->fk_soc.'"></td>';
			//	print '<tr><td>'.$langs->trans("fk_bails").'</td>';
            //    print '<td><input name="fk_bails" class="flat" size="10" value="'.$agf->fk_bails.'"></td>';
			/*
            	print '<tr><td width="30%" class="nowrap"><span class="fieldrequired">'.$langs->trans("Date creation").'</span></td><td>';
				$form->select_date($agf->datec,'datec',1,1,1,"action",1,1,0,0,'update');
               // Date
	//print '<tr><td class="fieldrequired">'.$langs->trans('Datec').'</td><td colspan="2">';
	//$form->select_date('','datec','','','',"update");
	print '</td></tr>';
			 
             */
                print '</table>';
                print '</div>';
                print '<table style=noborder align="right">';
                print '<tr><td align="center" colspan=2>';
                print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
              //  print '<input type="submit" name="importadress" class="butAction" value="'.$langs->trans("AgfImportCustomerAdress").'"> &nbsp; ';
                print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
                print '</td></tr>';
                print '</table>';
                print '</form>';

                print '</div>'."\n";
            }
            else
            {
                // Affichage en mode "consultation"
                
                // copie object

                print_barre_liste($langs->trans("Mandat Immobilier"),"", "","","","",'',0);
                
                /*
                 * Confirmation de la suppression
                */
                if ($action == 'delete')
                {
                    $ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id,$langs->trans("Supression Mandat"),$langs->trans("Voulez vous vraiment supprimer ce biens?"),"confirm_delete",'','',1);
                    if ($ret == 'html') print 'c est fait .....<br>';
                }
                /*
                 * Confirmation de l'archivage/activation suppression
                
                if ($action=='archive' || $action=='active')
                {
                    if ($action == 'archive') $value=1;
                    if ($action == 'active') $value=0;

                    $ret=$form->form_confirm($_SERVER['PHP_SELF']."?arch=".$value."&id=".$id,$langs->trans("AgfFormationArchiveChange"),$langs->trans("AgfConfirmArchiveChange"),"arch_confirm_delete",'','',1);
                    if ($ret == 'html') print '<br>';
                }
*/
				//$showphoto=$agf->is_photo_available($conf->gestimmo->multidir_output[$agf->entity]);
                $formmandat = new FormImmobilier($db);
              
               $titre="  MANDAT :  ";
                                
            
			 print_titre($titre);
                 print "</table>";

                
                print '<table class="border" width="100%">';

                print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
                print '<td>'.$form->showrefnav($agf,'id','',1,'rowid','id').'</td></tr>';

                print '<tr><td>'.$langs->trans("Ref Mandat").'</td>';
                print '<td>'.$agf->ref_interne.'</td></tr>';

                print '&nbsp;';
                print '</tr>';
/* $this->ref_interne = $obj->ref_interne;
                $this->fk_soc = $obj->fk_soc;
                $this->fk_biens = $obj->fk_biens;
                $this->date_contrat = $this->db->jdate($obj->date_contrat);
 
 */
 //print '<tr><td valign="top">'.$langs->trans("Proprio").'</td><td>';
                if ($agf->fk_soc)
                {    $propro= new Societe($db);
                    
   // $soc = new Societe($db);
 $propro->fetch($agf->fk_soc);    
 print '<tr><td>'.$langs->trans('Proprio').'</td><td colspan="5">'.$soc->getNomUrl(1).'</td>';            
                    print '<a href="'.dol_buildpath('/comm/fiche.php',1).'?socid='.$agf->fk_soc.'">';
                  //  print img_object($langs->trans("Proprio"),"").' '.dol_trunc($proprio->nom,20).'</a>';
                    
                }
                else
                {
                    print '&nbsp;';
                }
                print '</td></tr>';

                
                
  //              
                
                
               print '<tr><td>'.$langs->trans("Date Contrat ").'</td>';
               print '<td>'.dol_print_date($agf->date_contrat).'</td></tr>';
               print '<tr><td>'.$langs->trans("Date Mise en service ").'</td>';
               print '<td>'.dol_print_date($agf->mise_en_service).'</td></tr>';
               print '<tr><td>'.$langs->trans("Date debut Contrat ").'</td>';
               print '<td>'.dol_print_date($agf->date_contrat).'</td></tr>';
               print '<tr><td>'.$langs->trans("Date Fin Contrat ").'</td>';
               print '<td>'.dol_print_date($agf->fin_validite).'</td></tr>';
               print '<tr><td>'.$langs->trans("status").'</td>';
               print '<tr><td valign="top">'.$langs->trans("note privé").'</td>';
               print '<td>'.nl2br($agf->notes_private).'</td></tr>';
               print '<tr><td valign="top">'.$langs->trans("note Public").'</td>';
               print '<td>'.nl2br($agf->notes_public).'</td></tr>';
                
 
               // print '<tr><td rowspan=3 valign="top">'.$langs->trans("Address").'</td>';
                //print '<td>'.$agf->adresse.'</td></tr>';

                //print '<tr>';
                //print '<td>'.$agf->fk_departement.' - '.$agf->town.'</td></tr>';

                print '<tr><td>'.$langs->trans("fk_bails ").'</td>';
                print '<td>'.$agf->fk_bails .'</td></tr>';
                print '<tr><td>'.$langs->trans("date creation ").'</td>';
                print '<td>'.dol_print_date($agf->datec) .'</td></tr>';
         	    print '<tr><td>'.$langs->trans("fk_bails ").'</td>';
                print '<td>'.$agf->fk_bails .'</td></tr>';
                print '</td></tr>';
				print '<tr><td>'.$langs->trans("fk_soc ").'</td>';
                print '<td>'.$agf->fk_soc .'</td></tr>';
                print '</td></tr>';
/*
               
  */             
                print "</table>";
                }
                print '</div>';
            }

        }
        else
        {
            dol_print_error($db);
        }
    }



/*
 * Barre d'actions
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' )
{
   // if ($user->rights->immobilier->creer)
    {
        print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
    }
   // else
    {
   //     print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
    }
    //if ($user->rights->immobilier->creer)
    {
        print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
    }
    //else
    {
     //   print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Delete').'</a>';
    }
    //if ($user->rights->immobilier->modifier)
    {
      //  if ($agf->archive == 0)
        {
            print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=create">'.$langs->trans('nouveau').'</a>';
        }
        //else
        {
       //     print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=active&id='.$id.'">'.$langs->trans('AgfActiver').'</a>';
        }
    }
    //else
    {
      //  print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfArchiver').'/'.$langs->trans('AgfActiver').'</a>';
    }


}

print '</div>';

llxFooter('$Date: 2010-03-30 20:58:28 +0200 (mar. 30 mars 2010) $ - $Revision: 54 $');

?>
