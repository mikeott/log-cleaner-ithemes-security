<?php
/*
Plugin Name: Log cleaner for iThemes Security
Plugin URI: https://github.com/mikeott/log-cleaner-ithemes-security
Description: Delete iThemes Security logs in a single click.
Version: 1.1
Author: Michael Ott
Author Email: hello@michaelott.id.au
Text Domain: log-cleaner
Domain Path: /languages/
*/

// Look for translation file.
function load_log_cleaner_textdomain() {
    load_plugin_textdomain( 'log-cleaner', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'load_log_cleaner_textdomain' );

// Create admin page under the Toosl menu.
add_action('admin_menu', 'create_tools_cleaner_submenu');
function create_tools_cleaner_submenu() {
    add_management_page( 'Log cleaner for iThemes Security', 'Log cleaner', 'manage_options', 'log-cleaner', 'generate_page_content' );
}

// Admin page.
function generate_page_content() { ?>
    
    <div class="wrap">

        <?php
            $action = $_GET['action'];
            $page   = $_GET["page"];
            $nonce  = wp_create_nonce( 'cleaner' );

            if($action == 'clean') {

                $nonce = $_REQUEST['_wpnonce'];

                if ( ! wp_verify_nonce( $nonce, 'cleaner' ) ) {

                    die( 'Security check' ); 

                } else {

                    global $wpdb;
                    $charset_collate    = $wpdb->get_charset_collate();
                    $lockouts_table     = $wpdb->prefix . 'itsec_lockouts';
                    $log_table          = $wpdb->prefix . 'itsec_log';
                    $logs_table         = $wpdb->prefix . 'itsec_logs';
                    $temp_table         = $wpdb->prefix . 'itsec_temp';
                    $wpdb->query("TRUNCATE TABLE " . $lockouts_table);
                    $wpdb->query("TRUNCATE TABLE " . $log_table);
                    $wpdb->query("TRUNCATE TABLE " . $logs_table);
                    $wpdb->query("TRUNCATE TABLE " . $temp_table); ?>

                    <div id="message" class="updated notice notice-success is-dismissible" style="margin: 20px 0;">
                        <p><?php _e("The logs have been deleted.", 'log-cleaner'); ?></p>
                        <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e("Dismiss this notice.", 'log-cleaner'); ?></span></button>
                    </div>

                <?php }

            }
        ?>

        <h1><?php _e('Log cleaner for iThemes Security', 'log-cleaner'); ?></h1>

        <?php 
            global $wpdb;
            $itsec_lockouts = $wpdb->prefix . 'itsec_lockouts';
            $lockouts_query = "select count(*) from $itsec_lockouts";
            $num_lockouts   = $wpdb->get_var($lockouts_query);

            $itsec_log = $wpdb->prefix . 'itsec_log';
            $log_query = "select count(*) from $itsec_log";
            $num_log   = $wpdb->get_var($log_query);

            $itsec_logs = $wpdb->prefix . 'itsec_logs';
            $logs_query = "select count(*) from $itsec_logs";
            $num_logs   = $wpdb->get_var($logs_query);

            $combined   = $num_log + $num_logs;

            $itsec_temp = $wpdb->prefix . 'itsec_temp';
            $temp_query = "select count(*) from $itsec_temp";
            $num_temps  = $wpdb->get_var($temp_query);

            $total      = $num_lockouts + $combined + $num_temps;

            global $current_user;
        ?>

        <?php if($total !=0) { ?>
        <div id="message" class="notice notice-warning" style="margin: 20px 0;">
            <p><?php if($action !== 'clean') { _e("Continuing here will delete all iThemes Security logs from the database. You absolutely can not undo this action. If in doubt, backup your database first.", 'log-cleaner'); } ?></p>
        </div>
        <?php } ?>

        <p>
            View logs before deleting: 
            <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Cimportant">Important Events</a> | 
            <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Call">All Events</a> | 
            <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Cwarning">Warnings</a> | 
            <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Cnotice">Notices</a>
        </p>

        <p><strong><?php _e("Table information", 'log-cleaner'); ?></strong></p>

        <table width="150" border="0" cellspacing="2" cellpadding="2">
            <tr>
                <td><strong><?php _e("Lockouts", 'log-cleaner'); ?>:</strong></td>
                <td><?php echo $num_lockouts; ?></td>
            </tr>
            <tr>
                <td><strong><?php _e("Logs", 'log-cleaner'); ?>:</strong></td>
                <td><?php echo $combined; ?></td>
            </tr>
            <tr>
                <td><strong><?php _e("Temps", 'log-cleaner'); ?>:</strong></td>
                <td><?php echo $num_temps; ?></td>
            </tr>
            <tr>
                <td><strong><?php _e("Total entries", 'log-cleaner'); ?>:</strong></td>
                <td><?php echo $total; ?></td>
            </tr>
        </table>
        
        <?php  // If the total number of log entries is not 0, and if you're an administrator
            if($total !=0 && current_user_can( 'manage_options' )) { ?>
            <p>
                <a href="<?php echo get_admin_url(); ?>tools.php?page=<?php echo $page; ?>&_wpnonce=<?php echo $nonce; ?>&action=clean" class="button button-primary" onclick="return confirm('<?php _e('This is your last chance. Are you sure?', 'log-cleaner'); ?>')"><?php _e('Delete all logs', 'log-cleaner'); ?></a>
            </p>
        <?php } ?>

    </div>

<?php }