"use strict";

import  L from 'leaflet';
import mapMarkerIcon from 'leaflet/dist/images/marker-icon.png'

document.addEventListener('DOMContentLoaded', (event) => {
    let divMapData = document.querySelector('#aerial-map').dataset;
    aerialMapGeneration(divMapData.longitude, divMapData.latitude);
});

function aerialMapGeneration(longitude, latitude){

    let aerialMap = L.map('aerial-map',{
        center: L.latLng([latitude, longitude]),
        zoom: 18
    });

    L.tileLayer(
        'https://wxs.ign.fr/choisirgeoportail/geoportail/wmts?' +
        '&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0' +
        '&layer=ORTHOIMAGERY.ORTHOPHOTOS' +
        '&STYLE=normal' +
        '&FORMAT=image/jpeg' +
        '&TILEMATRIXSET=PM' +
        '&TileMatrix={z}' +
        '&TileCol={x}' +
        '&TileRow={y}',
        {
            titleSize: 512,
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.ign.fr">IGN-F/Geoportail</a>'
        },
    ).addTo(aerialMap);

    L.tileLayer(
        'https://wxs.ign.fr/choisirgeoportail/geoportail/wmts?' +
        '&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0' +
        '&layer=CADASTRALPARCELS.PARCELLAIRE_EXPRESS' +
        '&STYLE=PCI vecteur' +
        '&TILEMATRIXSET=PM' +
        '&FORMAT=image/png' +
        '&TileMatrix={z}' +
        '&TileCol={x}' +
        '&TileRow={y}',
        {
            titleSize: 512,
            maxZoom: 19,
        },
    ).addTo(aerialMap);

    L.marker([latitude, longitude], {
        'icon': L.icon({
            iconUrl: mapMarkerIcon,
            iconSize: [25,41],
            iconAnchor: [12, 35]
        })
    }).addTo(aerialMap);
}
