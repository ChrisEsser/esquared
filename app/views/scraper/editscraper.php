<?php

/** @var \ScraperUrl $url */
$url = $this->getVar('url');

?>

<form id="ScraperForm">

    <input type="hidden" name="url_id" id="url_id" value="<?=$url->url_id?>" />

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" aria-describedby="nameHelp" value="<?=$url->name?>" />
    <!--            <div id="nameHelp" class="form-text"></div>-->
        </div>

        <div class="mb-3 col-md-6">
            <label for="state" class="form-label">State</label>
            <input type="text" class="form-control" id="state" name="state" aria-describedby="stateHelp" value="<?=$url->state?>" />
        </div>

    </div>

    <div class="mb-3">
        <label for="description" class="form-label">URL</label>
        <input type="text" class="form-control" id="url" name="url" aria-describedby="urlHelp" value="<?=$url->url?>" />
    </div>

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="description" class="form-label">Doc Type</label>
            <select class="form-control" id="doc_type" name="doc_type" aria-describedby="doc_typeHelp">
                <option value="pdf" <?=($url->doc_type == 'pdf') ? 'selected' : ''?>>PDF</option>
                <option value="html"<?=($url->doc_type == 'html') ? 'selected' : ''?>>HTML</option>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label for="depth" class="form-label">Scrape Depth</label>
            <input type="number" min="1" step="1" class="form-control" id="depth" name="depth" aria-describedby="depthHelp" value="<?=intval($url->depth)+1?>" />
            <div id="depthHelp" class="form-text">The depth is how many pages deep does the crawler need to go to find the correct information. each depth is associated with a filter string level below.</div>
        </div>

    </div>

    <div id="filter_levels">

        <?php for ($i = 0; $i < intval($url->depth)+1; $i++) { ?>
            <?php $levelDisplay = $i+1; ?>
            <div class="mb-3 filter_level">
                <label for="description" class="form-label">Filter String for Level <?=$levelDisplay?></label>
                <input type="text" class="form-control" id="search_string_<?=$i?>" name="search_string[<?=$i?>]" aria-describedby="search_string_<?=$i?>Help" value="<?=(!empty($url->search_string)) ? unserialize(base64_decode($url->search_string))[$i] : ''?>" />
                <?php if ($i == 0) { ?>
                    <div id="search_stringHelp" class="form-text">Separate multiple filters with ",". If you want to specify a string to not include, prefix the string with "!!". Example: include the strings .pdf and sale but omit the string document. .pdf, sale, !!document</div>
                <?php } ?>
            </div>
        <?php } ?>

    </div>

    <div class="mb-3">
        <label for="depth" class="form-label">Target Dom Element</label>
        <input type="text" class="form-control" id="dom_target" name="dom_target" aria-describedby="dom_targetHelp" value="<?=$url->dom_target?>" />
        <div id="dom_targetHelp" class="form-text">Use this to target links within a specific DOM element on the page. For example, say every link is formatted with a code but you know the links are all in a certain html table or container. Enter the class or id of said container here. Example: id=some_dom_id or class=some_dom_class.</div>
    </div>

</form>

<script>
    $(document).ready(function() {

        let imagePond = createPond('#image');

        $('#button_save').click(function() {
            $.post('/save-scraper', $('#ScraperForm').serialize()).done(function(result) {

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
                        : 'An error occurred saving the property';
                    alert(message);
                    return;
                }
            });
        });

        $('#depth').change(function() {

            var depth = parseInt($(this).val());
            var currentLevels = $('.filter_level').length;

            if (currentLevels < depth) {
                for (i = currentLevels; i < depth; i++) {
                    let levelDisplay = i + 1;
                    let html = '<div class="mb-3 filter_level">';
                    html += '<label for="search_string_' + parseInt(i) + '" class="form-label">Filter String for Level ' + parseInt(levelDisplay) + '</label>';
                    html += '<input type="text" class="form-control" id="search_string_' + parseInt(i) + '" name="search_string[' + parseInt(i) + ']" aria-describedby="search_string_' + parseInt(i) + 'Help" value="" />';
                    $('#filter_levels').append(html);
                }
            } else if (currentLevels > depth) {
                for (i = currentLevels; i >=  depth; i--){
                    $('#search_string_'+i).closest('.filter_level').remove();
                }
            }
        });

    });
</script>
