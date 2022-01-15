<?php

namespace Drupal\tokenauthy\Authentication\Provider;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\tokenauthy\Services\TokenAuthy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Token based authentication provider.
 */
class AuthToken implements AuthenticationProviderInterface {

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The token service.
   *
   * @var \Drupal\tokenauthy\Services\TokenAuthy
   */
  protected $tokenAuthy;

  /**
   * Construct.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\tokenauthy\Services\TokenAuthy $tokenauthy
   *   The token authy service.
   */
  public function __construct(
    SessionInterface $session,
    EntityTypeManagerInterface $entityTypeManager,
    TokenAuthy $tokenauthy
  ) {
    $this->session = $session;
    $this->entityTypeManager = $entityTypeManager;
    $this->tokenAuthy = $tokenauthy;
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
    // Get the provided token.
    if ($token = $request->query->get('authtoken')) {
      // Find the user by given token.
      if ($user = $this->tokenAuthy->getUserByToken($token)) {
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


}
