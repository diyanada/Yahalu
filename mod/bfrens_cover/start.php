<?php
/**
 * Elgg profile plugin
 *
 * @package ElggProfile
 */

elgg_register_event_handler('init', 'system', 'bfrens_cover_init', 1);

// Metadata on users needs to be independent
// outside of init so it happens earlier in boot. See #3316
register_metadata_as_independent('user');

/**
 * Profile init function
 */
function bfrens_cover_init() {
	
$action_base = elgg_get_plugins_path() . 'bfrens_cover';

	// Register a URL handler for users - this means that profile_url()
	// will dictate the URL for all ElggUser objects
	elgg_register_entity_url_handler('user', 'all', 'profile_url');

	elgg_register_plugin_hook_handler('entity:icon:url', 'user', 'profile_override_avatar_url');
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', 'bfrens_cover_add_adding_button');
	elgg_unregister_plugin_hook_handler('entity:icon:url', 'user', 'user_avatar_hook');


	elgg_register_simplecache_view('icon/user/default/tiny');
	elgg_register_simplecache_view('icon/user/default/topbar');
	elgg_register_simplecache_view('icon/user/default/small');
	elgg_register_simplecache_view('icon/user/default/medium');
	elgg_register_simplecache_view('icon/user/default/large');
	elgg_register_simplecache_view('icon/user/default/master');

	elgg_register_page_handler('profile', 'profile_page_handler');
	elgg_register_page_handler('cover', 'cover_page_handler');

	elgg_extend_view('page/elements/head', 'profile/metatags');
	elgg_extend_view('css/elgg', 'profile/css');
	elgg_extend_view('css/elgg', 'css/elements/icons2');
	elgg_extend_view('_graphics', '_graphics');
	elgg_extend_view('js/elgg', 'profile/js');
	

	

	// allow ECML in parts of the profile
	elgg_register_plugin_hook_handler('get_views', 'ecml', 'profile_ecml_views_hook');

	// allow admins to set default widgets for users on profiles
	elgg_register_plugin_hook_handler('get_list', 'default_widgets', 'profile_default_widgets_hook');
	
	elgg_register_action("cover/upload", "$action_base/actions/cover/upload.php", 'logged_in');
	elgg_register_action("cover/crop", "$action_base/actions/cover/crop.php", 'logged_in');
	

	
		
	$url="/mod/bfrens_cover/js/lib/ui.avatar_cropper_cover.js";

	elgg_register_js('elgg.avatar_cropper_cover', $url);	
	
}

/**
 * Profile page handler
 *
 * @param array $page Array of URL segments passed by the page handling mechanism
 * @return bool
 */
function profile_page_handler($page) {

	if (isset($page[0])) {
		$username = $page[0];
		$user = get_user_by_username($username);
		elgg_set_page_owner_guid($user->guid);
	} elseif (elgg_is_logged_in()) {
		forward(elgg_get_logged_in_user_entity()->getURL());
	}

	// short circuit if invalid or banned username
	if (!$user || ($user->isBanned() && !elgg_is_admin_logged_in())) {
		register_error(elgg_echo('profile:notfound'));
		forward();
	}

	$action = NULL;
	if (isset($page[1])) {
		$action = $page[1];
	}

	if ($action == 'edit') {
		// use the core profile edit page
		$base_dir = elgg_get_root_path();
		require "{$base_dir}pages/profile/edit.php";
		return true;
	}


	// main profile page
	$params = array(
		'content' => elgg_view('profile/wrapper'),
		'num_columns' => 3,
		'show_add_widgets' => false,
		'show_access' => false,
		'exact_match' => true,
	);
	$content = elgg_view_layout('widgets', $params);

	$body = elgg_view_layout('one_column', array('content' => $content));
	
	$content=elgg_view('profile/owner_cover');
	
	$body2 = elgg_view_layout('one_column', array('content' => $content));
	
	$body=$body2.$body;

	echo elgg_view_page($user->name, $body);
		
	return true;
}

/**
 * Profile URL generator for $user->getUrl();
 *
 * @param ElggUser $user
 * @return string User URL
 */
function profile_url($user) {
	return elgg_get_site_url() . "profile/" . $user->username;
}

/**
 * Use a URL for avatars that avoids loading Elgg engine for better performance
 *
 * @param string $hook
 * @param string $entity_type
 * @param string $return_value
 * @param array  $params
 * @return string
 */
function profile_override_avatar_url($hook, $entity_type, $return_value, $params) {

	// if someone already set this, quit
	if ($return_value) {
		return null;
	}

	$user = $params['entity'];
	$size = $params['size'];
	
	if (!elgg_instanceof($user, 'user')) {
		return null;
	}

	$user_guid = $user->getGUID();
	$icon_time = $user->icontime;

	if (!$icon_time) {
		return "_graphics/icons/user/default{$size}.gif";
	}

	if ($user->isBanned()) {
		return null;
	}

	$filehandler = new ElggFile();
	$filehandler->owner_guid = $user_guid;
	$filehandler->setFilename("profile/{$user_guid}{$size}.jpg");

	try {
		if ($filehandler->exists()) {
			$join_date = $user->getTimeCreated();
			return "mod/bfrens_cover/icondirect.php?lastcache=$icon_time&joindate=$join_date&guid=$user_guid&size=$size";
		}
	} catch (InvalidParameterException $e) {
		elgg_log("Unable to get profile icon for user with GUID $user_guid", 'ERROR');
		return "_graphics/icons/default/$size.png";
	}

	return null;
}

/**
 * Parse ECML on parts of the profile
 *
 * @param string $hook
 * @param string $entity_type
 * @param array  $return_value
 * @return array
 */
function profile_ecml_views_hook($hook, $entity_type, $return_value) {
	$return_value['profile/profile_content'] = elgg_echo('profile');

	return $return_value;
}

/**
 * Register profile widgets with default widgets
 *
 * @param string $hook
 * @param string $type
 * @param array  $return
 * @return array
 */
function profile_default_widgets_hook($hook, $type, $return) {
	$return[] = array(
		'name' => elgg_echo('profile'),
		'widget_context' => 'profile',
		'widget_columns' => 3,

		'event' => 'create',
		'entity_type' => 'user',
		'entity_subtype' => ELGG_ENTITIES_ANY_VALUE,
	);

	return $return;
}

function bfrens_cover_add_adding_button($hook, $type, $return, $params) {
    $user = $params['entity'];

    if (elgg_get_logged_in_user_guid() == $user->guid) {
        $url = '/cover/edit/'.$user->username;
        $item = new ElggMenuItem('button_name', elgg_echo("profile:bfrens:cover:edit"), $url);
        $item->setSection('action');
        $return[] = $item;
    }

    return $return;
}

function cover_page_handler($page) {

$action_base = elgg_get_plugins_path() . 'bfrens_cover';


		
		require "{$action_base}/pages/cover/editcover.php";
		return true;


	
}