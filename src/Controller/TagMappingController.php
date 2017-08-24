<?php

namespace Drupal\thunder_print\Controller;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
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

      $definitions = $this->entityTypeManager()->getDefinitions();

      $definitions = array_filter($definitions, function (EntityTypeInterface $definition) use ($string) {
        return (($definition instanceof ContentEntityTypeInterface && $definition->getBundleEntityType()) &&
          (empty($string) ||
            (strpos(strtolower($definition->getLabel()), strtolower($string)) !== FALSE ||
              strpos(strtolower($definition->id()), strtolower($string)) !== FALSE)));
      });

      foreach ($definitions as $definition) {
        $matches[] = [
          'value' => $definition->id() . '.',
          'label' => "{$definition->getLabel()} ({$definition->id()})",
        ];
      }

    }
    // Select bundle.
    elseif (substr_count($string, '.') == 1) {

      list ($entityType, $bundle) = explode('.', $string);
      $definition = $this->entityTypeManager()->getDefinition($entityType);

      $bundles = $this->entityTypeManager()
        ->getStorage($definition->getBundleEntityType())
        ->loadMultiple();

      if ($bundle) {
        $bundles = array_filter($bundles, function (EntityInterface $value) use ($bundle) {
          return strpos(strtolower($value->label()), strtolower($bundle)) !== FALSE ||
            strpos(strtolower($value->id()), strtolower($bundle)) !== FALSE;
        });
      }

      foreach ($bundles as $bundle) {
        $matches[] = [
          'value' => $entityType . '.' . $bundle->id() . '.',
          'label' => "{$bundle->label()} ({$bundle->id()})",
        ];
      }
    }
    // Select fields.
    else {
      $parts = explode('.', $string);

      $entityType = array_shift($parts);
      $bundle = array_shift($parts);

      $definitions = $this->entityManager()
        ->getFieldDefinitions($entityType, $bundle);

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
        $lastElement = end($parts);
        if ($lastElement) {
          $definitions = array_filter($definitions, function (FieldDefinitionInterface $value) use ($lastElement) {
            return strpos(strtolower($value->getLabel()), strtolower($lastElement)) !== FALSE ||
              strpos(strtolower($value->getName()), strtolower($lastElement)) !== FALSE;
          });
        }
        foreach ($definitions as $definition) {
          $name = $value . $definition->getName();
          $name .= in_array($definition->getType(), ['entity_reference_revisions', 'entity_reference']) ? ':' : '';
          $matches[] = [
            'value' => $name,
            'label' => "{$definition->getLabel()} ({$definition->getName()})",
          ];
        }
      }
      else {
        if ($bundle) {
          $targetBundles = array_filter($targetBundles, function ($value) use ($bundle) {
            return strpos(strtolower($value->label()), strtolower($bundle)) !== FALSE ||
              strpos(strtolower($value->id()), strtolower($bundle)) !== FALSE;
          });
        }

        foreach ($targetBundles as $definition) {
          $matches[] = [
            'value' => $value . $definition->id() . '.',
            'label' => "{$definition->label()} ({$definition->id()})",
          ];
        }
      }
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
