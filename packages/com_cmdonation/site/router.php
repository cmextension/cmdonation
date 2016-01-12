<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

if (!class_exists('CMComponentRouterBase'))
{
	if (class_exists('JComponentRouterBase'))
	{
		/**
		 * The new way.
		 *
		 * @since  1.1.0
		 */
		abstract class CMComponentRouterBase extends JComponentRouterBase {}
	}
	else
	{
		/**
		 * The old way.
		 *
		 * @since  1.1.0
		 */
		class CMComponentRouterBase {}
	}
}

/**
 * Routing class from com_cmdonation.
 *
 * @since  1.1.0
 */
class CMDonationRouter extends CMComponentRouterBase
{
	/**
	 * Build the route for the com_cmdonation component.
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.1.0
	 */
	public function build(&$query)
	{
		static $menu;
		$segments = array();

		// Load the menu if necessary.
		if (!$menu)
		{
			$menu = JFactory::getApplication('site')->getMenu();
		}

		// If Itemid doesn't exist in the query we check to see if we have already had a menu item for this view.
		// If there is a menu item available, we append its ID to the query.
		if (!isset($query['Itemid']) && isset($query['view']))
		{
			$view = $query['view'];
			$item = $menu->getItems('link', 'index.php?option=com_cmdonation&view=' . $view, true);

			if (!empty($item))
			{
				$query['Itemid'] = $item->id;
			}
		}

		/*
		 * First, handle menu item routes first. When the menu system builds a
		 * route, it only provides the option and the menu item id. We don't have
		 * to do anything to these routes.
		 */
		if (count($query) === 2 && isset($query['Itemid']) && isset($query['option']))
		{
			return $segments;
		}

		/*
		 * Next, handle a route with a supplied menu item id. All system generated
		 * routes should fall into this group. We can assume that the menu item id
		 * is the best possible match for the query but we need to go through and
		 * see which variables we can eliminate from the route query string because
		 * they are present in the menu item route already.
		 */
		if (!empty($query['Itemid']))
		{
			// Get the menu item.
			if (empty($item))
				$item = $menu->getItem($query['Itemid']);

			// Check if the view matches.
			if ($item && @$item->query['view'] === @$query['view'])
			{
				unset($query['view']);
			}

			// Check if we have additional params for layout and donation ID.
			if (isset($query['layout']) && isset($query['donation']))
			{
				// Add the params to the segments.
				$segments[] = $query['layout'];
				$segments[] = $query['donation'];
				unset($query['layout']);
				unset($query['donation']);
			}

			return $segments;
		}

		/*
		 * Lastly, handle a route with no menu item id. Fortunately, we only need
		 * to deal with the view as the other route variables are supposed to stay
		 * in the query string.
		 */
		if (isset($query['view']))
		{
			// Add the view to the segments.
			$segments[] = $query['view'];
			unset($query['view']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   1.1.0
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// There is only view segment.
		if (count($segments) == 1)
		{
			// Check if the view segment exists in the component.
			if (@$segments[0] === 'thankyou')
			{
				$vars['view'] = $segments[0];
			}
		}

		// There are layout and donation ID segments.
		if (count($segments) == 2)
		{
			if (@$segments[0] === 'complete' || @$segments[0] === 'cancel')
			{
				$vars['view'] = 'thankyou';
				$vars['layout'] = $segments[0];
			}

			$vars['donation'] = $segments[1];
		}

		return $vars;
	}
}

/**
 * Method to build a SEF route for CM Donation component.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @since  1.0.0
 */
function CMDonationBuildRoute(&$query)
{
	$router = new CMDonationRouter;

	return $router->build($query);
}

/**
 * Method to parse a SEF route for CM Donation component.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   1.0.0
 */
function CMDonationParseRoute($segments)
{
	$router = new CMDonationRouter;

	return $router->parse($segments);
}
