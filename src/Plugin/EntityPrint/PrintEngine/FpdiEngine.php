<?php

namespace Drupal\entity_print_templates\Plugin\EntityPrint\PrintEngine;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_print\Plugin\ExportTypeInterface;
use Drupal\entity_print\Plugin\PrintEngineBase;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * FPDI plugin implementation.
 *
 * @PrintEngine(
 *   id = "fpdiengine",
 *   label = @Translation("FPDI"),
 *   export_type = "pdf"
 * )
 *
 * To use this implementation you will need the TCPDF and FPDI library, simply run
 *
 * @code
 *     composer require "tecnickcom/tcpdf ~6"
 *     composer require "setasign/fpdi ^2"
 * @endcode
 */
class FpdiEngine extends PrintEngineBase {

  /**
   * The FDPI implementation.
   *
   * @var \setasign\Fpdi\Tcpdf\Fpdi
   */
  protected $fpdi;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExportTypeInterface $export_type) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $export_type);
    $this->fpdi = new Fpdi();
  }

  /**
   * {@inheritdoc}
   */
  public static function getInstallationInstructions() {
    return t('Please install with: @command', ['@command' => 'composer require "tecnickcom/tcpdf ~6" "setasign/fpdi ^2"']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'page_format' => 'LETTER',
      'orientation' => static::PORTRAIT,
      'x_offset' => 0,
      'y_offset' => 0,
      'template_path' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $page_formats = array_combine(array_keys(\TCPDF_STATIC::$page_formats), array_keys(\TCPDF_STATIC::$page_formats));
    $form['page_format'] = [
      '#title' => $this->t('Paper Size'),
      '#type' => 'select',
      '#options' => $page_formats,
      '#default_value' => $this->configuration['page_format'],
      '#description' => $this->t('The page size to print the PDF to.'),
    ];
    $form['orientation'] = [
      '#title' => $this->t('Paper Orientation'),
      '#type' => 'select',
      '#options' => [
        static::PORTRAIT => $this->t('Portrait'),
        static::LANDSCAPE => $this->t('Landscape'),
      ],
      '#default_value' => $this->configuration['orientation'],
      '#description' => $this->t('The paper orientation one of Landscape or Portrait'),
    ];
    $form['template_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to template file'),
      '#description' => $this->t('Set this to the system path where the PDF engine binary is located.'),
      '#default_value' => $this->configuration['template_path'],
    ];
    $form['x_offset'] = [
      '#type' => 'number',
      '#title' => $this->t('X Offset'),
      '#description' => $this->t('X Offset of the template page.'),
      '#default_value' => $this->configuration['x_offset'],
      '#step' => 0.01,
    ];
    $form['y_offset'] = [
      '#type' => 'number',
      '#title' => $this->t('Y Offset'),
      '#description' => $this->t('Y Offset of the template page.'),
      '#default_value' => $this->configuration['y_offset'],
      '#step' => 0.01,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addPage($content) {
    $this->fpdi->AddPage($this->configuration['orientation'], $this->configuration['page_format']);
    if (file_exists($this->configuration['template_path'])) {
      $this->fpdi->setSourceFile($this->configuration['template_path']);
    }
    $pageId = $this->fpdi->importPage(1);
    $this->fpdi->useTemplate($pageId, $this->configuration['x_offset'], $this->configuration['y_offset']);
    $this->fpdi->writeHTML($content);
  }

  /**
   * {@inheritdoc}
   */
  public function send($filename, $force_download = TRUE) {
    // If we have a filename then we force the download otherwise we open in the
    // browser.
    $this->fpdi->Output($filename, $force_download ? 'D' : 'I');
  }

  /**
   * {@inheritdoc}
   */
  public function getBlob() {
    return $this->fpdi->Output('', 'S');
  }

  /**
   * {@inheritdoc}
   */
  public static function dependenciesAvailable() {
    return class_exists('\TCPDF') && class_exists('\setasign\Fpdi\Tcpdf\Fpdi') && !drupal_valid_test_ua();
  }

  /**
   * {@inheritdoc}
   */
  public function getPrintObject() {
    return $this->fpdi;
  }

}
