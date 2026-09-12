<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function applyMonthFilter($query, mixed $month): void
    {
        if (blank($month) || ! ctype_digit((string) $month)) {
            return;
        }

        $query->whereMonth('date', (int) $month);
    }
}
