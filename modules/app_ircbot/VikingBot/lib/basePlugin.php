<?php

class basePlugin {

	protected $config, $socket;

	/**
	 * Called when plugins are loaded
	 *
	 * @param mixed[]	$config
	 * @param resource 	$socket
	 */
	public function __construct($config, $socket) {
		$this->config = $config;
		$this->socket = $socket;
	}

	/**
	 * Your plugin response to the help command.
	 * If your plugin features no commands simply return an empty array,
	 * or leave the function out.
	 *
	 * @return array[]
	 */
	public function help() {
		return array();
	}

	/**
	 * Called about twice per second or when there is
	 * activity on the channel the bot is in.
	 * Put your jobs that needs to be run without user interaction here.
	 */
	public function tick() {
	}

	/**
	 * Called when messages are posted on the channel
	 * the bot is in, or when somebody talks to it
	 *
	 * @param string $from
	 * @param string $channel
	 * @param string $msg
	 */
	public function onMessage($from, $channel, $msg) {
	}

	/**
	 * Called when the bot is shutting down
	 */
	public function __destruct() {
	}
}
