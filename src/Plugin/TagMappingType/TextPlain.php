<?php
/**
 * @file
 * TextPlain.php for tfp
 */

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder_print\Annotation\TagMappingType;
use Drupal\thunder_print\Plugin\TagMappingTypeBase;

/**
 * Class TextPlain
 * @package Drupal\thunder_print\Plugin\TagMappingType
 * @TagMappingType(
 *   id = "text_plain",
 *   label = @Translation("Text plain"),
 * )
 */
class TextPlain extends TagMappingTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    return [
      'value' => [
        'required' => TRUE,
        'name' => $this->t('Value'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function optionsForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Can be used as title'),
      '#default_value' => $this->getOption('title'),
    ];
    return $form;
  }


}
