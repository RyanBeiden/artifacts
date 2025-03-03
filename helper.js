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
  console.info('----------------------------');
}

export function errorMessage(error) {
  try {
    return new Error(error.response.data.error.message);
  } catch {
    return new Error(error);
  }
}
