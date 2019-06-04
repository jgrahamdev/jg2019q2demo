<?php

/**
 * @file
 * Contains \Drupal\as_tracking\EventSubscriber\AsTrackingSubscriber.
 */

namespace Drupal\as_tracking\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Url;

/**
 * Event Subscriber AsTrackingSubscriber.
 */
class AsTrackingSubscriber implements EventSubscriberInterface {

  /**
   * @param GetResponseEvent $event
   */
  public function trackEvents(GetResponseEvent $event) {
    $request = $event->getRequest();
    $route = RouteMatch::createFromRequest($request);
    $name = $route->getRouteName();
    if ($name == 'image.upload') {
      _as_tracking_event('quick_edit_image');
    }
  }

  /**
   * @param GetResponseEvent $event
   */
  public function checkRedirect(GetResponseEvent $event) {
    // Does user have permission to configure site?
    $user = \Drupal::currentUser();
    if ($user->hasPermission('administer site configuration')) {
      // Grab the currently set user_id value from our form config.
      $user_id = \Drupal::config('as_tracking.settings')->get('user_id');
      // Store the redirect url for the configuration form.
      $redirect_url = Url::fromRoute('as_tracking.amplitude_api_form')->toString();
      // Get the uri from the request object.
      $request = $event->getRequest()->getRequestUri();
      // Is site already configured?
      $urls = [$redirect_url, '/user/logout', 'batch'];
      if ($user_id != 'first.last@acquia.com' || in_array($request, $urls)) {
        return;
      }
      // Redirect to configuration form.
      $response = new RedirectResponse($redirect_url, 301);
      $event->setResponse($response);
    }
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['trackEvents'];
    $events[KernelEvents::REQUEST][] = ['checkRedirect'];
    return $events;
  }

}
