<?php
/**
 * @package    CMPayment
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

JLoader::import('joomla.plugin.plugin');

/**
 * Abstract class for CM Donation's payment plugin.
 * Based on Akeeba Subscription's plgAkpaymentAbstract class (akeebabackup.com).
 *
 * @since  1.0.0
 */
abstract class PlgCMPaymentAbstract extends JPlugin
{
	/**
	 * @var string Name of the plugin, used as an ID, returned to the component.
	 *
	 * @since   1.0.0
	 */
	protected $name = 'abstract';

	/**
	 * @var string Translation key of the plugin's title, returned to the component.
	 *
	 * @since   1.0.0
	 */
	protected $key = 'PLG_CMPAYMENT_ABSTRACT_TITLE';

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$subject, $config = array())
	{
		if (!is_object($config['params']))
		{
			JLoader::import('joomla.registry.registry');
			$config['params'] = new JRegistry($config['params']);
		}

		parent::__construct($subject, $config);

		if (array_key_exists('name', $config))
		{
			$this->name = $config['name'];
		}

		$name = $this->name;

		if (array_key_exists('key', $config))
		{
			$this->key = $config['key'];
		}
		else
		{
			$this->key = "PLG_CMPAYMENT_{$name}_TITLE";
		}
	}

	/**
	 * Plugin event which returns the identity information of this payment
	 * method. The result is an array containing one or more associative arrays.
	 * If the plugin only provides a single payment method you should only
	 * return an array containing just one associateive array. The assoc array
	 * has the keys 'name' (the name of the payment method), 'title'
	 * (translation key for the payment method's name).
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public final function onCMPaymentGetIdentity()
	{
		$title = $this->params->get('title', '');

		if (empty($title))
		{
			$title = JText::_($this->key);
		}

		$return = (object) array(
			'name'	=> $this->name,
			'title'	=> $title,
		);

		return $return;
	}

	/**
	 * Returns the payment form to be submitted by the user's browser.
	 *
	 * @param   string  $paymentMethod  The currently used payment method. Check it against $this->name.
	 * @param   JUser   $user           Current user.
	 * @param   object  $data           Object that contains the data of purchased items.
	 *
	 * @return  string  The payment form to render on the page.
	 *
	 * @since   1.0.0
	 */
	abstract public function onCMPaymentNew($paymentMethod, $user, $data);

	/**
	 * Processes a callback from the payment processor.
	 *
	 * @param   string  $paymentMethod  The currently used payment method. Check it against $this->name
	 *
	 * @return  boolean  True if the callback was handled, false otherwise
	 *
	 * @since   1.0.0
	 */
	abstract public function onCMPaymentCallback($paymentMethod);

	/**
	 * Logs the received IPN information to file
	 *
	 * @param   array    $data     Request data.
	 * @param   boolean  $isValid  Is it a valid payment?
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function logIPN($data, $isValid)
	{
		$config = JFactory::getConfig();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$logPath = $config->get('log_path');
		}
		else
		{
			$logPath = $config->getValue('log_path');
		}

		$logFilenameBase = $logPath . '/cmpayment_' . strtolower($this->name) . '_ipn';

		$logFile = $logFilenameBase . '.php';
		JLoader::import('joomla.filesystem.file');

		if (!JFile::exists($logFile))
		{
			$dummy = "<?php die(); ?>\n";
			JFile::write($logFile, $dummy);
		}
		else
		{
			if (@filesize($logFile) > 1048756)
			{
				$altLog = $logFilenameBase . '-1.php';

				if (JFile::exists($altLog))
				{
					JFile::delete($altLog);
				}

				JFile::copy($logFile, $altLog);
				JFile::delete($logFile);
				$dummy = "<?php die(); ?>\n";
				JFile::write($logFile, $dummy);
			}
		}

		$logData = JFile::read($logFile);

		if ($logData === false)
		{
			$logData = '';
		}

		$logData .= "\n" . str_repeat('-', 80);
		$pluginName = strtoupper($this->name);
		$logData .= $isValid ? 'VALID ' . $pluginName . ' IPN' : 'INVALID ' . $pluginName . ' IPN *** FRAUD ATTEMPT OR INVALID NOTIFICATION ***';
		$logData .= "\nDate/time : " . gmdate('Y-m-d H:i:s') . " GMT\n\n";

		foreach ($data as $key => $value)
		{
			$logData .= '  ' . str_pad($key, 30, ' ') . $value . "\n";
		}

		$logData .= "\n";
		JFile::write($logFile, $logData);
	}

	/**
	 * Write debug file.
	 *
	 * @param   string  $string  Text to write.
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	protected function debug($string)
	{
		$handle = fopen(JPATH_ROOT . '/log.txt', 'a+');
		fwrite($handle, date('Y-m-d H:i:s') . ' --- ' . $string . PHP_EOL);
		fclose($handle);
	}
}
