import request from './index.js';

export async function chickens() {
  const response = await request.get('maps?content_code=chicken');

  return response.data;
};
