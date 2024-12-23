const fs = require("fs");
const pdfLib = require("pdf-lib");
const logger = require(__dirname + "/utils/logger");
const args = require(__dirname + "/utils/args");

logger.info("New request for fill: " + JSON.stringify(process.argv));
(async () => {
  try {
    const opts = args();
    logger.info("with options " + JSON.stringify(opts));
    if (!opts.d) {
      logger.error("Field values are not specified");
      process.exit(1);
    }
    if (!fs.existsSync(opts.d)) {
      logger.error("Data file not found");
      process.exit(1);
    }
    if (!opts.file) {
      logger.error("File is not specified");
      process.exit(1);
    }
    if (!fs.existsSync(opts.file)) {
      logger.error("Form not found");
      process.exit(1);
    }
    const pdfBuffer = fs.readFileSync(opts.file);
    const pdfDoc = await pdfLib.PDFDocument.load(pdfBuffer);
    const form = pdfDoc.getForm();

    let inputs = [];
    try {
      const fileData = fs.readFileSync(opts.d);
      inputs = JSON.parse(fileData);
    } catch (error) {
      logger.error("Unable to parse inputs");
      process.exit(1);
    }

    const fields = form.getFields();

    for (const field of fields) {
      try {
        const key = field.getName();
        const inputField = inputs[key];
        if (!inputField) {
          continue;
        }
        switch (inputField.type) {
          case "text": {
            field.setText(inputField.value);
            break;
          }
          case "radio": {
            field.select(inputField.value);
            break;
          }
          case "checkbox": {
            if (!!inputField.value) {
              field.check();
            } else {
              field.uncheck();
            }
            break;
          }
          case "dropdown": {
            field.select(inputField.value);
            break;
          }
          case "image": {
            field.setImage(inputField.value);
            break;
          }
        }
      } catch (error) {
        logger.error(`Error: ${error}`);
      }
    }

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
      }
      return { name, type, values };
    });

    if (opts.flatten) {
      form.flatten();
    }

    process.stdout.write(await pdfDoc.save());
  } catch (error) {
    logger.error(`Error: ${error}`);
    process.exit(1);
  }
})();
