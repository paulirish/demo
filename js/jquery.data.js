

// $.fn.data 2.0 by Paul Irish
// MIT license
// use the same data() api to retrieve data from the DOM
// proposed replacement for metadata plugin


// usage: 
// store DOM element data in the data attribute in json format:
//   <div id="foo" data-json="{name:'Paul',age:27,baller:true}">
//  or store in html5 data- attribute style
//   <div id="foo" data-name="'Paul'" data-age="27" data-baller="true">
// retrieve data with data(), just like normal
//   $('#foo').data('name');  // ==> Paul

// also data-attributes must be all lower-case and strings must be inside single quotes, too.
// double quotes, BEEYOTCH.

(function($){

  var _data = $.fn.data, datajQueryAttr = 'data-json', undefined;
  
  $.fn.data = function(key,value){
    
    // store right into the cache
    if (value !== undefined) return _data.call(this,key,value);
    
    // we are retrieving now. we'll check cache first
    var ret = _data.call(this,key);
    if (ret !== undefined) return ret;
    
    // if there's data on the DOM we'll throw it in the cache.
    this.each(function(){
        
      var $elem = $(this), data = $elem.attr(datajQueryAttr);
      
      // if we've got data-json stuff...
      if (data !== undefined){
        
          if (! /^(?:\{|\[)/.test(data)) { data = '{'+data+'}';  }
          try{
            data = (window.JSON && JSON.parse || eval)('('+data+')');
          } catch(e){ 
            throw('error parsing '+datajQueryAttr+' JSON: '+data);  
          }
          
      } else {
        
          data = {};
          
          // nothing found in data-json, lets stash whats in data-attrs
          data[key] = (window.JSON && JSON.parse || eval)( '('+ this.getAttribute('data-'+key) +')');  // parens?
          this.removeAttribute('data-'+key);
      }
        
      // store what we found
      $.each(data,function(k,v){
          // unless there's something already in there
          _data.call($elem,k) === undefined && _data.call($elem,k,v);      
      });
      $elem.removeAttr(datajQueryAttr); 
    });
    
    // we return whatever is in the cache
    return _data.call(this,key);
    
  };
      

})(jQuery);

