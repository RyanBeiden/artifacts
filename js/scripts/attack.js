import { logInfo } from "../helpers/utilities.js";
import { move, rest, fight, depositGold } from "../characters/actions.js";
import { fetchMaps } from "../fetches/maps.js";
import { fetchMonster } from "../fetches/monsters.js";
import { fetchCharacter } from "../fetches/characters.js";
import { BANK } from "../helpers/maps.js";
import {
  getCharacterName,
  getMaxAttacks,
  getMonsterCode,
} from "../helpers/arguments.js";

// @TODO: Fetch highest rated monster that the character could kill.
// @TODO: Stop if inventory gets maxed out (maybe utlize the bank).
// @TODO: Rest at the start to ensure max HP.

let attackCount = 0;

try {
  const monsterCode = await getMonsterCode();
  const characterName = await getCharacterName();
  const maxAttacks = await getMaxAttacks();

  const monster = await fetchMonster(monsterCode);
  const character = await fetchCharacter(characterName);

  // Move and attack
  const map = await fetchMaps(monsterCode);
  const characterAtMonster = await move(character, map[0]);
  const victoriousCharacter = await attack(
    characterAtMonster,
    monster,
    maxAttacks,
  );

  // Move and deposit gold
  const bank = await fetchMaps(BANK);
  const characterAtBank = await move(victoriousCharacter, bank[0]);
  const characterAfterGoldDeposit = await depositGold(characterAtBank);

  logInfo(`${characterAfterGoldDeposit.name} attack farming complete!`);
} catch (error) {}

export async function attack(character, monster, maxAttacks) {
  if (character.hp <= monster.hp) {
    // Rest or your character will die!
    const restedCharacter = await rest(character);

    return attack(restedCharacter, monster, maxAttacks);
  }

  if (attackCount > 0) {
    logInfo(`Attacks completed: ${attackCount}/${maxAttacks}`);
  }

  attackCount++;

  const victoriousCharacter = await fight(character);

  if (attackCount < maxAttacks) {
    return attack(victoriousCharacter, monster, maxAttacks);
  }

  logInfo(`Attacking complete. Current level: ${victoriousCharacter.level}`);

  return victoriousCharacter;
}
