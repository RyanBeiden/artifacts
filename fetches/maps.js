import request from '../index.js';
import { errorMessage } from '../helper.js';

export async function fetchMaps(contentCode = '') {
  try {
    const mapsUrl = contentCode !== ''
      ? `maps?content_code=${contentCode}`
      : 'maps';

    return await request.get(mapsUrl)
      .then((response) => response.data)
      .then((maps) => {
        if (maps.data.length === 0) {
          throw `No maps for ${contentCode} found.`;
        }

        return maps.data;
      });
  } catch (error) {
    throw errorMessage(error);
  }
};
