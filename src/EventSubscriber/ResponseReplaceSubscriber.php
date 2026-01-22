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
        $content = (string) $response->getContent();
        if ($content === '') {
            $response->setContent($html);
            return;
        }

        // Try to replace <main> content
        if (preg_match('/<main\b([^>]*)>(.*)<\/main>/is', $content, $m)) {
            $new = '<main'.$m[1].'>'.$html.'</main>';
            $response->setContent(str_replace($m[0], $new, $content));
            return;
        }
        // Try to replace <div id="main">
        if (preg_match('/<div\b([^>]*\bid=("|\')main\2[^>]*)>(.*)<\/div>/isU', $content, $m)) {
            $open = '<div'.$m[1].'>';
            $close = '</div>';
            $response->setContent(str_replace($m[0], $open.$html.$close, $content));
            return;
        }
        // Try Contao indexer block
        if (preg_match('/<!--\s*indexer::stop\s*-->(.*)<!--\s*indexer::continue\s*-->/is', $content, $m)) {
            $response->setContent(str_replace($m[0], '<!-- indexer::stop -->'.$html.'<!-- indexer::continue -->', $content));
            return;
        }
        // Fallback: replace body content
        if (preg_match('/(<body\b[^>]*>)(.*)(<\/body>)/is', $content, $m)) {
            $response->setContent($m[1].$html.$m[3]);
            return;
        }
        // Final fallback: replace entire response
        $response->setContent($html);
    }
}

