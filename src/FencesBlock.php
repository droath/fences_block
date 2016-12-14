<?php

namespace Drupal\fences_block;

use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Form\FormStateInterface;
use Drupal\block\Entity\Block;

class FencesBlock {

  /**
   * Fences block pre-process.
   *
   * @param array &$variables
   *   An array of the block template variables.
   */
  public static function preprocess(array &$variables) {
    $element = &$variables['elements'];

    if (!isset($element['#id'])) {
      return;
    }
    $block = Block::load($element['#id']);

    if ($block instanceof ThirdPartySettingsInterface) {
      $settings = $block->getThirdPartySetting(
        'fences_block', 'fences', []
      );

      if (!isset($settings) || empty($settings['items'])) {
        return;
      }

      foreach ($settings['items'] as $section => $info) {
        $classes = self::processClasses($info['classes']);

        // Set the fences element classes based on the section.
        switch ($section) {
          case 'wrapper':
            self::addClasses($variables, 'attributes', $classes);
            break;

          case 'label':
            self::addClasses($variables, 'title_attributes', $classes);
            break;

          case 'content':
            self::addClasses($variables, 'content_attributes', $classes);
            break;
        }

        // Set the fences element based on the section.
        $variables['fences_' . $section . '_element'] = $info['element'];
      }
    }
  }

  /**
   * Add fences block configuration form.
   *
   * @param array &$form
   *   An array of form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public static function addConfigForm(array &$form, FormStateInterface $form_state) {
    $entity = $form_state->getFormObject()->getEntity();

    $form['fences'] = [
      '#type' => 'fences_tag',
      '#sections' => [
        'wrapper' => 'Wrapper',
        'title' => 'Title',
        'content' => 'Content',
      ],
      '#default_value' => $entity->getThirdPartySetting(
        'fences_block', 'fences', []
      ),
    ];

    $form['#entity_builders'][] = [get_class(), 'buildEntity'];
  }

  /**
   * Entity builder callback.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\Core\Entity\Entity $entity
   *   The entity object.
   * @param array &$form
   *   An array of form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public static function buildEntity(
    $entity_type_id,
    Entity $entity,
    array &$form,
    FormStateInterface &$form_state
  ) {
    $entity
      ->setThirdPartySetting('fences_block', 'fences', $form_state->getValue('fences'))
      ->save();
  }

  /**
   * Process CSS classes.
   *
   * @param array|string $classes
   *   Either a string or array of classes.
   * @param string $delimiter
   *   The boundary string for exploding classes.
   *
   * @return array
   *   An array of processed CSS classes.
   */
  protected static function processClasses($classes, $delimiter = ', ') {
    if (!is_array($classes)) {
      $classes = explode($delimiter, $classes);
    }

    return array_map(
      '\Drupal\Component\Utility\Html::cleanCssIdentifier', $classes
    );
  }

  /**
   * Add classes to attributes.
   *
   * @param array &$variables
   *   An array of template variables.
   * @param string $name
   *   The class attributes key.
   * @param array $classes
   *   An array of classes to add.
   */
  protected static function addClasses(&$variables, $name, array $classes) {
    if (!isset($variables[$name]['class'])) {
      $variables[$name]['class'] = [];
    }

    $variables[$name]['class'] = array_merge($variables[$name]['class'], $classes);
  }

}
