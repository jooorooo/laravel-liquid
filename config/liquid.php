<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Liquid extension
     |--------------------------------------------------------------------------
     |
     | By default extension is extension.php.
     */
    'extension' => 'liquid.tpl',

    /*
     |--------------------------------------------------------------------------
     | Liquid config
     |--------------------------------------------------------------------------
     */
    'liquid' => [

        // Allow template names with extension in include and extends tags.
        'INCLUDE_ALLOW_EXT' => false,

        // Suffix for include files.
        'INCLUDE_SUFFIX' => 'liquid',

        // Prefix for include files.
        'INCLUDE_PREFIX' => '_',

        // Tag start.
        'TAG_START' => '{%',

        // Tag end.
        'TAG_END' => '%}',

        // Variable start.
        'VARIABLE_START' => '{{',

        // Variable end.
        'VARIABLE_END' => '}}',

        // Variable name.
        'VARIABLE_NAME' => '[a-zA-Z_][a-zA-Z_0-9.-]*',

        'QUOTED_STRING' => '"[^"]*"|\'[^\']*\'',
        'QUOTED_STRING_FILTER_ARGUMENT' => '"[^":]*"|\'[^\':]*\'',

        // Automatically escape any variables unless told otherwise by a "raw" filter
        'ESCAPE_BY_DEFAULT' => false,
    ],


];
