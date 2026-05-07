<?php

// Portal-only override of nativeblade/ui-mobile colors. Laravel merges this
// on top of the package's own config, so any keys we leave out fall through
// to the package defaults.
return [
    'colors' => [
        'ios' => [
            'primary' => 'red-600',
            'primary_text' => 'white',
            'destructive' => 'red-700',
        ],
        'material' => [
            'primary' => 'red-600',
            'primary_text' => 'white',
            'destructive' => 'red-700',
        ],
    ],
];
