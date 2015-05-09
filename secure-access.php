<?php
/**
 * Plugin Name: Secure Access
 * Plugin URI: https://github.com/bobbywalters/secure-access
 * Description: Secure your site by requiring users to log in.
 * Author: Bobby Walters
 * Author URI: https://github.com/bobbywalters
 * Version: 1.0.0
 * Text Domain: secure-access
 * Domain Path: /languages
 * License: GPLv2
 */

/*
Copyright (C) 2015 Bobby Walters

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; version 2
of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined('WPINC') or die('No direct access.');

require 'php/SecureAccess.php';

add_action('plugins_loaded', array(new SecureAccess\SecureAccess(), 'pluginsLoaded'));
