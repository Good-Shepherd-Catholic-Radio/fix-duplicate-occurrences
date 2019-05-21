<?php
/**
 * Provides helper functions.
 *
 * @since	  {{VERSION}}
 *
 * @package	fix_duplicated_occurrences
 * @subpackage fix_duplicated_occurrences/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		{{VERSION}}
 *
 * @return		fix_duplicated_occurrences
 */
function FIXDUPLICATEDOCCURRENCES() {
	return fix_duplicated_occurrences::instance();
}