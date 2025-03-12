import { craft, move } from "../characters/actions.js";
import { fetchCharacter } from "../fetches/characters.js";
import { fetchItems } from "../fetches/items.js";
import { fetchMaps } from "../fetches/maps.js";
import { getCharacterName } from "../helpers/arguments.js";
import { COOKING } from "../helpers/maps.js";
import { exists, logInfo } from "../helpers/utilities.js";

try {
  const characterName = await getCharacterName();
  const character = await fetchCharacter(characterName);
  const items = await fetchItems(COOKING, character.cooking_level);

  const map = await fetchMaps(COOKING);
  const characterAtWorkshop = await move(character, map[0]);

  const characterAfterCooking = await cook(characterAtWorkshop, items);

  logInfo(`${characterAfterCooking.name} cooking complete!`);
} catch (error) {}

async function cook(character, items) {
  const inventory = character.inventory;

  const craftableItems = items.filter((item) => {
    const requiredItems = item.craft.items;

    return requiredItems.every((requiredItem) => {
      const inventoryItem = inventory.find(
        (inventoryItem) => inventoryItem.code === requiredItem.code,
      );

      return (
        exists(inventoryItem) && inventoryItem.quantity >= requiredItem.quantity
      );
    });
  });

  // @TODO: Loop all craftable items, not just 1
  // @TODO: Set the quantity to the total possible in the inventory
  return await craft(character, craftableItems[0].code, 1);
}
