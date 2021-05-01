"use strict";

const leaflet = require('leaflet');
const $ = require('jquery');

document.addEventListener('DOMContentLoaded', (event) => {
    let divMapData = document.querySelector('#aerial-map').dataset;
    let aerialMap = aerialMapGeneration(divMapData.longitude, divMapData.latitude);

    $.ajax({
        url: divMapData.geojson_data_endpoint,
        data: {
            'departmentCode': divMapData.department_code,
            'inseeCode': divMapData.insee_code
        },
        type: 'GET'
    }).done(function (data){
        addGeoJsonLayer(aerialMap, data);
    })
});

function addGeoJsonLayer(leafletMap, geoJsonDataObject){
    for (const [key, geoJsonDatum] of Object.entries(geoJsonDataObject)){
        let myStyle = {};
        switch (key){
            case 'city':
            case 'hamlet':
            case 'sectionPrefix':
                myStyle = {
                    'color':'#5f0f80',
                    'weight': 2,
                    'opacity': 1,
                    'fill': false
                }
                break;
            case 'building':
                myStyle = {
                    'color':'#db1f1f',
                    'weight': 2,
                    'opacity': 0.3,
                    'fillOpacity': 0.2
                }
                break;
            case 'land':
                myStyle = {
                    'color':'#38ff6a',
                    'weight': 2,
                    'opacity': 0.5,
                    'fillOpacity': 0.1
                }
                break;
            case 'section':
                myStyle = {
                    'color':'#fff638',
                    'weight': 2,
                    'opacity': 1,
                }
                break;
            case 'fiscalSubdivision':
                myStyle = {
                    'color':'#1574ff',
                    'weight': 2,
                    'opacity': 1,
                    'fillOpacity': 0
                }
                break;
        }
        leaflet.geoJSON(JSON.parse(geoJsonDatum), {'style': myStyle}).addTo(leafletMap)
    }
}

function aerialMapGeneration(longitude, latitude){
    let aerialMap = leaflet.map('aerial-map').setView(
        leaflet.latLng([latitude, longitude]),
        18
    );
    leaflet.tileLayer(
        'https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token={accessToken}',
        {
            titleSize: 512,
            maxZoom: 19,
            accessToken: 'pk.eyJ1IjoibWloYW5pIiwiYSI6ImNrbzRheWNsMDEwazIyd2xwZXA1NWx3eDEifQ.SqL_3989XWZTS7uw4Zveeg',
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>'
        }
    ).addTo(aerialMap);

    return aerialMap;
}
