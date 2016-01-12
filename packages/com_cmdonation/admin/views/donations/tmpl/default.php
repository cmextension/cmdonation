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
$countryDisplay	= $this->params->get('donor_list_country');
$dateFormat		= $this->params->get('date_format'. 'COM_CMDONATION_DATE_FORMAT_1');
$numOfColumns	= 11;
$paymentMethods	= $this->paymentMethods;
?>
<div class="cmdonation">
	<?php echo $this->submenu; ?>

	<div class="content-container">
		<form class="pure-form" action="<?php echo JRoute::_('index.php?option=com_cmdonation&view=donations'); ?>" method="post" name="adminForm" id="adminForm">
			<div class="cm-toolbar pure-clearfix">
				<div class="pure-float-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_CMDONATION_SEARCH', true); ?>" />
					<button type="submit" class="pure-button pure-button-primary" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT', true); ?>"><i class="fa fa-search"></i> <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
					<button type="button" class="pure-button pure-button-primary" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR', true); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="fa fa-eraser"></i> <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
				</div>

				<div class="pure-float-right pure-hidden-phone">
					<?php echo $this->pagination->getLimitBox(); ?>

					<select name="filter_status" id="filter_status" onchange="this.form.submit();">
						<option value=""><?php echo JText::_('COM_CMDONATION_SELECT_STATUS_OPTION');?></option>
						<?php echo JHtml::_(
							'select.options',
							array(
								JHtml::_('select.option', 'INCOMPLETE', JText::_('COM_CMDONATION_DONATION_STATUS_INCOMPLETE')),
								JHtml::_('select.option', 'COMPLETED', JText::_('COM_CMDONATION_DONATION_STATUS_COMPLETED')),
								JHtml::_('select.option', 'REFUNDED', JText::_('COM_CMDONATION_DONATION_STATUS_REFUNDED'))
							),
							'value', 'text', $this->state->get('filter.status'), true
						); ?>
					</select>

					<select name="filter_campaign" id="filter_campaign" onchange="this.form.submit();">
						<option value=""><?php echo JText::_('COM_CMDONATION_SELECT_CAMPAIGN_OPTION');?></option>
						<?php echo JHtml::_('select.options', $this->campaignOptions, 'value', 'text', $this->state->get('filter.campaign'), true); ?>
					</select>

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

			<table class="pure-table pure-table-horizontal" id="donationList">
				<thead>
					<tr>
						<th width="1%" class="nowrap pure-text-center hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL', true); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th width="1%" class="nowrap pure-text-center">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap pure-text-center">
							<?php echo JText::_('COM_CMDONATION_DONATION_CAMPAIGN_LABEL'); ?>
						</th>
						<th class="nowrap pure-text-center">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_AMOUNT_LABEL', 'a.amount', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap pure-text-center">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_STATUS_LABEL', 'a.status', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_FIRST_NAME_LABEL', 'a.first_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_LAST_NAME_LABEL', 'a.last_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_EMAIL_LABEL', 'a.email', $listDirn, $listOrder); ?>
						</th>
						<?php if ($countryDisplay != 'hide'): ?>
						<?php $numOfColumns++; ?>
						<th class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_COUNTRY_LABEL', 'a.country_code', $listDirn, $listOrder); ?>
						</th>
						<?php endif; ?>
						<?php if ($countryDisplay == 'both'): ?>
						<?php $numOfColumns++; ?>
						<th class="nowrap pure-text-center pure-hidden-phone"></th>
						<?php endif; ?>
						<th class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_ANONYMOUS_LABEL', 'a.anonymous', $listDirn, $listOrder); ?>
						</th class="nowrap pure-text-center pure-hidden-phone">
						<th class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_PAYMENT_METHOD_LABEL', 'a.payment_method_id', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_CMDONATION_DONATION_CREATED_LABEL', 'a.created', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="<?php echo $numOfColumns; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering		= ($listOrder == 'a.ordering');
					$canCreate		= $user->authorise('core.create', 'com_cmdonation');
					$canEdit		= $user->authorise('core.edit', 'com_cmdonation');
					$canCheckin		= $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
					$canChange		= $user->authorise('core.edit.state', 'com_cmdonation') && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="nowrap pure-text-center">
							<?php if ($item->checked_out): ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, null, $item->checked_out_time, 'donations.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit): ?>
								<a href="<?php echo JRoute::_('index.php?option=com_cmdonation&task=donation.edit&id=' . (int) $item->id); ?>">
									<?php echo (int) $item->id; ?></a>
							<?php else : ?>
									<?php echo (int) $item->id; ?>
							<?php endif; ?>
						</td>
						<td class="nowrap pure-text-center">
							<?php
							if (isset($this->campaignIndex[$item->campaign_id]))
								echo $this->campaignIndex[$item->campaign_id];
							else
								echo '<span class="text-error">' . JText::_('COM_CMDONATION_CAMPAIGN_NOT_FOUND') . '</span>';
							?>
						</td>
						<td class="nowrap pure-text-center">
							<?php echo $item->amount; ?>
						</td>
						<td class="nowrap pure-text-center">
							<?php
							switch ($item->status)
							{
								case 'INCOMPLETE':
									echo '<span class="text-warning">' . JText::_('COM_CMDONATION_DONATION_STATUS_INCOMPLETE') . '</span>';
									break;
								case 'COMPLETED':
									echo '<span class="text-success">' . JText::_('COM_CMDONATION_DONATION_STATUS_COMPLETED') . '</span>';
									break;
								case 'REFUNDED':
									echo '<span class="text-error">' . JText::_('COM_CMDONATION_DONATION_STATUS_REFUNDED') . '</span>';
									break;
							}
							?>
						</td>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo $item->first_name; ?>
						</td>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo $item->last_name; ?>
						</td>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo $item->email; ?>
						</td>
						<?php if ($countryDisplay == 'flag'): ?>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo CMDonationHelper::showCountryFlag($item->country_code); ?>
						</td>
						<?php elseif ($countryDisplay == 'name' || $countryDisplay == 'both'): ?>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo CMDonationHelper::showCountryName($item->country_code); ?>
						</td>
						<?php endif; ?>
						<?php if ($countryDisplay == 'both'): ?>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo CMDonationHelper::showCountryFlag($item->country_code); ?>
						</td>
						<?php endif; ?>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php
							if ($item->anonymous)
								echo '<i class="fa fa-check"></i>';
							?>
						</td>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo CMDonationHelper::displayPaymentMethodName($item->payment_method_id, $paymentMethods); ?>
						</td>
						<td class="nowrap pure-text-center pure-hidden-phone">
							<?php echo JHtml::_('date', $item->created, $dateFormat); ?>
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
