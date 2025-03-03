import { delay, logInfo } from "./helper.js";
import { fetchMaps } from "./maps.js";
import { fetchMonster } from "./monsters.js";
import { fetchCharacter } from "./characters.js";
import { move, rest, fight } from "./actions.js";

const NAME = 'chunt';
const MONSTER = 'yellow_slime';
const MAX_ATTACKS = 5;

let attackCount = 0;

// @TODO: Fetch highest rated monster that chunt could kill.
// @TODO: Stop if inventory gets maxed out (maybe utlize the bank).
// @TODO: Rest at the start to ensure max HP.
// @TODO: Maybe I hard code the character name, map location, and monster and just occassionally verify it still works.
//        Then I would only need to run `attack()`. The nesting is getting out of hand...

fetchCharacter(NAME)
  .then((character) => {
    fetchMaps(MONSTER)
      .then((maps) => delay(character).then(() => handleActions(character, maps[0])))
      .catch((error) => console.error(error));
  })
  .catch((error) => console.error(error));

// ----------------------------------------------- Functions ----------------------------------------------- //

async function handleActions(character, location) {
  return await move(character, location)
    .then((character) => {
      fetchMonster(location.content.code)
        .then((monster) => delay(character).then(() => attack(character, monster)))
        .catch((error) => console.error(error));
    })
    .catch((error) => console.error(error));
};

async function attack(character, monster) {
  if (character.hp <= monster.hp) {
    // Rest or chunt will die!
    return await delay(character).then(() => rest(character)
      .then((restedCharacter) => attack(restedCharacter, monster))
      .catch((error) => console.error(error))
    );
  }

  return await delay(character).then(() => fight(character)
    .then((victoriousCharacter) => {
      attackCount++;

      if (attackCount < MAX_ATTACKS) {
        logInfo(`Attacks completed: ${attackCount}`);

        return delay(character).then(() => attack(victoriousCharacter, monster));
      }

      logInfo(`Attacking complete. Current level: ${victoriousCharacter.level}`);

      return;
    })
    .catch((error) => console.error(error))
  );
};
