<?php

/** @var \Document[] $documents */
$documents = $this->getVar('documents');
$loggedInUser = $this->getVar('loggedInUser');
$viewing = $this->getVar('viewing');

?>

<h1 class="page_header">Manage Documents<?=(!empty($viewing)) ? '<small> - ' . $viewing . '</small>' : ''?></h1>


<a href="/documents?mydocs">My Documents</a> | <a href="/documents">All Documents</a>

<div class="d-grid gap-2 d-md-flex my-3 justify-content-md-end">
    <button role="button" class="btn btn-primary me-md-2 edit_trigger" type="button">Add Document</button>
</div>

<?php if (count($documents)) { ?>


    <table class="e2-table">
        <thead>
            <tr>
                <th>Document</th>
                <th>Uploaded By</th>
                <th>Upload Date</th>
<!--                <th>Owner</th>-->
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $document) { ?>
                <?php
//                $owner = $document->getOwner();
                $user = $document->getUser();
                $userName = $user->first_name . ' ' . $user->last_name;
//                $ownerName = ($owner) ? $owner->first_name . ' ' . $owner->last_name : '';
                ?>
                <tr>
                    <td><a href="/file/proxy?file=documents/<?=$document->user_id?>/<?=$document->name?>"><?=$document->name?></a></td>
                    <td><?=$userName?></td>
                    <td><?=date('m/d/Y g:ia', strtotime($document->created))?></td>
<!--                    <td>--><?//=$ownerName?><!--</td>-->
                    <td style="text-align: right">
                        <button role="button" class="btn btn-danger btn-sm me-md-1" data-trigger="confirm" data-message="Are you sure you want to delete <strong><?=$document->name?></strong>?" data-url="/delete-document/<?=$document->document_id?>" type="button">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } else { ?>

    <div class="alert alert-primary" role="alert">No Documents</div>

<?php } ?>

<script>

    $(document).ready(function() {

        $(document).on('click', '.edit_trigger', function () {

            let document = $(this).data('document');

            let url = (document) ? '/edit-document/' + document : '/create-document';
            let modalTitle = (document) ? 'Edit Document' : 'Create Document';

            $.get(url).done(function(result) {
                $('#editModalLabel').text(modalTitle);
                $('#editModal .modal-body').html(result);
                $('#editModal').modal('show');
            });
        });

    });

</script>
