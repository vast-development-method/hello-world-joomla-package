<?php
/**
 * @package    Hello.World
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Hello World <https://git.vdm.dev/joomla/Hello-World-Component>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Script File of Componentbuilder Package
 */
class pkg_helloworldInstallerScript
{
	/**
	 * Called after any type of action
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
		// only run these if we have an update
		if ('update' == $type)
		{
			// update the update server location
			$this->updateServerLocation();
		}
	}

	/**
	 * Update server location
	 *
	 * @return  void
	 */
	protected function updateServerLocation()
	{
		$location = "https://raw.githubusercontent.com/vast-development-method/hello-world-joomla-component/refs/heads/6.x/update_server.xml";
		$elements = ['pkg_helloworld', 'com_helloworld'];
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Get the Package Update Site Details
		foreach ($elements as $element)
		{
			if (($sites = $this->getUpdateSites($element, $db)) !== null)
			{
				foreach ($sites as $site)
				{
					if ($site->location !== $location)
					{
						// Update the update site location
						$site->location = $location;
						$db->updateObject('#__update_sites', $site, 'update_site_id');
					}
				}
			}
		}
	}

	/**
	 * Get Update Sites
	 *
	 * @return  array|null
	 */
	protected function getUpdateSites(string $element, DatabaseInterface $db): ?array
	{
		// Get the Package Update Site Details
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('s.location', 's.update_site_id')));
		$query->from($db->quoteName('#__update_sites', 's'));
		$query->join('LEFT', $db->quoteName('#__update_sites_extensions', 'u') . ' ON ' . $db->quoteName('s.update_site_id') . ' = ' . $db->quoteName('u.update_site_id'));
		$query->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('u.extension_id') . ' = ' . $db->quoteName('e.extension_id'));
		$query->where($db->quoteName('e.element') . ' = ' . $db->quote($element));
		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			return $db->loadObjectList();
		}
		return null;
	}
}

