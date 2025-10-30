import request from "../index.js";
import { errorMessage } from "../helpers/utilities.js";

export async function fetchMaps(content = "") {
  const mapsUrl = content !== "" ? `maps?content_code=${content}` : "maps";

  // @TODO: Find closest map since it changes cooldown time

  return request
    .get(mapsUrl)
    .then((response) => response.data)
    .then((maps) => {
      if (maps.data.length === 0) {
        throw `No maps found.`;
      }

      return maps.data;
    })
    .catch((error) => errorMessage(error));
}
