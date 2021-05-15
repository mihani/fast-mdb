"use strict";

import * as $ from 'jquery';

$(document).ready(function() {
    $('body')
        .click(function() {
            $('#search-existing-contact__list-group').css('display','none');
        })
        .on('click', '.result-item', function (){
            let data = $(this).children('div').data();
            $('.search-existing-contact__search-bar').val(data.fullname);
            $('.search-existing-contact__contact-id').val(data.id);
        })
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
                type: 'GET',
                statusCode:{
                    404: function (data){
                        return elementWhichShowResult.css('display','flex').html(data.responseJSON);
                    }
                }
            }).done(function (data){
                return elementWhichShowResult.css('display','flex').html(data);
            })
        })
    ;
});
