class tableData
{
    constructor(elementSelector, config)
    {
        this.element = $(elementSelector);
        this.id = this.element.prop('id');
        this.config = config;
        var $this = this;

        if (typeof this.config.filter == 'undefined') {
            this.config.filter = [];
        } else if (typeof this.config.filter == 'object') {
            this.config.filter = [this.config.filter];
        }
        if (typeof this.config.sort == 'undefined') {
            this.config.sort = [];
        } else if (typeof this.config.sort == 'object') {
            this.config.sort = [this.config.sort];
        }

        this.getBaseHtml(function(html) {
            // need to remove the original table and put the new html in it's place
            // replaceWith does not work because the element is not in the DOM after
            $this.element.wrap('<div id="' + $this.id + '_tableData_container"></div>');
            $('#' + $this.id + '_tableData_container').html(html);
            $this.getData(1, function (results) {
                $this.loadData(results);
            });
            $this.toggleSortIcons();
        });

        $(document).on('click', '#' + this.id + '_tableData_container .page_prev_trigger', function () {
            $this.paginationNavigateTrigger($(this).data('page'));
        });
        $(document).on('click', '#' + this.id + '_tableData_container .page_next_trigger', function () {
            $this.paginationNavigateTrigger($(this).data('page'));
        });
        $(document).on('click', '#' + this.id + '_tableData_container .page_trigger', function () {
            $this.paginationNavigateTrigger($(this).data('page'));
        });
        $(document).on('change', '#' + this.id + '_perPage', function() {
            sessionStorage.setItem($this.id + '_pageLength', $(this).val());
            $this.getData(1, function (results) {
                $this.loadData(results);
            });
        });

        $(document).on('click', '.' + this.id + '_tableData_sort_trigger', function(e) {
            const order = $(this).attr('data-order');
            const col = $(this).data('col');
            if (order == 'DESC') $(this).attr('data-order', 'ASC');
            else $(this).attr('data-order', 'DESC');
            if (!e.shiftKey) {
                // shift click allows us to do multiple filters
                $this.config.sort = [];
            }
            $this.config.sort = [];
            let tmpObj = {};
            tmpObj[col] = order;
            $this.config.sort.push(tmpObj);
            $this.toggleSortIcons();
            $this.getData(1, function (results) {
                $this.loadData(results);
            });
        });

        var timeoutSearch;
        $(document).on('keyup', '.tableData_search_input', function() {
            const val = $(this).val();
            const col = $(this).data('col');

            clearTimeout(timeoutSearch);
            timeoutSearch = setTimeout(function() {
                let tmpObj = {};
                tmpObj[col] = val;
                $this.config.filter.push(tmpObj);
                $this.getData(1, function (results) {
                    $this.loadData(results);
                });
            }, 200);
        });

        $(document).on('click', '.mobile_filter_trigger', function() {
            $('#' + $this.id + '_tableData_container thead tr:last-child').toggle();
        });
    }

    toggleSortIcons()
    {
        for (let i = 0; i < this.config.columns.length; i++) {
            for (let j = 0; j < this.config.sort.length; j++) {
                if (this.config.sort[j].hasOwnProperty(this.config.columns[i].col)) {
                    // find the th
                    $('#' + this.id + '_headerSort_' + i).find('.fa').css({color: '#212529'});
                    if (this.config.sort[j][this.config.columns[i].col] == 'ASC') {
                        $('#' + this.id + '_headerSort_' + i).find('.fa-chevron-up').hide();
                        $('#' + this.id + '_headerSort_' + i).find('.fa-chevron-down').show();
                    } else {
                        $('#' + this.id + '_headerSort_' + i).find('.fa-chevron-up').show();
                        $('#' + this.id + '_headerSort_' + i).find('.fa-chevron-down').hide();
                    }
                } else {
                    $('#' + this.id + '_headerSort_' + i).find('.fa').css({color: '#B6B8BA'});
                    $('#' + this.id + '_headerSort_' + i).find('.fa-chevron-up').hide();
                }
            }
        }
    }

    paginationNavigateTrigger(page)
    {
        var $this = this;
        this.getData(page, function (results) {
            $this.loadData(results);
        });
    }

    getData(page, callback)
    {
        var data = {
            page: page,
            len: $('#' + this.id + '_perPage').val(),
            filter: this.config.filter,
            sort: this.config.sort
        };
        data = 'tableData=' + JSON.stringify(data);

        $.post(this.config.url, data).done(function (results) {
            if (typeof callback == 'function') callback(results);
            else alert('Invalid callback');
        }).fail(function(result) {
            console.log(result);
            alert('Invalid data request');
        });
    }

    loadData(results)
    {
        try {
            results = JSON.parse(results);
            if (typeof results.total == 'undefined' || typeof results.data == 'undefined') throw "malformed";
        } catch (e) {
            console.log(results);
            alert('Invalid response data:');
            return;
        }

        if (results.total == 0) {
            this.showNoResults();
        } else {

            this.updateShowingOf(results.data.length, results.total);
            this.updatePaginationDisplay(results.page, results.pages)

            let $tbody = $('#' + this.id  + '_tableActual tbody');
            let html = '';

            for (let i = 0; i < results.data.length; i++) {
                html += '<tr>';
                for(let j = 0; j < this.config.columns.length; j++) {

                    let style = '';
                    if (typeof this.config.columns[j].cellStyle == 'string') {
                        style = ' style="' + this.config.columns[j].cellStyle + ';" ';
                    }
                    html += '<td' + ((style != '') ? style : '') + '>';
                    let value = (typeof results.data[i][this.config.columns[j].col] != 'undefined') ?
                        results.data[i][this.config.columns[j].col]
                        : '';

                    if (typeof this.config.columns[j].format == 'string') {
                        value = this.dataFormat(this.config.columns[j].format, value);
                    }
                    if (typeof this.config.columns[j].template == 'function') {
                        value = this.config.columns[j].template(results.data[i]);
                    }
                    html += value;
                    html += '</td>';
                }
                html += '</tr>';
            }

            $tbody.html(html);
        }
    }

    addFilterAdnReload(filter)
    {
        if (typeof filter != 'object') {
            alert('added filter must be an object');
            return;
        }

        for (let key in filter) {
            this.config.filter[key] = filter;
        }

        var $this = this;
        this.getData(1, function (results) {
            $this.loadData(results);
        });
    }

    removeFilterAndReload(filter)
    {
        if (typeof filter != 'string') {
            alert('removed filter reference must be a string');
            return;
        }

        for(var i = 0; i < this.config.filter.length; i++) {
            if (this.config.filter[i].hasOwnProperty(filter)) {
                this.config.filter = this.config.filter.splice(i, 1);
                break;
            }
        }

        var $this = this;
        this.getData(1, function (results) {
            $this.loadData(results);
        });
    }

    dataFormat(format, value)
    {
        if (format === 'usd') {
            value = new Intl.NumberFormat('en-US', { style: 'currency', 'currency':'USD' }).format(value);
        } else if (format === 'date' || format === 'datetime') {
            value += 'Z';
            const d = new Date(value);
            const month = d.getMonth()+1;
            value = + month + '/' + d.getDate() + '/' + d.getFullYear();
            if (format === 'datetime') {
                const time = d.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true,
                });
                value += ' ' + time;
            }
        }
        return value;
    }

    updateShowingOf(showing, total)
    {
        $('#' + this.id + '_tableData_container .showing_counts strong:first-child').text(total);
        // $('#' + this.id + '_tableData_container .showing_counts strong:last-child').text(total);
    }

    updatePaginationDisplay(page, pages)
    {
        let html = '';
        if (pages > 1) {
            if (page > 1) {
                let prev = parseInt(page) - 1;
                html += '<li class="page-item"><a class="page-link page_prev_trigger" data-page="' + prev + '" href="javascript:void(0);"><span aria-hidden="true">&laquo;</span></a></li>';
            }
            for (let i = 1; i <= pages; i++) {
                let uid = this.id + '_paginate_page_' + i;
                html += '<li class="page-item' + ((page == i) ? ' active' : '') + '"><a class="page-link page_trigger" id="' + uid + '" data-page="' + i + '" href="javascript:void(0);">' + i + '</a></li>';
            }
            if (page < pages) {
                let next = parseInt(page) + 1;
                html += '<li class="page-item"><a class="page-link page_next_trigger" data-page="' + next + '" href="javascript:void(0);"><span aria-hidden="true">&raquo;</span></a></li>';
            }
        }
        $('#' + this.id + '_paginate1 ul.pagination').html(html);
        $('#' + this.id + '_paginate2 ul.pagination').html(html);
    }

    showNoResults()
    {
        const cols = this.getColumnCount('#' + this.id + '_tableActual');
        $('#'+ this.id + '_tableActual tbody').html('<tr style="border: none; background-color: #fff"><td colspan="' + cols + '" style="border: none; background-color: #fff"><div class="alert alert-primary">No Results</div></td></tr>');
    }

    getBaseHtml(callback)
    {
        var origClone = this.element.clone();
        const headers = $('#' + this.id + ' thead > tr:first-child th');

        // add a new row in the thead with text search boxes for each column.
        // by default, they all have it unless the setting no search is set for this column in the config
        const cols = this.getColumnCount('#' + this.id);

        // add the search input row into the thead
        let hasOneSearchColumn = false;
        let html = '<tr style="padding: 0">';
        for(let i = 0; i < cols; i++) {
            if ((typeof this.config.columns[i].search != 'undefined' && this.config.columns[i].search === false)
                || typeof this.config.columns[i].col != 'string') {
                // add an empty td here... no search needed however we still have a column
                html += '<td></td>';
            } else {
                // try to grab the col header to display in the label
                const label = (typeof $(headers[i]).text() == 'string') ? $(headers[i]).text() : '';
                html += '<td style="padding: 8px 8px">';
                html += '<div class="mobile_header_label" style="display: none;">';
                html += '<div  style="display: flex; align-items: center; justify-content: space-between;">';
                html += '<lable for="' + this.id + '_headerSearch_' + i + '">' + label + '</lable>';
                html += '</div>';
                html += '</div>';
                html += '<input class="tableData_search_input" type="text" id="' + this.id + '_headerSearch_' + i + '" data-col="' + this.config.columns[i].col + '" />';
                html += '</td>';
                hasOneSearchColumn = true;
            }

        }
        html += '</tr>';
        if (hasOneSearchColumn) {
            origClone.find('thead').append(html);
        }

        // now add the sort triggers to each header column in the thead
        var i = 0;
        var $this = this;
        origClone.find('thead tr:first-child th').each(function() {
            if ($this.config.sort !== false && (typeof $this.config.columns[i].sort == 'undefined' || typeof $this.config.columns[i].sort === true || typeof $this.config.columns[i].sort == 'string')) {
                const order = (typeof $this.config.columns[i].order == 'undefined' || $this.config.columns[i].order === 'ASC') ? 'ASC' : '';
                let html = '<div class="' + $this.id + '_tableData_sort_trigger" data-col="' + $this.config.columns[i].col + '" data-order="' + order + '" style="display: flex; align-items: center; justify-content: start; flex-wrap: nowrap; cursor: pointer;" id="' + $this.id + '_headerSort_' + i + '">';
                html += '<div>' + $(this).html() + '</div>';
                html += '<div><i class="fa fa-chevron-down fa-sm ms-1"></i><i class="fa fa-chevron-up fa-sm ms-1"></i></div>';
                html += '</div>';
                $(this).html(html);
                i++;
            }
        });

        // build the base html
        var newHtml = '<div class="tableData_general_container">';
        newHtml += '<div class="my-2" style="display: flex; align-items: center; justify-content: space-between">';
        newHtml += '<div class="row g-3 align-items-center">';
        newHtml += '<div class="col-auto"><label for="' + this.id + '_perPage" class="col-form-label" style="font-weight: 500">Show</label></div>';
        newHtml += '<div class="col-auto">';
        newHtml += '<select class="form-control" id="' + this.id + '_perPage" style="max-width: 50px">';

        let pageLength = (typeof this.config.pageLength == 'bigint') ? this.config.pageLength : 10;

        if (sessionStorage.getItem(this.id + '_pageLength')) {
            pageLength = sessionStorage.getItem(this.id + '_pageLength');
        }

        if (typeof this.config.pageLengths == 'object') {
            for (var i = 0; i < this.config.pageLengths.length; i++) {
                newHtml += '<option value="' + this.config.pageLengths[i] + '"' + ((pageLength === this.config.pageLengths[i]) ? ' selected' : '') + '>' + this.config.pageLengths[i] + '</option>';
            }
        } else {
            newHtml += '<option value="10"' + ((pageLength === 10) ? ' selected' : '') + '>10</option>';
            newHtml += '<option value="20"' + ((pageLength === 20) ? ' selected' : '') + '>20</option>';
            newHtml += '<option value="50"' + ((pageLength === 50) ? ' selected' : '') + '>50</option>';
            newHtml += '<option value="100"' + ((pageLength === 100) ? ' selected' : '') + '>100</option>';
        }

        newHtml += '</select>';
        newHtml += '</div>';
        newHtml += '</div>'; // end inline container


        newHtml += '<nav id="' + this.id + '_paginate1">';
        newHtml += '<ul class="pagination pagination-sm">';
        newHtml += '<li class="page-item">';
        newHtml += '<a class="page-link" href="#" aria-label="Previous">';
        newHtml += '<span aria-hidden="true">&laquo;</span>';
        newHtml += '</a>';
        newHtml += '</li>';
        newHtml += '<li class="page-item"><a class="page-link" href="#">1</a></li>';
        newHtml += '<li class="page-item">';
        newHtml += '<a class="page-link" href="#" aria-label="Next">';
        newHtml += '<span aria-hidden="true">&raquo;</span>';
        newHtml += '</a>';
        newHtml += ' </li>';
        newHtml += '</ul>';
        newHtml += '</nav>';




        // newHtml += '<input class="form-control" id="' + this.id + '_search" type="search" placeholder="Search" aria-label="Search" style="max-width: 150px" />';
        newHtml += '</div>'; // end flex row
        // newHtml += '<div class="showing_counts mb-2">Total: <strong>0</strong></div>';
        newHtml += '<div class="mobile_filter_trigger">';
        newHtml += '<i class="fa fa-filter"></i>';
        newHtml += '</div>';
        newHtml += '<table class="e2-table" id="' + this.id + '_tableActual">' + origClone.html() + '</table>';
        newHtml += '<div class="my-2" style="display: flex; align-items: start; justify-content: space-between">';
        newHtml += '<div class="showing_counts">Total: <strong>0</strong></div>';
        newHtml += '<nav id="' + this.id + '_paginate2">';
        newHtml += '<ul class="pagination pagination-sm">';
        newHtml += '<li class="page-item">';
        newHtml += '<a class="page-link" href="#" aria-label="Previous">';
        newHtml += '<span aria-hidden="true">&laquo;</span>';
        newHtml += '</a>';
        newHtml += '</li>';
        newHtml += '<li class="page-item"><a class="page-link" href="#">1</a></li>';
        newHtml += '<li class="page-item">';
        newHtml += '<a class="page-link" href="#" aria-label="Next">';
        newHtml += '<span aria-hidden="true">&raquo;</span>';
        newHtml += '</a>';
        newHtml += ' </li>';
        newHtml += '</ul>';
        newHtml += '</nav>';
        newHtml += '</div>';
        newHtml += '</div>';

        if (typeof callback == 'function') {
            callback(newHtml);
        }
    }

    getColumnCount(tableSelector)
    {
        var colCount = 0;
        $(tableSelector + ' thead tr:first-child th').each(function () {
            if ($(this).attr('colspan')) {
                colCount += +$(this).attr('colspan');
            } else {
                colCount++;
            }
        });
        return colCount;
    }

}

