import request from "../index.js";
import { errorMessage, exists } from "../helpers/utilities.js";

export async function fetchItems(craftSkill = "", skillLevel = 0) {
  const url = "/items";
  const craftQuery = exists(craftSkill) ? `craft_skill=${craftSkill}` : "";
  const levelQuery = exists(skillLevel) ? `max_level=${skillLevel}` : "";

  return request
    .get(`${url}?${craftQuery}&${levelQuery}`)
    .then((response) => response.data)
    .then((items) => {
      if (items.data.length === 0) {
        throw `No items for found.`;
      }

      return items.data;
    })
    .catch((error) => errorMessage(error));
}
