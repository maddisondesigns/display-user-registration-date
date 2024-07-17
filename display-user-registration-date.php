<?php
/*
Plugin Name: Display User Registration Date
Plugin URI: https://github.com/maddisondesigns/display-user-registration-date
Description: A simple plugin to display the registration date of your Users on the Users List screen and the single User page.
Version: 1.0.1
Author: Anthony Hortin
Author URI: http://maddisondesigns.com
Text Domain: display-user-registration-date
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

class durd_display_user_registration_date_plugin {

	const SETTINGS_NAMESPACE = 'display_user_registration_date';
	public $timezoneString = '';
	public $dateFormat = '';
	public $timeFormat = '';

	/**
	 * Add the necessary filters to display the registration date and to make the column sortable
	 */
	public function __construct() {
		$this->timezoneString = new DateTimeZone( wp_timezone_string() );
		$this->dateFormat = get_option( 'date_format' );
		$this->timeFormat = get_option( 'time_format' );

		if ( is_admin() ) {
			// Add Filters for User list page
			add_filter( 'manage_users_columns', array( $this, 'durd_manage_users_columns' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'durd_manage_users_custom_column' ), 10, 3 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'durd_manage_users_sortable_columns' ) );

			// Add Actions for User profile page
			add_action( 'show_user_profile', array( $this, 'durd_display_reg_date' ) );
			add_action( 'edit_user_profile', array( $this, 'durd_display_reg_date' ) );

		}
	}

	/**
	 * Add User Registration Date column to User list page
	 */
	public function durd_manage_users_columns( $columns ) {
		$columns['registration_date'] = 'Registered';
		return $columns;
	}

	/**
	 * Display data for new user registration date column
	 */
	public function durd_manage_users_custom_column( $row_output, $column_id_attr, $user ) {
		$timestamp = get_userdata($user)->user_registered;
		$date = DateTime::createFromFormat( 'Y-m-d G:i:s', $timestamp, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( $this->timezoneString );

		if ( !empty( $timestamp ) ) {
			if ( false === $date ) {
				return( 'Invalid date format' );
			} 
			else {
				return $date->format( 'Y/m/d g:i:s a T' );
			}
		}

		return $row_output;
	}

	/**
	 * Make the registration date column sortable
	 */
	public function durd_manage_users_sortable_columns( $columns ) {
		return wp_parse_args( array( 'registration_date' => 'registered' ), $columns );
	}

	/**
	 * Display the registration date on the User Profile page
	 */
	public function durd_display_reg_date( $user ) {
		$timestamp = $user->user_registered;
		$date = DateTime::createFromFormat( 'Y-m-d G:i:s', $timestamp, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( $this->timezoneString );

		echo '<table class="form-table"><tr>';
		echo '<th><label for="registration_date">' . esc_html( __( 'User Registration Date', 'display-user-registration-date' ) ) . '</label></th>';
		echo '<td>';
		echo '<input type="text" name="registration_date" id="registration_date" value="' . esc_attr( $date->format( $this->dateFormat . ' @ ' . $this->timeFormat . ' T' ) ) . '" class="regular-text" readonly="readonly" />';
		echo '<p class="description">' . esc_html( __( 'The user registration date cannot be edited.', 'display-user-registration-date' ) ) . '</p>';
		echo '</td>';
		echo '</tr></table>';
	}
}

$durd_display_user_registration_date = new durd_display_user_registration_date_plugin();
