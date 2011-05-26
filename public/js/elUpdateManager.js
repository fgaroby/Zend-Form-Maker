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

        $.ElUpdateManager = 
        {
            getEditDialogDatas : function(id)
            {
                var formFilename = $.ElementManager.getFormFilename();

                var param =
                {
                    id : id,
                    formFilename : formFilename
                };
                $.ajaxCalls.getEditDialogDatas(param);
            },

            /**
             * Callback from the ajax call, display the datas of the element into the tabs
             * @param data : everything about this element
             */
            getEditDialogDatasCallback : function(data)
            {
                id = data['@attributes'].id;
                data.id = id;
                var dialogTitle = "Edit element " +data.name + "(" + id + ")";
                
                // Display the dialog box                
                $($.ZFM.subSelectors.editElementTabs).tabs("select", 0);                
                $($.ZFM.selectors.editElementDialogForm).dialog("option", "title", dialogTitle).dialog("open");
                
                // Init tabs
                $.TabGeneral.init(data);
                $.TabValidators.init(data);
                $.TabFilters.init(data);
                $.TabOptions.init(data);
                $.TabDecorators.init(data);

                // Focus the elementName of the general tab
                $($.ZFM.selectors.editElementDialogForm + " #elementName").focus();
            },
            
            /**
             * Refresh an element in the sortable list with the new rendred view
             * @param idElement : Id of the element to refresh
             * @param elementRendered : Html to put instead of the one already there
             */
            refreshElement: function(idElement, elementRendered)
            {
                // Build the sortable element with the new values
                var sortableElement = $.ElementManager.buildSortableElement(elementRendered, idElement);
                               
                // Change the html of this element
                var elementClassSelector = "." +  $.ZFM.className.sortableElement + "_" + idElement;
                $(elementClassSelector).replaceWith(sortableElement);
                
                // Put the context menu on it
                $.ElementManager.addCMSortableElement(idElement);
                $(elementClassSelector).effect('highlight');
                
                // Hide or not the element's handle depending on others handle's names
                if ($("." + $.ZFM.className.sortableHandle + ":hidden").length > 0)
                    $("." + $.ZFM.className.sortableHandle).hide();
            }
        };
        
        

        
    })(jQuery);
});