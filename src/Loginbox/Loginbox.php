<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox;

use Http\Client\HttpClient;
use Loginbox\Connection\RestClient;
use stdClass;

/**
 * Class Loginbox
 *
 * @package Loginbox
 */
class Loginbox
{
    const API_USER = "api";
    const SDK_VERSION = "1.0";
    const SDK_USER_AGENT = "loginbox-sdk-php";
    const DEFAULT_TIME_ZONE = "UTC";

    /**
     * @var RestClient
     */
    protected $restClient;

    /**
     * @var null|string
     */
    protected $apiKey;

    /**
     * Loginbox constructor.
     *
     * @param string     $apiKey
     * @param HttpClient $httpClient
     */
    public function __construct($apiKey, HttpClient $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->restClient = new RestClient($apiKey, $apiEndpoint = 'api.loginbox.io', $httpClient);
    }

    /**
     * @param string $endpointUrl
     * @param array  $postData
     * @param array  $files
     *
     * @return stdClass
     * @throws Connection\Exceptions\GenericHTTPError
     * @throws Connection\Exceptions\InvalidCredentials
     * @throws Connection\Exceptions\MissingEndpoint
     * @throws Connection\Exceptions\MissingRequiredParameters
     */
    public function post($endpointUrl, $postData = array(), $files = array())
    {
        return $this->restClient->post($endpointUrl, $postData, $files);
    }

    /**
     * @param string $endpointUrl
     * @param array  $queryString
     *
     * @return stdClass
     * @throws Connection\Exceptions\GenericHTTPError
     * @throws Connection\Exceptions\InvalidCredentials
     * @throws Connection\Exceptions\MissingEndpoint
     * @throws Connection\Exceptions\MissingRequiredParameters
     */
    public function get($endpointUrl, $queryString = array())
    {
        return $this->restClient->get($endpointUrl, $queryString);
    }

    /**
     * @param string $endpointUrl
     *
     * @return stdClass
     * @throws Connection\Exceptions\GenericHTTPError
     * @throws Connection\Exceptions\InvalidCredentials
     * @throws Connection\Exceptions\MissingEndpoint
     * @throws Connection\Exceptions\MissingRequiredParameters
     */
    public function delete($endpointUrl)
    {
        return $this->restClient->delete($endpointUrl);
    }

    /**
     * @param string $endpointUrl
     * @param array  $putData
     *
     * @return stdClass
     * @throws Connection\Exceptions\GenericHTTPError
     * @throws Connection\Exceptions\InvalidCredentials
     * @throws Connection\Exceptions\MissingEndpoint
     * @throws Connection\Exceptions\MissingRequiredParameters
     */
    public function put($endpointUrl, $putData)
    {
        return $this->restClient->put($endpointUrl, $putData);
    }

    /**
     * @param string $apiVersion
     *
     * @return $this
     */
    public function setApiVersion($apiVersion)
    {
        $this->restClient->setApiVersion($apiVersion);

        return $this;
    }

    /**
     * @param boolean $sslEnabled
     *
     * @return $this
     */
    public function setSslEnabled($sslEnabled)
    {
        $this->restClient->setSslEnabled($sslEnabled);

        return $this;
    }
}
