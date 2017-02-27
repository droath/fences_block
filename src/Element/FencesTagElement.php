<?php

namespace Drupal\fences_block\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Define the fences tag form element.
 *
 * @FormElement("fences_tag")
 */
class FencesTagElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#tree' => TRUE,
      '#input' => TRUE,
      '#title' => new TranslatableMarkup('Fences'),
      '#sections' => [],
      '#process' => [
        [$class, 'fencesTagElementProcess'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      return isset($element['#default_value'])
        && is_array($element['#default_value']) ? $element['#default_value'] : [];
    }

    if (!is_array($input)) {
      return [];
    }

    return $input;
  }

  /**
   * Process fences tag element.
   *
   * @param array &$element
   *   An array of the element properties.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The Drupal form state object.
   * @param array &$complete_form
   *   An array of the completed form elements.
   *
   * @return array
   *   An array of the fully processed element.
   */
  public static function fencesTagElementProcess(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $tag_manager = self::fencesTagManager();

    $element['sections'] = [
      '#type' => 'details',
      '#title' => $element['#title'],
      '#open' => FALSE,
    ];
    $value = is_array($element['#value']) ? $element['#value'] : [];

    foreach ($element['#sections'] as $section => $label) {
      $element['sections'][$section]['element'] = [
        '#type' => 'select',
        '#title' => new TranslatableMarkup('@label element', ['@label' => $label]),
        '#options' => $tag_manager->getTagOptions(),
        '#default_value' => isset($value['sections'][$section]['element']) ? $value['sections'][$section]['element'] : NULL,
        '#empty_option' => new TranslatableMarkup('-- Default --'),
      ];
      $element['sections'][$section]['classes'] = [
        '#type' => 'textfield',
        '#title' => new TranslatableMarkup('@label classes', ['@label' => $label]),
        '#description' => new TranslatableMarkup('Input multiple class values using a comma delimiter.'),
        '#default_value' => isset($value['sections'][$section]['classes']) ? $value['sections'][$section]['classes'] : NULL,
        '#states' => [
          'visible' => [
            ':input[name="' . $element['#name'] . '[sections][' . $section . '][element]"]' => ['!value' => 'none'],
          ],
        ],
      ];
    }

    return $element;
  }

  /**
   * Fences tag manager service.
   *
   * @return \Drupal\fences\TagManager
   *   The fences tag manager object.
   */
  protected static function fencesTagManager() {
    return \Drupal::service('fences.tag_manager');
  }

}
