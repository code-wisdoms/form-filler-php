const { createLogger, transports, format: winstonFormat } = require("winston");
require("winston-daily-rotate-file");

const myFormat = winstonFormat.printf(({ level, message, timestamp }) => {
  return `${level} - ${timestamp}: ${message}`;
});

const logger = createLogger({
  level: process.env.LOG_LEVEL ?? "silly",
  transports: [
    new transports.DailyRotateFile({
      dirname: __dirname + "/../../logs",
      filename: "%DATE%.log",
      datePattern: "YYYY-MM-DD",
      zippedArchive: true,
      level: process.env.LOG_LEVEL ?? "silly",
      format: winstonFormat.combine(winstonFormat.timestamp(), myFormat),
    }),
  ],
});

module.exports = logger;
