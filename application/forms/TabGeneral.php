<?php
/*
Copyright (C) 2011 Matthieu Di Blasio <matthieu.diblasio@gmail.com>

This file is part of Zend Form Maker.

Zend Form Maker is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Zend Form Maker is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with Zend Form Maker.  If not, see <http://www.gnu.org/licenses/>.
*/
class Application_Form_TabGeneral extends Zend_Form
{
    private $_txtDecorator;
    private $_imgDone;
    
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
       	$this->_txtDecorator = array(
						array('ViewScript', 
							  array('viewScript' => '/decorators/txt_element.phtml', 
							  		'class' => 'tabGeneralFormElement'
							  	   )
							  )
					    );
		$baseUrl = Zend_Registry::get('baseUrl');  
		$this->_imgDone = '<img src="' . $baseUrl . '/images/zfm/updateDone.png" alt="The field is up to date" title="The field is up to date" />';
					    
        $this->_elementId();
        $this->_elementName();
        $this->_elementPosition();
        $this->_elementLabel();
        $this->_elementDescription();
        $this->_elementValue();
        $this->_elementRequired();
        $this->_elementAllowEmpty();
        
        
        $this->addAttribs(array('onsubmit' => 'return false;'));
    }

    private function _elementId()
    {			    
        $element = new Zend_Form_Element_Text('elementId');
        $element->setLabel('Id')
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('disabled', 'disabled');
        
        $this->addElement($element, 'id');
    }
    
    private function _elementName()
    {			    
        $element = new Zend_Form_Element_Text('elementName');
        $element->setLabel('Name')
        ->setRequired(true)
        ->setDecorators($this->_txtDecorator)
        ->setDescription($this->_imgDone);
        
        $this->addElement($element, 'name');
    }
        
    private function _elementPosition()
    {
        $element = new Zend_Form_Element_Text('elementOrder');
        $element->setLabel('Order')
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('disabled', 'disabled');
        
        $this->addElement($element, 'order');
    }
    
    private function _elementLabel()
    {
       $element = new Zend_Form_Element_Text('elementLabel');
        $element->setLabel('Label')
        ->setDecorators($this->_txtDecorator)
        ->setDescription($this->_imgDone);
        
        $this->addElement($element, 'label');
    }

    private function _elementDescription()
    {
        $element = new Zend_Form_Element_Text('elementDescription');
        $element->setLabel('Description')
        ->setDecorators($this->_txtDecorator)
        ->setDescription($this->_imgDone);
        
        $this->addElement($element, 'description');
    }
    
    private function _elementValue()
    {			    
        $element = new Zend_Form_Element_Text('elementValue');
        $element->setLabel('Value')
        ->setDecorators($this->_txtDecorator)
        ->setDescription($this->_imgDone);
        
        $this->addElement($element, 'value');
    }
    
    private function _elementRequired()
    {
       $element = new Zend_Form_Element_Checkbox('elementRequired');
        $element->setLabel('Required ?')
        ->setDecorators($this->_txtDecorator)
        ->setDescription($this->_imgDone)
        ->setValue('true');
        
        $this->addElement($element, 'required')
        ->setDescription($this->_imgDone);
    }     
    
    private function _elementAllowEmpty()
    {
       $element = new Zend_Form_Element_Checkbox('elementAllowEmpty');
        $element->setLabel('Allow empty ?')
        ->setDecorators($this->_txtDecorator)
        ->setDescription($this->_imgDone)
        ->setValue('true');
        
        $this->addElement($element, 'allowEmpty');
    }    
}

