<?php
/* Copyright (C) 2009-2010  Erick Bullier   <eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011  Regis Houssin   <regis@dolibarr.fr>
* Copyright (C) 2012       Florian Henry   <florian.henry@open-concept.pro>
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
 *  \file           /agefodd/site/card.php $
 *  \brief          Page fiche site de formation
*  \version     $Id$
*/

$res=@include("../../main.inc.php");                // For root directory
if (! $res) $res=@include("../../../main.inc.php"); // For "custom" directory
if (! $res) die("Include of main fails");

dol_include_once('/gestimmo/class/logement.class.php');
dol_include_once('/gestimmo/lib/gestimmo.lib.php');
dol_include_once('/core/lib/function.lib.php');
dol_include_once('/core/class/html.formcompany.class.php');
dol_include_once('/core/class/doleditor.class.php');
dol_include_once('/gestimmo/class/html.formgestimmo.class.php');
dol_include_once('/gestimmo/class/mandat.class.php');
// Security check
//if (!$user->rights->agefodd->lire) accessforbidden();

$mesg = '';

$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$arch=GETPOST('arch','int');

$url_return=GETPOST('url_return','alpha');

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" )
{
    $gestimmo = new Logement($db);
    $gestimmo->id=$id;
    $result = $gestimmo->delete($user);

    if ($result > 0)
    {
        Header ( "Location: liste.php");
        exit;
    }
    else
    {
        dol_syslog("gestimmo::site::card error=".$agf->error, LOG_ERR);
        $mesg='<div class="error">'.$langs->trans("AgfDeleteErr").':'.$agf->error.'</div>';
    }
}


/*
 * Actions archive/active
*/
if ($action == 'arch_confirm_delete' )
{
    if ($confirm == "yes")
    {
        $agf = new Logement($db);

        $result = $agf->fetch($id);

        $agf->archive = $arch;
        $result = $agf->update($user);

        if ($result > 0)
        {
            Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
            exit;
        }
        else
        {
            dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
            $mesg='<div class="error">'.$agf->error.'</div>';
        }

    }
    else
    {
        Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
        exit;
    }
}

/*
 * Action update (fiche site de formation)
*/
if ($action == 'update' && $user->rights->agefodd->creer)
{
    if (! $_POST["cancel"] && ! $_POST["importadress"])
    {
        $agf = new Logement($db);

        $result = $agf->fetch($id);
        if($result > 0) 
        {
            $agf->ref = GETPOST('ref','alpha');
            $agf->adresse = GETPOST('adresse','alpha');
            $agf->fk_departement= GETPOST('zipcode','alpha');
            $agf->town = GETPOST('town','alpha');
            $agf->fk_pays = GETPOST('country_id','int');
            
            $agf->fk_mandat = GETPOST('mandat','int');
           // $agf->dol_htmlcleanlastbr(GETPOST('descriptif'));
           $agf->descriptif = GETPOST('descriptif','alpha');
            $agf->nb_piece= GETPOST('nb_piece','alpha');
            $agf->superficie= GETPOST('superficie','alpha');
            $agf->dpe= GETPOST('dpe','alpha');
            $agf->loyer=GETPOST('loyer','alpha');
            $agf->charges= GETPOST('charges','alpha');
            $agf->caution= GETPOST('caution','alpha');
            $agf->Honoraire= GETPOST('Honoraire','alpha');
            $agf->Assurance = GETPOST('Assurance','alpha');
            $result = $agf->update($user);
    
            if ($result > 0)
            {
                Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
                exit;
            }
            else
            {
                dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
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
    elseif (! $_POST["cancel"] && $_POST["importadress"])   {

        $agf = new Agefodd_place($db);

        $result = $agf->fetch($id);
        $result = $agf->import_customer_adress($user);

        if ($result > 0)
        {
            Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
            exit;
        }
        else
        {
            dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
            $mesg='<div class="error">'.$agf->error.'</div>';
        }

    }else {
        Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
        exit;
    }
}


/*
 * Action create (fiche bien immo)
*/

if ($action == 'create_confirm' )
{
    if (! $_POST["cancel"])
    {
            $agf = new Logement($db);

        
             $agf->ref = GETPOST('ref','alpha');
           // $agf->adresse = GETPOST('adresse','alpha');
            //$agf->fk_departement= GETPOST('zipcode','alpha');
            //$agf->town = GETPOST('town','alpha');
            $agf->descriptif = GETPOST('descriptif','alpha');
            $agf->entity = getpost('entity');
        
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
            dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
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
 * Action create
*/
if ($action == 'create' )
{   $title = ($action == 'create' ? $langs->trans("Creation bail") : $langs->trans("Visu bail"));
llxHeader('',$title);
    print_barre_liste($langs->trans("Création Bien Immobilier"),"", "","","","",'',0);   
    $formcompany = new FormCompany($db);
    $formmandat = new Formgestimmo ($db);
    print_fiche_titre($langs->trans("creation d'un biens"));

    print '<form name="create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
    print '<input type="hidden" name="action" value="create_confirm">'."\n";

    print '<input type="hidden" name="url_return" value="'.$url_return.'">'."\n";
    print '<input type="hidden" name="entity" value="1">'."\n";
    print '<table class="border" width="100%">'."\n";

    print '<tr><td width="20%"><span class="fieldrequired">'.$langs->trans("ref interne").'</span></td>';
    print '<td><input name="ref" class="flat" size="50" value=""></td></tr>';

    print '<tr><td><span class="fieldrequired">'.$langs->trans("Mandat").'</span></td>';
    print '<td>'.$formmandat->select_mandat('','mandat','',1,1,0).'</td></tr>';

    print '<tr><td valign="top">'.$langs->trans("descriptif").'</td>';
    print '<td><textarea name="descriptif" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';

    
    
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
    // Affichage de la fiche des biens gere 
    if ($id)
    {
       // $title = ($action == 'create' ? $langs->trans("Creation bail") : $langs->trans("Visu bail"));
        llxHeader('',$title);
        print_barre_liste($langs->trans("Gestion des Bien Immobilier"),"", "","","","",'',0);
        $agf = new Logement($db);
        $result = $agf->fetch($id);

        if ($result)
        {
            $head = biens_prepare_head($agf);

            dol_fiche_head($head, 'card', $langs->trans("Gestion des biens"), 0, 'address');

            // Affichage en mode "édition"
            if ($action == 'edit')
            {
               // $formcompany = new FormCompany($db);
                $formimmo = new Formgestimmo($db);
				print_barre_liste($langs->trans("Modification biens"),"", "","","","",'',0);
                print '<form name="update" action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
                print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
                print '<input type="hidden" name="action" value="update">'."\n";
                print '<input type="hidden" name="id" value="'.$id.'">'."\n";

                print '<table class="border" width="100%">'."\n";
                print '<tr><td width="30%">'.$langs->trans("Id").'</td>';
                print '<td>'.$agf->id.'</td></tr>';

                print '<tr><td>'.$langs->trans("REF interne").'</td>';
                print '<td><input name="ref" class="flat" size="50" value="'.$agf->ref.'"></td></tr>';
// TO DO remplace select_compagy pae select_mandat, rajouter select locataire et select proprio
                print '<tr><td>'.$langs->trans("Mandat").'</td>';
                print '<td>'.$formimmo->select_mandat($agf->fk_mandat,'mandat','',0,1).'</td></tr>';

                print '<tr><td>'.$langs->trans("Address").'</td>';
                print '<td><input name="adresse" class="flat" size="50" value="'.$agf->adresse.'"></td>';


                print '<td>'.$langs->trans('CP').'</td><td>';
               // print $formcompany->select_ziptown($agf->fk_departement,'zipcode',array('town','selectcountry_id'),6).'';
               print $formimmo->select_depville($agf->fk_departement,'zipcode',array('town','selectcountry_id'),6).'';
               
			    print '</td><td>'.$langs->trans('Ville').'</td><td>';
               
               print $formimmo->select_depville($agf->town,'town',array('zipcode','selectcountry_id')).'</tr>';
               // print $formcompany->select_ziptown($agf->town,'town',array('zipcode','selectcountry_id')).'</tr>';

                print '<tr><td>'.$langs->trans("Pays").'</td>';
                print '<td>'.$form->select_country($agf->fk_pays,'country_id').'</td></tr>';

                print '<tr><td>'.$langs->trans("type").'</td>';
                print '<td><input name="nb_piece" class="flat" size="10" value="'.$agf->nb_piece.'"></td></tr>';

               // print '<tr><td valign="top">'.$langs->trans("Descriptif").'</td>';
               // print '<td><textarea name="descriptif" rows="3" cols="0" class="flat" style="width:360px;">'.nl2br($agf->descriptif).'</textarea></td></tr>';
      print '<tr><td valign="top">'.$langs->trans("Description").'</td><td>';
        $doleditor = new DolEditor('descriptif',$agf->descriptif, '', 160, 'dolibarr_notes', '', false, true, $conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, 4, 90);
       
        $doleditor->Create();
       print "</td></tr>";          
     	        print '<tr><td>'.$langs->trans("superficie").'</td>';
      	        print '<td><input name="superficie" class="flat" size="10" value="'.$agf->superficie.'"></td>';
				print '<tr><td>'.$langs->trans("dpe").'</td>';
                print '<td><input name="dpe" class="flat" size="10" value="'.$agf->dpe.'"></td>';
				print '<tr><td>'.$langs->trans("loyer").'</td>';
                print '<td><input name="loyer" class="flat" size="10" value="'.$agf->loyer.'"></td>';
				print '<tr><td>'.$langs->trans("charges").'</td>';
                print '<td><input name="charges" class="flat" size="10" value="'.$agf->charges.'"></td>';
				print '<tr><td>'.$langs->trans("caution").'</td>';
                print '<td><input name="caution" class="flat" size="10" value="'.$agf->caution.'"></td>';
 				print '<tr><td>'.$langs->trans("Honoraire").'</td>';
                print '<td><input name="Honoraire" class="flat" size="10" value="'.$agf->Honoraire.'"></td>';  
			    print '<tr><td>'.$langs->trans("Assurance").'</td>';
                print '<td><input name="Assurance" class="flat" size="10" value="'.$agf->Assurance.'"></td>'; 
                            
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
                print_barre_liste($langs->trans("Bien Immobilier"),"", "","","","",'',0);
                
                /*
                 * Confirmation de la suppression
                */
                if ($action == 'delete')
                {
                    $ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id,$langs->trans("Supression biens immo"),$langs->trans("Voulez vous vraiment supprimer ce biens?"),"confirm_delete",'','',1);
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
				$showphoto=$agf->is_photo_available($conf->gestimmo->multidir_output[$agf->entity]);
                $formmandat = new Formgestimmo($db);
              
               $titre="  MANDAT :  ";
              
             
                 
                 // affichage du mandat/propio
               
                  $supplier=new Mandat($db);
                  $supplier->fetch($agf->fk_mandat);
             
               $titre=$supplier->getmandatUrl(1).$titre.$supplier->ref_interne." du :  ".dol_print_date($supplier->date_contrat);
			   //   print $supplier->getmandatUrl(1);
                // print '<tr><td>'.$supplier->rowid.'</td>';
                 // print '<input  name="ref_mandat" value="'.$ref_interne.'">';
                  //print '<td>'.$supplier->ref_interne.'</tr></td>';
                //print '</tr></table>'   ; 
                 //
              
               if ($showphoto)
            {
                print '<td valign="middle" align="center" width="25%" rowspan="'.$nblignes.'">';
                print $agf->show_photos($conf->gestimmo->multidir_output[$agf->entity],1,1,0,0,0,80);
                
                print '</td>';
            }	
			 print_titre($titre);
                 print "</table>";

                
                print '<table class="border" width="100%">';

                print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
             //   print '<td>'.$form->showrefnav($agf,'id','',1,'rowid','id').'</td></tr>';

                print '<tr><td>'.$langs->trans("Ref interne").'</td>';
                print '<td>'.$agf->ref_interne.'</td></tr>';

               /* print '<tr><td valign="top">'.$langs->trans("Company").'</td><td>';
                if ($agf->socid)
                {
                    print '<a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$agf->socid.'">';
                    print img_object($langs->trans("ShowCompany"),"company").' '.dol_trunc($agf->socname,20).'</a>';
                }
                else
                { */
                //    print '&nbsp;';
               // }
                print '</tr>';

                print '<tr><td rowspan=3 valign="top">'.$langs->trans("Address").'</td>';
                print '<td>'.$agf->adresse.'</td></tr>';

                print '<tr>';
                print '<td>'.$agf->fk_departement.' - '.$agf->town.'</td></tr>';

                print '<tr>';
                print '<td>';
                $img=picto_from_langcode($agf->fk_pays);
                if ($agf->isInEEC()) print $form->textwithpicto(($img?$img.' ':'').$agf->country,$langs->trans("CountryIsInEEC"),1,0);
                else print ($img?$img.' ':'').$agf->fk_pays;
                print '</td></tr>';

                print '</td></tr>';

                print '<tr><td>'.$langs->trans("type").'</td>';
                print '<td>'.$agf->nb_piece.'</td></tr>';

                print '<tr><td valign="top">'.$langs->trans("drescriptif").'</td>';
                print '<td>'.nl2br($agf->descriptif).'</td></tr>';

                print '<tr><td valign="top">'.$langs->trans("superficie").'</td>';
                print '<td>'.nl2br($agf->superficie).'</td></tr>';

                print '<tr><td valign="top">'.$langs->trans("dpe").'</td>';
                print '<td>'.nl2br($agf->dpe).'</td></tr>';
                print '<tr><td valign="top">'.$langs->trans("loyer").'</td>';
                print '<td>'.price2num($agf->loyer).' €</td></tr>';
                print '<tr><td valign="top">'.$langs->trans("Charges").'</td>';
                print '<td>'.price2num($agf->charges).' €</td></tr>';
                print '<tr><td valign="top">'.$langs->trans("cation").'</td>';
                print '<td>'.price2num($agf->caution).' €</td></tr>';
                print '<tr><td valign="top">'.$langs->trans("honoraire").'</td>';
                print '<td>'.price2num($agf->Honoraire).' %</td></tr>';
                print '<tr><td valign="top">'.$langs->trans("assurance").'</td>';
                print '<td>'.price2num($agf->Assurance).' €</td></tr>';
               
                print "</table>";
			if ($showphoto)
            {
                print '<td valign="middle" align="center" width="25%" rowspan="'.$nblignes.'">';
                print $agf->show_photos($conf->gestimmo->multidir_output[$agf->entity],1,5,0,0,0,80);
                
                print '</td>';
            }	
                print '</div>';
            }

        }
        else
        {
            dol_print_error($db);
        }
    }
}


/*
 * Barre d'actions
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'nfcontact')
{
    if ($user->rights->agefodd->creer)
    {
        print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
    }
    else
    {
        print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
    }
    if ($user->rights->agefodd->creer)
    {
        print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
    }
    else
    {
        print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Delete').'</a>';
    }
    if ($user->rights->agefodd->modifier)
    {
        if ($agf->archive == 0)
        {
            print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=create">'.$langs->trans('nouveau').'</a>';
        }
        else
        {
            print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=active&id='.$id.'">'.$langs->trans('AgfActiver').'</a>';
        }
    }
    else
    {
        print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfArchiver').'/'.$langs->trans('AgfActiver').'</a>';
    }


}

print '</div>';

llxFooter('$Date: 2010-03-30 20:58:28 +0200 (mar. 30 mars 2010) $ - $Revision: 54 $');
?>
