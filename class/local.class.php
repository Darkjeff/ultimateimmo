<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
 * \file htdocs/compta/class/BookKeeping.class.php
 * \ingroup compta
 * \brief Fichier de la classe des comptes comptable
 * \version $Id: BookKeeping.class.php,v 1.3 2011/08/03 00:46:33 eldy Exp $
 */

require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");

/**
 * \class local
 * \brief Classe permettant la gestion des locaux
 */
class Local extends CommonObject {
	var $db;
	var $id;
	var $rowid;
	var $immeuble_id;
	var $nom;
	var $adresse;
	var $commentaire;
	var $statut;
	var $superficie;
	var $type;
	var $date_dpe;
	var $dpe_ep;
	var $dpe_gs;
	var $proprietaire_id;
	var $status_array;
	
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='immo_local';			//!< Id that identify managed objects
	var $table_element='immo_local';		//!< Name of table without prefix where object is stored
	
	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	function __construct($db, $rowid = '') {
		$this->db = $db;
		
		$this->status_array = array('Actif'=>'Actif','Inactif'=>'Inactif');
		
		if ($rowid != '')
			return $this->fetch ( $rowid );
	}
	function fetch($rowid = null, $nom = null) {
		
		global $user;
		
		if ($rowid || $nom) {
			$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "immo_local WHERE ";
			if ($rowid) {
				$sql .= " rowid = '" . $rowid . "'";
			} elseif ($nom) {
				$sql .= " nom = '" . $nom . "'";
			}
			
			if ($user->id != 1) {
				$sql .= " AND proprietaire_id=".$user->id;
			}
			
			dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
			$result = $this->db->query ( $sql );
			if ($result) {
				$obj = $this->db->fetch_object ( $result );
			} else {
				return null;
			}
		}
		
		$this->id = $obj->rowid;
		$this->rowid = $obj->rowid;
		$this->immeuble_id = $obj->immeuble_id;
		$this->nom = $obj->nom;
		$this->adresse = $obj->adresse;
		$this->commentaire = $obj->commentaire;
		$this->statut = $obj->statut;
		$this->superficie = $obj->superficie;
		$this->type = $obj->type;
		$this->date_dpe = $obj->date_dpe;
		$this->dpe_ep = $obj->dpe_ep;
		$this->dpe_ges = $obj->dpe_ges;
		$this->proprietaire_id = $obj->proprietaire_id;
		
		return $obj->rowid;
	}
	function create($user) {
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "immo_local (";
		$sql .= " immeuble_id,";
		$sql .= " nom,";
		$sql .= " adresse,";
		$sql .= " commentaire,";
		$sql .= " statut,";
		$sql .= " proprietaire_id";
		$sql .= " ) VALUES (";
		$sql .= " '" . $this->immeuble_id . "',";
		$sql .= " '" . $this->nom . "',";
		$sql .= " '" . $this->adresse . "',";
		$sql .= "'" . $this->commentaire . "',";
		$sql .= "'Actif',";
		$sql .= "'" . $user->id . "'";
		$sql .= ")";
		dol_syslog ( get_class ( $this ) . "::create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->db->commit ();
			return 1;
		} else {
			$this->error = $this->db->error ();
			$this->db->rollback ();
			dol_syslog ( get_class ( $this ) . "::create error=" . $this->error, LOG_DEBUG );
			return - 1;
		}
	}
	function update() {
		$sql = "UPDATE " . MAIN_DB_PREFIX . "immo_local SET nom = '" . addslashes ( $this->nom ) . "', adresse = '" . addslashes ( $this->adresse ) . "', commentaire = '" . addslashes ( $this->commentaire ) . "', statut = '" . addslashes ( $this->statut ) . "' WHERE rowid = '" . $this->rowid . "'";
		if ($this->db->query ( $sql )) {
			return $this->rowid;
		} else {
			dol_print_error ( $this->db );
			return - 1;
		}
	}
	function select_nom_local($selected = '', $htmlname = 'actionnomlocal', $useempty = 0, $maxlen = 40, $help = 1) {
		global $db, $langs, $user;
		$sql = "SELECT l.rowid, l.nom as nomlocal";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_local as l";
		if ($user->id != 1) {
			$sql .= " WHERE l.proprietaire_id=".$user->id;
		}
		$sql .= " ORDER BY l.nom ASC";
		dol_syslog ( "Form::select_nom_local sql=" . $sql, LOG_DEBUG );
		$resql = $db->query ( $sql );
		if ($resql) {
			$num = $db->num_rows ( $resql );
			if ($num) {
				print '<select class="flat" name="' . $htmlname . '">';
				$i = 0;
				
				if ($useempty)
					print '<option value="0">&nbsp;</option>';
				while ( $i < $num ) {
					$obj = $db->fetch_object ( $resql );
					print '<option value="' . $obj->rowid . '"';
					if ($obj->rowid == $selected)
						print ' selected="selected"';
					print '>' . dol_trunc ( $obj->nomlocal, $maxlen );
					$i ++;
				}
				print '</select>';
			}
		} else {
			dol_print_error ( $db, $db->lasterror () );
		}
	}
	
	
	/**
	 * Renvoie nom clicable (avec eventuellement le picto)
	 *
	 * @param int $withpicto picto, 1=Inclut le picto dans le lien, 2=Picto seul
	 * @param int $maxlen libelle
	 * @return string avec URL
	 */
	function getNomUrl($withpicto = 0, $maxlen = 0) {
		global $langs;
		
		$result = '';
		
		if (empty ( $this->ref ))
			$this->ref = $this->nom;
		
		$lien = '<a href="' . DOL_URL_ROOT . '/immobilier/local/fiche_local.php?action=update&id=' . $this->id . '">';
		$lienfin = '</a>';
		
		if ($withpicto)
			$result .= ($lien . img_object ( $langs->trans ( "ShowProperty" ) . ': ' . $this->nom, 'bill' ) . $lienfin . ' ');
		if ($withpicto && $withpicto != 2)
			$result .= ' ';
		if ($withpicto != 2)
			$result .= $lien . ($maxlen ? dol_trunc ( $this->id, $maxlen ) : $this->id) . $lienfin;
		return $result;
	}
	
	function liste_photos($dir,$nbmax=0)
    {
        include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $nbphoto=0;
        $tabobj=array();

        $dir_osencoded=dol_osencode($dir);
        $handle=@opendir($dir_osencoded);
        if (is_resource($handle))
        {
            while (($file = readdir($handle)) != false)
            {
                if (! utf8_check($file)) $file=utf8_encode($file);  // readdir returns ISO
                if (dol_is_file($dir.$file) && preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $dir.$file))
                {
                    $nbphoto++;

                    // On determine nom du fichier vignette
                    $photo=$file;
                    $photo_vignette='';
                    if (preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $photo, $regs))
                    {
                        $photo_vignette=preg_replace('/'.$regs[0].'/i', '', $photo).'_small'.$regs[0];
                    }

                    $dirthumb = $dir.'thumbs/';

                    // Objet
                    $obj=array();
                    $obj['photo']=$photo;
                    if ($photo_vignette && dol_is_file($dirthumb.$photo_vignette)) $obj['photo_vignette']=$photo_vignette;
                    else $obj['photo_vignette']="";

                    $tabobj[$nbphoto-1]=$obj;

                    // On continue ou on arrete de boucler ?
                    if ($nbmax && $nbphoto >= $nbmax) break;
                }
            }

            closedir($handle);
        }

        return $tabobj;
    }

    /**
     *  Efface la photo du produit et sa vignette
     *
     *  @param  string      $file        Chemin de l'image
     *  @return void
     */
    function delete_photo($file)
    {
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $dir = dirname($file).'/'; // Chemin du dossier contenant l'image d'origine
        $dirthumb = $dir.'/thumbs/'; // Chemin du dossier contenant la vignette
        $filename = preg_replace('/'.preg_quote($dir,'/').'/i','',$file); // Nom du fichier

        // On efface l'image d'origine
        dol_delete_file($file);

        // Si elle existe, on efface la vignette
        if (preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i',$filename,$regs))
        {
            $photo_vignette=preg_replace('/'.$regs[0].'/i','',$filename).'_small'.$regs[0];
            if (file_exists(dol_osencode($dirthumb.$photo_vignette)))
            {
                dol_delete_file($dirthumb.$photo_vignette);
            }
        }
    }

    /**
     *  Load size of image file
     *
     *  @param  string  $file        Path to file
     *  @return void
     */
    function get_image_size($file)
    {
        $file_osencoded=dol_osencode($file);
        $infoImg = getimagesize($file_osencoded); // Get information on image
        $this->imgWidth = $infoImg[0]; // Largeur de l'image
        $this->imgHeight = $infoImg[1]; // Hauteur de l'image
    }
    
function add_photo($sdir, $file, $maxWidth = 160, $maxHeight = 120)
    {
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $dir = $sdir .'/'. get_exdir($this->id,2) . $this->id ."/photos";

        dol_mkdir($dir);

        $dir_osencoded=$dir;
        if (is_dir($dir_osencoded))
        {
            $originImage = $dir . '/' . $file['name'];

            // Cree fichier en taille origine
            $result=dol_move_uploaded_file($file['tmp_name'], $originImage, 1);

            if (file_exists(dol_osencode($originImage)))
            {
                // Cree fichier en taille vignette
                $this->add_thumb($originImage,$maxWidth,$maxHeight);
            }
        }
    }
    function show_photos($sdir,$size=0,$nbmax=0,$nbbyrow=5,$showfilename=0,$showaction=0,$maxHeight=120,$maxWidth=160)
    {
        global $conf,$user,$langs;

        include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';
        include_once DOL_DOCUMENT_ROOT .'/core/lib/images.lib.php';

        $pdir = get_exdir($this->id,2) . $this->id ."/photos/";
        $dir = $sdir . '/'. $pdir;
        $dirthumb = $dir.'thumbs/';
        $pdirthumb = $pdir.'thumbs/';

        $return ='<!-- Photo -->'."\n";
        $nbphoto=0;

        $dir_osencoded=dol_osencode($dir);
        if (file_exists($dir_osencoded))
        {
            $handle=opendir($dir_osencoded);
            if (is_resource($handle))
            {
                while (($file = readdir($handle)) != false)
                {
                    $photo='';

                    if (! utf8_check($file)) $file=utf8_encode($file);  // To be sure file is stored in UTF8 in memory

                    if (dol_is_file($dir.$file) && preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $dir.$file))
                    {
                        $nbphoto++;
                        $photo = $file;
                        $viewfilename = $file;

                        if ($size == 1) {   // Format vignette
                            // On determine nom du fichier vignette
                            $photo_vignette='';
                            if (preg_match('/(\.jpg|\.bmp|\.gif|\.png|\.tiff)$/i', $photo, $regs)) {
                                $photo_vignette=preg_replace('/'.$regs[0].'/i', '', $photo)."_small".$regs[0];
                                if (! dol_is_file($dirthumb.$photo_vignette)) $photo_vignette='';
                            }

                            // Get filesize of original file
                            $imgarray=dol_getImageSize($dir.$photo);

                            if ($nbbyrow && $nbphoto == 1) $return.= '<table width="100%" valign="top" align="center" border="0" cellpadding="2" cellspacing="2">';

                            if ($nbbyrow && ($nbphoto % $nbbyrow == 1)) $return.= '<tr align=center valign=middle border=1>';
                            if ($nbbyrow) $return.= '<td width="'.ceil(100/$nbbyrow).'%" class="photo">';

                            $return.= "\n";
                            $return.= '<a href="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'" class="aphoto" target="_blank">';

                            // Show image (width height=$maxHeight)
                            // Si fichier vignette disponible et image source trop grande, on utilise la vignette, sinon on utilise photo origine
                            $alt=$langs->transnoentitiesnoconv('File').': '.$pdir.$photo;
                            $alt.=' - '.$langs->transnoentitiesnoconv('Size').': '.$imgarray['width'].'x'.$imgarray['height'];
                            if ($photo_vignette && $imgarray['height'] > $maxHeight) {
                                $return.= '<!-- Show thumb -->';
                                $return.= '<img class="photo" border="0" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdirthumb.$photo_vignette).'" title="'.dol_escape_htmltag($alt).'">';
                            }
                            else {
                                $return.= '<!-- Show original file -->';
                                $return.= '<img class="photo" border="0" height="'.$maxHeight.'" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'" title="'.dol_escape_htmltag($alt).'">';
                            }

                            $return.= '</a>'."\n";

                            if ($showfilename) $return.= '<br>'.$viewfilename;
                            if ($showaction)
                            {
                                $return.= '<br>';
                                // On propose la generation de la vignette si elle n'existe pas et si la taille est superieure aux limites
                                if ($photo_vignette && preg_match('/(\.bmp|\.gif|\.jpg|\.jpeg|\.png)$/i', $photo) && ($product->imgWidth > $maxWidth || $product->imgHeight > $maxHeight))
                                {
                                    $return.= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$_GET["id"].'&amp;action=addthumb&amp;file='.urlencode($pdir.$viewfilename).'">'.img_picto($langs->trans('GenerateThumb'),'refresh').'&nbsp;&nbsp;</a>';
                                }
                                if ($user->rights->produit->creer || $user->rights->service->creer)
                                {
                                    // Link to resize
                                   // $return.= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('gestimmo').'&id='.$_GET["id"].'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"),DOL_URL_ROOT.'/theme/common/transform-crop-and-resize','',1).'</a> &nbsp; ';

                                    // Link to delete
                                    $return.= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$_GET["id"].'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
                                    $return.= img_delete().'</a>';
                                }
                            }
                            $return.= "\n";

                            if ($nbbyrow) $return.= '</td>';
                            if ($nbbyrow && ($nbphoto % $nbbyrow == 0)) $return.= '</tr>';

                        }

                        if ($size == 0) {     // Format origine
                            $return.= '<img class="photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=gestimmo&entity='.$this->entity.'&file='.urlencode($pdir.$photo).'">';

                            if ($showfilename) $return.= '<br>'.$viewfilename;
                            if ($showaction)
                            {
                                if ($user->rights->produit->creer || $user->rights->service->creer)
                                {
                                    // Link to resize
                                    $return.= '<a href="'.DOL_URL_ROOT.'/core/photos_resize.php?modulepart='.urlencode('produit|service').'&id='.$_GET["id"].'&amp;file='.urlencode($pdir.$viewfilename).'" title="'.dol_escape_htmltag($langs->trans("Resize")).'">'.img_picto($langs->trans("Resize"),DOL_URL_ROOT.'/theme/common/transform-crop-and-resize','',1).'</a> &nbsp; ';

                                    // Link to delete
                                    $return.= '<a href="'.$_SERVER["PHP_SELF"].'?id='.$_GET["id"].'&amp;action=delete&amp;file='.urlencode($pdir.$viewfilename).'">';
                                    $return.= img_delete().'</a>';
                                }
                            }
                        }

                        // On continue ou on arrete de boucler ?
                        if ($nbmax && $nbphoto >= $nbmax) break;
                    }
                }
            }

            if ($nbbyrow && $size==1)
            {
                // Ferme tableau
                while ($nbphoto % $nbbyrow)
                {
                    $return.= '<td width="'.ceil(100/$nbbyrow).'%">&nbsp;</td>';
                    $nbphoto++;
                }

                if ($nbphoto) $return.= '</table>';
            }

            closedir($handle);
        }

        $this->nbphoto = $nbphoto;

        return $return;
    }
function is_photo_available($sdir)
    {
        include_once DOL_DOCUMENT_ROOT .'/core/lib/files.lib.php';

        $pdir = get_exdir($this->id,2) . $this->id ."/photos/";
        $dir = $sdir . '/'. $pdir;

        $nbphoto=0;

        $dir_osencoded=dol_osencode($dir);
        if (file_exists($dir_osencoded))
        {
            $handle=opendir($dir_osencoded);
            if (is_resource($handle))
            {
                while (($file = readdir($handle)) != false)
                {
                    if (! utf8_check($file)) $file=utf8_encode($file);  // To be sure data is stored in UTF8 in memory
                    if (dol_is_file($dir.$file)) return true;
                }
            }
        }
        return false;
    }

}

?>
