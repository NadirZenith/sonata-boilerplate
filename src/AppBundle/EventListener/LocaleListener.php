<?php

// src/AppBundle/EventListener/LocaleListener.php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LocaleListener implements EventSubscriberInterface
{

    private $defaultLocale;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        /* $request->setLocale($request->getPreferredLanguage()); */

        include 'nzdebug.php';

        if ($request->query->has('_locale')) {
            //if user changed locale by query string
            //set locale in session and redirect to same url
            $response = new RedirectResponse(strtok($request->getUri(), '?'));
            $event->setResponse($response);
            $locale = $request->query->get('_locale');
            $request->getSession()->set('_locale', $locale);
            return;
        } else {
            //set browser preferred Language
            $locale = $request->getPreferredLanguage();
        }

        if (!$request->hasPreviousSession()) {
            $request->setLocale($locale);
            return;
        }
        
        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}
