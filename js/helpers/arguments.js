import { errorMessage, exists } from "./utilities.js";

const NAME_PREFIX = "CHARACTER=";
const MONSTER_PREFIX = "MONSTER=";
const MAX_ATTACKS_PREFIX = "MAX_ATTACKS=";

export async function getCharacterName() {
  const arg = process.argv.find((arg) => arg.startsWith(NAME_PREFIX));

  if (!exists(arg)) {
    throw errorMessage("Please input a character name");
  }

  return arg.replace(NAME_PREFIX, "");
}

export async function getMonsterCode() {
  const arg = process.argv.find((arg) => arg.startsWith(MONSTER_PREFIX));

  if (!exists(arg)) {
    throw errorMessage("Please input a monster");
  }

  return arg.replace(MONSTER_PREFIX, "");
}

export async function getMaxAttacks() {
  const arg = process.argv.find((arg) => arg.startsWith(MAX_ATTACKS_PREFIX));

  if (!exists(arg)) {
    throw errorMessage("Please input a max attack number");
  }

  return arg.replace(MAX_ATTACKS_PREFIX, "");
}
