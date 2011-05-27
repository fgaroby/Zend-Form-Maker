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
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	public function run ()
    {   	      
        Zend_Session::start();
                
        parent::run();
    }
    

    protected function _initView ()
    {
        $view = new Zend_View();
        
        $view->doctype('XHTML1_STRICT');
        $view->headTitle('Zend Form Maker v1.0');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        
        $view->headLink()->prependStylesheet('/css/global.css');
        $view->headLink()->prependStylesheet('/css/design.css');
        $view->headLink()->prependStylesheet('/css/jquery-ui-1.8.12.sunny.css');
        
        $view->headScript()->appendFile('/js/jquery-1.6.min.js');
        $view->headScript()->appendFile('/js/jquery-ui-1.8.12.min.js');
        
        
        
        // Return it, so it can be stored by the bootstrap
        return $view;
    }
    
    protected function _initOptions()
    {
       	// Get the content of the configuration file
    	$config = new Zend_Config($this->getOptions());
    	
    	// Save the configuration in the registry
    	Zend_Registry::set('config', $config);
    	
    	$logger = new Zend_Log(new Zend_Log_Writer_Firebug());
    	Zend_Registry::set('logger', $logger);
    }
    
}

