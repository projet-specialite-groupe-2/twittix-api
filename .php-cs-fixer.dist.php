<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PHP82Migration' => true,
        'ordered_imports' => true,
        'visibility_required' => [
            'elements' => ['property', 'method', 'const'],
        ],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'phpdoc_to_comment' => [
            'ignored_tags' => ['psalm-suppress', 'psalm-var'],
        ],
        'phpdoc_align' => false,
        'trailing_comma_in_multiline' => [
            'elements' =>  ['arrays', 'arguments', 'parameters'],
        ],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
    ])
    ->setFinder($finder)
    ;
