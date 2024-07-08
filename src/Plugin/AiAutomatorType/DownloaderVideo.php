<?php

namespace Drupal\drupal_video_dl\Plugin\AiAutomatorType;

use Drupal\ai_automator\Attribute\AiAutomatorType;
use Drupal\ai_automator\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The rules for a video field.
 */
#[AiAutomatorType(
  id: 'video_dl_link_to_video',
  label: new TranslatableMarkup('Video Downloader: Link to Video File'),
  field_rule: 'file',
  target: 'file',
)]
class DownloaderVideo extends DownloaderBase implements AiAutomatorTypeInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritDoc}
   */
  public $title = 'Video Downloader: Link to Video File';

  /**
   * {@inheritDoc}
   */
  public string $downloadType = 'video';

}
