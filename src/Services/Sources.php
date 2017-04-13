<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\BaseApplicationComponent as BaseApplication;

/**
 * Schematic Sources Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class Sources extends BaseApplication
{
    /**
    * Get sources based on the indexFrom attribute and return them with the indexTo attribute.
    *
    * @param string       $fieldType
    * @param string|array $sources
    * @param string       $indexFrom
    * @param string       $indexTo
    *
    * @return array|string
    */
   public function getMappedSources($fieldType, $sources, $indexFrom, $indexTo)
   {
       $mappedSources = $sources;
       if (is_array($sources)) {
           $mappedSources = [];
           foreach ($sources as $source) {
               $mappedSources[] = $this->getSource($fieldType, $source, $indexFrom, $indexTo);
           }
       }

       return $mappedSources;
   }

   /**
    * Gets a source by the attribute indexFrom, and returns it with attribute $indexTo.
    *
    * @TODO Break up and simplify this method
    *
    * @param string $fieldType
    * @param string $source
    * @param string $indexFrom
    * @param string $indexTo
    *
    * @return string
    */
   public function getSource($fieldType, $source, $indexFrom, $indexTo)
   {
       if ($source == 'singles' || $source == '*') {
           return $source;
       }

      /** @var BaseElementModel $sourceObject */
      $sourceObject = null;

       if (strpos($source, ':') > -1) {
           list($sourceType, $sourceFrom) = explode(':', $source);
           switch ($sourceType) {
              case 'section':
                  $service = Craft::app()->sections;
                  $method = 'getSectionBy';
                  break;
              case 'group':
                  $service = $fieldType == 'Users' ? Craft::app()->userGroups : Craft::app()->categories;
                  $method = 'getGroupBy';
                  break;
              case 'folder':
                  $service = Craft::app()->schematic_assetSources;
                  $method = 'getSourceBy';
                  break;
              case 'taggroup':
                  $service = Craft::app()->tags;
                  $method = 'getTagGroupBy';
                  break;
              case 'field':
                  $service = Craft::app()->fields;
                  $method = 'getFieldBy';
                  break;
          }
       } elseif ($source !== 'singles') {
           //Backwards compatibility
          $sourceType = 'section';
           $sourceFrom = $source;
           $service = Craft::app()->sections;
           $method = 'getSectionBy';
       }

       if (isset($service) && isset($method) && isset($sourceFrom)) {
           $method = $method.$indexFrom;
           $sourceObject = $service->$method($sourceFrom);
       }

       if ($sourceObject && isset($sourceType)) {
           return $sourceType.':'.$sourceObject->$indexTo;
       }

       return $source;
   }
}
