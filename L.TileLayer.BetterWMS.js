L.TileLayer.BetterWMS = L.TileLayer.WMS.extend({
  initialize: function (url, options, appToken) {
    this.popupEnabled = true;
    this.appToken = appToken;
    options.token = this.appToken.token;
    options.updateWhenIdle = true;
    options.updateWhenZooming = false;
    options.keepBuffer = 0;
    //this.wmsParams.height = this._map.getSize().y;
    //this.wmsParams.width = this._map.getSize().x;
    //options.bounds = this._map.getBounds();
    if (options.mapDivId != undefined) {
      this.mapDivId = options.mapDivId;
    }
    //options.tileSize = 1024;
    if (options.type == "external/wms") {
      delete options.token;
    }
    L.TileLayer.WMS.prototype.initialize.call(this, url, options);
  },
  _update: function (center) {
    this.appToken.check().then((data) => {
      //check for a new token before tile load
      this.options.token = this.appToken.token;
      this.wmsParams.token = this.appToken.token;
      if (this.wmsParams.keepCurrent) {
        this.wmsParams.fake = Date.now();
      }
      L.TileLayer.WMS.prototype._update.call(this, center);
    });
  },
  onAdd: function (map) {
    L.TileLayer.WMS.prototype.onAdd.call(this, map);
    map.on("click", this.getFeatureInfo, this);
  },
  onRemove: function (map) {
    // Triggered when the layer is removed from a map.
    //   Unregister a click listener, then do all the upstream WMS things
    L.TileLayer.WMS.prototype.onRemove.call(this, map);
    map.off("click", this.getFeatureInfo, this);
  },
  togglePopup: function (map) {
    if (this.popupEnabled) {
      map.off("click", this.getFeatureInfo, this);
    } else {
      map.on("click", this.getFeatureInfo, this);
    }
  },
  getFeatureInfo: function (evt) {
    this.appToken.check().then((data) => {
      // Make an AJAX request to the server and hope for the best
      //return promise
      var postData = { token: this.appToken.token };
      if (this.options.type == "external/wms") {
        var postDataString = "";
      } else {
        var postDataString = JSON.stringify(postData);
      }

      return new Promise((resolve, reject) => {
        var url = this.getFeatureInfoUrl(evt.latlng),
          showResults = L.Util.bind(this.showGetFeatureInfo, this);
        var that = this;
        $.ajax({
          type: "POST",
          data: postDataString,
          //contentType: "json",
          url: url,
          success: function (data, status, xhr) {
            var err = typeof data === "string" ? null : data;
            //edit data to show results of foreign keys (not ids)
            showResults(err, evt.latlng, data);
            resolve(true);
          },
          error: function (xhr, status, error) {
            showResults(error);
            //that.appToken.check();
            reject(false);
          },
        });
      });
    });
  },
  getFeatureInfoUrl: function (latlng) {
    // Construct a GetFeatureInfo request URL given a point
    if (this.wmsParams.cql_filter == undefined) {
      this.wmsParams.cql_filter = "1=1";
    }
    if (this.wmsParams.feature_count == undefined) {
      this.wmsParams.feature_count = 1;
    }
    var point = this._map.latLngToContainerPoint(latlng, this._map.getZoom()),
      size = this._map.getSize(),
      params = {
        request: "GetFeatureInfo",
        service: "WMS",
        srs: "EPSG:4326",
        styles: this.wmsParams.styles,
        transparent: this.wmsParams.transparent,
        version: this.wmsParams.version,
        format: "img/png",
        bbox: this._map.getBounds().toBBoxString(),
        height: size.y,
        width: size.x,
        layers: this.wmsParams.layers,
        query_layers: this.wmsParams.layers,
        info_format: "application/json",
        cql_filter: this.wmsParams["cql_filter"],
        token: this.appToken.token,
        lookupvalues: "true",
        feature_count: this.wmsParams.feature_count,
        buffer: 7,
      };
    if (this.options.type == "external/wms") {
      delete params["token"];
    }
    params[params.version === "1.3.0" ? "i" : "x"] = Math.round(point.x);
    params[params.version === "1.3.0" ? "j" : "y"] = Math.round(point.y);

    return this._url + L.Util.getParamString(params, this._url, true);
  },

  showGetFeatureInfo: function (err, latlng, content) {
    var evt = document.createEvent("Event");
    evt.initEvent("getFeatureInfo", true, true);
    evt.content = content;
    evt.err = err;
    evt.latlng = latlng;
    evt.this = this;
    document.getElementById(this._map._container.id).dispatchEvent(evt);
    /*if (this.mapDivId != undefined) {
      document.getElementById(this.mapDivId).dispatchEvent(evt);
    } else {
      document.dispatchEvent(evt);
    }*/
  },
});

L.tileLayer.betterWms = function (url, options, appToken) {
  return new L.TileLayer.BetterWMS(url, options, appToken);
};
