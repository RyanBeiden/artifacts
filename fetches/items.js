import request from "../index.js";
import { errorMessage, exists } from "../helpers/utilities.js";

export async function fetchItems(craftSkill = "", skillLevel = 0, type = "") {
  const url = "/items";
  const query = [];

  if (exists(craftSkill)) {
    query.push(`craft_skill=${craftSkill}`);
  }

  if (exists(skillLevel)) {
    query.push(`max_level=${skillLevel}`);
  }

  if (exists(type)) {
    query.push(`type=${type}`);
  }

  const requestUrl = query.length > 0 ? `${url}?${query.join('&')}` : url;

  return request
    .get(requestUrl)
    .then((response) => response.data)
    .then((items) => {
      if (items.data.length === 0) {
        throw `No items for found.`;
      }

      return items.data;
    })
    .catch((error) => errorMessage(error));
}
