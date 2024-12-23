<?php

use CodeWisdoms\Filler\Exceptions\STDError;
use CodeWisdoms\Filler\Filler;

require '../../vendor/autoload.php';


$data = [
    'Employer Name' => ['type' => 'text', 'value' => $_GET['employer_name']],
    'Is a certified interpreter required?' => ['type' => 'radio', 'value' => 'Yes'],
    'Claims Administrator State (Required)' => ['type' => 'dropdown', 'value' => 'CA'],
];
$file_path = __DIR__ . "/forms/{$_GET['form']}.pdf";

$filler = new Filler();

if (array_key_exists('fill', $_GET)) {
    try {
        $pdfData = $filler->fill($file_path, $data, array_key_exists('flatten', $_GET));
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
        $fields = $filler->getFields($file_path);

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
