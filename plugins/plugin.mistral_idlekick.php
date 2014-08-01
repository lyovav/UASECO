<?php
/*
 * Plugin: Player Infos
 * ~~~~~~~~~~~~~~~~~~~~
 * » Kick idle Players to let waiting spectators play.
 * » Based upon mistral.idlekick.php from XAseco2/1.03 written by Mistral and Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-21
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
 *  - none
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginMistralIdlekick();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginMistralIdlekick extends Plugin {
	public $kick_player_after;
	public $kick_spectator_after;
	public $kick_spectators;
	public $force_spectator_first;
	public $reset_onchat;
	public $reset_oncheckpoint;
	public $reset_onfinish;
	public $start;
	public $log;
	public $debug;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		global $aseco;

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Kick idle Players to let waiting spectators play.');

		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onBeginMap',		'onBeginMap');
		$this->registerEvent('onEndMap',		'onEndMap');

		if (!$settings = $aseco->parser->xmlToArray('config/mistral_idlekick.xml', true, true)) {
			trigger_error('[MistralIdlekick] Could not read/parse config file [config/mistral_idlekick.xml]!', E_USER_ERROR);
		}
		$settings = $settings['MISTRAL_IDLEKICK'];

		$this->kick_player_after	= (int)$settings['KICK_PLAYER_AFTER'][0];
		$this->kick_spectator_after	= (int)$settings['KICK_SPECTATOR_AFTER'][0];
		$this->kick_spectators		= $aseco->string2bool($settings['KICK_SPECTATORS'][0]);
		$this->force_spectator_first	= $aseco->string2bool($settings['FORCE_SPECTATOR_FIRST'][0]);
		$this->reset_onchat		= $aseco->string2bool($settings['RESET'][0]['CHAT'][0]);
		$this->reset_oncheckpoint	= $aseco->string2bool($settings['RESET'][0]['CHECKPOINT'][0]);
		$this->reset_onfinish		= $aseco->string2bool($settings['RESET'][0]['FINISH'][0]);


		// Register required events
		if ($this->reset_onchat == true) {
			$this->registerEvent('onPlayerChat', 'onPlayerChat');
		}
		if ($this->reset_oncheckpoint == true) {
			$this->registerEvent('onPlayerCheckpoint', 'onPlayerCheckpoint');
		}
		if ($this->reset_onfinish == true) {
			$this->registerEvent('onPlayerFinish', 'onPlayerFinish');
		}


		// Do not touch:
		$this->log			= false;
		$this->debug			= false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		$this->storePlayerData($player, 'idleCount', 0);
		if ($this->debug) {
			$aseco->console('[MistralIdlekick] {1} initialised with 0', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerChat ($aseco, $chat) {

		// If no check on chat use, bail out too
		if (!$this->reset_onchat) {
			return;
		}

		$player = $aseco->server->players->getPlayer($chat[1]);
		$this->storePlayerData($player, 'idleCount', 0);
		if ($this->debug) {
			$aseco->console('[MistralIdlekick] {1} reset on chat', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $checkpt: [0]=Login, [1]=WaypointBlockId, [2]=Time [3]=WaypointIndex, [4]=CurrentLapTime, [5]=LapWaypointNumber
	public function onPlayerCheckpoint ($aseco, $checkpt) {

		// If no check on checkpoints, bail out
		if (!$this->reset_oncheckpoint) {
			return;
		}

		$player = $aseco->server->players->getPlayer($checkpt[0]);
		$this->storePlayerData($player, 'idleCount', 0);
		if ($this->debug) {
			$aseco->console('[MistralIdlekick] {1} reset on checkpoint', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// if no check on finishes, bail out
		if (!$this->reset_onfinish) {
			return;
		}

		$player = $finish_item->player;
		$this->storePlayerData($player, 'idleCount', 0);
		if ($this->debug) {
			$aseco->console('[MistralIdlekick] {1} reset on finish', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		foreach ($aseco->server->players->player_list as $player) {
			// Check for admin immunity
			if ($player->isspectator ? $aseco->allowAbility($player, 'noidlekick_spec') : $aseco->allowAbility($player, 'noidlekick_play')) {
				continue;  // Go check next player
			}

			// Check for spectator kicking
			if ($this->kick_spectators || !$player->isspectator) {
				$this->storePlayerData($player, 'idleCount', ($this->getPlayerData($player, 'idleCount') + 1));
			}
			if ($this->log) {
				$aseco->console('[MistralIdlekick] {1} set to {2}', $player->login, $this->getPlayerData($player, 'idleCount'));
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		foreach ($aseco->server->players->player_list as $player) {
			// Check for spectator or player map counts
			if ($this->getPlayerData($player, 'idleCount') == ($player->isspectator ? $this->kick_spectator_after : $this->kick_player_after)) {
				$dokick = false;
				if ($player->isspectator) {
					$dokick = true;
					// Log console message
					$aseco->console('[MistralIdlekick] spectator: {1} after {2} map(s) without action', $player->login, $this->kick_spectator_after);
					$message = $aseco->formatText($aseco->getChatMessage('IDLEKICK_SPEC'),
						$player->nickname,
						$this->kick_spectator_after,
						($this->kick_spectator_after == 1 ? '' : 's')
					);
				}
				else {
					if ($this->force_spectator_first) {
						// Log console message
						$aseco->console('[MistralIdlekick] IdleSpec player: {1} after {2} map(s) without action', $player->login, $this->kick_player_after);
						$message = $aseco->formatText($aseco->getChatMessage('IDLESPEC_PLAY'),
							$player->nickname,
							$this->kick_player_after,
							($this->kick_player_after == 1 ? '' : 's')
						);

						// Force player into spectator
						$rtn = $aseco->client->query('ForceSpectator', $player->login, 1);
						if (!$rtn) {
							trigger_error('[' . $aseco->client->getErrorCode() . '] ForceSpectator - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
						}
						else {
							// Allow spectator to switch back to player
							$rtn = $aseco->client->query('ForceSpectator', $player->login, 0);
						}

						// Force free camera mode on spectator
						$aseco->client->addCall('ForceSpectatorTarget', array($player->login, '', 2));
					}
					else {
						$dokick = true;
						// Log console message
						$aseco->console('[MistralIdlekick] Kick player: {1} after {2} map(s) without action', $player->login, $this->kick_player_after);
						$message = $aseco->formatText($aseco->getChatMessage('IDLEKICK_PLAY'),
							$player->nickname,
							$this->kick_player_after,
							($this->kick_player_after == 1 ? '' : 's')
						);
					}
				}

				// Show chat message
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// Kick idle player
				if ($dokick) {
					$aseco->client->query('Kick', $player->login);
				}
			}
			else if ($this->debug) {
				$aseco->console('[MistralIdlekick] {1} current value is {2}', $player->login, $this->getPlayerData($player, 'idleCount'));
			}
		}
	}
}

?>