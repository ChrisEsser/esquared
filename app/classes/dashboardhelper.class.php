<?php

class DashboardHelper
{
    public static function getTotals()
    {
        $r = self::totalRevenue();
        $e = self::totalExpenses();
        $c = $r->total_revenue - $e->total_expenses;

        return [
            'total_revenue' => floatval($r->total_revenue),
            'total_expenses' => floatval($e->total_expenses),
            'total_cashflow' => floatval($c),
        ];
    }

    public static function totalRevenue()
    {
        $db = new StandardQuery();
        $sql = 'SELECT SUM(amount) AS total_revenue FROM payment_history';
        return $db->row($sql);
    }

    public static function totalExpenses()
    {
        $db = new StandardQuery();
        $sql = 'SELECT SUM(amount) AS total_expenses FROM expenses';
        return $db->row($sql);
    }

    public static function monthBreakdown($countOfMonths = 6)
    {
        $months = self::getMonthInfo($countOfMonths);

        $db = new StandardQuery();

        $exSql = $reSql = '';
        $sep = '';
        foreach ($months as $monthNum => $month) {

            $exSql .= $sep;
            $reSql .= $sep;

            $sep = 'UNION' . "\n\r";
            $exSql .= 'SELECT ' . $monthNum . ' AS month, SUM(amount) as expense 
                       FROM expenses 
                       WHERE date >= \'' . $month['start'] . '\' AND date <= \'' . $month['end'] . '\'' . "\n\r";
            $reSql .= 'SELECT ' . $monthNum . ' AS month, SUM(amount) as revenue 
                       FROM payment_history 
                       WHERE payment_date >= \'' . $month['start'] . '\' AND payment_date <= \'' . $month['end'] . '\'' . "\n\r";
        }

        if (!empty($exSql)) {
            foreach ($db->rows($exSql) as $dataRow) {
                $months[$dataRow->month]['expense'] = floatval($dataRow->expense);
            }
            foreach ($db->rows($reSql) as $dataRow) {
                $months[$dataRow->month]['revenue'] = floatval($dataRow->revenue);
                $months[$dataRow->month]['cashflow'] = floatval($months[$dataRow->month]['revenue'] - $months[$dataRow->month]['expense']);
            }
        }

        $tmpMonths = [];
        foreach ($months as $month) {
            $tmpMonths[] = $month;
        }

        return $tmpMonths;
    }

    public static function topCashFlowProperties($count = 5)
    {
        /** @var Property[] $tmpProperties */
        $tmpProperties = Property::find();

        $db = new StandardQuery();

        $sql = 'SELECT SUM(r.amount) AS revenue, u.property_id
                FROM payment_history r
                INNER JOIN units u ON u.unit_id = r.unit_id
                GROUP BY u.property_id';
        $revenues = $db->rows($sql);

        $rs = [];
        foreach ($revenues as $revenue) {
            $rs[$revenue->property_id] = $revenue->revenue;
        }

        $sql = 'SELECT SUM(e.amount) AS expense, e.property_id
                FROM expenses e
                GROUP BY e.property_id';
        $expenses = $db->rows($sql);

        $es = [];
        foreach ($expenses as $expense) {
            $es[$expense->property_id] = $expense->expense;
        }

        $cs = $properties = [];
        foreach ($tmpProperties as $tmpProperty) {
            $tmpEs = ($es[$tmpProperty->property_id]) ?? 0;
            $tmpRs = ($rs[$tmpProperty->property_id]) ?? 0;
            $cs[$tmpProperty->property_id] = $tmpRs - $tmpEs;
            $properties[$tmpProperty->property_id] = $tmpProperty->name;
        }
        asort($cs);

        $i = 0;
        $topProperties = [];
        foreach ($cs as $key => $value) {
            if ($i >= $count) {
                unset($cs[$key]);
            } else {
                $topProperties[$key]['cashflow'] = $cs[$key];
                $topProperties[$key]['expense'] = ($es[$key]) ?? 0;
                $topProperties[$key]['revenue'] = ($rs[$key]) ?? 0;
                $topProperties[$key]['property'] = ($properties[$key]) ?? '- No Name -';
            }
            $i++;
        }

        return $topProperties;
    }

    private static function getMonthInfo($countOfMonths)
    {
        $curMonth = date('n');
        $year = date('Y');
        $tmpMonth = $curMonth;

        $monthArr[$curMonth] = [
            'start' => date($year.'-'.$curMonth.'-01'),
            'end' => date($year.'-'.$curMonth.'-t'),
            'monthShortName' => date('M'),
        ];

        for ($i = 0; $i < $countOfMonths; $i++) {
            if ($tmpMonth > 1) $tmpMonth--;
            else {
                $tmpMonth = 12;
                $year--;
            }

            $timestamp = strtotime($tmpMonth.'/15/'.$year);
            $monthArr[$tmpMonth] = [
                'start' => date($year.'-'.$tmpMonth.'-01', $timestamp),
                'end' => date($year.'-'.$tmpMonth.'-t', $timestamp),
                'monthShortName' => date('M', $timestamp)
            ];
        }
        $monthArr = array_reverse($monthArr, true);

        return $monthArr;
    }
}