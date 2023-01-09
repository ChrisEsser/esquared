<?php

/** @var \Property[] $properties */
$properties = $this->getVar('properties');

?>

<h1 class="page_header">Manage Properties</h1>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button">Add Property</button>
</div>

<?php if (count($properties)) { ?>

    <table class="e2-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($properties as $property) { ?>
                <tr>
                    <td><a href="/property/<?=$property->property_id?>"><?=$property->name?></a></td>
                    <td><?=$property->description?></td>
                    <td style="text-align: right">
                        <button role="button" class="btn btn-primary btn-sm me-md-1 edit_trigger" data-property="<?=$property->property_id?>" type="button">Edit</button>
                        <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-property="<?=$property->property_id?>" data-message="Are you sure you want to delete <strong><?=$property->name?></strong>?" data-url="/delete-property/<?=$property->property_id?>" type="button">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } else { ?>

    <div class="alert alert-primary" role="alert">No properties</div>

<?php } ?>

<script>

    $(document).ready(function() {

        $(document).on('click', '.edit_trigger', function () {

            let property = $(this).data('property');
            let url = (property) ? '/edit-property/' + property : '/create-property';
            let modalTitle = (property) ? 'Edit Property' : 'Create Property';

            $.get(url).done(function(result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>


