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
class Application_Model_ValidatorsManager
{    
    const VALIDATORS = 0;
    const VALIDATORS_FILE  = 1;
    const FILTERS = 2;
    
    private $_params = array();
    private $_tags = array(self::VALIDATORS => array('validators', 'validator'),
                            self::VALIDATORS_FILE => array('validators', 'validator'),
                            self::FILTERS => array('filters', 'filter'));
    private $_xmlFileContent;
    private $_typeFile = self::VALIDATORS;
    
    
    public function __construct($fileSelected)
    {
        $this->_initParams();
        switch($fileSelected)
        {
            case Application_Model_ValidatorsManager::VALIDATORS_FILE :
                $file = $this->_params['validatorsFile'];
                break;
                
            case Application_Model_ValidatorsManager::FILTERS :
                $file = $this->_params['filters'];
                break;
                
            default:
            case Application_Model_ValidatorsManager::VALIDATORS :
                $file = $this->_params['validators'];
                break;
        }
        
        // Check for the file content if it exists
        if (file_exists($file))
        {
            if (!is_dir($file))
            {
                $xmlContent = file_get_contents($file);
                
                $this->_xmlFileContent = $xmlContent;
                
                $this->_typeFile = $fileSelected;
            }
            else
            {
                throw new Zend_Exception('The path given to ValidatorsManager is a directory. The path was : ' . $file);
            }
        }
        else
        {
            throw new Zend_Exception('The path given to ValidatorsManager doesn\'t exists. The path was : ' . $file);
        }
    }
    
    /*******************
     * Getter
     *******************/
        
    /**
     * 
     * Return an element of the file in JSON format
     * @param int $id
     */
    public function getElementInJson($name)
    {
        $sxe = new SimpleXMLElement($this->_xmlFileContent);
        $validator = $sxe->xpath('//' . $this->_tags[$this->_typeFile][1] . '[@class="' . $name . '"]');
        if (count($validator) == 1)
        {
            $validator = $validator[0];
            $validator->description = nl2br($validator->description);
            $json = Zend_Json::encode($validator);
            
            return $json;
        }
        else
        {
            throw new Zend_Exception('Didn\'t found the valdiator ' . $name . ' in the document.');
        }
    }
    
    /**
     * 
     * Build the options of a select list with the file content
     */
    public function buildSelectOptions()
    {
        $sxe = new SimpleXMLElement($this->_xmlFileContent);
        $validators = $sxe->xpath('//' . $this->_tags[$this->_typeFile][1]);
        $result = '';
        
        foreach($validators as $validator)
        {
            $result .= '<option value="' . trim($validator->attributes()->class) . '">' . trim($validator->attributes()->class) . '</options>' . PHP_EOL;
        }
        
        return $result;
    }
    /**
     * 
     * Return the list of element in the file
     */
    public function getValidatorList()
    {
        $sxe = new SimpleXMLElement($this->_xmlFileContent);
        $validators = $sxe->xpath('//' . $this->_tags[$this->_typeFile][1]);
        $result = '';
        
        foreach($validators as $validator)
        {
            $result[] = trim($validator->attributes()->class);
        }
        
        return $result;
    }
    
    /**
     * 
     * This method get the configuration file and load the params needed in $this->_params
     */
    private function _initParams()
    {  
    	$config = Zend_Registry::get('config');
    	
    	try
    	{
        	$this->_params['validators'] = $config->zfm->validatorsFilePath;
        	$this->_params['validatorsFile'] = $config->zfm->validatorsFileInputFilePath;
        	$this->_params['filters'] = $config->zfm->filtersFilePath;
    	}
    	catch (Zend_Exception $e) 
    	{ 
    	    throw new Exception('An error occured while loading params from Zend Registry. The error says : ' . $e->getMessage());
    	}
    }
    
}