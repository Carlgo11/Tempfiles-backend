<?php

namespace com\carlgo11\tempfiles\api;

use com\carlgo11\tempfiles\datastorage\DataStorage;
use com\carlgo11\tempfiles\exception\BadMethod;
use Exception;

class Cleanup extends API {

	/**
	 * Cleanup constructor.
	 *
	 * @param string|false $method Request {@link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods HTTP method}.
	 * @since 2.4 Changed internals to work with {@see FileStorage}
	 * @since 2.3
	 */
	public function __construct($method) {
		try {
			if ($method !== 'DELETE') throw new BadMethod('Bad method. Use DELETE.');
			DataStorage::deleteOldFiles();
			http_response_code(204);
		} catch (Exception $e) {
			parent::outputJSON(['error' => $e->getMessage()], $e->getCode() ?: 400);
		}
	}
}
