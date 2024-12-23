<?php
namespace CodeWisdoms\Filler;

use CodeWisdoms\Filler\Exceptions\STDError;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Filler
{
    public function __construct(private string | null $nodePath = null, private string | null $npmPath = null)
    {
        $executableFinder = new ExecutableFinder();
        if ($nodePath === null) {
            $this->nodePath = $executableFinder->find('node');
        }
        if ($npmPath === null) {
            $this->npmPath = $executableFinder->find('npm');
        }
        $this->init($executableFinder);
    }
    private function init(ExecutableFinder $executableFinder)
    {
        if (is_dir(__DIR__ . '/../commands/node_modules')) {
            return;
        }
        try {
            $commands = [];
            $commands[] = $this->npmPath;
            $commands[] = 'i';
            return $this->execute($commands);
        } catch (\Throwable $e) {
            try {
                $commands = [];
                $commands[] = $this->nodePath;
                $commands[] = $this->npmPath;
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
