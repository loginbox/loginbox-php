<?php

/*
 * This file is part of loginBox php library.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loginbox\Connection;

use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Loginbox\Connection\Exceptions\GenericHTTPError;
use Loginbox\Connection\Exceptions\InvalidCredentials;
use Loginbox\Connection\Exceptions\MissingEndpoint;
use Loginbox\Connection\Exceptions\MissingRequiredParameters;
use Loginbox\Loginbox;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * Class RestClient
 *
 * @package Loginbox\Connection
 * @version 0.1
 */
class RestClient
{
    /**
     * Your API key
     * @var string
     */
    private $apiKey;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiHost;

    /**
     * The version of the API to use
     * @var string
     */
    protected $apiVersion = 'v1';

    /**
     * If we should use SSL or not
     * @var bool
     */
    protected $sslEnabled = true;

    /**
     * @param string     $apiKey
     * @param string     $apiHost
     * @param HttpClient $httpClient
     */
    public function __construct($apiKey, $apiHost, HttpClient $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->apiHost = $apiHost;
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $endpointUrl
     * @param array  $postData
     * @param array  $files
     *
     * @return stdClass
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function post($endpointUrl, $postData = array(), $files = array())
    {
        $postFiles = [];

        $fields = ['message', 'attachment', 'inline'];
        foreach ($fields as $fieldName) {
            if (isset($files[$fieldName])) {
                if (is_array($files[$fieldName])) {
                    foreach ($files[$fieldName] as $file) {
                        $postFiles[] = $this->prepareFile($fieldName, $file);
                    }
                } else {
                    $postFiles[] = $this->prepareFile($fieldName, $files[$fieldName]);
                }
            }
        }

        $postDataMultipart = [];
        foreach ($postData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $postDataMultipart[] = [
                        'name' => $key,
                        'contents' => $subValue,
                    ];
                }
            } else {
                $postDataMultipart[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
        }

        return $this->send('POST', $endpointUrl, [], array_merge($postDataMultipart, $postFiles));
    }

    /**
     * @param string $endpointUrl
     * @param array  $queryString
     *
     * @return stdClass
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function get($endpointUrl, $queryString = array())
    {
        return $this->send('GET', $endpointUrl . '?' . http_build_query($queryString));
    }

    /**
     * @param string $endpointUrl
     *
     * @return stdClass
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function delete($endpointUrl)
    {
        return $this->send('DELETE', $endpointUrl);
    }

    /**
     * @param string $endpointUrl
     * @param array  $putData
     *
     * @return stdClass
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function put($endpointUrl, $putData)
    {
        return $this->send('PUT', $endpointUrl, $putData);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $body
     * @param array  $headers
     *
     * @return stdClass
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    protected function send($method, $uri, array $body = null, array $headers = [])
    {
        $headers['User-Agent'] = Loginbox::SDK_USER_AGENT . '/' . Loginbox::SDK_VERSION;
        $headers['Authorization'] = 'Basic ' . base64_encode(sprintf('%s:%s', Loginbox::API_USER, $this->apiKey));

        $request = new Request($method, $this->getApiUrl($uri), $headers, $body);
        $response = $this->getHttpClient()->sendRequest($request);

        return $this->responseHandler($response);
    }

    /**
     * @param ResponseInterface $responseObj
     *
     * @return stdClass
     *
     * @throws GenericHTTPError
     * @throws InvalidCredentials
     * @throws MissingEndpoint
     * @throws MissingRequiredParameters
     */
    public function responseHandler(ResponseInterface $responseObj)
    {
        $httpResponseCode = $responseObj->getStatusCode();
        if ($httpResponseCode === 200) {
            // Get response body and parse from json
            $data = (string)$responseObj->getBody();
            $jsonResponseData = json_decode($data, false);

            // Return response data as json if possible, raw if not
            $result = new stdClass();
            $result->http_response_body = $data && $jsonResponseData === null ? $data : $jsonResponseData;
        } elseif ($httpResponseCode == 400) {
            throw new MissingRequiredParameters("The parameters passed to the API were invalid!" . $this->getResponseExceptionMessage($responseObj));
        } elseif ($httpResponseCode == 401) {
            throw new InvalidCredentials("Your credentials are incorrect!");
        } elseif ($httpResponseCode == 404) {
            throw new MissingEndpoint("The endpoint you've tried to access does not exist!" . $this->getResponseExceptionMessage($responseObj));
        } else {
            throw new GenericHTTPError("An HTTP Error has occurred! Check your network connection and try again!", $httpResponseCode, $responseObj->getBody());
        }
        $result->http_response_code = $httpResponseCode;

        return $result;
    }

    /**
     * @param ResponseInterface $responseObj
     *
     * @return string|null
     */
    protected function getResponseExceptionMessage(ResponseInterface $responseObj)
    {
        $body = (string)$responseObj->getBody();
        $response = json_decode($body);
        if (json_last_error() == JSON_ERROR_NONE && isset($response->message)) {
            return ' ' . $response->message;
        }

        return null;
    }

    /**
     * Prepare a file for the postBody.
     *
     * @param string       $fieldName
     * @param string|array $filePath
     *
     * @return array
     */
    protected function prepareFile($fieldName, $filePath)
    {
        $filename = null;

        // Remove leading @ symbol
        if (strpos($filePath, '@') === 0) {
            $filePath = substr($filePath, 1);
        }

        return [
            'name' => $fieldName,
            'contents' => fopen($filePath, 'r'),
            'filename' => $filename,
        ];
    }

    /**
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = HttpClientDiscovery::find();
        }

        return $this->httpClient;
    }

    /**
     * @param $uri
     *
     * @return string
     */
    private function getApiUrl($uri)
    {
        return $this->generateEndpoint($this->apiHost, $this->apiVersion, $this->sslEnabled) . $uri;
    }


    /**
     * @param string $apiEndpoint
     * @param string $apiVersion
     * @param bool   $ssl
     *
     * @return string
     */
    private function generateEndpoint($apiEndpoint, $apiVersion, $ssl)
    {
        return (!$ssl ? "http://" : "https://") . $apiEndpoint . '/' . $apiVersion . '/';
    }

    /**
     * @param string $apiVersion
     *
     * @return RestClient
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @param boolean $sslEnabled
     *
     * @return RestClient
     */
    public function setSslEnabled($sslEnabled)
    {
        $this->sslEnabled = $sslEnabled;

        return $this;
    }
}
