$(document).ready(function(){

    $("[data-trigger=confirm]").click(function() {

        var button = $(this);
        var url = button.data('url');
        var title = button.data('title');
        var message = button.data('message');

        if(title) { $("#confirmModal .modal-title").html(title); }
        if(message) { $("#confirmModal .modal-body").html(message); }
        $("#confirmModal form").attr('action', url);

        $('#confirmModal').modal('show');
    });

});
