<?php


/**
 * @file
 *
 *
 * Contains \Drupal\nyumc_content_freshness\Controller\ContentSummaryController.
 */

namespace Drupal\nyumc_content_freshness\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller routine to get content summary API.
 */
class ContentSummaryController extends ControllerBase {

  /**
   *
   * Get node count based on status(publish, unpublish, 6months).
   *
   * @param Request $request
   * @param string $status
   * @return \Symfony\Component\HttpFoundation\JsonResponse JsonResponse
   *
   */
  public function getContentSummary(Request $request) {

    try {
      $response = array();
      //Count of published post
      $content_publish_count = db_query("SELECT COUNT(nid) as node_count FROM {node_field_data} WHERE `status` = :status", array(':status' => 1))
        ->fetchAssoc();
      //Count of published post
      $content_unpublish_count = db_query("SELECT COUNT(nid) AS node_count FROM {node_field_data} WHERE `status` = :status", array(':status' => 0))
        ->fetchAssoc();
      //Count of posts not updated in the last 6 months
      $content_sixmonth_noupdate_count = db_query("SELECT COUNT(nid) AS node_count FROM {node_field_data} WHERE status = 1 AND (changed <= (UNIX_TIMESTAMP() - 15897600)) ", array(':status' => 1))
        ->fetchAssoc();
      //Count of posts updated recently
      $content_recent_update_count = db_query("SELECT COUNT(node_field_data.nid) AS node_count
        FROM node_field_data node_field_data
        LEFT JOIN history history ON node_field_data.nid = history.nid AND history.uid = '1'
        INNER JOIN comment_entity_statistics comment_entity_statistics ON node_field_data.nid = comment_entity_statistics.entity_id AND comment_entity_statistics.entity_type = 'node'
        WHERE (( (node_field_data.status = '1') AND ((history.timestamp IS NULL AND (node_field_data.changed > (UNIX_TIMESTAMP() - 2592000) OR comment_entity_statistics.last_comment_timestamp > (UNIX_TIMESTAMP() - 2592000)))
          OR history.timestamp < node_field_data.changed OR history.timestamp < comment_entity_statistics.last_comment_timestamp) ))")
        ->fetchAssoc();

      $content_updated_last_2months = db_query("SELECT COUNT(node_field_data.nid) AS node_count
        FROM node_field_data node_field_data
        INNER JOIN comment_entity_statistics comment_entity_statistics ON node_field_data.nid = comment_entity_statistics.entity_id AND comment_entity_statistics.entity_type = 'node'
        WHERE (( (node_field_data.status = '1') AND (node_field_data.changed >= 1479892045-5270400) ))")
        ->fetchAssoc();

      $content_updated_last_6months = db_query("SELECT COUNT(node_field_data.nid) AS node_count
        FROM node_field_data node_field_data
        INNER JOIN comment_entity_statistics comment_entity_statistics ON node_field_data.nid = comment_entity_statistics.entity_id AND comment_entity_statistics.entity_type = 'node'
        WHERE (( (node_field_data.status = '1') AND (node_field_data.changed >= 1479892045-15897600) ))")
        ->fetchAssoc();



      $response['status'] = JsonResponse::HTTP_OK;
      $response['responseMessage'] = 'Success';
      $response['data']['publish_count'] = $content_publish_count['node_count'];
      $response['data']['unpublish_count'] = $content_unpublish_count['node_count'];
      $response['data']['sixmonth_noupdate_count'] = $content_sixmonth_noupdate_count['node_count'];
      $response['data']['recent_update_count'] = $content_recent_update_count['node_count'];
      $response['data']['updated_in_sixmonths'] = $content_updated_last_6months['node_count'];
      $response['data']['updated_in_twomonths'] = $content_updated_last_2months['node_count'];

      return new JsonResponse($response);
    } catch(Exception $e) {
      $response['status'] = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
      $response['responseMessage'] = 'Database failure! Please contact administrator';

      return new JsonResponse($response);
    }
  }
}
