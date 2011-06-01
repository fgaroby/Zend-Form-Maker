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
class XmlManagerController extends Zend_Controller_Action
{
    public function init()
    {   	
    	$this->_helper->viewRenderer->setNoRender(); // Disable the viewscript
		$this->_helper->layout->disableLayout();   // Disable the layout
    }
     
    /************************************************************
    *                                                           *
    *               Element management functions                *    
    *                                                           *
    *************************************************************/
    public function addElementAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $type = $this->getRequest()->getPost('type');
            if (!empty($type))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                
                try
                {
                    // Add the element to the xml file
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $xmlMng->addNewElement($type);

                    // Get the rendered element
                    $element = new Application_Model_FormElement();
                    $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                                            
                    // Get the element id
                    $id = $element->getElementId();
                    
                    // Prepare the datas to display the element in json
                    $displayOutput = array('el' => $elementRendered,
                                           'id' => (string)$id,
                                           'buildErrors' => $element->getErrors());
                    
                    // Return a json string
                    echo Zend_Json::encode($displayOutput);
 
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding</strong> an element.', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('No type given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }
    
    public function updateElementAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $id = $this->getRequest()->getPost('id');
            if (!empty($id))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                $property = $this->getRequest()->getPost('property');
                $newValue = $this->getRequest()->getPost('newValue');
                try
                {
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $updateState = $xmlMng->updateElementProperty($id, $property, $newValue);
                    
                    if ($updateState)
                    {
                        // Get the rendered element
                        $element = new Application_Model_FormElement();
                        $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                        
                        echo Zend_Json::encode(array('id' => $id, 
                        							'elementRendered' => $elementRendered,
                        							'buildErrors' => $element->getErrors()));
                    }
                    else
                    {
                        echo Zend_Json::encode(array('error' => 'The element ' . $id . ' has not been updated because the id wasn\'t found.'));
                    }
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>updating</strong> an element.', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('No id given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }      
    }
    
    public function getElementAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $id = $this->getRequest()->getPost('id');
            if (!empty($id))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                try
                {
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $json = $xmlMng->getElementInJson($id);
                    
                    echo $json;
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>getting</strong> the element.' , $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('No id given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }
    
    public function deleteElementAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $id = $this->getRequest()->getPost('id');
            if (!empty($id))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                try
                {
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $deletedState = $xmlMng->deleteElement($id);
                    
                    $json = Zend_Json::encode(array('msg' => 'Item correctly deleted'));
                    echo $json;
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>deleting</strong> an element. ', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('No id given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }
          
    /************************************************************
    *                                                           *
    *             Attributes management functions               *    
    *                                                           *
    *************************************************************/
    public function addEditElementAttributeAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            $value = $this->getRequest()->getPost('value');
            
            if (!empty($id) && !empty($name))
            {
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->addEditAttribute($id, $name, $value);

                    
                    if ($result)
                        echo Zend_Json::encode(array('msg' => 'Attribute ' . $name . ' added/updated', 'id' => $id));
                    else
                        echo Zend_Json::encode(array('error' => 'Unable to add/update the attribute ' . $name . ' to the element ' . $id));
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding or editing</strong> an element\'s attrbiute', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id or name empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }

    public function deleteElementAttributeAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            
            if (!empty($id) && !empty($name))
            {
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->deleteAttribute($id, $name);

                    if ($result)
                        echo Zend_Json::encode(array('msg' => 'Attribute ' . $name . ' deleted'));
                    else
                        echo Zend_Json::encode(array('error' => 'Unable to delete the attribute ' . $name . ' from the element ' . $id));
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>delete an attribute</strong> of an element', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id, name or value empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }
            

    /************************************************************
    *                                                           *
    *             Validators management functions               *    
    *                                                           *
    *************************************************************/
      
    public function addValidatorAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            
            if (!empty($id) && !empty($name))
            {
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->addValidator($id, $name);

                    if ($result != false)
                        echo $result;
                    else
                        $this->_printJsonError('Unable to add the validator ' . $name . ' to the element ' . $id);    
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding a validator</strong> to an element', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id or name empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    public function removeValidatorAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            if (!empty($id) && !empty($name))
            {
                try
                {              
                    $xmlMng = new Application_Model_XmlManager($formFilename);  
                    $result = $xmlMng->deleteValidator($id, $name);
                    
                    if ($result != false)
                        echo $result;
                    else
                        $this->_printJsonError('Unable to delete the validator ' . $name . ' from the element ' . $id);
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>removing a validator</strong> to an element', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id, name or value empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    public function updateValidatorAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            
            $propertyName = $this->getRequest()->getPost('propertyName');
            $propertyValue = $this->getRequest()->getPost('propertyValue');
            
            if (!empty($id) && !empty($name) && !empty($propertyName))
            {
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->updateValidator($id, $name, $propertyName, $propertyValue);

                    if ($result != false)
                        echo $result;
                    else
                        $this->_printJsonError('Unable to delete the validator ' . $name . ' from the element ' . $id);
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>updating a validator</strong> 
                    (' .  $name . ', ' . $propertyName . ' => ' . (empty($propertyValue)) ? '<i>empty</i>' : $propertyValue .') on the element ' . $id, $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id, name, propertyName or propertyValue empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    public function getValidatorDatasAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $name = $this->getRequest()->getPost('name');
            
            if (!empty($name))
            {
                try
                {            
                    $isFileValidator = preg_match("#^Zend_Validate_File.*$#", $name);      
                    $what = ($isFileValidator) ? Application_Model_ValidatorsManager::VALIDATORS_FILE : Application_Model_ValidatorsManager::VALIDATORS;
                    
                    $vManager = new Application_Model_ValidatorsManager($what);
                    $result = $vManager->getElementInJson($name);
                    

                    if ($result != false)
                    {
                        echo $result;
                    }
                    else
                        $this->_printJsonError('Unable to find the validator ' . $name . ' in the file.');
                }
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>reaading</strong> the validator file', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Validator name empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
        
    public function getValidatorsFileInputAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            try
            {                  
                $vManager = new Application_Model_ValidatorsManager(Application_Model_ValidatorsManager::VALIDATORS_FILE);
                $result = $vManager->getValidatorList();
                

                if ($result != false)
                {
                    echo Zend_Json::encode($result);
                }
                else
                    $this->_printJsonError('An error happened while getting the list of validators and it is really strange ...');
            }
            catch (Zend_Exception $e)
            {
                $this->_printJsonError('An error occured while <strong>reaading</strong> the validator file', $e->getMessage());
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }    
    

    /************************************************************
    *                                                           *
    *               Filters management functions                *    
    *                                                           *
    *************************************************************/    
         
    public function addFilterAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            
            if (!empty($id) && !empty($name))
            {
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->addFilter($id, $name);

                    if ($result != false)
                        echo $result;
                    else
                        $this->_printJsonError('Unable to add the filter ' . $name . ' to the element ' . $id);    
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding a filter</strong> to an element', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id or name empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    public function removeFilterAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            
            if (!empty($id) && !empty($name))
            {
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->deleteFilter($id, $name);

                    if ($result != false)
                        echo $result;
                    else
                        $this->_printJsonError('Unable to delete the filter ' . $name . ' from the element ' . $id);
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>removing a filter</strong> to an element', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id, name or value empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    public function updateFilterAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $id = $this->getRequest()->getPost('id');
            $name = $this->getRequest()->getPost('name');
            
            $propertyName = $this->getRequest()->getPost('propertyName');
            $propertyValue = $this->getRequest()->getPost('propertyValue');
            
            if (!empty($id) && !empty($name) && !empty($propertyName))
            {
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->updateFilter($id, $name, $propertyName, $propertyValue);

                    if ($result != false)
                        echo $result;
                    else
                        $this->_printJsonError('Unable to delete the filter ' . $name . ' from the element ' . $id);
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>updating a filter</strong> 
                    (' .  $name . ', ' . $propertyName . ' => ' . (empty($propertyValue)) ? '<i>empty</i>' : $propertyValue .') on the element ' . $id, $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id, name, or propertyName empty.('.$id.', '.$name.', '.$propertyName.')');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    public function getFilterDatasAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $name = $this->getRequest()->getPost('name');
            
            if (!empty($name))
            {
                try
                {               
                    $vManager = new Application_Model_ValidatorsManager( Application_Model_ValidatorsManager::FILTERS);
                    $result = $vManager->getElementInJson($name);
                    

                    if ($result != false)
                    {
                        echo $result;
                    }
                    else
                        $this->_printJsonError('Unable to find the filter ' . $name . ' in the file.');
                }
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>reaading</strong> the filters file', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Filter name empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
           
    /************************************************************
    *                                                           *
    *               Options management functions                *    
    *                                                           *
    *************************************************************/    
    
    public function getOptionsFormAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $type = $this->getRequest()->getPost('type');
            
            if (!empty($type))
            {             
                $optionForm = new Application_Form_TabOptions();
                
                // Get the form for this type of element
                ob_start(); 
                echo $optionForm->getSubForm($type);
                
                if ($type == 'captcha')
                {
                    echo $optionForm->getSubForm('captchaImage');
                    echo $optionForm->getSubForm('recaptcha');
                }                
                $formPrint = ob_get_clean();
                
                // Return a json string to get things
                $json = Zend_Json::encode(array('form' => $formPrint));
                echo $json;
            }
            else
            {                
                $this->_printJsonError('No type given.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }   
    }
    
    public function updateElementOptionAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $id = $this->getRequest()->getPost('id');
            if (!empty($id))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                $property = $this->getRequest()->getPost('property');
                $newValue = $this->getRequest()->getPost('newValue');
                try
                {
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $updateState = $xmlMng->updateElementOptionProperty($id, $property, $newValue);
  
                    if ($updateState)
                    {
                        // Get the rendered element
                        $element = new Application_Model_FormElement();
                        $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                        
                        echo Zend_Json::encode(array('id' => $id, 
                        							'elementRendered' => $elementRendered,
                        							'buildErrors' => $element->getErrors()));
                    }
                    else
                    {
                        $this->_printJsonError('Unable to update the property ' . $property . ' for the element ' . $id);
                    }
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>updating an element\'s option</strong> .', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('No id given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }      
    }
    
    
    public function addMultioptionAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $id = $this->getRequest()->getPost('id');
            if (!empty($id))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                $text = $this->getRequest()->getPost('text');
                try
                {
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $option = $xmlMng->addMultiOption($id, $text);
                    
                    // Get the rendered element
                    $element = new Application_Model_FormElement();
                    $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                    
                    echo Zend_Json::encode(array('option' => $option, 
                    'idElement' => $id, 
                    'elementRendered' => $elementRendered,
                   'buildErrors' => $element->getErrors()));
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding an element\'s option</strong> .', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('No id given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }    
    }
    
    public function removeMultioptionAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $idElement = $this->getRequest()->getPost('id');
            if (!empty($idElement))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                $idOption = $this->getRequest()->getPost('idOption');
                try
                {
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $xmlMng->removeMultiOption($idElement, $idOption);
                    
                    // Get the rendered element
                    $element = new Application_Model_FormElement();
                    $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                    
                    echo Zend_Json::encode(array('idOption' => $idOption, 
                    'idElement' => $idElement, 
                    'elementRendered' => $elementRendered,
                                           'buildErrors' => $element->getErrors()));
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>removing an element\'s option</strong> .', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('No id given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }    
    }    
    
    
    public function updateMultioptionAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $idElement = $this->getRequest()->getPost('idElement');
            if (!empty($idElement))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                $idOption = $this->getRequest()->getPost('idOption');
                $propertyName = $this->getRequest()->getPost('propertyName');
                $propertyValue = $this->getRequest()->getPost('propertyValue');
                if (!empty($idOption) && !empty($propertyName) && !empty($propertyValue))
                {
                    try
                    {
                        $xmlMng = new Application_Model_XmlManager($formFilename);
                        $option = $xmlMng->updateMultiOptionValue($idElement, $idOption, $propertyName, $propertyValue);
                        
                        // Get the rendered element
                        $element = new Application_Model_FormElement();
                        $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                        
                        echo Zend_Json::encode(array('idOption' => $idOption, 
                                                        'idElement' => $idElement, 
                        								'propertyName' => $propertyName, 
                                                        'propertyValue' => $propertyValue, 
                                                        'elementRendered' => $elementRendered,
                        								'option' => $option,
                        								'buildErrors' => $element->getErrors()));
                    }
                    // Error while accessing the xml file
                    catch (Zend_Exception $e)
                    {
                        $this->_printJsonError('An error occured while <strong>updating an element\'s option</strong> .', $e->getMessage());
                    }
                }
                else 
                {                    
                    $this->_printJsonError('Elements options id, propertyName or propertyValue empty !');
                }
            }
            else 
            {
                $this->_printJsonError('No id given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }    
    }    
    
    /************************************************************
    *                                                           *
    *              Decorators management functions              *    
    *                                                           *
    *************************************************************/
    public function addDecoratorAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $idElement = $this->getRequest()->getPost('idElement');
            $decClass = $this->getRequest()->getPost('decClass');
            $decOptions = $this->getRequest()->getPost('decOptions');
            if (!empty($idElement) && !empty($decClass))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                
                try
                { 
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $decorator = $xmlMng->addDecorator($idElement, $decClass, $decOptions);
                    
                    
                    if ($decorator !== false)
                    {                        
                        // Get the rendered element
                        $element = new Application_Model_FormElement();
                        $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                        
                        echo Zend_Json::encode(array('decId' => (string)$decorator->id, 
                                                    'decClass' => (string)$decorator->name, 
                                                    'decOptions' => (string)$decorator->options,
                                                    'elementRendered' => $elementRendered,
                                                    'idElement' => $idElement,
                        							'buildErrors' => $element->getErrors()));
                    }
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding a decorator</strong> .', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('Element id or decorator classname missing.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }    
    }
    
    public function updateDecoratorAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $idElement = $this->getRequest()->getPost('idElement');
            $decClass = $this->getRequest()->getPost('decClass');
            $decOptions = $this->getRequest()->getPost('decOptions');
            $decId = $this->getRequest()->getPost('decId');
            
            if (!empty($idElement) && !empty($decId) && !empty($decClass))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                
                try
                { 
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $decorator = $xmlMng->updateDecorator($idElement, $decId, $decClass, $decOptions);
                    
                    if ($decorator !== false)
                    {
                                                
                        // Get the rendered element
                        $element = new Application_Model_FormElement();
                        $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                        
                        echo Zend_Json::encode(array('decId' => (string)$decorator->id, 
                                                    'decClass' => (string)$decorator->name, 
                                                    'decOptions' => (string)$decorator->options,
                        							'elementRendered' => $elementRendered,
                                                    'idElement' => $idElement,
                                           			'buildErrors' => $element->getErrors()));
                    }
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding a decorator</strong> .', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('Element id, decorator id or decorator classname missing.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }    
    }    
    
    public function deleteDecoratorAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            // Get the type given in post
            $idElement = $this->getRequest()->getPost('idElement');
            $decId = $this->getRequest()->getPost('decId');
            
            if (!empty($idElement) && !empty($decId))
            {
                $formFilename = $this->getRequest()->getPost('formFilename');
                
                try
                { 
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $decoratorDatas = $xmlMng->deleteDecorator($idElement, $decId);
                    
                    if ($decoratorDatas !== false)
                    {
                        $resultArray = $decoratorDatas;
                        
                        // Get the rendered element
                        $element = new Application_Model_FormElement();
                        $elementRendered = $this->_getRenderedElement($element, $xmlMng->getXmlElement());
                        $resultArray['elementRendered'] = $elementRendered;
                        $resultArray['idElement'] = $idElement;
                        $resultArray['buildErrors'] = $element->getErrors();
                        
                        echo Zend_Json::encode($resultArray);
                    }
                }
                // Error while accessing the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding a decorator</strong> .', $e->getMessage());
                }
            }
            else 
            {
                $this->_printJsonError('Element id, decorator id or decorator classname missing.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }    
    }
    /************************************************************
    *                                                           *
    *                Form management functions                  *    
    *                                                           *
    *************************************************************/
    
    public function loadElementsAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            // Create the new element
            try
            {
                $xmlMng = new Application_Model_XmlManager($formFilename);
                $elements = $xmlMng->loadAllElements();
                $attribs = $xmlMng->getAttribs();
                
                $formElementManager = new Application_Model_FormElementManager();                
                $renderedElements = $formElementManager->loadForm($elements, $this->view);
                $buildErrors = $formElementManager->getBuildErrors();
                
                // Return a json string
                $json = Zend_Json::encode(array('elements' => $renderedElements, 
                								'buildErrors' => $buildErrors));
 
                if (!empty($attribs))
                {
                    // Erase last }
                    $json[strlen($json)-1] = ' ';
                    
                    // add the attribs field in the json string
                    $json .= ', "attribs": ' . $attribs . '}';
                }
                
                echo $json;
            }
            // Error while modifying the xml file
            catch (Zend_Exception $e)
            {
                $this->_printJsonError('An error occured while <strong>loading</strong> the form elements', $e->getMessage());
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }
    
    public function addEditFormAttributeAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $name = $this->getRequest()->getPost('name');
            $value = $this->getRequest()->getPost('value');
            
            if (!empty($name))
            {
                // Create the new element
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->addEditFormAttribute($name, $value);

                    
                    if ($result)
                        echo Zend_Json::encode(array('msg' => 'Attribute ' . $name . ' added/updated'));
                    else
                        echo Zend_Json::encode(array('error' => 'Unable to add/update the attribute ' . $name . ' to the form'));
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>adding or editing</strong> a form\'s attrbiute', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Name empty !');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }

    public function deleteFormAttributeAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
            $name = $this->getRequest()->getPost('name');
            
            if (!empty($name))
            {
                // Create the new element
                try
                {                    
                    $xmlMng = new Application_Model_XmlManager($formFilename);
                    $result = $xmlMng->deleteFormAttribute($name);

                    if ($result)
                        echo Zend_Json::encode(array('msg' => 'Attribute ' . $name . ' deleted'));
                    else
                        echo Zend_Json::encode(array('error' => 'Unable to delete the attribute ' . $name . ' from the form'));
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>deleting an attribute</strong> of the form', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('Id, name or value empty.');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }
      
    
    public function updateOrdersAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $orders_str = $this->getRequest()->getPost('orders');
            $formFilename = $this->getRequest()->getPost('formFilename');
            if (!empty($orders_str))
            {
                // Create the new element
                try
                {
                    $fmanager = new Application_Model_FormElementManager();
                    $fmanager->updateOrders($orders_str, $formFilename);
                    
                    echo Zend_Json::encode(array('msg' => 'Element\'s order  updated.'));
                }
                // Error while modifying the xml file
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('An error occured while <strong>ordering</strong> the form elements', $e->getMessage());
                }
            }
            else
            {                
                $this->_printJsonError('No new orders given');
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        }
    }    
    
    public function buildFormAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
   
            try 
            {
                $xmlMng = new Application_Model_XmlManager($formFilename);
                $elements = $xmlMng->loadAllElements();
                
                try 
                {
                    $formElementManager = new Application_Model_FormElementManager();
                    $result = $formElementManager->buildForm($elements, $xmlMng->getSxe(), $formFilename);
                    
                    if ($result !== false)
                    {
                        echo Zend_Json::encode(array('filename' => $result));
                    }
                }                
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('Error while building the Zend Form.', $e->getMessage());
                }                                
            }
            catch (Zend_Exception $e)
            {
                $this->_printJsonError('Error while accessing xml in the build process', $e->getMessage());
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    public function previewFormAction()
    {
        // Get the post
        if ($this->getRequest()->isPost())
        {
            $formFilename = $this->getRequest()->getPost('formFilename');
   
            try 
            {
                $xmlMng = new Application_Model_XmlManager($formFilename);
                $elements = $xmlMng->loadAllElements();
                
                try
                {
                    $formElementManager = new Application_Model_FormElementManager();
                    $formRendered = $formElementManager->previewForm($elements, $xmlMng->getSxe(), $formFilename);
                    if ($formRendered !== false)
                    {
                        echo $formRendered;
                    }
                }                
                catch (Zend_Exception $e)
                {
                    $this->_printJsonError('Error while building the preview of the Zend Form.', $e->getMessage());
                }                                
            }
            catch (Zend_Exception $e)
            {
                $this->_printJsonError('Error while accessing xml in the build preview process', $e->getMessage());
            }
        }
        else
        {
            $this->_printJsonError('No post sent');
        } 
    }
    
    
    
    
    
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $xml
     */
    private function _getRenderedElement($element, $xml)
    {
        try 
        {
            // Make php code from the xml file and evaluate it to create an element
            $element->createFromXml($xml, false);
            
            // Get the element rendered
            $elementRender = $element->getRenderedView($this->view);
        }           
        catch(Zend_Exception $e)
        {
           $this->_printJsonError('An error occured while <strong>rendering</strong> the element', $e->getMessage());
        }
        
        return $elementRender;
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $msg
     */
    private function _printJsonError($source, $errorMsg = '')
    {
        if (!empty($errorMsg))
            $error = $source . PHP_EOL . '<br/>The error was : <br/>' . PHP_EOL . $errorMsg;
        else 
           $error = $source;
            
        echo $this->_helper->json(array('error' => $error));
    }

}