<?php

namespace Liquid\Exceptions;

use Closure;
use Exception;
use Liquid\Tokens\TagToken;
use function is_object;
use function is_string;

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
     * @param TagToken           $token   The template line where the error occurred
     * @param Exception $previous The previous exception
     */
    public function __construct(string $message, TagToken $token = null, Exception $previous = null)
    {
        parent::__construct('', 0, $previous);

//        $temp_file = tempnam(sys_get_temp_dir(), $token->getName());
//        file_put_contents($temp_file, $token->getSource());
//        register_shutdown_function(function() use($temp_file) {
//            unlink($temp_file);
//        });
//        $this->sourcePath = $temp_file;

        if($token) {
            $this->sourceCode = $token->getSource();
            $this->lineno = $token->getLine();
            $this->name = $token->getFileName();
        }
        $this->rawMessage = $message;

        $this->updateRepr();
    }

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

    /**
     * @return string
     */
    public function getSourceCode()
    {
        return $this->sourceCode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
            if (is_string($this->name) || (is_object($this->name) && method_exists($this->name, '__toString'))) {
                $name = sprintf('"%s"', $this->name);
            } elseif($this->name instanceof Closure) {
                $name = 'closure';
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
