import moment from "moment";

export function exists(value) {
  return typeof value !== "undefined";
}

export function delay(character) {
  const now = moment().utc();
  const expiration = moment(character.cooldown_expiration).utc();

  const difference = expiration.diff(now) + 225;

  return new Promise((resolve) => setTimeout(resolve, Math.max(difference, 0)));
}

export function logInfo(message) {
  console.info(message);
  console.info("----------------------------");
}

export function errorMessage(error) {
  try {
    const errorObject = error.response.data.error;
    const thrownError = new Error(errorObject.message);

    thrownError.code = errorObject.code;

    console.error(thrownError);
  } catch {
    console.error(new Error(error));
  }
}
