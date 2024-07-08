<?php

namespace Drupal\drupal_video_dl;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\file\FileRepositoryInterface;

class Downloader {

  /**
   * The File System interface.
   */
  public FileSystemInterface $fileSystem;

  /**
   * The entity type manager.
   */
  public EntityTypeManagerInterface $entityManager;

  /**
   * The account proxy interface.
   */
  public AccountProxyInterface $currentUser;

  /**
   * Construct a downloader.
   */
  public function __construct(FileSystemInterface $fileSystem, EntityTypeManagerInterface $entityManager, AccountProxyInterface $currentUser) {
    $this->fileSystem = $fileSystem;
    $this->entityManager = $entityManager;
    $this->currentUser = $currentUser;
  }

  /**
   * Download a video or audio file from a URL.
   *
   * @param string $url
   *   The URL to download from.
   * @param string $destination
   *   The destination to save the file to.
   * @param string $type
   *   The type of file to download.
   * @param string $fileName
   *   The name of the file to save.
   *
   * @return \Drupal\File\Entity\File
   *   The file entity.
   */
  public function download($url, $path, $type = 'video', $fileName = '') {
    // If the executables are not found, throw an exception.
    $exec = $this->getExecutionPath();
    if (!$exec || !$this->hasFfmpeg()) {
      throw new \Exception('No youtube dl executable found.');
    }
    $fileStorage = $this->entityManager->getStorage('file');
    // Set a tempname.
    $tmpFile = $this->fileSystem->tempnam($this->fileSystem->getTempDirectory(), 'download_');
    if ($type == 'video') {
      exec("$exec -o $tmpFile \"$url\" --merge-output-format=webm", $output, $return);
      $tmpFile = $tmpFile . '.webm';
    }
    else {
      exec("$exec -o $tmpFile \"$url\" --extract-audio --audio-format mp3", $output, $return);
      $tmpFile = $tmpFile . '.mp3';
    }
    // Set the file name.
    $fileName = empty($fileName) ? basename($tmpFile) : $fileName;
    // If we have a file, get the filename.
    if ($return) {
      // Check this issue for audio if it is failing here:
      // https://github.com/yt-dlp/yt-dlp/issues/5651#issuecomment-1328228539.
      throw new \Exception("Failed to download file from Youtube with return code $return.");
    }
    $filePath = $path . '/' . $fileName;
    // Prepare the directory.
    $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    // Move the file.
    $newPath = $this->fileSystem->move($tmpFile, $filePath, FileExists::Replace);
    // Create file entity from the new path.
    $file = $fileStorage->create([
      'uri' => $newPath,
      'uid' => $this->currentUser->id(),
      'status' => 1,
      'filename' => $fileName,
    ]);
    $file->save();
    return $file;
  }

  /**
   * Figure out if ffmpeg exists.
   *
   * @return bool
   *   If ffmpeg exists.
   */
  public function hasFfmpeg() {
    $command = (PHP_OS == 'WINNT') ? 'where ffmpeg' : 'which ffmpeg';
    $path = shell_exec($command);
    return !empty($path);
  }

  /**
   * Figure out executable.
   *
   * @return string
   *   The path to the executable.
   */
  public function getExecutionPath() {
    $command = (PHP_OS == 'WINNT') ? 'where youtube-dl' : 'which youtube-dl';
    $path = shell_exec($command);
    if ($path) {
      return 'youtube-dl';
    }
    $command = (PHP_OS == 'WINNT') ? 'where yt-dlpg' : 'which yt-dlp';
    $path = shell_exec($command);
    if ($path) {
      return 'yt-dlp';
    }
    return '';
  }

}
