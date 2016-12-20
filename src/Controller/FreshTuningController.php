<?php


/**
 * @file
 *
 *
 * Contains \Drupal\nyumc_content_freshness\Controller\FreshTuningController.
 */

namespace Drupal\nyumc_content_freshness\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller routine to get content summary API.
 */
class FreshTuningController extends ControllerBase {

  /**
   *
   * Get node count based on months.
   *
   * @param Request $request
   * @param integer $months
   * @return \Symfony\Component\HttpFoundation\JsonResponse JsonResponse
   *
   */
  public function getContentStatusByMonths(Request $request, $months = 6) {

    $response = array();
    if(!filter_var($months, FILTER_VALIDATE_INT) === false) {
      $tuning_month = strtotime('-'.$months. 'months', time());
      $record = db_query("SELECT COUNT(nid) AS node_count FROM {node_field_data} WHERE status = 1 AND (changed <= ".$tuning_month.")")
        ->fetchAssoc();
      if (!empty($record)) {
        $response['status'] = JsonResponse::HTTP_OK;
        $response['responseMessage'] = 'Success';
        $response['data'] = $record;
        return new JsonResponse($response);
      } else {
        $response['status'] = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        $response['responseMessage'] = 'Database failure! Please contact administrator.';
        return new JsonResponse($response);
      }
    }

    $response['status'] = JsonResponse::HTTP_NOT_FOUND;
    $response['responseMessage'] = 'Requested API is not valid!';


    return new JsonResponse($response);
  }
}
