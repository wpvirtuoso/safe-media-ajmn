<?php
/**
 * The plugin core file
 *
 * @link              https://www.aljazeera.com/
 * @package           Safe Media
 * @wordpress-plugin
 * Plugin Name:       Safe Media
 * Plugin URI:        https://www.aljazeera.com/
 * Description:       Manages media deletion safely
 * Version:           1.0.0
 * Author:            Muhammad Zaki
 * Author URI:        https://www.aljazeera.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       safe-media
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require plugin_dir_path( __FILE__ ) . 'inc/class-media.php';

/**
 * Begins execution of the plugin.
 */
function init() {

	$media = new Media();
}

init();
