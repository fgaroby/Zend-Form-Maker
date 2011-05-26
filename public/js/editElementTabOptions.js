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
$(document).ready(function()
{
    (function($)
    {
        var debug37 = true;

        $.TabOptions = 
        {
                init: function(data)
                {
                    var type = data["@attributes"].type;
                    $.TabOptions.initTabForm(type);

                    $.TabOptions.initComponents(type, data);
                },
                
                initTabForm: function(type)
                {
                    switch(type)
                    {
                        case "captcha": 
                        case "image":
                        case "file":
                        case "hash":
                            $.ajaxCalls.getTabOptionsForm({type: type});
                            break;
                            
                        
                        case "select":
                        case "radio":
                        case "multiselect":
                        case "multicheckbox":
                            break;
                            
                        default:
                            $("#specialsArea").html("There is no special option for this element.");
                            break;
                    }
                },
                
                initTabFormCallback: function(data)
                {
                    $("#specialsArea").html(data.form);
                },
                
                initComponents: function(type, data)
                {
                    switch(type)
                    {
                        case "captcha": 

                            var captchaImageArea = $("#fieldset-captchaImage");
                            var recaptchaArea = $("#fieldset-recaptcha");
                                                       
                            // Init the elements features
                            $.TabOptions.setElement("#optionsArea #captchaWordLen", "wordLen", data);
                            $.TabOptions.setElement("#optionsArea #captchaTimeout", "timeout", data);
                            $.TabOptions.setElement("#optionsArea #captchaUseNumbers", "useNumbers", data);
                            $.TabOptions.setElement("#optionsArea #captchaSessionClass", "SessionClass", data);
                            $.TabOptions.setElement("#optionsArea #captchaSession", "session", data);
                            $.TabOptions.setElement("#optionsArea #captchaAdapter", "adapter", data);
                            
                            // Images elements
                            $.TabOptions.setElement("#optionsArea #captchaExpiration", "expiration", data);
                            $.TabOptions.setElement("#optionsArea #captchaGcFreq", "gcFreq", data);
                            $.TabOptions.setElement("#optionsArea #captchaFont", "font", data);
                            $.TabOptions.setElement("#optionsArea #captchaFontSize", "fontSize", data);
                            $.TabOptions.setElement("#optionsArea #captchaHeight", "height", data);
                            $.TabOptions.setElement("#optionsArea #captchaWidth", "width", data);
                            $.TabOptions.setElement("#optionsArea #captchaImgDir", "imgDir", data);
                            $.TabOptions.setElement("#optionsArea #captchaImgUrl", "imgUrl", data);
                            $.TabOptions.setElement("#optionsArea #captchaSuffix", "suffix", data);
                            $.TabOptions.setElement("#optionsArea #captchaDotNoiseLevel", "dotNoiseLevel", data);
                            $.TabOptions.setElement("#optionsArea #captchaLineNoiseLevel", "lineNoiseLevel", data);
                            
                            // ReCaptcha elements
                            $.TabOptions.setElement("#optionsArea #captchaPubKey", "pubKey", data);
                            $.TabOptions.setElement("#optionsArea #captchaPrivKey", "privKey", data);
                            $.TabOptions.setElement("#optionsArea #captchaService", "service", data);
                            
                            
                            
                            // Show / Hide elements specific to some kind of captcha
                            var adapterField = $("#optionsArea #captchaAdapter");
                            adapterField.change(function(event)
                            {
                                captchaImageArea.hide();
                                recaptchaArea.hide();
                                
                                if ($(this).val() == "Image")
                                    captchaImageArea.show();
                                
                                if ($(this).val() == "ReCaptcha")
                                    recaptchaArea.show();
                            });
                            
                            if (adapterField.val() == "Image")
                                captchaImageArea.show();
                            else
                                captchaImageArea.hide();
                            
                            if (adapterField.val() == "ReCaptcha")
                                recaptchaArea.show();
                            else
                                recaptchaArea.hide();

                            $(".optionHelp").tooltip();
                            $("#multioptions").hide();
                            break;
                            
                        case "image":
                            $.TabOptions.setElement("#optionsArea #imgPath", "src", data);
                            $.TabOptions.setElement("#optionsArea #imgValue", "imgValue", data);
                            $("#multioptions").hide();
                            break;
                            
                        case "file":
                            $.TabOptions.setElement("#optionsArea #fileDest", "destination", data);
                            $.TabOptions.setElement("#optionsArea #fileValueDisabled", "valueDisabled", data);
                            $.TabOptions.setElement("#optionsArea #fileMulti", "multiFile", data);
                            $("#multioptions").hide();
                            break;
                            
                        case "hash":
                            $.TabOptions.setElement("#optionsArea #hashSalt", "salt", data);
                            $.TabOptions.setElement("#optionsArea #hashTimeOut", "timeout", data);
                            $.TabOptions.setElement("#optionsArea #hashSession", "session", data);
                            $("#multioptions").hide();
                            break;
                            
                        
                        case "select":
                        case "radio":
                        case "multiselect":
                        case "multicheckbox":
                            $("#specialsArea").html('');
                            $("#multioptions").show();
                            $.TabOptions.initMultiOptionsComponents(data);
                            break;
                            
                            
                       default : 
                           $("#multioptions").hide();
                           break;
                       
                    }
                },
                /**
                 * Set an element's parameters, onchange, etc
                 * @param selector : The element 
                 * @param propertyName : The name of the property to update
                 * @param data . The datas about the element
                 */
                setElement : function(selector, propertyName, data)
                {                    
                    // Get the id of the element
                    var elementId = data.id;
                    
                    // Get the xml name
                    var formFilename = $.ElementManager.getFormFilename();
                    
                    // If the property isn't defined, juste keep it empty
                    var propertyValue = (typeof data.spec != 'undefined' && typeof data.spec[propertyName] != 'undefined') ? data.spec[propertyName] : "";
                    
                    // An empty object isn't allowed to be print, so replace it
                    if (propertyValue.length == null)
                    {
                        propertyValue = "";
                    }
                    
                    // Check and uncheck the checkboxes
                    if ($(selector).attr("type") == "checkbox")
                    {
                        $(selector).attr("checked", (propertyValue != "false"));
                    }
                    // Or fullfill text fields
                    else
                    {
                        $(selector).val(propertyValue);   
                    }
                    
                    
                    // Function to save a modified data
                    var bindFunction = function(event)
                    {       
                        if ($(this).attr("type") == "checkbox")
                        {
                            var newValue = $(this).is(":checked");
                        }
                        else
                        {
                            var newValue = $(this).val();                    
                        }
                        

                        var param =
                        {
                            id : elementId,
                            newValue : newValue,
                            property: propertyName,
                            formFilename: formFilename
                        };
                        
                        // Update this element's description
                        $(selector).parent().parent().children(".description").html($.ZFM.images.filedUpdateInProgress);
                        
                        // Call ajax request to modify what has to be modified
                        $.ajaxCalls.updateElementOptionValue(param, $(selector));
                    };

                    // Remove all bind
                    $(selector).unbind();

                    // Bind the onchange event with my update function
                    $(selector).bind("change", bindFunction);
                },

                updateElementValueCallback : function(idElement, elementRendered, el)
                {
                    // Set the image, field is up to date
                    el.parent().parent().children(".description").html($.ZFM.images.fieldUpToDate);
                    
                    // Refresh the element on the interface 
                    $.ElUpdateManager.refreshElement(idElement, elementRendered);
                },
                
                
                
                
                
                
                /*****************************
                 * Multi options management
                 *****************************/
                initMultiOptionsComponents: function(data)
                {
                    // Save the field when the value is changed
                    $("input#multioptionsText").unbind().change(function(event)
                    {
                        $.TabOptions.updateOptionValue("text", $(this));
                    })
                    
                    // Save on update error msg
                    $("input#multioptionsValue").unbind().change(function(event)
                    {
                        $.TabOptions.updateOptionValue("value", $(this));
                    });
                    
                    // Save on update error msg
                    $("input#multioptionsChecked").unbind().change(function(event)
                    {
                        $.TabOptions.updateOptionValue("checked", $(this));
                    });
                    
                    // Connect the add button to addSelectedValidator
                    $("#addOptionButton").unbind().click(function(event)
                    {
                        $.TabOptions.addSelectedOption();
                    });

                    // Connect the delete button to removeSelectedValidator
                    $("#deleteOptionButton").unbind().click(function(event)
                    {
                        $.TabOptions.removeSelectedOption();
                    });    

                    $("input#addOption").keyup(function(event)
                    {
                        if (event.keyCode == "13")
                            $.TabOptions.addSelectedOption();
                    });
                       
                    $.TabOptions.fillSelectedOptions(data.multioptions);
                },
                
                fillSelectedOptions: function(options)
                {
                    // No options for this one
                    if (options != null && (typeof options.option != "undefined"))
                    {
                        /*
                         * Strangley, when an object has only a child, it's not 
                         * an array but directly the property so let's cheat a bit
                         * and put it into an array for the loop 
                         */
                        if (options.option.length > 1)
                        {
                            options = options.option;
                        }
                        else
                            options[0] = options.option;
                        
                        // Fulfill the list of item selected for this element...
                        for (var i in options)
                        {
                            $.TabOptions.addSelectedOptionCallback({option: options[i]});
                        }
                    }
                },
                
                
                fillTab : function(datas)
                {   
                    // Fill the field with the datas
                    $("#optionsArea #multioptionsId").val(datas.id);
                    $("#optionsArea #multioptionsText").val(datas.text);
                    $("#optionsArea #multioptionsValue").val(datas.value);
                   
                    $("#optionsArea #multioptionsChecked").prop("checked", (datas.checked != "false"));               
                },
                
                addSelectedOption: function ()
                {
                    var param = 
                    {
                        formFilename: $.ElementManager.getFormFilename(),
                        id: $.TabGeneral.getElementId(),
                        text: $("input#addOption").val()
                    };
                    
                    if (jQuery.trim(param.text) == "")
                        $.Tools.alert("Empty", "Please fill the field.");
                    
                    // Is it already in the list ?
                    if ($($.ZFM.selectors.optionSelected + " :contains('" +  param.text + "')").length == 0)
                    {
                        $.ajaxCalls.addMultiOption(param);
                    }
                },
                
                addSelectedOptionCallback: function (datas)
                {
                    datas.option.id = datas.option["@attributes"].id;
                    
                    var firstChild = ($($.ZFM.selectors.optionSelected + ":has(option)").length == 0);
                    
                    // It's the first option for this element, enable the tab
                    if (firstChild)
                        $.TabOptions.enableTab();
                    
                    $("input#addOption").val("");
                    
                    // Add the option to the list of options
                    $($.ZFM.selectors.optionSelected).addOption(datas.option.id, datas.option.text, false); // 3rd argument is select or not the line added
                    
                    var lastOption =  $($.ZFM.selectors.optionSelected + " option:last");
                    
                    // Set the click of this option
                    lastOption.click(function(event)
                    {
                        $.TabOptions.fillTab(datas.option);
                    });
                    
                    // First element added, select it and click it!
                    if (firstChild)
                        lastOption.attr("selected", true).focus().click();

                    $.ElUpdateManager.refreshElement(datas.idElement, datas.elementRendered);
                },
                
                
                removeSelectedOption: function ()
                {
                    // Be sure that something is selected before to delete it !
                    if ($($.ZFM.selectors.optionSelected).selectedOptions().length == 1)
                    {
                        var param = 
                        {
                            formFilename: $.ElementManager.getFormFilename(),
                            id: $.TabGeneral.getElementId(),
                            idOption: $.TabOptions.getOptionId()
                        };
                        
                        $.ajaxCalls.removeMultiOption(param);
                    }
                },
                removeSelectedOptionCallback: function (datas)
                {
                    $($.ZFM.selectors.optionSelected + " option:selected").remove();

                    // Disable interface if there is no more options
                    if ($($.ZFM.selectors.optionSelected + ":has(option)").length == 0)
                    {
                        $.TabOptions.disableTab();
                    }
                    // Select last element in the list
                    else
                    {
                        $($.ZFM.selectors.optionSelected + " option:last")
                        .attr("selected", true)
                        .focus()
                        .click();    
                    }
                    

                    $.ElUpdateManager.refreshElement(datas.idElement, datas.elementRendered);
                },
                
                updateOptionValue : function (propertyName, element)
                {
                    if (element.attr("type") == "checkbox")
                    {
                        var newValue = element.is(":checked");
                    }
                    else
                    {
                        var newValue = element.val();                    
                    }
                    
                    
                    var param = 
                    {
                        formFilename: $.ElementManager.getFormFilename(),
                        
                        idElement: $.TabGeneral.getElementId(),
                        idOption : $.TabOptions.getOptionId(),
                        
                        propertyName: propertyName,
                        propertyValue: newValue
                    };
                                    

                    // Update this element's description
                    element.parent().parent().children(".description").html($.ZFM.images.filedUpdateInProgress);
                    $.ajaxCalls.updateOptionValue(param, element);
                },
                updateOptionValueCallback : function (datas, el)
                {
                    // Set the image, field is up to date
                    el.parent().parent().children(".description").html($.ZFM.images.fieldUpToDate);   
                    
                    // Select the option we just updated
                    var updatedOption = $($.ZFM.selectors.optionSelected + " option[value='" + datas.idOption + "']");
                    
                    // Update the option of the selected list when we edit the text
                    if (datas.propertyName == "text")
                        updatedOption.text(datas.propertyValue);
                    
                    //                     
                    datas.option.id = datas.idOption;
                    updatedOption.unbind().click(function(event)
                    {
                        $.TabOptions.fillTab(datas.option);
                    });
                    
                    $.ElUpdateManager.refreshElement(datas.idElement, datas.elementRendered);
                },
                
                getOptionId: function()
                {
                    return $("#multioptionsId").val();
                },
                
                
                
                
                
                disableTab: function()
                {
                    // Clean the option list
                    $($.ZFM.selectors.optionSelected).html("");
                    $("#optionsArea #multioptionsText").val("");
                    $("#optionsArea #multioptionsValue").val("");
                    $("#optionsArea #multioptionsChecked").attr("checked", false);
                    
                    // Disable the components
                    $("#optionsArea #multioptionsText").attr("disabled", true);
                    $("#optionsArea #multioptionsValue").attr("disabled", true);
                    $("#optionsArea #multioptionsChecked").attr("disabled", true);     
                },
                
                enableTab: function()
                {
                    $("#multioptionsText").removeAttr("disabled");
                    $("#multioptionsValue").removeAttr("disabled");
                    $("#multioptionsChecked").removeAttr("disabled");
                }
                
        };
    })(jQuery);
});