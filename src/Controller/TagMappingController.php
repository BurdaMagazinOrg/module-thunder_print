<?php

namespace Drupal\thunder_print\Controller;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TagMappingController.
 *
 * @package Drupal\thunder_print\Controller
 */
class TagMappingController extends ControllerBase {

  /**
   * Autocomplete path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   */
  public function autocomplete(Request $request) {
    $matches = [];
    $string = $request->query->get('q');

    // Select entity type.
    if (substr_count($string, '.') == 0) {

      $searchKeyword = $string;

      foreach ($this->entityTypeManager()->getDefinitions() as $definition) {
        if ($definition instanceof ContentEntityTypeInterface && $definition->getBundleEntityType()) {
          $name = $definition->id();
          $matches[] = [
            'value' => $definition->id() . '.',
            'label' => "$name... ({$definition->getLabel()})",
            'keyword' => "{$definition->id()} {$definition->getLabel()}",
          ];
        }
      }
    }
    // Select bundle.
    elseif (substr_count($string, '.') == 1) {

      list ($entityType, $bundle) = explode('.', $string);
      $definition = $this->entityTypeManager()->getDefinition($entityType);

      $bundles = $this->entityTypeManager()
        ->getStorage($definition->getBundleEntityType())
        ->loadMultiple();

      $searchKeyword = $bundle;

      foreach ($bundles as $bundle) {
        $name = $entityType . '.' . $bundle->id();
        $matches[] = [
          'value' => $name . '.',
          'label' => "$name... ({$bundle->label()})",
          'keyword' => "{$bundle->id()} {$bundle->label()}",
        ];
      }
    }
    // Select fields.
    else {
      $parts = explode('.', $string);

      $entityType = array_shift($parts);
      $bundle = array_shift($parts);

      $definitions = $this->entityManager()->getFieldDefinitions($entityType, $bundle);

      $value = $entityType . '.' . $bundle . '.';
      $targetBundles = [];
      foreach ($parts as $part) {

        if ($part) {
          if (strpos($part, ':') !== FALSE) {
            list($fieldName, $bundle) = array_pad(explode(':', $part), 2, NULL);
            $entityType = $definitions[$fieldName]->getFieldStorageDefinition()->getSetting('target_type');
            $definition = $this->entityTypeManager()->getDefinition($entityType);

            $targetBundles = $this->entityTypeManager()
              ->getStorage($definition->getBundleEntityType())
              ->loadMultiple();
            if ($bundle && isset($targetBundles[$bundle])) {
              $definitions = $this->entityManager()->getFieldDefinitions($entityType, $bundle);
              $value .= $fieldName . ':' . $bundle . '.';
            }
            else {
              $value .= $fieldName . ':';
            }
          }
        }
      }
      if (strrpos($string, '.') > strrpos($string, ':')) {
        $searchKeyword = end($parts);

        foreach ($definitions as $definition) {
          if (!$definition->isReadOnly() && $definition->getFieldStorageDefinition()->isQueryable()) {
            $name = $value . $definition->getName();
            $matches[] = [
              'value' => $name,
              'label' => "$name ({$definition->getLabel()})",
              'keyword' => "{$definition->getName()} {$definition->getLabel()}",
            ];

            if (in_array($definition->getType(), ['entity_reference_revisions', 'entity_reference']) && !empty($definition->getSetting('handler_settings')['target_bundles'])) {
              $matches[] = [
                'value' => $name . ':',
                'label' => "$name... ({$definition->getLabel()})",
                'keyword' => "{$definition->getName()} {$definition->getLabel()}",
              ];
            }
          }
        }
      }
      else {
        $searchKeyword = $bundle;

        foreach ($targetBundles as $definition) {
          $name = $value . $definition->id();
          $matches[] = [
            'value' => $name . '.',
            'label' => "$name ({$definition->label()})",
            'keyword' => "{$definition->id()} {$definition->label()}",
          ];
        }
      }
    }

    if ($searchKeyword) {
      $matches = array_filter($matches, function ($value) use ($searchKeyword) {
        return strpos(strtolower($value['keyword']), strtolower($searchKeyword)) !== FALSE;
      });
    }

    usort($matches, [$this, 'sortByLabelElement']);

    return new JsonResponse($matches);
  }

  /**
   * Sorts a structured array by 'label' element.
   *
   * Callback for uasort().
   *
   * @param array $a
   *   First item for comparison. The compared items should be associative
   *   arrays that optionally include a '#title' key.
   * @param array $b
   *   Second item for comparison.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sortByLabelElement(array $a, array $b) {
    return SortArray::sortByKeyString($a, $b, 'label');
  }

}
