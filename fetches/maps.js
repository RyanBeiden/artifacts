import request from "../index.js";
import { errorMessage } from "../helper.js";

export async function fetchMaps(content = "") {
  const mapsUrl = content !== "" ? `maps?content_code=${content}` : "maps";

  return request
    .get(mapsUrl)
    .then((response) => response.data)
    .then((maps) => {
      if (maps.data.length === 0) {
        throw `No maps for ${content} found.`;
      }

      return maps.data;
    })
    .catch((error) => errorMessage(error));
}
