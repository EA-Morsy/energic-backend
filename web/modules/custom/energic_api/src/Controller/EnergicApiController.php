<?php

namespace Drupal\energic_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EnergicApiController extends ControllerBase {

  protected $fileUrlGenerator;

  public function __construct(FileUrlGeneratorInterface $fileUrlGenerator) {
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_url_generator')
    );
  }

  /**
   * API endpoint: /api/landing-page
   */
  public function landingPage() {
    // بدلاً من التحميل بالـ ID.
    /** @var \Drupal\node\Entity\Node $node */

    $node = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties(['type' => 'landing_page']);
    $node = reset($node);
    if (!$node || $node->getType() !== 'landing_page') {
      return new JsonResponse(['error' => 'Landing page not found'], 404);
    }

    $sections = [];

    if ($node->hasField('field_sections')) {
      foreach ($node->get('field_sections')->referencedEntities() as $section) {
        $type = $section->bundle();

        if ($type === 'hero_section') {
          $sections['hero_section'] = $this->getHeroSectionData($section);
        }
        elseif ($type === 'statistics_section') {
          $sections['statistics_section'] = $this->getStatisticsSectionData($section);
        }
        elseif ($type === 'feature_list_section') {
          $sections['feature_list_section'] = $this->getFeatureListSectionData($section);
        }
        elseif ($type === 'info_section') {
          $sections['info_section'][] = $this->getInfoSectionData($section);
        }
        elseif ($type === 'testimonials_section') {
          $sections['testimonials_section'] = $this->getTestimonialsSectionData($section);
        }
        elseif ($type === 'sliders_section') {
          $sections['sliders_section'] = $this->getSlidersSectionData($section);
        }
        elseif ($type === 'events_section') {
          $sections['events_section'] = $this->getEventsSectionData($section);
        }
        elseif ($type === 'core_technologies') {
          $sections['core_technologies'] = $this->getCoreTechnologiesSectionData($section);
        }
        elseif ($type === 'integrated_brands_section') {
          $sections['integrated_brands_section'] = $this->getIntegratedBrandsSectionData($section);
        }
        

        // لاحقاً ممكن تضيف أنواع تانية من السكاشن هنا
      }
    }

    return new JsonResponse([
      'landing_page_id' => $node->id(),
      'title' => $node->getTitle(),
      'sections' => $sections,
    ]);
  }

  /**
   * ✅ Hero Section Data (يدعم صور متعددة)
   */
  private function getHeroSectionData($section) {
    $data = [
      'title' => $section->get('field_hero_title')->value ?? '',
      'subtitle' => $section->get('field_hero_subtitle')->value ?? '',
      'description' => $section->get('field_hero_description')->value ?? '',
      'cta_button' => '',
      'images' => [],
      'background_images' => [],
    ];

    // 🔗 CTA Button
    if ($section->hasField('field_cta_button') && !$section->get('field_cta_button')->isEmpty()) {
      $link = $section->get('field_cta_button')->first();
      $data['cta_button'] = [
        'title' => $link->title ?? '',
        'url' => $link->getUrl()->toString(),
      ];
    }

    // 🖼️ Multiple Images
    if ($section->hasField('field_image') && !$section->get('field_image')->isEmpty()) {
      foreach ($section->get('field_image')->referencedEntities() as $media) {
        if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof File) {
            $data['images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }
    }

    // 🖼️ Multiple Background Images
    if ($section->hasField('field_background_image') && !$section->get('field_background_image')->isEmpty()) {
      foreach ($section->get('field_background_image')->referencedEntities() as $media) {
        if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof File) {
            $data['background_images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }
    }

    return $data;
  }

  /**
 * ✅ Statistics Section Data
 */
private function getStatisticsSectionData($section) {
  $data = [
    'title' => $section->hasField('field_title') ? $section->get('field_title')->value : '',
    'description' => $section->hasField('field_description') ? $section->get('field_description')->value : '',
    'items' => [],
  ];

  // لو فيه عناصر للإحصائيات
  if ($section->hasField('field_statistics_items')) {
    foreach ($section->get('field_statistics_items')->referencedEntities() as $item) {
      $data['items'][] = [
        'number' => $item->hasField('field_stat_number') ? $item->get('field_stat_number')->value .'K' : '',
        'label'  => $item->hasField('field_stat_label') ? $item->get('field_stat_label')->value : '',
      ];
    }
  }

  return $data;
}

/**
 * ✅ Feature List Section Data
 */
private function getFeatureListSectionData($section) {
      $data = [
        'title' => $section->hasField('field_title') ? $section->get('field_title')->value : '',
        'subtitle' => $section->hasField('field_subtitle') ? $section->get('field_subtitle')->value : '',
        'button' => '',
        'image' => '',
        'features' => [],
      ];

      // 🔗 Button
      if ($section->hasField('field_feature_section_button') && !$section->get('field_feature_section_button')->isEmpty()) {
        $link = $section->get('field_feature_section_button')->first();
        $data['button'] = [
          'title' => $link->title ?? '',
          'url' => $link->getUrl()->toString(),
        ];
      }

      // 🖼️ Image
      if ($section->hasField('field_feature_list_image') && !$section->get('field_feature_list_image')->isEmpty()) {
        $media = $section->get('field_feature_list_image')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file) {
            $data['image'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      // 🔹 Feature Items
      if ($section->hasField('field_feature_items')) {
        foreach ($section->get('field_feature_items')->referencedEntities() as $item) {
          $data['features'][] = [
            'title' => $item->hasField('field_feature_item_title') ? $item->get('field_feature_item_title')->value : '',
          ];
        }
      }

      return $data;
    }

/**
 * ✅ Info Section Data
 */
private function getInfoSectionData($section) {
  $data = [
    'title' => $section->hasField('field_info_title') ? $section->get('field_info_title')->value : '',
    'subtitle' => $section->hasField('field_info_subtitle') ? $section->get('field_info_subtitle')->value : '',
    'description' => $section->hasField('field_info_description') ? $section->get('field_info_description')->value : '',
    'alignment' => $section->hasField('field_alignment') ? $section->get('field_alignment')->value : 'left',
    'button' => '',
    'images' => [],
  ];

  // 🔗 Button
  if ($section->hasField('field_info_button') && !$section->get('field_info_button')->isEmpty()) {
    $link = $section->get('field_info_button')->first();
    $data['button'] = [
      'title' => $link->title ?? '',
      'url' => $link->getUrl()->toString(),
    ];
  }

  // 🖼️ Images (Multiple)
  if ($section->hasField('field_info_section_images') && !$section->get('field_info_section_images')->isEmpty()) {
    foreach ($section->get('field_info_section_images')->referencedEntities() as $media) {
      if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file) {
          $data['images'][] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }
    }
  }

  return $data;
}


/**
 * ✅ Testimonials Section Data
 */
private function getTestimonialsSectionData($section) {
  $data = [
    // 'title' => $section->hasField('field_title') ? $section->get('field_title')->value : '',
    // 'subtitle' => $section->hasField('field_subtitle') ? $section->get('field_subtitle')->value : '',
    'items' => [],
  ];

  // 📦 Testimonials items
  if ($section->hasField('field_testimonial_items') && !$section->get('field_testimonial_items')->isEmpty()) {
    foreach ($section->get('field_testimonial_items')->referencedEntities() as $item) {
      $testimonial = [
        'author_name' => $item->hasField('field_author_name') ? $item->get('field_author_name')->value : '',
        'author_role' => $item->hasField('field_author_role') ? $item->get('field_author_role')->value : '',
        'message' => $item->hasField('field_message') ? $item->get('field_message')->value : '',
        'author_image' => '',
      ];

      // 🖼️ Author Image
      if ($item->hasField('field_author_image') && !$item->get('field_author_image')->isEmpty()) {
        $media = $item->get('field_author_image')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file) {
            $testimonial['author_image'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $data['items'][] = $testimonial;
    }
  }

  return $data;
}


/**
 * 🎠 Sliders Section Data
 */
private function getSlidersSectionData($section) {
  $data = [
    'items' => [],
  ];

  // ✅ جلب عناصر السلايدر
  if ($section->hasField('field_slider_item') && !$section->get('field_slider_item')->isEmpty()) {
    foreach ($section->get('field_slider_item')->referencedEntities() as $item) {
      $slider = [
        'title' => $item->hasField('field_slider_item_title') ? $item->get('field_slider_item_title')->value : '',
        'description' => $item->hasField('field_slider_item_description') ? $item->get('field_slider_item_description')->value : '',
        'icon' => '',
      ];

      // 🖼️ أيقونة السلايدر
      if ($item->hasField('field_slider_item_icon') && !$item->get('field_slider_item_icon')->isEmpty()) {
        $media = $item->get('field_slider_item_icon')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file) {
            $slider['icon'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $data['items'][] = $slider;
    }
  }

  return $data;
}


/**
 * 🎉 Events Section Data
 */
private function getEventsSectionData($section) {
  $data = [
    'title' => $section->hasField('field_event_title') ? $section->get('field_event_title')->value : '',
    'items' => [],
  ];

  // ✅ جلب العناصر المرتبطة
  if ($section->hasField('field_event_items') && !$section->get('field_event_items')->isEmpty()) {
    foreach ($section->get('field_event_items')->referencedEntities() as $item) {
      $event = [
        'title' => $item->hasField('field_event_item_title') ? $item->get('field_event_item_title')->value : '',
        'description' => $item->hasField('field_event_item_description') ? $item->get('field_event_item_description')->value : '',
        'link' => '',
        'image' => '',
      ];

      // 🌐 رابط الحدث
      if ($item->hasField('field_event_item_link') && !$item->get('field_event_item_link')->isEmpty()) {
        $linkField = $item->get('field_event_item_link')->first();
        $event['link'] = [
          'url' => $linkField->getUrl()->toString(),
          'title' => $linkField->title ?? '',
        ];
      }

      // 🖼️ صورة الحدث
      if ($item->hasField('field_event_item_image') && !$item->get('field_event_item_image')->isEmpty()) {
        $media = $item->get('field_event_item_image')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file) {
            $event['image'] = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $data['items'][] = $event;
    }
  }

  return $data;
}


/**
 * ✅ Core Technologies Section Data
 */
private function getCoreTechnologiesSectionData($section) {
  $data = [
    'title' => $section->hasField('field_core_technologies_title')
      ? $section->get('field_core_technologies_title')->value
      : '',
    'technologies' => [],
  ];

  // Loop through the nested "technologies" paragraph items
  if ($section->hasField('field_technology_items') && !$section->get('field_technology_items')->isEmpty()) {
    foreach ($section->get('field_technology_items')->referencedEntities() as $tech_item) {
      $tech = [
        'title' => $tech_item->hasField('field_technology_title')
          ? $tech_item->get('field_technology_title')->value
          : '',
        'description' => $tech_item->hasField('field_technology_description')
          ? $tech_item->get('field_technology_description')->value
          : '',
        'images' => [],
      ];

      // Handle multiple images from media entities
      if ($tech_item->hasField('field_technology_images') && !$tech_item->get('field_technology_images')->isEmpty()) {
        foreach ($tech_item->get('field_technology_images')->referencedEntities() as $media) {
          if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
            $file = $media->get('field_media_image')->entity;
            if ($file instanceof \Drupal\file\Entity\File) {
              $tech['images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
            }
          }
        }
      }

      $data['technologies'][] = $tech;
    }
  }

  return $data;
}


/**
 * ✅ Integrated Brands Section Data
 */
private function getIntegratedBrandsSectionData($section) {
  $data = [
    'title' => $section->hasField('field_brand_section_title')
      ? $section->get('field_brand_section_title')->value
      : '',
    'brands' => [],
  ];

  // Loop through the referenced "brands" paragraph items
  if ($section->hasField('field_brand_items') && !$section->get('field_brand_items')->isEmpty()) {
    foreach ($section->get('field_brand_items')->referencedEntities() as $brand_item) {
      $brand = [
        'name' => $brand_item->hasField('field_brand_name')
          ? $brand_item->get('field_brand_name')->value
          : '',
        'logo' => '',
      ];

      // 🖼️ Brand logo image
      if ($brand_item->hasField('field_brand_logo') && !$brand_item->get('field_brand_logo')->isEmpty()) {
        $media = $brand_item->get('field_brand_logo')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            $brand['logo'] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $data['brands'][] = $brand;
    }
  }

  return $data;
}

}

