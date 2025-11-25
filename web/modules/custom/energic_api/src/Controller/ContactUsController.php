<?php

namespace Drupal\energic_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class ContactUsController extends ControllerBase {

  protected $fileUrlGenerator;

  public function __construct() {
    $this->fileUrlGenerator = \Drupal::service('file_url_generator');
  }

  public function contactUs() {
    /** @var \Drupal\node\Entity\Node $node */

    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['type' => 'contact_us']);
    $node = reset($node);

    if (!$node) {
      return new JsonResponse(['error' => 'Contact Us page not found'], 404);
    }

    $sections = [];

    if ($node->hasField('field_contact_us_sections')) {
      foreach ($node->get('field_contact_us_sections')->referencedEntities() as $section) {
        $type = $section->bundle();

        if ($type === 'contact_hero_section') {
          $sections['contact_hero_section'] = $this->getContactHeroSectionData($section);
        }
        elseif ($type === 'contact_location_section') {
          $sections['contact_location_section'] = $this->getContactLocationSectionData($section);
        }
      }
    }

    // 🧹 تنظيف البيانات من الفارغ
    $sections = array_filter($sections, fn($v) => $v !== null && $v !== '' && $v !== []);

    return new JsonResponse([
      'contact_us_id' => $node->id(),
      'title' => $node->getTitle(),
      'sections' => $sections,
    ]);
  }

  /**
   * 🟢 Contact Hero Section Data
   */
  private function getContactHeroSectionData($section) {
    $data = [
      'title' => $section->get('field_contact_title')->value ?? '',
      'description' => $section->get('field_contact_description')->value ?? '',
      'form_title' => $section->get('field_contact_form_title')->value ?? '',
    ];

    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
  }

  /**
   * 🟣 Contact Location Section Data
   */
  private function getContactLocationSectionData($section) {
    $data = [
      'title' => $section->get('field_location_section_title')->value ?? '',
      'items' => [],
    ];

    if ($section->hasField('field_location_items') && !$section->get('field_location_items')->isEmpty()) {
      foreach ($section->get('field_location_items')->referencedEntities() as $item) {
        $data['items'][] = [
          'title' => $item->get('field_location_title')->value ?? '',
          'description' => $item->get('field_location_description')->value ?? '',
        ];
      }
    }

    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
  }

}
