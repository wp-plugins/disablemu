<?php
/**
 * Plugin Name: DisableMU
 * Plugin URI: http://www.binarytemplar.com/disableMU
 * Description: Tricks WordPress into being unable to find the /mu-plugins directory by redefining the location to an invalid directory path. Simply deactivate this plugin to undo this change.
 * Version: 1.0
 * Author: Dave McHale
 * Author URI: http://www.binarytemplar.com
 * License: GPL2+
 */

/*  Copyright 2014 Dave McHale (email : dmchale@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook( __FILE__, 'disableMU_activate' );
register_deactivation_hook( __FILE__, 'disableMU_deactivate' );

/**
 * Activation function
 */
function disableMU_activate() {
    // Add our code to wp-config, if there are no conflicts
    disableMU_add_to_config();
}


/**
 * Deactivation function
 */
function disableMU_deactivate() {
    // Remove our code from wp-config
    disableMU_remove_from_config();
}


/**
 * Create random text of a specified length
 *
 * @param int $length
 * @return string
 */
function disableMU_generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}


/**
 * Throw fatal error - prevents plugin from activating when something goes wrong
 *
 * @param $str
 */
function disableMU_send_error($str) {
    trigger_error($str,E_USER_ERROR);
}


/**
 * Add our contents to wp-config, if able & if none of our constants are already defined
 *
 * @return bool
 */
function disableMU_add_to_config() {
    $disableMU_random_string = disableMU_generate_random_string(20);

    $disableMU_disableText = "" . PHP_EOL . PHP_EOL;
    $disableMU_disableText .= "// Definitions by DisableMU plugin" . PHP_EOL;
    $disableMU_disableText .= "if ( !defined('WPMU_PLUGIN_DIR') ) { define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-$disableMU_random_string' ); }" . PHP_EOL;
    $disableMU_disableText .= "if ( !defined('WPMU_PLUGIN_URL') ) { define( 'WPMU_PLUGIN_URL', WP_CONTENT_URL . '/mu-$disableMU_random_string' ); }" . PHP_EOL;
    $disableMU_disableText .= "if ( !defined( 'MUPLUGINDIR' ) ) { define( 'MUPLUGINDIR', 'wp-content/mu-$disableMU_random_string' ); }" . PHP_EOL;
    $disableMU_disableText .= "// END Definitions by DisableMU plugin" . PHP_EOL;

    $edit_file_config_entry_exists = false;

    //Config file path
    $config_file = ABSPATH . 'wp-config.php';

    //Get wp-config.php file contents so we can check if our stuff already exists
    $config_contents = file($config_file);

    foreach ($config_contents as $line_num => $line) {
        if ((strpos($line, "'WPMU_PLUGIN_DIR'")) || (strpos($line, "'WPMU_PLUGIN_URL'")) || (strpos($line, "'MUPLUGINDIR'"))) {
            disableMU_send_error(__('wp-config.php is already manually defining the path to mu-plugins. No changes were made.', 'disablemu'));
            return false;
        }

        //For wp-config.php files originating from early WP versions we will remove the closing php tag
        if (strpos($line, "?>") !== false) {
            $config_contents[$line_num] = str_replace("?>", "", $line);
        }
    }

    if (!$edit_file_config_entry_exists) {
        $config_contents[] = $disableMU_disableText; //Append the new snippet to the end of the array

        //Make a backup of the config file
        if (!disableMU_backup_and_rename_wp_config($config_file)) {
            disableMU_send_error(__('Failed to make a backup of the wp-config.php file. This operation will not go ahead.', 'disablemu'));
            return false;
        }

        //Now let's modify the wp-config.php file
        if (disableMU_write_content_to_file($config_file, $config_contents)) {
            return true;
        } else {
            disableMU_send_error(__("Unable to modify wp-config.php", 'disablemu'));
            return false;
        }
    }

    return true;

}


/**
 * Backs up wp-config before writing to it.
 *
 * @param $src_file_path
 * @param string $prefix
 * @return bool
 */
function disableMU_backup_and_rename_wp_config($src_file_path, $prefix = 'backup')
{
    //Check to see if the main "backups" directory exists - create it otherwise
    $disableMU_backup_dir = WP_CONTENT_DIR . '/disableMU-backups';
    if (!disableMU_create_dir($disableMU_backup_dir)) {
        disableMU_send_error(__("backup_and_rename_wp_config - Creation of backup directory failed!", 'disablemu'));
        return false;
    }

    $src_parts = pathinfo($src_file_path);
    $backup_file_name = $prefix . '.' . $src_parts['basename'];

    $backup_file_path = $disableMU_backup_dir . '/' . $backup_file_name;
    if (!copy($src_file_path, $backup_file_path)) {
        //Failed to make a backup copy
        return false;
    }
    return true;
}


/**
 * Write contents to file
 *
 * @param $file_path
 * @param $new_contents
 * @return bool
 */
function disableMU_write_content_to_file($file_path, $new_contents) {
    @chmod($file_path, 0777);
    if (is_writeable($file_path)) {
        $handle = fopen($file_path, 'w');
        foreach( $new_contents as $line ) {
            fwrite($handle, $line);
        }
        fclose($handle);
        @chmod($file_path, 0644); //Let's change the file back to a secure permission setting
        return true;
    } else {
        return false;
    }
}


/**
 * Creates directory if it doesnt exist
 *
 * @param string $dirpath
 * @return bool
 */
function disableMU_create_dir($dirpath='') {
    $res = true;
    if ($dirpath != '') {
        if (!file_exists($dirpath)) {
            $res = mkdir($dirpath, 0755);
        }
    }
    return $res;
}


/**
 * Deletes content from wp-config on deactivation of the plugin
 */
function disableMU_remove_from_config() {

    $need_to_resave_config_file = FALSE;

    //Config file path
    $config_file = ABSPATH . 'wp-config.php';

    //Get wp-config.php file contents so we can check if our stuff already exists
    $config_contents = file($config_file);

    foreach ($config_contents as $line_num => $line) {
        if (strpos($line, "Definitions by DisableMU plugin")) {
            $need_to_resave_config_file = TRUE;

            unset($config_contents[$line_num]);
            unset($config_contents[$line_num+1]);
            unset($config_contents[$line_num+2]);
            unset($config_contents[$line_num+3]);
            unset($config_contents[$line_num+4]);
            break;
        }
    }

    if ($need_to_resave_config_file === TRUE) {
        //Make a backup of the config file
        if (!disableMU_backup_and_rename_wp_config($config_file)) {
            disableMU_send_error(__('Failed to make a backup of the wp-config.php file. This operation will not go ahead.', 'disablemu'));
            return false;
        }

        //Now let's modify the wp-config.php file
        if (disableMU_write_content_to_file($config_file, $config_contents)) {
            return true;
        } else {
            disableMU_send_error(__("Unable to modify wp-config.php", 'disablemu'));
            return false;
        }
    }

    return true;

}