$(document).ready(function() {
    $('.btn-edit-county').on('click', function() {
        var countyId = $(this).data('id');
        var editorRow = $('#editor-' + countyId);
        
        if (editorRow.is(':visible')) {
            editorRow.hide();
        } else {
            $.ajax({
                url: 'your_request_handler.php', 
                type: 'POST',
                data: { btn_edit_county: true, id: countyId },
                success: function(response) {
                    editorRow.children('td').html(response);
                    editorRow.show();
                },
                error: function() {
                    alert('Hiba történt a megye adatainak betöltésekor!');
                }
            });
        }
    });
});
