<?php

                //Server name, prefix it with "ssl://" in order to use SSL server
//$config['server']        = 'servername';
                //Server port
//$config['port']          = 6666;
                //Should we verify the ssl certficate of the server?
$config['verifySSL']     = false;
                //Channel to join; use array('#channel1'; '#channel2') for multiple channels
//$config['channel']       = '#vikingbot';
                //Name of the bot
//$config['name']          = 'vikingbot';
                //Nick of the bot
//$config['nick']          = 'vikingbot';
                //Server password
//$config['pass']          = '';
                //How many seconds to wait before joining channel after connecting to server
$config['waitTime']      = 10;
                //A list of admin nicks. This isn't used for authentification but for altering of the help command response.
//$config['adminNicks']    = array();
                //Bot admin password; used for commands like !exit (!exit vikingbot)
$config['adminPass']     = 'vikingbot';
                //Max memory the bot can use; in MB
$config['memoryLimit']   = '128';
                //Min memory usage; in MB. (The bot will try to clear RAM or restart if reached)
$config['memoryRestart'] = '10';
                //What character should be used as bot command prefixes
$config['trigger']       = '!';
                //Max messgages a user can send per 10 minutes before beeing ignored for that time
$config['maxPerTenMin']  = 50;

//=====================================
//Plugin specific configuration
//=====================================

//An array of disabled Plugins
$config['disabledPlugins'] = array();

//RSS Reader
//$config['plugins']['rssReader'] = array(
//              array('title'=> 'VG',           'url'=>'http://www.vg.no/rss/nyfront.php?frontId=1',    'pollInterval'=>15,     'channel'=>'#vikingbot'),
//              array('title'=> 'BBC News',     'url'=>'http://feeds.bbci.co.uk/news/rss.xml',                  'pollInterval'=>15,     'channel'=>'#vikingbot'),
//              array('title'=> 'CNN',          'url'=>'http://rss.cnn.com/rss/edition.rss',                    'pollInterval'=>15,     'channel'=>'#vikingbot'),
//);

//File reader
//$config['plugins']['fileReader'] = array(
//              'channel' => '#vikingbot',
//);

//Auto Op
//$config['plugins']['autoOp'] = array(
//              'mode'    => '0', // autop mode, 0 = disabled, 1 = only configured users, 2 = autoop everyone
//              'channel' => array(
//                              '#channel1'     => array('nick1','nick2','nick3','nick4','nick5','nick6','nick7','nick8'),
//                              '#channel2'     => array('ueland','ernini')
//              ),
//);
