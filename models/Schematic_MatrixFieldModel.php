<?php

namespace Craft;

/**
 * Schematic Matrix Field Model.
 *
 * A schematic field model for mapping matrix data
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Schematic_MatrixFieldModel extends Schematic_FieldModel
{
    /**
     * Returns matrix service.
     *
     * @return MatrixService
     */
    private function getMatrixService()
    {
        return craft()->matrix;
    }

    //==============================================================================================================
    //================================================  EXPORT  ====================================================
    //==============================================================================================================

    /**
     * @param FieldModel $field
     * @param $includeContext
     *
     * @return array
     */
    public function getDefinition(FieldModel $field, $includeContext)
    {
        $definition = parent::getDefinition($field, $includeContext);
        $definition['blockTypes'] = $this->getBlockTypeDefinitions($field);

        return $definition;
    }

    /**
     * Get block type definitions.
     *
     * @param FieldModel $field
     *
     * @return array
     */
    protected function getBlockTypeDefinitions(FieldModel $field)
    {
        $fieldFactory = $this->getFieldFactory();
        $blockTypeDefinitions = array();

        $blockTypes = $this->getMatrixService()->getBlockTypesByFieldId($field->id);
        foreach ($blockTypes as $blockType) {
            $blockTypeFieldDefinitions = array();

            foreach ($blockType->getFields() as $blockTypeField) {
                $schematicFieldModel = $fieldFactory->build($blockTypeField->type);
                $blockTypeFieldDefinitions[$blockTypeField->handle] = $schematicFieldModel->getDefinition($blockTypeField, false);
            }

            $blockTypeDefinitions[$blockType->handle] = array(
                'name' => $blockType->name,
                'fields' => $blockTypeFieldDefinitions,
            );
        }

        return $blockTypeDefinitions;
    }

    //==============================================================================================================
    //================================================  IMPORT  ====================================================
    //==============================================================================================================

    /**
     * @param array                $fieldDefinition
     * @param FieldModel           $field
     * @param string               $fieldHandle
     * @param FieldGroupModel|null $group
     */
    public function populate(array $fieldDefinition, FieldModel $field, $fieldHandle, FieldGroupModel $group = null)
    {
        parent::populate($fieldDefinition, $field, $fieldHandle, $group);

        /** @var MatrixSettingsModel $settingsModel */
        $settingsModel = $field->getFieldType()->getSettings();
        $settingsModel->setAttributes($fieldDefinition['settings']);
        $settingsModel->setBlockTypes($this->getBlockTypes($fieldDefinition, $field));
        $field->settings = $settingsModel;
    }

    /**
     * Get blocktypes.
     *
     * @param array      $fieldDefinition
     * @param FieldModel $field
     *
     * @return mixed
     */
    protected function getBlockTypes(array $fieldDefinition, FieldModel $field)
    {
        $blockTypes = $this->getMatrixService()->getBlockTypesByFieldId($field->id, 'handle');

        foreach ($fieldDefinition['blockTypes'] as $blockTypeHandle => $blockTypeDef) {
            $blockType = array_key_exists($blockTypeHandle, $blockTypes)
                ? $blockTypes[$blockTypeHandle]
                : new MatrixBlockTypeModel();

            $blockType->fieldId = $field->id;
            $blockType->name = $blockTypeDef['name'];
            $blockType->handle = $blockTypeHandle;

            $this->populateBlockType($blockType, $blockTypeDef);

            $blockTypes[$blockTypeHandle] = $blockType;
        }

        return $blockTypes;
    }

    /**
     * Populate blocktype.
     *
     * @param BaseModel $blockType
     * @param array     $blockTypeDef
     */
    protected function populateBlockType(BaseModel $blockType, array $blockTypeDef)
    {
        $fieldFactory = $this->getFieldFactory();

        $blockTypeFields = array();
        foreach ($blockType->getFields() as $blockTypeField) {
            $blockTypeFields[$blockTypeField->handle] = $blockTypeField;
        }

        $newBlockTypeFields = array();

        foreach ($blockTypeDef['fields'] as $blockTypeFieldHandle => $blockTypeFieldDef) {
            $blockTypeField = array_key_exists($blockTypeFieldHandle, $blockTypeFields)
                ? $blockTypeFields[$blockTypeFieldHandle]
                : new FieldModel();

            $schematicFieldModel = $fieldFactory->build($blockTypeFieldDef['type']);
            $schematicFieldModel->populate($blockTypeFieldDef, $blockTypeField, $blockTypeFieldHandle);

            $newBlockTypeFields[] = $blockTypeField;
        }

        $blockType->setFields($newBlockTypeFields);
    }
}
