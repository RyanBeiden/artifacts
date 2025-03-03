import request from "./index.js";
import { errorMessage, exists, logInfo } from "./helper.js";

export async function rest(character) {
  try {
    return await request.post(`/my/${character.name}/action/rest`)
      .then((response) => response.data)
      .then((responseData) => {
        logInfo(`${character.name} recovered ${responseData.data.hp_restored} HP`);

        return responseData.data.character;
      });
  } catch (error) {
    throw errorMessage(error);
  }
};

export async function fight(character) {
  try {
    return await request.post(`/my/${character.name}/action/fight`)
      .then((response) => response.data)
      .then((responseData) => {
        // logInfo(`${character.name}'s fight resulted in a ${responseData.data.fight.result}`);
        logInfo(`Gold received: ${responseData.data.fight.gold}`);

        return responseData.data.character;
      });
  } catch (error) {
    throw errorMessage(error);
  }
};

export async function move(character, location) {
  try {
    const { x, y, content } = location;

    if (!exists(x) || !exists(y) || !exists(content)) {
      throw 'Coordinates not found.';
    }

    if (character.x === x && character.y === y) {
      logInfo(`${character.name} is already at (${x}, ${y})`);

      return character;
    }

    return request.post(`/my/${character.name}/action/move`, { x, y })
      .then((response) => response.data)
      .then((responseData) => {
        logInfo(`${character.name} moved to (${x}, ${y})`);

        return responseData.data.character
      });
  } catch (error) {
    throw errorMessage(error);
  }
};
