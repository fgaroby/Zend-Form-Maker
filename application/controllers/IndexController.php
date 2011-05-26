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
class IndexController extends Zend_Controller_Action
{
    //private $_jsDir = '/js/';
    private $_jsDir = '/js/min/';
    
    public function init()
    {
        $view = $this->view;
        
        // Enable jquery for this controller
        $view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $view->jQuery()->uiEnable();
    }

    public function indexAction()
    {

    }
    
    public function helpAction()
    {
        
    }
    
    public function aboutAction()
    {
        
    }

    public function formListAction()
    {        
        $this->view->headLink()->appendStylesheet('/css/formList.css');  
        $this->view->headScript()->appendFile($this->_jsDir . 'formList.js');
        $this->view->formFilename = 'form_name';  
        
        // Add or rename a form
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('newFormFilename');
            $oldName = $this->getRequest()->getPost('oldName');
            if (!empty($formFilename))
            {                
                $xmlManager = new Application_Model_XmlManager(null, true);     
                if (empty($oldName))           
                    $result = $xmlManager->createForm($formFilename);
                else
                    $result = $xmlManager->renameForm($oldName, $formFilename);
                
                if (is_string($result))
                {
                    $this->view->errorsForm = $result;
                          
                    $this->view->formFilename = $formFilename;                  
                    $this->view->oldFilename = $oldName;
                }
            }
        }
        
        // Get the config keys
        $config = Zend_Registry::get('config');
        $formFilePath = $config->zfm->formDir;
        $xmlFilesPath = $config->zfm->formXmlDir;
        $xmlFiles = scandir($xmlFilesPath);
        
        
        // Save the datas about the xml files
        $fileList = array();
        foreach($xmlFiles as $xmlFile)
        {
            if (!in_array($xmlFile, array('.', '..')) && (strpos($xmlFile, '.xml') !== false))
            {
                $tmpList['path'] = '/' . $xmlFilesPath . $xmlFile;
                $tmpList['filename'] = basename($xmlFile);
                $tmpList['realname'] = basename($xmlFile, '.xml');
                $tmpList['date'] = date('Y-m-d H:i:s', filemtime($xmlFilesPath . $xmlFile));
                
                $formMadeName = $tmpList['realname'] . '.php';
                $formMadePath = $formFilePath . $formMadeName;
                
                if (file_exists($formMadePath) && !is_dir($formMadePath))
                {
                    $tmpList['formMade'] = $formMadeName;
                }
                else
                    unset($tmpList['formMade']);
                
                $fileList[] = $tmpList;
            }
        }
        
        $this->view->fileList = $fileList;
    }
    
    
    public function formDeleteAction()
    {
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            if (!empty($formFilename))
            {                
                $xmlManager = new Application_Model_XmlManager(null, true);
                $result = $xmlManager->deleteForm($formFilename);
                
                $this->_helper->redirector('form-list', 'index');
            }
        }
        else
            $this->_helper->redirector('form-list', 'index');
    }

    
    public function formTestAction()
    {
        // Get the config keys
        $config = Zend_Registry::get('config');
        $formClassPrefix = $config->zfm->formClassPrefix;
        $formDir =  $config->zfm->formDir;
        $customCssDir = $config->zfm->customCssDir;
        
        // Include the user's css for the test part
        $customCssFiles = glob($customCssDir . '*.css');
        foreach($customCssFiles as $file)
        {
            $file = substr($file, 1); // Remove the first dot in the filepath
            $this->view->headLink()->appendStylesheet($file);	
        }
        
        
        // Build name and path
        $filename = $this->getRequest()->getParam('formFilename');
        $formName = basename($filename, '.php');
        $formFilepath = $formDir. $filename;
        
        if (!empty($filename) && file_exists($formFilepath) && !is_dir($formFilepath))
        {
            require_once $formFilepath;  

            $className = $formClassPrefix . $formName;
            $form = new $className();
            
            $this->view->form = $form;
            $params = '';
                        
            if ($this->getRequest()->isPost())
                $params = $this->getRequest()->getPost();
            if ($this->getRequest()->isGet())
                $params = $this->getRequest()->getQuery();
            
            if (!empty($params))            
            {
                if ($form->isValid($params))
                {
                    Zend_Debug::dump($params);
                }
                else 
                {
                    $form->populate($params);
                }
            }            
        }
        else
            $this->_helper->redirector('form-list', 'index');
    }
    
    
    /**
     * It is the main pain of the application. It will allow you tu create your Zend
     * Form.
     * 
     */
    public function formMakerAction()
    {
        $view = $this->view;
        
        // System main javascript files
        $view->headScript()->appendFile($this->_jsDir . 'zfmVars.js');
        $view->headScript()->appendFile($this->_jsDir . 'tools.js');
        
    	$view->headScript()->appendFile($this->_jsDir . 'initUserInterface.js'); 
    	$view->headScript()->appendFile($this->_jsDir . 'ajaxCalls.js');
    	$view->headScript()->appendFile($this->_jsDir . 'elementManager.js');   	    	
    	$view->headScript()->appendFile($this->_jsDir . 'elUpdateManager.js');   	
    	
    	// Add the edit element tabs js files
    	$view->headScript()->appendFile($this->_jsDir . 'editElementTabGeneral.js');
    	$view->headScript()->appendFile($this->_jsDir . 'editElementTabValidators.js');
    	$view->headScript()->appendFile($this->_jsDir . 'editElementTabFilters.js');
    	$view->headScript()->appendFile($this->_jsDir . 'editElementTabOptions.js');
    	$view->headScript()->appendFile($this->_jsDir . 'editElementTabDecorators.js');   	
    		        
        // My css
        $view->headLink()->appendStylesheet('/css/formMaker.css');  
        $view->headLink()->appendStylesheet('/css/formMakerDialogs.css');          
        
        // Jquery libraries
        $view->headScript()->appendFile($this->_jsDir . 'jquery.contextMenu.js');		
        $view->headScript()->appendFile($this->_jsDir . 'jquery.toastmessage.js');
        $view->headScript()->appendFile($this->_jsDir . 'jquery.selectboxes.js');	 
        $view->headScript()->appendFile($this->_jsDir . 'jquery.tooltip.js');	 
        
        // Jquery librairies CSS
		$view->headLink()->appendStylesheet('/css/jquery.contextMenu.css');	
        $view->headLink()->appendStylesheet('/css/jquery.toastmessage.css'); 
        $view->headLink()->appendStylesheet('/css/jquery.tooltip.css'); 
        
        
        
        
        
        // Set the name of the xml file
        $formFilename = $this->getRequest()->getParam('formFilename');
        $this->view->formFilename = $formFilename;
            
            
        // Set the form for the general tab of the edit element dialog
        $generalForm = new Application_Form_TabGeneral();
        $this->view->generalForm = $generalForm;
        
        // Set the form for the validators tab of the edit element dialog
        $validatorForm = new Application_Form_TabValidators();
        $this->view->validatorInputsForm = $validatorForm;
        
        // Set the form for the filters tab of the edit element dialog
        $filtersForm = new Application_Form_TabFilters();
        $this->view->filtersInputsForm = $filtersForm;
        
        $optionsForm = new Application_Form_TabOptions();
        $this->view->multiOptionsForm = $optionsForm->getSubForm('multiOptions');
        
        // Get the list of options for standart validators
        $validatorsManager = new Application_Model_ValidatorsManager(Application_Model_ValidatorsManager::VALIDATORS);
        $validatorsOptions = $validatorsManager->buildSelectOptions();
        
        // Get the list of options for file specific validators
        $validatorsFileManager = new Application_Model_ValidatorsManager(Application_Model_ValidatorsManager::VALIDATORS_FILE);
        $validatorsFileOptions = $validatorsFileManager->buildSelectOptions();
        
        // Get the list of options for file specific validators
        $filtersManager = new Application_Model_ValidatorsManager(Application_Model_ValidatorsManager::FILTERS);
        $filtersOptions = $filtersManager->buildSelectOptions();
        
        $this->view->validatorsList = $validatorsOptions . $validatorsFileOptions;
        $this->view->filtersList = $filtersOptions;
    }
}
