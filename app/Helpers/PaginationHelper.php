<?php

namespace App\Helpers;

use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class PaginationHelper
{
    public static function paginate (Collection $results, $per_page = 20, $name = "page")
    {
        $page_number = Paginator::resolveCurrentPage ($name);

        $total_page_number = $results->count ();

        return self::paginator ($results->forPage ($page_number, $per_page), $total_page_number, $per_page, $page_number, [
            "path" => Paginator::resolveCurrentPath (),
            "pageName" => $name,
        ]);
    }

    protected static function paginator ($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance ()->makeWith (LengthAwarePaginator::class, compact (
            "items", "total", "perPage", "currentPage", "options"
        ));
    }
}
