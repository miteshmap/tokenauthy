<?php

namespace Drupal\tokenauthy\Stackmiddleware;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\SessionConfigurationInterface;
use Drupal\tokenauthy\Services\TokenAuthy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Middleware for the tokenauthy module.
 */
class AuthTokenMiddleware implements HttpKernelInterface {

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * The session configuration.
   *
   * @var \Drupal\Core\Session\SessionConfigurationInterface
   */
  protected $sessionConfiguration;


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
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   * @param \Drupal\Core\Session\SessionConfigurationInterface $session_configuration
   *   The session configuration.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\tokenauthy\Services\TokenAuthy $tokenauthy
   *   The token authy service.
   */
  public function __construct(
    HttpKernelInterface $http_kernel,
    SessionInterface $session,
    SessionConfigurationInterface $session_configuration,
    EntityTypeManagerInterface $entityTypeManager,
    TokenAuthy $tokenauthy
  ) {
    $this->httpKernel = $http_kernel;
    $this->session = $session;
    $this->sessionConfiguration = $session_configuration;
    $this->entityTypeManager = $entityTypeManager;
    $this->tokenAuthy = $tokenauthy;
  }


  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    if (!$request->query->has('authtoken')
      || $type != self::MASTER_REQUEST
      || (PHP_SAPI === 'cli')
      || ($request->hasSession() && $request->getSession()->has('uid'))
    ) {
      return $this->httpKernel->handle($request, $type, $catch);
    }
    else {
      // Get the provided token.
      if ($token = $request->query->get('authtoken')) {
        // Find the user by given token.
        if ($user = $this->tokenAuthy->getUserByToken($token)) {
          // Check if the user is not blocked and is authenticated.
          if ($user->isBlocked() && $user->isAuthenticated()) {
            $response = new Response();
            $response->headers->add([
              'WWW-Authenticate' => 'Unauthorised user.',
            ]);
            $response->setStatusCode(401);
            return $response;
          }
          $request->getSession()->set('uid', $user->id());
          $options = $this->sessionConfiguration->getOptions($request);
          $request->cookies->set($options['name'], '');
        }
      }
      return $this->httpKernel->handle($request, $type, $catch);
    }
  }

}
