<?php
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

namespace SecureAccess;

/**
 * The main entry point for all interaction of the Secure Access
 * plugin. All the WordPress hooks ae registered within this class.
 *
 * @package secureaccess
 * @author Bobby Walters
 * @link https://github.com/bobbywalters/secure-access
 * @license GPLv2
 * @copyright 2015 Bobby Walters
 */
class SecureAccess {
	/**
	 * An action for "init" that loads the secure access text domain for
	 * internationalization (i18n) support.
	 *
	 * The directory containing the gettext files is "languages" at the
	 * base of the plugin directory by default.
	 */
	function init() {
		load_plugin_textdomain('secure-access', false, 'secure-access/languages');
	}

	/**
	 * A filter for "do_parse_request" that actually triggers the logic
	 * for secure access.
	 *
	 * This fitler performs the security check and may cause a redirect
	 * if the user has not logged in yet. The secure access check will
	 * not be performed if the current page is one of the log in screens.
	 *
	 * @param bool $bool Whether or not to parse the request. Default
	 * true.
	 * @param WP $wp Current WordPress environment instance.
	 * @param array|string $extra_query_vars Extra passed query variables.
	 * @global string $pagenow The current PHP page being displayed. Will
	 * always end with ".php" and will never be empty.
	 * @uses is_user_logged_in
	 * @uses auth_redirect
	 */
	function doParseRequest($bool, $wp, $extra_query_vars) {
		global $pagenow;
		switch ($pagenow) {
			case 'wp-login.php':
			case 'wp-signup.php':
				return;
		}

		if (!is_user_logged_in()) {
			auth_redirect();
		}

		return $bool;
	}

	/**
	 * A filter for "login_errors" that adds the secure access log in
	 * message if no other message is queued up for display.
	 *
	 * This filter is conditionally registered for the login screen and
	 * the login action. Registration, verify e-mail, etc screens will
	 * never add the secure access message.
	 *
	 * @param WP_Error $errors A collection of error codes and messages
	 * that will be displayed back to the user. There may not be errors
	 * added to the object but the object itself will never be null.
	 * @param string $redirect_to A URL that the user will be redirected
	 * to.
	 * @global string $error A single error message that plugins may use
	 * instead of the errors object to pass a message along to the user.
	 * @see #loginFormLogin
	 */
	function loginErrors($errors, $redirect_to) {
		global $error;

		if (!$error && !$errors->get_error_code()) {
			$errors->add('secureaccess',
				esc_html__('Please log in to view this site.', 'secure-access'),
				'message');
		}

		return $errors;
	}

	/**
	 * An action for "login_form_login" that will register two (2)
	 * filters for proper handling of the log in screen messages.
	 *
	 * @see #loginMessage
	 * @see #loginErrors
	 */
	function loginFormLogin() {
		add_filter('login_message', array(&$this, 'loginMessage'));
		add_filter('wp_login_errors', array(&$this, 'loginErrors'), 99, 2);
	}

	/**
	 * An action for "login_head" to echo out some CSS rules that secure
	 * the site a little bit better.
	 */
	function loginHead() {
		echo '<style type="text/css">#login>h1:first-child,#backtoblog{display:none}</style>';
	}

	/**
	 * A filter for "login_message" that will add the secure access
	 * message only if the supplied $message is empty.
	 *
	 * The secure access message will only be added if no other error or
	 * regular status message is set.
	 *
	 * @param string $message A message possibly coming from another
	 * plugin that will be displayed on the log in screen
	 * @see #loginErrors
	 * @uses remove_filter
	 */
	function loginMessage($message) {
		if ($message) {
			remove_filter('wp_login_errors', array(&$this, 'loginErrors'), 99);
		}

		return $message;
	}

	/**
	 * A filter for "logout_url" that removes the "redirect_to" request
	 * parameter to get a proper logged out message.
	 *
	 * If this filter wasn't in place, the redirect would immediately
	 * happen after logging out but since the site is secured the user
	 * would be prompted to log in anyway. This cuts down on the traffic
	 * and keeps the messages back to the user correct.
	 *
	 * @param string $logout_url The URL to log out of the site.
	 * NOTE: this value appears to be HTML escaped...
	 * @param string $redirect The URL that the user would have been
	 * redirected to.
	 */
	function logoutUrl($logout_url, $redirect) {
		// It seems $logout_url will be escaped for HTML at this point
		if ($redirect) {
			$logout_url = str_replace(
				array('redirect_to=' . urlencode($redirect), '?&amp;', '&amp;&amp;'),
				array('', '?', '&amp;'),
				$logout_url);
		}

		return $logout_url;
	}

	/**
	 * Registers most of the hooks back into WordPress.
	 */
	function pluginsLoaded() {
		add_action('init', array(&$this, 'init'));
		add_action('login_form_login', array(&$this, 'loginFormLogin'));
		add_action('login_head', array(&$this, 'loginHead'), 99);

		add_filter('do_parse_request', array(&$this, 'doParseRequest'), 1, 3);
		add_filter('logout_url', array(&$this, 'logoutUrl'), 99, 2);
	}
}
