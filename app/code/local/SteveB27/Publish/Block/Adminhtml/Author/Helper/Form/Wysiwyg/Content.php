<?php
/**
 * Custom Publisher Models
 * 
 * Add custom model types, such as author, which can be used as a product
 * attribute while proviting additional details.
 * 
 * @license 	http://opensource.org/licenses/gpl-license.php GNU General Public License, Version 3
 * @copyright	Steven Brown March 12, 2016
 * @author		Steven Brown <steveb.27@outlook.com>
 */

class SteveB27_Publish_Block_Adminhtml_Author_Helper_Form_Wysiwyg_Content extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm(){
        $form = new Varien_Data_Form(array('id' => 'wysiwyg_edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $config['document_base_url']     = $this->getData('store_media_url');
        $config['store_id']              = $this->getData('store_id');
        $config['add_variables']         = false;
        $config['add_widgets']           = false;
        $config['add_directives']        = true;
        $config['use_container']         = true;
        $config['container_class']       = 'hor-scroll';
        $editorConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig($config);
        $editorConfig->setData('files_browser_window_url', Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'));
        $form->addField($this->getData('editor_element_id'), 'editor', array(
            'name'      => 'content',
            'style'     => 'width:725px;height:460px',
            'required'  => true,
            'force_load' => true,
            'config'    => $editorConfig
        ));
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
}