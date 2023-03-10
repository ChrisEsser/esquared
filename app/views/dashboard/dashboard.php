<?php

$totals = $this->getVar('totals');
$topProperties = $this->getVar('topProperties');
$monthlyBreakdown = $this->getVar('monthlyBreakdown');

?>

<script>

    var monthlyBreakdown = <?=json_encode($monthlyBreakdown)?>;

</script>

<h1 class="page_header">Dashboard</h1>

<div class="row">

    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">$<?=number_format($totals['total_revenue'], 2)?></h5>
                <h6 class="card-subtitle mb-0 text-muted">Total Revenue</h6>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">$<?=number_format($totals['total_expenses'], 2)?></h5>
                <h6 class="card-subtitle mb-0 text-muted">Total Expenses</h6>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">$<?=number_format($totals['total_cashflow'], 2)?></h5>
                <h6 class="card-subtitle mb-0 text-muted">Bottom Line</h6>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-6 mb-3">
        <canvas id="cashFlowMonthChart" style="width:100%;"></canvas>
    </div>

    <div class="col-md-6 mb-3">
        <div style="font-weight: bold; font-size: 13px; text-align: center; margin-top: 10px; margin-bottom: 10px; color: #555">Top Performing Properties</div>
        <table class="table" id="top_properties_table">
            <tbody>
                <?php foreach ($topProperties as $propertyId => $topProperty) { ?>
                    <tr>
                        <td><?=$topProperty['property']?></td>
                        <td class="table-success"><?=$topProperty['revenue']?></td>
                        <td class="table-danger"><?=$topProperty['expense']?></td>
                        <td class="table-primary"><?=$topProperty['cashflow']?></td>
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
        expenses.push(monthlyBreakdown[key].expense);
        revenues.push(monthlyBreakdown[key].revenue);
        cashflows.push(monthlyBreakdown[key].cashflow);
    }

    new Chart(document.getElementById('cashFlowMonthChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Expenses',
                    data: expenses,
                    borderWidth: 1,
                    minBarLength: 1
                },
                {
                    label: 'Income',
                    data: revenues,
                    borderWidth: 1,
                    minBarLength: 1
                },
                {
                    label: 'Cash Flow',
                    data: cashflows,
                    borderWidth: 1,
                    minBarLength: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Last 6 Months'
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

</script>