<?php
/**
 * Elgg pageshell
 * The standard HTML page shell that everything else fits into
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['title']       The page title
 * @uses $vars['body']        The main content of the page
 * @uses $vars['sysmessages'] A 2d array of various message registers, passed from system_messages()
 */

// backward compatability support for plugins that are not using the new approach
// of routing through admin. See reportedcontent plugin for a simple example.
if (elgg_get_context() == 'admin') {
	if (get_input('handler') != 'admin') {
		elgg_deprecated_notice("admin plugins should route through 'admin'.", 1.8);
	}
	elgg_admin_add_plugin_settings_menu();
	elgg_unregister_css('elgg');
	echo elgg_view('page/admin', $vars);
	return true;
}

// render content before head so that JavaScript and CSS can be loaded. See #4032

$messages = elgg_view('page/elements/messages', array('object' => $vars['sysmessages']));

$body = elgg_view('page/elements/body', $vars);
$up_index = elgg_view('page/elements/up_index', $vars);
$dw_index = elgg_view('page/elements/down', $vars);
$logo= elgg_view('page/elements/logo_width', $vars);
$rights= elgg_view('page/elements/rights', $vars);



// Set the content type
header("Content-type: text/html; charset=UTF-8");

$lang = get_current_language();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang; ?>" lang="<?php echo $lang; ?>">
<head>
<?php echo elgg_view('page/elements/head', $vars); ?>
</head>
<body>
<div class="elgg-page elgg-page-font">

<div class="elgg-page-site">

	<div class="elgg-page-messages">
		<?php echo $messages; ?>
	</div>

	<div class="elgg-page-logo">
		<div class="elgg-inner">
			<?php echo $logo; ?>
		</div>
	</div>
		
	<div class="elgg-page-body-logo">
		<div class="elgg-inner">
			<?php echo $body; ?>
		</div>
	</div>
	
	<div class="elgg-page-upindex">
		
			<?php echo $up_index; ?>
		
	</div>
	
	<div class="elgg-page-dwindex">
		<div class="elgg-inner">
			<?php echo $dw_index; ?>
		</div>
	</div>
	<div class="elgg-page-rights-site">
		<div class="elgg-inner">
			<?php echo $rights; ?>
		</div>
	</div>
</div>
</div>
<?php echo elgg_view('page/elements/foot'); ?>
</body>
</html>