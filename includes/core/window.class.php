<?php
/*
 * Class: Window
 * ~~~~~~~~~~~~~
 * » Provides a comfortable, configurable styled Manialink window.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-02
 * Copyright:	2014 - 2015 by undef.de
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
 *  - includes/core/windowlist.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Window {
	public $layout;
	public $settings;
	public $content;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($unique_manialink_id = false) {
		global $aseco;

		// Empty content by default
		$this->content = array(
			'title'		=> '',
			'data'		=> array(),
			'page'		=> 0,
			'maxpage'	=> 0,
		);

		// Setup defaults
		$this->layout = array(
			'position' => array(
				'x' => -102.00001,
				'y' => 57.28125,
				'z' => 30,
			),
			'main' => array(
				'background' => array(
					'style'		=> 'Bgs1InRace',
					'substyle'	=> 'BgTitle2',
					'color'		=> '0018',
				),
			),
			'title' => array(
				'background' => array(
					'style'		=> 'Bgs1InRace',
					'substyle'	=> 'BgTitle3_3',
				),
				'icon' => array(
					'style'		=> 'Icons64x64_1',
					'substyle'	=> 'ToolLeague1',
				),
				'textcolor'	=> '09FF',
			),
			'column' => array(
				'background' => array(
					'style'		=> 'BgsPlayerCard',
					'substyle'	=> 'BgRacePlayerName',
				),
			),
			'heading' => array(
				'textcolors'		=> 'FA0F',
				'seperator_color'	=> 'AAAF',
			),
		);

		$this->settings = array(
			'id'			=> 'TheWindowFromClassWindow',
			'timeout'		=> 0,
			'hideclick'		=> false,
			'columns'		=> 2,
			'widths'		=> array(),			// Inner columns
			'halign'		=> array(),			// Inner columns
			'heading'		=> array(),			// Inner columns
			'bgcolors'		=> array(),			// RGBA
			'textcolors'		=> array(),			// RGBA
		);

		if ($unique_manialink_id === true) {
			// Generate unique ID
			$this->settings['id'] = $this->generateManialinkId();
		}
		else if ($unique_manialink_id !== false) {
			// Use given ID
			$this->settings['id'] = $unique_manialink_id;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function send ($player, $timeout = 0, $hideclick = false) {
		global $aseco;

		$aseco->windows->send($this, $player, $timeout, $hideclick);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setContent ($title, $data) {
		global $aseco;

		$this->content['title'] = $aseco->handleSpecialChars($aseco->formatColors($title));
		$this->content['data'] = $data;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setColumns ($param = array()) {

		// Check for min. and max. values
		if (isset($param['columns']) && $param['columns']) {
			if ($param['columns'] < 1) {
				$param['columns'] = 1;
			}
			else if ($param['columns'] > 6) {
				$param['columns'] = 6;
			}
			$this->settings['columns'] = $param['columns'];
		}

		// Make sure there is min. one alignment
		if (isset($param['halign']) && count($param['halign']) > 0) {
			$this->settings['halign'] = $param['halign'];
		}

		// Make sure there is min. one width
		if (isset($param['widths']) && count($param['widths']) > 0) {
			$this->settings['widths'] = $param['widths'];
		}

		// Make sure there is min. one background color
		if (isset($param['bgcolors']) && count($param['bgcolors']) > 0) {
			$this->settings['bgcolors'] = $param['bgcolors'];
		}

		// Make sure there is min. one text color
		if (isset($param['textcolors']) && count($param['textcolors']) > 0) {
			$this->settings['textcolors'] = $param['textcolors'];
		}

		// Make sure there is min. one heading
		if (isset($param['heading']) && count($param['heading']) > 0) {
			$this->settings['heading'] = $param['heading'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setLayoutTitle ($param = array()) {

		if (isset($param['textcolor']) && $param['textcolor']) {
			$this->layout['title']['textcolor'] = trim($param['textcolor']);
		}
		if (isset($param['background']) && $param['background']) {
			list($this->layout['title']['background']['style'], $this->layout['title']['background']['substyle']) = explode(',', $param['background']);
			$this->layout['title']['background']['style'] = trim($this->layout['title']['background']['style']);
			$this->layout['title']['background']['substyle'] = trim($this->layout['title']['background']['substyle']);
		}
		if (isset($param['icon']) && $param['icon']) {
			list($this->layout['title']['icon']['style'], $this->layout['title']['icon']['substyle']) = explode(',', $param['icon']);
			$this->layout['title']['icon']['style'] = trim($this->layout['title']['icon']['style']);
			$this->layout['title']['icon']['substyle'] = trim($this->layout['title']['icon']['substyle']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setLayoutBackground ($param = array()) {

		if (isset($param['color']) && $param['color']) {
			$this->layout['main']['background']['color'] = trim($param['color']);
		}
		if (isset($param['background']) && $param['background']) {
			list($this->layout['main']['background']['style'], $this->layout['main']['background']['substyle']) = explode(',', $param['background']);
			$this->layout['main']['background']['style'] = trim($this->layout['main']['background']['style']);
			$this->layout['main']['background']['substyle'] = trim($this->layout['main']['background']['substyle']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setLayoutHeading ($param = array()) {

		if (isset($param['textcolors']) && $param['textcolors']) {
			$this->layout['heading']['textcolors'] = $param['textcolors'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function generateManialinkId () {

		$pool = array_merge(
			range('0', '9'),
			range('a', 'z'),
			range('A', 'Z')
		);
		shuffle($pool);

		$id = array();
		for ($i = 1; $i <= 32; $i++) {
			$id[] = $pool[mt_rand(0, count($pool)-1)];
		}

		return implode('', $id);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildColumns () {
		global $aseco;

		// Headings handling?
		$headings = false;
		if (count($this->settings['heading']) > 0) {
			$headings = true;
		}

		// Total width
		$frame_width = 187.5;

		// Build column background
		$xml = '<frame posn="7.8 -12.1875 0.01">';
		$outer_gap = 2.5;
		$column_width = (($frame_width - (($this->settings['columns'] - 1) * $outer_gap)) / $this->settings['columns']);
		foreach (range(0, ($this->settings['columns'] - 1)) as $i) {
			$xml .= '<quad posn="'. ($i * ($column_width + $outer_gap)) .' 1.5 0.02" sizen="'. $column_width .' 87.9" style="'. $this->layout['column']['background']['style'] .'" substyle="'. $this->layout['column']['background']['substyle'] .'"/>';
		}
		$xml .= '</frame>';
		$xml .= '<format textsize="1" textcolor="FFF"/>';

		// Include rows, if there is some data
		if (count($this->content['data']) > 0) {

			// Prepared settings
			$entries = 0;
			$row = 0;
			$inner_gap = 0.625;
			$offset = 0;
			$line_height = 3.47;
			if ($headings == true) {
				$line_height = 3.32;
				$xml .= '<frame posn="8.95 -11.4 0.02">';
				foreach (range(0, ($this->settings['columns'] - 1)) as $i) {
					$innercol = 0;
					$last_element_width = 0;
					for ($j = 0; $j <= count($this->settings['heading']) - 1; $j++) {
						$inner_width	= ($column_width - $outer_gap) - ($j * $inner_gap);
						$element_width	= (($inner_width / 100) * $this->settings['widths'][$innercol]);

						$textcolor	= ((isset($this->layout['heading']['textcolors'][$innercol])) ? $this->layout['heading']['textcolors'][$innercol] : end($this->layout['heading']['textcolors']));
						$text		= strtoupper((isset($this->settings['heading'][$innercol])) ? $this->settings['heading'][$innercol] : end($this->settings['heading']));
						$sizew		= (($element_width - ($inner_gap / 2)) + (($element_width - ($inner_gap / 2)) / 100 * 10));
						$posx		= (($inner_gap / 2) + $last_element_width + $offset) + (($sizew - $inner_gap) / 2.2);
						$xml .= '<label posn="'. $posx .' -0.3 0.01" sizen="'. ($sizew / 100 * 135) .' 3.32" halign="center" textcolor="'. $textcolor .'" scale="0.65" text="'. $text .'"/>';

						$last_element_width += $element_width + $inner_gap;
						$innercol ++;
					}
					$offset += (($frame_width + $outer_gap) / $this->settings['columns']);

					$xml .= '<quad posn="'. ($i * ($column_width + $outer_gap)) .' -2.8 0.02" sizen="'. ($column_width - $outer_gap) .' 0.05" bgcolor="'. $this->layout['heading']['seperator_color'] .'"/>';
				}
				$xml .= '</frame>';
				$xml .= '<frame posn="8.95 -14.9 0.02">';
			}
			else {
				$xml .= '<frame posn="8.95 -11.4 0.02">';
			}

			$entries = 0;
			$row = 0;
			$offset = 0;
			for ($i = ($this->content['page'] * ($this->settings['columns'] * 25)); $i < (($this->content['page'] * ($this->settings['columns'] * 25)) + ($this->settings['columns'] * 25)); $i ++) {
				// Is there a entry to display?
				if ( !isset($this->content['data'][$i]) ) {
					break;
				}
				$item = $this->content['data'][$i];

				$innercol = 0;
				$last_element_width = 0;
				foreach ($item as $value) {
					$inner_width	= ($column_width - $outer_gap) - ((count($item) - 1) * $inner_gap);
					$element_width	= (($inner_width / 100) * $this->settings['widths'][$innercol]);

					// Setup background <quad...>
					if (count($this->settings['bgcolors']) > 0) {
						$xml .= '<quad posn="'. ($last_element_width + $offset) .' -'. ($line_height * $row) .' 0.03" sizen="'. $element_width .' 3.188" bgcolor="'. ((isset($this->settings['bgcolors'][$innercol])) ? $this->settings['bgcolors'][$innercol] : end($this->settings['bgcolors']) ) .'"/>';
					}

					// Setup <label...>
					$textcolor	= ((isset($this->settings['textcolors'][$innercol])) ? $this->settings['textcolors'][$innercol] : end($this->settings['textcolors']));
					$sizew		= (($element_width - ($inner_gap/2)) + (($element_width - ($inner_gap / 2)) / 100 * 10)); // Add +10% of width because of scale="0.9"
					$posx		= (($inner_gap / 2) + $last_element_width + $offset);
					$posy		= -($line_height * $row + 1.45);
					if (isset($this->settings['halign'][$innercol]) && strtolower($this->settings['halign'][$innercol]) == 'right') {
						$posx = $posx + ($sizew - $inner_gap);
						$xml .= '<label posn="'. $posx .' '. $posy .' 0.04" sizen="'. $sizew .' 3.188" halign="right" valign="center" scale="0.9" textcolor="'. $textcolor .'" text="'. $this->normalizeString($value) .'"/>';
					}
					else {
						$xml .= '<label posn="'. $posx .' '. $posy .' 0.04" sizen="'. $sizew .' 3.188" valign="center" scale="0.9" textcolor="'. $textcolor .'" text="'. $this->normalizeString($value) .'"/>';
					}
					$last_element_width += $element_width + $inner_gap;
					$innercol ++;
				}
				$row ++;
				$entries ++;

				// Check last row, setup next column
				if ($row >= 25) {
					$offset += (($frame_width + $outer_gap) / $this->settings['columns']);
					$row = 0;
				}

				// Break if max. amount of entries reached
				if ($entries >= (25 * $this->settings['columns'])) {
					break;
				}
			}
			$xml .= '</frame>';
		}

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildButtons () {

		$totalentries			= count($this->content['data']);
		$this->content['maxpage']	= ceil($totalentries / ($this->settings['columns'] * 25));

		// Previous button
		$buttons = '<frame posn="160.1875 -101.8125 0.04">';
		if ($this->content['page'] > 0) {
			// First
			$buttons .= '<frame posn="0 0 0.05">';
			$buttons .= '<quad posn="0 0 0.12" sizen="5.625 5.625" action="WindowList?Action=ClassWindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.85 -0.8 0.13" sizen="3.94 3.94" bgcolor="000F"/>';
			$buttons .= '<quad posn="1.1 -0.28125 0.14" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="1.5 -1.05625 0.15" sizen="1 3.1875" bgcolor="CCCF"/>';
			$buttons .= '</frame>';

			// Previous (-5)
			$buttons .= '<frame posn="6.0625 0 0.05">';
			$buttons .= '<quad posn="0 0 0.12" sizen="5.625 5.625" action="WindowList?Action=ClassWindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.85 -0.8 0.13" sizen="3.94 3.94" bgcolor="000F"/>';
			$buttons .= '<quad posn="-0.35 -0.28125 0.14" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="1.1 -0.28125 0.15" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '</frame>';

			// Previous (-1)
			$buttons .= '<frame posn="12.0625 0 0.05">';
			$buttons .= '<quad posn="0 0 0.12" sizen="5.625 5.625" action="WindowList?Action=ClassWindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.85 -0.8 0.13" sizen="3.94 3.94" bgcolor="000F"/>';
			$buttons .= '<quad posn="0.4 -0.28125 0.14" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '</frame>';
		}
		else {
			// First
			$buttons .= '<quad posn="0.1375 -0.281 0.12" sizen="5.0625 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-5)
			$buttons .= '<quad posn="6.1375 -0.281 0.12" sizen="5.0625 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-1)
			$buttons .= '<quad posn="12.1375 -0.281 0.12" sizen="5.0625 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}
		$buttons .= '</frame>';

		// Next button (display only if more pages to display)
		$buttons .= '<frame posn="160.1875 -101.8125 0.04">';
		if (($this->content['page'] + 1) < $this->content['maxpage']) {
			// Next (+1)
			$buttons .= '<frame posn="18.0625 0 0.05">';
			$buttons .= '<quad posn="0 0 0.12" sizen="5.625 5.625" action="WindowList?Action=ClassWindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.85 -0.8 0.13" sizen="3.94 3.94" bgcolor="000F"/>';
			$buttons .= '<quad posn="0.4 -0.28125 0.14" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '</frame>';

			// Next (+5)
			$buttons .= '<frame posn="24.0625 0 0.05">';
			$buttons .= '<quad posn="0 0 0.12" sizen="5.625 5.625" action="WindowList?Action=ClassWindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.85 -0.8 0.13" sizen="3.94 3.94" bgcolor="000F"/>';
			$buttons .= '<quad posn="-0.35 -0.28125 0.14" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="1.1 -0.28125 0.15" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '</frame>';

			// Last
			$buttons .= '<frame posn="30.0625 0 0.05">';
			$buttons .= '<quad posn="0 0 0.12" sizen="5.625 5.625" action="WindowList?Action=ClassWindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.85 -0.8 0.13" sizen="3.94 3.94" bgcolor="000F"/>';
			$buttons .= '<quad posn="-0.25 -0.28125 0.14" sizen="4.875 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="3.275 -1.05625 0.15" sizen="1 3.1875" bgcolor="CCCF"/>';
			$buttons .= '</frame>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="18.1375 -0.281 0.12" sizen="5.0625 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Next (+5)
			$buttons .= '<quad posn="24.1375 -0.281 0.12" sizen="5.0625 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Last
			$buttons .= '<quad posn="30.1375 -0.281 0.12" sizen="5.0625 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}
		$buttons .= '</frame>';

		return $buttons;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildPageinfo () {
//		$xml  = '<frame posn="110.125 -101.8125 0.04">';
//		$xml .= '</frame>';
//		return $xml;
		return '';
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWindow () {

		// Placeholder:
		// - %content%
		// - %page%
		// - %buttons%
		// - %maniascript%

		// Begin Window
		$xml = '<manialink id="'. $this->settings['id'] .'" name="ClassWindow" version="1">';
		$xml .= '<frame posn="'. implode(' ', $this->layout['position']) .'" id="ClassWindow">';	// BEGIN: Window Frame
		$xml .= '<quad posn="-0.5 0.375 0.01" sizen="204.5 110.625" style="'. $this->layout['main']['background']['style'] .'" substyle="'. $this->layout['main']['background']['substyle'] .'" id="ClassWindowBody" ScriptEvents="1"/>';
		$xml .= '<quad posn="4.5 -7.6875 0.02" sizen="194.25 93.5625" bgcolor="'. $this->layout['main']['background']['color'] .'"/>';

		// Header Line
		$xml .= '<quad posn="-1.5 1.125 0.02" sizen="206.5 11.25" style="'. $this->layout['title']['background']['style'] .'" substyle="'. $this->layout['title']['background']['substyle'] .'"/>';
		$xml .= '<quad posn="-1.5 1.125 0.03" sizen="206.5 11.25" style="'. $this->layout['title']['background']['style'] .'" substyle="'. $this->layout['title']['background']['substyle'] .'" id="ClassWindowTitle" ScriptEvents="1"/>';

		// Title
		$xml .= '<quad posn="2.5 -1.7 0.04" sizen="5.5 5.5" style="'. $this->layout['title']['icon']['style'] .'" substyle="'. $this->layout['title']['icon']['substyle'] .'"/>';
		$xml .= '<label posn="9.75 -3.1 0.04" sizen="188.5 5" textsize="2" scale="0.9" textcolor="'. $this->layout['title']['textcolor'] .'" text="'. $this->content['title'] .'"/>';

		// Minimize Button
		$xml .= '<frame posn="187.5 -0.28125 0.05">';
		$xml .= '<quad posn="0 0 0.01" sizen="8.44 8.44" style="Icons64x64_1" substyle="ArrowUp" id="ClassWindowMinimize" ScriptEvents="1"/>';
		$xml .= '<quad posn="2.25 -2.4 0.02" sizen="3.75 3.75" bgcolor="EEEF"/>';
		$xml .= '<label posn="4.3 -4.5 0.03" sizen="15 0" halign="center" valign="center" textsize="3" textcolor="000F" text="$O-"/>';
		$xml .= '</frame>';

		// Close Button
		$xml .= '<frame posn="193.5 -0.28125 0.05">';
		$xml .= '<quad posn="0 0 0.01" sizen="8.44 8.44" style="Icons64x64_1" substyle="ArrowUp" id="ClassWindowClose" ScriptEvents="1"/>';
		$xml .= '<quad posn="2.25 -2.4 0.02" sizen="3.75 3.75" bgcolor="EEEF"/>';
		$xml .= '<quad posn="1.25 -1.3125 0.03" sizen="5.82 5.82" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '</frame>';

		// Content
		$xml .= '%content%';

		// Page info
		$xml .= '%page%';

		// Navigation Buttons
		$xml .= '%buttons%';

		// Footer
		$xml .= '</frame>';				// END: Window Frame
		$xml .= '%maniascript%';			// Maniascript
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildManiascript () {
$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.uaseco.org
 * Class:	window.class.php
 * License:	GPLv3
 * ----------------------------------
 */
Void WipeOut (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	if (Container != Null) {
		declare Real EndPosnX = 0.0;
		declare Real EndPosnY = 0.0;
		declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
		declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

		while (Container.RelativeScale > 0.0) {
			Container.RelativePosition.X += (PosnDistanceX / 20);
			Container.RelativePosition.Y += (PosnDistanceY / 20);
			Container.RelativeScale -= 0.05;
			yield;
		}
		Container.Unload();

//		// Disable catching ESC key
//		EnableMenuNavigationInputs = False;
	}
}
Void Minimize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = {$this->layout['position']['x']};
	declare Real EndPosnY = {$this->layout['position']['y']};
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale > 0.2) {
		Container.RelativePosition.X += (PosnDistanceX / 16);
		Container.RelativePosition.Y += (PosnDistanceY / 16);
		Container.RelativeScale -= 0.05;
		yield;
	}
}
Void Maximize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = {$this->layout['position']['x']};
	declare Real EndPosnY = {$this->layout['position']['y']};
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale < 1.0) {
		Container.RelativePosition.X += (PosnDistanceX / 16);
		Container.RelativePosition.Y += (PosnDistanceY / 16);
		Container.RelativeScale += 0.05;
		yield;
	}
}
main () {
	declare CMlControl Container <=> (Page.GetFirstChild("ClassWindow") as CMlFrame);
	declare CMlQuad Quad;
	declare Boolean MoveWindow = False;
	declare Boolean IsMinimized = False;
	declare Real MouseDistanceX = 0.0;
	declare Real MouseDistanceY = 0.0;

//	// Enable catching ESC key
//	EnableMenuNavigationInputs = True;

	while (True) {
		yield;
		if (MoveWindow == True) {
			Container.RelativePosition.X = (MouseDistanceX + MouseX);
			Container.RelativePosition.Y = (MouseDistanceY + MouseY);
		}
		if (MouseLeftButton == True) {
			if (PendingEvents.count > 0) {
				foreach (Event in PendingEvents) {
					if (Event.ControlId == "ClassWindowTitle") {
						MouseDistanceX = (Container.RelativePosition.X - MouseX);
						MouseDistanceY = (Container.RelativePosition.Y - MouseY);
						MoveWindow = True;
					}
				}
			}
		}
		else {
			MoveWindow = False;
		}
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ClassWindowClose") {
						WipeOut("ClassWindow");
					}
					else if ( (Event.ControlId == "ClassWindowMinimize") && (IsMinimized == False) ) {
						Minimize("ClassWindow");
						IsMinimized = True;
					}
					else if ( (Event.ControlId == "ClassWindowBody") && (IsMinimized == True) ) {
						Maximize("ClassWindow");
						IsMinimized = False;
					}
				}
//				case CMlEvent::Type::KeyPress : {
//					if (Event.KeyName == "Escape") {
//						WipeOut("ClassWindow");
//					}
//				}
			}
		}
	}
}
--></script>
EOL;
		return $maniascript;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function normalizeString ($string) {
		global $aseco;

		// Remove links, e.g. "$(L|H|P)[...]...$(L|H|P)"
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)\$(L|H|P)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)(.*?)/i', '$2', $string);

		// Remove $S (shadow)
		// Remove $H (manialink)
		// Remove $W (wide)
		// Remove $I (italic)
		// Remove $L (link)
		// Remove $O (bold)
		// Remove $N (narrow)
		$string = preg_replace('/\${1}[SHWILON]/i', '', $string);

		return $aseco->handleSpecialChars($string);
	}
}

?>
