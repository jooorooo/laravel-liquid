<?php

use Liquid\Filters\ArrFilters;
use Liquid\Filters\DateFilters;
use Liquid\Filters\DefFilters;
use Liquid\Filters\EscapeFilters;
use Liquid\Filters\MathFilters;
use Liquid\Filters\MultyFilters;
use Liquid\Filters\StrFilters;

use Liquid\Tag\TagAssign;
use Liquid\Tag\TagBlock;
use Liquid\Tag\TagBreak;
use Liquid\Tag\TagCapture;
use Liquid\Tag\TagCase;
use Liquid\Tag\TagComment;
use Liquid\Tag\TagContinue;
use Liquid\Tag\TagCycle;
use Liquid\Tag\TagDecrement;
use Liquid\Tag\TagFor;
use Liquid\Tag\TagIf;
use Liquid\Tag\TagIfchanged;
use Liquid\Tag\TagIncrement;
use Liquid\Tag\TagExtends;
use Liquid\Tag\TagLayout;
use Liquid\Tag\TagLiquid;
use Liquid\Tag\TagPaginate;
use Liquid\Tag\TagRaw;
use Liquid\Tag\TagRender;
use Liquid\Tag\TagTablerow;
use Liquid\Tag\TagUnless;
use Liquid\Tag\TagEcho;

return [

    /*
     |--------------------------------------------------------------------------
     | Liquid auto escape html
     |--------------------------------------------------------------------------
     |
     | By default is on.
     */
    'auto_escape' => true,

    /*
     |--------------------------------------------------------------------------
     | Liquid extension
     |--------------------------------------------------------------------------
     |
     | By default extension is extension.php.
     */
    'extension' => 'liquid',

    /*
     |--------------------------------------------------------------------------
     | Liquid extension
     |--------------------------------------------------------------------------
     |
     | By default compiled store cache is file.
     */
    'compiled_store' => 'file',

    /*
     |--------------------------------------------------------------------------
     | Liquid templates finder
     |--------------------------------------------------------------------------
     |
     | By default finder is file
     */
    'finder' => [
        'default' => env('LIQUID_VIEW_FINDER', 'file'),

        'drivers' => [
            'file' => [
                'driver' => 'file',
                'path' => resource_path('views'),
            ],

            'database' => [
                'driver' => 'database',
                'table' => 'templates',
                'connection' => null,
            ],
        ]
    ],

    /*
     |--------------------------------------------------------------------------
     | Liquid allowed tags
     |--------------------------------------------------------------------------
     */
    'tags' => [
        'assign' => TagAssign::class,
        'block' => TagBlock::class,
        'break' => TagBreak::class,
        'capture' => TagCapture::class,
        'case' => TagCase::class,
        'comment' => TagComment::class,
        'continue' => TagContinue::class,
        'cycle' => TagCycle::class,
        'decrement' => TagDecrement::class,
        'for' => TagFor::class,
        'if' => TagIf::class,
        'ifchanged' => TagIfchanged::class,
        'render' => TagRender::class,
        'increment' => TagIncrement::class,
        'extends' => TagExtends::class,
        'paginate' => TagPaginate::class,
        'raw' => TagRaw::class,
        'tablerow' => TagTablerow::class,
        'unless' => TagUnless::class,
        'layout' => TagLayout::class,
        'echo' => TagEcho::class,
        'liquid' => TagLiquid::class,
    ],

    /*
     |--------------------------------------------------------------------------
     | Liquid allowed filters
     |--------------------------------------------------------------------------
     */
    'filters' => [
        StrFilters::class,
        EscapeFilters::class,
        DefFilters::class,
        ArrFilters::class,
        MultyFilters::class,
        MathFilters::class,
        DateFilters::class,
    ],

    /*
     |--------------------------------------------------------------------------
     | Transform Laravel Model
     |--------------------------------------------------------------------------
     */
    'transform_model' => [
    ],

    /*
     |--------------------------------------------------------------------------
     | Protected variables for assign
     |--------------------------------------------------------------------------
     */
    'protected_variables' => [
        'content_for_header',
        'content_for_layout',
        'content_for_index',
        'content_for_footer'
    ],

];
