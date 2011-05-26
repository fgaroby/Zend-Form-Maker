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
class Application_Form_TabOptions extends Zend_Form
{
    private $_txtDecorator;
    private $_selectDecorator;
    private $_imgDone;
    
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
       	$this->_txtDecorator = array(
						array('ViewScript', 
							  array('viewScript' => '/decorators/txt_element.phtml', 
							  		'class' => 'tabOptionsFormElement'
							  	   )
							  )
					    );
       	$this->_selectDecorator = array(
						array('ViewScript', 
							  array('viewScript' => '/decorators/select_element.phtml', 
							  		'class' => 'tabOptionsFormElement'
							  	   )
							  )
					    );
					    
		$this->_imgDone = '<img src="/images/zfm/updateDone.png" alt="The field is up to date" title="The field is up to date" />';		

		
		
		
        $this->addAttribs(array('onsubmit' => 'return false;'));
		
		// Set the dubforms params, decorators etc
		$sfCaptcha = new Zend_Form_SubForm('Captcha');
		$sfCaptcha->setLegend('Captcha Options');
		$sfCaptcha->addElements($this->_getCaptchaElements());
		$sfCaptcha->addDecorator('FormElements');
        $sfCaptcha->removeDecorator('DtDdWrapper');
		
		
		$sfCaptchaImage = new Zend_Form_SubForm('Image options');
		$sfCaptchaImage->setLegend('Image captcha Options');
		$sfCaptchaImage->addElements($this->_getCaptchaImageElements());
		$sfCaptchaImage->addDecorator('FormElements');
        $sfCaptchaImage->removeDecorator('DtDdWrapper');
		
		$sfReCaptcha = new Zend_Form_SubForm('Re-Captcha options');
		$sfReCaptcha->setLegend('Re-Captcha Options');
		$sfReCaptcha->addElements($this->_getReCaptchaElements());
		$sfReCaptcha->addDecorator('FormElements');
        $sfReCaptcha->removeDecorator('DtDdWrapper');
		
		
		
		$sfImage = new Zend_Form_SubForm();
		$sfImage->removeDecorator('DtDdWrapper');
		$sfImage->setLegend('Image submit Options');
		$sfImage->addElements($this->_getImageElements());
		$this->addSubForm($sfImage,'image');
		
		$sfFile = new Zend_Form_SubForm();
		$sfFile->removeDecorator('DtDdWrapper');
		$sfFile->setLegend('File Options');
		$sfFile->addElements($this->_getFileElements());
		
		$sfHash = new Zend_Form_SubForm();
		$sfHash->removeDecorator('DtDdWrapper');
		$sfHash->setLegend('Hash Options');
		$sfHash->addElements($this->_getHashElements());
		$this->addSubForm($sfHash,'hash');	
		
		
		$sfOptions = new Zend_Form_SubForm();
		$sfOptions->removeDecorator('DtDdWrapper');
		$sfOptions->removeDecorator('Fieldset');
		$sfOptions->addElements($this->_getMultiOptionsElements());
		
		
		
		
		$this->addSubForm($sfCaptcha,'captcha');
		$this->addSubForm($sfCaptchaImage,'captchaImage');
		$this->addSubForm($sfReCaptcha,'recaptcha');
		$this->addSubForm($sfFile,'file');
		$this->addSubForm($sfOptions,'multiOptions');	
    }
    
    /********************************
     * File subforms
     ********************************/
    private function _getFileElements()
    {
        $elements = array();
        $elements[] = $this->_destination();
        $elements[] = $this->_valueDisabled();
        $elements[] = $this->_multiFile();
        return $elements;
    }

    private function _destination()
    {			    
        $element = new Zend_Form_Element_Text("fileDest");
        
        $element->setLabel('Destination path')
        ->setRequired(true)
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    private function _multiFile()
    {			    
        $element = new Zend_Form_Element_Text("fileMulti");
        
        $element->setLabel('Multifile')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    private function _valueDisabled()
    {			    
        $element = new Zend_Form_Element_Checkbox("fileValueDisabled");
        
        $element->setLabel('Value disabled')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    
    /********************************
     * Image subforms
     ********************************/
    private function _getImageElements()
    {
        $elements = array();
        $elements[] = $this->_imgSrc();
        $elements[] = $this->_imgValue();
        
        return $elements;
    }
    
    private function _imgSrc()
    {			    
        $element = new Zend_Form_Element_Text("imgPath");
        
        $element->setLabel('Image path')
        ->setRequired(true)
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    private function _imgValue()
    {			    
        $element = new Zend_Form_Element_Text("imgValue");
        
        $element->setLabel('Image value on submit')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    
    
    /********************************
     * Hash subforms
     ********************************/
    private function _getHashElements()
    {
        $elements = array();
        $elements[] = $this->_hashSalt();
        $elements[] = $this->_hashTimeOut();
        $elements[] = $this->_hashSession();
        
        return $elements;
    }
    
    private function _hashSalt()
    {			    
        $element = new Zend_Form_Element_Text("hashSalt");
        
        $element->setLabel('Salt')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    private function _hashTimeOut()
    {			    
        $element = new Zend_Form_Element_Text("hashTimeOut");
        
        $element->setLabel('Timeout')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    private function _hashSession()
    {			    
        $element = new Zend_Form_Element_Text("hashSession");
        
        $element->setLabel('Session namespace')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    
    
 
    
    /********************************
     * Captcha subforms
     ********************************/
    private function _getCaptchaElements()
    {
        $elements = array();
        $elements[] = $this->_captchaAdapter();
        $elements[] = $this->_captchaWordLen();
        $elements[] = $this->_captchaTimeout();
        $elements[] = $this->_captchaUseNumbers();
        $elements[] = $this->_captchaSessionClass();
        $elements[] = $this->_captchaSession();
        
        return $elements;
    }
    
    private function _captchaAdapter()
    {			    
        $element = new Zend_Form_Element_Select("captchaAdapter");
        
        $element->setLabel('Adapter')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_selectDecorator);
                
        $element->addMultiOption('Dumb', 'Dumb');
        $element->addMultiOption('Figlet', 'Figlet');
        $element->addMultiOption('Image', 'Image');
        $element->addMultiOption('ReCaptcha', 'ReCaptcha');
        
        return $element;
    }   
    
    private function _captchaWordLen()
    {			    
        $element = new Zend_Form_Element_Text("captchaWordLen");
        
        $element->setLabel('Word length')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the length of the generated "word" in characters.');
        
        return $element;
    }    
    private function _captchaTimeout()
    {			    
        $element = new Zend_Form_Element_Text("captchaTimeout");
        
        $element->setLabel('Timeout')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the time-to-live of the session token, should be specified in seconds.');
        
        return $element;
    }   
    private function _captchaUseNumbers()
    {			    
        $element = new Zend_Form_Element_Checkbox("captchaUseNumbers");
        
        $element->setLabel('Use numbers ?')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify if numbers will be considered as possible characters for the random work or only letters would be used. ');
        
        return $element;
    }   
    private function _captchaSessionClass()
    {			    
        $element = new Zend_Form_Element_Text("captchaSessionClass");
        
        $element->setLabel('Session\'s class')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify an alternate Zend_Session_Namespace implementation to use to persist the CAPTCHA token');
        
        return $element;
    }   
    private function _captchaSession()
    {			    
        $element = new Zend_Form_Element_Text("captchaSession");
        
        $element->setLabel('Session')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify a session object to use for persisting the CAPTCHA token.');
        
        return $element;
    }
    
    private function _getCaptchaImageElements()
    {
        $elements = array();
        $elements[] = $this->_captchaExpiration();
        $elements[] = $this->_captchaGcFreq();
        $elements[] = $this->_captchaFont();
        $elements[] = $this->_captchaFontSize();
        $elements[] = $this->_captchaHeight();
        $elements[] = $this->_captchaWidth();
        $elements[] = $this->_captchaImgDir();
        $elements[] = $this->_captchaImgUrl();
        $elements[] = $this->_captchaSuffix();
        $elements[] = $this->_captchaDotNoiseLevel();
        $elements[] = $this->_captchaLineNoiseLevel();
        
        return $elements;
    }
    
    private function _captchaExpiration()
    {			    
        $element = new Zend_Form_Element_Text("captchaExpiration");
        
        $element->setLabel('Expiration')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify a maximum lifetime the CAPTCHA image may reside on the filesystem.');
        
        return $element;
    }
    private function _captchaGcFreq()
    {			    
        $element = new Zend_Form_Element_Text("captchaGcFreq");
        
        $element->setLabel('Gc Freq.')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify how frequently garbage collection should run. Garbage collection will run every 1/gcFreq calls. The default is 100. ');
        
        return $element;
    }
    private function _captchaFont()
    {			    
        $element = new Zend_Form_Element_Select("captchaFont");
        
        $element->setLabel('Font')
        ->setRequired(true)
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_selectDecorator)
        ->setAttrib('title', 'Specify the font you will use. $font should be a fully qualified path to the font file. This value is required.');
        
        $dirFonts = Zend_Registry::get('config')->zfm->formElement->captcha->userFontsDirectory;
        
        if (is_dir($dirFonts))
        {
            $fonts = scandir($dirFonts);
            foreach($fonts as $font)
            {
                if (!in_array($font, array('.', '..')))
                    $element->addMultiOption($dirFonts . $font, $font);
            }
        }
        else
            die('Configuration error, key "zfm.formElement.captcha.userFontsDirectory" in application.ini isn\'t a directory');
        
        return $element;
    }
    private function _captchaFontSize()
    {			    
        $element = new Zend_Form_Element_Text("captchaFontSize");
        
        $element->setLabel('Font size')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the font size in pixels for generating the CAPTCHA. The default is 24px.');
        
        return $element;
    }
    private function _captchaHeight()
    {			    
        $element = new Zend_Form_Element_Text("captchaHeight");
        
        $element->setLabel('Height')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the height in pixels of the generated CAPTCHA image. The default is 50px.');
        
        return $element;
    }
    private function _captchaWidth()
    {			    
        $element = new Zend_Form_Element_Text("captchaWidth");
        
        $element->setLabel('Width')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the width in pixels of the generated CAPTCHA image. The default is 200px.');
        
        return $element;
    }
    private function _captchaImgDir()
    {			    
        $element = new Zend_Form_Element_Text("captchaImgDir");
        
        $element->setLabel('Image directory')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the directory for storing CAPTCHA images. The default is set in the application.ini file. The path is relative to the bootstrap script.');
        
        return $element;
    }
    private function _captchaImgUrl()
    {			    
        $element = new Zend_Form_Element_Text("captchaImgUrl");
        
        $element->setLabel('Image url')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the relative path to a CAPTCHA image to use for HTML markup. The default is "/images/captcha/".');
        
        return $element;
    }
    private function _captchaSuffix()
    {			    
        $element = new Zend_Form_Element_Text("captchaSuffix");
        
        $element->setLabel('Suffix')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the filename suffix for the CAPTCHA image. The default is ".png". Note: changing this value will not change the type of the generated image.');
        
        return $element;
    }
    private function _captchaDotNoiseLevel()
    {			    
        $element = new Zend_Form_Element_Text("captchaDotNoiseLevel");
        
        $element->setLabel('Dot noise level')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Control how much "noise" in the form of random dots the image would contain.');
        
        return $element;
    }
    private function _captchaLineNoiseLevel()
    {			    
        $element = new Zend_Form_Element_Text("captchaLineNoiseLevel");
        
        $element->setLabel('Line noise level')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Control how much "noise" in the form of random lines the image would contain.');
        
        return $element;
    }

    
    private function _getReCaptchaElements()
    {
        $elements = array();
        $elements[] = $this->_captchaPubKey();
        $elements[] = $this->_captchaPrivKey();
        $elements[] = $this->_captchaService();
        return $elements;
    }
    
    
    private function _captchaPubKey()
    {			    
        $element = new Zend_Form_Element_Text("captchaPubKey");
        
        $element->setLabel('Public key')
        ->setRequired(true)
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the public key to use for the ReCaptcha service');
        
        return $element;
    }
    private function _captchaPrivKey()
    {			    
        $element = new Zend_Form_Element_Text("captchaPrivKey");
        
        $element->setLabel('Private key')
        ->setRequired(true)
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Specify the private key to use for the ReCaptcha service');
        
        return $element;
    }
    
    private function _captchaService()
    {			    
        $element = new Zend_Form_Element_Text("captchaService");
        
        $element->setLabel('Service')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('title', 'Set the ReCaptcha service object');
        
        return $element;
    }
    
    private function _getMultiOptionsElements()
    {
        $elements = array();
        $elements[] = $this->_multiOptionsId();
        $elements[] = $this->_multiOptionsText();
        $elements[] = $this->_multiOptionsValue();
        $elements[] = $this->_multiOptionsChecked();
        return $elements;
    }
    

    private function _multiOptionsId()
    {			    
        $element = new Zend_Form_Element_Text("multioptionsId");
        
        $element->setLabel('Id')
        ->setDecorators($this->_txtDecorator)
        ->setAttrib('disabled', 'disabled');
        
        return $element;
    }
    private function _multiOptionsText()
    {			    
        $element = new Zend_Form_Element_Text("multioptionsText");
        
        $element->setLabel('Text')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    private function _multiOptionsValue()
    {			    
        $element = new Zend_Form_Element_Text("multioptionsValue");
        
        $element->setLabel('Value')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
    private function _multiOptionsChecked()
    {			    
        $element = new Zend_Form_Element_Checkbox("multioptionsChecked");
        
        $element->setLabel('Checked')
        ->setDescription($this->_imgDone)
        ->setDecorators($this->_txtDecorator);
        
        return $element;
    }
}

