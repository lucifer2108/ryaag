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

use Doctrine\Common\Cache\MemcachedCache;
use Memcached;


/**
 * Class MemcachedDriver
 * @package Thelia\Cache\Driver
 * @author  Julien Chanséaume <jchanseaume@openstudio.fr>
 */
class MemcachedDriver extends BaseCacheDriver
{

    const CONFIG_SERVER = 'tcache_memcached_server';

    const DEFAULT_SERVER = "localhost";

    const CONFIG_PORT = 'tcache_memcached_port';

    const DEFAULT_PORT = "11211";


    /**
     * Init the cache.
     */
    public function init(array $params = null)
    {
        $memcached = new Memcached();

        $this->initDefault($params);

        $server = $this->getParam(
            $params,
            "server",
            self::CONFIG_SERVER,
            self::DEFAULT_SERVER);

        $port = $this->getParam(
            $params,
            "port",
            self::CONFIG_PORT,
            self::DEFAULT_PORT);

        $memcached->addServer($server, $port);

        $this->cache = new MemcachedCache();

        $this->cache->setMemcached($memcached);

    }

} 