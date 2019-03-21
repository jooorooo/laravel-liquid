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
     | Liquid allowed tags
     |--------------------------------------------------------------------------
     */
    'tags' => [
        'assign' => Liquid\Tag\TagAssign::class,
        'block' => Liquid\Tag\TagBlock::class,
        'break' => Liquid\Tag\TagBreak::class,
        'capture' => Liquid\Tag\TagCapture::class,
        'case' => Liquid\Tag\TagCase::class,
        'comment' => Liquid\Tag\TagComment::class,
        'continue' => Liquid\Tag\TagContinue::class,
        'cycle' => Liquid\Tag\TagCycle::class,
        'decrement' => Liquid\Tag\TagDecrement::class,
        'for' => Liquid\Tag\TagFor::class,
        'if' => Liquid\Tag\TagIf::class,
        'ifchanged' => Liquid\Tag\TagIfchanged::class,
        'include' => Liquid\Tag\TagInclude::class,
        'increment' => Liquid\Tag\TagIncrement::class,
        'layout' => Liquid\Tag\TagLayout::class,
        'paginate' => Liquid\Tag\TagPaginate::class,
        'raw' => Liquid\Tag\TagRaw::class,
        'tablerow' => Liquid\Tag\TagTablerow::class,
        'unless' => Liquid\Tag\TagUnless::class,
    ],

];
