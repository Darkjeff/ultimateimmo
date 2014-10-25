<?php
 /* * * Copyright (C) 2013       Thierry LECERF       <contact@t3s-it.com>
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
 *       \file       htdocs/gestimmo/bails/fiche.php
 *       \ingroup    gestimmo
 *       \brief      Page of a bails
 */
 require ("../../main.inc.php");
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';

dol_include_once('/immobilier/class/local.class.php');
dol_include_once('/core/lib/function.lib.php');
dol_include_once('/core/class/html.formcompany.class.php');
dol_include_once('/core/class/doleditor.class.php');
dol_include_once('/immobilier/class/html.immobilier.php');
dol_include_once('/immobilier/class/mandat.class.php');
dol_include_once('/immobilier/class/bails.class.php');

$langs->load("immobilier");
$langs->load("orders");
$langs->load("companies");

// Get parameters
$id         = GETPOST('id','int');
$action     = GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$arch=GETPOST('arch','int');
$url_return=GETPOST('url_return','alpha');

/* TODO a suprimer , on ne peut pas surpimer un baills ou alors les archiver selon delais legal
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" )
{
    $gestimmo = new Bails($db);
    $gestimmo->id=$id;
    $result = $gestimmo->delete($user);

    if ($result > 0)
    {
        Header ( "Location: liste.php");
        exit;
    }
    else
    {
        dol_syslog("gestimmo::bails::card error=".$gestimmo->error, LOG_ERR);
        $mesg='<div class="error">'.$langs->trans("Deleterror").':'.$gestimmo->error.'</div>';
    }
}
/* TODO en prevision de l'archivage des baux
 * Actions archive/active
*/
if ($action == 'arch_confirm_delete' )
{
    if ($confirm == "yes")
    {
        $gestimmo = new Bails($db);

        $result = $gestimmo->fetch($id);

       $gestimmo->archive = $arch;
        $result = $gestimmo->update($user);

        if ($result > 0)
        {
            Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
            exit;
        }
        else
        {
            dol_syslog("gestimmo::bails::card error=".$gestimmo->error, LOG_ERR);
            $mesg='<div class="error">'.$gestimmo->error.'</div>';
        }

    }
    else
    {
        Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
        exit;
    }
}

/*
 * Action update 
*/
if ($action == 'update' )
{
    if (! $_POST["cancel"] )
    {
        $gestimmo = new Bails($db);
        $result = $gestimmo->fetch($id);

        if($result > 0) 
        {// TODO liste des champs a mettre a jour
        
         
               // $gestimmo->fk_prop = GETPOST('fk_prop','int');
                $gestimmo->fk_loc = GETPOST('fk_loc','int');
                $gestimmo->fk_logement = GETPOST('fk_logement','int');
                $gestimmo->fk_mandat =GETPOST('fk_mandat','int');
               
                if(Getpost('Type','alpha')){
                $gestimmo->Type = GETPOST('Type','alpha');
                    }
                else {
                    $gestimmo->Type ='1';
                }
                if(GETPOST('Date_location','alpha')){
                $gestimmo->Date_location = GETPOST('Date_location','alpha');
                
                }
                else {
                $gestimmo->Date_location=dol_now();
                }
                if (GETPOST('Depot_garantie','alpha')){
                $gestimmo->Depot_garantie = GETPOST('Depot_garantie','alpha');
                }
                else {
                  $gestimmo->Depot_garantie ='0';   
                }
                if (GETPOST('date_fin','alpha')){
                $gestimmo->date_fin = GETPOST('date_fin','alpha');
                }
                else {
                   $gestimmo->date_fin = dol_now();
                }
              //  $gestimmo->tms = GETPOST('tms ','alpha');
              //  $gestimmo->entity = GETPOST('entity','alpha');
                 $result = $gestimmo->update($user);
    
            if ($result > 0)
            {
                Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
                exit;
            }
            else
            {
                 dol_syslog("gestimmo::bails::card error=".$gestimmo->error, LOG_ERR);
                 $mesg='<div class="error">'.$gestimmo->error.'</div>';
                 //pour gere le retour en cas d erreur
                 $action = 'edit';
            }
         }
         
           
        
    }

}

/*
 * Action create (fiche bails immo)
*/

if ($action == 'create_confirm' )
{
    if (! $_POST["cancel"])
    {
               $gestimmo = new Bails($db);
                // Pour rensegner les valeurs du bails
                $staticmandat=new Mandat($db);
                
                
                $idmandat= GETPOST('fk_mandat');
                $staticresult =  $staticmandat->fetch( $idmandat);
              //  print $staticmandat->id;
// todo champs pour la creation du bail
                $gestimmo->fk_prop = $staticmandat->fk_soc;
                $gestimmo->fk_loc = GETPOST('fk_loc','int');
                $gestimmo->fk_logement = $staticmandat->fk_biens;
                $gestimmo->fk_mandat =GETPOST('fk_mandat','int');
                $gestimmo->Type = GETPOST('Type','alpha');
                $gestimmo->Date_location = GETPOST('Date_location','alpha');
                $gestimmo->Depot_garantie = GETPOST('Depot_garantie','alpha');
                $gestimmo->date_fin = GETPOST('date_fin','alpha');
                $gestimmo->entity = getpost('entity');
        
        $result =  $gestimmo->create($user);
        
            
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
            dol_syslog("gestimmo::bails::card error=".$gestimmo->error, LOG_ERR);
                 $mesg='<div class="error">'.$gestimmo->error.'</div>';
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

$title = ($action == 'create' ? $langs->trans("Creation bail") : $langs->trans("Visu bail"));
llxHeader('',$title);
$form = new Form($db);
dol_htmloutput_mesg($mesg);

/*
 * Action create
*/
if ($action == 'create' )
{
    print_barre_liste($langs->trans("Création Bails de location"),"", "","","","",'',0);   
    $formcompany = new FormCompany($db);
    $formmandat = new FormImmobilier ($db);
     
    print_fiche_titre($langs->trans("creation d'un bails"));

    print '<form name="create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
    print '<input type="hidden" name="action" value="create_confirm">'."\n";

    print '<input type="hidden" name="url_return" value="'.$url_return.'">'."\n";
    print '<input type="hidden" name="entity" value="1">'."\n";
    print '<table class="border" width="100%">'."\n";

    print '<tr><td width="20%"><span class="fieldrequired">'.$langs->trans("Locataire").'</span></td>';
    print '<td><input name="fk_loc" class="flat" size="50" value=""></td></tr>';

    print '<tr><td><span class="fieldrequired">'.$langs->trans("Mandat").'</span></td>';
    print '<td>'.$formmandat->select_mandat('','fk_mandat','',1,1,0).'</td></tr>';
 // TODO faire afficher les infos sur le mandat
    if ($fk_mandat)
    {
        print '<tr><td>'.$langs->trans("Info Mandat").'</span></td>';
        print '<tr><td>'.$fk_mandat.'</span></td>';
    }
    else 
        {print '<tr><td>'.$langs->trans("Info Mandat").'</span></td>';
        print '<tr><td>Pas encore de mandat</span></td>';}
    
   // print '<tr><td><span class="fieldrequired">'.$langs->trans("Locaraire").'</span></td>';
   // print '<td>'.$formmandat->select_mandat('','mandat','',1,1,0).'</td></tr>';
    

    
    
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
    // Affichage de la fiche du bails gere 
    if ($id)
    {
        print_barre_liste($langs->trans("Gestion Bail Immobilier"),"", "","","","",'',0);
       // $agf = new Logement($db);
        //$result = $agf->fetch($id);
    $formcompany = new FormCompany($db);
    $formmandat = new FormImmobilier ($db);
    $gestimmo=new Bails($db);
    $result = $gestimmo->fetch($id);
     if ($result)
        {
           // $head = biens_prepare_head($agf);

            //dol_fiche_head($head, 'card', $langs->trans("Gestion des biens"), 0, 'address');

            // Affichage en mode "édition"
            if ($action == 'edit')
            {
               // $formcompany = new FormCompany($db);
                $formimmo = new FormImmobilier($db);
                print_barre_liste($langs->trans("Modification biens"),"", "","","","",'',0);
                print '<form name="update" action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
                print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
                print '<input type="hidden" name="action" value="update">'."\n";
                print '<input type="hidden" name="id" value="'.$id.'">'."\n";

                print '<table class="border" width="100%">'."\n";
                print '<tr><td width="30%">'.$langs->trans("Id").'</td>';
                print '<td>'.$gestimmo->id.'</td></tr>';

                print '<tr><td>'.$langs->trans("bail").'</td>';
                print '<td><input name="ref" class="flat" size="50" value="'.$gestimmo->rowid.'"></td></tr>';
// TO DO remplace select_compagy pae select_mandat, rajouter select locataire et select proprio
                print '<tr><td>'.$langs->trans("Mandat").'</td>';
                print '<td>'.$formimmo->select_mandat($gestimmo->fk_mandat,'fk_mandat','',0,1).'</td></tr>';

                print '<tr><td>'.$langs->trans("fk_loc").'</td>';
                print '<td><input name="fk_loc" class="flat" size="50" value="'.$gestimmo->fk_loc.'"></td>';
                print '<tr><td>'.$langs->trans("fk_logement").'</td>';
                print '<td><input name="fk_logement" class="flat" size="50" value="'.$gestimmo->fk_logement.'"></td>';
                // Tiers
                print '<tr>';
                print '<td class="fieldrequired">'.$langs->trans('Locataire').'</td>';
                $soc = new Societe($db);
                $soc->fetch($gestimmo->fk_loc);
/*
                if ($gestimmo->fk_loc > 0)
                {
                    print '<tr><td width="20%">'.$langs->trans('ThirdPartyName').'</td>';
                    print '<td colspan="4">';
                    $form->form_thirdparty($_SERVER['PHP_SELF'].'?id='.$gestimmo->id,$gestimmo->fk_loc,'fk_loc','',1,0,1);
                    print '</td></tr>';
                }
                else
                {
                     print '<tr><td width="20%">'.$langs->trans('ThirdPartyName').'</td>';
                     print '<td colspan="4">';
                     $form->form_thirdparty($_SERVER['PHP_SELF'].'?id='.$gestimmo->id,null,'fk_loc','',1,0,1);
                print '</td></tr>';
                }
  */  
  
 /* 
   if($gestimmo->fk_loc > 0)
    {
        $locataire=new Societe($db);
        $resultloc = $locataire->fetch($gestimmo->fk_loc);
        print '<td colspan="2">';
      print $form->form_thirdparty($gestimmo->fk_loc,$gestimmo->fk_loc);
      
   
        print $locataire->getNomUrl(1);
        print '<input type="hidden" name="fk_loc" value="'.$gestimmo->fk_loc.'">';
        print '</td>';
    }
   else
 {
        print '<td colspan="2">';
        print $form->select_company($gestimmo->fk_loc,'fk_loc','s.client = 1 OR s.client = 3',1);
        print '</td>';
    }*/
    print '</tr>'."\n";
// TODO lien avec le logemet
               // print '<td>'.$langs->trans('CP').'</td><td>';
               // print $formcompany->select_ziptown($agf->fk_departement,'zipcode',array('town','selectcountry_id'),6).'';
               //print $formimmo->select_depville($agf->fk_departement,'zipcode',array('town','selectcountry_id'),6).'';
               
                print '</td><td>'.$langs->trans('LOGEMENT').'</td><td>';
               
               //print $formimmo->select_depville($agf->town,'town',array('zipcode','selectcountry_id')).'</tr>';
               // print $formcompany->select_ziptown($agf->town,'town',array('zipcode','selectcountry_id')).'</tr>';

                //print '<tr><td>'.$langs->trans("Pays").'</td>';
                //print '<td>'.$form->select_country($agf->fk_pays,'country_id').'</td></tr>';

                print '<tr><td>'.$langs->trans("type").'</td>';
                print '<td><input name="type" class="flat" size="10" value="'.$gestimmo->Type.'"></td></tr>';

               // print '<tr><td valign="top">'.$langs->trans("Descriptif").'</td>';
               // print '<td><textarea name="descriptif" rows="3" cols="0" class="flat" style="width:360px;">'.nl2br($agf->descriptif).'</textarea></td></tr>';
           /*
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
 }
 
else
    {
         $formimmo = new FormImmobilier($db);
                print_barre_liste($langs->trans("Modification biens"),"", "","","","",'',0);
        print '<tr><td>'.$langs->trans("bail").'</td>';
                print '<td><input name="ref" class="flat" size="50" value="'.$gestimmo->rowid.'"></td></tr>';
// TO DO remplace select_compagy pae select_mandat, rajouter select locataire et select proprio
                print '<tr><td>'.$langs->trans("Mandat").'</td>';
                print '<td>'.$formimmo->select_mandat($gestimmo->fk_mandat,'fk_mandat','',0,1).'</td></tr>';

                print '<tr><td>'.$langs->trans("fk_loc").'</td>';
                print '<td><input name="fk_loc" class="flat" size="50" value="'.$gestimmo->fk_loc.'"></td>';
                print '<tr><td>'.$langs->trans("fk_logement").'</td>';
                print '<td><input name="fk_logement" class="flat" size="50" value="'.$gestimmo->fk_logement.'"></td>';
        
    }
 }}
      
$db->close();
llxFooter();