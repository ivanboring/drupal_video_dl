
# Drupal Video DL

## What is this
This is a wrapper module for the [Youtube DL](https://github.com/ytdl-org/youtube-dl) library that can download video and audio from over [1000 different video providers](https://github.com/ytdl-org/youtube-dl/blob/master/docs/supportedsites.md) like Youtube, Vimeo, Facebook etc.

Because youtube-dl gets constant take downs, I did not want to host this on drupal.org and get them into troubles, so here it is on Github :)

Drupal Video DL is a module that currently have two things available for it. The one thing is a service where you can download video or audio files from provider links via a service for any third party module that would want to use it.

The other core feature is that it has two AI Automator types for the AI Automator module that can be found in the [AI module](https://www.drupal.org/project/ai). These makes it possible to take a link and generate video or audio files for further use for AI context or for just showcasing (if you have copyright or it doesn't have copyright).

For more information on how to use the AI Automator (previously AI Interpolator), check https://workflows-of-ai.com.

## Requirements
* You need youtube-dl and [ffmpeg](https://ffmpeg.org/) installed on your server.
* To use it, you need to use a third party module using the service. Currently its only usable with the AI Automator submodule of the [AI module](https://www.drupal.org/project/ai)

## Install
Add the following into your composer.json on the repositories key:

```
"repositories": {
  "drupal_video_dl": {
    "type": "vcs",
    "url": "https://github.com/ivanboring/drupal_video_dl.git"
  }
},
```

then run:

`composer require "ivanboring/drupal_video_dl:^1.0@rc"`

## How to use as AI Automator type
1. Install the [AI module](https://www.drupal.org/project/ai).
2. Install this module.
3. Create some entity or node type with a linl field.
4. Create a file field that either takes audio files or video files.
5. Enable AI Automator checkbox and configure it.
6. Create an entity of the type you generated, fill in a link to one of the providers and save.
7. The file should be save.
## How to use the Drupal Video DL service.
This is a code example on how you can download a video from Youtube.

```
$downloader = \Drupal::service('drupal_video_dl.downloader');
// Get back a file entity.
$file = $downloader->download('https://www.youtube.com/watch?v=NQuNEEiyxsk', 'public://myvideo.mp4', 'video');
```
