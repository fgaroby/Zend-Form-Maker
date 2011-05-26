
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
/**
 * This page will initialize all the element of the user interface, like drag'n
 * drop, the contextMenu, the formDialog, the sortable area, etc.
 * The dialog form has tabs which have specifics javascript file where their UI is initialized.
 */
$(document).ready(function()
{
    (function($)
    {
        // Disable selection on draggable areas
        $($.ZFM.selectors.elementDraggableContainer).disableSelection();
        $($.ZFM.selectors.sortableArea).disableSelection();
        $($.ZFM.selectors.trash).disableSelection();
        
        // Disable rightclick on the sortable area
        $($.ZFM.selectors.sortableArea).bind("contextmenu", function(e) {
            e.preventDefault();
        });
        
        // Let's init some graphics components
        $.UserInterface =
        {
            /***********************************************************************
             * Sidebar featues
             ***********************************************************************/

            /**
             * Make element from the sidebar draggable
             */
            
            enableDraggable: function()
            {
                $($.ZFM.subSelectors.elementDraggable).draggable(
                {
                    helper : "clone",
                    revert : "invalid",
                    disableSelection : true,
                    
                    connectToSortable: $($.ZFM.selectors.sortableArea)                
                })
                .unbind('dblclick');
            },
            
            disableDraggable: function()
            {
                $($.ZFM.subSelectors.elementDraggable).draggable('destroy')
                .dblclick(function(event)
                {
                    $.ElementManager.createElement($(this));
                });
            },

            /**
             * Add a context menu on the element draggable
             */
            initDraggableCMenu : $($.ZFM.subSelectors.elementDraggable).contextMenu(
                {
                    menu : $.ZFM.contextMenu.add
                }, 
                function(action, el, pos)
                {
                    switch (action)
                    {
                        case "add" :
                            $.ElementManager.createElement(el);
                            break;
                    }
                }
            ),
                       
            
            /**
             * The sidebar on the left can switch elements and be hidden, this is where we do this
             */
            initSidebar: function()
            {
                // Toggle elements lists
                $($.ZFM.subSelectors.elementsSwitch).toggle(
                function()
                {
                    $($.ZFM.selectors.elements1).hide("slide", { direction: "left" }, 500);
                    $($.ZFM.selectors.elements2).show("slide", { direction: "right" }, 500);
                    
                    $(this).html($.ZFM.images.previousElements);
                },
                function()
                {
                    $($.ZFM.selectors.elements2).hide("slide", { direction: "right" }, 500);
                    $($.ZFM.selectors.elements1).show("slide", { direction: "left" }, 500);
                    $(this).html($.ZFM.images.nextElements);
                });


                
                // Select all the elements to move when hiding elements
                var contentToMove = $.ZFM.selectors.elements1 + "," + $.ZFM.selectors.elements2 + "," + $.ZFM.selectors.elementDraggableContainer + "," + $.ZFM.selectors.showHideElements; 
                var movementSize = $($.ZFM.selectors.elementDraggableContainer).width();
                var openText = "OPEN";
                var closeText = "CLOSE";
                
                openText = openText.split("").join("<br/>");
                closeText = closeText.split("").join("<br/>");
                
                // Show / Hide the slidebar by moving it, and all related elements
                $($.ZFM.selectors.showHideElements)
                .toggle(function(){
                
                    $($.ZFM.selectors.showHideElements).html($.ZFM.images.openEye + openText);
                    $(contentToMove).animate({"left": "-=" + movementSize + "px"}, "slow");
                },
                function()
                {
                    $($.ZFM.selectors.showHideElements).html($.ZFM.images.closeCross + closeText);
                    $(contentToMove).animate({"left": "+=" + movementSize + "px"}, "slow");
                })
                .position({ // Put the tab to show / hide the sidebar, at the top left of the sidebar
                
                    my: "left top",            
                    at: "right top",
                    of: $($.ZFM.selectors.elementDraggableContainer)
                })
                .show();
                
                $("#sideBarHelp").tooltip();
            },

            
            
            
            /***********************************************************************
             * Footer's features
            ***********************************************************************/
            initFooter: function()
            {   
                $("#formMakerFooter .cell")
                .mouseenter(function(event){
                    $(this).children('.cellRightPart').show();
                })
                .mouseleave(function(event){
                    $(this).children('.cellRightPart').hide();
                });
            },

            
            /**
             * Make the trash droppable for the sortable elements.
             */
            initTrash : $($.ZFM.selectors.trash).droppable(
            {
                accept : "." + $.ZFM.subSelectors.sortableElement,
                activeClass : $.ZFM.className.sortableActiveClass,
                drop : function(event, ui)
                {
                    $.ElementManager.deleteElement(ui.draggable);
                }
            }),
            
            /**
             * Enable or disable the sortable list with the button in the footer
             */
            initSortable : $($.ZFM.selectors.toggleClassOrdering).parent().parent().toggle(
                // Create the sortable list, show the handles
                function()
                {
                    $("." + $.ZFM.className.sortableHandle).show();
                    $.UserInterface.enableSortable();
                    $($.ZFM.selectors.toggleClassOrdering).text("Lock order");
                    

                    $.UserInterface.enableDraggable();
                },
                // Destroy the sortable list, remove the handles
                function()
                {
                    $("." + $.ZFM.className.sortableHandle).hide();
                    $.UserInterface.disableSortable();
                    $($.ZFM.selectors.toggleClassOrdering).text("Unlock order");
                    
                    $.UserInterface.disableDraggable();
                }
            ),
            
            initBuildClass : $("#buildClass").parent().parent().click(function(event)
            {
                $.ajaxCalls.buildForm({formFilename: $.ElementManager.getFormFilename()});
            }),
            
            initFormSettings : $("#formSettingsLink").parent().parent().click(function(event)
            {
                $("#formSettings").dialog("open");
            }),
            
            initReloadForm: $("#reloadForm").parent().parent().click(function(event){
                $.ElementManager.loadForm();
                $($.ZFM.selectors.editElementDialogForm).dialog("close");
                $("#formSettings").dialog("close");
                $("#buildInfosDialog").dialog("close");
            }),

            initBuildInfoClick: $("#buildInfos").parent().parent().click(function(event){
                $("#buildInfosDialog").dialog("open");
            }),
            
            initPreviewForm : $("#previewForm").parent().parent().click(function(event)
            {
                $.ajaxCalls.previewForm({formFilename: $.ElementManager.getFormFilename()});   
            }),

            
            /***********************************************************************
             * Footer's links related features
            ***********************************************************************/

            /**
             * Make the area sortable, and update element's order in the form xml when the sortable is updated
             */
            enableSortable : function()
            {
                $($.ZFM.selectors.sortableArea).sortable(
                {
                    revert: true,
                    handle : "." + $.ZFM.className.sortableHandle,
                    placeholder : $.ZFM.className.sortablePlaceholder,
                    forcePlaceholderSize: true,
                    cursor: "move",
                    zIndex: 4000,
                    
                    // Save element's positions
                    update: function (event, ui)
                    {                    
                    	var orders = $($.ZFM.selectors.sortableArea).sortable("serialize");
                    	var param = 
                	    {
                            formFilename : $.ElementManager.getFormFilename(),
                	        orders: orders
                	    };

                    	$.ajaxCalls.updateOrders(param);
                    },
                    
                    beforeStop : function (event, ui)
                    {
                        // If we're adding a new element from drag
                        if ($(ui.helper).hasClass("formElement"))
                        {
                            // tmpSortableElement will be set after this function callback
                            $.ElementManager.createElement($(ui.helper), 'false');
                            ui.item.replaceWith($.ZFM.tmpSortableElement.el);
                            
                            $.ElementManager.addCMSortableElement($.ZFM.tmpSortableElement.id);
                            $("." + $.ZFM.className.sortableElement + '_' + $.ZFM.tmpSortableElement.id).effect('highlight');
                        }
                    }
                });
            },
            
            /**
             * Destroy the sortable list
             */
            disableSortable : function()
            {
                $($.ZFM.selectors.sortableArea).sortable('destroy');
            },
            
            /**
             * Display a message after the list has been updated
             */
            sortSortableCallback : function (data)
            {
                $.Tools.showMessage("success", "Element's order saved", 50);
            },
            
            
            
            buildClassCallback : function(data)
            {
                if (confirm('Do you want to try your form now ?'))
                {
                    window.location = "/Index/form-test/formFilename/" + data.filename;
                }
            },
            
            previewFormCallback : function(datas)
            {
                $("#previewFormDialog").html(datas);
                $("#previewFormDialog").dialog("open");                
            },
            
            initPreviewFormDialog: $("#previewFormDialog").dialog({
                autoOpen: false,
                resizable: true,
                modal: false,
                width: 800,
                height: 600
            }),
            
            initFormSettingsDialog: $("#formSettings").dialog({
                autoOpen: false,
                width: 600,
                height: 400
            }),
            
            initBuildInfosDialog: $("#buildInfosDialog").dialog({
                autoOpen: false,
                resizable: true,
                modal: false,
                width: 500,
                height: 450
            }),
            
            setWarnings: function(buildErrorsResult)
            {
                if (!(buildErrorsResult instanceof Array))
                {
                    var buildErrors = [];     
                    buildErrors.push(buildErrorsResult);
                }
                else
                    var buildErrors = buildErrorsResult;

                var dialog = $("#buildInfosDialog");

                for (var i in buildErrors)
                {
                    var cError = buildErrors[i];
                    for (var j in cError)
                    {
                        dialog.append("<h3>"+j+"</h3>");
                        
                        for (var k in cError[j])
                        {
                            var errorLine = '<div class="line">'
                                               + cError[j][k].msg + '<br/>'
                                               + '<a href="#" class="extraDatas">More</a>'
                                            + '</div>'
                                            + '<div class="cleaner"></div>';
                                                    
                            errorLine += '<div class="more">' + cError[j][k].extra + '</div>';
                            dialog.append(errorLine);                            
                        }
                    }
                }
                
                $("#buildInfosCounter").text(dialog.children('.line').length);
                $("#buildInfosDialog .line .extraDatas").toggle(
                function(event){
                    
                    $(this).text("Less").parent().next().next().fadeIn();
                },
                function(event){
                    $(this).text("More").parent().next().next().fadeOut();
                });
            },
            
            eraseBuildInfos: function()
            {
                $("#buildInfosDialog").html("");
            },
            
            
            
            /***********************************************************************
             * Other features
             ***********************************************************************/

            /**
             * 
             */
            initDropAreaHelper: $(".imgHelper").tooltip(),
            
            /**
             * Initilize the dialog to edit elements
             */
            initDialog : $($.ZFM.selectors.editElementDialogForm).dialog(
            {
                autoOpen : false,
                resizable : true,
                modal: false,
                width : 800,
                height : 570
            }),

            /**
             * Transform div into tabs in the edit element dialog
             */
            initTabs : $($.ZFM.subSelectors.editElementTabs).tabs(
            {

            }),
            
           
            /**
             * Prepare the div to display the ajax error in a dialog form
             */
            initAjaxError : $($.ZFM.selectors.ajaxError).dialog({
                autoOpen: false,
                width: 850,
                height: 600
            })
        };


        $.UserInterface.disableDraggable();
        $.UserInterface.initFooter();
        $.UserInterface.initSidebar();
    })(jQuery);
});
