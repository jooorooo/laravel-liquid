<?php
/**
 * Created by PhpStorm.
 * User: joro
 * Date: 27.3.2019 г.
 * Time: 15:42 ч.
 */

namespace Liquid;

class Constant
{

    const FilterSeparatorPartial        = '\|';
    const FilterSeparator               = '/' . self::FilterSeparatorPartial . '/';
    const ArgumentSeparator             = ',';
    const FilterArgumentSeparator       = ':';
    const VariableAttributeSeparator    = '.';
    const WhitespaceControl             = '-';
    const TagStartPartial               = '\{\%';
    const TagStart                      = '/' . self::TagStartPartial . '/';
    const TagEndPartial                 = '\%\}';
    const TagEnd                        = '/' . self::TagEndPartial . '/';
    const VariableSignaturePartial      = '\(?[\w\-\.\[\]]{1,}\)?';
    const VariableSignature             = '/' . self::VariableSignaturePartial . '/';
    const VariableSegmentPartial        = '[\w\-]';
    const VariableSegment               = '/' . self::VariableSegmentPartial . '/';
    const VariableStartPartial          = '/\{\{/';
    const VariableStart                 = '/' . self::VariableStartPartial . '/';
    const VariableEndPartial            = '/\}\}/';
    const VariableEnd                   = '/' . self::VariableEndPartial . '/';
    const VariableIncompleteEnd         = '/\}\}?/';
    const QuotedStringPartial           = '"[^"]*"|\'[^\']*\'';
    const QuotedString                  = '/' . self::QuotedStringPartial . '/';
    const QuotedFragmentPartial         = self::QuotedStringPartial . '|(?:[^\s,\|\'"]|' . self::QuotedStringPartial . ')+';
    const QuotedFragment                = '/' . self::QuotedFragmentPartial . '/ms';
    const TagAttributes                 = '/(\w+)\s*\:\s*(' . self::QuotedFragmentPartial . ')/ms';
    const AnyStartingTagPartial         = self::TagStartPartial . '|' . self::VariableStartPartial;
    const AnyStartingTag                = '/' . self::AnyStartingTagPartial . '/ms';
    const PartialTemplateParserPartial  = self::TagStartPartial . '.*?' . self::TagEndPartial . '|' . self::VariableStartPartial . '.*?' . self::VariableEndPartial;
    const PartialTemplateParser         = '/' . self::PartialTemplateParserPartial . '/ms';
    const TemplateParser                = '/(' . self::PartialTemplateParserPartial . '|' . self::AnyStartingTagPartial . ')/ms';
    const VariableParser                = '/\[[^\]]+\]|' . self::VariableSegmentPartial . '+\??/ms';

}