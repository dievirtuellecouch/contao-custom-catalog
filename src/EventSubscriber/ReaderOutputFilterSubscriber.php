<?php

namespace DVC\ContaoCustomCatalog\EventSubscriber;

use Contao\CoreBundle\Event\OutputFrontendTemplateEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener(event: OutputFrontendTemplateEvent::class)]
class ReaderOutputFilterSubscriber
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke(OutputFrontendTemplateEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }
        $html = (string) $request->attributes->get('_dvc_cc_reader_html', '');
        if ($html === '') {
            return;
        }

        $content = $event->getContent() ?? '';

        // Try to replace <main> content
        if (preg_match('/<main\b([^>]*)>(.*)<\/main>/is', $content, $m)) {
            $new = '<main'.$m[1].'>'.$html.'</main>';
            $event->setContent(str_replace($m[0], $new, $content));
            return;
        }
        // Try to replace <div id="main">
        if (preg_match('/<div\b([^>]*\bid=("|\')main\2[^>]*)>(.*)<\/div>/isU', $content, $m)) {
            $open = '<div'.$m[1].'>'; $close = '</div>';
            $event->setContent(str_replace($m[0], $open.$html.$close, $content));
            return;
        }
        // Try Contao indexer block
        if (preg_match('/<!--\s*indexer::stop\s*-->(.*)<!--\s*indexer::continue\s*-->/is', $content, $m)) {
            $event->setContent(str_replace($m[0], '<!-- indexer::stop -->'.$html.'<!-- indexer::continue -->', $content));
            return;
        }
        // Fallback: replace body content
        if (preg_match('/(<body\b[^>]*>)(.*)(<\/body>)/is', $content, $m)) {
            $event->setContent($m[1].$html.$m[3]);
            return;
        }
        // Final fallback: replace full content to ensure detail view renders
        $event->setContent($html);
    }
}
