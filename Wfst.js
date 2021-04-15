class WfstLayer {
  //Data structure to enable WFST editing of geographic features
  constructor(name, appToken, baseAPIURL) {
    this.appToken = appToken;
    this.appToken.check().then((data) => {
      this.name = name; //name of dataset in geoserver / postgis database
      this.displayName; //for display in legend, edit controls, etc.
      //this.token = token;//user's unique token
      this.token = this.appToken.token;
      this.baseAPIURL = baseAPIURL; //base URL for API calls
      this.wmsLayer; //wmsLayer object
      this.editWmsLayer; //wmsLayer object 'edit view'
      this.foreignKeyList; //store results from call to foreignkey API
      this.addAttributesFormId; //default
      this.editAttributesFormId; //default
      this.editable = false; //default
      this.projection = "4326"; //default
      this.geometryField = "Shape"; //default
      this.fidField = "OBJECTID"; //default
      this.featureType; //line, point, polygon, etc.
      this.memberType; //member type for gml represenation
      this.baseFeatureType; //base feature type for gml representation
      this.curDeleteId;
      this.curEditId;
      this.describeFeature().then((data) => {
        this.getFeatureType();
      });
      this.getForeignKeyList();
    });
  }
  //getFeature().then getBounds()
  getBounds() {
    return new Promise((resolve, reject) => {
      if (this.zoomTo) {
        this.appToken.check().then((msg) => {
          if (this.wmsLayer.options.cql_filter == undefined) {
            var cqlFilter = "1=1";
          } else {
            var cqlFilter = this.wmsLayer.options.cql_filter;
          }
          var url =
            this.baseAPIURL +
            "/simplewfs/?&service=wfs&request=GetFeature&typeNames=" +
            this.name +
            "&outputFormat=application%2Fjson&spatialdata=" +
            this.name +
            "&cql_filter=" +
            cqlFilter +
            "&token=" +
            this.token;
          var that = this;
          $.ajax({
            type: "GET",
            url: url,
            //contentType: "json",
            success: function (data) {
              if (data.numberReturned > 0) {
                var geoJsonLayer = L.GeoJSON.geometryToLayer(
                  data["features"][0]
                );
                that.bounds = geoJsonLayer.getBounds();
              } else {
                that.bounds = undefined;
              }
              resolve(that.bounds);
            },
            error: function (data) {
              reject(false);
            },
          });
        });
      } else {
        this.bounds = undefined;
        resolve(this.bounds);
      }
    });
  }
  getIDFromPopup(popupHTML) {
    //given a wms popup return the id of the clicked feature
    var parser = new DOMParser();
    var xmlDoc = parser.parseFromString(popupHTML, "text/xml");
    var s = new XMLSerializer();
    var a = xmlDoc.getElementsByTagName("tr");
    for (var i = 0; i < a.length; i++) {
      var node = a[i];
      var nodeString = s.serializeToString(node);
      if (nodeString.includes(this.fidField)) {
        return nodeString.replace(/\D/g, "");
      }
    }
  }
  edit(set = undefined) {
    //set or return edit status of instance
    if (set != undefined) {
      this.editable = set;
    } else {
      return this.editable;
    }
  }
  getComments(commentId = undefined) {
    //ajax request to get all comments for a particular feature
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var url = this.baseAPIURL + "/comment/?m=read";
        var postData = [
          {
            data: this.name,
            token: this.appToken.token,
            featureId: this.curId,
            featureIdField: this.fidField,
          },
        ];
        if (commentId != undefined) {
          postData[0]["commentId"] = commentId;
        }
        var postDataString = JSON.stringify(postData);
        var that = this;
        $.ajax({
          type: "POST",
          url: url,
          data: postDataString,
          //contentType: "json",
          success: function (data) {
            resolve(data);
          },
          error: function (data) {
            reject(data);
          },
        });
      });
    });
  }
  updateComment(commentId, comment, commentStatus, commentType) {
    //ajax request to update a comment
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var url = this.baseAPIURL + "/comment/?m=update";
        var postData = [
          {
            token: this.appToken.token,
            commentId: commentId,
            comment: comment,
            commentStatus: commentStatus,
            commentType: commentType,
          },
        ];
        var that = this;
        $.ajax({
          type: "POST",
          url: url,
          data: JSON.stringify(postData),
          //contentType: "json",
          success: function (data) {
            resolve(data);
          },
          error: function (data) {
            console.log("Error updating comment");
            reject(data);
          },
        });
      });
    });
  }
  addComment(comment, commentStatus, commentType, replyId = undefined) {
    //ajax request to add a comment
    return new Promise((resolve, reject) => {
      this.appToken.check().then((data) => {
        var url = this.baseAPIURL + "/comment/?m=add";
        var postRequest = [
          {
            data: this.name,
            token: this.appToken.token,
            featureId: this.curId,
            comment: comment,
            commentStatus: commentStatus,
            commentType: commentType,
          },
        ];
        if (replyId != undefined) {
          postRequest[0]["replyId"] = replyId;
        }
        var that = this;
        $.ajax({
          type: "POST",
          url: url,
          data: JSON.stringify(postRequest),
          //contentType: "json",
          success: function (data) {
            resolve(data);
          },
          error: function (data) {
            reject(data);
          },
        });
      });
    });
  }
  getEditCommentsHTML(commentsJson) {}
  getFeatureType() {
    //set the featureType of the instance
    var msg = this.describe;
    var s = new XMLSerializer();
    var a = msg.getElementsByTagName("xsd:element");
    var geometryTypes = [
      "gml:PointPropertyType",
      "gml:MultiPointPropertyType",
      "gml:LineString",
      "gml:MultiLineString",
      "gml:Polygon",
      "gml:MultiPolygon",
      "gml:MultiSurfacePropertyType",
      "gml:MultiCurvePropertyType",
    ];
    for (var i = 0; i < a.length; i++) {
      var node = a[i];
      if (geometryTypes.includes(node.attributes.type.value)) {
        this.featureType = node.attributes.type.value;
      }
    }
  }
  setGeometryField() {
    //set the geometryField of the instance
    var xmlDoc = this.describe;
    var a = xmlDoc.getElementsByTagName("xsd:element");
    var s = new XMLSerializer();
    for (var i = 0; i < a.length; i++) {
      var node = a[i];
      if (node.attributes.type.value == this.featureType) {
        this.geometryField = node.attributes.name.value;
      }
    }
  }
  setFidField() {
    //set the fidField of the instance based on the describe object of the instance
    //fid field must be id or objectid (not case sensitive)
    var xmlDoc = this.describe;
    var a = xmlDoc.getElementsByTagName("xsd:element");
    var s = new XMLSerializer();
    for (var i = 0; i < a.length; i++) {
      var node = a[i];
      if (
        node.attributes.name.value.toLowerCase() == "id" ||
        node.attributes.name.value.toLowerCase() == "objectid"
      ) {
        this.fidField = node.attributes.name.value;
      }
    }
  }
  createFormArray(formElement) {
    var formArray = [];
    var ignoreList = ["submit", "button"];
    for (let i = 0; i < formElement[0].elements.length; i++) {
      let curElement = formElement[0].elements[i];
      if (!ignoreList.includes(curElement.type.toLowerCase())) {
        let curObject = {
          name: curElement.name,
          value: curElement.value,
          type: curElement.type,
        };
        formArray.push(curObject);
      }
    }
    return formArray;
  }
  addFeature(editLayer) {
    //add a feature to the instance using a wfst request
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var formElement = $("#addAttributesForm");
        var projection = this.projection;
        var geomField = this.geometryField;
        var refresh = $("#" + formElement.attr("id"));
        //var formArray = refresh.serializeArray();
        var formArray = this.createFormArray(refresh);
        var xmlString = this.buildXMLRequest("Insert", editLayer, formArray);
        var url = this.baseAPIURL + "/simplewfs/";
        var that = this;
        $.ajax({
          type: "POST",
          url: url,
          data: xmlString,
          dataType: "xml",
          success: function (msg) {
            var stringMsg = new XMLSerializer().serializeToString(msg);
            resolve(msg);
          },
          error: function (msg) {
            reject(msg);
          },
        });
      });
    });
  }
  updateFeature(editLayer) {
    //update the feature using a wfst request
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var data = this.name;
        var method = "Update";
        var projection = this.projection;
        var geomField = this.geometryField;
        //var formArray = $("#editAttributesForm").serializeArray();
        try {
          var formArray = this.createFormArray($("#editAttributesForm"));
        } catch (e) {
          var formArray = [];
        }
        var xmlString = this.buildXMLRequest("Update", editLayer, formArray);
        var url = this.baseAPIURL + "/simplewfs/";
        var that = this;
        $.ajax({
          type: "POST",
          url: url,
          data: xmlString,
          dataType: "xml",
          success: function (msg) {
            var stringMsg = new XMLSerializer().serializeToString(msg);
            resolve(msg);
          },
          error: function (msg) {
            reject(msg);
          },
          always: function (msg) {
            that.curEditId = undefined;
          },
        });
      });
    });
  }
  deleteFeature() {
    //delete a feature enitrely using wfs-t
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var typeName = this.name;
        var idField = this.fidField;
        var xmlString =
          `<wfs:Transaction service="WFS" version="1.0.0"
  xmlns:cdf="http://www.opengis.net/cite/data"
  xmlns:ogc="http://www.opengis.net/ogc"
  xmlns:wfs="http://www.opengis.net/wfs"
  xmlns:topp="http://www.openplans.org/topp">
<token>` +
          this.appToken.token +
          `</token>
  <wfs:Delete typeName="` +
          typeName +
          `">
    <ogc:Filter>
      <ogc:PropertyIsEqualTo>
        <ogc:PropertyName>` +
          idField +
          `</ogc:PropertyName>
        <ogc:Literal>` +
          this.curDeleteId +
          `</ogc:Literal>
      </ogc:PropertyIsEqualTo>
    </ogc:Filter>
  </wfs:Delete>
</wfs:Transaction>`;
        var url = this.baseAPIURL + "/simplewfs/";
        var that = this;
        $.ajax({
          type: "POST",
          url: url,
          data: xmlString,
          dataType: "xml",
          success: function (msg) {
            var stringMsg = new XMLSerializer().serializeToString(msg);
            resolve(msg);
          },
          error: function (msg) {
            reject(msg);
          },
          always: function (msg) {
            that.curDeleteId = undefined;
          },
        });
      });
    });
  }
  describeFeature() {
    //call wfs DescribeFeatureType to get all fields and types
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var xmlString =
          '<?xml version="1.0" encoding="UTF-8"?><token>' +
          this.appToken.token +
          "</token>";
        //var xmlString = '<token>' + this.appToken.token + '</token>';
        //let parser = new DOMParser();
        //let xmlDoc = parser.parseFromString(xmlString,"text/xml");
        var self = this;
        $.ajax({
          type: "POST",
          url:
            self.baseAPIURL +
            "/simplewfs/?service=wfs&version=2.0.0&request=DescribeFeatureType&typeNames=" +
            self.name +
            "&spatialdata=" +
            self.name,
          //contentType: "text",
          dataType: "xml",
          data: xmlString,
          beforeSend: function () {
            self.appToken.check().then((data) => {
              //refresh token if needed
            });
          },
          success: function (msg) {
            self.describe = msg;
            resolve(true);
          },
          error: function (msg) {
            reject(false);
          },
        });
      });
    });
  }
  getForeignKeyList() {
    //get the foreign key list associated with the instance
    //this depends on the foreignkeys API which returns a structured list of all foreign keys and their associated tables which are linked to this dataset
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var url = this.baseAPIURL + "/foreignkeys/?table=" + this.name;
        var postData = { token: this.appToken.token };
        var postDataString = JSON.stringify(postData);
        var that = this;
        $.ajax({
          type: "POST",
          data: postDataString,
          url: url,
          dataType: "json",
          success: function (fkJson) {
            that.foreignKeyList = fkJson;
            resolve(fkJson);
          },
          error: function (fkJson) {
            reject(fkJson);
          },
        });
      });
    });
  }
  foreignKeyField(fkJson, fieldName) {
    //return true if a fieldName has an associated foreign key
    for (var i = 0; i < fkJson.length; i++) {
      if (fkJson[i]["primaryColumnName"] == fieldName) {
        return true;
      }
    }
    return false;
  }
  getForeignKeyDropDown(fkJson, fieldName, defaultValue = null, required) {
    //generate a dro down list based on the foreign keys of the dataset
    var dropDownHTML =
      '<select id="' +
      fieldName +
      '" name="' +
      fieldName +
      '" ' +
      required +
      ">";
    for (var i = 0; i < fkJson.length; i++) {
      if (fkJson[i]["primaryColumnName"] == fieldName) {
        for (var j = 0; j < fkJson[i]["values"].length; j++) {
          if (defaultValue === null) {
            dropDownHTML +=
              '<option value="' +
              fkJson[i]["values"][j]["id"] +
              '">' +
              fkJson[i]["values"][j]["value"] +
              "</option>";
          } else {
            if (defaultValue == fkJson[i]["values"][j]["id"]) {
              dropDownHTML +=
                '<option value="' +
                fkJson[i]["values"][j]["id"] +
                '" selected="selected">' +
                fkJson[i]["values"][j]["value"] +
                "</option>";
            } else {
              dropDownHTML +=
                '<option value="' +
                fkJson[i]["values"][j]["id"] +
                '">' +
                fkJson[i]["values"][j]["value"] +
                "</option>";
            }
          }
        }
      }
    }
    dropDownHTML += "</select>";
    return dropDownHTML;
  }
  convertDateTime(msg) {
    var htmlString = $(msg);
    htmlString.find("td").each(function () {
      var tableData = $(this).html();
      if (isNaN(Date.parse(tableData)) == false) {
        tableData += "+00";
        var dateText = new Date(tableData);
        if (dateText != "Invalid Date") {
          $(this).html(dateText.toString());
        }
      }
    });
    var returnString = "";
    htmlString.each(function () {
      if ($(this).prop("outerHTML") != undefined) {
        returnString += $(this).prop("outerHTML");
      }
    });
    return returnString;
  }
  getPopupForm() {
    //generate the Add Feature popup form
    var msg = this.describe;
    var fkJson = this.foreignKeyList;
    var htmlForm =
      "<h2>Add Feature - " +
      this.displayName +
      '</h2><form id="addAttributesForm" action="nada" onsubmit="return false">';
    var xmlDoc = msg;
    var s = new XMLSerializer();
    var a = xmlDoc.getElementsByTagName("xsd:element");
    for (var i = 0; i < a.length; i++) {
      var node = a[i];
      if (s.serializeToString(node.attributes.name) != this.name) {
        var curName = s.serializeToString(node.attributes.name);
        var curLabel = curName + "Label";
        var curType = s.serializeToString(node.attributes.type);
        var curNillable = s.serializeToString(node.attributes.nillable);
        if (curNillable == "false") {
          var curRequired = "required";
        } else {
          var curRequired = "";
        }
        curType = curType.substr(curType.indexOf(":") + 1, curType.length);
        if (curName == this.fidField || curName == this.geometryField) {
          htmlForm +=
            '<label for="' +
            curName +
            '" style="display:none">' +
            curName +
            "</label>";
        } else {
          htmlForm +=
            '<label for="' + curName + '">' + curName + "</label><br>";
        }
        if (this.foreignKeyField(fkJson, curName) == false) {
          var formType = this.getFormType(curType, curName);
          if (formType == "hidden") {
            htmlForm +=
              '<input type="' +
              formType +
              '" id="' +
              curName +
              '" name="' +
              curName +
              '" ' +
              curRequired +
              ">";
          } else if (formType == "textarea") {
            htmlForm +=
              '<textarea id="' +
              curName +
              '" name="' +
              curName +
              '" rows="5" cols="45"' +
              curRequired +
              "></textarea><br>";
          } else {
            htmlForm +=
              '<input type="' +
              formType +
              '" id="' +
              curName +
              '" name="' +
              curName +
              '" ' +
              curRequired +
              "><br>";
          }
        } else {
          htmlForm += this.getForeignKeyDropDown(fkJson, curName, curRequired);
        }
      }
    }
    htmlForm += '<button id="addAttributesButton">Save</button>';
    htmlForm += "</form>";
    return htmlForm;
  }
  getFormType(type, name) {
    //translate wfs type to form input type
    if (name == this.fidField) {
      return "hidden";
    } else if (name == this.geometryField) {
      return "hidden";
    } else if (name.toLowerCase().includes("description")) {
      return "textarea";
    } else if (name.toLowerCase().includes("link")) {
      return "textarea";
    } else {
      if (type == "string") {
        return "text";
      } else if (type == "dateTime") {
        return "datetime-local";
      } else if (type == "date") {
        return "date";
      } else if (type == "long") {
        return "number";
      } else if (type == "short") {
        return "number";
      } else if (type == "double") {
        return "number";
      }
    }
  }
  buildPointFeature(editLayer, formArray, mode) {
    //build a GML Point feature
    var gmlFeature = "";
    gmlFeature +=
      '<gml:Point srsName="http://www.opengis.net/gml/srs/epsg.xml#' +
      this.projection +
      '">';
    var commaCount = 0;
    var latlngString = "";
    editLayer.eachLayer(function (layer) {
      gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
      var latLngIndex = "_latlng";
      var loopFeature = layer[latLngIndex];
      var spaceCount = 0;
      latlngString += loopFeature["lng"] + "," + loopFeature["lat"];
    });
    gmlFeature += latlngString;
    gmlFeature += "</gml:coordinates>";
    gmlFeature += "</gml:Point>";
    return gmlFeature;
  }
  buildMultiPointFeature(editLayer, formArray, mode) {
    //build a GML Multipoint feature
    var gmlFeature = "";
    gmlFeature +=
      '<gml:MultiPoint srsName="http://www.opengis.net/gml/srs/epsg.xml#' +
      this.projection +
      '">';
    editLayer.eachLayer(function (layer) {
      if (mode == "Insert") {
        gmlFeature += "<gml:pointMember>";
        gmlFeature += "<gml:Point>";

        var commaCount = 0;
        var latlngString = "";
        gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
        var latLngIndex = "_latlng";
        var loopFeature = layer[latLngIndex];
        var spaceCount = 0;
        latlngString += loopFeature["lng"] + "," + loopFeature["lat"];
        gmlFeature += latlngString;
        gmlFeature += "</gml:coordinates>";
        gmlFeature += "</gml:Point>";
        gmlFeature += "</gml:pointMember>";
      } else if (mode == "Update") {
        var latLngIndex = "_latlng";
        if (layer["_layers"] != undefined) {
          var loopFeature = layer["_layers"];
          var spaceCount = 0;
          for (var j in loopFeature) {
            if (loopFeature[j]["options"]["draggable"] == false) {
              gmlFeature += "<gml:pointMember>";
              gmlFeature += "<gml:Point>";
              var latlngString = "";
              gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
              latlngString +=
                loopFeature[j][latLngIndex]["lng"] +
                "," +
                loopFeature[j][latLngIndex]["lat"];
              gmlFeature += latlngString;
              gmlFeature += "</gml:coordinates>";
              gmlFeature += "</gml:Point>";
              gmlFeature += "</gml:pointMember>";
            }
          }
        } else {
          //addToFeature mode
          var latlngString2 = "";
          gmlFeature += "<gml:pointMember>";
          gmlFeature += "<gml:Point>";
          gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
          latlngString2 =
            layer[latLngIndex]["lng"] + "," + layer[latLngIndex]["lat"];
          gmlFeature += latlngString2;
          gmlFeature += "</gml:coordinates>";
          gmlFeature += "</gml:Point>";
          gmlFeature += "</gml:pointMember>";
        }
      }
    });
    gmlFeature += "</gml:MultiPoint>";
    return gmlFeature;
  }
  buildLineFeature(editLayer, formArray, mode) {
    //build a GML Line Feature
    var gmlFeature = "";
    gmlFeature +=
      '<gml:LineString srsName="http://www.opengis.net/gml/srs/epsg.xml#' +
      this.projection +
      '">';
    var commaCount = 0;
    var latlngString = "";
    gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
    var latLngIndex = "_latlngs";
    var loopFeature = layer[latLngIndex];
    var spaceCount = 0;
    for (var j = 0; j < loopFeature.length; j++) {
      if (spaceCount > 0) {
        latlngString += " ";
      }
      latlngString += loopFeature[j]["lng"] + "," + loopFeature[j]["lat"];
      spaceCount += 1;
    }
    gmlFeature += latlngString;
    gmlFeature += "</gml:coordinates>";
    gmlFeature += "</gml:LineString>";
    return gmlFeature;
  }
  buildMultiLineFeature(editLayer, formArray, mode) {
    //build a GML MultiLine Feature
    var gmlFeature = "";
    gmlFeature +=
      '<gml:MultiLineString srsName="http://www.opengis.net/gml/srs/epsg.xml#' +
      this.projection +
      '">';
    editLayer.eachLayer(function (layer) {
      if (mode == "Insert") {
        gmlFeature += "<gml:lineStringMember>";
        gmlFeature += "<gml:LineString>";

        var commaCount = 0;
        gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
        var latLngIndex = "_latlngs";
        var loopFeature = layer[latLngIndex];
        var latlngString = "";
        var spaceCount = 0;
        for (var j = 0; j < loopFeature.length; j++) {
          if (spaceCount > 0) {
            latlngString += " ";
          }
          latlngString += loopFeature[j]["lng"] + "," + loopFeature[j]["lat"];
          spaceCount += 1;
        }
        gmlFeature += latlngString;
        gmlFeature += "</gml:coordinates>";
        gmlFeature += "</gml:LineString>";
        gmlFeature += "</gml:lineStringMember>";
      } else if (mode == "Update") {
        var addToFeature = false;
        var latLngIndex = "_latlngs";
        var loopFeature = layer[latLngIndex];
        var latlngString = "";
        var spaceCount = 0;
        for (var j = 0; j < loopFeature.length; j++) {
          if (Array.isArray(loopFeature[j])) {
            if (loopFeature[j].length > 0) {
              latlngString = "";
              gmlFeature += "<gml:lineStringMember>";
              gmlFeature += "<gml:LineString>";
              var commaCount = 0;
              gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
              for (var k = 0; k < loopFeature[j].length; k++) {
                if (spaceCount > 0) {
                  latlngString += " ";
                }
                latlngString +=
                  loopFeature[j][k]["lng"] + "," + loopFeature[j][k]["lat"];
                spaceCount += 1;
              }
              gmlFeature += latlngString;
              gmlFeature += "</gml:coordinates>";
              gmlFeature += "</gml:LineString>";
              gmlFeature += "</gml:lineStringMember>";
            }
          } else {
            addToFeature = true;
            if (j == 0) {
              latlngString = "";
              gmlFeature += "<gml:lineStringMember>";
              gmlFeature += "<gml:LineString>";
              var commaCount = 0;
              gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
            }
            if (spaceCount > 0) {
              latlngString += " ";
            }
            latlngString += loopFeature[j]["lng"] + "," + loopFeature[j]["lat"];
            spaceCount += 1;
          }
        }
        if (addToFeature == true) {
          gmlFeature += latlngString;
          gmlFeature += "</gml:coordinates>";
          gmlFeature += "</gml:LineString>";
          gmlFeature += "</gml:lineStringMember>";
        }
      }
    });
    gmlFeature += "</gml:MultiLineString>";
    return gmlFeature;
  }
  buildPolygonFeature(editLayer, formArray, mode) {
    //build a GML Polygon feature
    var gmlFeature = "";
    gmlFeature +=
      '<gml:Polygon srsName="http://www.opengis.net/gml/srs/epsg.xml#' +
      this.projection +
      '">';
    var commaCount = 0;
    var latlngString = "";
    gmlFeature += "<gml:outerBoundaryIs>";
    gmlFeature += "<gml:LinearRing>";
    gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
    var latLngIndex = "_latlngs";
    var loopFeature = layer[latLngIndex];
    var spaceCount = 0;
    for (var j = 0; j < loopFeature.length; j++) {
      if (spaceCount > 0) {
        latlngString += " ";
      }
      latlngString += loopFeature[j]["lng"] + "," + loopFeature[j]["lat"];
      spaceCount += 1;
    }
    gmlFeature += latlngString;
    gmlFeature += "</gml:LinearRing>";
    gmlFeature += "</gml:outerBoundaryIs>";
    gmlFeature += "</gml:Polygon>";

    return gmlFeature;
  }
  buildMultiPolygonFeature(editLayer, formArray, mode) {
    //build a GML Multipolygon feature
    var gmlFeature = "";
    gmlFeature +=
      '<gml:MultiPolygon srsName="http://www.opengis.net/gml/srs/epsg.xml#' +
      this.projection +
      '">';
    var addToFeature = false;
    editLayer.eachLayer(function (layer) {
      if (mode == "Insert") {
        gmlFeature += "<gml:Polygon>";
        gmlFeature += "<gml:outerBoundaryIs>";
        gmlFeature += "<gml:LinearRing>";

        var commaCount = 0;
        gmlFeature += '<gml:coordinates decimal="." cs="," ts=" ">';
        var latLngIndex = "_latlngs";
        var loopFeature = layer[latLngIndex];
        var latlngString = "";
        for (var j = 0; j < loopFeature.length; j++) {
          var spaceCount = 0;
          for (var k = 0; k < loopFeature[j].length; k++) {
            if (spaceCount > 0) {
              latlngString += " ";
            }
            latlngString +=
              loopFeature[j][k]["lng"] + "," + loopFeature[j][k]["lat"];
            if (k == 0) {
              var firstPoint = latlngString;
            }
            spaceCount += 1;
          }
        }
        gmlFeature += latlngString;
        gmlFeature += " " + firstPoint;
        gmlFeature += "</gml:coordinates>";
        gmlFeature += "</gml:LinearRing>";
        gmlFeature += "</gml:outerBoundaryIs>";
        gmlFeature += "</gml:Polygon>";
      } else if (mode == "Update") {
        var commaCount = 0;
        var latLngIndex = "_latlngs";
        var loopFeature = layer[latLngIndex];
        var spaceCount = 0;
        for (var j = 0; j < loopFeature.length; j++) {
          var firstPoint = undefined;
          var startFeatureString = "";
          startFeatureString += "<gml:Polygon>";
          startFeatureString += "<gml:outerBoundaryIs>";
          startFeatureString += "<gml:LinearRing>";
          startFeatureString += '<gml:coordinates decimal="." cs="," ts=" ">';
          var latlngString = "";
          for (var k = 0; k < loopFeature[j].length; k++) {
            if (Array.isArray(loopFeature[j][k])) {
              if (loopFeature[j][k].length > 0) {
                gmlFeature += startFeatureString;
              }
              latlngString = "";
              for (var l = 0; l < loopFeature[j][k].length; l++) {
                if (spaceCount > 0) {
                  latlngString += " ";
                }
                latlngString +=
                  loopFeature[j][k][l]["lng"] +
                  "," +
                  loopFeature[j][k][l]["lat"];
                if (l == 0) {
                  firstPoint =
                    loopFeature[j][k][l]["lng"] +
                    "," +
                    loopFeature[j][k][l]["lat"];
                }
                spaceCount += 1;
              }
              if (firstPoint != undefined) {
                gmlFeature += latlngString;
                gmlFeature += " " + firstPoint;
                gmlFeature += "</gml:coordinates>";
                gmlFeature += "</gml:LinearRing>";
                gmlFeature += "</gml:outerBoundaryIs>";
                gmlFeature += "</gml:Polygon>";
              }
            } else {
              addToFeature = true;
              if (spaceCount > 0) {
                latlngString += " ";
              }
              latlngString +=
                loopFeature[j][k]["lng"] + "," + loopFeature[j][k]["lat"];
              if (k == 0) {
                var firstPoint =
                  loopFeature[j][k]["lng"] + "," + loopFeature[j][k]["lat"];
              }
              spaceCount += 1;
            }
          }
          if (addToFeature) {
            gmlFeature += startFeatureString;
            gmlFeature += latlngString;
            gmlFeature += " " + firstPoint;
            gmlFeature += "</gml:coordinates>";
            gmlFeature += "</gml:LinearRing>";
            gmlFeature += "</gml:outerBoundaryIs>";
            gmlFeature += "</gml:Polygon>";
          }
        }
      }
    });
    gmlFeature += "</gml:MultiPolygon>";
    return gmlFeature;
  }
  buildGMLAttributes(mode, formArray) {
    //build the attributes of the gml feature
    var attributeString = "";
    for (var i = 0; i < formArray.length; i++) {
      var skip = false;
      if (mode == "Insert") {
        if (
          formArray[i]["name"] != this.fidField &&
          formArray[i]["name"] != this.geometryField
        ) {
          var regex = RegExp("</?[a-z][sS]*>");
          var result = regex.test(formArray[i]["value"]);
          if (
            formArray[i]["name"].toLowerCase().includes("date") &&
            (formArray[i]["value"] == "" || formArray[i]["value"] == undefined)
          ) {
            var skip = true;
          }
          if (skip == false) {
            //if (result){
            if (formArray[i].value == "" || formArray[i].value == undefined) {
              attributeString +=
                "<" + formArray[i]["name"] + ' xsi:nil="true" />';
            } else {
              if (formArray[i].type.toLowerCase() == "number") {
                attributeString +=
                  "<" +
                  formArray[i]["name"] +
                  ">" +
                  formArray[i]["value"] +
                  "</" +
                  formArray[i]["name"] +
                  ">";
              } else if (
                formArray[i].type.toLowerCase() == "datetime-local" ||
                formArray[i].type.toLowerCase() == "date"
              ) {
                var dateValue = new Date(formArray[i]["value"]).toISOString();
                attributeString +=
                  "<" +
                  formArray[i]["name"] +
                  "><![CDATA[" +
                  dateValue +
                  "Z]]></" +
                  formArray[i]["name"] +
                  ">";
              } else {
                attributeString +=
                  "<" +
                  formArray[i]["name"] +
                  "><![CDATA[" +
                  formArray[i]["value"] +
                  "]]></" +
                  formArray[i]["name"] +
                  ">";
              }
            }
          }
        }
      } else if (mode == "Update") {
        if (
          formArray[i]["name"] != this.fidField &&
          formArray[i]["name"] != this.geometryField
        ) {
          var regex = RegExp("</?[a-z][sS]*>");
          var result = regex.test(formArray[i]["value"]);
          if (
            formArray[i]["name"].toLowerCase().includes("date") &&
            (formArray[i]["value"] == "" || formArray[i]["value"] == undefined)
          ) {
            skip = true;
          }
          if (skip == false) {
            //if (result){
            if (formArray[i].value == "" || formArray[i].value == undefined) {
              attributeString +=
                "<" + formArray[i]["name"] + ' xsi:nil="true" />';
            } else if (
              formArray[i].type.toLowerCase() == "datetime-local" ||
              formArray[i].type.toLowerCase() == "date"
            ) {
              var dateValue = new Date(formArray[i]["value"]).toISOString();
              attributeString +=
                "<wfs:Property><wfs:Name>" +
                formArray[i]["name"] +
                "</wfs:Name><wfs:Value><![CDATA[" +
                dateValue +
                "]]></wfs:Value></wfs:Property>";
            } else {
              if (formArray[i].type.toLowerCase() == "number") {
                attributeString +=
                  "<wfs:Property><wfs:Name>" +
                  formArray[i]["name"] +
                  "</wfs:Name><wfs:Value>" +
                  formArray[i]["value"] +
                  "</wfs:Value></wfs:Property>";
              } else {
                attributeString +=
                  "<wfs:Property><wfs:Name>" +
                  formArray[i]["name"] +
                  "</wfs:Name><wfs:Value><![CDATA[" +
                  formArray[i]["value"] +
                  "]]></wfs:Value></wfs:Property>";
              }
            }
          }
        }
      }
    }
    return attributeString;
  }
  getTimeZone() {
    var date = new Date();
    var timezone = date.getTimezoneOffset() / 60;
    var timezoneString = String(timezone);
    var returnString = "";
    if (timezone < 0) {
      returnString += "+";
    } else {
      returnString += "-";
    }
    if (timezoneString.length == 1) {
      returnString += "0";
    }
    returnString += String(timezone) + ":00";
    return returnString;
  }
  buildXMLRequest(mode, editLayer, formArray) {
    //build XML body for WFS-T post request
    var xmlString = `<wfs:Transaction service="WFS" version="1.0.0"
  xmlns:wfs="http://www.opengis.net/wfs"
  xmlns:topp="http://www.openplans.org/topp"
  xmlns:gml="http://www.opengis.net/gml"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengis.net/wfs/1.0.0/WFS-transaction.xsd http://www.openplans.org/topp">`;
    xmlString += "<token>" + this.appToken.token + "</token>";
    if (mode == "Insert") {
      xmlString += "<wfs:" + mode + ">";
      xmlString += "<" + this.name + ">";
      xmlString += "<" + this.geometryField + ">";
    } else if (mode == "Update") {
      xmlString += "<wfs:" + mode + ' typeName="' + this.name + '">';
      xmlString += "<wfs:Property>";
    }
    var memberType, baseFeatureType, latLngIndex, requestFeatureType;
    var featureString;
    if (this.featureType == "gml:MultiPointPropertyType") {
      this.memberType = "pointMember";
      this.baseFeatureType = "Point";
      this.latLngIndex = "_latlng";
      this.requestFeatureType = "MultiPoint";
      featureString = this.buildMultiPointFeature(editLayer, formArray, mode);
    } else if (this.featureType == "gml:MultiCurvePropertyType") {
      this.memberType = "lineStringMember";
      this.baseFeatureType = "LineString";
      this.latLngIndex = "_latlngs";
      this.requestFeatureType = "MultiLineString";
      featureString = this.buildMultiLineFeature(editLayer, formArray, mode);
    } else if (this.featureType == "gml:MultiSurfacePropertyType") {
      this.memberType = "polygonMember";
      this.baseFeatureType = "Polygon";
      this.latLngIndex = "_latlngs";
      this.requestFeatureType = "MultiPolygon";
      featureString = this.buildMultiPolygonFeature(editLayer, formArray, mode);
    } else if (this.featureType == "gml:PointPropertyType") {
      this.memberType = undefined;
      this.baseFeatureType = undefined;
      this.latLngIndex = "_latlng";
      this.requestFeatureType = "Point";
      featureString = this.buildPointFeature(editLayer, formArray, mode);
    } else {
      console.log("Error: Unsupported geometry type.");
    }
    if (mode == "Update") {
      xmlString += "<wfs:Name>" + this.geometryField + "</wfs:Name><wfs:Value>";
    }
    xmlString += featureString;
    if (mode == "Update") {
      xmlString += `</wfs:Value></wfs:Property>`;
    } else if (mode == "Insert") {
      xmlString += "</" + this.geometryField + ">";
    }
    var attributeString = this.buildGMLAttributes(mode, formArray);
    xmlString += attributeString;
    if (mode == "Update") {
      xmlString += " <Filter>";
      xmlString +=
        '<FeatureId fid="' + this.name + "." + this.curEditId + '"/>';
      xmlString += "</Filter>";
    } else if (mode == "Insert") {
      xmlString += "</" + this.name + ">";
    }
    xmlString += "</wfs:" + mode + ">";
    xmlString += "</wfs:Transaction>";
    return xmlString;
  }
  getWFSFeatureFromId(id) {
    var dataString = "&typeNames=" + this.name;
    var IdString = "&featureID=" + id;
    var spatialDataString = "&spatialdata=" + this.name;
    var formatString = "&outputFormat=application%2Fjson";
    var wfsRequest =
      this.baseAPIURL +
      "/simplewfs/?&service=wfs&request=GetFeature" +
      dataString +
      IdString +
      formatString +
      spatialDataString;
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var postData = "<token>" + this.appToken.token + "</token>";
        var that = this;
        $.ajax({
          type: "POST",
          url: wfsRequest,
          dataType: "json",
          data: postData,
          //contentType: "xml",
          success: function (featureData) {
            resolve(featureData);
          },
          error: function (featureData) {
            reject(featureData);
          },
        });
      });
    });
  }
  parseDate(date) {
    var year = date.getFullYear().toString();
    var month = date.getMonth() + 1;
    month = month.toString();
    if (month.length < 2) {
      month = "0" + month;
    }
    var day = date.getDate().toString();
    if (day.length < 2) {
      day = "0" + day;
    }
    var hours = date.getHours().toString();
    if (hours.length < 2) {
      hours = "0" + hours;
    }
    var minutes = date.getMinutes().toString();
    if (minutes.length < 2) {
      minutes = "0" + minutes;
    }
    var seconds = date.getSeconds().toString();
    if (seconds.length < 2) {
      seconds = "0" + seconds;
    }
    var timezone = this.getTimeZone().toString();
    var dateString =
      year +
      "-" +
      month +
      "-" +
      day +
      "T" +
      hours +
      ":" +
      minutes +
      ":" +
      seconds;
    if (isNaN(year) || isNaN(month || isNaN(day))) {
      return "";
    } else {
      return dateString;
    }
  }
  getEditPopupForm(featureProperties) {
    //create html form for use in popup.  Dynamically generated using fields / types from wfs getfeaturerequest
    var htmlForm =
      "<h2>Edit Feature - " +
      this.displayName +
      '</h2><form id="editAttributesForm" action="nada" onsubmit="return false">';
    var xmlDoc = this.describe;
    var s = new XMLSerializer();
    var fkJson = this.foreignKeyList;
    var a = xmlDoc.getElementsByTagName("xsd:element");
    for (var i = 0; i < a.length; i++) {
      var node = a[i];
      if (s.serializeToString(node.attributes.name) != this.name) {
        var curName = s.serializeToString(node.attributes.name);
        var curLabel = curName + "Label";
        var curType = s.serializeToString(node.attributes.type);
        var curNillable = s.serializeToString(node.attributes.nillable);
        if (curNillable == "false") {
          var curRequired = "required";
        } else {
          var curRequired = "";
        }
        var curValue = featureProperties[curName];
        //ensure null values aren't converted to text value "null"
        if (curValue === null) {
          curValue = "";
        }
        curType = curType.substr(curType.indexOf(":") + 1, curType.length);
        if (curType == "date") {
          curValue = curValue.substr(0, 10);
        }
        if (curName == this.fidField || curName == this.geometryField) {
          htmlForm +=
            '<label for="' +
            curName +
            '" style="display:none">' +
            curName +
            "</label>";
        } else {
          htmlForm +=
            '<label for="' + curName + '">' + curName + "</label><br>";
        }
        if (this.foreignKeyField(fkJson, curName) == false) {
          var formType = this.getFormType(curType, curName);
          var regex = RegExp("</?[a-z][sS]*>");
          var result = regex.test(curValue);
          if (result) {
            formType = "textarea";
          }
          if (formType == "datetime-local") {
            var d = new Date(curValue);
            curValue = this.parseDate(d);
          }
          if (formType == "hidden") {
            htmlForm +=
              '<input type="' +
              formType +
              '" id="' +
              curName +
              '" name="' +
              curName +
              '" value="' +
              curValue +
              '" ' +
              curRequired +
              ">";
          } else if (formType == "textarea") {
            htmlForm +=
              '<textarea id="' +
              curName +
              '" name="' +
              curName +
              '" rows="5" cols="45" ' +
              curRequired +
              ">" +
              curValue +
              "</textarea><br>";
          } else {
            htmlForm +=
              '<input type="' +
              formType +
              '" id="' +
              curName +
              '" name="' +
              curName +
              '" value="' +
              curValue +
              '" ' +
              curRequired +
              "><br>";
          }
        } else {
          htmlForm += this.getForeignKeyDropDown(
            fkJson,
            curName,
            curValue,
            curRequired
          );
        }
      }
    }
    htmlForm += '<button id="editAttributesButton">Save</button>';
    htmlForm += "</form>";
    return htmlForm;
  }
}
