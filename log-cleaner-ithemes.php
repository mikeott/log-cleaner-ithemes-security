<?php
/*
Plugin Name: Log cleaner for iThemes Security
Plugin URI: https://github.com/mikeott/log-cleaner-ithemes-security
Description: Delete iThemes Security logs.
Version: 1.2
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

// Add custom CSS to admin
function log_cleaner_admin_style() {
	$plugin_directory = plugins_url('css/', __FILE__ );
    wp_enqueue_style('invoice-vehicle-style-admin', $plugin_directory . 'log-cleaner.css');
}
add_action('admin_enqueue_scripts', 'log_cleaner_admin_style');

// Admin page.
function generate_page_content() { ?>
    
    <div class="wrap">
        <form action="" method="post">
            <?php
                $page   = $_GET["page"];

                if (isset($_POST['submit']) && wp_verify_nonce($_POST['things'], 'delete-things')) {

                    if (isset($_POST['logs']) || isset($_POST['lockouts']) || isset($_POST['temp'])) {

                        global $wpdb;
                        $charset_collate    = $wpdb->get_charset_collate();
                        $lockouts_table     = $wpdb->prefix . 'itsec_lockouts';
                        $log_table          = $wpdb->prefix . 'itsec_log';
                        $logs_table         = $wpdb->prefix . 'itsec_logs';
                        $temp_table         = $wpdb->prefix . 'itsec_temp';

                        if (isset($_POST['lockouts']) || isset($_POST['all'])) {
                            $wpdb->query("TRUNCATE TABLE " . $lockouts_table);
                        }

                        if (isset($_POST['logs']) || isset($_POST['all'])) {
                            $wpdb->query("TRUNCATE TABLE " . $log_table);
                            $wpdb->query("TRUNCATE TABLE " . $logs_table);
                        }
                        
                        if (isset($_POST['temp']) || isset($_POST['all'])) {
                            $wpdb->query("TRUNCATE TABLE " . $temp_table);
                        }
                        ?>

                        <div id="message" class="updated notice notice-success is-dismissible" style="margin: 20px 0;">
                            <p><?php _e("The selected logs have been deleted.", 'log-cleaner'); ?></p>
                            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e("Dismiss this notice.", 'log-cleaner'); ?></span></button>
                        </div>

                    <?php } else { ?>
                        <div id="message" class="error notice notice-success is-dismissible" style="margin: 20px 0;">
                            <p><?php _e("You need to select at least one item to delete.", 'log-cleaner'); ?></p>
                            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e("Dismiss this notice.", 'log-cleaner'); ?></span></button>
                        </div>
                    <?php  }

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

            <p><?php _e("Note: Continuing here will delete the selected iThemes Security logs from the database. You absolutely can not undo this action. If in doubt, backup your database first.", 'log-cleaner'); ?></p>

            <p>
                <?php _e("View logs before deleting", 'log-cleaner'); ?>:  
                <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Cimportant"><?php _e("Important Events", 'log-cleaner'); ?></a> | 
                <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Call"><?php _e("All Events", 'log-cleaner'); ?></a> | 
                <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Cwarning"><?php _e("Warnings", 'log-cleaner'); ?></a> | 
                <a href="<?php echo admin_url(); ?>/admin.php?page=itsec-logs&filters=type%7Cnotice"><?php _e("Notices", 'log-cleaner'); ?></a>
            </p>

            <div class="log-cleaner boxy">
                <p><strong><?php _e("Clear the following log tables: ", 'log-cleaner'); ?></strong></p>
                <ul>
                    <li><input type="checkbox" name="all" id="all" /> <?php _e("All", 'log-cleaner'); ?></li>
                    <li><input type="checkbox" name="logs" /> <?php _e("Security logs", 'log-cleaner'); ?></li>
                    <li><input type="checkbox" name="lockouts" /> <?php _e("Lockouts", 'log-cleaner'); ?></li>
                    <li><input type="checkbox" name="temp" /> <?php _e("Temps", 'log-cleaner'); ?></li>
                </ul>
                <script>
                    jQuery('#all').click(function(event) {   
                        if(this.checked) {
                            // Iterate each checkbox
                            jQuery(':checkbox').each(function() {
                                this.checked = true;                        
                            });
                        } else {
                            jQuery(':checkbox').each(function() {
                                this.checked = false;                       
                            });
                        }
                    });
                </script>
            </div>

            <div class="log-cleaner boxy">

                <?php if($total > 0) { ?>
                    <p><strong><?php _e("Table information:", 'log-cleaner'); ?></strong></p>
                <?php } else { ?>
                    <p><strong><?php _e("All log tables are clear. Now get on with the rest of your day.", 'log-cleaner'); ?></strong></p>
                <?php } ?>

                <table border="0" cellspacing="0" cellpadding="2" class="lc-table">
                    <tr>
                        <td><?php _e("Logs", 'log-cleaner'); ?>:</td>
                        <td><?php echo $combined; ?></td>
                    </tr>
                    <tr>
                        <td><?php _e("Lockouts", 'log-cleaner'); ?>:</td>
                        <td><?php echo $num_lockouts; ?></td>
                    </tr>
                    <tr>
                        <td><?php _e("Temps", 'log-cleaner'); ?>:</td>
                        <td><?php echo $num_temps; ?></td>
                    </tr>
                    <tr class="lc-total">
                        <td><strong><?php _e("Total entries", 'log-cleaner'); ?>:</strong></td>
                        <td><strong><?php echo $total; ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <?php  // If the total number of log entries is not 0, and if you're an administrator
                if(current_user_can( 'manage_options' )) { ?>
                <input type="submit" name="submit" class="button button-primary" value="<?php _e('Clear logs', 'log-cleaner'); ?>" onclick="return confirm('<?php _e('This is your last chance. Are you sure?', 'log-cleaner'); ?>')" />
                <?php wp_nonce_field( 'delete-things','things' ) ?>
            <?php } ?>
        </form>
    </div>

<?php }