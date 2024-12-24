<?php
namespace GL_Color_Palette_Generator\Tests;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\DefaultResultPrinter;

class TestPrinter extends DefaultResultPrinter implements TestListener
{
    private $buffer = '';
    private $headersSafe = false;

    public function write(string $buffer): void
    {
        // Buffer output until headers are safe
        if (!$this->headersSafe) {
            $this->buffer .= $buffer;
            return;
        }

        // Once headers are safe, flush buffer and write normally
        if ($this->buffer !== '') {
            parent::write($this->buffer);
            $this->buffer = '';
        }
        parent::write($buffer);
    }

    public function setHeadersSafe(bool $safe): void
    {
        $this->headersSafe = $safe;
    }
}
