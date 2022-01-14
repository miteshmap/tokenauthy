<?php

namespace Drupal\tokenauthy\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;


class AllowAuthTokenRequests implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    if ($request->query->has('authtoken')) {
      return self::DENY;
    }
    return NULL;
  }

}
