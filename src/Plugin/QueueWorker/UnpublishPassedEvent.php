<?php

namespace Drupal\jcblock\Plugin\QueueWorker;

use Drupal;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unpublish passed events.
 *
 * @QueueWorker(
 *   id = "module_jcblock_UnpublishPassedEvent",
 *   title = @Translation("Dépublier les événements passés"),
 *   cron = {"time" = 180}
 * )
 */
class UnpublishPassedEvent extends QueueWorkerBase implements ContainerFactoryPluginInterface {
	
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($node) {
	$node->set('status', 0);
	$title = $node->getTitle();
	$node->save();
	$text = "Evénements $title dépubliés";
	\Drupal::logger('jcblock')->info($text);
  }
  
}