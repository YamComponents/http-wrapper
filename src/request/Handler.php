<?php
/**
 * Part of swisstok.ims 2016
 * Created by: deroy on 13.05.16:0:56
 */

namespace yamc\http\wrapper\request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use yamc\http\wrapper\HandlerInterface;

class Handler implements HandlerInterface
{
	/**
	 * @var Client
	 */
	protected $client;
	/**
	 * @var int|array
	 */
	protected $httpExpectation = 200;
	protected $muteExceptions  = false;
	protected $typeExpectation;

	/**
	 * Handler constructor.
	 *
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	protected function clearExpectations()
	{
		$this->muteExceptions  = false;
		$this->httpExpectation = 200;
		$this->typeExpectation = self::DECODED;
	}

	/**
	 * handle expectation on HTTP code and return handling option
	 *
	 * ---
	 *
	 * if you want more complex behavior, you could pass:
	 *  - positive int value if you expect ONLY this code as success - otherwise exception;
	 *  - negative int value if you accept any response less than and including provided as success, otherwise exception;
	 *  - array of positive and negative int enumerating codes threatened as non-exceptional response
	 *    (success (positive int), failure - no exception (negative int), exception (any not listed code)).
	 *
	 * ---
	 *
	 * returns integer indicating following:
	 *  - negative - exception;
	 *  - positive - success;
	 *  - 0 - silent failure (return false, not triggering onUpstreamFailure behavior).
	 *
	 * @param int       $status
	 * @param int|array $expectation HTTP code expectation description
	 *
	 * @return int
	 */
	protected function handleHttpCodeExpectation($status, $expectation)
	{
		//if negative int passed (e.g. -202) return success if status in range otherwise exception
		if (is_int($expectation) && $expectation < 0)
			return ($status <= -$expectation) ? 1 : -1;
		//if array or positive int passed, convert to array
		if (!is_array($expectation))
			$expectation = [$expectation];
		//process enumerated expectations
		/** @noinspection ForeachSourceInspection */
		foreach ($expectation as $code) {
			$result = (int) ($code > 0); //whatever to report success (1) or failure (0)
			$code   = (int) abs($code);
			if ($status === $code)
				return $result;
		}
		//if not in expected enumeration - return exception
		return -1;
	}

	/**
	 * setup type expectation
	 *
	 * @param int $type use constants
	 *
	 * @return HandlerInterface
	 */
	public function give($type)
	{
		$this->typeExpectation = $type;
		return $this;
	}

	/**
	 * handle transfer, using newRequest object from builder
	 *
	 * @param Request $request
	 * @param array   $options
	 *
	 * @return mixed decoded response body
	 * @throws Exception
	 */
	public function handle(Request $request, array $options = [])
	{
		$muteExceptions  = $this->muteExceptions;
		$httpExpectation = $this->httpExpectation;
		$typeExpectation = $this->typeExpectation;
		$this->clearExpectations();
		try {
			$response = $this->client->send($request, $options);
		} catch (BadResponseException $e) {
			$response = $e->getResponse();
		}
		$result = $this->handleHttpCodeExpectation($response->getStatusCode(), $httpExpectation);

		if ($result < 0 && $muteExceptions)
			$result = 0; //raise result to silent if exceptions are muted

		if ($result < 0) {
			$message = 'upstream fail';
			switch ($response->getStatusCode()) {
				case 404:
					$code = Exception::NOT_FOUND;
					break;
				case 429:
					$code = Exception::TOO_MANY_REQUESTS;
					break;
				case 500:
					$code = Exception::INTERNAL_SERVER_ERROR;
					break;
				default:
					//try extract additional info from response
					$m       = json_decode((string) $response->getBody(), true);
					$code    = @$m['code'] ?: Exception::BAD_REQUEST;
					$message = @$m['message'] ?: $message;
			}
			$httpError = $response->getStatusCode() . ':' . $response->getReasonPhrase();
			throw new Exception(
				$message . ':' . $httpError,
				$code,
				isset($e) ? $e : null);
		}

		if ($typeExpectation === self::PSR7)
			return $response;
		if ($typeExpectation === self::BOOL)
			return (bool) $result;

		$body = (string) $response->getBody();

		if ($typeExpectation === self::PLAIN)
			return $body;

		$cType = $response->getHeader(self::HEADER_CTYPE);
		if ([] === $cType)
			return $body;
		$cType = \GuzzleHttp\Psr7\parse_header($cType);
		$cType = trim(@$cType[0][0]);
		if ($cType === 'application/json' && !empty($body))
			return \GuzzleHttp\json_decode($body, true);
		return $body;
	}

	/**
	 * setup expectation for next newRequest
	 *
	 * @param int|array $expectation HTTP code expectation description
	 *
	 * @param bool      $silently mute exceptions
	 *
	 * @return HandlerInterface
	 * @see handleHttpCodeExpectation
	 */
	public function expect($expectation, $silently = false)
	{
		$this->httpExpectation = $expectation;
		$this->muteExceptions  = $silently;
		return $this;
	}
}