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
        $.ZFM = {};
        $.ZFM.selectors = 
        {
            // Element draggable area selectors
            elementDraggableContainer : "#elementSidebar",
            elements1 : "#elementFirstPart",
            elements2 : "#elementSecondPart",
            showHideElements : "#showHideElementGroup",
            
            // Drop
            //dropArea : "#formRenderArea",
            sortableArea : "#formRenderAreaContent",
            trash : "#trash",
            
            // Dialoge form selectors
            editElementDialogForm : "#dialogForm",
            
            // Footer selecter
            toggleClassOrdering : "#toggleClassOrdering",

           
            // Validators tab
            validatorSelected: "select#validatorsList",
            filterSelected: "select#filtersList",
            optionSelected: "select#optionsList",
            
            // Global selectors
            formFilename: ".formFilename",
            ajaxError : "#ajaxErrorDetails"
        };

        $.ZFM.className =
        { 
            droppableActiveClass : "ui-drop-hover",
            sortableActiveClass : "ui-drop-hover",
            sortablePlaceholer: "zfm-sortable-placeholder",
            sortableElement: "sortable",
            sortableHandle: "handle",
            sortableElementPart: "elementPart",
            sortablePlaceholder: "zfm-sortable-placeholder"
         };
                
        $.ZFM.subSelectors =
        {
            elementDraggable : $.ZFM.selectors.elementDraggableContainer + " .formElement",
            elementsSwitch : $.ZFM.selectors.elementDraggableContainer + " #switchPart",
            editElementTabs : $.ZFM.selectors.editElementDialogForm + " #tabs",

            editElementAttributesArea : $.ZFM.selectors.editElementDialogForm + " #generalArea #attributesArea",
            
            sortableElement : $.ZFM.selectors.sortableArea + " ." + $.ZFM.className.sortableElement,
            sortableHandle : $.ZFM.selectors.sortableArea + " ." + $.ZFM.className.sortableHandle,
            sortableElementPart: $.ZFM.selectors.sortableArea + " ." + $.ZFM.className.sortableElementPart
        };

        $.ZFM.images =
        {    
            fieldUpToDate : '<img src="/images/zfm/updateDone.png" title="update done" alt="update done" />',
            filedUpdateInProgress : '<img src="/images/zfm/updateProcessing.gif" title="update in progress" alt="update in progress" />',
            
            nextElements : '<img src="/images/zfm/slideright.png" alt="Next" title="Next elements" />',
            previousElements : '<img src="/images/zfm/slideleft.png" alt="Previous" title="Previous elements" />',
            
            openEye: '<img src="/images/zfm/open.png" title="Open sidebar" alt="eye" />',
            closeCross : '<img src="/images/zfm/close.png" title="Close sidebar" alt="cross" />'
        };
            
        $.ZFM.contextMenu =
        {
            add: "formElementAddMenu",
            edit: "formElementEditMenu"
        };
                      
    })(jQuery);
});
