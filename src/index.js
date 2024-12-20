const fs = require("fs");
const pdfLib = require("pdf-lib");
const logger = require(__dirname + "/utils/logger");
const args = require(__dirname + "/utils/args");

logger.info("New request: " + JSON.stringify(process.argv));
(async () => {
  try {
    const opts = args();
    logger.info("with options " + JSON.stringify(opts));
    const pdfBuffer = fs.readFileSync(__dirname + "/forms/QMEForm110.pdf");
    const pdfDoc = await pdfLib.PDFDocument.load(pdfBuffer);
    const form = pdfDoc.getForm();
    // const fields = form.getFields();

    const inputs = JSON.parse(atob(opts.d));

    for (const key in inputs) {
      const nameField = form.getTextField(key);
      nameField.setText(inputs[key]);
    }

    if (opts.flatten) {
      form.flatten();
    }

    const data = await pdfDoc.save();
    process.stdout.write(data);
  } catch (error) {
    process.stdout.write(error.toString());
    logger.error(error);
    process.exit(1);
  }
})();
