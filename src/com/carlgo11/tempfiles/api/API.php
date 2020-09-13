<?php

namespace com\carlgo11\tempfiles\api;

class API {

	protected array $_messages = [];

	public function addMessage(string $key, $value) {
		return ($this->_messages[$key] = $value) === $value;
	}

	/**
	 * @param array $messages
	 * @return bool Returns true if successful, otherwise false.
	 */
	public function addMessages(array $messages) {
		return ($this->_messages = array_merge($this->_messages, $messages)) !== NULL;
	}

	public function removeMessage(string $key) {
		unset($this->_messages[$key]);
		return TRUE;
	}

	public function outputJSON(int $HTTPCode = 200) {
		header('Access-Control-Allow-Origin: *'); // Allows other domains to send data to the API.
		header('Content-Type: application/json; charset=utf-8');
		http_response_code($HTTPCode);
		$json = json_encode($this->getMessages(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		return print($json);
	}

	public function getMessages() {
		return $this->_messages;
	}
}