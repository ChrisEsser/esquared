<?php

$totals = $this->getVar('totals');
$topProperties = $this->getVar('topProperties');
$monthlyBreakdown = $this->getVar('monthlyBreakdown');

?>

<style>
    .dashboard_total .text-muted {
        color: #fff !important;
    }
    .dashboard_total .card-title {
        color: #fff;
        font-weight: bold !important;
    }
    .dashboard_total_revenue {
        background-color: #55e7a6;
    }
    .dashboard_total_expenses {
        background-color: #ff466f;
    }
    .dashboard_total_cashflow {
        background-color: #4aa0fc;
    }
    a.card-body {
        text-decoration: none;
    }
    a.card-body:hover {
        text-decoration: none;
        opacity: 0.7;
    }
</style>

<script>

    var monthlyBreakdown = <?=json_encode($monthlyBreakdown)?>;

</script>

<h1 class="page_header">Dashboard</h1>

<div class="row">

    <div class="col-md-4 mb-3">
        <div class="card dashboard_total dashboard_total_revenue">
            <a href="/payments" class="card-body">
                <h5 class="card-title">$<?=number_format($totals['total_revenue'], 2)?></h5>
                <h6 class="card-subtitle mb-0 text-muted">Total Revenue</h6>
            </a>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card dashboard_total dashboard_total_expenses">
            <a href="/expenses" class="card-body">
                <h5 class="card-title">$<?=number_format($totals['total_expenses'], 2)?></h5>
                <h6 class="card-subtitle mb-0 text-muted">Total Expenses</h6>
            </a>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card dashboard_total dashboard_total_cashflow">
            <div class="card-body">
                <h5 class="card-title">$<?=number_format($totals['total_cashflow'], 2)?></h5>
                <h6 class="card-subtitle mb-0 text-muted">Bottom Line</h6>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-6 mb-3">
        <div id="cashFlowMonthChart" style="width:100%;"></div>
    </div>

    <div class="col-md-6 mb-3">
        <div style="font-weight: bold; font-size: 13px; text-align: center; margin-top: 10px; margin-bottom: 10px; color: #555">Top Performing Properties</div>
        <table class="table" id="top_properties_table">
            <tbody>
                <?php foreach ($topProperties as $propertyId => $topProperty) { ?>
                    <tr>
                        <td><?=$topProperty['property']?></td>
                        <td><?=$topProperty['revenue']?></td>
                        <td><?=$topProperty['expense']?></td>
                        <td><?=$topProperty['cashflow']?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>


<script>

    var labels = [];
    var expenses = [];
    var revenues = [];
    var cashflows = [];
    for (let key in monthlyBreakdown) {
        labels.push(monthlyBreakdown[key].monthShortName);
        if (monthlyBreakdown[key].expense == 0) {
            expenses.push(0.01);
        } else {
            expenses.push(monthlyBreakdown[key].expense);
        }
        if (monthlyBreakdown[key].revenue == 0) {
            revenues.push(0.01);
        } else {
            revenues.push(monthlyBreakdown[key].revenue);
        }
        cashflows.push(monthlyBreakdown[key].cashflow);
    }

    var chart = new ApexCharts(
        document.querySelector("#cashFlowMonthChart"),
        {
            series: [
                {name: 'Expenses', data: expenses},
                {name: 'Revenue', data: revenues}
            ],
            chart: {
                type: 'bar',
                // height: 400
            },
            plotOptions: {
                bar: {
                    // horizontal: true,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            dataLabels: {
                enabled: true,
                // offsetX: -6,
                style: {
                    fontSize: '7px',
                    colors: ['#fff']
                }
            },
            colors: ['#ff466f', '#55e7a6'],
            tooltip: {
                shared: true,
                intersect: false
            },
            xaxis: {
                min: 1,
                categories: labels,
            }
        }
    );
    chart.render();

</script>