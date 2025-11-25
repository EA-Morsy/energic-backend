<?php

namespace Drupal\energic_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AboutUsController extends ControllerBase {

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
   * API endpoint: /api/about-us
   */
  public function aboutUs() {
    // جلب نود الـ about_us
    /** @var \Drupal\node\Entity\Node $node */

    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['type' => 'about_us']);
    $node = reset($node);

    if (!$node || $node->getType() !== 'about_us') {
      return new JsonResponse(['error' => 'About Us page not found'], 404);
    }

    $sections = [];

    if ($node->hasField('field_about_us_sections')) {
      foreach ($node->get('field_about_us_sections')->referencedEntities() as $section) {
        $type = $section->bundle();

        if ($type === 'hero_section') {
          $sections['hero_section'] = $this->getHeroSectionData($section);
        }
        elseif ($type === 'about_company_section') {
          $sections['about_company_section'] = $this->getAboutCompanySectionData($section);
        }
        elseif ($type === 'our_values_section') {
          $sections['our_values_section'] = $this->getOurValuesSectionData($section);
        }
        elseif ($type === 'teams_section') {
          $sections['teams_section'] = $this->getTeamsSectionData($section);
        }
        elseif ($type === 'global_reach_section') {
          $sections['global_reach_section'] = $this->getGlobalReachSectionData($section);
        }
        elseif ($type === 'our_solutions_section') {
          $sections['our_solutions_section'] = $this->getOurSolutionsSectionData($section);
        }

        // لاحقاً ممكن تضيف سكاشنز جديدة
      }
    }

    // return new JsonResponse([
    //   'about_us_id' => $node->id(),
    //   'title' => $node->getTitle(),
    //   'sections' => $sections,
    // ]);
    
  // 👇 هنا بتبني الـ response الفعلي
  $response = [
    'about_us_id' => $node->id(),
    'title' => $node->getTitle(),
    'sections' => $sections,
  ];

  // ✅ تشيلي أي keys فاضية
  $response = array_filter($response, fn($v) => $v !== null && $v !== '' && $v !== []);

  return new JsonResponse($response);
  }

  // -------------------------------
  // هنا هتعملي private functions لكل سكاشن زي اللي عندك في landing page
  // getHeroSectionData(), getTeamSectionData(), getHistorySectionData()...
  // -------------------------------



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

    // ✅ هنا بننظف الداتا من القيم الفاضية فقط جوه الهيرو
    $data = array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);

    return $data;
  }

  /**
 * 🏢 About Company Section Data
 */
private function getAboutCompanySectionData($section) {
  $data = [
    'title' => $section->get('field_about_company_title')->value ?? '',
    'description' => $section->get('field_about_company_description')->value ?? '',
    'images' => [],
    'items' => [],
  ];

  // 🖼️ Multiple Images
  if ($section->hasField('field_about_company_images') && !$section->get('field_about_company_images')->isEmpty()) {
    foreach ($section->get('field_about_company_images')->referencedEntities() as $media) {
      if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $data['images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
  }

  // ✅ جلب عناصر الـ items
  if ($section->hasField('field_about_company_items') && !$section->get('field_about_company_items')->isEmpty()) {
    foreach ($section->get('field_about_company_items')->referencedEntities() as $item) {
      $iconUrl = '';

      // 🔹 إذا فيه أيقونة مرفقة
      if ($item->hasField('field_about_company_item_icon') && !$item->get('field_about_company_item_icon')->isEmpty()) {
        $media = $item->get('field_about_company_item_icon')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            $iconUrl = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $itemData = [
        'title' => $item->get('field_about_company_item_title')->value ?? '',
        'description' => $item->get('field_about_company_item_descrip')->value ?? '',
        'icon' => $iconUrl,
      ];

      // 🔹 حذف القيم الفاضية جوه الـ item
      $itemData = array_filter($itemData, fn($v) => $v !== null && $v !== '' && $v !== []);

      $data['items'][] = $itemData;
    }
  }


  // ✅ حذف أي مفاتيح فاضية (null, '', [])
  $data = array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);

  return $data;
}


private function getOurValuesSectionData($section) {
  $data = [
    'title' => $section->get('field_our_values_title')->value ?? '',
    'description' => $section->get('field_our_values_description')->value ?? '',
    'images' => [],
    'background_image' => '',
  ];

  // 🖼️ Multiple images
  if ($section->hasField('field_our_values_images') && !$section->get('field_our_values_images')->isEmpty()) {
    foreach ($section->get('field_our_values_images')->referencedEntities() as $media) {
      if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file instanceof \Drupal\file\Entity\File) {
          $data['images'][] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
        }
      }
    }
  }

  // 🌄 Background image (واحدة فقط)
  if ($section->hasField('field_our_values_background') && !$section->get('field_our_values_background')->isEmpty()) {
    $media = $section->get('field_our_values_background')->entity;
    if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
      $file = $media->get('field_media_image')->entity;
      if ($file instanceof \Drupal\file\Entity\File) {
        $data['background_image'] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }
  }

  // ✅ حذف أي مفاتيح فاضية (null, '', [])
  $data = array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);

  return $data;
}



private function getTeamsSectionData($section) {
  $data = [
    'items' => [],
  ];

  // ✅ جلب عناصر الفريق (Team Items)
  if ($section->hasField('field_team_items') && !$section->get('field_team_items')->isEmpty()) {
    foreach ($section->get('field_team_items')->referencedEntities() as $item) {
      $teamItem = [
        'name' => $item->get('field_team_item_name')->value ?? '',
        'role' => $item->get('field_team_item_role')->value ?? '',
        'description' => $item->get('field_team_item_description')->value ?? '',
        'image' => '',
      ];

      // 🖼️ صورة العضو
      if ($item->hasField('field_team_item_image') && !$item->get('field_team_item_image')->isEmpty()) {
        $media = $item->get('field_team_item_image')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            $teamItem['image'] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      // ❌ حذف المفاتيح الفاضية لكل عضو
      $teamItem = array_filter($teamItem, fn($v) => $v !== null && $v !== '' && $v !== []);

      $data['items'][] = $teamItem;
    }
  }

  // ❌ حذف المفاتيح الفاضية لو السكشن كله فاضي
  $data = array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);

  return $data;
}


private function getGlobalReachSectionData($section) {
  $data = [
    'title' => $section->get('field_global_reach_title')->value ?? '',
    'description' => $section->get('field_global_reach_description')->value ?? '',
    'image' => '',
    'stats' => [],
    'locations' => [],
  ];

  // 🌍 صورة الكرة الأرضية
  if ($section->hasField('field_global_reach_image') && !$section->get('field_global_reach_image')->isEmpty()) {
    $media = $section->get('field_global_reach_image')->entity;
    if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
      $file = $media->get('field_media_image')->entity;
      if ($file instanceof \Drupal\file\Entity\File) {
        $data['image'] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
      }
    }
  }

  // 📊 الإحصائيات العامة
  if ($section->hasField('field_global_reach_stats') && !$section->get('field_global_reach_stats')->isEmpty()) {
    foreach ($section->get('field_global_reach_stats')->referencedEntities() as $item) {
      $entry = [
        'label' => $item->get('field_global_reach_stat_label')->value ?? '',
        'value' => $item->get('field_global_reach_stat_value')->value ?? '',
        'icon' => '',
      ];

      if ($item->hasField('field_global_reach_stat_icon') && !$item->get('field_global_reach_stat_icon')->isEmpty()) {
        $media = $item->get('field_global_reach_stat_icon')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            $entry['icon'] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $entry = array_filter($entry, fn($v) => $v !== null && $v !== '' && $v !== []);
      $data['stats'][] = $entry;
    }
  }

  // 📍 المواقع على الخريطة
  if ($section->hasField('field_global_reach_locations') && !$section->get('field_global_reach_locations')->isEmpty()) {
    foreach ($section->get('field_global_reach_locations')->referencedEntities() as $item) {
      $entry = [
        'name' => $item->get('field_global_reach_location_name')->value ?? '',
        'value' => $item->get('field_global_reach_location_valu')->value ?? '',
        'flag' => '',
      ];

      if ($item->hasField('field_global_reach_location_flag') && !$item->get('field_global_reach_location_flag')->isEmpty()) {
        $media = $item->get('field_global_reach_location_flag')->entity;
        if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            $entry['flag'] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
          }
        }
      }

      $entry = array_filter($entry, fn($v) => $v !== null && $v !== '' && $v !== []);
      $data['locations'][] = $entry;
    }
  }

  $data = array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
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


}
