import { delay, logInfo } from "../helper.js";
import { move, rest, fight } from "../characters/actions.js";
import { fetchMaps } from "../fetches/maps.js";
import { fetchMonster } from "../fetches/monsters.js";
import { fetchCharacter } from "../fetches/characters.js";

const NAME = "chunt";
const MONSTER_PREFIX = "MONSTER=";
const MAX_ATTACKS_PREFIX = "MAX_ATTACKS=";

// @TODO: Fetch highest rated monster that chunt could kill.
// @TODO: Stop if inventory gets maxed out (maybe utlize the bank).
// @TODO: Rest at the start to ensure max HP.
// @TODO: Maybe I hard code the character name, map location, and monster and just occassionally verify it still works.
//        Then I would only need to run `attack()`. The nesting is getting out of hand...

const monsterCode = process.argv.find((arg) => arg.startsWith(MONSTER_PREFIX)).replace(MONSTER_PREFIX, '');
const maxAttacks = process.argv.find((arg) => arg.startsWith(MAX_ATTACKS_PREFIX)).replace(MAX_ATTACKS_PREFIX, '');

let attackCount = 0;

fetchCharacter(NAME)
  .then((character) => {
    if (monsterCode === '' || maxAttacks === 0) {
      logInfo('MONSTER and MAX_ATTACKS arguments are required to farm.');

      return;
    }

    fetchMaps(monsterCode)
      .then((maps) =>
        delay(character).then(() => handleActions(character, maps[0])),
      )
      .catch((error) => console.error(error));
  })
  .catch((error) => console.error(error));

// ----------------------------------------------- Functions ----------------------------------------------- //

async function handleActions(character, location) {
  return await move(character, location)
    .then((character) => {
      fetchMonster(location.content.code)
        .then((monster) =>
          delay(character).then(() => attack(character, monster)),
        )
        .catch((error) => console.error(error));
    })
    .catch((error) => console.error(error));
}

async function attack(character, monster) {
  if (character.hp <= monster.hp) {
    // Rest or chunt will die!
    return await delay(character).then(() =>
      rest(character)
        .then((restedCharacter) => attack(restedCharacter, monster))
        .catch((error) => console.error(error)),
    );
  }

  return await delay(character).then(() =>
    fight(character)
      .then((victoriousCharacter) => {
        attackCount++;

        if (attackCount < maxAttacks) {
          logInfo(`Attacks completed: ${attackCount}/${maxAttacks}`);

          return delay(character).then(() =>
            attack(victoriousCharacter, monster),
          );
        }

        logInfo(
          `Attacking complete. Current level: ${victoriousCharacter.level}`,
        );

        return;
      })
      .catch((error) => console.error(error)),
  );
}
