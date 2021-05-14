"use strict";

import * as $ from 'jquery';

$(document).ready(function() {
    $('body')
        .click(function() {
            $('#search-existing-contact__list-group').css('display','none');
        })
        // .on('click', '.result-item', function (){
        //     $('.dashboard-new-project-search-bar').val($(this).children('div').text());
        // })
        .find('.search-existing-contact__search-bar').keyup(function (){
            let contactSearched = $(this).val();
            if (contactSearched === ''){
                return;
            }

            let elementWhichShowResult = $('#search-existing-contact__list-group');
            let dataset = document.querySelector('.search-existing-contact__search-bar').dataset;

            $.ajax({
                url: dataset.url,
                data: {
                    'query' : contactSearched
                },
                type: 'GET'
            }).done(function (data){
                let resultLines = '';
                (data.features).forEach(function (object) {
                    resultLines += fillTemplateWithData(object.properties.label, object.properties.context)
                });

                return elementWhichShowResult.css('display','flex').html(resultLines);
            })
        })
    ;
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
