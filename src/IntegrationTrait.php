<?php
/**
 * Part of swisstok.ims 2016
 * Created by: deroy on 24.10.16:18:54
 */

namespace yamc\http\wrapper;

trait IntegrationTrait
{
	/**
	 * @var BuilderInterface
	 */
	private $requestBuilder;
	/**
	 * @var HandlerInterface
	 */
	private $handler;
	/**
	 * @var array request masked paths
	 */
	protected $requestPathMap = [];

	/**
	 * set builder instance
	 *
	 * @param BuilderInterface $requestBuilder
	 *
	 * @return $this
	 */
	public function setBuilder(BuilderInterface $requestBuilder)
	{
		$this->requestBuilder = $requestBuilder;
		return $this;
	}

	/**
	 * set handler instance
	 *
	 * @param HandlerInterface $handler
	 *
	 * @return $this
	 */
	public function setHandler(HandlerInterface $handler)
	{
		$this->handler = $handler;
		return $this;
	}

	/**
	 * @param array $paths
	 *
	 * @return $this
	 */
	protected function addRequestPaths(array $paths)
	{
		$this->requestPathMap = $this->requestPathMap ? array_merge($this->requestPathMap, $paths) : $paths;
		return $this;
	}

	/**
	 * get actual path for newRequest
	 *
	 * @param string $requestId one of PATH_* constants
	 * @param array  $params path parameters to insert as `['{key}'=>value]`
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function computeRequestPath($requestId, array $params = [])
	{
		if (!isset($this->requestPathMap[$requestId]))
			throw new \RuntimeException("Request `{$requestId}` does not exists");
		$path = $this->requestPathMap[$requestId];
		return $params ? strtr($path, $params) : $path;
	}

	/**
	 * start new builder to form a newRequest
	 *
	 * @param string $requestId one of PATH_* constants
	 * @param array  $params path parameters to insert as `['{key}'=>value]`
	 *
	 * @return BuilderInterface
	 * @uses computeRequestPath
	 */
	protected function newRequest($requestId, array $params = [])
	{
		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		return $this->requestBuilder->request($this->computeRequestPath($requestId, $params));
	}

	/**
	 * setup response expectation
	 *
	 * @param int|array $expectation
	 * @param bool      $silently
	 *
	 * @return HandlerInterface
	 */
	protected function expect($expectation, $silently = false)
	{
		return $this->handler->expect($expectation, $silently);
	}
}