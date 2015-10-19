<?php

namespace Craft;

/**
 * Schematic Command.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class SchematicCommand extends BaseCommand
{
    /**
     * Imports the Craft datamodel.
     *
     * @param string $file  yml file containing the schema definition
     * @param bool   $force if set to true items not in the import will be deleted
     */
    public function actionImport($file = 'craft/config/schema.yml', $force = false)
    {
        if (!IOHelper::fileExists($file)) {
            $this->usageError(Craft::t('File not found.'));
            exit(1);
        }

        $result = craft()->schematic->importFromYaml($file, $force);

        if (!$result->hasErrors()) {
            echo Craft::t('Loaded schema from {file}', array('file' => $file))."\n";
            exit(0);
        }

        echo Craft::t('There was an error loading schema from {file}', array('file' => $file))."\n";
        print_r($result->errors);
        exit(1);
    }

    /**
     * Exports the Craft datamodel.
     *
     * @param string $file file to write the schema to
     */
    public function actionExport($file = 'craft/config/schema.yml')
    {
        craft()->schematic->exportToYaml($file);

        echo Craft::t('Exported schema to {file}', array('file' => $file))."\n";
        exit(0);
    }
}
