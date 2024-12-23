const fs = require("fs");
const pdfLib = require("pdf-lib");
const logger = require(__dirname + "/utils/logger");
const args = require(__dirname + "/utils/args");

logger.info("New request for fields: " + JSON.stringify(process.argv));
(async () => {
  try {
    const opts = args();
    logger.info("with options " + JSON.stringify(opts));
    if (!opts.file) {
      logger.error("Form name is not specified");
      process.exit(1);
    }
    if (!fs.existsSync(opts.file)) {
      logger.error("Form not found");
      process.exit(1);
    }
    const pdfBuffer = fs.readFileSync(opts.file);
    const pdfDoc = await pdfLib.PDFDocument.load(pdfBuffer);
    const form = pdfDoc.getForm();
    const fields = form.getFields();

    const data = fields.map((field) => {
      const name = field.getName();
      let type = "unknown";
      let values = [];

      if (field instanceof pdfLib.PDFTextField) {
        type = "text";
      } else if (field instanceof pdfLib.PDFCheckBox) {
        type = "checkbox";
      } else if (field instanceof pdfLib.PDFDropdown) {
        type = "select";
        values = field.getOptions();
      } else if (field instanceof pdfLib.PDFRadioGroup) {
        type = "radio";
        values = field.getOptions();
      } else if (field instanceof pdfLib.PDFButton) {
        type = "button";
      } else if (field instanceof pdfLib.PDFOptionList) {
        values = field.getOptions();
        type = "multi-select";
      } else if (field instanceof pdfLib.PDFSignature) {
        type = "signature";
      } else {
        type = field.constructor.name;
      }
      return { name, type, values };
    });
    process.stdout.write(JSON.stringify(data));
  } catch (error) {
    logger.error(`Error: ${error}`);
    process.exit(1);
  }
})();
