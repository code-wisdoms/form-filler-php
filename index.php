<?php

$data = [
    'Employer Name' => $_GET['employer_name'],
];

$data = base64_encode(json_encode($data));

$tempFile = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
$command = 'node C:/wamp64/www/projects/form-filler/src/index.js';

if (array_key_exists('flatten', $_GET)) {
    $command .= ' --flatten';
}
$command .= " -d $data";
$command .= ' > ' . escapeshellarg($tempFile);

// FOR DEBUGGING, OUTPUT STDERR TO STDOUT
//$command .= ' 2>&1';

exec($command, $output, $status);

if ($status === 0) {
    // Check if the file was created successfully
    if (file_exists($tempFile)) {
        // Read the contents of the temporary file
        $pdfData = file_get_contents($tempFile);

        // Set the appropriate headers to display the PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="filled_form.pdf"');
        header('Content-Length: ' . strlen($pdfData));

        // Output the PDF data to the browser
        echo $pdfData;

        // Optionally, remove the temporary file after use
        unlink($tempFile);
    } else {
        echo "Failed to create PDF.";
    }
} else {
    echo "Error occurred. Exit status: $status<br>";
    echo implode("\n", $output);
}
