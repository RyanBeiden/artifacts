import { logInfo } from "../helper.js";
import { move, rest, fight, depositGold } from "../characters/actions.js";
import { fetchMaps } from "../fetches/maps.js";
import { fetchMonster } from "../fetches/monsters.js";
import { fetchCharacter } from "../fetches/characters.js";

const NAME_PREFIX = "CHARACTER=";
const MONSTER_PREFIX = "MONSTER=";
const MAX_ATTACKS_PREFIX = "MAX_ATTACKS=";

const BANK = "bank";

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

try {
  // @TODO: Find closest map since it changes cooldown time
  const monster = await fetchMonster(monsterCode);
  const character = await fetchCharacter(characterName);

  // Move and attack
  const map = await fetchMaps(monsterCode);
  const characterAtMonster = await move(character, map[0]);
  const victoriousCharacter = await attack(characterAtMonster, monster);

  // Move and deposit gold
  const bank = await fetchMaps(BANK);
  const characterAtBank = await move(victoriousCharacter, bank[0]);
  const characterAfterGoldDeposit = await depositGold(characterAtBank);

  logInfo(`${characterAfterGoldDeposit.name} attack farming complete!`);
} catch (error) {}

async function attack(character, monster) {
  if (character.hp <= monster.hp) {
    // Rest or your character will die!
    const restedCharacter = await rest(character);

    return attack(restedCharacter, monster);
  }

  if (attackCount > 0) {
    logInfo(`Attacks completed: ${attackCount}/${maxAttacks}`);
  }

  attackCount++;

  const victoriousCharacter = await fight(character);

  if (attackCount < maxAttacks) {
    return attack(victoriousCharacter, monster);
  }

  logInfo(`Attacking complete. Current level: ${victoriousCharacter.level}`);

  return victoriousCharacter;
}
