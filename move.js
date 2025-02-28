import request from './index.js';

export async function move(character, x, y) {
  const response = await request.post(`/my/${character.name}/action/move`, { x, y });

  return response.data;
};
