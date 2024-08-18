<?php

namespace Drupal\jcblock\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime ; 
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface ;
use Drupal\taxonomy\Entity\Term;

/* use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\image\Entity\ImageStyle; */

/**
 * Provides a 'Event' Block.
 *
 * @Block(
 *   id = "jc_event_block",
 *   admin_label = @Translation("Autres événements"),
 *   category = @Translation("Custom JC"),
 * )
 */
class EventBlock extends BlockBase {
	private function node2html($nd){
		// Extraction, composition des éléments à afficher 
		// Arbitrairement titre, type et dates
		$link = $nd->toUrl()->toString();
		$dates = $nd->get('field_date_range')->first()->getValue();
		$type_e = $nd->get('field_event_type')->first()->getValue()['target_id'];
		$term = \Drupal\taxonomy\Entity\Term::load($type_e);
		$term_name = $term->get('name')->value;
		$title = $nd->getTitle();
		$dt1 = date('d/m/Y H:i',strtotime($dates["value"]));
		$dt2 = date('d/m/Y H:i',strtotime($dates["end_value"]));
		// Composition de l'élément à afficher 
		$html = "<div class='link-block'>";
		$html .= "<a href='$link'>$title</a>";
		$html .= "<div class='term-block'>$term_name</div>";
		$html .= "<div class='date-block'>$dt1 / $dt2</div>";
		$html .= "</div>";
		return $html;
	}
    /**
     * {@inheritdoc}
     */
    public function build() {
		// On cherche le node sur lequel on est
        $n = \Drupal::routeMatch()->getParameter('node');
        $nid = $n->id();
        // on vérifie le tupe du node (même si à ce jour on n'a que des 'event'
        $type = $n->bundle();
		// On regarde quel est le type d'événement de ce node
		$type_e = $n->get('field_event_type')->first()->getValue()['target_id'];
        // on filtre sur le type de note 'event'
		// On peut également procéder à ce filtre dans l'affichage du block en front mais si par la suite on ajoute un type de contenu on va éviter d'entrer dans ce processus.
        if($type == 'event'){
			// $html est le code qu'on va envoyer au block
			$html = "";
			
			$storage  = \Drupal::service( 'entity_type.manager' )-> getStorage( 'node' );
			
			// on créé une entité de type DrupalDateTime pour les querys
			$date = new DrupalDateTime(); 
			$date -> setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
			$date = $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
			
			// Première requête : les nodes du même type, saufe ce node là, dont la date de fin  n'est pas passée (max 3)
			$qnodes = \Drupal::entityQuery('node')
			->accessCheck(TRUE)
			->condition('type', 'event')
			->condition('field_event_type', $type_e)
			->condition ( 'field_date_range.end_value',$date,'>' )
			->condition ( 'status',1 )
			->condition('nid', $nid ,'!=')
			->sort ( 'field_date_range.value','ASC')
			->range(0, 3)
			->execute();
			$c = count($qnodes);
			if($c > 0){
				$nodes = $storage->loadMultiple($qnodes);
				foreach($nodes as $nd){
					// je "construit" les éléments du dom à partir du node
					$html .= $this->node2html($nd);
				}
			}
			if($c < 3){
				$r = 3 - $c;
				// Seconde requête : les nodes d'un autre type, dont la date de fin  n'est pas passée (max 3 moins les nodes précédents)
				$qnodes2 = \Drupal::entityQuery('node')
				->accessCheck(TRUE)
				->condition('type', 'event')
				->condition('field_event_type', $type_e, "!=")
				->condition ( 'field_date_range.end_value',$date,'>' )
				->condition ( 'status',1 )
				->sort ( 'field_date_range.value','ASC')
				->range(0, $r)
				->execute();
				$c2 = count($qnodes2);
				if($c2 > 0){
					$nodes = $storage->loadMultiple($qnodes2);
					foreach($nodes as $nd){
						// je "construit" les éléments du dom à partir du node
						$html .= $this->node2html($nd);
					}
				}
			}
		}
		
        return [
            '#markup' => $html,
			'#cache' => [
                'max-age' => 0,
            ],
			'#attached' => array(
				'library' => array(
				  'jcblock/jcblock',
				),
			  ),
        ];
    }

}
