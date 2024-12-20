const fs = require("fs");
const pdfLib = require("pdf-lib");
const logger = require(__dirname + "/../utils/logger");
const args = require(__dirname + "/../utils/args");

logger.info("New request for fill: " + JSON.stringify(process.argv));
(async () => {
  try {
    const opts = args();
    logger.info("with options " + JSON.stringify(opts));
    if (!opts.d) {
      logger.error("Field values are not specified");
      process.exit(1);
    }
    if (!opts.form) {
      logger.error("Form name is not specified");
      process.exit(1);
    }
    const formFilePath = `${__dirname}/../forms/${opts.form}.pdf`
    if (!fs.existsSync(formFilePath)) {
      logger.error("Form not found");
      process.exit(1);
    }
    const pdfBuffer = fs.readFileSync(formFilePath);
    const pdfDoc = await pdfLib.PDFDocument.load(pdfBuffer);
    const form = pdfDoc.getForm();

    let inputs = [];
    try {
      inputs = JSON.parse(atob(opts.d));
    } catch (error) {
      logger.error("Unable to parse inputs");
      process.exit(1);
    }

    for (const key in inputs) {
      const nameField = form.getTextField(key);
      nameField.setText(inputs[key]);
    }

    if (opts.flatten) {
      form.flatten();
    }

    process.stdout.write(await pdfDoc.save());
  } catch (error) {
    logger.error(`Error: ${error}`);
    process.exit(1);
  }
})();
