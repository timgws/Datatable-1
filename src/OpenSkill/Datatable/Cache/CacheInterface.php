<?php
namespace OpenSkill\Datatable\Cache;

interface CacheInterface
{
    public function getTotalItems();

    public function putTotalItems($items);

    public function getTotalItemsWithSearchValue($searchValue);

    public function putTotalItemsWithSearchValue($searchValue, $items);

}