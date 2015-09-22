<?php

/**
 * Plugin that responds to "!ping" messages with a pong
 * simply to verify that the bot is alive
 */
class pingPlugin extends basePlugin {

	/**
	 * @return array[]
	 */
	public function help() {
		return array(
			array(
				'command'     => 'ping',
				'description' => 'Returns PONG.'
			)
		);
	}

	public function onMessage($from, $channel, $msg) {
		if(stringEndsWith($msg, "{$this->config['trigger']}ping")) {
			sendMessage($this->socket, $channel, $from.": Pong");
		}
	}

}
