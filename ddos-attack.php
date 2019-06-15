<?php
/*
Plugin Name: DDOS Attack
Plugin URI: http://correiatec.com.br/ddos-attack
Description: Protect WP-Login from DDOS Attack. To get started: activate the DDOS Attack
Version: 1.0.0
Author: Paulo Correia
Author URI: http://correiatec.com.br
Domain Path: /languages
*/
register_activation_hook( __FILE__, 'sl_activation' );
register_deactivation_hook(__FILE__, 'sl_deactivation' );

/*
 * Debug Function
 * Only Work if WP_DEBUG is Enabled
 * And WP_DEBUG_LOG is Enabled
 * Write on WP Error Log /wp-content/debug.log
*/
function sl_debug($message) {
	if( WP_DEBUG === true ) {
		if ( (is_array($message)) || (is_object($message)) ) {
			error_log("[DDOS Attack] ".print_r($message, true));
		} else {
			error_log("[DDOS Attack] ".$message);
		}
	}
}

/*
 * Activation Function
 * AutoEnable Options Values
 * Call sl_debug
 */
function sl_activation() {

    $activation = ["enabled"=>true, "quantity"=>10, "site" => "https://google.com", "email"=> true];

    add_option( 'ddosattack_options', $activation);

    sl_debug("Enabled");
}

/*
 * Deactivation Function
 * Destroy Options Values
 * Call sl_debug
 */
function sl_deactivation() {

    delete_option('ddosattack_options');

    sl_debug("Disabled");
}

function app_output_buffer() {
        ob_start();
}

add_action('init', 'app_output_buffer');

/*
 * Charge Internationalization
 */
$ret = load_plugin_textdomain( 'ddos-attack', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/*
 * Admin Menu
 * Show HTML Menu
 * Add Users Typed Value on Option Value
 */
 function sl_menu_html() {

     $ret = load_plugin_textdomain( 'ddos-attack', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

     if (!current_user_can('manage_options')) {
        return;
 }

 /*
  * Show Option
  * Imput: option
  * Return: get_option
  */
 function sl_show_option($option) {
       $sl_options = get_option('ddosattack_options');
       if (isset($sl_options[$option])) {
           return $sl_options[$option];
       }
 }

 ?>
    <!-- Settings Page: HTML Content, Some PHP and WP Calls to Internationalization-->
    <div class="wrap">
        <div>
            <h1><? _e(get_admin_page_title(), 'ddos-attack'); ?></h1>
            <form method="post" >
            <div>
                &nbsp;
            </div>
            <div>
                <label for="enabled"><? _e('Enabled', 'ddos-attack') ?>:</label>
                <select style="<?
                if ($ret) {
                    echo "margin-left: 71px";
                } else {
                    echo "margin-left: 63px";
                }
                ?>;" name='enabled'>
                    <?php if (sl_show_option("enabled")=='true') {
                        echo "<option value='true' >";
                        _e('Yes', 'ddos-attack');
                        echo "</option>";
                        echo "<option value='false'>";
                        _e('No', 'ddos-attack');
                        echo "</option>";
                    } else {
                        echo "<option value='false'>";
                        _e('No', 'ddos-attack');
                        echo "</option>";
                        echo "<option value='true'>";
                        _e('Yes', 'ddos-attack');
                        echo "</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="quantity"><? _e('Quantity', 'ddos-attack') ?>:</label>
                <input type="text" name="quantity" size="3" style="<?
                    if ($ret) {
                        echo "margin-left: 2px";
                    } else {
                        echo "margin-left: 59px";
                    }
                ?>
                ;" value="<?php echo sl_show_option("quantity");?>" >
            </div>
            <div>
                <label for="site"><? _e('Redirected Site', 'ddos-attack') ?>:</label>
                <input type="text" name="site" size="30" style="margin-left: 20px;" value="<?php echo sl_show_option("site");?>" >
            </div>
            <div>
                <label for="email"><? _e('Send E-mail', 'ddos-attack') ?>:</label>
                <select style="<?
                if ($ret) {
                    echo "margin-left: 55px";
                } else {
                    echo "margin-left: 42px";
                }
                ?>
                ;" name='email'>
                    <?php if (sl_show_option("email")=='true') {
                        echo "<option value='true' >";
                        _e('Yes', 'ddos-attack');
                        echo "</option>";
                        echo "<option value='false'>";
                        _e('No', 'ddos-attack');
                        echo "</option>";
                    } else {
                        echo "<option value='false'>";
                        _e('No', 'ddos-attack');
                        echo "</option>";
                        echo "<option value='true'>";
                        _e('Yes', 'ddos-attack');
                        echo "</option>";
                    }
                    ?>
                </select>
                &nbsp;
                <? _e('Administrator E-mail', 'ddos-attack');
                    echo ":&nbsp;<strong>".get_option('admin_email')."</strong>";
                 ?>
             </div>
            <?
                settings_fields('ddosattack_options');
                do_settings_sections('ddosattack');
                submit_button(__('Save Settings', 'ddos-attack') );
            ?>
            </form>
        </div>
    </div>
<?php
}

/*
 * Function Menu Page
 * Add On WP Dashboard
 */
function sl_menu_page() {
	add_menu_page(
		__('Settings', 'ddos-attack'),
		__('DDOS Attack Options', 'ddos-attack'),
		'manage_options',
		'ddos-attack.php',
		'sl_menu_html' //,
	);
}

/*
 * WP Admin Menu Hook
 */
add_action('admin_menu', 'sl_menu_page');

/*
 * WP Login Message Hook
 * Annonymous Function:
 * If Disabled on Option Page by User, do nothing
 * Check if exists a file, if not exists do nothing
 * Read External IP and Counter
 * if limit Typed by the user <=0, do nothing
 * If Counter > limit Typed by User redirect login page to an User Typed page
 */
add_filter("login_message", function() {

    function sl_show_option($option) {
        $sl_options = get_option('ddosattack_options');
        if (isset($sl_options[$option])) {
            return $sl_options[$option];
        }
    }

    if (sl_show_option("enabled")=="true") {

        $dir = __DIR__;
        $limit = sl_show_option("quantity");
        $site =  sl_show_option("site");

        if (intval($limit)>0) {

            if (file_exists($dir . "/ddos/attack")) {

                $ipblk = file($dir . "/ddos/attack");
                $ipremoto = $_SERVER['REMOTE_ADDR'];

                for ($idsc = 0; $idsc < count($ipblk); $idsc++) {

                    $exts = strstr($ipblk[$idsc], $ipremoto);

                    if (strlen($exts) > 0) {

                        $spex = explode("-", $exts);
                        $cnt = intval($spex[1]) + 1;

                        if ($cnt > intval($limit)) {

                            if (strlen(trim($site))>0) {
				
                                header('Location: '.$site);

			    }
                        }
                    }
                }
            }
        }
    }
} );

/*
 * WP Login Errors Hook
 * Annonymous Function:
 * If Disabled on Option Page by User, do nothing
 * If not exists an directory create, else do nothing
 * If file exists Write External IP and Counter, ele create file with External IP and Counter = 1
 * If Limit typed by user <=0 do nothing
 * If User disable send e-mail not send a e-mail, else send
 */
add_filter( 'login_errors', function( $error ) {

    global $errors;
    $err_codes = $errors->get_error_codes();

    if (sl_show_option("enabled")=='true') {

        $limit = sl_show_option("quantity");

        if (intval($limit) > 0) {

            $ipremoto = $_SERVER['REMOTE_ADDR'];
            //$u_agent = $_SERVER['HTTP_USER_AGENT'];
            $dir = __DIR__;

            if (!file_exists($dir . "/ddos")) {
                mkdir($dir . "/ddos");
            }

            if (file_exists($dir . "/ddos/attack")) {

                $ipblk = file($dir . "/ddos/attack");

                for ($idsc = 0; $idsc < count($ipblk); $idsc++) {

                    $exts = strstr($ipblk[$idsc], $ipremoto);

                    if (strlen($exts) > 0) {

                        $spex = explode("-", $exts);

                        $cnt = intval($spex[1]) + 1;

                        $ipblk[$idsc] = str_replace($spex[0] . "-" . $spex[1], $spex[0] . "-" . $cnt . "\n", $exts);

                        if ($cnt == intval($limit)) {

                            if (sl_show_option("email")=="true") {

                                $admin_email = get_option('admin_email');

                                $ret_mail = wp_mail( $admin_email, "[".get_site_url()."]-Bloked IP/Bloqueio de IP ", "\nIP: ".$ipremoto."\nAttempts/Tentativas: ".$cnt."\nLimit/Numero de Tentativas: ".$limit, "", "" );

                                sl_debug("Mail Send?");
                                if ($ret_mail) {
                                    sl_debug("true");
                                } else {
                                    sl_debug("false");
                                }

                            }
                        }
                    }
                }

                file_put_contents($dir . "/ddos/attack", $ipblk);

            } else {
                file_put_contents($dir . "/ddos/attack", $ipremoto . "-1\n");
            }
        }
    }

    sl_debug("Error Codes");
    sl_debug($err_codes);

    return $error;

} );

/*
 * Save Posted Data on Options
 */
if (isset($_POST["submit"])) {

    $all_data = $_POST;
    $arr_data = array_diff($all_data, array( $_POST["option_page"], $_POST["action"], $_POST["_wpnonce"],
        $_POST["_wp_http_referer"], $_POST["submit"]) );

    update_option('ddosattack_options', $arr_data);
}
