<?php

namespace DVC\ContaoCustomCatalog\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, priority: -128)]
class ResponseReplaceSubscriber
{
    public function __invoke(ResponseEvent $event): void
    {
        if (method_exists($event, 'isMainRequest')) {
            if (!$event->isMainRequest()) {
                return;
            }
        } elseif (method_exists($event, 'isMasterRequest')) {
            if (!$event->isMasterRequest()) {
                return;
            }
        }

        $request = $event->getRequest();
        $html = (string) $request->attributes->get('_dvc_cc_reader_html', '');
        if ($html === '') {
            return;
        }

        $response = $event->getResponse();
        if (!$response->isSuccessful() || !str_contains(strtolower($response->headers->get('Content-Type', 'text/html')), 'text/html')) {
            return;
        }
        $response->setContent(\DVC\ContaoCustomCatalog\Util\ReaderContent::replace((string) $response->getContent(), $html));
    }
}
