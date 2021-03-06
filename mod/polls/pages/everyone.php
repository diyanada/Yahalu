<?php

	/**
	 * Elgg Poll plugin
	 * @package Elggpoll
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @Original author John Mellberg
	 * website http://www.syslogicinc.com
	 * @Modified By Team Webgalli to work with ElggV1.5
	 * www.webgalli.com or www.m4medicine.com
	 */

	
// Get the current page's owner
		$page_owner = page_owner_entity();
		if ($page_owner === false || is_null($page_owner)) {
			$page_owner = $_SESSION['user'];
			set_page_owner($_SESSION['guid']);
		}
		
		$offset = get_input('offset');
		
		if (empty($offset)) {
			$offset = 0;
		}
		
		$limit = 10;
			
		$title = elgg_echo('polls:everyone');
		$polls = elgg_get_entities(array('types' => 'object', 'subtypes' => 'poll' ,'limit' => $limit ,'offset' => $offset));
		$count = elgg_get_entities(array('types' => 'object', 'subtypes' => 'poll' , 'limit' => 999, 'count' => true));
		
		elgg_push_context('search');
		$area2 = elgg_view_entity_list($polls,$count,$offset,$limit,false,false,true);
		elgg_pop_context();

		elgg_push_context('polls');
		$body = elgg_view_layout('content', array(
		    'filter_context' => 'all',
		    'content' => $area2,
		    'title' => $title,
		));
		elgg_pop_context();

		echo elgg_view_page($title, $body);


// $params = array(
// 		'content' => $area2,
//                 'sidebar' => '',
// 		
// 	);
// 	  
// $body = elgg_view_layout('two_sidebar', $params);
// 		
// 		//$body = elgg_view_layout("two_column_left_sidebar", '', $area1 . $area2);
// 		
// 	// Display page
// 		page_draw(elgg_echo('polls:everyone'),$body);
		
?>