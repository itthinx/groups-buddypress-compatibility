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

function groups_buddpress_compatibility_groups_created_group_pre( $group_id ) {

	$is_groups = false;

// 	$args = func_get_args();
// 	if ( count( $args ) < 2 ) {
// 		$is_groups = true;
// 	}

// 	error_log(__FUNCTION__ . ' ' . var_export($args,true)); // @todo remove

	$traces = debug_backtrace();
	//error_log( __FUNCTION__. ' trace ... ' . var_export($traces,true) ); // @todo remove
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
		error_log('call is assumed from groups - removing BP actions'); // @todo remove
		global $groups_buddpress_compatibility;
		$groups_buddpress_compatibility = true;
		remove_action( 'groups_created_group', 'groups_update_last_activity' );
		remove_action( 'groups_created_group', 'bp_groups_clear_group_creator_cache', 10 );
		remove_action( 'groups_created_group', 'bp_core_clear_cache' );
	}
}

function groups_buddpress_compatibility_groups_created_group_post( $group_id ) {
	global $groups_buddpress_compatibility;
	if ( isset( $groups_buddpress_compatibility ) ) {
		error_log('call is assumed from groups - adding BP actions'); // @todo remove
		add_action( 'groups_created_group', 'groups_update_last_activity' );
		add_action( 'groups_created_group', 'bp_groups_clear_group_creator_cache', 10, 2 );
		add_action( 'groups_created_group', 'bp_core_clear_cache' );
	}
}

add_action( 'groups_created_group', 'groups_buddpress_compatibility_groups_created_group_pre', 0 );
add_action( 'groups_created_group', 'groups_buddpress_compatibility_groups_created_group_post', 99999 );
