const fs = require("fs");
const pdfLib = require("pdf-lib");
const logger = require(__dirname + "/utils/logger");
const args = require(__dirname + "/utils/args");
const { drawRectangle, rgb, degrees, drawImage } = require("pdf-lib");

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
            let pdfLibSigImg = null;
            if (inputField.mime == "image/jpg") {
              pdfLibSigImg = await pdfDoc.embedJpg(
                Buffer.from(inputField.value, "base64")
              );
            } else {
              pdfLibSigImg = await pdfDoc.embedPng(
                Buffer.from(inputField.value, "base64")
              );
            }
            const pdfLibSigImgName = "PDF_LIB_SIG_IMG";
            field.acroField.getWidgets().forEach((widget) => {
              const { context } = widget.dict;
              const { width, height } = widget.getRectangle();

              const appearance = [
                ...drawImage(pdfLibSigImgName, {
                  x: 0,
                  y: 2,
                  width: width,
                  height: height - 2,
                  rotate: degrees(0),
                  xSkew: degrees(0),
                  ySkew: degrees(0),
                }),
              ];

              const stream = context.formXObject(appearance, {
                Resources: {
                  XObject: {
                    [pdfLibSigImgName]: pdfLibSigImg.ref,
                  },
                },
                BBox: context.obj([0, 0, width, height]),
                Matrix: context.obj([1, 0, 0, 1, 0, 0]),
              });
              const streamRef = context.register(stream);

              widget.setNormalAppearance(streamRef);
            });
            break;
          }
        }
      } catch (error) {
        logger.error(`Error: ${error}`);
      }
    }

    if (opts.flatten) {
      form.flatten();
    }

    process.stdout.write(await pdfDoc.save({ updateFieldAppearances: false }));
  } catch (error) {
    logger.error(`Error: ${error}`);
    process.exit(1);
  }
})();
