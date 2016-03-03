<?php
namespace OpenSkill\Datatable\Cache;

use \Illuminate\Contracts\Cache\Repository as CacheRepository;


class SensibleCache implements CacheInterface
{
    /** @var CacheRepository */
    private $store;

    /** @var string */
    private $keyName;

    /** @var int */
    private $cacheTime;

    public function __construct(CacheRepository $store, $keyName = 'datatable-cache', $cacheTime = 5)
    {
        $this->store = $store;
        $this->keyName = $keyName;
        $this->cacheTime = $cacheTime;
    }

    /**
     * Check if a cache storage engine has a key
     *
     * @param $key
     * @return mixed
     * @throws CacheMissException
     */
    private function hasKey($key)
    {
        if (!$this->store->has($this->keyName . ':' . $key)) {
            throw new CacheMissException();
        }

        return $this->store->get($this->keyName . ':' . $key);
    }

    /**
     * Let the cache storage engine put in a key
     *
     * @param $key
     * @param $value
     */
    private function putKey($key, $value)
    {
        return $this->store->put($this->keyName . ':' . $key);
    }

    /**
     * Get the int value from cache
     *
     * @param $value
     * @return int
     * @throws CacheMissException when the value is 0
     */
    private function value($value)
    {
        if ((int)$value === 0) {
            throw new CacheMissException();
        }

        return (int)$value;
    }

    /**
     * Get the total amount of items from a cache storage engine to the datatable.
     *
     * @return int
     * @throws CacheMissException
     */
    public function getTotalItems()
    {
        $value = $this->hasKey('total-items');
        return $this->value($value);
    }

    /**
     * Put the total amount of items inside a datatable to a cache storage engine.
     *
     * @param $items
     */
    public function putTotalItems($items)
    {
        $this->putKey('total-items', $items);
    }

    /**
     * Get the total amount of items when a search value has been entered from cache.
     *
     * @param $searchValue
     * @return int
     * @throws CacheMissException when the value does not exist or it is 0.
     */
    public function getTotalItemsWithSearchValue($searchValue)
    {
        $key = 'search:' . $searchValue;

        $value = $this->hasKey($key);
        return $this->value($value);
    }

    /**
     * Put/store the total amount of items when a search value has been entered from the datatable into Cache.
     *
     * @param $searchValue
     * @return int
     * @throws CacheMissException when the value does not exist or it is 0.
     */
    public function putTotalItemsWithSearchValue($searchValue, $items)
    {
        $key = 'search:' . $searchValue;
        $this->putKey($key, $value);
    }
}