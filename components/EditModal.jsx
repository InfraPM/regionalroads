//import React, { Component } from 'react';
import Modal from './modal';

class EditModal extends Modal {
    constructor(props) {
        super(props);
        this.state = {  }
        this.props.layerReadable;
    }
    generateList(){
        var list;
        var fileName = i.displayName.replace(/[^A-Z0-9]/ig, "");
        var csvFileName = fileName + ".csv"
        var csvId = 'csvLink' + fileName;
        this.props.featureGrouping.forEach(function(i){
            list+= (<ul>
                <li>
                    <b>
                        {i.displayName}<br>
                        <br><button id ={csvId} type="button" class="exportLinkButton" data-filename={csvFileName} data-type="csv">Download CSV</button>
                        {this.generateSubList(i.wfstLayers)}
                    </b>
                </li>
            </ul>);
        });
        return list;
    }
    generateSubList(wfstLayers){
        wfstLayers.forEach(function(j){

        });
        return subList;
    }
    render() {
        return (
            <div>
            <button type="button" id="closeExportModalButton">
                <svg width="24" height="24">
                    <path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path>
                </svg>
            </button>
            <h4>Export Layers</h4>
            <div id="layerListContainer">
                <div id="layerList">
                    {this.generateList()};
                </div>
            </div>
            </div>
        );
    }
}
 
export default EditModal;