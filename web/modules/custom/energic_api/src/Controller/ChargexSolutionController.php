<?php

namespace Drupal\energic_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

class ChargexSolutionController extends ControllerBase {

  protected $fileUrlGenerator;

  public function __construct() {
    $this->fileUrlGenerator = \Drupal::service('file_url_generator');
  }

  public function chargexSolutions() {
    /** @var \Drupal\node\Entity\Node $node */

    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['type' => 'energic_chargex_solution']);
    $node = reset($node);

    if (!$node) {
      return new JsonResponse(['error' => 'ChargeX solution page not found'], 404);
    }

    $sections = [];

    if ($node->hasField('field_chargex_sections')) {
      foreach ($node->get('field_chargex_sections')->referencedEntities() as $section) {
        $type = $section->bundle();

        if ($type === 'chargex_hero_section') {
          $sections['chargex_hero_section'] = $this->getChargexHeroSectionData($section);
        }
        elseif ($type === 'chargex_info_section') {
          $sections['chargex_info_section'] = $this->getChargexInfoSectionData($section);
        }
        elseif ($type === 'chargex_core_features') {
          $sections['chargex_core_features'] = $this->getChargexCoreFeaturesSectionData($section);
        }
        elseif ($type === 'chargex_app_section') {
          $sections['chargex_app_section'] = $this->getChargexAppSection($section);
        }
        elseif ($type === 'chargex_performance_section') {
            $sections['chargex_performance_section'] =
              $this->getChargexPerformanceSectionData($section);
        }
        elseif ($type === 'chargex_app_promotion_section') {
            $sections['chargex_app_promotion_section'] = $this->getChargexAppPromotionSectionData($section);
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
   * 🟢 chargex Hero Section Data
   */
  private function getChargexHeroSectionData($section) {

    // ✅ جلب عناصر الـ hero list لو موجودة
    $heroList = [];
    if ($section->hasField('field_chargex_hero_list') && !$section->get('field_chargex_hero_list')->isEmpty()) {
        foreach ($section->get('field_chargex_hero_list')->referencedEntities() as $item) {
            if ($item->hasField('field_chargex_hero_item') && !$item->get('field_chargex_hero_item')->isEmpty()) {
                $heroList[] = $item->get('field_chargex_hero_item')->value;
            }
        }
    }
    $data = [
      'title' => $section->get('field_chargex_hero_title')->value ?? '',
      'hero_list' => $heroList // ✅ ضيفنا البيانات الجديدة هنا
    ];

    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
  }



  private function getChargexInfoSectionData($section) {
    $imageUrl = null;
  
    if ($section->hasField('field_chargex_info_image') && !$section->get('field_chargex_info_image')->isEmpty()) {
      $media = $section->get('field_chargex_info_image')->entity;
      if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $imageUrl = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
  
    $data = [
      'title' => $section->get('field_chargex_info_title')->value ?? '',
      'description' => $section->get('field_chargex_info_description')->value ?? '',
      'image' => $imageUrl,
    ];
  
    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
  }


  private function getChargexCoreFeaturesSectionData($section) {

    $data = [
        'title1'=>$section->get('field_chargex_features_title1')->value ?? '',
        'title2'=>$section->get('field_chargex_features_title2')->value ?? '',
        'description'=>$section->get('field_chargex_features_desc')->value ?? '',
        'images' => [],
        'items' => [],
       
    ];
  
    // Check if section has feature items
    if (
        $section->hasField('field_chargex_feature_items') &&
        !$section->get('field_chargex_feature_items')->isEmpty()
    ) {
        foreach ($section->get('field_chargex_feature_items')->referencedEntities() as $feature) {
  
          
            // 🔹 Build clean feature object (NO type, NO list items)
            $data['items'][] = [
                'title'       => $feature->get('field_chargex_feature_title')->value ?? '',
                'description' => $feature->get('field_chargex_feature_desc')->value ?? '',
            ];
        }
    }
  

      // 🖼️ الصور المتعددة
  if ($section->hasField('field_chargex_feature_images') && !$section->get('field_chargex_feature_images')->isEmpty()) {
    foreach ($section->get('field_chargex_feature_images')->referencedEntities() as $media) {
      if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $data['images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
  }
    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
  }



  private function getChargexAppSection($section)
  {

    $imageUrl = null;
  
    if ($section->hasField('field_app_section_image') && !$section->get('field_app_section_image')->isEmpty()) {
      $media = $section->get('field_app_section_image')->entity;
      if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $imageUrl = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
    $data = [
        'title1'=>$section->get('field_app_section_title1')->value ?? '',
        'title2'=>$section->get('field_app_section_title2')->value ?? '',
        'description'=>$section->get('field_app_section_description')->value ?? '',
        'image' =>$imageUrl
       
    ];

    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);

  }


  private function getChargexPerformanceSectionData($section) {
    $data = [
      'title'       => $section->get('field_chargex_performance_title')->value ?? '',
      'description' => $section->get('field_chargex_performance_desc')->value ?? '',
      'items'       => $section->get('field_chargex_performance_items')->value ?? '',
      'images'      => [],
    ];
  
    // 🖼️ Multiple Performance Images
    if (
      $section->hasField('field_chargex_performance_images') &&
      !$section->get('field_chargex_performance_images')->isEmpty()
    ) {
      foreach ($section->get('field_chargex_performance_images') as $mediaRef) {
        $media = $mediaRef->entity;
  
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
  
          if ($file instanceof \Drupal\file\Entity\File) {
            $data['images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }
    }
  
    return $data;
  }
  


  private function getChargexAppPromotionSectionData($section) {
    $title = $section->get('field_app_promotion_title')->value ?? '';
    $description = $section->get('field_app_promotion_description')->value ?? '';

    // زر تحميل التطبيق
    $download_app_button = null;
    if ($section->hasField('field_download_app_button') && !$section->get('field_download_app_button')->isEmpty()) {
        $download_field = $section->get('field_download_app_button');
        $download_app_button = [
            'label' => $download_field->title ?? '',
            'url' => $download_field->uri ?? '',
        ];
    }

    // زر طلب الديمو
    $request_demo_button = null;
    if ($section->hasField('field_request_demo_button') && !$section->get('field_request_demo_button')->isEmpty()) {
        $demo_field = $section->get('field_request_demo_button');
        $request_demo_button = [
            'label' => $demo_field->title ?? '',
            'url' => $demo_field->uri ?? '',
        ];
    }

    $data = [
        'title' => $title,
        'description' => $description,
        'download_app_button' => $download_app_button,
        'request_demo_button' => $request_demo_button,
    ];

    return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
}


}
