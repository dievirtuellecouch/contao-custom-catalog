<?php

namespace DVC\ContaoCustomCatalog\Util;

final class ReaderContent
{
    public static function replace(string $content, string $html): string
    {
        // Match balanced divs so the first nested closing tag cannot truncate #main.
        $patterns = [
            '~(<main\b[^>]*>).*?(</main>)~is',
            '~(<div\b[^>]*\bid\s*=\s*["\']main["\'][^>]*>)(?:(?!</?div\b).|(?&div))*(</div>)(?(DEFINE)(?<div><div\b[^>]*>(?:(?!</?div\b).|(?&div))*</div>))~is',
            '~(<body\b[^>]*>).*?(</body>)~is',
        ];
        foreach ($patterns as $pattern) {
            $result = preg_replace_callback($pattern, static fn(array $m) => $m[1].$html.$m[2], $content, 1, $count);
            if ($result !== null && $count > 0) {
                return $result;
            }
        }
        return $html;
    }
}
