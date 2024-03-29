<?php
/**
 * @author           Pierre-Henry Soria <hi@ph7.me>
 * @copyright        (c) 2018, Pierre-Henry Soria. All Rights Reserved.
 * @link             http://pierrehenry.be
 */

namespace Lifyzer\Api;

class AllowCors
{
    private const ALLOW_CORS_ORIGIN_KEY = 'Access-Control-Allow-Origin';
    private const ALLOW_CORS_METHOD_KEY = 'Access-Control-Allow-Methods';
    private const ALLOW_CORS_ORIGIN_VALUE = '*';
    private const ALLOW_CORS_METHOD_VALUE = 'GET, POST, PUT, DELETE, PATCH, OPTIONS';

    /**
     * Initialize the Cross-Origin Resource Sharing (CORS) headers.
     *
     * @link https://en.wikipedia.org/wiki/Cross-origin_resource_sharing More info concerning CORS headers.
     */
    public function init(): void
    {
        $this->set(self::ALLOW_CORS_ORIGIN_KEY, self::ALLOW_CORS_ORIGIN_VALUE);
        $this->set(self::ALLOW_CORS_METHOD_KEY, self::ALLOW_CORS_METHOD_VALUE);
    }

    /**
     * Set data key to value.
     *
     * @param string $sKey The data key.
     * @param string $sValue The data value.
     */
    private function set(string $sKey, string $sValue): void
    {
        header($sKey . ':' . $sValue);
    }
}
