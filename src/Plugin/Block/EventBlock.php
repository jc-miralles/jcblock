<?php
namespace Drupal\jcblock\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime ; 
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface ;
use Drupal\taxonomy\Entity\Term;

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
	
	private function node2array($nd){
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
		return [
			"title" => $title,
			"link" => $link,
			"term_name" => $term_name,
			"dt1" => $dt1,
			"dt2" => $dt2,
		];
	}
    /**
     * {@inheritdoc}
     */
    public function build() {
		// Initialisation du tableau de variables à retourner au template
		$varnodes = array();
		// On cherche le node sur lequel on est
        $node = \Drupal::routeMatch()->getParameter('node');
		if ($node instanceof \Drupal\node\NodeInterface) {
			$nid = $node->id();
			// on vérifie le type du node (même si à ce jour on n'a que des 'event')
			$type = $node->bundle();
			// On regarde quel est le type d'événement de ce node
			$type_e = $node->get('field_event_type')->first()->getValue()['target_id'];
		}else{
			$type = "not a node";
		}
        // on filtre sur le type de note 'event'
		// On peut également procéder à ce filtre dans l'affichage du block en front mais si par la suite on ajoute un type de contenu on va éviter d'entrer dans ce processus.
        if($type == 'event'){
					
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
					// je récupère les variables à partir du node
					$varnodes[] = $this->node2array($nd);
				}
			}
			// Si on a moins que 3 nodes du même type à afficher
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
						// je récupère les variables à partir du node
						$varnodes[] = $this->node2array($nd);
					}
				}
			}
		}
		// Appel du template avec injection des variables récupérées.
		// Attachement de la librairie du module pour un design éventuel via la feuille de style 
		return [
		  '#theme' => 'jc_block_event',
		  '#varnodes' => $varnodes,
		  '#attached' => array(
				'library' => array(
				  'jcblock/jcblock',
				),
			  ),
		];
    }

}
