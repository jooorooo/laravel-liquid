<?php

namespace Liquid\Exceptions;

use Exception;
use Liquid\LiquidCompiler;

class Error extends Exception
{
    private $lineno;
    private $name;
    private $rawMessage;
    private $sourcePath;
    private $sourceCode;

    /**
     * Constructor.
     *
     * Set both the line number and the name to false to
     * disable automatic guessing of the original template name
     * and line number.
     *
     * Set the line number to -1 to enable its automatic guessing.
     * Set the name to null to enable its automatic guessing.
     *
     * By default, automatic guessing is enabled.
     *
     * @param string             $message  The error message
     * @param int                $lineno   The template line where the error occurred
     * @param Source|string|null $source   The source context where the error occurred
     * @param \Exception         $previous The previous exception
     */
    public function __construct(string $message, int $lineno = -1, $source = null, \Exception $previous = null, bool $autoGuess = true)
    {
        parent::__construct('', 0, $previous);

        if (null === $source) {
            $name = null;
        } elseif($source instanceof LiquidCompiler) {
            $name = basename($source->getPath());
            $this->sourcePath = $source->getPath();
            $this->sourceCode = $source->getFileSource($source->getPath());
        } else {
            $name = $source->getName();
            $this->sourceCode = $source->getCode();
            $this->sourcePath = $source->getPath();
        }

        $this->lineno = $lineno;
        $this->name = $name;

//        if ($autoGuess && (-1 === $lineno || null === $name || null === $this->sourcePath)) {
//            $this->guessTemplateInfo();
//        }

        $this->rawMessage = $message;

        $this->updateRepr();
    }

//    private function guessTemplateInfo()
//    {
//        $template = null;
//        $templateClass = null;
//
//        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT);
//        foreach ($backtrace as $trace) {
//            if (isset($trace['object']) && $trace['object'] instanceof Template) {
//                $currentClass = \get_class($trace['object']);
//                $isEmbedContainer = 0 === strpos($templateClass, $currentClass);
//                if (null === $this->name || ($this->name == $trace['object']->getTemplateName() && !$isEmbedContainer)) {
//                    $template = $trace['object'];
//                    $templateClass = \get_class($trace['object']);
//                }
//            }
//        }
//
//        // update template name
//        if (null !== $template && null === $this->name) {
//            $this->name = $template->getTemplateName();
//        }
//
//        // update template path if any
//        if (null !== $template && null === $this->sourcePath) {
//            $src = $template->getSourceContext();
//            $this->sourceCode = $src->getCode();
//            $this->sourcePath = $src->getPath();
//        }
//
//        if (null === $template || $this->lineno > -1) {
//            return;
//        }
//
//        $r = new \ReflectionObject($template);
//        $file = $r->getFileName();
//
//        $exceptions = [$e = $this];
//        while ($e = $e->getPrevious()) {
//            $exceptions[] = $e;
//        }
//
//        while ($e = array_pop($exceptions)) {
//            $traces = $e->getTrace();
//            array_unshift($traces, ['file' => $e->getFile(), 'line' => $e->getLine()]);
//
//            while ($trace = array_shift($traces)) {
//                if (!isset($trace['file']) || !isset($trace['line']) || $file != $trace['file']) {
//                    continue;
//                }
//
//                foreach ($template->getDebugInfo() as $codeLine => $templateLine) {
//                    if ($codeLine <= $trace['line']) {
//                        // update template line
//                        $this->lineno = $templateLine;
//
//                        return;
//                    }
//                }
//            }
//        }
//    }

    public function appendMessage($rawMessage)
    {
        $this->rawMessage .= $rawMessage;
        $this->updateRepr();
    }

    /**
     * Gets the raw message.
     *
     * @return string The raw message
     */
    public function getRawMessage()
    {
        return $this->rawMessage;
    }

    /**
     * Gets the template line where the error occurred.
     *
     * @return int The template line
     */
    public function getTemplateLine()
    {
        return $this->lineno;
    }

    private function updateRepr()
    {
        $this->message = $this->rawMessage;

        if ($this->sourcePath && $this->lineno > 0) {
            $this->file = $this->sourcePath;
            $this->line = $this->lineno;

            return;
        }

        $dot = false;
        if ('.' === substr($this->message, -1)) {
            $this->message = substr($this->message, 0, -1);
            $dot = true;
        }

        $questionMark = false;
        if ('?' === substr($this->message, -1)) {
            $this->message = substr($this->message, 0, -1);
            $questionMark = true;
        }

        if ($this->name) {
            if (\is_string($this->name) || (\is_object($this->name) && method_exists($this->name, '__toString'))) {
                $name = sprintf('"%s"', $this->name);
            } else {
                $name = json_encode($this->name);
            }
            $this->message .= sprintf(' in %s', $name);
        }

        if ($this->lineno && $this->lineno >= 0) {
            $this->message .= sprintf(' at line %d', $this->lineno);
        }

        if ($dot) {
            $this->message .= '.';
        }

        if ($questionMark) {
            $this->message .= '?';
        }
    }

}