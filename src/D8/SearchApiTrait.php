<?php

namespace DrevOps\BehatSteps\D8;

use Drupal\node\Entity\Node;

/**
 * Trait SearchApiTrait.
 *
 * @package DrevOps\BehatSteps\D8
 */
trait SearchApiTrait {

  use ContentTrait;

  /**
   * Index a node with all Search API indices.
   *
   * @When I index :type :title for search
   */
  public function searchApiIndexContent($type, $title) {
    $nids = $this->contentNodeLoadMultiple($type, [
      'title' => $title,
    ]);

    if (empty($nids)) {
      throw new \RuntimeException(sprintf('Unable to find %s page "%s"', $type, $title));
    }

    ksort($nids);
    $nid = end($nids);
    $node = Node::load($nid);

    search_api_entity_insert($node);

    $this->searchApiDoIndex(1);
  }

  /**
   * Index a number of items across all active Search API indices.
   *
   * @When I index :limit Search API items
   * @When I index 1 Search API item
   */
  public function searchApiDoIndex($limit = 1) {
    $index_storage = \Drupal::entityTypeManager()->getStorage('search_api_index');
    /** @var \Drupal\search_api\IndexInterface[] $indexes */
    $indexes = $index_storage->loadByProperties(['status' => TRUE]);
    if (!$indexes) {
      return;
    }

    foreach ($indexes as $index_id => $index) {
      $index->indexItems($limit);
    }
  }

}
