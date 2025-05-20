<?php
/* Copyright (C) 2018-2019 Philippe GRAND <philippe.grand@atoo-net.com>
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
 * \file    ultimateimmo/class/actions_ultimateimmo.class.php
 * \ingroup ultimateimmo
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsUltimateimmo
 */
class ActionsUltimateimmo
{

    /**
     * @var DoliDB Database handler.
     */
    public $db;

    /**
     * @var string Error
     */
    public $error = '';

    /**
     * @var array Errors
     */
    public $errors = array();

    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public $results = array();

    /**
     * @var string String displayed by executeHook() immediately after return
     */
    public $resprints;

    /**
     * Constructor
     *
     *  @param		DoliDB		$db      Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Precise information on classes of ultimate immo
     */
    function getElementProperties($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;
//        var_dump($parameters['elementType']);
        $contextes         = explode(':', $parameters['context']);
        $elementType       = array_key_exists('elementType', $parameters) ? $parameters['elementType'] : false;
        $elementProperties = array_key_exists('elementProperties', $parameters) ? $parameters['elementProperties'] : false;
        if ($elementType && $elementProperties && in_array('elementproperties', $contextes)) {
            switch ($elementType) {
                case 'immoowner_type':
                case 'ImmoOwner_Type':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immoowner_type';
                    $elementProperties['table_element'] = 'ultimateimmo_immoowner_type';
                    $elementProperties['subelement']    = 'ImmoOwner_Type';
                    $elementProperties['classfile']     = 'immoowner_type';
                    $elementProperties['classname']     = 'ImmoOwner_Type';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immoowner':
                case 'ImmoOwner':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immoowner';
                    $elementProperties['table_element'] = 'ultimateimmo_immoowner';
                    $elementProperties['subelement']    = 'ImmoOwner';
                    $elementProperties['classfile']     = 'immoowner';
                    $elementProperties['classname']     = 'ImmoOwner';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;
                case 'immoproperty':
                case 'ImmoProperty':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immoproperty';
                    $elementProperties['table_element'] = 'ultimateimmo_immoproperty';
                    $elementProperties['subelement']    = 'ImmoProperty';
                    $elementProperties['classfile']     = 'immoproperty';
                    $elementProperties['classname']     = 'ImmoProperty';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'ImmoProperty_Type':
                case 'immoproperty_type':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immoproperty_type';
                    $elementProperties['table_element'] = 'c_ultimateimmo_immoproperty_type';
                    $elementProperties['subelement']    = 'ImmoProperty_Type';
                    $elementProperties['classfile']     = 'immoproperty_type';
                    $elementProperties['classname']     = 'ImmoProperty_Type';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immocompteur';
                case 'ImmoCompteur':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immocompteur';
                    $elementProperties['table_element'] = 'ultimateimmo_immocompteur';
                    $elementProperties['subelement']    = 'ImmoCompteur';
                    $elementProperties['classfile']     = 'immocompteur';
                    $elementProperties['classname']     = 'ImmoCompteur';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immocompteur_type':
                case 'ImmoCompteur_Type':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immocompteur_type';
                    $elementProperties['table_element'] = 'c_ultimateimmo_immocompteur_type';
                    $elementProperties['subelement']    = 'ImmoCompteur_Type';
                    $elementProperties['classfile']     = 'immocompteur_type';
                    $elementProperties['classname']     = 'ImmoCompteur_Type';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immocompteur_cost':
                case 'ImmoCompteur_Cost':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immocompteur_cost';
                    $elementProperties['table_element'] = 'ultimateimmo_immocompteur_cost';
                    $elementProperties['subelement']    = 'ImmoCompteur_Cost';
                    $elementProperties['classfile']     = 'immocompteur_cost';
                    $elementProperties['classname']     = 'ImmoCompteur_Cost';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immorenter':
                case 'ImmoRenter':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immorenter';
                    $elementProperties['table_element'] = 'ultimateimmo_immorenter';
                    $elementProperties['subelement']    = 'ImmoRenter';
                    $elementProperties['classfile']     = 'immorenter';
                    $elementProperties['classname']     = 'ImmoRenter';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immorent':
                case 'ImmoRent':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immorent';
                    $elementProperties['table_element'] = 'ultimateimmo_immorent';
                    $elementProperties['subelement']    = 'ImmoRent';
                    $elementProperties['classfile']     = 'immorent';
                    $elementProperties['classname']     = 'ImmoRent';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immoreceipt':
                case 'ImmoReceipt':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immoreceipt';
                    $elementProperties['table_element'] = 'ultimateimmo_immoreceipt';
                    $elementProperties['subelement']    = 'ImmoReceipt';
                    $elementProperties['classfile']     = 'immoreceipt';
                    $elementProperties['classname']     = 'ImmoReceipt';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immocost':
                case 'ImmoCost':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immocost';
                    $elementProperties['table_element'] = 'ultimateimmo_immocost';
                    $elementProperties['subelement']    = 'ImmoCost';
                    $elementProperties['classfile']     = 'immocost';
                    $elementProperties['classname']     = 'ImmoCost';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                case 'immocost_type':
                case 'ImmoCost_Type':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'immocost_type';
                    $elementProperties['table_element'] = 'ultimateimmo_immocost_type';
                    $elementProperties['subelement']    = 'ImmoCost_Type';
                    $elementProperties['classfile']     = 'immocost_type';
                    $elementProperties['classname']     = 'ImmoCost_Type';
                    $elementProperties['classpath']     = 'ultimateimmo/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                // yes the core doesn't know where his class is ...
                case 'account':
                case 'Account':
                    $elementProperties['module']        = 'ultimateimmo';
                    $elementProperties['element']       = 'bank_account';
                    $elementProperties['table_element'] = 'bank_account';
                    $elementProperties['subelement']    = 'Account';
                    $elementProperties['classfile']     = 'account';
                    $elementProperties['classname']     = 'Account';
                    $elementProperties['classpath']     = 'compta/bank/class';
                    $this->results                      = $elementProperties;
                    return 1; //replace (0 overwrite)
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
//    public function doActions($parameters, &$object, &$action, $hookmanager)
//    {
//        global $conf, $user, $langs;
//
//        $error = 0; // Error counter
//
//        /* print_r($parameters); print_r($object); echo "action: " . $action; */
//        if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {     // do something only for the context 'somecontext1' or 'somecontext2'
//            // Do what you want here...
//            // You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
//        }
//
//        if (!$error) {
//            $this->results   = array('myreturn' => 999);
//            $this->resprints = 'A text to show';
//            return 0;                                    // or return 1 to replace standard code
//        } else {
//            $this->errors[] = 'Error message';
//            return -1;
//        }
//    }

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
//    public function doMassActions($parameters, &$object, &$action, $hookmanager)
//    {
//        global $conf, $user, $langs;
//
//        $error = 0; // Error counter
//
//        /* print_r($parameters); print_r($object); echo "action: " . $action; */
//        if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {  // do something only for the context 'somecontext1' or 'somecontext2'
//            foreach ($parameters['toselect'] as $objectid) {
//                // Do action on each object id
//            }
//        }
//
//        if (!$error) {
//            $this->results   = array('myreturn' => 999);
//            $this->resprints = 'A text to show';
//            return 0;                                    // or return 1 to replace standard code
//        } else {
//            $this->errors[] = 'Error message';
//            return -1;
//        }
//    }

    /**
     * Overloading the addMoreMassActions function : replacing the parent's function with the one below
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
//    public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
//    {
//        global $conf, $user, $langs;
//
//        $error = 0; // Error counter
//
//        /* print_r($parameters); print_r($object); echo "action: " . $action; */
//        if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {  // do something only for the context 'somecontext1' or 'somecontext2'
//            $this->resprints = '<option value="0"' . ($disabled ? ' disabled="disabled"' : '') . '>' . $langs->trans("UltimateimmoMassAction") . '</option>';
//        }
//
//        if (!$error) {
//            return 0;                                    // or return 1 to replace standard code
//        } else {
//            $this->errors[] = 'Error message';
//            return -1;
//        }
//    }

    /**
     * Execute action
     *
     * @param	array	$parameters		Array of parameters
     * @param   Object	$object		   	Object output on PDF
     * @param   string	$action     	'add', 'update', 'view'
     * @return  int 		        	<0 if KO,
     *                          		=0 if OK but we want to process standard actions too,
     *  	                            >0 if OK and we want to replace standard actions.
     */
//    function beforePDFCreation($parameters, &$object, &$action)
//    {
//        global $langs, $conf;
//        global $hookmanager;
//
//        $outputlangs = $langs;
//
//        $ret     = 0;
//        $deltemp = array();
//        dol_syslog(get_class($this) . '::executeHooks action=' . $action);
//
//        /* print_r($parameters); print_r($object); echo "action: " . $action; */
//        if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {  // do something only for the context 'somecontext1' or 'somecontext2'
//        }
//
//        return $ret;
//    }

    /**
     * Execute action
     *
     * @param	array	$parameters		Array of parameters
     * @param   Object	$pdfhandler   	PDF builder handler
     * @param   string	$action     	'add', 'update', 'view'
     * @return  int 		        	<0 if KO,
     *                          		=0 if OK but we want to process standard actions too,
     *  	                            >0 if OK and we want to replace standard actions.
     */
//    function afterPDFCreation($parameters, &$pdfhandler, &$action)
//    {
//        global $langs, $conf;
//        global $hookmanager;
//
//        $outputlangs = $langs;
//
//        $ret     = 0;
//        $deltemp = array();
//        dol_syslog(get_class($this) . '::executeHooks action=' . $action);
//
//        /* print_r($parameters); print_r($object); echo "action: " . $action; */
//        if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {  // do something only for the context 'somecontext1' or 'somecontext2'
//        }
//
//        return $ret;
//    }
    /* Add here any other hooked methods... */

    function addToLandingPageList($parameters, &$object, &$action, $hookmanager)
    {
        if ($object->rights->ultimateimmo->read) {
            $parameters[dol_buildpath('/custom/ultimateimmo/rent/immorent_list.php', 1) . '?mainmenu=properties&leftmenu='] = 'UltimateImmo';
            $this->results                                                                                                  = $parameters;
            return 1;
        }
    }

    /**
     * Do something
     *
     * @param   int				$param				0=True url, 1=Url formated with colors
     * @return	string								Url string
     */
    function addSearchEntry($parameters, $object, $action, $hookmanager)
    {
        global $user, $langs;
        $search_boxvalue = ''; // ??????????
        if ($user->rights->ultimateimmo->read) {
            if (in_array($parameters['currentcontext'], array('globalcard', 'searchform', 'leftblock'))) {  // do something only for the context 'somecontext1' or 'somecontext2'
                $langs->load("ultimateimmo@ultimateimmo");
                $this->results = [
                    ['position' => 200, 'shortcut' => 'U', 'img' => 'immorenter.png@ultimateimmo', 'label' => $langs->trans("Renter"), 'text' => img_picto('', 'immorenter@ultimateimmo', 'class="pictofixedwidth"') . ' ' . $langs->trans("Renter", $search_boxvalue), 'url' => dol_buildpath('/ultimateimmo/renter/immorenter_list.php', 1) . ($search_boxvalue ? '?search_all=' . urlencode($search_boxvalue) : '')],
                    ['position' => 200,
                        'shortcut' => 'U',
                        'img' => 'immoproperty.png@ultimateimmo',
                        'label' => $langs->trans("Property"),
                        'text' => img_picto('', 'immoproperty@ultimateimmo', 'class="pictofixedwidth"')
                        . ' ' . $langs->trans("Property", $search_boxvalue),
                        'url' => dol_buildpath('/ultimateimmo/property/immoproperty_list.php', 1)
                        . ($search_boxvalue ? '?search_all=' . urlencode($search_boxvalue) : '')
                    ],
                    ['position' => 200, 'shortcut' => 'U', 'img' => 'immoowner.png@ultimateimmo', 'label' => $langs->trans("Owner"), 'text' => img_picto('', 'immoowner@ultimateimmo', 'class="pictofixedwidth"') . ' ' . $langs->trans("Owner", $search_boxvalue), 'url' => dol_buildpath('/ultimateimmo/owner/immoowner_list.php', 1) . ($search_boxvalue ? '?search_all=' . urlencode($search_boxvalue) : '')],
                ];
            }
        }
    }
}
