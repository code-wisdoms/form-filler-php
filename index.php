<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

require './vendor/autoload.php';

$data = [
    'Employer Name' => $_GET['employer_name'],
];

$data = base64_encode(json_encode($data));

$tempFile = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';

$executableFinder = new ExecutableFinder();
$nodePath = $executableFinder->find('node');
$command = [];
$command[] = $nodePath;
$command[] = 'C:/wamp64/www/projects/form-filler/src/commands/fill.js';

if (array_key_exists('flatten', $_GET)) {
    $command[] = '--flatten';
}
$command[] = '-form';
$command[] = $_GET['form'];
$command[] = '-d';
$command[] = $data;
$command[] = '>';
$command[] = escapeshellarg($tempFile);

$process = new Process($command);

try {
    $process->mustRun();
    $pdfData = $process->getOutput();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="filled_form.pdf"');
    header('Content-Length: ' . strlen($pdfData));
    echo $pdfData;
    exit;
} catch (ProcessFailedException $e) {
    echo $e->getMessage();
    echo $process->getErrorOutput();
} catch (\Throwable $e) {
    echo $e->getMessage();
}
