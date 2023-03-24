<?php

/** @var Lease $lease */
$lease = $this->getVar('lease');
/** @var Unit $unit */
$unit = $this->getVar('unit');
$properties = $this->getVar('properties');
$propertyId = $this->getVar('propertyId');

?>

<script>
    var properties = <?=json_encode($properties)?>;
    var unit = <?=json_encode($unit)?>;
    var lease_id = '<?=$lease->lease_id?>';
</script>

<style>
    .user_lookup_result_row {
        cursor: pointer;
    }
    .user_lookup_result_row:hover {
        background-color: #f5fcff;
    }
</style>

<form id="leaseForm">

    <input type="hidden" name="lease" id="lease" value="<?=$lease->lease_id?>" />

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="name" class="form-label">Property</label>
            <select name="property" id="property" class="form-control">
                <option value="">- Select -</option>
                <?php foreach ($properties as $property) { ?>
                    <option value="<?=$property['property_id']?>" <?=($property['property_id'] == $propertyId) ? 'selected' : ''?>><?=$property['property_name']?></option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3 col-md-6">
            <label for="name" class="form-label">Unit</label>
            <select name="unit_id" id="unit_id" class="form-control">
            </select>
        </div>

    </div>

    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="start_date" class="form-label">Start Date</label>
            <div class="input-group">
                <input type="text" class="form-control" id="start_date" name="start_date" aria-describedby="start_dateHelp" value="<?=date('m/d/y', strtotime($lease->start_date))?>" />
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-calendar"></i>
                    </span>
                </span>
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <label for="start_date" class="form-label">End Date</label>
            <div class="input-group">
                <input type="text" class="form-control" id="end_date" name="end_date" aria-describedby="end_dateHelp" value="<?=date('m/d/y', strtotime($lease->end_date))?>" />
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-calendar"></i>
                    </span>
                </span>
            </div>
        </div>

    </div>


    <div class="row">

        <div class="mb-3 col-md-6">
            <label for="rent" class="form-label">Rent</label>
            <div class="input-group">
                <span class="input-group-append">
                    <span class="input-group-text bg-light d-block">
                        <i class="fa fa-dollar"></i>
                    </span>
                </span>
                <input type="number" min="0" step=".01" class="form-control" id="rent" name="rent" aria-describedby="rentHelp" value="<?=$lease->rent?>" />
            </div>
        </div>

        <div class="mb-3 col-md-6">
            <label for="rent_frequency" class="form-label">Rent Frequency</label>
            <select class="form-control" id="rent_frequency" name="rent_frequency" aria-describedby="rent_frequencyHelp">
                <?php foreach ($lease->rentFrequencyStrings() as $value => $string) { ?>
                    <option value="<?=$value?>" <?=$lease->rent_frequency == $value ? 'selected' : ''?>><?=$string?></option>
                <?php } ?>
            </select>
        </div>

    </div>

    <div class="mb-3">
        <label for="document" class="form-label">Document</label>
        <div id="document" name="filepond"></div>
    </div>

    <div class="mb-3">
        <label for="users" class="form-label">User(s)</label>
        <div id="users_container"></div>
    </div>

</form>

<script>
    $(document).ready(function() {

        $("#start_date").datepicker();
        $("#end_date").datepicker();

        let pond = createPond('#document');

        $('#button_save').click(function() {
            $.post('/save-lease', $('#leaseForm').serialize()).done(function(result) {

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
                        : 'An error occurred saving the unit';
                    alert(message);
                    return;
                }
            });
        });

        $('#property').change(function() {
            updateUnitDropdown();
        });

        $(document).on('click', '.user_row_add_trigger', function() {
            createUserRow();
            updateUserRows();
        });
        $(document).on('click', '.user_row_delete_trigger', function() {
            $(this).closest('.user_row').remove();
            updateUserRows();
        });
        $(document).on('click', '.user_lookup_result_row', function() {
            $(this).closest('.user_row').find('.user_row_user_id').val($(this).data('user'));
            $(this).closest('.user_row').find('.user_row_user_name').val($(this).data('name'));
            $('.user_lookup_results').hide();
        });

        var timeoutLeaseSearch;
        $(document).on('keyup', '.user_row_user_name', function () {

            var $this = $(this);

            if ($this.val() == '') {
                $this.closest('.user_row').find('.user_lookup_results').hide();
                return;
            }

            clearTimeout(timeoutLeaseSearch);
            timeoutLeaseSearch = setTimeout(function() {
                var data = {
                    page: 1,
                    len: 5,
                    filter: [{full_name: $this.val()}]
                };
                data = 'tableData=' + JSON.stringify(data);
                $.post('/app-data/users', data).done(function (result) {
                    result = JSON.parse(result);
                    if (result.data.length > 0) {
                        let html = '';
                        for(i = 0; i < result.data.length; i++) {
                            html += '<tr class="user_lookup_result_row" data-user="' + result.data[i].user_id;
                            html += '" data-name="' + result.data[i].first_name + '  ' + result.data[i].last_name + '">';
                            html += '<td>' + result.data[i].first_name + '  ' + result.data[i].last_name + '</td>';
                            html += '</tr>';
                        }
                        $this.closest('.user_row').find('.user_lookup_results table').html(html);
                        $this.closest('.user_row').find('.user_lookup_results').show();
                    } else {
                        $this.closest('.user_row').find('.user_lookup_results').hide();
                    }

                }).fail(function(result) {
                    console.log(result);
                    alert('Invalid data request');
                });
            }, 200);
        });

        updateUnitDropdown();
        initUserRows();
    });

    function updateUnitDropdown()
    {
        const property = $('#property').val();
        let units = [];
        for (i = 0; i < properties.length; i++) {
            if (property == properties[i].property_id) {
                units = properties[i].units;
                break;
            }
        }
        let html = '<option value="">- Select -</option>';
        for (i = 0; i < units.length; i++) {
            html += '<option value="' + units[i].unit_id + '" ' + ((unit.unit_id == units[i].unit_id) ? 'selected' : '') + '>';
            html += units[i].unit_name + '</option>';
        }
        $('#unit_id').html(html);
    }

    function initUserRows()
    {
        if (lease_id) {
            var data = {
                page: 1,
                len: 999,
                filter: [{lease_id: lease_id}]
            };
            data = 'tableData=' + JSON.stringify(data);

            $.post('/app-data/users', data).done(function (result) {
                result = JSON.parse(result);
                if (result.data.length) {
                    for(i = 0; i < result.data.length; i++) {
                        createUserRow(result.data[i]);
                    }
                } else {
                    createUserRow();
                }
            }).fail(function(result) {
                console.log(result);
                alert('Invalid data request');
            });
        } else {
            createUserRow();
        }

        updateUserRows();
    }

    function updateUserRows()
    {
        const countRows = $('.user_row').length;
        if (countRows === 1) {
            $('.user_row_delete_trigger').hide();
        } else {
            $('.user_row_delete_trigger').show();
        }
        $('.user_row').each(function(i, obj) {
            if (i === countRows - 1) {
                $(this).find('.user_row_add_trigger').show();
            } else {
                $(this).find('.user_row_add_trigger').hide();
            }
        });
    }

    function createUserRow(data)
    {
        let userId = '';
        let userName = '';
        if (typeof data != 'undefined') {
            userId = (typeof data.user_id != 'undefined') ? data.user_id : '';
            userName = (typeof data.first_name != 'undefined') ? data.first_name + ' ' + data.last_name : '';
        }

        let html = '';
        html += '<div class="mb-1 user_row">';
        html += '<div class="input-group">';
        html += '<span class="input-group-append">';
        html += '<span class="input-group-text bg-light d-block">';
        html += '<i class="fa fa-search"></i>';
        html += '</span>';
        html += '</span>';
        html += '<input type="hidden" class="user_row_user_id" name="users[]" value="' + userId + '" />';
        html += '<input type="text" class="form-control user_row_user_name" value="' + userName + '" />';
        html += '<button class="btn btn-outline-danger user_row_delete_trigger" type="button"><i class="fa fa-times"></i></button>';
        html += '<button class="btn btn-outline-primary user_row_add_trigger" type="button"><i class="fa fa-plus"></i></button>';
        html += '</div>';
        html += '<div style="width: 100%;border: 1px solid #ccc; padding: 5px; display: none;" class="user_lookup_results">';
        html += '<table style="width: 100%;">';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        $('#users_container').append(html);
    }

</script>

