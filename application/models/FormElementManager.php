<?php
/*
Copyright (C) 2011 Matthieu Di Blasio <matthieu.diblasio@gmail.com>

This file is part of Zen Form Maker.

Zen Form Maker is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Zen Form Maker is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with Zen Form Maker.  If not, see <http://www.gnu.org/licenses/>.
*/
class Application_Model_FormElementManager
{    
    private $_buildErrors;
    
    public function __construct()
    {

    }
    
    public function getBuildErrors()
    {
        return $this->_buildErrors;
    }
    
    /*
     * Public methods
    */

    /**
     * 
     * Load some xml elements, order them, render them and return a JSON-ready array to display them.
     * @param string $xmlList : List of xml elements
     * @param Zend_View $view : The view to render it
     * @return An array containing arrays containing the element rendered view and his id.
     */
    public function loadForm($xmlList, $view)
    {
        $elementsRendered = array();
        $buildErrors = array();
        
        // 
        foreach($xmlList as $xmlElement)
        {
            $element = new Application_Model_FormElement($xmlElement);

            // Get the zend form element and his rendered view
            $formElement = $element->getElement();
            $renderedView = $element->getRenderedView($view);
            
            // Prepare the datas to display the element in json
            $displayOutput = array('el' => $renderedView,
                                   'id' => $element->getElementId());
            
            $this->_buildErrors[] = $element->getErrors();
            
            // Put the result in an array, so we'll sort by order
            $elementsRendered[$formElement->getOrder()] = $displayOutput;            
        }
        
        // Sort the element by their order
        ksort($elementsRendered);
        
        //
        return $elementsRendered;
    }
    
    /**
     * 
     * Update the element's order in the xml file.
     * @param string $newOrders : string with the order separeted by &
     * @param string $formFilename : name of the xml file
     * @throws Zend_Exception
     */
    public function updateOrders($newOrders, $formFilename)
    {
        $result = true;
        $orders = explode('&', $newOrders);
        for($i = 0; $i < count($orders); $i++)
        {
            $order = $orders[$i];
            $id = str_replace('orderid[]=', '', $orders[$i]);
            $order = $i+1;
                
            // Add the element to the xml file
            $xmlMng = new Application_Model_XmlManager($formFilename);
            $result &= $xmlMng->updateElementProperty($id, 'order', $order);
        }
        
        if (!$result)
            throw new Zend_Exception('One of the element\'s id wasn\'t found. Ordering may be incorrect.');
    }
    
    /**
     * 
     * Preview the form
     * @param array(string) $xmlList
     * @param string $formFilename
     * @param bool $saveResult
     */
    public function previewForm($xmlList, $sxeAttribs, $formFilename)
    {
        // Get the config keys
        $config = Zend_Registry::get('config');
        $formClassPrefix = $config->zfm->formClassPrefix;
        $formDir =  $config->zfm->formDir;
        $customCssDir = $config->zfm->customCssDir;
        
        
        // Build the code as tmp and get it
        $filename = $this->buildForm($xmlList, $sxeAttribs, $formFilename, true);
        $formName = basename($filename, '.php.preview');
        $className = $formClassPrefix . $formName;
         
        // Build name and path
        $formFilepath = $formDir. $filename;

        try 
        {
            // Include the user's css for the test part
            $customCssFiles = glob($customCssDir . '*.css');
            foreach($customCssFiles as $file)
            {
                echo '<style type="text/css">' . file_get_contents($file) . '</style>' . PHP_EOL;	
            }
            
            
            if (file_exists($formFilepath) && !is_dir($formFilepath))
            {
                require_once $formFilepath;
				
                $form = new $className();
                
                ob_start();
                    echo $form;
                    // Erase the file
                    unlink($formFilepath);                    
                // Return the form html
                return ob_get_clean();
                
            }
            else
                throw new Zend_Exception('Unable to find the file to preview.');
        }
        catch (Exception $e)
        {
            throw new Zend_Exception('An error occured while evaluating the form\'s code to preview');
        }
    }
    
    /**
     * 
     * Build the form's Zend Form class
     * @param array(string) $xmlList : elements from the xml file
     * @param SimpleXMLElement $sxeAttribs
     * @param string $formFilename
     * @param bool $saveResult
     */
    public function buildForm($xmlList, $sxeAttribs, $formFilename, $preview = false)
    {
        $params = Zend_Registry::get('config');
        $classPrefix = $params->zfm->formClassPrefix;
        $formSaveDir = $params->zfm->formDir;
        $formName = basename($formFilename, '.xml');
        
        $fileExt = ($preview === true) ? '.php.preview' : '.php';
        
        $phpCode = '<?php' . PHP_EOL . PHP_EOL;
        $phpCode .= '/**************************************************
Class generated by Zend Form Maker (http://zfm.matthieudiblasio.ch)
Author : Matthieu Di Blasio
**************************************************/' . PHP_EOL;
        
        $phpCode .= 'class ' . $classPrefix . $formName . ' extends Zend_Form' . PHP_EOL;
        $phpCode .= '{' . PHP_EOL;
        
        
        $initFunction = 'public function init()' . PHP_EOL;
        $initFunction .= '{' . PHP_EOL;
        
        $initFunction .= $this->_getFormAttribs($sxeAttribs);
        
        $elementsPhpCode = '';
        
        // Add the elements codes into method, and call theses in the init method
        foreach($xmlList as $xmlElement)
        {
            $element = new Application_Model_FormElement($xmlElement, true);
            $elementName = $element->getElement()->getName();
            
            $initFunction .= '$this->addElement($this->_' . $elementName . '());' . PHP_EOL;
            
            // Start of the element's function
            $elementPhpcode = 'private function _' . $elementName . '()' . PHP_EOL;
            $elementPhpcode .= '{' . PHP_EOL;
            
            // Code to generate the element            
            $elementPhpcode .= $element->getPhpCode() . PHP_EOL;
            
            // End of the element's function
            $elementPhpcode .= '}' . PHP_EOL . PHP_EOL;
            
            // Add the element function in the code of all elements
            $elementsPhpCode .= $elementPhpcode;
        }
        
        // Add the init function to the class
        $initFunction .= '}' . PHP_EOL;
        $phpCode .= $initFunction;
        
        // Add the elements functions to the class
        $phpCode .= $elementsPhpCode;
        $phpCode .= '}' . PHP_EOL . PHP_EOL;
        
        
        // Indent the code
        $lvl = 0;
        $indentSize = 4;
        $tmpElementCode = explode(PHP_EOL, $phpCode);
        foreach($tmpElementCode as &$line)
        {
            if (strpos($line, '}') !== false) $lvl--;

            // Build space indent
            $space = $lvl * $indentSize;
            $space_str = str_repeat(' ', $space);   
                 
            $line = $space_str . $line;    
            
            if (strpos($line, '{') !== false) $lvl++;
        }
        $phpCode = implode(PHP_EOL, $tmpElementCode);
        
        
        // Save the result in a file ?
        $formMadePath = $formSaveDir . $formName . $fileExt;
        
        $handle = fopen($formMadePath, 'w+');
        
        if ($handle === false)
        {
            throw new Zend_Exception('Unable to create the file, fopen failed. Check the chmod and the filename.');
            return false;
        }
            
        fclose($handle);
        $resultInsert = file_put_contents($formMadePath, $phpCode);
        
        if ($resultInsert === false)    
        {
            unlink($formMadePath);
            throw new Zend_Exception('Insert data in the file. Check if the file really exists and the path is correct.');
            return false;
        } 
        
        
        return $formName . $fileExt;
    }
    
    /**
     * 
     * Get the attributes of the form as php code
     * @param SimpleXMLElement $sxeAttribs
     */
    private function _getFormAttribs($sxeAttribs)
    {
        // Read the attribs
        if ($sxeAttribs->attribs->count() > 0)
        {
            $phpCode = "// Set form attribs" . PHP_EOL;
            $attribs = 'array(';
            foreach($sxeAttribs->attribs->children() as $attrib)
            {
                $name = $attrib->attributes()->name;
                $attribs .= '\'' . $name . '\'=> \'' . $attrib . '\',' . PHP_EOL;
            }
            // Remove the last, and new line
            $attribs = trim($attribs, ','.PHP_EOL);
            
            // Close the array
            $attribs .= ')';
            
            // Add the array to the line to set it
            $phpCode .= '$attribs = ' . $attribs . ';' . PHP_EOL;
            
            // Add the attributes to the form
            $phpCode .= '$this->setAttribs($attribs);' . PHP_EOL;     
            return $phpCode;
        }
        else
            return false;
        
    }
        
}