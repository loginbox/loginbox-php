<?php
namespace Loginbox\Connection\Exceptions;

use Exception;

/**
 * Class GenericHTTPError
 *
 * @package Loginbox\Connection\Exceptions
 */
class GenericHTTPError extends Exception
{
    /**
     * @type int
     */
    protected $httpResponseCode;

    /**
     * @type mixed
     */
    protected $httpResponseBody;

    /**
     * GenericHTTPError constructor.
     *
     * @param string         $message
     * @param int            $response_code
     * @param mixed          $response_body
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = null, $response_code = null, $response_body = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->httpResponseCode = $response_code;
        $this->httpResponseBody = $response_body;
    }

    /**
     * @return int|null
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }

    /**
     * @return mixed|null
     */
    public function getHttpResponseBody()
    {
        return $this->httpResponseBody;
    }
}

?>
