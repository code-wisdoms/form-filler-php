<?php
namespace Filler;

use Filler\Exceptions\STDError;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Filler
{
    private $nodePath;
    public function __construct()
    {
        $executableFinder = new ExecutableFinder();
        $this->nodePath = $executableFinder->find('node');
    }
    public function fill(string $form_name, array $data, bool $flatten = false): string
    {
        $command = [];
        $command[] = __DIR__ . '/../commands/fill.js';

        if ($flatten) {
            $command[] = '--flatten';
        }
        $command[] = '-form';
        $command[] = $form_name;
        $command[] = '-d';
        $command[] = $this->encodeData($data);
        return $this->execute($command);
    }
    public function getFields(string $form_name): array
    {
        $command = [];
        $command[] = __DIR__ . '/../commands/fields.js';

        $command[] = '-form';
        $command[] = $form_name;
        return json_decode($this->execute($command), true);
    }
    private function execute(array $command): string
    {
        array_unshift($command, $this->nodePath);
        $process = new Process($command);
        try {
            $process->mustRun();
            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            throw new STDError($e->getMessage(), $process->getErrorOutput(), $e->getCode(), $e);
        } catch (\Throwable $e) {
            throw new STDError($e->getMessage(), $process->getErrorOutput(), $e->getCode(), $e);
        }
    }
    private function encodeData(array $data): string
    {
        return base64_encode(json_encode($data));
    }
}
