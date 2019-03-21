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
use Liquid\Tag\TagLayout;
use Liquid\Tag\TagPaginate;
use Liquid\Tag\TagRaw;
use Liquid\Tag\TagTablerow;
use Liquid\Tag\TagUnless;

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
        'layout' => TagLayout::class,
        'paginate' => TagPaginate::class,
        'raw' => TagRaw::class,
        'tablerow' => TagTablerow::class,
        'unless' => TagUnless::class,
    ],

];
