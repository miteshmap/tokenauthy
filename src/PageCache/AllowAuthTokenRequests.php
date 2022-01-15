<?php

namespace Drupal\tokenauthy\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * By pass page cache when authtoken query parameter found in url.
 */
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
