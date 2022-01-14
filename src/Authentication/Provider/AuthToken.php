<?php

namespace Drupal\tokenauthy\Authentication\Provider;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Token based authentication provider.
 */
class AuthToken implements AuthenticationProviderInterface {

  protected $session;

  protected $entityTypeManager;

  /**
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   */
  public function __construct(
    SessionInterface $session,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->session = $session;
    $this->entityTypeManager = $entityTypeManager;
  }


  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    return $request->query->has('authtoken')
      && empty($this->session->get('uid'));
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {
    // Get the provided key.
    if ($key = $request->query->get('authtoken')) {
      // Find the user by given key.
      if ($user = $this->getUserByKey($key)) {
        // Check if the user is not blocked and is authenticated.
        if ($user->isBlocked() && $user->isAuthenticated()) {
          throw new AccessDeniedHttpException(
            t(
              '%name is blocked or has not been activated yet.',
              ['%name' => $user->label()]
            ));
        }
        return $user;
      }
    }
    return NULL;
  }

  /**
   * Get user by the given auth token key.
   *
   * @param $key
   *   The key to find user.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  protected function getUserByKey($key) {
    $storage = $this->entityTypeManager->getStorage('user');
    $query = $storage->getQuery()->condition('field_auth_token', $key);
    if ($result = $query->execute()) {
      $uid = reset($result);
      return $storage->load($uid);
    }
    return NULL;
  }
}
