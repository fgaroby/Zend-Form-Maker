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
        var emptyAttribMessage = "Please insert an attrib to see it here.";
        $.TabGeneral = 
        {
            /**
             * 
             * @param data
             */
            init: function(data)
            {
                // Initialize the components of this tab
                $.TabGeneral.initComponents();
                
                // Fulfill the form element and put a modify function on it
                $.TabGeneral.createModifyFunction("#elementId", "id", data);
                $.TabGeneral.createModifyFunction("#elementName", "name", data);
                $.TabGeneral.createModifyFunction("#elementOrder", "order", data);
                $.TabGeneral.createModifyFunction("#elementLabel", "label", data);
                $.TabGeneral.createModifyFunction("#elementDescription", "description", data);
                $.TabGeneral.createModifyFunction("#elementValue", "value", data);
                $.TabGeneral.createModifyFunction("#elementRequired", "required", data);
                $.TabGeneral.createModifyFunction("#elementAllowEmpty", "allowEmpty", data);

                // Load the element attributs
                $.TabGeneral.loadElementAttribs(data);
            },
            /**
             * 
             */
            initComponents : function()
            {
                // General tab, attribute add /edit button
                $("#generalArea #addAttributeButton").click(function(event)
                    {
                        $.TabGeneral.addAttributeButtonClick(event);
                    }
                );
                
                $("#generalArea #attribValue").keyup(function(event)
                {
                    if (event.keyCode == '13')
                    {                        
                        $.TabGeneral.addAttributeButtonClick(event);
                    }
                });
            
                $("#generalArea #attribName").keyup(function(event)
                {
                    if (event.keyCode == '13')
                    {                        
                        $.TabGeneral.addAttributeButtonClick(event);
                    }
                });
            },
            
            getElementId: function()
            {
                return $("#generalArea input:disabled#elementId").val();
            },
            
            /**
             * 
             * @param elementIdentifier
             * @param propertyName
             * @param data
             */
            createModifyFunction : function(elementIdentifier, propertyName, data)
            {
                // Get the id of the element
                var elementId = data.id;
                
                // Get the xml name
                var formFilename = $.ElementManager.getFormFilename();
                
                // If the property isn't defined, juste keep it empty
                var propertyValue = typeof data[propertyName] != 'undefined' ? data[propertyName] : "";
                
                // An empty object isn't allowed to be print, so replace it
                if (propertyValue.length == null)
                {
                    propertyValue = "";
                }
                
                // Check and uncheck the checkboxes
                if ($(elementIdentifier).attr("type") == "checkbox")
                {
                    $(elementIdentifier).attr("checked", (propertyValue != "false"));
                }
                // Or fullfill text fields
                else
                {
                    $(elementIdentifier).val(propertyValue);   
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
                    $(elementIdentifier).parent().parent().children(".description").html($.ZFM.images.filedUpdateInProgress);
                    
                    // Call ajax request to modify what has to be modified
                    $.ajaxCalls.updateElementValue(param, $(elementIdentifier));
                };

                // Remove all bind
                $(elementIdentifier).unbind();

                // Bind the onchange event with my update function
                $(elementIdentifier).bind("change", bindFunction);
            },
            /**
             * 
             * @param idElement
             * @param elementRendered
             * @param el
             */
            updateElementValueCallback : function(idElement, elementRendered, el)
            {
            	// Set the image, field is up to date
                el.parent().parent().children(".description").html($.ZFM.images.fieldUpToDate);
                // Refresh the element on the interface 
                $.ElUpdateManager.refreshElement(idElement, elementRendered);
            },
            /**
             * 
             * @param data
             */
            loadElementAttribs : function (data)
            {
                var attribs = data.attribs;

                $.TabGeneral.clearAttributes();
                // If attribs is not set
                if (typeof attribs != 'undefined')
                {
                    // Run attribs to display all of them
                    for (var name in attribs)
                    {
                        $.TabGeneral.addEditAttribute(data.id, name, attribs[name]);
                    }
                }
            },
            /**
             * 
             * @param event
             */
            addAttributeButtonClick: function(event)
            {
                event.stopPropagation();
                event.stopImmediatePropagation();

                
                // Get the xml name
                var formFilename = $.ElementManager.getFormFilename();

                // Get the id of the element from the input disabled
                var elementId = $.TabGeneral.getElementId();                
                
                // Get the values
                var name = jQuery.trim($("#generalArea #attributEditLine #attribName").val());
                var value = jQuery.trim($("#generalArea #attributEditLine #attribValue").val());
                
                var param=
                {
                    formFilename: formFilename,
                    id: elementId,
                    name: name,
                    value: value
                };
                
                if (name.length > 0)
                {
                    $.ajaxCalls.addEditElementAttribute(param);
                }
                else
                {
                    //display error
                    $.Tools.alert("Incorrect values", "Please fulfill the name");
                }
            },
            /**
             * 
             * @param id
             * @param name
             * @param value
             */
            addEditAttribute : function (id, name, value)
            {
                // Put the edit and delete function on it
                var editFunc = function(event)
                {
                    $("#generalArea #attributEditLine #attribName").val(name);
                    $("#generalArea #attributEditLine #attribValue").val(value);
                };
                
                var deleteFunc = function(event)
                {                 
                    if (confirm("Do you really want to delete the attribute " + name + " ?"))
                    {                        
                        $(this).parent().parent().remove();
                        var param = 
                        {
                            formFilename: $.ElementManager.getFormFilename(),
                            id: id,
                            name: name
                        };
                        
                        $.ajaxCalls.deleteElementAttribute(param);
                    }                    
                };
                
                
                // Let's check if we're in a edit statement
                var elementToReplace = null;
                var allAttNames = $("#generalArea #elementAttributes").children().children().children('.attName');
                
                // Run through all the attributes names in the list
                allAttNames.each(function()
                {
                    var tmpName = $(this).text();
                    
                    // If the new name is already on it, save the current element to replace in it
                    if (tmpName == name)
                    {
                        var currentAttribBlock = $(this).parent().parent();
                        elementToReplace = currentAttribBlock;
                    }
                });
                
                // If elementToReplace exists, we found a attribute with the new name so update it !
                if (elementToReplace != null)
                {
                    elementToReplace.children().children('.attValue').html(value);
                    elementToReplace.children('.datasArea').unbind().click(editFunc);
                    elementToReplace.children().children('.attribDelete').unbind().click(deleteFunc);
                }
                // New element !
                else
                {
                    // Get the html to create a new line
                    var defaultAttrib = $.TabGeneral.getDefAttribute();

                    // Set the name and value in the default html
                    defaultAttrib.children('.datasArea').click(editFunc);
                    defaultAttrib.children().children('.attName').html(name);
                    defaultAttrib.children().children('.attValue').html(value);        
                    defaultAttrib.children().children('.attribDelete').click(deleteFunc);
                    
                    // Add the element created to the end of the list
                    $("#generalArea #elementAttributes").append(defaultAttrib);
                }                              
            },
            /**
             * 
             * @param name
             * @returns
             */
            getDefAttribute : function (name)
            {
                var attribElement = $($("#generalArea #attribDefaultLine").clone().html());
                return attribElement;
            },
            /**
             * 
             */
            clearAttributes: function()
            {
                // Remove only direct children with attrib class, so we save the default line :)
                $("#generalArea #elementAttributes").children(".attrib").remove();
            }
           
        };
        
        

        
    })(jQuery);
});