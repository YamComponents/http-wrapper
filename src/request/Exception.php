<?php
/**
 * Part of swisstok.ims 2016
 * Created by: deroy on 24.10.16:19:52
 */

namespace yamc\http\wrapper\request;

class Exception extends \Exception
{
	const BAD_REQUEST           = 400;
	const INTERNAL_SERVER_ERROR = 500;
	const NOT_FOUND             = 404;
	const NO_CONTENT            = 204;
	const TOO_MANY_REQUESTS     = 429;
}