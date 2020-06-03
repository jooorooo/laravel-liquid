<?php

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
use Liquid\Tag\TagInclude;
use Liquid\Tag\TagIncrement;
use Liquid\Tag\TagExtends;
use Liquid\Tag\TagLayout;
use Liquid\Tag\TagLiquid;
use Liquid\Tag\TagPaginate;
use Liquid\Tag\TagRaw;
use Liquid\Tag\TagTablerow;
use Liquid\Tag\TagUnless;
use Liquid\Tag\TagEcho;

use Liquid\Filters\Str;
use Liquid\Filters\Escape;
use Liquid\Filters\Def;
use Liquid\Filters\Arr;
use Liquid\Filters\Multy;
use Liquid\Filters\Math;
use Liquid\Filters\Date;

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
     | Liquid compiled store
     |--------------------------------------------------------------------------
     |
     | By default store is file. Allowed types is in cache.php -> stores
     */
    'compiled_store' => 'file',

    /*
     |--------------------------------------------------------------------------
     | Liquid compiled store
     |--------------------------------------------------------------------------
     |
     | By default store is file (load from view.paths). Allowed types is file, mysql
     */
    'view_store' => [
        'connection' => 'file',
        'table' => 'templates',
//        'where' => [
//            'version_id' => 2
//        ]
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
        'include' => TagInclude::class,
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
        Str::class,
        Escape::class,
        Def::class,
        Arr::class,
        Multy::class,
        Math::class,
        Date::class,
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
