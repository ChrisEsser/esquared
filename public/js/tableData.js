class tableData
{
    constructor(elementSelector, config)
    {
        this.element = $(elementSelector);
        this.id = this.element.prop('id');
        this.config = config;
        var $this = this;

        if (typeof this.config.filter == 'undefined') {
            this.config.filter = {
                search: '',
            }
        }

        this.getBaseHtml(function(html) {
            // need to remove the original table and put the new html in it's place
            // replaceWith does not work because the element is not in the DOM after
            $this.element.wrap('<div id="' + $this.id + '_tableData_container"></div>');
            $('#' + $this.id + '_tableData_container').html(html);

            $this.getData(1, function (results) {
                $this.loadData(results);
            });
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
            $this.getData(1, function (results) {
                $this.loadData(results);
            });
        });
        var timeout;
        $(document).on('keyup', '#' + this.id + '_search', function() {
            $this.config.filter.search = $(this).val();
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                $this.getData(1, function (results) {
                    $this.loadData(results);
                });
            }, 200);
        });
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
        const filters = this.config.filter;
        var query = new URLSearchParams({
            page: page,
            len: $('#' + this.id + '_perPage').val(),
            filters: JSON.stringify(filters)
        });
        query = query.toString();
        $.post(this.config.url, query).done(function (results) {
            if (typeof callback == 'function') callback(results);
            else alert('Invalid callback');
        }).fail(function(result) {
            alert('Invalid data request');
        });
    }

    loadData(results)
    {
        try {
            results = JSON.parse(results);
            if (typeof results.total == 'undefined' || typeof results.data == 'undefined') throw "malformed";
        } catch (e) {
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
                        style = ' style="' + this.config.columns[j].cellStyle + '" ';
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

        for(let key in filter) {
            var newFilterKey = key;
            var newFilterValue = filter[key];
        }

        let currentFilter = this.config.filter;
        currentFilter[newFilterKey] = newFilterValue;

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

        let currentFilter = this.config.filter;
        if (currentFilter.hasOwnProperty(filter)) delete currentFilter[filter];

        var $this = this;
        this.getData(1, function (results) {
            $this.loadData(results);
        });
    }

    dataFormat(format, value)
    {
        if (format == 'usd') {
            value = new Intl.NumberFormat('en-US', { style: 'currency', 'currency':'USD' }).format(value);
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
        $('#' + this.id + '_paginate ul.pagination').html(html);
    }

    showNoResults()
    {
        var colCount = 0;
        $('#' + this.id + '_tableActual thead th').each(function () {
            if ($(this).attr('colspan')) {
                colCount += +$(this).attr('colspan');
            } else {
                colCount++;
            }
        });
        $('#'+ this.id + '_tableActual tbody').html('<tr style="border: none; background-color: #fff"><td colspan="' + colCount + '" style="border: none; background-color: #fff"><div class="alert alert-primary">No Results</div></td></tr>');
    }

    getBaseHtml(callback)
    {
        var origClone = this.element.clone().html();

        var newHtml = '<div>';
        newHtml += '<div class="mb-2" style="display: flex; align-items: center; justify-content: space-between">';
        newHtml += '<div class="row g-3 align-items-center">';
        newHtml += '<div class="col-auto"><label for="' + this.id + '_perPage" class="col-form-label" style="font-weight: 500">Show</label></div>';
        newHtml += '<div class="col-auto">';
        newHtml += '<select class="form-control" id="' + this.id + '_perPage" style="max-width: 50px">'
        newHtml += '<option value="10">10</option>';
        newHtml += '<option value="20">20</option>';
        newHtml += '<option value="50">50</option>';
        newHtml += '<option value="100">100</option>';
        newHtml += '</select>';
        newHtml += '</div>';
        newHtml += '</div>'; // end inline container
        newHtml += '<input class="form-control" id="' + this.id + '_search" type="search" placeholder="Search" aria-label="Search" style="max-width: 150px" />';
        newHtml += '</div>'; // end flex row
        // newHtml += '<div class="showing_counts mb-2">Total: <strong>0</strong></div>';
        newHtml += '<table class="e2-table" id="' + this.id + '_tableActual">' + origClone + '</table>';
        newHtml += '<div class="my-2" style="display: flex; align-items: start; justify-content: space-between">';
        newHtml += '<div class="showing_counts">Total: <strong>0</strong></div>';
        newHtml += '<nav id="' + this.id + '_paginate">';
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

}

