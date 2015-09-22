<?php

chdir(dirname(__FILE__).'/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 
 
include_once("./load_settings.php");
include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

   include_once(DIR_MODULES.'app_ircbot/app_ircbot.class.php');
   $irc=new app_ircbot();


   $irc->getConfig();



   $config['server']=$irc->config['SERVER'];
   $config['port']=$irc->config['PORT'];
   $config['channel']=$irc->config['CHANNEL'];
   $config['name']=$irc->config['NAME'];
   $config['nick']=$irc->config['NICK'];
   $config['pass']=$irc->config['PASS'];
   $config['encoding']=$irc->config['ENCODING'];

   $config['adminNicks']=explode(',', $irc->config['ADMINS']);


   include_once(DIR_MODULES.'app_ircbot/VikingBot/VikingBot.php');


echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

DebMes("Unexpected close of cycle: " . basename(__FILE__));

