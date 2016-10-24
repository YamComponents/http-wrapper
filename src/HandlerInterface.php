<?php
/**
 * Part of swisstok.ims 2016
 * Created by: deroy on 24.10.16:19:20
 */
namespace yamc\http\wrapper;

use GuzzleHttp\Psr7\Request;
use yamc\http\wrapper\request\Exception;

interface HandlerInterface
{
	const BOOL         = 1;
	const DECODED      = 3;
	const HEADER_CTYPE = 'Content-Type';
	const PLAIN        = 2;
	const PSR7         = 7;

	/**
	 * setup type expectation
	 *
	 * @param int $type use constants
	 *
	 * @return HandlerInterface
	 */
	public function give($type);

	/**
	 * handle transfer, using newRequest object from builder
	 *
	 * @param Request $request
	 * @param array   $options
	 *
	 * @return mixed decoded response body
	 * @throws Exception
	 */
	public function handle(Request $request, array $options = []);

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
	public function expect($expectation, $silently = false);
}