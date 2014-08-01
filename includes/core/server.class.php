<?php
/*
 * Class: Server
 * ~~~~~~~~~~~~~
 * » Stores basic information of the server UASECO is running on.
 * » Based upon types.inc.php from XAseco2/1.03 written by Xymph and others
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
 *  - includes/core/gameinfo.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Server {
	// Listmethod 'GetVersion'
	public $game;
	public $version;
	public $build;
	public $title;
	public $api_version;

	// Listmethod 'GetServerOptions'
	public $options;

	// Listmethod 'GetServerPlanets'
	public $amount_planets;

	// Listmethod 'GetSystemInfo'
	public $id;
	public $login;
	public $ip;
	public $port;
	public $p2pport;

	// Listmethod 'GetDetailedPlayerInfo', 'GetServerOptions'
	public $name;				// <dedicated><server_options><name> from "UserData/Config/dedicated_cfg.txt"
	public $zone;
	public $comment;

	// Listmethod 'GetLadderServerLimits'
	public $ladder_limit_min;
	public $ladder_limit_max;

	// Listmethod 'GameDataDirectory'
	public $gamedir;

	// Listmethod 'GetMapsDirectory'
	public $mapdir;

	// Listmethod 'GetCurrentGameInfo' and 'GetModeScriptSettings'
	public $gameinfo;			// Used by class Gameinfo

	// Listmethod 'IsRelayServer' and 'GetMainServerPlayerInfo'
	public $isrelay;
	public $relaymaster;
	public $relay_list;

	// Listmethod 'GetNetworkStats'
	public $networkstats;

	// Misc.
	public $starttime;			// Timestamp when the server was started
	public $timeout;			// For <dedicated_server><timeout> from "config/UASECO.xml"

	public $xmlrpc;				// An array wich holds 'ip', 'port', 'login' and 'pass' for XML-RPC connection
	public $maps;				// Used by class MapList
	public $records;			// Used by class RecordList
	public $players;			// Used by class PlayerList
	public $mutelist;			// Server wide mutelist

	public $gamestate;			// Holds actual gamestate: Server::RACE or Server::SCORE
	public $state_names;			// Server status Id to Names (used to translate 'GetStatus')


	// Game states constants
	const RACE  = 'race';
	const SCORE = 'score';

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($ip, $port, $login, $pass) {
		$this->xmlrpc['ip']	= $ip;
		$this->xmlrpc['port']	= $port;
		$this->xmlrpc['login']	= $login;
		$this->xmlrpc['pass']	= $pass;

		$this->starttime	= time();
		$this->isrelay		= false;
		$this->relaymaster	= null;
		$this->relay_list	= array();
		$this->gamestate	= self::RACE;

		$this->state_names	= array(
			1		=> 'Waiting',
			2		=> 'Launching',
			3		=> 'Running - Synchronization',
			4		=> 'Running - Play',
			5		=> 'Unknown Status, please report!',
			6		=> 'Running - Exit',
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentGameInfo () {
		global $aseco;

		$clone = false;
		if ( !empty($this->gameinfo->mode) ) {
			$clone = clone $this->gameinfo;
		}
		$this->gameinfo = new Gameinfo($aseco, $clone);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getServerSettings () {

		// Get basic server info
		$this->_callMethodGetVersion();

		// Get server id, login, name, zone...
		$this->_callMethodGetSystemInfo();

		// Get server name, options
		$this->_callMethodGetServerOptions();

		// Get server planets
		$this->_callMethodGetServerPlanets();

		// Get server ladder limits
		$this->_callMethodGetLadderServerLimits();

		// Get gamedir
		$this->_callMethodGameDataDirectory();

		// Get mapdir
		$this->_callMethodGetMapsDirectory();

		// Check for relay server
		$this->_callMethodIsRelayServer();

		// Get mode and limits
		$this->getCurrentGameInfo();

		// Get server stats: uptime...
		$this->_callMethodGetNetworkStats();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updateServerOptions () {

		// Update basic server info
		$this->_callMethodGetVersion();

		// Update server name, options
		$this->_callMethodGetServerOptions();

		// Update server planets
		$this->_callMethodGetServerPlanets();

		// Update server ladder limits (almost for RoC-Servers)
		$this->_callMethodGetLadderServerLimits();

		// Update server stats: uptime...
		$this->_callMethodGetNetworkStats();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGetNetworkStats () {
		global $aseco;

		// Get server stats: uptime...
		$aseco->client->query('GetNetworkStats');
		$network = $aseco->client->getResponse();

		$this->networkstats['Uptime']			= $network['Uptime'];
		$this->networkstats['NbrConnection']		= $network['NbrConnection'];
		$this->networkstats['MeanConnectionTime']	= $network['MeanConnectionTime'];
		$this->networkstats['MeanNbrPlayer']		= $network['MeanNbrPlayer'];
		$this->networkstats['RecvNetRate']		= $network['RecvNetRate'];
		$this->networkstats['SendNetRate']		= $network['SendNetRate'];
		$this->networkstats['TotalReceivingSize']	= $network['TotalReceivingSize'];
		$this->networkstats['TotalSendingSize']		= $network['TotalSendingSize'];
		unset($network);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodIsRelayServer () {
		global $aseco;

		$aseco->client->query('IsRelayServer');
		$this->isrelay = ($aseco->client->getResponse() > 0);
		if ($this->isrelay) {
			$aseco->client->query('GetMainServerPlayerInfo', 1);
			$this->relaymaster = $aseco->client->getResponse();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGetMapsDirectory () {
		global $aseco;

		// Get mapdir
		$aseco->client->query('GetMapsDirectory');
		$this->mapdir = $aseco->client->getResponse();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGameDataDirectory () {
		global $aseco;

		// Get gamedir
		$aseco->client->query('GameDataDirectory');
		$this->gamedir = $aseco->client->getResponse();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGetLadderServerLimits () {
		global $aseco;

		// Get server ladder limits
		$aseco->client->query('GetLadderServerLimits');
		$ladder = $aseco->client->getResponse();
		$this->ladder_limit_min		= $ladder['LadderServerLimitMin'];
		$this->ladder_limit_max		= $ladder['LadderServerLimitMax'];
		unset($ladder);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGetServerPlanets () {
		global $aseco;

		// Gets current server name and options
		$aseco->client->query('GetServerPlanets');
		$this->amount_planets = $aseco->client->getResponse();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGetServerOptions () {
		global $aseco;

		// Gets current server name and options
		$aseco->client->query('GetServerOptions');
		$options = $aseco->client->getResponse();

		$this->comment						= $options['Comment'];

		$this->options['Password']				= $options['Password'];
		$this->options['PasswordForSpectator']			= $options['PasswordForSpectator'];
		$this->options['CurrentMaxPlayers']			= $options['CurrentMaxPlayers'];
		$this->options['NextMaxPlayers']			= $options['NextMaxPlayers'];
		$this->options['CurrentMaxSpectators']			= $options['CurrentMaxSpectators'];
		$this->options['NextMaxSpectators']			= $options['NextMaxSpectators'];
		$this->options['KeepPlayerSlots']			= $options['KeepPlayerSlots'];
		$this->options['IsP2PUpload']				= $options['IsP2PUpload'];
		$this->options['IsP2PDownload']				= $options['IsP2PDownload'];
		$this->options['CurrentLadderMode']			= $options['CurrentLadderMode'];
		$this->options['NextLadderMode']			= $options['NextLadderMode'];
		$this->options['CurrentVehicleNetQuality']		= $options['CurrentVehicleNetQuality'];
		$this->options['NextVehicleNetQuality']			= $options['NextVehicleNetQuality'];
		$this->options['CurrentCallVoteTimeOut']		= $options['CurrentCallVoteTimeOut'];
		$this->options['NextCallVoteTimeOut']			= $options['NextCallVoteTimeOut'];
		$this->options['CallVoteRatio']				= $options['CallVoteRatio'];
		$this->options['AllowMapDownload']			= $options['AllowMapDownload'];
		$this->options['AutoSaveReplays']			= $options['AutoSaveReplays'];
		$this->options['RefereePassword']			= $options['RefereePassword'];
		$this->options['RefereeMode']				= $options['RefereeMode'];
		$this->options['AutoSaveValidationReplays']		= $options['AutoSaveValidationReplays'];
		$this->options['HideServer']				= $options['HideServer'];
		$this->options['CurrentUseChangingValidationSeed']	= $options['CurrentUseChangingValidationSeed'];
		$this->options['NextUseChangingValidationSeed']		= $options['NextUseChangingValidationSeed'];
		$this->options['ClientInputsMaxLatency']		= $options['ClientInputsMaxLatency'];
		unset($options);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGetSystemInfo () {
		global $aseco;

		// Get server id, login, name, zone
		$aseco->client->query('GetSystemInfo');
		$system = $aseco->client->getResponse();
		$this->id			= $system['ServerPlayerId'];
		$this->login			= $system['ServerLogin'];
		$this->ip			= $system['PublishedIp'];
		$this->port			= $system['Port'];
		$this->p2pport			= $system['P2PPort'];
		unset($system);

		$aseco->client->query('GetDetailedPlayerInfo', $this->login);
		$info = $aseco->client->getResponse();
		$this->name			= $info['NickName'];
		$this->zone			= explode('|', substr($info['Path'], 6));  // Strip 'World|' and split into array()
		unset($info);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _callMethodGetVersion () {
		global $aseco;

		// Get basic server info
		$aseco->client->query('GetVersion');
		$version = $aseco->client->getResponse();

		$this->game		= $version['Name'];
		$this->version		= $version['Version'];
		$this->build		= $version['Build'];
		$this->title		= $version['TitleId'];
		$this->api_version	= $version['ApiVersion'];
		unset($version);
	}
}

?>