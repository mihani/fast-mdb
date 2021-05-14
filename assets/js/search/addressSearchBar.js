"use strict";

import * as $ from 'jquery';

$(document).ready(function() {
    $('body')
        .click(function() {
            $('#search-bar__list-group').css('display','none');
        })
        .on('click', '.result-item', function (){
            $('.dashboard-new-project-search-bar').val($(this).children('div').text());
        })
    ;

    $('.dashboard-new-project-search-bar').keyup(function (){
        let addressSearched = $(this).val();
        if (addressSearched === ''){
            return;
        }
        let elementWhichShowResult = $('#search-bar__list-group');

        $.ajax({
            url: "https://api-adresse.data.gouv.fr/search/?q="+addressSearched.replace(/\s/g,'+')+"&limit=5&type=housenumber&autocomplete=1",
            header:{
                'Access-Control-Allow-Origin':'*'
            },
            type: 'GET'
        }).done(function (data){
            let resultLines = '';
            (data.features).forEach(function (object) {
                resultLines += fillTemplateWithData(object.properties.label, object.properties.context)
            });

            return elementWhichShowResult.css('display','flex').html(resultLines);
        })
    });
});

function fillTemplateWithData(address, context) {
    return '<a href="#" class="list-group-item list-group-item-action result-item">'+
        '<div class="d-flex w-100 justify-content-between">' +
        '<h6 class="mb-1">' + address + '</h6>' +
        '</div>' +
        '<p class="mb-1">' +
        '<small>'+context+'</small>' +
        '</p>' +
        '</a>'
}
