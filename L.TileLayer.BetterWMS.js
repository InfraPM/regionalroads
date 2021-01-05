L.TileLayer.BetterWMS = L.TileLayer.WMS.extend({
  
  onAdd: function (map) {
    // Triggered when the layer is added to a map.
    //   Register a click listener, then do all the upstream WMS things
    L.TileLayer.WMS.prototype.onAdd.call(this, map);
    map.on('click', this.getFeatureInfo, this);
  },
  
  onRemove: function (map) {
    // Triggered when the layer is removed from a map.
    //   Unregister a click listener, then do all the upstream WMS things
    L.TileLayer.WMS.prototype.onRemove.call(this, map);
    map.off('click', this.getFeatureInfo, this);
  },
  
  getFeatureInfo: function (evt) {
      // Make an AJAX request to the server and hope for the best
      //return promise
      return new Promise((resolve, reject)=>{
    var url = this.getFeatureInfoUrl(evt.latlng),
        showResults = L.Util.bind(this.showGetFeatureInfo, this);
    $.ajax({
      url: url,
      success: function (data, status, xhr) {
          var err = typeof data === 'string' ? null : data;
	  //edit data to show results of foreign keys (not ids)
          showResults(err, evt.latlng, data);
	  resolve(true);
      },
	error: function (xhr, status, error) {
	    showResults(error);
	    reject(false);
      }
    });
      });
  },
  
  getFeatureInfoUrl: function (latlng) {
      // Construct a GetFeatureInfo request URL given a point
      var point = this._map.latLngToContainerPoint(latlng, this._map.getZoom()),

        size = this._map.getSize(),
        params = {
          request: 'GetFeatureInfo',
          service: 'WMS',
          srs: 'EPSG:4326',
          styles: this.wmsParams.styles,
          transparent: this.wmsParams.transparent,
          version: this.wmsParams.version,      
          format: this.wmsParams.format,
          bbox: this._map.getBounds().toBBoxString(),
          height: size.y,
          width: size.x,
            layers: this.wmsParams.layers,
          query_layers: this.wmsParams.layers,
            info_format: 'text/html',
	    token: this.wmsParams.token,
	    lookupvalues: 'true'
        };
      //console.log(point);
      params[params.version === '1.3.0' ? 'i' : 'x'] = Math.round(point.x);
      params[params.version === '1.3.0' ? 'j' : 'y'] = Math.round(point.y);
    
    return this._url + L.Util.getParamString(params, this._url, true);
  },
  
    showGetFeatureInfo: function (err, latlng, content) {
	var evt = document.createEvent("Event");
	evt.initEvent("getFeatureInfo",true,true);
	evt.content = content;
	evt.err = err;
	evt.latlng = latlng;
	evt.this = this;
	document.dispatchEvent(evt);
	/*if (err) { console.log(err); return; } // do nothing if there's an error
      //console.log(content.length);      
      if (content.length==0){
	  return;
      }
      else if (content == `<?xml version="1.0" encoding="UTF-8" standalone="no"?><!DOCTYPE ServiceExceptionReport SYSTEM "http://regionalroads.com:8080/geoserver/schemas/wms/1.1.1/WMS_exception_1_1_1.dtd"> <ServiceExceptionReport version="1.1.1" >   <ServiceException code="OperationNotSupported" locator="QUERY_LAYERS">
      Either no layer was queryable, or no layers were specified using QUERY_LAYERS
</ServiceException></ServiceExceptionReport>`){
	  return;
      }
      if (this.options.externalPopup==false){
    // Otherwise show the content in a popup, or something.
      L.popup({ maxWidth: 800})
	  .setLatLng(latlng)
	  .setContent(content)
	  .openOn(this._map);
      }
      else{	  
	  $(this.options.externalPopupDiv).empty();
	  $(this.options.externalPopupDiv).append(content);
      }*/
  }
});

L.tileLayer.betterWms = function (url, options) {
  return new L.TileLayer.BetterWMS(url, options);  
};
