(function(){
"use strict";

  var AppResponse = function(jsonResponse, jsonTextResponse){
    if ( !(this instanceof AppResponse) ) {
      return new AppResponse(jsonResponse, jsonTextResponse);
    }
    this.version = 0.1;

    // PROPERTIES
    var $ = this;
    
    $.appJsonResponse = jsonResponse ? jsonResponse : (jsonTextResponse ? JSON.parse(jsonTextResponse) : {});
    
    $.status = {
        isError : function(){
            return ($.appJsonResponse.response.status === 'Error' ? true : false);
        },
        isSuccess : function(){
            return ($.appJsonResponse.response.status === 'Success' ? true : false);
        }
    };

    $.message = (typeof $.appJsonResponse.response.message !== 'undefined') ? $.appJsonResponse.response.message : null;
    $.data = (typeof $.appJsonResponse.response.data !== 'undefined') ? $.appJsonResponse.response.data : null;

    return(this);
  };

  // Node.js-style export for Node and Component
  if (typeof module != 'undefined') {
    module.exports = AppResponse;
  } else if (typeof define === "function" && define.amd) {
    // AMD/requirejs: Define the module
    define(function(){
      return AppResponse;
    });
  } else {
    // Browser: Expose to window
    window.AppResponse = AppResponse;
  }

})();