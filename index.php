<?php
require './vendor/autoload.php';

use Filler\Exceptions\STDError;
use Filler\Filler;

$data = [
    'Employer Name' => $_GET['employer_name'],
];

$filler = new Filler();

if (array_key_exists('fill', $_GET)) {
    try {
        $pdfData = $filler->fill($_GET['form'], $data, array_key_exists('flatten', $_GET));
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="filled_form.pdf"');
        header('Content-Length: ' . strlen($pdfData));
        echo $pdfData;
    } catch (STDError $e) {
        echo $e->getMessage();
        echo $e->getErrorOutput();
    } catch (\Throwable $e) {
        echo $e->getMessage();
    }
    exit;
} elseif (array_key_exists('fields', $_GET)) {
    try {
        $fields = $filler->getFields($_GET['form']);

        echo '<pre>';
        var_dump($fields);
    } catch (STDError $e) {
        echo $e->getMessage();
        echo $e->getErrorOutput();
    } catch (\Throwable $e) {
        echo $e->getMessage();
    }
    exit;
}
