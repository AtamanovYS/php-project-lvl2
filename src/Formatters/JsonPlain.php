<?php

namespace Differ\Formatters\JsonPlain;

use function _\flatMapDepth;

function format(array $data): string
{
    // Приведение к типу string, чтобы тесты проходили
    // Здесь невозможно ситуации, чтобы нельзя было привести к json
    return (string) json_encode(
        array_values(array_filter(formatIter($data))),
        JSON_UNESCAPED_SLASHES
    );
}

function formatIter(array $data, string $prevPath = ''): array
{
    return flatMapDepth(
        $data,
        function ($elem) use ($prevPath): array {
            $path = "{$prevPath}/{$elem['key']}";

            switch ($elem['type']) {
                case 'nested':
                    return formatIter($elem['children'], $path);
                case 'replace':
                    return [
                        'status' => 'replace',
                        'value' => $elem['newValue'],
                        'prevValue' => $elem['oldValue'],
                        'path' => $path
                    ];
                case 'add':
                    return [
                        'status' => 'add',
                        'value' => $elem['newValue'],
                        'path' => $path
                    ];
                case 'remove':
                    return [
                        'status' => 'remove',
                        'prevValue' => $elem['oldValue'],
                        'path' => $path
                    ];
                case 'unchanged':
                    return [null];
                default:
                    throw new \Exception("unknown node type: \"{$elem['type']}\"");
            }
        }
    );
}
