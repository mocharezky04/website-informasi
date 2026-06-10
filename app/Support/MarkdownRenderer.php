<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class MarkdownRenderer
{
    public static function render(?string $text): HtmlString
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", (string) $text));
        if ($text === '') {
            return new HtmlString('');
        }

        $lines = explode("\n", $text);
        $html = [];

        for ($index = 0, $total = count($lines); $index < $total;) {
            $line = trim($lines[$index]);

            if ($line === '') {
                $index++;
                continue;
            }

            if (Str::startsWith($line, '```')) {
                [$html[], $index] = self::consumeCodeBlock($lines, $index);
                continue;
            }

            if (preg_match('/^(#{1,4})\s+(.+)$/', $line, $matches)) {
                $html[] = self::renderHeading($matches[2], strlen($matches[1]));
                $index++;
                continue;
            }

            if (Str::startsWith($line, '|')) {
                [$tableRows, $index] = self::consumeWhile($lines, $index, fn ($value) => Str::startsWith(trim($value), '|'));
                $block = implode("\n", $tableRows);
                $html[] = str_contains($block, '---') ? self::renderTable($block) : self::renderParagraph($block);
                continue;
            }

            if (preg_match('/^([-*]|\d+\.)\s+/', $line)) {
                [$listRows, $index] = self::consumeWhile($lines, $index, fn ($value) => preg_match('/^([-*]|\d+\.)\s+/', trim($value)));
                $html[] = self::renderList($listRows);
                continue;
            }

            [$paragraphRows, $index] = self::consumeWhile($lines, $index, fn ($value) => !self::startsSpecialBlock(trim($value)));
            $html[] = self::renderParagraph(implode(' ', array_map('trim', $paragraphRows)));
        }

        return new HtmlString(implode("\n", $html));
    }

    private static function consumeCodeBlock(array $lines, int $index): array
    {
        $firstLine = trim($lines[$index]);
        $language = trim(substr($firstLine, 3)) ?: 'text';
        $codeLines = [];
        $index++;

        for ($total = count($lines); $index < $total; $index++) {
            if (Str::startsWith(trim($lines[$index]), '```')) {
                $index++;
                break;
            }
            $codeLines[] = $lines[$index];
        }

        return [self::renderCodeBlock($language, implode("\n", $codeLines)), $index];
    }

    private static function consumeWhile(array $lines, int $index, callable $predicate): array
    {
        $rows = [];

        for ($total = count($lines); $index < $total; $index++) {
            $line = $lines[$index];
            if (trim($line) === '' || !$predicate($line)) {
                break;
            }
            $rows[] = $line;
        }

        return [$rows, $index];
    }

    private static function startsSpecialBlock(string $line): bool
    {
        return $line === ''
            || Str::startsWith($line, '```')
            || (bool) preg_match('/^#{1,4}\s+/', $line)
            || Str::startsWith($line, '|')
            || (bool) preg_match('/^([-*]|\d+\.)\s+/', $line);
    }

    private static function renderCodeBlock(string $language, string $code): string
    {
        return '<pre class="max-w-full bg-slate-950 border border-slate-800 rounded-lg p-3.5 my-3.5 overflow-x-auto whitespace-pre font-mono text-[11px] text-emerald-400"><div class="flex items-center justify-between border-b border-slate-900 pb-1.5 mb-2 text-[9px] text-slate-500 uppercase tracking-widest font-sans"><span>'.e($language).' console</span><span>Secure Shell Console</span></div><code class="whitespace-pre">'.e($code).'</code></pre>';
    }

    private static function renderHeading(string $text, int $level): string
    {
        if ($level <= 2) {
            return '<h3 class="text-xs sm:text-sm font-bold text-white mt-5 mb-2 font-mono uppercase tracking-wider border-l-2 border-emerald-500 pl-2 leading-relaxed">'.self::inline($text).'</h3>';
        }

        if ($level === 3) {
            return '<h3 class="text-xs sm:text-sm font-bold text-white mt-5 mb-2 font-mono uppercase tracking-wider border-l-2 border-emerald-500 pl-2 leading-relaxed">'.self::inline($text).'</h3>';
        }

        return '<h4 class="text-xs font-bold text-slate-200 mt-4 mb-1.5 font-sans leading-relaxed">'.self::inline($text).'</h4>';
    }

    private static function renderTable(string $block): string
    {
        $rows = collect(explode("\n", $block))->filter(fn ($row) => str_contains($row, '|'))->values();
        $body = $rows->map(function ($row, $index) {
            if (str_contains($row, '---')) {
                return '';
            }
            $cells = collect(explode('|', $row))->map(fn ($cell) => trim($cell))->filter(fn ($cell) => $cell !== '')->values();
            $tag = $index === 0 ? 'th' : 'td';
            $cellClass = $index === 0 ? 'p-2 align-top min-w-32' : 'p-2 text-slate-350 align-top min-w-32';
            $rowClass = $index === 0 ? 'bg-slate-950 text-slate-400 font-bold border-b border-slate-800' : 'bg-slate-900/40 border-b border-slate-800/80';
            $cellHtml = $cells->map(fn ($cell) => '<'.$tag.' class="'.$cellClass.'">'.self::inline($cell).'</'.$tag.'>')->implode('');

            return '<tr class="'.$rowClass.'">'.$cellHtml.'</tr>';
        })->implode('');

        return '<div class="overflow-x-auto my-3 border border-slate-800 rounded-lg max-w-full"><table class="min-w-full text-left border-collapse text-[11px] font-mono"><tbody>'.$body.'</tbody></table></div>';
    }

    private static function renderList(array $lines): string
    {
        $ordered = isset($lines[0]) && preg_match('/^\d+\.\s+/', trim($lines[0]));
        $items = collect($lines)->map(function ($line) {
            $cleaned = preg_replace('/^([-*]|\d+\.)\s+/', '', trim($line));
            return '<li class="leading-relaxed pl-1 text-slate-300">'.self::inline($cleaned).'</li>';
        })->implode('');
        $tag = $ordered ? 'ol' : 'ul';
        $listClass = $ordered ? 'list-decimal' : 'list-disc';

        return '<'.$tag.' class="'.$listClass.' list-inside space-y-1.5 my-3.5 text-xs text-slate-300">'.$items.'</'.$tag.'>';
    }

    private static function renderParagraph(string $text): string
    {
        return '<p class="text-xs text-slate-300 leading-relaxed my-2 font-sans break-words max-w-full">'.self::inline(trim($text)).'</p>';
    }

    private static function inline(string $text): string
    {
        $escaped = e($text);
        $escaped = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-white font-bold">$1</strong>', $escaped);
        $escaped = preg_replace('/`([^`]+)`/', '<code class="text-emerald-300 bg-neutral-950 border border-neutral-800 rounded px-1 whitespace-nowrap">$1</code>', $escaped);

        return $escaped;
    }
}
