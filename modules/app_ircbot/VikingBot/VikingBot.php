<?php

parse_str(implode('&', array_slice($argv, 1)), $_GET);


$configFile = 'config.php';
if (isset($_GET['config'])) {
        $configFile = trim($_GET['config']);
}

if(!is_file($configFile)) {
        die("You have not created a " . $configFile . " yet.\n");
}

require_once(dirname(__FILE__)."/config.dist.php");
require_once(dirname(__FILE__).'/'.$configFile);


require_once(dirname(__FILE__)."/lib/functions.php");
require_once(dirname(__FILE__)."/lib/basePlugin.php");

set_time_limit(0);
error_reporting(E_ALL);
date_default_timezone_set('GMT');
declare(ticks = 1);

set_error_handler("errorHandler");

class VikingBot {

        // $floodDb: Internal database over user activity to keep flodders away
        var $socket, $startTime, $plugins, $config, $floodDb, $lastServerMessage;
        var $inChannel         = false;
        var $lastMemCheckTime  = 0;

        var $helpData = array(
                array(
                        'command'     => 'help [command]',
                        'description' => 'Sends a list of commands or the description of a given command to the user.'
                ),
                array(
                        'command'     => 'exit [adminPassword]',
                        'description' => 'Shuts down the bot.'
                ),
                array(
                        'command'     => 'restart [adminPassword]',
                        'description' => 'Restarts the bot.'
                )
        );

        function __construct($config) {

                if (extension_loaded("pcntl")) {
                        //Add signal handlers to shut down the bot correctly if its getting killed
                        pcntl_signal(SIGTERM, array($this, "signalHandler"));
                        pcntl_signal(SIGINT, array($this, "signalHandler"));
                } else {
                        //die("Please make sure the pcntl PHP extension is enabled.\n");
                }

                $this->config            = $config;
                $this->startTime         = time();
                $this->lastServerMessage = $this->startTime;

                ini_set("memory_limit", $this->config['memoryLimit']."M");
                if ($config['verifySSL']) {
                        $this->socket = stream_socket_client("".$config['server'].":".$config['port']) or die("Connection error!");
                } else {
                        $socketContext = stream_context_create(array(
                                "ssl"=>array(
                                        "verify_peer"       => false,
                                        "verify_peer_name"  => false
                                ),
                        ));
                        $this->socket = stream_socket_client("".$config['server'].":".$config['port'], $errno, $errstr, 30,
                                                                                                        STREAM_CLIENT_CONNECT, $socketContext) or die("Connection error!");
                }
                stream_set_blocking($this->socket, 0);
                stream_set_timeout($this->socket, 600);
                $this->login();
                $this->loadPlugins();
                $this->main($config);
        }

        function loadPlugins() {
                $this->plugins = array();
                $pluginsRecDirIterator = new RecursiveDirectoryIterator(dirname(__FILE__).'/plugins');
                foreach (new RecursiveIteratorIterator($pluginsRecDirIterator) as $filename) {
                        if(stringEndsWith($filename, '.php')) {
                                $content = file_get_contents($filename);
                                if (preg_match("/class (.+) extends basePlugin/", $content, $matches)) {
                                        $pName = $matches[1];
                                        if (!in_array($pName, $this->config['disabledPlugins'])) {
                                                require_once($filename);
                                                logMsg("Loading plugin " . $pName);
                                                $this->plugins[] = new $pName($this->config, $this->socket);
                                        }
                                }
                        }
                }
                foreach($this->plugins as $plugin) {
                        $pluginHelpData = $plugin->help();
                        if (isset($pluginHelpData[0]['command'])) {
                                $this->helpData = array_merge($this->helpData, $pluginHelpData);
                        }
                }
        }

        function login() {
                if(strlen($this->config['pass']) > 0) {
                        sendData($this->socket, "PASS {$this->config['pass']}");
                }
                sendData($this->socket, "NICK {$this->config['nick']}");
                sendData($this->socket, "USER {$this->config['name']} 0 *: {$this->config['name']}");
        }

        function main() {

                while(true) {
                        //Sleep a bit, no need to hog all CPU resources
                        usleep(100000);

                        //Join channels if not already joined
                        if( !$this->inChannel && (time() - $this->config['waitTime']) > $this->startTime ) {
                                $this->joinChannel($this->config['channel']);
                                sleep(2);
                                $this->inChannel = true;
                        }

                        //Run scheduled memory check
                        if(time() - 600 > $this->lastMemCheckTime) {
                                $this->lastMemCheckTime = time();
                                $this->doMemCheck();
                        }

                        //Tick plugins
                        foreach($this->plugins as $plugin) {
                                $plugin->tick();
                        }

                        //Load data from IRC server
                        $data = fgets($this->socket, 256);
                        if(strlen($data) > 0) {
                                $this->lastServerMessage = time();
                                logMsg("<Server to bot> ".$data);
                                $bits = explode(' ', $data);
                                if($bits[0] == 'PING') {
                                        sendData($this->socket, "PONG {$bits[1]}"); //Ping? Pong!
                                } else if($bits[0] == 'ERROR') {
                                        logMsg("Error from server, trying to reconnect in 2 minutes");
                                        $this->prepareShutdown("");
                                        sleep(120);
                                        doRestart();
                                }
                                $from = getNick($bits[0]);

                                if($this->antiFlood($from)) {

                                        if(isset($bits[2])) {
                                                $chan = trim($bits[2]);
                                        }

                                        if(isset($chan[0]) && $chan[0] != '#') {
                                                $chan = $from;
                                        }

                                        if(isset($bits[3])) {
                                                $cmd = trim($bits[3]);
                                                switch($cmd) {
                                                        case ":{$this->config['trigger']}exit":
                                                                $this->shutdown($bits[4], $from, $chan);
                                                        break;

                                                        case ":{$this->config['trigger']}restart":
                                                                $this->restart($bits[4], $from, $chan);
                                                        break;

                                                        case ":{$this->config['trigger']}help":
                                                                if (isset($bits[4])) {
                                                                        $this->help($from, $chan, $bits[4]);
                                                                } else {
                                                                        $this->help($from, $chan);
                                                                }
                                                        break;
                                                }
                                                $cmd = null;
                                        }

                                        if($bits[1] == 'PRIVMSG') {

                                                $msg = @substr($bits[3], 1);
                                                for($i=4; $i<count($bits); $i++) {
                                                        $msg .= ' '.$bits[$i];
                                                }
                                                $msg = trim($msg);
                                                foreach($this->plugins as $plugin) {
                                                        $plugin->onMessage($from, $chan, $msg);
                                                }
                                                $msg = null;
                                        } else {
                                                foreach($this->plugins as $plugin) {
                                                        if(method_exists($plugin, 'onData'))
                                                                $plugin->onData($data);
                                                }
                                        }
                                } else {
                                        logMsg("Ignoring {$from} due to flooding");
                                }
                                $bits = null;
                                $from = null;
                                $chan = null;
                                $bits = null;
                        }
                        $data = null;

                        if ($this->lastServerMessage < (time() - (60*5))) {
                                sendData($this->socket, "PING {$this->config['server']}");
                        }

                        if ($this->lastServerMessage < (time() - (60*10))) {
                                logMsg("Last server message/ping was >10min ago, going to restart the bot.");
                                $this->prepareShutdown("");
                                doRestart();
                        }

                }
        }

        function doMemCheck() {
                //Run garbage collection
                gc_collect_cycles();
                $memFree = ((($this->config['memoryLimit']*1024)*1024) - memory_get_usage());
                if($memFree < (($this->config['memoryRestart']*1024)*1024)) {
                        $this->prepareShutdown("Out of memory, restarting...");
                        doRestart();
                }
        }

        function joinChannel($channel) {
                if (is_array($channel)) {
                        logMsg("Joining channels " . join(", ", $channel));
                } else {
                        logMsg("Joining channel {$channel}");
                }
                if(is_array($channel)) {
                        foreach($channel as $chan) {
                                sendData($this->socket, "JOIN {$chan}");
                        }
                } else {
                        sendData($this->socket, "JOIN {$channel}");
                }
        }


        function restart($pass, $from, $chan) {
                if(!$this->correctAdminPass($pass)) {
                        sendMessage($this->socket, $chan, "{$from}: Wrong password");
                        return false;
                }
                sendMessage($this->socket, $chan, "{$from}: Restarting...");
                $this->prepareShutdown("");
                doRestart();
        }

        function prepareShutdown($msg) {
                if(strlen($msg) == 0) {
                        $msg = "VikingBot - https://github.com/Ueland/VikingBot";
                }
                sendData($this->socket, "QUIT :{$msg}");
        }

        function shutdown($pass, $from, $chan) {
                if(!$this->correctAdminPass($pass)) {
                        sendMessage($this->socket, $chan, "{$from}: Wrong password");
                        return false;
                }
                sendMessage($this->socket, $chan, "{$from}: Shutting down...");
                $this->prepareShutdown("");
                exit();
        }

        function help($from, $channel, $command = "") {
                $command = trim($command);
                if (empty($command)) {
                        if ($channel != $from) {
                                sendMessage($this->socket, $channel, $from . ': Sending you all available commands by PM.');
                        }
                        foreach ($this->helpData as $commandArray) {
                                if (in_array($from, $this->config['adminNicks']) || strpos(strtolower($commandArray['command']), 'password') === false) {
                                        sendMessage($this->socket, $from, $commandArray['command'] . " - " . $commandArray['description']);
                                }
                        }
                } else {
                        $commandFound = false;
                        foreach ($this->helpData as $commandArray) {
                                $split = explode(" ", $commandArray['command']);
                                if ($split[0] == $command && (in_array($from, $this->config['adminNicks']) || strpos(strtolower($commandArray['command']), 'password') === false)) {
                                        sendMessage($this->socket, $channel, $from . ": " . $commandArray['command'] . " - " . $commandArray['description']);
                                        $commandFound = true;
                                        break;
                                }
                        }
                        if ($commandFound === false) {
                                sendMessage($this->socket, $channel, $from . ": This command doesn't exist!");
                        }
                }
        }

        function correctAdminPass($pass) {
                $pass = trim($pass);
                if(strlen($this->config['adminPass']) > 0) {
                        if($pass == $this->config['adminPass']) {
                                return true;
                        } else {
                                return false;
                        }
                } else {
                        return true;
                }
        }

        function signalHandler($signal) {
                $this->prepareShutdown("QUIT :Caught signal {$signal}, shutting down");
                logMsg("Caught {$signal}, shutting down\n");
                exit();
        }

        function antiFlood($user) {
                $interval = substr(date('i'), 0, 1);
                if(isset($this->floodDb[$interval])) {
                        $floodData = $this->floodDb[$interval];
                } else {
                        $floodData = array();
                }
                if(isset($floodData[$user])) {
                        $floodData[$user]++;
                        if($floodData[$user] > $this->config['maxPerTenMin']) {
                                return false;
                        }
                } else {
                        $floodData[$user] = 1;
                }
                $this->floodDb = array($interval=>$floodData);
                return true;
        }
}

//Start the bot

while (true) {
 $bot = new VikingBot($config);
}
