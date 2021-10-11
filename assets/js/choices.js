"use strict";

import Choices from 'choices.js'

let choices = document.querySelectorAll('.choices');
for(var i=0; i<choices.length;i++) {
    if (choices[i].classList.contains("multiple-remove")) {
        new Choices(choices[i],
            {
                delimiter: ',',
                editItems: true,
                maxItemCount: 50,
                removeItemButton: true,
            });
    }else{
        new Choices(choices[i]);
    }
}
