# VikingBot
*VikingBot is yet another simple PHP based IRC bot with support for plugins and secure IRC servers.*<br/>
The bot requires Unix/Linux shell access with PHP and SSL support in PHP for use against secure IRC servers.

### INSTALLING
1. copy config.dist.php to config.php `cp config.dist.php config.php`
2. update config.php with correct settings `nano config.php` (undeclared settings will be declared by config.dist.php)
3. run the start script `sh start.sh`
4. check log output `cat logs/vikingbot.log`

You can define different config files: `sh start.sh config=anotherConfigFile.php`

### SUPPORTED COMMANDS
The following commands are supported out of the box (aka they are not controlled by plugins):
* `!exit [adminPassword]` Shuts the bot down
* `!restart [adminPassword]` Restarts the bot
* `!help [command]` Sends a list of commands or the description of a given command to the user

The following commands are supported via the plugins that are installed by default:

* `!botlog [adminPassword] [rows=10]` The bot responds with the [rows] last rows of the bot log file
* `!memory` The bot responds with memory usage statistics
* `!ping` The bot responds with a pong to say that it is still alive
* `!uptime` The bot responds with the it's uptime
* `!upgrade [adminPassword]` The bot will attempt to upgrade itself and its plugins to the latest version via `git pull`
* `!op <nick> <channel> [adminPassword]` The bot attempts to give *user* on *channel* OP status

### INSTALLED PLUGINS
The following plugins are installed by default:
* fileReaderPlugin
	* Outputs data from db/fileReaderOutput.db to channel specified in the plugin, useful for GIT/SVN commit hooks or anything other that should push data to a channel
* botLogPlugin
	* Plugin that responds to `!botlog` with the last N rows of the bot's log file
* memoryPlugin
	* Plugin that responds to `!memory` with information about memory usage
* opPlugin
	* Plugin that respons to `!op ...` by oping a user if the bot has op itself
* pingPlugin
	* Plugin that responds to `!ping` with a "PONG".
* uptimePlugin
    * Plugin that responds to `!uptime` with the bots uptime
* rssPlugin
	* Plugin that pulls RSS feeds at specified intervals and outputs new RSS elements to a specified channel
* upgradePlugin
    * Plugin that upgrades the bot and its plugins via `git pull`
* autoOpPlugin
	* Plugin that gives +o to everyone or to certain nicks on channel join.

### THIRD-PARTY PLUGINS
Links to other plugins for VikingBot:
* [A NickServ authentication plugin](https://github.com/DasKaktus/VikingBot-NickAuth-Plugin)
* [A doorway/plugin for Roundup Issue Tracker](https://gist.github.com/3295338)
* [A plugin with various IMDB commands](https://github.com/hashworks/VikingBot-IMDB-Plugin)
* [A plugin to check if a page is reachable using the popular isup.me website](https://github.com/hashworks/VikingBot-IsDown-Plugin)
* [A plugin to access the xREL.to API](https://github.com/hashworks/VikingBot-xREL-Plugin)
* [A plugin which shows the title of posted YouTube links](https://github.com/hashworks/VikingBot-Youtube-Plugin)
* [A plugin which converts posted gifs to gfycat/webm links](https://github.com/hashworks/VikingBot-Gfycat-Plugin)
* [A plugin which checks for reposts of urls and credits the original poster](https://github.com/hashworks/VikingBot-RepostCheck-Plugin)

Make sure to install your plugins into the thirdparty-plugins folder so git ignores them!

### TEXT FORMATTING
If you wish, you can format text the bot sends to a channel/user  via your plugins. Use any of the following codes to apply the relevant text color or format. The text will keep the given format until either end of string, or the {reset} tag.

**Available colors:**<br/>
{white}, {black}, {blue}, {green}, {red}, {darkRed}, {purple}, {orange}, {yellow}, {lime}, {teal}, {cyan}, {lightBlue}, {pink}, {grey} & {lightGrey}

**Other tags:**<br/>
{reset}, {bold} & {underline}

**Example:**<br/>
"{bold}**i am bold and** {red}**red**{reset}, but now i am normal"

PS: Different IRC-clients may display colors differently, some servers may even deny color usage!

### BUGS/PROBLEMS?
Feel free to contact me via IRC on EfNet/Freenode/Undernet (Ueland) or via e-mail: tor.henning AT gmail.com.
