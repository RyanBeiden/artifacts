import request from "./index.js";
import { exists } from "./helper.js";
import { chickens } from "./maps.js";
import { move } from "./move.js";

const NAME = 'chunt';

request.get('/my/characters')
  .then((response) => response.data)
  .then((responseData) => {
    const character = responseData.data.find((character) => character.name === NAME);

    if (!exists(character)) {
      console.error(`Character ${NAME} not found.`);

      return;
    }

    moveToChicken(character);
  });

function moveToChicken(character) {
  chickens().then((responseData) => {
    const chickenLocations = responseData.data;

    if (chickenLocations.length === 0) {
      console.error('No chickens found.');

      return;
    }

    const firstChicken = chickenLocations[0];

    const { x, y } = firstChicken;

    if (!exists(x) || !exists(y)) {
      console.error('Coordinates not found.');

      return;
    }

    if (character.x !== x || character.y !== y) {
      move(character, x, y);
    }

    // @TODO: Attack!

    return;
  });
};
