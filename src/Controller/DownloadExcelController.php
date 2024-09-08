<?php

/**
 * @file
 * 
 */

namespace Drupal\stressor_module\Controller;

use Drupal\node\Entity\Node;
use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\media\Entity\Media;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DownloadExcelController extends ControllerBase
{

    public function content()
    {
        return [
            '#type' => 'markup',
            '#markup' => $this->t('Hello, World!'),
        ];
    }


    protected $moduleHandler;

    public function __construct(ModuleHandlerInterface $moduleHandler)
    {
        $this->moduleHandler = $moduleHandler;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('module_handler')
        );
    }

    public function downloadExcelFull($ids)
    {

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Add some data to the spreadsheet
        $worksheet_title = 'Main';
        // Create a new worksheet
        // $spreadsheet->createSheet()->setTitle($worksheet_title);
        // Update the title of the existing DEFAULT worksheet
        $spreadsheet->getActiveSheet()->setTitle($worksheet_title);
        // Set the newly created sheet as the active sheet
        $spreadsheet->setActiveSheetIndexByName($worksheet_title);

        // Input values for headers
        // Stressors, Stressor_cat, Interaction, Linked, Stress_Scale, Function, Life_stages, Parameters, Units, Model

        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Stressors');
        $spreadsheet->getActiveSheet()->setCellValue('B1', 'Stressor_cat');
        $spreadsheet->getActiveSheet()->setCellValue('C1', 'Interaction');
        $spreadsheet->getActiveSheet()->setCellValue('D1', 'Linked');
        $spreadsheet->getActiveSheet()->setCellValue('E1', 'Stress_Scale');
        $spreadsheet->getActiveSheet()->setCellValue('F1', 'Function');
        $spreadsheet->getActiveSheet()->setCellValue('G1', 'Life_stages');
        $spreadsheet->getActiveSheet()->setCellValue('H1', 'Parameters');
        $spreadsheet->getActiveSheet()->setCellValue('I1', 'Units');
        $spreadsheet->getActiveSheet()->setCellValue('J1', 'Model');
        $spreadsheet->getActiveSheet()->setCellValue('K1', 'ID');
        $spreadsheet->getActiveSheet()->setCellValue('L1', 'TITLE');
        $spreadsheet->getActiveSheet()->setCellValue('M1', 'METRIC');
        $spreadsheet->getActiveSheet()->setCellValue('N1', 'COMMON NAME');
        $spreadsheet->getActiveSheet()->setCellValue('O1', 'GENUS');
        $spreadsheet->getActiveSheet()->setCellValue('P1', 'SPECIES');
        $spreadsheet->getActiveSheet()->setCellValue('Q1', 'ACTIVITY');
        $spreadsheet->getActiveSheet()->setCellValue('R1', 'SEASON');
        $spreadsheet->getActiveSheet()->setCellValue('S1', 'CITATION');
        $spreadsheet->getActiveSheet()->setCellValue('T1', 'DESCRIPTION');
        $spreadsheet->getActiveSheet()->setCellValue('U1', 'GEOGRAPHY');
        
        // Split the IDs into an array
        $ids_array_dirty = explode(',', $ids);
        // Sanitize the IDs
        $ids_array = array_map(function ($number) {
            return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
        }, $ids_array_dirty);

        // Excel main 
        $rowNumber = 2; // Start from the first row in the sheet
        
        // Loop through the node IDs in the url
        foreach ($ids_array as $node_id) {
            $node = Node::load($node_id);

            if ($node && $node->getType() == "stressor_response") {

                // Node is of the desired content type
                $filtered_nodes[] = $node;
                // Get the field data
                // field_stressor_name
                $field_title = $node->getTitle();
                $field_id = $node->id();
                $field_stressor_name = $node->get('field_stressor_name')->value;
                $field_stressor_units = $node->get('field_stressor_units')->value;
                $field_specific_stressor_metric = $node->get('field_specific_stressor_metric')->value;
                $field_species_common_name = $node->get('field_species_common_name')->value;
                $field_species_latin_ = $node->get('field_species_latin_')->value;
                $field_genus = $node->get('field_genus')->value;
                $field_activity = $node->get('field_activity')->value;
                $field_season = $node->get('field_season')->value;
                $field_citation = $node->get('field_citation_s_')->value;
                $field_description = $node->get('field_description')->value;
                $field_geography = $node->get('field_geography')->value;
                $field_vital_rate_process_ = $node->get('field_vital_rate_process_')->value;

                // Function type
                $field_stressor_scale = [];
                $list_items = $node->get('field_stressor_scale')->getValue();
                foreach ($list_items as $item) {
                    $value = $item['value'];
                    // Now you can use $value as needed
                    $field_stressor_scale[] = $value;
                }
                $field_stressor_scale = implode('; ', $field_stressor_scale);


                // field_known_covariates_and_depen
                $field_known_covariates_and_depen = [];
                if ($node->hasField('field_known_covariates_and_depen')) {
                    $text_items = $node->get('field_known_covariates_and_depen')->getValue();
                    foreach ($text_items as $item) {
                        $text_value = $item['value'];
                        // Now you can use $text_value as needed
                        $field_known_covariates_and_depen[] = $text_value;
                    }
                }
                $field_known_covariates_and_depen = implode('; ', $field_known_covariates_and_depen);

                // Function type
                $field_function_type = [];
                $list_items = $node->get('field_function_type')->getValue();
                foreach ($list_items as $item) {
                    $value = $item['value'];
                    // Now you can use $value as needed
                    $field_function_type[] = $value;
                }
                $field_function_type = implode('; ', $field_function_type);

                // Get the links
                $field_reference_link = [];
                foreach ($node->get('field_reference_link') as $link_item) {
                    $url = $link_item->getUrl()->toString();
                    // Process each $url
                    $field_reference_link[] = $url;
                }
                $field_reference_link = implode('; ', $field_reference_link);

                // Entity reference tax terms
                $field_life_stage = [];
                $eris = $node->get('field_life_stage');
                foreach ($eris as $item) {
                    $referenced_entity = $item->entity;
                    if ($referenced_entity) {
                        $field_life_stage[] = $referenced_entity->label();
                    }
                }
                $field_life_stage = implode('; ', $field_life_stage);

                $field_stressor_name = $field_stressor_name ?? '';
                $new_name = substr($field_stressor_name,0,30);


                $add_data = array(
                    $new_name,
                    $new_name,
                    'NA',
                    'NA',
                    $field_stressor_scale,
                    $field_function_type,
                    $field_life_stage,
                    $field_vital_rate_process_,
                    $field_stressor_units,
                    'All',
                    $field_id,
                    $field_title,
                    $field_specific_stressor_metric,
                    $field_species_common_name,
                    $field_genus,
                    $field_species_latin_,
                    $field_activity,
                    $field_season,
                    $field_citation,
                    $field_description,
                    $field_geography
                );

                // Add data to Excel
                $columnLetter = 'A';
                foreach ($add_data as $cellValue) {
                    $spreadsheet->getActiveSheet()->setCellValue($columnLetter++ . $rowNumber, $cellValue);
                }
                // Increment the row number
                $rowNumber++;
            }
        }

        // Add on the underlying data - only work with filtered nodes now
        foreach ($ids_array as $node_id) {
            $node = Node::load($node_id);

            if ($node && $node->getType() == "stressor_response") {
                
                $field_stressor_name = $node->get('field_stressor_name')->value;
                $new_name = substr($field_stressor_name,0,30);

                // Load the csv data to a php array
                $csv_field = $node->get('field_stressor_response_csv_data');
                $csv_file = $csv_field->entity;

                // Add on the series title

                if (isset($csv_file)) {

                    $file_uri = $csv_file->getFileUri();
                    $handle = fopen($file_uri, 'r');

                    // $new_name
                    // Add some data to the spreadsheet
                    $worksheet_title = $new_name;
                    $spreadsheet->createSheet()->setTitle($worksheet_title);
                    // Set the newly created sheet as the active sheet
                    $spreadsheet->setActiveSheetIndexByName($worksheet_title);
                    $rowNumber_dat = 1;
                    
                    if ($handle !== FALSE) {

                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $columnLetter = 'A';
                            foreach ($data as $cellValue) {
                                $spreadsheet->getActiveSheet()->setCellValue($columnLetter++ . $rowNumber_dat, $cellValue);
                            }
                            $rowNumber_dat++;
                        }
                        fclose($handle);
                    }

                }
                

            }
        }




        // Create the Excel file
        $writer = new Xlsx($spreadsheet);

        // Capture output
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        // Create response object
        $response = new Response();
        $response->setContent($content);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="SRDatabase.xlsx"');

        return $response;




        // Get the query string
        $sanitized_html = Html::escape($ids);
        // Split the IDs into an array
        $ids_array_dirty = explode(',', $ids);
        // Sanitize the IDs
        $ids_array = array_map(function ($number) {
            return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
        }, $ids_array_dirty);

        // Gather data from the nodes
        $filtered_nodes = [];
        $sr_plot_data = [];
        $highcharts_data = [];
        $x_axis_lab_parts = [];
        $hc_series_multiple = [];

        // Loop through the node IDs in the url
        foreach ($ids_array as $node_id) {
            $node = Node::load($node_id);

            if ($node && $node->getType() == "stressor_response") {

                // Node is of the desired content type
                $filtered_nodes[] = $node;
                // Get the field data
                // field_stressor_name
                $field_title = $node->getTitle();
                $field_id = $node->id();
                $field_stressor_name = $node->get('field_stressor_name')->value;
                $field_stressor_units = $node->get('field_stressor_units')->value;
                $field_specific_stressor_metric = $node->get('field_specific_stressor_metric')->value;
                $field_species_common_name = $node->get('field_species_common_name')->value;
                $field_species_latin_ = $node->get('field_species_latin_')->value;
                $field_genus = $node->get('field_genus')->value;
                $field_activity = $node->get('field_activity')->value;
                $field_season = $node->get('field_season')->value;

                // Entity reference tax terms
                $field_life_stage = [];
                $eris = $node->get('field_life_stage');
                foreach ($eris as $item) {
                    $referenced_entity = $item->entity;
                    if ($referenced_entity) {
                        $field_life_stage[] = $referenced_entity->label();
                    }
                }
                $field_life_stage = implode(', ', $field_life_stage);

                // Load the csv data to a php array
                $csv_field = $node->get('field_stressor_response_csv_data');
                $csv_file = $csv_field->entity;
                $csv_data = [];
                $counter = 1;
                $hc_series_data = [];

                // Add on the series title
                $custom_title = array($field_id, ". ", $field_title);

                if (isset($csv_file)) {

                    $file_uri = $csv_file->getFileUri();
                    $handle = fopen($file_uri, 'r');

                    if ($handle !== FALSE) {
                        while (($data = fgetcsv($handle)) !== FALSE) {
                            $csv_data[] = array_slice($data, 0, 5);
                            // Skip header
                            if ($counter > 1) {
                                $hc_series_data[] = array(
                                    'x' => (float) $data[0],
                                    'y' => (float) $data[1],
                                );
                            }
                            $counter = 1 + $counter;
                        }
                        fclose($handle);
                    }

                    // Ensure file format is ok
                    // Check headers

                    $headers = $csv_data[0];
                    $pass_test = true;
                    if (!($headers[2] == "SD")) {
                        $pass_test = false;
                    }
                    if (!($headers[3] == "low.limit")) {
                        $pass_test = false;
                    }
                    if (!($headers[4] == "up.limit")) {
                        $pass_test = false;
                    }

                    $hc_series_data_complete = array(
                        'name' => implode('', $custom_title),
                        'data' => $hc_series_data
                    );

                } else {
                    $pass_test = false;
                }

                // If file format is ok, add to plot data
                if ($pass_test) {
                    //kint("PASS TEST");
                    $sr_plot_data[] = array(
                        'node_id' => $field_id,
                        'title' => $field_title,
                        'stressor_name' => $field_stressor_name,
                        'stressor_units' => $field_stressor_units,
                        'specific_stressor_metric' => $field_specific_stressor_metric,
                        'species_common_name' => $field_species_common_name,
                        'species_latin' => $field_species_latin_,
                        'genus' => $field_genus,
                        'activity' => $field_activity,
                        'season' => $field_season,
                        'life_stage' => $field_life_stage,
                        'csv_data' => $csv_data,
                    );

                    // Add on high charts series
                    $hc_series_multiple[] = $hc_series_data_complete;
                    $x_axis_lab_parts[] = $field_stressor_name;
                    $x_axis_lab_parts[] = $field_specific_stressor_metric;
                    $x_axis_lab_parts[] = $field_stressor_units;

                }

            }
        }

        // Fix x-axis
        $uniqueArray = array_unique($x_axis_lab_parts);
        $x_axis_lab = implode(' ', $uniqueArray);

        // Build the highcharts data object
        $highcharts_data = [
            'title' => [
                'text' => 'Multi-SR Comparison Plot',
                'align' => 'left',
            ],
            'subtitle' => [
                'text' => 'Click on a series in the legend to include or exclude it from the plot',
                'align' => 'left',
            ],
            'xAxis' => [
                'title' => [
                    'text' => $x_axis_lab,
                ],
            ],
            'yAxis' => [
                'title' => [
                    'text' => 'Response (0 - 100%)',
                ],
                'max' => 100,
            ],
            'exporting' => [
                'enabled' => 'true',
                'csv' => [
                    'itemDelimiter' => ',',
                ],
            ],
            'legend' => [
                'align' => 'left',
                'verticalAlign' => 'bottom',
            ],
            'series' => $hc_series_multiple
        ];


        // Send data to stressor_module.module and twig template to page
        return [
            '#theme' => 'stressor_response_plot',
            '#sr_plot_data' => $sr_plot_data,
            '#highcharts_data' => json_encode($highcharts_data),
            '#attached' => [
                'library' => [
                    'stressor_module/highcharts',
                ],
            ],
            '#markup' => $this->t('Received IDs: @ids', ['@ids' => print_r($ids_array, TRUE)]),
        ];
    }


}


