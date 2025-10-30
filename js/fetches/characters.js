import request from "../index.js";
import { errorMessage } from "../helpers/utilities.js";

export async function fetchCharacter(name) {
  return request
    .get(`/characters/${name}`)
    .then((response) => response.data)
    .then((character) => character.data)
    .catch((error) => errorMessage(error));
}
