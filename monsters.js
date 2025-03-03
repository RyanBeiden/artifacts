import request from './index.js';
import { errorMessage } from './helper.js';

export async function fetchMonster(code) {
  try {
    return await request.get(`/monsters/${code}`)
      .then((response) => response.data)
      .then((monster) => monster.data);
  } catch (error) {
    throw errorMessage(error);
  }
};
