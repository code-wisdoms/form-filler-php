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
        $this->init($executableFinder);
    }
    private function init(ExecutableFinder $executableFinder)
    {
        if (is_dir(__DIR__ . '/../commands/node_modules')) {
            return;
        }
        try {
            $commands = [];
            $commands[] = $executableFinder->find('npm');
            $commands[] = 'i';
            return $this->execute($commands);
        } catch (\Throwable $e) {
            try {
                $commands = [];
                $commands[] = $this->nodePath;
                $commands[] = $executableFinder->find('npm');
                $commands[] = 'i';
                return $this->execute($commands);
            } catch (\Throwable $e) {
                $commands = [];
                $commands[] = $this->nodePath;
                $commands[] = 'npm';
                $commands[] = 'i';
                return $this->execute($commands);
            }
        }
    }
    public function fill(string $file_path, array $data, bool $flatten = false): string
    {
        $command = [];
        $command[] = $this->nodePath;
        $command[] = __DIR__ . '/../commands/fill.js';

        if ($flatten) {
            $command[] = '--flatten';
        }
        $command[] = '-file';
        $command[] = $file_path;
        $command[] = '-d';
        $command[] = $this->encodeData($data);
        return $this->execute($command);
    }
    public function getFields(string $file_path): array
    {
        $command = [];
        $command[] = $this->nodePath;
        $command[] = __DIR__ . '/../commands/fields.js';

        $command[] = '-file';
        $command[] = $file_path;
        return json_decode($this->execute($command), true);
    }
    private function execute(array $command): string
    {
        $process = new Process($command);
        try {
            $process->setWorkingDirectory(__DIR__ . '/../commands');
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
