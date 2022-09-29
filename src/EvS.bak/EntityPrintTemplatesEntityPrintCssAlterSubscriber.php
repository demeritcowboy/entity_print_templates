<?php
namespace Drupal\entity_print_templates\EventSubscriber;

use Drupal\entity_print\Event\PrintCssAlterEvent;
use Drupal\entity_print\Event\PrintEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityPrintTemplatesEntityPrintCssAlterSubscriber implements EventSubscriberInterface {

  /**
   * React to a config object being saved.
   *
   * @param \Drupal\entity_print\Event\PrintCssAlterEvent $event
   *   Entity Print CSS alter event.
   */
  public function alterCss(PrintCssAlterEvent $event) {
    $entities = $event->getEntities();
    foreach ($entities as $entity) {
      if ($entity->getEntityTypeId() === 'view') {
        $event->getBuild()['#attached']['library'][] = 'entity_print_templates/custom_css';
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PrintEvents::CSS_ALTER => 'alterCss',
    ];
  }

}
