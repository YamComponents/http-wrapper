<?php
/**
 * Part of swisstok.ims 2016
 * Created by: deroy on 12.05.16:21:23
 */

namespace yamc\http\wrapper\request;

use GuzzleHttp\Psr7\Request;
use yamc\http\wrapper\BuilderInterface;

class Builder implements BuilderInterface
{
	protected $method;
	protected $path;
	protected $queryParams = [];
	protected $body;
	protected $headers     = [];

	/**
	 * @param mixed $method
	 *
	 * @return BuilderInterface
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @return mixed
	 */
	protected function buildUrl()
	{
		$query = (count($this->queryParams) > 0) ? '?' . $this->createPathInfo($this->queryParams, '=', '&') : '';
		return $this->path . $query;
	}

	protected function createPathInfo($params, $equal, $ampersand)
	{
		$pairs = [];
		foreach ($params as $k => $v) {
			$pairs[] = urlencode($k) . $equal . urlencode($v);
		}
		return implode($ampersand, $pairs);
	}

	/**
	 * clears internal builder state
	 */
	public function clear()
	{
		$this->method      = null;
		$this->path        = null;
		$this->queryParams = [];
		$this->body        = null;
		$this->headers     = [];
	}

	/**
	 * build actual low level newRequest object
	 *
	 * @return Request
	 * @throws \InvalidArgumentException
	 */
	public function build()
	{
		return new Request($this->method, $this->buildUrl(), $this->headers, $this->body);
	}

	/**
	 * initialize new Builder object with path value and return it
	 *
	 * factory
	 *
	 * @param string $path
	 *
	 * @return BuilderInterface
	 */
	public function request($path)
	{
		$new       = new static();
		$new->path = $path;
		return $new;
	}

	/**
	 * @param array $queryParams
	 *
	 * @return BuilderInterface
	 */
	public function withQueryParams(array $queryParams)
	{
		$this->queryParams = $queryParams;
		return $this;
	}

	/**
	 * @param mixed $body
	 *
	 * @return BuilderInterface
	 */
	public function withBody($body)
	{
		if (!is_string($body))
			$body = \GuzzleHttp\json_encode($body);
		$this->body = $body;
		return $this;
	}

	/**
	 * @param array $headers
	 *
	 * @return BuilderInterface
	 */
	public function withHeaders($headers)
	{
		$this->headers = $headers;
		return $this;
	}

	public function post()
	{
		return $this->setMethod(self::METHOD_POST);
	}

	public function get()
	{
		return $this->setMethod(self::METHOD_GET);
	}

	public function put()
	{
		return $this->setMethod(self::METHOD_PUT);
	}

	public function delete()
	{
		return $this->setMethod(self::METHOD_DELETE);
	}

	public function patch()
	{
		return $this->setMethod(self::METHOD_PATCH);
	}
}