<?php

namespace Drupal\drupal_video_dl\Plugin\AiAutomatorType;

use Drupal\ai_automators\PluginBaseClasses\ExternalBase;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\drupal_video_dl\Downloader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The base rules for a downloading field.
 */
class DownloaderBase extends ExternalBase implements AiAutomatorTypeInterface, ContainerFactoryPluginInterface {

  /**
   * The type of download.
   */
  public string $downloadType = 'audio';

  /**
   * The token system to replace and generate paths.
   */
  public Token $token;

  /**
   * The downloader.
   */
  public Downloader $downloader;

  /**
   * Construct an image field.
   *
   * @param array $configuration
   *   Inherited configuration.
   * @param string $plugin_id
   *   Inherited plugin id.
   * @param mixed $plugin_definition
   *   Inherited plugin definition.
   * @param \Drupal\Core\Utility\Token $token
   *   The token system.
   * @param \Drupal\drupal_video_dl\Downloader $downloader
   *   The downloader.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Token $token,
    Downloader $downloader,
  ) {
    $this->token = $token;
    $this->downloader = $downloader;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token'),
      $container->get('drupal_video_dl.downloader'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function helpText() {
    return $this->t("Generate @type files from Youtube or other links.", [
      '@type' => $this->downloadType,
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function needsPrompt() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function advancedMode() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function allowedInputs() {
    return [
      'link',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function ruleIsAllowed(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition) {
    // Checks system for ffmpeg, otherwise this rule does not exist.
    if (!$this->downloader->hasFfmpeg()) {
      return FALSE;
    }
    if (!$this->downloader->getExecutionPath()) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function placeholderText() {
    return "";
  }

  /**
   * {@inheritDoc}
   */
  public function generate(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    $values = [];
    foreach ($entity->{$automatorConfig['base_field']} as $link) {
      // Download as last step.
      $values[] = $link->uri;
    }
    return $values;
  }

  /**
   * {@inheritDoc}
   */
  public function verifyValue(ContentEntityInterface $entity, $value, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Check so the value is a valid url.
    if (filter_var($value, FILTER_VALIDATE_URL)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function storeValues(ContentEntityInterface $entity, array $values, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    $config = $fieldDefinition->getConfig($entity->bundle())->getSettings();
    // Transform string to boolean.
    $fileEntities = [];

    foreach ($values as $value) {
      // Get tha path from config.
      $path = $this->token->replace($config['uri_scheme'] . '://' . rtrim($config['file_directory'], '/'));
      // Download the file.
      $fileEntities[] = $this->downloader->download($value, $path, $this->downloadType);
    }
    // Then set the value.
    $entity->set($fieldDefinition->getName(), $fileEntities);
  }
}
