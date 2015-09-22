<?php
/**
* IRC bot 
*
* Ircbot
*
* @package project
* @author Serge J. <sergejey@gmail.com>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 14:09:40 [Sep 22, 2015])
*/
//
//
class app_ircbot extends module {
/**
* app_ircbot
*
* Module class constructor
*
* @access private
*/
function app_ircbot() {
  $this->name="app_ircbot";
  $this->title="IRC bot";
  $this->module_category="CMS";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";

  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}

function processSubscription($event, $details='') {
 //to-do
 if ($event=='SAY') {
  $level=$details['level'];
  $message=$details['message'];
  $current_queue=getGlobal('IRCBot1.pushMessage');
  $queue=explode("\n", $current_queue);
  $queue[]=$message;
  if (count($queue)>=25) {
   $queue = array_slice($queue, -25);
  }
  setGlobal('IRCBot1.pushMessage', implode("\n", $queue));
 }
}


function processIncomingMessage($data) {
 //DebMes("IRCBot: ".serialize($data));
 callMethod('IRCBot1.onNewMessage', array('message'=>$data['message'], 'channel'=>$data['channel'], 'from'=>$data['from']));
}

/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {

 $this->getConfig();

 if ($this->view_mode=='update_settings') {
  global $server;
  global $port;
  global $channel;
  global $name;
  global $nick;
  global $pass;
  global $admins;
  global $encoding;

  $ok=1;

  $this->config['SERVER']=$server;
  if (!$this->config['SERVER']) {
   $ok=0;
   $out['ERR_SERVER']=1;
  }

  $this->config['PORT']=$port;
  if (!$this->config['PORT']) {
   $this->config['PORT']=6667;
  }

  $this->config['ENCODING']=$encoding;
  if (!$this->config['ENCODING']) {
   $this->config['ENCODING']='UTF-8';
  }


  $this->config['CHANNEL']=$chennel;
  if (!$this->config['CHANNEL']) {
   $this->config['CHANNEL']='MajorDoMo';
  }

  if (!preg_match('/^\#/', $this->config['CHANNEL'])) {
   $this->config['CHANNEL']='#'.$this->config['CHANNEL'];
  }

  $this->config['NAME']=$name;
  if (!$this->config['NAME']) {
   $this->config['NAME']='AliceBot';
  }

  $this->config['NICK']=$nick;
  if (!$this->config['NICK']) {
   $this->config['NICK']=$this->config['NAME'];
  }

  $this->config['PASS']=$pass;
  $this->config['ADMINS']=str_replace(' ', '', $admins);
  $this->saveConfig();
 }

 if (is_array($this->config)) {
  foreach($this->config as $k=>$v) {
   $out[$k]=$v;
  }
 }
}

function install($data='') {
  parent::install();
  subscribeToEvent($this->name, 'SAY');

  addClass('IRCBots');
  addClassProperty('IRCBots', 'message', 5);
  addClassProperty('IRCBots', 'pushMessage');
  //addClassProperty('IRCBots', 'alive');
  addClassProperty('IRCBots', 'updated');
  addClassProperty('IRCBots', 'updatedTime');
  addClassMethod('IRCBots', 'onNewMessage', '$this->setProperty("updated", time());'."\n".'$this->setProperty("updatedTime", date("H:i"));'."\n".'$this->setProperty("message", $params["message"]);');
  addClassObject('IRCBots', 'IRCBot1');


 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDIyLCAyMDE1IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
