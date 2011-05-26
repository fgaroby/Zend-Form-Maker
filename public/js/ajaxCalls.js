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
        var urlXmlController = "/xml-manager/";

        $.ajaxCalls =
        {
            /*****************************************
             * Element manage                        *
             *****************************************/
                
            /**
             * 
             * @param param:
             */
            createElement : function(param, asyncCall)
            {
                var url = urlXmlController + "add-element";
                var dataType = "json";

                var funcResult = function(datas)
                {
                    $.ElementManager.createElementCallback(datas.el, datas.id, asyncCall);
                };

                if (asyncCall == 'false')
                {
                    var failFunction = function(){};
                    
                    $.Tools.sendAjax(url, param, funcResult, dataType, failFunction, 'false');
                }
                else
                {
                    $.Tools.sendAjax(url, param, funcResult, dataType);
                }
                
            },

            deleteElement : function (param, el)
            {
                var url = urlXmlController + "delete-element";
                var dataType = "json";

                var funcResult = function(datas)
                {
                    $.ElementManager.deleteElementCallback(el, datas);
                };
                
                var failCallback = function()
                {
                    $.ElementManager.loadForm();
                };

                $.Tools.sendAjax(url, param, funcResult, dataType, failCallback);
            },

            updateElementValue : function(param, el)
            {
                var url = urlXmlController + "update-element";
                var dataType = "json";

                var funcResult = function(datas)
                {
                    $.TabGeneral.updateElementValueCallback(datas.id, datas.elementRendered, el);        
                };
                
                var failCallback = function()
                {
                    $($.ZFM.selectors.editElementDialogForm).dialog("close");                    
                    $.ElementManager.loadForm();
                };
                
                $.Tools.displayAjaxLoader = false;
                $.Tools.sendAjax(url, param, funcResult, dataType, failCallback);
                $.Tools.displayAjaxLoader = true;
            },

            /*****************************************
             * Element attributes management         *
             *****************************************/
            addEditElementAttribute: function (param)
            {
                var url = urlXmlController + "add-edit-element-attribute";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.TabGeneral.addEditAttribute(datas.id, param.name, param.value);
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);                   
            },
            
            deleteElementAttribute : function(param)
            {
                var url = urlXmlController + "delete-element-attribute";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    //            
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);      
            },
            
            
            
            
            
            
            /*****************************************
             * Update element validators             *
             *****************************************/
            
            addValidator: function(param)
            {
                var url = urlXmlController + "add-validator";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.TabValidators.addSelectedValidatorCallback(datas);                
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);    
            },
            
            removeValidator : function (param)
            {
                var url = urlXmlController + "remove-validator";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.TabValidators.removeSelectedValidatorCallback(datas);                
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);    
            },
            
            updateValidatorValue: function(param, element)
            {
                var url = urlXmlController + "update-validator";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.TabValidators.updateValidatorValueCallback(element);                
                };

                $.Tools.displayAjaxLoader = false;
                $.Tools.sendAjax(url, param, funcResult, dataType);  
                $.Tools.displayAjaxLoader = true;
            },
            
            getValidatorDatas: function (param)
            {
                var url = urlXmlController + "get-validator-datas";
                var dataType = "json";
                
                var funcResult = function(newDatas)
                {
                    $.TabValidators.fillTabCallback(newDatas, param.knownDatas);                          
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);  
            },
            
            
            getValidatorsFileInput: function (param, elementType)
            {
                var url = urlXmlController + "get-validators-file-input";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabValidators.enableDisableFileValidators(datas, elementType); 
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);  
            },
            
            
            
            
            
            /*************************************
             * Update element filters            *
             ************************************/
            
            addFilter: function(param)
            {
                var url = urlXmlController + "add-filter";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.TabFilters.addSelectedFilterCallback(datas);                
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);    
            },
            
            removeFilter : function (param)
            {
                var url = urlXmlController + "remove-filter";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.TabFilters.removeSelectedFilterCallback(datas);                
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);    
            },
            
            updateFilterValue: function(param, element)
            {
                var url = urlXmlController + "update-filter";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.TabFilters.updateFilterValueCallback(element);                
                };

                $.Tools.displayAjaxLoader = false;
                $.Tools.sendAjax(url, param, funcResult, dataType);  
                $.Tools.displayAjaxLoader = true;
            },
            
            getFilterDatas: function (param)
            {
                var url = urlXmlController + "get-filter-datas";
                var dataType = "json";
                
                var funcResult = function(newDatas)
                {
                    $.TabFilters.fillTabCallback(newDatas, param.knownDatas);                          
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);  
            },
            
            
            
            
            
            

            /*************************************
             * Options tab *
             ************************************/
            getTabOptionsForm: function(param)
            {
                var url = urlXmlController + "get-options-form";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabOptions.initTabFormCallback(datas);                          
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType, function(){}, "false"); 
            },

            updateElementOptionValue : function(param, el)
            {
                var url = urlXmlController + "update-element-option";
                var dataType = "json";

                var funcResult = function(datas)
                {
                    $.TabGeneral.updateElementValueCallback(datas.id, datas.elementRendered, el);                    
                };
                
                var failCallback = function()
                {
                    $($.ZFM.selectors.editElementDialogForm).dialog("close");                    
                    $.ElementManager.loadForm();
                };
                
                $.Tools.displayAjaxLoader = false;
                $.Tools.sendAjax(url, param, funcResult, dataType, failCallback);
                $.Tools.displayAjaxLoader = true;
            },
            
            /***********************************
             * Multioptions
             ***********************************/
            addMultiOption: function(param)
            {
                var url = urlXmlController + "add-multioption";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabOptions.addSelectedOptionCallback(datas);                          
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType); 
            },
            
            removeMultiOption: function(param)
            {
                var url = urlXmlController + "remove-multioption";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabOptions.removeSelectedOptionCallback(datas);                          
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType); 
            },
            
            updateOptionValue: function(param, element)
            {
                var url = urlXmlController + "update-multioption";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabOptions.updateOptionValueCallback(datas, element);                          
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType); 
            },

            /*************************************
             * Decorators management             *
             ************************************/

            addDecorator: function(param)
            {
                var url = urlXmlController + "add-decorator";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabDecorators.addDecCallback(datas);                  
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType); 
            },
            

            updateDecorator: function(param)
            {
                var url = urlXmlController + "update-decorator";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabDecorators.updateDecCallback(datas);                  
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType); 
            },
            
            deleteDecorator: function (param, line)
            {
                var url = urlXmlController + "delete-decorator";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.TabDecorators.deleteDecCallback(datas, line);                  
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType); 
            },
            
            /*************************************
             * Form management                   *
             ************************************/
            addEditFormAttribute: function(param)
            {

                var url = urlXmlController + "add-edit-form-attribute";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.ElementManager.addEditAttribute(param.name, param.value);
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);  
            },
            
            deleteFormAttribute: function(param)
            {

                var url = urlXmlController + "delete-form-attribute";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    //            
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);    
            },    
            
            loadForm : function (param)
            {
                var url = urlXmlController + "load-elements";
                var dataType = "json";

                var funcResult = function(datas)
                {
                    $.ElementManager.loadFormCallback(datas);
                };

                $.Tools.sendAjax(url, param, funcResult, dataType);
            }, 
            
            updateOrders : function(param)
            {
                var url = urlXmlController + "update-orders";
                var dataType = "json";
    
                var funcResult = function(datas)
                {
                    $.UserInterface.sortSortableCallback(datas);                
                };
                
                var failResult = function(datas)
                {
                    $.ElementManager.loadForm();
                };
                $.Tools.sendAjax(url, param, funcResult, dataType, failResult);   
            },

            getEditDialogDatas : function(param)
            {
                var url = urlXmlController + "get-element";
                var dataType = "json";

                var funcResult = function(datas)
                {
                    $.ElUpdateManager.getEditDialogDatasCallback(datas);
                };

                $.Tools.sendAjax(url, param, funcResult, dataType);
            },
            
            previewForm: function (param)
            {
                var url = urlXmlController + "preview-form";
                var dataType = "html";
                
                var funcResult = function(datas)
                {
                    $.UserInterface.previewFormCallback(datas);
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);  
            },
            
            buildForm: function (param)
            {
                var url = urlXmlController + "build-form";
                var dataType = "json";
                
                var funcResult = function(datas)
                {
                    $.UserInterface.buildClassCallback(datas);
                };
                
                $.Tools.sendAjax(url, param, funcResult, dataType);  
            }
        };
    })(jQuery);
});
