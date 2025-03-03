import request from "../index.js";
import { errorMessage } from "../helper.js";

export async function fetchCharacter(name) {
  try {
    return await request.get(`/characters/${name}`)
      .then((response) => response.data)
      .then((character) => character.data);
  } catch (error) {
    throw errorMessage(error);
  }
};
