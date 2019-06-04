<?php

namespace Drupal\as_lift\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to handle Content Hub responses.
 */
class ContentHubResponseSubscriber implements EventSubscriberInterface {

  /**
   * Replaces all relative URLs in content hub responses with absolute URLs.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event.
   */
  public function onResponse(FilterResponseEvent $event) {
    $request = $event->getRequest();
    if (strpos($request->getPathInfo(), '/acquia-contenthub/display') === 0) {
      $url = new Url('<front>');
      $url = $url->setAbsolute(TRUE)->toString();
      $url = str_replace('http://', 'https://', $url);

      $response = $event->getResponse();
      $content = $response->getContent();
      $doc = new \DOMDocument();
      libxml_use_internal_errors(true);
      $success = $doc->loadHTML($content);
      libxml_clear_errors();
      if ($success) {
        $tag_map = ['img' => 'src', 'a' => 'href'];
        foreach ($tag_map as $tagname => $attributename) {
          foreach ($doc->getElementsByTagName($tagname) as $tag) {
            $src = $tag->getAttribute($attributename);
            if (strpos(pathinfo($src, PATHINFO_DIRNAME), '/') === 0) {
              $new_src_url = $url . ltrim($src, '/');
              $tag->setAttribute($attributename, $new_src_url);
            }
          }
        }
        $srcset_tags = ['img', 'source'];
        foreach ($srcset_tags as $tagname) {
          foreach ($doc->getElementsByTagName($tagname) as $tag) {
            $srcset = $tag->getAttribute('srcset');
            $srcset = explode(', ', $srcset);
            array_walk($srcset, function (&$src) use ($url) {
              if (strpos(pathinfo($src, PATHINFO_DIRNAME), '/') === 0) {
                $src = $url . ltrim($src, '/');
              }
            });
            $tag->setAttribute('srcset', implode(', ', $srcset));
          }
        }
        $response->setContent($doc->saveHTML());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onResponse', -100];
    return $events;
  }

}
