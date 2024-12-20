<?php
namespace CodeWisdoms\Filler\Exceptions;
class STDError extends \Exception
{
    private string $stdErrOutput;
    public function __construct(string $message, string $stdErrOutput, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->stdErrOutput = $stdErrOutput;
    }
    public function getErrorOutput(): string
    {
        return $this->stdErrOutput;
    }
}
