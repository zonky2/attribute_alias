<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\Attribute\Alias;

use ContaoCommunityAlliance\Contao\EventDispatcher\Event\CreateEventDispatcherEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\Factory;

/**
 * Handle events for tl_metamodel_attribute.alias_fields.attr_id.
 */
class PropertyAttribute
	extends BaseSubscriber
{
	/**
	 * Register all listeners to handle creation of a data container.
	 *
	 * @param CreateEventDispatcherEvent $event The event.
	 *
	 * @return void
	 */
	public static function registerEvents(CreateEventDispatcherEvent $event)
	{
		$dispatcher = $event->getEventDispatcher();
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_attribute',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelAttributeEvents'
		);
	}

	/**
	 * Register the events for table tl_metamodel_attribute.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelAttributeEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => __CLASS__ . '::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_attribute', 'alias_fields', 'field_attribute')
		);
	}

	/**
	 * Retrieve the options for the attributes.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$model     = $event->getModel();
		$metaModel = Factory::byId($model->getProperty('pid'));

		if (!$metaModel)
		{
			return;
		}

		$result = array();

		// Fetch all attributes except for the current attribute.
		foreach ($metaModel->getAttributes() as $attribute)
		{
			if ($attribute->get('id') === $model->getId())
			{
				continue;
			}

			$result[$attribute->getColName()] = sprintf(
				'%s [%s]',
				$attribute->getName(),
				$attribute->get('type')
			);
		}

		$event->setOptions($result);
	}
}
