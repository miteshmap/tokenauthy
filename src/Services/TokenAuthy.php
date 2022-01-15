<?php

namespace Drupal\tokenauthy\Services;

use Drupal\Component\Utility\Random;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * TokenAuthy service.
 */
class TokenAuthy {

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->config = $config_factory->get('tokenauthy.settings');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Generate random string of token.
   *
   * @return string
   *   Return string of token.
   */
  public function generateToken() {
    $size = $this->config->get('token_size');
    $random = new Random();
    return $random->name($size);
  }

  /**
   * Get user by the given auth token.
   *
   * @param $token
   *   The token to find user.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function getUserByToken($token) {
    $storage = $this->entityTypeManager->getStorage('user');
    $query = $storage->getQuery()->condition('field_auth_token', $token);
    if ($result = $query->execute()) {
      $uid = reset($result);
      return $storage->load($uid);
    }
    return NULL;
  }


}
