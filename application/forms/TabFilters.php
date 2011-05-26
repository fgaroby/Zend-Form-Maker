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
class Application_Form_TabFilters extends Zend_Form
{
    private $_txtDecorator;
    private $_imgDone;
    
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
       	$this->_txtDecorator = array(
						array('ViewScript', 
							  array('viewScript' => '/decorators/txt_element.phtml', 
							  		'class' => 'tabFiltersFormElement'
							  	   )
							  )
					    );

		$this->_imgDone = '<img src="/images/zfm/updateDone.png" alt="The field is up to date" title="The field is up to date" />';
					    
					    
        $this->_paramInput();
        
        $this->addAttribs(array('onsubmit' => 'return false;'));
    }

    
    private function _paramInput()
    {			    
        $element = new Zend_Form_Element_Text("filterConstruct");
        
        $element->setLabel('Parameters')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        $this->addElement($element, 'constructor');
    }
        
}

