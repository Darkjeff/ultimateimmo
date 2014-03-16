<?php
/* Copyright (C) 2012-2013  Florian Henry   <florian.henry@open-concept.pro>
 * Copyright (C) 2012       JF FERRY        <jfefe@aternatik.fr>
*
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
 * \file agefodd/class/html.formagefodd.class.php
 * \brief Fichier de la classe des fonctions predefinie de composants html agefodd
 */

/**
 * Class to manage building of HTML components
 */
class FormImmobilier extends Form
{
	var $db;
	var $error;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db handler
	 */
	function __construct($db)
	{
		global $langs;
		$this->db = $db;
		
		return 1;
	}

	/**
	 * Affiche un champs select des locataire
	 *
	 * @param int $selectid à preselectionner
	 * @param string $htmlname select field
	 * @param string $sort Value to show/edit (not used in this function)
	 * @param int $showempty empty field
	 * @param int $forcecombo use combo box
	 * @param array $event
	 * @return string select field
	 */
	function select_locataire($selectid, $htmlname = 'locataire', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_locataire";
		$sql .= " WHERE statut= 'Actif'";
		if ($user->id != 1) {
			$sql .= " AND proprietaire_id=" . $user->id;
		}
		$sql .= " ORDER BY nom";
		
		dol_syslog(get_class($this) . "::select_locataire sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->nom;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}

	/**
	 * Affiche un champs select des locataire
	 *
	 * @param int $selectid à preselectionner
	 * @param string $htmlname select field
	 * @param string $sort Value to show/edit (not used in this function)
	 * @param int $showempty empty field
	 * @param int $forcecombo use combo box
	 * @param array $event
	 * @return string select field
	 */
	function select_local($selectid, $htmlname = 'local', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_local";
		$sql .= " WHERE statut= 'Actif'";
		if ($user->id != 1) {
			$sql .= " AND proprietaire_id=" . $user->id;
		}
		$sql .= " ORDER BY nom";
		
		dol_syslog(get_class($this) . "::select_local sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->nom;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}

	function select_type($selectid, $htmlname = 'type', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, type";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_typologie";
		$sql .= " ORDER BY type";
		
		dol_syslog(get_class($this) . "::select_type sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->type;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}

	function select_propertie($selectid, $htmlname = 'propertie', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_local";
		$sql .= " WHERE statut= 'Actif' OR statut='Immeuble'";
		if ($user->id != 1) {
			$sql .= " AND proprietaire_id=" . $user->id;
		}
		$sql .= " ORDER BY nom";
		
		dol_syslog(get_class($this) . "::select_propertie sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->nom;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}

	function select_immeuble($selectid, $htmlname = 'immeuble', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid , nom , proprietaire_id";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_immeuble  ";
		if ($user->id != 1) {
			$sql .= " WHERE proprietaire_id=" . $user->id;
		}
		$sql .= " ORDER by nom";
		
		dol_syslog(get_class($this) . "::select_immeuble sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->nom;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}

	function select_fournisseur($selectid, $htmlname = 'fournisseur', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT DISTINCT fournisseur ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_charge ";
		if ($user->id != 1) {
			$sql .= " WHERE proprietaire_id=" . $user->id;
		}
		$sql .= " ORDER BY fournisseur";
		
		dol_syslog(get_class($this) . "::select_fournisseur sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->fournisseur;
					
					if (($selectid != '') && $selectid == $obj->fournisseur) {
						$out .= '<option value="' . $obj->fournisseur . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->fournisseur . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}

	function select_type_compteur($selectid, $htmlname = 'type_compteur', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid,code, intitule ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_dict_type_compteur ";
		$sql .= " WHERE active=1 ";
		$sql .= " ORDER BY sort";
		
		dol_syslog(get_class($this) . "::select_type_compteur sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->code;
					
					if (($selectid != '') && $selectid == $obj->fournisseur) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}

	function select_compteur_by_local($selectid, $local_id, $htmlname = 'compteur', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		require_once 'compteur_local.class.php';
		
		$compteur_local = new Immocompteurlocal($this->db);
		$result = $compteur_local->fetch_all_by_local($local_id);
		if ($result < 0) {
			setEventMessage($compteur_local->error, 'errors');
		}
		
		$out = '';
		
		$out .= ajax_combobox($htmlname, $event);
		
		$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
		if (is_array($compteur_local->lines) && count($compteur_local->lines) > 0) {
			
			if ($showempty)
				$out .= '<option value=""></option>';
			
			foreach ($compteur_local->lines as $line) {
				
				$label = $line->type . '-' . $line->label;
				
				if (($selectid != '') && $selectid == $obj->fournisseur) {
					$out .= '<option value="' . $line->id . '" selected="selected">' . $label . '</option>';
				} else {
					$out .= '<option value="' . $line->id . '">' . $label . '</option>';
				}
			}
		}
		
		$out .= '</select>';
		
		return $out;
	}
	
	function select_type_letter($selectid, $htmlname = 'type_letter', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid,code, intitule ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_dict_type_letter ";
		$sql .= " WHERE active=1 ";
		$sql .= " ORDER BY sort";
		
		dol_syslog(get_class($this) . "::select_type_letter sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->intitule;
					
					if (($selectid != '') && $selectid == $obj->fournisseur) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	// Pour choisir la ville
/**
	 *    Return a select list with zip codes and their town
	 *
	 *    @param	string		$selected				Preselected value
	 *    @param    string		$htmlname				HTML select name
	 *    @param    string		$fields					Fields
	 *    @param    int			$fieldsize				Field size
	 *    @param    int			$disableautocomplete    1 To disable autocomplete features
	 *    @return	void
	 */
   function select_depville($selected='', $htmlname='zipcode', $fields='', $fieldsize=0, $disableautocomplete=0)
	{
		global $conf;

		$out='';

		$size='';
		if (!empty($fieldsize)) $size='size="'.$fieldsize.'"';

		if ($conf->use_javascript_ajax && empty($disableautocomplete))	$out.= ajax_multiautocompleter($htmlname,$fields,DOL_URL_ROOT.'/core/ajax/ziptown.php')."\n";
		$out.= '<input id="'.$htmlname.'" type="text" name="'.$htmlname.'" '.$size.' value="'.$selected.'">'."\n";

		return $out;
	} 
	
	function select_mandat($selectid, $htmlname='mandat', $filter='', $showempty=0, $forcecombo=0, $event=array())
    {
        global $conf,$langs;

        $sql = "SELECT t.rowid, t.ref_interne";
        $sql.= " FROM ".MAIN_DB_PREFIX."immo_mandat as t";
        if (!empty($filter)) {
            $sql .= ' WHERE '.$filter;
        }
        $sql.= " ORDER BY t.ref_interne";

        dol_syslog(get_class($this)."::select_mandat sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);
        if ($result)
        {

            if ($conf->use_javascript_ajax  && ! $forcecombo)
            {
                $out.= ajax_combobox($htmlname, $event);
            }

            $out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'">';
            if ($showempty) $out.= '<option value="-1"></option>';
            $num = $this->db->num_rows($result);
            $i = 0;
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($result);
                    $label = stripslashes($obj->ref_interne);

                    if ($selectid > 0 && $selectid == $obj->rowid)
                    {
                        $out.= '<option value="'.$obj->rowid.'" selected="selected">'.$label.'</option>';
                    }
                    else
                    {
                        $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                    }
                    $i++;
                }
            }
            $out.= '</select>';
            $this->db->free($result);
            return $out;
        }
        else
        {
            $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::select_mandat ".$this->error, LOG_ERR);
            return -1;
        }
    }
	function select_proprio($selectid, $htmlname = 'Proprietaire', $showempty = 0, $event = array())
	{
		global $conf, $user, $langs;
		
		$out = '';
		
		$sql = "SELECT rowid, nom";
		$sql .= " FROM " . MAIN_DB_PREFIX . "immo_proprio";
		$sql .= " WHERE statut= 'Actif'";
		if ($user->id != 1) {
			$sql .= " AND proprietaire_id=" . $user->id;
		}
		$sql .= " ORDER BY nom";
		
		dol_syslog(get_class($this) . "::select_locataire sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			
			$out .= ajax_combobox($htmlname, $event);
			
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows($resql);
			$i = 0;
			if ($num) {
				while ($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = $obj->nom;
					
					if ($selectid > 0 && $selectid == $obj->rowid) {
						$out .= '<option value="' . $obj->rowid . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->rowid . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error($this->db);
		}
		$this->db->free($resql);
		return $out;
	}
	function select_contacts_combobox($socid,$selected='',$htmlname='contactid',$showempty=0,$exclude='',$limitto='',$showfunction=0, $moreclass='', $options_only=false,$forcecombo=0,$event=array())
    {
        global $conf,$langs;
    
        $langs->load('companies');
    
        $out='';
    
        // On recherche les societes
        $sql = "SELECT sp.rowid, sp.nom as name ";
        $sql.= " FROM ".MAIN_DB_PREFIX ."societe as sp";
        $sql.= " WHERE sp.entity IN (".getEntity('societe', 1).")";
        if ($socid > 0) $sql.= " AND sp.fk_soc=".$socid;
        $sql.= " ORDER BY sp.nom ASC";
    
        dol_syslog(get_class($this)."::select_contacts_combobox sql=".$sql);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $num=$this->db->num_rows($resql);
            
            if ($conf->use_javascript_ajax && $conf->global->AGF_CONTACT_USE_SEARCH_TO_SELECT && ! $forcecombo)
            {           
                $out.= ajax_combobox($htmlname, $event);
            }
    
            if ($htmlname != 'none' || $options_only) $out.= '<select class="flat'.($moreclass?' '.$moreclass:'').'" id="'.$htmlname.'" name="'.$htmlname.'">';
            if ($showempty) $out.= '<option value="0"></option>';
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                include_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
                $contactstatic=new Contact($this->db);
    
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
    
                    $contactstatic->id=$obj->rowid;
                    $contactstatic->name=$obj->name;
                    $contactstatic->lastname=$obj->name;
                   
    
                    if ($htmlname != 'none')
                    {
                        $disabled=0;
                        if (is_array($exclude) && count($exclude) && in_array($obj->rowid,$exclude)) $disabled=1;
                        if (is_array($limitto) && count($limitto) && ! in_array($obj->rowid,$limitto)) $disabled=1;
                        if ($selected && $selected == $obj->rowid)
                        {
                            $out.= '<option value="'.$obj->rowid.'"';
                            if ($disabled) $out.= ' disabled="disabled"';
                            $out.= ' selected="selected">';
                            $out.= $contactstatic->getFullName($langs);
                            if ($showfunction && $obj->poste) $out.= ' ('.$obj->poste.')';
                            $out.= '</option>';
                        }
                        elseif (!$disabled)
                        {
                            $out.= '<option value="'.$obj->rowid.'"';
                            if ($disabled) $out.= ' disabled="disabled"';
                            $out.= '>';
                            $out.= $contactstatic->getFullName($langs);
                            if ($showfunction && $obj->poste) $out.= ' ('.$obj->poste.')';
                            $out.= '</option>';
                        }
                    }
                    else
                    {
                        if ($selected == $obj->rowid)
                        {
                            $out.= $contactstatic->getFullName($langs);
                            if ($showfunction && $obj->poste) $out.= ' ('.$obj->poste.')';
                        }
                    }
                    $i++;
                }
            }
            else
            {
                $out.= '<option value="-1" selected="selected" disabled="disabled">'.$langs->trans("NoContactDefined").'</option>';
            }
            if ($htmlname != 'none' || $options_only)
            {
                $out.= '</select>';
            }
    
            $this->num = $num;
            return $out;
        }
        else
        {
            dol_print_error($this->db);
            return -1;
        }
    }
	
}
