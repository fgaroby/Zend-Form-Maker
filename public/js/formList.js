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

        $.formList =
        {
            addSubmitCheck: function(event)
            {
                var fieldValue = $("#newFormFilename").val();
                fieldValue = jQuery.trim(fieldValue);
                
                var isAllowed = fieldValue.match(/^[A-Za-z0-9_-]+$/);

                if (isAllowed)
                {
                    return true;
                }
                else
                {
                    alert("Please fulfill the name with alphanumeric characters.");
                    return false;
                }
            },
            
            renameSubmitCheck: function(event)
            {
                var fieldValue = $("#formFilename").val();
                fieldValue = jQuery.trim(fieldValue);

                var isAllowed = fieldValue.match(/^[A-Za-z0-9_-]+$/);

                if (isAllowed)
                {
                    return true;
                }
                else
                {
                    alert("Please fulfill the name with alphanumeric characters.");
                    return false;
                }
            },
            
            deleteClick : function (event)
            {
                var formName = $(this).parent().children(":hidden").val();
                
                return (confirm("Do you really want to delete the form " + formName + " ?"));              
            }
        };
        
        // Add form management
        $("#newFormFilename").addClass("unselectedField")
        .focus(function(event){
            $(this).val("").removeClass("unselectedField");
        })
        .focusout(function(event){
            that = $(this);
            
            if (that.val() == "")
                that.addClass("unselectedField").val("form name");
        });
        $("#addFormLink a").click(function(event){
            $("#createForm").fadeToggle().find("oldName").val("");
        });
        
        // Rename management
        $("#formList .actions a").click(function(event){
            event.stopImmediatePropagation();
            event.preventDefault();
            
            var formName = $(this).parent().parent().find(".link a").text();
            
            $("#createForm").show();
            $("#newFormFilename")
            .focus()
            .val(formName);
            $("#oldName").val(formName);
        });
        
        
        
        // Forms submit management
        $("#createFormXml").submit($.formList.addSubmitCheck);
        $("#renameFilename").submit($.formList.renameSubmitCheck);
        $("#formList .actions #sendDeleteForm").click($.formList.deleteClick);
        
        
        
        // Table list management
        $("#formList .cell a").click(function(event){event.stopImmediatePropagation();});
        $("#formList tr").
        mouseenter(function(event){
            $(this).find(".actions a, .actions input").show();
        })
        .mouseleave(function(event){
            $(this).find(".actions a, .actions input").hide();
        })
        .click(function(event){
            var url = $(this).find(".link a").prop("href");
            console.log(url);
            window.location = url;
        });
        
    })(jQuery);
});
