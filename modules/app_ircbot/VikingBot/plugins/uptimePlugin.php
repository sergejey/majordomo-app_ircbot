<?php

/**
 * Plugin that responds to "!uptime" messages with information
 * about how long the bot has been running.
 */
class uptimePlugin extends basePlugin {

	private $startTime;

	public function __construct($config, $socket) {
		parent::__construct($config, $socket);
		$this->startTime = new DateTime();
	}

	/**
	 * @return array[]
	 */
	public function help() {
		return array(
			array(
				'command'     => 'uptime',
				'description' => 'Responds with the bot\'s uptime.'
			)
		);
	}

	public function onMessage($from, $channel, $msg) {
		if(stringEndsWith($msg, "{$this->config['trigger']}uptime")) {
			sendMessage($this->socket, $channel, $from.": I have been running for ".$this->makeNiceTimeString($this->startTime->diff(new DateTime())));
		}
	}

	private function makeNiceTimeString($r) {
		if($r->m > 0) {
			return "{$r->m} months, {$r->d} days, {$r->h} hours, {$r->i} minutes & {$r->s} seconds";
		} else if($r->d > 0) {
			return "{$r->d} days, {$r->h} hours, {$r->i} minutes & {$r->s} seconds";
		} else if($r->h > 0) {
			return "{$r->h} hours, {$r->i} minutes & {$r->s} seconds";
		} else if($r->i > 0) {
			return "{$r->i} minutes & {$r->s} seconds";
		} else {
			return "{$r->s} seconds";
		}
	}
}
