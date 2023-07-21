class GeoJsonLayer {
  constructor() {
    this.options;
    this.url;
    this.name;
    this.styleFunction;
    this.filterFunction;
    this.pointToLayerFunction;
    this.legendOrder;
    this.leafletGeoJsonLayer;
  }
  setLeafletGeoJsonLayer() {
    $.ajax({
      type: "GET",
      url: this.url,
      success: (data) => {
        this.leafletGeoJsonLayer = L.geoJSON(data, {
          style: this.styleFunction,
          filter: this.filterFunction,
          pointToLayer: this.pointToLayerFunction,
          onEachFeature: function (feature, layer) {
            layer.on("click", function (e) {
              var popupTitle = "<h4>" + title + "</h4><table>";
              var popupHTML = "";
              var geometry = feature.geometry;
              for (let i in feature.properties) {
                popupHTML +=
                  "<tr><td>" +
                  i.replace("_", " ") +
                  "</td><td>" +
                  feature.properties[i] +
                  "</td></tr>";
              }
              popupHTML += "</table>";
              var popupObj = {};
              popupObj.geometry = geometry;
              popupObj.geometryName = "GEOMETRY";
              popupObj.popupContent = popupTitle + popupHTML;
              popupObj.popupHtml = popupHTML;
              that.popupArray.push(popupObj);
              if (that.popupOpen == false) {
                that.popup = L.popup({ maxWidth: 800 })
                  .setLatLng(e.latlng)
                  .setContent(
                    that.addPopupLinks(
                      that.popupArray[that.popupIndex].popupContent
                    )
                  )
                  .openOn(that.map);
                var position = L.DomUtil.getPosition(that.popup.getElement());
                L.DomUtil.setPosition(that.popup.getElement(), position);
                var draggable = new L.Draggable(that.popup.getElement());
                draggable.enable();
                that.addPopupLayer();
              }
            });
          },
        });
        wfstLayers[key]["geoJsonLayer"] = geoJsonLayer;
        that.wfstLayers.push(wfstLayers[key]);
        if (wfstLayers[key].options.visible) {
          geoJsonLayer.addTo(that.map);
        }
        if (geometryType == "point") {
          that.generateSvgFromStyle(pointToLayerFunction, geometryType);
        } else if (geometryType == "line" || geometryType == "polygon") {
          console.log(that.generateSvgFromStyle(styleFunction, geometryType));
        }
      },
      error: function (data) {
        console.log("Error retrieving external geojson layer.");
      },
    });
  }
  setSvgLegendGraphic() {
    return true;
  }
}
