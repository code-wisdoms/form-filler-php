const logger = require(__dirname + "/logger");
const optRegex = new RegExp(/^\-{2}/);
const argRegex = new RegExp(/^\-{1}/);

module.exports = () => {
  const args = process.argv.slice(2);
  logger.debug("Args in : " + JSON.stringify(args));
  const opts = {};
  for (const key in args) {
    const arg = args[key];
    if (optRegex.test(arg)) {
      logger.debug(`matched opt ${key} | ${arg}`);
      const _o = arg.replace(optRegex, "");
      opts[_o] = true;
    } else if (argRegex.test(arg)) {
      logger.debug(`matched arg ${key} | ${arg}`);
      opts[arg.replace(argRegex, "")] = args[parseInt(key) + 1];
    } else {
      logger.debug("No match: " + arg);
    }
  }

  logger.debug("Opts out: " + JSON.stringify(opts));
  return opts;
};
