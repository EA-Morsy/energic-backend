<?php
namespace Drupal\energic_contact\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;

class ContactApiController {

  public function submit(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    // ✅ التحقق من وجود البيانات المطلوبة
    if (
      empty($data['name']) ||
      empty($data['email']) ||
      empty($data['phone'])
    ) {
      return new JsonResponse([
        'status' => 'error',
        'message' => 'Missing required fields.',
      ], 400);
    }

    try {

      if (!\Drupal::entityTypeManager()->getStorage('node_type')->load('contact_message')) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Content type contact_message does not exist.',
        ], 500);
    }
    
      // ✅ إنشاء node لحفظ الرسالة
      $node = Node::create([
        'type' => 'contact_message',
        'title' => $data['name'],
        'field_email' => $data['email'],
        'field_phone' => $data['phone'],
        'field_message' => $data['message'] ?? '',
        'uid' => 0, // 0 للـ anonymous

      ]);
      $node->save();

      // ✅ (اختياري) إرسال إيميل تنبيه
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'energic_contact';
      $key = 'contact_message';
      $to = 'info@energic.com';
      $params = [
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'message' => $data['message'] ?? '',
      ];
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $mailManager->mail($module, $key, $to, $langcode, $params);

      return new JsonResponse([
        'status' => 'success',
        'message' => 'Message sent successfully!',
      ], 200);

    } catch (\Exception $e) {
      return new JsonResponse([
        'status' => 'error',
        'message' => $e->getMessage(),
      ], 500);
    }
  }
}
