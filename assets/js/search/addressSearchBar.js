"use strict";

import * as $ from 'jquery';
import 'jquery.typewatch';

$(document).ready(function() {
    let $form = $('form[name="address_more_information"]');

    $('body')
        .click(function() {
            $('#address-search-bar__list-group').css('display','none');
        })
        .on('click', '.result-item', function (){
            $('.dashboard-new-project-search-bar').val($(this).children('div').text());
            $form.submit();
        })
    ;

    let typeWatchOptions = {
        callback: function () {
            let addressSearched = $(this).val();
            if (addressSearched === ''){
                return;
            }
            let elementWhichShowResult = $('#address-search-bar__list-group');

            $.ajax({
                url: "/search/address?q="+addressSearched.replace(/\s/g,'+'),
                type: 'GET'
            }).done(function (data){
                let resultLines = '';
                (data.cities).forEach(function (object) {
                    let label = object.nom + ' ' + object.codesPostaux[0];
                    let context = object.departement.code + ', ' + object.departement.nom
                    resultLines += fillTemplateWithData(label, context)
                });
                (data.addresses.features).forEach(function (object) {
                    resultLines += fillTemplateWithData(object.properties.label, object.properties.context)
                });

                return elementWhichShowResult.css('display','flex').html(resultLines);
            })
        },
        wait: 500,
        highlight: true,
        allowSubmit: false,
        captureLength: 3
    }

    $('.dashboard-new-project-search-bar').typeWatch(typeWatchOptions);
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
