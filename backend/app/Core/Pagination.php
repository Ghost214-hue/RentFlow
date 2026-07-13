<?php
namespace App\Core;

class Pagination
{
    public static function fromRequest(array $defaults = ['page' => 1, 'per_page' => 25]): array
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : $defaults['page'];
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : $defaults['per_page'];
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = $defaults['per_page'];
        $max = 200;
        if ($perPage > $max) $perPage = $max;

        $offset = ($page - 1) * $perPage;
        return ['page' => $page, 'per_page' => $perPage, 'offset' => $offset, 'limit' => $perPage];
    }

    public static function meta(int $total, int $page, int $perPage): array
    {
        $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 0;
        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }
}
