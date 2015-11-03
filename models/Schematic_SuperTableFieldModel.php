<?php

namespace Craft;

/**
 * Class Schematic_SuperTableFieldModel
 */
class Schematic_SuperTableFieldModel extends Schematic_MatrixFieldModel
{
    /**
     * @return SuperTableService
     */
    private function getSuperTableService()
    {
        return craft()->superTable;
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

        /** @var SuperTable_BlockTypeModel[] $blockTypes */
        $blockTypes = $this->getSuperTableService()->getBlockTypesByFieldId($field->id);
        foreach ($blockTypes as $blockType) {
            $blockTypeFieldDefinitions = array();

            foreach ($blockType->getFields() as $blockTypeField) {
                $blockTypeFieldDefinitions[$blockTypeField->handle] = $fieldFactory->getDefinition($blockTypeField, false);
            }

            $blockTypeDefinitions[] = array(
                'fields' => $blockTypeFieldDefinitions,
            );
        }

        return $blockTypeDefinitions;
    }

    /**
     * @param array $fieldDefinition
     * @param FieldModel $field
     * @return SuperTable_BlockTypeModel[]
     */
    protected function getBlockTypes(array $fieldDefinition, FieldModel $field)
    {
        $blockTypes = array();
        foreach ($fieldDefinition['blockTypes'] as $blockTypeId => $blockTypeDef) {
            $blockType = new SuperTable_BlockTypeModel();
            $this->populateBlockType($field, $blockType, $blockTypeDef);
            $blockTypes[] = $blockType;
        }

        return $blockTypes;
    }

    /**
     * @param FieldModel $field
     * @param SuperTable_BlockTypeModel $blockType
     * @param array $blockTypeDef
     */
    private function populateBlockType(FieldModel $field, SuperTable_BlockTypeModel $blockType, array $blockTypeDef)
    {
        $fieldFactory = $this->getFieldFactory();

        $blockType->fieldId = $field->id;

        $blockTypeFields = array();
        foreach ($blockType->getFields() as $blockTypeField) {
            $blockTypeFields[$blockTypeField->handle] = $blockTypeField;
        }

        $newBlockTypeFields = array();

        foreach ($blockTypeDef['fields'] as $blockTypeFieldHandle => $blockTypeFieldDef) {
            $blockTypeField = array_key_exists($blockTypeFieldHandle, $blockTypeFields)
                ? $blockTypeFields[$blockTypeFieldHandle]
                : new FieldModel();

            $fieldFactory->populate($blockTypeFieldDef, $blockTypeField, $blockTypeFieldHandle);

            $newBlockTypeFields[] = $blockTypeField;
        }

        $blockType->setFields($newBlockTypeFields);
    }
}
