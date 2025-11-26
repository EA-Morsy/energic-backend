<?php
namespace Drupal\energic_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class SolutionsController extends ControllerBase {

  protected $fileUrlGenerator;

  public function __construct() {
    $this->fileUrlGenerator = \Drupal::service('file_url_generator');
  }

  public function solutions() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['type' => 'solutions']);
    $node = reset($node);

    if (!$node) {
      return new JsonResponse(['error' => 'Solutions page not found'], 404);
    }

    $sections = [];
https://energic-backend.ddev.site/
    if ($node->hasField('field_solution_sections')) {
      foreach ($node->get('field_solution_sections')->referencedEntities() as $section) {
        $type = $section->bundle();

        if ($type === 'solution_hero_section') {
          $sections['solution_hero_section'] = $this->getSolutionHeroSectionData($section);
        }
        elseif ($type === 'solution_info_section') {
          $sections['solution_info_section'] = $this->getSolutionInfoSectionData($section);
        }
        elseif ($type === 'solution_core_features') {
          $sections['solution_core_features'] = $this->getSolutionCoreFeaturesSectionData($section);
        }elseif ($type === 'integrated_brands_section') {
          $sections['integrated_brands_section'] = $this->getIntegratedBrandsSectionData($section);
        }elseif ($type === 'our_solutions_section') {
          $sections['our_solutions_section'] = $this->getOurSolutionsSectionData($section);
        }elseif ($type === 'energic_control_platform_section') {
          $sections['energic_control_platform_section'] = 
            $this->getEnergicControlPlatformSectionData($section);
        }elseif ($type === 'energic_control_benefits_section') {
          $sections['energic_control_benefits_section'] =
            $this->getBenefitsSectionData($section);
        }elseif ($type === 'energic_control_demo_section') {
          $sections['energic_control_demo_section'] =
            $this->getEnergicControlDemoSectionData($section);
        }
        

      }
    }

    // 🧹 Clean up empty data
    $sections = array_filter($sections, fn($v) => $v !== null && $v !== '' && $v !== []);

    return new JsonResponse([
      'solutions_id' => $node->id(),
      'title' => $node->getTitle(),
      'sections' => $sections,
    ]);
  }

  /**
   * 🟢 Solution Hero Section
   */
  private function getSolutionHeroSectionData($section) {
    $imageUrl = null;
    if ($section->hasField('field_solution_hero_image') && !$section->get('field_solution_hero_image')->isEmpty()) {
        $media = $section->get('field_solution_hero_image')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            $imageUrl = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
        
    }

     // ✅ جلب عناصر الـ hero list لو موجودة
     $heroList = [];
     if ($section->hasField('field_solution_hero_list') && !$section->get('field_solution_hero_list')->isEmpty()) {
         foreach ($section->get('field_solution_hero_list')->referencedEntities() as $item) {
             if ($item->hasField('field_hero_list_text') && !$item->get('field_hero_list_text')->isEmpty()) {
                 $heroList[] = $item->get('field_hero_list_text')->value;
             }
         }
     }
    $data = [
      'title' => $section->get('field_solution_hero_title')->value ?? '',
      'subtitle' => $section->get('field_solution_hero_subtitle')->value ?? '',
      'image' => $imageUrl,
      'hero_list' => $heroList // ✅ ضيفنا البيانات الجديدة هنا

    ];

    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
  }

  /**
   * 🟣 Solution Info Section
   */
/**
 * 🟣 Solution Info Section
 */
private function getSolutionInfoSectionData($section) {
    $imageUrl = null;
  
    if ($section->hasField('field_solution_info_image') && !$section->get('field_solution_info_image')->isEmpty()) {
      $media = $section->get('field_solution_info_image')->entity;
      if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $imageUrl = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
  
    $data = [
      'title' => $section->get('field_solution_info_title')->value ?? '',
      'subtitle' => $section->get('field_solution_info_subtitle')->value ?? '',
      'description' => $section->get('field_solution_info_description')->value ?? '',
      'quote' => $section->get('field_solution_quote')->value ?? '',
      'image' => $imageUrl,
    ];
  
    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
  }
  

  /**
   * 🔵 Solution Core Features Section
   */
/**
 * 🟢 Solution Core Features Section
 */
/**
 * 🟢 Solution Core Features Section
 */


  /**
 * 🟢 Solution Core Features Section (clean version)
 */
private function getSolutionCoreFeaturesSectionData($section) {
  $data = [
      'items' => [],
  ];

  // Check if section has feature items
  if (
      $section->hasField('field_solution_feature_items') &&
      !$section->get('field_solution_feature_items')->isEmpty()
  ) {
      foreach ($section->get('field_solution_feature_items')->referencedEntities() as $feature) {

          // 🔹 Handle image (Media -> File)
          $imageUrl = null;
          if (
              $feature->hasField('field_solution_feature_image') &&
              !$feature->get('field_solution_feature_image')->isEmpty()
          ) {
              $media = $feature->get('field_solution_feature_image')->entity;

              if ($media &&
                  $media->hasField('field_media_image') &&
                  !$media->get('field_media_image')->isEmpty()
              ) {
                  $file = $media->get('field_media_image')->entity;

                  if ($file instanceof \Drupal\file\Entity\File) {
                      $imageUrl = $this->fileUrlGenerator
                          ->generateAbsoluteString($file->getFileUri());
                  }
              }
          }

          // 🔹 Build clean feature object (NO type, NO list items)
          $data['items'][] = [
              'title'       => $feature->get('field_solution_feature_title')->value ?? '',
              'description' => $feature->get('field_solution_feature_desc')->value ?? '',
              'image'       => $imageUrl,
          ];
      }
  }

  return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
}
/**
 * ✅ Integrated Brands Section Data
 */
private function getIntegratedBrandsSectionData($section) {
  $data = [
    'title' => $section->hasField('field_brand_section_title')
      ? $section->get('field_brand_section_title')->value
      : '',
    'subtitle'=>  $section->hasField('field_brand_section_subtitle')
    ? $section->get('field_brand_section_subtitle')->value
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

private function getOurSolutionsSectionData($section) {
  $data = [
    'title' => $section->get('field_our_solutions_title')->value ?? '',
    'description' => $section->get('field_our_solutions_description')->value ?? '',
    'button' => [],
    'images' => [],
  ];

  // 🎯 زرار CTA
  if ($section->hasField('field_our_solutions_button') && !$section->get('field_our_solutions_button')->isEmpty()) {
    $link = $section->get('field_our_solutions_button')->first();
    $data['button'] = [
      'title' => $link->title ?? '',
      'url' => $link->getUrl()->toString() ?? '',
    ];
  }

  // 🖼️ الصور المتعددة
  if ($section->hasField('field_our_solutions_images') && !$section->get('field_our_solutions_images')->isEmpty()) {
    foreach ($section->get('field_our_solutions_images')->referencedEntities() as $media) {
      if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $data['images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
  }

  // 🧹 حذف أي مفاتيح فاضية (null, '', [])
  $data = array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);

  return $data;
}




/**
 * 🟦 Energic Control Platform Section
 */
private function getEnergicControlPlatformSectionData($section) {
  
  $imageUrl = null;
  if ($section->hasField('field_energic_platform_image') && !$section->get('field_energic_platform_image')->isEmpty()) {
      $media = $section->get('field_energic_platform_image')->entity;
      if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $imageUrl = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
      
  }
  $data = [
    'title' => $section->get('field_energic_platform_title')->value ?? '',
    'subtitle' => $section->get('field_energic_platform_subtitle')->value ?? '',
    'description' => $section->get('field_energic_platform_desc')->value ?? '',
    'image' =>$imageUrl,
    'features' => []
    
  ];

  // 🔵 Features
  if ($section->hasField('field_energic_platform_features') && !$section->get('field_energic_platform_features')->isEmpty()) {
    foreach ($section->get('field_energic_platform_features')->referencedEntities() as $feature) {

      // 🔹 Handle icon image
      $icon_url = null;
      if ($feature->hasField('field_energic_feature_icon') && !$feature->get('field_energic_feature_icon')->isEmpty()) {
        $media = $feature->get('field_energic_feature_icon')->entity;

        if ($media && $media->hasField('field_media_image')) {
          $image_file = $media->get('field_media_image')->entity;
          if ($image_file) {
            $icon_url = $this->fileUrlGenerator->generateAbsoluteString($image_file->getFileUri());
          }
        }
      }

      // Push data
      $data['features'][] = [
        'title' => $feature->get('field_energic_feature_title')->value ?? '',
        'description' => $feature->get('field_energic_feature_desc')->value ?? '',
        'icon' => $icon_url,
      ];
    }
  }

  return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
}


private function getBenefitsSectionData($section) {
  $data = [
    'title' => $section->get('field_energic_benefits_title')->value ?? '',
    'short_items' => [],
    'detailed_items' => [],
  ];

  // -----------------------------------
  // 🔹 Short Items (Text Only)
  // -----------------------------------
  if ($section->hasField('field_benefits_short_items') && !$section->get('field_benefits_short_items')->isEmpty()) {
    foreach ($section->get('field_benefits_short_items')->referencedEntities() as $item) {
      $data['short_items'][] = [
        'text' => $item->get('field_short_item_text')->value ?? '',
      ];
    }
  }

  // -----------------------------------
  // 🔹 Detailed Items (Title + Desc + Icon)
  // -----------------------------------
  if ($section->hasField('field_benefits_detailed_items') && !$section->get('field_benefits_detailed_items')->isEmpty()) {
    foreach ($section->get('field_benefits_detailed_items')->referencedEntities() as $item) {

      $icon_url = '';

      // 🖼️ Load icon (Media → Image)
      if ($item->hasField('field_benefits_list_icon') && !$item->get('field_benefits_list_icon')->isEmpty()) {
        $media = $item->get('field_benefits_list_icon')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            $icon_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $data['detailed_items'][] = [
        'title'       => $item->get('field_benefits_list_title')->value ?? '',
        'description' => $item->get('field_benefits_list_description')->value ?? '',
        'icon'        => $icon_url,
      ];
    }
  }

  return $data;
}


private function getEnergicControlDemoSectionData($section) {
  $data = [
    'title' => $section->get('field_energic_demo_title')->value ?? '',
    'subtitle' => $section->get('field_energic_demo_subtitle')->value ?? '',
    'description' => $section->get('field_energic_demo_description')->value ?? '',
    'booking_demo_link' => '',
    'talk_to_expert_link' => '',
  ];

  // 🔗 Booking Demo Link
  if ($section->hasField('field_energic_booking_demo') && !$section->get('field_energic_booking_demo')->isEmpty()) {
    $link = $section->get('field_energic_booking_demo')->first();
    $data['booking_demo_link'] = [
      'url' => $link->getUrl()->toString(),
      'title' => $link->title ?? '',
    ];
  }

  // 🔗 Talk To Expert Link
  if ($section->hasField('field_talk_to_expert') && !$section->get('field_talk_to_expert')->isEmpty()) {
    $link = $section->get('field_talk_to_expert')->first();
    $data['talk_to_expert_link'] = [
      'url' => $link->getUrl()->toString(),
      'title' => $link->title ?? '',
    ];
  }

  return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
}

}
