import request from "../index.js";
import { errorMessage } from "../helpers/utilities.js";

export async function fetchMonster(code) {
  return request
    .get(`/monsters/${code}`)
    .then((response) => response.data)
    .then((monster) => monster.data)
    .catch((error) => errorMessage(error));
}
