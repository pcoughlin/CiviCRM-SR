<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id: Display.php 33959 2011-04-28 18:01:24Z kurund $
 *
 */

require_once 'CRM/Admin/Form/Preferences.php';

/**
 * This class generates form components for the display preferences
 * 
 */
class CRM_Admin_Form_Preferences_Display extends CRM_Admin_Form_Preferences
{
    function preProcess( ) {
        parent::preProcess( );
        CRM_Utils_System::setTitle(ts('Settings - Site Preferences'));
        // add all the checkboxes
        $this->_cbs = array(
                            'contact_view_options'    => ts( 'Viewing Contacts'  ),
                            'contact_edit_options'    => ts( 'Editing Contacts'  ),
                            'advanced_search_options' => ts( 'Contact Search'    ),
                            'user_dashboard_options'  => ts( 'Contact Dashboard' )
                            );
    }

    function setDefaultValues( ) {
        $defaults = array( );
        $config =& CRM_Core_Config::singleton();

        parent::cbsDefaultValues( $defaults );
        if ( $this->_config->editor_id ) {
            $defaults['wysiwyg_editor'] = $this->_config->editor_id ;
        }
        if ( empty( $this->_config->display_name_format ) ) {
            $defaults['display_name_format'] = "{contact.individual_prefix}{ }{contact.first_name}{ }{contact.last_name}{ }{contact.individual_suffix}";
        } else {
            $defaults['display_name_format'] = $this->_config->display_name_format;
        }

        if ( empty( $this->_config->sort_name_format ) ) {
            $defaults['sort_name_format'] = "{contact.last_name}{, }{contact.first_name}";
        } else {
            $defaults['sort_name_format'] = $this->_config->sort_name_format;
        }

        if ( $config->userFramework == 'Drupal' && module_exists("wysiwyg")) {
            $defaults['wysiwyg_input_format'] = variable_get('civicrm_wysiwyg_input_format', 0);
        }
 
        return $defaults;
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $wysiwyg_options = array( '' => ts( 'Textarea' ) ) + CRM_Core_PseudoConstant::wysiwygEditor( );

        $config =& CRM_Core_Config::singleton();
        $extra = array();

        //if not using Joomla, remove Joomla default editor option
        if ( $config->userFramework != 'Joomla' ) {
            unset( $wysiwyg_options[3] );
        }

        if ( $config->userFramework != 'Drupal' || !module_exists("wysiwyg")) {
            unset( $wysiwyg_options[4] );
        } else {
            $extra['onchange'] = 'if (this.value==4) { cj("#crm-preferences-display-form-block-wysiwyg_input_format").show(); } else {  cj("#crm-preferences-display-form-block-wysiwyg_input_format").hide() }';
            $formats = filter_formats();
            $format_options = array();
            foreach ($formats as $id => $format) {
                $format_options[$id] = $format->name;
            }
            $drupal_wysiwyg = true;
        }
        $this->addElement( 'select', 'wysiwyg_editor', ts('WYSIWYG Editor'), $wysiwyg_options, $extra);
        
        if ($drupal_wysiwyg) {
          $this->addElement( 'select', 'wysiwyg_input_format', ts('Input Format'), $format_options, null);
        }
 
        $this->addElement('textarea','display_name_format', ts('Individual Display Name Format'));  
        $this->addElement('textarea','sort_name_format',    ts('Individual Sort Name Format'));
                
        require_once 'CRM/Core/OptionGroup.php';
        $editOptions = CRM_Core_OptionGroup::values( 'contact_edit_options', false, false, false, 'AND v.filter = 0' );
        $this->assign( 'editOptions', $editOptions );
        
        $contactBlocks = CRM_Core_OptionGroup::values( 'contact_edit_options', false, false, false, 'AND v.filter = 1' );
        $this->assign( 'contactBlocks', $contactBlocks );

        $this->addElement('hidden','contact_edit_prefences', null, array('id'=> 'contact_edit_prefences') );

        parent::buildQuickForm( );
    }

       
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $config =& CRM_Core_Config::singleton();
        if ( $this->_action == CRM_Core_Action::VIEW ) {
            return;
        }

        $this->_params = $this->controller->exportValues( $this->_name );
        
        if ( CRM_Utils_Array::value( 'contact_edit_prefences', $this->_params ) ) {
            $preferenceWeights = explode( ',' , $this->_params['contact_edit_prefences'] );
            foreach( $preferenceWeights as $key => $val ) {
                if ( !$val ) {
                    unset($preferenceWeights[$key]);
                }
            }
            require_once 'CRM/Core/BAO/OptionValue.php';
            $opGroupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup' , 'contact_edit_options', 'id', 'name' );
            CRM_Core_BAO_OptionValue::updateOptionWeights( $opGroupId, array_flip($preferenceWeights) );
        }

        if ( $config->userFramework == 'Drupal' && module_exists("wysiwyg")) {
            variable_set('civicrm_wysiwyg_input_format', $this->_params['wysiwyg_input_format']);
        }
        
        $this->_config->editor_id = $this->_params['wysiwyg_editor'];
        $this->_config->display_name_format = $this->_params['display_name_format'];
        $this->_config->sort_name_format    = $this->_params['sort_name_format'];

        // set default editor to session if changed
        $session = CRM_Core_Session::singleton();
        $session->set( 'defaultWysiwygEditor', $this->_params['wysiwyg_editor'] );
        
        parent::postProcess( );
    }//end of function

}


