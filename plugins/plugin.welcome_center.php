<?php
/*
 * Plugin: Welcome Center
 * ~~~~~~~~~~~~~~~~~~~~~~
 * » Displays a message in the chat and can display a Welcome-Window on Player connects.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-27
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
 *  - includes/core/window.class.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginWelcomeCenter();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginWelcomeCenter extends Plugin {
	public $config = array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays a message in the chat and can display a Welcome-Window on Player connects.');

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onPlayerConnect1',	'onPlayerConnect1');
		$this->registerEvent('onPlayerDisconnect',	'onPlayerDisconnect');
		$this->registerEvent('onEndMap',		'onEndMap');

		$this->registerChatCommand('message', 'chat_message', 'Shows random informational message', Player::PLAYERS);

		// Init random generator
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float) $sec + ((float) $usec * 100000));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $reload = null) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '1.0.0';
		if ( defined('UASECO_VERSION') ) {
			$version = str_replace(
				array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'),
				array('.1','.2','.3','.4','.5','.6','.7','.8','.9'),
				UASECO_VERSION
			);
			if ( version_compare($version, $uaseco_min_version, '<') ) {
				trigger_error('[WelcomeCenter] Not supported USAECO version ('. $version .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[WelcomeCenter] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}


		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/welcome_center.xml', true, true)) {
			trigger_error('[WelcomeCenter] Could not read/parse config file "config/welcome_center.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['WELCOME_CENTER'];

		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->config['WELCOME_WINDOW'][0]['ENABLED'][0]			= ((strtoupper($this->config['WELCOME_WINDOW'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]	= ((strtoupper($this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]) == 'TRUE')	? true : false);
		$this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]		= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]) == 'TRUE')		? true : false);
		$this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]) == 'TRUE')			? true : false);
		$this->config['INFO_MESSAGES'][0]['ENABLED'][0]				= ((strtoupper($this->config['INFO_MESSAGES'][0]['ENABLED'][0]) == 'TRUE')			? true : false);


		$fields = array();
		$result = $aseco->mysqli->query('SHOW COLUMNS FROM `players_extra`;');
		if ($result) {
			while ($row = $result->fetch_row()) {
				$fields[] = $row[0];
			}
			$result->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_message ($aseco, $login, $chat_command, $chat_parameter) {

		// Get random message
		$i = mt_rand(0, count($this->config['INFO_MESSAGES'][0]['MESSAGES'][0]['MESSAGE']) - 1);
		$message = $aseco->formatColors($this->config['INFO_MESSAGES'][0]['MESSAGE_PREFIX'][0] . $this->config['INFO_MESSAGES'][0]['MESSAGES'][0]['MESSAGE'][$i]);

		// Send the message
		$aseco->sendChatMessage($message, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		if ($this->config['WELCOME_WINDOW'][0]['ENABLED'][0] == true) {
			$skip = false;
			if ($this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0] == true) {
				$query = "SELECT `Avg` FROM `rs_rank` WHERE `PlayerId` = '". $player->id ."';";
				$res = $aseco->mysqli->query($query);
				if ($res) {
					if ($res->num_rows > 0) {
						$skip = true;
					}
					$res->free_result();
				}
			}
			if ($skip == false) {
				// Send it direct to the Player
				$this->buildWelcomeWindow($player);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect1 ($aseco, $player) {

		if ( ($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0] == true) && ($aseco->startup_phase == false) ) {
			// Retrieve the amount of visits
			$visits = 0;
			$query = "
			SELECT
				`Visits`
			FROM `players`
			WHERE `Id` = '". $player->id ."'
			LIMIT 1;
			";
			$res = $aseco->mysqli->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					while ($row = $res->fetch_object()) {
						$visits = $row->Visits;
					}
				}
				$res->free_result();
			}

			// Define Admin/Player title
			$title = 'New Player';
			if ($this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0] == true) {
				$title = $aseco->isMasterAdmin($player) ? '{#logina}'.$aseco->titles['MASTERADMIN'][0] :
					($aseco->isAdmin($player) ? '{#logina}'.$aseco->titles['ADMIN'][0] :
					($aseco->isOperator($player) ? '{#logina}'.$aseco->titles['OPERATOR'][0] :
					'New Player')
				);
			}

			// Setup Ladderrank, Serverrank, Nation and Zone
			$ladderrank = $aseco->formatNumber($player->ladderrank, 0);
			$serverrank = $aseco->plugins['PluginRasp']->getRank($player->login);
			$zone = $player->zone;
			array_shift($zone);		// Remove continent from $zone array

			// Show new Player joins message to all Players
			$message = $this->config['JOIN_LEAVE_INFO'][0]['JOIN_MESSAGE'][0];
			$message = str_replace(
				array(
					'{title}',
					'{nickname}',
					'{continent}',
					'{nation}',
					'{zone}',
					'{visits}',
					'{ladderrank}',
					'{serverrank}',
				),
				array(
					$title,
					$aseco->stripColors($player->nickname),
					$player->continent,
					$player->nation,
					implode(', ', $zone),
					$visits,
					$ladderrank,
					$serverrank,
				),
				$message
			);
			if ($message != '') {
				if ( ($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0] == true) && (function_exists('send_window_message')) ) {
					send_window_message($aseco, $message, false);
				}
				else {
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		if ($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0] == true) {

			// Setup Zone
			$zone = $player->zone;
			array_shift($zone);	// Remove continent from $zone array

			// Show Player leaves message to all remaining Players
			$message = $this->config['JOIN_LEAVE_INFO'][0]['LEAVE_MESSAGE'][0];
			$message = str_replace(
				array(
					'{nickname}',
					'{continent}',
					'{nation}',
					'{zone}',
					'{playtime}',
				),
				array(
					$aseco->stripColors($player->nickname),
					$player->continent,
					$player->nation,
					implode(', ', $zone),
					$aseco->formatTime($player->getTimeOnline() * 1000, false),
				),
				$message
			);
			if ($message != '') {
				if ( ($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0] == true) && (function_exists('send_window_message')) ) {
					send_window_message($aseco, $message, false);
				}
				else {
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	function onEndMap ($aseco, $data) {

		// If not enabled, bail out immediately
		if ($this->config['INFO_MESSAGES'][0]['ENABLED'][0] == false) {
			return;
		}

		// If no info messages, bail out immediately
		if (count($this->config['INFO_MESSAGES'][0]['MESSAGES'][0]['MESSAGE']) == 0) {
			return;
		}

		// Get random message
		$i = mt_rand(0, count($this->config['INFO_MESSAGES'][0]['MESSAGES'][0]['MESSAGE']) - 1);
		$message = $aseco->formatColors($this->config['INFO_MESSAGES'][0]['MESSAGE_PREFIX'][0] . $this->config['INFO_MESSAGES'][0]['MESSAGES'][0]['MESSAGE'][$i]);


		// Send the Message to all connected Players...
		if (strtoupper($this->config['INFO_MESSAGES'][0]['ENABLED'][0]) == 'WINDOW') {
			// ..into message window
			$aseco->releaseEvent('onSendWindowMessage', array($message, false));
		}
		else {
			// ..into chat
			$aseco->sendChatMessage($message, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWelcomeWindow ($player) {
		global $aseco;

		$xml = '';

		// Replace line break markers with line break
		$message = str_replace('{br}', LF, $this->config['WELCOME_WINDOW'][0]['MESSAGE'][0]);
		$message = str_replace('{server}', $aseco->handleSpecialChars($aseco->stripColors($aseco->server->name)), $message);
		$message = str_replace('{player}', $aseco->handleSpecialChars($player->nickname.'$Z'), $message);

		// Set the content
		if ($this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['NORMAL'][0] != '') {
			$xml .= '<quad posn="150 -11.25 0.02" sizen="45 87.1875" image="'. $this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['NORMAL'][0] .'"';
			if ($this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['FOCUS'][0] != '') {
	 			$xml .= ' imagefocus="'. $this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['FOCUS'][0] .'"';
			}
			if ($this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['LINK'][0] != '') {
	 			$xml .= ' url="'. $this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['LINK'][0] .'"';
			}
			$xml .= '/>';
			$xml .= '<label posn="7.5 -11.25 0.01" sizen="137.5 0" autonewline="1" textsize="1" textcolor="FF0F" text="'. $message .'"/>';
		}
		else {
			$xml .= '<label posn="7.5 -11.25 0.01" sizen="181 0" autonewline="1" textsize="1" textcolor="FF0F" text="'. $message .'"/>';
		}


		// Setup settings for Window
		$settings_title = array(
			'icon'	=> $this->config['WELCOME_WINDOW'][0]['ICON_STYLE'][0] .','. $this->config['WELCOME_WINDOW'][0]['ICON_SUBSTYLE'][0],
		);
		$window = new Window();
		$window->setLayoutTitle($settings_title);
		$window->setContent('Welcome to '. $aseco->stripColors($aseco->server->name) .'!', $xml);
		$window->send($player, 0, false);
	}
}

?>