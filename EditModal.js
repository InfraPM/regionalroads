var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

//import React, { Component } from 'react';
import Modal from './modal';

var EditModal = function (_Modal) {
    _inherits(EditModal, _Modal);

    function EditModal(props) {
        _classCallCheck(this, EditModal);

        var _this = _possibleConstructorReturn(this, (EditModal.__proto__ || Object.getPrototypeOf(EditModal)).call(this, props));

        _this.state = {};
        return _this;
    }

    _createClass(EditModal, [{
        key: "generateContent",
        value: function generateContent() {
            this.props.featureGrouping.forEach(function (i) {});
        }
    }, {
        key: "render",
        value: function render() {
            return React.createElement(
                "button",
                { type: "button", id: "closeExportModalButton" },
                React.createElement(
                    "svg",
                    { width: "24", height: "24" },
                    React.createElement("path", { d: "M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z", "fill-rule": "evenodd" })
                )
            );
        }
    }]);

    return EditModal;
}(Modal);

export default EditModal;