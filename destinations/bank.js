import { delay } from "../helper.js";
import { fetchMaps } from "../fetches/maps.js";
import { depositGold, move } from "../characters/actions.js";

const BANK = "bank";

export async function moveAndDepositGold(character) {
  return await fetchMaps(BANK)
    .then((maps) =>
      move(character, maps[0]).then((movedCharacter) =>
        delay(movedCharacter).then(() => depositGold(movedCharacter)),
      ),
    )
    .catch((error) => console.error(error));
}
