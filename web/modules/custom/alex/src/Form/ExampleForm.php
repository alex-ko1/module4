<?php

namespace Drupal\alex\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class that create table form.
 */
class ExampleForm extends FormBase {

  /**
   * Table headers.
   *
   * @var array
   */
  protected array $Header;

  /**
   * @var array
   */
  protected array $calculatedCells;

  /**
   * Number of year.
   *
   * @var int
   */
  protected int $year = 2022;

  /**
   * Number of row.
   *
   * @var int
   */
  protected int $rows = 1;

  /**
   * Number of table.
   *
   * @var int
   */
  protected int $tables = 1;

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'alex_form';
  }

  /**
   * Create table head.
   */
  public function buildHeader() {
    $this->Header = [
      'year' => $this->t("Year"),
      'jan' => $this->t("Jan"),
      'feb' => $this->t("Feb"),
      'mar' => $this->t("Mar"),
      'q1' => $this->t("Q1"),
      'apr' => $this->t("Apr"),
      'may' => $this->t("May"),
      'jun' => $this->t("Jun"),
      'q2' => $this->t("Q2"),
      'jul' => $this->t("Jul"),
      'aug' => $this->t("Aug"),
      'sep' => $this->t("Sep"),
      'q3' => $this->t("Q3"),
      'oct' => $this->t("Oct"),
      'nov' => $this->t("Nov"),
      'dec' => $this->t("Dec"),
      'q4' => $this->t("Q4"),
      'ytd' => $this->t("YTD"),
    ];
    $this->calculatedCells = [
      'year' => $this->t("Year"),
      'q1' => $this->t("Q1"),
      'q2' => $this->t("Q2"),
      'q3' => $this->t("Q3"),
      'q4' => $this->t("Q4"),
      'ytd' => $this->t("YTD"),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $this->buildHeader();

    $form['#prefix'] = '<div id="alex-table">';
    $form['#suffix'] = '</div>';
    for ($i = 0; $i < $this->tables; $i++) {
      $table_id = $i;
      $form[$table_id] = [
        '#type' => 'table',
        '#caption' => $this
          ->t('Simple Table'),
        '#header' => $this->Header,
      ];
      $this->buildRows($table_id, $form[$table_id], $form_state);
    }

    $form['addRow'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#submit' => [
        '::addRow',
      ],
      '#ajax' => [
        'callback' => '::submitAjax',
        'wrapper' => 'alex-table',
      ],
    ];
    $form['add_table'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => [
        '::addTable',
      ],
      '#ajax' => [
        'callback' => '::submitAjax',
        'wrapper' => 'alex-table',
      ],
    ];
    $form['Submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'wrapper' => 'alex-table',
      ],
    ];
    $form['#attached']['library'][] = 'alex/alex.form';
    return $form;
  }

  /**
   * Function to building rows.
   */
  public function buildRows($table_id, array &$table, FormStateInterface $form_state) {
    for ($i = 0; $i < $this->rows; $i++) {
      foreach ($this->Header as $key => $value) {
        $table[$i][$key] = [
          '#type' => 'number',
        ];

        if (array_key_exists($key, $this->calculatedCells)) {
          $value = $form_state->getValue($table_id . '][' . $i . '][' . $key, 0);
          $table[$i][$key]['#disabled'] = TRUE;
          $table[$i][$key]['#default_value'] = 0 + round($value, 2);
        }
        $table[$i]['year']['#default_value'] = date('Y') - $i;
      }
    }
  }

  /**
   * Button for adding a new row.
   */
  public function addRow(array &$form, FormStateInterface $form_state) {
    $this->rows++;
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Button for adding a new table.
   */
  public function addTable(array &$form, FormStateInterface $form_state) {
    $this->tables++;
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Submit with AJAX.
   */
  public function submitAjax(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($form_state->getErrors()) {
      $this->messenger()->addError('Invalid');
      $form_state->clearErrors();
    }
    $values = $form_state->getValues();
    foreach ($values as $tableKey => $table) {
      foreach ($table as $rowKey => $row) {

        $path = $tableKey . '][' . $rowKey . '][';

        $q1 = ($row['jan'] + $row['feb'] + $row['mar'] + 1) / 3;
        $q2 = ($row['apr'] + $row['may'] + $row['jun'] + 1) / 3;
        $q3 = ($row['jul'] + $row['aug'] + $row['sep'] + 1) / 3;
        $q4 = ($row['oct'] + $row['nov'] + $row['dec'] + 1) / 3;
        $ytd = ($q1 + $q2 + $q3 + $q4 + 1) / 4;

        $form_state->setValue($path . 'q1', $q1);
        $form_state->setValue($path . 'q2', $q2);
        $form_state->setValue($path . 'q3', $q3);
        $form_state->setValue($path . 'q4', $q4);
        $form_state->setValue($path . 'ytd', $ytd);
      }
    }
    $form_state->setRebuild();

    /*for ($i = 0; $i < $this->tables; $i++) {
    for ($b = 0; $b < $this->rows; $b++) {
    $valueJan = $form[$i][$b][1]['#value'];
    $valueFeb = $form[$i][$b][2]['#value'];
    $valueMar = $form[$i][$b][3]['#value'];
    $valueApr = $form[$i][$b][5]['#value'];
    $valueMay = $form[$i][$b][6]['#value'];
    $valueJun = $form[$i][$b][7]['#value'];
    $valueJul = $form[$i][$b][9]['#value'];
    $valueAug = $form[$i][$b][10]['#value'];
    $valueSep = $form[$i][$b][11]['#value'];
    $valueOct = $form[$i][$b][13]['#value'];
    $valueNov = $form[$i][$b][14]['#value'];
    $valueDec = $form[$i][$b][15]['#value'];
    $q1 = round((($valueJan + $valueFeb + $valueMar + 1) / 3), 2);
    $q2 = round((($valueApr + $valueMay + $valueJun + 1) / 3), 2);
    $q3 = round((($valueJul + $valueAug + $valueSep + 1) / 3), 2);
    $q4 = round((($valueOct + $valueNov + $valueDec + 1) / 3), 2);
    $sum_year = round((($q1 + $q2 + $q3 + $q4 + 1) / 4), 2);
    $form[$i][4]['#value'] = $q1;

    }
    }*/
    \Drupal::messenger()->addMessage('Form is valid! ' . $row['jan'] . ' , ' . $q1);
  }

  /**
   * Build updated array after submit.
   */
  public function UpdatedArray($array) {
    $values = [];
    for ($i = 0; $i < $this->rows; $i++) {
      foreach ($array[$i] as $key => $value) {
        if (!array_key_exists($key, $this->calculatedCells)) {
          if ($value == "") {
            $value = 0;
          }
          $values[] = $value;
        }
      }
    }
    return $values;
  }

  /**
   * Validate form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Getting values.
    $data = $form_state->getValues();
    // Array for all tables values.
    $tablesValues = [];
    for ($i = 0; $i < $this->tables; $i++) {
      // Update array.
      $values = $this->updatedArray($data[$i]);
      // Pass values off current table.
      $tablesValues[] = $values;
      // Calculate number of active cells.
      $months = $this->rows * 12;
      // Variable for position of filled cells.
      $position = [];
      // Count of filled cells.
      $nonEmpty = 0;
      // Cycle for getting positions of filled cells.
      for ($q = 0; $q < $months; $q++) {
        if ($values[$q] !== 0) {
          $position[] = $q;
          $nonEmpty++;
        }
      }
      // Cycle for comparison of position.
      for ($k = 0; $k < $nonEmpty - 1; $k++) {
        // Check that there are not gaps between two values.
        $difference = $position[$k + 1] - $position[$k];
        if ($difference != 1) {
          $form_state->setErrorByName($k, 'Gap');
        }
      }
    }
    // Cycle for checking similarity of tables.
    for ($i = 0; $i < $this->tables - 1; $i++) {
      if ($tablesValues[$i] != $tablesValues[$i + 1]) {
        $form_state->setErrorByName($i, 'Tables are not the same!');
      }
    }
  }

}
