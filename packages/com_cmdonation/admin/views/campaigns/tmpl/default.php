<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');

$user			= JFactory::getUser();
$listOrder		= $this->escape($this->state->get('list.ordering'));
$listDirn		= $this->escape($this->state->get('list.direction'));
$canOrder		= $user->authorise('core.edit.state', 'com_cmdonation');
$sortFields		= $this->getSortFields();
?>
<div class="cmdonation">
	<?php echo $this->submenu; ?>

	<div class="content-container">
		<form class="pure-form" action="<?php echo JRoute::_('index.php?option=com_cmdonation&view=campaigns'); ?>" method="post" name="adminForm" id="adminForm">
			<div class="cm-toolbar pure-clearfix">
				<div class="pure-float-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CMDONATION_SEARCH', true); ?>" />
					<button type="submit" class="pure-button pure-button-primary" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT', true); ?>"><i class="fa fa-search"></i> <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					<button type="button" class="pure-button pure-button-primary" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR', true); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="fa fa-eraser"></i> <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>

				<div class="pure-float-right pure-hidden-phone">
					<?php echo $this->pagination->getLimitBox(); ?>

					<select name="directionTable" id="directionTable" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_CMDONATION_ORDER_ASCENDING');?></option>
						<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('COM_CMDONATION_ORDER_DESCENDING');?></option>
					</select>

					<select name="sortTable" id="sortTable" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('COM_CMDONATION_SORT_BY');?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
					</select>
				</div>
			</div>

			<div class="pure-clearfix"></div>

			<table class="pure-table pure-table-horizontal" id="campaignList">
				<thead>
					<tr>
						<th width="1%" class="nowrap pure-text-center">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL', true); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap pure-text-center">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="3">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canCreate	= $user->authorise('core.create', 'com_cmdonation');
					$canEdit	= $user->authorise('core.edit', 'com_cmdonation');
					$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
					$canChange	= $user->authorise('core.edit.state', 'com_cmdonation') && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="pure-text-center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="nowrap has-context">
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, null, $item->checked_out_time, 'campaigns.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_cmdonation&task=campaign.edit&id=' . (int) $item->id); ?>">
									<?php echo $this->escape($item->name); ?></a>
							<?php else : ?>
									<?php echo $this->escape($item->name); ?>
							<?php endif; ?>
						</td>
						<td class="pure-text-center">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
</div>
