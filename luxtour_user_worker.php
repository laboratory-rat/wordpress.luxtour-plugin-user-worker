<?
/*
Plugin Name: plugin for users functions
Description: null
Version: 0.1
Author: Oleg A. T.
Author URI: http://luxtour.online
Plugin URI: http://luxtour.online
*/

class luxtour_user_worker
{

    public $db_name = "luxtour_agents";
    public $main_page = "http://wp-test.in/";

	function __construct()
	{
		add_action( 'load-profile.php', array($this, 'disable_user_profile') );
		add_action('admin_menu', array($this, 'plugin_menu'));

		add_action('wp_login', array($this, 'login_user'), 10, 2);

		add_filter('login_redirect', array($this,'user_redirect_after_login'));

		register_activation_hook( __FILE__, array($this, 'activate'));
		register_deactivation_hook( __FILE__, array($this, 'deactivate'));

	}

    function activate()
    {

    }

    function deactivate()
    {


    }

    // Plugin menu options

    function plugin_menu() {
        add_options_page( 'Luxtour test plugin', 'lux-1', 'manage_options', 'lux_1', array('Luxtour_test', 'options') );
    }

    function options() {

        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        echo '<div class="wrap">';
        echo '<p>Here is where the form would go if I actually had options.</p>';
        echo '</div>';
    }

    // self functions

	function user_redirect_after_login()
	{
		return "/dashboard";
	}

    function login_user($user_login, $user )
    {
		$id -> $user->ID;

		global $wpdb;
		$db = $wpdb->prefix.$this->db_name;

		$query = "update $db set last_login = now()";
		$wpdb->query($query);
    }

    function add_new_user($args)
    {
		if (!array_key_exists("email", $args) || !array_key_exists("fullname", $args)
		   || !array_key_exists("password", $args) || !array_key_exists("ip", $args))
			return "Error. Bad form.";

		if (!email_exists($args["email"]))
		{

			$user_id = wp_create_user($args["email"], $args["password"], $args["email"]);

			if(is_int($user_id))
			{
				$fullname = $args['fullname'];
				$ip = $args['ip'];
				$key = wp_generate_password(50, false, false);

				global $wpdb;
				$db = $wpdb->prefix."luxtour_agents";

				$sql = "INSERT into `".$db."` VALUES ($user_id, '$fullname', '$ip', now(), 'null', 'agent', '$key');";

				$wpdb->query($sql);

				return "success";
			}

			return $user_id->get_error_message();
		}
    }

    function disable_user_profile()
    {
        //if ( !is_admin() )
            wp_redirect( self::$main_page );

    }

	function get_key()
	{
		if (is_user_logged_in())
		{
			$id = get_current_user_id();

			global $wpdb;
			$db = $wpdb->prefix."luxtour_agents";

			$sql = "select `key` from $db where id = $id;";
			$result = $wpdb->get_var($sql);

			return $result;
		}
		return "-1";
	}

	function get_user_data()
	{
		$data = array();

		$id = get_current_user_id();

		global $wpdb;
		$db_name = "luxtour_agents";

		$db = $wpdb->prefix.$db_name;

		$query = "select fullname, tel, `key` from $db where id = $id";

		$result = $wpdb->get_row($query);

		$email = get_userdata($id)->data->user_email;

		$data['id'] = $id;
		$data['tel'] = $result->tel;
		$data['key'] = $result->key;
		$data['email'] = $email;
		$data['fullname'] = $result->fullname;

		return $data;
	}

	function change_tel($id, $tel)
	{
		global $wpdb;
		$db_name = "luxtour_agents";

		$db = $wpdb->prefix.$db_name;

		$sql = "update $db set `tel` = $tel where id = $id";
		$wpdb->query($sql);
	}
}

$luw = new luxtour_user_worker();

?>
