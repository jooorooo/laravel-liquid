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
     | Liquid cache driver
     |--------------------------------------------------------------------------
     |
     | By default DebugBar route served from the same domain that request served.
     | To override default domain, specify it as a non-empty value.
     */
    'cache' => [
        'driver' => env('LIQUID_CACHE_DRIVER', 'file'),

        'expire' => 60 //minutes
    ],

    /*
     |--------------------------------------------------------------------------
     | Liquid config
     |--------------------------------------------------------------------------
     */
    'liquid' => [
        // The method is called on objects when resolving variables to see
        // if a given property exists.
        'HAS_PROPERTY_METHOD' => 'field_exists',

        // This method is called on object when resolving variables when
        // a given property exists.
        'GET_PROPERTY_METHOD' => 'get',

        // Separator between filters.
        'FILTER_SEPARATOR' => '\|',

        // Separator for arguments.
        'ARGUMENT_SEPARATOR' => ',',

        // Separator for argument names and values.
        'FILTER_ARGUMENT_SEPARATOR' => ':',

        // Separator for variable attributes.
        'VARIABLE_ATTRIBUTE_SEPARATOR' => '.',

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
