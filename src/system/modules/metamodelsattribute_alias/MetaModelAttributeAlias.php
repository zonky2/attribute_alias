<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage AttributeText
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This is the MetaModelAttribute class for handling text fields.
 * 
 * @package	   MetaModels
 * @subpackage AttributeText
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttributeAlias extends MetaModelAttributeSimple
{

	public function getSQLDataType()
	{
		return 'varchar(255) NOT NULL default \'\'';
	}

	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array(
			'parentCheckbox',
			'titleField',
			'width50',
			'insertBreak',
			'sortingField',
			'filteredField',
			'searchableField',
			'mandatory',
			'defValue',
			'uniqueItem',
			'formatPrePost',
			'format',
			'editGroups'
		));
	}

	public function getFieldDefinition()
	{
		$arrFieldDef = parent::getFieldDefinition();
                
		$arrFieldDef['inputType'] = 'text';
//		$arrFieldDef['save_callback'] = array(array('TableMetaModelsAttributeAlias','saveCallback'));
                             
		return $arrFieldDef;                
	}

	/**
	 * {@inheritdoc}
	 */
	public function sortIds($arrIds, $strDirection)
	{
		$objDB = Database::getInstance();
		$objSorted = $objDB->execute('SELECT id FROM ' . $this->getMetaModel()->getTableName() . ' WHERE id IN (' . implode(',', array_map('intval', $arrIds)) . ') ORDER BY ' . $this->getColName() . ' ' . $strDirection);
		return $objSorted->fetchEach('id');
	}
}

?>