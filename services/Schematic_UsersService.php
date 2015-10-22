<?php

namespace Craft;

/**
 * Schematic Users Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class Schematic_UsersService extends Schematic_AbstractService
{
    /**
     * Export user settings.
     *
     * @param UserModel[] $users
     *
     * @return array
     */
    public function export(array $users = array())
    {
        return $this->getUsersDefinition(new UserModel());
    }

    /**
     * Get users definition.
     *
     * @param UserModel $user
     *
     * @return array
     */
    private function getUsersDefinition(UserModel $user)
    {
        return array(
            'fieldLayout' => craft()->schematic_fields->getFieldLayoutDefinition($user->getFieldLayout()),
        );
    }

    /**
     * Attempt to import user settings.
     *
     * @param array $user_settings
     * @param bool  $force                If set to true user settings not included in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function import(array $user_settings, $force = true)
    {
        // always delete existing fieldlayout first
        craft()->fields->deleteLayoutsByType(ElementType::User);

        if(isset($user_settings['fieldLayout'])) {
            $fieldLayoutDefinition = (array) $user_settings['fieldLayout'];
        } else {
            $fieldLayoutDefinition = array();
        }

        $fieldLayout = craft()->schematic_fields->getFieldLayout($fieldLayoutDefinition);
		$fieldLayout->type = ElementType::User;

		if (!craft()->fields->saveLayout($fieldLayout)) {  // Save fieldlayout via craft

            $this->addErrors($fieldLayout->getAllErrors());

            continue;
        }

        return $this->getResultModel();
    }
}
