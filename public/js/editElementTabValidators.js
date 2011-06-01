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
        
        $.TabValidators = 
        {
            init : function(data)
            {
                // Freeze the tab until there is a validator selected
                $.TabValidators.disableTab();
                

                var elementType = data["@attributes"].type;                
                $.ajaxCalls.getValidatorsFileInput({}, elementType);
                
                // Put the validators in the list of selected validators
                var selectedValidators = (typeof data.validators != "undefined") ? data.validators : null;                                
                $.TabValidators.fillSelectedValidators(selectedValidators);
                
                // Init the component change functions, click, autocomplete,...
                $.TabValidators.initComponents();
            },
            
            initComponents : function()
            {   
                $(".validatorsHelpDialog").dialog(
                { 
                    autoOpen: false,
                    width: 400,
                    height: 400
                });
                
                $("#dialogForm #validatorsArea #config .validatorHelper img").click(function()
                {
                    $(".validatorsHelpDialog").dialog("open");
                });
                
                // Save on update error msg
                $("input#validatorMsgError").unbind().change(function(event)
                {
                    $.TabValidators.updateValidatorValue("errorMessage", $(this));
                });
                
                // Save on update error msg
                $("input#validatorBCOF").unbind().change(function(event)
                {
                    $.TabValidators.updateValidatorValue("bcof", $(this));
                });
                
                // Connect the add button to addSelectedValidator
                $("#addValidatorButton").unbind().click(function(event)
                {
                    $.TabValidators.addSelectedValidator();
                });

                // Connect the delete button to removeSelectedValidator
                $("#deleteValidatorButton").unbind().click(function(event)
                {
                    $.TabValidators.removeSelectedValidator();
                });
                
                // Autocomplete on the constructor field
                $("#validatorConstruct").unbind().autocomplete({
                    minLength: 0,
                    autoFocus: true,
                    source: [],
                    change: function (event, ui)
                    {
                        $.TabValidators.updateValidatorValue("constructor", $(this));
                    }
                })
                .keyup(function(event) // Leave the field on enter key pressed to simulate the save on enter
                {
                    if (event.keyCode == '13')
                    {
                        event.preventDefault();
                        $("#validatorConstruct").blur();
                        return null;
                    }
                });
            },

            fillHelpDialog : function(data)
            {
                if (typeof data.help == "undefined")
                {
                    $(".validatorsHelpDialog").html("There is no specific help for this validaror.");
                    return false;
                }
                
                // Only one option, build an array with it so the loop will work
                if ((data.help.options instanceof Array) == false)
                {
                    var helpDatas = new Array();
                    helpDatas.push(data.help.options);
                }
                else
                {
                    var helpDatas = data.help.options;
                }
                
                var helpString = "";
                for(var i = 0; i < helpDatas.length; i++)
                {
                    helpString += "<fieldset>"
                        + "<legend>" + helpDatas[i]["@attributes"]["for"] + "</legend>";
                        
                    for (var j = 0; j < helpDatas[i].option.length; j++)
                    {
                        helpString += helpDatas[i].option[j] + "<br/>";
                    }
                    
                    helpString += "</fieldset>";
                }
                
                $(".validatorsHelpDialog").html(helpString);
                return true;            
            },
            
            enableDisableFileValidators : function(data, elementType)
            {
                for (var i in data)
                {                                                
                    $("select#addValidator option:contains(" + data[i] + ")").prop("disabled", (elementType != "file"));
                }
            },
                        
            fillSelectedValidators: function(validators)
            {
                // No validators for this one
                if (validators != null && (typeof validators.validator != "undefined"))
                {
                    /*
                     * Strangley, when an object has only a child, it's not 
                     * an array but directly the property so let's cheat a bit
                     * and put it into an array for the loop 
                     */
                    if (validators.validator.length > 1)
                    {
                        validators = validators.validator;
                    }
                    else
                        validators[0] = validators.validator;
                    
                    // Fulfill the list of item selected for this element...
                    for (var i in validators)
                    {
                        $.TabValidators.addSelectedValidatorCallback(validators[i]);
                    }
                }
            },
            
            fillTab: function (datas)
            {
                var param=
                {
                    name: datas.name,
                    knownDatas : datas  
                };
                
                // Don't fulfill the tab if there is no name found ... a bug happened somehow
                if (typeof param.name != "undefined")
                    $.ajaxCalls.getValidatorDatas(param);
                
                else
                    console.log("fillTab called without a classname ... there is probably no validator selected or something wierder.");
                
            },
            
            fillTabCallback : function(data, knownDatas)
            {               
                // Run the datas given and erase empty objects for display purposes
                for(var i in knownDatas)
                {
                    if (knownDatas[i].length == null || typeof knownDatas[i] == "undefined")
                        knownDatas[i] = "";
                }
                
                // Fill the help dialog form
                $.TabValidators.fillHelpDialog(data);
                
                // Fill the field with the datas
                $("#validatorsArea #description #className")
                .prop("href", data.link)
                .text(knownDatas.name);
                
                $("#validatorsArea #validatorConstruct").val(knownDatas.constructor);
                $("#validatorsArea #validatorMsgError").val(knownDatas.errorMessage);
                $("#validatorsArea #validatorBCOF").prop("checked", (knownDatas.bcof != "false"));
                
                // Fill the fields with new datas coming from the ajax call on the validators xml file
                $("#validatorsArea #description #classDescription").text(data.desc); 

                // If there is no codes, disable autocomplete
                if (typeof data.codes == "undefined" || typeof(data.codes.code) == "undefined")
                {                    
                    $("#validatorsArea #validatorConstruct").autocomplete("option", "source", []);
                }
                // Set the codes options as the source
                else
                {
                    // If it's an array, we've more than 1 suggestion
                    if (data.codes.code instanceof Array)
                    {
                        var codes = data.codes.code;
                    }
                    // Else we've got only 1 suggestion, so make an array with it
                    else
                    {
                        var codes = [];
                        codes[0] = data.codes.code;
                    }
                   $("#validatorsArea #validatorConstruct").autocomplete("option", "source", codes);
                }
                
            },
            
            addSelectedValidator: function ()
            {
                var param = 
                {
                    formFilename: $.ElementManager.getFormFilename(),
                    id: $.TabGeneral.getElementId(),
                    name: $("select#addValidator").val()
                };
                
                // Is it already in the list ?
                if (!$($.ZFM.selectors.validatorSelected).containsOption(param.name))
                {
                    $.ajaxCalls.addValidator(param);
                }
            },
            
            addSelectedValidatorCallback: function (datas)
            {
                var firstChild = ($($.ZFM.selectors.validatorSelected + ":has(option)").length == 0);
                
                // It's the first validator for this element, enable the tab
                if (firstChild)
                    $.TabValidators.enableTab();
                
                $("select#addValidator option:contains(" + datas.name + ")").prop("disabled", true);
                
                // Add the validator to the list of options
                $($.ZFM.selectors.validatorSelected).addOption(datas.name, datas.name, false); // 3rd argument is select or not the line added
                
                var lastOption =  $($.ZFM.selectors.validatorSelected + " option:last");
                
                // Set the click of this option
                lastOption.click(function(event)
                {
                    // Remove the selected attribute on all the element
                    $.TabValidators.fillTab(datas);
                });
                
                // First element added, select it and click it!
                if (firstChild)
                {
                    lastOption.prop("selected", true).click();
                }                 
            },
            
            
            removeSelectedValidator: function ()
            {
                // Be sure that something is selected before to delete it !
                if ($($.ZFM.selectors.validatorSelected).selectedOptions().length == 1)
                {
                    var param = 
                    {
                        formFilename: $.ElementManager.getFormFilename(),
                        id: $.TabGeneral.getElementId(),
                        name: $($.ZFM.selectors.validatorSelected).val()
                    };
                    
                    $.ajaxCalls.removeValidator(param);
                }
            },
            removeSelectedValidatorCallback: function (data)
            {
                $($.ZFM.selectors.validatorSelected).removeOption(data.name);

                $("select#addValidator option:contains(" + data.name + ")").prop("disabled", false);
                
                // Disable interface if there is no more validators
                if ($($.ZFM.selectors.validatorSelected + ":has(option)").length == 0)
                {
                    $.TabValidators.disableTab();
                }
                // Select last element in the list
                else
                {
                    $($.ZFM.selectors.validatorSelected + " option:last")
                    .prop("selected", true)
                    .focus()
                    .click();    
                }
            },
            
            updateValidatorValue : function (propertyName, element)
            {
                if (element.prop("type") == "checkbox")
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
                    
                    id: $.TabGeneral.getElementId(),
                    name : $.TabValidators.getClassName(),
                    
                    propertyName: propertyName,
                    propertyValue: newValue
                };
                                

                // Update this element's description
                element.parent().parent().children(".description").html($.ZFM.images.filedUpdateInProgress);
                $.ajaxCalls.updateValidatorValue(param, element);
            },
            updateValidatorValueCallback : function (el)
            {
                // Set the image, field is up to date
                el.parent().parent().children(".description").html($.ZFM.images.fieldUpToDate);                
            },
            
            
            
            
            
            
            
            disableTab: function()
            {
                // Clean the validator list
                $($.ZFM.selectors.validatorSelected).html("");
                $("select#addValidator option:not(:contains('Zend_Validate_File'))").removeAttr("disabled");
                
                // Fullfill the tab with empty values
                $("#validatorsArea #description #className").text("Zend_Validate_Class");
                $("#validatorsArea #description #classDescription").text("Please select a validator");
                
                $("#validatorsArea #validatorConstruct").val("");
                $("#validatorsArea #validatorMsgError").val("");
                $("#validatorsArea #validatorBCOF").prop("checked", false);
                
                // Disable the components
                $("#validatorsArea #validatorConstruct").prop("disabled", true);
                $("#validatorsArea #validatorMsgError").prop("disabled", true);
                $("#validatorsArea #validatorBCOF").prop("disabled", true);     
            },
            
            enableTab: function()
            {
                $("#validatorsArea #validatorConstruct").prop("disabled", false);
                $("#validatorsArea #validatorMsgError").prop("disabled", false);
                $("#validatorsArea #validatorBCOF").prop("disabled", false);     
            },
            
            getClassName: function()
            {
                return $($.ZFM.selectors.validatorSelected).selectedValues()[0];
            }
        };
    })(jQuery);
});