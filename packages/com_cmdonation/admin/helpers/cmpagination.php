<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

if (version_compare(JVERSION, '3.8.0', 'ge'))
{
	require_once JPATH_LIBRARIES . '/src/Pagination/Pagination.php';
}
elseif (version_compare(JVERSION, '3.8.0', 'lt') && version_compare(JVERSION, '3.0.0', 'ge'))
{
	require_once JPATH_LIBRARIES . '/cms/pagination/pagination.php';
}
else
{
	require_once JPATH_LIBRARIES . '/joomla/html/pagination.php';
}

/**
 * Override JPagination for PureCSS style.
 *
 * @since  1.0.0
 */
class CMPagination extends JPagination
{
	/**
	 * Override labels with Font Awesome icons.
	 *
	 * @return  object  Pagination data object.
	 *
	 * @since   1.0.0
	 */
	protected function _buildDataObject()
	{
		// Initialise variables.
		$data = new stdClass;

		// Build the additional URL parameters string.
		$params = '';

		if (!empty($this->_additionalUrlParams))
		{
			foreach ($this->_additionalUrlParams as $key => $value)
			{
				$params .= '&' . $key . '=' . $value;
			}
		}

		$data->all = new JPaginationObject(JText::_('JLIB_HTML_VIEW_ALL'), $this->prefix);

		if ((version_compare(JVERSION, '3.0.0', 'lt') && !$this->_viewall)
			|| (version_compare(JVERSION, '3.0.0', 'ge') && !$this->viewall))
		{
			$data->all->base = '0';
			$data->all->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=');
		}

		// Set the start and previous data objects.
		$data->start = new JPaginationObject('<i class="fa fa-angle-double-left"></i>', $this->prefix);
		$data->previous = new JPaginationObject('<i class="fa fa-angle-left"></i>', $this->prefix);

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') - 2) * $this->limit;

			// Set the empty for removal from route
			// $page = $page == 0 ? '' : $page;

			$data->start->base = '0';
			$data->start->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=0');
			$data->previous->base = $page;
			$data->previous->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $page);
		}

		// Set the next and end data objects.
		$data->next = new JPaginationObject('<i class="fa fa-angle-right"></i>', $this->prefix);
		$data->end = new JPaginationObject('<i class="fa fa-angle-double-right"></i>', $this->prefix);

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$next = $this->get('pages.current') * $this->limit;
			$end = ($this->get('pages.total') - 1) * $this->limit;

			$data->next->base = $next;
			$data->next->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $next);
			$data->end->base = $end;
			$data->end->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $end);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');

		for ($i = $this->get('pages.start'); $i <= $stop; $i++)
		{
			$offset = ($i - 1) * $this->limit;

			// Set the empty for removal from route
			// $offset = $offset == 0 ? '' : $offset;

			$data->pages[$i] = new JPaginationObject($i, $this->prefix);

			if ((version_compare(JVERSION, '3.0.0', 'lt') && ($i != $this->get('pages.current') || $this->_viewall))
				|| (version_compare(JVERSION, '3.0.0', 'ge') && ($i != $this->get('pages.current') || $this->viewall)))
			{
				$data->pages[$i]->base = $offset;
				$data->pages[$i]->link = JRoute::_($params . '&' . $this->prefix . 'limitstart=' . $offset);
			}
		}

		return $data;
	}

	/**
	 * Override to not use template's pagination style.
	 *
	 * @return  string  Pagination page list string.
	 *
	 * @since   1.0.0
	 */
	public function getPagesLinks()
	{
		$app = JFactory::getApplication();

		// Build the page navigation list.
		$data = $this->_buildDataObject();

		$list = array();
		$list['prefix'] = $this->prefix;

		if ($data->start->base !== null)
		{
			$list['start']['active'] = true;
			$list['start']['data'] = $this->item_active($data->start);
		}
		else
		{
			$list['start']['active'] = false;
			$list['start']['data'] = $this->item_inactive($data->start);
		}

		if ($data->previous->base !== null)
		{
			$list['previous']['active'] = true;
			$list['previous']['data'] = $this->item_active($data->previous);
		}
		else
		{
			$list['previous']['active'] = false;
			$list['previous']['data'] = $this->item_inactive($data->previous);
		}

		$list['pages'] = array();

		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null)
			{
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = $this->item_active($page);
			}
			else
			{
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = $this->item_inactive($page);
			}
		}

		if ($data->next->base !== null)
		{
			$list['next']['active'] = true;
			$list['next']['data'] = $this->item_active($data->next);
		}
		else
		{
			$list['next']['active'] = false;
			$list['next']['data'] = $this->item_inactive($data->next);
		}

		if ($data->end->base !== null)
		{
			$list['end']['active'] = true;
			$list['end']['data'] = $this->item_active($data->end);
		}
		else
		{
			$list['end']['active'] = false;
			$list['end']['data'] = $this->item_inactive($data->end);
		}

		if ($this->total > $this->limit)
		{
			return $this->_list_render($list);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Override to not use template's pagination style.
	 *
	 * @return  string   Pagination footer.
	 *
	 * @since   1.0.0
	 */
	public function getListFooter()
	{
		$app = JFactory::getApplication();

		$list = array();
		$list['prefix'] = $this->prefix;
		$list['limit'] = $this->limit;
		$list['limitstart'] = $this->limitstart;
		$list['total'] = $this->total;
		$list['limitfield'] = $this->getLimitBox();
		$list['pagescounter'] = $this->getPagesCounter();
		$list['pageslinks'] = $this->getPagesLinks();

		return $this->_list_footer($list);
	}

	/**
	 * Override to remove limit box.
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list footer
	 *
	 * @since   1.0.0
	 */
	protected function _list_footer($list)
	{
		$html = "<div class=\"list-footer\">\n";

		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"pagination-counter\">" . $list['pagescounter'] . "</div>";

		$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"" . $list['limitstart'] . "\" />";
		$html .= "\n</div>";

		return $html;
	}

	/**
	 * Override to add PureCSS's classes.
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list start, previous, next,end
	 *
	 * @since   1.0.0
	 */
	protected function _list_render($list)
	{
		// Reverse output rendering for right-to-left display.
		$html = '<ul class="pure-paginator">';
		$html .= $list['start']['data'];
		$html .= $list['previous']['data'];

		foreach ($list['pages'] as $page)
		{
			$html .= $page['data'];
		}

		$html .= $list['next']['data'];
		$html .= $list['end']['data'];
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Method to create an inactive pagination string
	 *
	 * @param   JPaginationObject  $item  The item to be processed
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	protected function item_inactive(JPaginationObject $item)
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return '<li class="pure-button pure-button-disabled"><span>' . $item->text . '</span>';
		}
		else
		{
			return "<span class=\"pagenav\">" . $item->text . "</span>";
		}
	}

	/**
	 * Method to create an active pagination link to the item
	 *
	 * @param   JPaginationObject  $item  The object with which to make an active link.
	 *
	 * @return   string  HTML link
	 *
	 * @since    1.0.0
	 */
	protected function item_active(JPaginationObject $item)
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			$data = '<li class="pure-button" ';

			if ($item->base > 0)
			{
				$data .= "onclick=\"document.adminForm." . $this->prefix . "limitstart.value=" . $item->base
					. "; Joomla.submitform();return false;\">" . $item->text;
			}
			else
			{
				$data .= "onclick=\"document.adminForm." . $this->prefix
					. "limitstart.value=0; Joomla.submitform();return false;\">" . $item->text;
			}

			$data .= '</li>';

			return $data;
		}
		else
		{
			return "<a title=\"" . $item->text . "\" href=\"" . $item->link . "\" class=\"pagenav\">" . $item->text . "</a>";
		}
	}
}
