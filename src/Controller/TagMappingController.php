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
    $string = $request->query->get('q');

    if (substr_count($string, '.') == 0) {
      list($searchKeyword, $matches) = $this->matchEntityType($string);
    }
    elseif (substr_count($string, '.') == 1) {
      list($searchKeyword, $matches) = $this->matchBundle($string);
    }
    else {
      list($searchKeyword, $matches) = $this->matchField($string, $request->query->get('type'));
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

  /**
   * Matches the current entity type.
   *
   * @param string $string
   *   Current search string.
   *
   * @return array
   *   Possible suggestions.
   */
  protected function matchEntityType($string) {
    $matches = [];

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
    return [$string, $matches];
  }

  /**
   * Matches the current bundle.
   *
   * @param string $string
   *   Current search string.
   *
   * @return array
   *   Possible suggestions.
   */
  protected function matchBundle($string) {
    list ($entityType, $bundle) = explode('.', $string);
    $definition = $this->entityTypeManager()->getDefinition($entityType);

    $targetBundles = $this->entityTypeManager()
      ->getStorage($definition->getBundleEntityType())
      ->loadMultiple();

    $matches = [];
    foreach ($targetBundles as $targetBundle) {
      $name = $entityType . '.' . $targetBundle->id();
      $matches[] = [
        'value' => "$name.",
        'label' => "$name... ({$targetBundle->label()})",
        'keyword' => "{$targetBundle->id()} {$targetBundle->label()}",
      ];
    }
    return [$bundle, $matches];
  }

  /**
   * Matches the current field.
   *
   * @param string $string
   *   Current search string.
   *
   * @return array
   *   Possible suggestions.
   */
  protected function matchField($string, $type) {
    $matches = [];

    $parts = explode('.', $string);

    $entityType = array_shift($parts);
    $bundle = array_shift($parts);

    $entityManager = $this->entityManager();
    $entityTypeManager = $this->entityTypeManager();

    $definitions = $entityManager->getFieldDefinitions($entityType, $bundle);

    $value = "$entityType.$bundle.";
    $targetBundles = [];
    $reference = TRUE;
    foreach ($parts as $part) {

      if (strpos($part, ':') !== FALSE) {
        $reference = TRUE;

        list($fieldName, $bundle) = array_pad(explode(':', $part), 2, NULL);
        $entityType = $definitions[$fieldName]->getFieldStorageDefinition()->getSetting('target_type');
        $definition = $entityTypeManager->getDefinition($entityType);

        $targetBundles = $entityTypeManager->getStorage($definition->getBundleEntityType())
          ->loadMultiple();
        if ($bundle && isset($targetBundles[$bundle])) {
          $definitions = $entityManager->getFieldDefinitions($entityType, $bundle);
          $value .= "$fieldName:$bundle.";
        }
        else {
          $value .= "$fieldName:";
        }
      }
      else {
        if (!$reference) {
          $definitions = [];
        }
        $reference = FALSE;
      }
    }
    if (strrpos($string, '.') > strrpos($string, ':')) {
      $searchKeyword = end($parts);

      foreach ($definitions as $definition) {
        if (!$definition->isReadOnly() &&
          $definition->getFieldStorageDefinition()->isQueryable() &&
          in_array($definition->getType(), array_merge(explode(',', $type), ['entity_reference', 'entity_reference_revisions']))
        ) {
          $name = $value . $definition->getName();
          $matches[] = [
            'value' => $name,
            'label' => "$name ({$definition->getLabel()})",
            'keyword' => "{$definition->getName()} {$definition->getLabel()}",
          ];

          if (in_array($definition->getType(), ['entity_reference_revisions', 'entity_reference']) &&
            !empty($definition->getSetting('handler_settings')['target_bundles'])
          ) {
            $matches[] = [
              'value' => "$name:",
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
          'value' => "$name.",
          'label' => "$name ({$definition->label()})",
          'keyword' => "{$definition->id()} {$definition->label()}",
        ];
      }
    }
    return [$searchKeyword, $matches];
  }

}
