<?php
/**
 * Plugin Name: Groups BuddyPress Compatibility
 * Plugin URI: http://www.itthinx.com/
 * Description: A solution to avoid action clashes between Groups and BuddyPress.
 * Version: 1.0.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 * Donate-Link: http://www.itthinx.com
 * License: GPLv3
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Identifies a call on the groups_created_group action from Groups (as opposed to those calls
 * coming from BuddyPress). Disables the related BuddyPress actions when appropriate, i.e. when
 * the call comes from Groups and not from BuddyPress.
 *
 * @param int $group_id
 */
function groups_buddpress_compatibility_groups_created_group_pre( $group_id ) {

	$is_groups = false;

	$traces = debug_backtrace();
	if ( is_array( $traces ) ) {
		foreach( $traces as $trace ) {
			if ( isset( $trace['file'] ) ) {
				if ( strpos( $trace['file'], 'class-groups-group.php' ) !== false ) {
					$is_groups = true;
					break;
				}
			}
		}
	}
	if ( $is_groups ) {
		// call is assumed to come from groups - removing BP actions
		global $groups_buddpress_compatibility;
		$groups_buddpress_compatibility = true;
		remove_action( 'groups_created_group', 'groups_update_last_activity' );
		remove_action( 'groups_created_group', 'bp_groups_clear_group_creator_cache', 10 );
		remove_action( 'groups_created_group', 'bp_core_clear_cache' );
	}
}

/**
 * Adds BuddyPress actions hooked on groups_created_group back.
 *
 * @param int $group_id
 */
function groups_buddpress_compatibility_groups_created_group_post( $group_id ) {
	global $groups_buddpress_compatibility;
	if ( isset( $groups_buddpress_compatibility ) ) {
		// call was assumed to come from groups - adding BP actions back
		add_action( 'groups_created_group', 'groups_update_last_activity' );
		add_action( 'groups_created_group', 'bp_groups_clear_group_creator_cache', 10, 2 );
		add_action( 'groups_created_group', 'bp_core_clear_cache' );
	}
}

add_action( 'groups_created_group', 'groups_buddpress_compatibility_groups_created_group_pre', 0 );
add_action( 'groups_created_group', 'groups_buddpress_compatibility_groups_created_group_post', 99999 );
