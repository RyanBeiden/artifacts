import 'dotenv/config';
import axios from "axios";

const instance = axios.create({
  baseURL: process.env.BASE_URL,
  timeout: 10000,
  headers: {
    "Accept": "application/json",
    "Content-type": "application/json",
    "Authorization": `Bearer ${process.env.TOKEN}`,
  },
});

export default instance;
