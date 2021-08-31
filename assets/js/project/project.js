"use strict";

import * as $ from 'jquery';

$(document).ready(function() {

    let noteEditor;

    $('body')
        .on('click', '.note-content', function() {
            if ($(this).data('editable') !== true) {
                return;
            }

            let noteId = $(this).data('note-id');
            let content = $(this).html();
            let editableContent = '<textarea id="edit_note_content" name="note[content]" autocomplete="off" class="form-control">'+content+'</textarea>';
            $(this).html(editableContent);
            if (CKEDITOR.instances["edit_note_content"]) { CKEDITOR.instances["edit_note_content"].destroy(true); delete CKEDITOR.instances["edit_note_content"]; }
            noteEditor = CKEDITOR.replace("edit_note_content", {"toolbar":[["Bold","Italic","Underline","Strike","NumberedList","BulletedList","FontSize","TextColor","BGColor"]],"removePlugins":"exportpdf","language":"fr"});
            $(this).data('editable', false);

            $('#update-note-'+noteId).show();
        })
        .on('click', '.update-note', function() {
            let content = noteEditor.getData();
            let noteId = $(this).data('note-id');

            $.post('/note/'+noteId+'/edit', {'content': content}).done(function() {
                $('.note-content').html(content);
                $('.note-content').data('editable', true);
                let truncatedContent = content.length > 150 ? content.substring(0,250) + '...' : content;
                $('#note-content-truncated-'+noteId).html(truncatedContent);
            });
        });
});