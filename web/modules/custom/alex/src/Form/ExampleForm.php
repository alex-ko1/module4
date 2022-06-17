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
  protected array $header;

  /**
   * An array with quarter and year cells.
   *
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
    $this->header = [
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
        '#header' => $this->header,
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
      foreach ($this->header as $key => $value) {
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
  public function addRow(array $form, FormStateInterface $form_state) {
    $this->rows++;
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Button for adding a new table.
   */
  public function addTable(array $form, FormStateInterface $form_state) {
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
    \Drupal::messenger()->addMessage('Form is valid!');
  }

  /**
   * Build updated array after submit.
   */
  public function updatedArray($array): array {
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
    $data = $form_state->getValues();
    $tablesValues = [];
    for ($i = 0; $i < $this->tables; $i++) {
      $values = $this->updatedArray($data[$i]);
      $tablesValues[] = $values;
      $months = $this->rows * 12;
      $position = [];
      $nonEmpty = 0;
      for ($q = 0; $q < $months; $q++) {
        if ($values[$q] !== 0) {
          $position[] = $q;
          $nonEmpty++;
        }
      }
      for ($k = 0; $k < $nonEmpty - 1; $k++) {
        $difference = $position[$k + 1] - $position[$k];
        if ($difference != 1) {
          $form_state->setErrorByName($k, 'The gap between values');
        }
      }
    }
    for ($i = 0; $i < $this->tables - 1; $i++) {
      if ($tablesValues[$i] != $tablesValues[$i + 1]) {
        $form_state->setErrorByName($i, 'Tables are not the same!');
      }
    }
  }

}
