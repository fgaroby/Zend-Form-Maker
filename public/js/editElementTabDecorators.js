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

        $.TabDecorators = 
        {
           emptyList : "There is no custom validators, the default one will be used. " +
           		"Be careful, if you set one, every default decorators will be removed and you'll have to set everyting.",
           newLineId : -1,
           
           init: function(datas)
           {
               $.TabDecorators.initComponents(datas);
           },
           
           initComponents: function(datas)
           {
               var decClass = $("#decoratorsArea #decoratorClass");
               var decOptions = $("#decoratorsArea #decoratorOptions");
               var decId = $("#decoratorsArea #decId");
               
               var classSources = ["ViewHelper", "HtmlTag", "Description", "Errors",  "Label",
                                   "Fieldset", "ViewScript", "File", "Form", "FormElements", "FormErrors", 
                                   "Image", "Captcha", "DtDdWrapper", "PrepareElements", "Callback"
                                   ];
               
               var optionsSources= ["array('tag' => '')", 
                                    "array('placement' => '')",                           
                                    "array('separator' => '')",
                                    
                                    "array('tag' => '', 'placement' => 'prepend')",
                                    "array('tag' => '', 'separator' => '')",
                                    "array('tag' => '', 'placement' => 'prepend', 'separator' => '')",
                                    "array('placement' => '', 'separator' => '')",

                                    "array('viewScript' => '_file.phtml')",
                                    
                                    
                                    // With class
                                    "array('tag' => '', 'class' => '')",
                                    "array('tag' => '', 'class' => '', 'placement' => 'prepend')",
                                    "array('tag' => '', 'class' => '', 'separator' => '')",
                                    "array('tag' => '', 'class' => '', 'separator' => '', 'placement' => 'prepend')",
                                    
                                    "array('class' => '')",
                                    "array('class' => '', 'placement' => '')",
                                    "array('class' => '', 'separator' => '')",
                                    "array('class' => '', 'placement' => '', 'separator' => '')"
                                    
                                    ];
               
               // Set teh defautl message for empty decorators
               $("#decList").html($.TabDecorators.emptyList);
               
               // Load the decorators
               $.TabDecorators.loadList(datas);

               // Reset the fields
               $.TabDecorators.initFields();

               $("#decoratorsArea #decSubmit").unbind().click(function(event)
               {
                   event.preventDefault();
                   
                   // Add or update a row depending on the id value
                   if (decId.val() > 0)
                       $.TabDecorators.updateDec(decId.val(), decClass.val(), decOptions.val());
                   else                       
                       $.TabDecorators.addDec(decClass.val(), decOptions.val());
               });
               
               decClass.val("").autocomplete({
                   minLength: 0,
                   source: classSources
               });
               
               decOptions.val("").autocomplete({
                   minLength: 0,
                   source: optionsSources
               });
           },
           
           initFields: function()
           {
               $("#decoratorsArea #decId").val($.TabDecorators.newLineId);
               $("#decoratorsArea #decoratorOptions").val("").unbind("keyup").keyup(function(event){
                   if (event.keyCode == "13")
                       $("#decoratorsArea #decSubmit").click();
               });
               $("#decoratorsArea #decoratorClass").val("").focus().unbind("keyup").keyup(function(event){
                   if (event.keyCode == "13")
                       $("#decoratorsArea #decSubmit").click();
               });;
           },
           
           loadList: function(datas)
           {
               if (typeof datas.decorators != "undefined")
               {       
                   if (typeof datas.decorators.decorator != "undefined")
                   {
                       if (datas.decorators.decorator instanceof Array)
                       {
                           var decorators = datas.decorators.decorator;
                       }
                       else
                       {
                           var decorators = [];
                           decorators.push(datas.decorators.decorator);
                       }

                       for (var i in decorators)
                       {
                           datas = {
                                   decId : decorators[i].id, 
                                   decClass: decorators[i].name, 
                                   decOptions: decorators[i].options
                                   };
                           
                           $.TabDecorators.addDecCallback(datas);
                       }
                   }
               }
           },
           
           addDec: function(decClass, decOptions)
           {
               decClass = jQuery.trim(decClass);
               decOptions = jQuery.trim(decOptions);
               param = {
                           formFilename: $.ElementManager.getFormFilename(),
                           idElement: $.TabGeneral.getElementId(), 
                           decClass: decClass, 
                           decOptions: decOptions
                       };
               
               if (decClass.length > 0)
                   $.ajaxCalls.addDecorator(param);
               else
                   $.Tools.alert("Empty fields", "Please at least fulfill the decorator name.");
           },
           
           addDecCallback: function(datas)
           {
               var line = $.TabDecorators.getNewLine(datas.decId, datas.decClass, datas.decOptions);
               var list = $("#decList");
               
               // Clean the list if it's empty
               if ($("#decList:contains(" + $.TabDecorators.emptyList + ")").length > 0)
               {
                   list.html("");
               }

               // Add the line at the end
               list.append(line);
               
               // Set the click function
               var newLine = list.children('.lineWrapper').last();
               
               $.TabDecorators.setLine(newLine, datas);
           },
           
           getNewLine: function (decId, decClass, decOptions)
           {
               var line = 
                       '<div class="lineWrapper">'
                              +'<div class="line">'
                                   + '<input type="hidden" class="lineId" value="" />'
                                   + '<div class="actions">'
                                       + '<img src="/images/zfm/decoratorDelete.png" alt="Delete" title="Delete">'
                                   + '</div>'
                                   + '<div class="decClass"></div>'
                                   + '<div class="decOptions"></div>'
                                   + '<div class="cleaner"></div>'
                            + '</div>'
                            + '<div class="spacer_v15"></div>'
                        + '</div>';
               
               return line;
           },
           
           setLine: function(line, datas)
           {
               // Update the line's content
               line.children().children('.lineId').val(datas.decId);
               line.children().children('.decOptions').text(datas.decOptions);
               line.children().children('.decClass').text(datas.decClass);
               
               // Set the line content in the fields
               line.children('.line').click(function(event)
               {
                   // If the property is empty, display an empty string
                   if (typeof datas.decOptions.length == "undefined")
                       datas.decOptions = "";
                   
                   $("#decoratorsArea #decoratorClass").val(datas.decClass);
                   $("#decoratorsArea #decoratorOptions").val(datas.decOptions);
                   $("#decoratorsArea #decId").val(datas.decId);
               });
               
               // Delete the line
               line.children('.line').children('.actions').click(function(event)
               {
                   // Avoid the line click
                   event.stopImmediatePropagation();
                   
                   // Delete the line
                   $.TabDecorators.deleteDec($(this).parent());
               });
               
               // Interface pretty things
               line.effect('highlight');
               $.TabDecorators.initFields();
               $.ElUpdateManager.refreshElement(datas.idElement, datas.elementRendered);
           },
           
           updateDec: function (decId, decClass, decOptions)
           {
               decClass = jQuery.trim(decClass);
               decOptions = jQuery.trim(decOptions);
               param = {
                           formFilename: $.ElementManager.getFormFilename(),
                           idElement: $.TabGeneral.getElementId(), 
                           decId: decId, 
                           decClass: decClass, 
                           decOptions: decOptions
                       };
               
               if (decClass.length > 0)
                   $.ajaxCalls.updateDecorator(param);
               else
                   $.Tools.alert("Empty fields", "Please at least fulfill the decorator name.");
               
           },
           
           updateDecCallback: function(datas)
           {
               var el = $("#decoratorsArea .line .lineId[value='" + datas.decId + "']").parent().parent();               
               $.TabDecorators.setLine(el, datas);

               $.TabDecorators.initFields();
               $.ElUpdateManager.refreshElement(datas.idElement, datas.elementRendered);
           },
           
           deleteDec: function(line)
           {
               var decId = line.children('.lineId').val();
               if (confirm("Do you really want to delete the decorator " + line.children(".decClass").text() + " (id = " + decId + ")"))
               {
                   param = {
                       formFilename: $.ElementManager.getFormFilename(),
                       idElement: $.TabGeneral.getElementId(), 
                       decId: decId
                   };
                   

                   $.ajaxCalls.deleteDecorator(param, line);
               }
           },
           
           deleteDecCallback: function(datas, line)
           {
               line.parent().effect("explode");
               line.parent().remove();
               $.Tools.showMessage("success", "The decorators " + datas.decClass + " has successfully been deleted.");

               $.TabDecorators.initFields();
               $.ElUpdateManager.refreshElement(datas.idElement, datas.elementRendered);
           }
           
           
        };
    })(jQuery);
});