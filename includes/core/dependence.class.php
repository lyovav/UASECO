<?php
/*
 * Class: Dependence
 * ~~~~~~~~~~~~~~~~~
 * » Structure for dependencies of Plugins.
 * » Based upon dependence.class.php from ASECO/2.2.0c
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-20
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


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Dependence {
	public $classname;
	public $permissions;
	public $min_version;
	public $max_version;

	const DISALLOWED	= 1;
	const WANTED		= 2;
	const REQUIRED		= 4;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($classname, $permissions = self::REQUIRED, $min_version = null, $max_version = null) {
		$this->classname	= $classname;
		$this->permissions	= $permissions;
		$this->min_version	= $min_version;
		$this->max_version	= $max_version;
	}
}

?>
