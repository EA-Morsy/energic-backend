<?php
namespace Drupal\energic_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;

class WhiteLabelSolutionController extends ControllerBase {

    protected $fileUrlGenerator;

    public function __construct() {
        $this->fileUrlGenerator = \Drupal::service('file_url_generator');
    }

    public function whiteLabelSolutions() {
        $node = \Drupal::entityTypeManager()
            ->getStorage('node')
            ->loadByProperties(['type' => 'white_label_solution']);
        $node = reset($node);

        if (!$node) {
            return new JsonResponse(['error' => 'White Label solution page not found'], 404);
        }

        $sections = [];

        if ($node->hasField('field_white_label_sections')) {
            foreach ($node->get('field_white_label_sections')->referencedEntities() as $section) {
                $type = $section->bundle();

                if ($type === 'white_label_hero_section') {
                    $sections['white_label_hero_section'] = $this->getWhiteLabelHeroSectionData($section);
                  }
                elseif ($type === 'white_label_features_section') {
                    $sections['white_label_features_section'] = $this->getWhiteLabelFeaturesSectionData($section);
                }
                
                  
            }
        }

        // تنظيف
        $sections = array_filter($sections, fn($v) => $v !== null && $v !== '' && $v !== []);

        return new JsonResponse([
            'white_label_id' => $node->id(),
            'title' => $node->getTitle(),
            'sections' => $sections,
        ]);
    }

    private function getWhiteLabelHeroSectionData($section) {
        $title1 = $section->get('field_white_label_hero_title1')->value ?? '';
        $title2 = $section->get('field_white_label_hero_title2')->value ?? '';
        $title3 = $section->get('field_white_label_hero_title3')->value ?? '';
        $desc   = $section->get('field_white_label_hero_desc')->value ?? '';
        
        // الزر (من نوع Link)
        $button = null;
        if ($section->hasField('field_white_label_hero_button') && !$section->get('field_white_label_hero_button')->isEmpty()) {
            $btn_field = $section->get('field_white_label_hero_button');
            $button = [
                'label' => $btn_field->title ?? '',
                'url'   => $btn_field->uri ?? '',
            ];
        }
    
        // الصورة
        $imageUrl = null;
        if ($section->hasField('field_white_label_hero_image') && !$section->get('field_white_label_hero_image')->isEmpty()) {
            $media = $section->get('field_white_label_hero_image')->entity;
            if ($media && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
                $file = $media->get('field_media_image')->entity;
                if ($file instanceof \Drupal\file\Entity\File) {
                    $imageUrl = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                }
            }
        }
    
        $data = [
            'title1' => $title1,
            'title2' => $title2,
            'title3' => $title3,
            'desc'   => $desc,
            'button' => $button,
            'image'  => $imageUrl,
        ];
    
        return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
    }
    


    private function getWhiteLabelFeaturesSectionData($section) {
        // جلب العنوان الرئيسي
        $title = $section->get('field_white_label_features_title')->value ?? '';
    
        // جلب كل الصور
        $images = [];
        if ($section->hasField('field_white_label_feature_images') && !$section->get('field_white_label_feature_images')->isEmpty()) {
            foreach ($section->get('field_white_label_feature_images')->referencedEntities() as $media) {
                if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
                    $file = $media->get('field_media_image')->entity;
                    if ($file instanceof \Drupal\file\Entity\File) {
                        $images[] = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                    }
                }
            }
        }
    
        // جلب عناصر المميزات (كنوع paragraph)
        $items = [];
        if (
            $section->hasField('field_white_label_features_items') &&
            !$section->get('field_white_label_features_items')->isEmpty()
        ) {
            foreach ($section->get('field_white_label_features_items')->referencedEntities() as $feature) {
                $items[] = [
                    'title'       => $feature->get('field_white_label_feature_title')->value ?? '',
                    'description' => $feature->get('field_white_label_feature_desc')->value ?? '',
                ];
            }
        }
      
        $data = [
            'title' => $title,
            'images' => $images,
            'items' => $items,
        ];
    
        return array_filter($data, fn($v) => $v !== null && $v !== '' && $v !== []);
    }
    
}
