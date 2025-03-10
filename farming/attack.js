import { delay, logInfo } from "../helper.js";
import { move, rest, fight } from "../characters/actions.js";
import { fetchMaps } from "../fetches/maps.js";
import { fetchMonster } from "../fetches/monsters.js";
import { fetchCharacter } from "../fetches/characters.js";
import { moveAndDepositGold } from "../destinations/bank.js";

const NAME_PREFIX = "CHARACTER=";
const MONSTER_PREFIX = "MONSTER=";
const MAX_ATTACKS_PREFIX = "MAX_ATTACKS=";

// @TODO: Fetch highest rated monster that the character could kill.
// @TODO: Stop if inventory gets maxed out (maybe utlize the bank).
// @TODO: Rest at the start to ensure max HP.

const characterName = process.argv
  .find((arg) => arg.startsWith(NAME_PREFIX))
  .replace(NAME_PREFIX, "");
const monsterCode = process.argv
  .find((arg) => arg.startsWith(MONSTER_PREFIX))
  .replace(MONSTER_PREFIX, "");
const maxAttacks = process.argv
  .find((arg) => arg.startsWith(MAX_ATTACKS_PREFIX))
  .replace(MAX_ATTACKS_PREFIX, "");

let attackCount = 0;

// --------------------------------------------------- Init --------------------------------------------------- //

fetchCharacter(characterName)
  .then((character) => handleMapActions(character))
  .catch((error) => console.error(error));

// ----------------------------------------------- Action Chain ----------------------------------------------- //

async function handleMapActions(character) {
  return await fetchMaps(monsterCode)
    .then((maps) =>
      delay(character).then(() => handleMoveActions(character, maps[0])),
    )
    .catch((error) => console.error(error));
}

async function handleMoveActions(character, location) {
  return await delay(character).then(() =>
    move(character, location)
      .then((movedCharacter) => handleMonsterActions(movedCharacter, location))
      .catch((error) => console.error(error)),
  );
}

async function handleMonsterActions(character, location) {
  return await fetchMonster(location.content.code)
    .then((monster) => handleAttackActions(character, monster))
    .catch((error) => console.error(error));
}

async function handleAttackActions(character, monster) {
  return await delay(character).then(() =>
    attack(character, monster).then((victoriousCharacter) =>
      delay(victoriousCharacter).then(() =>
        moveAndDepositGold(victoriousCharacter),
      ),
    ),
  );
}

async function handleRestActions(character, monster) {
  return await delay(character).then(() =>
    rest(character)
      .then((restedCharacter) => attack(restedCharacter, monster))
      .catch((error) => console.error(error)),
  );
}

// ------------------------------------------------ Farm Loop ------------------------------------------------ //

async function attack(character, monster) {
  if (character.hp <= monster.hp) {
    // Rest or your character will die!
    return await handleRestActions(character, monster);
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

        return victoriousCharacter;
      })
      .catch((error) => console.error(error)),
  );
}
