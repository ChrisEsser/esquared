<?php


?>

<h1 class="page_header">Dashboard</h1>

<script>

    $(document).ready(function() {

        let html = '<div class="alert alert-danger" role="alert">';
        html += '<h4 class="alert-heading">Oh Shit Bro!</h4>';
        html += '<p>This is something you should do really soon!<br /><a href="#">Here is a link to do that thing.</a></p><hr>';
        html += '<p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>';
        html += '</div>';

        $('#viewSmallModalLabel').text('Alert');
        $('#viewSmallModal .modal-body').html(html);
        $('#viewSmallModal').modal('show');

    });

</script>
