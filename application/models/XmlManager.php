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
class Application_Model_XmlManager
{    
    private $_params = array();
    
    private $_xmlFileContent;
    private $_xmlPath;
    private $_xmlElement;
    
    
    /**
     * 
     * Get an xml file content
     * @param string $xmlPath
     * @param string $allowEmptyPath
     */
    public function __construct($xmlPath = null, $allowEmptyPath = false)
    {
        $this->_initParams();

        // Skip that if we allowed to use an empty path
        if (!$allowEmptyPath)
        {
            // If no path given, throw error
            if ($xmlPath === null) 
            {
                throw new Zend_Exception('The path given to XmlManager was null.');   
            }
            
            $realXmlPath = $this->_params['xmlDir'] . $xmlPath;
            
            // Check for the file content if it exists
            if (file_exists($realXmlPath))
            {
                if (!is_dir($realXmlPath))
                {
                    $xmlContent = file_get_contents($realXmlPath);
                    
                    $this->_xmlPath = $realXmlPath;
                    $this->_xmlFileContent = $xmlContent;
                }
                else
                {
                    throw new Zend_Exception('The path given to XmlManager is a directory. The path was : ' . $realXmlPath);
                }
            }
            else
            {
                throw new Zend_Exception('The path given to XmlManager doesn\'t exists. The path was : ' . $realXmlPath);
            }
        }
    }
    
    /*******************
     * Getter
     *******************/
        
    /**
     * 
     * Get an element from the file requesting on his id and prepare it for json output
     * @param int $id
     * @return A JSON string corresponding to this element's properties
     */
    public function getElementInJson($id)
    {
        $sxe = $this->_openXml();
        $element = $sxe->xpath('//element[@id=' . $id . ']');
        
        if (count($element) == 1)
        {
            $element = $element[0];
            
            $json = Zend_Json::encode($element);
            
            // Is there a tag attribs ?
            if (!empty($element->attribs))
            {
                // Get the equivalent of this tag in json
                $attribs = $this->_getAttribsInJSON($element);
                
                // If there is something in it, place it in the json string 
                if (!empty($attribs))
                {
                    $json_attribs = '"attribs": ' . $attribs;           
                    
                    $json = preg_replace('#"attribs":.*}#U', $json_attribs, $json);        
                }
                // Else rebuild the json string without attribs
                else 
                {
                    unset($element->attribs);
                    
                    $json = Zend_Json::encode($element);
                }
            }
            
            return $json;
        }
        else
        {
            throw new Zend_Exception('Didn\'t found the element (id=' . $id .') in the document.');
        }
    }

        
    /**
     * 
     * Get the XML Element string
     */
    public function getXmlElement()
    {
        return $this->_xmlElement;
    }
    
	/**
     * 
     * Get the form's attributes
     */
    public function getAttribs()
    {
        $sxe = $this->_openXml();
        
        return $this->_getAttribsInJSON($sxe);
    }
    
    public function getSxe()
    {
        return $this->_openXml();
    }
    
    
    
    /*******************
     * Public methods
     *******************/
    /**
     * 
     * Create a new form
     * @param string $formFilename
     */
    public function createForm($formFilename)
    {
        // Get the directoreis infos
        $dir = Zend_Registry::get('config')->zfm->formXmlDir;
        $filePath = $dir . $formFilename . '.xml';
        
        if (!file_exists($filePath))
        {
            // Get the content to write
            $xmlContent = $this->_getFormDefaultStructure();
            
        
            if (!preg_match('#^[A-Za-z0-9-_]+$#', $formFilename))
                return 'Please only use alphanumeric characters, underscores and dashes in the name.';
                
            // Create the file and write in it
            $handle = fopen($filePath, 'w+');
            
            if ($handle != false)
            {
                $result = fwrite($handle, $xmlContent);
                fclose($handle);
                
                if ($result === false)
                {
                    unlink($filePath);
                    return 'Unable to write the content in the file. File deleted.';
                }
                else
                    return true;
            }
            else
                return 'Unable to create the file (Check chmod on ' . $dir . ')';
            
        }
        else
            return 'This filename is already used.';
    }
    /**
     * 
     * Delete an existing form
     * @param string $formFilename
     */
    public function deleteForm($formFilename)
    {
        $config =Zend_Registry::get('config');
        $dir = $config->zfm->formXmlDir;
        $filePath = $dir . $formFilename . '.xml';
        $dirFormMade = $config->zfm->formDir;
        $formMadePath = $dirFormMade . $formFilename . '.php';
        
        if (file_exists($filePath))
        {
			if (file_exists($formMadePath))
				unlink ($formMadePath);
			
            return unlink($filePath);
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 
     * Rename a form
     * @param string $oldName
     * @param string $newName
     */
    public function renameForm($oldName, $newName)
    {
        $dir = Zend_Registry::get('config')->zfm->formXmlDir;
        $filePath = $dir . $oldName . '.xml';
        
        if (file_exists($filePath) && !file_exists($dir . $newName . '.xml'))
        {
            if (!preg_match('#^[A-Za-z0-9-_]+$#', $newName))
                return 'Please only use alphanumeric characters, underscores and dashes in the name.';
                
            if (rename($filePath, $dir . $newName . '.xml'))
            {
                return true;
            }
            else
                return 'Unable to rename the file.';
        }
        else
        {
            return 'The file doesn\'t exist, or the new name is already used. Unable to rename it.';
        }
    }
    
    /**
     * 
     * Add or edit an attribute on form depending on if the attrib name is already present
     * @param string $name
     * @param string $value
     */
    public function addEditFormAttribute($name, $value)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $form = $sxe;
                
        if (!isset($form->attribs))
        {
            $form->addChild('attribs');
        }    
        else
        {
            $attribFound = $form->xpath('/form/attribs/attrib[@name="' . $name . '"]');
            if (!empty($attribFound))
            {
                $attribFound[0][0] = $value;
            }
        }
        
        if (empty($attribFound))
        {
            // Add a new attrib node
            $newChild = $form->attribs->addChild('attrib', $value);
            
            // Add an attribute to the node we made                            
            $newChild->addAttribute('name', $name);       
        }
        
        // Save the new xml file
        $this->_saveXml($sxe);
        return true;
    }
    
	/**
     * 
     * Delete an attribute from the form
     * @param string $name
     * @param string $value
     */
    public function deleteFormAttribute($name)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe;
        
        if (isset($element->attribs))
        {
            $attribFound = $element->xpath('/form/attribs/attrib[@name="' . $name . '"]');
            if (!empty($attribFound))
            {
                unset($attribFound[0][0]);                    
            }
        }
        
        // Save the new xml file
        $this->_saveXml($sxe);
        return true;
    }
    
    /**
     * 
     * Add a new element of the given type to the xml file. Store the xml structure in _xmlElement property.
     * @param string $type
     */
    public function addNewElement($type)
    {
        if (!in_array($type, $this->_params['types']))
        {
            throw new Zend_Exception('The type given (' . $type . ') isn\'t in the allowed list.');
            return;
        }
        
        // Create an instance of SimpleXmlElement for the file content
        $sxe = $this->_openXml();

        // Get the id of the last element in the xml and add 1
        $allElements = $sxe->elements->xpath('element');
        $maxId = 0;
        foreach($allElements as $element)
        {
            $currentId = (int)$element['id'];
            if ($currentId > $maxId)
                $maxId = $currentId;
        }
        $nextId = $maxId+1;
        
        // Create a default xml structure
        $this->_xmlElement = $this->_getElementDefaultStructure($nextId, $type);        
  
        // Add the new element to the list
        $sxe_child = $this->_openXml($this->_xmlElement);
        $sxe = $this->_sxeAppendChild($sxe->elements, $sxe_child); 

        // Save the xml file
        //$sxe->asXml($this->_xmlPath);
        
        $this->_saveXml($sxe);
    }
    
    /**
     * 
     * Update the xml file, set $newValue on $property for the element corresponding to $id
     * @param int $id : Element's id
     * @param string $property : Element's property to update
     * @param string $newValue : New value of the property
     * @return bool result's state
     */
    public function updateElementProperty($id, $property, $newValue)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']');
        
        // Update his value
        if (count($element) == 1)
        {            
            $element[0][0]->$property = $newValue;
        
            // Put the result as xml in this object's property to create view later
            $this->_xmlElement = $element[0][0]->asXml();
            
            // Save the new xml file
            //$sxe->asXml($this->_xmlPath);
            
            $this->_saveXml($sxe);
            return true;
        }
        else 
        {
            return false;
        }
    }
    
    /**
     * 
     * Delete an element from the xml file.
     * @param int $id : Id of the element to delete.
     * @return bool result's state
     */
    public function deleteElement($id)
    {
        $sxe = $this->_openXml();
        
        $elementToDelete = $sxe->elements->xpath('element[@id="' . $id . '"]');

        // If the element is found
        if (count($elementToDelete) > 0)
        {
            unset($elementToDelete[0][0]);
            //$sxe->asXml($this->_xmlPath);            
            $this->_saveXml($sxe);
            return true;
        }
        else
        {
            throw new Zend_Exception('The element ' . $id . ' wasn\'t found, delete cancelled');
            return false;    
        }
        
    }

    /**
     * 
     * Get all the element from the xml file, and return them as xml string
     * @return array of string elementsList
     */
    public function loadAllElements()
    {
        $elements = array();
        
        $sxe = $this->_openXml();
        foreach($sxe->elements->element as $element)
        {
            $elements[] = $element->asXml();
        }
        
        return $elements;
    }
    
    
    /*******************
     * Attributes edition
     *******************/
    /**
     * 
     * Add or edit an attribute on an element
     * @param int $id
     * @param string $name
     * @param string $value
     */
    public function addEditAttribute($id, $name, $value)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']');
        
        // Update his value
        if (count($element) == 1)
        {         
            $element = $element[0][0];   
            
            if (!isset($element->attribs))
            {
                $element->addChild('attribs');
            }    
            else
            {
                $attribFound = $element->xpath('//element[@id=' . $id . ']/attribs/attrib[@name="' . $name . '"]');
                if (!empty($attribFound))
                {
                    $attribFound[0][0] = $value;
                }
            }
            
            if (empty($attribFound))
            {
                // Add a new attrib node
                $element->attribs->addChild('attrib', $value);
                
                $lastElementPos = $element->attribs->children()->count()-1; // Get the position of the node we made            
                $attribsChild = $element->attribs->children(); // Get the childrens of attribS
                
    
                // Add an attribute to the node we made                            
                $attribsChild[$lastElementPos]->addAttribute('name', $name);       
            }
            
            // Put the result as xml in this object's property to create view later
            $this->_xmlElement = $element->asXml();
            
            
            // Save the new xml file
            $this->_saveXml($sxe);
            return true;
        }
        else 
        {
            return false;
        }
    }
    
	/**
     * 
     * Delete an attribute on an element
     * @param int $id
     * @param string $name
     */
    public function deleteAttribute($id, $name)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']');
        
        if (count($element) == 1)
        {         
            $element = $element[0][0];   
            
            if (isset($element->attribs))
            {
                $attribFound = $element->xpath('//element[@id=' . $id . ']/attribs/attrib[@name="' . $name . '"]');
                if (!empty($attribFound))
                {
                    unset($attribFound[0][0]);                    
                }
            }
            
            
            // Put the result as xml in this object's property to create view later
            $this->_xmlElement = $element->asXml();
            
            // Save the new xml file
            //$sxe->asXml($this->_xmlPath);
            $this->_saveXml($sxe);
            return true;
        }
        else 
        {
            return false;
        }
    }
    
    
    /*******************
     * Validators edition
     *******************/
    /**
     * 
     * Add a validator on an element
     * @param int $id : id of the element
     * @param string $name
     * @throws Zend_Exception
     */
    public function addValidator($id, $name)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']');
        
        $validatorPresent = $sxe->xpath('//element[@id=' . $id . ']/validators/validator[name="' . $name . '"]');
                
        if (!empty($validatorPresent))
        {
            throw new Zend_Exception('Validator (' . $name . ') already present on this element (' . $id . '), edit it.');
            return false;
        }
        
        // If the validator doesnt exists	
        if (!empty($element))
        {       
            $element = $element[0][0];   
            
            if (!isset($element->validators))
            {
                $element->addChild('validators');
            }
            
            $element->validators->addChild('validator');
            $validators = $element->validators->children();
            $newValidator = $validators[count($validators)-1];
            
            $newValidator->addChild('name', $name);
            $newValidator->addChild('bcof', 'false');            
            $newValidator->addChild('constructor');
            $newValidator->addChild('errorMessage');
            
            $this->_xmlElement = $element->asXml();
            
            
            // Save the new xml file       
            $result = $this->_saveXml($sxe);
            if ($result)
            {
                $datas = array('name' => $name, 'bcof' => false, 'constructor' => '', 'errorMessage' => '');
                $json = Zend_Json::encode($datas);
                
                return $json;
            }
            else
            {                
                throw new Zend_Exception('Unable to save the file at the end.');
                return false;
            }
        }
        else
        {
            throw new Zend_Exception('Element ' . $id . ' not found.');
        }
        
        return false;
    }
    
    /**
     * 
     * Delete a validator on an element
     * @param int $id : id of the element
     * @param string $name
     */
    public function deleteValidator($id, $name)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']/validators/validator[name="' . $name . '"]');
        
        // If we found the elemetn and the validator
        if (count($element) == 1)
        {       
            unset($element[0][0]);   
                    
            // Save the new xml file
            $result = $this->_saveXml($sxe);
            if ($result)
            {
                $datas = array('name' => $name);
                $json = Zend_Json::encode($datas);
                return $json;
            }
            else
            {
                throw new Zend_Exception('Unable to save the file at the end.');
                return false;
            }
        } 
        else
        {
            throw new Zend_Exception('Element ' . $id . ' doesn\'t exists or doesn\'t have a validator named ' . $name . '.');
        }
        
        return false;
    }
    
    /**
     * 
     * Update a validator on an element
     * @param int $id : Id of the element
     * @param string $name
     * @param string $propertyName
     * @param string $propertyValue
     */
    public function updateValidator($id, $name, $propertyName, $propertyValue)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']/validators/validator[name="' . $name . '"]');
        
        // If we found the elemetn and the validator
        if (count($element) == 1)
        {       
            $element = $element[0][0];

            $element->$propertyName = $propertyValue;
                    
            
            // Save the new xml file
            $result = $this->_saveXml($sxe);
            if ($result)
            {
                return true;
            }
            else
            {
                throw new Zend_Exception('Unable to save the file at the end.');
                return false;
            }
        } 
        else
        {
            throw new Zend_Exception('Element ' . $id . ' doesn\'t exists or doesn\'t have a validator named ' . $name . '.');
        }
        
        return false;
    }    
    
    
    
    /*******************
     * Filters edition
     *******************/
    
    /**
     * 
     * Add a filter on the element
     * @param int $id : Id of the element
     * @param string $name
     * @throws Zend_Exception
     */
    public function addFilter($id, $name)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']');
        
        $filterPresent = $sxe->xpath('//element[@id=' . $id . ']/filters/filter[name="' . $name . '"]');
        
        if (!empty($filterPresent))
        {
            throw new Zend_Exception('Filter (' . $name . ') already present on this element (' . $id . '), edit it.');
            return false;
        }
        
        // If we found it
        if (!empty($element))
        {       
            $element = $element[0][0];   
            
            if (!isset($element->filters))
            {
                $element->addChild('filters');
            }
            
            $element->filters->addChild('filter');
            $filters = $element->filters->children();
            $newfilter = $filters[count($filters)-1];
            
            $newfilter->addChild('name', $name);
            
            $this->_xmlElement = $element->asXml();
            
            
            // Save the new xml file
            //$result = $sxe->asXml($this->_xmlPath);
            $result = $this->_saveXml($sxe);
            
            if ($result)
            {
                $datas = array('name' => $name);
                $json = Zend_Json::encode($datas);
                
                //Zend_Debug::dump(Zend_Json::prettyPrint($json));
                
                return $json;
            }
            else
            {                
                throw new Zend_Exception('Unable to save the file at the end.');
                return false;
            }
        } 
        else
        {
            throw new Zend_Exception('Element ' . $id . ' not found.');
        }
        
        return false;
    }
    
    /**
     * 
     * Delete a filter on the element
     * @param int $id : elemet's id
     * @param string $name
     */
    public function deleteFilter($id, $name)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']/filters/filter[name="' . $name . '"]');
        
        // If we found the elemetn and the filter
        if (count($element) == 1)
        {       
            unset($element[0][0]);   
                    
            // Save the new xml file
            //$result = $sxe->asXml($this->_xmlPath);
            $result = $this->_saveXml($sxe);
            if ($result)
            {
                $datas = array('name' => $name);
                $json = Zend_Json::encode($datas);
                
                //Zend_Debug::dump(Zend_Json::prettyPrint($json));
                
                return $json;
            }
            else
            {
                throw new Zend_Exception('Unable to save the file at the end.');
                return false;
            }
        } 
        else
        {
            throw new Zend_Exception('Element ' . $id . ' doesn\'t exists or doesn\'t have a filter named ' . $name . '.');
        }
        
        return false;
    }
    
    /**
     * 
     * Update the filter on an element
     * @param int $id : element's id
     * @param string $name
     * @param string $propertyName
     * @param string $propertyValue
     */
    public function updateFilter($id, $name, $propertyName, $propertyValue)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']/filters/filter[name="' . $name . '"]');
        
        // If we found the elemetn and the filter
        if (count($element) == 1)
        {       
            $element = $element[0][0];

            $element->$propertyName = $propertyValue;
                    
            
            // Save the new xml file
            //$result = $sxe->asXml($this->_xmlPath);
            $result = $this->_saveXml($sxe);
            if ($result)
            {
                return true;
            }
            else
            {
                throw new Zend_Exception('Unable to save the file at the end.');
                return false;
            }
        } 
        else
        {
            throw new Zend_Exception('Element ' . $id . ' doesn\'t exists or doesn\'t have a filter named ' . $name . '.');
        }
        
        return false;
    }    
    
    
    
    /*******************
     * Options edition
     *******************/
    
    /**
     * 
     * Update the xml file, set $newValue on $property for the element's option corresponding to $id
     * @param int $id : Element's id
     * @param string $property : Element's option's property to update
     * @param string $newValue : New value of the property
     * @return bool result's state
     */
    public function updateElementOptionProperty($id, $property, $newValue)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $id . ']');
        
        // Update his value
        if (count($element) == 1)
        {   
            if (!isset($element[0][0]->spec))
                $element[0][0]->addChild('spec');
                
            $element[0][0]->spec->$property = $newValue;
        
            // Put the result as xml in this object's property to create view later
            $this->_xmlElement = $element[0][0]->asXml();
                        
            // Save the new xml file
            //$sxe->asXml($this->_xmlPath);
            $this->_saveXml($sxe);
            return true;
        }
        else 
        {
            return false;
        }
    }    
    
    /**
     * 
     * Add an option on the multioption element
     * @param int $idElement
     * @param string $text : display text
     */
    public function addMultiOption($idElement, $text)
    {
        $changeMade = false;
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $idElement . ']');
        
        // Found it !
        if (count($element) == 1)
        {         
            $element = $element[0][0];   
            
            // Not present
            $optionPresent = array();
            
            // Add multioptions if there isn't
            if (!isset($element->multioptions))
            {
                $element->addChild('multioptions');
                $changeMade = true;
            }   
            else
            {             
                // Did we found the option ?   
                $optionPresent = $element->xpath('//element[@id=' . $idElement . ']/multioptions[option="' . $text . '"]');
            } 
            
            // Option not present in the list
            if (empty($optionPresent))
            {
                // Add a new attrib node
                $newChild = $element->multioptions->addChild('option');                
                $newChild->addChild('text', $text);
                $newChild->addChild('value', $text);
                $newChild->addChild('checked', 'false');
                                
                
                $allOptions = $element->multioptions->xpath('option');
                $maxId = 0;
                foreach($allOptions as $option)
                {
                    $currentId = (int)$option['id'];
                    if ($currentId > $maxId)
                        $maxId = $currentId;
                }
                $nextId = $maxId+1;
                
                // Add an attribute to the node we made                            
                $newChild->addAttribute('id', $nextId);       

                $changeMade = true;
            }
            else            
                throw new Zend_Exception('The options ' . $text . ' is already present on this element.');
        
            // Put the result as xml in this object's property to create view later
            $this->_xmlElement = $element->asXml();
            
            // Save the new xml file
            $this->_saveXml($sxe);
            return $newChild;
        }
        else
        {
            throw new Zend_Exception('The element ' . $idElement . ' hasn\'t been found in the XML file.');
        }
    }

    /**
     * 
     * Update an option on the multioption element
     * @param int $idElement
     * @param int $idOption
     * @param string $property
     * @param string $newValue
     */
    public function updateMultiOptionValue($idElement, $idOption, $property, $newValue)
    {
        $changeMade = false;
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $idElement . ']');
        
        // Found it !
        if (count($element) == 1)
        {         
            $element = $element[0][0];   
            
            // Did we found the option ?   
            $optionPresent = $element->xpath('//element[@id=' . $idElement . ']/multioptions/option[@id="' . $idOption . '"]');
            
            // We found the option
            if (!empty($optionPresent))
            {
                $option = $optionPresent[0][0];
                
                // Add an attribute to the node we found                          
                $option->$property = $newValue;    

                // Put the result as xml in this object's property to create view later
                $this->_xmlElement = $element->asXml();
                
                // Save the new xml file
                $this->_saveXml($sxe);
                return $option;
            }
            else
                throw new Zend_Exception('The options ' . $idOption . ' hasn\'t been found on this element.');
        }
        else
            throw new Zend_Exception('The element ' . $idElement . ' hasn\'t been found in the XML file.');
    }

    /**
     * 
     * Delete an option on the multioption element
     * @param int $idElement
     * @param int $idOption
     */
    public function removeMultiOption($idElement, $idOption)
    {
        $changeMade = false;
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $idElement . ']');
        
        // Found it !
        if (count($element) == 1)
        {         
            $element = $element[0][0];
            
            // Did we found the option ?   
            $optionPresent = $element->xpath('//element[@id=' . $idElement . ']/multioptions/option[@id="' . $idOption. '"]');
            
            if (!empty($optionPresent))
            {
                unset($optionPresent[0][0]);
                
                // Put the result as xml in this object's property to create view later
                $this->_xmlElement = $element->asXml();
                
                // Save the new xml file
                $this->_saveXml($sxe);
                return true;
            }
            else
                throw new Zend_Exception('The options ' . $idOption . ' hasn\'t been found on this element.');
        }
        else
            throw new Zend_Exception('The element ' . $idElement . ' hasn\'t been found in the XML file.');
    }
    
    /**
     * 
     * Add a decorator on an element
     * @param int $idElement
     * @param string $decClass
     * @param string $decOptions
     */
    public function addDecorator($idElement, $decClass, $decOptions)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $idElement . ']');
        
        // Found it !
        if (count($element) == 1)
        {         
            $element = $element[0][0];   
            
            // Not present
            $optionPresent = array();
            
                  
            
            $allDec = $element->decorators->xpath('decorator');
            $maxId = 0;
            foreach($allDec as $dec)
            {
                $currentId = (int)$dec->id;
                if ($currentId > $maxId)
                    $maxId = $currentId;
            }
            $nextId = $maxId+1;
                         
              // Add multioptions if there isn't
            if (!isset($element->decorators))
            {
                $element->addChild('decorators');
                $changeMade = true;
            }
            
            // Add a new attrib node
            $newChild = $element->decorators->addChild('decorator');                
            $newChild->addChild('id', $nextId);
            $newChild->addChild('name', $decClass);
            $newChild->addChild('options', $decOptions);
            
                    

            // Put the result as xml in this object's property to create view later
            $this->_xmlElement = $element->asXml();
            
            // Save the new xml file
            $this->_saveXml($sxe);
            return $newChild;
            
        }
        else
            return false;
    }
    
    /**
     * 
     * Update a decorator on the element
     * @param int $idElement
     * @param int $decId
     * @param string $decClass
     * @param string $decOptions
     */
    public function updateDecorator($idElement, $decId, $decClass, $decOptions)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $idElement . ']');
        
        // Found it !
        if (count($element) == 1)
        {         
            $element = $element[0][0];   
            
            $decorator = $element->xpath('decorators/decorator[id="' . $decId . '"]');
            
            if (count($decorator) == 1)
            {
                $decorator = $decorator[0][0];
                
                // Update values 
                $decorator->name = $decClass;
                $decorator->options = $decOptions;
                
    
                // Put the result as xml in this object's property to create view later
                $this->_xmlElement = $element->asXml();
                
                // Save the new xml file
                $this->_saveXml($sxe);
                return $decorator;
            }
            else
            {
                throw new Zend_Exception('Decorators ' . $decId . ' not found on the element ' . $idElement);
                return false;
            }   
        }
        else
        {
            throw new Zend_Exception('The element ' . $idElement . ' hasn\'t been found');
            return false;
        }
    }
    
    /**
     * 
     * Delete a decorator from this element
     * @param int $idElement
     * @param int $decId
     */
    public function deleteDecorator($idElement, $decId)
    {
        $sxe = $this->_openXml();
        
        // Search the element with id
        $element = $sxe->xpath('//element[@id=' . $idElement . ']');
        
        // Found it !
        if (count($element) == 1)
        {         
            $element = $element[0][0];   
            
            $decoratorResult = $element->xpath('decorators/decorator[id="' . $decId . '"]');
            
            if (count($decoratorResult) == 1)
            {
                $decorator = $decoratorResult[0][0];
                $decoratorsDatas = array('decId' => (string)$decorator->id, 
                                        'decClass' => (string)$decorator->name, 
                                        'decOptions' => (string)$decorator->options);
                
                unset($decoratorResult[0][0]);
                
                
                // Put the result as xml in this object's property to create view later
                $this->_xmlElement = $element->asXml();
                
                // Save the new xml file
                $this->_saveXml($sxe);
                return $decoratorsDatas;
            }
            else
            {
                throw new Zend_Exception('Decorators ' . $decId . ' not found on the element ' . $idElement);
                return false;
            }   
        }
        else
        {
            throw new Zend_Exception('The element ' . $idElement . ' hasn\'t been found');
            return false;
        }
    }
    /********************
     * Private methods 
     *******************/
    
    /**
     * 
     * This method get the configuration file and load the params needed in $this->_params
     */
    private function _initParams()
    {  
    	$config = Zend_Registry::get('config');
    	
    	try
    	{
        	$this->_params['xmlDir'] = $config->zfm->formXmlDir;
        	
        	$this->_params['types'] = explode(',', $config->zfm->types->all);
        	$this->_params['types'] = array_map('trim', $this->_params['types']);
        	
        	$this->_params['types_nolabel'] = explode(',', $config->zfm->types->nolabel);
        	$this->_params['types_nolabel'] = array_map('trim', $this->_params['types_nolabel']);
        	
        	$this->_params['types_nodesc'] = explode(',', $config->zfm->types->nodesc);
        	$this->_params['types_nodesc'] = array_map('trim', $this->_params['types_nodesc']);
        	
        	$this->_params['types_multioptions'] = explode(',', $config->zfm->types->multioptions);
        	$this->_params['types_multioptions'] = array_map('trim', $this->_params['types_multioptions']);
        	
        	$this->_params['types_spec'] = explode(',', $config->zfm->types->spec);
        	$this->_params['types_spec'] = array_map('trim', $this->_params['types_spec']);
    	} 
    	catch (Zend_Exception $e) 
    	{ 
    	    throw new Exception('An error occured while loading params from Zend Registry. The error says : ' . $e->getMessage());
    	}
    }
  
    /**
     * 
     * Return the default structure to have a valid xml file
     */
    private function _getFormDefaultStructure()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<form>
	<attribs>
		<attrib name="name">form1</attrib>
		<attrib name="method">post</attrib>
		<attrib name="action"></attrib>
	</attribs>
    <name>formname</name>
    <method>post</method>
    <elements>
    </elements>
</form>';
		
		return $xml;
    }
    
    /**
     * 
     * This method return an xml string corresponding to an element of $type
     * @param int $id : Id of the new element
     * @param string $type : Type of the element to create
     * @return string The element's xml structure.
     */
    private function _getElementDefaultStructure($id, $type)
    {
        $xml = '';
        
        // Common structure
        $xml .= '<element id="' . $id . '" type="' . $type . '">' . PHP_EOL;
		$xml .= '	<name>' . $type . $id . '</name>' . PHP_EOL;
		$xml .= '	<order>' . $id . '</order>' . PHP_EOL;
		$xml .= '	<required>true</required>' . PHP_EOL;
		
		// Some kind of elements doesn't need a label
		if (!in_array($type, $this->_params['types_nolabel']))
		{
		    $xml .= '<label>' . $type . '\'s label(' . $id . ')</label>' . PHP_EOL;
		}
		
		switch($type)
		{
		    case 'radio':
		        $xml .= '<multioptions>' . PHP_EOL;
		        $xml .= '<option id="1"><text>Choice 1</text><value>radio_1</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '<option id="2"><text>Choice 2</text><value>radio_2</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '</multioptions>' . PHP_EOL;
		        break;
		        
	        case 'select':
		        $xml .= '<multioptions>' . PHP_EOL;
		        $xml .= '<option id="1"><text>Select a single option</text><value></value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '</multioptions>' . PHP_EOL;
		        break;
		        
	        case 'multicheckbox':
		        $xml .= '<multioptions>' . PHP_EOL;
		        $xml .= '<option id="1"><text>First option</text><value>multiselect_1</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '<option id="2"><text>Second option</text><value>multiselect_2</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '<option id="3"><text>Third option</text><value>multiselect_3</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '</multioptions>' . PHP_EOL;
		        break;
		        
	        case 'multiselect':
		        $xml .= '<multioptions>' . PHP_EOL;
		        $xml .= '<option id="1"><text>Select one or more option</text><value>multiselect_1</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '<option id="2"><text>Second option</text><value>multiselect_2</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '<option id="3"><text>Third option</text><value>multiselect_3</value><checked>false</checked></option>' . PHP_EOL;
		        $xml .= '</multioptions>' . PHP_EOL;
		        break;
		        
	        default: 
	            break;
		}
		    
		$xml .= '</element>' . PHP_EOL;
				
		return $xml;
    }
    
    /**
     * 
     * Add an SimpleXMLElement as child to an other SimpleXMLElement.
     * @param SimpleXMLElement $sxe : parent SimpleXMLElement
     * @param SimpleXMLElement $child : SimpleXMLElement to add
     * @return A SimpleXMLElement with the child element
     */
    private function _sxeAppendChild($sxe, $child)
    {
        // Create dom node from simplexml nodes
    	$originalNode = dom_import_simplexml($sxe); // This is a dom node in which we want to add an other node
    	$newNode = dom_import_simplexml($child); // This is the node to add
    	
    	// Import the new node into the document of the orignal one
    	$newNodeImported = $originalNode->ownerDocument->importNode($newNode, true);
    
    	// Append the new node to the original one
    	$node = $originalNode->appendChild($newNodeImported);
    	    	
        // Return the whole document, as xml, converted into a SimpleXMLElment
    	return (new SimpleXMLElement($newNodeImported->ownerDocument->saveXML()));
    }
    
    /**
     * 
     * Create an instance of SimpleXMLElement with this class's method or the string in parameter
     * @param string $xml
     */
    private function _openXml($xml = null)
    {
        
        try 
        {           
            if (!empty($xml))
                $sxe = new SimpleXMLElement($xml);
            else
                $sxe = new SimpleXMLElement($this->_xmlFileContent);

            return $sxe;        
        }
        catch (Exception $e) 
        {
            throw new Zend_Exception('Xml file format invalid.');
        }
    }

    /**
     * 
     * Save the SimpleXMLElemetn given in parameters at the path given in this class's property
     * @param unknown_type $sxe
     */
    private function _saveXml($sxe)
    {        
        $result = $sxe->asXml($this->_xmlPath);
        $xmlWellprinted = $this->_xmlpp($sxe->asXml()); 
        file_put_contents($this->_xmlPath, $xmlWellprinted);
		
		return $result;
    }
    
    /** Prettifies an XML string into a human-readable and indented work of art 
     * Source : http://gdatatips.blogspot.com/2008/11/xml-php-pretty-printer.html
     * @author Eric (Google)
     * @param string $xml The XML as a string 
     * @param boolean $html_output True if the output should be escaped (for use in HTML) 
     */
    private function _xmlpp ($xml, $html_output = false)
    {
        $xml_obj = new SimpleXMLElement($xml);
        $level = 4;
        $indent = 0; // current indentation level  
        $pretty = array();
        // get an array containing each XML element  
        $xml = explode("\n", 
        preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));
        // shift off opening XML tag if present  
        if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
            $pretty[] = array_shift($xml);
        }
        foreach ($xml as $el) {
            if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
                // opening tag, increase indent  
                $pretty[] = str_repeat(' ', $indent) . $el;
                $indent += $level;
            } else {
                if (preg_match('/^<\/.+>$/', $el)) {
                    $indent -= $level; // closing tag, decrease indent  
                }
                if ($indent < 0) {
                    $indent += $level;
                }
                $pretty[] = str_repeat(' ', $indent) . $el;
            }
        }
        $xml = implode("\n", $pretty);
        return ($html_output) ? htmlentities($xml) : $xml;
    }  
    
    /**
     * 
     * Return a json string to store an element's attributes
     * @param SimpleXMLElement $element : php element representing the complete element
     */
    private function _getAttribsInJSON($element)
    {
        $json = '';
        $ar = array();
        if ($element->attribs->children()->count() > 0)
        {
            // Run the children of attribs
            foreach($element->attribs->children() as $value)
            {  
                
                $name = (string)$value->attributes()->name;           
                $ar[$name] = (string)$value;
            }
        }
        // If there is an <attribs> but no <attrib>, just return empty string
        $json = (!empty($ar)) ? Zend_Json::encode($ar) : '';
        
        return $json;
    }
}