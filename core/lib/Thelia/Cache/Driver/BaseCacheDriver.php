<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/


namespace Thelia\Cache\Driver;

use Doctrine\Common\Cache\Cache;


/**
 * Class CacheDriverInterface
 * @package Thelia\Cache\Driver
 * @author  Julien Chanséaume <jchanseaume@openstudio.fr>
 */
abstract class BaseCacheDriver
{

    /** @var Cache $cache */
    protected $cache = null;

    /** @var bool $silent */
    protected $sleep = false;


    /**
     * Init the cache.
     */
    public abstract function init();

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function fetch($id)
    {
        return !$this->sleep ? $this->cache->fetch($id) : null;
    }

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     *
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id       The cache id.
     * @param mixed  $data     The cache entry/data.
     * @param int    $lifeTime The cache lifetime.
     *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     *
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function save($id, $data, $refs = array(), $lifeTime = 0)
    {
        if ($this->sleep) {
            return false;
        }

        $this->cache->save($id, $data, $lifeTime);

        if (is_array($refs)) {
            foreach ($refs as $ref) {
                $this->addRef($ref, $id);
            }
        } elseif (is_string($refs)){
            $this->addRef($refs, $id);
        }

    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id The cache id.
     *
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    public function delete($id)
    {
        $this->cache->delete($id);
    }

    /**
     * Retrieves cached information from the data store.
     *
     * The server's statistics array has the following values:
     *
     * - <b>hits</b>
     * Number of keys that have been requested and found present.
     *
     * - <b>misses</b>
     * Number of items that have been requested and not found.
     *
     * - <b>uptime</b>
     * Time that the server is running.
     *
     * - <b>memory_usage</b>
     * Memory used by this server to store items.
     *
     * - <b>memory_available</b>
     * Memory allowed to use for storage.
     *
     * @since 2.2
     *
     * @return array|null An associative array with server's statistics if available, NULL otherwise.
     */
    public function getStats()
    {
        $this->cache->getStats();
    }

    protected function addRef($ref, $key)
    {
        $content = $this->cache->fetch($ref);
        if (!is_array($content)) {
            $content = [];
        }
        $content[] = $key;

        return $this->cache->save($ref, $content);
    }

    /**
     * @param $ref
     * @param $key
     *
     * @return bool
     */
    public function deleteRef($ref)
    {
        $deleted = 0;
        $keys    = $this->cache->fetch($ref);
        if (is_array($keys)) {
            foreach ($keys as $key) {
                if ($this->cache->delete($key)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }


    public function sleep()
    {
        $this->sleep = true;
    }

    public function wakeUp()
    {
        $this->sleep = false;
    }



} 