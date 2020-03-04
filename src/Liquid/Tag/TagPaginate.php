<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\UrlWindow;
use Illuminate\Support\Arr;
use Liquid\AbstractBlock;
use Liquid\Constant;
use Liquid\Context;
use Liquid\LiquidCompiler;
use Liquid\LiquidException;
use Liquid\Regexp;
use Liquid\Traits\TransformLaravelModel;

/**
 * The paginate tag works in conjunction with the for tag to split content into numerous pages.
 *
 * Example:
 *
 *    {% paginate collection.products by 5 %}
 *        {% for product in collection.products %}
 *            <!--show product details here -->
 *        {% endfor %}
 *    {% endpaginate %}
 *
 */
class TagPaginate extends AbstractBlock
{

    use TransformLaravelModel;

    /**
     * @var string The collection to paginate
     */
    private $collectionName;

    /**
     * @var int The number of items to paginate by
     */
    private $numberItems;


    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     *
     * @param LiquidCompiler|null $compiler
     * @throws LiquidException
     */
    public function __construct($markup, array &$tokens, LiquidCompiler $compiler = null)
    {

        parent::__construct($markup, $tokens, $compiler);

        $syntax = new Regexp('/(' . Constant::VariableSignaturePartial . ')\s+by\s+(\d+)/');

        if ($syntax->match($markup)) {
            $this->collectionName = $syntax->matches[1];
            $this->numberItems = $this->validateNumberItems($syntax->matches[2]);
            $this->extractAttributes($markup);
        } else {
            throw new LiquidException("Syntax Error - Valid syntax: paginate [collection] by [items]");
        }

    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string
     *
     */
    public function render(Context $context)
    {
        $collection = $context->get($this->collectionName);

        if($collection instanceof Model || $collection instanceof Builder || $collection instanceof Relation) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $collection */
            $collection = $collection->paginate($this->numberItems);
        } else {
            if ($collection instanceof \Traversable) {
                $collection = iterator_to_array($this->collection);
            }
            if(!is_array($collection)) {
                $collection = [];
            }

            /** @var \Illuminate\Pagination\LengthAwarePaginator $collection */
            $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName = 'page');
            $collection = new LengthAwarePaginator(
                array_splice($collection, ($currentPage - 1) * $this->numberItems, $this->numberItems),
                count($collection),
                $this->numberItems,
                $currentPage,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        }

        $collectionSize = $collection->total();
        $totalPages = $collection->lastPage();
        $currentPage = $collection->currentPage();
        $currentOffset = ($currentPage - 1) * $this->numberItems;

        $paginatedCollection = $this->transformModel($collection->items());

        $context->push();
        // Sets the collection if it's a key of another collection (ie search.results, collection.products, blog.articles)
//        $segments = explode('.', $this->collectionName);
        $parts = preg_split('/(\.|\[|\])/', $this->collectionName, null, PREG_SPLIT_NO_EMPTY);
        $result = [];
        Arr::set($result, implode('.', $parts), $paginatedCollection);
        if($result) {
            $key = key($result);
            $context->set($key, $result[$key]);
        }

        $paginate = array(
            'current_offset' => $currentOffset,
            'current_page' => $currentPage,
            'items' => $collectionSize,
            'page_size' => $this->numberItems,
            'pages' => $totalPages,
            'parts' => $this->parts(UrlWindow::make($collection))
        );

        if ($previous = $collection->previousPageUrl()) {
            $paginate['previous']['title'] = 'Previous';
            $paginate['previous']['is_link'] = true;
            $paginate['previous']['url'] = $previous;
        }

        if ($next = $collection->nextPageUrl()) {
            $paginate['next']['title'] = 'Next';
            $paginate['next']['is_link'] = true;
            $paginate['next']['url'] = $next;
        }

        $context->set('paginate', $paginate);

        $result = parent::render($context);

        $context->pop();

        return $result;
    }

    /**
     * @return array
     */
    public function parts($elements) : array
    {
        $result = [];
        $elements = array_filter([
            $elements['first'],
            is_array($elements['slider']) ? '...' : null,
            $elements['slider'],
            is_array($elements['last']) ? '...' : null,
            $elements['last'],
        ]);

        foreach($elements AS $element) {
            if(is_array($element)) {
                foreach($element AS $page => $link) {
                    $result[] = [
                        'title' => $page,
                        'url' => $link,
                        'is_link' => true,
                    ];
                }
            } else {
                $result[] = [
                    'title' => $element,
                    'url' => null,
                    'is_link' => false,
                ];
            }
        }

        return $result;
    }

}
