import request from "../index.js";
import { delay, errorMessage, exists, logInfo } from "../helpers/utilities.js";

export async function rest(character) {
  return delay(character).then(() =>
    // @TODO: If the character has consumables, use them before resting.

    request
      .post(`/my/${character.name}/action/rest`)
      .then((response) => response.data)
      .then((responseData) => {
        logInfo(
          `${character.name} recovered ${responseData.data.hp_restored} HP`,
        );

        return responseData.data.character;
      })
      .catch((error) => errorMessage(error)),
  );
}

export async function fight(character) {
  return delay(character).then(() =>
    request
      .post(`/my/${character.name}/action/fight`)
      .then((response) => response.data)
      .then((responseData) => {
        logInfo(
          `Gold received: ${responseData.data.fight.gold} - (${responseData.data.fight.result})`,
        );

        return responseData.data.character;
      })
      .catch((error) => errorMessage(error)),
  );
}

export async function move(character, location) {
  const { x, y, content } = location;

  if (!exists(x) || !exists(y) || !exists(content)) {
    throw "Coordinates not found.";
  }

  if (character.x === x && character.y === y) {
    logInfo(`${character.name} is already at (${x}, ${y})`);

    return character;
  }

  return delay(character).then(() =>
    request
      .post(`/my/${character.name}/action/move`, { x, y })
      .then((response) => response.data)
      .then((responseData) => {
        logInfo(
          `${character.name} moved to: ${responseData.data.destination.content.type} (${x}, ${y})`,
        );

        return responseData.data.character;
      })
      .catch((error) => errorMessage(error)),
  );
}

export async function depositGold(character) {
  const quantity = character.gold;

  if (quantity < 1) {
    logInfo(`${character.name} has no gold to deposit`);

    return;
  }

  return delay(character).then(() =>
    request
      .post(`/my/${character.name}/action/bank/deposit/gold`, { quantity })
      .then((response) => response.data)
      .then((responseData) => {
        logInfo(
          `${quantity} gold deposited to the bank - Total gold: ${responseData.data.bank.quantity}`,
        );

        return responseData.data.character;
      })
      .catch((error) => errorMessage(error)),
  );
}

export async function craft(character, code, quantity) {
  return delay(character).then(() =>
    request
      .post(`/my/${character.name}/action/crafting`, { code, quantity })
      .then((response) => response.data)
      .then((responseData) => {
        logInfo(`Total ${code} crafted: ${quantity}`);

        return responseData.data.character;
      })
      .catch((error) => errorMessage(error)),
  );
}
