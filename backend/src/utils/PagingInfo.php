<?php

namespace Utils;

require '../vendor/autoload.php';

class PagingInfo
{
    /** @var int */
    public $page;

    /** @var int */
    public $pageSize;

    /** @var int */
    public $totalPages;

    public function __construct(? int $page, ? int $pageSize, int $totalPages = null)
    {
        $this->page = $page === null ? 1 : $page;
        $this->pageSize = $pageSize;
        $this->totalPages = $totalPages;
    }

    public function toArray()
    {
        $val = [
            'page' => $this->page,
            'total' => $this->totalPages,
            'pagesize' => $this->pageSize
        ];

        return array_filter($val, function ($var) {
            return !is_null($var);
        });
    }
}
