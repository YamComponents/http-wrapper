<?php
/**
 * Part of swisstok.ims 2016
 * Created by: deroy on 24.10.16:19:14
 */
namespace yamc\http\wrapper;

use GuzzleHttp\Psr7\Request;

interface BuilderInterface
{
	const METHOD_DELETE = 'DELETE';
	const METHOD_GET    = 'GET';
	const METHOD_PATCH  = 'PATCH';
	const METHOD_POST   = 'POST';
	const METHOD_PUT    = 'PUT';

	/**
	 * @param mixed $method
	 *
	 * @return BuilderInterface
	 */
	public function setMethod($method);

	/**
	 * clears internal builder state
	 */
	public function clear();

	/**
	 * build actual low level newRequest object
	 *
	 * @return Request
	 */
	public function build();

	/**
	 * initialize new Builder object with path value and return it
	 *
	 * factory
	 *
	 * @param string $path
	 *
	 * @return BuilderInterface
	 */
	public function request($path);

	/**
	 * @param array $queryParams
	 *
	 * @return BuilderInterface
	 */
	public function withQueryParams(array $queryParams);

	/**
	 * @param mixed $body
	 *
	 * @return BuilderInterface
	 */
	public function withBody($body);

	/**
	 * @param array $headers
	 *
	 * @return BuilderInterface
	 */
	public function withHeaders($headers);

	/**
	 * set request method to POST
	 * @return $this
	 */
	public function post();

	/**
	 * set request method to GET
	 * @return $this
	 */
	public function get();

	/**
	 * set request method to PUT
	 * @return $this
	 */
	public function put();

	/**
	 * set request method to DELETE
	 * @return $this
	 */
	public function delete();

	/**
	 * set request method to PATCH
	 * @return $this
	 */
	public function patch();
}