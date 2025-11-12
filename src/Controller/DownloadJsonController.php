<?php

namespace Drupal\stressor_module\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

class DownloadJsonController extends ControllerBase {

    protected $moduleHandler;

    public function __construct(ModuleHandlerInterface $moduleHandler) {
        $this->moduleHandler = $moduleHandler;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('module_handler')
        );
    }

    public function downloadJson($ids) {
        // Split and sanitize the IDs
        $ids_array_dirty = explode(',', $ids);
        $ids_array = array_map(function ($number) {
            return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
        }, $ids_array_dirty);

        $data = [];

        // Loop through each node ID
        foreach ($ids_array as $node_id) {
            $node = Node::load($node_id);

            if ($node && $node->getType() === "stressor_response") {
                
                // Gather field data
                $stressor_response = [
                    'id' => $node->id(),
                    'title' => $node->getTitle(),
                    'stressor_name' => $node->get('field_stressor_name')->value,
                    'stressor_units' => $node->get('field_stressor_units')->value,
                    'specific_stressor_metric' => $node->get('field_specific_stressor_metric')->value,
                    'species_common_name' => $node->get('field_species_common_name')->value,
                    'species_latin' => $node->get('field_species_latin_')->value,
                    'genus_latin' => $node->get('field_genus')->value,
                    'geography' => $node->get('field_geography')->value,
                    'activity' => $node->get('field_activity')->value,
                    'season' => $node->get('field_season')->value,
                    'description' => [
                        'overview' => $node->get('field_description')->value,
                        'function_derivation' => $node->get('field_function_derivation')->value,
                        'transferability_of_function' => $node->get('field_transferability_of_functio')->value,
                        'source_of_stressor_data1' => $node->get('field_source_of_stressor_data')->value,
                        'source_of_stressor_data2' => $node->get('field_stressor_magnitude_data')->value,
                        'pathways_of_effect' => $node->get('field_poe_chain')->value
                    ],
                    'citations' => [
                        'citation_text' => [],
                        'citation_links' => []
                    ],
                    'images' => [],
                    'life_stages' => [],
                    'citation_link' => [],
                    'covariates_dependencies' => [],
                    'stressor_scale' => $node->get('field_stressor_scale')->value,
                    'function_type' => $node->get('field_function_type')->value,
                    'csv_data' => [],
                ];


                $cit_texts = $node->get('field_citation_s_')->getValue();
                foreach ($cit_texts as $text_value) {
                    // Get the text content.
                    $citation = isset($text_value['value']) ? $text_value['value'] : '';
                    $stressor_response['citations']['citation_text'][] = $citation;
                }

                $links = $node->get('field_reference_link')->getValue();
                foreach ($links as $link) {
                    if ($referenced_entity = $link) {
                        $stressor_response['citations']['citation_links'][] = [
                           'title' => $link['title'],
                           'url' => $link['uri']
                        ];
                    }
                }

                $images = $node->get('field_images')->getValue();
                foreach ($images as $image) {
                    
                    if (isset($image['target_id'])) {
                        // Load the file entity using the target_id.
                        $file = \Drupal\file\Entity\File::load($image['target_id']);
                        
                        if ($file) {
                            // Get the external URL.
                            $file_uri = $file->getFileUri();
                            $external_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file_uri);
                            $stressor_response['images'][] = [
                                'image_caption' => $image['alt'],
                                'image_url' => $external_url
                             ];
                            
                        }
                    }
                }


                // Get life stages
                $life_stages = $node->get('field_life_stage');
                foreach ($life_stages as $item) {
                    if ($referenced_entity = $item->entity) {
                        $stressor_response['life_stages'][] = $referenced_entity->label();
                    }
                }

                // Get covariates
                $covar = $node->get('field_known_covariates_and_depen');
                foreach ($covar as $item) {
                    if ($referenced_entity = $item->entity) {
                        $stressor_response['covariates_dependencies'][] = $referenced_entity->label();
                    }
                }

                // Load CSV data
                $csv_field = $node->get('field_stressor_response_csv_data');
                $csv_file = $csv_field->entity;

                if ($csv_file) {
                    $file_uri = $csv_file->getFileUri();
                    if (($handle = fopen($file_uri, 'r')) !== false) {
                        $row_index = 0; // Track header vs data row
                        while (($data_row = fgetcsv($handle)) !== false) {
                            if($row_index == 0) {
                                // Treat first row as headers
                                $stressor_response['csv_data'][] = $data_row;
                            } else {
                                // Subsequent rows: parse numeric data
                                $numeric_row = array_map(function($value) {
                                    // Check if the value is numeric; if yes, cast to float or integer
                                    return is_numeric($value) ? (strpos($value, '.') !== false ? (float)$value : (int)$value) : $value;
                                }, $data_row);
                              $stressor_response['csv_data'][] = $numeric_row;
                            }
                            $row_index++;
                        }
                        fclose($handle);
                    }
                }

                // Append to the main data array
                $data[] = $stressor_response;
            }
        }

        // Return JSON response
        $response = new JsonResponse($data);
        $response->headers->set('Content-Disposition', 'attachment; filename="stressor_responses.json"');
        return $response;
    }
}
