<?php
/*
 * Plugin: Chat Server
 * ~~~~~~~~~~~~~~~~~~~
 * » Displays server/UASECO info & plugins/nations lists.
 * » Based upon chat.server.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-26
 * Copyright:	2014 by undef.de
 * ----------------------------------------------------------------------------------
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ----------------------------------------------------------------------------------
 *
 * Dependencies:
 *  - plugins/plugin.manialinks.php
 *  - plugins/plugin.local_records.php
 *  - plugins/plugin.rasp_votes.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginChatServer();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatServer extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Builds a chat message starting with the nickname from player.');

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::WANTED, '1.0.0', null);
		$this->addDependence('PluginRaspVotes',		Dependence::WANTED, '1.0.0', null);

		$this->registerChatCommand('server',	'chat_server',	'Displays info about this server',		Player::PLAYERS);
		$this->registerChatCommand('uaseco',	'chat_uaseco',	'Displays info about this UASECO',		Player::PLAYERS);
		$this->registerChatCommand('plugins',	'chat_plugins',	'Displays list of active plugins',		Player::PLAYERS);
		$this->registerChatCommand('nations',	'chat_nations',	'Displays top 10 most visiting nations',	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_server ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// collect players/nations stats
		$query = "
		SELECT
			COUNT(`Id`),
			COUNT(DISTINCT `Nation`),
			SUM(`TimePlayed`)
		FROM `players`;
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$row = $res->fetch_row();
				$players = $row[0];
				$nations = $row[1];
				$totaltime = $row[2];

				$playdays = floor($totaltime / (24 * 3600));
				$playtime = $totaltime - ($playdays * 24 * 3600);
			}
			$res->free_result();
		}
		else {
			trigger_error('No players/nations stats found!', E_USER_ERROR);
		}

		// Calculate server uptime
		$updays = floor($aseco->server->networkstats['Uptime'] / (24 * 3600));
		$uptime = $aseco->server->networkstats['Uptime'] - ($updays * 24 * 3600);

		// get more server settings in one go
		$comment = $aseco->server->comment;
		$planets = $aseco->server->amount_planets;
		$cuprpc = $aseco->server->gameinfo->cup['rounts_per_map'];

		$header = 'Welcome to: ' . $aseco->server->name;
		$stats = array();
		$stats[] = array('Server Date', '{#black}' . date('M d, Y'));
		$stats[] = array('Server Time', '{#black}' . date('H:i:s T'));
		$stats[] = array('Zone', '{#black}'. implode(', ', $aseco->server->zone));
		$field = 'Comment';

		// break up long line into chunks with continuation strings
		$multicmt = explode(LF, wordwrap($comment, 35, LF . '...'));
		foreach ($multicmt as $line) {
			$stats[] = array($field, '{#black}' . $line);
			$field = '';
		}

		$stats[] = array('Uptime', '{#black}' . $updays . ' day' . ($updays == 1 ? ' ' : 's ') . $aseco->formatTime($uptime * 1000, false));
		if ($aseco->server->isrelay) {
			$stats[] = array('Relays', '{#black}'.
				$aseco->server->relaymaster['Login'] .
				' / '. $aseco->server->relaymaster['NickName']
			);
		}
		else {
			$stats[] = array('Map Count', '{#black}'. count($aseco->server->maps->map_list));
		}
		$stats[] = array('Game Mode', '{#black}' . $aseco->server->gameinfo->getGamemodeName());
		switch ($aseco->server->gameinfo->mode) {
			case Gameinfo::ROUNDS:
				$stats[] = array('Points Limit', '{#black}'. $aseco->server->gameinfo->rounds['PointsLimit']);
				break;

			case Gameinfo::TIMEATTACK:
				$stats[] = array('Time Limit', '{#black}'. $aseco->formatTime($aseco->server->gameinfo->time_attack['TimeLimit'], false));
				break;

			case Gameinfo::TEAM:
				$stats[] = array('Points Limit', '{#black}'. $aseco->server->gameinfo->team['PointsLimit']);
				break;

			case Gameinfo::LAPS:
				$stats[] = array('Time Limit', '{#black}'. $aseco->formatTime($aseco->server->gameinfo->laps['TimeLimit'], false));
				break;

			case Gameinfo::CUP:
				$stats[] = array('Points Limit', '{#black}'. $aseco->server->gameinfo->cup['PointsLimit'] . '$g   R/C: {#black}' . $cuprpc);
				break;

			case Gameinfo::STUNTS:
				$stats[] = array('Time Limit', '{#black}'. $aseco->formatTime(5 * 60 * 1000, false));  // always 5 minutes?
				break;
		}
		$stats[] = array('Max Players', '{#black}' . $aseco->server->options['CurrentMaxPlayers']);
		$stats[] = array('Max Specs', '{#black}' . $aseco->server->options['CurrentMaxSpectators']);
		if ( isset($aseco->plugins['PluginLocalRecords']) ) {
			$stats[] = array('Recs/Map', '{#black}' . $aseco->plugins['PluginLocalRecords']->settings['max_recs']);
		}
		if (isset($aseco->plugins['PluginRaspVotes']) && $aseco->plugins['PluginRaspVotes']->feature_votes) {
			$stats[] = array('Voting info', '{#black}/helpvote');
		}
		else {
			$stats[] = array('Vote Timeout', '{#black}' . $aseco->formatTime($aseco->server->options['CurrentCallVoteTimeOut'], false));
			$stats[] = array('Vote Ratio', '{#black}' . round($aseco->server->options['CallVoteRatio'], 2));
		}
		if ($aseco->allowAbility($player, 'server_planets')) {
			$stats[] = array('Planets', '{#black}' . $planets);
		}
		$stats[] = array('Ladder Limits', '{#black}' . $aseco->server->ladder_limit_min .
		                  '$g - {#black}' . $aseco->server->ladder_limit_max);
		if ($aseco->settings['admin_contact']) {
			$stats[] = array('Admin Contact', '{#black}' . $aseco->settings['admin_contact']);
		}
		$stats[] = array();
		$stats[] = array('Visited by $f80' . $players . ' $gPlayers from $f40' . $nations . ' $gNations');
		$stats[] = array('who together played: {#black}' . $playdays . ' day' . ($playdays == 1 ? ' ' : 's ') . $aseco->formatTime($playtime * 1000, false) . ' $g!');

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons128x32_1', 'Settings', 0.01), $stats, array(1.0, 0.3, 0.7), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_uaseco ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		$uptime = time() - $aseco->uptime;
		$updays = floor($uptime / (24 * 3600));
		$uptime = $uptime - ($updays * 24 * 3600);

		// prepare Welcome message
		$welcome = $aseco->formatText($aseco->getChatMessage('WELCOME'),
			$aseco->stripColors($player->nickname),
			$aseco->server->name,
			UASECO_VERSION
		);

		$header = 'UASECO info: ' . $aseco->server->name;
		$info = array();
		$info[] = array('Version', '{#black}' . UASECO_VERSION);
		$field = 'Welcome';
		$welcome = preg_split('/{br}/', $aseco->formatColors($welcome));
		foreach ($welcome as $line) {
			$info[] = array($field, '{#black}' . $line);
			$field = '';
		}

		$info[] = array('Uptime', '{#black}' . $updays . ' day' . ($updays == 1 ? ' ' : 's ') . $aseco->formatTime($uptime * 1000, false));
		$info[] = array('Website', '{#black}$l[' . UASECO_WEBSITE . ']' . UASECO_WEBSITE . '$l');
		$info[] = array('Author', '{#black}undef');
		$info[] = array('Credits', '{#black}Main author of XAseco(2): Xymph');
		if (isset($aseco->masteradmin_list['TMLOGIN'])) {
			// count non-LAN logins
			$count = 0;
			foreach ($aseco->masteradmin_list['TMLOGIN'] as $lgn) {
				if ($lgn != '' && !$aseco->isLANLogin($lgn)) {
					$count++;
				}
			}
			if ($count > 0) {
				$field = 'Masteradmin';
				if ($count > 1)
					$field .= 's';
				foreach ($aseco->masteradmin_list['TMLOGIN'] as $lgn) {
					// skip any LAN logins
					if ($lgn != '' && !$aseco->isLANLogin($lgn)) {
						$info[] = array($field, '{#black}'. $aseco->server->players->getPlayerNickname($lgn) .'$z');
						$field = '';
					}
				}
			}
		}
		if ($aseco->settings['admin_contact']) {
			$info[] = array('Admin Contact', '{#black}' . $aseco->settings['admin_contact']);
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('BgRaceScore2', 'Warmup'), $info, array(1.0, 0.3, 0.7), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_plugins ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		$head = 'Currently active plugins:';
		$list = array();
		$list[] = array('Class', 'Version', 'Filename');
		$lines = 0;
		$player->msgs = array();
		$player->msgs[0] = array(1, $head, array(1.3, 0.5, 0.2, 0.6), array('Icons128x128_1', 'Browse'));

		// Create list of plugins
		$plugins = $aseco->plugins;
		ksort($plugins);
		foreach ($plugins as $plugin) {
			$list[] = array(
				'{#black}'. $plugin->getClassname(),
				'{#black}'. $plugin->getVersion(),
				'{#black}'. $plugin->getFilename(),
			);

			if (++$lines > 14) {
				$player->msgs[] = $list;
				$lines = 0;
				$list = array();
				$list[] = array('Class', 'Version', 'Filename');
			}
		}

		// add if last batch exists
		if (!empty($list)) {
			$player->msgs[] = $list;
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_nations ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		$top = 10;
		$query = "
		SELECT
			`Nation`,
			COUNT(`Nation`) AS `Count`
		FROM `players`
		GROUP BY `Nation`
		ORDER BY `Count` DESC
		LIMIT ". $top .";
		";
		$res = $aseco->mysqli->query($query);
		if ($res) {
			// collect and sort nations
			if ($res->num_rows > 0) {
				$nations = array();
				while ($row = $res->fetch_row()) {
					$nations[$row[0]] = $row[1];
				}
			}
			$res->free_result();
		}
		else {
			trigger_error('No players/nations found!', E_USER_WARNING);
			return;
		}
		arsort($nations);

		$header = 'TOP 10 Most Visiting Nations:';
		$nats = array();
		$bgn = '{#black}';  // nation begin

		// compile sorted nations
		$i = 1;
		foreach ($nations as $nat => $tot) {
			$nats[] = array($i++ . '.', $bgn . $nat, $tot);
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons128x128_1', 'Credits'), $nats, array(0.8, 0.1, 0.4, 0.3), 'OK');
	}
}

?>