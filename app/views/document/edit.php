<?php

/** @var \Document $document */
$document = $this->getVar('document');

?>

<form id="documentForm">

    <input type="hidden" name="document_id" id="document_id" value="<?=$document->document_id?>" />

<!--    <div class="mb-3">-->
<!--        <label for="description" class="form-label">Description</label>-->
<!--        <textarea class="form-control" id="description" name="description" aria-describedby="descriptionHelp">--><?//=$document->description?><!--</textarea>-->
<!--    </div>-->

    <div class="mb-3">
<!--        <label for="file" class="form-label">File</label>-->
        <div id="file" name="filepond"></div>
    </div>

</form>

<script>
    $(document).ready(function() {

        let imagePond = createPond('#file');

        $('#button_save').click(function() {
            $.post('/save-document', $('#documentForm').serialize()).done(function(result) {

                console.log(result);

                result = JSON.parse(result);
                if (typeof result.result == 'undefined') {
                    alert('An unknown error occurred');
                    return;
                }
                if (result.result == 'success') {
                    location.reload();
                } else if (result.result == 'error') {
                    let message = (typeof result.message != 'undefined')
                        ? result.message
                        : 'An error occurred saving the document';
                    alert(message);
                    return;
                }
            });
        });

    });
</script>

