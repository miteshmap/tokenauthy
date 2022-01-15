<?php

namespace Drupal\tokenauthy\EventSubscriber;

use Drupal\Core\Session\AccountEvents;
use Drupal\Core\Session\AccountSetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The token authy event subscriber.
 */
class TokenAuthyEventSubscriber implements EventSubscriberInterface {

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * construct.
   *
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   *   The session.
   */
  public function __construct(SessionInterface $session) {
    $this->session = $session;
  }

  /**
   * Sets the user session.
   */
  public function setUserSession(AccountSetEvent $event) {
    if (($account = $event->getAccount()) && !$this->session->has('uid')) {
      $this->session->set('uid', $account->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AccountEvents::SET_USER][] = ['setUserSession'];
    return $events;
  }
}
