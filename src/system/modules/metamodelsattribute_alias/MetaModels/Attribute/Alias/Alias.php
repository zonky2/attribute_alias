<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package     MetaModels
 * @subpackage  AttributeAlias
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\Alias;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReplaceInsertTagsEvent;
use MetaModels\Attribute\BaseSimple;

/**
 * This is the MetaModelAttribute class for handling text fields.
 *
 * @package    MetaModels
 * @subpackage AttributeAlias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Alias extends BaseSimple
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDataType()
    {
        return 'varchar(255) NOT NULL default \'\'';
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(parent::getAttributeSettingNames(), array(
            'alias_fields',
            'isunique',
            'force_alias',
            'mandatory',
            'alwaysSave',
            'filterable',
            'searchable',
            'sortable'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $arrFieldDef = parent::getFieldDefinition($arrOverrides);

        $arrFieldDef['inputType'] = 'text';

        // W do not need to set mandatory, as we will automatically update our value when isunique is given.
        if ($this->get('isunique'))
        {
            $arrFieldDef['eval']['mandatory'] = false;
        }

        // If "force_alias" is ture set alwaysSave to true.
        if ($this->get('force_alias'))
        {
            $arrFieldDef['eval']['alwaysSave'] = true;
        }

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     */
    public function modelSaved($objItem)
    {
        // Alias already defined and no update forced, get out!
        if ($objItem->get($this->getColName()) && (!$this->get('force_alias')))
        {
            return;
        }

        // Item is a variant but no overriding allowed, get out!
        if ($objItem->isVariant() && (!$this->get('isvariant')))
        {
            return;
        }

        $arrAlias = '';
        foreach (deserialize($this->get('alias_fields')) as $strAttribute)
        {
            $arrValues  = $objItem->parseAttribute($strAttribute['field_attribute'], 'text', null);
            $arrAlias[] = $arrValues['text'];
        }

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher   = $GLOBALS['container']['event-dispatcher'];
        $replaceEvent = new ReplaceInsertTagsEvent(implode('-', $arrAlias));
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS, $replaceEvent);

        // Implode with '-', replace inserttags and strip HTML elements.
        $strAlias = standardize(strip_tags($replaceEvent->getBuffer()));

        // We need to fetch the attribute values for all attributes in the alias_fields and update the database and the
        // model accordingly.
        if ($this->get('isunique'))
        {
            // Ensure uniqueness.
            $strBaseAlias = $strAlias;
            $arrIds       = array($objItem->get('id'));
            $intCount     = 2;
            while (array_diff($this->searchFor($strAlias), $arrIds))
            {
                $strAlias = $strBaseAlias . '-' . ($intCount++);
            }
        }

        $this->setDataFor(array($objItem->get('id') => $strAlias));
        $objItem->set($this->getColName(), $strAlias);
    }
}
