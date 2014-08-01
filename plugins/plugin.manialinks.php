<?php
/* Plugin: Manialinks
 * ~~~~~~~~~~~~~~~~~~
 * » Provides simple ManiaLink windows, also handles special panels and custom UI changes.
 * » Based upon the manialinks.inc.php from XAseco2, written by the Xymph
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
 * Documentation:
 * ~~~~~~~~~~~~~~
 * Currently reserved ManiaLink id's and action's:
 *        id= "UASECO-0": dummy for custom_ui block
 *            "UASECO-1": Main pop-up window
 *            "UASECO-2": CheckPoints panel
 *            "UASECO-3": Admin panel
 *            "UASECO-4": Records panel
 *            "UASECO-5": Vote panel
 *            "UASECO-6": Donate panel
 *            "UASECO-7": Messages window
 *            "UASECO-9": Scoreboard stats panel
 *    action= "0": Close main pop-up window
 *           "-1": Ignore action (by server)
 *           "-4": First page of main window
 *           "-3": Previous5 page of main window
 *           "-2": Previous page of main window
 *            "1": Refresh current page
 *            "2": Next page of main window
 *            "3": Next5 page of main window
 *            "4": Last page of main window
 *           "-5": /stats field Time Played       call "/active"
 *           "-6": /stats field Server Rank       call "/top100"
 *            "5": /stats field Records           call "/toprecs"
 *            "6": /stats field Races Won         call "/topwins"
 *            "7": Records panel PB field         call "/topsums"
 *            "8": Records panel Local field      call "/recs"
 *            "9": Records panel Dedi field       call "/dedirecs"
 *           "10": Records panel MX field         call "/mxrecs"
 *           "11": /list Env field Canyon         call "/list env:Canyon"
 *           "12": /list Env field Stadium        call "/list env:Stadium"
 *           "13": /list Env field Valley         call "/list env:Valley"
 *      "13"-"17": reserved for future use
 *           "18": Vote panel Yes, F5 key         call "/y"
 *           "19": Vote panel No, F6 key
 *           "20": /jukebox display Clear button  call "/admin clearjukebox"
 *           "21": Admin panel ClipRewind button  call "/admin restartmap"
 *           "22": Admin panel ClipPause button   call "/admin endround"
 *           "23": Admin panel ClipPlay button    call "/admin nextmap"
 *           "24": Admin panel Refresh button     call "/admin replaymap"
 *           "25": Admin panel ArrowGreen button  call "/admin pass"
 *           "26": Admin panel ArrowRed button    call "/admin cancel"
 *           "27": Admin panel Buddies button     call "/admin players"
 *           "28": Server planets Payment dialog Yes
 *           "29": Server planets Payment dialog No
 *           "30": Donate panel, button 1         call "/donate 20"
 *           "31": Donate panel, button 2         call "/donate 50"
 *           "32": Donate panel, button 3         call "/donate 100"
 *           "33": Donate panel, button 4         call "/donate 200"
 *           "34": Donate panel, button 5         call "/donate 500"
 *           "35": Donate panel, button 6         call "/donate 1000"
 *           "36": Donate panel, button 7         call "/donate 2000"
 *      "37"-"48": Vote panels, handled in plugin.panels.php
 *     "-7"-"-48": Admin panels, handled in plugin.panels.php
 *     "49"-"100": Window styles, handled in plugin.style.php
 *   "-49"-"-100": Records panels, handled in plugin.panels.php
 *   "101"-"2000": Map numbers for /jukebox, handled in plugin.rasp_jukebox.php
 * "-101"-"-2000": Map authors for /list, handled in plugin.rasp_jukebox.php
 *"-2001"-"-2100": Jukebox drop numbers, handled in plugin.rasp_jukebox.php
 *"-2101"-"-4000": Song numbers, handled in plugin.music_server.php
 *  "2001"-"2200": Player numbers for /stats, handled in chat.players.php
 *  "2201"-"2400": Player numbers for /admin warn, handled in chat.admin.php
 *  "2401"-"2600": Player numbers for /admin ignore, handled in chat.admin.php
 *  "2601"-"2800": Player numbers for /admin unignore, handled in chat.admin.php
 *  "2801"-"3000": Player numbers for /admin kick, handled in chat.admin.php
 *  "3001"-"3200": Player numbers for /admin ban, handled in chat.admin.php
 *  "3201"-"3400": Player numbers for /admin unban, handled in chat.admin.php
 *  "3401"-"3600": Player numbers for /admin black, handled in chat.admin.php
 *  "3601"-"3800": Player numbers for /admin unblack, handled in chat.admin.php
 *  "3801"-"4000": Player numbers for /admin addguest, handled in chat.admin.php
 *  "4001"-"4200": Player numbers for /admin removeguest, handled in chat.admin.php
 *  "4201"-"4400": Player numbers for /admin forcespec, handled in chat.admin.php
 *  "4401"-"4600": Player numbers for /admin listignores, handled in chat.admin.php
 *  "4601"-"4800": Player numbers for /admin listbans, handled in chat.admin.php
 *  "4801"-"5000": Player numbers for /admin listblacks, handled in chat.admin.php
 *  "5001"-"5200": Player numbers for /admin listguests, handled in chat.admin.php
 *  "5201"-"5700": MX numbers for /mxinfo, handled in plugin.rasp_jukebox.php
 *  "5701"-"6200": MX numbers for /add, handled in plugin.rasp_jukebox.php
 *  "6201"-"6700": MX numbers for /admin add, handled in plugin.rasp_jukebox.php
 *  "6701"-"7200": Authors for /xlist auth:, handled in plugin.rasp_jukebox.php
 *  "7201"-"7222": Donate panels, handled in plugin.panels.php
 *  "7224"-"7230": reserved for future use
 *  "7231"-"7262": Panel backgrounds, handled in plugin.panels.php
 *"-4001"-"-4200": Player numbers for /jfreu badword, handled in chat.jfreu.php
 *"-4201"-"-4400": Player numbers for /jfreu banfor 1H, handled in chat.jfreu.php
 *"-4401"-"-4600": Player numbers for /jfreu banfor 24H, handled in chat.jfreu.php
 *"-4601"-"-4800": Player numbers for /jfreu unban, handled in chat.jfreu.php
 *"-4801"-"-5000": Player numbers for /jfreu addvip, handled in chat.jfreu.php
 *"-5001"-"-5200": Player numbers for /jfreu removevip, handled in chat.jfreu.php
 *"-5201"-"-5400": Player numbers for /jfreu unspec, handled in chat.jfreu.php
 *"-5401"-"-5600": Player numbers for /jfreu listbans, handled in chat.jfreu.php
 *"-5601"-"-5800": Player numbers for /jfreu listvips, handled in chat.jfreu.php
 *"-5801"-"-6000": Team numbers for /jfreu listvipteams, handled in chat.jfreu.php
 *"-6001"-"-7900": Map numbers for /karma, handled in plugin.rasp_jukebox.php
 *"-7901"-"-8100": Player numbers for /admin unbanip, handled in chat.admin.php
 *
 * Dependencies:
 * - plugins/plugin.donate.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginManialinks();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginManialinks extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides simple ManiaLink windows, also handles special panels and custom UI changes.');

		$this->addDependence('PluginDonate',			Dependence::WANTED,	'1.0.0', null);

		// Register functions for events
		$this->registerEvent('onPlayerManialinkPageAnswer',	'event_manialink');
		$this->registerEvent('onEndMap',			'allwindows_off');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays a single ManiaLink window to a player
	 *
	 * $login : player login to send window to
	 * $header: string
	 * $icon  : array( $style, $substyle {, $sizechg} )
	 * $data  : array( $line1=array($col1, $col2, ...), $line2=array(...) )
	 * $widths: array( $overal, $col1, $col2, ...)
	 * $button: string
	 *
	 * A $line with one $col will occupy the full window width,
	 * otherwise all $line's must have the same number of columns,
	 * as should $widths (+1 for $overall).
	 * If $colX is an array, it contains the string and the button's action id.
	 */
	public function display_manialink ($login, $header, $icon, $data, $widths, $button) {
		global $aseco;

		$player = $aseco->server->players->getPlayer($login);
		$style = $player->style;

		$hsize = $style['HEADER'][0]['TEXTSIZE'][0];
		$bsize = $style['BODY'][0]['TEXTSIZE'][0];
		$lines = count($data);

		// build manialink header & window
		$xml  = '<manialink id="UASECO-1"><frame pos="' . ($widths[0]/2) . ' 0.47 0">' .
		        '<quad size="' . $widths[0] . ' ' . (0.11+$hsize+$lines*$bsize) .
		        '" style="' . $style['WINDOW'][0]['STYLE'][0] .
		        '" substyle="' . $style['WINDOW'][0]['SUBSTYLE'][0] . '"/>' . LF;

		// add header and optional icon
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -0.01 -0.1" size="' . ($widths[0]-0.02) . ' ' . $hsize .
		        '" halign="center" style="' . $style['HEADER'][0]['STYLE'][0] .
		        '" substyle="' . $style['HEADER'][0]['SUBSTYLE'][0] . '"/>' . LF;
		if (is_array($icon)) {
			$isize = $hsize;
			if (isset($icon[2]))
				$isize += $icon[2];
			$xml .= '<quad pos="-0.055 -0.045 -0.2" size="' . $isize . ' ' . $isize .
			        '" halign="center" valign="center" style="' . $icon[0] . '" substyle="' . $icon[1] . '"/>' . LF;
			$xml .= '<label pos="-0.10 -0.025 -0.2" size="' . ($widths[0]-0.12) . ' ' . $hsize .
			        '" halign="left" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
			        '" text="' . $aseco->handleSpecialChars($header) . '"/>' . LF;
		} else {
			$xml .= '<label pos="-0.03 -0.025 -0.2" size="' . ($widths[0]-0.05) . ' ' . $hsize .
			        '" halign="left" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
			        '" text="' . $aseco->handleSpecialChars($header) . '"/>' . LF;
		}

		// add body
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.02+$hsize) .
		        ' -0.1" size="' . ($widths[0]-0.02) . ' ' . (0.015+$lines*$bsize) .
		        '" halign="center" style="' . $style['BODY'][0]['STYLE'][0] .
		        '" substyle="' . $style['BODY'][0]['SUBSTYLE'][0] . '"/>' . LF;

		// add lines with optional columns
		$xml .= '<format style="' . $style['BODY'][0]['TEXTSTYLE'][0] . '"/>' . LF;
		$cnt = 0;
		foreach ($data as $line) {
			$cnt++;
			if (!empty($line)) {
				if (count($line) > 1) {
					for ($i = 0; $i < count($widths)-1; $i++) {
						if (is_array($line[$i])) {
							$xml .= '<quad pos="-' . (0.015+array_sum(array_slice($widths,1,$i))) .
							        ' -' . ($hsize-0.013+$cnt*$bsize) .
							        ' -0.15" size="' . ($widths[$i+1]-0.03) . ' ' . ($bsize+0.000) .
							        '" halign="left" style="' . $style['BUTTON'][0]['STYLE'][0] .
							        '" substyle="' . $style['BUTTON'][0]['SUBSTYLE'][0] .
							        '" action="' . $line[$i][1] . '"/>' . LF;
							$xml .= '<label pos="-' . (0.025+array_sum(array_slice($widths,1,$i))) .
							        ' -' . ($hsize-0.008+$cnt*$bsize) .
							        ' -0.2" size="' . ($widths[$i+1]-0.05) . ' ' . (0.02+$bsize) .
							        '" halign="left" style="' . $style['BODY'][0]['TEXTSTYLE'][0] .
							        '" text="' . $aseco->handleSpecialChars($line[$i][0]) . '"/>' . LF;
						} else {
							$xml .= '<label pos="-' . (0.025+array_sum(array_slice($widths,1,$i))) .
							        ' -' . ($hsize-0.008+$cnt*$bsize) .
							        ' -0.2" size="' . ($widths[$i+1]-0.05) . ' ' . (0.02+$bsize) .
							        '" halign="left" style="' . $style['BODY'][0]['TEXTSTYLE'][0] .
							        '" text="' . $aseco->handleSpecialChars($line[$i]) . '"/>' . LF;
						}
					}
				} else {
					$xml .= '<label pos="-0.025 -' . ($hsize-0.008+$cnt*$bsize) .
					        ' -0.2" size="' . ($widths[0]-0.04) . ' ' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['BODY'][0]['TEXTSTYLE'][0] .
					        '" text="' . $aseco->handleSpecialChars($line[0]) . '"/>' . LF;
				}
			}
		}

		// add button (action "0" = close) & footer
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.03+$hsize+$lines*$bsize) .
		        ' -0.2" size="0.08 0.08" halign="center" style="Icons64x64_1" substyle="Close" action="0"/>' . LF;
		$xml .= '</frame></manialink>';
		$xml = str_replace('{#black}', $style['WINDOW'][0]['BLACKCOLOR'][0], $xml);

		//$aseco->console_text($xml);
		$aseco->client->addCall('SendDisplayManialinkPageToLogin', array($login, $aseco->formatColors($xml), 0, true));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays a multipage ManiaLink window to a player
	 *
	 * $player: player object to send windows to
	 *  ->msgs: array( array( $ptr, $header, $widths, $icon ),
	 *   page1:        array( $line1=array($col1, $col2, ...), $line2=array(...) ),
	 *       2:        array( $line1=array($col1, $col2, ...), $line2=array(...) ),
	 *                 ... )
	 * $header: string
	 * $widths: array( $overal, $col1, $col2, ...)
	 * $icon  : array( $style, $substyle {, $sizechg} )
	 *
	 * A $line with one $col will occupy the full window width,
	 * otherwise all $line's must have the same number of columns,
	 * as should $widths (+1 for $overall).
	 * If $colX is an array, it contains the string and the button's action id.
	 */
	public function display_manialink_multi ($player) {
		global $aseco;

		// fake current page event
		$this->event_manialink($aseco, array(0, $player->login, 1));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// called @ onPlayerManialinkPageAnswer
	// Handles all ManiaLink main system responses,
	// as well as multi-page ManiaLink windows
	// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function event_manialink ($aseco, $answer) {

		// leave actions outside -6 - 36 to other handlers
		$action = (int)$answer[2];
		if ($action < -6 || $action > 36) {
			return;
		}

		// Do not handle plugin.donate.php
		if ($action >= 28 && $action <= 36) {
			return;
		}

		// get player
		$player = $aseco->server->players->getPlayer($answer[1]);

		// check player answer
		switch ($action) {
			case  0:
				// close main pop-up window
				$this->mainwindow_off($aseco, $player->login);
				return;

			// /stats fields
			case -5:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/active "', $player->login);
				// /stats field Time Played
				$aseco->releaseChatCommand('/active', $player->login);
				return;
			case -6:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/top100 "', $player->login);
				// /stats field Server Rank
				$aseco->releaseChatCommand('/top100', $player->login);
				return;
			case  5:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/toprecs "', $player->login);
				// /stats field Records
				$aseco->releaseChatCommand('/toprecs', $player->login);
				return;
			case  6:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/topwins "', $player->login);
				// /stats field Races Won
				$aseco->releaseChatCommand('/topwins', $player->login);
				return;

			// Records panel fields
			case  7:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/topsums "', $player->login);
				// records panel PB field
				$aseco->releaseChatCommand('/topsums', $player->login);
				return;
			case  8:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/recs "', $player->login);
				// records panel Local field
				$aseco->releaseChatCommand('/recs', $player->login);
				return;
			case  9:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/dedirecs "', $player->login);
				// records panel Dedi field
				$aseco->releaseChatCommand('/dedirecs', $player->login);
				return;
			case 10:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/mxrecs "', $player->login);
				// records panel MX field
				$aseco->releaseChatCommand('/mxrecs', $player->login);
				return;

			// /list Env fields
			case 11:
				// close main window because /list can take a while
				$this->mainwindow_off($aseco, $player->login);
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/list env:Canyon"', $player->login);
				// /list Env field Canyon
				$aseco->releaseChatCommand('/list env:Canyon', $player->login);
				return;
			case 12:
				// close main window because /list can take a while
				$this->mainwindow_off($aseco, $player->login);
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/list env:Stadium"', $player->login);
				// /list Env field Stadium
				$aseco->releaseChatCommand('/list env:Stadium', $player->login);
				return;
			case 13:
				// close main window because /list can take a while
				$this->mainwindow_off($aseco, $player->login);
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/list env:Valley"', $player->login);
				// /list Env field Valley
				$aseco->releaseChatCommand('/list env:Valley', $player->login);
				return;
			case 14:
			case 15:
			case 16:
			case 17:
				// reserved for future use
				return;

			// Vote panel buttons/keys
			case 18:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/y "', $player->login);
				// /y on chat-based vote
				$aseco->releaseChatCommand('/y', $player->login);
				return;
			case 19:
//				// log clicked command
//				$aseco->console('Player [{1}] clicked command "/n " (ignored)', $player->login);
				// /n on chat-based vote (ignored)
				return;

			case 20:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin clearjukebox"', $player->login);
				// close main window
				$this->mainwindow_off($aseco, $player->login);
				// /jukebox display Clear Jukebox button
				$aseco->releaseChatCommand('/admin clearjukebox', $player->login);
				return;

			// Admin panel buttons
			case 21:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin restartmap"', $player->login);
				// admin panel ClipRewind button
				$aseco->releaseChatCommand('/admin restartmap', $player->login);
				return;
			case 22:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin endround"', $player->login);
				// admin panel ClipPause button
				$aseco->releaseChatCommand('/admin endround', $player->login);
				return;
			case 23:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin nextmap"', $player->login);
				// admin panel ClipPlay button
				$aseco->releaseChatCommand('/admin nextmap', $player->login);
				return;
			case 24:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin replaymap"', $player->login);
				// admin panel Refresh button
				$aseco->releaseChatCommand('/admin replaymap', $player->login);
				return;
			case 25:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin pass"', $player->login);
				// admin panel ArrowGreen button
				$aseco->releaseChatCommand('/admin pass', $player->login);
				return;
			case 26:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin cancel"', $player->login);
				// admin panel ArrowRed button
				$aseco->releaseChatCommand('/admin cancel', $player->login);
				return;
			case 27:
//				// log clicked command
//				$aseco->console('[Player] Player [{1}] clicked command "/admin players live"', $player->login);
				// admin panel Buddies button
				$aseco->releaseChatCommand('/admin players live', $player->login);
				return;
		}

		// Handle multi-page ManiaLink windows in all styles
		// update page pointer
		$tot = count($player->msgs) - 1;
		switch ($action) {
			case -4:  $player->msgs[0][0] = 1; break;
			case -3:  $player->msgs[0][0] -= 5; break;
			case -2:  $player->msgs[0][0] -= 1; break;
			case  1:  break;  // stay on current page
			case  2:  $player->msgs[0][0] += 1; break;
			case  3:  $player->msgs[0][0] += 5; break;
			case  4:  $player->msgs[0][0] = $tot; break;
		}

		// stay within boundaries
		if ($player->msgs[0][0] < 1) {
			$player->msgs[0][0] = 1;
		}
		else if ($player->msgs[0][0] > $tot) {
			$player->msgs[0][0] = $tot;
		}

		// get control variables
		$ptr = $player->msgs[0][0];
		$header = $player->msgs[0][1];
		$widths = $player->msgs[0][2];
		$icon = $player->msgs[0][3];
		$style = $player->style;

		$hsize = $style['HEADER'][0]['TEXTSIZE'][0];
		$bsize = $style['BODY'][0]['TEXTSIZE'][0];
		$lines = count($player->msgs[$ptr]);
		// fill up multipage windows
		if ($tot > 1)
			$lines = max($lines, count($player->msgs[1]));

		// build manialink header & window
		$xml  = '<manialink id="UASECO-1"><frame pos="' . ($widths[0]/2) . ' 0.47 0">' .
		        '<quad size="' . $widths[0] . ' ' . (0.11+$hsize+$lines*$bsize) .
		        '" style="' . $style['WINDOW'][0]['STYLE'][0] .
		        '" substyle="' . $style['WINDOW'][0]['SUBSTYLE'][0] . '"/>' . LF;

		// add header
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -0.01 -0.1" size="' . ($widths[0]-0.02) . ' ' . $hsize .
		        '" halign="center" style="' . $style['HEADER'][0]['STYLE'][0] .
		        '" substyle="' . $style['HEADER'][0]['SUBSTYLE'][0] . '"/>' . LF;
		if (is_array($icon)) {
			$isize = $hsize;
			if (isset($icon[2]))
				$isize += $icon[2];
			$xml .= '<quad pos="-0.055 -0.045 -0.2" size="' . $isize . ' ' . $isize .
			        '" halign="center" valign="center" style="' . $icon[0] . '" substyle="' . $icon[1] . '"/>' . LF;
			$xml .= '<label pos="-0.10 -0.025 -0.2" size="' . ($widths[0]-0.25) . ' ' . $hsize .
			        '" halign="left" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
			        '" text="' . $aseco->handleSpecialChars($header) . '"/>' . LF;
		}
		else {
			$xml .= '<label pos="-0.03 -0.025 -0.2" size="' . ($widths[0]-0.18) . ' ' . $hsize .
			        '" halign="left" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
			        '" text="' . $aseco->handleSpecialChars($header) . '"/>' . LF;
		}
		$xml .= '<label pos="-' . ($widths[0]-0.02) . ' -0.025 -0.2" size="0.12 ' . $hsize .
		        '" halign="right" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
		        '" text="$n(' . $ptr . '/' . $tot . ')"/>' . LF;

		// add body
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.02+$hsize) .
		        ' -0.1" size="' . ($widths[0]-0.02) . ' ' . (0.015+$lines*$bsize) .
		        '" halign="center" style="' . $style['BODY'][0]['STYLE'][0] .
		        '" substyle="' . $style['BODY'][0]['SUBSTYLE'][0] . '"/>' . LF;

		// add lines with optional columns
		$xml .= '<format style="' . $style['BODY'][0]['TEXTSTYLE'][0] . '"/>' . LF;
		$cnt = 0;
		foreach ($player->msgs[$ptr] as $line) {
			$cnt++;
			if (!empty($line)) {
				if (count($line) > 1) {
					for ($i = 0; $i < count($widths)-1; $i++) {
						if (isset($line[$i])) {
							// check for action button
							if (is_array($line[$i])) {
								$xml .= '<quad pos="-' . (0.015+array_sum(array_slice($widths,1,$i))) .
								        ' -' . ($hsize-0.013+$cnt*$bsize) .
								        ' -0.15" size="' . ($widths[$i+1]-0.03) . ' ' . ($bsize+0.000) .
								        '" halign="left" style="' . $style['BUTTON'][0]['STYLE'][0] .
								        '" substyle="' . $style['BUTTON'][0]['SUBSTYLE'][0] .
								        '" action="' . $line[$i][1] . '"/>' . LF;
								$xml .= '<label pos="-' . (0.025+array_sum(array_slice($widths,1,$i))) .
								        ' -' . ($hsize-0.008+$cnt*$bsize) .
								        ' -0.2" size="' . ($widths[$i+1]-0.05) . ' ' . (0.02+$bsize) .
								        '" halign="left" style="' . $style['BODY'][0]['TEXTSTYLE'][0] .
								        '" text="' . $aseco->handleSpecialChars($line[$i][0]) . '"/>' . LF;
							}
							else {
								$xml .= '<label pos="-' . (0.025+array_sum(array_slice($widths,1,$i))) .
								        ' -' . ($hsize-0.008+$cnt*$bsize) .
								        ' -0.2" size="' . ($widths[$i+1]-0.05) . ' ' . (0.02+$bsize) .
								        '" halign="left" style="' . $style['BODY'][0]['TEXTSTYLE'][0] .
								        '" text="' . $aseco->handleSpecialChars($line[$i]) . '"/>' . LF;
							}
						}
					}
				}
				else {
					$xml .= '<label pos="-0.025 -' . ($hsize-0.008+$cnt*$bsize) .
					        ' -0.2" size="' . ($widths[0]-0.04) . ' ' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['BODY'][0]['TEXTSTYLE'][0] .
					        '" text="' . $aseco->handleSpecialChars($line[0]) . '"/>' . LF;
				}
			}
		}

		// add button(s) & footer
		$add5 = ($tot > 5);
		// check for preceding page(s), then First & Prev(5) button(s)
		if ($ptr > 1) {
			$first = '"ArrowFirst" action="-4"';
			$prev5 = '"ArrowFastPrev" action="-3"';
			$prev1 = '"ArrowPrev" action="-2"';
			$icstl = 'Icons64x64_1';
			$icsiz = '0.07';
			$icoff = 0.035;
		}
		else {  // first page so dummy buttons
			$first = '"BgTools"';
			$prev5 = '"BgTools"';
			$prev1 = '"BgTools"';
			$icstl = 'UIConstructionSimple_Buttons';
			$icsiz = '0.038';
			$icoff = 0.051;
		}
		$xml .= '<quad pos="-0.04 -' . ($icoff+$hsize+$lines*$bsize) .
		        ' -0.2" size="' . $icsiz . ' ' . $icsiz . '" halign="center" style="' . $icstl . '" substyle=' . $first . '/>' . LF;
		if ($add5) {
			$xml .= '<quad pos="-0.095 -' . ($icoff+$hsize+$lines*$bsize) .
			        ' -0.2" size="' . $icsiz . ' ' . $icsiz . '" halign="center" style="' . $icstl . '" substyle=' . $prev5 . '/>' . LF;
		}
		$xml .= '<quad pos="-' . ($widths[0]*0.25) . ' -' . ($icoff+$hsize+$lines*$bsize) .
		        ' -0.2" size="' . $icsiz . ' ' . $icsiz . '" halign="center" style="' . $icstl . '" substyle=' . $prev1 . '/>' . LF;
		// always a Close button
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.03+$hsize+$lines*$bsize) .
		        ' -0.2" size="0.08 0.08" halign="center" style="Icons64x64_1" substyle="Close" action="0"/>' . LF;
		// check for succeeding page(s), then Next(5) & Last button(s)
		if ($ptr < $tot) {
			$next1 = '"ArrowNext" action="2"';
			$next5 = '"ArrowFastNext" action="3"';
			$last  = '"ArrowLast" action="4"';
			$icstl = 'Icons64x64_1';
			$icsiz = '0.07';
			$icoff = 0.035;
		}
		else {  // last page so dummy buttons
			$next1 = '"BgTools"';
			$next5 = '"BgTools"';
			$last  = '"BgTools"';
			$icstl = 'UIConstructionSimple_Buttons';
			$icsiz = '0.038';
			$icoff = 0.051;
		}
		$xml .= '<quad pos="-' . ($widths[0]*0.75) . ' -' . ($icoff+$hsize+$lines*$bsize) .
		        ' -0.2" size="' . $icsiz . ' ' . $icsiz . '" halign="center" style="' . $icstl . '" substyle=' . $next1 . '/>' . LF;
		if ($add5) {
			$xml .= '<quad pos="-' . ($widths[0]-0.095) . ' -' . ($icoff+$hsize+$lines*$bsize) .
			        ' -0.2" size="' . $icsiz . ' ' . $icsiz . '" halign="center" style="' . $icstl . '" substyle=' . $next5 . '/>' . LF;
		}
		$xml .= '<quad pos="-' . ($widths[0]-0.04) . ' -' . ($icoff+$hsize+$lines*$bsize) .
		        ' -0.2" size="' . $icsiz . ' ' . $icsiz . '" halign="center" style="' . $icstl . '" substyle=' . $last . '/>' . LF;

		$xml .= '</frame></manialink>';
		$xml = str_replace('{#black}', $style['WINDOW'][0]['BLACKCOLOR'][0], $xml);

		//$aseco->console_text($xml);
		$aseco->client->addCall('SendDisplayManialinkPageToLogin', array($player->login, $aseco->formatColors($xml), 0, false));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Closes main window
	 *
	 * $login: player login to close window for
	 */
	public function mainwindow_off ($aseco, $login) {
		// close main window
		$xml = '<manialink id="UASECO-1"></manialink>';
		$aseco->addManialink($xml, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// called @ onEndMap
	public function allwindows_off ($aseco, $data) {

		// Disable all pop-up windows and records & donate panels at all Players
		$xml  = '<manialink id="UASECO-1"></manialink>';
		$xml .= '<manialink id="UASECO-4"></manialink>';
		$xml .= '<manialink id="UASECO-6"></manialink>';
		$aseco->addManialink($xml, false, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays the Message window
	 *
	 * $msgs   : lines to be displayed
	 * $timeout: timeout for window in msec
	 */
	public function display_msgwindow ($aseco, $msgs, $timeout) {

		$cnt = count($msgs);
		$xml = '<manialink id="UASECO-7"><frame posn="-49 43.5 0">' . LF .
		       '<quad sizen="93 ' . (1.5 + $cnt*2.5) . '" style="%STYLE%" substyle="%SUBST%"/>' . LF;
		$pos = -1;
		foreach ($msgs as $msg) {
			$xml .= '<label posn="1 ' . $pos . ' 1" sizen="91 1" style="TextRaceChat" text="' . $aseco->handleSpecialChars($msg) . '"/>' . LF .
			$pos -= 2.5;
		}
		$xml .= '</frame></manialink>';
		$xml = $this->set_panel_bg($xml, $aseco->panelbg);

		$aseco->addManialink($xml, false, $timeout, false);
	}
}

?>