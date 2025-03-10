import moment from "moment";

export function exists(value) {
  return typeof value !== "undefined";
}

export async function delay(character) {
  const difference = moment(character.cooldown_expiration).diff(moment());

  await new Promise((resolve) => setTimeout(resolve, Math.max(difference, 0)));
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

    return thrownError;
  } catch {
    return new Error(error);
  }
}
