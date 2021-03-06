<?php

/**
 * Plugin that responds to "!ping" messages with a pong
 * simply to verify that the bot is alive
 */
class majordomoPlugin extends basePlugin {

        public function __construct($config, $socket) {
                parent::__construct($config, $socket);
                $this->last_check = 0;
        }

        /**
         * @return array[]
         */
        public function help() {
        /*
                return array(
                        array(
                                'command'     => 'ping',
                                'description' => 'Returns PONG.'
                        )
                );
        */
        }

        public function tick() {
         global $config;
         if ((time()-$this->last_check)>3) {
          $queue_data=getGlobal('IRCBot1.pushMessage');
          $this->last_check=time();
          if ($queue_data!='') {
           setGlobal('IRCBot1.pushMessage', '');
           $messages=explode("\n", $queue_data);
           $total=count($messages);
           for($i=0;$i<$total;$i++) {
            if ($messages[$i]) {
             sendMessage($this->socket, $this->config['channel'], iconv('UTF-8', $config['encoding'], $messages[$i]));
            }
           }
          }
          setGlobal('cycle_app_ircbotRun', time(), 1);

          if (file_exists('./reboot'))   {      
           global $db;        
           $db->Disconnect();      
           exit;   
          }

         }
        }


        public function onMessage($from, $channel, $msg) {
         global $config;
         if ($from!='' || $channel!='') {
          global $irc;
          $data=$irc->processIncomingMessage(array('message'=>iconv($config['encoding'], 'UTF-8', $msg), 'from'=>iconv($config['encoding'], 'UTF-8', $from), 'channel'=>iconv($config['encoding'], 'UTF-8', $channel)));
          if ($data && $data!=1) {
           sendMessage($this->socket, $channel, $from.': '.iconv('UTF-8', $config['encoding'], $data));
          }
         }

        }

}
