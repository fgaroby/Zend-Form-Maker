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

        $.ElementManager = 
        {
            loadForm : function()
            {                
                var formFilename = $.ElementManager.getFormFilename();

                var param =
                {
                    formFilename : formFilename
                };
                
                $($.ZFM.selectors.sortableArea).html("");
                // Erase the html warning, we're getting a new list !
                $.UserInterface.eraseBuildInfos();
                $.ajaxCalls.loadForm(param);
            },
            
            loadFormCallback: function(datas)
            {
                
                for(var i in datas.elements)
                {
                    var data = datas.elements[i];
                    $.ElementManager.createElementCallback(data.el, data.id);
                }  
                
                $.ElementManager.loadElementAttribs(datas);
            },

            /****************************************************************************
             * Element management 
             ****************************************************************************/
            
            createElement : function(el, asyncCall)
            {
                // Get the type of the element to create
                var elementType = $.ElementManager.getElementType(el);
                var formFilename = $.ElementManager.getFormFilename();
                var param =
                {
                    type : elementType,
                    formFilename : formFilename
                };                

                $.ajaxCalls.createElement(param, asyncCall);
            },
  
            createElementCallback : function (el, id, asyncCall)
            {
                var sortableElement = $.ElementManager.buildSortableElement(el, id); 
                
                // If we want to get back the result, and not just add it at the end, save it in a tmp var
                if (asyncCall == 'false')
                {
                    $.ZFM.tmpSortableElement = {el: sortableElement, id: id};
                }
                else
                {
                    // Add the element at the end of the form
                    $($.ZFM.selectors.sortableArea).append(sortableElement);

                    // Add the context menu on it
                    $.ElementManager.addCMSortableElement(id);

                    $("." + $.ZFM.className.sortableElement + '_' + id).click(function(event){event.preventDefault(); event.stopImmediatePropagation();})
                    .effect('highlight');

                    // Hide or not the element's handle depending on others handle's names, if this is the first element, hide it
                    if ($("." + $.ZFM.className.sortableHandle + ":hidden").length > 0 || $("." + $.ZFM.className.sortableHandle).length == 1)
                        $("." + $.ZFM.className.sortableHandle).hide();
                }                
            },

            
            buildSortableElement : function (element, id)
            {
                var sortableElement = '';
                
                sortableElement += '<table id="orderid_' + id + '" class="' + $.ZFM.className.sortableElement + ' ' + $.ZFM.className.sortableElement + '_' + id + '">' + "\r\n";
                sortableElement += '    <tbody>' + "\r\n";
                sortableElement += '        <tr>' + "\r\n";
                sortableElement += '            <td>';
                sortableElement += '                <div class="' + $.ZFM.className.sortableHandle + '">' + "\r\n";
                sortableElement += '                </div>' + "\r\n";
                sortableElement += '            </td>' + "\r\n";
                sortableElement += '            <td class="' + $.ZFM.className.sortableElementPart + '" title="right click to display the context menu">' + "\r\n";
                sortableElement += '                ' + element + "\r\n";
                sortableElement += '            </td>' + "\r\n";
                sortableElement += '        </tr>' + "\r\n";
                sortableElement += '    </tbody>' + "\r\n";
                sortableElement += '</table>' + "\r\n\r\n";
                
                return sortableElement;
            },
            
            addCMSortableElement: function (id)
            {
            	// Build the selector
                var lastElementAddName = "." + $.ZFM.className.sortableElement + '_' + id;
                
                // Select the elementPart of this sortable element
                var lastElementAdd = $(lastElementAddName).children().children().children("." + $.ZFM.className.sortableElementPart);
                
                // Put the context menu on it
                lastElementAdd.contextMenu(
                    {
                        menu : $.ZFM.contextMenu.edit
                    }, function(action, el, pos)
                    {
                        switch (action)
                        {
                            case "edit" :
                                $.ElUpdateManager.getEditDialogDatas(id);
                                break;
                                
                            case "delete" :
                                var sortableElementFromHere = el.parent().parent().parent();
                                $.ElementManager.deleteElement(sortableElementFromHere);
                                break;
    
                            case "quit" :
                            default :
                                break;
                        }
                    }
                );
            },

            
            deleteElement : function (el)
            {
                // Get the id
                var id = $.ElementManager.getElementId(el);
                var formFilename = $.ElementManager.getFormFilename();

                var param =
                {
                    id : id,
                    formFilename : formFilename
                };

                var elementName = $.ElementManager.getElementName(el);
                
                if (confirm("You're about to delete the element named '" + elementName + "'. Are you sure ? "))
                    $.ajaxCalls.deleteElement(param, el);
            },
            
            deleteElementCallback : function (el, data)
            {
                // Remove it from UI                
                el.effect('explode');
                el.remove();
                
                // Close the edit dialog if opened
                if ($($.ZFM.selectors.editElementDialogForm).dialog('isOpen'))
                    $($.ZFM.selectors.editElementDialogForm).dialog('close');
                
                $.Tools.showMessage("success", data.msg);
            },
            
            
            
            /****************************************************************************
             * Getters
             ****************************************************************************/
			/**
             * 
             * @param el
             * @returns
             */
            getElementId : function(el)
            {
                return $.Tools.getDataInClass(el, $.ZFM.className.sortableElement);
            },

            /**
             * 
             * @param el
             * @returns
             */
            getElementType : function(el)
            {
                return $.Tools.getDataInClass(el, "type");
            },
            
            /*
             * 
             */
            getElementName : function (el)
            {
                var id = $.ElementManager.getElementId(el);
                var sortableTable = "." + $.ZFM.className.sortableElement + "_" + id;
                var element = $(sortableTable + " input");
                
                // The element isn't an input
                if (element.length == 0)
                {
                    var element = $(sortableTable + " select");
                    // The element isn't a select, so it's a textarea
                    if (element.length == 0)
                    {
                        var element = $(sortableTable + " textarea");
                        
                        // If it's still not a textarea, button is the last one
                        if (element.length == 0)
                            var element = $(sortableTable + " button");
                    }
                }
                
                // This is stupid if the element is hidden from start ... STUUUUUUUUUPPPIIIIID
                if (element.attr("type") == "hidden")
                {
                    //while(element.attr("type") == "hidden")
                        //element = element.next();
                }
                name = element.attr("name");
                return name;
            },
            
            getFormFilename: function()
            {
                return $($.ZFM.selectors.formFilename).text();   
            },
            
            
            
            
            /****************************************************************************
             * Manage attributes 
             ****************************************************************************/
            initFormAttributes: function()
            {
                
                // Form settings, attribute add /edit button
                $("#formSettings #addAttributeButton").click(function(event)
                {
                    $.ElementManager.addAttributeButtonClick(event);
                });
                
                $("#formSettings #attribValue").keyup(function(event)
                {
                    if (event.keyCode == '13')
                    {                        
                        $.ElementManager.addAttributeButtonClick(event);
                    }
                });
            
                $("#formSettings #attribName").keyup(function(event)
                {
                    if (event.keyCode == '13')
                    {                        
                        $.ElementManager.addAttributeButtonClick(event);
                    }
                });
            },
            
            /**
             * 
             * @param data
             */
            loadElementAttribs : function (data)
            {
                var attribs = data.attribs;
                $.ElementManager.clearAttributes();
                // If attribs is not set
                if (typeof attribs != 'undefined')
                {
                    // Run attribs to display all of them
                    for (var name in attribs)                    
                        $.ElementManager.addEditAttribute(name, attribs[name]);                                           
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
                
                // Get the values
                var name = jQuery.trim($("#formSettings #attributEditLine #attribName").val());
                var value = jQuery.trim($("#formSettings #attributEditLine #attribValue").val());
                
                var param=
                {
                    formFilename: formFilename,
                    name: name,
                    value: value
                };
                
                if (name.length > 0)
                {
                    $.ajaxCalls.addEditFormAttribute(param);
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
            addEditAttribute : function (name, value)
            {
                // Put the edit and delete function on it
                var editFunc = function(event)
                {
                    $("#formSettings #attributEditLine #attribName").val(name);
                    $("#formSettings #attributEditLine #attribValue").val(value);
                };
                
                var deleteFunc = function(event)
                {                 
                    if (confirm("Do you really want to delete the attribute " + name + " ?"))
                    {                        
                        $(this).parent().parent().next().remove();
                        $(this).parent().parent().remove();
                        var param = 
                        {
                            formFilename: $.ElementManager.getFormFilename(),
                            name: name
                        };
                        
                        $.ajaxCalls.deleteFormAttribute(param);
                    }                    
                };
                // Let's check if we're in a edit statement
                var elementToReplace = null;
                var allAttNames = $("#formSettings #elementAttributes").children().children().children('.attName');

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
                    var defaultAttrib = $.ElementManager.getDefAttribute();

                    // Set the name and value in the default html
                    defaultAttrib.children('.datasArea').click(editFunc);
                    defaultAttrib.children().children('.attName').html(name);
                    defaultAttrib.children().children('.attValue').html(value); 
                    defaultAttrib.children().children('.attribDelete').click(deleteFunc);

                    // Add the element created to the end of the list
                    $("#formSettings #elementAttributes").append(defaultAttrib);
                }                              
            },
            /**
             * 
             * @param name
             * @returns
             */
            getDefAttribute : function (name)
            {
                var attribElement = $($("#formSettings #attribDefaultLine").clone().html());
                return attribElement;
            },
            
            /**
             * 
             */
            clearAttributes: function()
            {
                // Remove only direct children with attrib class, so we save the default line :)
                $("#formSettings #elementAttributes").children(".attrib").each(function(){
                    $(this).next.remove();
                    $(this).remove();
                });
            }
        };
           
        // Load the form elements when ready
        $.ElementManager.loadForm();
        
        $.ElementManager.initFormAttributes();
    })(jQuery);
});