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

        $.Tools =
        {
            displayAjaxLoader: true,
            
            /**
             * This function check in "caller" css's classes and search for
             * the value associated to "identifier", then return it.
             * 
             * @param caller :
             *            A jquery selected object
             * @param identifier :
             *            What to search in caller classes
             * @returns The value associated to identifier in caller class
             *          if success, null if identifier isn't found.
             */
            getDataInClass : function(caller, identifier)
            {
                var result = null;

                var arrClasses = caller.attr("class").split(" ");
                for (i = 0; i < arrClasses.length; i++)
                {
                    var regex = new RegExp(identifier + "_(.+)");
                    var results = regex.exec(arrClasses[i]);

                    if (results != null)
                    {
                        result = results[1];
                        break;
                    }
                }

                return result;
            },

            /**
             * Send an ajax request using post and parameters given.
             * 
             * @param url : Url of the page to request
             * @param param :Parameters to pass
             * @param funcResult : Callback function if sucess
             * @param dataType : Type of the expected datas
             * @param failCallback : Function to call if the request fail
             * @param async : Is the request asynchronus ? Default : true
             */
            sendAjax : function(url, param, funcResult, dataType, failCallback, async)
            {
                $.ajax(
                {
                    type : "POST",
                    url : url,
                    data : param,
                    dataType : dataType,
                    async: (async == "false") ? false : true,
                        
                    success : function(data)
                    {
                        // If we proceeded to correction on the build statement
                        // because of the user's entry
                        if (data.buildErrors != null)
                        {
                            $.UserInterface.setWarnings(data.buildErrors);
                        }
                        
                        // If an error happened in the code
                        if (data.error != null)
                        {
                            $.Tools.showMessage("error", data.error);
                            if (typeof(failCallback) != "undefined")
                                failCallback();
                        } 
                        
                        // Everything is fine, let's call the callback !
                        else
                        {
                            funcResult(data);
                        }
                    },

                    error : function(jqXHR)
                    {
                        
                        console.log("error in ajax request :");
                        console.log("url : " + url);
                        console.log("dataType : " + dataType);
                        console.log("param : ");
                        console.log(param);
                        console.log("funcResult : ");
                        console.log(funcResult);
                         
                        $.Tools.showMessage("error", "An error occured in the ajax request, check the console for details");
                        
                        $("#ajaxErrorDetails").html(jqXHR.responseText).dialog("open");

                        if ($.Tools.displayAjaxLoader)
                            $("#ajaxLoader").hide();
                    },

                    beforeSend : function(jqXHR, settings)
                    {
                        if ($.Tools.displayAjaxLoader)
                            $("#ajaxLoader").show();
                    },

                    complete : function(data, statusCode)
                    {
                        if ($.Tools.displayAjaxLoader)
                            $("#ajaxLoader").hide();
                    }
                });
            },

            /**
             * Display a message using toastmessage plugin
             * @param type
             * @param msg
             */
            showMessage : function(type, msg, stayTime)
            {
                if (stayTime == null)
                    stayTime = 1000;

                if (type == "error")
                    stayTime = 8000;
                
                $().toastmessage("showToast",
                {
                    text : msg,
                    sticky : false,
                    position : "top-center",
                    type : type,
                    stayTime : stayTime
                });
            },
            
            alert: function(title, msg)
            {
                $( "#dialogAlertMsg" ).html(msg);
                $( "#dialogAlertMsg" ).attr("title", title);
                $( "#dialogAlertMsg" ).dialog({
                    modal: true,
                    buttons: 
                    {
                        Ok: function() 
                        {
                            $( this ).dialog( "close" );
                        }
                    }
                });                
            }
        };
    })(jQuery);
});
