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
        
        $.TabFilters = 
        {
            init : function(data)
            {
                // Freeze the tab until there is a filter selected
                $.TabFilters.disableTab();
                
                // Put the filters in the list of selected filters
                var selectedFilters = (typeof data.filters != "undefined") ? data.filters : null;                                
                $.TabFilters.fillSelectedFilters(selectedFilters);
                
                // Init the component change functions, click, autocomplete,...
                $.TabFilters.initComponents();
            },
            
            initComponents : function()
            {   
                $(".filtersHelpDialog").dialog(
                { 
                    autoOpen: false,
                    width: 400,
                    height: 400
                });
                
                $("#dialogForm #filtersArea #config .filtersHelper img").click(function()
                {
                    $(".filtersHelpDialog").dialog("open");
                });
                                
                // Connect the add button to addSelectedfilter
                $("#addFilterButton").unbind().click(function(event)
                {
                    $.TabFilters.addSelectedFilter();
                });

                // Connect the delete button to removeSelectedfilter
                $("#deleteFilterButton").unbind().click(function(event)
                {
                    $.TabFilters.removeSelectedFilter();
                });
                
                // Autocomplete on the constructor field
                $("#filterConstruct").unbind().autocomplete({
                    minLength: 0,
                    autoFocus: true,
                    source: [],
                    change: function (event, ui)
                    {
                        $.TabFilters.updateFilterValue("constructor", $(this));
                    }
                })
                .keyup(function(event) // Leave the field on enter key pressed to simulate the save on enter
                {
                    if (event.keyCode == '13')
                    {
                        event.preventDefault();
                        $("#filterConstruct").blur();
                        return null;
                    }
                });
            },

            fillHelpDialog : function(data)
            {
                if (typeof data.help == "undefined")
                {
                    $(".filtersHelpDialog").html("There is no specific help for this filter.");
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

                if (helpString == "")
                    helpString = "There is no specific help for this validaror.";
                
                $(".filtersHelpDialog").html(helpString);
                                
            },
            
            enableDisableFileFilters : function(data, elementType)
            {
                for (var i in data)
                { 
                    $("select#addFilter option:contains(" + data[i] + ")").prop("disabled", (elementType != "file"));
                }
            },
                        
            fillSelectedFilters: function(filters)
            {
                // No filters for this one
                if (filters != null && (typeof filters.filter != "undefined"))
                {
                    /*
                     * Strangley, when an object has only a child, it's not 
                     * an array but directly the property so let's cheat a bit
                     * and put it into an array for the loop 
                     */
                    if (filters.filter.length > 1)
                    {
                        filters = filters.filter;
                    }
                    else
                        filters[0] = filters.filter;
                    
                    // Fulfill the list of item selected for this element...
                    for (var i in filters)
                    {
                        $.TabFilters.addSelectedFilterCallback(filters[i]);
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
                    $.ajaxCalls.getFilterDatas(param);
                
                else
                    console.log("fillTab called without a classname ... there is probably no filters selected or something wierder.");
                 
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
                $.TabFilters.fillHelpDialog(data);
                
                // Fill the field with the datas
                $("#filtersArea #description #className").text(knownDatas.name);
                $("#filtersArea #filterConstruct").val(knownDatas.constructor);
                
                // Fill the fields with new datas coming from the ajax call on the filters xml file
                $("#filtersArea #description #classDescription").text(data.desc); 

                // If there is no codes, disable autocomplete
                if (typeof data.codes == "undefined" || typeof(data.codes.code) == "undefined")
                {                    
                    $("#filtersArea #filterConstruct").autocomplete("option", "source", []);
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
                    
                   $("#filtersArea #filterConstruct").autocomplete("option", "source", codes);
                }
                
            },
            
            addSelectedFilter: function ()
            {
                var param = 
                {
                    formFilename: $.ElementManager.getFormFilename(),
                    id: $.TabGeneral.getElementId(),
                    name: $("select#addFilter").val()
                };
                
                // Is it already in the list ?
                if (!$($.ZFM.selectors.filterSelected).containsOption(param.name))
                {
                    $.ajaxCalls.addFilter(param);
                }
            },
            
            addSelectedFilterCallback: function (datas)
            {
                var firstChild = ($($.ZFM.selectors.filterSelected + ":has(option)").length == 0);

                // It's the first filter for this element, enable the tab
                if (firstChild)
                    $.TabFilters.enableTab();
                
                $("select#addFilter option:contains(" + datas.name + ")").prop("disabled", true);
                
                // Add the filter to the list of options
                $($.ZFM.selectors.filterSelected).addOption(datas.name, datas.name, false); // 3rd argument is select or not the line added
                
                var lastOption =  $($.ZFM.selectors.filterSelected + " option:last");
                
                // Set the click of this option
                lastOption.click(function(event)
                {
                    // Remove the selected attribute on all the element
                    $.TabFilters.fillTab(datas);
                });
                
                // First element added, select it and click it!
                if (firstChild)
                {
                    lastOption.prop("selected", true).click();
                }                
            },
            
            
            removeSelectedFilter: function ()
            {
                // Be sure that something is selected before to delete it !
                if ($($.ZFM.selectors.filterSelected).selectedOptions().length == 1)
                {
                    var param = 
                    {
                        formFilename: $.ElementManager.getFormFilename(),
                        id: $.TabGeneral.getElementId(),
                        name: $($.ZFM.selectors.filterSelected).val()
                    };
                    
                    $.ajaxCalls.removeFilter(param);
                }
            },
            removeSelectedFilterCallback: function (data)
            {
                $($.ZFM.selectors.filterSelected).removeOption(data.name);

                $("select#addFilter option:contains(" + data.name + ")").removeAttr("disabled");
                
                // Disable interface if there is no more filters
                if ($($.ZFM.selectors.filterSelected + ":has(option)").length == 0)
                {
                    $.TabFilters.disableTab();
                }
                // Select last element in the list
                else
                {
                    $($.ZFM.selectors.filterSelected + " option").removeAttr("selected");
                    
                    $($.ZFM.selectors.filterSelected + " option:last")
                    .prop("selected", true)
                    .focus()
                    .click();    
                }
            },
            
            updateFilterValue : function (propertyName, element)
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
                    name : $.TabFilters.getClassName(),
                    
                    propertyName: propertyName,
                    propertyValue: newValue
                };
                                

                // Update this element's description
                element.parent().parent().children(".description").html($.ZFM.images.filedUpdateInProgress);
                $.ajaxCalls.updateFilterValue(param, element);
            },
            updateFilterValueCallback : function (el)
            {
                // Set the image, field is up to date
                el.parent().parent().children(".description").html($.ZFM.images.fieldUpToDate);                
            },
            
            
            
            
            
            
            
            disableTab: function()
            {
                // Clean the filter list
                $("#filtersList").html("");
                $("select#addFilter option").removeAttr("disabled");
                
                // Fullfill the tab with empty values
                $("#filtersArea #description #className").text("Zend_Filter_Class");
                $("#filtersArea #description #classDescription").text("Please select a filter");
                
                $("#filtersArea #filterConstruct").val("");
                
                // Disable the components
                $("#filtersArea #filterConstruct").prop("disabled", true);
     
            },
            
            enableTab: function()
            {
                $("#filtersArea #filterConstruct").removeAttr("disabled");
            },
            
            getClassName: function()
            {
                return $("select#filtersList").selectedValues()[0];
            }
        };
    })(jQuery);
});